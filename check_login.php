<?php
// Direkter Check ob Benutzer existiert
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DB Check</title></head><body>";
echo "<h1>Datenbank-Check</h1>";

$host = '62.108.32.157';
$port = '3306';
$dbname = 'aealubjt_2026';
$user = 'aealubjt_2026';
$pass = 'Morot1234';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Verbindung OK</p>";
    
    // Prüfe Tabellen
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Vorhandene Tabellen:</h2><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Prüfe users Tabelle
    if (in_array('users', $tables)) {
        echo "<h2>Benutzer in der Datenbank:</h2>";
        $stmt = $pdo->query("SELECT id, username, created_at FROM users");
        $users = $stmt->fetchAll();
        
        if (empty($users)) {
            echo "<p style='color: red;'>⚠ KEINE BENUTZER GEFUNDEN!</p>";
            echo "<p><a href='setup.php' style='padding: 10px; background: #e74c3c; color: white; text-decoration: none; display: inline-block;'>Setup ausführen →</a></p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th style='padding: 5px;'>ID</th><th style='padding: 5px;'>Username</th><th style='padding: 5px;'>Erstellt am</th></tr>";
            foreach ($users as $u) {
                echo "<tr><td style='padding: 5px;'>{$u['id']}</td><td style='padding: 5px;'>{$u['username']}</td><td style='padding: 5px;'>{$u['created_at']}</td></tr>";
            }
            echo "</table>";
            
            // Test-Login
            echo "<h2>Login-Test</h2>";
            echo "<form method='post'>";
            echo "<label>Username: <input type='text' name='test_user' value='olofb'></label><br>";
            echo "<label>Password: <input type='text' name='test_pass' value='svansele57'></label><br>";
            echo "<button type='submit' name='test_login'>Login testen</button>";
            echo "</form>";
            
            if (isset($_POST['test_login'])) {
                $testUser = $_POST['test_user'];
                $testPass = $_POST['test_pass'];
                
                $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
                $stmt->execute([$testUser]);
                $user = $stmt->fetch();
                
                if ($user) {
                    echo "<p>Benutzer gefunden: ID {$user['id']}</p>";
                    echo "<p>Gespeicherter Hash: " . substr($user['password'], 0, 20) . "...</p>";
                    
                    if (password_verify($testPass, $user['password'])) {
                        echo "<p style='color: green; font-weight: bold;'>✓ PASSWORT KORREKT!</p>";
                        echo "<p>Login sollte funktionieren!</p>";
                    } else {
                        echo "<p style='color: red; font-weight: bold;'>✗ PASSWORT FALSCH!</p>";
                        echo "<p>Das Passwort in der Datenbank stimmt nicht mit 'svansele57' überein.</p>";
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='reset_user' value='" . htmlspecialchars($testUser) . "'>";
                        echo "<button type='submit' name='do_reset'>Passwort zurücksetzen</button>";
                        echo "</form>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ Benutzer '$testUser' nicht gefunden!</p>";
                }
            }
            
            if (isset($_POST['do_reset'])) {
                $resetUser = $_POST['reset_user'];
                $newHash = password_hash('svansele57', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
                $stmt->execute([$newHash, $resetUser]);
                echo "<p style='color: green;'>✓ Passwort für '$resetUser' wurde zurückgesetzt auf: svansele57</p>";
                echo "<p><a href='login.php'>Jetzt zum Login →</a></p>";
            }
        }
    } else {
        echo "<p style='color: red;'>⚠ Tabelle 'users' existiert nicht!</p>";
        echo "<p><a href='setup.php' style='padding: 10px; background: #e74c3c; color: white; text-decoration: none; display: inline-block;'>Setup ausführen →</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
