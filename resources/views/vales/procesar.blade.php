@extends('layouts.app')

@section('title', 'Procesar Devolución')

@section('content')
<div class="d-flex mb-4">
    <h1 class="page-title" style="margin-bottom:0;">Procesar Devolución</h1>
    <a href="{{ route('vales.devolver_form') }}" class="btn" style="background:#172A45; color:var(--text-main); width:auto;">
        <i class="fa-solid fa-arrow-left mr-1"></i> Escanear Otro Vale
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<div style="display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Resumen del Vale -->
    <div class="card" style="flex: 1; min-width: 300px; background: var(--surface-color); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); height: fit-content; text-align: center;">
        <h3 style="color:var(--primary-color); margin-bottom: 0.5rem; font-size: 1.8rem;"><i class="fa-solid fa-file-invoice mr-1"></i></h3>
        <h2 style="margin-bottom: 1rem; color: var(--text-main); font-weight: bold; font-size: 1.5rem; letter-spacing: 1px;">{{ $vale->codigo_vale }}</h2>
        <p style="margin-bottom: 0;"><span class="badge {{ $vale->estado }}">{{ $vale->estado }}</span></p>

        <div style="margin-top:1.2rem; padding:1rem; background:rgba(100,255,218,0.05); border:1px solid rgba(100,255,218,0.15); border-radius:10px; text-align:left; font-size:0.82rem;">
            <div style="color:var(--text-muted); margin-bottom:0.4rem; font-weight:600; text-transform:uppercase; font-size:0.72rem;">Trabajador</div>
            <div style="color:var(--text-main); font-weight:600;">{{ $vale->trabajador->nombre ?? '—' }} {{ $vale->trabajador->apellidos ?? '' }}</div>
            <div style="color:var(--text-muted); margin-top:0.5rem; font-size:0.75rem;">{{ $vale->trabajador->cargo ?? '' }}</div>
        </div>

        <div style="margin-top:0.8rem; padding:0.8rem; background:rgba(239,68,68,0.05); border:1px dashed rgba(239,68,68,0.2); border-radius:8px; font-size:0.78rem; color:var(--text-muted); text-align:left;">
            <i class="fa-solid fa-circle-info mr-1" style="color:var(--primary-color);"></i>
            Si hay un <strong>problema</strong> con la herramienta, selecciona el estado correspondiente y el sistema registrará la <strong>medida disciplinaria automáticamente</strong>.
        </div>
    </div>

    <!-- Formulario de Devolución -->
    <div class="card" style="flex: 2; min-width: 400px; background: var(--surface-color); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 1rem;">Herramientas en este Vale</h3>
        <form action="{{ route('vales.guardar_devolucion', $vale->id) }}" method="POST">
            @csrf
            
            <div class="table-container" style="margin-bottom: 1.5rem; overflow-x: auto;">
                <table class="table" style="font-size: 0.88rem; min-width: 700px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Herramienta</th>
                            <th style="text-align: center;">Pendiente</th>
                            <th style="width: 80px;">Devolver</th>
                            <th style="width: 160px;">Estado al Devolver</th>
                            <th>Medida Disciplinaria</th>
                            <th style="width:130px;">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $todo_devuelto = true; @endphp
                        @foreach($vale->detalles as $d)
                        @php
                            $prestado = $d->cantidad_prestada;
                            $devuelto = $d->cantidad_devuelta;
                            $pendiente = $prestado - $devuelto;
                            if($pendiente > 0) $todo_devuelto = false;
                        @endphp
                        <tr>
                            <td style="font-weight: bold; color: var(--primary-color);">{{ $d->herramienta->codigo }}</td>
                            <td>{{ $d->herramienta->nombre }}</td>
                            <td style="text-align: center; font-weight: bold;">
                                {{ $pendiente }} <small style="color:var(--text-muted); font-weight:normal;">(de {{ $prestado }})</small>
                            </td>
                            <td>
                                @if($pendiente > 0)
                                    <input type="number" name="devolver_cant[{{ $d->id }}]" class="form-control input-cant" style="width: 65px; padding: 0.25rem; background: rgba(100, 255, 218, 0.1); border-color: var(--primary-color); font-weight: bold; text-align: center;" min="0" max="{{ $pendiente }}" value="{{ $pendiente }}">
                                @else
                                    <span style="color: var(--primary-color); font-size: 0.85rem;"><i class="fa-solid fa-check"></i> Listo</span>
                                @endif
                            </td>
                            <td>
                                @if($pendiente > 0)
                                    <select name="problema_tipo[{{ $d->id }}]" class="form-control select-problema" style="padding: 0.25rem; font-size: 0.82rem; background: #112240; border-color: var(--border-color); color: var(--text-main); border-radius: 6px; cursor: pointer;">
                                        <option value="ninguno">🟢 Buen estado</option>
                                        <option value="Dañado">🔨 Roto / Dañado</option>
                                        <option value="No es la herramienta">❌ Hta. Incorrecta</option>
                                        <option value="Perdido">❓ Perdido / Extraviado</option>
                                    </select>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($pendiente > 0)
                                    {{-- Hidden input: tipo_falta mapped from problema --}}
                                    <input type="hidden" name="tipo_falta[{{ $d->id }}]" class="input-tipo-falta" value="">
                                    <div class="medida-preview" style="font-size:0.78rem; color:var(--text-muted); font-style:italic; padding:0.3rem 0;">
                                        — Sin novedad —
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($pendiente > 0)
                                    <input type="text" name="nota_incidencia[{{ $d->id }}]" class="form-control input-nota" style="padding: 0.25rem; font-size: 0.8rem; background: rgba(255,255,255,0.05); border-color: var(--border-color); color: var(--text-main); border-radius: 6px;" placeholder="Detalle..." readonly>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!$todo_devuelto)
            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.9rem;">
                <i class="fa-solid fa-check-double mr-1"></i> Registrar Devolución y Aplicar Medidas
            </button>
            @else
            <div class="alert" style="background: rgba(16,185,129,0.1); color: #10B981; border: 1px solid #10B981; text-align: center;">
                <i class="fa-solid fa-check-circle"></i> Todas las herramientas de este vale han sido devueltas.
            </div>
            @endif
        </form>
    </div>
