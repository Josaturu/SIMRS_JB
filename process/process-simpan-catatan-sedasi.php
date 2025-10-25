<?php
// process/process-simpan-catatan-sedasi.php
session_start();

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
    $params = $_GET;
    $params['status'] = 'error';
    $params['msg'] = 'File database.php tidak ditemukan. Periksa konfigurasi.';
    header("Location: ../views/form-catatan-sedasi.php?" . http_build_query($params));
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
    $params = $_GET;
    $params['status'] = 'error';
    $params['msg'] = 'Data wajib tidak lengkap';
    header("Location: ../views/form-catatan-sedasi.php?" . http_build_query($params));
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
$infus_perifer = isset($_POST['infus']) ? implode(', ', array_filter($_POST['infus'])) : '';

// Handle posisi (checkbox array)
$posisi = isset($_POST['posisi']) ? implode(', ', $_POST['posisi']) : '';

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
    // Query INSERT ... ON DUPLICATE KEY UPDATE
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        no_rm = VALUES(no_rm), nama = VALUES(nama), tgl_lahir = VALUES(tgl_lahir),
        ruang_perawatan = VALUES(ruang_perawatan), dokter_merawat = VALUES(dokter_merawat),
        dokter_anestesi = VALUES(dokter_anestesi), perawat_anestesi = VALUES(perawat_anestesi),
        diagnosa_pra_bedah = VALUES(diagnosa_pra_bedah), nama_tindakan = VALUES(nama_tindakan),
        diagnosa_pasca_bedah = VALUES(diagnosa_pasca_bedah), asessment_pra_anestesi = VALUES(asessment_pra_anestesi),
        jenis_anestesi = VALUES(jenis_anestesi), keterangan = VALUES(keterangan),
        tanggal_anestesi = VALUES(tanggal_anestesi), pukul = VALUES(pukul),
        dokter_bedah = VALUES(dokter_bedah), perawat_bedah = VALUES(perawat_bedah),
        jenis_pembedahan = VALUES(jenis_pembedahan), bb = VALUES(bb), td = VALUES(td),
        suhu = VALUES(suhu), respirasi = VALUES(respirasi), hb = VALUES(hb), tb = VALUES(tb),
        nadi = VALUES(nadi), gcs = VALUES(gcs), golongan_darah = VALUES(golongan_darah),
        skrining_nyeri = VALUES(skrining_nyeri), status_fisik_asa = VALUES(status_fisik_asa),
        penyulit_pra_anestesi = VALUES(penyulit_pra_anestesi), resiko = VALUES(resiko),
        checklist_sebelum_induksi = VALUES(checklist_sebelum_induksi), teknik_anestesi = VALUES(teknik_anestesi),
        infus_perifer = VALUES(infus_perifer), posisi = VALUES(posisi), lain_lain_posisi = VALUES(lain_lain_posisi),
        premedikasi = VALUES(premedikasi), premedik_nama_obat = VALUES(premedik_nama_obat), 
        premedik_dosis_obat = VALUES(premedik_dosis_obat), induksi = VALUES(induksi), 
        jalan_nafas = VALUES(jalan_nafas), ventilasi = VALUES(ventilasi), ventilator = VALUES(ventilator),
        ukuran_balon = VALUES(ukuran_balon), jenis_balon = VALUES(jenis_balon), posisi_ett = VALUES(posisi_ett),
        lokasi_regional = VALUES(lokasi_regional), jarum_regional = VALUES(jarum_regional),
        kateter_regional = VALUES(kateter_regional), obat_anestesi_lokal = VALUES(obat_anestesi_lokal),
        hasil_regional = VALUES(hasil_regional), obat = VALUES(obat), cairan_infus = VALUES(cairan_infus),
        cairan_output = VALUES(cairan_output), masalah_selama_anestesi = VALUES(masalah_selama_anestesi),
        tindakan = VALUES(tindakan), perawat_menyerahkan = VALUES(perawat_menyerahkan),
        perawat_menerima = VALUES(perawat_menerima), dokter_anestesi_ttd = VALUES(dokter_anestesi_ttd),
        mulai_anestesi = VALUES(mulai_anestesi), selesai_anestesi = VALUES(selesai_anestesi),
        mulai_pembedahan = VALUES(mulai_pembedahan), selesai_pembedahan = VALUES(selesai_pembedahan),
        keterangan_waktu = VALUES(keterangan_waktu), induksi_pukul = VALUES(induksi_pukul),
        pasien_siap_insisi = VALUES(pasien_siap_insisi), insisi_mulai_pukul = VALUES(insisi_mulai_pukul),
        operasi_mulai_pukul = VALUES(operasi_mulai_pukul), ekstubasi_pukul = VALUES(ekstubasi_pukul),
        pasien_keluar_ok = VALUES(pasien_keluar_ok), lain_lain_balon = VALUES(lain_lain_balon),
        updated_at = CURRENT_TIMESTAMP";

    $stmt = $db->prepare($query);
    
    // Generate UUID untuk ID baru atau gunakan ID yang ada
    $existing_id = $_POST['id'] ?? '';
    
    // Cek apakah data sudah ada di database berdasarkan composite key
    if (empty($existing_id)) {
        $checkQuery = "SELECT id FROM tbl_anestesi_catatan_anestesi 
                       WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
        $existingData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $id = $existingData['id'] ?? generateUUID();
    } else {
        $id = $existing_id;
    }
    
    // Parameter array untuk execute - SESUAI URUTAN DI QUERY
    $params = [
        $id, $no_rawat, $kode_paket, $tanggal, $jam_mulai, 
        $no_rm, $nama, $tgl_lahir, $ruang_perawatan, 
        $dokter_merawat, $dokter_anestesi, $perawat_anestesi,
        $diagnosa_pra_bedah, $nama_tindakan, $diagnosa_pasca_bedah, 
        $asessment_pra_anestesi, $jenis_anestesi, $keterangan, 
        $tanggal_anestesi, $pukul, $dokter_bedah, $perawat_bedah,
        $jenis_pembedahan, $bb, $td, $suhu, $respirasi, $hb, $tb, 
        $nadi, $gcs, $golongan_darah, $skrining_nyeri, $status_fisik_asa, 
        $penyulit_pra_anestesi, $resiko, $checklist_sebelum_induksi, 
        $teknik_anestesi, $infus_perifer, 
        // Kolom baru dari database
        $posisi,
        $_POST['lain_lain_posisi'] ?? '',
        $premedikasi, 
        $premedik_nama_obat, 
        $premedik_dosis_obat,
        // Lanjutan
        $induksi, $jalan_nafas, $ventilasi, $ventilator,
        $ukuran_balon, $jenis_balon, $posisi_ett, $lokasi_regional,
        $jarum_regional, $kateter_regional, $obat_anestesi_lokal, $hasil_regional,
        $obat, $cairan_infus, $cairan_output, $masalah_selama_anestesi, $tindakan,
        $perawat_menyerahkan, $perawat_menerima, $dokter_anestesi_ttd,
        $mulai_anestesi, $selesai_anestesi, $mulai_pembedahan, 
        $selesai_pembedahan, $keterangan_waktu, $induksi_pukul,
        $pasien_siap_insisi, $insisi_mulai_pukul, $operasi_mulai_pukul, 
        $ekstubasi_pukul, $pasien_keluar_ok,
        // Kolom lain_lain_balon
        $_POST['lain-lain_balon'] ?? ''
    ];

    // Validasi jumlah parameter
    $tokenCount = substr_count($query, '?');
    $paramCount = count($params);
    
    if ($tokenCount !== $paramCount) {
        throw new Exception("Jumlah token ($tokenCount) tidak match dengan parameter ($paramCount). Periksa urutan parameter!");
    }

    $result = $stmt->execute($params);

    if ($result) {
        $affectedRows = $stmt->rowCount();
        
        // Tentukan apakah ini operasi insert atau update
        $isUpdate = !empty($_POST['id']);
        
        $action = ($affectedRows === 2 || $isUpdate) ? 'updated' : 'saved';
        $status = 'sukses';
    } else {
        $status = 'gagal';
        $error = 'Gagal menyimpan data.';
    }

} catch (Exception $e) {
    $status = 'error';
    $error = $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}

// Redirect kembali ke form with URL params
$redirect_url = "../views/form-catatan-sedasi.php?no_rawat=" . urlencode($no_rawat) . 
                "&kode_paket=" . urlencode($kode_paket) . 
                "&tanggal=" . urlencode($tanggal) . 
                "&jam_mulai=" . urlencode($jam_mulai) . 
                "&status=" . $status;

if ($status === 'sukses') {
    $redirect_url .= "&action=" . $action;
} else {
    $redirect_url .= "&error=" . urlencode($error ?? 'Terjadi kesalahan');
}

header("Location: " . $redirect_url);
exit;

// Fungsi generate UUID
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
