# Tidsrapportering - Zeiterfassungssoftware

Eine Webanwendung zur Erfassung und Verwaltung von Arbeitszeiten für Mitarbeiter.

## Funktionen

- Benutzerauthentifizierung
- Verwaltung von Mitarbeitern
- Erfassung von Arbeitszeiten, Krankheit, Urlaub und Fernarbeit
- Monats- und Jahresübersichten
- Verwaltung von Feiertagen

## Technische Anforderungen

- PHP 7.4 oder höher
- MariaDB 10.x oder höher
- Webserver (Apache, Nginx, etc.)

## Installation

1. Laden Sie die Dateien auf Ihren Webserver hoch.

2. Stellen Sie sicher, dass die Datenbankverbindungsdaten in `config/database.php` korrekt sind:

```php
define('DB_HOST', '62.108.32.157');
define('DB_PORT', '3306');
define('DB_NAME', 'aealubjt_arbetstid');
define('DB_USER', 'aealubjt_3');
define('DB_PASS', 'xalslv004');
```

3. Führen Sie das Datenbankinitialisierungsskript aus, um die Datenbankstruktur zu erstellen und den Standardbenutzer einzurichten:

```
php sql/init_database.php
```

4. Öffnen Sie die Anwendung in einem Webbrowser und melden Sie sich mit den folgenden Zugangsdaten an:

- Benutzername: olofb
- Passwort: svansele57

## Verzeichnisstruktur

- `assets/` - CSS-Dateien und andere statische Ressourcen
- `config/` - Konfigurationsdateien
- `includes/` - PHP-Funktionen und Hilfsdateien
- `sql/` - SQL-Skripte und Datenbankinitialisierung

## Verwendung

1. **Anmeldung**: Melden Sie sich mit den Zugangsdaten an.
2. **Startseite**: Zeigt eine Übersicht aller Mitarbeiter und deren Stundenkonto.
3. **Mitarbeiter**: Verwalten Sie Mitarbeiter (hinzufügen, bearbeiten, löschen).
4. **Arbeitszeiten**: Erfassen Sie Arbeitszeiten, Krankheit, Urlaub und Fernarbeit für jeden Mitarbeiter.
5. **Feiertage**: Verwalten Sie Feiertage, die automatisch als arbeitsfreie Tage markiert werden.

## Berechnungen

Die Anwendung berechnet automatisch folgende Werte:

- **100%**: `Arbetstid - Sjuk - Semester`
- **Avtalad tid**: `100% * Avtalad %`
- **Saldo**: `Arbete + Avtalad tid - Distansarbete`

## Lizenz

Diese Software wurde für einen spezifischen Kunden entwickelt und darf nur mit dessen Genehmigung verwendet werden.
