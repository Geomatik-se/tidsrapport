# Mehrjahresunterstützung - Update 2025

Dieses Update erweitert das Tidsrapport-System um vollständige Unterstützung für mehrere Jahre über 2025 hinaus.

## Neue Features

### 1. Automatische Feiertags-Generierung
- **Neue Datei**: `includes/holiday_generator.php`
- **Neue Datei**: `generate_holidays.php` (Admin-Interface)
- Automatische Generierung schwedischer Feiertage für beliebige Jahre
- Unterstützt sowohl feste als auch bewegliche Feiertage (basierend auf Ostern)

#### Generierte Feiertage:
- **Feste Feiertage**: Nyårsdagen, Trettondedag jul, Första maj, Sveriges nationaldag, Julafton, Juldagen, Annandag jul, Nyårsafton
- **Bewegliche Feiertage**: Långfredag, Påskdagen, Annandag påsk, Kristi himmelfärdsdag, Pingstdagen, Midsommarafton, Midsommardagen, Alla helgons dag

### 2. Erweiterte Jahr-Navigation
- **Neue Datei**: `includes/navigation_helper.php`
- Verbesserte Jahres-Auswahl mit Dropdown-Menü
- Schnelllinks zum aktuellen Jahr
- Konsistente Navigation zwischen allen Seiten

### 3. Automatisches Setup-System
- **Neue Datei**: `auto_setup.php`
- Automatische Initialisierung neuer Jahre beim ersten Zugriff
- Silent-Mode für Hintergrund-Operationen
- Arbeitszeiten-Initialisierung für neue Mitarbeiter

### 4. Erweiterte Arbeitszeiten-Verwaltung
- Neue Funktionen in `includes/work_hours.php`
- Automatische Initialisierung für ganze Jahre
- Bulk-Operationen für alle Mitarbeiter

## Aktualisierte Dateien

### Core-Dateien:
- `index.php` - Erweiterte Navigation und Auto-Setup
- `holidays.php` - Neue Navigation und Admin-Tools
- `employee_year.php` - Verbesserte Jahres-Navigation
- `employee_month.php` - Jahr- und Monats-Auswahl

### CSS-Verbesserungen:
- `assets/css/style.css` - Neue Styles für Navigation

### Helper-Dateien:
- `includes/work_hours.php` - Erweiterte Funktionen
- Neue Helper-Dateien für Navigation und Feiertage

## Verwendung

### Automatische Funktionen
Das System arbeitet automatisch:
1. Beim Aufruf einer beliebigen Seite werden automatisch Feiertage für die nächsten 2-3 Jahre generiert
2. Neue Mitarbeiter erhalten automatisch initialisierte Arbeitszeiten

### Manuelle Verwaltung
Administratoren können über das neue Interface:
1. **Feiertage verwalten**: `holidays.php` → "Admin-verktyg" → "Generera röda dagar"
2. **Bulk-Generierung**: `generate_holidays.php` für mehrere Jahre gleichzeitig
3. **API-Zugriff**: `auto_setup.php` für JSON-basierte Operationen

### Navigation
Alle Seiten haben jetzt:
- Jahr-Dropdown für schnellen Wechsel
- "I år" Button für Sprung zum aktuellen Jahr
- Verbesserte Vor/Zurück-Navigation

## Technische Details

### Feiertags-Berechnung
```php
// Automatische Generierung für ein Jahr
$holidays = generateStandardHolidays(2026);

// Bulk-Generierung
$report = autoGenerateHolidays(2025, 2030);
```

### Jahr-Navigation
```php
// Erweiterte Navigation rendern
echo renderYearNavigation($currentYear, 'employee_year.php', ['id' => $employeeId]);

// Einfache Jahr-Auswahl
echo renderYearSelector($currentYear, $baseUrl, 2020, 2035);
```

### Auto-Setup
```php
// Silent Setup (für automatische Aufrufe)
$report = silentAutoSetup($targetYear, $initWorkHours);

// Vollständiges Setup
$report = autoSetupYear($targetYear, $initWorkHours);
```

## Rückwärtskompatibilität

Alle Änderungen sind vollständig rückwärtskompatibel:
- Bestehende URLs funktionieren weiterhin
- Vorhandene Daten bleiben unverändert
- Keine Datenbankstruktur-Änderungen erforderlich

## Neue Admin-Features

### Feiertags-Generator (`generate_holidays.php`)
- Übersicht über vorhandene Feiertage
- Bulk-Generierung für mehrere Jahre
- Status-Reports und Fehlerbehandlung

### API-Endpoint (`auto_setup.php`)
```
GET auto_setup.php?year=2026&init_work=1
```
Gibt JSON-Response mit Setup-Status zurück.

## Migration

Für bestehende Installationen:
1. Alle neuen Dateien hochladen
2. Keine Datenbank-Updates erforderlich
3. System funktioniert sofort mit erweiterten Features

Das System generiert automatisch beim ersten Zugriff fehlende Feiertage für die nächsten Jahre.