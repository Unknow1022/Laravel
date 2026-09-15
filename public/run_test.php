<?php
/**
 * Script de prueba de conexión directa a la base de datos sin cargar Laravel.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🌐 Diagnóstico de Conexión a Base de Datos</h2>";

$host = "sql110.infinityfree.com";
$dbname = "if0_42375917_almacen";
$username = "if0_42375917";
$password = "kHcjQcw8PQ";

echo "Intentando conectar a: {$host} con el usuario {$username} y la DB {$dbname}...<br>";
flush();

try {
    $dsn = "mysql:host={$host};port=3306;dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<b style='color:green;'>✅ CONEXIÓN EXITOSA A LA BASE DE DATOS!</b><br><br>";
    
    // Probar hacer una consulta simple
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas (" . count($tables) . "):<br>";
    print_r($tables);
    
} catch (PDOException $e) {
    echo "<b style='color:red;'>❌ ERROR DE CONEXIÓN A LA BASE DE DATOS:</b> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Código de error: " . $e->getCode() . "<br>";
}
