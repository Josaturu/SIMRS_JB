<?php
session_start();

$page_title = "Checklist Persiapan Operasi";
$document_code = "RMO-1 a";

// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

// Jika tidak ada parameter, redirect ke daftar pasien
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header("Location: index.php");
    exit;
}

// Koneksi database untuk mendapatkan data booking DAN data pasien
$database = new Database();
$db = $database->getConnection();
$query = "SELECT b.*, p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp, p.gol_darah, p.tempat_lahir
          FROM booking_operasi b 
          LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
          WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

$pasien = $booking ? $booking : [];

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

// Cek apakah data checklist sudah ada
$query_checklist = "SELECT * FROM tbl_anestesi_persiapan_operasi 
                    WHERE no_rawat = ? AND kode_paket = ?";
$stmt_checklist = $db->prepare($query_checklist);
$stmt_checklist->execute([$no_rawat, $kode_paket]);
$existing_data = $stmt_checklist->fetch(PDO::FETCH_ASSOC);

// Helper function untuk checked state
function getRadioChecked($value, $compareValue) {
    if ($compareValue == 'ya') {
        return (!empty($value) && $value == 1) ? 'checked' : '';
    } else {
        return (empty($value) || $value == 0) ? 'checked' : '';
    }
}

// Mapping database fields untuk checklist UBS
$db_fields = [
    'program_ke_ubs', 'persetujuan_operasi', 'rekam_medis', 'laporan_operasi',
    'laporan_anestesi', 'hasil_lab', 'hasil_radiologi', 'hasil_ct_scan',
    'hasil_usg', 'hasil_ekg', 'hasil_lain',
    'puasa', 'lavement', 'pasang_dc', 'cukur_daerah_operasi',
    'rambut_makeup_dibersihkan', 'cat_kuku_dibersihkan', 'perhiasan_dilepas', 'transfusi_darah',
    'transfusi_whole_blood', 'transfusi_prc', 'transfusi_ffp',
    'premedikasi', 'antibiotik',
    'dm_insulin_preop', 'hipertensi_obat', 'asma_obat', 'obat_lain_radio',
    'obat_tidur', 'pasang_infus', 'tekanan_darah_radio', 'nadi_radio',
    'suhu_radio', 'pernafasan_radio', 'obat_ubs', 'skin_test_radio',
    'visit_dokter_bedah', 'visit_dokter_anestesi', 'visit_dokter_konsul_1', 'visit_dokter_konsul_2', 'visit_dokter_konsul_3'
];

