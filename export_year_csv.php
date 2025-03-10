<?php
require_once 'config/database.php';
require_once 'includes/employees.php';
require_once 'includes/work_hours.php';
require_once 'includes/date_helper.php';

// Parameter aus der URL abrufen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Mitarbeiter abrufen
$employee = getEmployeeById($id);
if (!$employee) {
    die('Mitarbeiter nicht gefunden');
}

// HTML-Header setzen
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=Tidsrapportering_' . $employee['name'] . '_' . $year . '.xls');

// HTML-Ausgabe starten
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th {
            background-color: #4a4a4a;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 4px;
            border: 1px solid #2a2a2a;
        }
        
        td {
            padding: 4px;
            border: 1px solid #cccccc;
            text-align: right;
            mso-number-format:"0\.0";
        }
        
        td:first-child {
            text-align: left;
        }
        
        .grey-bg {
            background-color: #f0f0f0;
        }
        
        .total-row {
            font-weight: bold;
            border-top: 2px solid #2a2a2a;
        }
        
        .total-row td {
            background-color: #e6e6e6;
        }
        
        .total-row td.grey-bg {
            background-color: #d9d9d9;
        }
        
        /* Titel */
        .title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            background-color: #4a4a4a;
            color: white;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="title">Tidsrapportering - <?php echo htmlspecialchars($employee['name']); ?> - <?php echo $year; ?></div>
    <table>
        <tr>
            <th>Monat</th>
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
        <?php
        // Durch alle Monate des Jahres iterieren
        for ($month = 1; $month <= 12; $month++) {
            // Monatssummen abrufen
            $monthBalance = getEmployeeMonthBalance($id, $month, $year);
            ?>
            <tr>
                <td><?php echo getMonthName($month); ?></td>
                <td class="grey-bg"><?php echo number_format($monthBalance['total_arbetstid'], 1, ',', ''); ?></td>
                <td><?php echo number_format($monthBalance['total_sjuk'], 1, ',', ''); ?></td>
                <td><?php echo number_format($monthBalance['total_semester'], 1, ',', ''); ?></td>
                <td><?php echo number_format($monthBalance['total_100'], 1, ',', ''); ?></td>
                <td><?php echo $month == 1 ? number_format($employee['avtalad_procent'] ?? 100, 1, ',', '') : '-'; ?></td>
                <td class="grey-bg"><?php echo number_format($monthBalance['total_avtalad'], 1, ',', ''); ?></td>
                <td><?php echo number_format($monthBalance['total_arbete'], 1, ',', ''); ?></td>
                <td><?php echo number_format($monthBalance['total_distansarbete'], 1, ',', ''); ?></td>
                <td class="grey-bg"><?php echo number_format($monthBalance['total_saldo'], 1, ',', ''); ?></td>
            </tr>
            <?php
        }
        
        // Jahressummen
        $yearBalance = getEmployeeYearBalance($id, $year);
        ?>
        <tr class="total-row">
            <td>Summa</td>
            <td class="grey-bg"><?php echo number_format($yearBalance['total_arbetstid'], 1, ',', ''); ?></td>
            <td><?php echo number_format($yearBalance['total_sjuk'], 1, ',', ''); ?></td>
            <td><?php echo number_format($yearBalance['total_semester'], 1, ',', ''); ?></td>
            <td><?php echo number_format($yearBalance['total_100'], 1, ',', ''); ?></td>
            <td>-</td>
            <td class="grey-bg"><?php echo number_format($yearBalance['total_avtalad'], 1, ',', ''); ?></td>
            <td><?php echo number_format($yearBalance['total_arbete'], 1, ',', ''); ?></td>
            <td><?php echo number_format($yearBalance['total_distansarbete'], 1, ',', ''); ?></td>
            <td class="grey-bg"><?php echo number_format($yearBalance['total_saldo'], 1, ',', ''); ?></td>
        </tr>
    </table>
</body>
</html><?php
// Skript beenden
exit;
?> 