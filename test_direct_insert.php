<?php
// Direkter Test ohne Includes
require_once 'config/database.php';

echo "<h2>Direkter Test: Feiertag in Datenbank einfügen</h2>";

try {
    $pdo = getDBConnection();
    
    $date = "2025-06-06";
    $name = "Test Sveriges nationaldag";
    $year = 2025;
    
    echo "<p>Versuche einzufügen:</p>";
    echo "<ul>";
    echo "<li>Datum: $date</li>";
    echo "<li>Name: $name</li>";
    echo "<li>Jahr: $year</li>";
    echo "</ul>";
    
    // Direkt SQL
    $stmt = $pdo->prepare("INSERT INTO holidays (date, name, year) VALUES (?, ?, ?)");
    $result = $stmt->execute([$date, $name, $year]);
    
    if ($result) {
        echo "<p style='color:green; font-weight:bold'>✓ Erfolgreich direkt eingefügt!</p>";
        
        // Prüfen
        $stmt = $pdo->prepare("SELECT * FROM holidays WHERE date = ?");
        $stmt->execute([$date]);
        $holiday = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Eingefügter Datensatz:</p>";
        echo "<pre>";
        print_r($holiday);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>✗ Fehler beim Einfügen</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'><strong>Fehler:</strong> " . $e->getMessage() . "</p>";
    echo "<p>SQL State: " . $e->getCode() . "</p>";
}

echo "<hr>";
echo "<h3>Jetzt mit includes/work_hours.php:</h3>";

require_once 'includes/work_hours.php';

// Zeige den Quellcode der addHoliday Funktion
$file = file_get_contents('includes/work_hours.php');
preg_match('/function addHoliday.*?\n\}/s', $file, $matches);

echo "<p>Code der addHoliday-Funktion:</p>";
echo "<pre style='background:#f0f0f0; padding:10px; border:1px solid #ccc;'>";
echo htmlspecialchars($matches[0] ?? 'Funktion nicht gefunden');
echo "</pre>";

try {
    $result2 = addHoliday("2025-12-25", "Juldagen via Function");
    if ($result2) {
        echo "<p style='color:green'>✓ addHoliday() funktioniert!</p>";
    } else {
        echo "<p style='color:red'>✗ addHoliday() fehlgeschlagen</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>addHoliday() Fehler: " . $e->getMessage() . "</p>";
}
