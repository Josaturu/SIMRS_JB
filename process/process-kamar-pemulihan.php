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
        
        // Prepare instruksi data
        $instruksi = [
            'pemantauan' => [
                'setiap' => $formData['pemantauan_setiap'] ?? '',
                'selama' => $formData['pemantauan_selama'] ?? ''
            ],
            'analgesia' => $formData['analgesia'] ?? '',
            'anti_muntah' => $formData['anti_muntah'] ?? '',
            'antibiotik' => $formData['antibiotik'] ?? '',
            'posisi_pasien' => $formData['posisi_pasien'] ?? '',
            'obat_lain' => $formData['obat_lain'] ?? '',
            'diet_nutrisi' => $formData['diet_nutrisi'] ?? '',
            'lain_lain' => $formData['lain_lain'] ?? ''
        ];
        
        // Prepare data for main pemulihan table
        $dataPemulihan = [
            'id' => generateUUID(),
            'no_rawat' => sanitizeInput($formData['no_rawat']),
            'kode_paket' => sanitizeInput($formData['kode_paket']),
            'tanggal' => sanitizeInput($formData['tanggal']),
            'jam_mulai' => sanitizeInput($formData['jam_mulai']),
            'jam_masuk' => sanitizeInput($formData['jamMasuk']),
            'tgl_masuk' => sanitizeInput($formData['tglMasuk']),
            'jalan_nafas' => json_encode($formData['jalanNafas'] ?? [], JSON_UNESCAPED_UNICODE),
            'pernapasan' => json_encode($formData['pernapasan'] ?? [], JSON_UNESCAPED_UNICODE),
            'kesadaran' => json_encode($formData['kesadaran'] ?? [], JSON_UNESCAPED_UNICODE),
            'instruksi' => json_encode($instruksi, JSON_UNESCAPED_UNICODE)
        ];
        
        // Insert main pemulihan data
        if ($form->createKamarPemulihan($dataPemulihan)) {
            $id_pemulihan = $dataPemulihan['id'];
            
            // Insert vital sign data for each time
            for ($i = 1; $i <= 3; $i++) {
                $dataVital = [
                    'id' => generateUUID(),
                    'no_rawat' => $dataPemulihan['no_rawat'],
                    'kode_paket' => $dataPemulihan['kode_paket'],
                    'tanggal' => $dataPemulihan['tanggal'],
                    'jam_mulai' => $dataPemulihan['jam_mulai'],
                    'id_pemulihan' => $id_pemulihan,
                    'waktu_label' => "Waktu $i",
                    'nadi' => !empty($formData["nadi_$i"]) ? (int)$formData["nadi_$i"] : null,
                    'sistol' => !empty($formData["sistol_$i"]) ? (int)$formData["sistol_$i"] : null,
                    'diastol' => !empty($formData["diastol_$i"]) ? (int)$formData["diastol_$i"] : null,
                    'respirasi' => !empty($formData["respirasi_$i"]) ? (int)$formData["respirasi_$i"] : null,
                    'nyeri' => !empty($formData["nyeri_$i"]) ? (int)$formData["nyeri_$i"] : null
                ];
                
                $form->createVitalPemulihan($dataVital);
            }
            
            header("Location: ../index.php?page=kamar-pemulihan&status=sukses&id=" . $id_pemulihan);
        } else {
            header("Location: ../index.php?page=kamar-pemulihan&status=gagal");
        }
        
    } catch (Exception $e) {
        error_log("Error submitting kamar pemulihan: " . $e->getMessage());
        header("Location: ../index.php?page=kamar-pemulihan&status=error");
    }
    exit;
} else {
    header("Location: ../index.php?page=kamar-pemulihan");
    exit;
}
?>