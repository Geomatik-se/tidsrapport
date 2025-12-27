<?php
/**
 * Mitarbeiterverwaltung
 */

require_once 'includes/auth.php';
require_once 'includes/employees.php';

// Benutzer muss angemeldet sein
requireLogin();

// Mitarbeiter löschen
if (isset($_POST['delete']) && isset($_POST['id'])) {
    // Nur Admin darf Mitarbeiter löschen
    if (!isAdmin()) {
        $errorMessage = 'Endast administratorer kan ta bort medarbetare.';
    } else {
        $id = (int)$_POST['id'];
        if (deleteEmployee($id)) {
            $successMessage = 'Medarbetaren har tagits bort.';
        } else {
            $errorMessage = 'Det gick inte att ta bort medarbetaren.';
        }
    }
}

// Alle Mitarbeiter abrufen
$employees = getAllEmployees();
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medarbetare - Tidsrapportering</title>
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
            <h1>Medarbetare</h1>
            <div class="page-actions">
                <a href="employee_add.php" class="btn btn-success">+ Ny medarbetare</a>
                <a href="sql/update_employees_table.php" class="btn">Uppdatera databas</a>
            </div>
        </div>
        
        <?php if (isset($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Namn</th>
                    <th>Åtgärder</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($employees) > 0): ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['name']); ?></td>
                            <td class="actions">
                                <a href="employee_edit.php?id=<?php echo $employee['id']; ?>" class="btn">Redigera</a>
                                
                                <?php if (isAdmin()): ?>
                                <form method="post" action="" class="delete-form" onsubmit="return confirm('Är du säker på att du vill ta bort denna medarbetare?');">
                                    <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger">Ta bort</button>
                                </form>
                                <?php endif; ?>
                                
                                <a href="employee_month.php?id=<?php echo $employee['id']; ?>" class="btn">Visa tidsrapport</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Inga medarbetare hittades.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 