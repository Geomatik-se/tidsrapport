<?php
/**
 * Installationsdatei für die Zeiterfassungssoftware
 * Diese Datei kann über den Browser aufgerufen werden, um die Datenbank zu initialisieren
 */

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Überprüfen, ob die Installation bereits durchgeführt wurde
$installLockFile = __DIR__ . '/install.lock';
if (file_exists($installLockFile) && !isset($_POST['force_install'])) {
    echo '<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation bereits durchgeführt</h1>
        <div class="warning">
            Die Installation wurde bereits durchgeführt. Aus Sicherheitsgründen wurde diese Datei gesperrt.
            Wenn Sie die Installation erneut durchführen möchten, können Sie dies erzwingen, aber alle vorhandenen Daten könnten verloren gehen.
        </div>
        <form method="post" onsubmit="return confirm(\'Sind Sie sicher, dass Sie die Installation erneut durchführen möchten? Alle vorhandenen Daten könnten verloren gehen.\');">
            <button type="submit" name="force_install" class="btn btn-danger">Installation erzwingen</button>
            <a href="login.php" class="btn">Zur Anmeldeseite</a>
            <a href="test_db.php" class="btn">Datenbank testen</a>
        </form>
    </div>
</body>
</html>';
    exit;
}

// Prüfen, ob die Datenbankverbindungsdatei existiert
if (!file_exists(__DIR__ . '/config/database.php')) {
    die('Die Datei "config/database.php" wurde nicht gefunden. Bitte stellen Sie sicher, dass alle Dateien korrekt hochgeladen wurden.');
}

// Datenbankverbindungsdatei einbinden
require_once __DIR__ . '/config/database.php';

// HTML-Header
echo '<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Tidsrapportering</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .step {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #3498db;
            background-color: #f9f9f9;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation der Zeiterfassungssoftware</h1>';

// Funktion zum Ausgeben von Schritten
function outputStep($title, $content, $status = '') {
    echo '<div class="step">';
    echo '<h3>' . $title . ' ' . ($status ? '<span class="' . $status . '">' . $status . '</span>' : '') . '</h3>';
    echo '<div>' . $content . '</div>';
    echo '</div>';
}

// Schritt 1: Datenbankverbindung prüfen
outputStep('Schritt 1: Datenbankverbindung prüfen', 'Verbindung zur Datenbank wird hergestellt...', '');

try {
    $pdo = getDBConnection();
    outputStep('Datenbankverbindung', 'Verbindung zur Datenbank erfolgreich hergestellt.', 'success');
} catch (PDOException $e) {
    outputStep('Datenbankverbindung', 'Fehler bei der Verbindung zur Datenbank: ' . $e->getMessage(), 'error');
    echo '<p>Bitte überprüfen Sie die Datenbankverbindungsdaten in der Datei "config/database.php" und versuchen Sie es erneut.</p>';
    echo '<p><a href="install.php" class="btn">Erneut versuchen</a></p>';
    echo '</div></body></html>';
    exit;
}

// Schritt 2: Datenbankstruktur erstellen
outputStep('Schritt 2: Datenbankstruktur erstellen', 'Tabellen werden erstellt...', '');

try {
    // SQL-Datei einlesen
    $sqlFile = __DIR__ . '/sql/database_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('Die Datei "sql/database_schema.sql" wurde nicht gefunden.');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // SQL-Befehle ausführen
    $pdo->exec($sql);
    
    // Überprüfen, ob die Tabellen erstellt wurden
    $tables = ['users', 'employees', 'holidays', 'work_hours'];
    $allTablesExist = true;
    $missingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $allTablesExist = false;
            $missingTables[] = $table;
        }
    }
    
    if ($allTablesExist) {
        outputStep('Datenbankstruktur', 'Datenbankstruktur wurde erfolgreich erstellt.', 'success');
    } else {
        outputStep('Datenbankstruktur', 'Einige Tabellen konnten nicht erstellt werden: ' . implode(', ', $missingTables), 'warning');
    }
} catch (Exception $e) {
    outputStep('Datenbankstruktur', 'Fehler beim Erstellen der Datenbankstruktur: ' . $e->getMessage(), 'error');
    echo '<p>Bitte überprüfen Sie die SQL-Datei und versuchen Sie es erneut.</p>';
    echo '<p><a href="install.php" class="btn">Erneut versuchen</a></p>';
    echo '</div></body></html>';
    exit;
}

// Schritt 3: Standardbenutzer erstellen
outputStep('Schritt 3: Standardbenutzer erstellen', 'Benutzer "olofb" wird erstellt...', '');

try {
    // Passwort für den Standardbenutzer hashen
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
        outputStep('Standardbenutzer', 'Benutzer "' . $username . '" wurde aktualisiert.', 'success');
    } else {
        // Benutzer einfügen
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        outputStep('Standardbenutzer', 'Benutzer "' . $username . '" wurde erstellt.', 'success');
    }
} catch (Exception $e) {
    outputStep('Standardbenutzer', 'Fehler beim Erstellen des Standardbenutzers: ' . $e->getMessage(), 'error');
    echo '<p>Bitte versuchen Sie es erneut oder erstellen Sie den Benutzer manuell.</p>';
    echo '<p><a href="install.php" class="btn">Erneut versuchen</a></p>';
    echo '</div></body></html>';
    exit;
}

// Schritt 4: Installation abschließen
outputStep('Schritt 4: Installation abschließen', 'Die Installation wird abgeschlossen...', '');

try {
    // Installationsdatei sperren
    file_put_contents($installLockFile, date('Y-m-d H:i:s'));
    
    outputStep('Installation abgeschlossen', '
        <p class="success">Die Installation wurde erfolgreich abgeschlossen!</p>
        <p>Sie können sich nun mit folgenden Zugangsdaten anmelden:</p>
        <ul>
            <li>Benutzername: olofb</li>
            <li>Passwort: svansele57</li>
        </ul>
        <p><a href="login.php" class="btn">Zur Anmeldeseite</a></p>
    ', 'success');
} catch (Exception $e) {
    outputStep('Installation abschließen', 'Fehler beim Abschließen der Installation: ' . $e->getMessage(), 'error');
    echo '<p>Die Installation wurde durchgeführt, aber die Sperrdatei konnte nicht erstellt werden. Bitte erstellen Sie die Datei "install.lock" manuell, um zu verhindern, dass die Installation erneut durchgeführt wird.</p>';
    echo '<p><a href="login.php" class="btn">Zur Anmeldeseite</a></p>';
}

// HTML-Footer
echo '</div></body></html>'; 