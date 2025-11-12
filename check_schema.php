<?php
/**
 * Check Database Schema - tbl_anestesi_persiapan_operasi
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== CHECKING DATABASE SCHEMA ===\n\n";
    
    // Get all columns
    $query = "SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi";
    $stmt = $db->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Columns: " . count($columns) . "\n\n";
    
    // Filter ket_ columns
    $ket_columns = array_filter($columns, function($col) {
        return strpos($col['Field'], 'ket_') === 0;
    });
    
    echo "=== KET_ COLUMNS (" . count($ket_columns) . ") ===\n";
    foreach ($ket_columns as $col) {
        echo "✓ " . $col['Field'] . " - " . $col['Type'] . "\n";
    }
    
    echo "\n=== EXPECTED KET_ COLUMNS (25) ===\n";
    $expected = [
        // Administrasi (11)
        'ket_program_ke_ubs',
        'ket_persetujuan_operasi',
        'ket_rekam_medis',
        'ket_laporan_operasi',
        'ket_laporan_anestesi',
        'ket_hasil_lab',
        'ket_hasil_radiologi',
        'ket_hasil_ct_scan',
        'ket_hasil_usg',
        'ket_hasil_ekg',
        'ket_hasil_lain',
        // Fisik (7)
        'ket_lavement',
        'ket_cukur_daerah_operasi',
        'ket_rambut_makeup_dibersihkan',
        'ket_cat_kuku_dibersihkan',
        'ket_perhiasan_dilepas',
        'ket_transfusi_darah',
        'ket_premedikasi',
        // Khusus (7)
        'ket_dm_insulin_preop',
        'ket_hipertensi_obat',
        'ket_asma_obat',
        'ket_obat_tidur',
        'ket_obat_ubs',
        'ket_visit_dokter_bedah',
        'ket_visit_dokter_anestesi',
    ];
    
    $existing_fields = array_column($ket_columns, 'Field');
    
    foreach ($expected as $field) {
        if (in_array($field, $existing_fields)) {
            echo "✅ $field\n";
        } else {
            echo "❌ $field - MISSING!\n";
        }
    }
    
    $missing = array_diff($expected, $existing_fields);
    if (!empty($missing)) {
        echo "\n⚠️ MISSING FIELDS (" . count($missing) . "):\n";
        foreach ($missing as $field) {
            echo "   - $field\n";
        }
        echo "\n🔧 ACTION REQUIRED: Run SQL migration!\n";
        echo "   File: sql/ADD_KETERANGAN_FIELDS_PERSIAPAN_OPERASI.sql\n";
    } else {
        echo "\n✅ ALL FIELDS EXIST!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
