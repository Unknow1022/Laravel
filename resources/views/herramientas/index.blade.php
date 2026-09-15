@extends('layouts.app')

@section('title', 'Catálogo de Herramientas')

@section('content')
<style>
    :root {
        --glass-bg: rgba(23, 42, 69, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-glow: 0 0 20px rgba(100, 255, 218, 0.15);
    }

    .page-header {
        background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);
        padding: 2.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--accent-glow);
    }

    .search-container {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--glass-border);
        transition: all 0.3s ease;
    }

    .search-container:focus-within {
        border-color: var(--primary-color);
        box-shadow: var(--accent-glow);
    }

    .custom-table {
        border-collapse: separate;
        border-spacing: 0 8px;
        width: 100%;
    }

    .custom-table thead th {
        background: transparent;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1.5px;
        padding: 1rem;
        border: none;
    }

    .custom-table tbody tr {
        background: var(--surface-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 8px;
    }

    .custom-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        background: #1d3557;
    }

    .custom-table td {
        padding: 1.25rem 1rem;
        border-top: 1px solid var(--glass-border);
        border-bottom: 1px solid var(--glass-border);
    }

    .custom-table td:first-child {
        border-left: 1px solid var(--glass-border);
        border-radius: 8px 0 0 8px;
    }

    .custom-table td:last-child {
        border-right: 1px solid var(--glass-border);
        border-radius: 0 8px 8px 0;
    }

    .badge-modern {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .section-tag {
        background: rgba(100, 255, 218, 0.1);
        color: var(--primary-color);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .tool-img-wrap {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        background: linear-gradient(135deg, #112240, #1d3557);
        border: 1px solid rgba(100,255,218,0.2);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        flex-shrink: 0;
    }

    .tool-img-wrap:hover {
        transform: scale(1.08);
        box-shadow: 0 0 18px rgba(100,255,218,0.25);
    }

    .tool-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tool-img-wrap .no-img-icon {
        font-size: 1.6rem;
        color: rgba(100,255,218,0.3);
    }

    .tool-details-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title h2 mb-1" style="color: #64ffda; font-weight: 800;">
                <i class="fa-solid fa-layer-group mr-2"></i>Catálogo Maestro
            </h1>
            <p class="mb-0" style="color: #8892b0; font-size: 0.95rem;">Control total de inventario y ubicación por secciones.</p>
        </div>
        <div>
            @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
            <a href="{{ route('herramientas.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="fa-solid fa-plus-circle mr-2"></i>Registrar Nuevo Lote
            </a>
            @endif
        </div>
    </div>

    <!-- Buscador Avanzado -->
    <div class="search-container mb-4">
        <form action="{{ route('herramientas.index') }}" method="GET" class="row g-3" onsubmit="return false;">
            <div class="col-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent border-end-0" style="color: var(--primary-color);">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="input_busqueda" name="search" class="form-control bg-transparent border-start-0 text-white" 
                           placeholder="Buscar por Nombre, Código, Sección o Almacén en tiempo real..." value="{{ $busqueda }}" style="font-weight: 500;">
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive" style="overflow-x: auto;">
        <table class="custom-table" id="tabla_herramientas">
            <thead>
                <tr>
                    <th>Ref. Identificador</th>
                    <th>Detalles Herramienta</th>
                    <th>Sección / Almacén</th>
                    <th style="text-align: center;">Disponibilidad</th>
                    <th style="text-align: center;">Estado Operativo</th>
                    <th style="text-align: right;">Gestión</th>
                </tr>
            </thead>
            <tbody>
                @if($herramientas->isEmpty())
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="py-4">
                            <i class="fa-solid fa-box-open fa-3x mb-3 text-muted"></i>
                            <p class="h5 text-muted">Sin resultados para la búsqueda actual.</p>
                        </div>
                    </td>
                </tr>
                @endif
                @foreach($herramientas as $h)
                @php
                    $esAgotada = $h->stock_disponible <= 0;
                    $esMantenimientoCritico = $h->estado === 'Mantenimiento' && $h->creado_en && (strtotime($h->creado_en) < (time() - 15 * 86400));
                    $tieneAlerta = $esAgotada || $esMantenimientoCritico;
                @endphp
                <tr class="herramienta-row" style="{{ $tieneAlerta ? 'background: linear-gradient(90deg, rgba(244, 63, 94, 0.08), transparent); border-left: 4px solid #F43F5E;' : '' }}">
                    <td>
                        <code style="background: #112240; color: #64ffda; padding: 4px 10px; border-radius: 4px; border: 1px solid #233554;">{{ $h->codigo }}</code>
                    </td>
                    <td>
                        <div class="tool-details-cell">
                            @php
                                $imgPng = '/img/herramientas/' . $h->id . '.png';
                                $imgSvg = '/img/herramientas/' . $h->id . '.svg';
                                $hasPng = file_exists(public_path('img/herramientas/' . $h->id . '.png'));
                                $hasSvg = file_exists(public_path('img/herramientas/' . $h->id . '.svg'));
                            @endphp
                            <div class="tool-img-wrap">
                                @if($hasPng)
                                    <img src="{{ $imgPng }}" alt="{{ $h->nombre }}" loading="lazy">
                                @elseif($hasSvg)
                                    <img src="{{ $imgSvg }}" alt="{{ $h->nombre }}" loading="lazy">
                                @else
                                    <span class="no-img-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 700; color: #ccd6f6; font-size: 1.05rem;">
                                    {{ $h->nombre }}
                                    @if($esAgotada)
                                        <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: 1px solid #F43F5E; font-size: 0.7rem; margin-left: 0.5rem; font-weight: bold; padding: 2px 6px;">
                                            AGOTADO
                                        </span>
                                    @elseif($esMantenimientoCritico)
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B; border: 1px solid #F59E0B; font-size: 0.7rem; margin-left: 0.5rem; font-weight: bold; padding: 2px 6px;">
                                            MANTENIMIENTO CRÍTICO
                                        </span>
                                    @endif
                                </div>
                                <small style="color: #8892b0;">{{ Str::limit($h->descripcion, 38) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mb-1"><span class="section-tag">{{ $h->categoria ? $h->categoria->nombre : 'General' }}</span></div>
                        <small style="color: #8892b0;"><i class="fa-solid fa-warehouse mr-1" style="font-size: 0.8rem;"></i> {{ $h->modelAlmacen ? $h->modelAlmacen->nombre : 'Principal' }}</small>
                    </td>
                    <td style="text-align: center; width: 150px;">
                        @php
                            $percentage = ($h->stock_total > 0) ? ($h->stock_disponible / $h->stock_total) * 100 : 0;
                            $stockColor = $percentage > 50 ? '#64ffda' : ($percentage > 20 ? '#ffcc00' : '#ff4d4d');
                        @endphp
                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-1 d-flex align-items-baseline gap-1">
                                <span style="font-size: 1.2rem; font-weight: 800; color: {{ $stockColor }};">{{ $h->stock_disponible }}</span>
                                <span style="font-size: 0.7rem; color: #8892b0; font-weight: 600;">/ {{ $h->stock_total }}</span>
                            </div>
                            <div class="progress w-100" style="height: 4px; background: rgba(255,255,255,0.05); border-radius: 10px; max-width: 80px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background: {{ $stockColor }}; box-shadow: 0 0 10px {{ $stockColor }}; transition: width 0.5s;"></div>
                            </div>
                            <small class="mt-1" style="font-size: 0.65rem; color: #8892b0; text-transform: uppercase; letter-spacing: 0.5px;">Disponibilidad</small>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-modern badge {{ $h->estado }}">{{ $h->estado }}</span>
                    </td>
                    <td style="text-align: right;">
                        <div class="d-flex justify-content-end gap-2">
                            @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                                <button class="btn btn-sm btn-outline-success" title="Abastecer Stock" onclick="quickRestock({{ $h->id }}, '{{ $h->nombre }}')">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </button>
                            @endif
                            <a href="{{ route('herramientas.show', $h->id) }}" class="btn btn-sm btn-outline-info" title="Expediente"><i class="fa-solid fa-file-invoice"></i></a>
                            @if(in_array(Auth::user()->rol, ['Administrador', 'Almacenero']))
                                <a href="{{ route('herramientas.edit', $h->id) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                            @endif
                            @if(Auth::user()->rol === 'Administrador')
                                <form action="{{ route('herramientas.destroy', $h->id) }}" method="POST" onsubmit="return confirm('¿Confirmar baja definitiva del lote?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('input_busqueda');
    const tableBody = document.querySelector('#tabla_herramientas tbody');
    const rows = document.querySelectorAll('.herramienta-row');

    function filtrar() {
        const query = inputBusqueda.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        let visibleRows = 0;

        rows.forEach(row => {
            const text = row.innerText.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            if (text.includes(query)) {
                row.style.display = "";
                visibleRows++;
            } else {
                row.style.display = "none";
            }
        });

        let dynamicMsg = document.getElementById('no_results_dynamic');
        if (visibleRows === 0) {
            if (!dynamicMsg) {
                dynamicMsg = document.createElement('tr');
                dynamicMsg.id = 'no_results_dynamic';
                dynamicMsg.innerHTML = '<td colspan="6" class="text-center py-5 text-muted font-weight-bold"><i class="fa-solid fa-box-open mr-2"></i>No se encontraron coincidencias locales.</td>';
                tableBody.appendChild(dynamicMsg);
            }
        } else {
            if (dynamicMsg) dynamicMsg.remove();
        }
    }

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', filtrar);
    }
});

function quickRestock(id, name) {
    Swal.fire({
        title: 'RE-ABASTECER LOTE',
        html: `
            <div class="text-left">
                <p class="text-muted small mb-3">Incrementar el stock total para <b>${name}</b></p>
                <input type="number" id="swal-stock-add" class="swal2-input m-0 w-100" placeholder="Cantidad a sumar..." min="1">
            </div>
        `,
        background: '#0A192F',
        color: '#E6F1FF',
        showCancelButton: true,
        confirmButtonColor: '#64FFDA',
        confirmButtonText: 'SUMAR STOCK',
        cancelButtonText: 'CANCELAR',
        preConfirm: () => {
            const add = document.getElementById('swal-stock-add').value;
            if (!add || add < 1) {
                Swal.showValidationMessage('Ingrese una cantidad válida');
            }
            return add;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'PROCESANDO...',
                didOpen: () => Swal.showLoading(),
                background: '#0A192F',
                color: '#E6F1FF'
            });

            fetch(`/herramientas/${id}/restock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ add: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Inventario Actualizado', background: '#0A192F', color: '#E6F1FF' })
                    .then(() => window.location.reload());
                }
            });
        }
    });
}
</script>
@endsection

