<?php
// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Datenbankverbindungsdaten
$dbHost = '62.108.32.157';
$dbPort = '3306';
$dbName = 'aealubjt_arbetstid';
$dbUser = 'aealubjt_3';
$dbPass = 'xalslv004';

echo "<h1>Tabellen erstellen</h1>";

try {
    // Datenbankverbindung herstellen
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Datenbankverbindung erfolgreich hergestellt.</p>";
    
    // Users-Tabelle erstellen
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Users-Tabelle erstellt.</p>";
    
    // Standardbenutzer erstellen
    $username = 'olofb';
    $password = 'svansele57';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Benutzer einfügen
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";
    $pdo->exec($sql);
    echo "<p>Benutzer '$username' erstellt.</p>";
    
    // Employees-Tabelle erstellen
    $sql = "
    CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Employees-Tabelle erstellt.</p>";
    
    // Holidays-Tabelle erstellen
    $sql = "
    CREATE TABLE IF NOT EXISTS holidays (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        description VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>Holidays-Tabelle erstellt.</p>";
    
    // Work_hours-Tabelle erstellen
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
    echo "<p>Work_hours-Tabelle erstellt.</p>";
    
    echo "<h2>Alle Tabellen wurden erfolgreich erstellt!</h2>";
    echo "<p>Sie können sich nun mit folgenden Zugangsdaten anmelden:</p>";
    echo "<ul>";
    echo "<li>Benutzername: olofb</li>";
    echo "<li>Passwort: svansele57</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Zur Anmeldeseite</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Fehler:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Detaillierte Fehlerinformationen anzeigen
    echo "<h3>Fehlerdetails:</h3>";
    echo "<pre>";
    print_r($e);
    echo "</pre>";
} 