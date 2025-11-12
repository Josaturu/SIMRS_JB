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

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

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
       style="position: fixed; bottom: 80px; right: 20px; width: 50px; height: 50px; background: #6c757d; color: white; border: none; border-radius: 50%; font-size: 20px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 998; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease;"
       onmouseover="this.style.background='#5a6268'; this.style.transform='scale(1.1)';" 
       onmouseout="this.style.background='#6c757d'; this.style.transform='scale(1)';" 
       title="Kembali ke Detail Pasien">
        <i class="fas fa-arrow-left"></i>
    </a>

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
            <div class="form-grid">
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
            <div class="form-grid">
                <!-- Kolom Kiri -->
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <label for="jamMasuk"><strong>Jam Masuk:</strong></label>
                        <input type="time" id="jamMasuk" name="jamMasuk" style="width: 100px" value="<?php echo $existingData['jam_masuk'] ?? ''; ?>" required />
                        <label for="tglMasuk" style="margin-left:10px;"><strong>Tanggal Masuk:</strong></label>
                        <input type="date" id="tglMasuk" name="tglMasuk" style="width: 140px" value="<?php echo $existingData['tgl_masuk'] ?? ''; ?>" required />
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
                <div class="form-column">
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

        <!-- Vital Sign Section - Modern Layout -->
        <div class="card">
            <h2>Monitoring Vital Sign</h2>
            
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; margin-top: 20px;">
                <!-- LEFT: Form Input -->
                <div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                        <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 20px;">
                            <i class="fas fa-heartbeat"></i> Form Input
                        </h3>
                        
                        <!-- Jam (Auto Real-time) -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">
                                Waktu Pemeriksaan
                            </label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" id="vs_jam" readonly 
                                       style="flex: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 16px; font-weight: 600; color: #007bff; background: #e7f3ff;">
                                <button type="button" id="btn_set_time" 
                                        style="padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; white-space: nowrap;">
                                    <i class="fas fa-clock"></i> Update
                                </button>
                            </div>
                            <small style="color: #6c757d; font-size: 11px; margin-top: 3px; display: block;">
                                ⏰ Waktu akan otomatis di-update saat Anda klik tombol "Update"
                            </small>
                        </div>
                        
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
                        
                        <!-- Button Tambah -->
                        <button type="button" id="btn_add_vital" 
                                style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-plus-circle"></i> Tambah Record
                        </button>
                    </div>
                </div>
                
                <!-- RIGHT: Chart + History Table -->
                <div>
                    <!-- Grafik -->
                    <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                            <i class="fas fa-chart-line"></i> Grafik Vital Sign
                        </h3>
                        <canvas id="vitalChart" style="max-height: 300px;"></canvas>
                    </div>
                    
                    <!-- Table History Records (dibawah grafik) -->
                    <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                        <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                            <i class="fas fa-table"></i> History Records
                        </h3>
                        <div style="overflow-x: auto; max-height: 350px; overflow-y: auto;">
                            <table id="vital_table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Waktu</th>
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Resp</th>
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Nadi</th>
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">TD</th>
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Nyeri</th>
                                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">SPO2</th>
                                    </tr>
                                </thead>
                                <tbody id="vital_tbody">
                                    <tr>
                                        <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                                            <i class="fas fa-info-circle"></i> Belum ada data vital sign. Silakan tambah record baru.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden inputs untuk submit (JSON array) -->
            <input type="hidden" name="vital_signs_data" id="vital_signs_data" value="[]">
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
                        <select name="perawat_menyerahkan">
                            <option value="">Pilih Perawat</option>
                            <option value="perawat1">Perawat 1</option>
                            <option value="perawat2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="perawat_menerima">
                            <option value="">Pilih Perawat</option>
                            <option value="perawat1">Perawat 1</option>
                            <option value="perawat2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="dokter_anestesi">
                            <option value="">Pilih Dokter</option>
                            <option value="dokter1">Dokter 1</option>
                            <option value="dokter2">Dokter 2</option>
                        </select>
                </tr>
            </table>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" class="btn btn-success" onclick="cetakPDF()">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='/index.php?page=detail-pasien&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>'">Kembali</button>
        </div>
        </form>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
