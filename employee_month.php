<?php
/**
 * Monatsansicht eines Mitarbeiters
 */

require_once 'includes/auth.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';
require_once 'includes/date_helper.php';
require_once 'includes/navigation_helper.php';

// Benutzer muss angemeldet sein
requireLogin();

// Parameter aus der URL abrufen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

// Mitarbeiter abrufen
$employee = getEmployeeById($id);

// Wenn der Mitarbeiter nicht existiert, zur Mitarbeiterliste umleiten
if (!$employee) {
    header('Location: employees.php');
    exit;
}

// Vorheriger und nächster Monat
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Arbeitszeiten für den Monat initialisieren, falls noch nicht vorhanden
initializeMonthWorkHours($id, $month, $year);

// Arbeitszeiten für den Monat abrufen
$workHours = getWorkHoursForMonth($id, $month, $year);

// Arbeitszeiten nach Datum indizieren
$workHoursByDate = [];
foreach ($workHours as $workHour) {
    $workHoursByDate[$workHour['date']] = $workHour;
}

// Feiertage für das Jahr abrufen
$holidays = getHolidaysForYear($year);
$holidayDates = [];
foreach ($holidays as $holiday) {
    $holidayDates[$holiday['date']] = $holiday['description'];
}

// Formular verarbeiten
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = $_POST['work_hours'] ?? [];
    $success = true;
    
    foreach ($updates as $date => $data) {
        $arbetstid = isset($data['arbetstid']) ? (float)$data['arbetstid'] : 0;
        $sjuk = isset($data['sjuk']) ? (float)$data['sjuk'] : 0;
        $semester = isset($data['semester']) ? (float)$data['semester'] : 0;
        $avtaladProcent = isset($data['avtalad_procent']) ? (float)$data['avtalad_procent'] : 100;
        $arbete = isset($data['arbete']) ? (float)$data['arbete'] : 0;
        $distansarbete = isset($data['distansarbete']) ? (float)$data['distansarbete'] : 0;
        
        if (!saveWorkHours($id, $date, $arbetstid, $sjuk, $semester, $avtaladProcent, $arbete, $distansarbete)) {
            $success = false;
            $error = 'Det gick inte att spara alla ändringar.';
            break;
        }
    }
    
    if ($success) {
        // Aktualisierte Daten abrufen
        $workHours = getWorkHoursForMonth($id, $month, $year);
        $workHoursByDate = [];
        foreach ($workHours as $workHour) {
            $workHoursByDate[$workHour['date']] = $workHour;
        }
    }
}

// Monatssummen berechnen
$monthBalance = getEmployeeMonthBalance($id, $month, $year);

