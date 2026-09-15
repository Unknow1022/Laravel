<?php
/**
 * Script de diagnóstico de ValesController.php en producción.
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/check_controller.php
 */

echo "<!DOCTYPE html><html><head><title>Check ValesController</title></head><body style='background:#0a0a1a;color:#e0e0e0;font-family:monospace;padding:30px;'>";
echo "<h2>🔍 Verificando código de ValesController.php en producción...</h2><pre>";

$file_path = __DIR__ . '/../app/Http/Controllers/ValesController.php';

if (!file_exists($file_path)) {
    echo "❌ El archivo no existe en: $file_path";
} else {
    $content = file_get_contents($file_path);
    
    // Buscar la línea 327 o donde se asigna el estado
    echo "<strong>Líneas con asignación de 'estado':</strong>\n";
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        if (strpos($line, 'estado') !== false) {
            echo "Línea " . ($num + 1) . ": " . htmlspecialchars(trim($line)) . "\n";
        }
    }
}

echo "</pre></body></html>";
