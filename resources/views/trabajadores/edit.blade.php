@extends('layouts.app')

@section('title', 'Editar Trabajador')

@section('content')
<div class="d-flex mb-4">
    <h1 class="page-title" style="margin-bottom:0;">Editar Trabajador</h1>
    <a href="{{ route('trabajadores.index') }}" class="btn" style="background:#172A45; color:var(--text-main); width:auto;">
        <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la lista
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<div class="card" style="background: var(--surface-color); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color); max-width: 600px;">
    <form action="{{ route('trabajadores.update', $trabajador->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="dni">DNI / Documento de Identidad</label>
            <input type="text" id="dni" name="dni" class="form-control" value="{{ old('dni', $trabajador->dni) }}" required autofocus maxlength="8" pattern="[0-9]{8}" inputmode="numeric" title="El DNI debe contener exactamente 8 números">
            <small class="text-muted">Debe ingresar exactamente 8 dígitos numéricos.</small>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre(s)</label>
            <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $trabajador->nombre) }}" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" class="form-control" value="{{ old('apellidos', $trabajador->apellidos) }}" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono de Contacto</label>
            <input type="text" id="telefono" name="telefono" class="form-control" value="{{ old('telefono', $trabajador->telefono) }}" maxlength="20">
        </div>

        <div class="form-group">
            <label for="cargo">Cargo o Especialidad</label>
            <input type="text" id="cargo" name="cargo" class="form-control" value="{{ old('cargo', $trabajador->cargo) }}" placeholder="Ej: Operario, Electricista, etc." maxlength="100">
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save mr-1"></i> Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
