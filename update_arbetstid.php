<?php
/**
 * Skript zur Aktualisierung der Arbetstid-Werte
 * Setzt den Wert für Arbetstid für alle Tage von Montag bis Freitag auf 8
 */

require_once 'config/database.php';
require_once 'includes/work_hours.php';

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Aktualisierung der Arbetstid-Werte</h1>";

try {
    // Datenbankverbindung herstellen
    $pdo = getDBConnection();
    
    // Alle Arbeitszeiten abrufen
    $stmt = $pdo->query("SELECT id, date FROM work_hours");
    $workHours = $stmt->fetchAll();
    
    $updated = 0;
    $skipped = 0;
    
    foreach ($workHours as $workHour) {
        $date = new DateTime($workHour['date']);
        $dayOfWeek = $date->format('N'); // 1 (Montag) bis 7 (Sonntag)
        
        // Wenn es ein Wochentag (Mo-Fr) ist und kein Feiertag, dann 8 Stunden
        if ($dayOfWeek <= 5 && !isHoliday($workHour['date'])) {
            $stmt = $pdo->prepare("UPDATE work_hours SET arbetstid = 8.0 WHERE id = ?");
            $stmt->execute([$workHour['id']]);
            $updated++;
        } else {
            $skipped++;
        }
    }
    
    echo "<p>Aktualisierung abgeschlossen.</p>";
    echo "<p>$updated Einträge wurden aktualisiert (Arbetstid = 8.0 für Montag bis Freitag).</p>";
    echo "<p>$skipped Einträge wurden übersprungen (Wochenenden oder Feiertage).</p>";
    
} catch (PDOException $e) {
    echo "<p>Fehler bei der Aktualisierung: " . $e->getMessage() . "</p>";
}

// Weiterleitung zur Startseite nach 5 Sekunden
header("refresh:5;url=index.php");
echo "<p>Sie werden in 5 Sekunden zur Startseite weitergeleitet...</p>";
?> 