<?php
require_once 'config/database.php';

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

$pdo = getDBConnection();
$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate = date('Y-m-t', strtotime($startDate));

$stmt = $pdo->prepare("SELECT * FROM work_hours WHERE employee_id = ? AND date BETWEEN ? AND ? ORDER BY date");
$stmt->execute([$employeeId, $startDate, $endDate]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>work_hours für Mitarbeiter $employeeId, $year-$month</h2>";
echo "<pre>";
foreach ($rows as $row) {
    print_r($row);
}
echo "</pre>";
