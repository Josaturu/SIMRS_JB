<?php
// Fetch Vital Sign Data from tbl_anestesi_vital_pemulihan
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil parameter dari query string
        $no_rawat = $_GET['no_rawat'] ?? null;
        $kode_paket = $_GET['kode_paket'] ?? null;
        $tanggal = $_GET['tanggal'] ?? null;
        $jam_mulai = $_GET['jam_mulai'] ?? null;
        
        // Validasi parameter
        if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
            throw new Exception("Missing required parameters: no_rawat, kode_paket, tanggal, jam_mulai");
        }
        
        // Query untuk fetch vital sign data, sorted by waktu ASC (earliest first)
        $query = "SELECT 
                    id,
                    no_rawat,
                    kode_paket,
                    tanggal,
                    jam_mulai,
                    waktu,
                    waktu_label,
                    nadi,
                    td_sistolik,
                    td_diastolik,
                    respirasi,
                    nyeri,
                    spo2,
                    suhu
                  FROM tbl_anestesi_vital_pemulihan
                  WHERE no_rawat = :no_rawat 
                    AND kode_paket = :kode_paket 
                    AND tanggal = :tanggal 
                    AND jam_mulai = :jam_mulai
                  ORDER BY waktu ASC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':no_rawat', $no_rawat);
        $stmt->bindParam(':kode_paket', $kode_paket);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':jam_mulai', $jam_mulai);
        
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Transform records ke format yang sesuai dengan frontend
        $vitalSigns = [];
        foreach ($records as $record) {
            $vitalSigns[] = [
                'id' => $record['id'],
                'jam' => $record['waktu_label'] ?? '',
                'respirasi' => (int)($record['respirasi'] ?? 0),
                'nadi' => (int)($record['nadi'] ?? 0),
                'sistol' => (int)($record['td_sistolik'] ?? 0),
                'diastol' => $record['td_diastolik'] ? (int)$record['td_diastolik'] : null,
                'nyeri' => (int)($record['nyeri'] ?? 0),
                'spo2' => (int)($record['spo2'] ?? 0),
                'waktu' => $record['waktu'],
                'suhu' => $record['suhu']
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'count' => count($vitalSigns),
            'data' => $vitalSigns
        ]);
        
    } catch (Exception $e) {
        error_log("Error fetching vital sign: " . $e->getMessage());
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
