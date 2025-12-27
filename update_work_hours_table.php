<?php
// Aktualisiere die work_hours Tabelle mit allen benötigten Spalten
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Update DB</title></head><body>";
echo "<h1>Datenbank-Update</h1>";

$host = '62.108.32.157';
$port = '3306';
$dbname = 'aealubjt_2026';
$user = 'aealubjt_2026';
$pass = 'Morot1234';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Verbindung OK</p>";
    
    // Prüfe aktuelle Spalten
    echo "<h2>Aktuelle work_hours Spalten:</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM work_hours");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul>";
    
    // Füge fehlende Spalten hinzu
    echo "<h2>Spalten hinzufügen...</h2>";
    
    $columnsToAdd = [
        'arbetstid' => "ALTER TABLE work_hours ADD COLUMN arbetstid DECIMAL(5,2) DEFAULT 0 AFTER date",
        'sjuk' => "ALTER TABLE work_hours ADD COLUMN sjuk DECIMAL(5,2) DEFAULT 0 AFTER arbetstid",
        'semester' => "ALTER TABLE work_hours ADD COLUMN semester DECIMAL(5,2) DEFAULT 0 AFTER sjuk",
        'avtalad_procent' => "ALTER TABLE work_hours ADD COLUMN avtalad_procent DECIMAL(5,2) DEFAULT 100.00 AFTER semester",
        'arbete' => "ALTER TABLE work_hours ADD COLUMN arbete DECIMAL(5,2) DEFAULT 0 AFTER avtalad_procent",
        'distansarbete' => "ALTER TABLE work_hours ADD COLUMN distansarbete DECIMAL(5,2) DEFAULT 0 AFTER arbete"
    ];
    
    foreach ($columnsToAdd as $colName => $sql) {
        if (!in_array($colName, $columns)) {
            try {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✓ Spalte '$colName' hinzugefügt</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠ Spalte '$colName': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>• Spalte '$colName' existiert bereits</p>";
        }
    }
    
    // Lösche die alte 'hours' Spalte wenn vorhanden
    if (in_array('hours', $columns)) {
        try {
            $pdo->exec("ALTER TABLE work_hours DROP COLUMN hours");
            echo "<p style='color: green;'>✓ Alte 'hours' Spalte entfernt</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Konnte 'hours' Spalte nicht entfernen: " . $e->getMessage() . "</p>";
        }
    }
    
    // Zeige finale Struktur
    echo "<h2>Neue work_hours Struktur:</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM work_hours");
    $newColumns = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th style='padding: 5px;'>Spalte</th><th style='padding: 5px;'>Typ</th><th style='padding: 5px;'>Null</th><th style='padding: 5px;'>Default</th></tr>";
    foreach ($newColumns as $col) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>{$col['Field']}</td>";
        echo "<td style='padding: 5px;'>{$col['Type']}</td>";
        echo "<td style='padding: 5px;'>{$col['Null']}</td>";
        echo "<td style='padding: 5px;'>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Update abgeschlossen!</h2>";
    echo "<p><a href='index.php' style='padding: 10px; background: #27ae60; color: white; text-decoration: none; display: inline-block;'>Zurück zur Hauptseite →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
