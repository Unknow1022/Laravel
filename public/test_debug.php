<?php
/**
 * Script de diagnóstico avanzado de Laravel.
 * Bottea el framework atrapando excepciones para revelar errores 500 en producción.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Diagnóstico de Laravel</title>
<style>
body { background:#0f172a; color:#cbd5e1; font-family:monospace; padding:2rem; }
pre { background:#1e293b; padding:1rem; border-radius:8px; overflow:auto; color:#f8fafc; }
h2 { color:#38bdf8; }
.ok { color:#4ade80; font-weight:bold; }
.err { color:#f87171; font-weight:bold; }
</style></head><body>";

echo "<h2>🔍 Diagnóstico de Inicialización de Laravel</h2>";

// 1. Verificar y corregir permisos de storage
echo "▶ Verificando permisos de escritura en Storage...\n<br>";
$directories = [
    __DIR__ . '/../storage',
    __DIR__ . '/../storage/app',
    __DIR__ . '/../storage/framework',
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    // Intentar aplicar permisos de escritura
    @chmod($dir, 0777);
    $writable = is_writable($dir);
    $status = $writable ? "<span class='ok'>[Escribible]</span>" : "<span class='err'>[No Escribible]</span>";
    echo "   * Folder: " . basename($dir) . " -> {$status}<br>";
}

// 2. Cargar Autoload
echo "<br>▶ Cargando Autoload de Composer...\n<br>";
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    die("<span class='err'>❌ ERROR: No se encuentra vendor/autoload.php. Vuelve a subir el proyecto.</span></body></html>");
}
require $autoload;
echo "✅ Autoload cargado.<br>";

// 3. Bootear Laravel
echo "<br>▶ Inicializando Laravel Application...\n<br>";
try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "<span class='ok'>✅ Laravel booteado con éxito. El entorno está listo.</span><br>";
    
    // Probar a resolver una ruta básica o ver si hay algún error interno
    echo "<br>▶ Probando acceso a base de datos por Laravel...\n<br>";
    $dbName = Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    echo "✅ Conectado a la base de datos de Laravel: <b style='color:#38bdf8;'>{$dbName}</b><br>";
    
} catch (Throwable $e) {
    echo "<span class='err'>❌ ERROR DETECTADO DURANTE EL BOOTSTRAP:</span><br>";
    echo "<pre style='border: 1px solid #f87171;'>";
    echo "Mensaje: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Archivo: " . htmlspecialchars($e->getFile()) . " en línea " . $e->getLine() . "\n\n";
    echo "Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>";
}

echo "</body></html>";
