<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ]);
    exit;
}

try {
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

    if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai) || empty($waktu)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data utama tidak lengkap.'
        ]);
        exit;
    }

    foreach ([
        'resp' => $resp,
        'nadi' => $nadi,
        'sistolik' => $sistolik,
        'diastolik' => $diastolik,
        'spo2' => $spo2
    ] as $label => $value) {
        if ($value === null || !is_numeric($value)) {
            echo json_encode([
                'success' => false,
                'message' => "Nilai $label tidak valid."
            ]);
            exit;
        }
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
        echo json_encode([
            'success' => true,
            'message' => 'Data vital sign berhasil disimpan.'
        ]);
        exit;
    }

    $errorInfo = $stmt->errorInfo();
    echo json_encode([
        'success' => false,
        'message' => $errorInfo[2] ?? 'Gagal menyimpan data vital sign.'
    ]);
} catch (Throwable $exception) {
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}