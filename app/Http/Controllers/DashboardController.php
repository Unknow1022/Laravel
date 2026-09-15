<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use App\Models\Trabajador;
use App\Models\Vale;
use App\Models\Almacen;
use App\Services\NotificacionRetrasoService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(\App\Services\AIService $aiService, NotificacionRetrasoService $notificador)
    {
        // Verificar vales retrasados y enviar notificaciones de correo si es necesario
        try {
            $notificador->verificarYNotificar();
        } catch (\Exception $e) {
            \Log::warning("[Dashboard] No se pudo ejecutar NotificacionRetrasoService: " . $e->getMessage());
        }

        $totalStock = Herramienta::sum('stock_total') ?: 0;
        $totalTrabajadores = Trabajador::where('estado', 'Activo')->count();
        $almacenes = Almacen::withCount('herramientas')->get();

        // Estadísticas de Vales
        $valesStats = [
            'emitidos' => Vale::count(),
            'pendientes' => Vale::where('estado', 'Activo')->count(),
            'cerrados' => Vale::where('estado', 'Devuelto')->count(),
            'parciales' => Vale::where('estado', 'Parcial')->count(),
        ];

        // Actividad semanal para gráfico
        $weeklyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = Vale::whereDate('fecha_creacion', $date)->count();
            $weeklyActivity[] = [
                'day' => Carbon::now()->subDays($i)->isoFormat('ddd'),
                'count' => $count
            ];
        }

        $todas = Herramienta::orderByDesc('id')->get();

        $recent = $todas->take(8);

        $stock_disponible = Herramienta::sum('stock_disponible') ?: 0;
        $stock_prestado = $totalStock - $stock_disponible;

        $totalValesActivos = $valesStats['pendientes'] + $valesStats['parciales'];
        $vales_activos_todos = Vale::with(['trabajador', 'detalles.herramienta'])
                             ->whereIn('estado', ['Activo', 'Parcial'])
                             ->get();
        
        $vales_activos = $vales_activos_todos->sortByDesc('fecha_creacion')->take(5);

        $stockBajo = Herramienta::whereColumn('stock_disponible', '<=', 'stock_minimo')
                                ->where('stock_disponible', '>', 0)
                                ->get();
        
        $vales_vencidos = $vales_activos_todos->filter(function($v) {
            if (!$v->fecha_limite) return false;
            return strtotime($v->fecha_limite) < time();
        });

        $herramientas_criticas = Herramienta::whereIn('estado', ['Dañado', 'Mantenimiento', 'Perdido', 'Falla técnica'])->get();

        // Insights de IA
        try {
            $aiInsights = $aiService->getInsights();
        } catch (\Exception $e) {
            \Log::error("Error en AI Service: " . $e->getMessage());
            $aiInsights = [
                'anomalies' => [],
                'recommendations' => [],
                'load_analysis' => collect([]),
                'trends' => ['activity_score' => 0, 'momentum' => 0, 'prediction' => 'Indeterminado'],
                'summary' => 'El sistema de análisis está temporalmente fuera de línea.',
                'security' => ['status' => 'OFFLINE', 'alerts' => [], 'level' => 'LOW'],
                'diagnostic' => []
            ];
        }

        return view('dashboard', compact(
            'totalStock',
            'totalTrabajadores',
            'almacenes',
            'valesStats',
            'weeklyActivity',
            'recent',
            'stock_disponible',
            'stock_prestado',
            'totalValesActivos',
            'vales_activos',
            'stockBajo',
            'vales_vencidos',
            'herramientas_criticas',
            'aiInsights'
        ));
    }
}
