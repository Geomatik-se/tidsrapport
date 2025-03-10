<?php
/**
 * Authentifizierungsfunktionen
 */

session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Überprüft, ob ein Benutzer angemeldet ist
 * 
 * @return bool True, wenn der Benutzer angemeldet ist, sonst False
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Leitet zur Anmeldeseite weiter, wenn der Benutzer nicht angemeldet ist
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Versucht, einen Benutzer anzumelden
 * 
 * @param string $username Benutzername
 * @param string $password Passwort
 * @return bool True bei erfolgreicher Anmeldung, sonst False
 */
function login($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        
        if (password_verify($password, $user['password'])) {
            // Anmeldung erfolgreich
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            return true;
        }
    }
    
    return false;
}

/**
 * Meldet den aktuellen Benutzer ab
 */
function logout() {
    // Session-Variablen löschen
    $_SESSION = array();
    
    // Session-Cookie löschen
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Session zerstören
    session_destroy();
} 