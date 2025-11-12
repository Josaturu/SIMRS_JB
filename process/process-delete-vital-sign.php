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
    
    // Ambil ID record yang akan dihapus
    $id = $data['id'] ?? '';
    
    // Validasi ID
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID record tidak ditemukan.'
        ]);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Delete query
    $query = "DELETE FROM tbl_anestesi_vital_sign WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->rowCount();
        
        if ($affected_rows > 0) {
            // Commit transaction
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Data vital sign berhasil dihapus.',
                'deleted_id' => $id
            ]);
        } else {
            throw new Exception('Data tidak ditemukan atau sudah dihapus.');
        }
    } else {
        throw new Exception('Gagal menghapus data dari database.');
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
