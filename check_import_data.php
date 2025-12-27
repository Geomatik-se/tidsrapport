<?php
// Prüfe importierte Daten
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Check Import</title></head><body>";
echo "<h1>Import-Daten prüfen</h1>";

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Hole Sören Padel
    echo "<h2>Mitarbeiter: Sören Padel</h2>";
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE name LIKE ?");
    $stmt->execute(['%Sören%']);
    $employee = $stmt->fetch();
    
    if ($employee) {
        echo "<p style='color: green;'>✓ Mitarbeiter gefunden: ID {$employee['id']}</p>";
        echo "<pre>" . print_r($employee, true) . "</pre>";
        
        // Hole work_hours für diesen Mitarbeiter
        echo "<h2>Arbeitszeiten für Sören Padel (Dezember 2025)</h2>";
        $stmt = $pdo->prepare("
            SELECT * FROM work_hours 
            WHERE employee_id = ? 
            AND year = 2025 
            AND month = 12
            ORDER BY date
        ");
        $stmt->execute([$employee['id']]);
        $workHours = $stmt->fetchAll();
        
        if (empty($workHours)) {
            echo "<p style='color: red;'>⚠ KEINE Arbeitszeiten gefunden!</p>";
            
            // Prüfe ob es überhaupt Einträge gibt
            echo "<h3>Alle Arbeitszeiten für diesen Mitarbeiter:</h3>";
            $stmt = $pdo->prepare("SELECT * FROM work_hours WHERE employee_id = ? ORDER BY date DESC LIMIT 10");
            $stmt->execute([$employee['id']]);
            $allHours = $stmt->fetchAll();
            
            if (empty($allHours)) {
                echo "<p style='color: red;'>⚠ KEINE Arbeitszeiten in der gesamten Datenbank!</p>";
            } else {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Datum</th><th>Arbeitszeit</th><th>Krank</th><th>Urlaub</th><th>Jahr</th><th>Monat</th></tr>";
                foreach ($allHours as $wh) {
                    echo "<tr>";
                    echo "<td>{$wh['id']}</td>";
                    echo "<td>{$wh['date']}</td>";
                    echo "<td>{$wh['arbetstid']}</td>";
                    echo "<td>{$wh['sjuk']}</td>";
                    echo "<td>{$wh['semester']}</td>";
                    echo "<td>{$wh['year']}</td>";
                    echo "<td>{$wh['month']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            $count = count($workHours);
            echo "<p style='color: green;'>✓ $count Einträge gefunden</p>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Datum</th><th>Arbeitszeit</th><th>Krank</th><th>Urlaub</th><th>Vereinbart%</th><th>Arbeit</th><th>Distanz</th></tr>";
            foreach ($workHours as $wh) {
                echo "<tr>";
                echo "<td>{$wh['id']}</td>";
                echo "<td>{$wh['date']}</td>";
                echo "<td>{$wh['arbetstid']}</td>";
                echo "<td>{$wh['sjuk']}</td>";
                echo "<td>{$wh['semester']}</td>";
                echo "<td>{$wh['avtalad_procent']}</td>";
                echo "<td>{$wh['arbete']}</td>";
                echo "<td>{$wh['distansarbete']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test: Manuell einen Eintrag hinzufügen
        if (isset($_GET['add_test'])) {
            echo "<h2>Test-Eintrag hinzufügen...</h2>";
            $testDate = '2025-12-29';
            $stmt = $pdo->prepare("
                INSERT INTO work_hours 
                (employee_id, date, arbetstid, sjuk, semester, avtalad_procent, arbete, distansarbete, year, month)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                arbetstid = VALUES(arbetstid),
                sjuk = VALUES(sjuk),
                semester = VALUES(semester)
            ");
            $stmt->execute([
                $employee['id'], $testDate, 0, 8.0, 0, 60, 0, 0, 2025, 12
            ]);
            echo "<p style='color: green;'>✓ Test-Eintrag für $testDate hinzugefügt (8h krank)</p>";
            echo "<p><a href='check_import_data.php'>Neu laden</a></p>";
        } else {
            echo "<p><a href='?add_test=1'>Test-Eintrag hinzufügen</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Mitarbeiter 'Sören Padel' nicht gefunden!</p>";
        
        echo "<h3>Alle Mitarbeiter:</h3>";
        $stmt = $pdo->query("SELECT * FROM employees");
        $employees = $stmt->fetchAll();
        echo "<ul>";
        foreach ($employees as $emp) {
            echo "<li>ID: {$emp['id']} - {$emp['name']}</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
