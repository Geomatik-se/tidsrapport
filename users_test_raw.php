<?php
require_once 'config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<pre>';
print_r($users);
echo '</pre>';
