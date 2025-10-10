<?php
require_once '../config/database.php';
require_once '../models/FormModel.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $form = new FormModel($db);
        
        // Collect all form data
        $formData = $_POST;
        
        // Prepare hasil_pemeriksaan JSON
        $hasil_pemeriksaan = [];
        
        // Collect checklist data
        for ($i = 1; $i <= 41; $i++) {
            $hasil_pemeriksaan["rawat_$i"] = $formData["rawat$i"] ?? '';
            $hasil_pemeriksaan["ubs_$i"] = $formData["ubs$i"] ?? '';
            $hasil_pemeriksaan["ket_$i"] = $formData["ket$i"] ?? '';
            
            // Handle special cases
            if ($i == 14) {
                $hasil_pemeriksaan["ket_{$i}_macam"] = $formData["ket{$i}_macam"] ?? '';
            }
            if ($i == 24) {
                $hasil_pemeriksaan["ket_{$i}_waktu"] = $formData["ket{$i}_waktu"] ?? '';
            }
        }
        
        // Prepare data for database
        $data = [
            'id' => generateUUID(),
            'no_rawat' => sanitizeInput($formData['no_rawat']),
            'kode_paket' => sanitizeInput($formData['kode_paket']),
            'tanggal' => sanitizeInput($formData['tanggal']),
            'jam_mulai' => sanitizeInput($formData['jam_mulai']),
            'tgl_operasi' => sanitizeInput($formData['tglOperasi']),
            'macam_operasi' => sanitizeInput($formData['macamOperasi']),
            'tinggi_badan' => !empty($formData['tinggiBadan']) ? $formData['tinggiBadan'] : null,
            'berat_badan' => !empty($formData['beratBadan']) ? $formData['beratBadan'] : null,
            'gol_darah' => sanitizeInput($formData['GolDarah'] ?? ''),
            'riwayat_alergi' => sanitizeInput($formData['riwayatAlergi'] ?? ''),
            'hasil_pemeriksaan' => json_encode($hasil_pemeriksaan, JSON_UNESCAPED_UNICODE)
        ];
        
        if ($form->createChecklistPersiapan($data)) {
            header("Location: ../index.php?page=persiapan-operasi&status=sukses&id=" . $data['id']);
        } else {
            header("Location: ../index.php?page=persiapan-operasi&status=gagal");
        }
        
    } catch (Exception $e) {
        error_log("Error submitting persiapan operasi: " . $e->getMessage());
        header("Location: ../index.php?page=persiapan-operasi&status=error");
    }
    exit;
} else {
    header("Location: ../index.php?page=persiapan-operasi");
    exit;
}
?>