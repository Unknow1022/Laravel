<?php

namespace App\Services;

use App\Models\Herramienta;
use App\Models\Vale;
use App\Models\Trabajador;
use App\Models\ValeDetalle;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CortexQueryService
{
    public function processQuery(string $query)
    {
        $query = Str::lower(trim($query));
        $query = $this->removeAccents($query);

        // 0. LISTA DE HERRAMIENTAS (Ayuda de memoria)
        if (Str::contains($query, ['ver herramientas', 'lista de herramientas', 'que herramientas hay', 'catalogo'])) {
            return $this->handleListToolsQuery();
        }

        // 1. LOCALIZACIÓN ("dónde está", "quién tiene")
        if (Str::contains($query, ['donde esta', 'quien tiene', 'ubicacion de'])) {
            return $this->handleLocationQuery($query);
        }

        // 2. PENDIENTES / RETRASADAS ("no devueltas", "retrasadas", "pendientes")
        if (Str::contains($query, ['no devueltas', 'retrasadas', 'pendientes'])) {
            return $this->handlePendingQuery($query);
        }

        // 3. STOCK ("cuantos", "disponibles", "stock bajo", "agotadas")
        if (Str::contains($query, ['cuantos', 'cuantas', 'disponibles', 'hay', 'stock'])) {
            return $this->handleStockQuery($query);
        }
        if (Str::contains($query, ['agotada', 'agotados'])) {
            return $this->handleOutOfStockQuery();
        }

        // 4. REPORTES ("mas utilizadas", "mas extraviadas", "trabajadores con", "reporte")
        if (Str::contains($query, ['mas utilizadas', 'mas prestadas'])) {
            return $this->handleMostUsedQuery();
        }
        if (Str::contains($query, ['mas retrasos', 'mas demoras'])) {
            return $this->handleMostDelayedWorkersQuery();
        }

        // Default
        return [
            'type' => 'text',
            'message' => 'No logré entender completamente tu solicitud. Intenta preguntar cosas como: "¿Dónde está el Taladro Bosch?", "¿Qué herramientas están retrasadas?" o "Ver lista de herramientas".'
        ];
    }

    private function handleListToolsQuery()
    {
        $herramientas = Herramienta::select('nombre', 'stock_disponible', 'stock_minimo')->orderBy('nombre')->get();
        if($herramientas->isEmpty()) return ['type' => 'text', 'message' => 'No hay herramientas registradas en el catálogo.'];

        $html = "<div class='mb-3'><i class='fa-solid fa-layer-group text-primary mr-2'></i><strong style='color: #E6F1FF;'>Catálogo de Herramientas</strong></div>";
        $html .= "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; max-height: 350px; overflow-y: auto; padding-right: 8px; margin-bottom: 10px;' class='custom-scrollbar'>";
        
        foreach($herramientas as $h) {
            $isLow = $h->stock_disponible <= $h->stock_minimo;
            $isZero = $h->stock_disponible == 0;
            $color = $isZero ? '#F43F5E' : ($isLow ? '#F59E0B' : '#64FFDA');
            $bgIcon = $isZero ? 'rgba(244,63,94,0.1)' : ($isLow ? 'rgba(245,158,11,0.1)' : 'rgba(100,255,218,0.1)');
            $icon = $isZero ? 'fa-triangle-exclamation' : 'fa-wrench';

            $html .= "
            <div style='background: rgba(10, 25, 47, 0.7); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 12px; transition: all 0.3s ease; backdrop-filter: blur(5px);' 
                 onmouseover=\"this.style.transform='translateY(-3px)'; this.style.borderColor='{$color}'; this.style.boxShadow='0 5px 15px {$bgIcon}'\" 
                 onmouseout=\"this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.05)'; this.style.boxShadow='none'\">
                <div style='min-width: 42px; height: 42px; border-radius: 10px; background: {$bgIcon}; display: flex; justify-content: center; align-items: center; color: {$color}; font-size: 1.1rem;'>
                   <i class='fa-solid {$icon}'></i>
                </div>
                <div style='overflow: hidden;'>
                   <div style='font-weight: 600; font-size: 0.85rem; color: #E6F1FF; line-height: 1.3; margin-bottom: 4px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;' title='{$h->nombre}'>{$h->nombre}</div>
                   <div style='font-size: 0.75rem; color: {$color}; font-weight: 500;'><i class='fa-solid fa-cubes mr-1'></i> Stock: {$h->stock_disponible}</div>
                </div>
            </div>";
        }
        $html .= "</div>";
        $html .= "<div style='font-size: 0.75rem; color: #8892B0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 8px;'><i class='fa-solid fa-circle-info mr-1'></i> Puedes preguntarme por la disponibilidad o ubicación de cualquiera de ellas.</div>";

        return ['type' => 'html', 'html' => $html];
    }

    private function handleLocationQuery(string $query)
    {
        // Extraer posible nombre de la herramienta omitiendo "donde esta el/la"
        $wordsToRemove = ['donde', 'esta', 'quien', 'tiene', 'ubicacion', 'de', 'el', 'la', 'los', 'las', 'un', 'una', '?'];
        $searchTerm = trim(str_replace($wordsToRemove, '', $query));

        if (empty($searchTerm)) {
            return ['type' => 'text', 'message' => 'Por favor especifica el nombre de la herramienta. Ejemplo: ¿Dónde está el Taladro?'];
        }

        $herramientas = Herramienta::where('nombre', 'LIKE', "%{$searchTerm}%")->get();

        if ($herramientas->isEmpty()) {
            return ['type' => 'text', 'message' => "No encontré ninguna herramienta llamada '{$searchTerm}' en el catálogo. Escribe 'ver lista de herramientas' si no recuerdas el nombre."];
        }

        $herramientaIds = $herramientas->pluck('id');

        $valesActivos = Vale::whereHas('detalles', function($q) use ($herramientaIds) {
                $q->whereIn('herramienta_id', $herramientaIds);
            })
            ->where('estado', 'Activo')
            ->with(['trabajador', 'detalles.herramienta'])
            ->get();

        if ($valesActivos->isEmpty()) {
            return [
                'type' => 'text', 
                'message' => "La herramienta '{$searchTerm}' actualmente NO está prestada. Debería encontrarse en su almacén correspondiente."
            ];
        }

        $html = "<p>He localizado <strong>" . $valesActivos->count() . "</strong> vale(s) activo(s) con esa herramienta:</p>";
        $html .= "<table class='cortex-table'><thead><tr><th>Herramientas (Cant.)</th><th>Trabajador</th><th>Fecha Préstamo</th><th>Estado</th></tr></thead><tbody>";
        foreach ($valesActivos as $vale) {
            $isOverdue = Carbon::now()->greaterThan($vale->fecha_limite);
            $status = $isOverdue ? "<span class='text-danger'>RETRASADA</span>" : "<span class='text-warning'>PRESTADA</span>";
            
            // Extraer solo las herramientas solicitadas de este vale
            $hNombres = [];
            foreach($vale->detalles as $d) {
                if(in_array($d->herramienta_id, $herramientaIds->toArray())) {
                    $hNombres[] = "{$d->herramienta->nombre} (x{$d->cantidad_prestada})";
                }
            }
            $toolsStr = implode(', ', $hNombres);

            $html .= "<tr>
                <td>{$toolsStr}</td>
                <td>{$vale->trabajador->nombre}</td>
                <td>{$vale->fecha_creacion->format('d/m/Y')}</td>
                <td>{$status}</td>
            </tr>";
        }
        $html .= "</tbody></table>";

        return ['type' => 'html', 'html' => $html];
    }

    private function handlePendingQuery(string $query)
    {
        $valesVencidos = Vale::where('estado', 'Activo')
            ->where('fecha_limite', '<', Carbon::now())
            ->with(['trabajador', 'detalles.herramienta'])
            ->get();

        if ($valesVencidos->isEmpty()) {
            return ['type' => 'text', 'message' => '¡Buenas noticias! No hay herramientas con devolución retrasada en este momento.'];
        }

        $html = "<div class='mb-3'><i class='fa-solid fa-clock-rotate-left text-danger mr-2'></i><strong style='color: #E6F1FF;'>Vales Retrasados (" . $valesVencidos->count() . ")</strong></div>";
        $html .= "<div style='display: grid; grid-template-columns: 1fr; gap: 10px; max-height: 350px; overflow-y: auto; padding-right: 8px; margin-bottom: 10px;' class='custom-scrollbar'>";
        
        foreach ($valesVencidos as $vale) {
            $diasRetraso = Carbon::parse($vale->fecha_limite)->diffInDays(Carbon::now());
            
            // Calculo de riesgo básico
            $riesgo = 'BAJO';
            $color = '#10B981';
            $bgRiesgo = 'rgba(16, 185, 129, 0.1)';
            if ($diasRetraso >= 7) { $riesgo = 'ALTO'; $color = '#F43F5E'; $bgRiesgo = 'rgba(244, 63, 94, 0.1)'; }
            elseif ($diasRetraso >= 3) { $riesgo = 'MEDIO'; $color = '#F59E0B'; $bgRiesgo = 'rgba(245, 158, 11, 0.1)'; }

            $hNombres = $vale->detalles->map(function($d) { return $d->herramienta->nombre; })->implode(', ');

            $html .= "
            <div style='background: rgba(10, 25, 47, 0.7); border: 1px solid rgba(255,255,255,0.05); border-left: 4px solid {$color}; border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease; backdrop-filter: blur(5px);' onmouseover=\"this.style.background='rgba(17, 34, 64, 0.9)'\" onmouseout=\"this.style.background='rgba(10, 25, 47, 0.7)'\">
                <div style='flex-grow: 1;'>
                    <div style='font-weight: bold; color: #E6F1FF; font-size: 0.95rem; margin-bottom: 4px;'><i class='fa-solid fa-user-circle text-muted mr-1'></i> {$vale->trabajador->nombre}</div>
                    <div style='font-size: 0.8rem; color: #8892B0;'><i class='fa-solid fa-screwdriver-wrench text-primary mr-1'></i> {$hNombres}</div>
                </div>
                <div style='text-align: right; min-width: 110px;'>
                    <div style='font-weight: 900; color: {$color}; font-size: 1.2rem;'>{$diasRetraso} <span style='font-size: 0.7rem; font-weight: normal; opacity: 0.8;'>DÍAS</span></div>
                    <div style='font-size: 0.65rem; padding: 3px 8px; border-radius: 12px; background: {$bgRiesgo}; color: {$color}; display: inline-block; font-weight: bold; margin-top: 4px; border: 1px solid rgba(255,255,255,0.1); letter-spacing: 0.5px;'>RIESGO {$riesgo}</div>
                </div>
            </div>";
        }
        $html .= "</div>";
        
        $html .= "<div style='font-size: 0.75rem; color: #F59E0B; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 8px;'><i class='fa-solid fa-triangle-exclamation mr-1'></i> <strong>ACCIÓN REQUERIDA:</strong> Solicitar devolución inmediata de los vales en riesgo ALTO.</div>";

        return ['type' => 'html', 'html' => $html];
    }

    private function handleStockQuery(string $query)
    {
        if (Str::contains($query, ['stock bajo'])) {
            $lowStock = Herramienta::whereRaw('stock_disponible <= (stock_minimo * 1.5)')->where('stock_disponible', '>', 0)->get();
            if ($lowStock->isEmpty()) return ['type' => 'text', 'message' => 'Actualmente ninguna herramienta tiene el stock bajo.'];
            
            $html = "<div class='mb-3'><i class='fa-solid fa-arrow-trend-down text-warning mr-2'></i><strong style='color: #E6F1FF;'>Alertas de Stock Bajo</strong></div>";
            $html .= "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 8px;' class='custom-scrollbar'>";
            foreach ($lowStock as $h) {
                $html .= "
                <div style='background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 12px; text-align: center; transition: all 0.2s;' onmouseover=\"this.style.background='rgba(245, 158, 11, 0.1)'\" onmouseout=\"this.style.background='rgba(245, 158, 11, 0.05)'\">
                    <div style='font-weight: bold; color: #E6F1FF; font-size: 0.9rem; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='{$h->nombre}'>{$h->nombre}</div>
                    <div style='display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 8px;'>
                        <div style='font-size: 0.75rem; color: #8892B0;'>Actual: <strong style='color: #F59E0B; font-size: 0.9rem;'>{$h->stock_disponible}</strong></div>
                        <div style='font-size: 0.75rem; color: #8892B0;'>Mínimo: <strong>{$h->stock_minimo}</strong></div>
                    </div>
                </div>";
            }
            $html .= "</div>";
            $html .= "<div style='font-size: 0.75rem; color: #F59E0B; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 8px; margin-top: 10px;'><i class='fa-solid fa-lightbulb mr-1'></i> RECOMENDACIÓN: Reponer unidades a la brevedad.</div>";
            return ['type' => 'html', 'html' => $html];
        }

        // Buscar stock de una herramienta específica
        $wordsToRemove = ['cuantos', 'cuantas', 'disponibles', 'hay', 'de', 'stock', 'el', 'la', 'los', 'las', '?'];
        $searchTerm = trim(str_replace($wordsToRemove, '', $query));

        if (empty($searchTerm)) {
            return ['type' => 'text', 'message' => '¿De qué herramienta deseas consultar el stock? (Pide "ver lista de herramientas" si no recuerdas el nombre)'];
        }

        $herramientas = Herramienta::where('nombre', 'LIKE', "%{$searchTerm}%")->get();
        
        if ($herramientas->isEmpty()) {
            return ['type' => 'text', 'message' => "No encontré herramientas bajo el nombre '{$searchTerm}'."];
        }

        $html = "<div class='mb-3'><i class='fa-solid fa-magnifying-glass text-primary mr-2'></i><strong style='color: #E6F1FF;'>Resultados para '{$searchTerm}'</strong></div>";
        $html .= "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 8px;' class='custom-scrollbar'>";
        foreach ($herramientas as $h) {
            $hasStock = $h->stock_disponible > 0;
            $color = $hasStock ? '#64FFDA' : '#F43F5E';
            $bg = $hasStock ? 'rgba(100,255,218,0.05)' : 'rgba(244,63,94,0.05)';
            $html .= "
            <div style='background: {$bg}; border: 1px solid {$color}; opacity: 0.8; border-radius: 8px; padding: 12px; text-align: center;'>
                <div style='font-weight: bold; color: #E6F1FF; font-size: 0.9rem; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='{$h->nombre}'>{$h->nombre}</div>
                <div style='font-size: 1.5rem; font-weight: 900; color: {$color}; line-height: 1;'>{$h->stock_disponible}</div>
                <div style='font-size: 0.65rem; color: #8892B0; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;'>Disponibles de {$h->stock_total} totales</div>
            </div>";
        }
        $html .= "</div>";

        return ['type' => 'html', 'html' => $html];
    }

    private function handleOutOfStockQuery()
    {
        $outOfStock = Herramienta::where('stock_disponible', '<=', 0)->get();
        if ($outOfStock->isEmpty()) return ['type' => 'text', 'message' => 'Excelente. No hay ninguna herramienta agotada.'];
        
        $html = "<div class='mb-3'><i class='fa-solid fa-ban text-danger mr-2'></i><strong style='color: #F43F5E;'>Herramientas AGOTADAS</strong></div>";
        $html .= "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 8px;' class='custom-scrollbar'>";
        foreach ($outOfStock as $h) {
            $html .= "
            <div style='background: rgba(244,63,94,0.05); border: 1px solid rgba(244,63,94,0.3); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 10px;'>
                <div style='width: 30px; height: 30px; border-radius: 50%; background: rgba(244,63,94,0.1); display: flex; justify-content: center; align-items: center; color: #F43F5E;'><i class='fa-solid fa-xmark'></i></div>
                <div style='overflow: hidden;'>
                    <div style='font-weight: bold; color: #E6F1FF; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='{$h->nombre}'>{$h->nombre}</div>
                    <div style='font-size: 0.7rem; color: #8892B0;'>Stock Mínimo: {$h->stock_minimo}</div>
                </div>
            </div>";
        }
        $html .= "</div>";
        return ['type' => 'html', 'html' => $html];
    }

    private function handleMostUsedQuery()
    {
        $herramientas = ValeDetalle::with('herramienta')
            ->select('herramienta_id', \DB::raw('sum(cantidad_prestada) as total'))
            ->groupBy('herramienta_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        if ($herramientas->isEmpty()) return ['type' => 'text', 'message' => 'Aún no hay suficientes datos de vales para generar este reporte.'];

        $html = "<p>Top 5 Herramientas más prestadas históricamente:</p>";
        $html .= "<table class='cortex-table'><thead><tr><th>Herramienta</th><th>Unidades Prestadas (Total Histórico)</th></tr></thead><tbody>";
        foreach ($herramientas as $item) {
            $nombre = $item->herramienta ? $item->herramienta->nombre : 'Desconocida';
            $html .= "<tr><td>{$nombre}</td><td>{$item->total} uds</td></tr>";
        }
        $html .= "</tbody></table>";
        return ['type' => 'html', 'html' => $html];
    }

    private function handleMostDelayedWorkersQuery()
    {
        $trabajadores = Vale::where('estado', 'Devuelto')
            ->whereNotNull('fecha_devolucion')
            ->whereColumn('fecha_devolucion', '>', 'fecha_limite')
            ->with('trabajador')
            ->select('trabajador_id', \DB::raw('count(*) as retrasos'))
            ->groupBy('trabajador_id')
            ->orderByDesc('retrasos')
            ->take(5)
            ->get();

        if ($trabajadores->isEmpty()) return ['type' => 'text', 'message' => 'Aún no hay registros de trabajadores con historial de retrasos documentados en el sistema.'];

        $html = "<p>Trabajadores con más incidencias de retraso históricas:</p>";
        $html .= "<table class='cortex-table'><thead><tr><th>Trabajador</th><th>Devoluciones Tardías</th></tr></thead><tbody>";
        foreach ($trabajadores as $item) {
            $nombre = $item->trabajador ? $item->trabajador->nombre : 'Desconocido';
            $html .= "<tr><td>{$nombre}</td><td class='text-danger'>{$item->retrasos} retrasos</td></tr>";
        }
        $html .= "</tbody></table>";
        $html .= "<p class='mt-2 small text-muted'><i class='fa-solid fa-lightbulb text-warning'></i> RECOMENDACIÓN: Identificar a estos trabajadores para advertir sobre políticas de entrega.</p>";
        
        return ['type' => 'html', 'html' => $html];
    }

    private function removeAccents($string) {
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        return strtr( $string, $unwanted_array );
    }
}
