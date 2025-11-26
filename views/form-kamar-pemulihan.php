<?php
$page_title = "Catatan Kamar Pemulihan";
$document_code = "RMOK - 30";

// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

// Jika tidak ada parameter, redirect ke daftar pasien
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    // Show error message if available
    $errorMsg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Parameter tidak lengkap';
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<strong>❌ Error:</strong> {$errorMsg}<br>";
    echo "<a href='index.php?page=daftar-pasien' style='color: #004085;'>← Kembali ke Daftar Pasien</a>";
    echo "</div>";
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

// Ambil semua dokter dari tbl_dokter untuk dropdown
$query_dokter = "SELECT id_dokter, nama_dokter FROM tbl_dokter ORDER BY nama_dokter ASC";
$stmt_dokter = $db->prepare($query_dokter);
$stmt_dokter->execute();
$dokter_list = $stmt_dokter->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua perawat dari tbl_perawat untuk dropdown
$query_perawat = "SELECT id_perawat, nama_perawat FROM tbl_perawat ORDER BY nama_perawat ASC";
$stmt_perawat = $db->prepare($query_perawat);
$stmt_perawat->execute();
$perawat_list = $stmt_perawat->fetchAll(PDO::FETCH_ASSOC);

// ===== HANDLE VITAL SIGN DATA SUBMISSION =====
// CATATAN: Vital sign data sekarang dihandle di submit-kamar-pemulihan.php
// Tidak perlu dihandle di sini lagi untuk menghindari duplikasi
// Kode ini hanya untuk backward compatibility jika ada POST langsung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vital_sign_data']) && !isset($_POST['no_rawat'])) {
    try {
        $vital_signs_json = $_POST['vital_sign_data'];
        $vital_signs = json_decode($vital_signs_json, true);
        
        if (is_array($vital_signs) && !empty($vital_signs)) {
            error_log("⚠️ Vital sign data submitted directly to form-kamar-pemulihan.php (should use submit-kamar-pemulihan.php)");
        }
    } catch (Exception $e) {
        error_log("Error handling vital sign data: " . $e->getMessage());
    }
}

// Ambil data vital sign yang sudah tersimpan untuk ditampilkan di grafik
$queryVitalSign = "SELECT id, waktu, respirasi, nadi, sistol, diastol, nyeri, spo2 
                   FROM tbl_anestesi_vital_pemulihan 
                   WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? 
                   ORDER BY waktu ASC";
$stmtVitalSign = $db->prepare($queryVitalSign);
$stmtVitalSign->execute([$no_rawat, $kode_paket, $tanggal]);
$dbVitalSigns = $stmtVitalSign->fetchAll(PDO::FETCH_ASSOC);

// Hitung umur dari tanggal lahir
$umur = 0;
if (!empty($pasien['tanggal_lahir'])) {
    $tanggal_lahir = new DateTime($pasien['tanggal_lahir']);
    $today = new DateTime('today');
    $umur = $tanggal_lahir->diff($today)->y;
}

// Data pasien untuk display
$no_rm = $pasien['kode_rekam_medis'] ?? '';
$nama_pasien = $pasien['nama'] ?? '';
$jenis_kelamin = $pasien['jenis_kelamin'] ?? '';
$tanggal_lahir_pasien = $pasien['tanggal_lahir'] ?? '';

// ===== LOAD EXISTING DATA (if any) =====
$existingData = null;
$isEdit = false;

$queryExisting = "SELECT * FROM tbl_anestesi_kamar_pemulihan 
                  WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmtExisting = $db->prepare($queryExisting);
$stmtExisting->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$existingData = $stmtExisting->fetch(PDO::FETCH_ASSOC);

if ($existingData) {
    $isEdit = true;
}

// Helper function untuk checked checkbox
function isChecked($existingData, $field) {
    return ($existingData && isset($existingData[$field]) && $existingData[$field]) ? 'checked' : '';
}

// Helper function untuk value input
function getValue($existingData, $field, $default = '') {
    return $existingData[$field] ?? $default;
}

