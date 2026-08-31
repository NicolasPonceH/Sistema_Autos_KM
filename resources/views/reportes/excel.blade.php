<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Oficial de Control de Kilometraje - PDI</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            color: #191c1e;
            margin: 0;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .titulo-pdi {
            font-size: 16pt;
            font-weight: bold;
            color: #ffffff;
            background-color: #000a1f;
            text-align: center;
            height: 40px;
        }
        .subtitulo-pdi {
            font-size: 10pt;
            font-weight: bold;
            color: #d7e2ff;
            background-color: #000a1f;
            text-align: center;
            height: 25px;
        }
        .nombre-informe {
            font-size: 12pt;
            font-weight: bold;
            color: #ffe08b;
            background-color: #00204a;
            text-align: center;
            height: 32px;
        }
        .meta-label {
            font-weight: bold;
            color: #00204a;
            background-color: #e0e3e5;
            border: 1px solid #c4c6d0;
            padding: 6px 10px;
            font-size: 10pt;
        }
        .meta-valor {
            color: #191c1e;
            background-color: #f7f9fb;
            border: 1px solid #c4c6d0;
            padding: 6px 10px;
            font-size: 10pt;
        }
        .th-header {
            font-size: 10pt;
            font-weight: bold;
            color: #ffffff;
            background-color: #00204a;
            border: 1px solid #747780;
            text-align: center;
            vertical-align: middle;
            height: 32px;
            padding: 6px;
        }
        .th-sub {
            font-size: 10pt;
            font-weight: bold;
            color: #ffffff;
            background-color: #003399;
            border: 1px solid #747780;
            text-align: center;
            vertical-align: middle;
            padding: 6px;
        }
        .td-data {
            border: 1px solid #c4c6d0;
            vertical-align: middle;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .td-center {
            border: 1px solid #c4c6d0;
            text-align: center;
            vertical-align: middle;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .td-num {
            border: 1px solid #c4c6d0;
            text-align: right;
            vertical-align: middle;
            padding: 6px 8px;
            font-size: 10pt;
            mso-number-format: "\#\,\#\#0";
        }
        .td-patente {
            border: 1px solid #c4c6d0;
            text-align: center;
            font-weight: bold;
            font-family: "Courier New", monospace;
            color: #000a1f;
            padding: 6px 8px;
            font-size: 11pt;
        }
        .fila-par {
            background-color: #ffffff;
        }
        .fila-impar {
            background-color: #f2f4f6;
        }
        .td-total {
            font-weight: bold;
            background-color: #eceef0;
            border-top: 2px solid #000a1f;
            border-bottom: 2px solid #000a1f;
            border-left: 1px solid #c4c6d0;
            border-right: 1px solid #c4c6d0;
            text-align: right;
            padding: 8px;
            font-size: 10pt;
        }
        .td-total-label {
            font-weight: bold;
            background-color: #eceef0;
            border-top: 2px solid #000a1f;
            border-bottom: 2px solid #000a1f;
            border-left: 1px solid #c4c6d0;
            border-right: 1px solid #c4c6d0;
            padding: 8px;
            font-size: 10pt;
        }
        .firma-box {
            text-align: center;
            font-weight: bold;
            border-top: 1px solid #000000;
            padding-top: 8px;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    @php
        $mesesHeaders = $filas->first()['meses'] ?? collect();
        $totalColumnas = 8 + $mesesHeaders->count();
    @endphp

    <table border="0" cellpadding="0" cellspacing="0">
        <!-- Encabezado Institucional Oficial PDI -->
        <tr>
            <td colspan="{{ $totalColumnas }}" class="titulo-pdi">POLICÍA DE INVESTIGACIONES DE CHILE</td>
        </tr>
        <tr>
            <td colspan="{{ $totalColumnas }}" class="subtitulo-pdi">JEFATURA NACIONAL DE ADMINISTRACIÓN Y LOGÍSTICA · DEPARTAMENTO DE TRANSPORTES</td>
        </tr>
        <tr>
            <td colspan="{{ $totalColumnas }}" class="nombre-informe">INFORME OFICIAL DE CONTROL DE KILOMETRAJE Y TELEMETRÍA DE FLOTA</td>
        </tr>
        <tr><td colspan="{{ $totalColumnas }}" height="12"></td></tr>

        <!-- Metadatos del Informe -->
        <tr>
            <td class="meta-label" colspan="2">Período Consultado:</td>
            <td class="meta-valor" colspan="3">{{ $mesInicio->translatedFormat('F Y') }} hasta {{ $mesFin->translatedFormat('F Y') }}</td>
            <td class="meta-label" colspan="2">Fecha de Emisión:</td>
            <td class="meta-valor" colspan="{{ max(1, $totalColumnas - 7) }}">{{ now()->format('d/m/Y H:i') }} hrs</td>
        </tr>
        <tr>
            <td class="meta-label" colspan="2">Total Unidades:</td>
            <td class="meta-valor" colspan="3">{{ $filas->count() }} vehículos policiales</td>
            <td class="meta-label" colspan="2">Sistema de Control:</td>
            <td class="meta-valor" colspan="{{ max(1, $totalColumnas - 7) }}">AutoTrack PDI Fleet Control v2.0</td>
        </tr>
        <tr><td colspan="{{ $totalColumnas }}" height="15"></td></tr>

        <!-- Encabezados de Columnas -->
        <thead>
            <tr>
                <th class="th-header" style="width: 40px;">N°</th>
                <th class="th-header" style="width: 100px;">PLACA PATENTE</th>
                <th class="th-header" style="width: 130px;">TIPO DE VEHÍCULO</th>
                <th class="th-header" style="width: 120px;">MARCA</th>
                <th class="th-header" style="width: 140px;">MODELO</th>
                <th class="th-header" style="width: 60px;">AÑO</th>
                <th class="th-header" style="width: 220px;">ASIGNACIÓN / CONTACTO</th>
                <th class="th-header" style="width: 130px;">ODÓMETRO ACTUAL</th>
                @foreach ($mesesHeaders as $m)
                    <th class="th-sub" style="width: 110px;">{{ strtoupper($m['mes']->translatedFormat('M Y')) }}</th>
                @endforeach
                <th class="th-header" style="width: 140px;">TOTAL KM PERÍODO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $granTotal = 0;
            @endphp
            @foreach ($filas as $indice => $fila)
                @php
                    $v = $fila['vehiculo'];
                    $totalVehiculo = $fila['meses']->sum('kmRecorridos');
                    $granTotal += $totalVehiculo;
                    $claseFila = $indice % 2 === 0 ? 'fila-par' : 'fila-impar';
                    
                    // Formatear patente chilena con guiones
                    $clean = strtoupper(trim(str_replace(['.', '-', ' '], '', $v->patente)));
                    $patenteFmt = strlen($clean) === 6 ? substr($clean,0,2).'-'.substr($clean,2,2).'-'.substr($clean,4,2) : $clean;
                @endphp
                <tr class="{{ $claseFila }}">
                    <td class="td-center">{{ $indice + 1 }}</td>
                    <td class="td-patente">{{ $patenteFmt }}</td>
                    <td class="td-data">{{ $v->tipoVehiculo->nombre }}</td>
                    <td class="td-data">{{ $v->marca }}</td>
                    <td class="td-data">{{ $v->modelo }}</td>
                    <td class="td-center">{{ $v->anio }}</td>
                    <td class="td-data">{{ $v->email_contacto }}</td>
                    <td class="td-num">{{ number_format($v->km_actual, 0, ',', '.') }} km</td>
                    @foreach ($fila['meses'] as $m)
                        <td class="td-num">
                            {{ is_null($m['kmRecorridos']) ? '—' : number_format($m['kmRecorridos'], 0, ',', '.') }}
                        </td>
                    @endforeach
                    <td class="td-num" style="font-weight: bold; background-color: #e6e8ea;">
                        {{ number_format($totalVehiculo, 0, ',', '.') }} km
                    </td>
                </tr>
            @endforeach

            <!-- Fila de Totales -->
            <tr>
                <td colspan="7" class="td-total-label" style="text-align: right; padding-right: 10px;">
                    TOTAL GENERAL FLOTA PDI:
                </td>
                <td class="td-total td-num">
                    {{ number_format($filas->sum(fn($f) => $f['vehiculo']->km_actual), 0, ',', '.') }} km
                </td>
                @foreach ($mesesHeaders as $idx => $m)
                    @php
                        $sumaMes = $filas->sum(fn($f) => $f['meses'][$idx]['kmRecorridos'] ?? 0);
                    @endphp
                    <td class="td-total td-num">
                        {{ number_format($sumaMes, 0, ',', '.') }}
                    </td>
                @endforeach
                <td class="td-total td-num" style="background-color: #ffe08b; color: #000a1f;">
                    {{ number_format($granTotal, 0, ',', '.') }} km
                </td>
            </tr>
        </tbody>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" style="margin-top: 40px; width: 100%;">
        <tr><td colspan="{{ $totalColumnas }}" height="40"></td></tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="3" class="firma-box">
                OFICIAL ENCARGADO DE TRANSPORTES<br>
                DEPARTAMENTO DE LOGÍSTICA PDI
            </td>
            <td colspan="2"></td>
            <td colspan="3" class="firma-box">
                JEFATURA NACIONAL DE ADMINISTRACIÓN<br>
                POLICÍA DE INVESTIGACIONES DE CHILE
            </td>
        </tr>
    </table>
</body>
</html>
