<?php
require_once '../config/database.php';
require_once '../models/FormModel.php';
require_once '../includes/functions.php';

// Fungsi untuk membuat UUID (karena kolom id VARCHAR(36))
function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$id = generateUUID();

// Ambil data utama dari form
$no_rawat       = $_POST['no_rawat'] ?? '';
$kode_paket     = $_POST['kode_paket'] ?? '';
$tanggal        = $_POST['tanggal'] ?? '';
$jam_mulai      = $_POST['jam_mulai'] ?? '';
$no_rm          = $_POST['no_rm'] ?? '';
$nama           = $_POST['nama'] ?? '';
$tgl_lahir      = $_POST['tgl_lahir'] ?? '';
$ruang_perawatan= $_POST['ruang_perawatan'] ?? '';
$dokter_merawat = $_POST['dokter_merawat'] ?? '';
$dokter_anestesi= $_POST['dokter_anestesi'] ?? '';
$perawat_anestesi=$_POST['perawat_anestesi'] ?? '';
$diagnosa_pra_bedah = $_POST['diagnosa'] ?? '';
$asessment_pra_anestesi = $_POST['asessment'] ?? '';
$jenis_anestesi = $_POST['jenis_anestesi'] ?? '';
$keterangan     = $_POST['keterangan'] ?? '';

// Tambahan input waktu (dari <div class="time-form">)
$mulai_anestesi     = $_POST['mulai_anestesi'] ?? null;
$selesai_anestesi   = $_POST['selesai_anestesi'] ?? null;
$mulai_pembedahan   = $_POST['mulai_pembedahan'] ?? null;
$selesai_pembedahan = $_POST['selesai_pembedahan'] ?? null;
$keterangan_waktu   = $_POST['keterangan_waktu'] ?? null;
$induksi_pukul      = $_POST['induksi_pukul'] ?? null;
$pasien_siap_insisi = $_POST['pasien_siap_insisi'] ?? null;
$insisi_mulai_pukul = $_POST['insisi_mulai_pukul'] ?? null;
$operasi_mulai_pukul= $_POST['operasi_mulai_pukul'] ?? null;
$ekstubasi_pukul    = $_POST['ekstubasi_pukul'] ?? null;
$pasien_keluar_ok   = $_POST['pasien_keluar_ok'] ?? null;

$cairan_output = json_encode($_POST['cairan_output'] ?? []);
$masalah_anestesi = json_encode($_POST['masalah'] ?? []);
$tindakan = json_encode($_POST['tindakan'] ?? []);

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    echo "❌ Data utama tidak lengkap.";
    exit;
}

$database = new Database();
$db = $database->getConnection();

$query = "INSERT INTO tbl_anestesi_catatan_anestesi 
          (id, no_rawat, kode_paket, tanggal, jam_mulai, no_rm, nama, tgl_lahir, ruang_perawatan, dokter_merawat, 
           dokter_anestesi, perawat_anestesi, diagnosa_pra_bedah, asessment_pra_anestesi, jenis_anestesi, keterangan,
           mulai_anestesi, selesai_anestesi, mulai_pembedahan, selesai_pembedahan, keterangan_waktu, induksi_pukul,
           pasien_siap_insisi, insisi_mulai_pukul, operasi_mulai_pukul, ekstubasi_pukul, pasien_keluar_ok,
           cairan_output, masalah_anestesi, tindakan)
          VALUES (:id, :no_rawat, :kode_paket, :tanggal, :jam_mulai, :no_rm, :nama, :tgl_lahir, :ruang_perawatan, :dokter_merawat, 
                  :dokter_anestesi, :perawat_anestesi, :diagnosa_pra_bedah, :asessment_pra_anestesi, :jenis_anestesi, :keterangan,
                  :mulai_anestesi, :selesai_anestesi, :mulai_pembedahan, :selesai_pembedahan, :keterangan_waktu, :induksi_pukul,
                  :pasien_siap_insisi, :insisi_mulai_pukul, :operasi_mulai_pukul, :ekstubasi_pukul, :pasien_keluar_ok,
                  :cairan_output, :masalah_anestesi, :tindakan)";

$stmt = $db->prepare($query);
// Bind semua parameter (contoh, sisanya ikuti pola ini)
$stmt->bindParam(':id', $id);
$stmt->bindParam(':no_rawat', $no_rawat);
// ... Bind sisanya ...
$stmt->bindParam(':cairan_output', $cairan_output);
$stmt->bindParam(':masalah_anestesi', $masalah_anestesi);
$stmt->bindParam(':tindakan', $tindakan);

if ($stmt->execute()) {
    header("Location: form_catatan_sedasi.php?no_rawat=$no_rawat&kode_paket=$kode_paket&tanggal=$tanggal&jam_mulai=$jam_mulai&success=1");
    exit;
} else {
    echo "❌ Gagal menyimpan catatan anestesi: " . implode(', ', $stmt->errorInfo());
}
?>
