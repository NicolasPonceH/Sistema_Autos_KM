<?php

namespace App\Http\Controllers;

use App\Models\LecturaOdometro;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteKmController extends Controller
{
    public function index(Request $request): View
    {
        [$filas, $mesInicio, $mesFin] = $this->construirReporte($request);

        return view('reportes.km', compact('filas', 'mesInicio', 'mesFin'));
    }

    public function exportar(Request $request): Response|StreamedResponse
    {
        [$filas, $mesInicio, $mesFin] = $this->construirReporte($request);

        // Si se pide formato CSV explícitamente:
        if ($request->query('formato') === 'csv') {
            $nombreArchivo = sprintf('INFORME_PDI_KM_%s_a_%s.csv', $mesInicio->format('Y-m'), $mesFin->format('Y-m'));

            return response()->streamDownload(function () use ($filas, $mesInicio, $mesFin) {
                $salida = fopen('php://output', 'w');
                // BOM UTF-8 para que Excel en Windows interprete tildes y caracteres especiales
                fwrite($salida, "\xEF\xBB\xBF");

                // Encabezado Institucional PDI en CSV
                fputcsv($salida, ['POLICIA DE INVESTIGACIONES DE CHILE'], ';');
                fputcsv($salida, ['JEFATURA NACIONAL DE ADMINISTRACION Y LOGISTICA - DEPARTAMENTO DE TRANSPORTES'], ';');
                fputcsv($salida, ['INFORME OFICIAL DE CONTROL DE KILOMETRAJE Y TELEMETRIA'], ';');
                fputcsv($salida, ['Periodo:', sprintf('%s a %s', $mesInicio->format('d/m/Y'), $mesFin->format('d/m/Y')), 'Fecha Emision:', now()->format('d/m/Y H:i')], ';');
                fputcsv($salida, [], ';');

                $meses = $filas->first()['meses'] ?? collect();
                $headersMeses = $meses->map(fn ($m) => strtoupper($m['mes']->translatedFormat('M Y')))->all();

                // Cabecera de columnas
                fputcsv($salida, [
                    'N°',
                    'Placa Patente',
                    'Tipo Vehiculo',
                    'Marca',
                    'Modelo',
                    'Año',
                    'Asignacion / Contacto',
                    'Odometro Actual (km)',
                    ...$headersMeses,
                    'Total Km Periodo',
                ], ';');

                foreach ($filas as $indice => $fila) {
                    $v = $fila['vehiculo'];
                    $valoresMeses = $fila['meses']->map(fn ($m) => $m['kmRecorridos'] ?? '')->all();

                    fputcsv($salida, [
                        $indice + 1,
                        $v->patente,
                        $v->tipoVehiculo->nombre,
                        $v->marca,
                        $v->modelo,
                        $v->anio,
                        $v->email_contacto,
                        $v->km_actual,
                        ...$valoresMeses,
                        $fila['meses']->sum('kmRecorridos'),
                    ], ';');
                }

                // Fila de Totales
                $totalesMeses = $meses->map(function ($m, $idx) use ($filas) {
                    return $filas->sum(fn ($f) => $f['meses'][$idx]['kmRecorridos'] ?? 0);
                })->all();

                fputcsv($salida, [
                    'TOTAL GENERAL',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $filas->sum(fn ($f) => $f['vehiculo']->km_actual),
                    ...$totalesMeses,
                    $filas->sum(fn ($f) => $f['meses']->sum('kmRecorridos')),
                ], ';');

                fclose($salida);
            }, $nombreArchivo, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Por defecto: Excel con formato enriquecido PDI (.xls)
        $nombreArchivo = sprintf('INFORME_PDI_KM_%s_a_%s.xls', $mesInicio->format('Y-m'), $mesFin->format('Y-m'));

        $contenido = view('reportes.excel', compact('filas', 'mesInicio', 'mesFin'))->render();

        return response($contenido, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function enviarCorreo(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
            'desde' => ['nullable', 'string'],
            'hasta' => ['nullable', 'string'],
            'patente' => ['nullable', 'string'],
        ]);

        [$filas, $mesInicio, $mesFin] = $this->construirReporte($request);

        \Illuminate\Support\Facades\Mail::to($validated['email'])->send(
            new \App\Mail\ReporteKmMail($filas, $mesInicio, $mesFin, $validated['mensaje'] ?? null)
        );

        return back()->with('status', 'Informe oficial de kilometraje enviado con éxito a '.$validated['email'].' (con archivo Excel adjunto).');
    }

    /**
     * @return array{0: Collection, 1: Carbon, 2: Carbon}
     */
    private function construirReporte(Request $request): array
    {
        $mesInicio = $request->filled('desde')
            ? Carbon::parse($request->string('desde').'-01')->startOfMonth()
            : now()->subMonthsNoOverflow(5)->startOfMonth();

        $mesFin = $request->filled('hasta')
            ? Carbon::parse($request->string('hasta').'-01')->startOfMonth()
            : now()->startOfMonth();

        $vehiculos = Vehiculo::query()
            ->when(
                $request->filled('patente'),
                fn ($query) => $query->where('patente', 'like', '%'.strtoupper($request->string('patente')).'%')
            )
            ->orderBy('patente')
            ->get();

        $filas = $vehiculos->map(fn (Vehiculo $vehiculo) => [
            'vehiculo' => $vehiculo,
            'meses' => $this->kmPorMes($vehiculo, $mesInicio, $mesFin),
        ]);

        return [$filas, $mesInicio, $mesFin];
    }

    /**
     * Km recorridos por mes = diferencia entre el cierre de odómetro de
     * este mes y el del mes anterior con datos.
     *
     * @return Collection<int, array{mes: Carbon, kmRecorridos: ?int}>
     */
    private function kmPorMes(Vehiculo $vehiculo, Carbon $mesInicio, Carbon $mesFin): Collection
    {
        $lecturas = LecturaOdometro::where('patente', $vehiculo->patente)
            ->orderBy('fecha')
            ->get(['km', 'fecha']);

        $kmAnterior = $lecturas->last(fn ($l) => $l->fecha->lt($mesInicio))?->km;

        $resultado = collect();
        $cursor = $mesInicio->copy();

        while ($cursor->lte($mesFin)) {
            $finDeMes = $cursor->copy()->endOfMonth();
            $kmCierre = $lecturas->last(fn ($l) => $l->fecha->lte($finDeMes))?->km;

            $resultado->push([
                'mes' => $cursor->copy(),
                'kmRecorridos' => ($kmCierre !== null && $kmAnterior !== null) ? $kmCierre - $kmAnterior : null,
            ]);

            if ($kmCierre !== null) {
                $kmAnterior = $kmCierre;
            }

            $cursor->addMonthNoOverflow();
        }

        return $resultado;
    }
}
