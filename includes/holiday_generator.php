<?php
/**
 * Generiert automatisch Standard-Feiertage für schwedische Feiertage
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/work_hours.php';

/**
 * Berechnet das Osterdatum für ein bestimmtes Jahr
 * 
 * @param int $year Jahr
 * @return string Osterdatum im Format YYYY-MM-DD
 */
function calculateEaster($year) {
    $base = new DateTime("$year-03-21");
    $days = easter_days($year);
    $base->add(new DateInterval("P{$days}D"));
    return $base->format('Y-m-d');
}

/**
 * Generiert Standard-Feiertage für ein Jahr (schwedische Feiertage)
 * 
 * @param int $year Jahr
 * @return array Array mit Feiertagen [datum => beschreibung]
 */
function generateStandardHolidays($year) {
    $holidays = [];
    
    // Feste Feiertage
    $holidays["$year-01-01"] = "Nyårsdagen";
    $holidays["$year-01-06"] = "Trettondedag jul";
    $holidays["$year-05-01"] = "Första maj";
    $holidays["$year-06-06"] = "Sveriges nationaldag";
    $holidays["$year-12-24"] = "Julafton";
    $holidays["$year-12-25"] = "Juldagen";
    $holidays["$year-12-26"] = "Annandag jul";
    $holidays["$year-12-31"] = "Nyårsafton";
    
    // Bewegliche Feiertage basierend auf Ostern
    try {
        $easter = new DateTime(calculateEaster($year));
        
        // Långfredag (Karfreitag) - 2 Tage vor Ostern
        $langfredag = clone $easter;
        $langfredag->modify('-2 days');
        $holidays[$langfredag->format('Y-m-d')] = "Långfredag";
        
        // Påskdagen (Ostern)
        $holidays[$easter->format('Y-m-d')] = "Påskdagen";
        
        // Annandag påsk (Ostermontag) - 1 Tag nach Ostern
        $annandag_pask = clone $easter;
        $annandag_pask->modify('+1 day');
        $holidays[$annandag_pask->format('Y-m-d')] = "Annandag påsk";
        
        // Kristi himmelfärdsdag (Himmelfahrt) - 39 Tage nach Ostern
        $himmelfahrt = clone $easter;
        $himmelfahrt->modify('+39 days');
        $holidays[$himmelfahrt->format('Y-m-d')] = "Kristi himmelfärdsdag";
        
        // Pingstdagen (Pfingsten) - 49 Tage nach Ostern
        $pingstdag = clone $easter;
        $pingstdag->modify('+49 days');
        $holidays[$pingstdag->format('Y-m-d')] = "Pingstdagen";
        
    } catch (Exception $e) {
        // Fallback: Falls Osterberechnung fehlschlägt, nur feste Feiertage verwenden
    }
    
    // Midsommarafton - letzter Freitag zwischen 19. und 25. Juni
    $midsommarafton = new DateTime("$year-06-19");
    while ($midsommarafton->format('N') != 5) { // 5 = Freitag
        $midsommarafton->modify('+1 day');
        if ($midsommarafton->format('m-d') > '06-25') {
            $midsommarafton = new DateTime("$year-06-19");
            break;
        }
    }
    $holidays[$midsommarafton->format('Y-m-d')] = "Midsommarafton";
    
    // Midsommardagen - Tag nach Midsommarafton
    $midsommardag = clone $midsommarafton;
    $midsommardag->modify('+1 day');
    $holidays[$midsommardag->format('Y-m-d')] = "Midsommardagen";
    
    // Alla helgons dag - erster Samstag zwischen 31. Oktober und 6. November
    $alla_helgons = new DateTime("$year-10-31");
    while ($alla_helgons->format('N') != 6) { // 6 = Samstag
        $alla_helgons->modify('+1 day');
        if ($alla_helgons->format('m-d') > '11-06') {
            $alla_helgons = new DateTime("$year-11-01");
            break;
        }
    }
    $holidays[$alla_helgons->format('Y-m-d')] = "Alla helgons dag";
    
    return $holidays;
}

/**
 * Fügt Standard-Feiertage für ein Jahr in die Datenbank ein
 * 
 * @param int $year Jahr
 * @return int Anzahl der hinzugefügten Feiertage
 */
function addStandardHolidaysForYear($year) {
    $holidays = generateStandardHolidays($year);
    $added = 0;
    
    foreach ($holidays as $date => $description) {
        // Prüfen, ob Feiertag bereits existiert
        if (!isHoliday($date)) {
            if (addHoliday($date, $description)) {
                $added++;
            }
        }
    }
    
    return $added;
}

/**
 * Prüft, ob für ein Jahr bereits Feiertage existieren
 * 
 * @param int $year Jahr
 * @return bool True, wenn bereits Feiertage existieren
 */
function hasHolidaysForYear($year) {
    $holidays = getHolidaysForYear($year);
    return count($holidays) > 0;
}

/**
 * Automatisches Hinzufügen von Standard-Feiertagen für Jahre ohne Feiertage
 * 
 * @param int $startYear Startjahr (optional, Standard: aktuelles Jahr)
 * @param int $endYear Endjahr (optional, Standard: aktuelles Jahr + 5)
 * @return array Bericht über hinzugefügte Feiertage
 */
function autoGenerateHolidays($startYear = null, $endYear = null) {
    if ($startYear === null) {
        $startYear = (int)date('Y');
    }
    if ($endYear === null) {
        $endYear = $startYear + 5;
    }
    
    $report = [];
    
    for ($year = $startYear; $year <= $endYear; $year++) {
        if (!hasHolidaysForYear($year)) {
            $added = addStandardHolidaysForYear($year);
            $report[$year] = $added;
        } else {
            $report[$year] = 0; // Bereits vorhanden
        }
    }
    
    return $report;
}