<?php
/**
 * Lee el log de errores de Laravel del servidor.
 * Abre: http://localhost/laravel_app/public/ver_log.php
 * Luego sube por FTP y abre: https://almacen-inteligente.infinityfreeapp.com/public/ver_log.php
 */
$log_path = dirname(__DIR__) . '/storage/logs/laravel.log';

header('Content-Type: text/html; charset=UTF-8');
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Laravel Log</title>";
echo "<style>body{background:#0a0a1a;color:#e0e0e0;font-family:monospace;padding:20px;font-size:13px;}
pre{background:#111;padding:15px;border-radius:8px;overflow:auto;max-height:90vh;white-space:pre-wrap;word-break:break-all;}
.err{color:#ff6b6b;} .warn{color:#ffd93d;} .info{color:#6bcb77;}
h2{color:#64b5f6;}</style></head><body>";

echo "<h2>📋 Laravel Error Log — últimas 100 líneas</h2>";

if (!file_exists($log_path)) {
    echo "<p style='color:#ff6b6b;'>El archivo de log NO existe: {$log_path}</p>";
    
    // Intentar crear uno
    $dir = dirname($log_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    echo "<p>Directorio storage/logs: " . (is_dir($dir) ? '✓ Existe' : '✗ No existe') . "</p>";
    echo "<p>PHP Version: " . PHP_VERSION . "</p>";
    echo "<p>Base path: " . dirname(__DIR__) . "</p>";
    
    // Mostrar clases disponibles
    echo "<h3>Archivos en app/Services:</h3><pre>";
    $services_path = dirname(__DIR__) . '/app/Services';
    if (is_dir($services_path)) {
        foreach (scandir($services_path) as $f) {
            if ($f !== '.' && $f !== '..') echo $f . "\n";
        }
    } else {
        echo "Directorio app/Services NO existe";
    }
    echo "</pre>";
} else {
    $lines = file($log_path);
    $total = count($lines);
    $last = array_slice($lines, max(0, $total - 150));
    
    echo "<p>Total líneas en log: <strong>{$total}</strong></p>";
    echo "<pre>";
    foreach ($last as $line) {
        if (str_contains($line, 'ERROR') || str_contains($line, 'CRITICAL') || str_contains($line, 'Exception')) {
            echo "<span class='err'>" . htmlspecialchars($line) . "</span>";
        } elseif (str_contains($line, 'WARNING')) {
            echo "<span class='warn'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
}
echo "</body></html>";
