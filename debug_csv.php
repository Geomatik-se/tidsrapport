<?php
/**
 * Debug CSV-Import - zeigt genau was gelesen wird
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
table { border-collapse: collapse; font-family: monospace; font-size: 12px; }
th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
th { background: #f0f0f0; }
.highlight { background: #ffffcc; }
</style></head><body>";
echo "<h1>CSV-Debug: Was wird gelesen?</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            // Auto-detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
            
            echo "<p><strong>Trennzeichen erkannt:</strong> " . ($delimiter === ';' ? 'Semikolon (;)' : 'Komma (,)') . "</p>";
            
            echo "<table>";
            echo "<tr><th>Zeile</th><th>A (Datum)</th><th>B (Arbeit)</th><th>C (Krank)</th><th>D (Urlaub)</th><th>E</th><th>F</th><th>G</th><th>H (Arbete)</th><th>I (Distans)</th><th>J</th></tr>";
            
            $lineNum = 0;
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false && $lineNum < 15) {
                $lineNum++;
                echo "<tr>";
                echo "<td>$lineNum</td>";
                
                for ($i = 0; $i < 10; $i++) {
                    $val = isset($data[$i]) ? trim($data[$i]) : '';
                    $class = ($i === 7 || $i === 8) ? 'highlight' : ''; // Highlight H und I
                    $display = htmlspecialchars($val);
                    if ($display === '') $display = '<em>leer</em>';
                    echo "<td class='$class'>[$i] $display</td>";
                }
                
                echo "</tr>";
            }
            
            echo "</table>";
            
            echo "<hr>";
            echo "<h3>Interpretation:</h3>";
            echo "<p>Spalte H (Index 7) = Arbete</p>";
            echo "<p>Spalte I (Index 8) = Distansarbete</p>";
            echo "<p>Die gelb markierten Spalten sollten Ihre Arbete/Distans-Werte enthalten.</p>";
            
            fclose($handle);
        }
    }
} else {
    ?>
    <form method="post" enctype="multipart/form-data" style="padding: 20px; background: #f8f9fa; border-radius: 8px; max-width: 500px;">
        <h3>CSV-Datei hochladen zum Debuggen</h3>
        <input type="file" name="csv_file" accept=".csv" required style="margin: 15px 0; padding: 8px;">
        <br>
        <button type="submit" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Datei analysieren</button>
    </form>
    
    <p style="margin-top: 20px; color: #666;">
        Dies zeigt die ersten 15 Zeilen Ihrer CSV und welche Werte in welchen Spalten stehen.
    </p>
    <?php
}

echo "</body></html>";
?>
