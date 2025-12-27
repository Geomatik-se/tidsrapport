<?php
/**
 * Verwaltung der Feiertage
 */

require_once 'includes/auth.php';
require_once 'includes/work_hours.php';
require_once 'includes/date_helper.php';
require_once 'includes/navigation_helper.php';

// Benutzer muss angemeldet sein
requireLogin();

// Aktuelles Jahr
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Feiertag hinzufügen
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $date = $_POST['date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        if (empty($date) || empty($description)) {
            $error = 'Datum och beskrivning krävs.';
        } else {
            if (addHoliday($date, $description)) {
                $success = true;
            } else {
                $error = 'Det gick inte att lägga till röd dag.';
            }
        }
    } elseif (isset($_POST['delete']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if (deleteHoliday($id)) {
            $success = true;
        } else {
            $error = 'Det gick inte att ta bort röd dag.';
        }
    }
}

// Feiertage für das Jahr abrufen
$holidays = getHolidaysForYear($year);

// Seitentitel
$pageTitle = 'Röda dagar ' . $year;
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
                    <li><a href="users.php">Användare</a></li>
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
                <?php echo renderYearNavigation($year, 'holidays.php'); ?>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">Ändringarna har sparats.</div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="admin-tools">
            <h3>Admin-verktyg</h3>
            <div class="admin-actions">
                <a href="generate_holidays.php" class="btn btn-secondary">Generera röda dagar för flera år</a>
            </div>
        </div>
        
        <div class="section">
            <h2>Lägg till röd dag</h2>
            <form method="post" action="" class="add-form">
                <div class="form-group">
                    <label for="date">Datum:</label>
                    <input type="date" id="date" name="date" required min="<?php echo $year; ?>-01-01" max="<?php echo $year; ?>-12-31">
                </div>
                
                <div class="form-group">
                    <label for="description">Beskrivning:</label>
                    <input type="text" id="description" name="description" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add" class="btn btn-success">Lägg till</button>
                </div>
            </form>
        </div>
        
        <div class="section">
            <h2>Röda dagar <?php echo $year; ?></h2>
            
            <?php if (count($holidays) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Beskrivning</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td><?php echo formatDateSwedish($holiday['date'], 'long'); ?></td>
                                <td><?php echo htmlspecialchars($holiday['name']); ?></td>
                                <td>
                                    <form method="post" action="" class="delete-form" onsubmit="return confirm('Är du säker på att du vill ta bort denna röda dag?');">
                                        <input type="hidden" name="id" value="<?php echo $holiday['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger">Ta bort</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Inga röda dagar har lagts till för <?php echo $year; ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 