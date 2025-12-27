<?php
/**
 * Import von Arbeitszeiten aus CSV-Dateien
 */

require_once 'includes/auth.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';

requireLogin();

$message = '';
$error = '';
$importYear = isset($_POST['import_year']) ? (int)$_POST['import_year'] : (int)date('Y');
$importMonth = isset($_POST['import_month']) ? (int)$_POST['import_month'] : (int)date('m');
$employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;

// CSV Import verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext === 'csv') {
            $imported = 0;
            $errors = 0;
            $details = [];
            
            if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
                // Detect delimiter
                $firstLine = fgets($handle);
                rewind($handle);
                $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
                
                fgetcsv($handle, 1000, $delimiter); // Skip header
                $line = 1;
                
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    $line++;
                    if (empty(array_filter($data))) continue;
                    
                    try {
                        // Debug: Zeige erste 3 Zeilen
                        if ($line <= 4) {
                            $details[] = "Zeile $line: " . implode(' | ', array_map('trim', $data));
                        }
                        
                        // Parse Datum (z.B. "1 Mån" oder "2025-12-01")
                        $dateStr = trim($data[0]);
                        if (preg_match('/^(\d+)/', $dateStr, $m)) {
                            $day = (int)$m[1];
                            $date = sprintf('%04d-%02d-%02d', $importYear, $importMonth, $day);
                        } else {
                            $errors++;
                            $details[] = "Zeile $line: Ungültiges Datum '$dateStr'";
                            continue;
                        }
                        
                        // Werte aus den richtigen Spalten lesen
                        $arbetstid = isset($data[1]) && trim($data[1]) !== '' ? floatval(str_replace(',', '.', trim($data[1]))) : 0;
                        $sjuk = isset($data[2]) && trim($data[2]) !== '' ? floatval(str_replace(',', '.', trim($data[2]))) : 0;
                        $semester = isset($data[3]) && trim($data[3]) !== '' ? floatval(str_replace(',', '.', trim($data[3]))) : 0;
                        
                        if ($employeeId > 0) {
                            $pdo = getDBConnection();
                            $year = $importYear;
                            $month = $importMonth;
                            
                            $stmt = $pdo->prepare("SELECT id FROM work_hours WHERE employee_id = ? AND date = ?");
                            $stmt->execute([$employeeId, $date]);
                            $existing = $stmt->fetch();
                            
                            if ($existing) {
                                $stmt = $pdo->prepare("UPDATE work_hours SET arbetstid=?, sjuk=?, semester=?, year=?, month=? WHERE id=?");
                                $stmt->execute([$arbetstid, $sjuk, $semester, $year, $month, $existing['id']]);
                            } else {
                                $stmt = $pdo->prepare("INSERT INTO work_hours (employee_id, date, arbetstid, sjuk, semester, year, month, avtalad_procent, arbete, distansarbete) VALUES (?, ?, ?, ?, ?, ?, ?, 60, 0, 0)");
                                $stmt->execute([$employeeId, $date, $arbetstid, $sjuk, $semester, $year, $month]);
                            }
                            $imported++;
                        }
                    } catch (Exception $e) {
                        $errors++;
                        $details[] = "Zeile $line Fehler: " . $e->getMessage();
                    }
                }
                fclose($handle);
                $message = "Import erfolgreich! $imported Zeilen importiert, $errors Fehler.";
                if (!empty($details)) {
                    $message .= " (Details in Konsole prüfen)";
                }
            }
        } else {
            $error = 'Nur CSV-Dateien sind erlaubt.';
        }
    } else {
        $error = 'Fehler beim Hochladen der Datei.';
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Import - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .import-box { max-width: 700px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; }
        .msg-success { padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin: 10px 0; }
        .msg-error { padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin: 10px 0; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; }
        .btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .upload-box { border: 2px dashed #ccc; padding: 30px; text-align: center; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">Tidsrapportering</div>
            <nav>
                <ul>
                    <li><a href="index.php">Hem</a></li>
                    <li><a href="employees.php">Medarbetare</a></li>
                    <li><a href="holidays.php">Röda dagar</a></li>
                    <li><a href="import.php" class="active">Importera</a></li>
                    <li><a href="logout.php">Logga ut</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="import-box">
            <h1>Importera arbetstider</h1>
            
            <?php if ($message): ?>
                <div class="msg-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Mitarbeiter:</label>
                    <select name="employee_id" required>
                        <option value="">-- Välj medarbetare --</option>
                        <?php
                        $employees = getAllEmployees();
                        foreach ($employees as $emp) {
                            $sel = ($emp['id'] == $employeeId) ? 'selected' : '';
                            echo "<option value='{$emp['id']}' $sel>{$emp['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jahr:</label>
                    <input type="number" name="import_year" value="<?php echo $importYear; ?>" min="2020" max="2030" required>
                </div>
                
                <div class="form-group">
                    <label>Monat:</label>
                    <select name="import_month" required>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($m == $importMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="upload-box">
                    <input type="file" name="import_file" accept=".csv" required>
                    <p>CSV-Datei auswählen (Format: Datum, Arbeitszeit, Krank, Urlaub)</p>
                </div>
                
                <button type="submit" class="btn">Importera</button>
                <a href="index.php" class="btn" style="background: #95a5a6; margin-left: 10px;">Avbryt</a>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 4px;">
                <h3>Format-Info</h3>
                <p>CSV-Datei sollte folgende Spalten haben:</p>
                <ol>
                    <li>Datum (z.B. "1 Mån" oder "27")</li>
                    <li>Arbeitszeit (Stunden)</li>
                    <li>Krank (Stunden)</li>
                    <li>Urlaub (Stunden)</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
