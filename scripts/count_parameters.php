<?php
// Script untuk menghitung jumlah kolom dan placeholder

$columns = [
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

$parameters = [
    '$no_rawat', '$kode_paket', '$tanggal', '$jam_mulai', '$ruang_perawatan', '$dokter_merawat',
    '$tanggal_konsul', '$jam_konsul', '$tinggi_badan', '$berat_badan', '$diagnosa_pra_operasi', '$jenis_diagnosa',
    '$rencana_tindakan_operasi', '$kondisi_khusus', '$tanggal_dibuat', '$jam_visit', '$menikah',
    '$jenis_kelamin', '$merokok', '$alkohol', '$has_pengobatan', '$pengobatan', '$daftar_alergi_obat', '$has_alergi_obat',
    '$alergi_makanan', '$alergi_lateks', '$tidak_alergi', '$komunikasi', '$komunikasi_lainnya',
    '$asma', '$hepatitis', '$sesak_nafas', '$pingsan', '$sumbatan_jalan_nafas', '$diabetes',
    '$tidur_mengorok', '$anemia', '$serangan_jantung', '$sakit_maag', '$hipertensi', '$pendarahan',
    '$stroke', '$pembekuan_darah', '$kejang', '$penyakit_berat_lainnya', '$penyakit_terpilih',
    '$gigi_palsu', '$makan_terakhir', '$riwayat_operasi', '$jenis_anestesi', '$terakhir_periksa',
    '$tempat_periksa_terakhir', '$penyakit_gangguan', '$jumlah_kehamilan', '$jumlah_anak',
    '$menyusui', '$kesadaran', '$tb', '$bb', '$td', '$nadi', '$rr', '$suhu', '$skrining_nyeri', '$jalan_nafas',
    '$gerakan_leher', '$gerakan_leher_keterangan', '$paru_paru', '$jantung', '$abdomen', '$ekstrimitas',
    '$neurologi', '$lain_lain', '$hb_ht_al_at', '$na_k_cl', '$ureum', '$ct_bt', '$kreatin', '$ekg', '$ro_dada',
    '$echo', '$lain_lain_pemeriksaan', '$asa_status', '$emergency', '$rekomendasi_anestesi',
    '$anestesi_umum', '$regional_anestesi', '$kombinasi_anestesi', '$sedasi', '$saran',
    '$puasa_mulai_jam', '$puasa_mulai_tanggal', '$rencana_tiba_jam', '$rencana_tiba_tanggal',
    '$rencana_operasi_jam', '$rencana_operasi_tanggal'
];

echo "=== PARAMETER COUNT ===\n";
echo "Total Columns: " . count($columns) . "\n";
echo "Total Parameters: " . count($parameters) . "\n";
echo "\n";

// Generate placeholder string
$placeholders = str_repeat('?, ', count($columns) - 1) . '?';
echo "Placeholders needed: " . substr_count($placeholders, '?') . "\n";
echo "\n";

// Generate VALUES line
echo "VALUES line:\n";
$chunks = array_chunk(range(1, count($columns)), 28);
foreach ($chunks as $i => $chunk) {
    if ($i == 0) {
        echo "              VALUES (";
    } else {
        echo "                      ";
    }
    echo str_repeat('?, ', count($chunk) - 1) . '?';
    if ($i < count($chunks) - 1) {
        echo ",\n";
    } else {
        echo ")\n";
    }
}
echo "\n";

// List all columns
echo "=== ALL COLUMNS ===\n";
foreach ($columns as $i => $col) {
    echo ($i + 1) . ". $col\n";
}
?>
