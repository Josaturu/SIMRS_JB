<?php
include __DIR__ . '/../database.php';  // Sesuaikan path

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_rawat = $_POST['no_rawat'] ?? '';
    $kode_paket = $_POST['kode_paket'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_mulai = $_POST['jam_mulai'] ?? '';
    $resp = $_POST['resp'] ?? null;
    $nadi = $_POST['nadi'] ?? null;
    $sistolik = $_POST['sistolik'] ?? null;
    $diastolik = $_POST['diastolik'] ?? null;
    $spo2 = $_POST['spo2'] ?? null;
    $waktu = $_POST['waktu'] ?? '';

    // Validasi sederhana
    if (empty($no_rawat) || empty($waktu) || !is_numeric($resp) || $resp < 0) {
        echo json_encode(['success' => false, 'error' => 'Data tidak valid.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO tbl_anestesi_vital_sign 
              (id, no_rawat, kode_paket, tanggal, jam_mulai, waktu, respirasi, nadi, td_sistolik, td_diastolik, spo2) 
              VALUES (UUID(), :no_rawat, :kode_paket, :tanggal, :jam_mulai, :waktu, :respirasi, :nadi, :td_sistolik, :td_diastolik, :spo2)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':no_rawat', $no_rawat);
    $stmt->bindParam(':kode_paket', $kode_paket);
    $stmt->bindParam(':tanggal', $tanggal);
    $stmt->bindParam(':jam_mulai', $jam_mulai);
    $stmt->bindParam(':waktu', $waktu);
    $stmt->bindParam(':respirasi', $resp, PDO::PARAM_INT);
    $stmt->bindParam(':nadi', $nadi, PDO::PARAM_INT);
    $stmt->bindParam(':td_sistolik', $sistolik, PDO::PARAM_INT);
    $stmt->bindParam(':td_diastolik', $diastolik, PDO::PARAM_INT);
    $stmt->bindParam(':spo2', $spo2, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => implode(', ', $stmt->errorInfo())]);
    }
}
?>