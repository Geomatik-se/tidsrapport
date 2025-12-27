<?php
/**
 * Jahresansicht eines Mitarbeiters
 */

require_once 'includes/auth.php';
require_once 'includes/employees.php';
require_once 'includes/date_helper.php';
require_once 'includes/navigation_helper.php';

// Benutzer muss angemeldet sein
requireLogin();

// Parameter aus der URL abrufen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Mitarbeiter abrufen
$employee = getEmployeeById($id);

// Wenn der Mitarbeiter nicht existiert, zur Mitarbeiterliste umleiten
if (!$employee) {
    header('Location: employees.php');
    exit;
}

// Monatsdaten für das Jahr abrufen
$monthsData = [];
$yearTotals = [
    'total_arbetstid' => 0,
    'total_sjuk' => 0,
    'total_semester' => 0,
    'total_100' => 0,
    'total_avtalad' => 0,
    'total_arbete' => 0,
    'total_distansarbete' => 0,
    'total_saldo' => 0
];

for ($month = 1; $month <= 12; $month++) {
    $monthData = getEmployeeMonthBalance($id, $month, $year);
    $monthsData[$month] = $monthData;
    
    // Jahressummen aktualisieren
    foreach ($yearTotals as $key => $value) {
        $yearTotals[$key] += $monthData[$key];
    }
}

// Seitentitel
$pageTitle = htmlspecialchars($employee['name']) . ' - Årsöversikt ' . $year;
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <?php echo renderYearNavigation($year, 'employee_year.php', ['id' => $id]); ?>
                <a href="export_year_csv.php?id=<?php echo $id; ?>&year=<?php echo $year; ?>" class="btn btn-primary">Als Excel exportieren</a>
                <a href="employees.php" class="btn">Tillbaka till medarbetare</a>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Månad</th>
                    <th class="grey-bg">Arbetstid</th>
                    <th>Sjuk</th>
                    <th>Semester</th>
                    <th>100%</th>
                    <th>Avtalad tid</th>
                    <th class="grey-bg">Arbete</th>
                    <th>Distansarbete</th>
                    <th class="grey-bg">Saldo</th>
                    <th>Åtgärder</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($month = 1; $month <= 12; $month++): ?>
                    <tr>
                        <td><?php echo getMonthName($month); ?></td>
                        <td class="grey-bg"><?php echo number_format($monthsData[$month]['total_arbetstid'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthsData[$month]['total_sjuk'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthsData[$month]['total_semester'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthsData[$month]['total_100'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthsData[$month]['total_avtalad'], 1, ',', ' '); ?></td>
                        <td class="grey-bg"><?php echo number_format($monthsData[$month]['total_arbete'], 1, ',', ' '); ?></td>
                        <td><?php echo number_format($monthsData[$month]['total_distansarbete'], 1, ',', ' '); ?></td>
                        <td class="grey-bg"><?php echo number_format($monthsData[$month]['total_saldo'], 1, ',', ' '); ?></td>
                        <td>
                            <a href="employee_month.php?id=<?php echo $id; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn">Visa detaljer</a>
                        </td>
                    </tr>
                <?php endfor; ?>
                <tr class="total-row">
                    <td>Summa</td>
                    <td class="grey-bg"><?php echo number_format($yearTotals['total_arbetstid'], 1, ',', ' '); ?></td>
                    <td><?php echo number_format($yearTotals['total_sjuk'], 1, ',', ' '); ?></td>
                    <td><?php echo number_format($yearTotals['total_semester'], 1, ',', ' '); ?></td>
                    <td><?php echo number_format($yearTotals['total_100'], 1, ',', ' '); ?></td>
                    <td><?php echo number_format($yearTotals['total_avtalad'], 1, ',', ' '); ?></td>
                    <td class="grey-bg"><?php echo number_format($yearTotals['total_arbete'], 1, ',', ' '); ?></td>
                    <td><?php echo number_format($yearTotals['total_distansarbete'], 1, ',', ' '); ?></td>
                    <td class="grey-bg"><?php echo number_format($yearTotals['total_saldo'], 1, ',', ' '); ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html> 