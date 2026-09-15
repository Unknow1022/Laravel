<?php

namespace App\Http\Controllers;

use App\Models\Vale;
use App\Models\ValeDetalle;
use App\Models\Trabajador;
use App\Models\Herramienta;
use App\Models\Incidencia;
use App\Models\Log;
use App\Mail\HerramientaAgotadaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ValesController extends Controller
{
    public function index()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero', 'Supervisor'])) {
            abort(403);
        }

        $vales = Vale::with('trabajador')->whereIn('estado', ['Activo', 'Parcial'])->orderBy('id', 'desc')->get();
        return view('vales.index', compact('vales'));
    }

    public function historial()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero', 'Supervisor'])) {
            abort(403);
        }

        $vales = Vale::with(['trabajador' => function($q) {
            $q->withTrashed();
        }, 'usuario'])->orderBy('id', 'desc')->get();
        return view('vales.historial', compact('vales'));
    }

    public function create()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $trabajadores = Trabajador::where('estado', 'Activo')->get();
        $herramientas = Herramienta::where('stock_disponible', '>', 0)->get();

        // IDs de trabajadores con sanción disciplinaria activa (suspendidos)
        $suspendidosIds = Incidencia::whereNotNull('tipo_falta')
            ->where('estado_disciplinario', 'Activo')
            ->whereNotNull('trabajador_id')
            ->pluck('trabajador_id')
            ->unique()
            ->toArray();

        return view('vales.create', compact('trabajadores', 'herramientas', 'suspendidosIds'));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $request->validate([
            'trabajador_id' => 'required|exists:trabajadores,id',
            'cantidades' => 'required|array',
        ]);

        $cantidades = array_filter($request->cantidades, function ($cant) {
            return (int)$cant > 0;
        });

        if (empty($cantidades)) {
            return back()->withErrors(['error' => 'Debe agregar al menos una herramienta con cantidad mayor a 0.']);
        }

        // Bloquear si el trabajador tiene sanción disciplinaria activa
        $tieneSancion = Incidencia::where('trabajador_id', $request->trabajador_id)
            ->whereNotNull('tipo_falta')
            ->where('estado_disciplinario', 'Activo')
            ->exists();

        if ($tieneSancion) {
            return back()->withErrors(['error' => '⚠️ Este trabajador tiene una sanción disciplinaria activa y no puede retirar herramientas hasta que la sanción sea levantada por un administrador.']);
        }

        DB::beginTransaction();
        try {
            // Generar código V-0001
            $lastVale = Vale::orderBy('id', 'desc')->first();
            $numero = $lastVale ? (int)str_replace('V-', '', $lastVale->codigo_vale) : 0;
            $codigo = 'V-' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);

            $vale = Vale::create([
                'codigo_vale' => $codigo,
                'trabajador_id' => $request->trabajador_id,
                'usuario_id' => Auth::id(),
                'fecha_creacion' => now(),
                'fecha_limite' => now()->addHours(24),
                'estado' => 'Activo'
            ]);

            foreach ($cantidades as $herramienta_id => $cantidad) {
                $cantidad = (int)$cantidad;
                $herramienta = Herramienta::lockForUpdate()->find($herramienta_id);

                if (!$herramienta || $herramienta->stock_disponible < $cantidad) {
                    throw new \Exception("Stock insuficiente para: " . ($herramienta ? $herramienta->nombre : "ID $herramienta_id"));
                }

                ValeDetalle::create([
                    'vale_id' => $vale->id,
                    'herramienta_id' => $herramienta_id,
                    'cantidad_prestada' => $cantidad,
                    'cantidad_devuelta' => 0
                ]);

                $herramienta->decrement('stock_disponible', $cantidad);

                // Si la herramienta se agotó, enviar notificación
                if ($herramienta->fresh()->stock_disponible <= 0) {
                    try {
                        $herramienta->load(['modelAlmacen', 'categoria']);
                        Mail::to('jesusmanuelriveragarcia6@gmail.com')
                            ->send(new HerramientaAgotadaMail($herramienta));
                    } catch (\Exception $mailEx) {
                        \Log::warning("[Stock] Correo agotado no enviado: " . $mailEx->getMessage());
                    }
                }
            }

            Log::create([
                'usuario_id' => Auth::id(),
                'accion' => 'CREAR',
                'tabla' => 'vales',
                'item_id' => $vale->id,
                'descripcion' => "Generó vale {$codigo} para el trabajador ID {$request->trabajador_id}",
                'fecha' => now()
            ]);

            DB::commit();

            return redirect()->route('vales.show', $vale->id)->with('exito', '1');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Vale $vale)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero', 'Supervisor'])) {
            abort(403);
        }

        $vale->load(['detalles.herramienta', 'trabajador', 'usuario']);
        return view('vales.show', compact('vale'));
    }

    public function devolver()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        return view('vales.devolver');
    }

    public function buscarVale(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $request->validate(['codigo_vale' => 'required|string']);

        $vale = Vale::where('codigo_vale', $request->codigo_vale)->first();

        if ($vale) {
            return redirect()->route('vales.procesar_devolucion', $vale->id);
        }

        return back()->withErrors(['error' => 'Vale no encontrado.']);
    }

    public function procesar_devolucion(Vale $vale)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        if ($vale->estado === 'Devuelto') {
            return redirect()->route('vales.devolver_form')->withErrors(['error' => 'El vale ya está devuelto.']);
        }

        $vale->load('detalles.herramienta');

        return view('vales.procesar', compact('vale'));
    }

    public function guardar_devolucion(Request $request, Vale $vale)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $cantidades = $request->devolver_cant ?? [];
        $problemas  = $request->problema_tipo ?? [];
        $tiposFalta = $request->tipo_falta ?? [];
        $notas      = $request->nota_incidencia ?? [];

        // Mapa de medidas correctivas por tipo de problema (sin dinero)
        $accionMap = [
            'Dañado'             => 'Capacitación obligatoria + Restricción temporal de préstamos',
            'No es la herramienta' => 'Amonestación escrita + Suspensión temporal de préstamos',
            'Perdido'            => 'Investigación + Restricción temporal de acceso al almacén',
        ];
        $gravedadMap = [
            'Dañado'             => 'Grave',
            'No es la herramienta' => 'Grave',
            'Perdido'            => 'Grave',
        ];
        $tipoFaltaMap = [
            'Dañado'             => 'Herramienta rota',
            'No es la herramienta' => 'Préstamo no autorizado',
            'Perdido'            => 'Herramienta perdida',
        ];

        DB::beginTransaction();
        try {
            $todasDevueltas = true;
            $algunAccion = false;

            foreach ($vale->detalles as $detalle) {
                $pendiente = $detalle->cantidad_prestada - $detalle->cantidad_devuelta;
                $cant_a_devolver = (int)($cantidades[$detalle->id] ?? 0);
                
                $problema  = $problemas[$detalle->id] ?? 'ninguno';
                $nota      = $notas[$detalle->id] ?? null;

                if ($problema !== 'ninguno') {
                    $algunAccion = true;

                    $accion   = $accionMap[$problema]   ?? 'Advertencia verbal';
                    $gravedad = $gravedadMap[$problema]  ?? 'Leve';
                    $tipoF    = $tipoFaltaMap[$problema] ?? $problema;

                    // Crear la incidencia disciplinaria (sin monto de dinero)
                    $incData = [
                        'herramienta_id'    => $detalle->herramienta_id,
                        'usuario_id'        => Auth::id(),
                        'trabajador_id'     => $vale->trabajador_id,
                        'vale_id'           => $vale->id,
                        'fecha'             => now(),
                        'cantidad_afectada' => $pendiente,
                        'tipo'              => $problema,
                        'descripcion'       => $nota ?: "Registrado durante devolución del vale {$vale->codigo_vale}.",
                        'monto_sancion'     => 0,
                        'estado_sancion'    => null,
                        'reparado'          => false,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('incidencias', 'tipo_falta')) {
                        $incData['tipo_falta']           = $tipoF;
                        $incData['gravedad']              = $gravedad;
                        $incData['accion_correctiva']     = $accion;
                        $incData['estado_disciplinario']  = 'Activo';
                    }
                    $incidencia = \App\Models\Incidencia::create($incData);

                    $herramienta = Herramienta::lockForUpdate()->find($detalle->herramienta_id);

                    if ($problema === 'Dañado') {
                        // Devuelve la herramienta rota — vale se cierra, stock NO aumenta
                        $detalle->cantidad_devuelta += $pendiente;
                        $detalle->save();
                        if ($herramienta) {
                            $herramienta->estado = 'Dañado';
                            $herramienta->save();
                        }
                    } elseif ($problema === 'Perdido') {
                        // Declarada perdida — vale se cierra, stock NO aumenta
                        $detalle->cantidad_devuelta += $pendiente;
                        $detalle->save();
                        if ($herramienta) {
                            $herramienta->estado = 'Perdido';
                            $herramienta->save();
                        }
                    } elseif ($problema === 'No es la herramienta') {
                        // Trajo herramienta equivocada — vale sigue PENDIENTE, la correcta aún no regresa
                        // No se incrementa cantidad_devuelta
                    }

                    // Log de la incidencia
                    Log::create([
                        'usuario_id'  => Auth::id(),
                        'accion'      => 'INCIDENCIA',
                        'tabla'       => 'incidencias',
                        'item_id'     => $incidencia->id,
                        'descripcion' => "Incidencia disciplinaria ({$tipoF} / {$gravedad}) — Medida: {$accion} — Vale {$vale->codigo_vale}",
                        'fecha'       => now()
                    ]);

                } else {
                    // Devolución normal (sin problemas) — stock regresa al almacén
                    if ($cant_a_devolver > 0 && $cant_a_devolver <= $pendiente) {
                        $detalle->cantidad_devuelta += $cant_a_devolver;
                        $detalle->save();

                        $herramienta = Herramienta::lockForUpdate()->find($detalle->herramienta_id);
                        if ($herramienta) {
                            $herramienta->increment('stock_disponible', $cant_a_devolver);
                            $algunAccion = true;
                        }
                    }
                }

                // Verificar si esta línea del vale sigue pendiente
                if ($detalle->cantidad_devuelta < $detalle->cantidad_prestada) {
                    $todasDevueltas = false;
                }
            }

            // Si todas las herramientas del vale ya están devueltas y el vale sigue activo/parcial,
            // forzamos su cierre para corregir posibles desincronizaciones previas de la base de datos
            if ($todasDevueltas && $vale->estado !== 'Devuelto') {
                $vale->estado = 'Devuelto';
                $vale->save();
                $algunAccion = true;
            }

            if ($algunAccion) {
                if (!$todasDevueltas) {
                    $vale->estado = 'Parcial';
                    $vale->save();
                }

                Log::create([
                    'usuario_id' => Auth::id(),
                    'accion' => 'EDITAR',
                    'tabla' => 'vales',
                    'item_id' => $vale->id,
                    'descripcion' => "Procesada devolución/incidencia en vale {$vale->codigo_vale}",
                    'fecha' => now()
                ]);

                DB::commit();
                return redirect()->route('vales.show', $vale->id)->with('devolucion', '1');
            } else {
                DB::rollBack();
                return back()->withErrors(['error' => 'No se registraron devoluciones o incidencias válidas.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error al procesar la devolución: ' . $e->getMessage()]);
        }
    }
}
