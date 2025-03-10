# Installationsanleitung für die Zeiterfassungssoftware

Diese Anleitung führt Sie durch die Schritte, um die Zeiterfassungssoftware auf Ihrem Webserver zu installieren.

## Voraussetzungen

- Webserver mit PHP 7.4 oder höher
- MariaDB 10.x oder höher
- PHP-Erweiterungen: PDO, PDO_MySQL, mbstring, json

## Installationsschritte

### 1. Dateien hochladen

Laden Sie alle Dateien und Verzeichnisse auf Ihren Webserver hoch. Sie können dies mit FTP, SFTP oder einem anderen Dateiübertragungsprogramm tun.

Stellen Sie sicher, dass die Verzeichnisstruktur wie folgt aussieht:

```
/
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── employees.php
│   └── work_hours.php
├── sql/
│   ├── database_schema.sql
│   └── init_database.php
├── .htaccess
├── employee_add.php
├── employee_edit.php
├── employee_month.php
├── employee_year.php
├── employees.php
├── holidays.php
├── index.php
├── login.php
├── logout.php
└── README.md
```

### 2. Datenbankverbindung konfigurieren

Die Datenbankverbindungsdaten sind bereits in der Datei `config/database.php` konfiguriert:

```php
define('DB_HOST', '62.108.32.157');
define('DB_PORT', '3306');
define('DB_NAME', 'aealubjt_arbetstid');
define('DB_USER', 'aealubjt_3');
define('DB_PASS', 'xalslv004');
```

Falls Sie andere Datenbankverbindungsdaten verwenden möchten, passen Sie diese Werte entsprechend an.

### 3. Datenbank initialisieren

Es gibt zwei Möglichkeiten, die Datenbank zu initialisieren:

#### Option 1: Über die Kommandozeile (SSH)

Wenn Sie SSH-Zugriff auf Ihren Server haben, führen Sie das folgende Kommando im Hauptverzeichnis der Anwendung aus:

```
php sql/init_database.php
```

#### Option 2: Über den Browser

Wenn Sie keinen SSH-Zugriff haben, können Sie die Datenbank über den Browser initialisieren. Dazu müssen Sie temporär die .htaccess-Regel deaktivieren, die den Zugriff auf das sql-Verzeichnis verhindert.

1. Öffnen Sie die .htaccess-Datei und kommentieren Sie die folgende Zeile aus:

```
# RewriteRule ^sql/.*\.php$ - [F,L]
```

2. Rufen Sie dann die folgende URL in Ihrem Browser auf:

```
https://ihre-domain.de/sql/init_database.php
```

3. Nach erfolgreicher Initialisierung sollten Sie die Meldung "Datenbank-Initialisierung abgeschlossen." sehen.

4. Aktivieren Sie die .htaccess-Regel wieder, indem Sie die Kommentierung entfernen.

### 4. Berechtigungen prüfen

Stellen Sie sicher, dass der Webserver Schreibrechte für die folgenden Verzeichnisse hat:

- `assets/` (falls Sie Uploads implementieren möchten)
- `logs/` (falls Sie Logging implementieren möchten)

### 5. Anmeldung testen

Öffnen Sie die Anwendung in Ihrem Browser:

```
https://ihre-domain.de/
```

Sie sollten zur Login-Seite weitergeleitet werden. Melden Sie sich mit den folgenden Zugangsdaten an:

- Benutzername: olofb
- Passwort: svansele57

Nach erfolgreicher Anmeldung sollten Sie zur Startseite weitergeleitet werden, wo Sie mit der Verwaltung von Mitarbeitern und Arbeitszeiten beginnen können.

## Fehlerbehebung

### Problem: Datenbankverbindungsfehler

Wenn Sie einen Datenbankverbindungsfehler erhalten, überprüfen Sie Folgendes:

1. Sind die Datenbankverbindungsdaten in `config/database.php` korrekt?
2. Ist die Datenbank erreichbar? (Firewall, Netzwerkprobleme)
3. Hat der Datenbankbenutzer die notwendigen Rechte?

### Problem: Leere Seite oder 500-Fehler

Wenn Sie eine leere Seite oder einen 500-Fehler erhalten, überprüfen Sie Folgendes:

1. Aktivieren Sie temporär die Fehleranzeige in der .htaccess-Datei:

```
php_flag display_errors on
php_value error_reporting E_ALL
```

2. Überprüfen Sie die PHP-Fehlerprotokolle auf Ihrem Server.

### Problem: Anmeldung funktioniert nicht

Wenn die Anmeldung nicht funktioniert, überprüfen Sie Folgendes:

1. Wurde die Datenbank korrekt initialisiert?
2. Wurde der Benutzer "olofb" korrekt in der Datenbank angelegt?
3. Ist das Passwort korrekt gehasht?

Sie können das Passwort manuell zurücksetzen, indem Sie folgende SQL-Abfrage ausführen:

```sql
UPDATE users SET password = '$2y$10$YourNewHashedPassword' WHERE username = 'olofb';
```

Wobei `$2y$10$YourNewHashedPassword` ein mit `password_hash()` generierter Hash ist.

## Support

Bei Fragen oder Problemen wenden Sie sich bitte an den Entwickler der Anwendung.
