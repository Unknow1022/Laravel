<?php
/**
 * Ejecuta SOLO las migraciones pendientes (sin borrar datos).
 * Usar cuando ya tienes datos y solo quieres agregar columnas nuevas.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Migración Pendiente</title>
<style>
body{background:#0a192f;color:#ccd6f6;font-family:monospace;padding:2rem;}
pre{background:#112240;padding:1.5rem;border-radius:10px;overflow-x:auto;font-size:.9rem;line-height:1.6;}
h2{color:#64ffda;}.ok{color:#64ffda;}.err{color:#ef4444;}
a{display:inline-block;margin-top:1.5rem;padding:.75rem 2rem;background:rgba(100,255,218,.1);
  color:#64ffda;border:1px solid #64ffda;border-radius:8px;text-decoration:none;font-size:1rem;}
</style></head><body>";

echo "<h2>⚙️ Ejecutando migraciones pendientes...</h2><pre>";

try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output = trim(Illuminate\Support\Facades\Artisan::output());
    echo "<span class='ok'>✅ OK</span>\n";
    echo htmlspecialchars($output) . "\n";
} catch (Exception $e) {
    echo "<span class='err'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

echo "\n<span class='ok'>✅ Migración completada. Ya puedes iniciar sesión.</span>";
echo "</pre>";
echo "<a href='/public/login'>→ Ir al Login del Sistema</a>";
echo "</body></html>";
