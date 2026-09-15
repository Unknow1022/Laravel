@extends('layouts.app')

@section('title', 'Cortex Monitor - Centro de Mando')

@section('content')
<script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>

<!-- HEADER ESTRATÉGICO -->
<div class="dashboard-hero mb-5 mt-5">
    <div class="hero-content">
        <h1 class="hero-title"><i class="fa-solid fa-microchip mr-3"></i>Cortex Monitor</h1>
        <p class="hero-subtitle">Inteligencia operativa y flujo logístico en tiempo real.</p>
    </div>
</div>

<div class="grid-layout mb-5 mt-4" style="gap: 2rem;">
    <div class="col-12">
        <div class="cortex-card-premium">
            <div class="cortex-header-neural">
                <div class="cortex-avatar-orb">
                    <div class="node-dot pulse" style="position: absolute; top: 2px; right: 2px; background: var(--primary-color);"></div>
                    <i class="fa-solid fa-brain-circuit"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="m-0" style="font-size: 1.4rem; font-weight: 800; letter-spacing: -0.5px; color: var(--text-main);">Cortex Assistant <span class="text-muted" style="font-weight: 300;">| Core v4.2</span></h2>
                        <span class="cortex-status-badge nominal">
                            <i class="fa-solid fa-signal mr-2"></i> Estado Nominal
                        </span>
                    </div>
                </div>
            </div>

            <div class="cortex-grid-modules">
                <div class="cortex-main-summary">
                    <div class="summary-label text-muted uppercase small mb-2" style="letter-spacing: 1.5px; font-weight: 700;">Análisis Operativo en Tiempo Real</div>
                    <p class="cortex-summary-text">{{ $aiInsights['summary'] }}</p>
                </div>

                <div class="cortex-metric-boxes">
                    <div class="cortex-mini-card">
                        <span class="label">Seguridad</span>
                        <div class="value {{ count($aiInsights['security']['alerts']) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ count($aiInsights['security']['alerts']) }}
                        </div>
                        <small class="text-muted" style="font-size: 0.6rem;">Alertas Activas</small>
                    </div>
                    <div class="cortex-mini-card">
                        <span class="label">Sugerencias</span>
                        <div class="value text-primary">
                            {{ count($aiInsights['recommendations']) }}
                        </div>
                        <small class="text-muted" style="font-size: 0.6rem;">Optimización IA</small>
                    </div>
                </div>
            </div>

            <div class="cortex-action-footer">
                <a href="{{ route('cortex.index') }}" class="btn-cortex-action">
                    <span>ACCEDER AL CENTRO DE MONITOREO</span>
                    <i class="fa-solid fa-arrow-right-long"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- 3. ANOMALÍAS CRÍTICAS (Sección destacada si existen) -->
    @if(count($aiInsights['anomalies']) > 0)
    <div class="col-12 mt-4">
        <div class="anomalies-strip p-3 rounded d-flex align-items-center gap-4" style="background: rgba(244,63,94,0.05); border: 1px solid rgba(244,63,94,0.2);">
            <div class="strip-label text-danger font-weight-bold" style="font-size: 0.7rem; letter-spacing: 2px;">
                <i class="fa-solid fa-bolt-lightning mr-2"></i> ANOMALÍAS DETECTADAS
            </div>
            <div class="d-flex gap-4 flex-wrap">
                @foreach($aiInsights['anomalies'] as $anom)
                    <div class="anom-pill small text-muted">
                        <span class="text-danger">•</span> {{ $anom['message'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- KPIs PRINCIPALES -->
<div class="grid-layout mb-5">
    <div class="col-3">
        <div class="stat-card premium-shadow">
            <div class="stat-icon blue"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div class="stat-details">
                <h3>Total Activos</h3>
                <p>{{ number_format($totalStock) }}</p>
                <small class="text-muted">Inventario Global</small>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card premium-shadow">
            <div class="stat-icon orange"><i class="fa-solid fa-file-export"></i></div>
            <div class="stat-details">
                <h3>Vales Emitidos</h3>
                <p>{{ number_format($valesStats['emitidos']) }}</p>
                <small class="text-warning">Histórico Total</small>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card premium-shadow">
            <div class="stat-icon red"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-details">
                <h3>Pendientes</h3>
                <p>{{ number_format($valesStats['pendientes']) }}</p>
                <small class="text-danger">Requiere Retorno</small>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card premium-shadow">
            <div class="stat-icon green"><i class="fa-solid fa-check-double"></i></div>
            <div class="stat-details">
                <h3>Cerrados</h3>
                <p>{{ number_format($valesStats['cerrados']) }}</p>
                <small class="text-success">Operación Completa</small>
            </div>
        </div>
    </div>
</div>

<div class="grid-layout mb-5">
    <!-- GRÁFICO DE ACTIVIDAD SEMANAL -->
    <div class="col-8">
        <div class="glass-container p-4">
            <div class="container-header mb-4">
                <h3 class="container-title"><i class="fa-solid fa-wave-square mr-2"></i>Flujo Operativo Semanal</h3>
                <span class="text-muted small">Movimiento de activos (últimos 7 días)</span>
            </div>
            <div style="height: 300px;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- TOP ALMACENES -->
    <div class="col-4">
        <div class="glass-container p-4">
            <h3 class="container-title mb-4"><i class="fa-solid fa-ranking-star mr-2"></i>Carga por Almacén</h3>
            <div class="warehouse-list">
                @foreach($aiInsights['load_analysis'] as $load)
                <div class="warehouse-item">
                    <div class="w-info">
                        <span class="w-name">{{ $load['nombre'] }}</span>
                        <span class="ai-badge {{ $load['status'] == 'Sobrecargado' ? 'text-danger' : ($load['status'] == 'Bajo Uso' ? 'text-warning' : 'text-success') }}">
                            {{ $load['status'] }}
                        </span>
                    </div>
                    <div class="w-bar-container">
                        <div class="w-bar" style="width: {{ $load['load'] }}%; background: {{ $load['status'] == 'Sobrecargado' ? '#F43F5E' : 'var(--primary-color)' }};"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ $load['load'] }}% de la carga total</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- ACCESOS RÁPIDOS DINÁMICOS (Condicionales por Rol) -->
<div class="grid-layout mb-5">
    <div class="col-12">
        <div class="glass-container p-4">
            <h3 class="container-title mb-4"><i class="fa-solid fa-bolt mr-2"></i>Panel de Acción Rápida</h3>
            <div class="row">

                @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                {{-- Acciones de escritura: Solo Administrador y Almacenero --}}
                <div class="col-md-3">
                    <a href="{{ route('herramientas.create') }}" class="quick-card-v2">
                        <i class="fa-solid fa-wrench"></i>
                        <div class="q-meta">
                            <strong>Alta de Activo</strong>
                            <span>Registrar nueva herramienta</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('trabajadores.create') }}" class="quick-card-v2">
                        <i class="fa-solid fa-user-plus"></i>
                        <div class="q-meta">
                            <strong>Personal</strong>
                            <span>Añadir nuevo trabajador</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('vales.create') }}" class="quick-card-v2">
                        <i class="fa-solid fa-file-signature"></i>
                        <div class="q-meta">
                            <strong>Nuevo Vale</strong>
                            <span>Emitir vale de salida</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('herramientas.ubicaciones') }}" class="quick-card-v2">
                        <i class="fa-solid fa-map-marked-alt"></i>
                        <div class="q-meta">
                            <strong>Ubicaciones</strong>
                            <span>Mapa de red logística</span>
                        </div>
                    </a>
                </div>
                @endif

                @if(Auth::user()->rol === 'Supervisor')
                {{-- Acciones de lectura: Solo Supervisor --}}
                <div class="col-md-3">
                    <a href="{{ route('herramientas.index') }}" class="quick-card-v2">
                        <i class="fa-solid fa-wrench"></i>
                        <div class="q-meta">
                            <strong>Catálogo</strong>
                            <span>Ver inventario de activos</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('trabajadores.index') }}" class="quick-card-v2">
                        <i class="fa-solid fa-users"></i>
                        <div class="q-meta">
                            <strong>Personal</strong>
                            <span>Consultar trabajadores</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('reportes.index') }}" class="quick-card-v2">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <div class="q-meta">
                            <strong>Reportes</strong>
                            <span>Estadísticas avanzadas</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('herramientas.ubicaciones') }}" class="quick-card-v2">
                        <i class="fa-solid fa-map-marked-alt"></i>
                        <div class="q-meta">
                            <strong>Ubicaciones</strong>
                            <span>Mapa de red logística</span>
                        </div>
                    </a>
                </div>
                @endif

                @if(Auth::user()->rol === 'Administrador')
                {{-- Acciones exclusivas del Administrador (reemplaza uno de los botones) --}}
                @endif

            </div>
        </div>
    </div>
