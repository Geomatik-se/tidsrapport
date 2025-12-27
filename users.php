<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

// Benutzer löschen
if (isset($_POST['delete']) && isset($_POST['id']) && isAdmin()) {
    $id = (int)$_POST['id'];
    
    if ($id !== $_SESSION['user_id']) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: users.php');
        exit;
    }
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        <table>
            <thead>
                <tr>
                    <th>Användarnamn</th>
                    <th>Skapad</th>
                    <th>Åtgärder</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td class="actions">
                            <?php 
                            $isCurrentUser = ($user['id'] == $_SESSION['user_id']);
                            $userIsAdmin = isAdmin();
                            ?>
                            
                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary" style="margin-right: 10px;">Redigera</a>
                            
                            <?php 
                            if ($isCurrentUser) {
                                echo '<span style="color: #95a5a6;">Nuvarande användare</span>';
                            } elseif ($userIsAdmin) {
                                echo '<form method="post" action="" class="delete-form" style="display:inline;" onsubmit="return confirm(\'Är du säker?\');">';
                                echo '<input type="hidden" name="id" value="' . $user['id'] . '">';
                                echo '<button type="submit" name="delete" class="btn btn-danger">Ta bort</button>';
                                echo '</form>';
                            } else {
                                echo '<span style="color: #95a5a6;">-</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