// Seitentitel
$pageTitle = htmlspecialchars($employee['name']) . ' - ' . getMonthName($month) . ' ' . $year;
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // JavaScript-Funktion zur Berechnung der Werte
        function calculateValues(row) {
            const arbetstid = parseFloat(row.querySelector('.arbetstid').value) || 0;
            const sjuk = parseFloat(row.querySelector('.sjuk').value) || 0;
            const semester = parseFloat(row.querySelector('.semester').value) || 0;
            const avtaladProcent = parseFloat(row.querySelector('.avtalad-procent').value) || 0;
            const arbete = parseFloat(row.querySelector('.arbete').value) || 0;
            const distansarbete = parseFloat(row.querySelector('.distansarbete').value) || 0;
            
            // 100% berechnen
            const hundredPercent = arbetstid - sjuk - semester;
            row.querySelector('.hundred-percent').textContent = hundredPercent.toFixed(1);
            
            // Avtalad tid berechnen
            const avtaladTid = hundredPercent * (avtaladProcent / 100);
            row.querySelector('.avtalad-tid').textContent = avtaladTid.toFixed(1);
            
            // Saldo berechnen
            const saldo = arbete + distansarbete - avtaladTid;
            row.querySelector('.saldo').textContent = saldo.toFixed(1);
        }
        
        // Alle Zeilen berechnen
        function calculateAllRows() {
            document.querySelectorAll('tr.day-row').forEach(row => {
                calculateValues(row);
            });
        }
        
        // Event-Listener hinzufügen, wenn das Dokument geladen ist
        document.addEventListener('DOMContentLoaded', function() {
            // Berechnung bei Änderung der Eingabefelder
            document.querySelectorAll('input.calc-trigger').forEach(input => {
                input.addEventListener('input', function() {
                    calculateValues(this.closest('tr'));
                });
            });
            
            // Initial alle Zeilen berechnen
            calculateAllRows();
        });
    </script>
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
                    <li><a href="logout.php">Logga ut</a></li>
                </ul>
            </nav>
            <div class="user-info">
                Inloggad som: <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo $pageTitle; ?></h1>
            <div class="page-actions">
                <?php echo renderYearNavigation($year, 'employee_month.php', ['id' => $id, 'month' => $month]); ?>
                <?php echo renderMonthSelector($month, $year, 'employee_month.php?id=' . $id); ?>
                <a href="employee_year.php?id=<?php echo $id; ?>&year=<?php echo $year; ?>" class="btn">Visa årsöversikt</a>
                <a href="export_csv.php?id=<?php echo $id; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-primary">Als Excel exportieren</a>
                <a href="employees.php" class="btn">Tillbaka till medarbetare</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">Ändringarna har sparats.</div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th class="grey-bg">Arbetstid</th>
                        <th>Sjuk</th>
                        <th>Semester</th>
                        <th>100%</th>
                        <th>Avtalad %</th>
                        <th class="grey-bg">Avtalad tid</th>
                        <th>Arbete</th>
                        <th>Distansarbete</th>
                        <th class="grey-bg">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Alle Tage des Monats durchlaufen
                    $startDate = new DateTime("$year-$month-01");
                    $endDate = new DateTime($startDate->format('Y-m-t'));
                    
                    $currentDate = clone $startDate;
                    while ($currentDate <= $endDate) {
                        $dateStr = $currentDate->format('Y-m-d');
                        $dayOfWeek = $currentDate->format('N'); // 1 (Montag) bis 7 (Sonntag)
                        $dayOfMonth = $currentDate->format('j');
                        $isHoliday = isset($holidayDates[$dateStr]);
                        $isWeekend = ($dayOfWeek >= 6); // Samstag oder Sonntag
                        
                        $workHour = $workHoursByDate[$dateStr] ?? [
                            'arbetstid' => ($dayOfWeek <= 5 && !$isHoliday) ? 8 : 0,
                            'sjuk' => 0,
                            'semester' => 0,
                            'avtalad_procent' => isset($employee['avtalad_procent']) ? $employee['avtalad_procent'] : 100,
                            'arbete' => 0,
                            'distansarbete' => 0
                        ];
                        
                        $rowClass = '';
                        if ($isHoliday) {
                            $rowClass = 'holiday';
                        } elseif ($isWeekend) {
                            $rowClass = 'weekend';
                        }
                    ?>
                        <tr class="day-row <?php echo $rowClass; ?>">
                            <td>
                                <?php echo $dayOfMonth; ?> 
                                <?php echo getDayName($currentDate->format('D')); ?>
                                <?php if ($isHoliday): ?>
                                    <span class="holiday-name"><?php echo htmlspecialchars($holidayDates[$dateStr]); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="grey-bg">
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][arbetstid]" 
                                       value="<?php echo $workHour['arbetstid']; ?>" 
                                       step="0.1" min="0" class="arbetstid calc-trigger">
                            </td>
                            <td>
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][sjuk]" 
                                       value="<?php echo $workHour['sjuk']; ?>" 
                                       step="0.1" min="0" class="sjuk calc-trigger">
                            </td>
                            <td>
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][semester]" 
                                       value="<?php echo $workHour['semester']; ?>" 
                                       step="0.1" min="0" class="semester calc-trigger">
                            </td>
                            <td class="hundred-percent">
                                <?php echo number_format($workHour['arbetstid'] - $workHour['sjuk'] - $workHour['semester'], 1, ',', ' '); ?>
                            </td>
                            <td>
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][avtalad_procent]" 
                                       value="<?php echo $workHour['avtalad_procent']; ?>" 
                                       step="0.1" min="0" max="100" class="avtalad-procent calc-trigger">
                            </td>
                            <td class="grey-bg avtalad-tid">
                                <?php 
                                $hundredPercent = $workHour['arbetstid'] - $workHour['sjuk'] - $workHour['semester'];
                                echo number_format($hundredPercent * ($workHour['avtalad_procent'] / 100), 1, ',', ' ');
                                ?>
                            </td>
                            <td>
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][arbete]" 
                                       value="<?php echo $workHour['arbete']; ?>" 
                                       step="0.1" min="0" class="arbete calc-trigger">
                            </td>
                            <td>
                                <input type="number" name="work_hours[<?php echo $dateStr; ?>][distansarbete]" 
                                       value="<?php echo $workHour['distansarbete']; ?>" 
                                       step="0.1" min="0" class="distansarbete calc-trigger">
                            </td>
                            <td class="grey-bg saldo">
                                <?php 
                                $hundredPercent = $workHour['arbetstid'] - $workHour['sjuk'] - $workHour['semester'];
                                $avtaladTid = $hundredPercent * ($workHour['avtalad_procent'] / 100);
                                echo number_format($workHour['arbete'] + $workHour['distansarbete'] - $avtaladTid, 1, ',', ' ');
                                ?>
                            </td>
                        </tr>
                    <?php
                        $currentDate->modify('+1 day');
                    }
                    ?>
                    <tr class="total-row">
                        <td><strong>Summa</strong></td>
                        <td class="grey-bg"><?php echo number_format($monthBalance['total_arbetstid'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthBalance['total_sjuk'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthBalance['total_semester'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthBalance['total_100'], 1, ',', ' '); ?></td>
                        <td>-</td>
                        <td class="grey-bg"><?php echo number_format($monthBalance['total_avtalad'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthBalance['total_arbete'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthBalance['total_distansarbete'], 1, ',', ' '); ?></td>
                        <td class="grey-bg"><?php echo number_format($monthBalance['total_saldo'], 1, ',', ' '); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Spara ändringar</button>
            </div>
        </form>
    </div>
</body>
</html> 