<?php
// DEBUG POST DATA - Form Konsultasi Anestesi
// Simpan file ini di root folder SIMRS_JB
// Akses via browser untuk melihat data yang dikirim

session_start();

if ($_POST) {
    echo "<h2>DATA POST YANG DITERIMA</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>FIELD YANG KOSONG (NULL atau Empty String)</h3>";
    echo "<ul>";
    
    $empty_fields = [];
    foreach ($_POST as $key => $value) {
        if (empty($value) && $value !== '0') {
            $empty_fields[] = $key;
            echo "<li><strong>$key</strong> = " . (is_null($value) ? 'NULL' : "''") . "</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>FIELD YANG TERISI</h3>";
    echo "<ul>";
    
    foreach ($_POST as $key => $value) {
        if (!empty($value) || $value === '0') {
            $display_value = is_array($value) ? json_encode($value) : htmlspecialchars($value);
            echo "<li><strong>$key</strong> = $display_value</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>FIELD YANG DIHARAPKAN TAPI TIDAK ADA</h3>";
    
    $expected_fields = [
        'tinggiBadan', 'beratBadan', 'pengobatan', 'komunikasiLainnya',
        'jamVisit', 'kesadaran', 'tb', 'bb', 'td', 'nadi', 'rr', 'suhu',
        'paruParu', 'jantung', 'abdomen', 'ekstrimitas', 'neurologi', 'lainLain',
        'asa', 'anestesi_umum', 'regional', 'combined', 'sedasi'
    ];
    
    echo "<ul>";
    foreach ($expected_fields as $field) {
        if (!isset($_POST[$field])) {
            echo "<li style='color: red;'><strong>$field</strong> - TIDAK ADA DI POST DATA</li>";
        }
    }
    echo "</ul>";
    
} else {
    echo "<h2>FORM BELUM DI-SUBMIT</h2>";
    echo "<p>Silakan submit form konsultasi anestesi terlebih dahulu.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug POST Data</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h3 { color: #555; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        ul { list-style-type: none; padding: 0; }
        li { padding: 5px 0; border-bottom: 1px solid #eee; }
        strong { color: #007bff; }
    </style>
</head>
<body>
    <h1>Debug Tool - Form Konsultasi Anestesi</h1>
    <p><a href="views/form-konsultasi-anestesi.php">← Kembali ke Form</a></p>
</body>
</html>
