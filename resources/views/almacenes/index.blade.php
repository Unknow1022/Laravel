@extends('layouts.app')

@section('title', 'Control de Nodos Logísticos')

@section('content')
<div class="logistics-header mb-5">
    <div class="header-main">
        <h1 class="premium-title"><i class="fa-solid fa-network-wired mr-3"></i>Red de Almacenes</h1>
        <p class="premium-subtitle">Gestión centralizada de infraestructuras, categorías y flujos de inventario.</p>
    </div>
    @if(Auth::user()->rol === 'Administrador')
    <button class="btn-premium" data-toggle="modal" data-target="#modalCrear">
        <i class="fa-solid fa-plus-circle mr-2"></i>Registrar Nuevo Nodo
    </button>
    @endif
</div>

<div class="nodes-grid">
    @foreach($almacenes as $almacen)
    @php
        $totalTools = \App\Models\Herramienta::sum('stock_total') ?: 1;
        $pctLoad = ($almacen->herramientas_count / $totalTools) * 100;
        $esSobrecargado = $pctLoad > 80;
        $tieneAgotadas = $almacen->herramientas()->where('stock_disponible', '<=', 0)->exists();
        $tieneAlerta = $esSobrecargado || $tieneAgotadas;
    @endphp
    <div class="node-card" style="{{ $tieneAlerta ? 'border-color: #F43F5E; box-shadow: 0 10px 30px rgba(244, 63, 94, 0.15); background: linear-gradient(135deg, rgba(244, 63, 94, 0.05) 0%, rgba(10, 25, 47, 0.4) 100%);' : '' }}">
        <div class="node-status">
            <span class="status-indicator" style="{{ $tieneAlerta ? 'background: #F43F5E; box-shadow: 0 0 10px #F43F5E;' : 'background: var(--primary-color); box-shadow: 0 0 10px var(--primary-color);' }}"></span>
            <div class="d-flex gap-2 align-items-center">
                <span class="active-count">{{ $almacen->herramientas_count }} Activos</span>
                @if($esSobrecargado)
                    <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: 1px solid #F43F5E; font-size: 0.7rem; font-weight: bold; padding: 2px 6px;">
                        SOBRECARGADO
                    </span>
                @elseif($tieneAgotadas)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B; border: 1px solid #F59E0B; font-size: 0.7rem; font-weight: bold; padding: 2px 6px;">
                        STOCK EN CERO
                    </span>
                @endif
            </div>
        </div>
        
        <div class="node-body">
            <div class="node-icon" style="{{ $tieneAlerta ? 'color: #F43F5E; border-color: rgba(244,63,94,0.3); background: rgba(244,63,94,0.05);' : '' }}">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div class="node-info">
                <h3 class="node-name">{{ $almacen->nombre }}</h3>
                <div class="node-meta">
                    <span class="meta-item"><i class="fa-solid fa-location-dot"></i> {{ $almacen->ubicacion_general ?: 'Localización pendiente' }}</span>
                </div>
                <p class="node-desc">{{ Str::limit($almacen->descripcion, 100) }}</p>
            </div>
        </div>

        <div class="node-actions">
            <a href="{{ route('almacenes.categorias.index', $almacen->id) }}" class="action-link" style="{{ $tieneAlerta ? 'color: #F43F5E;' : '' }}">
                <i class="fa-solid fa-tags"></i> Gestionar Categorías
            </a>
            <div class="action-btns">
                @if(Auth::user()->rol === 'Administrador')
                <button class="btn-icon warn" data-toggle="modal" data-target="#modalEdit{{ $almacen->id }}" title="Editar">
                    <i class="fa-solid fa-pen-nib"></i>
                </button>
                <form action="{{ route('almacenes.destroy', $almacen->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon danger" onclick="return confirm('¿Confirmar baja del nodo?')">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- MODALS -->
