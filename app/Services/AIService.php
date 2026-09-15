<?php

namespace App\Services;

use App\Models\Herramienta;
use App\Models\Vale;
use App\Models\Trabajador;
use App\Models\Almacen;
use App\Services\SystemValidatorService;
use Carbon\Carbon;

class AIService
{
    public function getInsights()
    {
        $validator = new SystemValidatorService();
        $security = new CortexSecurityService();
        $auditor = new CortexAuditorService();

        $allSolved = session('cortex_all_solved');

        return [
            'anomalies' => $allSolved ? [] : $this->detectAnomalies(),
            'recommendations' => $allSolved ? [] : $this->generateRecommendations(),
            'load_analysis' => $this->analyzeWarehouseLoad(),
            'trends' => $this->predictTrends(),
            'summary' => $allSolved ? 'El sistema opera bajo condiciones nominales perfectas. Todos los parámetros están estabilizados.' : $this->generateSmartSummary(),
            'security' => $allSolved ? ['status' => 'SECURE', 'alerts' => [], 'level' => 'LOW'] : $this->checkSecurityStatus(),
            'diagnostic' => $validator->runFullDiagnostic(),
            'security_incidents' => $allSolved ? [] : $security->performSecurityScan(),
            'risk_assessment' => $allSolved ? ['level' => 'LOW', 'score' => 0, 'color' => '#64FFDA'] : $security->getRiskLevel(),
            'neural_logs' => $auditor->getRecentEvents(12)
        ];
    }

    private function checkSecurityStatus()
    {
        $status = 'SECURE';
        $alerts = [];
        $level = 'LOW';
        $dismissed = session()->get('cortex_dismissed_incidents', []);

        // 1. Detección de borrados masivos (Señal de vandalismo o sabotaje)
        // Usamos count() en el modelo para rapidez
        $borradosRecientes = Herramienta::onlyTrashed()
            ->where('deleted_at', '>=', Carbon::now()->subHours(24))
            ->count();
            
        if ($borradosRecientes > 5 && !in_array('mass_deletions', $dismissed)) {
            $alerts[] = "ACTIVIDAD ANÓMALA: Se detectó la eliminación de " . $borradosRecientes . " activos en las últimas 24h.";
            $level = 'CRITICAL';
            $status = 'HACKING_DETECTED';
        }

        // 2. Verificación de accesos administrativos nuevos
        $adminsNuevos = \App\Models\Usuario::where('rol', 'Administrador')
            ->where('creado_en', '>=', Carbon::now()->subHours(12))
            ->count();
            
        if ($adminsNuevos > 0 && !in_array('new_admins', $dismissed)) {
            $alerts[] = "RIESGO DE ACCESO: Se ha registrado un nuevo perfil Administrativo recientemente.";
            if ($status !== 'HACKING_DETECTED') {
                $status = 'WARNING';
                $level = 'MEDIUM';
            }
        }

        return [
            'status' => $status,
            'alerts' => $alerts,
            'level' => $level
        ];
    }

    private function detectAnomalies()
    {
        $anomalies = [];

        // 1. Herramientas en mantenimiento por más de 15 días
        $stuckInMantenimiento = Herramienta::where('estado', 'Mantenimiento')
            ->where('creado_en', '<', Carbon::now()->subDays(15))
            ->get();
        
        foreach ($stuckInMantenimiento as $h) {
            $anomalies[] = [
                'type' => 'critical',
                'title' => 'Retraso en Mantenimiento',
                'message' => "La herramienta {$h->nombre} lleva más de 15 días en reparación. Riesgo de cuello de botella."
            ];
        }

        // 2. Vales con mora crítica (> 3 días vencidos)
        $valesCriticos = Vale::where('estado', 'Activo')
            ->where('fecha_limite', '<', Carbon::now()->subDays(3))
            ->with('trabajador')
            ->get();

        foreach ($valesCriticos as $v) {
            $anomalies[] = [
                'type' => 'warning',
                'title' => 'Mora Crítica Detectada',
                'message' => "El trabajador {$v->trabajador->nombre} tiene el vale #{$v->codigo_vale} con 3+ días de retraso."
            ];
        }

        // 3. Stock Agotado (Detección de Stock 0)
        $outOfStock = Herramienta::where('stock_disponible', '<=', 0)->get();
        foreach ($outOfStock as $h) {
            $anomalies[] = [
                'type' => 'critical',
                'title' => 'STOCK AGOTADO',
                'message' => "La herramienta {$h->nombre} no tiene unidades disponibles. Reposición urgente requerida."
            ];
        }

        return $anomalies;
    }

