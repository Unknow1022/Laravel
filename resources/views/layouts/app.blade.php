@php
    $valesRetrasadosCount = 0;
    $catalogoAlertsCount = 0;
    $trabajadoresAlertsCount = 0;
    $cortexAlertsCount = 0;
    $usuariosAlertsCount = 0;
    $almacenesAlertsCount = 0;

    if (Auth::check()) {
        try {
            $allSolved = session('cortex_all_solved');

            if (!$allSolved) {
                // 1. Vales de Salida & Registro de Vales
                $valesRetrasadosCount = \App\Models\Vale::where('estado', 'Activo')
                    ->where('fecha_limite', '<', \Carbon\Carbon::now())
                    ->count();

                // Trabajadores con moras
                $trabajadoresAlertsCount = \App\Models\Vale::where('estado', 'Activo')
                    ->where('fecha_limite', '<', \Carbon\Carbon::now())
                    ->distinct('trabajador_id')
                    ->count();

                // 2. Catálogo (Herramientas agotadas o en mantenimiento prolongado)
                $herramientasAgotadasCount = \App\Models\Herramienta::where('stock_disponible', '<=', 0)->count();
                $herramientasMantenimientoCriticoCount = \App\Models\Herramienta::where('estado', 'Mantenimiento')
                    ->where('creado_en', '<', \Carbon\Carbon::now()->subDays(15))
                    ->count();
                $catalogoAlertsCount = $herramientasAgotadasCount + $herramientasMantenimientoCriticoCount;

                // 3. Almacenes (Si hay herramientas agotadas o algún almacén sobrecargado > 80%)
                $totalTools = \App\Models\Herramienta::sum('stock_total') ?: 1;
                $almacenesSobrecargadosCount = \App\Models\Almacen::withCount('herramientas')
                    ->get()
                    ->filter(function($a) use ($totalTools) {
                        return (($a->herramientas_count / $totalTools) * 100) > 80;
                    })->count();
                $almacenesAlertsCount = $almacenesSobrecargadosCount + $herramientasAgotadasCount;

                // 4. Cortex Assistant (incidentes de seguridad + diagnósticos fallidos)
                $securityService = new \App\Services\CortexSecurityService();
                $securityIncidents = $securityService->performSecurityScan();
                $securityIncidentsCount = count($securityIncidents);

                $validator = new \App\Services\SystemValidatorService();
                $diagnostic = $validator->runFullDiagnostic();
                $diagnosticFailsCount = collect($diagnostic)->where('status', 'FAIL')->count();

                $cortexAlertsCount = $securityIncidentsCount + $diagnosticFailsCount;

                // 5. Gestión de Usuarios
                $newAdmins = \App\Models\Usuario::where('rol', 'Administrador')
                    ->where('creado_en', '>=', \Carbon\Carbon::now()->subHours(24))
                    ->count();
                if ($newAdmins > 1 && !in_array('new_admins', session()->get('cortex_dismissed_incidents', []))) {
                    $usuariosAlertsCount = $newAdmins;
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Almacén Inteligente')</title>
    <!-- Vendor CSS (Offline) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/table.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">
    
    <style>
        /* Ajustes indestructibles para el header en móviles */
        @media (max-width: 768px) {
            /* Prevenir desbordamiento horizontal en TODO el sitio */
            html, body {
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
            .main-content {
                overflow-x: hidden !important;
                width: 100% !important;
            }
            .page-content > * {
                max-width: 100% !important;
                word-break: break-word !important;
            }
            .wrapper {
                display: flex !important;
                position: relative !important;
                width: 100% !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }

            .cortex-container,
            .cortex-card-premium,
            .glass-container,
            .card,
            [class*="col-"] {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }


            .premium-header {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                height: 60px !important;
                padding: 0 1rem !important;
                background: #FFFFFF !important;
                border-bottom: 1px solid #E2E8F0 !important;
            }
            .header-left {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            .header-right {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            .user-profile-dropdown {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            .hamburger-btn {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 38px !important;
                height: 38px !important;
                background: var(--primary-light) !important;
                border: 1px solid rgba(13, 148, 136, 0.2) !important;
                color: var(--primary-color) !important;
                border-radius: 8px !important;
            }
            .user-info-chip {
                padding: 4px 8px !important;
                gap: 6px !important;
                background: var(--cortex-surface) !important;
                border-radius: 50px !important;
                border: 1px solid var(--border-color) !important;
            }
            .user-avatar {
                width: 34px !important;
                height: 34px !important;
                font-size: 0.95rem !important;
                background: var(--primary-color) !important;
                color: #FFFFFF !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .logout-btn {
                width: 34px !important;
                height: 34px !important;
                border-radius: 8px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: var(--danger-light) !important;
                color: var(--danger) !important;
                border: 1px solid rgba(220, 38, 38, 0.2) !important;
            }
            .user-meta, #live-clock {
                display: none !important;
            }
        }

        .badge-cortex-danger {
            background: linear-gradient(135deg, #F43F5E, #E11D48);
            color: white !important;
            border: 1px solid rgba(244, 63, 94, 0.4);
            box-shadow: 0 0 10px rgba(244, 63, 94, 0.5);
            border-radius: 50px;
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            min-width: 20px;
            text-align: center;
            line-height: 1;
            display: inline-block;
            animation: pulse-danger-badge 2s infinite;
        }
        @keyframes pulse-danger-badge {
            0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.6); }
            70% { box-shadow: 0 0 0 8px rgba(244, 63, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
        }
        .nav-link {
            position: relative;
        }
    </style>

    <!-- Vendor JS (Offline) -->
    <script src="{{ asset('vendor/jquery/jquery.slim.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/jsbarcode/JsBarcode.all.min.js') }}"></script>
</head>
<body>
<!-- Overlay para cerrar sidebar en móvil -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="main-sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-boxes-stacked"></i> Almacén
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie" style="width: 20px;"></i> Dashboard
            </a>
            <a href="{{ route('cortex.index') }}" class="nav-link {{ request()->routeIs('cortex.index') ? 'active' : '' }}">
                <i class="fa-solid fa-brain-circuit" style="width: 20px;"></i>
                <span>Cortex Assistant</span>
                @if($cortexAlertsCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $cortexAlertsCount }} incidentes/fallos">{{ $cortexAlertsCount }}</span>
                @endif
            </a>
            <a href="{{ route('herramientas.index') }}" class="nav-link {{ request()->is('herramientas') || request()->is('herramientas/*') && !request()->is('herramientas/ubicaciones') ? 'active' : '' }}">
                <i class="fa-solid fa-wrench" style="width: 20px;"></i>
                <span>Catálogo</span>
                @if($catalogoAlertsCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $catalogoAlertsCount }} alertas de catálogo">{{ $catalogoAlertsCount }}</span>
                @endif
            </a>
            <a href="{{ route('herramientas.ubicaciones') }}" class="nav-link {{ request()->is('herramientas/ubicaciones') ? 'active' : '' }}">
                <i class="fa-solid fa-warehouse" style="width: 20px;"></i>
                <span>Ubicaciones</span>
            </a>
            <a href="{{ route('almacenes.index') }}" class="nav-link {{ request()->is('almacenes*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked" style="width: 20px;"></i>
                <span>Almacenes</span>
                @if($almacenesAlertsCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $almacenesAlertsCount }} alertas en almacenes">{{ $almacenesAlertsCount }}</span>
                @endif
            </a>
            <a href="{{ route('trabajadores.index') }}" class="nav-link {{ request()->is('trabajadores*') && !request()->is('*inactivos') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear" style="width: 20px;"></i>
                <span>Trabajadores</span>
                @if($trabajadoresAlertsCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $trabajadoresAlertsCount }} trabajadores con moras">{{ $trabajadoresAlertsCount }}</span>
                @endif
            </a>

            @if(Auth::user()->rol !== 'Supervisor')
            <a href="{{ route('vales.index') }}" class="nav-link {{ request()->is('vales') || request()->is('vales/*/edit') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice" style="width: 20px;"></i>
                <span>Vales de Salida</span>
                @if($valesRetrasadosCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $valesRetrasadosCount }} vales retrasados">{{ $valesRetrasadosCount }}</span>
                @endif
            </a>
            <a href="{{ url('vales/historial') }}" class="nav-link {{ request()->is('vales/historial') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open" style="width: 20px;"></i>
                <span>Registro de Vales</span>
                @if($valesRetrasadosCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $valesRetrasadosCount }} vales retrasados">{{ $valesRetrasadosCount }}</span>
                @endif
            </a>
            @else
            <!-- El Supervisor puede ver reportes (acceso de solo lectura) -->
            <a href="{{ route('vales.index') }}" class="nav-link {{ request()->is('vales') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice" style="width: 20px;"></i>
                <span>Vales de Salida</span>
                @if($valesRetrasadosCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $valesRetrasadosCount }} vales retrasados">{{ $valesRetrasadosCount }}</span>
                @endif
            </a>
            <a href="{{ url('vales/historial') }}" class="nav-link {{ request()->is('vales/historial') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open" style="width: 20px;"></i>
                <span>Registro de Vales</span>
                @if($valesRetrasadosCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $valesRetrasadosCount }} vales retrasados">{{ $valesRetrasadosCount }}</span>
                @endif
            </a>
            @endif

            <a href="{{ route('incidencias.index') }}" class="nav-link {{ request()->is('incidencias*') ? 'active' : '' }}">
                <i class="fa-solid fa-triangle-exclamation" style="width: 20px;"></i>
                <span>Incidencias y Sanciones</span>
            </a>

            @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor']))
            <div class="nav-divider" style="border-top: 1px solid var(--border-color); margin: 0.5rem 1rem;"></div>
            <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->is('reportes*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-chart-column" style="width: 20px;"></i>
                <span>Reportes</span>
            </a>
            @endif

            @if(Auth::user()->rol === 'Administrador')
            <a href="{{ url('usuarios') }}" class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-cog" style="width: 20px;"></i>
                <span>Gestión de Usuarios</span>
                @if($usuariosAlertsCount > 0)
                    <span class="badge-cortex-danger ml-auto" title="{{ $usuariosAlertsCount }} alertas">{{ $usuariosAlertsCount }}</span>
                @endif
            </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content wrapper -->
    <div class="main-content">
        <!-- Header Principal -->
        <header class="main-header premium-header">
            <div class="header-left">
                <!-- Botón hamburger (visible en móvil) -->
                <button class="hamburger-btn" id="hamburger-btn" onclick="toggleSidebar()" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars" id="hamburger-icon"></i>
                </button>
                <div class="system-time" id="live-clock-container">
                    <i class="fa-regular fa-calendar-days" style="color: #0284C7;"></i>
                    <span id="live-clock"></span>
                </div>
            </div>
            <div class="header-right">
                <div class="user-profile-dropdown">
                    @php
                    $rolIcon = [
                        'Administrador' => 'fa-user-shield',
                        'Almacenero'    => 'fa-user-gear',
                        'Supervisor'    => 'fa-user-tie',
                    ];
                    $icon = $rolIcon[Auth::user()->rol] ?? 'fa-user';
                    @endphp
                    <div class="user-info-chip">
                        <div class="user-avatar">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div class="user-meta">
                            <span class="user-name">{{ Auth::user()->nombre }}</span>
                            <span class="user-role">{{ Auth::user()->rol }}</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST" class="logout-form" onsubmit="return confirm('¿Finalizar sesión segura?');">
                        @csrf
                        <button type="submit" class="logout-btn" title="Desconexión Segura">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <script>
            function updateClock() {
                const now = new Date();
                document.getElementById('live-clock').innerText = now.toLocaleString('es-ES', { 
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        </script>


        <!-- Page Content -->
        <main class="page-content">
            @yield('content')
        </main> <!-- End Page Content -->
    </div> <!-- /main-content -->
</div> <!-- /wrapper -->

@if(Auth::user()->rol === 'Administrador')
<!-- ============================================================
     CORTEX RESCUE WIDGET - Acceso flotante para administradores
     Visible en todas las páginas autenticadas
     ============================================================ -->
<div id="cortex-rescue-widget" aria-label="Acceso rápido a Cortex Rescue">

    <!-- Botón flotante principal -->
    <button id="rescue-fab" onclick="toggleRescueMenu()" title="Cortex: Sistema de Rescate">
        <i class="fa-solid fa-shield-heart" id="rescue-fab-icon"></i>
        <span class="rescue-fab-pulse"></span>
    </button>

    <!-- Panel expandido -->
    <div id="rescue-menu" class="rescue-menu" aria-hidden="true">
        <div class="rescue-menu-header">
            <div class="rescue-menu-logo">
                <i class="fa-solid fa-shield-heart"></i>
                <div>
                    <strong>CORTEX</strong>
                    <small>Sistema de Rescate</small>
                </div>
            </div>
            <button class="rescue-close" onclick="toggleRescueMenu()" title="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="rescue-menu-body">
            <a href="{{ route('cortex.rescue') }}" class="rescue-action-btn rescue-primary">
                <i class="fa-solid fa-bolt"></i>
                <div>
                    <span>Abrir Consola de Rescate</span>
                    <small>Diagnóstico y autoreparación del sistema</small>
                </div>
            </a>

            <a href="{{ route('cortex.index') }}" class="rescue-action-btn">
                <i class="fa-solid fa-brain-circuit"></i>
                <div>
                    <span>Cortex Dashboard</span>
                    <small>Monitor de salud del sistema</small>
                </div>
            </a>

            <a href="{{ route('cortex.scan') }}" class="rescue-action-btn">
                <i class="fa-solid fa-radar"></i>
                <div>
                    <span>Ejecutar Escaneo Rápido</span>
                    <small>Analizar estado actual</small>
                </div>
            </a>
        </div>

        <div class="rescue-menu-footer">
            <i class="fa-solid fa-circle-dot" style="color: #10b981; font-size: 0.6rem;"></i>
            Sistema activo &middot; Solo accesible a Administradores
        </div>
    </div>
</div>

<style>
    /* ---- Cortex Rescue Widget ---- */
    #cortex-rescue-widget {
        position: fixed;
        bottom: 1.8rem;
        right: 1.8rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.8rem;
        pointer-events: none; /* Deja pasar clicks a través del área vacía */
    }

    #rescue-fab {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f2027, #1a3a4a);
        border: 2px solid rgba(100, 255, 218, 0.5);
        color: #64ffda;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4), 0 0 15px rgba(100,255,218,0.15);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: visible;
        pointer-events: auto !important; /* Habilita clicks solo en el botón */
    }

    #rescue-fab:hover {
        transform: scale(1.1);
        border-color: #64ffda;
        box-shadow: 0 6px 25px rgba(0,0,0,0.5), 0 0 25px rgba(100,255,218,0.3);
    }

    #rescue-fab.is-open {
        background: linear-gradient(135deg, #64ffda 0%, #10b981 100%);
        color: #060b13;
        border-color: transparent;
        transform: rotate(0deg) scale(1.05);
    }

    .rescue-fab-pulse {
        position: absolute;
        top: -3px;
        right: -3px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #f43f5e;
        border: 2px solid #060b13;
        animation: fab-blink 2s infinite;
    }

    .rescue-menu {
        background: linear-gradient(145deg, #0d1b2a, #112240);
        border: 1px solid rgba(100, 255, 218, 0.2);
        border-radius: 16px;
        width: 290px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.6), 0 0 30px rgba(100,255,218,0.05);
        overflow: hidden;
        transform-origin: bottom right;
        transform: scale(0.8) translateY(10px);
        opacity: 0;
        pointer-events: none;
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        backdrop-filter: blur(10px);
    }

    .rescue-menu.is-visible {
        transform: scale(1) translateY(0);
        opacity: 1;
        pointer-events: auto !important;
    }

    .rescue-menu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.2rem 0.8rem;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    .rescue-menu-logo {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: #64ffda;
    }

    .rescue-menu-logo i { font-size: 1.2rem; }

    .rescue-menu-logo strong {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #fff;
        font-family: 'JetBrains Mono', monospace, sans-serif;
    }

    .rescue-menu-logo small {
        display: block;
        font-size: 0.65rem;
        color: #8892b0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .rescue-close {
        background: none;
        border: none;
        color: #8892b0;
        cursor: pointer;
        font-size: 1rem;
        padding: 0.2rem;
        transition: color 0.2s;
        line-height: 1;
    }
    .rescue-close:hover { color: #f43f5e; }

    .rescue-menu-body {
        padding: 0.8rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .rescue-action-btn {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.7rem 0.8rem;
        border-radius: 10px;
        text-decoration: none;
        color: #ccd6f6;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.05);
        transition: all 0.2s;
        font-size: 0.82rem;
    }

    .rescue-action-btn:hover {
        background: rgba(100, 255, 218, 0.08);
        border-color: rgba(100, 255, 218, 0.2);
        color: #fff;
        text-decoration: none;
        transform: translateX(-2px);
    }

    .rescue-action-btn.rescue-primary {
        background: rgba(100, 255, 218, 0.08);
        border-color: rgba(100, 255, 218, 0.25);
        color: #64ffda;
    }

    .rescue-action-btn.rescue-primary:hover {
        background: rgba(100, 255, 218, 0.15);
        border-color: rgba(100, 255, 218, 0.4);
        color: #64ffda;
        box-shadow: 0 0 15px rgba(100,255,218,0.1);
    }

    .rescue-action-btn i {
        font-size: 1.1rem;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
    }

    .rescue-action-btn div span { display: block; font-weight: 600; }
    .rescue-action-btn div small { display: block; font-size: 0.7rem; color: #8892b0; margin-top: 1px; }

    .rescue-menu-footer {
        padding: 0.6rem 1.2rem;
        border-top: 1px solid rgba(255,255,255,0.05);
        font-size: 0.65rem;
        color: #8892b0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-family: monospace;
    }

    @keyframes fab-blink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }
</style>

<script>
    function toggleRescueMenu() {
        const menu = document.getElementById('rescue-menu');
        const fab = document.getElementById('rescue-fab');
        const icon = document.getElementById('rescue-fab-icon');

        const isOpen = menu.classList.contains('is-visible');

        if (isOpen) {
            menu.classList.remove('is-visible');
            menu.setAttribute('aria-hidden', 'true');
            fab.classList.remove('is-open');
            icon.className = 'fa-solid fa-shield-heart';
        } else {
            menu.classList.add('is-visible');
            menu.setAttribute('aria-hidden', 'false');
            fab.classList.add('is-open');
            icon.className = 'fa-solid fa-xmark';
        }
    }

    // Cerrar al hacer click fuera
    document.addEventListener('click', function(e) {
        const widget = document.getElementById('cortex-rescue-widget');
        if (widget && !widget.contains(e.target)) {
            const menu = document.getElementById('rescue-menu');
            const fab = document.getElementById('rescue-fab');
            const icon = document.getElementById('rescue-fab-icon');
            if (menu && menu.classList.contains('is-visible')) {
                menu.classList.remove('is-visible');
                fab.classList.remove('is-open');
                icon.className = 'fa-solid fa-shield-heart';
            }
        }
    });
</script>
@endif

<script>
// Sistema de alertas global con SweetAlert2
document.addEventListener('DOMContentLoaded', function() {
    @if(session('exito') || request()->has('exito'))
        Swal.fire({ icon: 'success', title: '¡Éxito!', text: 'Operación realizada correctamente.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    @endif
    @if(session('eliminado') || request()->has('eliminado'))
        Swal.fire({ icon: 'warning', title: 'Eliminado', text: 'El registro ha sido eliminado (borrado lógico).', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    @endif
    @if(session('modificado') || request()->has('modificado'))
        Swal.fire({ icon: 'success', title: 'Actualizado', text: 'Los datos se han actualizado correctamente.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    @endif
    @if(session('error') || request()->has('error'))
        Swal.fire({ icon: 'error', title: 'Error', text: 'Hubo un problema al procesar la solicitud.', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true });
    @endif
});
</script>

<script src="{{ asset('js/main.js') }}?v={{ time() }}"></script>
</body>
</html>
