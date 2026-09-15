<?php
/**
 * Genera imágenes SVG del sistema sin GD ni ninguna extensión PHP.
 * Abre: http://localhost/laravel_app/public/capturar.php
 */

$dir = __DIR__ . '/images/reporte';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// ──────────────────────────────────────────────────────────────────────
// Función: genera un SVG limpio usando solo concatenación de strings
// ──────────────────────────────────────────────────────────────────────
function makeSVG(string $titulo, string $url, array $headers, array $rows): string
{
    $w = 900; $h = 460;
    $colW = ($w - 60) / max(count($headers), 1);

    // Fila de encabezados de la tabla
    $thRow = '<rect x="30" y="170" width="' . ($w-60) . '" height="26" fill="#1565c0"/>';
    foreach ($headers as $i => $hdr) {
        $x = 30 + $i * $colW + 8;
        $thRow .= '<text x="' . $x . '" y="188" fill="#ffffff" font-size="10" font-weight="bold" font-family="Arial">' . htmlspecialchars($hdr) . '</text>';
    }

    // Filas de datos
    $dataRows = '';
    $fills = ['#0a1929', '#0d2137'];
    foreach ($rows as $ri => $row) {
        $y = 196 + $ri * 26;
        $dataRows .= '<rect x="30" y="' . $y . '" width="' . ($w-60) . '" height="26" fill="' . $fills[$ri % 2] . '"/>';
        foreach (array_values($row) as $ci => $cell) {
            $x = 30 + $ci * $colW + 8;
            $textY = $y + 17;
            $fill = ($ci === 0) ? '#90caf9' : '#cfd8e3';
            if (str_contains($cell, 'RETRAS') || str_contains($cell, 'AGOTADO') || str_contains($cell, 'Error')) {
                $fill = '#ef5350';
            }
            if (str_contains($cell, 'Activo') || str_contains($cell, 'Disponible') || str_contains($cell, 'OK ') || str_ends_with($cell, 'OK')) {
                $fill = '#66bb6a';
            }
            $dataRows .= '<text x="' . $x . '" y="' . $textY . '" fill="' . $fill . '" font-size="9.5" font-family="Arial">' . htmlspecialchars($cell) . '</text>';
        }
    }

    // Ancho de tabla para KPIs visuales (no necesario, usamos tabla simple)
    $svg  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '">' . "\n";
    // Fondo
    $svg .= '<rect width="' . $w . '" height="' . $h . '" fill="#070f1a"/>' . "\n";
    // Barra nav superior
    $svg .= '<rect width="' . $w . '" height="48" fill="#0d1b2a"/>' . "\n";
    $svg .= '<text x="18" y="30" fill="#64ffda" font-size="13" font-weight="bold" font-family="Arial">Sistema Almacen Inteligente — CORTEX</text>' . "\n";
    $svg .= '<text x="700" y="30" fill="#90caf9" font-size="10" font-family="Arial">admin | Administrador</text>' . "\n";
    // Barra URL
    $svg .= '<rect y="48" width="' . $w . '" height="26" fill="#061428"/>' . "\n";
    $svg .= '<text x="18" y="65" fill="#90caf9" font-size="10" font-family="Arial">URL: ' . htmlspecialchars($url) . '</text>' . "\n";
    $svg .= '<text x="750" y="65" fill="#4caf50" font-size="10" font-family="Arial">● Operativo</text>' . "\n";
    // Título de sección
    $svg .= '<text x="' . ($w/2) . '" y="135" fill="#ffffff" font-size="15" font-weight="bold" font-family="Arial" text-anchor="middle">' . htmlspecialchars($titulo) . '</text>' . "\n";
    $svg .= '<line x1="200" y1="145" x2="700" y2="145" stroke="#1565c0" stroke-width="1.5"/>' . "\n";
    // Tabla
    $svg .= $thRow . "\n";
    $svg .= $dataRows . "\n";
    // Pie
    $svg .= '<text x="18" y="' . ($h-8) . '" fill="#546e7a" font-size="8" font-family="Arial">IESTP Luciano Castillo Colonna — 2026</text>' . "\n";
    $svg .= '</svg>';

    return $svg;
}

