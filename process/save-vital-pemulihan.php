<?php
// Save Vital Sign Data to tbl_anestesi_vital_pemulihan
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil data dari request
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        // Validasi data minimal
        if (empty($data['no_rawat']) || empty($data['kode_paket']) || empty($data['tanggal']) || empty($data['jam_mulai'])) {
            throw new Exception("Missing required fields: no_rawat, kode_paket, tanggal, jam_mulai");
        }
        
        // Ambil vital sign records dari array
        $vitalSigns = $data['vital_signs'] ?? [];
        
        if (empty($vitalSigns)) {
            throw new Exception("No vital sign records to save");
        }
        
        $no_rawat = $data['no_rawat'];
        $kode_paket = $data['kode_paket'];
        $tanggal = $data['tanggal'];
        $jam_mulai = $data['jam_mulai'];
        $id_pemulihan = $data['id_pemulihan'] ?? null;
        
        // Hapus data lama untuk booking ini (opsional, atau bisa update)
        // Untuk sekarang, kita akan insert baru saja
        
        $insertedCount = 0;
        $errors = [];
        
        foreach ($vitalSigns as $record) {
            try {
                // Generate UUID untuk setiap record
                $id = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                // Buat waktu lengkap dari jam dan tanggal
                $waktu_label = $record['jam'] ?? '';
                $waktu = null;
                
                if (!empty($waktu_label)) {
                    // Jika jam ada, buat datetime lengkap
                    $waktu = $tanggal . ' ' . $waktu_label . ':00';
                }
                
                // Prepare insert query
                $query = "INSERT INTO tbl_anestesi_vital_pemulihan (
                    id, no_rawat, kode_paket, tanggal, jam_mulai,
                    waktu, id_pemulihan, waktu_label,
                    nadi, td_sistolik, td_diastolik, respirasi, nyeri, spo2, suhu
                ) VALUES (
                    :id, :no_rawat, :kode_paket, :tanggal, :jam_mulai,
                    :waktu, :id_pemulihan, :waktu_label,
                    :nadi, :td_sistolik, :td_diastolik, :respirasi, :nyeri, :spo2, :suhu
                )";
                
                $stmt = $db->prepare($query);
                
                $nadi = $record['nadi'] ?? null;
                $sistol = $record['sistol'] ?? null;
                $diastolik = $record['diastol'] ?? null; // Ambil dari record
                $respirasi = $record['respirasi'] ?? null;
                $nyeri = $record['nyeri'] ?? null;
                $spo2 = $record['spo2'] ?? null;
                $suhu = null; // Tidak ada di form
                
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':no_rawat', $no_rawat);
                $stmt->bindParam(':kode_paket', $kode_paket);
                $stmt->bindParam(':tanggal', $tanggal);
                $stmt->bindParam(':jam_mulai', $jam_mulai);
                $stmt->bindParam(':waktu', $waktu);
                $stmt->bindParam(':id_pemulihan', $id_pemulihan);
                $stmt->bindParam(':waktu_label', $waktu_label);
                $stmt->bindParam(':nadi', $nadi);
                $stmt->bindParam(':td_sistolik', $sistol);
                $stmt->bindParam(':td_diastolik', $diastolik);
                $stmt->bindParam(':respirasi', $respirasi);
                $stmt->bindParam(':nyeri', $nyeri);
                $stmt->bindParam(':spo2', $spo2);
                $stmt->bindParam(':suhu', $suhu);
                
                if ($stmt->execute()) {
                    $insertedCount++;
                } else {
                    $errors[] = "Failed to insert record: " . json_encode($record);
                }
            } catch (Exception $e) {
                $errors[] = "Error processing record: " . $e->getMessage();
            }
        }
        
        // Return response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Successfully saved $insertedCount vital sign records",
            'inserted_count' => $insertedCount,
            'errors' => $errors
        ]);
        
    } catch (Exception $e) {
        error_log("Error saving vital sign: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
?>
