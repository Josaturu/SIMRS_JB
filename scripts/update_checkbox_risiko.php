<?php
// Script untuk update semua checkbox risiko dengan pendekatan regex

$file = __DIR__ . '/views/form-informed-consent-anestesi.php';
$content = file_get_contents($file);

echo "Menambahkan auto-check untuk semua Checkbox Risiko...\n\n";

// Pattern untuk checkbox risiko: <label><input type="checkbox" name="risiko[]" value="XXX"> XXX</label>
// Akan diganti dengan checked condition

// Gunakan preg_replace_callback untuk mengganti semua checkbox risiko sekaligus
$pattern = '/<label><input type="checkbox" name="risiko\[\]" value="([^"]+)">([^<]+)<\/label>/';

$content = preg_replace_callback($pattern, function($matches) {
    $value = $matches[1];
    $label = $matches[2];
    return '<label><input type="checkbox" name="risiko[]" value="' . $value . '" <?= in_array(\'' . $value . '\', $risiko_data) ? \'checked\' : \'\' ?>>' . $label . '</label>';
}, $content, -1, $count);

file_put_contents($file, $content);

echo "✅ Selesai! $count checkbox Risiko berhasil diupdate\n";
echo "\nSemua checkbox sudah support auto-check saat edit!\n";
?>
