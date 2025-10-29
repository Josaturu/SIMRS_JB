<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    // Ambil data JSON dari request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Ambil parameter utama
    $no_rawat = $data['no_rawat'] ?? '';
    $kode_paket = $data['kode_paket'] ?? '';
    $tanggal = $data['tanggal'] ?? '';
    $jam_mulai = $data['jam_mulai'] ?? '';
    $vital_signs = $data['vital_signs'] ?? [];

    // Validasi required fields
    if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
        echo json_encode([
            'success' => false,
            'message' => 'Parameter tidak lengkap (no_rawat, kode_paket, tanggal, jam_mulai).'
        ]);
        exit;
    }
    
    // Validasi vital signs array
    if (empty($vital_signs) || !is_array($vital_signs)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data vital sign tidak valid atau kosong.'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Hapus data lama untuk session ini (optional - jika ingin replace)
    $delete_query = "DELETE FROM tbl_anestesi_vital_sign 
                     WHERE no_rawat = :no_rawat 
                     AND kode_paket = :kode_paket 
                     AND tanggal = :tanggal 
                     AND jam_mulai = :jam_mulai";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':no_rawat', $no_rawat);
    $delete_stmt->bindParam(':kode_paket', $kode_paket);
    $delete_stmt->bindParam(':tanggal', $tanggal);
    $delete_stmt->bindParam(':jam_mulai', $jam_mulai);
    $delete_stmt->execute();
    
    // Insert query
    $query = "INSERT INTO tbl_anestesi_vital_sign
              (id, no_rawat, kode_paket, tanggal, jam_mulai, waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2)
              VALUES (UUID(), :no_rawat, :kode_paket, :tanggal, :jam_mulai, :waktu, :respirasi, :nadi, :td_sistolik, :td_diastolik, :fio2, :spo2)";

    $stmt = $db->prepare($query);
    
    $success_count = 0;
    $error_messages = [];
    
    // Loop through vital signs dan insert satu per satu
    foreach ($vital_signs as $index => $record) {
        // Validasi record
        if (!isset($record['jam']) || !isset($record['respirasi']) || !isset($record['nadi']) || 
            !isset($record['sistol']) || !isset($record['diastol']) || !isset($record['fio2']) || !isset($record['spo2'])) {
            $error_messages[] = "Record #" . ($index + 1) . " tidak lengkap";
            continue;
        }
        
        // Konversi jam ke datetime (tanggal + jam)
        $waktu_datetime = $tanggal . ' ' . $record['jam'];
        
        // Bind parameters
        $stmt->bindParam(':no_rawat', $no_rawat);
        $stmt->bindParam(':kode_paket', $kode_paket);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':jam_mulai', $jam_mulai);
        $stmt->bindParam(':waktu', $waktu_datetime);
        $stmt->bindParam(':respirasi', $record['respirasi'], PDO::PARAM_INT);
        $stmt->bindParam(':nadi', $record['nadi'], PDO::PARAM_INT);
        $stmt->bindParam(':td_sistolik', $record['sistol'], PDO::PARAM_INT);
        $stmt->bindParam(':td_diastolik', $record['diastol'], PDO::PARAM_INT);
        $stmt->bindParam(':fio2', $record['fio2'], PDO::PARAM_INT);
        $stmt->bindParam(':spo2', $record['spo2'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_messages[] = "Gagal menyimpan record #" . ($index + 1);
        }
    }
    
    // Commit transaction
    $db->commit();
    
    if ($success_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Berhasil menyimpan $success_count dari " . count($vital_signs) . " data vital sign.",
            'saved_count' => $success_count,
            'total_count' => count($vital_signs)
        ]);
    } else {
        throw new Exception('Gagal menyimpan data vital sign: ' . implode(', ', $error_messages));
    }

} catch (Throwable $exception) {
    // Rollback transaction jika ada error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}