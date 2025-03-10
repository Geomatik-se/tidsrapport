<?php
require_once 'config/database.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';
require_once 'includes/date_helper.php';

// TCPDF-Bibliothek einbinden
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

// Parameter aus der URL abrufen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

// Mitarbeiter abrufen
$employee = getEmployeeById($id);
if (!$employee) {
    die('Mitarbeiter nicht gefunden');
}

// PDF-Dokument erstellen
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Tidsrapportering', 0, 1, 'C');
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Neues PDF-Dokument erstellen
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Dokumenteigenschaften setzen
$pdf->SetCreator('Tidsrapportering');
$pdf->SetAuthor('System');
$pdf->SetTitle('Tidsrapportering - ' . $employee['name']);

// Erste Seite hinzufügen
$pdf->AddPage();

// Überschrift
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, $employee['name'] . ' - ' . getMonthName($month) . ' ' . $year, 0, 1, 'C');
$pdf->Ln(5);

// Tabellenkopf
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(240, 240, 240);

// Spaltenbreiten
$w = array(25, 20, 20, 20, 20, 20, 25, 20, 25, 25);

// Tabellenkopf
$header = array('Datum', 'Arbetstid', 'Sjuk', 'Semester', '100%', 'Avtalad %', 'Avtalad tid', 'Arbete', 'Distans', 'Saldo');
foreach($header as $i => $col) {
    $pdf->Cell($w[$i], 7, $col, 1, 0, 'C', true);
}
$pdf->Ln();

// Tabelleninhalt
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

// Arbeitszeiten für den Monat abrufen
$workHours = getWorkHoursForMonth($id, $month, $year);
$workHoursByDate = [];
foreach ($workHours as $workHour) {
    $workHoursByDate[$workHour['date']] = $workHour;
}

// Alle Tage des Monats durchlaufen
$startDate = new DateTime("$year-$month-01");
$endDate = new DateTime($startDate->format('Y-m-t'));

$currentDate = clone $startDate;
while ($currentDate <= $endDate) {
    $dateStr = $currentDate->format('Y-m-d');
    $dayOfWeek = $currentDate->format('N');
    $dayOfMonth = $currentDate->format('j');
    $isHoliday = isHoliday($dateStr);
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
    
    // Hintergrundfarbe für Wochenenden und Feiertage
    if ($isHoliday || $isWeekend) {
        $pdf->SetFillColor(245, 245, 245);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    
    // Datum mit Wochentag
    $datumText = $dayOfMonth . ' ' . getDayName($currentDate->format('D'));
    if ($isHoliday) {
        $datumText .= "\n" . getHolidayName($dateStr);
    }
    
    $pdf->Cell($w[0], 7, $datumText, 1, 0, 'L', true);
    $pdf->Cell($w[1], 7, number_format($workHour['arbetstid'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[2], 7, number_format($workHour['sjuk'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[3], 7, number_format($workHour['semester'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[4], 7, number_format($hundredPercent, 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[5], 7, number_format($workHour['avtalad_procent'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[6], 7, number_format($avtaladTid, 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[7], 7, number_format($workHour['arbete'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[8], 7, number_format($workHour['distansarbete'], 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Cell($w[9], 7, number_format($saldo, 1, ',', ' '), 1, 0, 'R', true);
    $pdf->Ln();
    
    $currentDate->modify('+1 day');
}

// Monatssummen
$monthBalance = getEmployeeMonthBalance($id, $month, $year);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(240, 240, 240);

$pdf->Cell($w[0], 7, 'Summa', 1, 0, 'L', true);
$pdf->Cell($w[1], 7, number_format($monthBalance['total_arbetstid'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[2], 7, number_format($monthBalance['total_sjuk'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[3], 7, number_format($monthBalance['total_semester'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[4], 7, number_format($monthBalance['total_100'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[5], 7, '-', 1, 0, 'R', true);
$pdf->Cell($w[6], 7, number_format($monthBalance['total_avtalad'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[7], 7, number_format($monthBalance['total_arbete'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[8], 7, number_format($monthBalance['total_distansarbete'], 1, ',', ' '), 1, 0, 'R', true);
$pdf->Cell($w[9], 7, number_format($monthBalance['total_saldo'], 1, ',', ' '), 1, 0, 'R', true);

// PDF ausgeben
$pdf->Output('Tidsrapportering_' . $employee['name'] . '_' . $year . '_' . $month . '.pdf', 'D');
?> 