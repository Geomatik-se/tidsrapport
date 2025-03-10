<?php
/**
 * Aufräumskript
 * Dieses Skript verschiebt Debug- und Installationsdateien in ein Unterverzeichnis
 */

// Liste der zu verschiebenden Dateien
$filesToMove = [
    'debug.php',
    'test_db.php',
    'init_db.php',
    'create_tables.php',
    'db_setup.php',
    'install.php'
];

// Zielverzeichnis erstellen, falls es nicht existiert
$targetDir = __DIR__ . '/setup';
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        die("Fehler: Konnte Verzeichnis '$targetDir' nicht erstellen.");
    }
    echo "Verzeichnis 'setup' wurde erstellt.<br>";
}

// Dateien verschieben
$movedFiles = [];
$failedFiles = [];

foreach ($filesToMove as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $targetPath = $targetDir . '/' . $file;
    
    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $targetPath)) {
            $movedFiles[] = $file;
        } else {
            $failedFiles[] = $file;
        }
    }
}

// Ergebnis anzeigen
echo "<h1>Aufräumen abgeschlossen</h1>";

if (!empty($movedFiles)) {
    echo "<h2>Folgende Dateien wurden in das Verzeichnis 'setup' verschoben:</h2>";
    echo "<ul>";
    foreach ($movedFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

if (!empty($failedFiles)) {
    echo "<h2>Folgende Dateien konnten nicht verschoben werden:</h2>";
    echo "<ul>";
    foreach ($failedFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    echo "<p>Bitte verschieben Sie diese Dateien manuell oder löschen Sie sie.</p>";
}

// .htaccess-Datei im setup-Verzeichnis erstellen, um den Zugriff zu verhindern
$htaccessContent = "# Zugriff auf dieses Verzeichnis verhindern\nDeny from all\n";
$htaccessPath = $targetDir . '/.htaccess';

if (file_put_contents($htaccessPath, $htaccessContent)) {
    echo "<p>Eine .htaccess-Datei wurde im 'setup'-Verzeichnis erstellt, um den Zugriff zu verhindern.</p>";
} else {
    echo "<p>Warnung: Konnte keine .htaccess-Datei im 'setup'-Verzeichnis erstellen.</p>";
}

echo "<p>Die Anwendung ist jetzt bereit für den produktiven Einsatz.</p>";
echo "<p><a href='index.php'>Zur Startseite</a></p>"; 