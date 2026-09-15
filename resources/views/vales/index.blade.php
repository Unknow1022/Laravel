@extends('layouts.app')

@section('title', 'Vales de Salida')

@section('content')
<div class="d-flex mb-4">
    <h1 class="page-title" style="margin-bottom:0;">Vales de Salida</h1>
    @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
    <div style="display:flex; gap:1rem;">
        <a href="{{ route('vales.create') }}" class="btn btn-primary" style="width: auto;">
            <i class="fa-solid fa-plus mr-1"></i> Generar Vale
        </a>
        <a href="{{ route('vales.devolver_form') }}" class="btn" style="background:#F59E0B; color:#fff; width: auto; border:none;">
            <i class="fa-solid fa-barcode mr-1"></i> Devolución Rápida
        </a>
    </div>
    @endif
</div>

@if (session('exito') || request()->has('exito'))
    <div class="alert" style="background: rgba(16,185,129,0.1); color: #10B981; border: 1px solid #10B981;">
        <i class="fa-solid fa-check-circle"></i> Vale generado exitosamente.
    </div>
@endif

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Código Vale</th>
                <th>Fecha Emisión</th>
                <th>Trabajador</th>
                <th>Emitido por</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @if($vales->isEmpty())
            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No hay vales registrados.</td></tr>
            @else
                @foreach($vales as $v)
                @php
                    $esVencido = $v->estado === 'Activo' && $v->fecha_limite && (strtotime($v->fecha_limite) < time());
                @endphp
                <tr style="{{ $esVencido ? 'background: linear-gradient(90deg, rgba(244, 63, 94, 0.08), transparent); border-left: 4px solid #F43F5E;' : '' }}">
                    <td style="font-weight: bold; color: var(--primary-color);">{{ $v->codigo_vale }}</td>
                    <td>{{ \Carbon\Carbon::parse($v->fecha_creacion)->format('d/m/Y H:i') }}</td>
                    <td>{{ $v->trabajador->nombre }} {{ $v->trabajador->apellidos }}</td>
                    <td><small style="color:var(--text-muted);"><i class="fa-solid fa-user-shield"></i> {{ $v->usuario->nombre }}</small></td>
                    <td>
                        <span class="badge {{ $v->estado }}">{{ $v->estado }}</span>
                        @if($esVencido)
                            <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: 1px solid #F43F5E; font-size: 0.7rem; margin-left: 0.5rem; font-weight: bold; padding: 2px 6px;">
                                RETRASADO
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('vales.show', $v->id) }}" class="action-btn" title="Ver e Imprimir">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>
                        @if($v->estado !== 'Devuelto' && in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                        <a href="{{ route('vales.procesar_devolucion', $v->id) }}" class="action-btn" title="Procesar Devolución" style="color:var(--warning);">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endsection