// ──────────────────────────────────────────────────────────────────────
// Definición de las 6 pantallas
// ──────────────────────────────────────────────────────────────────────
$imagenes = [

    'login' => makeSVG(
        'Pantalla de Inicio de Sesion Seguro',
        '/public/login',
        ['Campo', 'Tipo de Input', 'Proteccion de Seguridad'],
        [
            ['Campo' => 'Correo electronico',  'Tipo de Input' => 'Input email',    'Proteccion de Seguridad' => 'Sanitizacion XSS + Laravel Validation'],
            ['Campo' => 'Contrasena',           'Tipo de Input' => 'Input password', 'Proteccion de Seguridad' => 'Bcrypt Hash 12 rondas'],
            ['Campo' => 'Token CSRF',           'Tipo de Input' => 'Hidden field',   'Proteccion de Seguridad' => 'Laravel @csrf (proteccion CSRF)'],
            ['Campo' => 'Boton Ingresar',       'Tipo de Input' => 'Submit',         'Proteccion de Seguridad' => 'Rate Limit: max 5 intentos/minuto'],
        ]
    ),

    'dashboard' => makeSVG(
        'Dashboard Principal — Panel de Control',
        '/public/dashboard',
        ['Indicador (KPI)', 'Valor Actual', 'Descripcion', 'Estado'],
        [
            ['Vales Activos',         '12',        'Prestamos de herramientas en curso',    'OK '],
            ['Herramientas en Stock', '148 items', 'Total de herramientas inventariadas',   'OK '],
            ['Vales Retrasados',      '3',         'Vales que superaron la fecha limite',   'RETRASADO'],
            ['Trabajadores Activos',  '24',        'Personal con acceso al almacen',        'Activo'],
        ]
    ),

    'tools' => makeSVG(
        'Catalogo de Herramientas — Inventario',
        '/public/herramientas',
        ['Codigo', 'Herramienta', 'Almacen', 'Categoria', 'Stock Disponible', 'Estado'],
        [
            ['HRR-001', 'Taladro Percutor',    'Almacen A', 'Electricidad', '3 / 5 uds', 'Disponible'],
            ['HRR-002', 'Llave Francesa 12"',  'Almacen B', 'Mecanica',     '8 /10 uds', 'Disponible'],
            ['HRR-003', 'Esmeriladora Angul.', 'Almacen A', 'Metalurgia',   '1 / 2 uds', 'Disponible'],
            ['HRR-004', 'Martillo de Goma',    'Almacen C', 'Carpinteria',  '0 / 3 uds', 'AGOTADO'],
            ['HRR-005', 'Alicate Universal',   'Almacen B', 'Electricidad', '6 / 8 uds', 'Disponible'],
        ]
    ),

    'workers' => makeSVG(
        'Gestion de Personal — Expediente de Trabajadores',
        '/public/trabajadores',
        ['DNI', 'Apellidos y Nombres', 'Cargo', 'Almacen Asignado', 'Estado'],
        [
            ['12345678', 'Perez Torres, Juan',   'Tecnico A',  'Almacen A', 'Activo'],
            ['87654321', 'Lopez Diaz, Maria',    'Operario',   'Almacen B', 'Activo'],
            ['11223344', 'Ruiz Castro, Carlos',  'Supervisor', 'Almacen C', 'Activo'],
            ['55667788', 'Flores Medina, Ana',   'Operario',   'Almacen A', 'Activo'],
        ]
    ),

    'vales' => makeSVG(
        'Registro de Vales de Salida — Prestamos Activos',
        '/public/vales',
        ['Codigo', 'Trabajador', 'Fecha Emision', 'Fecha Limite', 'Herramientas', 'Estado'],
        [
            ['V-00045', 'Perez Torres, Juan',   '17/07/2026', '19/07/2026', '2 items', 'Activo'],
            ['V-00044', 'Lopez Diaz, Maria',    '10/07/2026', '12/07/2026', '3 items', 'RETRASADO'],
            ['V-00043', 'Ruiz Castro, Carlos',  '17/07/2026', '20/07/2026', '1 item',  'Activo'],
        ]
    ),

    'cortex' => makeSVG(
        'Cortex NOC — Centro de Operaciones y Auditoria',
        '/public/cortex',
        ['Componente del Sistema', 'Servidor / Ubicacion', 'Latencia', 'Estado'],
        [
            ['Base de Datos MySQL',     'sql110.infinityfree.com', '24 ms', 'OK '],
            ['Sesiones en BD',          'tabla: sessions',         '8 ms',  'OK '],
            ['Almacenamiento en Disco', 'htdocs/storage/',         '—',     'OK '],
            ['Rutas de Aplicacion',     'Artisan Route List',      '—',     'OK '],
            ['Modelos Eloquent',        '9 modelos validados',     '—',     'OK '],
        ]
    ),

];

// ──────────────────────────────────────────────────────────────────────
// Guardar todos los SVG en disco
// ──────────────────────────────────────────────────────────────────────
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Generador de Capturas</title></head>';
echo '<body style="background:#070f1a;color:#cfd8e3;font-family:monospace;padding:30px;">';
echo '<h2 style="color:#64ffda;">🖼️ Generando imágenes SVG del sistema...</h2><pre>';

$ok = 0;
foreach ($imagenes as $nombre => $svgContenido) {
    $ruta = $dir . '/' . $nombre . '.svg';
    if (file_put_contents($ruta, $svgContenido) !== false) {
        echo '✓ Generado: ' . $nombre . ".svg\n";
        $ok++;
    } else {
        echo '✗ Error al guardar: ' . $nombre . ".svg — verifica permisos de escritura\n";
    }
}

$total = count($imagenes);
echo "\n";
if ($ok === $total) {
    echo "✅ Todas las imágenes generadas ({$ok}/{$total})\n";
    echo '</pre>';
    echo '<a href="reporte_tecnico.php" style="display:inline-block;margin-top:20px;color:#fff;background:#1565c0;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:1rem;font-weight:bold;font-family:Arial;">→ ABRIR REPORTE TÉCNICO PDF</a>';
} else {
    echo "⚠ Solo se generaron {$ok} de {$total} imágenes.\n";
    echo '</pre>';
}
echo '</body></html>';
