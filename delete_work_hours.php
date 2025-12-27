<?php
require_once 'config/database.php';

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

if ($employeeId > 0) {
    $pdo = getDBConnection();
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    $stmt = $pdo->prepare("DELETE FROM work_hours WHERE employee_id = ? AND date BETWEEN ? AND ?");
    $stmt->execute([$employeeId, $startDate, $endDate]);
    echo "Alle work_hours für Mitarbeiter $employeeId, $year-$month wurden gelöscht.";
} else {
    echo "Bitte employee_id, year und month als GET-Parameter angeben.";
}
