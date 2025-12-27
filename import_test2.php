<?php
/**
 * Import von Arbeitszeiten aus Excel/CSV-Dateien - Vereinfacht
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'includes/auth.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';

// Benutzer muss angemeldet sein
requireLogin();

$message = '';
$error = '';
$importResults = [];

// Parameter für Monat/Jahr beim Import
$importYear = isset($_POST['import_year']) ? (int)$_POST['import_year'] : (int)date('Y');
$importMonth = isset($_POST['import_month']) ? (int)$_POST['import_month'] : (int)date('m');
$employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;

echo "<!DOCTYPE html><html lang='sv'><head><meta charset='UTF-8'><title>Import Test</title></head><body>";
echo "<h1>Import-Seite lädt...</h1>";
echo "<p>Wenn Sie dies sehen, funktioniert die Seite grundsätzlich.</p>";
echo "<p>Jahr: $importYear, Monat: $importMonth, Mitarbeiter-ID: $employeeId</p>";

// Hole alle Mitarbeiter
try {
    $employees = getAllEmployees();
    echo "<p>Mitarbeiter gefunden: " . count($employees) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='import.php'>Zurück zum echten Import</a></p>";
echo "</body></html>";
?>