@foreach($almacenes as $almacen)
    @if(Auth::user()->rol === 'Administrador')
    <div class="modal fade" id="modalEdit{{ $almacen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('almacenes.update', $almacen->id) }}" method="POST" class="modal-content premium-modal">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title text-primary"><i class="fa-solid fa-edit mr-2"></i>Configurar Nodo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group-premium">
                        <label>Identificador del Almacén</label>
                        <input type="text" name="nombre" class="form-input" value="{{ $almacen->nombre }}" required>
                    </div>
                    <div class="form-group-premium">
                        <label>Coordenadas / Ubicación</label>
                        <input type="text" name="ubicacion_general" class="form-input" value="{{ $almacen->ubicacion_general }}">
                    </div>
                    <div class="form-group-premium">
                        <label>Especificaciones Técnicas</label>
                        <textarea name="descripcion" class="form-input" rows="3">{{ $almacen->descripcion }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-dismiss="modal">Abortar</button>
                    <button type="submit" class="btn-save">Actualizar Nodo</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@if(Auth::user()->rol === 'Administrador')
<div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('almacenes.store') }}" method="POST" class="modal-content premium-modal">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title text-primary"><i class="fa-solid fa-plus-circle mr-2"></i>Nuevo Nodo Operativo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group-premium">
                    <label>Nombre del Almacén</label>
                    <input type="text" name="nombre" class="form-input" placeholder="Ej. Almacén Central" required>
                </div>
                <div class="form-group-premium">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion_general" class="form-input" placeholder="Ej. Planta Sur, Sector A">
                </div>
                <div class="form-group-premium">
                    <label>Descripción Operativa</label>
                    <textarea name="descripcion" class="form-input" rows="3" placeholder="Tipo de activos, capacidad, etc..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-save">Desplegar Nodo</button>
            </div>
        </form>
    </div>
</div>
@endif

<style>
/* CUSTOM LOGISTICS STYLES */
.logistics-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem; }
.premium-title { color: var(--primary-color); font-weight: 800; font-size: 2.2rem; margin: 0; }
.premium-subtitle { color: var(--text-muted); margin-top: 0.5rem; font-size: 1.1rem; }

.btn-premium { background: var(--primary-color); color: var(--bg-color); border: none; padding: 0.8rem 1.8rem; border-radius: 12px; font-weight: 800; transition: all 0.3s; cursor: pointer; }
.btn-premium:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(100, 255, 218, 0.3); }

.nodes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; }

.node-card { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
.node-card:hover { border-color: var(--primary-color); transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.4); }

.node-status { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.status-indicator { width: 10px; height: 10px; border-radius: 50%; background: var(--primary-color); box-shadow: 0 0 10px var(--primary-color); }
.active-count { color: var(--primary-color); font-weight: 800; font-size: 0.85rem; background: rgba(100, 255, 218, 0.1); padding: 4px 12px; border-radius: 50px; }

.node-body { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; }
.node-icon { width: 60px; height: 60px; background: rgba(100, 255, 218, 0.05); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--primary-color); border: 1px solid rgba(100, 255, 218, 0.1); }

.node-name { color: #ccd6f6; font-weight: 800; font-size: 1.4rem; margin: 0 0 0.5rem 0; }
.node-meta { display: flex; gap: 1rem; margin-bottom: 0.8rem; }
.meta-item { color: var(--text-muted); font-size: 0.85rem; }
.node-desc { color: #8892b0; font-size: 0.9rem; line-height: 1.5; }

.node-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1.2rem; }
.action-link { color: var(--primary-color); text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: all 0.2s; }
.action-link:hover { opacity: 0.8; padding-left: 5px; }

.action-btns { display: flex; gap: 0.8rem; }
.btn-icon { background: rgba(255,255,255,0.05); border: none; width: 36px; height: 36px; border-radius: 8px; color: var(--text-muted); transition: all 0.2s; cursor: pointer; }
.btn-icon:hover { color: white; background: rgba(255,255,255,0.1); }
.btn-icon.warn:hover { color: var(--warning); background: rgba(245, 158, 11, 0.1); }
.btn-icon.danger:hover { color: var(--danger); background: rgba(244, 63, 94, 0.1); }

/* MODAL STYLES */
.premium-modal { background: #0a192f; border: 1px solid var(--border-color); border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
.modal-header { border-bottom: 1px solid var(--border-color); padding: 1.5rem; }
.form-group-premium { margin-bottom: 1.5rem; }
.form-group-premium label { display: block; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }
.form-input { width: 100%; background: #112240; border: 1px solid var(--border-color); border-radius: 10px; padding: 0.8rem 1.2rem; color: white; transition: all 0.3s; }
.form-input:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 10px rgba(100, 255, 218, 0.1); }
.btn-cancel { background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.8rem 1.5rem; border-radius: 10px; font-weight: 700; margin-right: 1rem; cursor: pointer; }
.btn-save { background: var(--primary-color); border: none; color: var(--bg-color); padding: 0.8rem 1.5rem; border-radius: 10px; font-weight: 800; cursor: pointer; }
</style>
@endsection
