<?php
// Debug für employee_month.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Employee Month</title></head><body>";
echo "<h1>Debug: Employee Month</h1>";

try {
    echo "<p>1. Lade auth.php...</p>";
    require_once 'includes/auth.php';
    echo "<p style='color: green;'>✓ auth.php geladen</p>";
    
    echo "<p>2. Lade employees.php...</p>";
    require_once 'includes/employees.php';
    echo "<p style='color: green;'>✓ employees.php geladen</p>";
    
    echo "<p>3. Lade work_hours.php...</p>";
    require_once 'includes/work_hours.php';
    echo "<p style='color: green;'>✓ work_hours.php geladen</p>";
    
    echo "<p>4. Lade date_helper.php...</p>";
    require_once 'includes/date_helper.php';
    echo "<p style='color: green;'>✓ date_helper.php geladen</p>";
    
    echo "<p>5. Lade navigation_helper.php...</p>";
    require_once 'includes/navigation_helper.php';
    echo "<p style='color: green;'>✓ navigation_helper.php geladen</p>";
    
    // Test Parameter
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    
    echo "<p>6. Parameter: ID=$id, Year=$year, Month=$month</p>";
    
    echo "<p>7. Hole Mitarbeiter...</p>";
    $employee = getEmployeeById($id);
    
    if (!$employee) {
        echo "<p style='color: red;'>✗ Mitarbeiter mit ID $id nicht gefunden!</p>";
        echo "<p><a href='employees.php'>Zu Mitarbeitern →</a></p>";
    } else {
        echo "<p style='color: green;'>✓ Mitarbeiter gefunden: {$employee['name']}</p>";
        echo "<pre>" . print_r($employee, true) . "</pre>";
        
        echo "<p>8. Initialisiere Arbeitszeiten...</p>";
        try {
            initializeMonthWorkHours($id, $month, $year);
            echo "<p style='color: green;'>✓ Arbeitszeiten initialisiert</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Fehler bei initializeMonthWorkHours: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        
        echo "<p>9. Hole Arbeitszeiten...</p>";
        try {
            $workHours = getWorkHoursForMonth($id, $month, $year);
            echo "<p style='color: green;'>✓ Arbeitszeiten geladen: " . count($workHours) . " Einträge</p>";
            
            if (!empty($workHours)) {
                echo "<details><summary>Erste 3 Einträge anzeigen</summary>";
                echo "<pre>" . print_r(array_slice($workHours, 0, 3), true) . "</pre>";
                echo "</details>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Fehler bei getWorkHoursForMonth: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }
    
    echo "<hr>";
    echo "<p>Wenn alle Tests erfolgreich sind, sollte <a href='employee_month.php?id=$id'>employee_month.php?id=$id</a> funktionieren.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>FEHLER:</p>";
    echo "<pre style='background: #fee; padding: 10px; border: 1px solid red;'>";
    echo htmlspecialchars($e->getMessage());
    echo "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "</body></html>";
?>
