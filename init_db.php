<?php
/**
 * Einfaches Skript zur Initialisierung der Datenbank
 */

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

echo "<h1>Datenbank-Initialisierung</h1>";

try {
    // Datenbankverbindung herstellen
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>Datenbankverbindung erfolgreich hergestellt.</p>";
    
    // Tabellen erstellen
    $sql = "
    -- Benutzer-Tabelle
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

    -- Mitarbeiter-Tabelle
    CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

    -- Feiertage-Tabelle
    CREATE TABLE IF NOT EXISTS holidays (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        description VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

    -- Arbeitszeit-Tabelle
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
    
    // SQL-Befehle ausführen
    $pdo->exec($sql);
    
    echo "<p style='color: green;'>Tabellen wurden erfolgreich erstellt.</p>";
    
    // Standardbenutzer erstellen
    $username = 'olofb';
    $password = 'svansele57';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prüfen, ob der Benutzer bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        // Benutzer aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "<p style='color: green;'>Benutzer '$username' wurde aktualisiert.</p>";
    } else {
        // Benutzer einfügen
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "<p style='color: green;'>Benutzer '$username' wurde erstellt.</p>";
    }
    
    // Tabellen überprüfen
    $tables = ['users', 'employees', 'holidays', 'work_hours'];
    
    echo "<h2>Tabellen überprüfen</h2>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<li style='color: green;'>Tabelle '$table' existiert.</li>";
            
            // Anzahl der Datensätze ausgeben
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<ul><li>Anzahl der Datensätze: $count</li></ul>";
        } else {
            echo "<li style='color: red;'>Tabelle '$table' existiert nicht!</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<h2>Datenbank-Initialisierung abgeschlossen</h2>";
    echo "<p>Sie können sich nun mit folgenden Zugangsdaten anmelden:</p>";
    echo "<ul>";
    echo "<li>Benutzername: olofb</li>";
    echo "<li>Passwort: svansele57</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Zur Anmeldeseite</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Fehler: " . $e->getMessage() . "</p>";
    
    echo "<h2>Mögliche Lösungen</h2>";
    echo "<ul>";
    echo "<li>Überprüfen Sie die Datenbankverbindungsdaten.</li>";
    echo "<li>Stellen Sie sicher, dass der Datenbankserver läuft und erreichbar ist.</li>";
    echo "<li>Überprüfen Sie, ob der Datenbankbenutzer die notwendigen Rechte hat.</li>";
    echo "</ul>";
} 