@extends('layouts.app')

@section('title', 'Gestión de Sanciones')

@section('content')
@php
    $migrationPending = !\Illuminate\Support\Facades\Schema::hasColumn('incidencias', 'tipo_falta');
@endphp

@if($migrationPending)
<div style="background:rgba(249,115,22,.12); border:1px solid rgba(249,115,22,.4); border-radius:10px; padding:1rem 1.4rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
    <i class="fa-solid fa-database fa-lg" style="color:#F97316;"></i>
    <div style="flex:1;">
        <strong style="color:#F97316;">⚙️ Migración pendiente.</strong>
        <span style="color:var(--text-muted); font-size:.9rem;"> Las columnas disciplinarias aún no existen en la base de datos. El formulario funciona en modo básico hasta que apliques la migración.</span>
    </div>
    <a href="/laravel_app/public/run_migration.php" target="_blank" class="btn" style="background:rgba(249,115,22,.15); color:#F97316; border:1px solid rgba(249,115,22,.4); width:auto; padding:.5rem 1.2rem; flex-shrink:0;">
        <i class="fa-solid fa-play mr-1"></i> Aplicar Migración
    </a>
</div>
@endif

<link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />

<style>
/* ─── Select2 Dark ─── */
.select2-container--default .select2-selection--single {
    background: var(--surface-color); border: 1px solid var(--border-color);
    border-radius: 8px; height: 44px; display: flex; align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); line-height: 44px; padding-left: 12px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; }
.select2-dropdown { background: #0D2137; border: 1px solid var(--border-color); border-radius: 8px; }
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background: rgba(100,255,218,.12); color: var(--primary-color); }
.select2-container--default .select2-results__option--selected { background: rgba(100,255,218,.2); }
.select2-search--dropdown .select2-search__field { background: #0A192F; color: var(--text-main); border: 1px solid var(--border-color); border-radius: 6px; padding: 6px 10px; }
.select2-results__option { color: var(--text-main); padding: 8px 12px; }

/* ─── Stats Cards ─── */
.stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
.stat-card {
    background: var(--surface-color); border-radius: 14px; padding: 1.4rem 1.6rem;
    border: 1px solid var(--border-color); position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.4); }
