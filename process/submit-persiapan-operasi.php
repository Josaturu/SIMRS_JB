<?php
// Submit Form Persiapan Operasi - Production Version
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil data form
        $formData = $_POST;
        
        // DEBUG: Tampilkan data keterangan yang dikirim
        /*
        echo "<h3>DEBUG: Data Keterangan yang Dikirim</h3><pre>";
        echo "ket12 (waktu_puasa): " . ($formData['ket12'] ?? 'KOSONG') . "\n";
        echo "ket14 (dc_no): " . ($formData['ket14'] ?? 'KOSONG') . "\n";
        echo "ket14_macam (dc_macam): " . ($formData['ket14_macam'] ?? 'KOSONG') . "\n";
        echo "ket20 (kantong_wb): " . ($formData['ket20'] ?? 'KOSONG') . "\n";
        echo "ket21 (kantong_prc): " . ($formData['ket21'] ?? 'KOSONG') . "\n";
        echo "ket22 (kantong_ffp): " . ($formData['ket22'] ?? 'KOSONG') . "\n";
        echo "ket24 (antibiotik): " . ($formData['ket24'] ?? 'KOSONG') . "\n";
        echo "ket24_waktu (jam): " . ($formData['ket24_waktu'] ?? 'KOSONG') . "\n";
        echo "ket28 (obat_lain): " . ($formData['ket28'] ?? 'KOSONG') . "\n";
        echo "ket30 (iv_catch_no): " . ($formData['ket30'] ?? 'KOSONG') . "\n";
        echo "ket31 (tekanan_darah): " . ($formData['ket31'] ?? 'KOSONG') . "\n";
        echo "ket32 (nadi): " . ($formData['ket32'] ?? 'KOSONG') . "\n";
        echo "ket33 (suhu): " . ($formData['ket33'] ?? 'KOSONG') . "\n";
        echo "ket34 (pernafasan): " . ($formData['ket34'] ?? 'KOSONG') . "\n";
        echo "ket36 (skin_test): " . ($formData['ket36'] ?? 'KOSONG') . "\n";
        echo "</pre>";
        die("DEBUG STOP - Hapus comment untuk lanjut");
        */
        
        // Helper function: convert radio "ya"/"tidak" to tinyint 1/0
        function radioToBool($value) {
            return ($value === 'ya') ? 1 : 0;
        }
        
        // Mapping 41 item checklist ke kolom database
        
        // ===== KOLOM UBS =====
        // Item 1-11: Administrasi
        $program_ke_ubs = radioToBool($formData['ubs1'] ?? '');
        $persetujuan_operasi = radioToBool($formData['ubs2'] ?? '');
        $rekam_medis = radioToBool($formData['ubs3'] ?? '');
        $laporan_operasi = radioToBool($formData['ubs4'] ?? '');
        $laporan_anestesi = radioToBool($formData['ubs5'] ?? '');
        $hasil_lab = radioToBool($formData['ubs6'] ?? '');
        $hasil_radiologi = radioToBool($formData['ubs7'] ?? '');
        $hasil_ct_scan = radioToBool($formData['ubs8'] ?? '');
        $hasil_usg = radioToBool($formData['ubs9'] ?? '');
        $hasil_ekg = radioToBool($formData['ubs10'] ?? '');
        $hasil_lain = radioToBool($formData['ubs11'] ?? '');
        
        // ===== KOLOM R. RAWAT =====
        // Item 1-11: Administrasi
        $rawat_program_ke_ubs = radioToBool($formData['rawat1'] ?? '');
        $rawat_persetujuan_operasi = radioToBool($formData['rawat2'] ?? '');
        $rawat_rekam_medis = radioToBool($formData['rawat3'] ?? '');
        $rawat_laporan_operasi = radioToBool($formData['rawat4'] ?? '');
        $rawat_laporan_anestesi = radioToBool($formData['rawat5'] ?? '');
        $rawat_hasil_lab = radioToBool($formData['rawat6'] ?? '');
        $rawat_hasil_radiologi = radioToBool($formData['rawat7'] ?? '');
        $rawat_hasil_ct_scan = radioToBool($formData['rawat8'] ?? '');
        $rawat_hasil_usg = radioToBool($formData['rawat9'] ?? '');
        $rawat_hasil_ekg = radioToBool($formData['rawat10'] ?? '');
        $rawat_hasil_lain = radioToBool($formData['rawat11'] ?? '');
        
        // Item 12-24: Fisik (UBS)
        $puasa = radioToBool($formData['ubs12'] ?? '');
        $waktu_puasa = !empty($formData['ket12']) ? date('Y-m-d H:i:s', strtotime($formData['ket12'])) : null;
        $lavement = radioToBool($formData['ubs13'] ?? '');
        $pasang_dc = radioToBool($formData['ubs14'] ?? '');
        // Item 14: DC No dan Macam
        $dc_no = !empty($formData['ket14']) ? (int)$formData['ket14'] : null;
        $dc_macam = $formData['ket14_macam'] ?? null;
        $cukur_daerah_operasi = radioToBool($formData['ubs15'] ?? '');
        $rambut_makeup_dibersihkan = radioToBool($formData['ubs16'] ?? '');
        $cat_kuku_dibersihkan = radioToBool($formData['ubs17'] ?? '');
        $perhiasan_dilepas = radioToBool($formData['ubs18'] ?? '');
        
        // Item 19: Persiapan darah untuk transfusi
        $transfusi_darah = radioToBool($formData['ubs19'] ?? '');
        // Item 20-22: WB, PRC, FFP
        $transfusi_whole_blood = radioToBool($formData['ubs20'] ?? '');
        $kantong_wb = !empty($formData['ket20']) ? (int)$formData['ket20'] : null;
        $transfusi_prc = radioToBool($formData['ubs21'] ?? '');
        $kantong_prc = !empty($formData['ket21']) ? (int)$formData['ket21'] : null;
        $transfusi_ffp = radioToBool($formData['ubs22'] ?? '');
        $kantong_ffp = !empty($formData['ket22']) ? (int)$formData['ket22'] : null;
        
        $premedikasi = radioToBool($formData['ubs23'] ?? '');
        // Item 24: Antibiotik - radio button + nama obat + jam pemberian
        $antibiotik = radioToBool($formData['ubs24'] ?? '');
        $antibiotik_preops = $formData['ket24'] ?? null;
        $jam_antibiotik = !empty($formData['ket24_waktu']) ? $formData['ket24_waktu'] : null;
        
        // Item 12-24: Fisik (R. RAWAT)
        $rawat_puasa = radioToBool($formData['rawat12'] ?? '');
        $rawat_lavement = radioToBool($formData['rawat13'] ?? '');
        $rawat_pasang_dc = radioToBool($formData['rawat14'] ?? '');
        $rawat_cukur_daerah_operasi = radioToBool($formData['rawat15'] ?? '');
        $rawat_rambut_makeup_dibersihkan = radioToBool($formData['rawat16'] ?? '');
        $rawat_cat_kuku_dibersihkan = radioToBool($formData['rawat17'] ?? '');
        $rawat_perhiasan_dilepas = radioToBool($formData['rawat18'] ?? '');
        $rawat_transfusi_darah = radioToBool($formData['rawat19'] ?? '');
        $rawat_transfusi_whole_blood = radioToBool($formData['rawat20'] ?? '');
        $rawat_transfusi_prc = radioToBool($formData['rawat21'] ?? '');
        $rawat_transfusi_ffp = radioToBool($formData['rawat22'] ?? '');
        $rawat_premedikasi = radioToBool($formData['rawat23'] ?? '');
        $rawat_antibiotik = radioToBool($formData['rawat24'] ?? '');
        
        // Item 25-41: Khusus (UBS)
        $dm_insulin_preop = radioToBool($formData['ubs25'] ?? '');
        $hipertensi_obat = radioToBool($formData['ubs26'] ?? '');
        $asma_obat = radioToBool($formData['ubs27'] ?? '');
        // Item 28: Obat Lain - radio button + keterangan
        $obat_lain_radio = radioToBool($formData['ubs28'] ?? '');
        $obat_lain = $formData['ket28'] ?? null;
        $obat_tidur = radioToBool($formData['ubs29'] ?? '');
        $pasang_infus = radioToBool($formData['ubs30'] ?? '');
        $iv_catch_no = $formData['ket30'] ?? null;
        // Item 31-34: Vital Signs - radio button + nilai
        $tekanan_darah_radio = radioToBool($formData['ubs31'] ?? '');
        $tekanan_darah = $formData['ket31'] ?? null;
        $nadi_radio = radioToBool($formData['ubs32'] ?? '');
        $nadi = $formData['ket32'] ?? null;
        $suhu_radio = radioToBool($formData['ubs33'] ?? '');
        $suhu = !empty($formData['ket33']) ? (float)$formData['ket33'] : null;
        $pernafasan_radio = radioToBool($formData['ubs34'] ?? '');
        $pernafasan = $formData['ket34'] ?? null;
        $obat_ubs = radioToBool($formData['ubs35'] ?? '');
        // Item 36: Skin Test - radio button + hasil
        $skin_test_radio = radioToBool($formData['ubs36'] ?? '');
        $hasil_skin_test = isset($formData['ket36']) ? (($formData['ket36'] === 'positif') ? 'Positif' : (($formData['ket36'] === 'negatif') ? 'Negatif' : null)) : null;
        $visit_dokter_bedah = radioToBool($formData['ubs37'] ?? '');
        $visit_dokter_anestesi = radioToBool($formData['ubs38'] ?? '');
        $visit_dokter_konsul_1 = radioToBool($formData['ubs39'] ?? '');
        $visit_dokter_konsul_2 = radioToBool($formData['ubs40'] ?? '');
        $visit_dokter_konsul_3 = radioToBool($formData['ubs41'] ?? '');
        
        // Item 25-41: Khusus (R. RAWAT)
        $rawat_dm_insulin_preop = radioToBool($formData['rawat25'] ?? '');
        $rawat_hipertensi_obat = radioToBool($formData['rawat26'] ?? '');
        $rawat_asma_obat = radioToBool($formData['rawat27'] ?? '');
        $rawat_obat_lain_radio = radioToBool($formData['rawat28'] ?? '');
        $rawat_obat_tidur = radioToBool($formData['rawat29'] ?? '');
        $rawat_pasang_infus = radioToBool($formData['rawat30'] ?? '');
        $rawat_tekanan_darah_radio = radioToBool($formData['rawat31'] ?? '');
        $rawat_nadi_radio = radioToBool($formData['rawat32'] ?? '');
        $rawat_suhu_radio = radioToBool($formData['rawat33'] ?? '');
        $rawat_pernafasan_radio = radioToBool($formData['rawat34'] ?? '');
        $rawat_obat_ubs = radioToBool($formData['rawat35'] ?? '');
        $rawat_skin_test_radio = radioToBool($formData['rawat36'] ?? '');
        $rawat_visit_dokter_bedah = radioToBool($formData['rawat37'] ?? '');
        $rawat_visit_dokter_anestesi = radioToBool($formData['rawat38'] ?? '');
        $rawat_visit_dokter_konsul_1 = radioToBool($formData['rawat39'] ?? '');
        $rawat_visit_dokter_konsul_2 = radioToBool($formData['rawat40'] ?? '');
        $rawat_visit_dokter_konsul_3 = radioToBool($formData['rawat41'] ?? '');
        
        // Cek apakah data sudah ada
        $check_query = "SELECT id FROM tbl_anestesi_persiapan_operasi 
                        WHERE no_rawat = ? AND kode_paket = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$formData['no_rawat'], $formData['kode_paket']]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // UPDATE existing data
            $query = "UPDATE tbl_anestesi_persiapan_operasi SET
                      no_rm = :no_rm, nama = :nama, jenis_kelamin = :jenis_kelamin,
                      umur = :umur, tanggal_lahir = :tanggal_lahir,
                      tanggal_operasi = :tanggal_operasi, macam_operasi = :macam_operasi,
                      dpjp = :dpjp,
                      tinggi_badan = :tinggi_badan, berat_badan = :berat_badan, 
                      gol_darah = :gol_darah, riwayat_alergi = :riwayat_alergi,
                      program_ke_ubs = :program_ke_ubs, rawat_program_ke_ubs = :rawat_program_ke_ubs,
                      persetujuan_operasi = :persetujuan_operasi, rawat_persetujuan_operasi = :rawat_persetujuan_operasi,
                      rekam_medis = :rekam_medis, rawat_rekam_medis = :rawat_rekam_medis,
                      laporan_operasi = :laporan_operasi, rawat_laporan_operasi = :rawat_laporan_operasi,
                      laporan_anestesi = :laporan_anestesi, rawat_laporan_anestesi = :rawat_laporan_anestesi,
                      hasil_lab = :hasil_lab, rawat_hasil_lab = :rawat_hasil_lab,
                      hasil_radiologi = :hasil_radiologi, rawat_hasil_radiologi = :rawat_hasil_radiologi,
                      hasil_ct_scan = :hasil_ct_scan, rawat_hasil_ct_scan = :rawat_hasil_ct_scan,
                      hasil_usg = :hasil_usg, rawat_hasil_usg = :rawat_hasil_usg,
                      hasil_ekg = :hasil_ekg, rawat_hasil_ekg = :rawat_hasil_ekg,
                      hasil_lain = :hasil_lain, rawat_hasil_lain = :rawat_hasil_lain,
                      puasa = :puasa, rawat_puasa = :rawat_puasa,
                      waktu_puasa = :waktu_puasa,
                      lavement = :lavement, rawat_lavement = :rawat_lavement,
                      pasang_dc = :pasang_dc, rawat_pasang_dc = :rawat_pasang_dc,
                      dc_no = :dc_no, dc_macam = :dc_macam,
                      cukur_daerah_operasi = :cukur_daerah_operasi, rawat_cukur_daerah_operasi = :rawat_cukur_daerah_operasi,
                      rambut_makeup_dibersihkan = :rambut_makeup_dibersihkan, rawat_rambut_makeup_dibersihkan = :rawat_rambut_makeup_dibersihkan,
                      cat_kuku_dibersihkan = :cat_kuku_dibersihkan, rawat_cat_kuku_dibersihkan = :rawat_cat_kuku_dibersihkan,
                      perhiasan_dilepas = :perhiasan_dilepas, rawat_perhiasan_dilepas = :rawat_perhiasan_dilepas,
                      transfusi_darah = :transfusi_darah, rawat_transfusi_darah = :rawat_transfusi_darah,
                      transfusi_whole_blood = :transfusi_whole_blood, rawat_transfusi_whole_blood = :rawat_transfusi_whole_blood,
                      kantong_wb = :kantong_wb,
                      transfusi_prc = :transfusi_prc, rawat_transfusi_prc = :rawat_transfusi_prc,
                      kantong_prc = :kantong_prc,
                      transfusi_ffp = :transfusi_ffp, rawat_transfusi_ffp = :rawat_transfusi_ffp,
                      kantong_ffp = :kantong_ffp,
                      premedikasi = :premedikasi, rawat_premedikasi = :rawat_premedikasi,
                      antibiotik = :antibiotik, rawat_antibiotik = :rawat_antibiotik,
                      jam_antibiotik = :jam_antibiotik,
                      dm_insulin_preop = :dm_insulin_preop, rawat_dm_insulin_preop = :rawat_dm_insulin_preop,
                      hipertensi_obat = :hipertensi_obat, rawat_hipertensi_obat = :rawat_hipertensi_obat,
                      asma_obat = :asma_obat, rawat_asma_obat = :rawat_asma_obat,
                      obat_lain_radio = :obat_lain_radio, rawat_obat_lain_radio = :rawat_obat_lain_radio,
                      obat_lain = :obat_lain,
                      obat_tidur = :obat_tidur, rawat_obat_tidur = :rawat_obat_tidur,
                      pasang_infus = :pasang_infus, rawat_pasang_infus = :rawat_pasang_infus,
                      iv_catch_no = :iv_catch_no,
                      tekanan_darah_radio = :tekanan_darah_radio, rawat_tekanan_darah_radio = :rawat_tekanan_darah_radio,
                      tekanan_darah = :tekanan_darah,
                      nadi_radio = :nadi_radio, rawat_nadi_radio = :rawat_nadi_radio,
                      nadi = :nadi,
                      suhu_radio = :suhu_radio, rawat_suhu_radio = :rawat_suhu_radio,
                      suhu = :suhu,
                      pernafasan_radio = :pernafasan_radio, rawat_pernafasan_radio = :rawat_pernafasan_radio,
                      pernafasan = :pernafasan,
                      obat_ubs = :obat_ubs, rawat_obat_ubs = :rawat_obat_ubs,
                      skin_test_radio = :skin_test_radio, rawat_skin_test_radio = :rawat_skin_test_radio,
                      hasil_skin_test = :hasil_skin_test,
                      visit_dokter_bedah = :visit_dokter_bedah, rawat_visit_dokter_bedah = :rawat_visit_dokter_bedah,
                      visit_dokter_anestesi = :visit_dokter_anestesi, rawat_visit_dokter_anestesi = :rawat_visit_dokter_anestesi,
                      visit_dokter_konsul_1 = :visit_dokter_konsul_1, rawat_visit_dokter_konsul_1 = :rawat_visit_dokter_konsul_1,
                      visit_dokter_konsul_2 = :visit_dokter_konsul_2, rawat_visit_dokter_konsul_2 = :rawat_visit_dokter_konsul_2,
                      visit_dokter_konsul_3 = :visit_dokter_konsul_3, rawat_visit_dokter_konsul_3 = :rawat_visit_dokter_konsul_3
                      WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket";
        } else {
            // INSERT new data
            $query = "INSERT INTO tbl_anestesi_persiapan_operasi 
                  (no_rawat, kode_paket, 
                   no_rm, nama, jenis_kelamin, umur, tanggal_lahir,
                   tanggal_operasi, macam_operasi, dpjp,
                   tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
                   program_ke_ubs, rawat_program_ke_ubs,
                   persetujuan_operasi, rawat_persetujuan_operasi,
                   rekam_medis, rawat_rekam_medis,
                   laporan_operasi, rawat_laporan_operasi,
                   laporan_anestesi, rawat_laporan_anestesi,
                   hasil_lab, rawat_hasil_lab,
                   hasil_radiologi, rawat_hasil_radiologi,
                   hasil_ct_scan, rawat_hasil_ct_scan,
                   hasil_usg, rawat_hasil_usg,
                   hasil_ekg, rawat_hasil_ekg,
                   hasil_lain, rawat_hasil_lain,
                   puasa, rawat_puasa, waktu_puasa,
                   lavement, rawat_lavement,
                   pasang_dc, rawat_pasang_dc, dc_no, dc_macam,
                   cukur_daerah_operasi, rawat_cukur_daerah_operasi,
                   rambut_makeup_dibersihkan, rawat_rambut_makeup_dibersihkan,
                   cat_kuku_dibersihkan, rawat_cat_kuku_dibersihkan,
                   perhiasan_dilepas, rawat_perhiasan_dilepas,
                   transfusi_darah, rawat_transfusi_darah,
                   transfusi_whole_blood, rawat_transfusi_whole_blood, kantong_wb,
                   transfusi_prc, rawat_transfusi_prc, kantong_prc,
                   transfusi_ffp, rawat_transfusi_ffp, kantong_ffp,
                   premedikasi, rawat_premedikasi,
                   antibiotik, rawat_antibiotik,
                   antibiotik_preops, jam_antibiotik,
                   dm_insulin_preop, rawat_dm_insulin_preop,
                   hipertensi_obat, rawat_hipertensi_obat,
                   asma_obat, rawat_asma_obat,
                   obat_lain_radio, rawat_obat_lain_radio, obat_lain,
                   obat_tidur, rawat_obat_tidur,
                   pasang_infus, rawat_pasang_infus, iv_catch_no,
                   tekanan_darah_radio, rawat_tekanan_darah_radio, tekanan_darah,
                   nadi_radio, rawat_nadi_radio, nadi,
                   suhu_radio, rawat_suhu_radio, suhu,
                   pernafasan_radio, rawat_pernafasan_radio, pernafasan,
                   obat_ubs, rawat_obat_ubs,
                   skin_test_radio, rawat_skin_test_radio, hasil_skin_test,
                   visit_dokter_bedah, rawat_visit_dokter_bedah,
                   visit_dokter_anestesi, rawat_visit_dokter_anestesi,
                   visit_dokter_konsul_1, rawat_visit_dokter_konsul_1,
                   visit_dokter_konsul_2, rawat_visit_dokter_konsul_2,
                   visit_dokter_konsul_3, rawat_visit_dokter_konsul_3)
                  VALUES 
                  (:no_rawat, :kode_paket, 
                   :no_rm, :nama, :jenis_kelamin, :umur, :tanggal_lahir,
                   :tanggal_operasi, :macam_operasi, :dpjp,
                   :tinggi_badan, :berat_badan, :gol_darah, :riwayat_alergi,
                   :program_ke_ubs, :rawat_program_ke_ubs,
                   :persetujuan_operasi, :rawat_persetujuan_operasi,
                   :rekam_medis, :rawat_rekam_medis,
                   :laporan_operasi, :rawat_laporan_operasi,
                   :laporan_anestesi, :rawat_laporan_anestesi,
                   :hasil_lab, :rawat_hasil_lab,
                   :hasil_radiologi, :rawat_hasil_radiologi,
                   :hasil_ct_scan, :rawat_hasil_ct_scan,
                   :hasil_usg, :rawat_hasil_usg,
                   :hasil_ekg, :rawat_hasil_ekg,
                   :hasil_lain, :rawat_hasil_lain,
                   :puasa, :rawat_puasa, :waktu_puasa,
                   :lavement, :rawat_lavement,
                   :pasang_dc, :rawat_pasang_dc, :dc_no, :dc_macam,
                   :cukur_daerah_operasi, :rawat_cukur_daerah_operasi,
                   :rambut_makeup_dibersihkan, :rawat_rambut_makeup_dibersihkan,
                   :cat_kuku_dibersihkan, :rawat_cat_kuku_dibersihkan,
                   :perhiasan_dilepas, :rawat_perhiasan_dilepas,
                   :transfusi_darah, :rawat_transfusi_darah,
                   :transfusi_whole_blood, :rawat_transfusi_whole_blood, :kantong_wb,
                   :transfusi_prc, :rawat_transfusi_prc, :kantong_prc,
                   :transfusi_ffp, :rawat_transfusi_ffp, :kantong_ffp,
                   :premedikasi, :rawat_premedikasi,
                   :antibiotik, :rawat_antibiotik,
                   :antibiotik_preops, :jam_antibiotik,
                   :dm_insulin_preop, :rawat_dm_insulin_preop,
                   :hipertensi_obat, :rawat_hipertensi_obat,
                   :asma_obat, :rawat_asma_obat,
                   :obat_lain_radio, :rawat_obat_lain_radio, :obat_lain,
                   :obat_tidur, :rawat_obat_tidur,
                   :pasang_infus, :rawat_pasang_infus, :iv_catch_no,
                   :tekanan_darah_radio, :rawat_tekanan_darah_radio, :tekanan_darah,
                   :nadi_radio, :rawat_nadi_radio, :nadi,
                   :suhu_radio, :rawat_suhu_radio, :suhu,
                   :pernafasan_radio, :rawat_pernafasan_radio, :pernafasan,
                   :obat_ubs, :rawat_obat_ubs,
                   :skin_test_radio, :rawat_skin_test_radio, :hasil_skin_test,
                   :visit_dokter_bedah, :rawat_visit_dokter_bedah,
                   :visit_dokter_anestesi, :rawat_visit_dokter_anestesi,
                   :visit_dokter_konsul_1, :rawat_visit_dokter_konsul_1,
                   :visit_dokter_konsul_2, :rawat_visit_dokter_konsul_2,
                   :visit_dokter_konsul_3, :rawat_visit_dokter_konsul_3)";
        }
        
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($db->errorInfo(), true));
        }
        
        // Bind parameters
        $stmt->bindParam(':no_rawat', $formData['no_rawat']);
        $stmt->bindParam(':kode_paket', $formData['kode_paket']);
        
        // Bind data pasien (dari hidden fields)
        $stmt->bindParam(':no_rm', $formData['no_rm']);
        $stmt->bindParam(':nama', $formData['nama']);
        $stmt->bindParam(':jenis_kelamin', $formData['jenis_kelamin']);
        $stmt->bindParam(':umur', $formData['umur']);
        $stmt->bindParam(':tanggal_lahir', $formData['tanggal_lahir']);
        
        $stmt->bindParam(':tanggal_operasi', $formData['tglOperasi']);
        $stmt->bindParam(':macam_operasi', $formData['macamOperasi']);
        $stmt->bindParam(':dpjp', $formData['dpjp']);
        $stmt->bindParam(':tinggi_badan', $formData['tinggiBadan']);
        $stmt->bindParam(':berat_badan', $formData['beratBadan']);
        $stmt->bindParam(':gol_darah', $formData['GolDarah']);
        $stmt->bindParam(':riwayat_alergi', $formData['riwayatAlergi']);
        
        // Bind UBS parameters
        $stmt->bindParam(':program_ke_ubs', $program_ke_ubs);
        $stmt->bindParam(':persetujuan_operasi', $persetujuan_operasi);
        $stmt->bindParam(':rekam_medis', $rekam_medis);
        $stmt->bindParam(':laporan_operasi', $laporan_operasi);
        $stmt->bindParam(':laporan_anestesi', $laporan_anestesi);
        $stmt->bindParam(':hasil_lab', $hasil_lab);
        $stmt->bindParam(':hasil_radiologi', $hasil_radiologi);
        $stmt->bindParam(':hasil_ct_scan', $hasil_ct_scan);
        $stmt->bindParam(':hasil_usg', $hasil_usg);
        $stmt->bindParam(':hasil_ekg', $hasil_ekg);
        $stmt->bindParam(':hasil_lain', $hasil_lain);
        $stmt->bindParam(':puasa', $puasa);
        $stmt->bindParam(':waktu_puasa', $waktu_puasa);
        $stmt->bindParam(':lavement', $lavement);
        $stmt->bindParam(':pasang_dc', $pasang_dc);
        $stmt->bindParam(':dc_no', $dc_no);
        $stmt->bindParam(':dc_macam', $dc_macam);
        $stmt->bindParam(':cukur_daerah_operasi', $cukur_daerah_operasi);
        $stmt->bindParam(':rambut_makeup_dibersihkan', $rambut_makeup_dibersihkan);
        $stmt->bindParam(':cat_kuku_dibersihkan', $cat_kuku_dibersihkan);
        $stmt->bindParam(':perhiasan_dilepas', $perhiasan_dilepas);
        $stmt->bindParam(':transfusi_darah', $transfusi_darah);
        $stmt->bindParam(':transfusi_whole_blood', $transfusi_whole_blood);
        $stmt->bindParam(':kantong_wb', $kantong_wb);
        $stmt->bindParam(':transfusi_prc', $transfusi_prc);
        $stmt->bindParam(':kantong_prc', $kantong_prc);
        $stmt->bindParam(':transfusi_ffp', $transfusi_ffp);
        $stmt->bindParam(':kantong_ffp', $kantong_ffp);
        $stmt->bindParam(':premedikasi', $premedikasi);
        $stmt->bindParam(':antibiotik', $antibiotik);
        if (!$existing) {
            $stmt->bindParam(':antibiotik_preops', $antibiotik_preops);
        }
        $stmt->bindParam(':jam_antibiotik', $jam_antibiotik);
        $stmt->bindParam(':dm_insulin_preop', $dm_insulin_preop);
        $stmt->bindParam(':hipertensi_obat', $hipertensi_obat);
        $stmt->bindParam(':asma_obat', $asma_obat);
        $stmt->bindParam(':obat_lain_radio', $obat_lain_radio);
        $stmt->bindParam(':obat_lain', $obat_lain);
        $stmt->bindParam(':obat_tidur', $obat_tidur);
        $stmt->bindParam(':pasang_infus', $pasang_infus);
        $stmt->bindParam(':iv_catch_no', $iv_catch_no);
        $stmt->bindParam(':tekanan_darah_radio', $tekanan_darah_radio);
        $stmt->bindParam(':tekanan_darah', $tekanan_darah);
        $stmt->bindParam(':nadi_radio', $nadi_radio);
        $stmt->bindParam(':nadi', $nadi);
        $stmt->bindParam(':suhu_radio', $suhu_radio);
        $stmt->bindParam(':suhu', $suhu);
        $stmt->bindParam(':pernafasan_radio', $pernafasan_radio);
        $stmt->bindParam(':pernafasan', $pernafasan);
        $stmt->bindParam(':obat_ubs', $obat_ubs);
        $stmt->bindParam(':skin_test_radio', $skin_test_radio);
        $stmt->bindParam(':hasil_skin_test', $hasil_skin_test);
        $stmt->bindParam(':visit_dokter_bedah', $visit_dokter_bedah);
        $stmt->bindParam(':visit_dokter_anestesi', $visit_dokter_anestesi);
        $stmt->bindParam(':visit_dokter_konsul_1', $visit_dokter_konsul_1);
        $stmt->bindParam(':visit_dokter_konsul_2', $visit_dokter_konsul_2);
        $stmt->bindParam(':visit_dokter_konsul_3', $visit_dokter_konsul_3);
        
        // Bind R. RAWAT parameters (28 kolom)
        $stmt->bindParam(':rawat_program_ke_ubs', $rawat_program_ke_ubs);
        $stmt->bindParam(':rawat_persetujuan_operasi', $rawat_persetujuan_operasi);
        $stmt->bindParam(':rawat_rekam_medis', $rawat_rekam_medis);
        $stmt->bindParam(':rawat_laporan_operasi', $rawat_laporan_operasi);
        $stmt->bindParam(':rawat_laporan_anestesi', $rawat_laporan_anestesi);
        $stmt->bindParam(':rawat_hasil_lab', $rawat_hasil_lab);
        $stmt->bindParam(':rawat_hasil_radiologi', $rawat_hasil_radiologi);
        $stmt->bindParam(':rawat_hasil_ct_scan', $rawat_hasil_ct_scan);
        $stmt->bindParam(':rawat_hasil_usg', $rawat_hasil_usg);
        $stmt->bindParam(':rawat_hasil_ekg', $rawat_hasil_ekg);
        $stmt->bindParam(':rawat_hasil_lain', $rawat_hasil_lain);
        $stmt->bindParam(':rawat_puasa', $rawat_puasa);
        $stmt->bindParam(':rawat_lavement', $rawat_lavement);
        $stmt->bindParam(':rawat_pasang_dc', $rawat_pasang_dc);
        $stmt->bindParam(':rawat_cukur_daerah_operasi', $rawat_cukur_daerah_operasi);
        $stmt->bindParam(':rawat_rambut_makeup_dibersihkan', $rawat_rambut_makeup_dibersihkan);
        $stmt->bindParam(':rawat_cat_kuku_dibersihkan', $rawat_cat_kuku_dibersihkan);
        $stmt->bindParam(':rawat_perhiasan_dilepas', $rawat_perhiasan_dilepas);
        $stmt->bindParam(':rawat_transfusi_darah', $rawat_transfusi_darah);
        $stmt->bindParam(':rawat_transfusi_whole_blood', $rawat_transfusi_whole_blood);
        $stmt->bindParam(':rawat_transfusi_prc', $rawat_transfusi_prc);
        $stmt->bindParam(':rawat_transfusi_ffp', $rawat_transfusi_ffp);
        $stmt->bindParam(':rawat_premedikasi', $rawat_premedikasi);
        $stmt->bindParam(':rawat_antibiotik', $rawat_antibiotik);
        $stmt->bindParam(':rawat_dm_insulin_preop', $rawat_dm_insulin_preop);
        $stmt->bindParam(':rawat_hipertensi_obat', $rawat_hipertensi_obat);
        $stmt->bindParam(':rawat_asma_obat', $rawat_asma_obat);
        $stmt->bindParam(':rawat_obat_lain_radio', $rawat_obat_lain_radio);
        $stmt->bindParam(':rawat_obat_tidur', $rawat_obat_tidur);
        $stmt->bindParam(':rawat_pasang_infus', $rawat_pasang_infus);
        $stmt->bindParam(':rawat_tekanan_darah_radio', $rawat_tekanan_darah_radio);
        $stmt->bindParam(':rawat_nadi_radio', $rawat_nadi_radio);
        $stmt->bindParam(':rawat_suhu_radio', $rawat_suhu_radio);
        $stmt->bindParam(':rawat_pernafasan_radio', $rawat_pernafasan_radio);
        $stmt->bindParam(':rawat_obat_ubs', $rawat_obat_ubs);
        $stmt->bindParam(':rawat_skin_test_radio', $rawat_skin_test_radio);
        $stmt->bindParam(':rawat_visit_dokter_bedah', $rawat_visit_dokter_bedah);
        $stmt->bindParam(':rawat_visit_dokter_anestesi', $rawat_visit_dokter_anestesi);
        $stmt->bindParam(':rawat_visit_dokter_konsul_1', $rawat_visit_dokter_konsul_1);
        $stmt->bindParam(':rawat_visit_dokter_konsul_2', $rawat_visit_dokter_konsul_2);
        $stmt->bindParam(':rawat_visit_dokter_konsul_3', $rawat_visit_dokter_konsul_3);
        
        // Execute query
        if ($stmt->execute()) {
            // Redirect with URL params (not SESSION) for global notification
            $action = $existing ? 'updated' : 'saved';
            header("Location: ../index.php?page=persiapan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=sukses&action={$action}");
        } else {
            $errorInfo = $stmt->errorInfo();
            header("Location: ../index.php?page=persiapan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=gagal&error=" . urlencode($errorInfo[2]));
        }
        
    } catch (Exception $e) {
        error_log("Error submitting persiapan operasi: " . $e->getMessage());
        header("Location: ../index.php?page=persiapan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=error&msg=" . urlencode($e->getMessage()));
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>
