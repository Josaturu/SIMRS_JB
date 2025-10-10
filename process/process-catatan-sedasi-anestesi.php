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
        'tanggal_tindakan' => $_POST['tanggalTindakan'],
        'pukul' => $_POST['pukul'],
        'dokter_bedah' => $_POST['dokterBedah'],
        'perawat_bedah' => $_POST['perawatBedah'],
        'dokter_anestesi' => $_POST['dokterAnestesi'],
        'perawat_anestesi' => $_POST['perawatAnestesi'],
        'jenis_pembedahan' => json_encode($_POST['jenisPembedahan'] ?? []),
        'diagnosa' => $_POST['diagnosa'],
        'asesmen' => $_POST['assessment'],
        'bb_kg' => $_POST['bb'],
        'td_mmHg' => $_POST['td'],
        'suhu' => $_POST['suhu'],
        'respirasi' => $_POST['respirasi'],
        'hb' => $_POST['hb'],
        'tb_cm' => $_POST['tb'],
        'nadi' => $_POST['nadi'],
        'gcs' => $_POST['gcs'],
        'gol_darah' => $_POST['golonganDarah'],
        'skrining_nyeri' => $_POST['skriningNyeri'],
        'status_fisik_asa' => $_POST['statusFisikASA'],
        'penyulit_pra_anestesi' => $_POST['penyulitPraAnestesi'],
        'jenis_anestesi' => json_encode($_POST['jenisAnestesi'] ?? []),
        'risiko' => $_POST['risiko'],
        'checklist_sebelum_induksi' => json_encode($_POST['checklistSebelumInduksi'] ?? []),
        'infus_perifer' => json_encode([
            $_POST['infusPerifer1'] ?? '',
            $_POST['infusPerifer2'] ?? '',
            $_POST['infusPerifer3'] ?? ''
        ]),
        'posisi' => json_encode($_POST['posisi'] ?? []),
        'premedikasi_jenis' => json_encode($_POST['premedikasiJenis'] ?? []),
        'premedikasi_nama_obat' => $_POST['premedikasiNamaObat'],
        'premedikasi_dosis_obat' => $_POST['premedikasiDosisObat'],
        'induksi' => json_encode($_POST['induksi'] ?? []),
        'jalan_nafas' => json_encode($_POST['jalanNafas'] ?? []),
        'ventilasi' => json_encode($_POST['ventilasi'] ?? []),
        'ventilator_tv' => $_POST['ventilatorTV'],
        'ventilator_rr' => $_POST['ventilatorRR'],
        'ventilator_spo2' => $_POST['ventilatorSpO2'],
        'ventilator_peep' => $_POST['ventilatorPEEP'],
        'ukuran' => $_POST['ukuran'],
        'balon' => json_encode($_POST['balon'] ?? []),
        'rute' => json_encode($_POST['rute'] ?? []),
        'lokasi' => $_POST['lokasi'],
        'jarum_no' => $_POST['jarumNo'],
        'kateter' => $_POST['kateter'],
        'obat_anestesi_lokal' => $_POST['obatAnestesiLokal'],
        'hasil_anestesi_regional' => json_encode($_POST['hasilAnestesiRegional'] ?? []),
        'obat_obatan' => json_encode([
            $_POST['obat1'] ?? '',
            $_POST['obat2'] ?? '',
            $_POST['obat3'] ?? '',
            $_POST['obat4'] ?? '',
            $_POST['obat5'] ?? '',
            $_POST['obat6'] ?? '',
            $_POST['obat7'] ?? '',
            $_POST['obat8'] ?? ''
        ]),
        'cairan_infus' => json_encode([
            $_POST['infus1'] ?? '',
            $_POST['infus2'] ?? '',
            $_POST['infus3'] ?? '',
            $_POST['infus4'] ?? ''
        ]),
        'cairan_output' => json_encode([
            $_POST['output1'] ?? '',
            $_POST['output2'] ?? '',
            $_POST['output3'] ?? '',
            $_POST['output4'] ?? ''
        ]),
        'masalah_anestesi' => json_encode([
            $_POST['masalah1'] ?? '',
            $_POST['masalah2'] ?? '',
            $_POST['masalah3'] ?? '',
            $_POST['masalah4'] ?? ''
        ]),
        'tindakan' => json_encode([
            $_POST['tindakan1'] ?? '',
            $_POST['tindakan2'] ?? '',
            $_POST['tindakan3'] ?? '',
            $_POST['tindakan4'] ?? ''
        ]),
        'perawat_menyerahkan' => $_POST['perawatMenyerahkan'],
        'perawat_menerima' => $_POST['perawatMenerima'],
        'dokter_anestesi_serah' => $_POST['dokterAnestesiSerah']
    ];
    
    if ($formModel->createCatatanAnestesi($data)) {
        header("Location: ../views/detail-pasien.php?no_rawat=" . $_POST['no_rawat'] . "&success=1");
        exit;
    } else {
        header("Location: ../views/form-catatan-sedasi-anestesi.php?no_rawat=" . $_POST['no_rawat'] . "&kode_paket=" . $_POST['kode_paket'] . "&tanggal=" . $_POST['tanggal'] . "&jam_mulai=" . $_POST['jam_mulai'] . "&error=1");
        exit;
    }
}
?>