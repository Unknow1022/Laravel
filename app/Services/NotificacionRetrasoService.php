<?php

namespace App\Services;

use App\Mail\ValeRetrasadoMail;
use App\Mail\HerramientaAgotadaMail;
use App\Models\Vale;
use App\Models\Herramienta;
use App\Models\ValeDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class NotificacionRetrasoService
{
    /**
     * Dirección de correo del administrador que recibe las alertas.
     */
    const ADMIN_EMAIL = 'jesusmanuelriveragarcia6@gmail.com';

    /**
     * Verifica vales activos con fecha límite vencida y envía un correo
     * de notificación por cada uno que aún no haya sido notificado.
     * También verifica herramientas con stock agotado o crítico.
     *
     * Retorna el número de correos enviados en esta ejecución.
     */
    public function verificarYNotificar(): int
    {
        $enviados = 0;

        $enviados += $this->notificarValesRetrasados();
        $enviados += $this->notificarHerramientasAgotadas();

        return $enviados;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  VALES RETRASADOS
    // ─────────────────────────────────────────────────────────────────────

    private function notificarValesRetrasados(): int
    {
        if (!Schema::hasColumn('vales', 'notificado_retraso')) {
            return 0;
        }

        $enviados = 0;

        $valesRetrasados = Vale::where('estado', 'Activo')
            ->where('fecha_limite', '<', Carbon::now())
            ->where('notificado_retraso', false)
            ->with(['trabajador', 'detalles.herramienta'])
            ->get();

        foreach ($valesRetrasados as $vale) {
            try {
                Mail::to(self::ADMIN_EMAIL)
                    ->send(new ValeRetrasadoMail($vale));

                $vale->update(['notificado_retraso' => true]);

                $nombre = optional($vale->trabajador)->nombre . ' ' . optional($vale->trabajador)->apellidos;
                Log::info("[NotificacionRetraso] Correo enviado para vale {$vale->codigo_vale} — Trabajador: {$nombre}");

                $enviados++;
            } catch (\Exception $e) {
                Log::error("[NotificacionRetraso] Error vale {$vale->codigo_vale}: " . $e->getMessage());
            }
        }

        return $enviados;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  HERRAMIENTAS AGOTADAS / STOCK CRÍTICO
    // ─────────────────────────────────────────────────────────────────────

    private function notificarHerramientasAgotadas(): int
    {
        $enviados = 0;

        // Cache key para evitar spam: cada herramienta se notifica máx. 1 vez por hora
        $cachePrefix = 'notif_herramienta_';

        // 1. Herramientas completamente agotadas (stock_disponible = 0)
        $agotadas = Herramienta::where('stock_disponible', '<=', 0)
            ->with('modelAlmacen')
            ->get();

        foreach ($agotadas as $herramienta) {
            $cacheKey = $cachePrefix . 'agotada_' . $herramienta->id;

            if (Cache::has($cacheKey)) {
                continue; // Ya fue notificada recientemente
            }

            try {
                $trabajadores = $this->getTrabajadoresConHerramienta($herramienta->id);

                Mail::to(self::ADMIN_EMAIL)
                    ->send(new HerramientaAgotadaMail($herramienta, $trabajadores, 'agotada'));

                // No volver a notificar en 60 minutos
                Cache::put($cacheKey, true, now()->addHours(1));

                Log::info("[NotificacionStock] Correo AGOTADA enviado para herramienta [{$herramienta->codigo}] {$herramienta->nombre}");
                $enviados++;

            } catch (\Exception $e) {
                Log::error("[NotificacionStock] Error herramienta {$herramienta->codigo}: " . $e->getMessage());
            }
        }

        // 2. Herramientas en nivel crítico (stock_disponible <= stock_minimo y > 0)
        $criticas = Herramienta::where('stock_disponible', '>', 0)
            ->whereColumn('stock_disponible', '<=', 'stock_minimo')
            ->with('modelAlmacen')
            ->get();

        foreach ($criticas as $herramienta) {
            $cacheKey = $cachePrefix . 'critica_' . $herramienta->id;

            if (Cache::has($cacheKey)) {
                continue;
            }

            try {
                $trabajadores = $this->getTrabajadoresConHerramienta($herramienta->id);

                Mail::to(self::ADMIN_EMAIL)
                    ->send(new HerramientaAgotadaMail($herramienta, $trabajadores, 'critica'));

                Cache::put($cacheKey, true, now()->addHours(1));

                Log::info("[NotificacionStock] Correo CRÍTICA enviado para herramienta [{$herramienta->codigo}] {$herramienta->nombre}");
                $enviados++;

            } catch (\Exception $e) {
                Log::error("[NotificacionStock] Error herramienta {$herramienta->codigo}: " . $e->getMessage());
            }
        }

        return $enviados;
    }

    /**
     * Obtiene la lista de trabajadores que actualmente tienen unidades
     * de la herramienta en préstamo activo (vales en estado Activo).
     */
    private function getTrabajadoresConHerramienta(int $herramientaId): array
    {
        try {
            $detalles = ValeDetalle::where('herramienta_id', $herramientaId)
                ->whereHas('vale', fn($q) => $q->where('estado', 'Activo'))
                ->with(['vale.trabajador'])
                ->get();

            $trabajadores = [];
            foreach ($detalles as $det) {
                $vale       = $det->vale;
                $trabajador = optional($vale)->trabajador;

                $trabajadores[] = [
                    'nombre'       => $trabajador
                        ? trim($trabajador->nombre . ' ' . $trabajador->apellidos)
                        : 'Trabajador desconocido',
                    'dni'          => optional($trabajador)->dni ?? '—',
                    'cargo'        => optional($trabajador)->cargo ?? '',
                    'telefono'     => optional($trabajador)->telefono ?? '',
                    'vale_codigo'  => optional($vale)->codigo_vale ?? '—',
                    'fecha_limite' => optional($vale)->fecha_limite
                        ? Carbon::parse($vale->fecha_limite)->format('d/m/Y H:i')
                        : '—',
                    'cantidad'     => $det->cantidad_prestada ?? 1,
                ];
            }

            return $trabajadores;
        } catch (\Exception $e) {
            Log::error("[NotificacionStock] Error obteniendo trabajadores: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  CONTADORES (para el panel de Cortex)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Retorna cuántos vales activos vencidos hay en total.
     */
    public function contarRetrasados(): int
    {
        if (!Schema::hasColumn('vales', 'notificado_retraso')) {
            return Vale::where('estado', 'Activo')
                ->where('fecha_limite', '<', Carbon::now())
                ->count();
        }

        return Vale::where('estado', 'Activo')
            ->where('fecha_limite', '<', Carbon::now())
            ->count();
    }

    /**
     * Retorna cuántas herramientas con stock agotado o crítico hay.
     */
    public function contarHerramientasAlerta(): int
    {
        try {
            $agotadas = Herramienta::where('stock_disponible', '<=', 0)->count();
            $criticas = Herramienta::where('stock_disponible', '>', 0)
                ->whereColumn('stock_disponible', '<=', 'stock_minimo')
                ->count();
            return $agotadas + $criticas;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
