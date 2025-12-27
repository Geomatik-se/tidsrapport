<?php
/**
 * Skript zur Aktualisierung der Mitarbeiter-Tabelle
 * Fügt die Felder "personnummer", "address" und "avtalad_procent" hinzu
 */

require_once __DIR__ . '/../config/database.php';

// HTML-Header ausgeben
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktualisierung der Mitarbeiter-Tabelle</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .update-status {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .update-status p {
            margin: 10px 0;
            padding: 10px;
            border-left: 4px solid #3498db;
            background-color: #ecf0f1;
        }
        .update-status .success {
            border-left-color: #27ae60;
            background-color: #d5f4e6;
        }
        .update-status .info {
            border-left-color: #3498db;
            background-color: #ebf5fb;
        }
        .update-status .error {
            border-left-color: #e74c3c;
            background-color: #fadbd8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-status">
<?php
try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    
    echo "<h1>Aktualisierung der Mitarbeiter-Tabelle</h1>";
    
    // Überprüfen, ob die Spalten bereits existieren
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'personnummer'");
    $personnummerExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'address'");
    $addressExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'avtalad_procent'");
    $avtaladProcentExists = $stmt->rowCount() > 0;
    
    // Spalten hinzufügen, falls sie noch nicht existieren
    if (!$personnummerExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN personnummer VARCHAR(20) DEFAULT NULL AFTER name");
        echo "<p class='success'>Spalte 'personnummer' wurde hinzugefügt.</p>";
    } else {
        echo "<p class='info'>Spalte 'personnummer' existiert bereits.</p>";
    }
    
    if (!$addressExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER personnummer");
        echo "<p class='success'>Spalte 'address' wurde hinzugefügt.</p>";
    } else {
        echo "<p class='info'>Spalte 'address' existiert bereits.</p>";
    }
    
    if (!$avtaladProcentExists) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN avtalad_procent DECIMAL(5,1) DEFAULT 100.0 AFTER address");
        echo "<p class='success'>Spalte 'avtalad_procent' wurde hinzugefügt.</p>";
    } else {
        echo "<p class='info'>Spalte 'avtalad_procent' existiert bereits.</p>";
    }
    
    echo "<h2>Aktualisierung abgeschlossen</h2>";
    echo "<p class='success'>Die Mitarbeiter-Tabelle wurde erfolgreich aktualisiert.</p>";
    echo "<p><a href='../employees.php' class='btn btn-primary'>Zurück zur Mitarbeiterliste</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Fehler:</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}
?>
        </div>
    </div>
</body>
</html> 