@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="d-flex mb-4" style="justify-content: space-between; align-items: center;">
    <h1 class="page-title" style="margin-bottom:0;">
        <i class="fa-solid fa-users-cog" style="color: var(--primary-color);"></i> Gestión de Usuarios
    </h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary" style="width: auto;">
        <i class="fa-solid fa-user-plus mr-1"></i> Crear Usuario
    </a>
</div>

@if(session('creado'))
    <div class="alert" style="background: rgba(16,185,129,0.1); color: #10B981; border: 1px solid #10B981;">
        <i class="fa-solid fa-check-circle"></i> Usuario creado exitosamente.
    </div>
@endif

@if(session('editado'))
    <div class="alert" style="background: rgba(56,189,248,0.1); color: #38BDF8; border: 1px solid #38BDF8;">
        <i class="fa-solid fa-pen"></i> Usuario actualizado exitosamente.
    </div>
@endif

@if(session('eliminado'))
    <div class="alert" style="background: rgba(239,68,68,0.1); color: #EF4444; border: 1px solid #EF4444;">
        <i class="fa-solid fa-trash"></i> Usuario eliminado del sistema.
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

<div style="background: rgba(100,255,218,0.05); border: 1px solid rgba(100,255,218,0.2); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
    <p style="color: var(--text-muted); margin:0; font-size: 0.875rem;">
        <i class="fa-solid fa-shield-halved" style="color: var(--primary-color);"></i>
        <strong style="color: var(--primary-color);">Zona restringida.</strong>
        Solo el Administrador puede crear, editar o eliminar usuarios del sistema.
    </p>
</div>

<div class="table-container" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color);">#</th>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color);">Nombre</th>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color);">Usuario</th>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color);">Rol</th>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color);">Creado</th>
                <th style="padding: 1rem; background: #112240; color: #8892B0; border-bottom: 1px solid var(--border-color); text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $u)
                @php
                    $rolColors = [
                        'Administrador' => ['bg' => 'rgba(239,68,68,0.15)',   'color' => '#EF4444', 'icon' => 'fa-user-shield'],
                        'Almacenero'    => ['bg' => 'rgba(245,158,11,0.15)',  'color' => '#F59E0B', 'icon' => 'fa-user-gear'],
                        'Supervisor'    => ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#818CF8', 'icon' => 'fa-user-tie'],
                    ];
                    $rc = $rolColors[$u->rol] ?? ['bg' => '#333', 'color' => '#888', 'icon' => 'fa-user'];
                    $esMiCuenta = ($u->id === Auth::id());
                @endphp
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem; color: var(--text-muted);">{{ $u->id }}</td>
                    <td style="padding: 1rem; color: var(--text-main); font-weight: 500;">
                        {{ $u->nombre }}
                        @if($esMiCuenta)
                            <span style="font-size: 0.75rem; color: var(--primary-color); margin-left: 0.5rem;">(tú)</span>
                        @endif
                    </td>
                    <td style="padding: 1rem; color: #8892B0; font-family: monospace;">
                        <i class="fa-solid fa-at" style="font-size: 0.75rem;"></i> {{ $u->usuario }}
                    </td>
                    <td style="padding: 1rem;">
                        <span style="background: {{ $rc['bg'] }}; color: {{ $rc['color'] }}; padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fa-solid {{ $rc['icon'] }}"></i> {{ $u->rol }}
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                        {{ $u->creado_en ? date('d/m/Y', strtotime($u->creado_en)) : 'N/A' }}
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <a href="{{ route('usuarios.edit', $u->id) }}"
                           style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(56,189,248,0.1); color: #38BDF8; border: 1px solid #38BDF8; padding: 0.4rem 0.85rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 0.5rem;">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </a>
                        @if(!$esMiCuenta)
                        <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" style="display:inline-block; margin:0;"
                              onsubmit="return confirm('Eliminar al {{ $u->rol }}: {{ addslashes($u->nombre) }} (@{{ $u->usuario }}). Esta accion quedara registrada en el log y no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(239,68,68,0.1); color: #EF4444; border: 1px solid #EF4444; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.85rem; cursor:pointer;">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay usuarios registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
