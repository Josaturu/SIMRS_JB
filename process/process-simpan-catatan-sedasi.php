<?php
require_once '../config/database.php';
require_once '../models/FormModel.php';
require_once '../includes/functions.php';

$id = generateUUID();

// Ambil data utama dari form
$no_rawat       = $_POST['no_rawat'] ?? '';
$kode_paket     = $_POST['kode_paket'] ?? '';
$tanggal        = $_POST['tanggal'] ?? '';
$jam_mulai      = $_POST['jam_mulai'] ?? '';

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    echo "❌ Data utama tidak lengkap.";
    exit;
}

$database = new Database();
$db = $database->getConnection();
$formModel = new FormModel($db);

// Siapkan data untuk model - SESUAIKAN DENGAN STRUCTUR TABEL
$data = [
    'id' => $id,
    'no_rawat' => $no_rawat,
    'kode_paket' => $kode_paket,
    'tanggal' => $tanggal,
    'jam_mulai' => $jam_mulai,
    
    // Data pasien (diambil dari form atau database)
    'no_rm' => $_POST['noRm'] ?? '',
    'nama' => $_POST['nama'] ?? '',
    'tgl_lahir' => $_POST['tglLahir'] ?? '',
    'ruang_perawatan' => $_POST['ruangPerawatan'] ?? '',
    'dokter_merawat' => $_POST['dokterPerawat'] ?? '',
    
    // Data dasar
    'tanggal_anestesi' => $_POST['tanggal_anestesi'] ?? '',
    'pukul' => $_POST['pukul'] ?? '',
    'dokter_bedah' => $_POST['dokter_bedah'] ?? '',
    'perawat_bedah' => $_POST['perawat_bedah'] ?? '',
    'dokter_anestesi' => $_POST['dokter_anestesi'] ?? '',
    'perawat_anestesi' => $_POST['perawat_anestesi'] ?? '',
    'jenis_pembedahan' => $_POST['jenis_pembedahan'] ?? '',
    
    // Diagnosa dan asesmen
    'diagnosa_pra_bedah' => $_POST['diagnosa_pra_bedah'] ?? '',
    'nama_tindakan' => $_POST['nama_tindakan'] ?? '',
    'diagnosa_pasca_bedah' => $_POST['diagnosa_pasca_bedah'] ?? '',
    'asessment_pra_anestesi' => $_POST['asessment'] ?? '',
    
    // Data vital dan fisik
    'bb' => $_POST['bb'] ?? '',
    'td' => $_POST['td'] ?? '',
    'suhu' => $_POST['suhu'] ?? '',
    'respirasi' => $_POST['respirasi'] ?? '',
    'hb' => $_POST['hb'] ?? '',
    'tb' => $_POST['tb'] ?? '',
    'nadi' => $_POST['nadi'] ?? '',
    'gcs' => $_POST['gcs'] ?? '',
    'golongan_darah' => $_POST['golongan_darah'] ?? '',
    'skrining_nyeri' => $_POST['skrining_nyeri'] ?? '',
    'status_fisik_asa' => $_POST['status_fisik_asa'] ?? '',
    'penyulit_pra_anestesi' => $_POST['penyulit_pra_anestesi'] ?? '',
    
    // Jenis anestesi dan risiko
    'jenis_anestesi' => $_POST['jenis_anestesi'] ?? '',
    'resiko' => $_POST['resiko'] ?? '',
    
    // Checklist dan teknik
    'checklist_sebelum_induksi' => isset($_POST['checklist_sebelum_induksi']) ? 
        implode(',', $_POST['checklist_sebelum_induksi']) : '',
    'teknik_anestesi' => $_POST['teknik_anestesi'] ?? '',
    'infus' => isset($_POST['infus']) ? 
        implode(',', $_POST['infus']) : '',
    
    // Data anestesi umum
    'induksi' => isset($_POST['induksi']) ? 
        implode(',', $_POST['induksi']) : '',
    'jalan_nafas' => isset($_POST['jalan_nafas']) ? 
        implode(',', $_POST['jalan_nafas']) : '',
    'ventilasi' => isset($_POST['ventilasi']) ? 
        implode(',', $_POST['ventilasi']) : '',
    'ventilator' => isset($_POST['ventilator']) ? 
        implode(',', $_POST['ventilator']) : '',
    
    // Data balon/ETT
    'ukuran_balon' => $_POST['ukuran_balon'] ?? '',
    'jenis_balon' => $_POST['jenis_balon'] ?? '',
    'posisi_ett' => $_POST['posisi_ett'] ?? '',
    'lain_lain_balon' => $_POST['lain-lain_balon'] ?? '',
    
    // Data regional
    'lokasi_regional' => $_POST['lokasi_regional'] ?? '',
    'jarum_regional' => $_POST['jarum_regional'] ?? '',
    'kateter_regional' => $_POST['kateter_regional'] ?? '',
    'obat_anestesi_lokal' => $_POST['obat_anestesi_lokal'] ?? '',
    'hasil_regional' => isset($_POST['hasil_regional']) ? 
        implode(',', $_POST['hasil_regional']) : '',
    
    // Obat-obatan
    'obat' => isset($_POST['obat']) ? 
        implode(',', $_POST['obat']) : '',
    
    // Cairan
    'cairan_infus' => isset($_POST['cairan_infus']) ? 
        implode(',', $_POST['cairan_infus']) : '',
    'cairan_output' => isset($_POST['cairan_output']) ? 
        implode(',', $_POST['cairan_output']) : '',
    
    // Masalah dan tindakan
    'masalah_selama_anestesi' => isset($_POST['masalah_selama_anestesi']) ? 
        implode(',', $_POST['masalah_selama_anestesi']) : '',
    'tindakan' => isset($_POST['tindakan']) ? 
        implode(',', $_POST['tindakan']) : '',
    
    // Serah terima
    'perawat_menyerahkan' => $_POST['perawat_menyerahkan'] ?? '',
    'perawat_menerima' => $_POST['perawat_menerima'] ?? '',
    'dokter_anestesi_ttd' => $_POST['dokter_anestesi_ttd'] ?? '',
    
    // Waktu prosedur
    'mulai_anestesi' => $_POST['mulai_anestesi'] ?? null,
    'selesai_anestesi' => $_POST['selesai_anestesi'] ?? null,
    'mulai_pembedahan' => $_POST['mulai_pembedahan'] ?? null,
    'selesai_pembedahan' => $_POST['selesai_pembedahan'] ?? null,
    'keterangan_waktu' => $_POST['keterangan_waktu'] ?? null,
    'induksi_pukul' => $_POST['induksi_pukul'] ?? null,
    'pasien_siap_insisi' => $_POST['pasien_siap_insisi'] ?? null,
    'insisi_mulai_pukul' => $_POST['insisi_mulai_pukul'] ?? null,
    'operasi_mulai_pukul' => $_POST['operasi_mulai_pukul'] ?? null,
    'ekstubasi_pukul' => $_POST['ekstubasi_pukul'] ?? null,
    'pasien_keluar_ok' => $_POST['pasien_keluar_ok'] ?? null,
    
    // Keterangan
    'keterangan' => $_POST['keterangan'] ?? ''
];

try {
    // Gunakan model untuk menyimpan data
    if ($formModel->createCatatanAnestesi($data)) {
        header("Location: ../views/form-catatan-sedasi.php?no_rawat=$no_rawat&kode_paket=$kode_paket&tanggal=$tanggal&jam_mulai=$jam_mulai&success=1");
        exit;
    } else {
        echo "❌ Gagal menyimpan catatan anestesi.";
        // Untuk debugging
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    // Untuk debugging
    echo "<pre>Error details: ";
    print_r($e->getMessage());
    echo "</pre>";
}
?>