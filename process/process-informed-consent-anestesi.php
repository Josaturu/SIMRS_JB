<?php
include_once '../config/database.php';
include_once '../models/formmodel.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $formModel = new FormModel($db);
    
    // Generate UUID
    $id = uniqid();
    
    // Prepare data
    $data = [
        'id' => $id,
        'no_rawat' => $_POST['no_rawat'],
        'kode_paket' => $_POST['kode_paket'],
        'tanggal' => $_POST['tanggal'],
        'jam_mulai' => $_POST['jam_mulai'],
        'ruang' => $_POST['ruang'],
        'dokter_pelaksana' => $_POST['dokter'],
        'pemberi_info' => $_POST['pemberi'],
        'jabatan' => $_POST['jabatan'],
        'penerima_info' => $_POST['penerima'],
        'hubungan_pasien' => $_POST['hubungan'],
        'tindakan_operasi' => $_POST['tindakanOperasi'],
        'jenis_anestesi' => implode(',', $_POST['jenisAnestesi'] ?? []),
        'indikasi' => implode(',', $_POST['indikasi'] ?? []),
        'tata_cara' => implode(',', $_POST['tataCara'] ?? []),
        'tujuan' => 'Memfasilitasi tindakan pembedahan, agar pasien dan dokter operator aman dan nyaman',
        'risiko' => implode(',', $_POST['risiko'] ?? [])
    ];
    
    if ($formModel->createInformedConsent($data)) {
        header("Location: ../views/detail-pasien.php?no_rawat=" . $_POST['no_rawat'] . "&success=1");
        exit;
    } else {
        header("Location: ../views/form-informed-consent-anestesi.php?no_rawat=" . $_POST['no_rawat'] . "&kode_paket=" . $_POST['kode_paket'] . "&tanggal=" . $_POST['tanggal'] . "&jam_mulai=" . $_POST['jam_mulai'] . "&error=1");
        exit;
    }
}
?>