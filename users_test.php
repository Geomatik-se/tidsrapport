<?php
/**
 * Test-Benutzerverwaltung (vereinfacht)
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

// Alle Benutzer abrufen
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, username, created_at, is_admin FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    echo "Fehler: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Användare (TEST) - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">Tidsrapportering [TEST]</div>
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
                (Admin: <?php echo isAdmin() ? 'JA' : 'NEJ'; ?>)
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h1>Användare (TEST-VERSION)</h1>
            <div class="page-actions">
                <a href="user_add.php" class="btn btn-success">+ Ny användare</a>
            </div>
        </div>
        
        <p style="background: yellow; padding: 10px;">
            <strong>DEBUG:</strong> Anzahl Benutzer: <?php echo count($users); ?>
        </p>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Användarnamn</th>
                    <th>Skapad</th>
                    <th>Admin</th>
                    <th>Åtgärder</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                echo "<!-- Anzahl Benutzer: " . count($users) . " -->\n";
                if (count($users) > 0): 
                ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></td>
                            <td><?php echo $user['is_admin'] == 1 ? 'JA' : 'NEJ'; ?></td>
                            <td class="actions">
                                <?php if ($user['id'] != $_SESSION['user_id'] && isAdmin()): ?>
                                    <form method="post" action="users.php" class="delete-form">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger">Ta bort</button>
                                    </form>
                                <?php elseif ($user['id'] == $_SESSION['user_id']): ?>
                                    <span style="color: #95a5a6;">Nuvarande användare</span>
                                <?php else: ?>
                                    <span style="color: #95a5a6;">Nur Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="background: red; color: white;">Inga användare hittades!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <hr>
        <p><a href="users.php">Zur normalen users.php</a></p>
    </div>
</body>
</html>
