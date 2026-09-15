<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SystemValidatorService;
use App\Services\CortexAutomationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CortexRescueController extends Controller
{
    /**
     * Comprueba si la petición es local o tiene la clave de rescate correcta.
     */
    protected function validateRescueAccess(Request $request)
    {
        $allowedIps = ['127.0.0.1', '::1'];
        $clientIp = $request->ip();

        // Si es de localhost, se permite automáticamente
        if (in_array($clientIp, $allowedIps)) {
            return true;
        }

        // Si está configurada una clave de rescate en el .env, verificarla
        $rescueKey = env('CORTEX_RESCUE_KEY');
        if ($rescueKey && $request->input('key') === $rescueKey) {
            return true;
        }

        // Si no, denegar acceso
        return false;
    }

    /**
     * Muestra la interfaz de rescate con el estado del sistema.
     */
    public function index(Request $request)
    {
        if (!$this->validateRescueAccess($request)) {
            abort(403, 'Acceso denegado: La consola de rescate de Cortex solo es accesible desde localhost o con una clave válida.');
        }

        $validator = new SystemValidatorService();
        $diagnostic = $validator->runFullDiagnostic();

        return view('cortex.rescue', compact('diagnostic'));
    }

    /**
     * Ejecuta la reparación autónoma para un componente.
     */
    public function repair(Request $request, $component)
    {
        if (!$this->validateRescueAccess($request)) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $validator = new SystemValidatorService();
        $automation = new CortexAutomationService();

        try {
            $message = "";

            switch ($component) {
                case 'database':
                    // 1. Ejecutar migraciones
                    Artisan::call('migrate', ['--force' => true]);
                    $message = "Migraciones sincronizadas exitosamente. ";
                    
                    // 2. Ejecutar optimización de tablas
                    try {
                        $optMessage = $automation->executeAuthorizedRepair('optimize_db');
                        $message .= $optMessage;
                    } catch (\Exception $e) {
                        $message .= " Nota: No se pudo optimizar las tablas (posiblemente porque la BD está vacía o sin inicializar).";
                    }
                    break;

                case 'storage':
                    // Limpiar caches
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');
                    Artisan::call('config:clear');
                    
                    // Ejecutar purga de cache desde automatización
                    $message = $automation->executeAuthorizedRepair('purge_cache');
                    break;

                case 'security':
                    // Ajustar permisos de directorios
                    $message = $automation->executeAuthorizedRepair('fix_permissions');
                    break;

                case 'models':
                    // Limpieza de datos huérfanos simulada/real
                    $message = $validator->repairComponent('models');
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'Componente desconocido.'], 400);
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            Log::error("Fallo en Consola de Rescate para {$component}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
