<?php
/**
 * Debug Script: Count Parameters in submit-persiapan-operasi.php
 */

$file = file_get_contents(__DIR__ . '/process/submit-persiapan-operasi.php');

// Extract UPDATE query
preg_match('/UPDATE tbl_anestesi_persiapan_operasi SET(.*?)WHERE no_rawat/s', $file, $update_match);
$update_query = $update_match[1] ?? '';

// Extract INSERT query
preg_match('/INSERT INTO tbl_anestesi_persiapan_operasi\s+\((.*?)\)\s+VALUES\s+\((.*?)\)/s', $file, $insert_match);
$insert_columns = $insert_match[1] ?? '';
$insert_values = $insert_match[2] ?? '';

// Extract bindParam calls
preg_match_all('/\$stmt->bindParam\([\'"]:(.*?)[\'"]/s', $file, $bind_matches);
$bind_params = $bind_matches[1] ?? [];

// Count parameters in UPDATE
preg_match_all('/:(\w+)/', $update_query, $update_params);
$update_params = array_unique($update_params[1]);

// Count parameters in INSERT VALUES
preg_match_all('/:(\w+)/', $insert_values, $insert_params);
$insert_params = array_unique($insert_params[1]);

// Count bindParam
$bind_params = array_unique($bind_params);

echo "=== PARAMETER COUNT DEBUG ===\n\n";

echo "UPDATE Query Parameters: " . count($update_params) . "\n";
echo "INSERT Query Parameters: " . count($insert_params) . "\n";
echo "bindParam Calls: " . count($bind_params) . "\n\n";

// Find missing in UPDATE
$missing_in_update = array_diff($bind_params, $update_params);
if (!empty($missing_in_update)) {
    echo "❌ Parameters in bindParam but NOT in UPDATE:\n";
    foreach ($missing_in_update as $param) {
        echo "   - :$param\n";
    }
    echo "\n";
}

// Find missing in INSERT
$missing_in_insert = array_diff($bind_params, $insert_params);
if (!empty($missing_in_insert)) {
    echo "❌ Parameters in bindParam but NOT in INSERT:\n";
    foreach ($missing_in_insert as $param) {
        echo "   - :$param\n";
    }
    echo "\n";
}

// Find missing bindParam
$missing_bind_update = array_diff($update_params, $bind_params);
if (!empty($missing_bind_update)) {
    echo "❌ Parameters in UPDATE but NOT in bindParam:\n";
    foreach ($missing_bind_update as $param) {
        echo "   - :$param\n";
    }
    echo "\n";
}

$missing_bind_insert = array_diff($insert_params, $bind_params);
if (!empty($missing_bind_insert)) {
    echo "❌ Parameters in INSERT but NOT in bindParam:\n";
    foreach ($missing_bind_insert as $param) {
        echo "   - :$param\n";
    }
    echo "\n";
}

if (empty($missing_in_update) && empty($missing_in_insert) && empty($missing_bind_update) && empty($missing_bind_insert)) {
    echo "✅ ALL PARAMETERS MATCH!\n";
} else {
    echo "❌ PARAMETER MISMATCH DETECTED!\n";
}

echo "\n=== DETAILED LISTS ===\n\n";

echo "UPDATE Parameters (" . count($update_params) . "):\n";
sort($update_params);
foreach ($update_params as $p) {
    echo "  :$p\n";
}

echo "\nINSERT Parameters (" . count($insert_params) . "):\n";
sort($insert_params);
foreach ($insert_params as $p) {
    echo "  :$p\n";
}

echo "\nbindParam Calls (" . count($bind_params) . "):\n";
sort($bind_params);
foreach ($bind_params as $p) {
    echo "  :$p\n";
}
