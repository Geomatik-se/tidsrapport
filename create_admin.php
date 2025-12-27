<?php
/**
 * Erstellt den Admin-Benutzer
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Admin-Benutzer erstellen</h2>";
    
    // Prüfen, ob is_admin Spalte existiert
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    $isAdminExists = $stmt->rowCount() > 0;
    
    if (!$isAdminExists) {
        echo "<p>Füge 'is_admin' Spalte zur users-Tabelle hinzu...</p>";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER password");
        echo "<p style='color:green'>✓ Spalte 'is_admin' hinzugefügt.</p>";
    } else {
        echo "<p style='color:blue'>ℹ Spalte 'is_admin' existiert bereits.</p>";
    }
    
    // Admin-Benutzer erstellen oder aktualisieren
    $username = 'admin';
    $password = 'Morot1234';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prüfen, ob admin bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        // Admin existiert bereits - nur Passwort und Admin-Flag aktualisieren
        $adminId = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);
        echo "<p style='color:green'>✓ Admin-Benutzer aktualisiert (Passwort und Admin-Status).</p>";
    } else {
        // Admin erstellen
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)");
        $stmt->execute([$username, $hashedPassword]);
        echo "<p style='color:green'>✓ Admin-Benutzer erstellt.</p>";
    }
    
    echo "<h3>Admin-Login:</h3>";
    echo "<ul>";
    echo "<li><strong>Benutzername:</strong> admin</li>";
    echo "<li><strong>Passwort:</strong> Morot1234</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php'>Zur Login-Seite</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Fehler: " . $e->getMessage() . "</p>";
}
