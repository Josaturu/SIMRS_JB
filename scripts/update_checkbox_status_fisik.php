<?php
// Script untuk update checkbox status fisik dan checkbox konfirmasi

$file = __DIR__ . '/views/form-informed-consent-anestesi.php';
$content = file_get_contents($file);

echo "Menambahkan auto-check untuk Status Fisik dan Checkbox Konfirmasi...\n\n";

// Status Fisik
$status_fisik = [
    [
        'search' => '<label><input type="checkbox" name="statusFisik[]" value="ASA I"> ASA I</label>',
        'replace' => '<label><input type="checkbox" name="statusFisik[]" value="ASA I" <?= in_array(\'ASA I\', $status_fisik_data) ? \'checked\' : \'\' ?>> ASA I</label>',
        'label' => 'ASA I'
    ],
    [
        'search' => '<label><input type="checkbox" name="statusFisik[]" value="ASA II"> ASA II</label>',
        'replace' => '<label><input type="checkbox" name="statusFisik[]" value="ASA II" <?= in_array(\'ASA II\', $status_fisik_data) ? \'checked\' : \'\' ?>> ASA II</label>',
        'label' => 'ASA II'
    ],
    [
        'search' => '<label><input type="checkbox" name="statusFisik[]" value="ASA III"> ASA III</label>',
        'replace' => '<label><input type="checkbox" name="statusFisik[]" value="ASA III" <?= in_array(\'ASA III\', $status_fisik_data) ? \'checked\' : \'\' ?>> ASA III</label>',
        'label' => 'ASA III'
    ],
    [
        'search' => '<label><input type="checkbox" name="statusFisik[]" value="ASA IV"> ASA IV</label>',
        'replace' => '<label><input type="checkbox" name="statusFisik[]" value="ASA IV" <?= in_array(\'ASA IV\', $status_fisik_data) ? \'checked\' : \'\' ?>> ASA IV</label>',
        'label' => 'ASA IV'
    ],
    [
        'search' => '<label><input type="checkbox" name="statusFisik[]" value="ASA V"> ASA V</label>',
        'replace' => '<label><input type="checkbox" name="statusFisik[]" value="ASA V" <?= in_array(\'ASA V\', $status_fisik_data) ? \'checked\' : \'\' ?>> ASA V</label>',
        'label' => 'ASA V'
    ]
];

$updated = 0;
foreach ($status_fisik as $item) {
    if (strpos($content, $item['search']) !== false) {
        $content = str_replace($item['search'], $item['replace'], $content);
        echo "✅ Status Fisik - {$item['label']}\n";
        $updated++;
    }
}

// Checkbox Konfirmasi (cek1 sampai cek10)
echo "\n";
for ($i = 1; $i <= 10; $i++) {
    $old_cek = "<input type=\"checkbox\" name=\"cek$i\" value=\"1\">";
    $new_cek = "<input type=\"checkbox\" name=\"cek$i\" value=\"1\" <?= in_array('$i', \$checkbox_confirm_data) ? 'checked' : '' ?>>";
    
    if (strpos($content, $old_cek) !== false) {
        $content = str_replace($old_cek, $new_cek, $content);
        echo "✅ Checkbox Konfirmasi cek$i\n";
        $updated++;
    }
}

file_put_contents($file, $content);

echo "\n✅ Selesai! $updated checkbox diupdate\n";
echo "\nLanjut ke update checkbox Risiko dengan pendekatan generik...\n";
?>
