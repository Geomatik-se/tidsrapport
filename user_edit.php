<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

$error = '';
$success = '';

// Benutzer-ID aus URL
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = (int)$_GET['id'];

// Nur Admin darf andere Benutzer bearbeiten, normale Benutzer nur sich selbst
if (!isAdmin() && $id !== $_SESSION['user_id']) {
    header('Location: users.php');
    exit;
}

$pdo = getDBConnection();

// Benutzer laden
$stmt = $pdo->prepare("SELECT id, username, is_admin FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit;
}

// Formular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $is_admin_value = isset($_POST['is_admin']) ? 1 : 0;
    
    // Validierung
    if (empty($username)) {
        $error = 'Användarnamn får inte vara tomt.';
    } elseif (strlen($username) < 3) {
        $error = 'Användarnamn måste vara minst 3 tecken.';
    } else {
        // Prüfen ob Username schon existiert (außer für aktuellen Benutzer)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            $error = 'Användarnamnet finns redan.';
        } elseif (!empty($new_password)) {
            // Wenn neues Passwort gesetzt wird, validieren
            if (strlen($new_password) < 6) {
                $error = 'Lösenordet måste vara minst 6 tecken.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Lösenorden matchar inte.';
            }
        }
    }
    
    if (empty($error)) {
        try {
            if (!empty($new_password)) {
                // Mit neuem Passwort
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, is_admin = ? WHERE id = ?");
                $stmt->execute([$username, password_hash($new_password, PASSWORD_DEFAULT), $is_admin_value, $id]);
            } else {
                // Ohne Passwortänderung
                $stmt = $pdo->prepare("UPDATE users SET username = ?, is_admin = ? WHERE id = ?");
                $stmt->execute([$username, $is_admin_value, $id]);
            }
            
            $success = 'Användaren har uppdaterats.';
            
            // Benutzer neu laden
            $stmt = $pdo->prepare("SELECT id, username, is_admin FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = 'Fel vid uppdatering: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera användare - Tidsrapportering</title>
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
        <h1>Redigera användare</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Användarnamn:</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nytt lösenord:</label>
                <input type="password" id="new_password" name="new_password" class="form-control">
                <small class="form-text">Lämna tomt för att behålla nuvarande lösenord. Minst 6 tecken om du ändrar.</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Bekräfta nytt lösenord:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_admin" value="1" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                    Administratör
                </label>
            </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Spara</button>
                <a href="users.php" class="btn btn-secondary" style="margin-left: 30px; margin-top: 10px;">Avbryt</a>
            </div>
        </form>
    </div>
</body>
</html>
