<?php
/**
 * Mitarbeiter bearbeiten
 */

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/auth.php';
require_once 'includes/employees.php';

// Benutzer muss angemeldet sein
requireLogin();

// Mitarbeiter-ID aus der URL abrufen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Mitarbeiter abrufen
$employee = getEmployeeById($id);

// Wenn der Mitarbeiter nicht existiert, zur Mitarbeiterliste umleiten
if (!$employee) {
    header('Location: employees.php');
    exit;
}

$error = '';
$success = false;

// Formular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $personnummer = trim($_POST['personnummer'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $avtaladProcent = isset($_POST['avtalad_procent']) ? (float)$_POST['avtalad_procent'] : 100.0;
        
        if (empty($name)) {
            $error = 'Namn är obligatoriskt.';
        } else {
            if (updateEmployee($id, $name, $personnummer, $address, $avtaladProcent)) {
                $success = true;
                // Aktualisierte Daten abrufen
                $employee = getEmployeeById($id);
            } else {
                $error = 'Det gick inte att uppdatera medarbetaren.';
            }
        }
    } catch (Exception $e) {
        $error = 'Ett fel inträffade: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera medarbetare - Tidsrapportering</title>
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
            <h1>Redigera medarbetare</h1>
            <div class="page-actions">
                <a href="employees.php" class="btn">Tillbaka till medarbetare</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">Medarbetaren har uppdaterats.</div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="name">Namn:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="personnummer">Personnummer:</label>
                <input type="text" id="personnummer" name="personnummer" value="<?php echo htmlspecialchars($employee['personnummer'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Adress:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($employee['address'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="avtalad_procent">Avtalad %:</label>
                <input type="number" id="avtalad_procent" name="avtalad_procent" value="<?php echo htmlspecialchars($employee['avtalad_procent'] ?? 100.0); ?>" step="0.1" min="0" max="100">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Spara</button>
                <a href="employees.php" class="btn">Avbryt</a>
            </div>
        </form>
    </div>
</body>
</html> 