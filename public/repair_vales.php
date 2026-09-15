<?php
/**
 * Script nativo de diagnóstico y reparación ultra-rápido de vales.
 * No inicializa el Kernel de Laravel, evitando bloqueos en servidores gratuitos.
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/repair_vales.php
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Reparador Ultra-Rápido de Vales</title>";
echo "<style>
body { background: #070f1a; color: #cfd8e3; font-family: monospace; padding: 30px; font-size: 14px; }
h2 { color: #64ffda; }
.card { background: #112240; padding: 15px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #1e88e5; }
.success { color: #00ff88; font-weight: bold; }
.warning { color: #ffeb3b; }
.error { color: #f44336; }
a { color: #64ffda; text-decoration: none; font-weight: bold; }
</style></head><body>";

echo "<h2>🛠️ Diagnóstico y Reparación de Vales (Modo Nativo Ultra-Rápido)</h2>";

// 1. Leer credenciales desde el archivo .env de Laravel
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    echo "<p class='error'>✗ No se pudo encontrar el archivo .env de Laravel en la ruta: " . htmlspecialchars($envFile) . "</p>";
    exit;
}

$dbConfig = [
    'DB_HOST' => '',
    'DB_DATABASE' => '',
    'DB_USERNAME' => '',
    'DB_PASSWORD' => ''
];

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2) + [NULL, NULL];
    if ($key !== NULL) {
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (array_key_exists($key, $dbConfig)) {
            $dbConfig[$key] = $value;
        }
    }
}

// 2. Conectar a la Base de Datos usando PDO
try {
    $dsn = "mysql:host=" . $dbConfig['DB_HOST'] . ";dbname=" . $dbConfig['DB_DATABASE'] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['DB_USERNAME'], $dbConfig['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5 // Timeout rápido para evitar esperas eternas
    ]);
    echo "<p class='success'>✓ Conectado a la Base de Datos con éxito.</p>";
} catch (\Exception $e) {
    echo "<p class='error'>✗ Error de conexión a la Base de Datos: " . $e->getMessage() . "</p>";
    exit;
}

// 3. Consultar los vales Activos o Parciales y sus detalles
try {
    $query = "
        SELECT v.id as vale_id, v.codigo_vale, v.estado as vale_estado,
               vd.id as detalle_id, vd.cantidad_prestada, vd.cantidad_devuelta,
               h.nombre as herramienta_nombre
        FROM vales v
        LEFT JOIN vale_detalles vd ON v.id = vd.vale_id
        LEFT JOIN herramientas h ON vd.herramienta_id = h.id
        WHERE v.estado IN ('Activo', 'Parcial')
    ";
    
    $stmt = $pdo->query($query);
    $resultados = $stmt->fetchAll();
    
    if (empty($resultados)) {
        echo "<p class='warning'>No se encontraron vales con estado 'Activo' o 'Parcial' en la base de datos.</p>";
    } else {
        // Agrupar los detalles por cada vale
        $vales = [];
        foreach ($resultados as $row) {
            $valeId = $row['vale_id'];
            if (!isset($vales[$valeId])) {
                $vales[$valeId] = [
                    'id' => $valeId,
                    'codigo' => $row['codigo_vale'],
                    'estado' => $row['vale_estado'],
                    'detalles' => []
                ];
            }
            if ($row['detalle_id'] !== null) {
                $vales[$valeId]['detalles'][] = [
                    'prestado' => (int)$row['cantidad_prestada'],
                    'devuelto' => (int)$row['cantidad_devuelta'],
                    'herramienta' => $row['herramienta_nombre']
                ];
            }
        }
        
        echo "<p>Revisando " . count($vales) . " vales encontrados:</p>";
        $reparados = 0;
        
        foreach ($vales as $vale) {
            echo "<div class='card'>";
            echo "<strong>Vale:</strong> <span style='color:#90caf9;'>" . htmlspecialchars($vale['codigo']) . "</span> (ID: " . $vale['id'] . ")<br>";
            echo "<strong>Estado en DB:</strong> " . $vale['estado'] . "<br>";
            
            $todasDevueltas = true;
            $itemsCount = 0;
            
            foreach ($vale['detalles'] as $det) {
                $itemsCount++;
                $pendiente = $det['prestado'] - $det['devuelto'];
                echo "&nbsp;&nbsp;• " . htmlspecialchars($det['herramienta']) . " | Prestado: " . $det['prestado'] . " | Devuelto: " . $det['devuelto'] . " | Pendiente: " . $pendiente . "<br>";
                if ($det['devuelto'] < $det['prestado']) {
                    $todasDevueltas = false;
                }
            }
            
            if ($itemsCount === 0) {
                echo "<span class='warning'>&nbsp;&nbsp;⚠ Este vale no posee ítems de detalle.</span><br>";
                $todasDevueltas = false;
            }
            
            if ($todasDevueltas) {
                // Actualizar estado del vale a 'Devuelto' de forma directa y atómica
                $update = $pdo->prepare("UPDATE vales SET estado = 'Devuelto' WHERE id = :id");
                $update->execute(['id' => $vale['id']]);
                echo "<span class='success'>✓ ¡Reparado exitosamente! Estado cambiado a 'Devuelto' en base de datos.</span><br>";
                $reparados++;
            } else {
                echo "<span class='warning'>&nbsp;&nbsp;● Sigue activo: Aún tiene herramientas pendientes.</span><br>";
            }
            
            echo "</div>";
        }
        
        echo "<h3 class='success'>Proceso completado. Vales reparados: " . $reparados . "</h3>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>✗ Error al procesar las consultas: " . $e->getMessage() . "</p>";
}

echo "<br><br><a href='vales'>← Volver a la Lista de Vales</a>";
echo "</body></html>";
