<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family: Arial, Helvetica, sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; background-color:{{ $estado->vencido() ? '#fef2f2' : '#fffbeb' }}; border-bottom:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; font-weight:bold; letter-spacing:0.05em; text-transform:uppercase; color:{{ $estado->vencido() ? '#e11d48' : '#d97706' }};">
                                {{ $estado->vencido() ? 'Servicio vencido' : 'Servicio próximo' }}
                            </p>
                            <h1 style="margin:6px 0 0; font-size:18px;">{{ $estado->plan->nombre }} — {{ $vehiculo->patente }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.5;">
                                {{ $estado->descripcion() }} para el servicio "{{ $estado->plan->nombre }}" de
                                <strong>{{ $vehiculo->patente }}</strong> ({{ $vehiculo->tipoVehiculo->nombre }} —
                                {{ $vehiculo->marca }} {{ $vehiculo->modelo }}).
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Kilometraje actual</td>
                                    <td style="padding:6px 0; text-align:right; font-weight:bold;">{{ number_format($vehiculo->km_actual, 0, ',', '.') }} km</td>
                                </tr>
                                @if ($vehiculo->fecha_km)
                                    <tr>
                                        <td style="padding:6px 0; color:#64748b;">Fecha de esa lectura</td>
                                        <td style="padding:6px 0; text-align:right;">{{ $vehiculo->fecha_km->format('d-m-Y H:i') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Kilometraje objetivo</td>
                                    <td style="padding:6px 0; text-align:right;">{{ number_format($estado->kmObjetivo, 0, ',', '.') }} km</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">{{ $estado->vencidoPorKm() ? 'Atraso' : 'Restantes' }}</td>
                                    <td style="padding:6px 0; text-align:right; font-weight:bold; color:{{ $estado->vencidoPorKm() ? '#e11d48' : '#0f172a' }};">
                                        {{ number_format(abs($estado->kmFaltantes), 0, ',', '.') }} km
                                    </td>
                                </tr>
                                @if ($estado->descripcionTiempo())
                                    <tr>
                                        <td style="padding:6px 0; color:#64748b;">{{ $estado->vencidoPorTiempo() ? 'Atraso (tiempo)' : 'Restantes (tiempo)' }}</td>
                                        <td style="padding:6px 0; text-align:right; font-weight:bold; color:{{ $estado->vencidoPorTiempo() ? '#e11d48' : '#0f172a' }};">
                                            {{ number_format(abs($estado->diasFaltantes), 0, ',', '.') }} días
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Último servicio del mismo tipo</td>
                                    <td style="padding:6px 0; text-align:right;">
                                        @if ($ultimoServicio)
                                            {{ $ultimoServicio->fecha->format('d-m-Y') }} — {{ number_format($ultimoServicio->km_evento, 0, ',', '.') }} km
                                        @else
                                            Nunca registrado
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; background-color:#f8fafc; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                Sistema Autos KM — aviso automático de mantención.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