// Mapping database fields untuk checklist R. RAWAT
$rawat_fields = [
    'rawat_program_ke_ubs', 'rawat_persetujuan_operasi', 'rawat_rekam_medis', 'rawat_laporan_operasi',
    'rawat_laporan_anestesi', 'rawat_hasil_lab', 'rawat_hasil_radiologi', 'rawat_hasil_ct_scan',
    'rawat_hasil_usg', 'rawat_hasil_ekg', 'rawat_hasil_lain',
    'rawat_puasa', 'rawat_lavement', 'rawat_pasang_dc', 'rawat_cukur_daerah_operasi',
    'rawat_rambut_makeup_dibersihkan', 'rawat_cat_kuku_dibersihkan', 'rawat_perhiasan_dilepas', 'rawat_transfusi_darah',
    'rawat_transfusi_whole_blood', 'rawat_transfusi_prc', 'rawat_transfusi_ffp',
    'rawat_premedikasi', 'rawat_antibiotik',
    'rawat_dm_insulin_preop', 'rawat_hipertensi_obat', 'rawat_asma_obat', 'rawat_obat_lain_radio',
    'rawat_obat_tidur', 'rawat_pasang_infus', 'rawat_tekanan_darah_radio', 'rawat_nadi_radio',
    'rawat_suhu_radio', 'rawat_pernafasan_radio', 'rawat_obat_ubs', 'rawat_skin_test_radio',
    'rawat_visit_dokter_bedah', 'rawat_visit_dokter_anestesi', 'rawat_visit_dokter_konsul_1', 'rawat_visit_dokter_konsul_2', 'rawat_visit_dokter_konsul_3'
];

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
    <?php
    // Tampilkan notifikasi
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
        echo '<div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">Data berhasil disimpan!</div>';
    }
    if (isset($_GET['status']) && $_GET['status'] == 'gagal') {
        echo '<div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">Gagal menyimpan data: ' . htmlspecialchars($_GET['error'] ?? 'Unknown error') . '</div>';
    }
    ?>
    <div class="title">
        <div style="color: #004d80;">CHECKLIST PERSIAPAN OPERASI</div>
        <div>RMO-1 a</div>
    </div>
    
    <!-- Form Data Booking -->
    <div class="card">
        <h2>Data Booking Operasi</h2>
        <div class="form-grid">
            <div class="form-column">
                <div class="input-container">
                    <input type="text" id="no_rawat" name="no_rawat" placeholder=" " value="<?php echo htmlspecialchars($no_rawat); ?>" readonly>
                    <label for="no_rawat" class="label-floating">No. Rawat</label>
                </div>
                <div class="input-container">
                    <input type="text" id="kode_paket" name="kode_paket" placeholder=" " value="<?php echo htmlspecialchars($kode_paket); ?>" readonly>
                    <label for="kode_paket" class="label-floating">Kode Paket</label>
                </div>
            </div>
            <div class="form-column">
                <div class="input-container">
                    <input type="date" id="tanggal" name="tanggal" placeholder=" " value="<?php echo htmlspecialchars($tanggal); ?>" readonly>
                    <label for="tanggal" class="label-floating">Tanggal Booking</label>
                </div>
                <div class="input-container">
                    <input type="time" id="jam_mulai" name="jam_mulai" placeholder=" " value="<?php echo htmlspecialchars($jam_mulai); ?>" readonly>
                    <label for="jam_mulai" class="label-floating">Jam Mulai</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Form dimulai di sini -->
    <?php
    // Get base URL for form action
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_url = $protocol . $host . $base_path;
    $form_action = rtrim($base_url, '/') . '/process/submit-persiapan-operasi.php';
    ?>
    <form id="formPersiapanOperasi" action="<?php echo htmlspecialchars($form_action); ?>" method="POST" data-no-loading>
        <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
        <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
        <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
        <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">

    <!-- Info Operasi -->
    <div class="card">
        <h2>Data Masuk dan Kondisi Pasien</h2>
        <div class="form-grid">
            <!-- Kolom Kiri -->
            <div class="form-column">
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="date" id="tglOperasi" name="tglOperasi" placeholder=" " value="<?php echo htmlspecialchars($existing_data['tanggal_operasi'] ?? ''); ?>" required>
                        <label for="tglOperasi" class="label-floating">Tanggal Operasi</label>
                    </div>
                </div>
                
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="text" id="macamOperasi" name="macamOperasi" placeholder=" " value="<?php echo htmlspecialchars($existing_data['macam_operasi'] ?? ''); ?>" required>
                        <label for="macamOperasi" class="label-floating">Macam Operasi</label>
                    </div>
                </div>
                
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="text" id="dpjp" name="dpjp" placeholder=" " value="<?php echo htmlspecialchars($existing_data['dpjp'] ?? ''); ?>">
                        <label for="dpjp" class="label-floating">DPJP (Dokter Penanggung Jawab Pelayanan)</label>
                    </div>
                </div>
                
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="number" id="tinggiBadan" name="tinggiBadan" placeholder=" " step="0.1" value="<?php echo htmlspecialchars($existing_data['tinggi_badan'] ?? ''); ?>">
                        <label for="tinggiBadan" class="label-floating">Tinggi Badan (cm)</label>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="form-column">
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="text" id="riwayatAlergi" name="riwayatAlergi" placeholder=" " value="<?php echo htmlspecialchars($existing_data['riwayat_alergi'] ?? ''); ?>">
                        <label for="riwayatAlergi" class="label-floating">Riwayat Alergi</label>
                    </div>
                </div>
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="number" id="beratBadan" name="beratBadan" placeholder=" " step="0.1" value="<?php echo htmlspecialchars($existing_data['berat_badan'] ?? ''); ?>">
                        <label for="beratBadan" class="label-floating">Berat Badan (kg)</label>
                    </div>
                </div>
                <div class="keterangan-pasien">
                    <label><strong>Gol. Darah:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="GolDarah" value="A" <?php echo (!empty($existing_data['gol_darah']) && $existing_data['gol_darah'] == 'A') ? 'checked' : ''; ?> />A</label>
                        <label><input type="radio" name="GolDarah" value="AB" <?php echo (!empty($existing_data['gol_darah']) && $existing_data['gol_darah'] == 'AB') ? 'checked' : ''; ?> />AB</label>
                        <label><input type="radio" name="GolDarah" value="O" <?php echo (!empty($existing_data['gol_darah']) && $existing_data['gol_darah'] == 'O') ? 'checked' : ''; ?> />O</label>
                        <label><input type="radio" name="GolDarah" value="B" <?php echo (!empty($existing_data['gol_darah']) && $existing_data['gol_darah'] == 'B') ? 'checked' : ''; ?> />B</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklist -->
    <div class="card">
        <h2>Checklist Persiapan Operasi</h2>
            <table>
                <tr>
                    <th></th>
                    <th>
                        <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                            <span>R. RAWAT</span>
                            <div style="display: flex; gap: 5px;">
                                <button type="button" class="btn-mini" onclick="checkAllColumn('rawat', 'ya')" style="padding: 2px 8px; background: #17a2b8; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 10px;" title="Ceklis Ya semua di kolom R. RAWAT">
                                    ✓ Ya
                                </button>
                                <button type="button" class="btn-mini" onclick="checkAllColumn('rawat', 'tidak')" style="padding: 2px 8px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 10px;" title="Ceklis Tidak semua di kolom R. RAWAT">
                                    ✗ Tidak
                                </button>
                            </div>
                        </div>
                    </th>
                    <th>
                        <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                            <span>UBS</span>
                            <div style="display: flex; gap: 5px;">
                                <button type="button" class="btn-mini" onclick="checkAllColumn('ubs', 'ya')" style="padding: 2px 8px; background: #17a2b8; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 10px;" title="Ceklis Ya semua di kolom UBS">
                                    ✓ Ya
                                </button>
                                <button type="button" class="btn-mini" onclick="checkAllColumn('ubs', 'tidak')" style="padding: 2px 8px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 10px;" title="Ceklis Tidak semua di kolom UBS">
                                    ✗ Tidak
                                </button>
                            </div>
                        </div>
                    </th>
                    <th>Keterangan</th>
                </tr>

                <!-- Administrasi -->
                <tr class="section-title">
                    <td colspan="4" style="font-weight: bold; background-color: #4285f4; color: #fff;">
                        1. Persiapan Administrasi
                    </td>
                </tr>
                
                <?php
                $checklist_items = [
                    'Program ke UBS',
                    'Persetujuan Operasi Lengkap dan Terisi',
                    'Rekam Medis',
                    'Laporan Operasi',
                    'Laporan Anestesi',
                    'Hasil Laboratorium',
                    'Hasil Radiologi',
                    'Hasil CT Scan',
                    'Hasil USG',
                    'Hasil EKG',
                    'Lain-lain'
                ];

                for ($i = 1; $i <= count($checklist_items); $i++): 
                    $item = $checklist_items[$i-1];
                    $db_field = $db_fields[$i-1];
                    $rawat_field = $rawat_fields[$i-1];
                ?>
                <tr class="checklist-row-administrasi">
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td><input type="text" name="ket<?php echo $i; ?>"></td>
                </tr>
                <?php endfor; ?>

                <!-- Fisik -->
                <tr class="section-title">
                    <td colspan="4" style="font-weight: bold; background-color: #4285f4; color: #fff;">
                        2. Persiapan Fisik
                    </td>
                </tr>
                
                <?php
                $fisik_items = [
                    'Puasa',
                    'Lavement/garam Inggris',
                    'Pasang DC',
                    'Cukur dan bersihkan daerah operasi',
                    'Rambut palsu, gigi palsu, contact lens, sudah dilepas',
                    'Cat kuku dan make up muka sudah dibersihkan',
                    'Perhiasan dan arloji dll, sudah dilepas',
                    'Persiapan darah untuk transfusi',
                    '1) Whole Blood (WB)',
                    '2) PRC',
                    '3) FFP',
                    'Premedikasi',
                    'Antibiotik pre-ops'
                ];

                for ($i = 12; $i <= 24; $i++): 
                    $item = $fisik_items[$i-12];
                    $db_field = $db_fields[$i-1];
                    $rawat_field = $rawat_fields[$i-1];
                ?>
                <tr class="checklist-row-fisik">
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <?php if($i == 12): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Sejak:" value="<?php echo htmlspecialchars($existing_data['waktu_puasa'] ?? ''); ?>">
                        <?php elseif($i == 14): ?>
                            <input style="width: 45%;" type="number" name="ket<?php echo $i; ?>" placeholder="No." value="<?php echo htmlspecialchars($existing_data['dc_no'] ?? ''); ?>"> / 
                            <input style="width: 52%;" type="text" name="ket<?php echo $i; ?>_macam" placeholder="Macam" value="<?php echo htmlspecialchars($existing_data['dc_macam'] ?? ''); ?>">
                        <?php elseif($i == 20): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Kantong:" value="<?php echo htmlspecialchars($existing_data['kantong_wb'] ?? ''); ?>">
                        <?php elseif($i == 21): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Kantong:" value="<?php echo htmlspecialchars($existing_data['kantong_prc'] ?? ''); ?>">
                        <?php elseif($i == 22): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Kantong:" value="<?php echo htmlspecialchars($existing_data['kantong_ffp'] ?? ''); ?>">
                        <?php elseif($i == 24): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Nama Antibiotik" value="<?php echo htmlspecialchars($existing_data['antibiotik_preops'] ?? ''); ?>" style="width: 48%; margin-right: 2%;">
                            <input type="time" name="ket<?php echo $i; ?>_waktu" value="<?php echo htmlspecialchars($existing_data['jam_antibiotik'] ?? ''); ?>" style="width: 30%;">
                            <label for="ket24_waktu"> WIB</label>
                        <?php else: ?>
                            <input type="text" name="ket<?php echo $i; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>

                <!-- Khusus -->
                <tr class="section-title">
                    <td colspan="4" style="font-weight: bold; background-color: #4285f4; color: #fff;">
                        3. Persiapan Khusus
                    </td>
                </tr>
                
                <?php
                $khusus_items = [
                    'DM - Insulin Pre Op',
                    'Hipertensi - Obat anti hipertensi pre ops',
                    'Asma - Obat anti asma / Corticosteroid pre ops',
                    'Obat Lain',
                    'Obat-obatan sebelum tidur',
                    'Pasang Infus',
                    'Tekanan Darah',
                    'Nadi',
                    'Suhu',
                    'Pernapasan',
                    'Obat yang dibawa ke UBS',
                    'Hasil Skin Test',
                    'Kunjungan dokter pra bedah - Dokter Bedah',
                    'Kunjungan dokter pra bedah - Dokter Anestesi',
                    'Kunjungan dokter pra bedah - Dokter Konsul terkait',
                    'Kunjungan dokter pra bedah - Dokter Konsul terkait',
                    'Kunjungan dokter pra bedah - Dokter Konsul terkait'
                ];

                for ($i = 25; $i <= 41; $i++): 
                    $item = $khusus_items[$i-25];
                    $db_field = $db_fields[$i-1];
                    $rawat_field = $rawat_fields[$i-1];
                ?>
                <tr class="checklist-row-khusus">
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak" <?php echo $rawat_field ? getRadioChecked($existing_data[$rawat_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'ya') : ''; ?>> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak" <?php echo $db_field ? getRadioChecked($existing_data[$db_field] ?? 0, 'tidak') : ''; ?>> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <?php if($i == 28): ?>
                            <input type="text" name="ket<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_data['obat_lain'] ?? ''); ?>">
                        <?php elseif($i == 30): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="IV Catch No:" value="<?php echo htmlspecialchars($existing_data['iv_catch_no'] ?? ''); ?>">
                        <?php elseif($i == 31): ?>
                            <input type="text" name="ket<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_data['tekanan_darah'] ?? ''); ?>">
                        <?php elseif($i == 32): ?>
                            <input type="text" name="ket<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_data['nadi'] ?? ''); ?>">
                        <?php elseif($i == 33): ?>
                            <input type="text" name="ket<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_data['suhu'] ?? ''); ?>">
                        <?php elseif($i == 34): ?>
                            <input type="text" name="ket<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_data['pernafasan'] ?? ''); ?>">
                        <?php elseif($i == 36): ?>
                            <div class="radio-group">
                                <label><input type="radio" name="ket<?php echo $i; ?>" value="positif" <?php echo (!empty($existing_data['hasil_skin_test']) && $existing_data['hasil_skin_test'] == 'Positif') ? 'checked' : ''; ?>> Positif</label>
                                <label><input type="radio" name="ket<?php echo $i; ?>" value="negatif" <?php echo (!empty($existing_data['hasil_skin_test']) && $existing_data['hasil_skin_test'] == 'Negatif') ? 'checked' : ''; ?>> Negatif</label>
                            </div>
                        <?php else: ?>
                            <input type="text" name="ket<?php echo $i; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $existing_data ? 'Simpan Perubahan' : 'Simpan Data'; ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?page=detail-pasien&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>'">Kembali</button>
            </div>
        </form>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script src="/assets/js/autosave.js"></script>
