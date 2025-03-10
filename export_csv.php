<?php
// Fehlerbehandlung aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';
require_once 'includes/date_helper.php';

// Sicherstellen, dass keine Ausgabe vor den Headern erfolgt
ob_start();

try {
    // Parameter aus der URL abrufen
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

    // Mitarbeiter abrufen
    $employee = getEmployeeById($id);
    if (!$employee) {
        throw new Exception('Mitarbeiter nicht gefunden');
    }

    // Arbeitszeiten für den Monat abrufen
    $workHours = getWorkHoursForMonth($id, $month, $year);
    $workHoursByDate = [];
    foreach ($workHours as $workHour) {
        $workHoursByDate[$workHour['date']] = $workHour;
    }

    // Feiertage für den Monat abrufen
    $pdo = getDBConnection();
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $stmt = $pdo->prepare("SELECT date, description FROM holidays WHERE date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $holidays = $stmt->fetchAll();
    
    $holidayDates = [];
    foreach ($holidays as $holiday) {
        $holidayDates[$holiday['date']] = $holiday['description'];
    }

    // Ausgabepuffer leeren
    ob_clean();

    // CSV-Header setzen
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Tidsrapportering_' . $employee['name'] . '_' . $year . '_' . sprintf('%02d', $month) . '.csv');

    // CSV-Datei öffnen
    $output = fopen('php://output', 'w');

    // BOM für Excel hinzufügen
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Überschrift
    $header = array(
        'Datum',
        'Arbetstid',
        'Sjuk',
        'Semester',
        '100%',
        'Avtalad %',
        'Avtalad tid',
        'Arbete',
        'Distansarbete',
        'Saldo'
    );
    fputcsv($output, $header, ';');

    // Alle Tage des Monats durchlaufen
    $startDate = new DateTime("$year-$month-01");
    $endDate = new DateTime($startDate->format('Y-m-t'));

    $currentDate = clone $startDate;
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $dayOfWeek = $currentDate->format('N');
        $dayOfMonth = $currentDate->format('j');
        $isHoliday = isset($holidayDates[$dateStr]);
        $isWeekend = ($dayOfWeek >= 6);
        
        $workHour = $workHoursByDate[$dateStr] ?? [
            'arbetstid' => ($dayOfWeek <= 5 && !$isHoliday) ? 8 : 0,
            'sjuk' => 0,
            'semester' => 0,
            'avtalad_procent' => $employee['avtalad_procent'] ?? 100,
            'arbete' => 0,
            'distansarbete' => 0
        ];
        
        // Berechnungen
        $hundredPercent = $workHour['arbetstid'] - $workHour['sjuk'] - $workHour['semester'];
        $avtaladTid = $hundredPercent * ($workHour['avtalad_procent'] / 100);
        $saldo = $workHour['arbete'] + $workHour['distansarbete'] - $avtaladTid;
        
        // Datum mit Wochentag
        $datumText = $dayOfMonth . ' ' . getDayName($currentDate->format('D'));
        if ($isHoliday) {
            $datumText .= ' (' . $holidayDates[$dateStr] . ')';
        }
        
        // Zeile für CSV vorbereiten
        $row = array(
            $datumText,
            number_format($workHour['arbetstid'], 1, ',', ''),
            number_format($workHour['sjuk'], 1, ',', ''),
            number_format($workHour['semester'], 1, ',', ''),
            number_format($hundredPercent, 1, ',', ''),
            number_format($workHour['avtalad_procent'], 1, ',', ''),
            number_format($avtaladTid, 1, ',', ''),
            number_format($workHour['arbete'], 1, ',', ''),
            number_format($workHour['distansarbete'], 1, ',', ''),
            number_format($saldo, 1, ',', '')
        );
        
        fputcsv($output, $row, ';');
        
        $currentDate->modify('+1 day');
    }

    // Monatssummen
    $monthBalance = getEmployeeMonthBalance($id, $month, $year);

    // Leerzeile
    fputcsv($output, array(''), ';');

    // Summenzeile
    $sumRow = array(
        'Summa',
        number_format($monthBalance['total_arbetstid'], 1, ',', ''),
        number_format($monthBalance['total_sjuk'], 1, ',', ''),
        number_format($monthBalance['total_semester'], 1, ',', ''),
        number_format($monthBalance['total_100'], 1, ',', ''),
        '-',
        number_format($monthBalance['total_avtalad'], 1, ',', ''),
        number_format($monthBalance['total_arbete'], 1, ',', ''),
        number_format($monthBalance['total_distansarbete'], 1, ',', ''),
        number_format($monthBalance['total_saldo'], 1, ',', '')
    );
    fputcsv($output, $sumRow, ';');

    fclose($output);
    
} catch (Exception $e) {
    // Fehlerbehandlung
    ob_clean();
    header('Content-Type: text/plain; charset=utf-8');
    echo "Fehler beim Exportieren: " . $e->getMessage();
}

exit;
?> 