.stat-card .sc-glow {
    position: absolute; top: -30px; right: -30px; width: 100px; height: 100px;
    border-radius: 50%; opacity: .18; filter: blur(30px);
}
.stat-card .sc-icon { font-size: 2rem; margin-bottom: .6rem; }
.stat-card .sc-value { font-size: 2.4rem; font-weight: 800; line-height: 1; }
.stat-card .sc-label { font-size: .8rem; color: var(--text-muted); margin-top: .3rem; text-transform: uppercase; letter-spacing: .04em; }
.sc-red   { border-top: 3px solid #EF4444; } .sc-red   .sc-glow { background:#EF4444; } .sc-red   .sc-value { color:#EF4444; }
.sc-orange{ border-top: 3px solid #F97316; } .sc-orange .sc-glow { background:#F97316; } .sc-orange .sc-value { color:#F97316; }
.sc-purple{ border-top: 3px solid #8B5CF6; } .sc-purple .sc-glow { background:#8B5CF6; } .sc-purple .sc-value { color:#8B5CF6; }
.sc-teal  { border-top: 3px solid var(--primary-color); } .sc-teal  .sc-glow { background:var(--primary-color); } .sc-teal  .sc-value { color:var(--primary-color); }

/* ─── Top Workers ─── */
.top-worker { display:flex; align-items:center; gap:.8rem; padding:.6rem 0; border-bottom:1px solid var(--border-color); }
.top-worker:last-child { border-bottom:none; }
.tw-rank { width:28px; height:28px; border-radius:50%; background:rgba(239,68,68,.15); color:#EF4444; font-size:.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.tw-name { flex:1; font-size:.9rem; font-weight:500; }
.tw-count { font-size:.85rem; font-weight:700; color:#EF4444; }

/* ─── Section Layout ─── */
.page-grid { display:grid; grid-template-columns: 1fr 380px; gap:1.5rem; align-items:start; }
@media(max-width:960px){ .page-grid { grid-template-columns:1fr; } }

/* ─── Form Panel ─── */
.form-panel { background:var(--surface-color); border:1px solid var(--border-color); border-radius:14px; padding:1.6rem; position:sticky; top:1rem; }
.form-panel h3 { margin:0 0 1.2rem; color:var(--primary-color); font-size:1.1rem; display:flex; align-items:center; gap:.5rem; }

/* ─── Inc-Type cards ─── */
.inc-tipo-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; margin-bottom:1rem; }
.inc-tipo-card {
    border:1px solid var(--border-color); border-radius:8px; padding:.55rem .6rem;
    cursor:pointer; text-align:center; font-size:.72rem; font-weight:600;
    transition:all .2s; background:rgba(255,255,255,.03); color:var(--text-muted);
    display:flex; flex-direction:column; align-items:center; gap:.2rem;
}
.inc-tipo-card:hover { background:rgba(100,255,218,.06); border-color:rgba(100,255,218,.4); color:var(--text-main); }
.inc-tipo-card.selected { background:rgba(100,255,218,.12); border-color:var(--primary-color); color:var(--primary-color); box-shadow:0 0 10px rgba(100,255,218,.15); }
.inc-tipo-card .ico { font-size:1.3rem; }
.inc-tipo-card input[type="radio"] { display:none; }

/* Gravedad badge */
.badge-leve    { background:rgba(34,197,94,.15); color:#22C55E; border:1px solid rgba(34,197,94,.3); }
.badge-grave   { background:rgba(249,115,22,.15); color:#F97316; border:1px solid rgba(249,115,22,.3); }
.badge-muygrave{ background:rgba(239,68,68,.15);  color:#EF4444; border:1px solid rgba(239,68,68,.3); }
.badge-cumplido{ background:rgba(100,255,218,.1); color:var(--primary-color); border:1px solid rgba(100,255,218,.3); }
.badge-activo  { background:rgba(249,115,22,.12); color:#F97316; border:1px solid rgba(249,115,22,.3); }
.badge { padding:.2rem .65rem; border-radius:20px; font-size:.72rem; font-weight:700; white-space:nowrap; }

/* ─── Filters bar ─── */
.filter-bar { background:var(--surface-color); border:1px solid var(--border-color); border-radius:12px; padding:1rem 1.2rem; margin-bottom:1.2rem; display:flex; gap:.8rem; flex-wrap:wrap; align-items:flex-end; }
.filter-bar label { font-size:.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; margin-bottom:.2rem; display:block; }
.filter-bar .form-control { background:#0A192F; border-color:var(--border-color); color:var(--text-main); height:38px; font-size:.85rem; }
.filter-bar select.form-control { height:38px; padding:0 .6rem; }
.filter-group { display:flex; flex-direction:column; min-width:160px; }

/* ─── Evidence upload drop zone ─── */
.drop-zone {
    border:2px dashed var(--border-color); border-radius:10px;
    padding:1.2rem; text-align:center; cursor:pointer;
    transition:all .2s; background:rgba(255,255,255,.02);
}
.drop-zone:hover, .drop-zone.dragover { border-color:var(--primary-color); background:rgba(100,255,218,.05); }
.drop-zone .dz-icon { font-size:1.8rem; color:var(--text-muted); }
.drop-zone .dz-text { font-size:.8rem; color:var(--text-muted); margin-top:.4rem; }
.drop-zone .dz-filename { font-size:.8rem; color:var(--primary-color); font-weight:600; margin-top:.4rem; display:none; }

/* ─── Recommended action pill ─── */
#accion-preview { background:rgba(100,255,218,.07); border:1px solid rgba(100,255,218,.2); border-radius:8px; padding:.6rem .8rem; font-size:.8rem; color:var(--primary-color); display:none; margin-top:.6rem; }

/* ─── Table improvements ─── */
.table td, .table th { vertical-align: middle; }
</style>

<div class="d-flex mb-4" style="align-items:center; gap:1rem; flex-wrap:wrap;">
    <div>
        <h1 class="page-title" style="margin:0;">⚠️ Gestión de Sanciones</h1>
        <p style="color:var(--text-muted); margin:.2rem 0 0; font-size:.9rem;">Registra y controla las sanciones disciplinarias de los trabajadores del almacén.</p>
    </div>
    <div style="margin-left:auto; display:flex; gap:.6rem;">
        <button onclick="window.print()" class="btn" style="background:rgba(100,255,218,.08); color:var(--primary-color); border:1px solid rgba(100,255,218,.3); width:auto; padding:.5rem 1rem;">
            <i class="fa-solid fa-print mr-1"></i> Imprimir Reporte
        </button>
    </div>
</div>

{{-- Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('exito') && session('resumen'))
@php $r = session('resumen'); @endphp
<div class="alert" style="background:rgba(100,255,218,.08); border:1px solid rgba(100,255,218,.3); color:var(--primary-color); border-radius:10px; padding:1rem 1.4rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:.8rem;">
    <i class="fa-solid fa-check-circle fa-lg"></i>
    <div>
        <strong>Sanción registrada correctamente.</strong><br>
        <small style="color:var(--text-muted);">{{ $r['herramienta'] }} — {{ $r['estado'] }} — {{ $r['fecha'] }}</small>
    </div>
</div>
@endif
@if(session('success'))
<div class="alert" style="background:rgba(16,185,129,.1); border:1px solid #10B981; color:#10B981; border-radius:10px; padding:1rem 1.4rem; margin-bottom:1.5rem;">
    <i class="fa-solid fa-check-circle mr-1"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:1.5rem;">
    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $errors->first() }}
</div>
@endif

{{-- ── DASHBOARD STATS ────────────────────────────────────────────────────── --}}
<div class="stat-cards">
    <div class="stat-card sc-red">
        <div class="sc-glow"></div>
        <div class="sc-icon">🚨</div>
        <div class="sc-value">{{ $totalSanciones }}</div>
        <div class="sc-label">Total Sanciones</div>
    </div>
    <div class="stat-card sc-orange">
        <div class="sc-glow"></div>
        <div class="sc-icon">🔨</div>
        <div class="sc-value">{{ $totalDanadas }}</div>
        <div class="sc-label">Herramientas Dañadas</div>
    </div>
    <div class="stat-card sc-purple">
        <div class="sc-glow"></div>
        <div class="sc-icon">❓</div>
        <div class="sc-value">{{ $totalPerdidas }}</div>
        <div class="sc-label">Herramientas Perdidas</div>
    </div>
    <div class="stat-card sc-teal" style="padding:1.2rem;">
        <div class="sc-glow"></div>
        <div style="font-size:.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:.7rem; letter-spacing:.04em;">
            <i class="fa-solid fa-ranking-star mr-1"></i> Trabajadores Críticos
        </div>
        @forelse($topTrabajadores as $i => $tw)
        <div class="top-worker">
            <div class="tw-rank">{{ $i+1 }}</div>
            <div class="tw-name">{{ $tw->trabajador->nombre ?? '—' }} {{ $tw->trabajador->apellidos ?? '' }}</div>
            <div class="tw-count">{{ $tw->total }} falta{{ $tw->total > 1 ? 's' : '' }}</div>
        </div>
        @empty
        <p style="color:var(--text-muted); font-size:.85rem; margin:0;">Sin incidencias registradas aún.</p>
        @endforelse
    </div>
</div>

{{-- ── MAIN LAYOUT ─────────────────────────────────────────────────────────── --}}
<div class="page-grid">

    {{-- LEFT: FILTERS + TABLE --}}
    <div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('incidencias.index') }}">
            <div class="filter-bar">
                <div class="filter-group" style="flex:2; min-width:200px;">
                    <label>Trabajador</label>
                    <select name="trabajador_id" class="form-control" id="filter-trabajador">
                        <option value="">— Todos —</option>
                        @foreach($trabajadores as $t)
                            <option value="{{ $t->id }}" {{ request('trabajador_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->nombre }} {{ $t->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="flex:2; min-width:180px;">
                    <label>Tipo de Falta</label>
                    <select name="tipo_falta" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($tiposFalta as $tf)
                            <option value="{{ $tf }}" {{ request('tipo_falta') == $tf ? 'selected' : '' }}>{{ $tf }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="filter-group">
                    <label>Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <button type="submit" class="btn btn-primary" style="height:38px; padding:0 1.2rem; width:auto; align-self:flex-end;">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['trabajador_id','tipo_falta','fecha_inicio','fecha_fin']))
                <a href="{{ route('incidencias.index') }}" class="btn" style="height:38px; padding:0 1rem; width:auto; align-self:flex-end; background:rgba(239,68,68,.1); color:#EF4444; border:1px solid rgba(239,68,68,.3);">
                    <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-container" style="overflow-x:auto;">
            <table class="table" style="font-size:.85rem; min-width:900px;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Herramienta</th>
                        <th>Tipo de Falta</th>
                        <th>Gravedad</th>
                        <th>Trabajador</th>
                        <th>Medida Correctiva</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if($incidencias->isEmpty())
                    <tr><td colspan="8" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                        <i class="fa-solid fa-check-circle fa-2x" style="color:rgba(100,255,218,.3); display:block; margin-bottom:.5rem;"></i>
                        No hay sanciones registradas con los filtros actuales.
                    </td></tr>
                    @else
                    @foreach($incidencias as $inc)
                    <tr>
                        <td style="white-space:nowrap;">
                            <small style="color:var(--text-muted);">{{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}</small><br>
                            <small style="color:var(--text-muted); font-size:.7rem;">{{ \Carbon\Carbon::parse($inc->fecha)->format('H:i') }}</small>
                        </td>
                        <td>
                            <strong style="color:var(--primary-color);">{{ $inc->herramienta->codigo ?? '—' }}</strong><br>
                            <small style="color:var(--text-muted);">{{ $inc->herramienta->nombre ?? '—' }}</small>
                        </td>
                        <td><span class="badge" style="background:rgba(239,68,68,.1); color:#EF4444; border:1px solid rgba(239,68,68,.3);">{{ $inc->tipo_falta ?? $inc->tipo }}</span></td>
                        <td>
                            @php
                                $g = $inc->gravedad ?? 'Leve';
                                $gClass = $g === 'Muy grave' ? 'badge-muygrave' : ($g === 'Grave' ? 'badge-grave' : 'badge-leve');
                            @endphp
                            <span class="badge {{ $gClass }}">{{ $g }}</span>
                        </td>
                        <td>
                            @if($inc->trabajador)
                                <i class="fa-solid fa-user mr-1" style="color:var(--text-muted);"></i>
                                <a href="{{ route('trabajadores.show', $inc->trabajador_id) }}" style="color:var(--text-main);">
                                    {{ $inc->trabajador->nombre }} {{ $inc->trabajador->apellidos }}
                                </a>
                            @else
                                <span style="color:var(--text-muted); font-style:italic;">No asignado</span>
                            @endif
                        </td>
                        <td style="max-width:220px;">
                            <small style="color:var(--text-muted);">{{ $inc->accion_correctiva ?? '—' }}</small>
                        </td>
                        <td>
                            @php $estado = $inc->estado_disciplinario ?? 'Activo'; @endphp
                            <span class="badge {{ $estado === 'Cumplido' ? 'badge-cumplido' : 'badge-activo' }}">
                                {{ $estado }}
                            </span>
                            @if($inc->evidencia)
                                <br><a href="{{ asset('storage/'.$inc->evidencia) }}" target="_blank" style="font-size:.7rem; color:var(--primary-color);"><i class="fa-solid fa-paperclip mr-1"></i>Evidencia</a>
                            @endif
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            @if(($inc->estado_disciplinario ?? 'Activo') === 'Activo')
                                @if(in_array(Auth::user()->rol, ['Administrador','Almacenero']))
                                <form method="POST" action="{{ route('incidencias.resolver', $inc->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="padding:.25rem .6rem; font-size:.72rem; background:rgba(16,185,129,.1); color:#10B981; border:1px solid rgba(16,185,129,.3); cursor:pointer;" title="Marcar sanción como cumplida" onclick="return confirm('¿Marcar esta sanción como cumplida? El trabajador recuperará el acceso.')">
                                        <i class="fa-solid fa-check"></i> Cumplido
                                    </button>
                                </form>
                                @endif
                            @else
                                <span style="color:var(--text-muted); font-size:.75rem;"><i class="fa-solid fa-lock-open"></i> Levantada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT: FORM PANEL --}}
    @if(in_array(Auth::user()->rol, ['Administrador','Almacenero']))
    <div class="form-panel">
        <h3><i class="fa-solid fa-circle-plus"></i> Registrar Nueva Sanción</h3>

        <form action="{{ route('incidencias.store') }}" method="POST" enctype="multipart/form-data" id="form-incidencia">
            @csrf

            {{-- Herramienta --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Herramienta Afectada *</label>
                <select id="sel_herramienta" name="herramienta_id" required style="width:100%;">
                    <option value="">Buscar herramienta por código o nombre...</option>
                    @foreach($herramientas as $h)
                    <option value="{{ $h->id }}" data-stock="{{ $h->stock_disponible }}" data-estado="{{ $h->estado }}"
                        {{ old('herramienta_id', request('herramienta_id')) == $h->id ? 'selected' : '' }}>
                        {{ $h->codigo }} — {{ $h->nombre }} (Disp: {{ $h->stock_disponible }} | {{ $h->estado }})
                    </option>
                    @endforeach
                </select>
                <div id="herr-info" style="display:none; align-items:center; gap:.6rem; margin-top:.4rem; padding:.5rem .7rem; border-radius:8px; background:rgba(13,148,136,.07); border:1px solid rgba(13,148,136,.3); font-size:.8rem;">
                    <i class="fa-solid fa-circle-info" style="color:var(--primary-color);"></i>
                    <span id="herr-info-text"></span>
                </div>
            </div>

            {{-- Trabajador --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Trabajador Responsable</label>
                <select id="sel_trabajador" name="trabajador_id" style="width:100%;">
                    <option value="">Ninguno / No determinado</option>
                    @foreach($trabajadores as $t)
                    <option value="{{ $t->id }}" {{ old('trabajador_id', request('trabajador_id')) == $t->id ? 'selected' : '' }}>
                        {{ $t->dni }} — {{ $t->nombre }} {{ $t->apellidos }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo de Falta --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Tipo de Falta *</label>
                <div class="inc-tipo-grid" id="tipo-grid">
                    @php
                    $tipos = [
                        ['val' => 'Retraso en devolución',   'ico' => '⏰', 'label' => 'Retraso Devolución'],
                        ['val' => 'Herramienta sucia',       'ico' => '🧹', 'label' => 'Hta. Sucia'],
                        ['val' => 'Daño por mal uso',         'ico' => '💥', 'label' => 'Daño Mal Uso'],
                        ['val' => 'Herramienta rota',         'ico' => '🔨', 'label' => 'Hta. Rota'],
                        ['val' => 'Herramienta perdida',      'ico' => '❓', 'label' => 'Hta. Perdida'],
                        ['val' => 'Préstamo no autorizado',   'ico' => '🚫', 'label' => 'Préstamo No Autorizado'],
                        ['val' => 'Ocultamiento de daños',    'ico' => '🤫', 'label' => 'Ocultó Daños'],
                        ['val' => 'Alteración de registros',  'ico' => '📝', 'label' => 'Alteró Registros'],
                    ];
                    @endphp
                    @foreach($tipos as $t)
                    <label class="inc-tipo-card" data-tipo="{{ $t['val'] }}">
                        <input type="radio" name="tipo_falta" value="{{ $t['val'] }}" {{ old('tipo_falta') == $t['val'] ? 'checked' : '' }}>
                        <span class="ico">{{ $t['ico'] }}</span>
                        <span>{{ $t['label'] }}</span>
                    </label>
                    @endforeach
                </div>
                <input type="hidden" name="tipo_falta" id="tipo_falta_hidden" value="{{ old('tipo_falta') }}">

                {{-- Recommended action preview --}}
                <div id="accion-preview">
                    <i class="fa-solid fa-lightbulb mr-1"></i>
                    <strong>Gravedad sugerida:</strong> <span id="prev-gravedad">—</span> |
                    <strong>Acción:</strong> <span id="prev-accion">—</span>
                </div>
            </div>

            {{-- Unidades afectadas --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Unidades Afectadas</label>
                <input type="number" id="cant_afectada" name="cantidad_afectada" class="form-control" min="1" value="{{ old('cantidad_afectada', 1) }}" required>
            </div>

            {{-- Descripción --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Descripción Detallada</label>
                <textarea name="descripcion_incidencia" class="form-control" rows="3" placeholder="Describe lo sucedido con detalle...">{{ old('descripcion_incidencia') }}</textarea>
            </div>

            {{-- Evidencia --}}
            <div class="form-group">
                <label style="font-size:.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Evidencia (foto / documento)</label>
                <div class="drop-zone" id="drop-zone" onclick="document.getElementById('evidencia-input').click()">
                    <div class="dz-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div class="dz-text">Haz clic o arrastra un archivo aquí<br><small>JPG, PNG, PDF, DOC — Máx 5MB</small></div>
                    <div class="dz-filename" id="dz-filename"></div>
                </div>
                <input type="file" id="evidencia-input" name="evidencia" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            </div>

            <button type="submit" id="btnSubmit" class="btn btn-primary" style="width:100%; padding:.9rem; font-size:1rem;">
                <i class="fa-solid fa-shield-halved mr-1"></i> Registrar Sanción
            </button>
        </form>
    </div>
    @endif

</div>

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────────── --}}
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
<script>
const REGLAS = {
    'Retraso en devolución':   { gravedad: 'Leve',      accion: 'Advertencia verbal (o amonestación escrita si es reincidente)' },
    'Herramienta sucia':       { gravedad: 'Leve',      accion: 'Advertencia verbal (o amonestación escrita si es reincidente)' },
    'Daño por mal uso':        { gravedad: 'Grave',     accion: 'Capacitación obligatoria + Suspensión temporal de préstamos' },
    'Herramienta rota':        { gravedad: 'Grave',     accion: 'Capacitación obligatoria + Suspensión temporal de préstamos' },
    'Herramienta perdida':     { gravedad: 'Grave',     accion: 'Investigación + Restricción temporal de acceso al almacén' },
    'Préstamo no autorizado':  { gravedad: 'Grave',     accion: 'Suspensión de permiso para retirar herramientas' },
    'Ocultamiento de daños':   { gravedad: 'Grave',     accion: 'Suspensión de permiso para retirar herramientas' },
    'Alteración de registros': { gravedad: 'Muy grave', accion: 'Generar reporte disciplinario formal' },
};

$(document).ready(function () {
    // Select2 init
    $('#sel_herramienta').select2({ placeholder: 'Buscar herramienta...', width: '100%' });
    $('#sel_trabajador').select2({ placeholder: 'Ninguno / No determinado', allowClear: true, width: '100%' });
    $('#filter-trabajador').select2({ placeholder: '— Todos —', allowClear: true, width: '100%' });

    // Herramienta info
    $('#sel_herramienta').on('change', function () {
        let opt = $(this).find(':selected');
        let stock = opt.data('stock');
        let estado = opt.data('estado');
        if (stock !== undefined) {
            $('#cant_afectada').attr('max', stock);
            if (stock <= 0) {
                $('#herr-info').css({ display: 'flex', background: 'rgba(239,68,68,.1)', borderColor: '#EF4444' });
                $('#herr-info-text').html('<strong style="color:#EF4444;">Sin stock disponible</strong>. Esta herramienta ya no tiene unidades operativas.');
                $('#btnSubmit').prop('disabled', true).css('opacity', '.5');
            } else {
                $('#herr-info').css({ display: 'flex', background: 'rgba(13,148,136,.07)', borderColor: 'rgba(13,148,136,.3)' });
                $('#herr-info-text').html(`Stock: <strong>${stock}</strong> | Estado: <strong>${estado}</strong>`);
                $('#btnSubmit').prop('disabled', false).css('opacity', '1');
            }
        } else {
            $('#herr-info').hide();
        }
    });

    // Tipo de falta cards
    $('.inc-tipo-card').on('click', function () {
        $('.inc-tipo-card').removeClass('selected');
        $(this).addClass('selected');
        let tipo = $(this).data('tipo');
        $('#tipo_falta_hidden').val(tipo);
        // Update preview
        if (REGLAS[tipo]) {
            $('#prev-gravedad').text(REGLAS[tipo].gravedad);
            $('#prev-accion').text(REGLAS[tipo].accion);
            $('#accion-preview').show();
        }
    });

    // Mark selected on load (for old() restore)
    let oldTipo = $('#tipo_falta_hidden').val();
    if (oldTipo) {
        $(`[data-tipo="${oldTipo}"]`).addClass('selected');
        if (REGLAS[oldTipo]) {
            $('#prev-gravedad').text(REGLAS[oldTipo].gravedad);
            $('#prev-accion').text(REGLAS[oldTipo].accion);
            $('#accion-preview').show();
        }
    }

    // Herramienta change on load
    if ($('#sel_herramienta').val()) {
        $('#sel_herramienta').trigger('change');
    }

    // Evidence drop-zone
    let dropZone = document.getElementById('drop-zone');
    let fileInput = document.getElementById('evidencia-input');
    if (dropZone && fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files[0]) {
                document.getElementById('dz-filename').textContent = this.files[0].name;
                document.getElementById('dz-filename').style.display = 'block';
            }
        });
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault(); dropZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            if (fileInput.files[0]) {
                document.getElementById('dz-filename').textContent = fileInput.files[0].name;
                document.getElementById('dz-filename').style.display = 'block';
            }
        });
    }
});
</script>
@endsection
