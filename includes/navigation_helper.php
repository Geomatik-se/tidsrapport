<?php
/**
 * Helper-Funktionen für die Jahr- und Monatsnavigation
 */

/**
 * Generiert eine Jahr-Auswahl-Dropdown
 * 
 * @param int $currentYear Aktuell ausgewähltes Jahr
 * @param string $baseUrl Basis-URL für Links
 * @param int $startYear Erstes Jahr in der Auswahl (Standard: aktuelles Jahr - 5)
 * @param int $endYear Letztes Jahr in der Auswahl (Standard: aktuelles Jahr + 10)
 * @return string HTML für Jahr-Auswahl
 */
function renderYearSelector($currentYear, $baseUrl, $startYear = null, $endYear = null) {
    if ($startYear === null) {
        $startYear = max(2020, (int)date('Y') - 5);
    }
    if ($endYear === null) {
        $endYear = (int)date('Y') + 10;
    }
    
    $html = '<div class="year-selector">';
    $html .= '<label>Välj år: </label>';
    $html .= '<select onchange="window.location.href=this.value">';
    
    for ($year = $startYear; $year <= $endYear; $year++) {
        $selected = ($year == $currentYear) ? 'selected' : '';
        $url = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'year=' . $year;
        $html .= "<option value=\"$url\" $selected>$year</option>";
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Generiert eine Monats-Auswahl-Dropdown
 * 
 * @param int $currentMonth Aktuell ausgewählter Monat
 * @param int $currentYear Aktuell ausgewähltes Jahr
 * @param string $baseUrl Basis-URL für Links
 * @return string HTML für Monats-Auswahl
 */
function renderMonthSelector($currentMonth, $currentYear, $baseUrl) {
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mars', 4 => 'April',
        5 => 'Maj', 6 => 'Juni', 7 => 'Juli', 8 => 'Augusti',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
    ];
    
    $html = '<div class="month-selector">';
    $html .= '<label>Välj månad: </label>';
    $html .= '<select onchange="window.location.href=this.value">';
    
    for ($month = 1; $month <= 12; $month++) {
        $selected = ($month == $currentMonth) ? 'selected' : '';
        $url = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'year=' . $currentYear . '&month=' . $month;
        $html .= "<option value=\"$url\" $selected>{$monthNames[$month]}</option>";
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Generiert Navigations-Links für Jahr (vorheriges/nächstes)
 * 
 * @param int $currentYear Aktuelles Jahr
 * @param string $baseUrl Basis-URL
 * @param array $additionalParams Zusätzliche URL-Parameter
 * @return array Array mit 'prev' und 'next' URLs
 */
function getYearNavigationUrls($currentYear, $baseUrl, $additionalParams = []) {
    $prevYear = $currentYear - 1;
    $nextYear = $currentYear + 1;
    
    $params = array_merge(['year' => null], $additionalParams);
    
    $prevParams = array_merge($params, ['year' => $prevYear]);
    $nextParams = array_merge($params, ['year' => $nextYear]);
    
    $prevUrl = $baseUrl . '?' . http_build_query($prevParams);
    $nextUrl = $baseUrl . '?' . http_build_query($nextParams);
    
    return [
        'prev' => $prevUrl,
        'next' => $nextUrl,
        'prevText' => "← $prevYear",
        'nextText' => "$nextYear →"
    ];
}

/**
 * Generiert erweiterte Jahresnavigation mit Schnelllinks
 * 
 * @param int $currentYear Aktuelles Jahr
 * @param string $baseUrl Basis-URL
 * @param array $additionalParams Zusätzliche Parameter
 * @return string HTML für erweiterte Navigation
 */
function renderYearNavigation($currentYear, $baseUrl, $additionalParams = []) {
    $navigation = getYearNavigationUrls($currentYear, $baseUrl, $additionalParams);
    $currentDate = (int)date('Y');
    
    $html = '<div class="year-navigation">';
    
    // Vorheriges Jahr
    $html .= "<a href=\"{$navigation['prev']}\" class=\"btn nav-btn\">{$navigation['prevText']}</a>";
    
    // Jahr-Auswahl
    $params = array_merge(['year' => null], $additionalParams);
    $selectorBaseUrl = $baseUrl;
    if (!empty($params)) {
        $cleanParams = array_filter($params, function($key) {
            return $key !== 'year';
        }, ARRAY_FILTER_USE_KEY);
        if (!empty($cleanParams)) {
            $selectorBaseUrl .= '?' . http_build_query($cleanParams);
        }
    }
    
    $html .= '<div class="year-selector-inline">';
    $html .= '<select onchange="if(this.value) window.location.href=this.value" class="year-select">';
    
    $startYear = max(2020, $currentDate - 5);
    $endYear = $currentDate + 15;
    
    for ($year = $startYear; $year <= $endYear; $year++) {
        $selected = ($year == $currentYear) ? 'selected' : '';
        $yearParams = array_merge($params, ['year' => $year]);
        $yearUrl = $selectorBaseUrl . (strpos($selectorBaseUrl, '?') !== false ? '&' : '?') . http_build_query(array_filter($yearParams));
        $html .= "<option value=\"$yearUrl\" $selected>$year</option>";
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    // Nächstes Jahr
    $html .= "<a href=\"{$navigation['next']}\" class=\"btn nav-btn\">{$navigation['nextText']}</a>";
    
    // Schnelllinks
    if ($currentYear != $currentDate) {
        $todayParams = array_merge($params, ['year' => $currentDate]);
        $todayUrl = $baseUrl . '?' . http_build_query(array_filter($todayParams));
        $html .= "<a href=\"$todayUrl\" class=\"btn btn-primary nav-btn\">I år ($currentDate)</a>";
    }
    
    $html .= '</div>';
    
    return $html;
}