<?php
/**
 * Logout-Seite
 */

require_once 'includes/auth.php';

// Benutzer abmelden
logout();

// Zur Login-Seite weiterleiten
header('Location: login.php');
exit; 