<script src="/assets/js/autosave.js"></script>
<script>
    // ==================== VITAL SIGN MANAGEMENT ====================
    let vitalSignsArray = [];
    let vitalChart = null;
    
    // Set current time
    function setCurrentTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = `${hours}:${minutes}:${seconds}`;
        document.getElementById('vs_jam').value = timeString;
    }
    
    // Initialize Chart.js
    function initChart() {
        const ctx = document.getElementById("vitalChart").getContext("2d");
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
                        label: "Skala Nyeri", 
                        data: [], 
                        borderColor: "#9b59b6", 
                        backgroundColor: "rgba(155, 89, 182, 0.1)",
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
                maintainAspectRatio: true,
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
    }
    
    // Update slider nyeri value display
    document.getElementById('vs_nyeri').addEventListener('input', function() {
        document.getElementById('nyeri_value').textContent = this.value;
    });
    
    // Button hover effect
    document.getElementById('btn_add_vital').addEventListener('mouseenter', function() {
        this.style.background = '#0056b3';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 8px rgba(0,123,255,0.3)';
    });
    
    document.getElementById('btn_add_vital').addEventListener('mouseleave', function() {
        this.style.background = '#007bff';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });
    
    // Add vital sign record
    document.getElementById('btn_add_vital').addEventListener('click', function() {
        const jam = document.getElementById('vs_jam').value;
        const respirasi = document.getElementById('vs_respirasi').value;
        const nadi = document.getElementById('vs_nadi').value;
        const sistol = document.getElementById('vs_sistol').value;
        const diastol = document.getElementById('vs_diastol').value;
        const nyeri = document.getElementById('vs_nyeri').value;
        const spo2 = document.getElementById('vs_spo2').value;
        
        // Validation
        if (!jam) {
            alert('⚠️ Jam harus diisi!');
            document.getElementById('vs_jam').focus();
            return;
        }
        
        if (!respirasi || !nadi || !sistol || !diastol || !spo2) {
            alert('⚠️ Semua field harus diisi!');
            return;
        }
        
        // Create record object
        const record = {
            id: Date.now(),
            jam: jam,
            respirasi: parseInt(respirasi),
            nadi: parseInt(nadi),
            sistol: parseInt(sistol),
            diastol: parseInt(diastol),
            nyeri: parseInt(nyeri),
            spo2: parseInt(spo2)
        };
        
        // Add to array
        vitalSignsArray.push(record);
        
        // Update UI
        updateTable();
        updateChart();
        updateHiddenInput();
        
        // Clear form
        setCurrentTime(); // Update ke waktu sekarang
        document.getElementById('vs_respirasi').value = '';
        document.getElementById('vs_nadi').value = '';
        document.getElementById('vs_sistol').value = '';
        document.getElementById('vs_diastol').value = '';
        document.getElementById('vs_nyeri').value = '5';
        document.getElementById('nyeri_value').textContent = '5';
        document.getElementById('vs_spo2').value = '';
        
        // Show success message
        showToast('✅ Data vital sign berhasil ditambahkan!');
    });
    
    // Update table
    function updateTable() {
        const tbody = document.getElementById('vital_tbody');
        
        if (vitalSignsArray.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                        <i class="fas fa-info-circle"></i> Belum ada data vital sign. Silakan tambah record baru.
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = '';
        vitalSignsArray.forEach((record, index) => {
            const row = document.createElement('tr');
            row.style.background = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
            row.innerHTML = `
                <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600; color: #495057;">
                    <i class="fas fa-clock" style="color: #007bff; margin-right: 5px;"></i>${record.jam}
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
                    <span style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                        ${record.sistol}/${record.diastol}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: ${getNyeriBadge(record.nyeri).color}; padding: 4px 10px; border-radius: 15px; color: white; font-weight: 600; font-size: 12px;">
                        ${record.nyeri}/10
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                        ${record.spo2}%
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Update chart
    function updateChart() {
        if (!vitalChart) return;
        
        const labels = vitalSignsArray.map(r => r.jam);
        const respirasi = vitalSignsArray.map(r => r.respirasi);
        const nadi = vitalSignsArray.map(r => r.nadi);
        const sistol = vitalSignsArray.map(r => r.sistol);
        const nyeri = vitalSignsArray.map(r => r.nyeri);
        const spo2 = vitalSignsArray.map(r => r.spo2);
        
        vitalChart.data.labels = labels;
        vitalChart.data.datasets[0].data = respirasi;
        vitalChart.data.datasets[1].data = nadi;
        vitalChart.data.datasets[2].data = sistol;
        vitalChart.data.datasets[3].data = nyeri;
        vitalChart.data.datasets[4].data = spo2;
        
        vitalChart.update();
    }
    
    // Update hidden input
    function updateHiddenInput() {
        // Save vital sign data as JSON
        document.getElementById('vital_sign_data').value = JSON.stringify(vitalSignsArray);
        
        // Capture chart as base64 image
        if (vitalChart && vitalSignsArray.length > 0) {
            setTimeout(() => {
                const chartImage = document.getElementById('vitalChart').toDataURL('image/png');
                document.getElementById('chart_image').value = chartImage;
                console.log('Chart image captured');
            }, 500); // Delay to ensure chart is fully rendered
        }
        
        // AutoSave to localStorage
        const storageKey = 'vital_signs_kamar_pemulihan_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            localStorage.setItem(storageKey, JSON.stringify(vitalSignsArray));
            console.log('Vital signs auto-saved to localStorage');
        } catch (e) {
            console.error('Failed to auto-save vital signs:', e);
        }
    }
    
    // Restore vital signs from localStorage
    function restoreVitalSigns() {
        const storageKey = 'vital_signs_kamar_pemulihan_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                vitalSignsArray = JSON.parse(saved);
                if (vitalSignsArray.length > 0) {
                    updateTable();
                    updateChart();
                    updateHiddenInput();
                    showToast('📋 Data vital sign dipulihkan dari autosave');
                    console.log('Vital signs restored from localStorage:', vitalSignsArray.length + ' records');
                }
            }
        } catch (e) {
            console.error('Failed to restore vital signs:', e);
        }
    }
    
    // Clear autosave from localStorage
    function clearAutosave() {
        const storageKey = 'vital_signs_kamar_pemulihan_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            localStorage.removeItem(storageKey);
            console.log('Vital signs autosave cleared');
        } catch (e) {
            console.error('Failed to clear autosave:', e);
        }
    }
    
    // Delete record
    function deleteVitalRecord(id) {
        if (confirm('🗑️ Hapus data vital sign ini?')) {
            vitalSignsArray = vitalSignsArray.filter(r => r.id !== id);
            updateTable();
            updateChart();
            updateHiddenInput();
            showToast('🗑️ Data berhasil dihapus!');
        }
    }
    
    // Get nyeri badge color
    function getNyeriBadge(value) {
        if (value <= 3) return { color: '#4caf50' };
        if (value <= 6) return { color: '#ff9800' };
        return { color: '#f44336' };
    }
    
    // Toast notification
    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #323232;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }
    
    // Make deleteVitalRecord global
    window.deleteVitalRecord = deleteVitalRecord;
    
    // Populate existing data
    function populateExistingData() {
        <?php if ($existingData): ?>
        const data = <?php echo json_encode($existingData); ?>;
        
        // Populate checkboxes
        if (data.spontan_adekuat) document.querySelector('input[name="spontan[]"][value="adekuat"]')?.setAttribute('checked', 'checked');
        if (data.spontan_penyumbatan) document.querySelector('input[name="spontan[]"][value="penyumbatan"]')?.setAttribute('checked', 'checked');
        if (data.spontan_alat) document.querySelector('input[name="spontan[]"][value="alat"]')?.setAttribute('checked', 'checked');
        if (data.kesadaran_sadar) document.querySelector('input[name="kesadaran[]"][value="sadar_betul"]')?.setAttribute('checked', 'checked');
        if (data.kesadaran_belum_sadar) document.querySelector('input[name="kesadaran[]"][value="belum_sadar"]')?.setAttribute('checked', 'checked');
        if (data.kesadaran_tidur_dalam) document.querySelector('input[name="kesadaran[]"][value="tidur_dalam"]')?.setAttribute('checked', 'checked');
        
        // Populate text inputs
        const fields = ['pemantauan_setiap', 'pemantauan_selama', 'analgesia', 'anti_muntah', 'antibiotik', 
                       'posisi_pasien', 'obat_lain', 'diet_nutrisi', 'lain_lain', 'jam_keluar', 'td_keluar',
                       'n_keluar', 'r_keluar', 's_keluar', 'spo2_keluar', 'skrining_nyeri', 'tujuan_keluar',
                       'catatan_khusus', 'aldrete_score', 'bromage_score', 'steward_score',
                       'nama_penanggungjawab', 'perawat_menyerahkan', 'perawat_menerima', 'dokter_anestesi'];
        
        fields.forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input && data[field]) {
                input.value = data[field];
            }
        });
        
        // Populate vital signs (nadi, sistol, diastol, respirasi, nyeri)
        for (let i = 1; i <= 3; i++) {
            ['nadi', 'sistol', 'diastol', 'respirasi', 'nyeri'].forEach(type => {
                const field = `${type}_${i}`;
                const input = document.querySelector(`[name="${field}"]`);
                if (input && data[field]) {
                    input.value = data[field];
                }
            });
        }
        
        // Load vital sign data from JSON and populate chart
        if (data.vital_sign_data) {
            try {
                const vitalSignData = JSON.parse(data.vital_sign_data);
                if (Array.isArray(vitalSignData) && vitalSignData.length > 0) {
                    vitalSignsArray = vitalSignData;
                    console.log('Loaded vital signs from database:', vitalSignsArray.length + ' records');
                    
                    // Update table and chart
                    updateTable();
                    updateChart();
                    updateHiddenInput();
                    
                    showToast('📊 Data vital sign berhasil dimuat (' + vitalSignsArray.length + ' records)');
                }
            } catch (e) {
                console.error('Failed to parse vital_sign_data:', e);
            }
        }
        <?php endif; ?>
    }
    
    // Cetak PDF
    function cetakPDF() {
        const no_rawat = '<?= $no_rawat ?>';
        const kode_paket = '<?= $kode_paket ?>';
        const tanggal = '<?= $tanggal ?>';
        const jam_mulai = '<?= $jam_mulai ?>';
        
        const url = `/process/pdf/pdf-kamar-pemulihan.php?no_rawat=${encodeURIComponent(no_rawat)}&kode_paket=${encodeURIComponent(kode_paket)}&tanggal=${encodeURIComponent(tanggal)}&jam_mulai=${encodeURIComponent(jam_mulai)}`;
        
        window.open(url, '_blank');
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial time
        setCurrentTime();
        
        // Update time button click
        document.getElementById('btn_set_time').addEventListener('click', function() {
            setCurrentTime();
            showToast('⏰ Waktu diperbarui!');
        });
        
        // Initialize chart first
        initChart();
        
        // Then populate existing data (will update chart if data exists)
        populateExistingData();
        
        // Restore vital signs from autosave (only if not editing and no data from DB)
        <?php if (!$isEdit): ?>
        if (vitalSignsArray.length === 0) {
            restoreVitalSigns();
        }
        <?php endif; ?>
        
        // Initialize AutoSave
        AutoSave.init('formKamarPemulihan', {
            debounce: 1000,
            exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai', 'vital_signs_data'],
            showNotification: true,
            clearOnSubmit: true
        });
        
        // Clear vital signs autosave on form submit
        const form = document.getElementById('formKamarPemulihan');
        if (form) {
            form.addEventListener('submit', function() {
                clearAutosave();
            });
        }
    });
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
</style>