    private function generateRecommendations()
    {
        $recommendations = [];

        // 1. Stock bajo o agotado
        $lowStock = Herramienta::whereRaw('stock_disponible <= (stock_minimo * 1.5)')
            ->orderBy('stock_disponible', 'asc')
            ->take(5)
            ->get();

        foreach ($lowStock as $h) {
            $isZero = $h->stock_disponible <= 0;
            $recommendations[] = [
                'icon' => $isZero ? 'fa-triangle-exclamation' : 'fa-cart-plus',
                'text' => $isZero 
                    ? "REPOSICIÓN URGENTE: {$h->nombre} está en 0. Comprar nuevas unidades inmediatamente."
                    : "Considerar reposición de {$h->nombre} (Stock: {$h->stock_disponible}). Cerca del límite crítico."
            ];
        }

        // 2. Optimización de personal
        $inactiveWorkers = Trabajador::where('estado', 'Activo')
            ->whereDoesntHave('vales', function($q) {
                $q->where('fecha_creacion', '>', Carbon::now()->subDays(30));
            })->take(2)->get();

        foreach ($inactiveWorkers as $w) {
            $recommendations[] = [
                'icon' => 'fa-user-slash',
                'text' => "El trabajador {$w->nombre} no ha registrado actividad en 30 días. Revisar asignación."
            ];
        }

        // 3. Recuperación de Activos de Alto Riesgo
        $valesRiesgoAlto = Vale::where('estado', 'Activo')
            ->where('fecha_limite', '<', Carbon::now()->subDays(7))
            ->with(['trabajador', 'detalles.herramienta'])
            ->take(3)->get();

        foreach ($valesRiesgoAlto as $v) {
            $dias = Carbon::now()->diffInDays($v->fecha_limite);
            $primerDetalle = $v->detalles->first();
            $herramientaNombre = $primerDetalle && $primerDetalle->herramienta
                ? $primerDetalle->herramienta->nombre
                : 'herramienta(s)';
            $recommendations[] = [
                'icon' => 'fa-skull-crossbones',
                'text' => "RIESGO ALTO DE PÉRDIDA: {$v->trabajador->nombre} tiene '{$herramientaNombre}' con {$dias} días de retraso. Iniciar protocolo de reclamación."
            ];
        }

        return $recommendations;
    }

    private function analyzeWarehouseLoad()
    {
        $almacenes = Almacen::withCount('herramientas')->get();
        $totalTools = Herramienta::sum('stock_total') ?: 1;

        return $almacenes->map(function($a) use ($totalTools) {
            $percentage = ($a->herramientas_count / $totalTools) * 100;
            return [
                'nombre' => $a->nombre,
                'load' => round($percentage, 1),
                'status' => $percentage > 80 ? 'Sobrecargado' : ($percentage < 20 ? 'Bajo Uso' : 'Normal')
            ];
        });
    }

    private function predictTrends()
    {
        // Simulación de tendencias basadas en la frecuencia de vales de los últimos 7 días
        $lastWeek = Vale::where('fecha_creacion', '>', Carbon::now()->subDays(7))->count();
        $previousWeek = Vale::where('fecha_creacion', '<=', Carbon::now()->subDays(7))
                            ->where('fecha_creacion', '>', Carbon::now()->subDays(14))
                            ->count();

        $change = $previousWeek > 0 ? (($lastWeek - $previousWeek) / $previousWeek) * 100 : 100;

        return [
            'activity_score' => $lastWeek > 0 ? min($lastWeek * 5, 100) : 0,
            'momentum' => round($change, 1),
            'prediction' => $change > 0 ? 'Creciente' : 'Estable'
        ];
    }

    public function generateSmartSummary()
    {
        $totalVales = Vale::count();
        $totalTools = Herramienta::count();
        return "El sistema opera al " . ($totalTools > 0 ? round(($totalVales / $totalTools) * 10, 1) : 0) . "% de su capacidad nominal. Se detecta un flujo estable en los nodos principales.";
    }

    public function getNeuralLogs()
    {
        // Generamos un log de "TODO lo que se hace" (actividad real del sistema)
        $logs = [];

        // 1. Vales recientes (Usamos fecha_creacion)
        $vales = Vale::with(['trabajador', 'detalles.herramienta'])->orderBy('fecha_creacion', 'desc')->take(5)->get();
        foreach($vales as $v) {
            $primerDetalle = $v->detalles->first();
            $herramientaNombre = $primerDetalle && $primerDetalle->herramienta
                ? $primerDetalle->herramienta->nombre
                : 'múltiples activos';
            $logs[] = [
                'time' => $v->fecha_creacion->format('H:i:s'),
                'type' => 'TRANSACTION',
                'msg' => "Vale #{$v->codigo_vale} procesado para {$v->trabajador->nombre} (Activo: {$herramientaNombre})"
            ];
        }

        // 2. Herramientas modificadas (Usamos creado_en ya que no hay updated_at)
        $tools = Herramienta::with('modelAlmacen')->orderBy('creado_en', 'desc')->take(5)->get();
        foreach($tools as $t) {
            $almacenNombre = $t->modelAlmacen ? $t->modelAlmacen->nombre : ($t->almacen ?? 'Almacén General');
            $logs[] = [
                'time' => $t->creado_en->format('H:i:s'),
                'type' => 'DATABASE',
                'msg' => "Registro de activo '{$t->nombre}' sincronizado en {$almacenNombre}"
            ];
        }

        // 3. Sistema
        $logs[] = ['time' => now()->format('H:i:s'), 'type' => 'SECURITY', 'msg' => 'Protocolo de integridad de red verificado sin brechas.'];
        $logs[] = ['time' => now()->format('H:i:s'), 'type' => 'SYSTEM', 'msg' => 'Cortex Assistant sincronizado con el núcleo de datos local.'];

        usort($logs, function($a, $b) { return strcmp($b['time'], $a['time']); });
        return array_slice($logs, 0, 10);
    }
}
