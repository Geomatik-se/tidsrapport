<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Tabellen-Struktur anzeigen
    $stmt = $pdo->query("SHOW COLUMNS FROM holidays");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Holidays Tabellen-Struktur:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}
