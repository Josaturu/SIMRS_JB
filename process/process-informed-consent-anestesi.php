<?php
session_start();
include_once '../config/database.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Cek apakah ini INSERT atau UPDATE (berdasarkan ID)
    $id = !empty($_POST['id']) ? $_POST['id'] : uniqid();
    $is_update = !empty($_POST['id']);
    
    // Prepare checkbox arrays
    $jenis_anestesi = isset($_POST['jenisAnestesi']) ? implode(', ', $_POST['jenisAnestesi']) : '';
    $indikasi = isset($_POST['indikasi']) ? implode(', ', $_POST['indikasi']) : '';
    $tata_cara = isset($_POST['tataCara']) ? implode(', ', $_POST['tataCara']) : '';
    $risiko = isset($_POST['risiko']) ? implode(', ', $_POST['risiko']) : '';
    $status_fisik = isset($_POST['statusFisik']) ? implode(', ', $_POST['statusFisik']) : '';
    
    // Generate checkbox_confirm dari cek1-cek10
    $checkbox_confirm_array = [];
    for ($i = 1; $i <= 10; $i++) {
        if (isset($_POST["cek$i"]) && $_POST["cek$i"] == '1') {
            $checkbox_confirm_array[] = $i;
        }
    }
    $checkbox_confirm = implode(',', $checkbox_confirm_array);
    
    // Prepare data
    $no_rawat = $_POST['no_rawat'];
    $kode_paket = $_POST['kode_paket'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $ruang = $_POST['ruang'] ?? '';
    $dokter_pelaksana = $_POST['dokter'] ?? '';
    $pemberi_info = $_POST['pemberi'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    $penerima_info = $_POST['penerima'] ?? '';
    $hubungan_pasien = $_POST['hubungan'] ?? '';
    $tindakan_operasi = $_POST['tindakanOperasi'] ?? '';
    $tujuan = 'Memfasilitasi tindakan pembedahan, agar pasien dan dokter operator aman dan nyaman';
    $prognosis = $_POST['prognosis'] ?? '';
    $alternatif_resiko = $_POST['alternatif'] ?? '';
    $lain_lain = $_POST['lainLain'] ?? '';
    
    // Query dengan INSERT ... ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO tbl_anestesi_informed_consent_anestesi 
              (id, no_rawat, kode_paket, tanggal, jam_mulai, ruang, 
               dokter_pelaksana, pemberi_info, jabatan, penerima_info, 
               hubungan_pasien, tindakan_operasi, jenis_anestesi, indikasi, 
               tata_cara, tujuan, risiko, status_fisik, prognosis, 
               alternatif_resiko, lain_lain, checkbox_confirm)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
                ruang = VALUES(ruang),
                dokter_pelaksana = VALUES(dokter_pelaksana),
                pemberi_info = VALUES(pemberi_info),
                jabatan = VALUES(jabatan),
                penerima_info = VALUES(penerima_info),
                hubungan_pasien = VALUES(hubungan_pasien),
                tindakan_operasi = VALUES(tindakan_operasi),
                jenis_anestesi = VALUES(jenis_anestesi),
                indikasi = VALUES(indikasi),
                tata_cara = VALUES(tata_cara),
                tujuan = VALUES(tujuan),
                risiko = VALUES(risiko),
                status_fisik = VALUES(status_fisik),
                prognosis = VALUES(prognosis),
                alternatif_resiko = VALUES(alternatif_resiko),
                lain_lain = VALUES(lain_lain),
                checkbox_confirm = VALUES(checkbox_confirm)";
    
    $stmt = $db->prepare($query);
    
    // Execute dengan semua parameter
    $success = $stmt->execute([
        $id, $no_rawat, $kode_paket, $tanggal, $jam_mulai, $ruang,
        $dokter_pelaksana, $pemberi_info, $jabatan, $penerima_info,
        $hubungan_pasien, $tindakan_operasi, $jenis_anestesi, $indikasi,
        $tata_cara, $tujuan, $risiko, $status_fisik, $prognosis,
        $alternatif_resiko, $lain_lain, $checkbox_confirm
    ]);
    
    if ($success) {
        $_SESSION['success'] = $is_update ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!';
        header("Location: ../index.php?page=informed-consent-anestesi&no_rawat=" . urlencode($no_rawat) . "&kode_paket=" . urlencode($kode_paket) . "&tanggal=" . urlencode($tanggal) . "&jam_mulai=" . urlencode($jam_mulai));
        exit;
    } else {
        $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
        header("Location: ../index.php?page=informed-consent-anestesi&no_rawat=" . urlencode($no_rawat) . "&kode_paket=" . urlencode($kode_paket) . "&tanggal=" . urlencode($tanggal) . "&jam_mulai=" . urlencode($jam_mulai));
        exit;
    }
}
?>