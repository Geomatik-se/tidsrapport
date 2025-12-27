<?php
// Repariere year und month Werte in work_hours
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Data</title></head><body>";
echo "<h1>Daten reparieren</h1>";

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Suche Einträge mit year=0 oder month=0...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM work_hours WHERE year = 0 OR month = 0");
    $count = $stmt->fetchColumn();
    
    echo "<p>Gefunden: <strong>$count</strong> Einträge</p>";
    
    if ($count > 0 && !isset($_POST['fix'])) {
        echo "<form method='post'>";
        echo "<p>Möchten Sie diese Einträge reparieren? (year und month werden aus dem Datum berechnet)</p>";
        echo "<button type='submit' name='fix' value='1' style='padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;'>Ja, reparieren</button>";
        echo "</form>";
    } elseif (isset($_POST['fix'])) {
        echo "<h2>Repariere Einträge...</h2>";
        
        // Aktualisiere alle Einträge
        $stmt = $pdo->prepare("
            UPDATE work_hours 
            SET year = YEAR(date), month = MONTH(date)
            WHERE year = 0 OR month = 0
        ");
        $stmt->execute();
        
        $affected = $stmt->rowCount();
        echo "<p style='color: green; font-weight: bold;'>✓ $affected Einträge aktualisiert!</p>";
        
        // Zeige Beispiele
        echo "<h3>Beispiele der reparierten Einträge:</h3>";
        $stmt = $pdo->query("
            SELECT id, employee_id, date, year, month, arbetstid 
            FROM work_hours 
            WHERE date >= '2025-12-01' 
            ORDER BY date DESC 
            LIMIT 10
        ");
        $examples = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Mitarbeiter-ID</th><th>Datum</th><th>Jahr</th><th>Monat</th><th>Arbeitszeit</th></tr>";
        foreach ($examples as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['employee_id']}</td>";
            echo "<td>{$row['date']}</td>";
            echo "<td>{$row['year']}</td>";
            echo "<td>{$row['month']}</td>";
            echo "<td>{$row['arbetstid']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<hr>";
        echo "<p style='color: green; font-size: 18px;'>✓ Reparatur abgeschlossen!</p>";
        echo "<p><a href='employee_month.php?id=1' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; display: inline-block; border-radius: 4px;'>Zur Monatsansicht →</a></p>";
    } else {
        echo "<p style='color: green;'>✓ Alle Einträge sind korrekt!</p>";
        echo "<p><a href='employee_month.php?id=1'>Zur Monatsansicht →</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
