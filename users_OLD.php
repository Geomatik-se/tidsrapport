<?php
/**
 * Benutzerverwaltung
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

// Benutzer muss angemeldet sein
requireLogin();

// Benutzer löschen
if (isset($_POST['delete']) && isset($_POST['id'])) {
    // Nur Admin darf Benutzer löschen
    if (!isAdmin()) {
        $errorMessage = 'Endast administratorer kan ta bort användare.';
    } else {
        $id = (int)$_POST['id'];
        
        // Verhindere das Löschen des eigenen Accounts
        if ($id !== $_SESSION['user_id']) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $successMessage = 'Användaren har tagits bort.';
            } catch (PDOException $e) {
                $errorMessage = 'Det gick inte att ta bort användaren.';
            }
        } else {
            $errorMessage = 'Du kan inte ta bort ditt eget konto.';
        }
    }
}

// Alle Benutzer abrufen
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $errorMessage = 'Kunde inte hämta användare.';
}

$isCurrentUserAdmin = isAdmin();
$currentUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Användare - Tidsrapportering</title>
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
            <h1>Användare</h1>
            <div class="page-actions">
                <a href="user_add.php" class="btn btn-success">+ Ny användare</a>
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
                    <th>Användarnamn</th>
                    <th>Skapad</th>
                    <th>Åtgärder</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="actions">
                                <?php if ($user['id'] == $currentUserId): ?>
                                    <span style="color: #95a5a6;">Nuvarande användare</span>
                                <?php elseif ($isCurrentUserAdmin): ?>
                                    <form method="post" action="" class="delete-form" onsubmit="return confirm('Är du säker på att du vill ta bort denna användare?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger">Ta bort</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #95a5a6;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Inga användare hittades.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
