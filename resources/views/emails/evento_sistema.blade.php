<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evento del Sistema — CORTEX NOC</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body { margin:0; padding:0; background:#030712; font-family:'Outfit',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; -webkit-font-smoothing:antialiased; }
        .wrap { padding: 20px; }
        .container { max-width:600px; margin:0 auto; background:#0b1329; border:1px solid #1e293b; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.6); }

        /* HEADER — color cambia según tipo */
        .header { padding:30px 40px; text-align:center; }
        .header-creacion  { background:linear-gradient(135deg,#064e3b,#065f46); border-bottom:2px solid #10b981; }
        .header-edicion   { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); border-bottom:2px solid #3b82f6; }
        .header-eliminacion{ background:linear-gradient(135deg,#7f1d1d,#991b1b); border-bottom:2px solid #ef4444; }
        .header-login     { background:linear-gradient(135deg,#1e1b4b,#312e81); border-bottom:2px solid #818cf8; }
        .header-default   { background:linear-gradient(135deg,#0c1a2e,#1e293b); border-bottom:2px solid #64ffda; }
        .header-logo { font-size:2.5rem; display:block; margin-bottom:10px; }
        .header h1 { color:#fff; font-size:18px; font-weight:800; margin:0; letter-spacing:2px; text-transform:uppercase; }
        .header p  { font-size:11px; margin:6px 0 0; letter-spacing:3px; text-transform:uppercase; font-weight:600; color:rgba(255,255,255,0.7); }

        /* BODY */
        .body { padding:32px 40px; }

        /* Alert card */
        .alert-card { border-radius:12px; padding:18px 20px; margin-bottom:24px; border-left-width:5px; border-left-style:solid; }
        .alert-card p { margin:0; font-size:14px; line-height:1.7; font-weight:500; }
        .ac-creacion   { background:rgba(16,185,129,0.06); border-color:rgba(16,185,129,0.2); border-left-color:#10b981; }
        .ac-creacion p { color:#6ee7b7; }
        .ac-edicion    { background:rgba(59,130,246,0.06); border-color:rgba(59,130,246,0.2); border-left-color:#3b82f6; }
        .ac-edicion p  { color:#93c5fd; }
        .ac-eliminacion{ background:rgba(239,68,68,0.06); border-color:rgba(239,68,68,0.2); border-left-color:#ef4444; }
        .ac-eliminacion p { color:#fca5a5; }
        .ac-login      { background:rgba(129,140,248,0.06); border-color:rgba(129,140,248,0.2); border-left-color:#818cf8; }
        .ac-login p    { color:#c7d2fe; }
        .ac-default    { background:rgba(100,255,218,0.04); border-color:rgba(100,255,218,0.15); border-left-color:#64ffda; }
        .ac-default p  { color:#a7f3d0; }

        /* Info table */
        .section-title { color:#64ffda; font-size:11px; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; margin:0 0 14px; padding-bottom:8px; border-bottom:1px solid #1e293b; }
        .info-table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        .info-table td { padding:11px 0; border-bottom:1px solid #1e293b; font-size:13px; vertical-align:middle; }
        .info-table tr:last-child td { border-bottom:none; }
        .info-table td.label { color:#94a3b8; width:38%; font-weight:500; }
        .info-table td.value { color:#f1f5f9; font-weight:600; }
        .badge { padding:3px 10px; border-radius:6px; font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; display:inline-block; }
        .badge-green  { background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }
        .badge-blue   { background:rgba(59,130,246,0.15); color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .badge-red    { background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); }
        .badge-purple { background:rgba(129,140,248,0.15); color:#c7d2fe; border:1px solid rgba(129,140,248,0.3); }
        .badge-cyan   { background:rgba(100,255,218,0.1); color:#64ffda; border:1px solid rgba(100,255,218,0.25); }
        .code-tag { background:#0f172a; color:#64ffda; padding:3px 10px; border-radius:6px; font-family:monospace; font-size:13px; border:1px solid #1e293b; font-weight:700; }

        /* Descripción grande */
        .desc-box { background:#0f172a; border:1px solid #1e293b; border-radius:12px; padding:20px; margin-bottom:24px; }
        .desc-box p { color:#e6f1ff; font-size:14px; line-height:1.7; margin:0; }

        /* Footer */
        .footer { background:#030712; padding:26px 40px; text-align:center; border-top:1px solid #1e293b; }
        .footer p { color:#64748b; font-size:12px; margin:4px 0; line-height:1.6; }
    </style>
</head>
<body>
<div class="wrap">
<div class="container">

@php
    $aLower = strtolower($accion);
    $esEliminacion = str_contains($aLower,'elimin') || str_contains($aLower,'borr');
    $esCreacion    = str_contains($aLower,'cre') || str_contains($aLower,'regist') || str_contains($aLower,'nuevo') || str_contains($aLower,'agre');
    $esEdicion     = str_contains($aLower,'actu') || str_contains($aLower,'edit') || str_contains($aLower,'modif');
    $esLogin       = str_contains($aLower,'login') || str_contains($aLower,'logout') || str_contains($aLower,'acces') || str_contains($aLower,'sesi');

    if ($esEliminacion) { $tipo='eliminacion'; $emoji='🗑️'; $badgeClass='badge-red';    $badgeLabel='ELIMINACIÓN'; }
    elseif ($esLogin)   { $tipo='login';       $emoji='🔐'; $badgeClass='badge-purple'; $badgeLabel='ACCESO'; }
    elseif ($esEdicion) { $tipo='edicion';     $emoji='✏️'; $badgeClass='badge-blue';   $badgeLabel='MODIFICACIÓN'; }
    elseif ($esCreacion){ $tipo='creacion';    $emoji='✅'; $badgeClass='badge-green';  $badgeLabel='CREACIÓN'; }
    else                { $tipo='default';     $emoji='🔔'; $badgeClass='badge-cyan';   $badgeLabel='EVENTO'; }

    $tablaDisplay = ucwords(str_replace('_', ' ', $tabla));
@endphp

    {{-- HEADER --}}
    <div class="header header-{{ $tipo }}">
        <span class="header-logo">{{ $emoji }}</span>
        <h1>{{ $tablaDisplay }} — {{ strtoupper($accion) }}</h1>
        <p>CORTEX NOC &middot; Auditoría del Sistema &middot; {{ $fecha ?? now()->format('d/m/Y H:i:s') }}</p>
    </div>

    {{-- BODY --}}
    <div class="body">

        {{-- Alerta --}}
        <div class="alert-card ac-{{ $tipo }}">
            <p>
                @if($esEliminacion)
                    ⚠️ Se ha registrado una <strong>eliminación permanente</strong> en el módulo de <strong>{{ $tablaDisplay }}</strong>.
                @elseif($esCreacion)
                    Se ha registrado un <strong>nuevo registro</strong> en el módulo de <strong>{{ $tablaDisplay }}</strong>.
                @elseif($esEdicion)
                    Se ha realizado una <strong>modificación</strong> en el módulo de <strong>{{ $tablaDisplay }}</strong>.
                @elseif($esLogin)
                    Se ha registrado un <strong>evento de acceso</strong> al sistema.
                @else
                    Se ha registrado un evento en el módulo de <strong>{{ $tablaDisplay }}</strong>.
                @endif
            </p>
        </div>

        {{-- Detalles del evento --}}
        <p class="section-title">Detalles del Evento</p>
        <table class="info-table">
            <tr>
                <td class="label">Módulo afectado</td>
                <td class="value"><span class="code-tag">{{ $tablaDisplay }}</span></td>
            </tr>
            <tr>
                <td class="label">Acción realizada</td>
                <td class="value">{{ $accion }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de evento</td>
                <td class="value"><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
            </tr>
            @if($itemId)
            <tr>
                <td class="label">ID del registro</td>
                <td class="value"><span class="code-tag">#{{ $itemId }}</span></td>
            </tr>
            @endif
            <tr>
                <td class="label">Usuario responsable</td>
                <td class="value" style="color:#64ffda;">{{ $usuario ?? 'Sistema Automático' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha y hora</td>
                <td class="value">{{ $fecha ?? now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>

        {{-- Descripción completa --}}
        @if($descripcion)
        <p class="section-title">Descripción Completa</p>
        <div class="desc-box">
            <p>{{ $descripcion }}</p>
        </div>
        @endif

    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Notificación generada automáticamente por <strong>Cortex NOC</strong> — Sistema de Auditoría.</p>
        <p>Todas las acciones del sistema son monitoreadas y registradas en tiempo real.</p>
        <p style="margin-top:12px;font-size:11px;color:#475569;">&copy; {{ date('Y') }} Almacén Inteligente. No responda a este correo.</p>
    </div>

</div>
</div>
</body>
</html>
