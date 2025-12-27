<?php
// ABSOLUT MINIMALER TEST
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Minimal Test</title></head><body>";
echo "<h1>PHP funktioniert!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Zeit: " . date('Y-m-d H:i:s') . "</p>";

// Test Datenbankverbindung
echo "<h2>Datenbanktest</h2>";
try {
    $host = '62.108.32.157';
    $port = '3306';
    $dbname = 'aealubjt_2026';
    $user = 'aealubjt_2026';
    $pass = 'Morot1234';
    
    echo "<p>Verbinde zu: $host:$port / $dbname</p>";
    
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green; font-weight: bold;'>✓ VERBINDUNG ERFOLGREICH!</p>";
    
    // Tabellen zählen
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $count = count($tables);
    
    echo "<p>Anzahl Tabellen: <strong>$count</strong></p>";
    
    if ($count > 0) {
        echo "<details><summary>Tabellen anzeigen</summary><ul>";
        foreach ($tables as $table) {
            // Zeilen zählen
            try {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                $rows = $countStmt->fetchColumn();
                echo "<li>$table ($rows Zeilen)</li>";
            } catch (Exception $e) {
                echo "<li>$table (Fehler beim Zählen)</li>";
            }
        }
        echo "</ul></details>";
    }
    
    // Prüfe ob Benutzer existieren
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        echo "<p>Benutzer in DB: <strong>$userCount</strong></p>";
        
        if ($userCount == 0) {
            echo "<p style='color: orange;'>⚠ Keine Benutzer gefunden - Installation erforderlich!</p>";
        }
    } else {
        echo "<p style='color: red;'>⚠ Tabelle 'users' existiert nicht - Installation erforderlich!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ VERBINDUNGSFEHLER:</p>";
    echo "<pre style='background: #ffe; border: 2px solid red; padding: 10px;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
}

echo "<hr>";
echo "<h3>Nächste Schritte:</h3>";
echo "<ul>";
echo "<li><a href='install.php'>Installation/Setup ausführen</a></li>";
echo "<li><a href='login.php'>Zum Login</a></li>";
echo "<li><a href='index.php'>Zur Hauptseite</a></li>";
echo "</ul>";

echo "</body></html>";
?>
