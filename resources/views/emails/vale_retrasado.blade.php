<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Incidencia de Inventario - CORTEX NOC</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        
        body {
            margin: 0;
            padding: 0;
            background-color: #030712;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #0b1329;
            border: 1px solid #1e293b;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
            padding: 35px 40px;
            text-align: center;
            border-bottom: 2px solid #b91c1c;
        }
        .header-logo {
            font-size: 3rem;
            margin-bottom: 10px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header p {
            color: #fca5a5;
            font-size: 11px;
            margin: 6px 0 0 0;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .body {
            padding: 40px;
        }
        .alert-card {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-left: 5px solid #ef4444;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .alert-card p {
            color: #f87171;
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            font-weight: 500;
        }
        .section-title {
            color: #64ffda;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #1e293b;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            background: #0f172a;
            border-radius: 12px;
            border: 1px solid #1e293b;
        }
        .stats-col {
            display: table-cell;
            width: 50%;
            padding: 20px;
            text-align: center;
            vertical-align: middle;
        }
        .stats-col:first-child {
            border-right: 1px solid #1e293b;
        }
        .stats-number {
            font-size: 40px;
            font-weight: 800;
            color: #ef4444;
            line-height: 1;
        }
        .stats-label {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 6px;
            font-weight: 600;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-table td {
            padding: 12px 0;
            border-bottom: 1px solid #1e293b;
            font-size: 14px;
            vertical-align: middle;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-table td.label {
            color: #94a3b8;
            width: 38%;
            font-weight: 500;
        }
        .info-table td.value {
            color: #f1f5f9;
            font-weight: 600;
        }
        .code-tag {
            background: #0f172a;
            color: #64ffda;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            border: 1px solid #1e293b;
            font-weight: 700;
        }
        .status-badge {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: 1px solid rgba(239, 68, 68, 0.3);
            display: inline-block;
        }
        .tools-card {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .tool-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #1e293b;
        }
        .tool-row:last-child {
            border-bottom: none;
        }
        .tool-name {
            color: #f1f5f9;
            font-size: 14px;
            font-weight: 600;
        }
        .tool-qty {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        .cta-btn {
            display: block;
            text-align: center;
            background: #ef4444;
            color: #ffffff;
            text-decoration: none;
            padding: 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
            transition: all 0.2s ease;
            margin-bottom: 30px;
        }
        .footer {
            background: #030712;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #1e293b;
        }
        .footer p {
            color: #64748b;
            font-size: 12px;
            margin: 4px 0;
            line-height: 1.6;
        }
        .footer a {
            color: #64ffda;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div style="padding: 20px;">
    <div class="container">
        
        <!-- Header -->
        <div class="header">
            <span class="header-logo">🚨</span>
            <h1>NOTIFICACIÓN DE RETRASO CRÍTICO</h1>
            <p>CORTEX NOC · INCIDENCIA DE INVENTARIO</p>
        </div>

        <!-- Body -->
        <div class="body">
            
            <!-- Alerta Principal -->
            <div class="alert-card">
                <p>
                    Se ha detectado una violación en los tiempos de retorno de equipos. El trabajador 
                    <strong>{{ $vale->trabajador ? $vale->trabajador->nombre . ' ' . $vale->trabajador->apellidos : 'Desconocido' }}</strong> 
                    ha excedido el tiempo límite autorizado para la devolución de herramientas del vale 
                    <strong>{{ $vale->codigo_vale }}</strong>.
                </p>
            </div>

            <!-- Grid de estadísticas rápidas -->
            @php
                $diasRetraso = \Carbon\Carbon::parse($vale->fecha_limite)->diffInDays(\Carbon\Carbon::now());
                $totalPendiente = 0;
                foreach($vale->detalles as $det) {
                    $totalPendiente += ($det->cantidad_prestada - $det->cantidad_devuelta);
                }
            @endphp
            <div class="stats-grid">
                <div class="stats-col">
                    <div class="stats-number">{{ $diasRetraso }}</div>
                    <div class="stats-label">{{ $diasRetraso == 1 ? 'Día transcurrido' : 'Días transcurridos' }}</div>
                </div>
                <div class="stats-col">
                    <div class="stats-number">{{ $totalPendiente }}</div>
                    <div class="stats-label">Equipos retenidos</div>
                </div>
            </div>

            <!-- Ficha del trabajador -->
            <p class="section-title">Expediente del Personal</p>
            <table class="info-table">
                <tr>
                    <td class="label">Trabajador</td>
                    <td class="value">{{ $vale->trabajador ? $vale->trabajador->nombre . ' ' . $vale->trabajador->apellidos : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">DNI</td>
                    <td class="value">{{ $vale->trabajador ? $vale->trabajador->dni : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Cargo</td>
                    <td class="value">{{ $vale->trabajador ? ($vale->trabajador->cargo ?: 'Operario General') : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Teléfono</td>
                    <td class="value" style="color: #64ffda;">{{ $vale->trabajador ? ($vale->trabajador->telefono ?: '—') : '—' }}</td>
                </tr>
            </table>

            <!-- Ficha del vale -->
            <p class="section-title">Detalles del Préstamo</p>
            <table class="info-table">
                <tr>
                    <td class="label">Código de Control</td>
                    <td class="value"><span class="code-tag">{{ $vale->codigo_vale }}</span></td>
                </tr>
                <tr>
                    <td class="label">Fecha de Emisión</td>
                    <td class="value">{{ \Carbon\Carbon::parse($vale->fecha_creacion)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha de Retorno Límite</td>
                    <td class="value" style="color: #ef4444;">{{ \Carbon\Carbon::parse($vale->fecha_limite)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Estado de Alerta</td>
                    <td class="value"><span class="status-badge">CRÍTICO - RETRASADO</span></td>
                </tr>
            </table>

            <!-- Herramientas retenidas -->
            @if($vale->detalles->count() > 0)
            <p class="section-title">Detalle de Equipos Retenidos</p>
            <div class="tools-card">
                @foreach($vale->detalles as $detalle)
                @if($detalle->herramienta)
                <div class="tool-row">
                    <span class="tool-name">
                        {{ $detalle->herramienta->nombre }} 
                        <span style="color:#64748b; font-size:12px; margin-left: 6px;">({{ $detalle->herramienta->codigo }})</span>
                    </span>
                    <span class="tool-qty">
                        {{ $detalle->cantidad_prestada - $detalle->cantidad_devuelta }} und pendientes
                    </span>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            <!-- Botón de acción -->
            <a href="http://localhost/laravel_app/public/vales" class="cta-btn">Gestionar Retorno en Sistema</a>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este informe de incidencia ha sido generado por el núcleo de automatización de <strong>Cortex NOC</strong>.</p>
            <p>No responda a esta dirección de correo directamente.</p>
            <p style="margin-top: 15px; font-size: 11px; color: #475569;">© {{ date('Y') }} Almacén Inteligente. Nivel de seguridad activo 3.</p>
        </div>

    </div>
</div>
</body>
</html>