<script>
    // Function untuk ceklis semua dalam satu kolom (R. RAWAT atau UBS)
    function checkAllColumn(column, value) {
        // Tentukan kolom mana (2 = R. RAWAT, 3 = UBS)
        const columnIndex = column === 'rawat' ? 2 : 3;
        
        // Ambil semua row checklist (yang punya class checklist-row-*)
        const allRows = document.querySelectorAll('[class*="checklist-row-"]');
        
        let count = 0;
        allRows.forEach(function(row) {
            // Ambil radio button di kolom yang dipilih
            const radios = row.querySelectorAll(`td:nth-child(${columnIndex}) input[type="radio"]`);
            
            radios.forEach(function(radio) {
                if (radio.value === value) {
                    radio.checked = true;
                    // Trigger change event untuk autosave
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                    count++;
                }
            });
        });
        
        // Tampilkan notifikasi
        const columnName = column === 'rawat' ? 'R. RAWAT' : 'UBS';
        const valueText = value === 'ya' ? 'Ya' : 'Tidak';
        
        showNotification(`✓ Semua item kolom ${columnName} diset ke "${valueText}" (${count} item)`, '#17a2b8');
    }
    
    // Function untuk menampilkan notifikasi
    function showNotification(message, bgColor) {
        // Buat notifikasi sementara
        const notification = document.createElement('div');
        notification.style.cssText = `position: fixed; top: 20px; right: 20px; background: ${bgColor}; color: white; padding: 15px 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 9999; animation: slideIn 0.3s ease;`;
        notification.innerHTML = message;
        document.body.appendChild(notification);
        
        // Hapus notifikasi setelah 2 detik
        setTimeout(function() {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(function() {
                document.body.removeChild(notification);
            }, 300);
        }, 2000);
    }
    
    // Tambahkan CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .btn-mini:hover {
            opacity: 0.85;
            transform: scale(1.08);
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .btn-mini:active {
            transform: scale(0.95);
        }
    `;
    document.head.appendChild(style);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AutoSave
        AutoSave.init('formPersiapanOperasi', {
            debounce: 1000,
            exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
            showNotification: true,
            clearOnSubmit: true
        });
    });
</script>
