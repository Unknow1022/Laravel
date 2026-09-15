<?php
/**
 * Script local definitivo con division en partes y correccion de rutas de Linux.
 * Abre esta URL en tu PC: http://localhost/laravel_app/public/subir_ftp.php
 */

set_time_limit(0);
ini_set('memory_limit', '1024M');

// Forzar salida en vivo
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
echo "<!DOCTYPE html><html><head><title>Subidor FTP Inteligente</title>
<style>
body { background:#0a192f; color:#ccd6f6; font-family: monospace; padding: 2rem; }
pre  { background:#112240; padding: 1.5rem; border-radius: 10px; overflow-x:auto; font-size:.9rem; line-height:1.6; }
h2   { color:#64ffda; }
.ok  { color:#64ffda; font-weight:bold; }
.err { color:#ef4444; }
.info { color:#8892b0; }
</style></head><body>";
echo "<h2>🚀 Creando y Subiendo Proyecto por Partes (Corregido para Linux)</h2>";
echo "<p style='color:#8892b0;'>Ya identificamos el problema. El script ahora empaquetará las carpetas usando barras diagonales de Linux y subirá todo de forma rápida. No te preocupes por el tiempo de espera anterior.</p><pre>";

$rootPath = dirname(__DIR__);
$zipPath = $rootPath . '/proyecto_almacen_completo.zip';

// ── PASO 1: COMPRIMIR EL PROYECTO ──────────────────────────────────────────
echo "▶ Paso 1/4: Comprimiendo archivos en formato Linux...\n";
flush();

if (file_exists($zipPath)) { @unlink($zipPath); }

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("<span class='err'>❌ ERROR: No se pudo crear el archivo ZIP.</span></pre></body></html>");
}

$foldersToInclude = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor'];
$filesToInclude = [
    'artisan', 'composer.json', 'composer.lock', 'package.json', 
    'Procfile', 'nixpacks.toml', 'apache.conf', 'vite.config.js',
    'public/run_test.php', 'public/run_migration.php'
];

foreach ($filesToInclude as $file) {
    $filePath = $rootPath . '/' . $file;
    if (file_exists($filePath)) { 
        $zip->addFile($filePath, $file); 
    }
}

foreach ($foldersToInclude as $folder) {
    $folderPath = $rootPath . '/' . $folder;
    if (!is_dir($folderPath)) continue;
    
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            if (strpos($filePath, 'crear_zip.php') !== false || 
                strpos($filePath, 'subir_ftp.php') !== false ||
                strpos($filePath, 'proyecto_almacen') !== false ||
                strpos($filePath, 'node_modules') !== false ||
                strpos($filePath, '.git') !== false) {
                continue;
            }
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            
            // 🚨 CRÍTICO: Reemplazar contrabarras de Windows por barras de Linux para que cree carpetas en internet
            $relativePath = str_replace('\\', '/', $relativePath);
            
            $zip->addFile($filePath, $relativePath);
        }
    }
}
$zip->close();
$sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
echo "✅ ZIP creado con éxito en formato Linux ({$sizeMB} MB).\n";
flush();


// ── PASO 2: DIVIDIR EL ZIP EN CHUNKS DE 8 MB ────────────────────────────────
echo "\n▶ Paso 2/4: Dividiendo el archivo en partes de 8 MB...\n";
flush();

$chunkSize = 8 * 1024 * 1024; // 8 MB
$handle = fopen($zipPath, 'rb');
$partNum = 1;
$parts = [];

while (!feof($handle)) {
    $buffer = fread($handle, $chunkSize);
    if (empty($buffer)) break;
    $partPath = $zipPath . ".part" . $partNum;
    file_put_contents($partPath, $buffer);
    $parts[] = $partPath;
    echo "   + Creada parte {$partNum} (" . round(strlen($buffer)/1024/1024, 2) . " MB)\n";
    flush();
    $partNum++;
}
fclose($handle);
@unlink($zipPath);


// ── PASO 3: CONECTAR AL FTP Y SUBIR unzip.php CON AUTOLIMPIEZA ──────────────
$ftp_server   = "ftpupload.net";
$ftp_username = "if0_42375917";
$ftp_password = "kHcjQcw8PQ";

$local_unzip_code = '<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

$finalZip = "proyecto_almacen_completo.zip";
echo "<h2>📦 Limpiando y Reconstruyendo Proyecto en el Servidor...</h2>";

// Limpiar archivos anteriores con contrabarras basura de Windows
echo "▶ Limpiando archivos basura anteriores...\n";
$dir = __DIR__;
$files = scandir($dir);
foreach ($files as $file) {
    if ($file === "." || $file === ".." || $file === ".env" || $file === ".htaccess" || $file === "unzip.php" || strpos($file, "proyecto_almacen_completo.zip") !== false) {
        continue;
    }
    // Borrar archivos
    if (is_file($dir . "/" . $file)) {
        @unlink($dir . "/" . $file);
    }
}
echo "✅ Servidor limpio.\n\n";

// 1. Unir las partes
$out = fopen($finalZip, "wb");
if (!$out) { die("❌ Error: No se pudo crear el archivo final en el servidor."); }

for ($i = 1; ; $i++) {
    $partFile = "proyecto_almacen_completo.zip.part" . $i;
    if (!file_exists($partFile)) {
        break;
    }
    echo "   * Uniendo parte {$i}...\n";
    $in = fopen($partFile, "rb");
    stream_copy_to_stream($in, $out);
    fclose($in);
    @unlink($partFile);
}
fclose($out);
echo "✅ Archivo ZIP reconstruido correctamente (" . round(filesize($finalZip)/1024/1024, 2) . " MB).\n";

// 2. Extraer el ZIP
$zip = new ZipArchive;
if ($zip->open($finalZip) === TRUE) {
    echo "⏳ Extrayendo archivos en la estructura de carpetas correctas...\n";
    $zip->extractTo(__DIR__);
    $zip->close();
    @unlink($finalZip); // Borrar el zip
    
    // 🚨 LIMPIAR CONFIG CACHED: Borrar archivos de cache para evitar error 500 de rutas absolutas locales
    echo "▶ Limpiando caché local de Laravel...\n";
    $cacheFiles = [
        __DIR__ . "/bootstrap/cache/config.php",
        __DIR__ . "/bootstrap/cache/routes-v7.php",
        __DIR__ . "/bootstrap/cache/services.php",
        __DIR__ . "/bootstrap/cache/packages.php"
    ];
    foreach ($cacheFiles as $cf) {
        if (file_exists($cf)) {
            @unlink($cf);
            echo "   - Borrado: " . basename($cf) . "\n";
        }
    }
    echo "<h3 style=\"color:green;\">✅ ¡Descompresión completada con éxito!</h3>";
} else {
    echo "<h3 style=\"color:red;\">❌ Error abriendo el archivo ZIP reconstruido.</h3>";
}
';

echo "\n▶ Paso 3/4: Conectando al servidor FTP...\n";
flush();
$conn_id = ftp_connect($ftp_server);
if (!$conn_id || !ftp_login($conn_id, $ftp_username, $ftp_password)) {
    die("<span class='err'>❌ ERROR: Conexión FTP fallida.</span></pre></body></html>");
}
ftp_pasv($conn_id, true);

// Subir unzip.php
$temp_unzip = tempnam(sys_get_temp_dir(), 'unzip');
file_put_contents($temp_unzip, $local_unzip_code);
ftp_put($conn_id, "/htdocs/unzip.php", $temp_unzip, FTP_BINARY);
@unlink($temp_unzip);
echo "✅ Conexión FTP lista y script extractor (unzip.php) subido.\n";
flush();


// ── PASO 4: SUBIR CADA PARTE INDIVIDUALMENTE ────────────────────────────────
echo "\n▶ Paso 4/4: Subiendo las partes individuales por FTP...\n";
flush();

$uploadOk = true;
foreach ($parts as $index => $partPath) {
    $partName = basename($partPath);
    echo "   * Subiendo parte " . ($index + 1) . " de " . count($parts) . " ({$partName})...\n";
    flush();
    
    $maxRetries = 3;
    $partUploaded = false;
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        // Verificar conexión, si se cayó, volver a conectar
        if (!$conn_id) {
            $conn_id = ftp_connect($ftp_server);
            if ($conn_id) {
                @ftp_login($conn_id, $ftp_username, $ftp_password);
                ftp_pasv($conn_id, true);
            }
        }
        
        if ($conn_id && @ftp_put($conn_id, "/htdocs/" . $partName, $partPath, FTP_BINARY)) {
            $partUploaded = true;
            break;
        } else {
            echo "     ⚠️ Intento {$attempt}/{$maxRetries} falló. Reconectando en 3 segundos...\n";
            flush();
            if ($conn_id) {
                @ftp_close($conn_id);
                $conn_id = null;
            }
            sleep(3);
        }
    }
    
    if ($partUploaded) {
        echo "     ✅ Parte " . ($index + 1) . " subida.\n";
    } else {
        echo "     ❌ ERROR definitivo al subir la parte " . ($index + 1) . "\n";
        $uploadOk = false;
    }
    @unlink($partPath);
    flush();
}
if ($conn_id) {
    @ftp_close($conn_id);
}

if ($uploadOk) {
    echo "\n<span class='ok'>======================================================\n";
    echo "🎉 ¡TODAS LAS PARTES SUBIDAS CON ÉXITO EN FORMATO LINUX!\n";
    echo "======================================================\n</span>";
    echo "Haz clic en este enlace para reconstruir y descomprimir todo en el servidor:\n";
    echo "👉 <a href='https://almacen-inteligente.infinityfreeapp.com/unzip.php' target='_blank' style='color:#64ffda;font-weight:bold;text-decoration:underline;'>RECONSTRUIR Y DESCOMPRIMIR EN EL SERVIDOR</a>\n\n";
} else {
    echo "<span class='err'>❌ Hubo un error al subir alguna de las partes. Por favor refresca la página para intentarlo de nuevo.</span>\n";
}
echo "</pre></body></html>";
