<?php
require_once 'config/database.php';
require_once 'includes/work_hours.php';

echo "<h2>Test: Feiertag hinzufügen</h2>";

// Test-Daten
$date = "2025-01-01";
$description = "Test-Feiertag";

echo "<p>Versuche Feiertag hinzuzufügen:</p>";
echo "<p>Datum: $date</p>";
echo "<p>Beschreibung: $description</p>";

try {
    $result = addHoliday($date, $description);
    
    if ($result) {
        echo "<p style='color:green'>✓ Erfolgreich hinzugefügt!</p>";
    } else {
        echo "<p style='color:red'>✗ Fehler beim Hinzufügen</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Fehler: " . $e->getMessage() . "</p>";
}

// Zeige alle Feiertage für 2025
echo "<hr>";
echo "<h3>Alle Feiertage für 2025:</h3>";
$holidays = getHolidaysForYear(2025);
echo "<pre>";
print_r($holidays);
echo "</pre>";
