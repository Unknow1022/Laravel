@extends('layouts.app')

@section('title', 'Registrar Nueva Herramienta')

@section('content')
<div class="d-flex mb-4" style="align-items: center; justify-content: space-between;">
    <h1 class="page-title" style="margin-bottom:0;">Registrar Nueva Herramienta</h1>
    <a href="{{ route('herramientas.index') }}" class="btn" style="background:#172A45; color:var(--text-main); width:auto;">
        <i class="fa-solid fa-arrow-left mr-1"></i> Volver al catálogo
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<div class="card" style="background: var(--surface-color); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color); max-width: 600px;">
    <form action="{{ route('herramientas.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="codigo">Código Único</label>
            <input type="text" id="codigo" name="codigo" class="form-control" value="Auto-generado" readonly style="background-color: transparent; color: var(--text-muted); cursor: not-allowed; border: 1px dashed var(--border-color);">
        </div>

        <div class="form-group">
            <label for="nombre">Nombre de Herramienta</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="150" value="{{ old('nombre') }}">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción / Marca / Modelo</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex:1;">
                <label for="almacen_id">Almacén de Destino</label>
                <select id="almacen_id" name="almacen_id" class="form-control" required onchange="filterCategories()">
                    <option value="">Seleccione Almacén...</option>
                    @foreach($almacenes as $alm)
                        <option value="{{ $alm->id }}" {{ old('almacen_id') == $alm->id ? 'selected' : '' }}>{{ $alm->nombre }}</option>
                    @endforeach
                </select>
                @if($almacenes->isEmpty())
                    <small style="color:#f59e0b; margin-top:4px; display:block;">
                        ⚠ No hay almacenes creados. <a href="{{ route('almacenes.create') }}" style="color:#64ffda;">Crear almacén primero</a>
                    </small>
                @endif
            </div>
            <div class="form-group" style="flex:1;">
                <label for="categoria_id">Categoría (Función)</label>
                <select id="categoria_id" name="categoria_id" class="form-control" required disabled
                    style="background: var(--surface-color, #0d1b2a); color: var(--text-muted, #8892b0);
                           border: 1px solid var(--border-color, #1e3a5f); opacity: 0.7; cursor: not-allowed;">
                    <option value="">Primero elija un almacén</option>
                </select>
            </div>
        </div>


        <div class="form-group">
            <label for="ubicacion">Ubicación Exacta (Estante / Zona)</label>
            <input type="text" id="ubicacion" name="ubicacion" class="form-control" required maxlength="100" value="{{ old('ubicacion') }}" placeholder="Ej. Estante A-4, Gaveta 2">
        </div>

        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex:1;">
                <label for="tamano">Tamaño / Medida</label>
                <input type="text" id="tamano" name="tamano" class="form-control" maxlength="50" value="{{ old('tamano') }}" placeholder="Ej. 10mm, Grande, 1/2 pulgada">
            </div>
            <div class="form-group" style="flex:1;">
                <label for="uso">Uso / Propósito</label>
                <input type="text" id="uso" name="uso" class="form-control" maxlength="200" value="{{ old('uso') }}" placeholder="Ej. Perforación, Corte">
            </div>
        </div>

        <div class="form-group">
            <label for="estado">Estado Inicial</label>
            <select id="estado" name="estado" class="form-control" required>
                <option value="Disponible" {{ old('estado') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="Mantenimiento" {{ old('estado') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="Dañado" {{ old('estado') == 'Dañado' ? 'selected' : '' }}>Dañado</option>
                <option value="Falla técnica" {{ old('estado') == 'Falla técnica' ? 'selected' : '' }}>Falla técnica</option>
            </select>
        </div>

        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex:1;">
                <label for="stock_total">Stock Inicial</label>
                <input type="number" id="stock_total" name="stock_total" class="form-control" value="{{ old('stock_total', 1) }}" min="1" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label for="stock_minimo">Stock Crítico (Alerta)</label>
                <input type="number" id="stock_minimo" name="stock_minimo" class="form-control" value="{{ old('stock_minimo', 2) }}" min="0" required>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa-solid fa-save mr-2"></i>Registrar Herramienta</button>
        </div>
    </form>
</div>

<script>
    const data = @json($almacenes);

    function filterCategories() {
        const almacenId = document.getElementById('almacen_id').value;
        const categoriaSelect = document.getElementById('categoria_id');

        categoriaSelect.innerHTML = '<option value="">Seleccione Categoría...</option>';

        if (almacenId) {
            const almacen = data.find(a => a.id == almacenId);
            if (almacen && almacen.categorias && almacen.categorias.length > 0) {
                almacen.categorias.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.nombre;
                    categoriaSelect.appendChild(option);
                });
                categoriaSelect.disabled = false;
                categoriaSelect.style.opacity = '1';
                categoriaSelect.style.cursor = 'pointer';
                categoriaSelect.style.color = 'var(--text-main, #ccd6f6)';
            } else {
                categoriaSelect.innerHTML = '<option value="">⚠ Sin categorías — crea una primero</option>';
                categoriaSelect.disabled = true;
                categoriaSelect.style.opacity = '0.7';
                categoriaSelect.style.cursor = 'not-allowed';
            }
        } else {
            categoriaSelect.innerHTML = '<option value="">Primero elija un almacén</option>';
            categoriaSelect.disabled = true;
            categoriaSelect.style.opacity = '0.7';
            categoriaSelect.style.cursor = 'not-allowed';
        }
    }

    // Init si hay valores previos (al volver con error)
    window.onload = function() {
        if (document.getElementById('almacen_id').value) {
            filterCategories();
            const oldCat = "{{ old('categoria_id') }}";
            if (oldCat) document.getElementById('categoria_id').value = oldCat;
        }
    }
</script>

@endsection
