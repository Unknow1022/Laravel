@extends('layouts.app')

@section('title', 'Generar Vale de Salida')

@section('content')
<div class="d-flex mb-4">
    <h1 class="page-title" style="margin-bottom:0;">Generar Vale de Salida</h1>
    <a href="{{ route('vales.index') }}" class="btn" style="background:#172A45; color:var(--text-main); width:auto;">
        <i class="fa-solid fa-arrow-left mr-1"></i> Volver
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('vales.store') }}" method="POST">
    @csrf

    <div style="display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Panel Trabajador -->
        <div class="card" style="flex:1; min-width:300px; background: var(--surface-color); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 1rem; color:var(--primary-color);">1. Datos del Solicitante</h3>
            <div class="form-group">
                <label for="trabajador_id">Seleccionar Trabajador</label>
                <select id="trabajador_id" name="trabajador_id" class="form-control" required>
                    <option value="">-- Seleccione un trabajador --</option>
                    @foreach($trabajadores as $t)
                        @php $suspendido = in_array($t->id, $suspendidosIds ?? []); @endphp
                        <option value="{{ $t->id }}"
                            data-suspendido="{{ $suspendido ? '1' : '0' }}"
                            {{ old('trabajador_id') == $t->id ? 'selected' : '' }}
                            style="{{ $suspendido ? 'color: #EF4444;' : '' }}">
                            {{ $t->dni }} - {{ $t->nombre }} {{ $t->apellidos }}{{ $suspendido ? ' ⚠️ SUSPENDIDO' : '' }}
                        </option>
                    @endforeach
                </select>
                <div id="suspension-alert" style="display:none; margin-top:.5rem; padding:.7rem 1rem; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.4); border-radius:8px; color:#EF4444; font-size:.85rem;">
                    <i class="fa-solid fa-ban mr-1"></i> <strong>Trabajador suspendido.</strong> Tiene una sanción disciplinaria activa. No puede retirar herramientas.
                </div>
            </div>
        </div>

        <!-- Panel Herramientas (Carrito) -->
        <div class="card" style="flex:2; min-width:400px; background: var(--surface-color); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; color:var(--primary-color);">2. Selección de Herramientas</h3>
                <input type="text" id="buscar_herramienta" class="form-control" placeholder="Buscar herramienta..." style="width: 250px; padding: 0.5rem;">
            </div>
            <div class="table-container" style="max-height: 400px; overflow-y:auto; margin-bottom: 1rem;">
                <table class="table" style="font-size: 0.9rem;" id="tabla_herramientas">
                    <thead>
                        <tr>
                            <th>Herramienta</th>
                            <th>Stock Disp.</th>
                            <th>Cantidad a Prestar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($herramientas as $h)
                        <tr class="herramienta-row">
                            <td class="herramienta-nombre">
                                <strong>{{ $h->codigo }}</strong> - {{ $h->nombre }}
                            </td>
                            <td><span style="color:var(--primary-color); font-weight:bold;">{{ $h->stock_disponible }}</span></td>
                            <td>
                                <input type="number" name="cantidades[{{ $h->id }}]" class="form-control cant-input" style="width: 80px; padding: 0.25rem;" min="0" max="{{ $h->stock_disponible }}" value="{{ old('cantidades.'.$h->id, 0) }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                <i class="fa-solid fa-file-invoice mr-1"></i> Emitir Vale de Salida
            </button>
        </div>
    </div>
</form>

<!-- jQuery and Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>

<style>
/* Custom Select2 Dark Theme to match the site */
.select2-container--default .select2-selection--single {
    background-color: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    height: 40px;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--text-main);
}
.select2-dropdown {
    background-color: var(--surface-color);
    border: 1px solid var(--border-color);
}
.select2-container--default .select2-results__option--selected {
    background-color: var(--primary-color);
    color: #fff;
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: #172A45;
    color: var(--text-main);
}
.select2-search--dropdown .select2-search__field {
    background-color: #0A192F;
    color: var(--text-main);
    border: 1px solid var(--border-color);
}
</style>

<script>
$(document).ready(function() {
    // Inicializar Select2 para trabajadores
    $('#trabajador_id').select2({
        placeholder: '-- Seleccione un trabajador --',
        allowClear: true,
        width: '100%'
    });

    // Alerta de suspensión al seleccionar trabajador
    $('#trabajador_id').on('change', function() {
        let opt = $(this).find(':selected');
        let suspendido = opt.data('suspendido');
        if (suspendido == '1') {
            $('#suspension-alert').show();
            $('button[type="submit"]').prop('disabled', true).css('opacity', '.5');
        } else {
            $('#suspension-alert').hide();
            $('button[type="submit"]').prop('disabled', false).css('opacity', '1');
        }
    });

    // Filtro de búsqueda rápida para herramientas
    $('#buscar_herramienta').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#tabla_herramientas tbody tr").filter(function() {
            // Mostrar si coincide el texto, o si tiene una cantidad mayor a 0 (para no perder lo que ya se seleccionó)
            var cant = parseInt($(this).find('.cant-input').val()) || 0;
            var textMatch = $(this).find('.herramienta-nombre').text().toLowerCase().indexOf(value) > -1;
            $(this).toggle(textMatch || cant > 0);
        });
    });
});
</script>
@endsection
