<?php
/**
 * Skript zur Aktualisierung der Mitarbeiter-Tabelle
 * Fügt die Felder "personnummer", "address" und "avtalad_procent" hinzu
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    
    echo "<h1>Aktualisierung der Mitarbeiter-Tabelle</h1>";
    
    // Überprüfen, ob die Spalten bereits existieren
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'personnummer'");
    $personnummerExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'address'");
    $addressExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'avtalad_procent'");
    $avtaladProcentExists = $stmt->rowCount() > 0;
    
    // Spalten hinzufügen, falls sie noch nicht existieren
    if (!$personnummerExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN personnummer VARCHAR(20) DEFAULT NULL AFTER name");
        echo "<p>Spalte 'personnummer' wurde hinzugefügt.</p>";
    } else {
        echo "<p>Spalte 'personnummer' existiert bereits.</p>";
    }
    
    if (!$addressExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER personnummer");
        echo "<p>Spalte 'address' wurde hinzugefügt.</p>";
    } else {
        echo "<p>Spalte 'address' existiert bereits.</p>";
    }
    
    if (!$avtaladProcentExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN avtalad_procent DECIMAL(5,1) DEFAULT 100.0 AFTER address");
        echo "<p>Spalte 'avtalad_procent' wurde hinzugefügt.</p>";
    } else {
        echo "<p>Spalte 'avtalad_procent' existiert bereits.</p>";
    }
    
    echo "<h2>Aktualisierung abgeschlossen</h2>";
    echo "<p>Die Mitarbeiter-Tabelle wurde erfolgreich aktualisiert.</p>";
    echo "<p><a href='../employees.php'>Zurück zur Mitarbeiterliste</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Fehler:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
} 