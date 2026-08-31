<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background-color:#f7f9fb; font-family: Calibri, Arial, sans-serif; color:#191c1e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f9fb; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="540" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #e0e3e5; border-radius:12px; overflow:hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:24px; background-color:#000a1f; color:#ffffff;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="font-size:18px; font-weight:bold; letter-spacing:0.02em;">POLICÍA DE INVESTIGACIONES DE CHILE</div>
                                        <div style="font-size:11px; color:#d7e2ff; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Jefatura Nacional de Administración y Logística</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title Banner -->
                    <tr>
                        <td style="padding:14px 24px; background-color:#00204a; border-bottom:1px solid #e0e3e5;">
                            <div style="font-size:13px; font-weight:bold; color:#ffe08b; text-transform:uppercase;">
                                Informe Consolidado de Kilometraje y Flota
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.5; color:#191c1e;">
                                Estimado(a),
                            </p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.5; color:#44474f;">
                                Se adjunta el informe oficial de control de odómetros y rodaje de la flota policial correspondiente al período <strong>{{ $mesInicio->translatedFormat('F Y') }} - {{ $mesFin->translatedFormat('F Y') }}</strong>.
                            </p>

                            <!-- KPI Summary Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f4f6; border-radius:8px; border:1px solid #e0e3e5; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e0e3e5; font-size:13px; color:#44474f;">
                                        Total Unidades Policiales:
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e0e3e5; font-size:14px; font-weight:bold; text-align:right; color:#000a1f;">
                                        {{ $filas->count() }} vehículos
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e0e3e5; font-size:13px; color:#44474f;">
                                        Kilómetros Totales Recorridos en el Período:
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e0e3e5; font-size:15px; font-weight:bold; text-align:right; color:#003399;">
                                        {{ number_format($granTotal, 0, ',', '.') }} km
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#44474f;">
                                        Archivo Adjunto:
                                    </td>
                                    <td style="padding:12px 16px; font-size:12px; font-weight:bold; text-align:right; color:#10b981;">
                                        📊 Planilla Oficial Excel (.xls)
                                    </td>
                                </tr>
                            </table>

                            @if ($mensajeAdicional)
                                <div style="background-color:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px; margin-bottom:20px; font-size:13px; color:#745b00;">
                                    <strong>Observaciones de la Jefatura:</strong><br>
                                    {{ $mensajeAdicional }}
                                </div>
                            @endif

                            <p style="margin:0; font-size:13px; color:#44474f; line-height:1.5;">
                                En el archivo adjunto encontrará el desglose detallado por vehículo, patentes, marcas, asignaciones por brigada y rodaje mes a mes.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:16px 24px; background-color:#f7f9fb; border-top:1px solid #e0e3e5; text-align:center;">
                            <p style="margin:0; font-size:11px; color:#747780;">
                                AutoTrack PDI Fleet Control · Generado automáticamente por el Departamento de Transportes
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
