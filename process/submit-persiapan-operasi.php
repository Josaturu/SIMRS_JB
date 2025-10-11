<?php
// Submit Form Persiapan Operasi - Production Version
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil data form
        $formData = $_POST;
        
        // Helper function: convert radio "ya"/"tidak" to tinyint 1/0
        function radioToBool($value) {
            return ($value === 'ya') ? 1 : 0;
        }
        
        // Mapping 41 item checklist ke kolom database
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
        
        // Item 12-24: Fisik
        $puasa = radioToBool($formData['ubs12'] ?? '');
        $waktu_puasa = !empty($formData['ket12']) ? date('Y-m-d H:i:s', strtotime($formData['ket12'])) : null;
        $lavement = radioToBool($formData['ubs13'] ?? '');
        $pasang_dc = radioToBool($formData['ubs14'] ?? '');
        $cukur_daerah_operasi = radioToBool($formData['ubs15'] ?? '');
        $rambut_makeup_dibersihkan = radioToBool($formData['ubs16'] ?? '');
        $perhiasan_dilepas = radioToBool($formData['ubs17'] ?? '');
        
        // Item 18: Persiapan darah
        $transfusi_darah = radioToBool($formData['ubs18'] ?? '');
        // Item 19-21: WB, PRC, FFP
        $transfusi_whole_blood = radioToBool($formData['ubs19'] ?? '');
        $kantong_wb = !empty($formData['ket19']) ? (int)$formData['ket19'] : null;
        $transfusi_prc = radioToBool($formData['ubs20'] ?? '');
        $kantong_prc = !empty($formData['ket20']) ? (int)$formData['ket20'] : null;
        $transfusi_ffp = radioToBool($formData['ubs21'] ?? '');
        $kantong_ffp = !empty($formData['ket21']) ? (int)$formData['ket21'] : null;
        
        $premedikasi = radioToBool($formData['ubs22'] ?? '');
        $antibiotik_preops = $formData['ket23'] ?? null;
        $jam_antibiotik = !empty($formData['ket24_waktu']) ? $formData['ket24_waktu'] : null;
        
        // Item 25-41: Khusus
        $dm_insulin_preop = radioToBool($formData['ubs25'] ?? '');
        $hipertensi_obat = radioToBool($formData['ubs26'] ?? '');
        $asma_obat = radioToBool($formData['ubs27'] ?? '');
        $obat_lain = $formData['ket28'] ?? null;
        $obat_tidur = radioToBool($formData['ubs29'] ?? '');
        $pasang_infus = radioToBool($formData['ubs30'] ?? '');
        $iv_catch_no = $formData['ket30'] ?? null;
        $tekanan_darah = $formData['ket31'] ?? null;
        $nadi = $formData['ket32'] ?? null;
        $suhu = !empty($formData['ket33']) ? (float)$formData['ket33'] : null;
        $pernafasan = $formData['ket34'] ?? null;
        $obat_ubs = radioToBool($formData['ubs35'] ?? '');
        $hasil_skin_test = isset($formData['ket36']) ? (($formData['ket36'] === 'positif') ? 'Positif' : (($formData['ket36'] === 'negatif') ? 'Negatif' : null)) : null;
        $visit_dokter_bedah = radioToBool($formData['ubs37'] ?? '');
        $visit_dokter_anestesi = radioToBool($formData['ubs38'] ?? '');
        
        // Prepare query - tanpa ID karena AUTO_INCREMENT
        $query = "INSERT INTO tbl_anestesi_persiapan_operasi 
                  (no_rawat, kode_paket, tanggal_operasi, macam_operasi,
                   tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
                   program_ke_ubs, persetujuan_operasi, rekam_medis, laporan_operasi,
                   laporan_anestesi, hasil_lab, hasil_radiologi, hasil_ct_scan,
                   hasil_usg, hasil_ekg, hasil_lain, puasa, waktu_puasa, lavement,
                   pasang_dc, cukur_daerah_operasi, rambut_makeup_dibersihkan,
                   perhiasan_dilepas, transfusi_whole_blood, kantong_wb,
                   transfusi_prc, kantong_prc, transfusi_ffp, kantong_ffp,
                   premedikasi, antibiotik_preops, jam_antibiotik,
                   dm_insulin_preop, hipertensi_obat, asma_obat, obat_lain,
                   obat_tidur, pasang_infus, iv_catch_no, tekanan_darah,
                   nadi, suhu, pernafasan, obat_ubs, hasil_skin_test,
                   visit_dokter_bedah, visit_dokter_anestesi)
                  VALUES 
                  (:no_rawat, :kode_paket, :tanggal_operasi, :macam_operasi,
                   :tinggi_badan, :berat_badan, :gol_darah, :riwayat_alergi,
                   :program_ke_ubs, :persetujuan_operasi, :rekam_medis, :laporan_operasi,
                   :laporan_anestesi, :hasil_lab, :hasil_radiologi, :hasil_ct_scan,
                   :hasil_usg, :hasil_ekg, :hasil_lain, :puasa, :waktu_puasa, :lavement,
                   :pasang_dc, :cukur_daerah_operasi, :rambut_makeup_dibersihkan,
                   :perhiasan_dilepas, :transfusi_whole_blood, :kantong_wb,
                   :transfusi_prc, :kantong_prc, :transfusi_ffp, :kantong_ffp,
                   :premedikasi, :antibiotik_preops, :jam_antibiotik,
                   :dm_insulin_preop, :hipertensi_obat, :asma_obat, :obat_lain,
                   :obat_tidur, :pasang_infus, :iv_catch_no, :tekanan_darah,
                   :nadi, :suhu, :pernafasan, :obat_ubs, :hasil_skin_test,
                   :visit_dokter_bedah, :visit_dokter_anestesi)";
        
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($db->errorInfo(), true));
        }
        
        // Bind parameters
        $stmt->bindParam(':no_rawat', $formData['no_rawat']);
        $stmt->bindParam(':kode_paket', $formData['kode_paket']);
        $stmt->bindParam(':tanggal_operasi', $formData['tglOperasi']);
        $stmt->bindParam(':macam_operasi', $formData['macamOperasi']);
        $stmt->bindParam(':tinggi_badan', $formData['tinggiBadan']);
        $stmt->bindParam(':berat_badan', $formData['beratBadan']);
        $stmt->bindParam(':gol_darah', $formData['GolDarah']);
        $stmt->bindParam(':riwayat_alergi', $formData['riwayatAlergi']);
        
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
        $stmt->bindParam(':cukur_daerah_operasi', $cukur_daerah_operasi);
        $stmt->bindParam(':rambut_makeup_dibersihkan', $rambut_makeup_dibersihkan);
        $stmt->bindParam(':perhiasan_dilepas', $perhiasan_dilepas);
        $stmt->bindParam(':transfusi_whole_blood', $transfusi_whole_blood);
        $stmt->bindParam(':kantong_wb', $kantong_wb);
        $stmt->bindParam(':transfusi_prc', $transfusi_prc);
        $stmt->bindParam(':kantong_prc', $kantong_prc);
        $stmt->bindParam(':transfusi_ffp', $transfusi_ffp);
        $stmt->bindParam(':kantong_ffp', $kantong_ffp);
        $stmt->bindParam(':premedikasi', $premedikasi);
        $stmt->bindParam(':antibiotik_preops', $antibiotik_preops);
        $stmt->bindParam(':jam_antibiotik', $jam_antibiotik);
        $stmt->bindParam(':dm_insulin_preop', $dm_insulin_preop);
        $stmt->bindParam(':hipertensi_obat', $hipertensi_obat);
        $stmt->bindParam(':asma_obat', $asma_obat);
        $stmt->bindParam(':obat_lain', $obat_lain);
        $stmt->bindParam(':obat_tidur', $obat_tidur);
        $stmt->bindParam(':pasang_infus', $pasang_infus);
        $stmt->bindParam(':iv_catch_no', $iv_catch_no);
        $stmt->bindParam(':tekanan_darah', $tekanan_darah);
        $stmt->bindParam(':nadi', $nadi);
        $stmt->bindParam(':suhu', $suhu);
        $stmt->bindParam(':pernafasan', $pernafasan);
        $stmt->bindParam(':obat_ubs', $obat_ubs);
        $stmt->bindParam(':hasil_skin_test', $hasil_skin_test);
        $stmt->bindParam(':visit_dokter_bedah', $visit_dokter_bedah);
        $stmt->bindParam(':visit_dokter_anestesi', $visit_dokter_anestesi);
        
        // Execute query
        if ($stmt->execute()) {
            $id = $db->lastInsertId();
            // Redirect to detail pasien dengan status sukses
            header("Location: ../index.php?page=detail-pasien&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=sukses");
        } else {
            $errorInfo = $stmt->errorInfo();
            header("Location: ../index.php?page=persiapan-operasi&status=gagal&error=" . urlencode($errorInfo[2]));
        }
        
    } catch (Exception $e) {
        error_log("Error submitting persiapan operasi: " . $e->getMessage());
        header("Location: ../index.php?page=persiapan-operasi&status=error&msg=" . urlencode($e->getMessage()));
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>
