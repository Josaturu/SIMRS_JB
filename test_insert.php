<?php
/**
 * Test INSERT Query - Persiapan Operasi
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== TESTING INSERT QUERY ===\n\n";
    
    // Dummy data
    $data = [
        'no_rawat' => 'TEST001',
        'kode_paket' => 'PKT001',
        'no_rm' => 'RM001',
        'nama' => 'Test Patient',
        'jenis_kelamin' => 'L',
        'umur' => '30',
        'tanggal_lahir' => '1995-01-01',
        'tanggal_operasi' => '2025-10-30',
        'macam_operasi' => 'Test Operation',
        'dpjp' => 'Dr. Test',
        'tinggi_badan' => 170,
        'berat_badan' => 70,
        'gol_darah' => 'A',
        'riwayat_alergi' => 'Tidak ada',
        // Keterangan fields
        'ket_program_ke_ubs' => 'Test ket 1',
        'ket_lavement' => 'Test ket lavement',
        'ket_cukur_daerah_operasi' => 'Test ket cukur',
        'ket_dm_insulin_preop' => 'Test ket dm',
    ];
    
    // Test prepare
    $query = "INSERT INTO tbl_anestesi_persiapan_operasi 
              (no_rawat, kode_paket, no_rm, nama, jenis_kelamin, umur, tanggal_lahir,
               tanggal_operasi, macam_operasi, dpjp, tinggi_badan, berat_badan, 
               gol_darah, riwayat_alergi,
               ket_program_ke_ubs, ket_lavement, ket_cukur_daerah_operasi, ket_dm_insulin_preop)
              VALUES 
              (:no_rawat, :kode_paket, :no_rm, :nama, :jenis_kelamin, :umur, :tanggal_lahir,
               :tanggal_operasi, :macam_operasi, :dpjp, :tinggi_badan, :berat_badan,
               :gol_darah, :riwayat_alergi,
               :ket_program_ke_ubs, :ket_lavement, :ket_cukur_daerah_operasi, :ket_dm_insulin_preop)";
    
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . print_r($db->errorInfo(), true));
    }
    
    echo "✅ Query prepared successfully\n\n";
    
    // Bind parameters
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    echo "✅ Parameters bound successfully\n\n";
    
    // Execute
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ INSERT executed successfully!\n";
        echo "   Inserted ID: " . $db->lastInsertId() . "\n\n";
        
        // Clean up test data
        $delete = $db->prepare("DELETE FROM tbl_anestesi_persiapan_operasi WHERE no_rawat = 'TEST001'");
        $delete->execute();
        echo "✅ Test data cleaned up\n";
    } else {
        throw new Exception("Execute failed: " . print_r($stmt->errorInfo(), true));
    }
    
    echo "\n✅ ALL TESTS PASSED!\n";
    echo "   Database is ready for production use.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
