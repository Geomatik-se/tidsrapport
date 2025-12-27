<?php
/**
 * Datenbankkonfiguration und Verbindungsfunktion
 */

// Datenbank-Konfiguration
define('DB_HOST', '62.108.32.157');
define('DB_PORT', '3306');
define('DB_NAME', 'aealubjt_2026');
define('DB_USER', 'aealubjt_2026');
define('DB_PASS', 'Morot1234');

/**
 * Stellt eine Verbindung zur Datenbank her
 * 
 * @return PDO Die Datenbankverbindung
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        // Fehlermeldung in Log schreiben
        error_log("Datenbankverbindungsfehler: " . $e->getMessage());
        
        // Benutzerfreundliche Fehlermeldung anzeigen
        die("Es konnte keine Verbindung zur Datenbank hergestellt werden. Bitte versuchen Sie es später erneut oder kontaktieren Sie den Administrator.");
    }
} 