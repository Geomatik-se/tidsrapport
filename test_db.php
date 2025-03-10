<?php
/**
 * Datenbank-Testdatei
 * Diese Datei überprüft, ob die Datenbank korrekt initialisiert wurde
 */

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Datenbankverbindung einbinden
require_once 'config/database.php';

echo "<h1>Datenbank-Test</h1>";

try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    echo "<p style='color: green;'>Datenbankverbindung erfolgreich hergestellt.</p>";
    
    // Tabellen überprüfen
    $tables = ['users', 'employees', 'holidays', 'work_hours'];
    $allTablesExist = true;
    
    echo "<h2>Tabellen überprüfen</h2>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<li style='color: green;'>Tabelle '$table' existiert.</li>";
        } else {
            echo "<li style='color: red;'>Tabelle '$table' existiert nicht!</li>";
            $allTablesExist = false;
        }
    }
    
    echo "</ul>";
    
    // Wenn nicht alle Tabellen existieren, Datenbank initialisieren
    if (!$allTablesExist) {
        echo "<h2>Datenbank initialisieren</h2>";
        echo "<p>Nicht alle erforderlichen Tabellen existieren. Möchten Sie die Datenbank initialisieren?</p>";
        echo "<form method='post' action='install.php'>";
        echo "<button type='submit' name='initialize' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Datenbank initialisieren</button>";
        echo "</form>";
    } else {
        // Benutzer überprüfen
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
        $stmt->execute(['olofb']);
        
        echo "<h2>Benutzer überprüfen</h2>";
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            echo "<p style='color: green;'>Benutzer 'olofb' existiert (ID: {$user['id']}).</p>";
        } else {
            echo "<p style='color: red;'>Benutzer 'olofb' existiert nicht!</p>";
            echo "<p>Möchten Sie den Standardbenutzer erstellen?</p>";
            echo "<form method='post' action='install.php'>";
            echo "<button type='submit' name='create_user' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Standardbenutzer erstellen</button>";
            echo "</form>";
        }
        
        // Mitarbeiter überprüfen
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
        $employeeCount = $stmt->fetchColumn();
        
        echo "<h2>Mitarbeiter überprüfen</h2>";
        echo "<p>Anzahl der Mitarbeiter: $employeeCount</p>";
        
        if ($employeeCount > 0) {
            echo "<p style='color: green;'>Es sind bereits Mitarbeiter in der Datenbank vorhanden.</p>";
            
            // Beispiel-Mitarbeiter anzeigen
            $stmt = $pdo->query("SELECT * FROM employees LIMIT 5");
            $employees = $stmt->fetchAll();
            
            echo "<h3>Beispiel-Mitarbeiter:</h3>";
            echo "<ul>";
            foreach ($employees as $employee) {
                echo "<li>{$employee['name']} (ID: {$employee['id']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>Es sind noch keine Mitarbeiter in der Datenbank vorhanden.</p>";
            echo "<p>Sie können Mitarbeiter über die Anwendung hinzufügen.</p>";
        }
    }
    
    echo "<h2>Nächste Schritte</h2>";
    echo "<ul>";
    echo "<li><a href='login.php'>Zur Anmeldeseite</a></li>";
    if ($allTablesExist) {
        echo "<li><a href='employee_add.php'>Mitarbeiter hinzufügen</a></li>";
    }
    echo "<li><a href='debug.php'>Debugging-Informationen anzeigen</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler bei der Datenbankverbindung: " . $e->getMessage() . "</p>";
    
    echo "<h2>Mögliche Lösungen</h2>";
    echo "<ul>";
    echo "<li>Überprüfen Sie die Datenbankverbindungsdaten in der Datei 'config/database.php'.</li>";
    echo "<li>Stellen Sie sicher, dass der Datenbankserver läuft und erreichbar ist.</li>";
    echo "<li>Überprüfen Sie, ob der Datenbankbenutzer die notwendigen Rechte hat.</li>";
    echo "</ul>";
} 