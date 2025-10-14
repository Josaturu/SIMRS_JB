<?php
// Kolom dari SQL file (excluding id)
$sql_columns = [
    'no_rawat', 'kode_paket', 'tanggal', 'jam_mulai', 'ruang_perawatan', 'dokter_merawat',
    'tanggal_konsul', 'jam_konsul', 'tinggi_badan', 'berat_badan', 'diagnosa_pra_operasi', 'jenis_diagnosa',
    'rencana_tindakan_operasi', 'kondisi_khusus', 'tanggal_dibuat', 'jam_visit', 'menikah',
    'jenis_kelamin', 'merokok', 'alkohol', 'has_pengobatan', 'pengobatan', 'daftar_alergi_obat', 'has_alergi_obat',
    'alergi_makanan', 'alergi_lateks', 'tidak_alergi', 'komunikasi', 'komunikasi_lainnya',
    'asma', 'hepatitis', 'sesak_nafas', 'pingsan', 'sumbatan_jalan_nafas', 'diabetes',
    'tidur_mengorok', 'anemia', 'serangan_jantung', 'sakit_maag', 'hipertensi', 'pendarahan',
    'stroke', 'pembekuan_darah', 'kejang', 'penyakit_berat_lainnya', 'penyakit_terpilih',
    'gigi_palsu', 'makan_terakhir', 'riwayat_operasi', 'jenis_anestesi', 'terakhir_periksa',
    'tempat_periksa_terakhir', 'penyakit_gangguan', 'jumlah_kehamilan', 'jumlah_anak',
    'menyusui', 'kesadaran', 'tb', 'bb', 'td', 'nadi', 'rr', 'suhu', 'skrining_nyeri', 'jalan_nafas',
    'gerakan_leher', 'gerakan_leher_keterangan', 'paru_paru', 'jantung', 'abdomen', 'ekstrimitas',
    'neurologi', 'lain_lain', 'hb_ht_al_at', 'na_k_cl', 'ureum', 'ct_bt', 'kreatin', 'ekg', 'ro_dada',
    'echo', 'lain_lain_pemeriksaan', 'asa_status', 'emergency', 'rekomendasi_anestesi',
    'anestesi_umum', 'regional_anestesi', 'kombinasi_anestesi', 'sedasi', 'saran',
    'puasa_mulai_jam', 'puasa_mulai_tanggal', 'rencana_tiba_jam', 'rencana_tiba_tanggal',
    'rencana_operasi_jam', 'rencana_operasi_tanggal'
];

// Kolom dari query INSERT di process-konsultasi-anestesi.php
$query_columns = [
    'no_rawat', 'kode_paket', 'tanggal', 'jam_mulai', 'ruang_perawatan', 'dokter_merawat',
    'tanggal_konsul', 'jam_konsul', 'tinggi_badan', 'berat_badan', 'diagnosa_pra_operasi', 'jenis_diagnosa',
    'rencana_tindakan_operasi', 'kondisi_khusus', 'tanggal_dibuat', 'jam_visit', 'menikah',
    'jenis_kelamin', 'merokok', 'alkohol', 'has_pengobatan', 'pengobatan', 'daftar_alergi_obat', 'has_alergi_obat',
    'alergi_makanan', 'alergi_lateks', 'tidak_alergi', 'komunikasi', 'komunikasi_lainnya',
    'asma', 'hepatitis', 'sesak_nafas', 'pingsan', 'sumbatan_jalan_nafas', 'diabetes',
    'tidur_mengorok', 'anemia', 'serangan_jantung', 'sakit_maag', 'hipertensi', 'pendarahan',
    'stroke', 'pembekuan_darah', 'kejang', 'penyakit_berat_lainnya', 'penyakit_terpilih',
    'gigi_palsu', 'makan_terakhir', 'riwayat_operasi', 'jenis_anestesi', 'terakhir_periksa',
    'tempat_periksa_terakhir', 'penyakit_gangguan', 'jumlah_kehamilan', 'jumlah_anak',
    'menyusui', 'kesadaran', 'tb', 'bb', 'td', 'nadi', 'rr', 'suhu', 'skrining_nyeri', 'jalan_nafas',
    'gerakan_leher', 'gerakan_leher_keterangan', 'paru_paru', 'jantung', 'abdomen', 'ekstrimitas',
    'neurologi', 'lain_lain', 'hb_ht_al_at', 'na_k_cl', 'ureum', 'ct_bt', 'kreatin', 'ekg', 'ro_dada',
    'echo', 'lain_lain_pemeriksaan', 'asa_status', 'emergency', 'rekomendasi_anestesi',
    'anestesi_umum', 'regional_anestesi', 'kombinasi_anestesi', 'sedasi', 'saran',
    'puasa_mulai_jam', 'puasa_mulai_tanggal', 'rencana_tiba_jam', 'rencana_tiba_tanggal',
    'rencana_operasi_jam', 'rencana_operasi_tanggal'
];

echo "=== COLUMN COUNT ===\n";
echo "SQL File columns: " . count($sql_columns) . "\n";
echo "Query columns: " . count($query_columns) . "\n";
echo "Difference: " . (count($query_columns) - count($sql_columns)) . "\n\n";

echo "=== COMPARISON ===\n";
$diff_in_query = array_diff($query_columns, $sql_columns);
$diff_in_sql = array_diff($sql_columns, $query_columns);

if (empty($diff_in_query) && empty($diff_in_sql)) {
    echo "✅ All columns MATCH!\n";
} else {
    if (!empty($diff_in_query)) {
        echo "❌ Columns in QUERY but NOT in SQL:\n";
        foreach ($diff_in_query as $col) {
            echo "  - $col\n";
        }
    }
    
    if (!empty($diff_in_sql)) {
        echo "\n❌ Columns in SQL but NOT in QUERY:\n";
        foreach ($diff_in_sql as $col) {
            echo "  - $col\n";
        }
    }
}

echo "\n=== SIDE BY SIDE (first 20) ===\n";
echo str_pad("SQL File", 40) . " | Query\n";
echo str_repeat("-", 85) . "\n";
for ($i = 0; $i < min(20, max(count($sql_columns), count($query_columns))); $i++) {
    $sql = $sql_columns[$i] ?? '(missing)';
    $query = $query_columns[$i] ?? '(missing)';
    $match = $sql === $query ? '✅' : '❌';
    echo str_pad($sql, 40) . " | " . str_pad($query, 40) . " $match\n";
}

if (count($sql_columns) > 20 || count($query_columns) > 20) {
    echo "... (showing first 20 only)\n";
}
?>
