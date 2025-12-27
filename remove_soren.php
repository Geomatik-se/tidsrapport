<?php
require_once 'config/database.php';
$pdo = getDBConnection();
$stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
$stmt->execute(['Sören']);
echo "Benutzer 'Sören' wurde entfernt.";
