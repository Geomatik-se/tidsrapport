<?php
/**
 * Debug-Skript für Datenbankverbindung
 */

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Datenbank-Verbindungstest</h1>";

echo "<h2>PHP-Version</h2>";
echo "<p>" . phpversion() . "</p>";

echo "<h2>Datenbank-Konfiguration</h2>";

// Konfigurationsdatei einbinden
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Port: " . DB_PORT . "</li>";
    echo "<li>Datenbankname: " . DB_NAME . "</li>";
    echo "<li>Benutzername: " . DB_USER . "</li>";
    echo "<li>Passwort: " . str_repeat('*', strlen(DB_PASS)) . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Konfigurationsdatei nicht gefunden!</p>";
    exit;
}

echo "<h2>Verbindungstest</h2>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    echo "<p>DSN: $dsn</p>";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green; font-weight: bold;'>✓ Datenbankverbindung erfolgreich!</p>";
    
    // Tabellen anzeigen
    echo "<h2>Vorhandene Tabellen</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠ Keine Tabellen in der Datenbank gefunden!</p>";
        echo "<p><a href='install.php'>Zur Installation →</a></p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Prüfen ob alle benötigten Tabellen existieren
        $requiredTables = ['users', 'employees', 'holidays', 'work_hours'];
        $missingTables = array_diff($requiredTables, $tables);
        
        if (!empty($missingTables)) {
            echo "<p style='color: orange;'>⚠ Fehlende Tabellen: " . implode(', ', $missingTables) . "</p>";
            echo "<p><a href='install.php'>Zur Installation →</a></p>";
        } else {
            echo "<p style='color: green;'>✓ Alle benötigten Tabellen sind vorhanden!</p>";
            
            // Benutzer überprüfen
            echo "<h2>Benutzer</h2>";
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            
            if ($userCount == 0) {
                echo "<p style='color: orange;'>⚠ Keine Benutzer gefunden!</p>";
                echo "<p><a href='install.php'>Zur Installation →</a></p>";
            } else {
                echo "<p style='color: green;'>✓ $userCount Benutzer in der Datenbank</p>";
                echo "<p><a href='login.php'>Zum Login →</a></p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Fehler beim Verbindungsaufbau:</p>";
    echo "<pre style='background: #fee; padding: 10px; border: 1px solid #c00;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    
    echo "<h3>Mögliche Ursachen:</h3>";
    echo "<ul>";
    echo "<li>Die Datenbank existiert nicht</li>";
    echo "<li>Benutzername oder Passwort sind falsch</li>";
    echo "<li>Der Host oder Port sind falsch</li>";
    echo "<li>Der Datenbankbenutzer hat keine Zugriffsrechte</li>";
    echo "</ul>";
}
?>
