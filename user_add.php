<?php
/**
 * Neuen Benutzer hinzufügen
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

// Benutzer muss angemeldet sein
requireLogin();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validierung
    if (empty($username)) {
        $error = 'Användarnamn krävs.';
    } elseif (strlen($username) < 3) {
        $error = 'Användarnamnet måste vara minst 3 tecken långt.';
    } elseif (empty($password)) {
        $error = 'Lösenord krävs.';
    } elseif (strlen($password) < 6) {
        $error = 'Lösenordet måste vara minst 6 tecken långt.';
    } elseif ($password !== $password_confirm) {
        $error = 'Lösenorden matchar inte.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Überprüfen, ob Benutzername bereits existiert
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Användarnamnet finns redan.';
            } else {
                // Benutzer erstellen
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                
                if ($stmt->execute([$username, $hashedPassword])) {
                    $success = true;
                } else {
                    $error = 'Det gick inte att skapa användaren.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Ett databasfel uppstod: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lägg till användare - Tidsrapportering</title>
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
            <h1>Lägg till användare</h1>
            <div class="page-actions">
                <a href="users.php" class="btn">Tillbaka till användare</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">
                Användaren har skapats! <a href="users.php">Tillbaka till användarlistan</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Användarnamn: *</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           minlength="3">
                    <small>Minst 3 tecken</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Lösenord: *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Minst 6 tecken</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Bekräfta lösenord: *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                </div>
                
                <div class="form-group" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-success">Skapa användare</button>
                    <a href="users.php" class="btn" style="margin-left: 30px; background-color: #95a5a6;">Avbryt</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
