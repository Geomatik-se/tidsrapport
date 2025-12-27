<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Users Tabellen-Struktur:</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2>Alle Benutzer:</h2>";
    $stmt = $pdo->query("SELECT * FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Anzahl: " . count($users) . "</p>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}
