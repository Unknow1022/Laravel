<?php

namespace App\Observers;

use App\Mail\EventoSistemaMail;
use App\Models\Log as AuditLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as LaravelLog;

class LogObserver
{
    const ADMIN_EMAIL = 'jesusmanuelriveragarcia6@gmail.com';

    /**
     * Se dispara cada vez que se crea un nuevo registro en la tabla `logs`.
     * Envía un correo al administrador con los detalles del evento.
     */
    public function created(AuditLog $log): void
    {
        // Evitar notificar eventos rutinarios que generarían demasiado spam
        // (lectura de listas, aperturas normales de páginas sin acción)
        $accionLower = strtolower($log->accion ?? '');
        $skip = ['vio', 'listó', 'consultó', 'accedió a lista', 'abrió'];
        foreach ($skip as $s) {
            if (str_contains($accionLower, $s)) return;
        }

        // Construir el nombre del usuario responsable
        $usuarioNombre = null;
        try {
            if ($log->usuario_id) {
                $usuario = \App\Models\Usuario::find($log->usuario_id);
                $usuarioNombre = $usuario
                    ? ($usuario->nombre ?? $usuario->email ?? "Usuario #{$log->usuario_id}")
                    : "Usuario #{$log->usuario_id}";
            }
        } catch (\Exception $e) {
            $usuarioNombre = "Usuario #{$log->usuario_id}";
        }

        try {
            Mail::to(self::ADMIN_EMAIL)->send(new EventoSistemaMail(
                accion:      $log->accion ?? 'Evento',
                tabla:       $log->tabla  ?? 'sistema',
                descripcion: $log->descripcion ?? '',
                usuario:     $usuarioNombre,
                fecha:       $log->fecha
                    ? \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i:s')
                    : now()->format('d/m/Y H:i:s'),
                itemId:      $log->item_id,
            ));

            LaravelLog::info("[LogObserver] Correo enviado — Acción: {$log->accion} | Tabla: {$log->tabla}");

        } catch (\Exception $e) {
            // No lanzar excepción para no interrumpir el flujo normal del sistema
            LaravelLog::error("[LogObserver] Error enviando correo: " . $e->getMessage());
        }
    }
}
