<?php
/**
 * Debugging-Datei
 * Diese Datei hilft bei der Fehlersuche nach dem Einloggen
 */

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session starten
session_start();

// Ausgabe der Session-Daten
echo "<h1>Session-Daten</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Datenbankverbindung testen
echo "<h1>Datenbankverbindung testen</h1>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "Datenbankverbindung erfolgreich hergestellt.<br>";
    
    // Tabellen überprüfen
    $tables = ['users', 'employees', 'holidays', 'work_hours'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Tabelle '$table' existiert.<br>";
            
            // Anzahl der Datensätze ausgeben
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "Anzahl der Datensätze in '$table': $count<br>";
            
            // Beispieldaten ausgeben
            if ($count > 0) {
                $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "Beispieldaten aus '$table':<br>";
                echo "<pre>";
                print_r($row);
                echo "</pre>";
            }
        } else {
            echo "Tabelle '$table' existiert nicht!<br>";
        }
    }
} catch (PDOException $e) {
    echo "Fehler bei der Datenbankverbindung: " . $e->getMessage() . "<br>";
}

// Dateipfade überprüfen
echo "<h1>Dateipfade überprüfen</h1>";
$files = [
    'includes/auth.php',
    'includes/employees.php',
    'includes/work_hours.php',
    'config/database.php',
    'index.php',
    'login.php',
    'assets/css/style.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Datei '$file' existiert.<br>";
    } else {
        echo "Datei '$file' existiert nicht!<br>";
    }
}

// PHP-Informationen ausgeben
echo "<h1>PHP-Informationen</h1>";
echo "PHP-Version: " . phpversion() . "<br>";
echo "Geladene PHP-Erweiterungen:<br>";
$extensions = get_loaded_extensions();
echo "<pre>";
print_r($extensions);
echo "</pre>";

// Server-Informationen ausgeben
echo "<h1>Server-Informationen</h1>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>"; 