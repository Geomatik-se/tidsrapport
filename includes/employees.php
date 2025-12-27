<?php
/**
 * Funktionen für die Mitarbeiterverwaltung
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Holt alle Mitarbeiter aus der Datenbank
 * 
 * @return array Liste aller Mitarbeiter
 */
function getAllEmployees() {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Holt einen Mitarbeiter anhand seiner ID
 * 
 * @param int $id ID des Mitarbeiters
 * @return array|false Mitarbeiterdaten oder false, wenn nicht gefunden
 */
function getEmployeeById($id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    
    $employee = $stmt->fetch();
    
    // Wenn der Mitarbeiter nicht gefunden wurde, false zurückgeben
    if (!$employee) {
        return false;
    }
    
    // Sicherstellen, dass alle erforderlichen Felder vorhanden sind
    if (!isset($employee['personnummer'])) {
        $employee['personnummer'] = null;
    }
    
    if (!isset($employee['address'])) {
        $employee['address'] = null;
    }
    
    if (!isset($employee['avtalad_procent'])) {
        $employee['avtalad_procent'] = 100.0;
    }
    
    return $employee;
}

/**
 * Erstellt einen neuen Mitarbeiter
 * 
 * @param string $name Name des Mitarbeiters
 * @param string $personnummer Personennummer des Mitarbeiters (optional)
 * @param string $address Adresse des Mitarbeiters (optional)
 * @param float $avtaladProcent Vereinbarter Prozentsatz (optional, Standard: 100.0)
 * @return int|false ID des neuen Mitarbeiters oder false bei Fehler
 */
function createEmployee($name, $personnummer = null, $address = null, $avtaladProcent = 100.0) {
    $pdo = getDBConnection();
    
    // Überprüfen, ob die neuen Spalten existieren
    $columnsExist = true;
    
    try {
        $stmt = $pdo->query("SELECT personnummer, address, avtalad_procent FROM employees LIMIT 1");
        $stmt->fetch();
    } catch (PDOException $e) {
        $columnsExist = false;
    }
    
    // Wenn die Spalten nicht existieren, nur den Namen einfügen
    if (!$columnsExist) {
        $stmt = $pdo->prepare("INSERT INTO employees (name) VALUES (?)");
        $result = $stmt->execute([$name]);
    } else {
        // Wenn die Spalten existieren, alle Felder einfügen
        $stmt = $pdo->prepare("INSERT INTO employees (name, personnummer, address, avtalad_procent) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$name, $personnummer, $address, $avtaladProcent]);
    }
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

/**
 * Aktualisiert einen Mitarbeiter
 * 
 * @param int $id ID des Mitarbeiters
 * @param string $name Neuer Name des Mitarbeiters
 * @param string $personnummer Neue Personennummer des Mitarbeiters (optional)
 * @param string $address Neue Adresse des Mitarbeiters (optional)
 * @param float $avtaladProcent Neuer vereinbarter Prozentsatz (optional)
 * @return bool True bei Erfolg, sonst False
 */
function updateEmployee($id, $name, $personnummer = null, $address = null, $avtaladProcent = null) {
    $pdo = getDBConnection();
    
    // Überprüfen, ob die neuen Spalten existieren
    $columnsExist = true;
    
    try {
        $stmt = $pdo->query("SELECT personnummer, address, avtalad_procent FROM employees LIMIT 1");
        $stmt->fetch();
    } catch (PDOException $e) {
        $columnsExist = false;
    }
    
    // Wenn die Spalten nicht existieren, nur den Namen aktualisieren
    if (!$columnsExist) {
        $stmt = $pdo->prepare("UPDATE employees SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }
    
    // Wenn avtaladProcent geändert wurde, den alten Wert abrufen
    $oldAvtaladProcent = null;
    if ($avtaladProcent !== null) {
        $stmt = $pdo->prepare("SELECT avtalad_procent FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result && isset($result['avtalad_procent'])) {
            $oldAvtaladProcent = $result['avtalad_procent'];
        }
    }
    
    // Mitarbeiter aktualisieren
    $success = false;
    if ($avtaladProcent === null) {
        $stmt = $pdo->prepare("UPDATE employees SET name = ?, personnummer = ?, address = ? WHERE id = ?");
        $success = $stmt->execute([$name, $personnummer, $address, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE employees SET name = ?, personnummer = ?, address = ?, avtalad_procent = ? WHERE id = ?");
        $success = $stmt->execute([$name, $personnummer, $address, $avtaladProcent, $id]);
    }
    
    // Wenn die Aktualisierung erfolgreich war und avtaladProcent geändert wurde,
    // auch die bestehenden Arbeitszeiten aktualisieren
    if ($success && $avtaladProcent !== null && $oldAvtaladProcent !== null && $avtaladProcent != $oldAvtaladProcent) {
        try {
            // Alle bestehenden Arbeitszeiten für diesen Mitarbeiter aktualisieren
            $stmt = $pdo->prepare("UPDATE work_hours SET avtalad_procent = ? WHERE employee_id = ?");
            $stmt->execute([$avtaladProcent, $id]);
        } catch (PDOException $e) {
            // Fehler beim Aktualisieren der Arbeitszeiten ignorieren
            error_log("Fehler beim Aktualisieren der Arbeitszeiten: " . $e->getMessage());
        }
    }
    
    return $success;
}

/**
 * Löscht einen Mitarbeiter
 * 
 * @param int $id ID des Mitarbeiters
 * @return bool True bei Erfolg, sonst False
 */
function deleteEmployee($id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Berechnet das Stundenkonto eines Mitarbeiters für einen bestimmten Monat
 * 
 * @param int $employeeId ID des Mitarbeiters
 * @param int $month Monat (1-12)
 * @param int $year Jahr
 * @return array Stundenkonto-Daten
 */
function getEmployeeMonthBalance($employeeId, $month, $year) {
    $pdo = getDBConnection();
    
    // Startdatum und Enddatum des Monats
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(arbetstid) AS total_arbetstid,
            SUM(sjuk) AS total_sjuk,
            SUM(semester) AS total_semester,
            SUM(arbetstid - sjuk - semester) AS total_100,
            SUM((arbetstid - sjuk - semester) * (avtalad_procent / 100)) AS total_avtalad,
            SUM(arbete) AS total_arbete,
            0 AS total_distansarbete,
            SUM(arbete - ((arbetstid - sjuk - semester) * (avtalad_procent / 100))) AS total_saldo
        FROM work_hours
        WHERE employee_id = ? AND date BETWEEN ? AND ?
    ");
    
    $stmt->execute([$employeeId, $startDate, $endDate]);
    $result = $stmt->fetch();
    
    // Wenn keine Daten vorhanden sind, leere Werte zurückgeben
    if (!$result || $result['total_arbetstid'] === null) {
        return [
            'total_arbetstid' => 0,
            'total_sjuk' => 0,
            'total_semester' => 0,
            'total_100' => 0,
            'total_avtalad' => 0,
            'total_arbete' => 0,
            'total_distansarbete' => 0,
            'total_saldo' => 0
        ];
    }
    
    return $result;
}

/**
 * Berechnet das Stundenkonto eines Mitarbeiters für ein bestimmtes Jahr
 * 
 * @param int $employeeId ID des Mitarbeiters
 * @param int $year Jahr
 * @return array Stundenkonto-Daten
 */
function getEmployeeYearBalance($employeeId, $year) {
    $pdo = getDBConnection();
    
    // Startdatum und Enddatum des Jahres
    $startDate = sprintf('%04d-01-01', $year);
    $endDate = sprintf('%04d-12-31', $year);
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(arbetstid) AS total_arbetstid,
            SUM(sjuk) AS total_sjuk,
            SUM(semester) AS total_semester,
            SUM(arbetstid - sjuk - semester) AS total_100,
            SUM((arbetstid - sjuk - semester) * (avtalad_procent / 100)) AS total_avtalad,
            SUM(arbete) AS total_arbete,
            SUM(distansarbete) AS total_distansarbete,
            SUM(arbete + distansarbete - ((arbetstid - sjuk - semester) * (avtalad_procent / 100))) AS total_saldo
        FROM work_hours
        WHERE employee_id = ? AND date BETWEEN ? AND ?
    ");
    
    $stmt->execute([$employeeId, $startDate, $endDate]);
    $result = $stmt->fetch();
    
    // Wenn keine Daten vorhanden sind, leere Werte zurückgeben
    if (!$result || $result['total_arbetstid'] === null) {
        return [
            'total_arbetstid' => 0,
            'total_sjuk' => 0,
            'total_semester' => 0,
            'total_100' => 0,
            'total_avtalad' => 0,
            'total_arbete' => 0,
            'total_distansarbete' => 0,
            'total_saldo' => 0
        ];
    }
    
    return $result;
}

/**
 * Berechnet das Stundenkonto aller Mitarbeiter für einen bestimmten Monat
 * 
 * @param int $month Monat (1-12)
 * @param int $year Jahr
 * @return array Stundenkonto-Daten aller Mitarbeiter
 */
function getAllEmployeesMonthBalance($month, $year) {
    $employees = getAllEmployees();
    $result = [];
    
    foreach ($employees as $employee) {
        $balance = getEmployeeMonthBalance($employee['id'], $month, $year);
        $result[] = [
            'employee' => $employee,
            'balance' => $balance
        ];
    }
    
    return $result;
}

/**
 * Berechnet das Stundenkonto aller Mitarbeiter für ein bestimmtes Jahr
 * 
 * @param int $year Jahr
 * @return array Stundenkonto-Daten aller Mitarbeiter
 */
function getAllEmployeesYearBalance($year) {
    $employees = getAllEmployees();
    $result = [];
    
    foreach ($employees as $employee) {
        $balance = getEmployeeYearBalance($employee['id'], $year);
        $result[] = [
            'employee' => $employee,
            'balance' => $balance
        ];
    }
    
    return $result;
} 