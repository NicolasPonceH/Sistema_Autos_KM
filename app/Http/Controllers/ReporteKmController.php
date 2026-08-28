<?php

namespace App\Http\Controllers;

use App\Models\LecturaOdometro;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
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

    public function exportar(Request $request): StreamedResponse
    {
        [$filas, $mesInicio, $mesFin] = $this->construirReporte($request);

        $nombreArchivo = sprintf('km-%s-a-%s.csv', $mesInicio->format('Y-m'), $mesFin->format('Y-m'));

        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');
            // BOM UTF-8: que Excel no arruine las tildes al abrir el CSV.
            fwrite($salida, "\xEF\xBB\xBF");

            $meses = $filas->first()['meses'] ?? collect();
            fputcsv($salida, ['Patente', ...$meses->map(fn ($m) => $m['mes']->translatedFormat('M Y'))->all(), 'Total']);

            foreach ($filas as $fila) {
                $valores = $fila['meses']->map(fn ($m) => $m['kmRecorridos'] ?? '');
                fputcsv($salida, [$fila['vehiculo']->patente, ...$valores->all(), $fila['meses']->sum('kmRecorridos')]);
            }

            fclose($salida);
        }, $nombreArchivo, ['Content-Type' => 'text/csv']);
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
     * este mes y el del mes anterior con datos. Sin lectura en un mes, no
     * hay cierre nuevo y el km recorrido queda sin dato (null), no en 0.
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
