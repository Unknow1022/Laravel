@extends('errors.layout')

@section('title', 'Acceso Denegado')

@section('content')
<div style="text-align: center; padding: 4rem 2rem;">
    <div style="font-size: 5rem; color: #EF4444; margin-bottom: 1rem;">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    <h1 style="font-size: 2rem; color: var(--text-main); margin-bottom: 0.5rem;">403 — Acceso Denegado</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">
        No tienes permisos para acceder a esta sección del sistema.
    </p>
    <a href="{{ route('login') }}"
       style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(100,255,218,0.1); color: var(--primary-color); border: 1px solid var(--primary-color); padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none;">
        <i class="fa-solid fa-arrow-left"></i> Volver al inicio
    </a>
</div>
@endsection
