<?php
/**
 * Admin-Script zum automatischen Generieren von Feiertagen für zukünftige Jahre
 */

require_once 'includes/auth.php';
require_once 'includes/holiday_generator.php';

// Benutzer muss angemeldet sein
requireLogin();

$success = false;
$error = '';
$report = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate'])) {
        $startYear = isset($_POST['start_year']) ? (int)$_POST['start_year'] : (int)date('Y');
        $endYear = isset($_POST['end_year']) ? (int)$_POST['end_year'] : $startYear + 5;
        
        // Validierung
        if ($startYear < 2020 || $startYear > 2050) {
            $error = 'Startår måste vara mellan 2020 och 2050.';
        } elseif ($endYear < $startYear || $endYear > 2050) {
            $error = 'Slutår måste vara större än eller lika med startår och max 2050.';
        } elseif (($endYear - $startYear) > 20) {
            $error = 'Du kan max generera röda dagar för 20 år åt gången.';
        } else {
            try {
                $report = autoGenerateHolidays($startYear, $endYear);
                $success = true;
            } catch (Exception $e) {
                $error = 'Ett fel uppstod: ' . $e->getMessage();
            }
        }
    }
}

// Statistik abrufen - für welche Jahre existieren bereits Feiertage?
$currentYear = (int)date('Y');
$existingYearsStats = [];

for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
    $holidays = getHolidaysForYear($year);
    $existingYearsStats[$year] = count($holidays);
}

$pageTitle = 'Generera röda dagar';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-table th, .stats-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .stats-table th {
            background-color: #f2f2f2;
        }
        .has-holidays {
            color: green;
            font-weight: bold;
        }
        .no-holidays {
            color: red;
        }
        .form-row {
            display: flex;
            gap: 15px;
            align-items: end;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .report-table th {
            background-color: #f2f2f2;
        }
        .added {
            color: green;
            font-weight: bold;
        }
        .existed {
            color: orange;
        }
    </style>
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
                <a href="holidays.php" class="btn">Tillbaka till röda dagar</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">
                Röda dagar har genererats!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Nuvarande status</h2>
            <p>Översikt över vilka år som redan har röda dagar inlagda:</p>
            
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>År</th>
                        <th>Antal röda dagar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($existingYearsStats as $year => $count): ?>
                        <tr>
                            <td>
                                <a href="holidays.php?year=<?php echo $year; ?>">
                                    <?php echo $year; ?>
                                </a>
                            </td>
                            <td><?php echo $count; ?></td>
                            <td class="<?php echo $count > 0 ? 'has-holidays' : 'no-holidays'; ?>">
                                <?php echo $count > 0 ? 'Finns' : 'Saknas'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>Generera röda dagar</h2>
            <p>Detta verktyg genererar automatiskt svenska röda dagar för de angivna åren. Endast år som saknar röda dagar kommer att få nya röda dagar tillagda.</p>
            
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_year">Från år:</label>
                        <input type="number" id="start_year" name="start_year" 
                               value="<?php echo $currentYear; ?>" 
                               min="2020" max="2050" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_year">Till år:</label>
                        <input type="number" id="end_year" name="end_year" 
                               value="<?php echo $currentYear + 5; ?>" 
                               min="2020" max="2050" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="generate" class="btn btn-primary">
                            Generera röda dagar
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($success && !empty($report)): ?>
            <div class="section">
                <h2>Resultat</h2>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>År</th>
                            <th>Nya röda dagar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report as $year => $added): ?>
                            <tr>
                                <td>
                                    <a href="holidays.php?year=<?php echo $year; ?>">
                                        <?php echo $year; ?>
                                    </a>
                                </td>
                                <td class="<?php echo $added > 0 ? 'added' : 'existed'; ?>">
                                    <?php echo $added; ?>
                                </td>
                                <td class="<?php echo $added > 0 ? 'added' : 'existed'; ?>">
                                    <?php echo $added > 0 ? 'Tillagda' : 'Fanns redan'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                $totalAdded = array_sum($report);
                if ($totalAdded > 0) {
                    echo "<p><strong>Totalt $totalAdded nya röda dagar tillagda!</strong></p>";
                } else {
                    echo "<p>Inga nya röda dagar behövde läggas till - alla år hade redan röda dagar.</p>";
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Information</h2>
            <p>Följande svenska röda dagar genereras automatiskt för varje år:</p>
            <ul>
                <li><strong>Fasta datum:</strong> Nyårsdagen, Trettondedag jul, Första maj, Sveriges nationaldag, Julafton, Juldagen, Annandag jul, Nyårsafton</li>
                <li><strong>Rörliga datum (baserat på påsk):</strong> Långfredag, Påskdagen, Annandag påsk, Kristi himmelfärdsdag, Pingstdagen</li>
                <li><strong>Andra rörliga datum:</strong> Midsommarafton, Midsommardagen, Alla helgons dag</li>
            </ul>
        </div>
    </div>
</body>
</html>