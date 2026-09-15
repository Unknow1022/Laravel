<?php
/**
 * Limpia la caché de vistas compiladas de Laravel.
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/clear_views.php
 */

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html><html><head><title>Limpiando Vistas</title></head><body style='background:#0a0a1a;color:#fff;font-family:monospace;padding:30px;'>";
echo "<h2>🧹 Limpiando caché de vistas de Laravel...</h2><pre>";

try {
    // Limpiar vistas compiladas
    $status = $kernel->call('view:clear');
    echo "✓ Vistas compiladas eliminadas con éxito.\n";
    
    // Limpiar caché de rutas y configuración
    $kernel->call('config:clear');
    $kernel->call('route:clear');
    echo "✓ Configuración y rutas limpiadas.\n";
    
    echo "\n✅ Proceso completado exitosamente.";
} catch (\Exception $e) {
    echo "❌ Error al ejecutar el comando: " . $e->getMessage() . "\n";
}

echo "</pre><br><a href='dashboard' style='color:#64ffda;font-size:18px;'>Volver al Dashboard</a></body></html>";
