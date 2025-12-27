<?php
// Minimaler Test - zeigt alle Fehler an
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Server Test</h1>";
echo "<p>PHP läuft: " . phpversion() . "</p>";

// Test 1: Datenbankverbindung OHNE config/database.php
echo "<h2>Test 1: Direkte Datenbankverbindung</h2>";
try {
    $pdo = new PDO(
        'mysql:host=62.108.32.157;port=3306;dbname=aealubjt_2026;charset=utf8mb4',
        'aealubjt_2026',
        'Morot1234'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Direkte Verbindung erfolgreich!</p>";
    
    // Tabellen anzeigen
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Gefundene Tabellen: " . count($tables) . "</p>";
    if (!empty($tables)) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: config/database.php einbinden
echo "<h2>Test 2: config/database.php laden</h2>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        echo "<p style='color: green;'>✓ config/database.php geladen</p>";
        echo "<p>DB_HOST: " . DB_HOST . "</p>";
        echo "<p>DB_NAME: " . DB_NAME . "</p>";
        
        $pdo2 = getDBConnection();
        echo "<p style='color: green;'>✓ getDBConnection() funktioniert!</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>config/database.php nicht gefunden!</p>";
}

echo "<hr>";
echo "<p><a href='install.php'>Zur Installation</a> | <a href='login.php'>Zum Login</a></p>";
?>
