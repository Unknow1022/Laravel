@extends('layouts.app')

@section('title', 'Cortex NOC - Neural Operations Center')

@section('content')
<!-- Cargar Chart.js para los gráficos dinámicos del sistema -->
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>

<div class="cortex-neural-container">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(100, 255, 218, 0.3); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(100, 255, 218, 0.6); }
        .chat-bubble-bot {
            background: linear-gradient(135deg, rgba(10, 25, 47, 0.8), rgba(17, 34, 64, 0.9));
            border: 1px solid rgba(100, 255, 218, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 4px solid #64FFDA;
            color: #E6F1FF;
        }
        .chat-bubble-user {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2));
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-right: 4px solid #3B82F6;
            color: #E6F1FF;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        /* ── Sandbox Security Buttons ─────────────────────────────────── */
        .sandbox-segmented {
            display: flex;
            background: rgba(3, 7, 18, 0.7);
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 3px;
            margin-top: 14px;
            width: 100%;
        }
        .sandbox-segment-btn {
            flex: 1;
            border: none;
            background: transparent;
            color: #8892B0;
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 9px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            outline: none;
        }
        .sandbox-segment-btn:focus { outline: none; }
        .sandbox-segment-btn:hover:not(.active-green):not(.active-red) {
            background: rgba(255,255,255,0.06);
            color: #cfd8e3;
        }
        .sandbox-segment-btn.active-green {
            background: linear-gradient(135deg, #059669, #10B981);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4);
        }
        .sandbox-segment-btn.active-red {
            background: linear-gradient(135deg, #b91c1c, #EF4444);
            color: #FFFFFF;
            font-weight: 800;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.4);
            animation: sandbox-pulse-glow 1.8s ease-in-out infinite;
        }
        @keyframes sandbox-pulse-glow {
            0%,100% { box-shadow: 0 2px 8px rgba(239, 68, 68, 0.35); }
            50%      { box-shadow: 0 4px 20px rgba(239, 68, 68, 0.65); }
        }
        /* ── Email Status Panel ───────────────────────────────────────── */
        .email-stat-box {
            background: var(--cortex-surface);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
        }
        .email-stat-number {
            font-size: 1.6rem;
            font-weight: 900;
            line-height: 1;
        }
        .email-stat-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8892B0;
            margin-top: 4px;
        }
        .email-pulse-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 0 0 rgba(16,185,129,0.5);
            animation: email-pulse 2s infinite;
            display: inline-block;
        }
        @keyframes email-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
            70%  { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }
        .email-pulse-dot.warn { background:#F59E0B; animation-name:email-pulse-warn; }
        @keyframes email-pulse-warn {
            0%   { box-shadow: 0 0 0 0 rgba(245,158,11,0.6); }
            70%  { box-shadow: 0 0 0 8px rgba(245,158,11,0); }
            100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
        }
        /* ── Responsive adjustments for mobile screen sizes ─────────────── */
        @media (max-width: 768px) {
            .noc-tabs {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                gap: 0.5rem !important;
                padding-bottom: 0.6rem !important;
                scrollbar-width: none !important; /* Firefox */
                -ms-overflow-style: none !important; /* IE 10+ */
            }
            .noc-tabs::-webkit-scrollbar {
                display: none !important; /* Safari and Chrome */
            }
            .noc-tab-btn {
                flex-shrink: 0 !important;
                white-space: nowrap !important;
                font-size: 0.72rem !important;
                padding: 0.4rem 0.8rem !important;
            }
            .noc-tab-btn.active::after {
                bottom: -0.6rem !important;
            }
            .status-node {
                gap: 0.5rem !important;
                padding: 0.5rem 1rem !important;
            }
        }
    </style>
    <x-cortex.assistant-shell :security-status="$aiInsights['security']['status']">
        
        <!-- HEADER: ESTADO GLOBAL -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4 mb-md-5">
            <div>
                <h2 class="text-white font-weight-bold mb-1" style="font-size: calc(1.375rem + 1.5vw);">Neural Operations Center</h2>
                <p class="text-muted m-0">Supervisión centralizada y autonomía de nivel 3 activa.</p>
                <div class="mt-2">
                    <span class="badge" style="background: rgba(100,255,218,0.1); color: #64FFDA; border: 1px solid #64FFDA; font-size: 0.8rem; padding: 0.4em 0.8em;">
                        <i class="fa-solid fa-check-double mr-1"></i> ESTADO GENERAL: ÓPTIMO
                    </span>
                </div>
            </div>
            <div class="d-flex gap-4">
                <div class="text-center px-4 border-right border-dark">
                    <span class="d-block text-muted small uppercase font-weight-bold mb-1">Nivel de Riesgo</span>
                    <span style="color: {{ $aiInsights['risk_assessment']['color'] }}; font-weight: 900; font-size: 1.2rem;">
                        {{ $aiInsights['risk_assessment']['level'] }}
                    </span>
                </div>
                <div class="text-center px-4">
                    <span class="d-block text-muted small uppercase font-weight-bold mb-1">Uptime de IA</span>
                    <span class="text-primary font-weight-bold" style="font-size: 1.2rem;">99.98%</span>
                </div>
            </div>
        </div>

        <!-- TABS DE NAVEGACIÓN -->
        <div class="noc-tabs">
            <button class="noc-tab-btn active" onclick="switchTab(this, 'monitoring')">Monitoreo</button>
            <button class="noc-tab-btn" onclick="switchTab(this, 'security')">Seguridad</button>
            <button class="noc-tab-btn" onclick="switchTab(this, 'diagnostic')">Diagnóstico</button>
            <button class="noc-tab-btn" onclick="switchTab(this, 'intelligence')">Inteligencia</button>
            <button class="noc-tab-btn" onclick="switchTab(this, 'activity')" style="color: #F59E0B;"><i class="fa-solid fa-clock-rotate-left mr-2"></i>Actividad</button>
            <button class="noc-tab-btn" onclick="switchTab(this, 'assistant')" style="color: #64FFDA;"><i class="fa-solid fa-robot mr-2"></i>Asistente</button>
        </div>

        <!-- CONTENIDO DE TABS -->
        <div id="noc-content">
            
            <!-- TAB 1: MONITOREO -->
            <div id="tab-monitoring" class="noc-section">

                {{-- ── PANEL DE NOTIFICACIONES AUTOMÁTICAS ─── --}}
                @if(Auth::user()->rol === 'Administrador')
                @php
                    $es = $emailStatus ?? [
                        'email' => 'N/A', 'enviados_hoy' => 0,
                        'vales_retrasados' => 0, 'agotadas' => 0,
                        'ultima_revision' => 'N/A', 'error' => null, 'activo' => true
                    ];
                @endphp
                <div class="row mb-4 mt-2">
                    <div class="col-12">
                        <div class="p-4 rounded" style="background: rgba(5,15,35,0.6); border: 1px solid rgba(100,255,218,0.15); border-left: 4px solid #64FFDA;">

                            {{-- Cabecera del panel --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="email-pulse-dot {{ $es['error'] ? 'warn' : '' }}"></div>
                                    <div>
                                        <h6 class="text-white font-weight-bold mb-0" style="font-size:0.95rem;">
                                            <i class="fa-solid fa-envelope-circle-check mr-2" style="color:#64FFDA;"></i>
                                            Sistema de Alertas Automático
                                        </h6>
                                        <p class="text-muted mb-0" style="font-size:0.72rem;">
                                            Correo: <strong style="color:#64FFDA;">{{ $es['email'] }}</strong>
                                            &nbsp;·&nbsp; Última revisión: <span style="color:#8892B0;">{{ $es['ultima_revision'] }}</span>
                                        </p>
                                    </div>
                                </div>
                                <span class="badge d-flex align-items-center gap-2"
                                    style="background:{{ $es['error'] ? 'rgba(245,158,11,0.15)' : 'rgba(16,185,129,0.12)' }};
                                           color:{{ $es['error'] ? '#F59E0B' : '#10B981' }};
                                           border:1px solid {{ $es['error'] ? '#F59E0B' : '#10B981' }};
                                           font-size:0.72rem; font-weight:800; padding:6px 12px; border-radius:20px;">
                                    <i class="fa-solid {{ $es['error'] ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                                    {{ $es['error'] ? 'CON ADVERTENCIA' : 'ACTIVO' }}
                                </span>
                            </div>

                            {{-- Tarjetas de estado --}}
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-3">
                                    <div class="email-stat-box">
                                        <div class="email-stat-number" style="color:#64FFDA;">{{ $es['vales_retrasados'] }}</div>
                                        <div class="email-stat-label">Vales retrasados</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="email-stat-box">
                                        <div class="email-stat-number" style="color:#EF4444;">{{ $es['agotadas'] }}</div>
                                        <div class="email-stat-label">Herr. agotadas</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="email-stat-box">
                                        <div class="email-stat-number" style="color:#10B981;">{{ $es['enviados_hoy'] }}</div>
                                        <div class="email-stat-label">Correos enviados</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="email-stat-box">
                                        <div class="email-stat-number" style="color:#8892B0; font-size:1rem;">AUTO</div>
                                        <div class="email-stat-label">Modo de envío</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Descripción del sistema --}}
                            <div class="d-flex align-items-start gap-3" style="background:rgba(100,255,218,0.04); border-radius:8px; padding:12px;">
                                <i class="fa-solid fa-circle-info mt-1" style="color:#64FFDA; flex-shrink:0;"></i>
                                <p class="text-muted mb-0 small" style="line-height:1.6;">
                                    El sistema revisa automáticamente cada vez que el administrador abre el Cortex.
                                    Si hay <strong style="color:#E6F1FF;">vales retrasados</strong> o <strong style="color:#E6F1FF;">herramientas agotadas</strong>,
                                    se envía un correo real a tu cuenta de Gmail sin que tengas que hacer nada.
                                    Cada vale se notifica <u>solo una vez</u> para no saturar tu bandeja.
                                </p>
                            </div>

                            @if($es['error'])
                            <div class="mt-3 p-3 rounded d-flex align-items-start gap-3" style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.3);">
                                <i class="fa-solid fa-triangle-exclamation mt-1" style="color:#F59E0B; flex-shrink:0;"></i>
                                <div>
                                    <strong style="color:#F59E0B;">Advertencia de envío de correo:</strong>
                                    <p class="mb-0 small" style="color:#FCD34D;">{{ $es['error'] }}</p>
                                    <p class="mb-0 small mt-1" style="color:#8892B0;">Verifica en <code style="color:#64FFDA;">.env</code>: <code>MAIL_PASSWORD</code> y <code>MAIL_SCHEME=null</code>.</p>
                                </div>
                            </div>
                            @endif

                            @if($es['enviados_hoy'] > 0)
                            <div class="mt-3 p-3 rounded d-flex align-items-center gap-3" style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25);">
                                <i class="fa-solid fa-paper-plane" style="color:#10B981; font-size:1.1rem;"></i>
                                <span style="color:#10B981; font-weight:600;">
                                    ✅ Se enviaron {{ $es['enviados_hoy'] }} correo(s) de alerta en esta sesión
                                </span>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endif

                @if(count($aiInsights['anomalies']) > 0)

                <div class="row mb-5">
                    <div class="col-12">
                        <div class="p-4 rounded position-relative overflow-hidden" style="background: rgba(244, 63, 94, 0.05); border: 1px solid rgba(244, 63, 94, 0.3); box-shadow: 0 0 20px rgba(244, 63, 94, 0.1);">
                            <div class="position-absolute" style="top:0; left:0; width:4px; height:100%; background:#F43F5E; box-shadow: 0 0 15px #F43F5E;"></div>
                            <h6 class="text-danger font-weight-bold mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-triangle-exclamation mr-3 fa-beat"></i> ALERTAS CRÍTICAS DETECTADAS
                            </h6>
                            <div class="row">
                                @foreach($aiInsights['anomalies'] as $anomaly)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="mt-1" style="width: 8px; height: 8px; border-radius: 50%; background: #F43F5E; box-shadow: 0 0 8px #F43F5E;"></div>
                                            <div class="text-white small" style="opacity: 0.9; line-height: 1.5;">
                                                <strong class="text-danger mr-2">{{ strtoupper($anomaly['type']) }}:</strong> {{ $anomaly['message'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="terminal-window">
                            <div class="d-flex justify-content-between mb-4 border-bottom border-dark pb-2">
                                <span class="text-primary small font-weight-bold">> NEURAL_LOGS_STREAM</span>
                                <span class="text-muted small">REAL_TIME_FEED</span>
                            </div>
                            <div id="neural-logs-container">
                                @foreach($aiInsights['neural_logs'] as $log)
                                    <div class="neural-log-line">
                                        <span class="log-time">[{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}]</span>
                                        <span class="log-type text-{{ $log->type === 'SECURITY' ? 'danger' : ($log->type === 'TRANSACTION' ? 'success' : 'primary') }}">
                                            {{ $log->type }}
                                        </span>
                                        <span class="log-msg">{{ $log->message }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="neural-log-line mt-3" style="opacity: 0.5;">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Escuchando eventos del núcleo...
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-cortex.module-card title="Carga de Sistema" icon="fa-microchip">
                            <div class="prediction-stats mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">CPU Load</span>
                                    <span class="text-primary small">24%</span>
                                </div>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                                    <div class="progress-bar bg-primary" style="width: 24%"></div>
                                </div>
                            </div>
                            <div class="prediction-stats mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">DB Latency</span>
                                    <span class="text-success small">12ms</span>
                                </div>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                                    <div class="progress-bar bg-success" style="width: 15%"></div>
                                </div>
                            </div>
                        </x-cortex.module-card>

                        <div class="mt-4">
                            <x-cortex.module-card title="Consola de Análisis" icon="fa-radar">
                                <p class="text-muted small mb-4">Inicia un diagnóstico completo para recalibrar los sensores neurales del almacén.</p>
                                <button class="btn-cortex-neural w-100 mb-3" style="padding: 1rem !important; border-radius: 12px !important;" onclick="runAIScan()">
                                    <i class="fa-solid fa-atom fa-spin mr-3"></i> RE-INICIAR ESCANEO
                                </button>

                                @if(!session('cortex_all_solved'))
                                <button class="btn w-100 mb-3 d-flex justify-content-center align-items-center" 
                                   style="padding: 1rem; border-radius: 12px; font-weight: bold; border: 1px solid rgba(100, 255, 218, 0.5); background: linear-gradient(45deg, rgba(100, 255, 218, 0.05), rgba(100, 255, 218, 0.15)); color: #64FFDA; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(100, 255, 218, 0.1);" 
                                   onmouseover="this.style.background='linear-gradient(45deg, rgba(100, 255, 218, 0.1), rgba(100, 255, 218, 0.25))'; this.style.boxShadow='0 6px 20px rgba(100, 255, 218, 0.3)'; this.style.transform='translateY(-2px)';" 
                                   onmouseout="this.style.background='linear-gradient(45deg, rgba(100, 255, 218, 0.05), rgba(100, 255, 218, 0.15))'; this.style.boxShadow='0 4px 15px rgba(100, 255, 218, 0.1)'; this.style.transform='translateY(0)';"
                                   onclick="solveAllErrors()">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-3" style="font-size: 1.2rem;"></i> 
                                    <span>Autopiloto: Corregir Todo</span>
                                </button>
                                @else
                                <div class="p-3 mb-3 rounded text-center" style="background: rgba(100, 255, 218, 0.05); border: 1px solid rgba(100, 255, 218, 0.3);">
                                    <i class="fa-solid fa-circle-check text-success mr-2"></i>
                                    <span class="text-white small font-weight-bold" style="letter-spacing: 0.5px; color: #64FFDA !important;">Optimización Global Activa</span>
                                </div>
                                <button class="btn btn-sm btn-link text-muted w-100 mb-3" style="text-decoration: none; font-size: 0.75rem; color: #8892B0 !important;" onclick="resetSimulation()">
                                    <i class="fa-solid fa-rotate-right mr-2"></i> Reiniciar simulación (ver errores)
                                </button>
                                @endif

                                <a href="{{ route('cortex.export_audit') }}" class="btn w-100 d-flex justify-content-center align-items-center" 
                                   style="padding: 1rem; border-radius: 12px; font-weight: bold; border: 1px solid rgba(244, 63, 94, 0.5); background: linear-gradient(45deg, rgba(244, 63, 94, 0.05), rgba(244, 63, 94, 0.15)); color: #F43F5E; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.1);" 
                                   onmouseover="this.style.background='linear-gradient(45deg, rgba(244, 63, 94, 0.1), rgba(244, 63, 94, 0.25))'; this.style.boxShadow='0 6px 20px rgba(244, 63, 94, 0.3)'; this.style.transform='translateY(-2px)';" 
                                   onmouseout="this.style.background='linear-gradient(45deg, rgba(244, 63, 94, 0.05), rgba(244, 63, 94, 0.15))'; this.style.boxShadow='0 4px 15px rgba(244, 63, 94, 0.1)'; this.style.transform='translateY(0)';">
                                    <i class="fa-solid fa-file-pdf mr-3" style="font-size: 1.2rem;"></i> 
                                    <span>Exportar Informe PDF</span>
                                </a>

                            </x-cortex.module-card>

                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: SEGURIDAD -->
            <div id="tab-security" class="noc-section d-none">

                {{-- ── CORTEX SECURITY SANDBOX ─── --}}
                @if(Auth::user()->rol === 'Administrador')
                @php $xssActive = session()->get('cortex_xss_protection', true); $sqliActive = session()->get('cortex_sqli_protection', true); @endphp
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="p-4 rounded" style="background: rgba(10, 25, 47, 0.4); border: 1px solid rgba(100, 255, 218, 0.15); border-left: 4px solid #64FFDA; text-align: left;">
                            <h6 class="text-white font-weight-bold mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-shield-halved mr-2" style="color:#64FFDA;"></i>
                                Cortex Security Sandbox &nbsp;<small class="text-muted">(Exposición de Vulnerabilidades en Vivo)</small>
                            </h6>
                            <p class="text-muted small mb-4">Usa este entorno de pruebas interactivo para demostrar ataques de seguridad en vivo. Activa/desactiva las defensas del sistema en tiempo real.</p>
                            @if(session('demo_status_changed'))
                            <div class="alert py-2 px-3 rounded mb-3 small d-flex align-items-center gap-2" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color:#10B981; font-weight: 600;">
                                <i class="fa-solid fa-circle-check"></i> {{ session('demo_status_changed') }}
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="p-3 rounded" style="background: rgba(17,34,64,0.4); border: 1px solid #1e293b; display:flex; flex-direction:column; justify-content:space-between; height:100%;">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span style="font-weight:700;color:#E6F1FF;font-size:0.85rem;">Filtro de Sanitización XSS</span>
                                                <span class="badge" style="background:{{ $xssActive ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' }};color:{{ $xssActive ? '#10B981' : '#EF4444' }};border:1px solid {{ $xssActive ? '#10B981' : '#EF4444' }};font-size:0.7rem;font-weight:700;">{{ $xssActive ? 'ACTIVO' : 'VULNERABLE' }}</span>
                                            </div>
                                            <p class="text-muted small mb-3" style="font-size:0.75rem;">Evita que atacantes guarden código malicioso <code style="color:#64FFDA;">&lt;script&gt;</code> en formularios.</p>
                                        </div>
                                        <div class="sandbox-segmented">
                                            <form method="POST" action="{{ route('cortex.demo.toggle_xss') }}" style="display:contents;">@csrf<input type="hidden" name="status" value="1"><button type="submit" class="sandbox-segment-btn {{ $xssActive ? 'active-green' : '' }}"><i class="fa-solid fa-shield"></i> Protegido</button></form>
                                            <form method="POST" action="{{ route('cortex.demo.toggle_xss') }}" style="display:contents;">@csrf<input type="hidden" name="status" value="0"><button type="submit" class="sandbox-segment-btn {{ !$xssActive ? 'active-red' : '' }}"><i class="fa-solid fa-triangle-exclamation"></i> Vulnerable</button></form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded" style="background: rgba(17,34,64,0.4); border: 1px solid #1e293b; display:flex; flex-direction:column; justify-content:space-between; height:100%;">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span style="font-weight:700;color:#E6F1FF;font-size:0.85rem;">Consultas Preparadas SQLi</span>
                                                <span class="badge" style="background:{{ $sqliActive ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' }};color:{{ $sqliActive ? '#10B981' : '#EF4444' }};border:1px solid {{ $sqliActive ? '#10B981' : '#EF4444' }};font-size:0.7rem;font-weight:700;">{{ $sqliActive ? 'ACTIVO' : 'VULNERABLE' }}</span>
                                            </div>
                                            <p class="text-muted small mb-3" style="font-size:0.75rem;">Evita inyecciones SQL mediante consultas preparadas y validación de parámetros.</p>
                                        </div>
                                        <div class="sandbox-segmented">
                                            <form method="POST" action="{{ route('cortex.demo.toggle_sqli') }}" style="display:contents;">@csrf<input type="hidden" name="status" value="1"><button type="submit" class="sandbox-segment-btn {{ $sqliActive ? 'active-green' : '' }}"><i class="fa-solid fa-shield"></i> Protegido</button></form>
                                            <form method="POST" action="{{ route('cortex.demo.toggle_sqli') }}" style="display:contents;">@csrf<input type="hidden" name="status" value="0"><button type="submit" class="sandbox-segment-btn {{ !$sqliActive ? 'active-red' : '' }}"><i class="fa-solid fa-triangle-exclamation"></i> Vulnerable</button></form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-5 rounded mb-4" style="background: rgba(10, 25, 47, 0.4); border: 1px solid rgba(100, 255, 218, 0.1);">
                            <div class="risk-gauge-container">
                                <svg class="gauge-svg" width="150" height="150">
                                    <circle class="gauge-circle-bg" cx="75" cy="75" r="65"></circle>
                                    <circle class="gauge-circle-val" cx="75" cy="75" r="65" 
                                            style="stroke-dasharray: {{ ($aiInsights['risk_assessment']['score'] / 100) * 408 }}, 408; stroke: {{ $aiInsights['risk_assessment']['color'] }};">
                                    </circle>
                                </svg>
                                <div class="gauge-text">
                                    <span class="val" style="color: {{ $aiInsights['risk_assessment']['color'] }}">{{ $aiInsights['risk_assessment']['score'] }}%</span>
                                    <span class="label">Riesgo Base</span>
                                </div>
                            </div>
                            <h5 class="mt-4 text-white uppercase mb-1">{{ $aiInsights['risk_assessment']['level'] }}</h5>
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small">
                                <i class="fa-solid fa-circle-info text-primary" style="font-size: 0.7rem;"></i>
                                <span style="font-size: 0.75rem; letter-spacing: 0.5px;">Cálculo basado en integridad, accesos y firewall.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h5 class="text-white mb-4">Incidentes de Seguridad</h5>
                        @forelse($aiInsights['security_incidents'] as $incident)
                            <div class="incident-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-{{ $incident['severity'] === 'CRITICAL' ? 'danger' : 'warning' }} px-3 py-1">
                                        {{ $incident['severity'] }}
                                    </span>
                                    <span class="text-muted small">Detectado hace momentos</span>
                                </div>
                                <p class="text-white m-0 font-weight-bold">{{ $incident['message'] }}</p>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-danger px-3" onclick="executeSecurityResponse('AISLAR', '{{ $incident['key'] }}')">AISLAR NODO</button>
                                    <button class="btn btn-sm btn-link text-muted" onclick="executeSecurityResponse('IGNORAR', '{{ $incident['key'] }}')">IGNORAR</button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5">
                                <i class="fa-solid fa-shield-check text-success mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="text-muted">No se detectan amenazas activas en el perímetro.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 3: DIAGNÓSTICO -->
            <div id="tab-diagnostic" class="noc-section d-none">
                <div class="row">
                    @foreach($aiInsights['diagnostic'] as $key => $test)
                        <div class="col-md-3 mb-4">
                            <div class="p-4 rounded text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <h6 class="text-muted uppercase small mb-3">{{ $key }}</h6>
                                <div class="mb-3">
                                    <i class="fa-solid {{ $test['status'] === 'PASS' ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}" style="font-size: 2rem;"></i>
                                </div>
                                <p class="small text-white mb-3" style="height: 40px; overflow: hidden;">{{ $test['message'] }}</p>
                                <button class="btn-cortex-neural py-2 w-100" style="font-size: 0.7rem;" onclick="runRepair('{{ $key }}')">
                                    {{ $test['status'] === 'PASS' ? 'OPTIMIZAR' : 'REPARAR' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- GRÁFICOS DE RESUMEN DE BUGS -->
                <div class="row mt-5">
                    <div class="col-md-6 mb-4">
                        <div class="glass-container p-4 h-100" style="background: rgba(10, 25, 47, 0.4); border: 1px solid rgba(100, 255, 218, 0.1); border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);">
                            <h4 class="text-white font-weight-bold mb-4" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-chart-pie mr-2 text-primary"></i> Bugs por Categoría
                            </h4>
                            <div class="row align-items-center">
                                <div class="col-sm-6 text-center">
                                    <div style="position: relative; width: 140px; height: 140px; margin: 0 auto;">
                                        <canvas id="chartBugsByCategory"></canvas>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-3 mt-sm-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless text-white mb-0" style="font-size: 0.8rem; background: transparent;">
                                            <tbody>
                                                @foreach($aiInsights['bugsByCategory'] as $cat => $count)
                                                    @php
                                                        $colors = [
                                                            'Seguridad' => '#F43F5E',
                                                            'Rendimiento' => '#3B82F6',
                                                            'Base de Datos' => '#10B981',
                                                            'Integridad de Modelos' => '#F59E0B',
                                                            'Permisos' => '#8B5CF6'
                                                        ];
                                                        $color = $colors[$cat] ?? '#8892B0';
                                                    @endphp
                                                    <tr onclick="showBugsModal('categoria', '{{ $cat }}')" style="border-bottom: 1px solid rgba(255,255,255,0.03); cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(100, 255, 218, 0.05)';" onmouseout="this.style.backgroundColor='transparent';">
                                                        <td class="d-flex align-items-center py-1 px-0">
                                                            <span class="d-inline-block rounded-circle mr-2" style="width: 8px; height: 8px; background-color: {{ $color }}; box-shadow: 0 0 5px {{ $color }};"></span>
                                                            <span style="opacity: 0.85;">{{ $cat }}</span>
                                                        </td>
                                                        <td class="text-right py-1 px-0 font-weight-bold" style="color: {{ $color }}">{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="glass-container p-4 h-100" style="background: rgba(10, 25, 47, 0.4); border: 1px solid rgba(100, 255, 218, 0.1); border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);">
                            <h4 class="text-white font-weight-bold mb-4" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-triangle-exclamation mr-2 text-danger"></i> Distribución de Severidad
                            </h4>
                            <div class="row align-items-center">
                                <div class="col-sm-6 text-center">
                                    <div style="position: relative; width: 140px; height: 140px; margin: 0 auto;">
                                        <canvas id="chartBugsBySeverity"></canvas>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-3 mt-sm-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless text-white mb-0" style="font-size: 0.8rem; background: transparent;">
                                            <tbody>
                                                @foreach($aiInsights['bugsBySeverity'] as $sev => $count)
                                                    @php
                                                        $colors = [
                                                            'Crítico' => '#EF4444',
                                                            'Alto' => '#F97316',
                                                            'Medio' => '#EAB308',
                                                            'Bajo' => '#10B981'
                                                        ];
                                                        $color = $colors[$sev] ?? '#8892B0';
                                                    @endphp
                                                    <tr onclick="showBugsModal('severidad', '{{ $sev }}')" style="border-bottom: 1px solid rgba(255,255,255,0.03); cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(244, 63, 94, 0.05)';" onmouseout="this.style.backgroundColor='transparent';">
                                                        <td class="d-flex align-items-center py-1 px-0">
                                                            <span class="d-inline-block rounded-circle mr-2" style="width: 8px; height: 8px; background-color: {{ $color }}; box-shadow: 0 0 5px {{ $color }};"></span>
                                                            <span style="opacity: 0.85;">{{ $sev }}</span>
                                                        </td>
                                                        <td class="text-right py-1 px-0 font-weight-bold" style="color: {{ $color }}">{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NUEVA SECCIÓN: TABLA DE RESUMEN PARA EL INFORME -->
                <div class="row mt-5">
                    <div class="col-12">
                        <x-cortex.module-card title="Resumen General: Bugs e Incidencias" icon="fa-bug">
                            <div class="table-responsive mt-3">
                                <table class="table table-borderless text-white" style="background: rgba(10,25,47,0.3); border-radius: 12px; overflow: hidden;">
                                    <thead style="background: rgba(100,255,218,0.1); color: #64FFDA; border-bottom: 2px solid rgba(100,255,218,0.2);">
                                        <tr>
                                            <th class="py-3 px-4 font-weight-bold">Módulo / Categoría</th>
                                            <th class="py-3 px-4 font-weight-bold">Descripción del Escaneo</th>
                                            <th class="py-3 px-4 font-weight-bold text-center">Severidad</th>
                                            <th class="py-3 px-4 font-weight-bold text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($aiInsights['diagnostic'] as $key => $test)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td class="py-3 px-4 uppercase text-muted font-weight-bold">{{ $key }}</td>
                                            <td class="py-3 px-4" style="opacity: 0.8; font-size: 0.9rem;">{{ $test['message'] }}</td>
                                            <td class="py-3 px-4 text-center">
                                                @if($test['status'] === 'PASS')
                                                    <span class="badge" style="background: rgba(100,255,218,0.1); color: #64FFDA; border: 1px solid #64FFDA;">LOW (Nominal)</span>
                                                @else
                                                    <span class="badge" style="background: rgba(244,63,94,0.1); color: #F43F5E; border: 1px solid #F43F5E;">HIGH (Crítico)</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($test['status'] === 'PASS')
                                                    <i class="fa-solid fa-check text-success"></i> 0 Bugs
                                                @else
                                                    <i class="fa-solid fa-bug text-danger"></i> Falla Detectada
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        <!-- Simulamos un escaneo del núcleo para dar volumen a la tabla -->
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td class="py-3 px-4 uppercase text-muted font-weight-bold">FIREWALL</td>
                                            <td class="py-3 px-4" style="opacity: 0.8; font-size: 0.9rem;">Verificación de accesos no autorizados en capa de red.</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="badge" style="background: rgba(100,255,218,0.1); color: #64FFDA; border: 1px solid #64FFDA;">LOW (Nominal)</span>
                                            </td>
                                            <td class="py-3 px-4 text-center"><i class="fa-solid fa-check text-success"></i> 0 Bugs</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </x-cortex.module-card>
                    </div>
                </div>
            </div>

            <!-- TAB 4: INTELIGENCIA -->
            <div id="tab-intelligence" class="noc-section d-none">
                <div class="row">
                    <div class="col-md-6">
                        <x-cortex.module-card title="Análisis Predictivo" icon="fa-chart-line">
                            <p class="text-muted">Proyección de actividad para los próximos 7 días.</p>
                            <div class="p-4 text-center">
                                <div class="predictive-chart-container mb-4" style="height: 110px; position: relative;">
                                    <!-- SVG Smooth Line Graph -->
                                    <svg viewBox="0 0 100 30" width="100%" height="100%" preserveAspectRatio="none" style="overflow: visible;">
                                        <defs>
                                            <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#64FFDA" stop-opacity="0.25"/>
                                                <stop offset="100%" stop-color="#64FFDA" stop-opacity="0.0"/>
                                            </linearGradient>
                                        </defs>
                                        <!-- Gradient Area -->
                                        <path d="M 0 30 C 15 25, 30 20, 50 12 C 70 8, 85 5, 100 2 L 100 30 Z" fill="url(#chartGrad)"/>
                                        <!-- Animated Line -->
                                        <path d="M 0 30 C 15 25, 30 20, 50 12 C 70 8, 85 5, 100 2" fill="none" stroke="#64FFDA" stroke-width="1.5" stroke-linecap="round"
                                              style="stroke-dasharray: 200; stroke-dashoffset: 200; animation: drawLine 2.5s ease-out forwards;"/>
                                        <!-- Forecast dots -->
                                        <circle cx="50" cy="12" r="1.5" fill="#64FFDA" style="opacity: 0; animation: fadeInPoint 0.5s ease-out 1s forwards;"/>
                                        <circle cx="100" cy="2" r="1.5" fill="#0A192F" stroke="#64FFDA" stroke-width="1" style="opacity: 0; animation: fadeInPoint 0.5s ease-out 2s forwards;"/>
                                    </svg>
                                    
                                    <!-- CSS Animation styles -->
                                    <style>
                                        @keyframes drawLine {
                                            to { stroke-dashoffset: 0; }
                                        }
                                        @keyframes fadeInPoint {
                                            to { opacity: 1; }
                                        }
                                    </style>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-left">
                                        <h4 class="text-white font-weight-bold m-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-arrow-trend-up text-success fa-beat"></i>
                                            {{ $aiInsights['trends']['prediction'] }}
                                        </h4>
                                        <span class="text-muted small" style="font-size: 0.75rem;">Tendencia de flujo operativo</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge bg-primary text-dark px-3 py-1 font-weight-bold" style="font-size: 0.75rem;">
                                            SCORE: {{ $aiInsights['trends']['activity_score'] }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </x-cortex.module-card>
                    </div>
                    <div class="col-md-6">
                        <x-cortex.module-card title="Recomendaciones" icon="fa-lightbulb">
                            @forelse($aiInsights['recommendations'] as $rec)
                                <div class="p-3 mb-3 rounded" style="background: rgba(100, 255, 218, 0.03); border-left: 3px solid #64FFDA;">
                                    <i class="fa-solid {{ $rec['icon'] }} mr-2 text-primary"></i>
                                    <span class="text-muted small">{{ $rec['text'] }}</span>
                                </div>
                            @empty
                                <div class="p-5 text-center" style="background: rgba(100, 255, 218, 0.02); border: 1px dashed rgba(100, 255, 218, 0.15); border-radius: 12px; margin-top: 15px;">
                                    <i class="fa-solid fa-circle-check text-success fa-bounce mb-3" style="font-size: 3rem; --fa-bounce-start-y-value: -8px; --fa-bounce-transition-duration: 1.5s;"></i>
                                    <h6 class="text-white font-weight-bold mb-1">Núcleo en Estado Óptimo</h6>
                                    <p class="text-muted small m-0" style="font-size: 0.75rem; line-height: 1.5;">
                                        No hay recomendaciones críticas pendientes. Todos tus activos mantienen stock estable y tu personal reporta actividad continua en el almacén.
                                    </p>
                                </div>
                            @endforelse
                        </x-cortex.module-card>
                    </div>
                </div>
            </div>

            <!-- TAB 5: ASISTENTE VIRTUAL -->
            <div id="tab-assistant" class="noc-section d-none">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <x-cortex.module-card title="Chat NLP Cortex" icon="fa-comment-dots">
                            <div class="cortex-chat-window d-flex flex-column h-100" style="min-height: 400px;">
                                <div id="cortex-chat-messages" class="flex-grow-1 p-4 overflow-auto" style="background: rgba(10,25,47,0.3); border-radius: 8px; margin-bottom: 1rem; max-height: 500px;">
                                    <div class="chat-message mb-4" style="text-align: left;">
                                        <div class="d-inline-block p-3 rounded" style="background: rgba(100, 255, 218, 0.1); border-left: 4px solid #64FFDA; color: #E6F1FF; max-width: 80%;">
                                            <strong>Cortex:</strong> Hola, soy el Asistente Neuronal. Puedes preguntarme sobre herramientas no devueltas, stock disponible o quién tiene alguna herramienta específica.
                                        </div>
                                    </div>
                                </div>
                                <div class="cortex-chat-input d-flex flex-column gap-2">
                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                        <button type="button" class="badge" style="background: rgba(100, 255, 218, 0.1); color: #64FFDA; border: 1px solid rgba(100, 255, 218, 0.3); cursor: pointer; padding: 6px 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(100, 255, 218, 0.2)'" onmouseout="this.style.background='rgba(100, 255, 218, 0.1)'" onclick="document.getElementById('cortex-query-input').value = 'Ver lista de herramientas'; sendCortexQuery();"><i class="fa-solid fa-list mr-1"></i> Ver Catálogo</button>
                                        <button type="button" class="badge" style="background: rgba(100, 255, 218, 0.1); color: #64FFDA; border: 1px solid rgba(100, 255, 218, 0.3); cursor: pointer; padding: 6px 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(100, 255, 218, 0.2)'" onmouseout="this.style.background='rgba(100, 255, 218, 0.1)'" onclick="document.getElementById('cortex-query-input').value = 'Qué herramientas están retrasadas?'; sendCortexQuery();"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Retrasadas</button>
                                        <button type="button" class="badge" style="background: rgba(100, 255, 218, 0.1); color: #64FFDA; border: 1px solid rgba(100, 255, 218, 0.3); cursor: pointer; padding: 6px 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(100, 255, 218, 0.2)'" onmouseout="this.style.background='rgba(100, 255, 218, 0.1)'" onclick="document.getElementById('cortex-query-input').value = 'Qué herramientas tienen stock bajo?'; sendCortexQuery();"><i class="fa-solid fa-arrow-trend-down mr-1"></i> Stock Bajo</button>
                                        <button type="button" class="badge" style="background: rgba(100, 255, 218, 0.1); color: #64FFDA; border: 1px solid rgba(100, 255, 218, 0.3); cursor: pointer; padding: 6px 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(100, 255, 218, 0.2)'" onmouseout="this.style.background='rgba(100, 255, 218, 0.1)'" onclick="document.getElementById('cortex-query-input').value = 'Cuáles son las herramientas más prestadas?'; sendCortexQuery();"><i class="fa-solid fa-star mr-1"></i> Más Prestadas</button>
                                    </div>
                                    <div class="d-flex w-100">
                                        <input type="text" id="cortex-query-input" class="form-control" placeholder="Ej: ¿Dónde está el taladro? o ¿Qué herramientas están retrasadas?" style="background: rgba(10,25,47,0.8); border: 1px solid rgba(100,255,218,0.3); color: #E6F1FF; padding: 12px; border-radius: 8px 0 0 8px;" onkeypress="if(event.key === 'Enter') sendCortexQuery();">
                                        <button class="btn btn-primary" onclick="sendCortexQuery()" style="border-radius: 0 8px 8px 0; background: #64FFDA; color: #0A192F; font-weight: bold; border: none; padding: 0 20px;">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </x-cortex.module-card>
                    </div>
                </div>
            </div>

            <!-- TAB: ACTIVIDAD - Registro de Auditoría -->
            <div id="tab-activity" class="noc-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white mb-0">
                        <i class="fa-solid fa-clock-rotate-left mr-2" style="color:#F59E0B;"></i>
                        Registro de Actividad del Sistema
                    </h5>
                    <span class="badge" style="background:rgba(245,158,11,0.15); color:#F59E0B; border:1px solid #F59E0B; font-size:0.75rem; padding:6px 12px;">
                        Últimas {{ $auditLogs->count() }} acciones registradas
                    </span>
                </div>

                <!-- Filtro rápido -->
                <div class="mb-3">
                    <input type="text" id="filtro-actividad" onkeyup="filtrarActividad()" placeholder="Filtrar por acción, usuario, descripción..."
                        style="width:100%; background:rgba(10,25,47,0.8); border:1px solid rgba(100,255,218,0.2); color:#E6F1FF; padding:10px 14px; border-radius:8px; outline:none;">
                </div>

                <div class="table-container" style="border-radius:12px; overflow:hidden; border:1px solid rgba(255,255,255,0.07);">
                    <table class="table" id="tabla-actividad" style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                        <thead>
                            <tr>
                                <th style="padding:0.85rem 1rem; background:#0d2137; color:#8892B0; border-bottom:1px solid rgba(255,255,255,0.07); white-space:nowrap;">Acción</th>
                                <th style="padding:0.85rem 1rem; background:#0d2137; color:#8892B0; border-bottom:1px solid rgba(255,255,255,0.07);">Ejecutado por</th>
                                <th style="padding:0.85rem 1rem; background:#0d2137; color:#8892B0; border-bottom:1px solid rgba(255,255,255,0.07);">Tabla</th>
                                <th style="padding:0.85rem 1rem; background:#0d2137; color:#8892B0; border-bottom:1px solid rgba(255,255,255,0.07);">Descripción</th>
                                <th style="padding:0.85rem 1rem; background:#0d2137; color:#8892B0; border-bottom:1px solid rgba(255,255,255,0.07); white-space:nowrap;">Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditLogs as $log)
                            @php
                                $accionColor = match($log->accion) {
                                    'ELIMINAR' => ['bg' => 'rgba(239,68,68,0.12)', 'color' => '#EF4444', 'icon' => 'fa-trash'],
                                    'CREAR'    => ['bg' => 'rgba(16,185,129,0.12)', 'color' => '#10B981', 'icon' => 'fa-plus'],
                                    'EDITAR'   => ['bg' => 'rgba(56,189,248,0.12)', 'color' => '#38BDF8', 'icon' => 'fa-pen'],
                                    default    => ['bg' => 'rgba(139,148,177,0.12)', 'color' => '#8892B0', 'icon' => 'fa-circle-info'],
                                };
                                $rowBg = $log->accion === 'ELIMINAR' ? 'background: rgba(239,68,68,0.04); border-left: 3px solid rgba(239,68,68,0.4);' : '';
                            @endphp
                            <tr class="fila-actividad" style="border-bottom:1px solid rgba(255,255,255,0.05); {{ $rowBg }}">
                                <td style="padding:0.75rem 1rem;">
                                    <span style="background:{{ $accionColor['bg'] }}; color:{{ $accionColor['color'] }}; padding:3px 10px; border-radius:20px; font-weight:700; font-size:0.72rem; white-space:nowrap;">
                                        <i class="fa-solid {{ $accionColor['icon'] }} mr-1"></i>
                                        {{ $log->accion }}
                                    </span>
                                </td>
                                <td style="padding:0.75rem 1rem; color:#E6F1FF;">
                                    @if($log->usuario)
                                        <div style="font-weight:600;">{{ $log->usuario->nombre }}</div>
                                        <div style="color:#8892B0; font-size:0.75rem;">
                                            <i class="fa-solid fa-at" style="font-size:0.65rem;"></i> {{ $log->usuario->usuario }}
                                            &nbsp;·&nbsp;
                                            <span style="color:
                                                @if($log->usuario->rol === 'Administrador') #EF4444
                                                @elseif($log->usuario->rol === 'Almacenero') #F59E0B
                                                @else #818CF8
                                                @endif
                                            ;">{{ $log->usuario->rol }}</span>
                                        </div>
                                    @else
                                        <span style="color:#8892B0; font-style:italic;">Sistema</span>
                                    @endif
                                </td>
                                <td style="padding:0.75rem 1rem;">
                                    <code style="color:#64FFDA; font-size:0.75rem; background:rgba(100,255,218,0.07); padding:2px 6px; border-radius:4px;">{{ $log->tabla }}</code>
                                </td>
                                <td style="padding:0.75rem 1rem; color:#CCD6F6; line-height:1.4;">
                                    {{ $log->descripcion }}
                                </td>
                                <td style="padding:0.75rem 1rem; color:#8892B0; white-space:nowrap; font-size:0.75rem;">
                                    {{ $log->fecha ? \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color:#8892B0; padding:2rem;">No hay registros de actividad todavía.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </x-cortex.assistant-shell>
</div>

<script>
function switchTab(btn, tabId) {
    // UI Update
    document.querySelectorAll('.noc-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Section Visibility
    document.querySelectorAll('.noc-section').forEach(sec => sec.classList.add('d-none'));
    const target = document.getElementById('tab-' + tabId);
    if (target) target.classList.remove('d-none');
}

function filtrarActividad() {
    const filtro = document.getElementById('filtro-actividad').value.toLowerCase();
    document.querySelectorAll('#tabla-actividad .fila-actividad').forEach(function(fila) {
        const texto = fila.innerText.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
}

function sendCortexQuery() {
    const input = document.getElementById('cortex-query-input');
    const msg = input.value.trim();
    if(!msg) return;

    appendChatMessage('Tú', msg, 'user');
    input.value = '';

    fetch('/cortex/ask', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ query: msg })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            appendChatMessage('Cortex', data.response.type === 'html' ? data.response.html : data.response.message, 'bot');
        } else {
            appendChatMessage('Sistema', 'Error procesando la consulta.', 'error');
        }
    })
    .catch(e => appendChatMessage('Sistema', 'Fallo de conexión.', 'error'));
}

function appendChatMessage(sender, content, type) {
    const container = document.getElementById('cortex-chat-messages');
    const align = type === 'user' ? 'text-align: right;' : 'text-align: left;';
    const bubbleClass = type === 'user' ? 'chat-bubble-user' : (type === 'error' ? 'chat-bubble-bot border-left-danger' : 'chat-bubble-bot');
    const additionalStyle = type === 'error' ? 'border-left-color: #F43F5E;' : '';
    
    container.innerHTML += `
        <div class="chat-message mb-4" style="${align}">
            <div class="d-inline-block p-3 rounded ${bubbleClass}" style="max-width: 85%; text-align: left; ${additionalStyle}">
                <div class="mb-2" style="font-size: 0.8rem; font-weight: bold; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fa-solid ${type === 'user' ? 'fa-user' : 'fa-microchip'} mr-2"></i> ${sender}
                </div>
                <div class="mt-1" style="line-height: 1.5;">${content}</div>
            </div>
        </div>
    `;
    container.scrollTop = container.scrollHeight;
}

function runRepair(component) {
    Swal.fire({
        title: 'AUTORIZACIÓN REQUERIDA',
        text: '¿Desea que Cortex ejecute el protocolo de reparación para ' + component + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#64FFDA',
        cancelButtonColor: '#F43F5E',
        confirmButtonText: 'SÍ, AUTORIZAR',
        cancelButtonText: 'CANCELAR',
        background: '#0A192F',
        color: '#E6F1FF'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'EJECUTANDO PROTOCOLO',
                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i>',
                showConfirmButton: false,
                background: '#0A192F',
                color: '#E6F1FF'
            });

            const repairs = {
                'database': 'optimize_db',
                'storage': 'purge_cache',
                'security': 'fix_permissions',
                'models': 'optimize_db'
            };

            fetch(`/cortex/repair/${repairs[component] || component}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Operación Exitosa', text: data.message, background: '#0A192F', color: '#E6F1FF' });
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Fallo de Autorización', text: data.message, background: '#0A192F', color: '#E6F1FF' });
                }
            });
        }
    });
}

function executeSecurityResponse(action, incidentKey) {
    const config = {
        'AISLAR': { title: 'PROTOCOLO DE AISLAMIENTO', text: '¿Confirmas el aislamiento del nodo afectado?', icon: 'warning', btn: '#F43F5E' },
        'IGNORAR': { title: 'DESCARTAR INCIDENTE', text: '¿Deseas marcar este incidente como falso positivo?', icon: 'info', btn: '#64FFDA' }
    };

    Swal.fire({
        title: config[action].title,
        text: config[action].text,
        icon: config[action].icon,
        showCancelButton: true,
        confirmButtonColor: config[action].btn,
        cancelButtonColor: '#112240',
        confirmButtonText: 'CONFIRMAR',
        cancelButtonText: 'CANCELAR',
        background: '#0A192F',
        color: '#E6F1FF'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'PROCESANDO RESPUESTA',
                html: '<i class="fa-solid fa-shield-halved fa-spin fa-2x text-primary"></i>',
                showConfirmButton: false,
                background: '#0A192F',
                color: '#E6F1FF'
            });

            // Enviar petición POST al backend para resolver/ignorar el incidente de seguridad
            fetch('/cortex/incident/dismiss', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ key: incidentKey })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Acción Ejecutada',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Ejecución',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo de Red',
                    text: 'No se pudo comunicar con el núcleo de seguridad de Cortex.',
                    background: '#0A192F',
                    color: '#E6F1FF'
                });
            });
        }
    });
}

function restoreSecurityIncidents() {
    Swal.fire({
        title: 'RESTAURAR HISTORIAL',
        text: '¿Deseas restaurar todas las alertas e incidentes ignorados para volver a analizarlos?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#64FFDA',
        cancelButtonColor: '#112240',
        confirmButtonText: 'RESTAURAR',
        cancelButtonText: 'CANCELAR',
        background: '#0A192F',
        color: '#E6F1FF'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'RECOLECTANDO DATOS',
                html: '<i class="fa-solid fa-rotate-left fa-spin fa-2x text-primary"></i>',
                showConfirmButton: false,
                background: '#0A192F',
                color: '#E6F1FF'
            });
            fetch('/cortex/incident/restore', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alertas Restauradas',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF'
                    }).then(() => window.location.reload());
                }
            });
        }
    });
}

function resetSimulation() {
    Swal.fire({
        title: 'REINICIAR SIMULACIÓN',
        text: '¿Deseas restaurar la simulación original con todas las alertas e incidentes de ejemplo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F43F5E',
        cancelButtonColor: '#8892B0',
        confirmButtonText: 'SÍ, REINICIAR',
        cancelButtonText: 'CANCELAR',
        background: '#0A192F',
        color: '#E6F1FF'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/cortex/reset-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Simulación Reiniciada',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF'
                    }).then(() => window.location.reload());
                }
            });
        }
    });
}

function solveAllErrors() {
    Swal.fire({
        title: 'AUTOPILOTO DE MITIGACIÓN',
        text: '¿Deseas autorizar a Cortex para corregir y optimizar de manera autónoma todos los errores y alertas del sistema?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#64FFDA',
        cancelButtonColor: '#F43F5E',
        confirmButtonText: 'SÍ, OPTIMIZAR',
        cancelButtonText: 'CANCELAR',
        background: '#0A192F',
        color: '#E6F1FF'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'OPTIMIZANDO NÚCLEO',
                html: '<div class="text-center"><i class="fa-solid fa-wand-magic-sparkles fa-spin fa-2x text-primary mb-3"></i><p style="font-family:\'JetBrains Mono\'">> Reconfigurando directivas de red...<br>> Sanando dependencias y variables...<br>> Eliminando registros obsoletos...</p></div>',
                showConfirmButton: false,
                background: '#0A192F',
                color: '#E6F1FF'
            });

            fetch('/cortex/solve-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sistema Corregido',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF',
                        confirmButtonColor: '#64FFDA'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Mitigación',
                        text: data.message,
                        background: '#0A192F',
                        color: '#E6F1FF'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo de Conexión',
                    text: 'No se pudo establecer contacto con el núcleo de Cortex.',
                    background: '#0A192F',
                    color: '#E6F1FF'
                });
            });
        }
    });
}

function runAIScan() {
    Swal.fire({
        title: 'INICIANDO ESCANEO ESTRATÉGICO',
        html: `
            <div class="text-left mt-3" style="font-family: 'JetBrains Mono'; font-size: 0.8rem; color: #64FFDA;">
                <div id="scan-log"></div>
                <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.05); border-radius: 10px;">
                    <div id="scan-progress" class="progress-bar" style="width: 0%; background: #64FFDA; box-shadow: 0 0 10px #64FFDA; transition: width 0.4s;"></div>
                </div>
            </div>
        `,
        background: '#0A192F',
        color: '#E6F1FF',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            const log = document.getElementById('scan-log');
            const progress = document.getElementById('scan-progress');
            const steps = [
                'Iniciando protocolos de enlace...',
                'Analizando integridad de base de datos...',
                'Escaneando firmas de seguridad...',
                'Evaluando patrones de flujo...',
                'Compilando reporte de Cortex Assistant...'
            ];
            
            let current = 0;
            const interval = setInterval(() => {
                if (current < steps.length) {
                    const p = document.createElement('p');
                    p.innerHTML = '> ' + steps[current];
                    p.className = 'mb-1';
                    log.appendChild(p);
                    progress.style.width = ((current + 1) / steps.length * 100) + '%';
                    current++;
                } else {
                    clearInterval(interval);
                    window.location.href = "{{ route('cortex.scan') }}";
                }
            }, 600);
        }
    });
}

// FEEDBACK DE ESCANEO
@if(session('scan_completed'))
    Swal.fire({
        icon: "{{ session('scan_errors') ? 'warning' : 'success' }}",
        title: "{{ session('scan_errors') ? 'Diagnóstico Finalizado' : 'Sistema Nominal' }}",
        text: "{{ session('scan_errors') ? 'Se detectaron anomalías que requieren atención inmediata. El reporte de auditoría ha sido generado e iniciado su descarga automáticamente.' : 'Todos los módulos operan en parámetros óptimos. El reporte de auditoría ha sido generado e iniciado su descarga automáticamente.' }}",
        background: '#0A192F',
        color: '#E6F1FF',
        confirmButtonColor: '#64FFDA'
    });

    // Descarga automática del reporte PDF
    setTimeout(() => {
        const downloadLink = document.createElement('a');
        downloadLink.href = "{{ route('cortex.export_audit') }}";
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }, 500);
@endif

// Inicialización de Gráficos de Auditoría de Cortex
document.addEventListener('DOMContentLoaded', function() {
    initializeCortexCharts();
});

function showBugsModal(filterType, filterValue) {
    const bugsList = @json($aiInsights['bugsList'] ?? []);
    
    // Filtrar lista
    const filteredBugs = bugsList.filter(bug => {
        if (filterType === 'categoria') {
            return bug.categoria.toLowerCase() === filterValue.toLowerCase();
        } else if (filterType === 'severidad') {
            return bug.severidad.toLowerCase() === filterValue.toLowerCase();
        }
        return false;
    });

    if (filteredBugs.length === 0) {
        Swal.fire({
            title: 'SIN INCIDENCIAS',
            text: 'No se encontraron errores registrados para esta selección.',
            icon: 'info',
            background: '#0A192F',
            color: '#E6F1FF',
            confirmButtonColor: '#64FFDA'
        });
        return;
    }

    // Construir estructura HTML personalizada con estilos Cyberpunk
    let htmlContent = `
        <div class="text-left" style="font-family: 'JetBrains Mono', monospace; max-height: 400px; overflow-y: auto; padding-right: 5px;">
            <p class="text-muted small mb-3">> Leyendo bitácora de anomalías filtrada por ${filterType}: "${filterValue}"</p>
    `;

    filteredBugs.forEach((bug, index) => {
        const severityColors = {
            'Crítico': '#EF4444',
            'Alto': '#F97316',
            'Medio': '#EAB308',
            'Bajo': '#10B981'
        };
        const color = severityColors[bug.severidad] ?? '#8892B0';
        
        htmlContent += `
            <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.02); border-left: 4px solid ${color}; border-top: 1px solid rgba(255,255,255,0.03); border-right: 1px solid rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.03); text-align: left;">
                <div class="d-flex justify-content-between align-items-center mb-2" style="display: flex !important; justify-content: space-between !important; align-items: center !important;">
                    <span class="badge" style="background: rgba(${hexToRgb(color)}, 0.1); color: ${color}; border: 1px solid ${color}; font-size: 0.7rem; padding: 0.25em 0.6em; border-radius: 4px;">
                        ${bug.severidad.toUpperCase()}
                    </span>
                    <span class="text-muted" style="font-size: 0.7rem;">${bug.fecha}</span>
                </div>
                <div class="text-white mb-2" style="font-size: 0.85rem; line-height: 1.4; font-weight: normal; font-family: sans-serif;">
                    ${bug.descripcion}
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">
                    Categoría: <span style="color: #64FFDA;">${bug.categoria}</span>
                </div>
            </div>
        `;
    });

    htmlContent += `</div>`;

    Swal.fire({
        title: `<span style="font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; font-weight: bold; letter-spacing: 1px; color: #64FFDA;">INSPECTOR DE INCIDENCIAS</span><br><span style="font-size: 0.75rem; color: #8892B0; font-family: 'JetBrains Mono', monospace;">FILTRO: ${filterValue.toUpperCase()}</span>`,
        html: htmlContent,
        width: '600px',
        background: '#0A192F',
        color: '#E6F1FF',
        confirmButtonColor: '#64FFDA',
        confirmButtonText: 'CERRAR PANEL',
        customClass: {
            popup: 'cyber-swal-popup'
        }
    });
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? 
        parseInt(result[1], 16) + ',' + parseInt(result[2], 16) + ',' + parseInt(result[3], 16)
        : '100, 255, 218';
}

function initializeCortexCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }

    const categoryCanvas = document.getElementById('chartBugsByCategory');
    const severityCanvas = document.getElementById('chartBugsBySeverity');

    if (categoryCanvas) {
        const ctxCategory = categoryCanvas.getContext('2d');
        const bugsByCategoryData = @json($aiInsights['bugsByCategory'] ?? []);
        const labelsCategory = Object.keys(bugsByCategoryData);
        const valuesCategory = Object.values(bugsByCategoryData);
        
        const colorsCategory = labelsCategory.map(label => {
            if (label === 'Seguridad') return '#F43F5E';
            if (label === 'Rendimiento') return '#3B82F6';
            if (label === 'Base de Datos') return '#10B981';
            if (label === 'Integridad de Modelos') return '#F59E0B';
            return '#8B5CF6';
        });

        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: labelsCategory,
                datasets: [{
                    data: valuesCategory,
                    backgroundColor: colorsCategory,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, activeElements) => {
                    if (activeElements && activeElements.length > 0) {
                        const activeElement = activeElements[0];
                        const index = activeElement.index;
                        const label = labelsCategory[index];
                        showBugsModal('categoria', label);
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0A192F',
                        titleColor: '#E6F1FF',
                        bodyColor: '#E6F1FF',
                        borderColor: 'rgba(100, 255, 218, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                cutout: '70%'
            }
        });
    }

    if (severityCanvas) {
        const ctxSeverity = severityCanvas.getContext('2d');
        const bugsBySeverityData = @json($aiInsights['bugsBySeverity'] ?? []);
        const labelsSeverity = Object.keys(bugsBySeverityData);
        const valuesSeverity = Object.values(bugsBySeverityData);

        const colorsSeverity = labelsSeverity.map(label => {
            if (label === 'Crítico') return '#EF4444';
            if (label === 'Alto') return '#F97316';
            if (label === 'Medio') return '#EAB308';
            return '#10B981';
        });

        new Chart(ctxSeverity, {
            type: 'doughnut',
            data: {
                labels: labelsSeverity,
                datasets: [{
                    data: valuesSeverity,
                    backgroundColor: colorsSeverity,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, activeElements) => {
                    if (activeElements && activeElements.length > 0) {
                        const activeElement = activeElements[0];
                        const index = activeElement.index;
                        const label = labelsSeverity[index];
                        showBugsModal('severidad', label);
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0A192F',
                        titleColor: '#E6F1FF',
                        bodyColor: '#E6F1FF',
                        borderColor: 'rgba(100, 255, 218, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                cutout: '70%'
            }
        });
    }
}
</script>
@endsection