</div>

<style>
.medida-tag {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
}
.medida-leve    { background: rgba(34,197,94,.12);  color: #22C55E; border: 1px solid rgba(34,197,94,.3); }
.medida-grave   { background: rgba(249,115,22,.12); color: #F97316; border: 1px solid rgba(249,115,22,.3); }
.medida-perdido { background: rgba(139,92,246,.12); color: #8B5CF6; border: 1px solid rgba(139,92,246,.3); }
</style>

<script>
// Reglas de tipo de falta y medida disciplinaria por estado devuelto
const MEDIDAS = {
    'ninguno':           { tipo: '',                    texto: '— Sin novedad —',                                          clase: '' },
    'Dañado':            { tipo: 'Herramienta rota',    texto: '🔨 Capacitación obligatoria + Restricción de préstamos',    clase: 'medida-grave' },
    'No es la herramienta': { tipo: 'Préstamo no autorizado', texto: '❌ Amonestación escrita + Suspensión temporal',      clase: 'medida-grave' },
    'Perdido':           { tipo: 'Herramienta perdida', texto: '🔎 Investigación + Restricción de acceso al almacén',       clase: 'medida-perdido' },
};

$(document).ready(function() {
    $('.select-problema').on('change', function() {
        let row      = $(this).closest('tr');
        let tipo     = $(this).val();
        let inputCant  = row.find('.input-cant');
        let inputNota  = row.find('.input-nota');
        let inputFalta = row.find('.input-tipo-falta');
        let preview    = row.find('.medida-preview');
        let maxCant  = inputCant.attr('max');
        let info     = MEDIDAS[tipo] || MEDIDAS['ninguno'];

        // Actualizar campo oculto de tipo_falta
        inputFalta.val(info.tipo);

        // Mostrar la medida disciplinaria como badge de texto
        if (info.clase) {
            preview.html(`<span class="medida-tag ${info.clase}">${info.texto}</span>`);
        } else {
            preview.html('<span style="color:var(--text-muted); font-style:italic;">— Sin novedad —</span>');
        }

        // Controlar estado de otros inputs
        if (tipo === 'ninguno') {
            inputNota.val('').prop('readonly', true).css('background', 'rgba(255,255,255,0.05)');
            inputCant.val(maxCant).prop('readonly', false).css('background', 'rgba(100, 255, 218, 0.1)');
        } else {
            inputNota.prop('readonly', false).css('background', '#112240');
            if (tipo === 'Dañado') {
                inputCant.val(maxCant).prop('readonly', false).css('background', 'rgba(100, 255, 218, 0.1)');
            } else {
                // Perdido o Hta. Incorrecta: cantidad devuelta = 0 (no regresa físicamente)
                inputCant.val(0).prop('readonly', true).css('background', 'rgba(255,255,255,0.05)');
            }
        }
    });
});
</script>
@endsection
