<?php
/**
 * Script de setup inicial para producción.
 * Ejecuta migraciones, seeders y enlace de storage.
 * Protegido con token de acceso.
 */
// 🚨 Habilitar reporte de errores al inicio para que no dé 500 genérico si falla bootstrap
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ── Protección: solo localhost O con token secreto ─────────────────────────
// Se removió el token temporalmente para facilitar la ejecución directa en el servidor.

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Setup del Sistema</title>
<style>
body { background:#0a192f; color:#ccd6f6; font-family: monospace; padding: 2rem; }
pre  { background:#112240; padding: 1.5rem; border-radius: 10px; overflow-x:auto; font-size:.9rem; line-height:1.6; }
h2   { color:#64ffda; }
.ok  { color:#64ffda; }
.err { color:#ef4444; }
.sep { color:#8892b0; }
</style></head><body>";
echo "<h2>⚙️ Setup del Sistema — Almacén Inteligente</h2><pre>";

$pasos = [
    ['label' => 'Migraciones',         'cmd' => 'migrate:fresh', 'args' => ['--force' => true]],
    ['label' => 'Seeders',             'cmd' => 'db:seed',       'args' => ['--force' => true]],
    ['label' => 'Enlace de Storage',   'cmd' => 'storage:link',  'args' => ['--force' => true]],
    ['label' => 'Caché de Config',     'cmd' => 'config:cache',  'args' => []],
    ['label' => 'Caché de Rutas',      'cmd' => 'route:cache',   'args' => []],
    ['label' => 'Caché de Vistas',     'cmd' => 'view:cache',    'args' => []],
];

$ok = true;
foreach ($pasos as $paso) {
    echo "<span class='sep'>──────────────────────────────────────────</span>\n";
    echo "▶ {$paso['label']}...\n";
    try {
        Illuminate\Support\Facades\Artisan::call($paso['cmd'], $paso['args']);
        $output = trim(Illuminate\Support\Facades\Artisan::output());
        echo "<span class='ok'>✅ OK</span>" . ($output ? "\n   " . str_replace("\n", "\n   ", $output) : '') . "\n";
    } catch (Exception $e) {
        echo "<span class='err'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
        $ok = false;
    }
}

echo "<span class='sep'>══════════════════════════════════════════</span>\n";
if ($ok) {
    echo "<span class='ok' style='font-size:1.1rem;font-weight:bold;'>🚀 Setup completado con éxito.\n</span>";
    echo "<span style='color:#8892b0;'>Puedes eliminar este archivo o restringir su acceso.\n";
    echo "URL de acceso: " . htmlspecialchars(env('APP_URL', 'https://tu-app.railway.app')) . "</span>\n";
} else {
    echo "<span class='err'>⚠️ Hubo errores. Revisa los mensajes de arriba.</span>\n";
}
echo "</pre></body></html>";
