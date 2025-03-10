<?php
/**
 * Startseite der Zeiterfassungssoftware
 */

require_once 'includes/auth.php';
require_once 'includes/employees.php';
require_once 'includes/date_helper.php';

// Benutzer muss angemeldet sein
requireLogin();

// Aktuelles Jahr und Monat
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

// Ansicht (Monat oder Jahr)
$view = isset($_GET['view']) ? $_GET['view'] : 'month';

// Mitarbeiter-Daten abrufen
if ($view === 'month') {
    $employeesData = getAllEmployeesMonthBalance($currentMonth, $currentYear);
} else {
    $employeesData = getAllEmployeesYearBalance($currentYear);
}

// Titel der Seite
$pageTitle = $view === 'month' 
    ? getMonthName($currentMonth) . ' ' . $currentYear
    : 'Årsöversikt ' . $currentYear;

// Vorheriger und nächster Monat/Jahr
if ($view === 'month') {
    $prevMonth = $currentMonth - 1;
    $prevYear = $currentYear;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }
    
    $nextMonth = $currentMonth + 1;
    $nextYear = $currentYear;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    
    $prevUrl = "?view=month&year=$prevYear&month=$prevMonth";
    $nextUrl = "?view=month&year=$nextYear&month=$nextMonth";
} else {
    $prevUrl = "?view=year&year=" . ($currentYear - 1);
    $nextUrl = "?view=year&year=" . ($currentYear + 1);
}

// Wechsel zwischen Monats- und Jahresansicht
$toggleViewUrl = $view === 'month' 
    ? "?view=year&year=$currentYear" 
    : "?view=month&year=$currentYear&month=$currentMonth";
$toggleViewText = $view === 'month' ? 'Visa årsöversikt' : 'Visa månadsöversikt';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidsrapportering - <?php echo htmlspecialchars($pageTitle); ?></title>
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
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            
            <div class="page-actions">
                <a href="<?php echo $prevUrl; ?>" class="btn">&laquo; Föregående</a>
                <a href="<?php echo $toggleViewUrl; ?>" class="btn"><?php echo $toggleViewText; ?></a>
                <a href="<?php echo $nextUrl; ?>" class="btn">Nästa &raquo;</a>
            </div>
        </div>
        
        <div class="employee-cards">
            <?php if (empty($employeesData)): ?>
                <div class="employee-card">
                    <h3>Inga medarbetare hittades</h3>
                    <div class="actions">
                        <a href="employee_add.php" class="btn btn-success">+ Lägg till din första medarbetare</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($employeesData as $data): ?>
                    <div class="employee-card">
                        <h3><?php echo htmlspecialchars($data['employee']['name']); ?></h3>
                        <div class="balance">
                            Saldo: <?php echo number_format($data['balance']['total_saldo'], 1, ',', ' '); ?> timmar
                        </div>
                        <div class="actions">
                            <a href="employee_month.php?id=<?php echo $data['employee']['id']; ?>&year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth; ?>" class="btn">
                                Visa detaljer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="employee-card add-employee">
                <h3>Lägg till medarbetare</h3>
                <div class="actions">
                    <a href="employee_add.php" class="btn btn-success">+ Ny medarbetare</a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($employeesData)): ?>
            <h2>Översikt</h2>
            <table>
                <thead>
                    <tr>
                        <th>Medarbetare</th>
                        <th>Arbetstid</th>
                        <th>Sjuk</th>
                        <th>Semester</th>
                        <th>100%</th>
                        <th>Avtalad tid</th>
                        <th>Arbete</th>
                        <th>Distansarbete</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employeesData as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['employee']['name']); ?></td>
                            <td><?php echo number_format($data['balance']['total_arbetstid'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_sjuk'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_semester'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_100'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_avtalad'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_arbete'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_distansarbete'], 1, ',', ' '); ?></td>
                            <td><?php echo number_format($data['balance']['total_saldo'], 1, ',', ' '); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html> 