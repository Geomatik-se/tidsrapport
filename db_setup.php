<?php
// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Datenbank-Setup</h1>";

// Datenbankverbindungsdaten
$dbHost = '62.108.32.157';
$dbPort = '3306';
$dbName = 'aealubjt_arbetstid';
$dbUser = 'aealubjt_3';
$dbPass = 'xalslv004';

try {
    echo "<p>Verbindung zur Datenbank wird hergestellt...</p>";
    
    // Datenbankverbindung herstellen
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Datenbankverbindung erfolgreich hergestellt.</p>";
    
    // Users-Tabelle erstellen
    echo "<p>Users-Tabelle wird erstellt...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Users-Tabelle wurde erstellt.</p>";
    
    // Standardbenutzer erstellen
    echo "<p>Standardbenutzer wird erstellt...</p>";
    $username = 'olofb';
    $password = 'svansele57';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prüfen, ob der Benutzer bereits existiert
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userExists = $stmt->fetchColumn() > 0;
    
    if ($userExists) {
        // Benutzer aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "<p>Benutzer '$username' wurde aktualisiert.</p>";
    } else {
        // Benutzer einfügen
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "<p>Benutzer '$username' wurde erstellt.</p>";
    }
    
    // Employees-Tabelle erstellen
    echo "<p>Employees-Tabelle wird erstellt...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Employees-Tabelle wurde erstellt.</p>";
    
    // Holidays-Tabelle erstellen
    echo "<p>Holidays-Tabelle wird erstellt...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS holidays (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        description VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Holidays-Tabelle wurde erstellt.</p>";
    
    // Work_hours-Tabelle erstellen
    echo "<p>Work_hours-Tabelle wird erstellt...</p>";
    $sql = "
    CREATE TABLE IF NOT EXISTS work_hours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        date DATE NOT NULL,
        arbetstid DECIMAL(4,1) DEFAULT 6.0,
        sjuk DECIMAL(4,1) DEFAULT 0.0,
        semester DECIMAL(4,1) DEFAULT 0.0,
        avtalad_procent DECIMAL(5,1) DEFAULT 100.0,
        arbete DECIMAL(4,1) DEFAULT 0.0,
        distansarbete DECIMAL(4,1) DEFAULT 0.0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (employee_id, date),
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Work_hours-Tabelle wurde erstellt.</p>";
    
    // Tabellen überprüfen
    echo "<h2>Tabellen überprüfen</h2>";
    $tables = ['users', 'employees', 'holidays', 'work_hours'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabelle '$table' existiert.</p>";
        } else {
            echo "<p>❌ Tabelle '$table' existiert nicht!</p>";
        }
    }
    
    echo "<h2>Setup abgeschlossen</h2>";
    echo "<p>Sie können sich nun mit folgenden Zugangsdaten anmelden:</p>";
    echo "<ul>";
    echo "<li>Benutzername: olofb</li>";
    echo "<li>Passwort: svansele57</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Zur Anmeldeseite</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Fehler:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Detaillierte Fehlerinformationen anzeigen
    echo "<h3>Fehlerdetails:</h3>";
    echo "<pre>";
    print_r($e);
    echo "</pre>";
    
    echo "<h3>Mögliche Lösungen:</h3>";
    echo "<ul>";
    echo "<li>Überprüfen Sie, ob die Datenbankverbindungsdaten korrekt sind.</li>";
    echo "<li>Stellen Sie sicher, dass der Datenbankbenutzer die erforderlichen Rechte hat.</li>";
    echo "<li>Kontaktieren Sie Ihren Hosting-Anbieter für weitere Unterstützung.</li>";
    echo "</ul>";
} 