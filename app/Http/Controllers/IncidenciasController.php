<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\Herramienta;
use App\Models\Trabajador;
use App\Models\Vale;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidenciasController extends Controller
{
    // Mapa de tipos de falta a su gravedad y acción correctiva predeterminada
    private static $reglas = [
        'Retraso en devolución'   => ['gravedad' => 'Leve',      'accion_base' => 'Advertencia verbal'],
        'Herramienta sucia'       => ['gravedad' => 'Leve',      'accion_base' => 'Advertencia verbal'],
        'Daño por mal uso'        => ['gravedad' => 'Grave',     'accion_base' => 'Capacitación obligatoria + Suspensión temporal de préstamos'],
        'Herramienta rota'        => ['gravedad' => 'Grave',     'accion_base' => 'Capacitación obligatoria + Suspensión temporal de préstamos'],
        'Herramienta perdida'     => ['gravedad' => 'Grave',     'accion_base' => 'Investigación + Restricción temporal de acceso al almacén'],
        'Préstamo no autorizado'  => ['gravedad' => 'Grave',     'accion_base' => 'Suspensión de permiso para retirar herramientas'],
        'Ocultamiento de daños'   => ['gravedad' => 'Grave',     'accion_base' => 'Suspensión de permiso para retirar herramientas'],
        'Alteración de registros' => ['gravedad' => 'Muy grave', 'accion_base' => 'Generar reporte disciplinario formal'],
    ];

    public function index(Request $request)
    {
        $herramientas = Herramienta::orderBy('codigo')->get();
        $trabajadores = Trabajador::where('estado', 'Activo')->orderBy('nombre')->get();

        // ── Dashboard Stats (safe fallback if migration not yet applied) ───────
        try {
            $totalSanciones  = Incidencia::whereNotNull('tipo_falta')->count();
        } catch (\Exception $e) { $totalSanciones = 0; }

        $totalDanadas    = Herramienta::where('estado', 'Dañado')->count();
        $totalPerdidas   = Herramienta::where('estado', 'Perdido')->count();

        try {
            $topTrabajadores = Incidencia::whereNotNull('trabajador_id')
                ->whereNotNull('tipo_falta')
                ->select('trabajador_id', DB::raw('COUNT(*) as total'))
                ->groupBy('trabajador_id')
                ->orderByDesc('total')
                ->with('trabajador')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $topTrabajadores = collect();
        }

        // ── Filtros ────────────────────────────────────────────────────────────
        try {
            $query = Incidencia::with(['herramienta', 'usuario', 'trabajador', 'vale'])
                                ->whereNotNull('tipo_falta')
                                ->orderBy('id', 'desc');

            if ($request->filled('trabajador_id')) {
                $query->where('trabajador_id', $request->trabajador_id);
            }
            if ($request->filled('tipo_falta')) {
                $query->where('tipo_falta', $request->tipo_falta);
            }
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha', '>=', $request->fecha_inicio);
            }
            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha', '<=', $request->fecha_fin);
            }

            $incidencias = $query->get();
        } catch (\Exception $e) {
            // Migration not yet applied — fall back to classic incidencias
            $incidencias = Incidencia::with(['herramienta', 'usuario', 'trabajador', 'vale'])
                ->orderBy('id', 'desc')
                ->get();
        }

        // Lista de tipos de falta para el formulario y filtros
        $tiposFalta = array_keys(self::$reglas);

        return view('incidencias.index', compact(
            'herramientas', 'trabajadores', 'incidencias',
            'totalSanciones', 'totalDanadas', 'totalPerdidas', 'topTrabajadores',
            'tiposFalta'
        ));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403, 'No tienes permisos para registrar incidencias.');
        }

        $request->validate([
            'herramienta_id'        => 'required|exists:herramientas,id',
            'estado_incidencia'     => 'nullable|string|in:Dañado,Mantenimiento,Revision,Perdido,Falla técnica,No es la herramienta,Otro',
            'tipo_falta'            => 'required|string',
            'gravedad'              => 'nullable|string|in:Leve,Grave,Muy grave',
            'cantidad_afectada'     => 'required|integer|min:1',
            'descripcion_incidencia'=> 'nullable|string',
            'trabajador_id'         => 'nullable|exists:trabajadores,id',
            'vale_id'               => 'nullable|exists:vales,id',
            'monto_sancion'         => 'nullable|numeric|min:0',
            'evidencia'             => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $herramienta   = Herramienta::lockForUpdate()->findOrFail($request->herramienta_id);
            $tipo_falta    = $request->tipo_falta;
            $regla         = self::$reglas[$tipo_falta] ?? ['gravedad' => 'Leve', 'accion_base' => 'Advertencia verbal'];

            // Determinar gravedad (la del form, o la auto-calculada)
            $gravedad = $request->gravedad ?: $regla['gravedad'];

            // Determinar acción correctiva según reincidencia
            $accion_correctiva = $regla['accion_base'];
            if ($request->filled('trabajador_id') && in_array($gravedad, ['Leve'])) {
                try {
                    $antecedentes = Incidencia::where('trabajador_id', $request->trabajador_id)
                        ->whereNotNull('tipo_falta')
                        ->count();
                    if ($antecedentes > 0) {
                        $accion_correctiva = 'Amonestación escrita (reincidencia)';
                    }
                } catch (\Exception $e) {
                    // Columns not yet created — skip reincidence check
                }
            }

            // Subir evidencia
            $evidencia_path = null;
            if ($request->hasFile('evidencia')) {
                $evidencia_path = $request->file('evidencia')
                    ->store('evidencias', 'public');
            }

            // Estado incidencia para afectación de herramienta
            $nuevo_estado = $request->estado_incidencia;
            if (!$nuevo_estado) {
                $mapa = [
                    'Herramienta rota'    => 'Dañado',
                    'Daño por mal uso'    => 'Dañado',
                    'Herramienta perdida' => 'Perdido',
                ];
                $nuevo_estado = $mapa[$tipo_falta] ?? null;
            }
            $cant_afectada = $request->cantidad_afectada;

            // Base data (always safe — columns exist since the beginning)
            $incidenciaData = [
                'herramienta_id'  => $herramienta->id,
                'usuario_id'      => Auth::id(),
                'trabajador_id'   => $request->trabajador_id ?: null,
                'vale_id'         => $request->vale_id ?: null,
                'fecha'           => now(),
                'cantidad_afectada' => $cant_afectada,
                'tipo'            => $nuevo_estado ?? $tipo_falta,
                'descripcion'     => $request->descripcion_incidencia,
                'monto_sancion'   => 0,    // No deducción monetaria
                'estado_sancion'  => null,
                'reparado'        => false,
            ];

            // Add disciplinary fields only if the columns exist
            if (\Illuminate\Support\Facades\Schema::hasColumn('incidencias', 'tipo_falta')) {
                $incidenciaData['tipo_falta']           = $tipo_falta;
                $incidenciaData['gravedad']              = $gravedad;
                $incidenciaData['accion_correctiva']     = $accion_correctiva;
                $incidenciaData['evidencia']             = $evidencia_path;
                $incidenciaData['estado_disciplinario']  = 'Activo';
            }

            $incidencia = Incidencia::create($incidenciaData);

            // Afectar herramienta solo para ciertos tipos
            if ($nuevo_estado) {
                $estado_herramienta = in_array($nuevo_estado, ['Dañado', 'Mantenimiento', 'Perdido', 'Falla técnica'])
                    ? $nuevo_estado
                    : $herramienta->estado;
                $herramienta->estado = $estado_herramienta;

                if ($cant_afectada > 0 && $herramienta->stock_disponible > 0 && in_array($nuevo_estado, ['Dañado', 'Perdido', 'Mantenimiento', 'Falla técnica'])) {
                    $reducir = min($cant_afectada, $herramienta->stock_disponible);
                    $herramienta->decrement('stock_disponible', $reducir);
                } else {
                    $herramienta->save();
                }
            }

            Log::create([
                'usuario_id'  => Auth::id(),
                'accion'      => 'INCIDENCIA',
                'tabla'       => 'incidencias',
                'item_id'     => $incidencia->id,
                'descripcion' => "Sanción disciplinaria ({$tipo_falta} / {$gravedad}) registrada para " . ($herramienta->nombre ?? ''),
                'fecha'       => now()
            ]);

            DB::commit();

            return redirect()->route('incidencias.index')
                ->with('exito', '1')
                ->with('resumen', [
                    'herramienta'  => $herramienta->nombre,
                    'estado'       => $tipo_falta,
                    'cantidad'     => $cant_afectada,
                    'usuario'      => Auth::user()->nombre,
                    'fecha'        => now()->format('d/m/Y H:i'),
                    'descripcion'  => $request->descripcion_incidencia
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error: ' . $e->getMessage()]);
        }
    }

    /**
     * Marcar una sanción disciplinaria como Cumplida (levanta la suspensión).
     */
    public function resolver(Request $request, $id)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $incidencia = Incidencia::findOrFail($id);
        $incidencia->estado_disciplinario = 'Cumplido';
        $incidencia->save();

        Log::create([
            'usuario_id'  => Auth::id(),
            'accion'      => 'RESOLVER',
            'tabla'       => 'incidencias',
            'item_id'     => $incidencia->id,
            'descripcion' => "Sanción #{$incidencia->id} marcada como Cumplida por " . Auth::user()->nombre,
            'fecha'       => now()
        ]);

        return back()->with('success', 'Sanción marcada como cumplida. El trabajador puede volver a retirar herramientas.');
    }
}
