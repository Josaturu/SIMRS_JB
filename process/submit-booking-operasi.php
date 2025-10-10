<?php
require_once '../config/database.php';
require_once '../models/FormModel.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $form = new FormModel($db);
        
        // Generate kode_paket otomatis
        $kode_paket = $form->generateKodePaket(
            $_POST['no_rawat'], 
            $_POST['nama_pasien']
        );
        
        // Prepare data untuk booking operasi
        $data = [
            'no_rawat' => sanitizeInput($_POST['no_rawat']),
            'kode_paket' => $kode_paket,
            'tanggal' => sanitizeInput($_POST['tanggal']),
            'jam_mulai' => sanitizeInput($_POST['jam_mulai']),
            'jam_selesai' => !empty($_POST['jam_selesai']) ? sanitizeInput($_POST['jam_selesai']) : null,
            'status' => 'Menunggu',
            'kd_dokter' => sanitizeInput($_POST['kd_dokter']),
            'kd_ruang_ok' => sanitizeInput($_POST['kd_ruang_ok'])
        ];
        
        if ($form->createBookingOperasi($data)) {
            header("Location: ../index.php?status=sukses&kode_paket=" . $kode_paket);
        } else {
            header("Location: ../index.php?status=gagal");
        }
        
    } catch (Exception $e) {
        error_log("Error submitting booking operasi: " . $e->getMessage());
        header("Location: ../index.php?status=error");
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>