<?php
/**
 * Login-Seite
 */

require_once 'includes/auth.php';

// Wenn der Benutzer bereits angemeldet ist, zur Startseite weiterleiten
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Anmeldeformular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Användarnamn och lösenord krävs';
    } else {
        if (login($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Ogiltigt användarnamn eller lösenord';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logga in - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>Tidsrapportering</h1>
            <h2>Logga in</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Användarnamn:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Lösenord:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit">Logga in</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 