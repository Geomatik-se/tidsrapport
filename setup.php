<?php
// Minimale Installation ohne Abhängigkeiten
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup</title></head><body>";
echo "<h1>Datenbank Setup</h1>";

// Direkte DB-Verbindung
$host = '62.108.32.157';
$port = '3306';
$dbname = 'aealubjt_2026';
$user = 'aealubjt_2026';
$pass = 'Morot1234';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Datenbankverbindung erfolgreich!</p>";
    
    // Tabellen erstellen
    echo "<h2>Tabellen erstellen...</h2>";
    
    // Users Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>✓ Tabelle 'users' erstellt</p>";
    
    // Employees Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        personnummer VARCHAR(13),
        address TEXT,
        avtalad_procent DECIMAL(5,2) DEFAULT 100.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>✓ Tabelle 'employees' erstellt</p>";
    
    // Holidays Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS holidays (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        name VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        UNIQUE KEY unique_holiday (date, name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>✓ Tabelle 'holidays' erstellt</p>";
    
    // Work Hours Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS work_hours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        date DATE NOT NULL,
        hours DECIMAL(5,2) DEFAULT 0,
        year INT NOT NULL,
        month INT NOT NULL,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        UNIQUE KEY unique_entry (employee_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>✓ Tabelle 'work_hours' erstellt</p>";
    
    // Benutzer prüfen/erstellen
    echo "<h2>Benutzer erstellen...</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['olofb']);
    
    if ($stmt->fetchColumn() == 0) {
        // Benutzer anlegen
        $hashedPassword = password_hash('svansele57', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['olofb', $hashedPassword]);
        echo "<p style='color: green;'>✓ Benutzer 'olofb' erstellt</p>";
        echo "<p>Passwort: svansele57</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Benutzer 'olofb' existiert bereits</p>";
        
        // Passwort aktualisieren
        if (isset($_POST['update_password'])) {
            $hashedPassword = password_hash('svansele57', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hashedPassword, 'olofb']);
            echo "<p style='color: green;'>✓ Passwort aktualisiert!</p>";
        } else {
            echo "<form method='post'>";
            echo "<button type='submit' name='update_password' style='padding: 10px; background: #3498db; color: white; border: none; cursor: pointer;'>Passwort zurücksetzen</button>";
            echo "</form>";
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Installation erfolgreich!</h2>";
    echo "<p><a href='login.php' style='padding: 10px; background: #27ae60; color: white; text-decoration: none; display: inline-block; margin: 5px;'>Zum Login →</a></p>";
    echo "<p><strong>Zugangsdaten:</strong><br>Benutzername: olofb<br>Passwort: svansele57</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
