<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "START LOADING...<br>";

require_once 'config/database.php';
echo "Database config loaded<br>";

// Session starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session started<br>";

// Login prüfen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo "User logged in: " . $_SESSION['username'] . "<br>";

// Benutzer abrufen
try {
    $pdo = getDBConnection();
    echo "DB connected<br>";
    
    $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Gefundene Benutzer: " . count($users) . "</h2>";
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #3498db; color: white;'>";
    echo "<th>ID</th><th>Username</th><th>Created At</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($user['created_at'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Raw Data:</h3>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 20px;'>FEHLER: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='users.php'>Zurück zu users.php</a></p>";
