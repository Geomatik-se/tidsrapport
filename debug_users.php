<?php
/**
 * Debug-Version der Benutzerverwaltung
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

echo "<h2>Debug: Benutzerverwaltung</h2>";

// Alle Benutzer abrufen
try {
    $pdo = getDBConnection();
    
    echo "<h3>SQL-Abfrage:</h3>";
    $sql = "SELECT id, username, created_at, is_admin FROM users ORDER BY username";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Anzahl gefundener Benutzer: " . count($users) . "</h3>";
    
    echo "<h3>Alle Benutzer (Rohausgabe):</h3>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Tabellen-Ausgabe:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Created At</th><th>Is Admin</th><th>Sichtbar?</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['created_at'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($user['is_admin']) . "</td>";
        echo "<td style='background:" . (count($users) > 0 ? 'green' : 'red') . "; color:white'>JA</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Session-Info:</h3>";
    echo "<pre>";
    echo "user_id: " . ($_SESSION['user_id'] ?? 'nicht gesetzt') . "\n";
    echo "username: " . ($_SESSION['username'] ?? 'nicht gesetzt') . "\n";
    echo "is_admin: " . ($_SESSION['is_admin'] ?? 'nicht gesetzt') . "\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Fehler: " . $e->getMessage() . "</p>";
}

echo "<p><a href='users.php'>Zur normalen Benutzer-Seite</a></p>";
