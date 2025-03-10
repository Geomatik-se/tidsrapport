<?php
/**
 * Löschskript
 * Dieses Skript löscht alle unnötigen Debug- und Installationsdateien
 */

// Liste der zu löschenden Dateien
$filesToDelete = [
    'debug.php',
    'test_db.php',
    'init_db.php',
    'create_tables.php',
    'db_setup.php',
    'install.php',
    'cleanup.php',
    'INSTALLATION.md'
];

// Dateien löschen
$deletedFiles = [];
$failedFiles = [];

foreach ($filesToDelete as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $deletedFiles[] = $file;
        } else {
            $failedFiles[] = $file;
        }
    }
}

// Ergebnis anzeigen
echo "<h1>Löschvorgang abgeschlossen</h1>";

if (!empty($deletedFiles)) {
    echo "<h2>Folgende Dateien wurden gelöscht:</h2>";
    echo "<ul>";
    foreach ($deletedFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

if (!empty($failedFiles)) {
    echo "<h2>Folgende Dateien konnten nicht gelöscht werden:</h2>";
    echo "<ul>";
    foreach ($failedFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    echo "<p>Bitte löschen Sie diese Dateien manuell.</p>";
}

// Selbstzerstörung
echo "<p>Dieses Skript wird sich selbst löschen, nachdem Sie diese Seite verlassen haben.</p>";
echo "<p>Die Anwendung ist jetzt bereit für den produktiven Einsatz.</p>";
echo "<p><a href='index.php'>Zur Startseite</a></p>";

// Skript zur Selbstzerstörung registrieren
register_shutdown_function(function() {
    @unlink(__FILE__);
}); 