include __DIR__ . '/../includes/assets.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">
<!-- Chart.js untuk Vital Sign -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <div class="title">
        <div style="color: #004d80;">
            CATATAN KAMAR PEMULIHAN
        </div>
        <div>RMOK - 30</div>
    </div>

    <!-- Tombol Back ke Detail Pasien (Floating) -->
    <a href="index.php?page=detail-pasien&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
       class="btn-back-to-detail" 
       style="position: fixed; bottom: 80px; right: 20px; width: auto; height: 32px; padding: 5px 12px; background: #6c757d; color: white; border: none; border-radius: 16px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 998; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease; white-space: nowrap;"
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.3)';" 
       onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" 
       title="Kembali ke Detail Pasien">
        Kembali
    </a>

    <!-- Patient Info Card - Top Section -->
    <?php
    $dokter = $booking['kd_dokter'] ?? '';
    $nama_pasien = $booking['nama'] ?? $booking['nama_pasien'] ?? '';
    ?>
    
    <?php if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal)): ?>
    <div class="patient-info-top-card">
        <div class="patient-info-top-header">
            <strong><i class="fas fa-id-badge"></i> Identitas Pasien</strong>
        </div>
        <div class="patient-info-top-content">
            <?php if (!empty($nama_pasien)): ?>
            <div class="patient-info-top-item">
                <span class="patient-info-top-label"><i class="fas fa-user"></i> Nama:</span>
                <span class="patient-info-top-value"><?= htmlspecialchars($nama_pasien) ?></span>
            </div>
            <?php endif; ?>
            <div class="patient-info-top-item">
                <span class="patient-info-top-label"><i class="fas fa-id-card"></i> No. Rawat:</span>
                <span class="patient-info-top-value"><?= htmlspecialchars($no_rawat) ?></span>
            </div>
            <div class="patient-info-top-item">
                <span class="patient-info-top-label"><i class="fas fa-barcode"></i> Kode Paket:</span>
                <span class="patient-info-top-value"><?= htmlspecialchars($kode_paket) ?></span>
            </div>
            <div class="patient-info-top-item">
                <span class="patient-info-top-label"><i class="fas fa-calendar"></i> Tgl Operasi:</span>
                <span class="patient-info-top-value"><?= htmlspecialchars($tanggal) ?></span>
            </div>
            <?php if (!empty($dokter)): ?>
            <div class="patient-info-top-item">
                <span class="patient-info-top-label"><i class="fas fa-user-md"></i> Dokter:</span>
                <span class="patient-info-top-value"><?= htmlspecialchars($dokter) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Informasi Pasien & Data Booking Operasi -->
    <div class="card" style="background: #e3f2fd; border-left: 4px solid #2196F3;">
        <h2 style="margin-bottom: 20px;">
            <i class="fas fa-user-circle"></i> Informasi Pasien & Data Booking Operasi
        </h2>
        
        <!-- Data Pasien -->
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbdefb;">
            <h3 style="color: #1976d2; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #2196F3; padding-bottom: 8px;">
                Data Pasien
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="form-column">
                    <div class="input-container">
                        <input type="text" id="display_nama" placeholder=" " value="<?php echo htmlspecialchars($nama_pasien); ?>" readonly style="background: #f5f5f5;">
                        <label for="display_nama" class="label-floating">Nama Lengkap</label>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="display_no_rm" placeholder=" " value="<?php echo htmlspecialchars($no_rm); ?>" readonly style="background: #f5f5f5;">
                            <label for="display_no_rm" class="label-floating">No. Rekam Medis</label>
                        </div>
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="no_rawat" name="no_rawat" placeholder=" " value="<?php echo htmlspecialchars($no_rawat); ?>" readonly style="background: #f5f5f5;">
                            <label for="no_rawat" class="label-floating">No. Rawat</label>
                        </div>
                        <div class="input-container" style="flex: 1;">
                            <input type="date" id="tanggal" name="tanggal" placeholder=" " value="<?php echo htmlspecialchars($tanggal); ?>" readonly style="background: #f5f5f5;">
                            <label for="tanggal" class="label-floating">Tanggal Booking</label>
                        </div>
                    </div>
                </div>
                <div class="form-column">
                    <div style="display: flex; gap: 10px;">
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="kode_paket" name="kode_paket" placeholder=" " value="<?php echo htmlspecialchars($kode_paket); ?>" readonly style="background: #f5f5f5;">
                            <label for="kode_paket" class="label-floating">Kode Paket</label>
                        </div>
                        <div class="input-container" style="flex: 1;">
                            <input type="time" id="jam_mulai" name="jam_mulai" placeholder=" " value="<?php echo htmlspecialchars($jam_mulai); ?>" readonly style="background: #f5f5f5;">
                            <label for="jam_mulai" class="label-floating">Jam Mulai</label>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="display_umur" placeholder=" " value="<?php echo $umur . ' Tahun'; ?>" readonly style="background: #f5f5f5;">
                            <label for="display_umur" class="label-floating">Umur</label>
                        </div>
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="display_tgl_lahir" placeholder=" " value="<?php echo !empty($tanggal_lahir_pasien) ? date('d/m/Y', strtotime($tanggal_lahir_pasien)) : '-'; ?>" readonly style="background: #f5f5f5;">
                            <label for="display_tgl_lahir" class="label-floating">Tanggal Lahir</label>
                        </div>
                        <div class="input-container" style="flex: 1;">
                            <input type="text" id="display_jk" placeholder=" " value="<?php echo $jenis_kelamin == 'L' ? 'Laki-laki' : ($jenis_kelamin == 'P' ? 'Perempuan' : '-'); ?>" readonly style="background: #f5f5f5;">
                            <label for="display_jk" class="label-floating">Jenis Kelamin</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Data Booking (REMOVED - merged above) -->
    <div style="display: none;">
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
    
    <!-- Data Masuk -->
    <div class="card">
        <h2>Data Masuk dan Kondisi Pasien</h2>
        <?php
        // Get base URL for form action
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_url = $protocol . $host . $base_path;
        $form_action = rtrim($base_url, '/') . '/process/submit-kamar-pemulihan.php';
        ?>
        <form id="formKamarPemulihan" action="<?php echo htmlspecialchars($form_action); ?>" method="POST" data-no-loading>
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <input type="hidden" name="chart_image" id="chart_image" value="">
            <input type="hidden" name="vital_sign_data" id="vital_sign_data" value="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Kolom Kiri -->
                <div>
                    <div class="keterangan-pasien">
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label for="jamMasuk" style="font-weight: 600; color: #495057; width: 120px; text-align: left;"><strong>Jam Masuk:</strong></label>
                                <input type="time" id="jamMasuk" name="jamMasuk" 
                                       style="width: 180px; padding: 10px 12px; border: 2px solid #ced4da; border-radius: 5px; font-size: 14px; background: white; z-index: 1; position: relative;" 
                                       value="<?php echo $existingData['jam_masuk'] ?? ''; ?>" required />
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label for="tglMasuk" style="font-weight: 600; color: #495057; width: 120px; text-align: left;"><strong>Tanggal Masuk:</strong></label>
                                <input type="date" id="tglMasuk" name="tglMasuk" 
                                       style="width: 180px; padding: 10px 12px; border: 2px solid #ced4da; border-radius: 5px; font-size: 14px; background: white; z-index: 1; position: relative;" 
                                       value="<?php echo $existingData['tgl_masuk'] ?? ''; ?>" required />
                            </div>
                        </div>
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Jalan Nafas:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jalanNafas[]" value="bersih_lapang" <?php echo isChecked($existingData, 'jalan_nafas_bersih'); ?> /> Bersih & lapang</label>
                        </div>
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Pernapasan:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="pernapasan[]" value="spontan" <?php echo isChecked($existingData, 'pernapasan_spontan'); ?> /> Spontan</label>
                            <label><input type="checkbox" name="pernapasan[]" value="dibantu" <?php echo isChecked($existingData, 'pernapasan_dibantu'); ?> /> Dibantu</label>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <div class="keterangan-pasien">
                        <label><strong>Bila spontan:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="spontan[]" value="adekuat" /> Adekuat Bersuara</label>
                            <label><input type="checkbox" name="spontan[]" value="penyumbatan" /> Penyumbatan</label>
                            <label><input type="checkbox" name="spontan[]" value="alat" /> Membutuhkan alat</label>
                        </div>
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Kesadaran:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="kesadaran[]" value="sadar_betul" /> Sadar betul</label>
                            <label><input type="checkbox" name="kesadaran[]" value="belum_sadar" /> Belum sadar betul</label>
                            <label><input type="checkbox" name="kesadaran[]" value="tidur_dalam" /> Tidur dalam</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== VITAL SIGN SECTION ========== -->
        <h2 style="margin-top: 30px;">Monitoring Vital Sign</h2>
        
        <!-- Setting Waktu -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: white; font-size: 16px;">
                    <i class="fas fa-clock"></i> Pengaturan Waktu Monitoring
                </h3>
                
                <!-- Toggle Switch untuk Mode Input -->
                <div style="display: flex; align-items: center; gap: 12px; z-index: 100; position: relative;">
                    <span style="color: white; font-size: 13px; font-weight: 600;">Mode Input:</span>
                    <div style="display: flex; align-items: center; gap: 8px; z-index: 100;">
                        <span id="intervalLabel" style="color: white; font-size: 12px; font-weight: 600; opacity: 1;">Interval</span>
                        <div style="position: relative; width: 50px; height: 24px; background: rgba(255,255,255,0.3); border-radius: 12px; cursor: pointer; transition: all 0.3s ease; z-index: 100; pointer-events: auto;" 
                             onclick="toggleInputMode()" id="toggleSwitch">
                            <div id="toggleSlider" style="position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: white; border-radius: 50%; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                        </div>
                        <span id="manualLabel" style="color: white; font-size: 12px; font-weight: 600; opacity: 0.5;">Manual</span>
                    </div>
                </div>
            </div>
            
            <!-- Container untuk Mode Interval dan Manual -->
            <div id="inputModeContainer">
                <!-- Mode Interval -->
                <div id="intervalMode" style="display: block;">
                    <div style="display: grid; grid-template-columns: 2fr 2fr auto auto; gap: 15px; align-items: end;">
                        <!-- Waktu Mulai (HH:MM:SS) -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                ⏰ Waktu Mulai (HH:MM:SS)
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; gap: 5px; align-items: center;">
                                <input type="number" id="vs_waktu_jam" min="0" max="23" placeholder="HH" 
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_waktu_menit" min="0" max="59" placeholder="MM" 
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_waktu_detik" min="0" max="59" placeholder="SS" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                            </div>
                        </div>
                        
                        <!-- Interval (HH:MM:SS) -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                ⏱️ Interval (HH:MM:SS)
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; gap: 5px; align-items: center;">
                                <input type="number" id="vs_interval_jam" min="0" max="23" placeholder="HH" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_interval_menit" min="0" max="59" placeholder="MM" value="5"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_interval_detik" min="0" max="59" placeholder="SS" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                            </div>
                        </div>
                        
                        <!-- Button Now -->
                        <div style="z-index: 100; position: relative;">
                            <button type="button" id="btn_set_now" 
                                    style="padding: 10px 15px; background: rgba(255,255,255,0.3); color: white; border: 2px solid white; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap; width: 100%; pointer-events: auto;"
                                    onclick="setCurrentTimeVitalWaktu(event); return false;">
                                <i class="fas fa-clock"></i> Set Now
                            </button>
                        </div>
                        
                        <!-- Button Set -->
                        <div style="z-index: 100; position: relative;">
                            <button type="button" id="btn_set_waktu_config" 
                                    style="padding: 10px 20px; background: white; color: #667eea; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap; width: 100%; pointer-events: auto;"
                                    onclick="applyWaktuConfig(event); return false;">
                                <i class="fas fa-check-circle"></i> Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mode Manual -->
                <div id="manualMode" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: end;">
                        <!-- Input Manual -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                📋 Waktu Input Manual
                            </label>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="time" id="vs_jam" step="1"
                                           style="flex: 1; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center; background: rgba(255,255,255,0.3); color: white;">
                                    <button type="button" id="btn_manual_time" 
                                            style="padding: 10px 12px; background: rgba(255,255,255,0.3); color: white; border: 2px solid white; border-radius: 5px; cursor: pointer; font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button type="button" onclick="addManualTimeEntry(event)" 
                                            style="padding: 6px 12px; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)';" 
                                            onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';">
                                        <i class="fas fa-plus"></i> Add Entry
                                    </button>
                                    <button type="button" onclick="clearManualEntries(event)" 
                                            style="padding: 6px 12px; background: rgba(220, 53, 69, 0.3); color: white; border: 1px solid rgba(220, 53, 69, 0.5); border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(220, 53, 69, 0.5)'; this.style.transform='scale(1.05)';" 
                                            onmouseout="this.style.background='rgba(220, 53, 69, 0.3)'; this.style.transform='scale(1)';">
                                        <i class="fas fa-trash"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Button Apply Manual -->
                        <div>
                            <button type="button" id="btn_apply_manual" 
                                    style="padding: 10px 20px; background: white; color: #667eea; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap;">
                                <i class="fas fa-check-circle"></i> Terapkan Manual
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div style="margin-top: 12px; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px; color: white; font-size: 12px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Cara Pakai:</strong> Set waktu mulai dan interval, lalu klik "Terapkan". 
                Waktu akan otomatis bertambah setiap kali Anda tambah record.
            </div>
            <!-- Form Input + Chart + Table in Single Card -->
            <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;" class="vital-sign-grid">
                    <!-- LEFT: Form Input -->
                    <div>
                        <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 20px;">
                            <i class="fas fa-heartbeat"></i> Form Input
                        </h3>
                        
                        <!-- Respirasi -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Respirasi (x/menit)</label>
                            <input type="number" id="vs_respirasi" min="0" max="60" placeholder="Contoh: 18" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                        </div>
                        
                        <!-- Nadi -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Nadi (N) - BPM</label>
                            <input type="number" id="vs_nadi" min="0" max="200" placeholder="Contoh: 86" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                        </div>
                        
                        <!-- Tekanan Darah -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Tekanan Darah (mmHg)</label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center;">
                                <input type="number" id="vs_sistol" min="0" max="300" placeholder="Sistol (120)" 
                                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                                <span style="font-weight: 700; font-size: 18px; color: #6c757d;">/</span>
                                <input type="number" id="vs_diastol" min="0" max="200" placeholder="Diastol (80)" 
                                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                            </div>
                        </div>
                        
                        <!-- Skala Nyeri -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">
                                Skala Nyeri: <span id="nyeri_value" style="color: #007bff; font-size: 18px;">5</span>/10
                            </label>
                            <input type="range" id="vs_nyeri" min="0" max="10" value="5" 
                                   style="width: 100%; height: 8px; -webkit-appearance: none; appearance: none; background: linear-gradient(to right, #28a745 0%, #ffc107 50%, #dc3545 100%); border-radius: 5px; outline: none;">
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #6c757d; margin-top: 3px;">
                                <span>0 (Tidak Nyeri)</span>
                                <span>10 (Sangat Nyeri)</span>
                            </div>
                        </div>
                        
                        <!-- SPO2 -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">SPO2 (%)</label>
                            <input type="number" id="vs_spo2" min="0" max="100" placeholder="Contoh: 98" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                        </div>
                        
                        <!-- Button Group -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; z-index: 100; position: relative;">
                            <button type="button" id="btn_add_vital" 
                                    style="padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; z-index: 100; pointer-events: auto;"
                                    onclick="addVitalSign(); return false;">
                                <i class="fas fa-plus-circle"></i> Tambah
                            </button>
                            <button type="button" id="btn_clear_form" 
                                    style="padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; z-index: 100; pointer-events: auto;"
                                    onclick="clearVitalForm(); return false;">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
                        
                        <small style="display: block; margin-top: 8px; color: #6c757d; font-size: 11px; text-align: center;">
                            💡 Data vital sign akan otomatis tersimpan saat klik tombol "Simpan" di bawah
                        </small>
                    </div>
                        
                    <!-- RIGHT: Chart + History Table -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- Grafik -->
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
                            <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                                <i class="fas fa-chart-line"></i> Grafik Vital Sign
                            </h3>
                            <div style="position: relative; width: 100%; height: 350px;">
                                <canvas id="vitalChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Table Database Records -->
                        <div style="margin-bottom: 30px;">
                            <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                                <i class="fas fa-database"></i> Data Tersimpan di Database
                                <span style="background: #28a745; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;" id="db_record_count">
                                    0 record
                                </span>
                            </h3>
                            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-exclamation-circle" style="color: #ff9800; font-size: 18px;"></i>
                                <span style="color: #856404; font-weight: 600; font-size: 13px;">
                                    ⚠️ Jangan lupa tekan tombol <strong>SIMPAN</strong> setelah input data di sini!
                                </span>
                            </div>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead style="position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <tr style="background: #28a745; color: white;">
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745; width: 40px;">#</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">Waktu</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">Resp</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">Nadi</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">TD</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">Nyeri</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">SPO2</th>
                                            <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745; width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="db_vital_tbody">
                                        <tr>
                                            <td colspan="8" style="padding: 20px; text-align: center; color: #6c757d;">
                                                <i class="fas fa-database"></i> Belum ada record dari database.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Table Unsaved Records -->
                        <div>
                            <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                                <i class="fas fa-edit"></i> Data Input Baru (Belum Disimpan)
                            </h3>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead style="position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <tr style="background: #ffc107; color: #333;">
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center; width: 40px;">#</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Waktu</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Resp</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Nadi</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">TD</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Nyeri</th>
       /                                     <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">SPO2</th>
                                            <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left; width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="unsaved_vital_tbody">
                                        <tr>
                                            <td colspan="8" style="padding: 20px; text-align: center; color: #6c757d;">
                                                <i class="fas fa-info-circle"></i> Belum ada record yang belum tersimpan.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="card">
                <h2>Instruksi Pasca Sedasi dan Anestesi</h2>
                <label>Pemantauan Tanda Vital Setiap:
                    <input type="text" name="pemantauan_setiap" style="width: 60px" /> Selama
                    <input type="text" name="pemantauan_selama" style="width: 60px" />
                </label>
                <div class="input-container">
                    <input type="text" name="analgesia" placeholder=" " />
                    <label class="label-floating">Analgesia</label>
                </div>
                <div class="input-container">
                    <input type="text" name="anti_muntah" placeholder=" " />
                    <label class="label-floating">Anti Muntah</label>
                </div>
                <div class="input-container">
                    <input type="text" name="antibiotik" placeholder=" " />
                    <label class="label-floating">Antibiotik</label>
                </div>
            </div>
            <div class="card">
                <h2>&nbsp;</h2>
                <div class="input-container">
                    <input type="text" name="posisi_pasien" placeholder=" " />
                    <label class="label-floating">Posisi Pasien</label>
                </div>
                <div class="input-container">
                    <input type="text" name="obat_lain" placeholder=" " />
                    <label class="label-floating">Obat-obatan lain</label>
                </div>
                <div class="input-container">
                    <input type="text" name="diet_nutrisi" placeholder=" " />
                    <label class="label-floating">Diet & Nutrisi</label>
                </div>
                <div class="input-container">
                    <input type="text" name="lain_lain" placeholder=" " />
                    <label class="label-floating">Lain-lain</label>
                </div>
            </div>
        </div>

        <!-- Keluar kamar pulih & penilain -->
        <div class="two-column">
            <div class="card">
                <h2>Keluar Kamar Pulih</h2>
                <label>Jam <input type="time" name="jam_keluar" style="width: 80px" /></label><br /><br />
                <table style="width: 100%; border-collapse: collapse">
                    <tr>
                        <th style="text-align: left">Tanda Vital</th>
                        <th>TD</th>
                        <th>N</th>
                        <th>R</th>
                        <th>S</th>
                        <th>SPO2</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="text" name="td_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="n_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="r_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="s_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="spo2_keluar" style="width: 60px" /></td>
                    </tr>
                </table>
                <div class="input-group">
                    <label><strong>Skrining Nyeri:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="skrining_nyeri" value="ya"> Ya</label>
                        <label><input type="radio" name="skrining_nyeri" value="tidak"> Tidak</label>
                    </div>
                </div>
                
                <div class="input-group">
                    <label><strong>Ke:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="tujuan_keluar" value="ruang_rawat"> Ruang Rawat</label>
                        <label><input type="radio" name="tujuan_keluar" value="icu"> ICU</label>
                        <label><input type="radio" name="tujuan_keluar" value="pulang"> Pulang</label>
                    </div>
                </div>
                
                <div class="input-container" style="margin-top: 15px;">
                    <textarea name="catatan_khusus" rows="3" placeholder=""></textarea>
                    <label for="catatan_khusus" class="label-floating">Catatan Khusus Ruang Pemulihan</label>
                </div>
            </div>

            <div class="card">
                <h2>Penilaian</h2>
                <table class="score-table">
                    <tr>
                        <td>Aldrete Score</td>
                        <td><input type="number" name="aldrete_score" style="width: fit-content" /></td>
                    </tr>
                    <tr>
                        <td>Bromage Score</td>
                        <td><input type="number" name="bromage_score" style="width: fit-content" /></td>
                    </tr>
                    <tr>
                        <td>Steward Score</td>
                        <td><input type="number" name="steward_score" style="width: fit-content" /></td>
                    </tr>
                </table>
                <br />
                <label for="ket1">Catatan: isi scoring sesuai metode penilaian yang tepat</label>
            </div>
        </div>

        <div class="card">
            <h2>Serah Terima Pasien ke Rawat Lanjut</h2>
            <table>
                <tr>
                    <th>Nama Jelas</th>
                    <th>Perawat yang Menyerahkan</th>
                    <th>Perawat yang Menerima</th>
                    <th>Dokter Anestesi</th>
                </tr>
                <tr>
                    <td>
                        <div class="input-container" style="margin-top: 17px;">
                            <input type="text" name="nama_penanggungjawab" placeholder=" ">
                            <label class="label-floating">Nama</label>
                        </div>
                    </td>
                    <td>
                        <?php
                        $current_perawat_menyerahkan = $existingData['perawat_menyerahkan'] ?? '';
                        $is_other_perawat_menyerahkan = !empty($current_perawat_menyerahkan) && !in_array($current_perawat_menyerahkan, array_column($perawat_list, 'nama_perawat'));
                        ?>
                        <select id="perawat_menyerahkan_select" name="perawat_menyerahkan_select" onchange="toggleInput('perawat_menyerahkan')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Pilih Perawat Menyerahkan --</option>
                            <?php
                            foreach ($perawat_list as $perawat) {
                                $selected = ($current_perawat_menyerahkan == $perawat['nama_perawat']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_perawat_menyerahkan ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="perawat_menyerahkan_input_container" style="display: <?= $is_other_perawat_menyerahkan ? 'block' : 'none' ?>; margin-top: 5px;">
                            <input type="text" id="perawat_menyerahkan_input" name="perawat_menyerahkan_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_menyerahkan ? htmlspecialchars($current_perawat_menyerahkan) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <input type="hidden" id="perawat_menyerahkan" name="perawat_menyerahkan" value="<?= htmlspecialchars($current_perawat_menyerahkan) ?>">
                    </td>
                    <td>
                        <?php
                        $current_perawat_menerima = $existingData['perawat_menerima'] ?? '';
                        $is_other_perawat_menerima = !empty($current_perawat_menerima) && !in_array($current_perawat_menerima, array_column($perawat_list, 'nama_perawat'));
                        ?>
                        <select id="perawat_menerima_select" name="perawat_menerima_select" onchange="toggleInput('perawat_menerima')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Pilih Perawat Menerima --</option>
                            <?php
                            foreach ($perawat_list as $perawat) {
                                $selected = ($current_perawat_menerima == $perawat['nama_perawat']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_perawat_menerima ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="perawat_menerima_input_container" style="display: <?= $is_other_perawat_menerima ? 'block' : 'none' ?>; margin-top: 5px;">
                            <input type="text" id="perawat_menerima_input" name="perawat_menerima_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_menerima ? htmlspecialchars($current_perawat_menerima) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <input type="hidden" id="perawat_menerima" name="perawat_menerima" value="<?= htmlspecialchars($current_perawat_menerima) ?>">
                    </td>
                    <td>
                        <?php
                        $current_dokter_anestesi = $existingData['dokter_anestesi'] ?? '';
                        $is_other_dokter_anestesi = !empty($current_dokter_anestesi) && !in_array($current_dokter_anestesi, array_column($dokter_list, 'nama_dokter'));
                        ?>
                        <select id="dokter_anestesi_select" name="dokter_anestesi_select" onchange="toggleInput('dokter_anestesi')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Pilih Dokter Anestesi --</option>
                            <?php
                            foreach ($dokter_list as $dokter) {
                                $selected = ($current_dokter_anestesi == $dokter['nama_dokter']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_dokter_anestesi ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="dokter_anestesi_input_container" style="display: <?= $is_other_dokter_anestesi ? 'block' : 'none' ?>; margin-top: 5px;">
                            <input type="text" id="dokter_anestesi_input" name="dokter_anestesi_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_dokter_anestesi ? htmlspecialchars($current_dokter_anestesi) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <input type="hidden" id="dokter_anestesi" name="dokter_anestesi" value="<?= htmlspecialchars($current_dokter_anestesi) ?>">
                    </td>
                </tr>
            </table>
        </div>

        <div class="form-actions">
            <!-- Tombol aksi utama dipindahkan ke Speed Dial -->
        </div>
        </form>
    </div>
</div>

<!-- Speed Dial Actions: Simpan, Cetak PDF, Kembali -->
<div data-dial-init class="fixed right-6 bottom-6 group">
    <div id="speed-dial-menu-kamar-pemulihan" class="flex flex-col w-32 justify-end hidden mb-4 space-y-2 bg-neutral-primary-medium border border-default-medium rounded-base shadow-xs">
        <ul class="p-2 text-sm text-body font-medium">
            <li>
                <a href="#" onclick="document.getElementById('formKamarPemulihan').submit(); return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <!-- Save Icon -->
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
                    <span class="text-sm font-medium">Simpan</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="if (typeof cetakPDF === 'function') { cetakPDF(); } return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <!-- Print Icon -->
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z"/></svg>
                    <span class="text-sm font-medium">Cetak PDF</span>
                </a>
            </li>
            <li>
                <a href="/index.php?page=detail-pasien&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <!-- Back Arrow Icon -->
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6l-6 6 6 6"/></svg>
                    <span class="text-sm font-medium">Kembali</span>
                </a>
            </li>
        </ul>
    </div>
    <button type="button" data-dial-toggle="speed-dial-menu-kamar-pemulihan" aria-controls="speed-dial-menu-kamar-pemulihan" aria-expanded="false" class="flex items-center justify-center ml-auto text-white bg-brand rounded-base w-14 h-14 hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium focus:outline-none">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
        <span class="sr-only">Open actions menu</span>
    </button>
</div>
<script src="/assets/js/autosave.js"></script>
<script>
    // ==================== VITAL SIGN MANAGEMENT ====================
    let vitalSignsArray = [];
    let vitalChart = null;
    
    // Variabel untuk pengaturan waktu otomatis
    let waktuMulai = '';
    let intervalJam = 0;
    let intervalMenit = 5;
    let intervalDetik = 0;
    let recordCount = 0;
    
    // Set current time
    function setCurrentTime() {
        try {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            const jamElement = document.getElementById('vs_jam');
            if (jamElement) {
                jamElement.value = timeString;
                waktuMulai = timeString;
                console.log('⏰ Waktu manual diset ke:', timeString);
                return true;
            } else {
                console.error('❌ vs_jam element not found!');
                return false;
            }
        } catch (error) {
            console.error('❌ Error in setCurrentTime:', error);
            return false;
        }
    }
    
    // Hitung waktu berikutnya berdasarkan interval
    function hitungWaktuBerikutnya() {
        let waktuTerakhir;

        // Jika sudah ada record, ambil waktu dari record terakhir. Jika tidak, gunakan waktuMulai.
        if (vitalSignsArray.length > 0) {
            // Pastikan array diurutkan sebelum mengambil record terakhir
            sortVitalSignsByTime();
            waktuTerakhir = vitalSignsArray[vitalSignsArray.length - 1].jam;
        } else if (waktuMulai) {
            waktuTerakhir = waktuMulai;
            // Untuk record pertama, kita tidak menambahkan interval, jadi langsung set dan keluar
            document.getElementById('vs_jam').value = waktuTerakhir;
            console.log(`⏰ Waktu pertama diset ke waktu mulai: ${waktuTerakhir}`);
            return;
        } else {
            // Fallback jika tidak ada waktu sama sekali
            setCurrentTime();
            return;
        }

        // Parse waktu terakhir
        const [jamTerakhir, menitTerakhir, detikTerakhir = 0] = waktuTerakhir.split(':').map(Number);
        
        // Hitung total detik dari waktu terakhir
        let totalDetikTerakhir = jamTerakhir * 3600 + menitTerakhir * 60 + detikTerakhir;
        
        // Hitung total detik interval
        const totalDetikInterval = intervalJam * 3600 + intervalMenit * 60 + intervalDetik;
        
        // Waktu berikutnya adalah waktu terakhir + interval
        const totalDetikBerikutnya = totalDetikTerakhir + totalDetikInterval;
        
        // Convert kembali ke format HH:MM:SS
        const jamBerikutnya = Math.floor(totalDetikBerikutnya / 3600) % 24;
        const menitBerikutnya = Math.floor((totalDetikBerikutnya % 3600) / 60);
        const detikBerikutnya = totalDetikBerikutnya % 60;
        
        const newTimeString = `${String(jamBerikutnya).padStart(2, '0')}:${String(menitBerikutnya).padStart(2, '0')}:${String(detikBerikutnya).padStart(2, '0')}`;
        
        document.getElementById('vs_jam').value = newTimeString;
        
        console.log(`⏰ Waktu berikutnya dihitung: ${newTimeString} (Dari: ${waktuTerakhir}, Interval: ${intervalJam}h ${intervalMenit}m ${intervalDetik}s)`);
    }
    
    // Initialize Chart.js
    function initVitalChart() {
        try {
            const canvas = document.getElementById("vitalChart");
            if (!canvas) {
                console.error('❌ Canvas element "vitalChart" not found!');
                return;
            }
            
            const ctx = canvas.getContext("2d");
            if (!ctx) {
                console.error('❌ Cannot get 2D context from canvas!');
                return;
            }
            
            console.log('📊 Initializing Chart.js...');
            vitalChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [],
                datasets: [
                    { 
                        label: "Respirasi (R)", 
                        data: [], 
                        borderColor: "#3498db", 
                        backgroundColor: "rgba(52, 152, 219, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Nadi (N)", 
                        data: [], 
                        borderColor: "#ff9800", 
                        backgroundColor: "rgba(255, 152, 0, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Tekanan Darah (Sistol)", 
                        data: [], 
                        borderColor: "#e74c3c", 
                        backgroundColor: "rgba(231, 76, 60, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Tekanan Darah (Diastol)", 
                        data: [], 
                        borderColor: "#000000", 
                        backgroundColor: "rgba(0, 0, 0, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Skala Nyeri", 
                        data: [], 
                        borderColor: "#9c27b0", 
                        backgroundColor: "rgba(156, 39, 176, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "SPO2", 
                        data: [], 
                        borderColor: "#2ecc71", 
                        backgroundColor: "rgba(46, 204, 113, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nilai'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Waktu'
                        }
                    }
                }
            }
        });
            
            console.log('✅ Chart initialized successfully!');
        } catch (error) {
            console.error('❌ Error initializing chart:', error);
        }
    }
    
    // Fungsi untuk mengurutkan vital signs berdasarkan waktu (ascending)
    function sortVitalSignsByTime() {
        vitalSignsArray.sort((a, b) => {
            const timeA = a.jam || a.waktu || '00:00:00';
            const timeB = b.jam || b.waktu || '00:00:00';
            return timeA.localeCompare(timeB);
        });
        console.log('⏰ Vital signs sorted by time (ascending)');
    }
    
    // Update chart dengan data
    function updateVitalChart() {
        if (!vitalChart) return;
        
        // Sort array berdasarkan waktu sebelum update chart
        sortVitalSignsByTime();
        
        // Ambil data dari array
        const labels = vitalSignsArray.map(record => record.jam || record.waktu);
        const respirasiData = vitalSignsArray.map(record => record.respirasi);
        const nadiData = vitalSignsArray.map(record => record.nadi);
        const sistolData = vitalSignsArray.map(record => record.sistol);
        const diastolData = vitalSignsArray.map(record => record.diastol);
        const nyeriData = vitalSignsArray.map(record => record.nyeri);
        const spo2Data = vitalSignsArray.map(record => record.spo2);
        
        // Update chart data (6 datasets: Respirasi, Nadi, Sistol, Diastol, Nyeri, SPO2)
        vitalChart.data.labels = labels;
        vitalChart.data.datasets[0].data = respirasiData;
        vitalChart.data.datasets[1].data = nadiData;
        vitalChart.data.datasets[2].data = sistolData;
        vitalChart.data.datasets[3].data = diastolData;
        vitalChart.data.datasets[4].data = nyeriData;
        vitalChart.data.datasets[5].data = spo2Data;
        
        vitalChart.update();
        
        console.log('📊 Chart updated with', vitalSignsArray.length, 'records (sorted by time)');
    }
    
    // Separate database records from unsaved records
    let dbVitalRecords = [];
    let unsavedVitalRecords = [];
    
    // Update tabel vital sign - split into database and unsaved
    function updateVitalTable() {
        // Sort array berdasarkan waktu sebelum update tabel
        sortVitalSignsByTime();
        
        // Separate records: UUID (dari DB) vs numeric ID (unsaved)
        dbVitalRecords = vitalSignsArray.filter(r => r.id && typeof r.id === 'string' && r.id.includes('-'));
        unsavedVitalRecords = vitalSignsArray.filter(r => !r.id || typeof r.id === 'number');
        
        updateDatabaseTable();
        updateUnsavedTable();
        
        console.log('📋 Table updated - DB:', dbVitalRecords.length, 'Unsaved:', unsavedVitalRecords.length, '(sorted by time)');
    }
    
    // Update database records table
    function updateDatabaseTable() {
        const tbody = document.getElementById('db_vital_tbody');
        const recordCount = document.getElementById('db_record_count');
        
        if (!tbody || !recordCount) {
            console.warn('⚠️ Database table elements not found!');
            return;
        }
        
        if (dbVitalRecords.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                        <i class="fas fa-database"></i> Belum ada record dari database.
                    </td>
                </tr>
            `;
            recordCount.textContent = '0 record';
            return;
        }
        
        let html = '';
        let displayIndex = 0;
        dbVitalRecords.forEach((record) => {
            // Skip record yang ditandai untuk dihapus
            if (record._deleted) {
                return;
            }
            
            displayIndex++;
            const bgColor = displayIndex % 2 === 0 ? '#ffffff' : '#f8f9fa';
            html += `
                <tr style="background: ${bgColor};">
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center; font-weight: 600; color: #007bff;">
                        ${displayIndex}
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600;">
                        ${record.jam}
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                            ${record.respirasi}
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                            ${record.nadi} bpm
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #ffebee; padding: 4px 8px; border-radius: 15px; color: #d32f2f; font-weight: 600; font-size: 12px;">
                            ${record.sistol}${record.diastol !== null ? `/${record.diastol}` : ''}
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #9b59b6; font-weight: 600; font-size: 12px;">
                            ${record.nyeri}/10
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #e8f5e8; padding: 4px 10px; border-radius: 15px; color: #388e3c; font-weight: 600; font-size: 12px;">
                            ${record.spo2}%
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <button onclick="editDbVitalRecord('${record.id}')" 
                                style="background: #007bff; color: white; border: none; padding: 5px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; margin-right: 3px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteDbVitalRecord('${record.id}')" 
                                style="background: #dc3545; color: white; border: none; padding: 5px 8px; border-radius: 3px; cursor: pointer; font-size: 11px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        recordCount.textContent = `${dbVitalRecords.length} record${dbVitalRecords.length > 1 ? 's' : ''}`;
    }
    
    // Update unsaved records table
    function updateUnsavedTable() {
        const tbody = document.getElementById('unsaved_vital_tbody');
        
        if (!tbody) {
            console.warn('⚠️ Unsaved table elements not found!');
            return;
        }
        
        if (unsavedVitalRecords.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                        <i class="fas fa-info-circle"></i> Belum ada record yang belum tersimpan.
                    </td>
                </tr>
            `;
            recordCount.textContent = '0 record';
            return;
        }
        
        let html = '';
        unsavedVitalRecords.forEach((record, index) => {
            const bgColor = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
            const dbIndex = vitalSignsArray.indexOf(record);
            html += `
                <tr style="background: ${bgColor};">
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center; font-weight: 600; color: #007bff;">
                        ${index + 1}
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600;">
                        ${record.jam}
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                            ${record.respirasi}
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                            ${record.nadi} bpm
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #ffebee; padding: 4px 8px; border-radius: 15px; color: #d32f2f; font-weight: 600; font-size: 12px;">
                            ${record.sistol}${record.diastol !== null ? `/${record.diastol}` : ''}
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #9b59b6; font-weight: 600; font-size: 12px;">
                            ${record.nyeri}/10
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <span style="background: #e8f5e8; padding: 4px 10px; border-radius: 15px; color: #388e3c; font-weight: 600; font-size: 12px;">
                            ${record.spo2}%
                        </span>
                    </td>
                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                        <button onclick="deleteVitalRecord(${dbIndex})" 
                                style="background: #dc3545; color: white; border: none; padding: 5px 8px; border-radius: 3px; cursor: pointer; font-size: 11px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Update hidden input untuk submit
    function updateVitalHiddenInput() {
        const hiddenInput = document.getElementById('vital_sign_data');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(vitalSignsArray);
            console.log('💾 Hidden input updated:', vitalSignsArray.length, 'records');
        } else {
            console.warn('⚠️ Hidden input vital_sign_data not found!');
        }
    }
    
    // Edit unsaved record
    function editVitalRecord(index) {
        const record = vitalSignsArray[index];
        if (!record) {
            alert('❌ Record tidak ditemukan!');
            return;
        }
        
        // Populate form dengan data record
        document.getElementById('vs_respirasi').value = record.respirasi;
        document.getElementById('vs_nadi').value = record.nadi;
        document.getElementById('vs_sistol').value = record.sistol;
        const diastolInput = document.getElementById('vs_diastol');
        if (diastolInput && record.diastol) {
            diastolInput.value = record.diastol;
        }
        document.getElementById('vs_nyeri').value = record.nyeri;
        document.getElementById('vs_spo2').value = record.spo2;
        document.getElementById('vs_jam').value = record.jam;
        
        // Delete record lama
        vitalSignsArray.splice(index, 1);
        updateVitalTable();
        updateVitalChart();
        updateVitalHiddenInput();
        
        console.log('✏️ Record edited, form populated');
        alert('✏️ Data record telah dimuat ke form. Ubah data dan klik Tambah untuk menyimpan perubahan.');
    }
    
    // Delete unsaved record
    function deleteVitalRecord(index) {
        if (confirm('🗑️ Hapus record ini?')) {
            vitalSignsArray.splice(index, 1);
            updateVitalTable();
            updateVitalChart();
            updateVitalHiddenInput();
            console.log('🗑️ Record deleted, remaining:', vitalSignsArray.length);
        }
    }
    
    // Edit database record - buka modal edit
    function editDbVitalRecord(recordId) {
        const index = vitalSignsArray.findIndex(r => r.id === recordId);
        if (index === -1) {
            alert('❌ Record tidak ditemukan!');
            return;
        }
        
        // Buka modal edit dengan index
        openEditModal(index);
    }
    
    // Delete database record - tandai untuk dihapus dari database
    function deleteDbVitalRecord(recordId) {
        const confirmDelete = confirm('⚠️ PERHATIAN!\n\nAnda yakin ingin menghapus record ini dari database?\n\nRecord akan dihapus setelah Anda klik tombol "Simpan" di bawah.\n\nKlik OK untuk melanjutkan atau Cancel untuk batal.');
        
        if (confirmDelete) {
            const index = vitalSignsArray.findIndex(r => r.id === recordId);
            if (index !== -1) {
                // Tandai record untuk dihapus (soft delete)
                vitalSignsArray[index]._deleted = true;
                
                updateVitalTable();
                updateVitalChart();
                updateVitalHiddenInput();
                
                console.log('🗑️ Record marked for deletion:', recordId);
                alert('✅ Record ditandai untuk dihapus.\n\nJangan lupa tekan tombol "Simpan" untuk mengkonfirmasi penghapusan!');
            }
        }
    }
    
    // ===== POPULATE DATA DARI DATABASE =====
    <?php if (!empty($dbVitalSigns)): ?>
    // Data vital sign dari database
    const dbVitalData = <?php echo json_encode($dbVitalSigns); ?>;
    console.log('📊 Database vital signs loaded:', dbVitalData.length, 'records');
    
    // PENTING: Clear vitalSignsArray terlebih dahulu untuk menghindari duplikasi
    vitalSignsArray = [];
    console.log('🧹 vitalSignsArray cleared before populating from database');
    
    // Populate vitalSignsArray dari database
    dbVitalData.forEach(function(record) {
        // Extract jam dari waktu (format: YYYY-MM-DD HH:MM:SS atau HH:MM:SS)
        const waktuString = record.waktu || record.jam || '';
        let jamValue = '';
        
        if (waktuString.includes(' ')) {
            // Format: YYYY-MM-DD HH:MM:SS
            jamValue = waktuString.split(' ')[1];
        } else if (waktuString.includes(':')) {
            // Format: HH:MM:SS
            jamValue = waktuString;
        } else {
            jamValue = '';
        }
        
        const sistolValue = record.td_sistolik ?? record.sistol;
        const diastolValue = record.td_diastolik ?? record.diastol;

        vitalSignsArray.push({
            id: record.id,  // Gunakan ID asli dari database (UUID string)
            jam: jamValue,
            respirasi: parseInt(record.respirasi, 10) || 0,
            nadi: parseInt(record.nadi, 10) || 0,
            sistol: parseInt(sistolValue, 10) || 0,
            diastol: diastolValue != null ? parseInt(diastolValue, 10) || 0 : null,
            nyeri: parseInt(record.nyeri, 10) || 0,
            spo2: parseInt(record.spo2, 10) || 0
        });
    });
    
    console.log('✅ vitalSignsArray populated with', vitalSignsArray.length, 'records from database');
    console.log('📋 Database record IDs:', dbVitalData.map(r => r.id).join(', '));
    <?php endif; ?>

// ===== WRAPPER FUNCTIONS UNTUK ONCLICK HANDLER (GLOBAL SCOPE) =====
function addVitalSign() {
    console.log('🔘 addVitalSign() called from onclick');
    const respirasi = document.getElementById('vs_respirasi').value;
    const nadi = document.getElementById('vs_nadi').value;
    const sistol = document.getElementById('vs_sistol').value;
    const diastolInput = document.getElementById('vs_diastol');
    const diastol = diastolInput ? diastolInput.value : '';
    const nyeri = document.getElementById('vs_nyeri').value;
    const spo2 = document.getElementById('vs_spo2').value;
    const jam = document.getElementById('vs_jam').value;
    
    if (!respirasi || !nadi || !sistol || !nyeri || !spo2 || (diastolInput && !diastol)) {
        alert('⚠️ Semua field harus diisi!');
        return;
    }
    
    const record = {
        id: Date.now(),
        jam: jam,
        respirasi: parseInt(respirasi),
        nadi: parseInt(nadi),
        sistol: parseInt(sistol),
        diastol: diastol ? parseInt(diastol) : null,
        nyeri: parseInt(nyeri),
        spo2: parseInt(spo2)
    };
    
    vitalSignsArray.push(record);
    console.log('✅ Vital sign added:', record);
    
    updateVitalTable();
    updateVitalChart();
    updateVitalHiddenInput();
    
    // Hitung waktu berikutnya untuk record selanjutnya (jika mode interval)
    if (!isManualMode) {
        hitungWaktuBerikutnya();
    }
    
    // Clear form
    document.getElementById('vs_respirasi').value = '';
    document.getElementById('vs_nadi').value = '';
    document.getElementById('vs_sistol').value = '';
    if (diastolInput) {
        diastolInput.value = '';
    }
    document.getElementById('vs_nyeri').value = '5';
    document.getElementById('vs_spo2').value = '';
}

function clearVitalForm() {
    console.log('🔘 clearVitalForm() called from onclick');
    if (confirm('🗑️ Kosongkan semua input form?')) {
        document.getElementById('vs_respirasi').value = '';
        document.getElementById('vs_nadi').value = '';
        document.getElementById('vs_sistol').value = '';
        document.getElementById('vs_nyeri').value = '5';
        document.getElementById('vs_spo2').value = '';
        console.log('✅ Form cleared');
    }
}

function setCurrentTimeVitalWaktu(showFeedback = true) {
    console.log('🔘 setCurrentTimeVitalWaktu() called');
    const jamField = document.getElementById('vs_jam');
    const jamInput = document.getElementById('vs_waktu_jam');
    const menitInput = document.getElementById('vs_waktu_menit');
    const detikInput = document.getElementById('vs_waktu_detik');

    if (!jamField) {
        console.warn('⚠️ vs_jam field not found');
        return;
    }

    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;

    jamField.value = timeString;

    if (jamInput && menitInput && detikInput) {
        jamInput.value = parseInt(hours, 10);
        menitInput.value = parseInt(minutes, 10);
        detikInput.value = parseInt(seconds, 10);
    }

    if (showFeedback) {
        console.log('⏰ Waktu diset ke:', timeString);
    }
}

function applyWaktuConfig() {
    console.log('🔘 applyWaktuConfig() called');
    const jamInput = document.getElementById('vs_waktu_jam');
    const menitInput = document.getElementById('vs_waktu_menit');
    const detikInput = document.getElementById('vs_waktu_detik');
    const intervalJamInput = document.getElementById('vs_interval_jam');
    const intervalMenitInput = document.getElementById('vs_interval_menit');
    const intervalDetikInput = document.getElementById('vs_interval_detik');
    const jamField = document.getElementById('vs_jam');

    if (!jamInput || !menitInput || !detikInput || !jamField) {
        alert('⚠️ Input waktu tidak lengkap.');
        return;
    }

    if (jamInput.value === '' || menitInput.value === '') {
        alert('⚠️ Jam dan menit harus diisi.');
        return;
    }

    const jam = String(jamInput.value).padStart(2, '0');
    const menit = String(menitInput.value).padStart(2, '0');
    const detik = String(detikInput.value || 0).padStart(2, '0');

    waktuMulai = `${jam}:${menit}:${detik}`;
    intervalJam = parseInt(intervalJamInput.value || 0, 10);
    intervalMenit = parseInt(intervalMenitInput.value || 0, 10);
    intervalDetik = parseInt(intervalDetikInput.value || 0, 10);

    jamField.value = waktuMulai;

    console.log('✅ Pengaturan waktu diterapkan', {
        waktuMulai,
        intervalJam,
        intervalMenit,
        intervalDetik
    });

    alert('✅ Pengaturan waktu diterapkan!\n\nWaktu akan otomatis bertambah setiap kali Anda tambah record.');
}

// Event listeners untuk button waktu
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOMContentLoaded - Starting initialization...');
    
    // Update table dan chart dengan data dari database
    if (vitalSignsArray.length > 0) {
        updateVitalTable();
        updateVitalHiddenInput();
    }
    
    // Initialize chart dengan delay untuk memastikan canvas siap
    setTimeout(function() {
        console.log('📊 Initializing chart after DOM ready...');
        initVitalChart();
        
        // Update chart dengan data yang sudah ada
        if (vitalSignsArray.length > 0) {
            updateVitalChart();
        }
        
        // Trigger resize untuk responsive
        window.dispatchEvent(new Event('resize'));
    }, 100);
    
    // Button hover effect
    const btnAddVital = document.getElementById('btn_add_vital');
    const btnClearForm = document.getElementById('btn_clear_form');
    
    if (btnAddVital) {
        btnAddVital.addEventListener('mouseenter', function() {
            this.style.background = '#0056b3';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,123,255,0.3)';
        });
        
        btnAddVital.addEventListener('mouseleave', function() {
            this.style.background = '#007bff';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    }
    
    if (btnClearForm) {
        btnClearForm.addEventListener('mouseenter', function() {
            this.style.background = '#5a6268';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(108,117,125,0.3)';
        });
        
        btnClearForm.addEventListener('mouseleave', function() {
            this.style.background = '#6c757d';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    }
    
    // Update slider nyeri value display
    const vsNyeri = document.getElementById('vs_nyeri');
    if (vsNyeri) {
        vsNyeri.addEventListener('input', function() {
            const nyeriValue = document.getElementById('nyeri_value');
            if (nyeriValue) {
                nyeriValue.textContent = this.value;
            }
        });
    }
    
    // Update chart and set initial time
    updateVitalChart();
    setCurrentTimeVitalWaktu();
    
    // ===== LOAD VITAL SIGN DATA FROM DATABASE =====
    loadVitalSignFromDatabase();
    
    console.log('✅ All event listeners initialized successfully!');
    
    // Debug function untuk testing
    window.debugVitalSign = function() {
        console.log('=== DEBUG VITAL SIGN ===');
        console.log('btn_set_now:', document.getElementById('btn_set_now'));
        console.log('btn_set_waktu_config:', document.getElementById('btn_set_waktu_config'));
        console.log('vs_waktu_jam:', document.getElementById('vs_waktu_jam'));
        console.log('vs_waktu_menit:', document.getElementById('vs_waktu_menit'));
        console.log('vs_waktu_detik:', document.getElementById('vs_waktu_detik'));
        console.log('vs_jam:', document.getElementById('vs_jam'));
        console.log('vitalChart:', vitalChart);
        console.log('========================');
    };
    
    // Test function untuk manual testing
    window.testSetCurrentTime = function() {
        console.log('Testing setCurrentTimeVitalWaktu...');
        return setCurrentTimeVitalWaktu();
    };

    // ===== FORM SUBMIT HANDLER =====
    const formKamarPemulihan = document.getElementById('formKamarPemulihan');
    if (formKamarPemulihan) {
        formKamarPemulihan.addEventListener('submit', function(e) {
            console.log('📤 Form submit triggered - preparing vital sign data...');

            // Pemanggilan saveVitalSignToDatabase() dihapus dari sini.
            // Proses penyimpanan sekarang ditangani sepenuhnya oleh submit-kamar-pemulihan.php
            // untuk mencegah duplikasi data.

            // Update vital_sign_data hidden input dengan JSON array
            const hiddenVitalData = document.getElementById('vital_sign_data');
            if (hiddenVitalData) {
                hiddenVitalData.value = JSON.stringify(vitalSignsArray);
                console.log('✅ vital_sign_data updated:', vitalSignsArray.length, 'records');
            }

            // Capture chart image sebagai base64
            const canvas = document.getElementById('vitalChart');
            if (canvas && vitalChart) {
                try {
                    const chartImage = canvas.toDataURL('image/png');
                    const hiddenChartImage = document.getElementById('chart_image');
                    
                    // -- DEBUGGING START --
                    console.log('--- DEBUGGING CHART IMAGE ---');
                    if (!hiddenChartImage) {
                        console.error('❌ FATAL: Hidden input #chart_image TIDAK DITEMUKAN!');
                    } else {
                        hiddenChartImage.value = chartImage;
                        console.log('1. Tipe Data Gambar:', typeof chartImage);
                        console.log('2. Panjang String Gambar:', chartImage.length, 'karakter');
                        console.log('3. Awal Data Gambar:', chartImage.substring(0, 50));
                        console.log('4. Nilai Hidden Input setelah di-set:', hiddenChartImage.value.substring(0, 50));
                        console.log('✅ Hidden input #chart_image BERHASIL di-set.');
                    }
                    console.log('--- END DEBUGGING ---');
                    // -- DEBUGGING END --

                } catch (error) {
                    console.warn('⚠️ Error saat mengambil gambar grafik:', error);
                }
            }

            console.log('📋 Form data ready for submission');
        });
    }
});

// Toggle Input untuk Dropdown dengan opsi "Lainnya"
function toggleInput(fieldName) {
    const select = document.getElementById(fieldName + '_select');
    const inputContainer = document.getElementById(fieldName + '_input_container');
    const inputField = document.getElementById(fieldName + '_input');
    const hiddenField = document.getElementById(fieldName);
    if (select.value === 'lainnya') {
        inputContainer.style.display = 'block';
        inputField.required = false;
        select.required = false;
        hiddenField.value = inputField.value;
    } else {
        inputContainer.style.display = 'none';
        inputField.required = false;
        select.required = false;
        hiddenField.value = select.value;
    }
}

// Initialize toggle untuk dropdown dokter dan perawat
const fields = ['perawat_menyerahkan', 'perawat_menerima', 'dokter_anestesi'];

fields.forEach(function(fieldName) {
    const select = document.getElementById(fieldName + '_select');
    const inputField = document.getElementById(fieldName + '_input');
    const hiddenField = document.getElementById(fieldName);
    
    if (select && inputField && hiddenField) {
        // Event listener untuk dropdown
        select.addEventListener('change', function() {
            if (this.value !== 'lainnya') {
                hiddenField.value = this.value;
            }
            toggleInput(fieldName);
        });
        
        // Event listener untuk input manual
        inputField.addEventListener('input', function() {
            hiddenField.value = this.value;
        });
        
        // Initialize state saat load
        toggleInput(fieldName);
    }
});

// Initialize AutoSave
AutoSave.init('formKamarPemulihan', {
    debounce: 1000,
    exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai', 'vital_sign_data'],
    showNotification: true,
    clearOnSubmit: true
});

// Fungsi global untuk set interval default (5 menit)
function setDefaultInterval() {
    document.getElementById('vs_interval_jam').value = 0;
    document.getElementById('vs_interval_menit').value = 5;
    document.getElementById('vs_interval_detik').value = 0;
    
    // Visual feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Set!';
    button.style.background = 'rgba(40, 167, 69, 0.3)';
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = 'rgba(255,255,255,0.2)';
    }, 1500);
    
    console.log('⏱️ Interval diset ke default: 00:05:00');
}

// Fungsi global untuk set interval custom
function setCustomInterval() {
    const minutes = prompt('Masukkan interval dalam menit (1-60):', '5');
    if (minutes !== null && !isNaN(minutes) && minutes >= 1 && minutes <= 60) {
        document.getElementById('vs_interval_jam').value = 0;
        document.getElementById('vs_interval_menit').value = parseInt(minutes);
        document.getElementById('vs_interval_detik').value = 0;
        
        // Visual feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Set!';
        button.style.background = 'rgba(40, 167, 69, 0.3)';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = 'rgba(255,255,255,0.2)';
        }, 1500);
        
        console.log('⏱️ Interval custom diset ke:', `00:${String(minutes).padStart(2, '0')}:00`);
    } else if (minutes !== null) {
        alert('⚠️ Masukkan angka antara 1-60 menit!');
    }
}

// Variable untuk mode input
let isManualMode = false;
let manualTimeSlot = '';

// Fungsi global untuk toggle mode input
function toggleInputMode(evt) {
    isManualMode = !isManualMode;
    const intervalMode = document.getElementById('intervalMode');
    const manualMode = document.getElementById('manualMode');
    const toggleSlider = document.getElementById('toggleSlider');
    const intervalLabel = document.getElementById('intervalLabel');
    const manualLabel = document.getElementById('manualLabel');
    
    if (isManualMode) {
        // Switch ke Manual Mode
        intervalMode.style.display = 'none';
        manualMode.style.display = 'block';
        toggleSlider.style.left = '28px';
        intervalLabel.style.opacity = '0.5';
        manualLabel.style.opacity = '1';
        console.log('🔄 Mode diubah ke: Manual Input');
        setCurrentTimeVitalWaktu(false);
    } else {
        // Switch ke Interval Mode
        intervalMode.style.display = 'block';
        manualMode.style.display = 'none';
        toggleSlider.style.left = '2px';
        intervalLabel.style.opacity = '1';
        manualLabel.style.opacity = '0.5';
        console.log('🔄 Mode diubah ke: Interval Input');
        manualTimeSlot = '';
        document.getElementById('vs_jam').value = '';
    }
}

// Fungsi global untuk set waktu vital sign ke waktu sekarang
function setCurrentTimeVitalWaktu(evt, updateManual = true) {
    try {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        // Cek apakah elemen ada
        const jamElement = document.getElementById('vs_waktu_jam');
        const menitElement = document.getElementById('vs_waktu_menit');
        const detikElement = document.getElementById('vs_waktu_detik');
        
        if (!jamElement || !menitElement || !detikElement) {
            console.error('❌ Input waktu elements not found!');
            return false;
        }
        
        jamElement.value = parseInt(hours);
        menitElement.value = parseInt(minutes);
        detikElement.value = parseInt(seconds);
        
        // Visual feedback hanya jika dipanggil dari event
        if (evt && evt.target) {
            const button = evt.target.closest('button');
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Set!';
                button.style.background = 'rgba(40, 167, 69, 0.3)';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = 'rgba(255,255,255,0.2)';
                }, 1500);
            }
        }
        
        if (updateManual) {
            manualTimeSlot = `${hours}:${minutes}:${seconds}`;
            document.getElementById('vs_jam').value = manualTimeSlot;
        }

        console.log('⏰ Waktu vital sign diset ke:', `${hours}:${minutes}:${seconds}`);
        return true;
    } catch (error) {
        console.error('❌ Error in setCurrentTimeVitalWaktu:', error);
        return false;
    }
}

