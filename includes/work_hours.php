<?php
/**
 * Funktionen für die Arbeitszeitverwaltung
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Prüft, ob ein Datum ein Feiertag ist
 * 
 * @param string $date Datum im Format YYYY-MM-DD
 * @return bool True, wenn es ein Feiertag ist, sonst False
 */
function isHoliday($date) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE date = ?");
    $stmt->execute([$date]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Holt alle Feiertage für ein bestimmtes Jahr
 * 
 * @param int $year Jahr
 * @return array Liste aller Feiertage
 */
function getHolidaysForYear($year) {
    $pdo = getDBConnection();
    
    $startDate = sprintf('%04d-01-01', $year);
    $endDate = sprintf('%04d-12-31', $year);
    
    $stmt = $pdo->prepare("SELECT * FROM holidays WHERE date BETWEEN ? AND ? ORDER BY date");
    $stmt->execute([$startDate, $endDate]);
    
    return $stmt->fetchAll();
}

/**
 * Fügt einen neuen Feiertag hinzu
 * 
 * @param string $date Datum im Format YYYY-MM-DD
 * @param string $description Beschreibung des Feiertags
 * @return bool True bei Erfolg, sonst False
 */
function addHoliday($date, $description) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("INSERT INTO holidays (date, description) VALUES (?, ?)");
    return $stmt->execute([$date, $description]);
}

/**
 * Löscht einen Feiertag
 * 
 * @param int $id ID des Feiertags
 * @return bool True bei Erfolg, sonst False
 */
function deleteHoliday($id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("DELETE FROM holidays WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Holt die Arbeitszeiten eines Mitarbeiters für einen bestimmten Monat
 * 
 * @param int $employeeId ID des Mitarbeiters
 * @param int $month Monat (1-12)
 * @param int $year Jahr
 * @return array Liste der Arbeitszeiten
 */
function getWorkHoursForMonth($employeeId, $month, $year) {
    $pdo = getDBConnection();
    
    // Startdatum und Enddatum des Monats
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $stmt = $pdo->prepare("
        SELECT * FROM work_hours 
        WHERE employee_id = ? AND date BETWEEN ? AND ? 
        ORDER BY date
    ");
    
    $stmt->execute([$employeeId, $startDate, $endDate]);
    return $stmt->fetchAll();
}

/**
 * Erstellt oder aktualisiert einen Arbeitszeitdatensatz
 * 
 * @param int $employeeId ID des Mitarbeiters
 * @param string $date Datum im Format YYYY-MM-DD
 * @param float $arbetstid Standardarbeitszeit
 * @param float $sjuk Krankheit
 * @param float $semester Urlaub
 * @param float $avtaladProcent Vereinbarte Arbeitszeit in Prozent
 * @param float $arbete Tatsächliche Arbeitszeit
 * @param float $distansarbete Fernarbeit
 * @return bool True bei Erfolg, sonst False
 */
function saveWorkHours($employeeId, $date, $arbetstid, $sjuk, $semester, $avtaladProcent, $arbete, $distansarbete) {
    $pdo = getDBConnection();
    
    // Prüfen, ob bereits ein Datensatz für diesen Mitarbeiter und dieses Datum existiert
    $stmt = $pdo->prepare("SELECT id FROM work_hours WHERE employee_id = ? AND date = ?");
    $stmt->execute([$employeeId, $date]);
    $existingRecord = $stmt->fetch();
    
    if ($existingRecord) {
        // Datensatz aktualisieren
        $stmt = $pdo->prepare("
            UPDATE work_hours 
            SET arbetstid = ?, sjuk = ?, semester = ?, avtalad_procent = ?, arbete = ?, distansarbete = ?
            WHERE id = ?
        ");
        return $stmt->execute([$arbetstid, $sjuk, $semester, $avtaladProcent, $arbete, $distansarbete, $existingRecord['id']]);
    } else {
        // Neuen Datensatz erstellen
        $stmt = $pdo->prepare("
            INSERT INTO work_hours 
            (employee_id, date, arbetstid, sjuk, semester, avtalad_procent, arbete, distansarbete)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$employeeId, $date, $arbetstid, $sjuk, $semester, $avtaladProcent, $arbete, $distansarbete]);
    }
}

/**
 * Initialisiert die Arbeitszeiten für einen Monat mit Standardwerten
 * 
 * @param int $employeeId ID des Mitarbeiters
 * @param int $month Monat (1-12)
 * @param int $year Jahr
 * @return bool True bei Erfolg, sonst False
 */
function initializeMonthWorkHours($employeeId, $month, $year) {
    $pdo = getDBConnection();
    
    // Standardwert für avtalad_procent
    $avtaladProcent = 100.0;
    
    // Versuchen, den Wert für avtalad_procent aus der Mitarbeitertabelle zu holen
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch();
        
        // Wenn der Mitarbeiter existiert und die Spalte avtalad_procent vorhanden ist
        if ($employee && isset($employee['avtalad_procent'])) {
            $avtaladProcent = $employee['avtalad_procent'];
        }
    } catch (PDOException $e) {
        // Fehler ignorieren, Standardwert verwenden
        error_log("Fehler beim Abrufen des avtalad_procent-Werts: " . $e->getMessage());
    }
    
    // Startdatum und Enddatum des Monats
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Alle Tage des Monats durchlaufen
    $currentDate = new DateTime($startDate);
    $lastDate = new DateTime($endDate);
    
    $success = true;
    
    while ($currentDate <= $lastDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $dayOfWeek = $currentDate->format('N'); // 1 (Montag) bis 7 (Sonntag)
        
        // Standardarbeitszeit basierend auf Wochentag und Feiertagen
        $arbetstid = 0;
        
        // Wenn es ein Wochentag (Mo-Fr) ist und kein Feiertag, dann 8 Stunden
        if ($dayOfWeek <= 5 && !isHoliday($dateStr)) {
            $arbetstid = 8.0;
        }
        
        // Prüfen, ob bereits ein Datensatz für diesen Tag existiert
        $stmt = $pdo->prepare("SELECT id FROM work_hours WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employeeId, $dateStr]);
        
        if ($stmt->rowCount() === 0) {
            // Neuen Datensatz mit Standardwerten erstellen
            $stmt = $pdo->prepare("
                INSERT INTO work_hours 
                (employee_id, date, arbetstid, sjuk, semester, avtalad_procent, arbete, distansarbete)
                VALUES (?, ?, ?, 0, 0, ?, 0, 0)
            ");
            
            $result = $stmt->execute([$employeeId, $dateStr, $arbetstid, $avtaladProcent]);
            if (!$result) {
                $success = false;
            }
        }
        
        // Zum nächsten Tag
        $currentDate->modify('+1 day');
    }
    
    return $success;
} 