<?php
/**
 * PHP Script to create a COMPLETE deployment ZIP file (includes vendor, streams progress).
 * Run this by visiting: http://localhost/laravel_app/public/crear_zip.php
 */

// Desactivar límite de tiempo y habilitar salida en vivo
set_time_limit(0);
ini_set('memory_limit', '1024M');

// Forzar salida en vivo en el navegador
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
ob_implicit_flush(true);
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Creador de ZIP Completo</title>
<style>
body { background:#0a192f; color:#ccd6f6; font-family: monospace; padding: 2rem; }
pre  { background:#112240; padding: 1.5rem; border-radius: 10px; overflow-x:auto; font-size:.9rem; line-height:1.6; }
h2   { color:#64ffda; }
.ok  { color:#64ffda; font-weight:bold; }
.err { color:#ef4444; }
.info { color:#8892b0; }
</style></head><body>";
echo "<h2>📦 Creando ZIP Completo (Código + Dependencias Vendor)</h2>";
echo "<p style='color:#8892b0;'>Este proceso toma 1-2 minutos. No cierres la ventana.</p><pre>";

$zipPath = dirname(__DIR__) . '/proyecto_almacen_completo.zip';
if (file_exists($zipPath)) {
    @unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("<span class='err'>❌ ERROR: No se pudo abrir/crear el archivo ZIP.</span></pre></body></html>");
}

$rootPath = dirname(__DIR__);
$foldersToInclude = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor'];
$filesToInclude = [
    'artisan', 'composer.json', 'composer.lock', 'package.json', 
    'Procfile', 'nixpacks.toml', 'apache.conf', 'vite.config.js'
];

echo "▶ Agregando archivos raíz...\n";
foreach ($filesToInclude as $file) {
    $filePath = $rootPath . '/' . $file;
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $file);
        echo "   + {$file}\n";
    }
}
flush();

echo "\n▶ Comprimiendo carpetas principales (esto empaqueta vendor)...\n";
flush();

foreach ($foldersToInclude as $folder) {
    $folderPath = $rootPath . '/' . $folder;
    if (!is_dir($folderPath)) continue;

    echo "   * Procesando carpeta: <b style='color:#64ffda;'>{$folder}</b>...\n";
    flush();

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $count = 0;
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            
            // Excluir archivos innecesarios
            if (strpos($filePath, 'crear_zip.php') !== false || 
                strpos($filePath, 'proyecto_almacen') !== false ||
                strpos($filePath, 'node_modules') !== false ||
                strpos($filePath, '.git') !== false) {
                continue;
            }
            
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            $zip->addFile($filePath, $relativePath);
            
            $count++;
            if ($count % 2500 === 0) {
                echo "     - Agregados {$count} archivos...\n";
                flush();
            }
        }
    }
    echo "     ✅ Finalizado: {$folder} ({$count} archivos añadidos)\n";
    flush();
}

echo "\n▶ Escribiendo y guardando el archivo ZIP en el disco...\n";
flush();

if ($zip->close()) {
    $sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
    echo "<span class='ok'>======================================================\n";
    echo "✅ ¡ZIP COMPLETO CREADO EXITOSAMENTE!\n";
    echo "   Archivo: proyecto_almacen_completo.zip\n";
    echo "   Tamaño: {$sizeMB} MB\n";
    echo "======================================================\n</span>";
    echo "<span class='info'>\nSiguiente paso:\n1. Sube 'proyecto_almacen_completo.zip' a tu htdocs en el File Manager.\n2. Descomprímelo con la opción 'Upload & Unzip'.</span>\n";
} else {
    echo "<span class='err'>❌ ERROR: No se pudo guardar el archivo ZIP final.</span>\n";
}

echo "</pre></body></html>";
