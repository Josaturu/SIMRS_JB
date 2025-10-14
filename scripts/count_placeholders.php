<?php
// Read the actual query from process file
$file = file_get_contents('process/process-konsultasi-anestesi.php');

// Extract the query
preg_match('/\$query = "INSERT INTO.*?VALUES \((.*?)\)/s', $file, $matches);

if (isset($matches[1])) {
    $values_part = $matches[1];
    $placeholder_count = substr_count($values_part, '?');
    
    echo "=== PLACEHOLDER COUNT ===\n";
    echo "Total '?' in VALUES: $placeholder_count\n\n";
    
    // Count per line
    $lines = explode("\n", $values_part);
    echo "=== PER LINE ===\n";
    $total = 0;
    foreach ($lines as $i => $line) {
        $count = substr_count($line, '?');
        if ($count > 0) {
            $total += $count;
            echo "Line " . ($i + 1) . ": $count placeholders (total so far: $total)\n";
        }
    }
    
    echo "\n=== EXPECTED ===\n";
    echo "Should be: 96\n";
    echo "Actual: $placeholder_count\n";
    
    if ($placeholder_count == 96) {
        echo "\n✅ CORRECT!\n";
    } else {
        echo "\n❌ MISMATCH! Difference: " . ($placeholder_count - 96) . "\n";
    }
} else {
    echo "❌ Could not extract query from file\n";
}
?>
