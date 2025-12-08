<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// =================== DEBUGGING & ERROR HANDLING ===================

// Set header ke JSON
header('Content-Type: application/json');

// Ambil data mentah dari body request
$raw_data = file_get_contents('php://input');
error_log("--- SIMPAN VITAL SIGN ---");
error_log("Raw data diterima: " . $raw_data);

// Decode data JSON
$data = json_decode($raw_data, true);

// Cek jika JSON valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    $error_message = 'JSON tidak valid: ' . json_last_error_msg();
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

// Validasi data dasar
if (!$data || !isset($data['vital_signs']) || !is_array($data['vital_signs'])) {
    http_response_code(400); // Bad Request
    $error_message = 'Struktur data tidak valid atau key `vital_signs` tidak ditemukan.';
    error_log($error_message . " Data: " . print_r($data, true));
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

// Ekstrak data dari payload
$no_rawat = $data['no_rawat'] ?? '';
$kode_paket = $data['kode_paket'] ?? '';
$tanggal = $data['tanggal'] ?? '';
$jam_mulai = $data['jam_mulai'] ?? '';
$vital_signs = $data['vital_signs'];
$mode = $data['mode'] ?? 'append'; // Default ke 'append'

// Jika tidak ada data vital sign baru, kirim respon sukses tanpa melakukan apa-apa
if (empty($vital_signs)) {
    error_log("Tidak ada record vital sign baru untuk disimpan.");
    echo json_encode(['success' => true, 'message' => 'Tidak ada data vital sign baru untuk diproses.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    error_log("Transaksi database dimulai. Mode: {$mode}");

    // Hapus data lama HANYA jika mode = 'replace'
    if ($mode === 'replace') {
        $delete_query = "DELETE FROM tbl_anestesi_vital_sign WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket AND tanggal = :tanggal AND jam_mulai = :jam_mulai";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->execute([':no_rawat' => $no_rawat, ':kode_paket' => $kode_paket, ':tanggal' => $tanggal, ':jam_mulai' => $jam_mulai]);
        error_log("Data lama dihapus untuk {$no_rawat} ({$delete_stmt->rowCount()} baris).");
    }

    // Siapkan query insert
    $query = "INSERT INTO tbl_anestesi_vital_sign (id, no_rawat, kode_paket, tanggal, jam_mulai, waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2) VALUES (UUID(), :no_rawat, :kode_paket, :tanggal, :jam_mulai, :waktu, :respirasi, :nadi, :td_sistolik, :td_diastolik, :fio2, :spo2)";
    $stmt = $db->prepare($query);

    $success_count = 0;
    foreach ($vital_signs as $index => $record) {
        if (is_array($record) && isset($record['jam'])) {
            $waktu_datetime = $tanggal . ' ' . $record['jam'];
            
            $params = [
                ':no_rawat' => $no_rawat,
                ':kode_paket' => $kode_paket,
                ':tanggal' => $tanggal,
                ':jam_mulai' => $jam_mulai,
                ':waktu' => $waktu_datetime,
                ':respirasi' => $record['respirasi'] ?? null,
                ':nadi' => $record['nadi'] ?? null,
                ':td_sistolik' => $record['td_sistolik'] ?? null,
                ':td_diastolik' => $record['td_diastolik'] ?? null,
                ':fio2' => $record['fio2'] ?? null,
                ':spo2' => $record['spo2'] ?? null
            ];

            $stmt->execute($params);
            $success_count++;
        } else {
            error_log("Record index {$index} dilewati karena format tidak valid: " . print_r($record, true));
        }
    }

    $db->commit();
    error_log("Transaksi berhasil. {$success_count} record disimpan.");
    echo json_encode(['success' => true, 'message' => $success_count . ' data vital sign berhasil disimpan.']);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500); // Internal Server Error
    $error_message = 'Database Error: ' . $e->getMessage();
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500); // Internal Server Error
    $error_message = 'General Error: ' . $e->getMessage();
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}