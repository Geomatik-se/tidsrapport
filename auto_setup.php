<?php
/**
 * Auto-Setup Script für neue Jahre
 * Dieses Script wird automatisch von anderen Seiten aufgerufen und stellt sicher,
 * dass für die nächsten Jahre die notwendigen Feiertage vorhanden sind.
 */

require_once __DIR__ . '/includes/holiday_generator.php';
require_once __DIR__ . '/includes/work_hours.php';

/**
 * Automatische Initialisierung für neue Jahre
 * 
 * @param int $targetYear Das Jahr, für das sichergestellt werden soll, dass Daten vorhanden sind
 * @param bool $initWorkHours Ob auch Arbeitszeiten initialisiert werden sollen
 * @return array Bericht über durchgeführte Aktionen
 */
function autoSetupYear($targetYear = null, $initWorkHours = false) {
    if ($targetYear === null) {
        $targetYear = (int)date('Y');
    }
    
    $report = [
        'year' => $targetYear,
        'holidays_added' => 0,
        'employees_initialized' => 0,
        'actions' => []
    ];
    
    try {
        // Überprüfen und generieren von Feiertagen für das Zieljahr und die nächsten 2 Jahre
        for ($year = $targetYear; $year <= $targetYear + 2; $year++) {
            if (!hasHolidaysForYear($year)) {
                $added = addStandardHolidaysForYear($year);
                $report['holidays_added'] += $added;
                $report['actions'][] = "Lade $added Feiertage für Jahr $year hinzu";
            }
        }
        
        // Optional: Arbeitszeiten initialisieren
        if ($initWorkHours) {
            $workHoursReport = autoInitializeYear($targetYear);
            $report['employees_initialized'] = $workHoursReport['employees_initialized'];
            if ($workHoursReport['employees_initialized'] > 0) {
                $report['actions'][] = "Initialisiere Arbeitszeiten für {$workHoursReport['employees_initialized']} Mitarbeiter für Jahr $targetYear";
            }
            if (!empty($workHoursReport['errors'])) {
                $report['work_hours_errors'] = $workHoursReport['errors'];
            }
        }
        
        $report['success'] = true;
        
    } catch (Exception $e) {
        $report['success'] = false;
        $report['error'] = $e->getMessage();
    }
    
    return $report;
}

/**
 * Silent auto-setup (ohne Ausgabe, für automatische Aufrufe)
 */
function silentAutoSetup($targetYear = null, $initWorkHours = false) {
    try {
        return autoSetupYear($targetYear, $initWorkHours);
    } catch (Exception $e) {
        // Fehler stumm ignorieren für automatische Aufrufe
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Wenn das Script direkt aufgerufen wird, führe Setup durch
if (basename($_SERVER['SCRIPT_NAME']) === 'auto_setup.php') {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $initWorkHours = isset($_GET['init_work']) ? (bool)$_GET['init_work'] : false;
    $report = autoSetupYear($year, $initWorkHours);
    
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT);
}