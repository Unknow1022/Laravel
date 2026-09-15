<?php
set_time_limit(0);
ini_set('display_errors', 1);

$ftp_server   = "ftpupload.net";
$ftp_username = "if0_42375917";
$ftp_password = "kHcjQcw8PQ";
$remote_base  = "/htdocs";
$local_base   = dirname(__DIR__);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Subiendo Correcciones y Rediseño v2.0</title>";
echo "<style>
body{background:#0d9488;color:#ffffff;font-family:monospace;padding:20px;font-size:14px;}
h2{color:#ffffff;}h3{color:#ffffff;margin-top:20px;}
.container{background:#ffffff;color:#1e293b;padding:25px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.2);}
.ok{color:#059669;font-weight:bold;}.err{color:#dc2626;font-weight:bold;}
a{color:#ffffff;background:#0d9488;display:inline-block;margin:5px 0;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;}
a:hover{background:#0f766e;}
pre{background:#f8fafc;padding:15px;border-radius:8px;border:1px solid #e2e8f0;line-height:1.7;}
</style></head><body><div class='container'><pre>";

echo "<h2>🚀 Subiendo Solución de Sesión (419 Fix) + Rediseño v2.0 al Hosting...</h2>\n";

$files = [
    // Manejo de excepciones y 419 Fix
    $local_base . '/bootstrap/app.php'
        => $remote_base . '/bootstrap/app.php',

    // Hojas de estilo actualizadas
    $local_base . '/public/css/style.css'
        => $remote_base . '/public/css/style.css',

    $local_base . '/public/css/table.css'
        => $remote_base . '/public/css/table.css',

    $local_base . '/public/css/responsive.css'
        => $remote_base . '/public/css/responsive.css',

    // Vistas principales y Auth
    $local_base . '/resources/views/auth/login.blade.php'
        => $remote_base . '/resources/views/auth/login.blade.php',

    $local_base . '/resources/views/layouts/app.blade.php'
        => $remote_base . '/resources/views/layouts/app.blade.php',

    $local_base . '/resources/views/dashboard.blade.php'
        => $remote_base . '/resources/views/dashboard.blade.php',

    $local_base . '/resources/views/cortex/index.blade.php'
        => $remote_base . '/resources/views/cortex/index.blade.php',
];

$conn = ftp_connect($ftp_server, 21, 30);
if (!$conn) { echo "<span class='err'>✗ No se pudo conectar al servidor FTP.</span>"; exit; }
$login = ftp_login($conn, $ftp_username, $ftp_password);
if (!$login) { echo "<span class='err'>✗ Credenciales FTP incorrectas.</span>"; exit; }
ftp_pasv($conn, true);
echo "<span class='ok'>✓ Conexión FTP establecida con éxito.</span>\n\n";

// Crear directorios de framework si no existen
$dirsToEnsure = [
    '/htdocs/storage/framework/sessions',
    '/htdocs/storage/framework/views',
    '/htdocs/storage/framework/cache',
    '/htdocs/storage/framework/cache/data',
];

foreach ($dirsToEnsure as $dir) {
    @ftp_mkdir($conn, $dir);
}

$exito = 0; $fallo = 0;
foreach ($files as $local => $remote) {
    $basename = basename($local);
    if (!file_exists($local)) {
        echo "<span class='err'>✗ No se encontró archivo local: $basename</span>\n"; $fallo++; continue;
    }
    if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "<span class='ok'>✓ Subido correctamente:</span> $remote\n"; $exito++;
    } else {
        echo "<span class='err'>✗ Error al subir: $remote</span>\n"; $fallo++;
    }
}
ftp_close($conn);
echo "\n";

if ($fallo === 0) {
    echo "<span class='ok'>🎉 ¡Solución al Error 419 + Rediseño v2.0 subidos con éxito!</span>\n</pre>";
    echo "<h3>⚡ Pasos finales obligatorios:</h3>";
    echo "<p>1. Ejecuta la limpieza de caché en el hosting:</p>";
    echo "<a href='https://almacen-inteligente.infinityfreeapp.com/public/clear_all_cache.php' target='_blank'>👉 Limpiar Caché en el Hosting</a><br><br>";
    echo "<p>2. Abre el login en el hosting y recarga (Ctrl + F5 para renovar la sesión de tu navegador):</p>";
    echo "<a href='https://almacen-inteligente.infinityfreeapp.com/public/login' target='_blank'>🚀 Abrir Login en Producción</a>";
} else {
    echo "<span class='err'>⚠️ Algunos archivos fallaron al subirse. Revisa el log superior.</span>\n</pre>";
}

echo "</div></body></html>";