// Fungsi untuk menambah entry waktu manual
function addManualTimeEntry(evt) {
    const jamInput = document.getElementById('vs_jam');
    const currentTime = jamInput.value || manualTimeSlot || getCurrentTimeString();

    manualTimeSlot = formatManualTime(currentTime);
    jamInput.value = manualTimeSlot;

    console.log('📝 Manual time prepared:', manualTimeSlot);

    if (evt && evt.target) {
        const button = evt.target.closest('button');
        if (button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Ready!';
            button.style.background = 'rgba(40, 167, 69, 0.3)';

            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = 'rgba(255,255,255,0.2)';
            }, 1500);
        }
    }
}

// Fungsi untuk clear semua entry manual
function clearManualEntries(evt) {
    manualTimeSlot = '';
    document.getElementById('vs_jam').value = '';
    console.log('🗑️ Manual time cleared');

    if (evt && evt.target) {
        const button = evt.target.closest('button');
        if (button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Cleared!';
            button.style.background = 'rgba(40, 167, 69, 0.3)';

            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = 'rgba(220, 53, 69, 0.3)';
            }, 1500);
        }
    }
}

// Helper function untuk get current time string
function getCurrentTimeString() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

function formatManualTime(value) {
    if (!value) return '';
    const parts = value.split(':');
    const jam = parts[0] ?? '00';
    const menit = parts[1] ?? '00';
    return `${jam.padStart(2, '0')}:${menit.padStart(2, '0')}`;
}

