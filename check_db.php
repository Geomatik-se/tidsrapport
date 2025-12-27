<?php
// Zeige was tatsächlich in der Datenbank ist
require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Datenbank-Inhalt prüfen</h1>";

$pdo = getDBConnection();

// Zeige alle work_hours für Mitarbeiter 1 im Dezember 2025
$stmt = $pdo->prepare("
    SELECT * FROM work_hours 
    WHERE employee_id = 1 
    AND date BETWEEN '2025-12-01' AND '2025-12-31'
    ORDER BY date
");
$stmt->execute();
$rows = $stmt->fetchAll();

echo "<h2>Einträge für Mitarbeiter 1 (Dezember 2025): " . count($rows) . "</h2>";

if (empty($rows)) {
    echo "<p style='color: red;'>KEINE Einträge gefunden!</p>";
    
    // Zeige alle Einträge für diesen Mitarbeiter
    $stmt = $pdo->prepare("SELECT * FROM work_hours WHERE employee_id = 1 ORDER BY date DESC LIMIT 20");
    $stmt->execute();
    $allRows = $stmt->fetchAll();
    
    echo "<h3>Alle Einträge für Mitarbeiter 1:</h3>";
    if (empty($allRows)) {
        echo "<p style='color: red;'>Überhaupt KEINE Einträge!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Datum</th><th>Arbeitszeit</th><th>Krank</th><th>Urlaub</th><th>Jahr</th><th>Monat</th></tr>";
        foreach ($allRows as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['date']}</td>";
            echo "<td>{$row['arbetstid']}</td>";
            echo "<td>{$row['sjuk']}</td>";
            echo "<td>{$row['semester']}</td>";
            echo "<td>{$row['year']}</td>";
            echo "<td>{$row['month']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Datum</th><th>Arbeitszeit</th><th>Krank</th><th>Urlaub</th><th>Vereinbart%</th><th>Arbeit</th><th>Distanz</th><th>Jahr</th><th>Monat</th></tr>";
    foreach ($rows as $row) {
        $color = ($row['arbetstid'] == 0 && $row['sjuk'] == 0 && $row['semester'] == 0) ? 'background: #fee;' : '';
        echo "<tr style='$color'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "<td>{$row['arbetstid']}</td>";
        echo "<td>{$row['sjuk']}</td>";
        echo "<td>{$row['semester']}</td>";
        echo "<td>{$row['avtalad_procent']}</td>";
        echo "<td>{$row['arbete']}</td>";
        echo "<td>{$row['distansarbete']}</td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['month']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: orange;'>Hinweis: Rote Zeilen = alle Werte sind 0</p>";
}

// Test: Manuell einen Eintrag mit Werten erstellen
if (isset($_GET['add_test'])) {
    $testDate = '2025-12-27';
    $stmt = $pdo->prepare("
        DELETE FROM work_hours WHERE employee_id = 1 AND date = ?
    ");
    $stmt->execute([$testDate]);
    
    $stmt = $pdo->prepare("
        INSERT INTO work_hours 
        (employee_id, date, arbetstid, sjuk, semester, avtalad_procent, arbete, distansarbete, year, month)
        VALUES (1, ?, 8.0, 0, 0, 60, 8.0, 0, 2025, 12)
    ");
    $stmt->execute([$testDate]);
    
    echo "<p style='color: green;'>✓ Test-Eintrag für $testDate hinzugefügt (8h Arbeitszeit)</p>";
    echo "<p><a href='check_db.php'>Neu laden</a></p>";
}

echo "<hr>";
echo "<p><a href='?add_test=1' style='padding: 10px; background: #3498db; color: white; text-decoration: none;'>Test-Eintrag mit Werten hinzufügen</a></p>";
echo "<p><a href='employee_month.php?id=1&year=2025&month=12'>Zur Monatsansicht</a></p>";

echo "</body></html>";
?>
