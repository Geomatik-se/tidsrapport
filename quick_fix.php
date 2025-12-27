<?php
// Schnelle Reparatur für importierte Daten
require_once 'config/database.php';

$pdo = getDBConnection();

// Repariere alle Einträge mit year=0 oder month=0
$stmt = $pdo->prepare("
    UPDATE work_hours 
    SET year = YEAR(date), month = MONTH(date)
    WHERE year = 0 OR month = 0
");
$stmt->execute();
$affected = $stmt->rowCount();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Daten repariert</h1>";
echo "<p style='color: green; font-size: 18px;'>✓ $affected Einträge aktualisiert!</p>";
echo "<p><a href='employee_month.php?id=1&year=2025&month=12' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; display: inline-block; border-radius: 4px;'>Zur Monatsansicht →</a></p>";
echo "</body></html>";
?>
