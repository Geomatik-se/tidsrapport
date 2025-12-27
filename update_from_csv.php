<?php
/**
 * Aktualisiere vorhandene Einträge mit CSV-Daten
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Vorhandene Daten aktualisieren</h1>";

$employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
$year = isset($_POST['year']) ? (int)$_POST['year'] : 2025;
$month = isset($_POST['month']) ? (int)$_POST['month'] : 12;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $updated = 0;
        $errors = 0;
        $details = [];
        
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            // Auto-detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
            
            echo "<p>Erkanntes Trennzeichen: " . ($delimiter === ';' ? 'Semikolon' : 'Komma') . "</p>";
            
            fgetcsv($handle, 1000, $delimiter); // Skip header
            $line = 1;
            
            $pdo = getDBConnection();
            
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $line++;
                if (empty(array_filter($data))) continue;
                
                try {
                    // Debug für erste 5 Zeilen
                    if ($line <= 6) {
                        $details[] = "Zeile $line RAW: [" . implode('] [', array_map(function($v) {
                            return trim($v) . " (" . strlen(trim($v)) . ")";
                        }, $data)) . "]";
                    }
                    
                    // Parse Datum
                    $dateStr = trim($data[0]);
                    if (preg_match('/^(\d+)/', $dateStr, $m)) {
                        $day = (int)$m[1];
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    } else {
                        $errors++;
                        continue;
                    }
                    
                    // Werte extrahieren - mit besserer Fehlerbehandlung
                    $arbetstid = 0;
                    $sjuk = 0;
                    $semester = 0;
                    $arbete = 0;
                    $distansarbete = 0;
                    
                    // Spalte B (Index 1) = Arbetstid
                    if (isset($data[1]) && trim($data[1]) !== '') {
                        $val = str_replace(',', '.', trim($data[1]));
                        if (is_numeric($val)) {
                            $arbetstid = floatval($val);
                        }
                    }
                    
                    // Spalte C (Index 2) = Sjuk
                    if (isset($data[2]) && trim($data[2]) !== '') {
                        $val = str_replace(',', '.', trim($data[2]));
                        if (is_numeric($val)) {
                            $sjuk = floatval($val);
                        }
                    }
                    
                    // Spalte D (Index 3) = Semester
                    if (isset($data[3]) && trim($data[3]) !== '') {
                        $val = str_replace(',', '.', trim($data[3]));
                        if (is_numeric($val)) {
                            $semester = floatval($val);
                        }
                    }
                    
                    // Spalte H (Index 7) = Arbete
                    if (isset($data[7]) && trim($data[7]) !== '') {
                        $val = str_replace(',', '.', trim($data[7]));
                        if (is_numeric($val)) {
                            $arbete = floatval($val);
                        }
                    }
                    
                    // Spalte I (Index 8) = Distansarbete
                    if (isset($data[8]) && trim($data[8]) !== '') {
                        $val = str_replace(',', '.', trim($data[8]));
                        if (is_numeric($val)) {
                            $distansarbete = floatval($val);
                        }
                    }
                    
                    if ($line <= 6) {
                        $details[] = "Zeile $line WERTE: Datum=$date, Arbeit=$arbetstid, Krank=$sjuk, Urlaub=$semester, Arbete=$arbete, Distans=$distansarbete";
                    }
                    
                    // Update in DB
                    $stmt = $pdo->prepare("
                        UPDATE work_hours 
                        SET arbetstid = ?, sjuk = ?, semester = ?, arbete = ?, distansarbete = ?
                        WHERE employee_id = ? AND date = ?
                    ");
                    $stmt->execute([$arbetstid, $sjuk, $semester, $arbete, $distansarbete, $employeeId, $date]);
                    
                    if ($stmt->rowCount() > 0) {
                        $updated++;
                    }
                    
                } catch (Exception $e) {
                    $errors++;
                    $details[] = "Zeile $line Fehler: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            echo "<div style='padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin: 20px 0;'>";
            echo "<h2>✓ Aktualisierung abgeschlossen!</h2>";
            echo "<p><strong>$updated</strong> Einträge aktualisiert, <strong>$errors</strong> Fehler</p>";
            echo "</div>";
            
            if (!empty($details)) {
                echo "<details style='margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px;'>";
                echo "<summary>Debug-Details anzeigen (" . count($details) . " Meldungen)</summary>";
                echo "<pre style='white-space: pre-wrap; font-family: monospace; font-size: 12px;'>";
                foreach ($details as $detail) {
                    echo htmlspecialchars($detail) . "\n";
                }
                echo "</pre>";
                echo "</details>";
            }
            
            echo "<p><a href='check_db.php' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px;'>Daten prüfen</a>";
            echo "<a href='employee_month.php?id=$employeeId&year=$year&month=$month' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px;'>Zur Monatsansicht</a></p>";
        }
    }
} else {
    // Formular anzeigen
    ?>
    <form method="post" enctype="multipart/form-data" style="max-width: 600px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Mitarbeiter:</label>
            <select name="employee_id" required style="width: 100%; padding: 8px;">
                <?php
                $pdo = getDBConnection();
                $stmt = $pdo->query("SELECT * FROM employees ORDER BY name");
                $employees = $stmt->fetchAll();
                foreach ($employees as $emp) {
                    $sel = ($emp['id'] == 1) ? 'selected' : '';
                    echo "<option value='{$emp['id']}' $sel>{$emp['name']}</option>";
                }
                ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Jahr:</label>
            <input type="number" name="year" value="2025" min="2020" max="2030" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Monat:</label>
            <select name="month" required style="width: 100%; padding: 8px;">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo ($m == 12) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">CSV-Datei:</label>
            <input type="file" name="csv_file" accept=".csv" required style="padding: 8px;">
        </div>
        
        <button type="submit" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Daten aktualisieren</button>
    </form>
    
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 4px; max-width: 600px;">
        <h3>⚠️ Hinweis</h3>
        <p>Dieses Skript aktualisiert die <strong>vorhandenen</strong> Einträge mit den Werten aus Ihrer CSV-Datei.</p>
        <p>Format: Spalte A = Datum, B = Arbeitszeit, C = Krank, D = Urlaub, H = Arbete, I = Distansarbete</p>
    </div>
    <?php
}

echo "</body></html>";
?>
