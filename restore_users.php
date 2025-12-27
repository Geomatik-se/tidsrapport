<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Benutzer wiederherstellen</h2>";
    
    // Prüfen welche Benutzer existieren
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Vorhandene Benutzer:</p><pre>";
    print_r($existing);
    echo "</pre>";
    
    // Benutzer olofb wiederherstellen falls nicht vorhanden
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['olofb']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $stmt->execute(['olofb', password_hash('svansele57', PASSWORD_DEFAULT), 0]);
        echo "<p style='color:green;'>✓ Benutzer 'olofb' wiederhergestellt</p>";
    } else {
        echo "<p style='color:blue;'>Benutzer 'olofb' existiert bereits</p>";
    }
    
    // Benutzer Sören wiederherstellen falls nicht vorhanden
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['Sören']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $stmt->execute(['Sören', password_hash('password123', PASSWORD_DEFAULT), 0]);
        echo "<p style='color:green;'>✓ Benutzer 'Sören' wiederhergestellt</p>";
    } else {
        echo "<p style='color:blue;'>Benutzer 'Sören' existiert bereits</p>";
    }
    
    echo "<hr>";
    echo "<h3>Alle Benutzer nach Wiederherstellung:</h3>";
    $stmt = $pdo->query("SELECT id, username, is_admin, created_at FROM users ORDER BY username");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($all_users);
    echo "</pre>";
    
    echo "<p><a href='users.php'>Zurück zur Benutzerverwaltung</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Fehler: " . $e->getMessage() . "</p>";
}