</div>

<style>
.quick-card-v2 { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; text-decoration: none; color: inherit; transition: all 0.3s; }
.quick-card-v2 i { font-size: 1.5rem; color: var(--primary-color); }
.quick-card-v2 .q-meta { display: flex; flex-direction: column; }
.quick-card-v2 strong { font-size: 0.9rem; color: #E6F1FF; }
.quick-card-v2 span { font-size: 0.75rem; color: var(--text-muted); }
.quick-card-v2:hover { background: rgba(100, 255, 218, 0.05); border-color: var(--primary-color); transform: translateY(-3px); }

.ai-card { box-shadow: 0 0 30px rgba(100, 255, 218, 0.05); }
.insight-item { font-size: 0.9rem; color: #CCD6F6; }
</style>

<script>
// NOTIFICACIÓN POST-ESCANEO
@if(session('scan_completed'))
    Swal.fire({
        icon: "{{ session('scan_errors') ? 'error' : 'success' }}",
        title: "{{ session('scan_errors') ? 'ANOMALÍAS DETECTADAS' : 'SISTEMA ÓPTIMO' }}",
        text: "{{ session('scan_errors') ? 'Se han encontrado fallos críticos en el núcleo del sistema. Revise el Core Monitor.' : 'Todos los protocolos de integridad han pasado las pruebas satisfactoriamente.' }}",
        background: '#050D1A',
        color: '#E6F1FF',
        confirmButtonColor: 'var(--primary-color)'
    });
@endif

// LÓGICA DE BÚSQUEDA GLOBAL
const searchInput = document.getElementById('globalSearch');
const searchResults = document.getElementById('search-results-dropdown');

if (searchInput) {
    searchInput.addEventListener('keyup', function(e) {
        const query = this.value;
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Usar ruta absoluta para evitar problemas de resolución en diferentes entornos
        fetch('/api/global-search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="p-4 text-center text-muted small">No se encontraron resultados</div>';
                } else {
                    let html = '';
                    data.forEach(item => {
                        html += `
                            <a href="${item.url}" class="search-result-item">
                                <i class="fa-solid ${item.icon}"></i>
                                <div class="result-meta">
                                    <strong>${item.title}</strong>
                                    <small>${item.subtitle}</small>
                                </div>
                                <span class="result-type-badge">${item.type}</span>
                            </a>
                        `;
                    });
                    searchResults.innerHTML = html;
                }
                searchResults.style.display = 'block';
            })
            .catch(err => {
                console.error('Error en búsqueda:', err);
            });
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
}

// GRÁFICO DE ACTIVIDAD SEMANAL
const ctxActivity = document.getElementById('activityChart').getContext('2d');
const gradient = ctxActivity.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(100, 255, 218, 0.2)');
gradient.addColorStop(1, 'rgba(100, 255, 218, 0)');

new Chart(ctxActivity, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($weeklyActivity, 'day')) !!},
        datasets: [{
            label: 'Vales Emitidos',
            data: {!! json_encode(array_column($weeklyActivity, 'count')) !!},
            borderColor: '#64FFDA',
            borderWidth: 3,
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#64FFDA',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#8892B0' } },
            x: { grid: { display: false }, ticks: { color: '#8892B0' } }
        }
    }
});
</script>
@endsection
