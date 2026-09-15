<?php
/**
 * Limpia el caché completo del servidor (config, vistas, caché de app).
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/clear_all_cache.php
 */

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request  = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$kernel->terminate($request, $response);

header('Content-Type: text/html; charset=utf-8');
$ok  = fn(string $t) => "<div style='color:#00ff88;margin:3px 0'>✓ {$t}</div>";
$err = fn(string $t) => "<div style='color:#ff4444;margin:3px 0'>✗ {$t}</div>";
$inf = fn(string $t) => "<div style='color:#64ffda;margin:3px 0'>ℹ {$t}</div>";

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Clear Cache</title>
<style>body{background:#070f1a;color:#cfd8e3;font-family:monospace;padding:24px;font-size:14px;line-height:1.7}
h2{color:#64ffda}h3{color:#e6f1ff}.card{background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:18px;margin-bottom:18px}
a{color:#00ff88;display:inline-block;margin:6px 0;padding:9px 18px;border:1px solid #00ff88;border-radius:6px;text-decoration:none;font-weight:bold}
</style></head><body><h2>🧹 Limpieza completa de caché</h2><div class='card'>";

$limpiados = 0;

// 1. Caché de notificaciones de herramientas (elimina el cooldown de 1 hora)
try {
    $keys = \Illuminate\Support\Facades\Cache::get('_cortex_notif_keys', []);
    \Illuminate\Support\Facades\Cache::flush();
    echo $ok("Caché de aplicación vaciada (notificaciones de herramientas reiniciadas)");
    $limpiados++;
} catch (\Exception $e) {
    echo $err("Error vaciando caché: " . htmlspecialchars($e->getMessage()));

    // Intentar eliminar archivos de caché manualmente
    $cacheDir = __DIR__ . '/../storage/framework/cache/data';
    if (is_dir($cacheDir)) {
        $deleted = 0;
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iter as $file) {
            if ($file->isFile()) { unlink($file->getPathname()); $deleted++; }
        }
        echo $ok("Eliminados {$deleted} archivos de caché manualmente");
        $limpiados++;
    }
}

// 2. Caché de vistas compiladas
$viewCache = __DIR__ . '/../storage/framework/views';
if (is_dir($viewCache)) {
    $deleted = 0;
    foreach (glob($viewCache . '/*.php') as $f) { unlink($f); $deleted++; }
    echo $ok("Vistas compiladas eliminadas ({$deleted} archivos)");
    $limpiados++;
}

// 3. Caché de configuración
$configCache = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($configCache)) { unlink($configCache); echo $ok("Config cache eliminada"); $limpiados++; }

// 4. Caché de rutas
$routesCache = __DIR__ . '/../bootstrap/cache/routes-v7.php';
if (file_exists($routesCache)) { unlink($routesCache); echo $ok("Routes cache eliminada"); $limpiados++; }

echo "<br><b style='color:#10B981;font-size:1.05em'>✅ Caché limpiada ({$limpiados} operaciones). Sistema listo para re-enviar notificaciones.</b>";
echo "</div>";

echo "<h3>Siguientes pasos:</h3>";
echo "<a href='https://almacen-inteligente.infinityfreeapp.com/public/test_stock_mail.php' target='_blank'>🧪 Probar envío ahora (test_stock_mail.php)</a><br>";
echo "<a href='https://almacen-inteligente.infinityfreeapp.com/public/cortex' target='_blank'>🤖 Ir al Cortex (dispara auto-notificación)</a>";

echo "<p style='color:#64748b;font-size:12px;margin-top:24px'>Cortex NOC — clear_all_cache.php · " . date('d/m/Y H:i:s') . "</p>";
echo "</body></html>";
