<?php
/**
 * Hilfsfunktionen für die Datumsformatierung auf Schwedisch
 */

// Monatsnamen auf Schwedisch
$GLOBALS['monthNames'] = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Mars', 4 => 'April',
    5 => 'Maj', 6 => 'Juni', 7 => 'Juli', 8 => 'Augusti',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
];

// Wochentagsnamen auf Schwedisch
$GLOBALS['dayNames'] = [
    'Mon' => 'Mån', 'Tue' => 'Tis', 'Wed' => 'Ons', 
    'Thu' => 'Tor', 'Fri' => 'Fre', 'Sat' => 'Lör', 'Sun' => 'Sön'
];

// Vollständige Wochentagsnamen auf Schwedisch
$GLOBALS['fullDayNames'] = [
    'Monday' => 'Måndag', 'Tuesday' => 'Tisdag', 'Wednesday' => 'Onsdag', 
    'Thursday' => 'Torsdag', 'Friday' => 'Fredag', 'Saturday' => 'Lördag', 'Sunday' => 'Söndag'
];

/**
 * Formatiert ein Datum auf Schwedisch
 * 
 * @param string|DateTime $date Datum als String oder DateTime-Objekt
 * @param string $format Format (short, medium, long, full)
 * @return string Formatiertes Datum auf Schwedisch
 */
function formatDateSwedish($date, $format = 'medium') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    $day = $date->format('j');
    $month = $date->format('n');
    $year = $date->format('Y');
    $weekday = $date->format('l'); // Vollständiger Wochentagsname auf Englisch
    $shortWeekday = $date->format('D'); // Kurzer Wochentagsname auf Englisch
    
    switch ($format) {
        case 'short':
            // Format: 2025-03-09
            return $date->format('Y-m-d');
        
        case 'medium':
            // Format: 9 mar 2025
            return $day . ' ' . mb_strtolower(mb_substr($GLOBALS['monthNames'][$month], 0, 3)) . ' ' . $year;
        
        case 'long':
            // Format: 9 mars 2025
            return $day . ' ' . mb_strtolower($GLOBALS['monthNames'][$month]) . ' ' . $year;
        
        case 'full':
            // Format: söndag 9 mars 2025
            return mb_strtolower($GLOBALS['fullDayNames'][$weekday]) . ' ' . $day . ' ' . mb_strtolower($GLOBALS['monthNames'][$month]) . ' ' . $year;
        
        case 'weekday':
            // Format: Sön
            return $GLOBALS['dayNames'][$shortWeekday];
        
        default:
            return $date->format('Y-m-d');
    }
}

/**
 * Gibt den Monatsnamen auf Schwedisch zurück
 * 
 * @param int $month Monatsnummer (1-12)
 * @param bool $lowercase Kleinschreibung (true) oder Großschreibung (false)
 * @return string Monatsname auf Schwedisch
 */
function getMonthName($month, $lowercase = false) {
    $monthName = $GLOBALS['monthNames'][$month] ?? '';
    return $lowercase ? mb_strtolower($monthName) : $monthName;
}

/**
 * Gibt den Wochentagsnamen auf Schwedisch zurück
 * 
 * @param string $day Wochentagsname auf Englisch (Mon, Tue, etc.)
 * @return string Wochentagsname auf Schwedisch
 */
function getDayName($day) {
    return $GLOBALS['dayNames'][$day] ?? $day;
} 