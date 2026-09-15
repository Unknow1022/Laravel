@extends('layouts.app')

@section('title', 'Personal Activo')

@section('content')
<div class="d-flex mb-4" style="flex-wrap:wrap; gap:1rem;">
    <h1 class="page-title" style="margin-bottom:0; flex:1;">Personal Activo</h1>
    
    <div style="display:flex; gap:1rem; align-items:center;">
        <form action="{{ route('trabajadores.index') }}" method="GET" style="display: flex; gap: 0.5rem; margin: 0;">
            <input type="text" name="search" id="filtro_trabajador" class="form-control" placeholder="Buscar por DNI o Nombre..." value="{{ $busqueda }}" style="width: 250px; padding: 0.5rem;">
            <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Buscar</button>
            @if(!empty($busqueda))
                <a href="{{ route('trabajadores.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>
        
        <a href="{{ route('trabajadores.inactivos') }}" class="btn" style="background:var(--surface-color); border:1px solid var(--border-color); color:var(--text-main);">
            <i class="fa-solid fa-user-slash mr-1"></i> Ex-Trabajadores
        </a>

        @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
        <a href="{{ route('trabajadores.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus mr-1"></i> Registrar Trabajador
        </a>
        @endif
    </div>
</div>

<div class="table-container">
    <table class="table" id="tabla_trabajadores">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre Completo</th>
                <th>Cargo</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @if($trabajadores->isEmpty())
            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No hay trabajadores activos registrados.</td></tr>
            @else
                @foreach($trabajadores as $t)
                @php
                    $tieneRetraso = $t->vales()->where('estado', 'Activo')->where('fecha_limite', '<', \Carbon\Carbon::now())->exists();
                @endphp
                <tr class="trabajador-row" style="{{ $tieneRetraso ? 'background: linear-gradient(90deg, rgba(244, 63, 94, 0.08), transparent); border-left: 4px solid #F43F5E;' : '' }}">
                    <td class="col-dni" style="font-weight: 500;">{{ $t->dni }}</td>
                    <td class="col-nombre">
                        @if(session()->get('cortex_xss_protection', true))
                            {{ $t->nombre }} {{ $t->apellidos }}
                        @else
                            {!! $t->nombre !!} {!! $t->apellidos !!}
                        @endif
                        @if($tieneRetraso)
                            <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: 1px solid #F43F5E; font-size: 0.75rem; margin-left: 0.5rem; font-weight: bold; padding: 2px 6px;">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Retraso
                            </span>
                        @endif
                    </td>
                    <td>{{ $t->cargo }}</td>
                    <td>{{ $t->telefono }}</td>
                    <td>
                        <span style="color: #10B981; background: rgba(16,185,129,0.1); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;"><i class="fa-solid fa-check"></i> Activo</span>
                    </td>
                    <td>
                        <a href="{{ route('trabajadores.show', $t->id) }}" class="action-btn" title="Ver Reporte / Perfil" style="color:var(--primary-color);">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        
                        @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                        <a href="{{ route('trabajadores.edit', $t->id) }}" class="action-btn" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        @endif
                        
                        @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                        <form action="{{ route('trabajadores.destroy', $t->id) }}" method="POST" style="display:inline-block;"
                              onsubmit="return confirm('Dar de baja a: {{ addslashes($t->nombre) }} {{ addslashes($t->apellidos) }} (DNI: {{ $t->dni }}). Esta accion quedara registrada en el log de auditoria.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Dar de baja al trabajador" style="border:none; cursor:pointer;">
                                <i class="fa-solid fa-user-minus"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

@if($trabajadores->hasPages())
<div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; margin-bottom: 2rem;">
    {{ $trabajadores->appends(['search' => $busqueda])->links('vendor.pagination.custom') }}
</div>
@endif

@endsection
