<?php
// process/process-simpan-catatan-sedasi.php
session_start();

// Fungsi generate UUID - HARUS DI ATAS SEBELUM DIGUNAKAN
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// PROTECTION: Hanya proses jika method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method. Form harus di-submit dengan POST.";
    header("Location: ../index.php?page=form-catatan-sedasi&" . http_build_query($_GET));
    exit;
}

// Cek config path
$configPaths = [
    __DIR__ . '/../config/database.php',
    $_SERVER['DOCUMENT_ROOT'] . '/SIMRS_JB/config/database.php',
    'C:/FOLDER RIZKI/SIMRS_JB/config/database.php'
];

foreach ($configPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

if (!class_exists('Database')) {
    $_SESSION['error'] = "File database.php tidak ditemukan. Periksa konfigurasi.";
    $redirect_url = "../index.php?page=form-catatan-sedasi&" . http_build_query($_GET);
    header("Location: " . $redirect_url);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data dari POST
$no_rawat = $_POST['no_rawat'] ?? '';
$kode_paket = $_POST['kode_paket'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$jam_mulai = $_POST['jam_mulai'] ?? '';

// Validasi data wajib
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    $_SESSION['error'] = "Data wajib tidak lengkap";
    header("Location: ../index.php?page=form-catatan-sedasi&" . http_build_query($_GET));
    exit;
}

// Ambil semua data dari form (sama seperti sebelumnya)
$no_rm = $_POST['noRm'] ?? '';
$nama = $_POST['nama'] ?? '';
$tgl_lahir = $_POST['tglLahir'] ?? '';
$ruang_perawatan = $_POST['ruangPerawatan'] ?? '';
$dokter_merawat = $_POST['dokterPerawat'] ?? '';
$dokter_anestesi = $_POST['dokter_anestesi'] ?? '';
$perawat_anestesi = $_POST['perawat_anestesi'] ?? '';
$diagnosa_pra_bedah = $_POST['diagnosa_pra_bedah'] ?? '';
$nama_tindakan = $_POST['nama_tindakan'] ?? '';
$diagnosa_pasca_bedah = $_POST['diagnosa_pasca_bedah'] ?? '';
$asessment_pra_anestesi = $_POST['asessment'] ?? '';
$jenis_anestesi = $_POST['jenis_anestesi'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';
$tanggal_anestesi = $_POST['tanggal_anestesi'] ?? '';
$pukul = $_POST['pukul'] ?? '';
$dokter_bedah = $_POST['dokter_bedah'] ?? '';
$perawat_bedah = $_POST['perawat_bedah'] ?? '';
$jenis_pembedahan = $_POST['jenis_pembedahan'] ?? '';
$bb = !empty($_POST['bb']) ? $_POST['bb'] : null;
$td = $_POST['td'] ?? '';
$suhu = !empty($_POST['suhu']) ? $_POST['suhu'] : null;
$respirasi = !empty($_POST['respirasi']) ? $_POST['respirasi'] : null;
$hb = !empty($_POST['hb']) ? $_POST['hb'] : null;
$tb = !empty($_POST['tb']) ? $_POST['tb'] : null;
$nadi = !empty($_POST['nadi']) ? $_POST['nadi'] : null;
$gcs = !empty($_POST['gcs']) ? $_POST['gcs'] : null;
$golongan_darah = $_POST['golongan_darah'] ?? '';
$skrining_nyeri = $_POST['skrining_nyeri'] ?? '';
$status_fisik_asa = $_POST['status_fisik_asa'] ?? '';
$penyulit_pra_anestesi = $_POST['penyulit_pra_anestesi'] ?? '';
$resiko = $_POST['resiko'] ?? '';

// Handle array data
$checklist_sebelum_induksi = isset($_POST['checklist_sebelum_induksi']) ? 
    implode(', ', $_POST['checklist_sebelum_induksi']) : '';
$teknik_anestesi = $_POST['teknik_anestesi'] ?? '';
$infus_perifer = isset($_POST['infus_perifer']) ? implode(', ', array_filter($_POST['infus_perifer'])) : '';

// Handle posisi (checkbox array)
$posisi = isset($_POST['posisi']) ? implode(', ', $_POST['posisi']) : '';

// Handle lain_lain_posisi (text input, bukan checkbox array)
// Hanya simpan jika checkbox "Lain-lain :" dicentang
$posisi_array = isset($_POST['posisi']) ? $_POST['posisi'] : [];
$lain_lain_checked = in_array('Lain-lain :', $posisi_array);
$lain_lain_posisi = ($lain_lain_checked && isset($_POST['lain_lain_posisi'])) ? $_POST['lain_lain_posisi'] : '';

// Handle premedikasi (checkbox array)
$premedikasi = isset($_POST['premedikasi']) ? implode(', ', $_POST['premedikasi']) : '';
$premedik_nama_obat = $_POST['premedik_nama_obat'] ?? '';
$premedik_dosis_obat = $_POST['premedik_dosis_obat'] ?? '';

$induksi = isset($_POST['induksi']) ? implode(', ', $_POST['induksi']) : '';
$jalan_nafas = isset($_POST['jalan_nafas']) ? implode(', ', $_POST['jalan_nafas']) : '';
$ventilasi = isset($_POST['ventilasi']) ? implode(', ', $_POST['ventilasi']) : '';
$ventilator = isset($_POST['ventilator']) ? implode(', ', $_POST['ventilator']) : '';
$ukuran_balon = $_POST['ukuran_balon'] ?? '';
$jenis_balon = $_POST['jenis_balon'] ?? '';
$posisi_ett = $_POST['posisi_ett'] ?? '';
// IMPORTANT: Form menggunakan name="lain-lain_balon" (dengan dash)
// PHP POST tidak convert dash ke underscore, jadi kita ambil dengan dash
$lain_lain_balon = $_POST['lain-lain_balon'] ?? '';  // Field lain-lain balon (dengan dash!)

$lokasi_regional = $_POST['lokasi_regional'] ?? '';
$jarum_regional = $_POST['jarum_regional'] ?? '';
$kateter_regional = $_POST['kateter_regional'] ?? '';
$obat_anestesi_lokal = $_POST['obat_anestesi_lokal'] ?? '';
$hasil_regional = isset($_POST['hasil_regional']) ? implode(', ', $_POST['hasil_regional']) : '';
$obat = isset($_POST['obat']) ? implode(', ', array_filter($_POST['obat'])) : '';
$cairan_infus = isset($_POST['cairan_infus']) ? implode(', ', array_filter($_POST['cairan_infus'])) : '';
$cairan_output = isset($_POST['cairan_output']) ? implode(', ', array_filter($_POST['cairan_output'])) : '';
$masalah_selama_anestesi = isset($_POST['masalah_selama_anestesi']) ? 
    implode(', ', array_filter($_POST['masalah_selama_anestesi'])) : '';
$tindakan = isset($_POST['tindakan']) ? implode(', ', array_filter($_POST['tindakan'])) : '';
$perawat_menyerahkan = $_POST['perawat_menyerahkan'] ?? '';
$perawat_menerima = $_POST['perawat_menerima'] ?? '';
$dokter_anestesi_ttd = $_POST['dokter_anestesi_ttd'] ?? '';

// Data waktu
$mulai_anestesi = $_POST['mulai_anestesi'] ?? '';
$selesai_anestesi = $_POST['selesai_anestesi'] ?? '';
$mulai_pembedahan = $_POST['mulai_pembedahan'] ?? '';
$selesai_pembedahan = $_POST['selesai_pembedahan'] ?? '';
$keterangan_waktu = $_POST['keterangan_waktu'] ?? '';
$induksi_pukul = $_POST['induksi_pukul'] ?? '';
$pasien_siap_insisi = $_POST['pasien_siap_insisi'] ?? '';
$insisi_mulai_pukul = $_POST['insisi_mulai_pukul'] ?? '';
$operasi_mulai_pukul = $_POST['operasi_mulai_pukul'] ?? '';
$ekstubasi_pukul = $_POST['ekstubasi_pukul'] ?? '';
$pasien_keluar_ok = $_POST['pasien_keluar_ok'] ?? '';

try {
    // Cek apakah data sudah ada berdasarkan composite key
    $checkQuery = "SELECT id FROM tbl_anestesi_catatan_anestesi 
                   WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
    $existingData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    $isUpdate = !empty($existingData);
    $id = $existingData['id'] ?? ($_POST['id'] ?? generateUUID());
    
    if ($isUpdate) {
        // UPDATE existing record
        $query = "UPDATE tbl_anestesi_catatan_anestesi SET
            no_rm = ?, nama = ?, tgl_lahir = ?, ruang_perawatan = ?, dokter_merawat = ?,
            dokter_anestesi = ?, perawat_anestesi = ?, diagnosa_pra_bedah = ?, nama_tindakan = ?,
            diagnosa_pasca_bedah = ?, asessment_pra_anestesi = ?, jenis_anestesi = ?, keterangan = ?,
            tanggal_anestesi = ?, pukul = ?, dokter_bedah = ?, perawat_bedah = ?, jenis_pembedahan = ?,
            bb = ?, td = ?, suhu = ?, respirasi = ?, hb = ?, tb = ?, nadi = ?, gcs = ?, golongan_darah = ?,
            skrining_nyeri = ?, status_fisik_asa = ?, penyulit_pra_anestesi = ?, resiko = ?,
            checklist_sebelum_induksi = ?, teknik_anestesi = ?, infus_perifer = ?, posisi = ?, lain_lain_posisi = ?,
            premedikasi = ?, premedik_nama_obat = ?, premedik_dosis_obat = ?, induksi = ?, jalan_nafas = ?,
            ventilasi = ?, ventilator = ?, ukuran_balon = ?, jenis_balon = ?, posisi_ett = ?, lokasi_regional = ?,
            jarum_regional = ?, kateter_regional = ?, obat_anestesi_lokal = ?, hasil_regional = ?, obat = ?,
            cairan_infus = ?, cairan_output = ?, masalah_selama_anestesi = ?, tindakan = ?,
            perawat_menyerahkan = ?, perawat_menerima = ?, dokter_anestesi_ttd = ?,
            mulai_anestesi = ?, selesai_anestesi = ?, mulai_pembedahan = ?, selesai_pembedahan = ?,
            keterangan_waktu = ?, induksi_pukul = ?, pasien_siap_insisi = ?, insisi_mulai_pukul = ?,
            operasi_mulai_pukul = ?, ekstubasi_pukul = ?, pasien_keluar_ok = ?, lain_lain_balon = ?
            WHERE id = ?";
    } else {
        // INSERT new record
        $query = "INSERT INTO tbl_anestesi_catatan_anestesi (
        id, no_rawat, kode_paket, tanggal, jam_mulai, no_rm, nama, tgl_lahir, 
        ruang_perawatan, dokter_merawat, dokter_anestesi, perawat_anestesi,
        diagnosa_pra_bedah, nama_tindakan, diagnosa_pasca_bedah, asessment_pra_anestesi,
        jenis_anestesi, keterangan, tanggal_anestesi, pukul, dokter_bedah, perawat_bedah,
        jenis_pembedahan, bb, td, suhu, respirasi, hb, tb, nadi, gcs, golongan_darah,
        skrining_nyeri, status_fisik_asa, penyulit_pra_anestesi, resiko,
        checklist_sebelum_induksi, teknik_anestesi, infus_perifer, posisi, lain_lain_posisi,
        premedikasi, premedik_nama_obat, premedik_dosis_obat, induksi, jalan_nafas,
        ventilasi, ventilator, ukuran_balon, jenis_balon, posisi_ett, lokasi_regional,
        jarum_regional, kateter_regional, obat_anestesi_lokal, hasil_regional, obat,
        cairan_infus, cairan_output, masalah_selama_anestesi, tindakan, perawat_menyerahkan,
        perawat_menerima, dokter_anestesi_ttd, mulai_anestesi, selesai_anestesi,
        mulai_pembedahan, selesai_pembedahan, keterangan_waktu, induksi_pukul,
        pasien_siap_insisi, insisi_mulai_pukul, operasi_mulai_pukul, ekstubasi_pukul,
        pasien_keluar_ok, lain_lain_balon
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }
    
    $stmt = $db->prepare($query);
    
    // Parameter array untuk execute
    if ($isUpdate) {
        // Parameter untuk UPDATE (semua field SET, lalu id di WHERE)
        $params = [
        $no_rm, $nama, $tgl_lahir, $ruang_perawatan, $dokter_merawat,
        $dokter_anestesi, $perawat_anestesi, $diagnosa_pra_bedah, $nama_tindakan,
        $diagnosa_pasca_bedah, $asessment_pra_anestesi, $jenis_anestesi, $keterangan,
        $tanggal_anestesi, $pukul, $dokter_bedah, $perawat_bedah, $jenis_pembedahan,
        $bb, $td, $suhu, $respirasi, $hb, $tb, $nadi, $gcs, $golongan_darah,
        $skrining_nyeri, $status_fisik_asa, $penyulit_pra_anestesi, $resiko,
        $checklist_sebelum_induksi, $teknik_anestesi, $infus_perifer, $posisi, $lain_lain_posisi,
        $premedikasi, $premedik_nama_obat, $premedik_dosis_obat, $induksi, $jalan_nafas,
        $ventilasi, $ventilator, $ukuran_balon, $jenis_balon, $posisi_ett, $lokasi_regional,
        $jarum_regional, $kateter_regional, $obat_anestesi_lokal, $hasil_regional, $obat,
        $cairan_infus, $cairan_output, $masalah_selama_anestesi, $tindakan,
        $perawat_menyerahkan, $perawat_menerima, $dokter_anestesi_ttd,
        $mulai_anestesi, $selesai_anestesi, $mulai_pembedahan, $selesai_pembedahan,
        $keterangan_waktu, $induksi_pukul, $pasien_siap_insisi, $insisi_mulai_pukul,
        $operasi_mulai_pukul, $ekstubasi_pukul, $pasien_keluar_ok, $lain_lain_balon,
        $id  // WHERE id = ?
        ];
    } else {
        // Parameter untuk INSERT (semua field termasuk id di awal)
        $params = [
        $id, $no_rawat, $kode_paket, $tanggal, $jam_mulai,
        $no_rm, $nama, $tgl_lahir, $ruang_perawatan, $dokter_merawat,
        $dokter_anestesi, $perawat_anestesi, $diagnosa_pra_bedah, $nama_tindakan,
        $diagnosa_pasca_bedah, $asessment_pra_anestesi, $jenis_anestesi, $keterangan,
        $tanggal_anestesi, $pukul, $dokter_bedah, $perawat_bedah, $jenis_pembedahan,
        $bb, $td, $suhu, $respirasi, $hb, $tb, $nadi, $gcs, $golongan_darah,
        $skrining_nyeri, $status_fisik_asa, $penyulit_pra_anestesi, $resiko,
        $checklist_sebelum_induksi, $teknik_anestesi, $infus_perifer, $posisi, $lain_lain_posisi,
        $premedikasi, $premedik_nama_obat, $premedik_dosis_obat, $induksi, $jalan_nafas,
        $ventilasi, $ventilator, $ukuran_balon, $jenis_balon, $posisi_ett, $lokasi_regional,
        $jarum_regional, $kateter_regional, $obat_anestesi_lokal, $hasil_regional, $obat,
        $cairan_infus, $cairan_output, $masalah_selama_anestesi, $tindakan,
        $perawat_menyerahkan, $perawat_menerima, $dokter_anestesi_ttd,
        $mulai_anestesi, $selesai_anestesi, $mulai_pembedahan, $selesai_pembedahan,
        $keterangan_waktu, $induksi_pukul, $pasien_siap_insisi, $insisi_mulai_pukul,
        $operasi_mulai_pukul, $ekstubasi_pukul, $pasien_keluar_ok, $lain_lain_balon
        ];
    }

    // Validasi jumlah parameter
    $tokenCount = substr_count($query, '?');
    $paramCount = count($params);
    
    if ($tokenCount !== $paramCount) {
        throw new Exception("Jumlah token ($tokenCount) tidak match dengan parameter ($paramCount). Periksa urutan parameter!");
    }

    $result = $stmt->execute($params);

    if ($result) {
        $affectedRows = $stmt->rowCount();
        
        // Simpan data vital sign jika ada
        $vitalSignSaved = 0;
        
        // Debug logging
        error_log("=== VITAL SIGN DEBUG ===");
        error_log("POST vital_sign_data exists: " . (isset($_POST['vital_sign_data']) ? 'YES' : 'NO'));
        error_log("POST vital_sign_data empty: " . (empty($_POST['vital_sign_data']) ? 'YES' : 'NO'));
        if (isset($_POST['vital_sign_data'])) {
            error_log("POST vital_sign_data value: " . $_POST['vital_sign_data']);
        }
        error_log("=======================");
        
        if (!empty($_POST['vital_sign_data'])) {
            try {
                $vitalSignData = json_decode($_POST['vital_sign_data'], true);
                
                error_log("Decoded vital sign data: " . print_r($vitalSignData, true));
                
                if ($vitalSignData && isset($vitalSignData['vital_signs']) && is_array($vitalSignData['vital_signs'])) {
                    $vitalSigns = $vitalSignData['vital_signs'];
                    
                    // Prepare statement untuk insert vital sign
                    $vitalQuery = "INSERT INTO tbl_anestesi_vital_sign 
                                   (id, no_rawat, kode_paket, tanggal, jam_mulai, waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $vitalStmt = $db->prepare($vitalQuery);
                    
                    foreach ($vitalSigns as $vital) {
                        $vitalId = generateUUID();
                        
                        error_log("Inserting vital sign: " . print_r($vital, true));
                        
                        // Gabungkan tanggal dengan waktu untuk field waktu (datetime)
                        $waktuDatetime = $vitalSignData['tanggal'] . ' ' . ($vital['jam'] ?? '00:00:00');
                        
                        $insertResult = $vitalStmt->execute([
                            $vitalId,
                            $vitalSignData['no_rawat'],
                            $vitalSignData['kode_paket'],
                            $vitalSignData['tanggal'],
                            $vitalSignData['jam_mulai'],
                            $waktuDatetime,  // waktu (datetime)
                            $vital['respirasi'] ?? null,
                            $vital['nadi'] ?? null,
                            $vital['sistol'] ?? null,  // td_sistolik
                            $vital['diastol'] ?? null,  // td_diastolik
                            $vital['fio2'] ?? null,
                            $vital['spo2'] ?? null
                        ]);
                        
                        if ($insertResult) {
                            $vitalSignSaved++;
                            error_log("✅ Vital sign inserted successfully");
                        } else {
                            error_log("❌ Failed to insert vital sign");
                        }
                    }
                    
                    error_log("Total vital signs saved: " . $vitalSignSaved);
                }
            } catch (Exception $vitalError) {
                error_log("Error saving vital signs: " . $vitalError->getMessage());
                // Tidak throw error, hanya log saja
            }
        }
        
        if ($isUpdate) {
            $message = "Data catatan sedasi berhasil diperbarui!";
            if ($vitalSignSaved > 0) {
                $message .= " ($vitalSignSaved data vital sign tersimpan)";
            }
            $_SESSION['success'] = $message;
        } else {
            $message = "Data catatan sedasi berhasil disimpan!";
            if ($vitalSignSaved > 0) {
                $message .= " ($vitalSignSaved data vital sign tersimpan)";
            }
            $_SESSION['success'] = $message;
        }
    } else {
        $_SESSION['error'] = "Gagal menyimpan data.";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    error_log("=== CATATAN SEDASI ERROR ===");
    error_log("Database Error: " . $e->getMessage());
    error_log("Query: " . ($query ?? 'N/A'));
    error_log("Param count: " . (isset($params) ? count($params) : 0));
    error_log("Token count: " . (isset($query) ? substr_count($query, '?') : 0));
    error_log("Is Update: " . ($isUpdate ?? 'N/A'));
    error_log("ID: " . ($id ?? 'N/A'));
    error_log("===========================");
}

// Redirect kembali ke form melalui index.php (untuk menghindari CSS hilang dan looping)
$redirect_url = "../index.php?page=form-catatan-sedasi&no_rawat=" . urlencode($no_rawat) . 
                "&kode_paket=" . urlencode($kode_paket) . 
                "&tanggal=" . urlencode($tanggal) . 
                "&jam_mulai=" . urlencode($jam_mulai);
header("Location: " . $redirect_url);
exit;
?>