// ===== LOAD VITAL SIGN DATA FROM DATABASE =====
function loadVitalSignFromDatabase() {
    const no_rawat = document.querySelector('input[name="no_rawat"]')?.value;
    const kode_paket = document.querySelector('input[name="kode_paket"]')?.value;
    const tanggal = document.querySelector('input[name="tanggal"]')?.value;
    const jam_mulai = document.querySelector('input[name="jam_mulai"]')?.value;
    
    if (!no_rawat || !kode_paket || !tanggal || !jam_mulai) {
        console.warn('⚠️ Missing booking parameters, skipping load vital sign');
        return;
    }
    
    console.log('📥 Loading vital sign data from database...');
    
    const params = new URLSearchParams({
        no_rawat: no_rawat,
        kode_paket: kode_paket,
        tanggal: tanggal,
        jam_mulai: jam_mulai
    });
    
    fetch(`/process/fetch-vital-pemulihan.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                console.log('✅ Loaded', data.count, 'vital sign records from database');
                
                // Clear existing data
                vitalSignsArray = [];
                
                // Populate vitalSignsArray dengan data dari database
                data.data.forEach(record => {
                    vitalSignsArray.push({
                        id: record.id,
                        jam: record.jam,
                        respirasi: parseInt(record.respirasi) || 0,
                        nadi: parseInt(record.nadi) || 0,
                        sistol: parseInt(record.sistol) || 0,
                        diastol: record.diastol ? parseInt(record.diastol) : null,
                        nyeri: parseInt(record.nyeri) || 0,
                        spo2: parseInt(record.spo2) || 0
                    });
                });
                
                console.log('📊 Updating chart with loaded data...');
                updateVitalTable();
                updateVitalChart();
                updateVitalHiddenInput();
            } else {
                console.log('ℹ️ No vital sign data found in database for this booking');
            }
        })
        .catch(error => {
            console.warn('⚠️ Error loading vital sign data:', error);
        });
}

// ===== SAVE VITAL SIGN DATA TO DATABASE =====
function saveVitalSignToDatabase() {
    const no_rawat = document.querySelector('input[name="no_rawat"]')?.value;
    const kode_paket = document.querySelector('input[name="kode_paket"]')?.value;
    const tanggal = document.querySelector('input[name="tanggal"]')?.value;
    const jam_mulai = document.querySelector('input[name="jam_mulai"]')?.value;
    const id_pemulihan = document.querySelector('input[name="id"]')?.value || null;
    
    if (!no_rawat || !kode_paket || !tanggal || !jam_mulai) {
        console.warn('⚠️ Missing booking parameters, cannot save vital sign');
        return;
    }
    
    if (vitalSignsArray.length === 0) {
        console.warn('⚠️ No vital sign data to save');
        return;
    }
    
    console.log('💾 Saving', vitalSignsArray.length, 'vital sign records to database...');
    
    const payload = {
        no_rawat: no_rawat,
        kode_paket: kode_paket,
        tanggal: tanggal,
        jam_mulai: jam_mulai,
        id_pemulihan: id_pemulihan,
        vital_signs: vitalSignsArray
    };
    
    fetch('/process/save-vital-pemulihan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Successfully saved', data.inserted_count, 'vital sign records');
        } else {
            console.warn('⚠️ Error saving vital sign:', data.message);
        }
    })
    .catch(error => {
        console.warn('⚠️ Error saving vital sign data:', error);
    });
}

</script>

<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    /* Slider styling */
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #007bff;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #007bff;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border: none;
    }
    
    /* Fix untuk input time dan date */
    input[type="time"], input[type="date"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        position: relative !important;
        z-index: 10 !important;
        background: white !important;
        color: #495057 !important;
        font-family: inherit !important;
    }
    
    input[type="time"]::-webkit-calendar-picker-indicator,
    input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent;
        bottom: 0;
        color: transparent;
        cursor: pointer;
        height: auto;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: auto;
        z-index: 2;
    }
    
    
    /* Pastikan keterangan-pasien tidak overlap */
    .keterangan-pasien {
        margin-bottom: 15px !important;
        padding: 15px !important;
        background: rgba(248, 249, 250, 0.5) !important;
        border-radius: 5px !important;
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Layout vertikal simetris untuk input jam dan tanggal */
    .keterangan-pasien div[style*="flex-direction: column"] > div {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }
    
    .keterangan-pasien div[style*="flex-direction: column"] label {
        width: 120px !important;
        flex-shrink: 0 !important;
        font-weight: 600 !important;
        color: #495057 !important;
        text-align: left !important;
    }
    
    .keterangan-pasien div[style*="flex-direction: column"] input {
        width: 180px !important;
        padding: 10px 12px !important;
        border: 2px solid #ced4da !important;
        border-radius: 5px !important;
        font-size: 14px !important;
        background: white !important;
        z-index: 1 !important;
        position: relative !important;
    }
    
    /* Responsive untuk Vital Sign Grid */
    @media (max-width: 1200px) {
        .vital-sign-grid {
            grid-template-columns: 1fr 1.5fr !important;
            gap: 20px !important;
        }
    }
    
    @media (max-width: 992px) {
        .vital-sign-grid {
            grid-template-columns: 1fr 1.2fr !important;
            gap: 15px !important;
        }
    }
    
    @media (max-width: 768px) {
        .vital-sign-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        
        .vital-sign-grid > div:first-child {
            order: 1;
        }
        
        .vital-sign-grid > div:last-child {
            order: 2;
        }
    }
    
    /* Responsive untuk mobile */
    @media (max-width: 480px) {
        .keterangan-pasien div[style*="flex-direction: column"] label {
            width: 100px !important;
        }
        
        .keterangan-pasien div[style*="flex-direction: column"] input {
            width: 160px !important;
        }
    }
</style>

<script>
// Window resize handler untuk chart responsiveness
window.addEventListener('resize', function() {
    if (vitalChart) {
        console.log('📐 Window resized, updating chart...');
        if (typeof vitalChart.resize === 'function') {
            vitalChart.resize();
        } else if (typeof vitalChart.update === 'function') {
            vitalChart.update();
        }
    }
});

// Juga trigger chart update saat page fully loaded
window.addEventListener('load', function() {
    console.log('✅ Page fully loaded');
    if (vitalChart) {
        setTimeout(function() {
            if (typeof vitalChart.resize === 'function') {
                vitalChart.resize();
            } else if (typeof vitalChart.update === 'function') {
                vitalChart.update();
            }
            console.log('📊 Chart resized on page load');
        }, 200);
    }
});
</script>

<!-- Modal Edit Record -->
<div id="editRecordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; margin-bottom: 20px; color: #333;">✏️ Edit Record Vital Sign</h2>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Waktu (HH:MM:SS)</label>
            <input type="time" id="editRecordTime" step="1" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Respirasi (x/menit)</label>
            <input type="number" id="editRecordRespirasi" min="0" max="60" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Nadi (N) - BPM</label>
            <input type="number" id="editRecordNadi" min="0" max="200" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Tekanan Darah (mmHg)</label>
            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center;">
                <input type="number" id="editRecordSistol" min="0" max="300" placeholder="Sistol" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                <span style="font-weight: 700; font-size: 18px; color: #6c757d;">/</span>
                <input type="number" id="editRecordDiastol" min="0" max="200" placeholder="Diastol" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Skala Nyeri: <span id="editNyeriValue" style="color: #007bff; font-size: 18px;">5</span>/10</label>
            <input type="range" id="editRecordNyeri" min="0" max="10" value="5" style="width: 100%; height: 8px; -webkit-appearance: none; appearance: none; background: linear-gradient(to right, #28a745 0%, #ffc107 50%, #dc3545 100%); border-radius: 5px; outline: none;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">SPO2 (%)</label>
            <input type="number" id="editRecordSpo2" min="0" max="100" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <button onclick="closeEditModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 14px;">Batal</button>
            <button onclick="saveEditRecord()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 14px;">Simpan Perubahan</button>
        </div>
    </div>
</div>

<script>
// Variable untuk menyimpan index record yang sedang diedit
let editingRecordIndex = null;

// Fungsi untuk membuka modal edit
function openEditModal(index) {
    const record = vitalSignsArray[index];
    if (!record) {
        alert('❌ Record tidak ditemukan!');
        return;
    }
    
    editingRecordIndex = index;
    
    // Populate form dengan data record
    // Ensure the time format is always HH:MM:SS
    let timeValue = record.jam || '00:00:00';
    if (timeValue.split(':').length === 2) {
        timeValue += ':00'; // Append seconds if missing
    }
    document.getElementById('editRecordTime').value = timeValue;
    document.getElementById('editRecordRespirasi').value = record.respirasi;
    document.getElementById('editRecordNadi').value = record.nadi;
    document.getElementById('editRecordSistol').value = record.sistol;
    document.getElementById('editRecordDiastol').value = record.diastol || '';
    document.getElementById('editRecordNyeri').value = record.nyeri;
    document.getElementById('editNyeriValue').textContent = record.nyeri;
    document.getElementById('editRecordSpo2').value = record.spo2;
    
    // Tampilkan modal
    document.getElementById('editRecordModal').style.display = 'flex';
    
    console.log('✏️ Edit modal opened for record index:', index);
}

// Fungsi untuk menutup modal edit
function closeEditModal() {
    document.getElementById('editRecordModal').style.display = 'none';
    editingRecordIndex = null;
    console.log('❌ Edit modal closed');
}

// Update nyeri value saat slider berubah
document.addEventListener('input', function(e) {
    if (e.target.id === 'editRecordNyeri') {
        document.getElementById('editNyeriValue').textContent = e.target.value;
    }
});

// Fungsi untuk menyimpan perubahan record
function saveEditRecord() {
    if (editingRecordIndex === null) {
        alert('❌ Tidak ada record yang sedang diedit!');
        return;
    }
    
    const jam = document.getElementById('editRecordTime').value;
    const respirasi = document.getElementById('editRecordRespirasi').value;
    const nadi = document.getElementById('editRecordNadi').value;
    const sistol = document.getElementById('editRecordSistol').value;
    const diastol = document.getElementById('editRecordDiastol').value;
    const nyeri = document.getElementById('editRecordNyeri').value;
    const spo2 = document.getElementById('editRecordSpo2').value;
    
    // Validasi
    if (!jam || !respirasi || !nadi || !sistol || !nyeri || !spo2) {
        alert('⚠️ Semua field harus diisi!');
        return;
    }
    
    // Update record
    vitalSignsArray[editingRecordIndex] = {
        ...vitalSignsArray[editingRecordIndex],
        jam: jam,
        respirasi: parseInt(respirasi),
        nadi: parseInt(nadi),
        sistol: parseInt(sistol),
        diastol: diastol ? parseInt(diastol) : null,
        nyeri: parseInt(nyeri),
        spo2: parseInt(spo2)
    };
    
    console.log('✅ Record updated:', vitalSignsArray[editingRecordIndex]);
    
    // Update tabel dan grafik
    updateVitalTable();
    updateVitalChart();
    updateVitalHiddenInput();
    
    // Tutup modal
    closeEditModal();
    
    alert('✅ Record berhasil diperbarui!');
}

// Tutup modal jika klik di luar modal
document.getElementById('editRecordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

</body>
</html>