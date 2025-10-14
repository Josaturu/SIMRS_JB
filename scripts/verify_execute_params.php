<?php
// Script untuk menghitung parameter di execute()

$execute_params = [
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

echo "=== EXECUTE PARAMETERS COUNT ===\n";
echo "Total Parameters in execute(): " . count($execute_params) . "\n\n";

echo "=== LIST OF PARAMETERS ===\n";
foreach ($execute_params as $i => $param) {
    echo ($i + 1) . ". $param\n";
}

echo "\n=== EXPECTED ===\n";
echo "Columns in INSERT: 96\n";
echo "Placeholders (?): 96\n";
echo "Parameters in execute(): " . count($execute_params) . "\n";

if (count($execute_params) == 96) {
    echo "\n✅ MATCH! All counts are 96\n";
} else {
    echo "\n❌ MISMATCH! Execute has " . count($execute_params) . " parameters, expected 96\n";
}
?>
