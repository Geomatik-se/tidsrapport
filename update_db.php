<?php
/**
 * Direktes Datenbankaktualisierungsskript
 * Dieses Skript aktualisiert die Datenbank direkt, ohne dass der Benutzer auf einen Link klicken muss
 */

require_once 'config/database.php';

try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    
    // Überprüfen, ob die Spalten bereits existieren
    $columnsExist = true;
    
    try {
        $stmt = $pdo->query("SELECT personnummer, address, avtalad_procent FROM employees LIMIT 1");
        $stmt->fetch();
    } catch (PDOException $e) {
        $columnsExist = false;
    }
    
    if (!$columnsExist) {
        // Spalten hinzufügen
        $pdo->exec("ALTER TABLE employees ADD COLUMN personnummer VARCHAR(20) DEFAULT NULL AFTER name");
        $pdo->exec("ALTER TABLE employees ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER personnummer");
        $pdo->exec("ALTER TABLE employees ADD COLUMN avtalad_procent DECIMAL(5,1) DEFAULT 100.0 AFTER address");
        
        echo "Datenbank wurde aktualisiert. Die Spalten 'personnummer', 'address' und 'avtalad_procent' wurden zur Tabelle 'employees' hinzugefügt.";
    } else {
        echo "Die Spalten existieren bereits in der Tabelle 'employees'.";
    }
    
} catch (PDOException $e) {
    echo "Fehler bei der Datenbankaktualisierung: " . $e->getMessage();
}

// Weiterleitung zur Startseite nach 5 Sekunden
header("refresh:5;url=index.php");
echo "<p>Sie werden in 5 Sekunden zur Startseite weitergeleitet...</p>";
?> 