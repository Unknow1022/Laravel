<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use App\Models\Log;
use App\Models\ValeDetalle;
use App\Mail\HerramientaAgotadaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class HerramientasController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = substr(strip_tags($request->query('search', '')), 0, 100);
        $busqueda_segura = str_replace(['%', '_'], ['\%', '\_'], $busqueda);
        
        $sqliProtection = session()->get('cortex_sqli_protection', true);
        $executedQuery = "";

        if (!$sqliProtection && !empty($busqueda)) {
            // Consulta vulnerable con concatenación directa de strings
            $rawSql = "SELECT * FROM herramientas WHERE deleted_at IS NULL AND (nombre LIKE '%" . $busqueda . "%' OR codigo LIKE '%" . $busqueda . "%')";
            $executedQuery = $rawSql;
            
            try {
                // Ejecutar consulta raw
                $results = \Illuminate\Support\Facades\DB::select($rawSql);
                // Hidratar a modelos de Herramienta para no romper la vista
                $herramientas = Herramienta::hydrate($results);
                // Cargar relaciones
                $herramientas->load(['modelAlmacen', 'categoria']);
            } catch (\Exception $e) {
                // Capturar errores de sintaxis SQL (comunes durante inyección) y mostrarlos en el debugger
                $executedQuery .= "\n\n❌ ERROR SQL: " . $e->getMessage();
                $herramientas = collect();
            }
        } else {
            // Consulta parametrizada segura (Eloquent original)
            $herramientas = Herramienta::with(['modelAlmacen', 'categoria'])
                ->when($busqueda_segura, function ($query, $busqueda_segura) {
                    return $query->where('nombre', 'like', "%{$busqueda_segura}%")
                                 ->orWhere('codigo', 'like', "%{$busqueda_segura}%")
                                 ->orWhereHas('modelAlmacen', function($q) use ($busqueda_segura) {
                                     $q->where('nombre', 'like', "%{$busqueda_segura}%");
                                 })
                                 ->orWhereHas('categoria', function($q) use ($busqueda_segura) {
                                     $q->where('nombre', 'like', "%{$busqueda_segura}%");
                                 });
                })->orderBy('nombre', 'asc')->get();

            // Guardar representación de la query ejecutada
            if (!empty($busqueda)) {
                $executedQuery = "SELECT * FROM herramientas \nWHERE deleted_at IS NULL \n  AND (nombre LIKE ? OR codigo LIKE ? OR ...)\n\nBindings: ['%" . $busqueda . "%', '%" . $busqueda . "%']";
            } else {
                $executedQuery = "SELECT * FROM herramientas WHERE deleted_at IS NULL ORDER BY nombre ASC";
            }
        }

        // Compartir query ejecutada con la sesión
        session(['cortex_last_query' => $executedQuery]);

        return view('herramientas.index', compact('herramientas', 'busqueda'));
    }

    public function ubicaciones()
    {
        $almacenes = \App\Models\Almacen::with(['herramientas.categoria'])->get();
        return view('herramientas.ubicaciones', compact('almacenes'));
    }

    public function create()
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403, 'No tienes permiso para registrar herramientas.');
        }

        $almacenes = \App\Models\Almacen::with('categorias')->get();
        return view('herramientas.create', compact('almacenes'));
    }

    public function store(\App\Http\Requests\HerramientaRequest $request)
    {
        $lastCodigo = Herramienta::withTrashed()->orderBy('id', 'desc')->first()->codigo ?? 'H-0000';
        $numero = (int)str_replace('H-', '', $lastCodigo);
        $codigo = 'H-' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);

        $herramienta = Herramienta::create(array_merge($request->validated(), [
            'codigo' => $codigo,
            'stock_disponible' => $request->stock_total,
            'stock_minimo' => $request->stock_minimo ?? 2,
        ]));

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'CREAR',
            'tabla' => 'herramientas',
            'item_id' => $herramienta->id,
            'descripcion' => "Lote registrado: {$herramienta->nombre} ({$herramienta->codigo})",
            'fecha' => now()
        ]);

        // Si el stock inicial es 0 (error de carga), notificar
        if ($herramienta->stock_disponible <= 0) {
            try {
                $herramienta->load(['modelAlmacen', 'categoria']);
                Mail::to('jesusmanuelriveragarcia6@gmail.com')->send(new HerramientaAgotadaMail($herramienta));
            } catch (\Exception $e) {
                \Log::warning("[Stock] No se pudo enviar correo agotado: " . $e->getMessage());
            }
        }

        return redirect()->route('herramientas.index')->with('exito', '1');
    }

    public function edit(Herramienta $herramienta)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            abort(403);
        }

        $almacenes = \App\Models\Almacen::with('categorias')->get();
        return view('herramientas.edit', compact('herramienta', 'almacenes'));
    }

    public function update(\App\Http\Requests\HerramientaRequest $request, Herramienta $herramienta)
    {
        $diferencia = $request->stock_total - $herramienta->stock_total;
        $nuevoDisponible = $herramienta->stock_disponible + $diferencia;

        if ($nuevoDisponible < 0) {
            return back()->withErrors(['stock_total' => 'El nuevo stock total no puede ser menor a las herramientas ya prestadas.']);
        }

        $herramienta->update(array_merge($request->validated(), [
            'stock_disponible' => $nuevoDisponible,
        ]));

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'EDITAR',
            'tabla' => 'herramientas',
            'item_id' => $herramienta->id,
            'descripcion' => "Actualizada: {$herramienta->nombre} ({$herramienta->codigo})",
            'fecha' => now()
        ]);

        // Notificar si el stock disponible llegó a 0
        if ($herramienta->fresh()->stock_disponible <= 0) {
            try {
                $herramienta->load(['modelAlmacen', 'categoria']);
                Mail::to('jesusmanuelriveragarcia6@gmail.com')->send(new HerramientaAgotadaMail($herramienta));
            } catch (\Exception $e) {
                \Log::warning("[Stock] No se pudo enviar correo agotado: " . $e->getMessage());
            }
        }

        return redirect()->route('herramientas.index')->with('modificado', '1');
    }

    public function show(Herramienta $herramienta)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero', 'Supervisor'])) {
            abort(403);
        }

        $herramienta->load(['modelAlmacen', 'categoria']);
        $historial = ValeDetalle::with(['vale', 'vale.trabajador', 'vale.usuario'])
                        ->where('herramienta_id', $herramienta->id)
                        ->orderByDesc('id')
                        ->get();

        return view('herramientas.show', compact('herramienta', 'historial'));
    }

    public function restock(Request $request, $id)
    {
        if (!in_array(Auth::user()->rol, ['Administrador', 'Almacenero'])) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $herramienta = Herramienta::findOrFail($id);
        $cantidad = (int) $request->add;

        if ($cantidad < 1) {
            return response()->json(['success' => false, 'message' => 'Cantidad inválida']);
        }

        $herramienta->stock_total += $cantidad;
        $herramienta->stock_disponible += $cantidad;
        $herramienta->save();

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'ABASTECER',
            'tabla' => 'herramientas',
            'item_id' => $herramienta->id,
            'descripcion' => "Re-abastecimiento rápido: +{$cantidad} unidades para {$herramienta->nombre}",
            'fecha' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Stock actualizado correctamente']);
    }

    public function destroy(Herramienta $herramienta)
    {
        if (Auth::user()->rol !== 'Administrador') {
            abort(403);
        }

        $herramienta->delete();

        Log::create([
            'usuario_id' => Auth::id(),
            'accion' => 'ELIMINAR',
            'tabla' => 'herramientas',
            'item_id' => $herramienta->id,
            'descripcion' => "Herramienta enviada a papelera.",
            'fecha' => now()
        ]);

        return redirect()->route('herramientas.index')->with('eliminado', '1');
    }
}
