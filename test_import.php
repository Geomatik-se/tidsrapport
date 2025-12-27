<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Testing import.php includes...<br>";

try {
    echo "1. Loading auth.php...<br>";
    require_once 'includes/auth.php';
    echo "OK<br>";
    
    echo "2. Loading employees.php...<br>";
    require_once 'includes/employees.php';
    echo "OK<br>";
    
    echo "3. Loading work_hours.php...<br>";
    require_once 'includes/work_hours.php';
    echo "OK<br>";
    
    echo "<br>All includes loaded successfully!<br>";
    echo "<a href='import.php'>Go to import.php</a>";
    
} catch (Exception $e) {
    echo "<br><strong style='color: red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</strong><br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
