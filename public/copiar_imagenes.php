<?php
/**
 * Copia las capturas de pantalla de la carpeta temporal a la carpeta del proyecto.
 * Abre: http://localhost/laravel_app/public/copiar_imagenes.php
 */

$gemini_dir = "C:/Users/jesus/.gemini/antigravity-ide/brain/d43da97b-682e-46b7-9b1d-dc0c802d18e8";
$target_dir = __DIR__ . '/images/reporte';

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$files = [
    'login.png'     => $gemini_dir . '/login_page_1784296500961.png',
    'dashboard.png' => $gemini_dir . '/dashboard_page_1784296522576.png',
    'tools.png'     => $gemini_dir . '/tools_page_1784296533297.png',
    'workers.png'   => $gemini_dir . '/workers_page_1784296548114.png',
    'vales.png'     => $gemini_dir . '/vales_page_1784296561778.png',
    'cortex.png'    => $gemini_dir . '/cortex_page_1784296581321.png',
];

echo "<!DOCTYPE html><html><head><title>Copiador de Imagenes</title></head><body style='background:#0a0a1a;color:#fff;font-family:monospace;padding:30px;'>";
echo "<h2>copying images...</h2><pre>";

$exito = 0;
foreach ($files as $name => $src) {
    $dst = $target_dir . '/' . $name;
    if (file_exists($src)) {
        if (copy($src, $dst)) {
            echo "✓ Copiado: $name\n";
            $exito++;
        } else {
            echo "✗ Error al copiar: $name\n";
        }
    } else {
        echo "✗ No existe archivo origen: $src\n";
    }
}

echo "\nTotal de imágenes copiadas: $exito/6";
echo "</pre></body></html>";
