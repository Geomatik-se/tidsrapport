<?php
/**
 * Datenbank-Initialisierungsskript
 * Dieses Skript erstellt die Datenbankstruktur und fügt den Standardbenutzer ein
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    
    echo "Datenbankverbindung hergestellt.\n";
    
    // SQL-Datei einlesen
    $sql = file_get_contents(__DIR__ . '/database_schema.sql');
    
    // SQL-Befehle ausführen
    $pdo->exec($sql);
    
    echo "Datenbankstruktur wurde erstellt.\n";
    
    // Passwort für den Standardbenutzer hashen und aktualisieren
    $username = 'olofb';
    $password = 'svansele57';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prüfen, ob der Benutzer bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        // Benutzer aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "Benutzer '$username' wurde aktualisiert.\n";
    } else {
        // Benutzer einfügen
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "Benutzer '$username' wurde erstellt.\n";
    }
    
    echo "Datenbank-Initialisierung abgeschlossen.\n";
    
} catch (PDOException $e) {
    die("Fehler bei der Datenbank-Initialisierung: " . $e->getMessage());
} 