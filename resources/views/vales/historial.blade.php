@extends('layouts.app')

@section('title', 'Registro de Vales (Historial)')

@section('content')
<div class="d-flex mb-4">
    <h1 class="page-title" style="margin-bottom:0;">Registro de Vales (Historial)</h1>
</div>

<div class="card" style="background: var(--surface-color); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 2rem;">
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <label for="filtro_trabajador" style="color: var(--text-muted); font-size: 0.85rem;">Buscar por Trabajador</label>
            <input type="text" id="filtro_trabajador" class="form-control" placeholder="Ej. Juan Pérez" style="width: 100%;">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label for="filtro_estado" style="color: var(--text-muted); font-size: 0.85rem;">Estado del Vale</label>
            <select id="filtro_estado" class="form-control" style="width: 100%;">
                <option value="">Todos los Estados</option>
                <option value="Activo">Activo</option>
                <option value="Parcial">Parcial</option>
                <option value="Devuelto">Devuelto</option>
            </select>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="table" id="tabla_historial">
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
            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No hay vales registrados en el historial.</td></tr>
            @else
                @foreach($vales as $v)
                @php
                    $esVencido = $v->estado === 'Activo' && $v->fecha_limite && (strtotime($v->fecha_limite) < time());
                @endphp
                <tr class="historial-row" style="{{ $esVencido ? 'background: linear-gradient(90deg, rgba(244, 63, 94, 0.08), transparent); border-left: 4px solid #F43F5E;' : '' }}">
                    <td class="col-codigo" style="font-weight: bold; color: var(--primary-color);">{{ $v->codigo_vale }}</td>
                    <td class="col-fecha">{{ \Carbon\Carbon::parse($v->fecha_creacion)->format('d/m/Y H:i') }}</td>
                    <td class="col-trabajador">{{ $v->trabajador->nombre }} {{ $v->trabajador->apellidos }}</td>
                    <td><small style="color:var(--text-muted);"><i class="fa-solid fa-user-shield"></i> {{ $v->usuario->nombre }}</small></td>
                    <td class="col-estado">
                        <span class="badge {{ $v->estado }}">{{ $v->estado }}</span>
                        @if($esVencido)
                            <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: 1px solid #F43F5E; font-size: 0.7rem; margin-left: 0.5rem; font-weight: bold; padding: 2px 6px;">
                                RETRASADO
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('vales.show', $v->id) }}" class="action-btn" title="Ver Detalles y Herramientas" style="color:var(--primary-color);">
                            <i class="fa-solid fa-folder-open"></i> Abrir Expediente
                        </a>
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputTrabajador = document.getElementById('filtro_trabajador');
    const selectEstado = document.getElementById('filtro_estado');
    const rows = document.querySelectorAll('.historial-row');

    function filtrar() {
        const query = inputTrabajador.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        const estado = selectEstado.value.toLowerCase().trim();

        rows.forEach(row => {
            const colTrabajador = row.querySelector('.col-trabajador').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const colEstado = row.querySelector('.col-estado').textContent.toLowerCase().trim();

            const matchTrabajador = colTrabajador.includes(query);
            const matchEstado = (estado === "" || colEstado.includes(estado));

            if (matchTrabajador && matchEstado) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    if (inputTrabajador) inputTrabajador.addEventListener('input', filtrar);
    if (selectEstado) selectEstado.addEventListener('change', filtrar);
});
</script>
@endsection
