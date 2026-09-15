<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Stock — CORTEX NOC</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body {
            margin: 0; padding: 0;
            background-color: #030712;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 620px; margin: 40px auto;
            background-color: #0b1329;
            border: 1px solid #1e293b;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
        }
        .header { padding: 35px 40px; text-align: center; }
        .header.agotada {
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 50%, #7f1d1d 100%);
            border-bottom: 2px solid #b91c1c;
        }
        .header.critica {
            background: linear-gradient(135deg, #78350f 0%, #92400e 50%, #78350f 100%);
            border-bottom: 2px solid #d97706;
        }
        .header-logo { font-size: 3rem; margin-bottom: 10px; display: block; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: 2px; text-transform: uppercase; }
        .header p { font-size: 11px; margin: 6px 0 0 0; letter-spacing: 3px; text-transform: uppercase; font-weight: 600; }
        .header.agotada p { color: #fca5a5; }
        .header.critica p { color: #fde68a; }
        .body { padding: 36px 40px; }
        .alert-card { border-radius: 12px; padding: 18px 20px; margin-bottom: 28px; }
        .alert-card.agotada { background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.22); border-left: 5px solid #ef4444; }
        .alert-card.critica { background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.22); border-left: 5px solid #f59e0b; }
        .alert-card p { margin: 0; font-size: 14px; line-height: 1.65; font-weight: 500; }
        .alert-card.agotada p { color: #f87171; }
        .alert-card.critica p { color: #fcd34d; }
        .section-title { color: #64ffda; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin: 0 0 14px 0; padding-bottom: 8px; border-bottom: 1px solid #1e293b; }
        .stats-grid { display: table; width: 100%; margin-bottom: 28px; background: #0f172a; border-radius: 12px; border: 1px solid #1e293b; }
        .stats-col { display: table-cell; width: 33.3%; padding: 18px 12px; text-align: center; vertical-align: middle; }
        .stats-col + .stats-col { border-left: 1px solid #1e293b; }
        .stats-number { font-size: 34px; font-weight: 900; line-height: 1; }
        .stats-number.red   { color: #ef4444; }
        .stats-number.amber { color: #f59e0b; }
        .stats-number.cyan  { color: #64ffda; }
        .stats-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 5px; font-weight: 600; }
        .herramienta-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 20px; margin-bottom: 28px; }
        .h-nombre { font-size: 17px; font-weight: 800; color: #e6f1ff; margin-bottom: 4px; }
        .h-codigo { font-family: monospace; font-size: 12px; color: #64ffda; background: rgba(100,255,218,0.08); border: 1px solid rgba(100,255,218,0.2); padding: 2px 8px; border-radius: 5px; display: inline-block; }
        .progress-bar-wrap { background: #1e293b; border-radius: 6px; height: 8px; margin: 14px 0 6px 0; overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 6px; }
        .progress-bar-fill.danger { background: linear-gradient(90deg, #991b1b, #ef4444); }
        .progress-bar-fill.warning { background: linear-gradient(90deg, #92400e, #f59e0b); }
        .progress-label { font-size: 11px; color: #64748b; display: flex; justify-content: space-between; margin-top: 2px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .info-table td { padding: 11px 0; border-bottom: 1px solid #1e293b; font-size: 13px; vertical-align: middle; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-table td.label { color: #94a3b8; width: 40%; font-weight: 500; }
        .info-table td.value { color: #f1f5f9; font-weight: 600; }
        .badge { padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; display: inline-block; }
        .badge-red   { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-amber { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-cyan  { background: rgba(100,255,218,0.1); color: #64ffda; border: 1px solid rgba(100,255,218,0.25); }
        .workers-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 20px; margin-bottom: 28px; }
        .worker-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid #1e293b; }
        .worker-row:last-child { border-bottom: none; }
        .worker-info { flex: 1; }
        .worker-name { color: #f1f5f9; font-size: 14px; font-weight: 700; }
        .worker-sub  { color: #64748b; font-size: 11px; margin-top: 3px; line-height: 1.5; }
        .worker-qty  { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; white-space: nowrap; margin-left: 12px; }
        .cta-btn { display: block; text-align: center; color: #ffffff; text-decoration: none; padding: 16px; border-radius: 10px; font-weight: 700; font-size: 14px; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 10px; }
        .cta-btn.red   { background: #dc2626; box-shadow: 0 4px 15px rgba(220,38,38,0.35); }
        .cta-btn.amber { background: #d97706; box-shadow: 0 4px 15px rgba(217,119,6,0.35); }
        .footer { background: #030712; padding: 28px 40px; text-align: center; border-top: 1px solid #1e293b; }
        .footer p { color: #64748b; font-size: 12px; margin: 4px 0; line-height: 1.6; }
        .footer a { color: #64ffda; text-decoration: none; font-weight: 600; }
        .code-tag { background: #0f172a; color: #64ffda; padding: 3px 10px; border-radius: 6px; font-family: monospace; font-size: 13px; border: 1px solid #1e293b; font-weight: 700; }
        .no-workers { color: #64748b; font-size: 13px; font-style: italic; text-align: center; padding: 16px 0; }
    </style>
</head>
<body>
<div style="padding: 20px;">
<div class="container">

    {{-- HEADER --}}
    <div class="header {{ $tipoAlerta }}">
        <span class="header-logo">{{ $tipoAlerta === 'agotada' ? '🔴' : '🟡' }}</span>
        <h1>{{ $tipoAlerta === 'agotada' ? 'HERRAMIENTA AGOTADA' : 'STOCK CRÍTICO DETECTADO' }}</h1>
        <p>CORTEX NOC &middot; ALERTA DE INVENTARIO &middot; {{ strtoupper(now()->format('d/m/Y H:i')) }}</p>
    </div>

    {{-- BODY --}}
    <div class="body">

        {{-- Alerta principal --}}
        <div class="alert-card {{ $tipoAlerta }}">
            <p>
                @if($tipoAlerta === 'agotada')
                    Cortex NOC detectó que la herramienta
                    <strong>{{ $herramienta->nombre }}</strong>
                    ha alcanzado <strong>stock = 0</strong>. No hay unidades disponibles para nuevos préstamos.
                @else
                    La herramienta <strong>{{ $herramienta->nombre }}</strong>
                    tiene solo <strong>{{ $herramienta->stock_disponible }} unidad(es)</strong>
                    disponible(s), igual o por debajo del mínimo configurado ({{ $herramienta->stock_minimo }}).
                @endif
                Se requiere atención inmediata.
            </p>
        </div>

        {{-- Grid de métricas --}}
        @php
            $pct       = $herramienta->stock_total > 0 ? round(($herramienta->stock_disponible / $herramienta->stock_total) * 100) : 0;
            $enPrestamo = $herramienta->stock_total - $herramienta->stock_disponible;
        @endphp
        <div class="stats-grid">
            <div class="stats-col">
                <div class="stats-number red">{{ $herramienta->stock_disponible }}</div>
                <div class="stats-label">Disponibles</div>
            </div>
            <div class="stats-col">
                <div class="stats-number amber">{{ $enPrestamo }}</div>
                <div class="stats-label">En Préstamo</div>
            </div>
            <div class="stats-col">
                <div class="stats-number cyan">{{ $herramienta->stock_total }}</div>
                <div class="stats-label">Stock Total</div>
            </div>
        </div>

        {{-- Ficha de herramienta --}}
        <p class="section-title">Ficha de la Herramienta</p>
        <div class="herramienta-card">
            <div class="h-nombre">{{ $herramienta->nombre }}</div>
            <div style="margin-top: 6px;">
                <span class="h-codigo">{{ $herramienta->codigo }}</span>
                &nbsp;
                <span class="badge {{ $tipoAlerta === 'agotada' ? 'badge-red' : 'badge-amber' }}">
                    {{ $tipoAlerta === 'agotada' ? '⛔ AGOTADA' : '⚠️ STOCK CRÍTICO' }}
                </span>
            </div>
            {{-- Barra de stock --}}
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill {{ $tipoAlerta === 'agotada' ? 'danger' : 'warning' }}"
                     style="width: {{ $pct }}%;"></div>
            </div>
            <div class="progress-label">
                <span>{{ $herramienta->stock_disponible }} disp.</span>
                <span>{{ $pct }}% disponible</span>
                <span>{{ $herramienta->stock_total }} total</span>
            </div>
        </div>

        {{-- Detalles --}}
        <p class="section-title">Detalles del Inventario</p>
        <table class="info-table">
            <tr>
                <td class="label">Código</td>
                <td class="value"><span class="code-tag">{{ $herramienta->codigo }}</span></td>
            </tr>
            <tr>
                <td class="label">Estado</td>
                <td class="value">{{ $herramienta->estado ?? '—' }}</td>
            </tr>
            @if($herramienta->modelAlmacen)
            <tr>
                <td class="label">Almacén</td>
                <td class="value">{{ $herramienta->modelAlmacen->nombre ?? '—' }}</td>
            </tr>
            @endif
            @if($herramienta->ubicacion)
            <tr>
                <td class="label">Ubicación</td>
                <td class="value">{{ $herramienta->ubicacion }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Stock Mínimo Configurado</td>
                <td class="value" style="color: #f59e0b;">{{ $herramienta->stock_minimo }} unidades</td>
            </tr>
            <tr>
                <td class="label">Nivel de Alerta</td>
                <td class="value">
                    <span class="badge {{ $tipoAlerta === 'agotada' ? 'badge-red' : 'badge-amber' }}">
                        {{ $tipoAlerta === 'agotada' ? 'CRÍTICO — AGOTADO' : 'ADVERTENCIA — BAJO' }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- Trabajadores con unidades en préstamo --}}
        <p class="section-title">Trabajadores con Unidades en Préstamo Activo</p>
        @if(count($trabajadores) > 0)
        <div class="workers-card">
            @foreach($trabajadores as $t)
            <div class="worker-row">
                <div class="worker-info">
                    <div class="worker-name">{{ $t['nombre'] }}</div>
                    <div class="worker-sub">
                        DNI: {{ $t['dni'] ?? '—' }}
                        @if(!empty($t['cargo'])) &nbsp;&middot;&nbsp; {{ $t['cargo'] }} @endif
                        @if(!empty($t['telefono'])) &nbsp;&middot;&nbsp; &#128222; {{ $t['telefono'] }} @endif
                        <br>
                        Vale: <span style="color: #64ffda; font-weight: 700;">{{ $t['vale_codigo'] }}</span>
                        &nbsp;&middot;&nbsp; Devuelve: <span style="color: #f59e0b;">{{ $t['fecha_limite'] }}</span>
                    </div>
                </div>
                <div class="worker-qty">{{ $t['cantidad'] }} prestada(s)</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="workers-card">
            <p class="no-workers">No hay vales activos con esta herramienta en préstamo actualmente.</p>
        </div>
        @endif

        {{-- Botón CTA --}}
        <a href="https://almacen-inteligente.infinityfreeapp.com/public/catalogo"
           class="cta-btn {{ $tipoAlerta === 'agotada' ? 'red' : 'amber' }}">
           &#128230; Ver Inventario en el Sistema
        </a>

    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Informe generado automáticamente por el núcleo de automatización de <strong>Cortex NOC</strong>.</p>
        <p>Sistema de Alertas Inteligente — Nivel 3 activo. No responda a este correo.</p>
        <p style="margin-top: 14px; font-size: 11px; color: #475569;">
            &copy; {{ date('Y') }} Almacén Inteligente.
        </p>
    </div>

</div>
</div>
</body>
</html>
