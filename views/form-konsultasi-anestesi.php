<?php
session_start();

$page_title = "Konsultasi Anestesi";
$document_code = "RMOK 3A";

// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

// Jika tidak ada parameter, redirect ke daftar pasien (seragam)
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header('Location: /index.php?page=daftar-pasien');
    exit;
}

// Koneksi database untuk mendapatkan data booking dan pasien
$database = new Database();
$db = $database->getConnection();
$query = "SELECT bo.*, 
                 p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.alamat, p.jenis_kelamin, 
                 p.tempat_lahir, p.no_hp, p.gol_darah,
                 d.nama_dokter,
                 r.nama_ruang
          FROM booking_operasi bo 
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien 
          LEFT JOIN tbl_dokter d ON bo.dokter_rawat COLLATE utf8mb4_unicode_ci = d.id_dokter
          LEFT JOIN tbl_ruang r ON bo.ruang_rawat COLLATE utf8mb4_unicode_ci = r.id_ruang
          WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

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

// Ambil semua ruang dari tbl_ruang untuk dropdown
$query_ruang = "SELECT id_ruang, nama_ruang FROM tbl_ruang ORDER BY nama_ruang ASC";
$stmt_ruang = $db->prepare($query_ruang);
$stmt_ruang->execute();
$ruang_list = $stmt_ruang->fetchAll(PDO::FETCH_ASSOC);

// Set $pasien untuk header (jangan overwrite!)
$pasien = $booking;

// Debug: Log jenis kelamin dari database
error_log("DEBUG FORM - Jenis Kelamin dari DB: " . ($booking['jenis_kelamin'] ?? 'NULL'));
error_log("DEBUG FORM - Pasien jenis_kelamin: " . ($pasien['jenis_kelamin'] ?? 'NULL'));

// Load data konsultasi existing jika ada
$konsul = [];
$is_update = false;
try {
    $query_check = "SELECT * FROM tbl_anestesi_konsultasi_anestesi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
    $konsul = $stmt_check->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table mungkin belum dibuat, biarkan $konsul kosong
    error_log("Error loading konsultasi: " . $e->getMessage());
}

include __DIR__ . '/../includes/assets.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<style>
@keyframes slideInRight {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(400px); opacity: 0; }
}
.alert { padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 14px; }
.alert-success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
.alert-danger { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }

/* Required field indicator */
.required-field::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
    font-size: 16px;
}
.form-row > label.required-field::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}
/* Special handling for label-floating to prevent overlap */
.label-floating.required-field::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
    margin-left: 2px;
}
/* Ensure label moves up properly when input has value or is focused */
.input-container input:focus + .label-floating.required-field,
.input-container input:not(:placeholder-shown) + .label-floating.required-field,
.input-container select:focus + .label-floating.required-field,
.input-container select:not([value=""]) + .label-floating.required-field,
.input-container textarea:focus + .label-floating.required-field,
.input-container textarea:not(:placeholder-shown) + .label-floating.required-field {
    top: -22px !important;
    font-size: 12px !important;
    background: transparent !important;
    padding: 0 !important;
    left: 0 !important;
}
/* Also handle when input has value attribute */
.input-container input[value]:not([value=""]) + .label-floating.required-field {
    top: -22px !important;
    font-size: 12px !important;
    background: transparent !important;
    padding: 0 !important;
    left: 0 !important;
}
/* Force label up when has-value class is added */
.label-floating.has-value {
    top: -22px !important;
    font-size: 12px !important;
    background: transparent !important;
    padding: 0 !important;
    left: 0 !important;
}
.required-note {
    color: #dc3545;
    font-size: 12px;
    font-style: italic;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.required-note::before {
    content: "*";
    font-weight: bold;
    font-size: 16px;
}
</style>

<div class="container">
    <div class="title">
        <div style="color: #004d80;">KONSULTASI ANESTESI</div>
        <div>RMOK 3A</div>
    </div>
    
    <!-- Notifikasi Success/Error -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Tombol Back ke Detail Pasien (Floating) digantikan oleh Speed Dial -->
    
    <!-- Tombol Back to Top (Floating) -->
    <button id="backToTopBtn" 
            style="position: fixed; bottom: 20px; right: 20px; width: 40px; height: 40px; background: #007bff; color: white; border: none; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 999; display: none; align-items: center; justify-content: center; font-size: 18px; transition: all 0.3s ease;"
            onmouseover="this.style.background='#0056b3'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.3)';" 
            onmouseout="this.style.background='#007bff'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" 
            title="Kembali ke Atas">
        <i class="fas fa-arrow-up"></i>
    </button>
    
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

        <!-- Informasi Identitas -->
        <h2>Informasi Pasien</h2>
        <div class="required-note">* Hanya Ruang Perawatan dan Dokter Merawat yang wajib diisi</div>
        <form id="formKonsultasiAnestesi" action="/process/process-konsultasi-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <input type="hidden" name="id" value="<?php echo isset($konsul['id']) ? htmlspecialchars($konsul['id']) : ''; ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <?php
                        $current_ruang = $konsul['ruang_perawatan'] ?? $booking['nama_ruang'] ?? '';
                        $is_other_ruang = !empty($current_ruang) && !in_array($current_ruang, array_column($ruang_list, 'nama_ruang'));
                        ?>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
                            <i class="fas fa-door-open"></i> Ruang Perawatan <span style="color: #dc3545;">*</span>
                        </label>
                        <select id="ruang_select" name="ruang_select" onchange="toggleInput('ruang')" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                            <option value="">-- Pilih Ruang Perawatan --</option>
                            <?php
                            foreach ($ruang_list as $ruang) {
                                $selected = ($current_ruang == $ruang['nama_ruang']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($ruang['nama_ruang']) . "\" $selected>" . htmlspecialchars($ruang['nama_ruang']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_ruang ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="ruang_input_container" style="display: <?= $is_other_ruang ? 'block' : 'none' ?>; margin-top: 8px;">
                            <input type="text" id="ruang_input" name="ruang_input" placeholder="Nama Ruang Lainnya" value="<?= $is_other_ruang ? htmlspecialchars($current_ruang) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <input type="hidden" id="ruang" name="ruang" value="<?= htmlspecialchars($current_ruang) ?>">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Default dari booking operasi, dapat diedit jika salah
                        </small>
                    </div>
                    <div class="keterangan-pasien">
                        <?php
                        $current_dokter = $konsul['dokter_merawat'] ?? $booking['nama_dokter'] ?? '';
                        $is_other_dokter = !empty($current_dokter) && !in_array($current_dokter, array_column($dokter_list, 'nama_dokter'));
                        ?>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
                            <i class="fas fa-user-md"></i> Dokter Merawat
                        </label>
                        <select id="dokter_select" name="dokter_select" onchange="toggleInput('dokter')" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php
                            foreach ($dokter_list as $dokter) {
                                $selected = ($current_dokter == $dokter['nama_dokter']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_dokter ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="dokter_input_container" style="display: <?= $is_other_dokter ? 'block' : 'none' ?>; margin-top: 8px;">
                            <input type="text" id="dokter_input" name="dokter_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_dokter ? htmlspecialchars($current_dokter) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <input type="hidden" id="dokter" name="dokter" value="<?= htmlspecialchars($current_dokter) ?>">
                    </div>
                </div>
            </div>

            <!-- Bagian A: Identitas Pasien -->
            <h2>Data Pasien</h2>
            <div class="form-section">
                <div class="document-id">RMOK 3A</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="tanggalKonsul" name="tanggalKonsul" placeholder=" " value="<?= htmlspecialchars($konsul['tanggal_konsul'] ?? '') ?>">
                            <label for="tanggalKonsul" class="label-floating">Tanggal</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="jam" name="jam" placeholder=" " value="<?= htmlspecialchars($konsul['jam_konsul'] ?? '') ?>">
                            <label for="jam" class="label-floating">Jam</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="tinggiBadan" name="tinggiBadan" placeholder=" " value="<?= htmlspecialchars($konsul['tinggi_badan'] ?? '') ?>" step="0.1">
                            <label for="tinggiBadan" class="label-floating">Tinggi Badan (cm)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="beratBadan" name="beratBadan" placeholder=" " value="<?= htmlspecialchars($konsul['berat_badan'] ?? '') ?>" step="0.1">
                            <label for="beratBadan" class="label-floating">Berat Badan (kg)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="diagnosaPraOperasi" name="diagnosaPraOperasi" placeholder=" "><?= htmlspecialchars($konsul['diagnosa_pra_operasi'] ?? '') ?></textarea>
                        <label for="diagnosaPraOperasi" class="label-floating">Diagnosa Pra Operasi</label>
                    </div>
                    <div class="radio-group">
                        <?php
                        // Cek jenis diagnosa dari konsultasi atau default Elektif
                        $jenis_diagnosa = $konsul['jenis_diagnosa'] ?? 'Elektif';
                        ?>
                        <label><input type="radio" name="jenisDiagnosa" value="Cito" <?= $jenis_diagnosa == 'Cito' ? 'checked' : '' ?>> Cito</label>
                        <label><input type="radio" name="jenisDiagnosa" value="Elektif" <?= $jenis_diagnosa == 'Elektif' ? 'checked' : '' ?>> Elektif</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="rencanaTindakanOperasi" name="rencanaTindakanOperasi" placeholder=" "><?= htmlspecialchars($konsul['rencana_tindakan_operasi'] ?? '') ?></textarea>
                        <label for="rencanaTindakanOperasi" class="label-floating">Rencana Tindakan Operasi</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="kondisiKhusus" name="kondisiKhusus" placeholder=" "><?= htmlspecialchars($konsul['kondisi_khusus'] ?? '') ?></textarea>
                        <label for="kondisiKhusus" class="label-floating">Kondisi Khusus atau penyulit yang mungkin terjadi pada pasien</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="datetime-local" id="tanggalDibuat" name="tanggalDibuat" placeholder=" " value="<?= htmlspecialchars($konsul['tanggal_dibuat'] ?? '') ?>">
                        <label for="tanggalDibuat" class="label-floating">Tanggal dan Jam Dibuat</label>
                    </div>
                </div>
            </div>

            <!-- Bagian B: Jawaban Konsul -->
            <h2>JAWABAN KONSUL (ASSESMENT PRA ANESTESI / PRADESI)</h2>
            <div class="form-section">
                <div class="subsection-header">
                    <div class="subsection-title">DIISI : ANAMNESA</div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="jamVisit" name="jamVisit" placeholder=" " value="<?= htmlspecialchars($konsul['jam_visit'] ?? '') ?>">
                            <label for="jamVisit" class="label-floating">Jam Visit</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Menikah</label>
                        <div class="radio-group">
                            <label><input type="radio" name="menikah" value="Ya" <?= !isset($konsul['menikah']) || $konsul['menikah'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="menikah" value="Tidak" <?= isset($konsul['menikah']) && $konsul['menikah'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Jenis Kelamin <span style="color: red;">*</span></label>
                        <div class="radio-group">
                            <?php 
                            // Prioritas: 1. Data konsultasi, 2. Data pasien, 3. Default
                            $jk = $konsul['jenis_kelamin'] ?? $pasien['jenis_kelamin'] ?? '';
                            error_log("DEBUG FORM - Final jenis_kelamin: $jk");
                            
                            // Normalisasi nilai jenis kelamin
                            $is_laki = ($jk == 'Laki-laki' || $jk == 'L' || $jk == 'Laki-Laki');
                            $is_perempuan = ($jk == 'Perempuan' || $jk == 'Wanita' || $jk == 'P');
                            ?>
                            <label><input type="radio" name="jenis_kelamin" value="Laki-laki" <?= $is_laki ? 'checked' : '' ?>> Laki-laki</label>
                            <label><input type="radio" name="jenis_kelamin" value="Perempuan" <?= $is_perempuan ? 'checked' : '' ?>> Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Kebiasaan Merokok</label>
                        <div class="radio-group">
                            <label><input type="radio" name="merokok" value="Ya" <?= isset($konsul['merokok']) && $konsul['merokok'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="merokok" value="Sebanyak" <?= isset($konsul['merokok']) && $konsul['merokok'] == 'Sebanyak' ? 'checked' : '' ?>> Sebanyak</label>
                            <label><input type="radio" name="merokok" value="Tidak" <?= !isset($konsul['merokok']) || $konsul['merokok'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Kebiasaan Alkohol</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alkohol" value="Ya" <?= isset($konsul['alkohol']) && $konsul['alkohol'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="alkohol" value="Sebanyak" <?= isset($konsul['alkohol']) && $konsul['alkohol'] == 'Sebanyak' ? 'checked' : '' ?>> Sebanyak</label>
                            <label><input type="radio" name="alkohol" value="Tidak" <?= !isset($konsul['alkohol']) || $konsul['alkohol'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label>Pengobatan:</label>
                    <div class="form-row-grid">
                        <div class="form-item">
                            <div class="radio-group">
                                <label><input type="radio" name="has_pengobatan" value="Ya" <?= isset($konsul['has_pengobatan']) && $konsul['has_pengobatan'] == 'Ya' ? 'checked' : '' ?> onchange="togglePengobatan()"> Ya</label>
                                <label><input type="radio" name="has_pengobatan" value="Tidak" <?= isset($konsul['has_pengobatan']) && $konsul['has_pengobatan'] == 'Tidak' ? 'checked' : '' ?> onchange="togglePengobatan()"> Tidak</label>
                            </div>
                        </div>
                        <div class="form-item" id="pengobatanDetail" style="display: <?= isset($konsul['has_pengobatan']) && $konsul['has_pengobatan'] == 'Ya' ? 'block' : 'none' ?>; margin-top: 15px;">
                            <div class="input-container">
                                <textarea id="pengobatan" name="pengobatan" placeholder=" " rows="2"><?= htmlspecialchars($konsul['pengobatan'] ?? '') ?></textarea>
                                <label for="pengobatan" class="label-floating">Sebutkan dosis atau jumlah pil per hari</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label>Alergi Obat:</label>
                    <div class="form-row-grid">
                        <div class="form-item">
                            <div class="radio-group">
                                <label><input type="radio" name="has_alergi_obat" value="Ya" <?= isset($konsul['has_alergi_obat']) && $konsul['has_alergi_obat'] == 'Ya' ? 'checked' : '' ?> onchange="toggleAlergiObat()"> Ya</label>
                                <label><input type="radio" name="has_alergi_obat" value="Tidak" <?= isset($konsul['has_alergi_obat']) && $konsul['has_alergi_obat'] == 'Tidak' ? 'checked' : '' ?> onchange="toggleAlergiObat()"> Tidak</label>
                            </div>
                        </div>
                        <div class="form-item" id="alergiObatDetail" style="display: <?= isset($konsul['has_alergi_obat']) && $konsul['has_alergi_obat'] == 'Ya' ? 'block' : 'none' ?>; margin-top: 15px;">
                            <div class="input-container">
                                <textarea id="daftarAlergiObat" name="daftarAlergiObat" placeholder=" " rows="2"><?= htmlspecialchars($konsul['daftar_alergi_obat'] ?? '') ?></textarea>
                                <label for="daftarAlergiObat" class="label-floating">Daftar Obat & Tipe Reaksi</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Alergi Makanan</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alergi_makanan" value="Ya" <?= isset($konsul['alergi_makanan']) && $konsul['alergi_makanan'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="alergi_makanan" value="Tidak" <?= isset($konsul['alergi_makanan']) && $konsul['alergi_makanan'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Alergi Lateks</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alergi_lateks" value="Ya" <?= isset($konsul['alergi_lateks']) && $konsul['alergi_lateks'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="alergi_lateks" value="Tidak" <?= isset($konsul['alergi_lateks']) && $konsul['alergi_lateks'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="tidakAlergi" name="tidakAlergi" placeholder=" " value="<?= htmlspecialchars($konsul['tidak_alergi'] ?? '') ?>">
                        <label for="tidakAlergi" class="label-floating">Tidak Alergi</label>
                    </div>
                </div>

                <div class="form-row">
                    <label>Komunikasi</label>
                    <div class="form-row-grid">
                        <div class="form-item">
                            <div class="radio-group">
                                <label><input type="radio" name="komunikasi" value="Bahasa Indonesia" checked> Bahasa Indonesia</label>
                            </div>
                        </div>
                        <div class="form-item">
                            <div class="input-container">
                                <input type="text" id="komunikasiLainnya" name="komunikasiLainnya" placeholder=" " value="<?= htmlspecialchars($konsul['komunikasi_lainnya'] ?? '') ?>">
                                <label for="komunikasiLainnya" class="label-floating">Lainnya</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="subsection-title">Apakah Pasien pernah / sedang menderita penyakit di bawah ini?</div>
                
                <div class="checkbox-grid">
                    <?php
                    $penyakit_list = [
                        'asma' => 'Asma',
                        'hepatitis' => 'Hepatitis / Sakit Kuning',
                        'sesak_nafas' => 'Sesak Nafas',
                        'pingsan' => 'Pingsan',
                        'sumbatan_jalan_nafas' => 'Sumbatan Jalan Nafas',
                        'diabetes' => 'Diabetes',
                        'tidur_mengorok' => 'Tidur / Mengorok',
                        'anemia' => 'Anemia',
                        'serangan_jantung' => 'Serangan Jantung / Nyeri Dada',
                        'sakit_maag' => 'Sakit Maag',
                        'hipertensi' => 'Hipertensi',
                        'pendarahan' => 'Pendarahan yang tidak normal',
                        'stroke' => 'Stroke',
                        'pembekuan_darah' => 'Pembekuan darah yang tidak normal',
                        'kejang' => 'Kejang',
                        'penyakit_berat_lainnya' => 'Penyakit Berat Lainnya'
                    ];
                    
                    foreach ($penyakit_list as $key => $value):
                        $checked_ya = isset($konsul[$key]) && $konsul[$key] == 'Ya' ? 'checked' : '';
                        $checked_tidak = !isset($konsul[$key]) || $konsul[$key] == 'Tidak' ? 'checked' : '';
                    ?>
                    <div class="checkbox-item">
                        <label><input type="radio" name="<?php echo $key; ?>" value="Ya" <?= $checked_ya ?>> Ya</label>
                        <label><input type="radio" name="<?php echo $key; ?>" value="Tidak" <?= $checked_tidak ?>> Tidak</label>
                        <span><?php echo $value; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="subsection-title">Jelaskan penyakit yang dijawab "Ya":</div>
                
                <div class="form-row">
                    <div class="input-container">
                        <textarea id="penjelasanPenyakit" name="penjelasanPenyakit" placeholder=" " rows="3"><?= htmlspecialchars($konsul['penyakit_terpilih'] ?? '') ?></textarea>
                        <label for="penjelasanPenyakit" class="label-floating">Penjelasan Penyakit</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Gigi Palsu</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gigi_palsu" value="Ya" <?= isset($konsul['gigi_palsu']) && $konsul['gigi_palsu'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="gigi_palsu" value="Tidak" <?= isset($konsul['gigi_palsu']) && $konsul['gigi_palsu'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="makanTerakhir" name="makanTerakhir" placeholder=" " value="<?= htmlspecialchars($konsul['makan_terakhir'] ?? '') ?>">
                            <label for="makanTerakhir" class="label-floating">Makan / Minum terakhir jam</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <textarea id="riwayatOperasi" name="riwayatOperasi" placeholder=" " rows="3"><?= htmlspecialchars($konsul['riwayat_operasi'] ?? '') ?></textarea>
                            <label for="riwayatOperasi" class="label-floating">Riwayat Operasi, tahun dan jenis operasi</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="jenisAnestesi" name="jenisAnestesi" placeholder=" " rows="3"><?= htmlspecialchars($konsul['jenis_anestesi'] ?? '') ?></textarea>
                        <label for="jenisAnestesi" class="label-floating">Jenis Anestesi yang digunakan dan sebutkan komplikasi / reaksi yang dialami</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="terakhirPeriksa" name="terakhirPeriksa" placeholder=" " value="<?= htmlspecialchars($konsul['terakhir_periksa'] ?? '') ?>">
                            <label for="terakhirPeriksa" class="label-floating">Tanggal terakhir kali periksa kesehatan ke dokter</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="tempatPeriksaTerakhir" name="tempatPeriksaTerakhir" placeholder=" " value="<?= htmlspecialchars($konsul['tempat_periksa_terakhir'] ?? '') ?>">
                            <label for="tempatPeriksaTerakhir" class="label-floating">Dimana</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="penyakitGangguan" name="penyakitGangguan" placeholder=" " value="<?= htmlspecialchars($konsul['penyakit_gangguan'] ?? '') ?>">
                        <label for="penyakitGangguan" class="label-floating">Untuk penyakit gangguan apa</label>
                    </div>
                </div>
            </div>

            <!-- Bagian C: Pemeriksaan Dokter -->
            <h2>PEMERIKSAAN DOKTER</h2>
            <div class="form-section">
                <div class="document-id">RMOK 3B</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="jumlahKehamilan" name="jumlahKehamilan" placeholder=" " value="<?= htmlspecialchars($konsul['jumlah_kehamilan'] ?? '') ?>">
                            <label for="jumlahKehamilan" class="label-floating">Jumlah Kehamilan</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="jumlahAnak" name="jumlahAnak" placeholder=" " value="<?= htmlspecialchars($konsul['jumlah_anak'] ?? '') ?>">
                            <label for="jumlahAnak" class="label-floating">Jumlah Anak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Menyusui</label>
                        <div class="radio-group">
                            <label><input type="radio" name="menyusui" value="Ya" <?= isset($konsul['menyusui']) && $konsul['menyusui'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                            <label><input type="radio" name="menyusui" value="Tidak" <?= isset($konsul['menyusui']) && $konsul['menyusui'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="subsection-title">Diisi oleh : Dokter</div>
                
                <div class="subsection-title">Keadaan Umum</div>
                
                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="kesadaran" name="kesadaran" placeholder=" " value="<?= htmlspecialchars($konsul['kesadaran'] ?? '') ?>">
                        <label for="kesadaran" class="label-floating">Kesadaran</label>
                    </div>
                </div>

                <div class="subsection-title">Pemeriksaan Fisik</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="inline-input">
                            <span>TB:</span>
                            <input type="text" id="tb" name="tb" placeholder=" " value="<?= htmlspecialchars($konsul['tb'] ?? '') ?>"> <span>Cm</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>BB:</span>
                            <input type="text" id="bb" name="bb" placeholder=" " value="<?= htmlspecialchars($konsul['bb'] ?? '') ?>"> <span>Kg</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>TD:</span>
                            <input type="text" id="td" name="td" placeholder=" " value="<?= htmlspecialchars($konsul['td'] ?? '') ?>"> <span>mmHg</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>Nadi:</span>
                            <input type="text" id="nadi" name="nadi" placeholder=" " value="<?= htmlspecialchars($konsul['nadi'] ?? '') ?>"> <span>x/mnt</span>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="inline-input">
                            <span>RR:</span>
                            <input type="text" id="rr" name="rr" placeholder=" " value="<?= htmlspecialchars($konsul['rr'] ?? '') ?>"> <span>x/mnt</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>Suhu:</span>
                            <input type="text" id="suhu" name="suhu" placeholder=" " value="<?= htmlspecialchars($konsul['suhu'] ?? '') ?>"> <span>°C</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label>Skrining Nyeri</label>
                    <div class="radio-group">
                        <label><input type="radio" name="skrining_nyeri" value="Tidak" <?= isset($konsul['skrining_nyeri']) && $konsul['skrining_nyeri'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        <label><input type="radio" name="skrining_nyeri" value="Ya" <?= isset($konsul['skrining_nyeri']) && $konsul['skrining_nyeri'] == 'Ya' ? 'checked' : '' ?>> Ya <strong>(Lanjut pengisian Formulir Assesment Nyeri)</strong></label>
                    </div>
                </div>

                <!-- Jalan Nafas - Bagian 1 -->
                <div class="form-row">
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>1. Jalan Nafas</span>
                        <button type="button" class="btn-clear-radio" onclick="clearRadioGroup('jalan_nafas')" title="Kosongkan pilihan">
                            <i class="fas fa-times-circle"></i> Clear
                        </button>
                    </label>
                    <div class="radio-group">
                        <label><input type="radio" name="jalan_nafas" value="Normal" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Normal' ? 'checked' : '' ?>> Normal</label>
                        <label><input type="radio" name="jalan_nafas" value="Buka mulut > 2 jari" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Buka mulut > 2 jari' ? 'checked' : '' ?>> Buka Mulut > 2 jari</label>
                        <label><input type="radio" name="jalan_nafas" value="Jarak Thyrimental > 3 jari" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Jarak Thyrimental > 3 jari' ? 'checked' : '' ?>> Jarak Thyrimental > 3 jari</label>
                    </div>
                </div>

                <!-- Mallampati - Bagian 2 -->
                <div class="form-row">
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>2. Mallampati</span>
                        <button type="button" class="btn-clear-radio" onclick="clearRadioGroup('mallampati')" title="Kosongkan pilihan">
                            <i class="fas fa-times-circle"></i> Clear
                        </button>
                    </label>
                    <div class="radio-group">
                        <label><input type="radio" name="mallampati" value="Mallampati I" <?= isset($konsul['mallampati']) && $konsul['mallampati'] == 'Mallampati I' ? 'checked' : '' ?>> Mallampati I</label>
                        <label><input type="radio" name="mallampati" value="Mallampati II" <?= isset($konsul['mallampati']) && $konsul['mallampati'] == 'Mallampati II' ? 'checked' : '' ?>> Mallampati II</label>
                        <label><input type="radio" name="mallampati" value="Mallampati III" <?= isset($konsul['mallampati']) && $konsul['mallampati'] == 'Mallampati III' ? 'checked' : '' ?>> Mallampati III</label>
                        <label><input type="radio" name="mallampati" value="Mallampati IV" <?= isset($konsul['mallampati']) && $konsul['mallampati'] == 'Mallampati IV' ? 'checked' : '' ?>> Mallampati IV</label>
                    </div>
                </div>

                <!-- Gerakan Leher - Bagian 3 -->
                <div class="form-row">
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>3. Gerakan Leher</span>
                        <button type="button" class="btn-clear-radio" onclick="clearRadioGroup('gerakan_leher')" title="Kosongkan pilihan">
                            <i class="fas fa-times-circle"></i> Clear
                        </button>
                    </label>
                    <div class="radio-group">
                        <label><input type="radio" name="gerakan_leher" value="Maksimal" <?= isset($konsul['gerakan_leher']) && $konsul['gerakan_leher'] == 'Maksimal' ? 'checked' : '' ?> onchange="toggleGerakanLeher()"> Gerakan Leher Maksimal</label>
                        <label><input type="radio" name="gerakan_leher" value="Abnormal" <?= isset($konsul['gerakan_leher']) && $konsul['gerakan_leher'] == 'Abnormal' ? 'checked' : '' ?> onchange="toggleGerakanLeher()"> Abnormal</label>
                    </div>
                    <div class="form-item" id="gerakanLeherKeteranganDetail" style="display: <?= isset($konsul['gerakan_leher']) && $konsul['gerakan_leher'] == 'Abnormal' ? 'block' : 'none' ?>; margin-top: 15px;">
                        <div class="input-container">
                            <input type="text" id="gerakanLeherKeterangan" name="gerakanLeherKeterangan" placeholder=" " value="<?= htmlspecialchars($konsul['gerakan_leher_keterangan'] ?? '') ?>">
                            <label for="gerakanLeherKeterangan" class="label-floating">Keterangan Abnormal</label>
                        </div>
                    </div>
                </div>

                <style>
                .btn-clear-radio {
                    padding: 4px 12px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    font-size: 12px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                    transition: all 0.3s ease;
                }
                .btn-clear-radio:hover {
                    background: #c82333;
                    transform: scale(1.05);
                }
                .btn-clear-radio i {
                    font-size: 14px;
                }
                </style>

                <script>
                // Clear radio button group
                function clearRadioGroup(groupName) {
                    const radios = document.querySelectorAll(`input[name="${groupName}"]`);
                    radios.forEach(radio => {
                        radio.checked = false;
                    });
                    
                    // Clear keterangan if clearing gerakan_leher
                    if (groupName === 'gerakan_leher') {
                        document.getElementById('gerakanLeherKeteranganDetail').style.display = 'none';
                        document.getElementById('gerakanLeherKeterangan').value = '';
                    }
                    
                    console.log(`Cleared radio group: ${groupName}`);
                }

                // Toggle keterangan for Gerakan Leher
                function toggleGerakanLeher() {
                    const radios = document.querySelectorAll('input[name="gerakan_leher"]');
                    const keteranganDetail = document.getElementById('gerakanLeherKeteranganDetail');
                    let selectedValue = '';
                    
                    radios.forEach(radio => {
                        if (radio.checked) {
                            selectedValue = radio.value;
                        }
                    });
                    
                    if (selectedValue === 'Abnormal') {
                        keteranganDetail.style.display = 'block';
                    } else {
                        keteranganDetail.style.display = 'none';
                        document.getElementById('gerakanLeherKeterangan').value = '';
                    }
                }
                </script>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="paruParu" name="paruParu" placeholder=" " value="<?= htmlspecialchars($konsul['paru_paru'] ?? '') ?>">
                        <label for="paruParu" class="label-floating">Paru - paru</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="jantung" name="jantung" placeholder=" " value="<?= htmlspecialchars($konsul['jantung'] ?? '') ?>">
                        <label for="jantung" class="label-floating">Jantung</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="abdomen" name="abdomen" placeholder=" " value="<?= htmlspecialchars($konsul['abdomen'] ?? '') ?>">
                        <label for="abdomen" class="label-floating">Abdomen</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="ekstrimitas" name="ekstrimitas" placeholder=" " value="<?= htmlspecialchars($konsul['ekstrimitas'] ?? '') ?>">
                        <label for="ekstrimitas" class="label-floating">Ekstrimitas</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="neurologi" name="neurologi" placeholder=" " value="<?= htmlspecialchars($konsul['neurologi'] ?? '') ?>">
                        <label for="neurologi" class="label-floating">Neurologi</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="lainLain" name="lainLain" placeholder=" " value="<?= htmlspecialchars($konsul['lain_lain'] ?? '') ?>">
                        <label for="lainLain" class="label-floating">Lain - lain</label>
                    </div>
                </div>

                <div class="subsection-title">Pemeriksaan Penunjang</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="hbHtAlAt" name="hbHtAlAt" placeholder=" " value="<?= htmlspecialchars($konsul['hb_ht_al_at'] ?? '') ?>">
                            <label for="hbHtAlAt" class="label-floating">Hb/Ht/AL/AT</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="naKCl" name="naKCl" placeholder=" " value="<?= htmlspecialchars($konsul['na_k_cl'] ?? '') ?>">
                            <label for="naKCl" class="label-floating">NA/K/C L</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ureum" name="ureum" placeholder=" " value="<?= htmlspecialchars($konsul['ureum'] ?? '') ?>">
                            <label for="ureum" class="label-floating">Ureum</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ctBt" name="ctBt" placeholder=" " value="<?= htmlspecialchars($konsul['ct_bt'] ?? '') ?>">
                            <label for="ctBt" class="label-floating">CT / BT</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="kreatin" name="kreatin" placeholder=" " value="<?= htmlspecialchars($konsul['kreatin'] ?? '') ?>">
                            <label for="kreatin" class="label-floating">Kreatin</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ekg" name="ekg" placeholder=" " value="<?= htmlspecialchars($konsul['ekg'] ?? '') ?>">
                            <label for="ekg" class="label-floating">EKG</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="roDada" name="roDada" placeholder=" " value="<?= htmlspecialchars($konsul['ro_dada'] ?? '') ?>">
                            <label for="roDada" class="label-floating">RO Dada</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="echo" name="echo" placeholder=" " value="<?= htmlspecialchars($konsul['echo'] ?? '') ?>">
                            <label for="echo" class="label-floating">Echo</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="lainLainPemeriksaan" name="lainLainPemeriksaan" placeholder=" " value="<?= htmlspecialchars($konsul['lain_lain_pemeriksaan'] ?? '') ?>">
                        <label for="lainLainPemeriksaan" class="label-floating">Lain - lain</label>
                    </div>
                </div>

                <div class="subsection-title">DIAGNOSIS</div>
                
                <div class="diagnosis-section">
                    <div class="asa-list">
                        <div><strong>A. Klasifikasi berdasarkan ASA :</strong></div>
                        <div>1. ASA 1 Pasien normal yang Sehat</div>
                        <div>2. ASA 2 Pasien dengan Penyakit Sistemik Ringan</div>
                        <div>3. ASA 3 Pasien dengan Penyakit Sistemik Sedang</div>
                        <div>4. ASA 4 Pasien dengan Penyakit Sistemik Berat yang Mengancam Nyawa</div>
                        <div>5. ASA 5 Pasien yang apabila tidak dilakukan tindakan operasi akan mengancam nyawa</div>
                    </div>
                    <div class="radio-group">
                        <label><input type="radio" name="asa" value="ASA 1" <?= isset($konsul['asa_status']) && $konsul['asa_status'] == 'ASA 1' ? 'checked' : '' ?>> ASA 1</label>
                        <label><input type="radio" name="asa" value="ASA 2" <?= isset($konsul['asa_status']) && $konsul['asa_status'] == 'ASA 2' ? 'checked' : '' ?>> ASA 2</label>
                        <label><input type="radio" name="asa" value="ASA 3" <?= isset($konsul['asa_status']) && $konsul['asa_status'] == 'ASA 3' ? 'checked' : '' ?>> ASA 3</label>
                        <label><input type="radio" name="asa" value="ASA 4" <?= isset($konsul['asa_status']) && $konsul['asa_status'] == 'ASA 4' ? 'checked' : '' ?>> ASA 4</label>
                        <label><input type="radio" name="asa" value="ASA 5" <?= isset($konsul['asa_status']) && $konsul['asa_status'] == 'ASA 5' ? 'checked' : '' ?>> ASA 5</label>
                    </div>
                    <div><strong>B. Emergency</strong></div>
                    <div class="radio-group">
                        <label><input type="radio" name="emergency" value="Ya" <?= isset($konsul['emergency']) && $konsul['emergency'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                        <label><input type="radio" name="emergency" value="Tidak" <?= !isset($konsul['emergency']) || $konsul['emergency'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                    </div>
                    <div style="margin-top: 15px;"><strong>C. Lain - lain</strong></div>
                    <div class="input-container" style="margin-top: 10px;">
                        <input type="text" id="diagnosisLain" name="diagnosisLain" placeholder=" " value="<?= htmlspecialchars($konsul['lain_lain_diagnosis'] ?? '') ?>">
                        <label for="diagnosisLain" class="label-floating">Keterangan lain-lain</label>
                    </div>
                </div>

                <div class="subsection-title">REKOMENDASI TINDAKAN ANESTESI YANG DIPILIH</div>
                
                <div class="anesthesia-options">
                    <div class="option-group">
                        <div class="option-title">Anestesi Umum</div>
                        <div class="radio-group">
                            <label><input type="radio" name="anestesi_umum" value="Intravena" <?= isset($konsul['anestesi_umum']) && $konsul['anestesi_umum'] == 'Intravena' ? 'checked' : '' ?>> Intravena</label>
                            <label><input type="radio" name="anestesi_umum" value="Sungkup Muka" <?= isset($konsul['anestesi_umum']) && $konsul['anestesi_umum'] == 'Sungkup Muka' ? 'checked' : '' ?>> Sungkup Muka</label>
                            <label><input type="radio" name="anestesi_umum" value="LMA" <?= isset($konsul['anestesi_umum']) && $konsul['anestesi_umum'] == 'LMA' ? 'checked' : '' ?>> LMA</label>
                            <label><input type="radio" name="anestesi_umum" value="Pipa ET" <?= isset($konsul['anestesi_umum']) && $konsul['anestesi_umum'] == 'Pipa ET' ? 'checked' : '' ?>> Pipa ET</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Regional Anestesi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="regional" value="Spinal Anestesi" <?= isset($konsul['regional_anestesi']) && $konsul['regional_anestesi'] == 'Spinal Anestesi' ? 'checked' : '' ?>> Spinal Anestesi</label>
                            <label><input type="radio" name="regional" value="Epidural" <?= isset($konsul['regional_anestesi']) && $konsul['regional_anestesi'] == 'Epidural' ? 'checked' : '' ?>> Epidural</label>
                            <label><input type="radio" name="regional" value="CSE" <?= isset($konsul['regional_anestesi']) && $konsul['regional_anestesi'] == 'CSE' ? 'checked' : '' ?>> CSE</label>
                            <label><input type="radio" name="regional" value="PNB" <?= isset($konsul['regional_anestesi']) && $konsul['regional_anestesi'] == 'PNB' ? 'checked' : '' ?>> PNB</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Kombinasi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="combined" value="Anestesi Umum + Regional" <?= isset($konsul['kombinasi_anestesi']) && $konsul['kombinasi_anestesi'] == 'Anestesi Umum + Regional' ? 'checked' : '' ?>> Anestesi Umum + Regional Anestesi</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Sedasi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="sedasi" value="Sedasi Sedang" <?= isset($konsul['sedasi']) && $konsul['sedasi'] == 'Sedasi Sedang' ? 'checked' : '' ?>> Sedasi Sedang</label>
                            <label><input type="radio" name="sedasi" value="Sedasi Dalam" <?= isset($konsul['sedasi']) && $konsul['sedasi'] == 'Sedasi Dalam' ? 'checked' : '' ?>> Sedasi Dalam</label>
                        </div>
                    </div>
                </div>

                <div class="form-row" style="margin-top: 20px;">
                    <label>SARAN</label>
                    <div class="input-container" style="margin-top: 10px;">
                        <textarea id="saran" name="saran" placeholder=" " rows="3"><?= htmlspecialchars($konsul['saran'] ?? '') ?></textarea>
                        <label for="saran" class="label-floating">Saran</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="puasaMulaiJam" name="puasaMulaiJam" placeholder=" " value="<?= htmlspecialchars($konsul['puasa_mulai_jam'] ?? '') ?>">
                            <label for="puasaMulaiJam" class="label-floating">Puasa Mulai (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="puasaMulaiTanggal" name="puasaMulaiTanggal" placeholder=" " value="<?= htmlspecialchars($konsul['puasa_mulai_tanggal'] ?? '') ?>">
                            <label for="puasaMulaiTanggal" class="label-floating">Puasa Mulai (Tanggal)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="rencanaTibaJam" name="rencanaTibaJam" placeholder=" " value="<?= htmlspecialchars($konsul['rencana_tiba_jam'] ?? '') ?>">
                            <label for="rencanaTibaJam" class="label-floating">Rencana Tiba di OK (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="rencanaTibaTanggal" name="rencanaTibaTanggal" placeholder=" " value="<?= htmlspecialchars($konsul['rencana_tiba_tanggal'] ?? '') ?>">
                            <label for="rencanaTibaTanggal" class="label-floating">Rencana Tiba di OK (Tanggal)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="rencanaOperasiJam" name="rencanaOperasiJam" placeholder=" " value="<?= htmlspecialchars($konsul['rencana_operasi_jam'] ?? '') ?>">
                            <label for="rencanaOperasiJam" class="label-floating">Rencana Operasi (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="rencanaOperasiTanggal" name="rencanaOperasiTanggal" placeholder=" " value="<?= htmlspecialchars($konsul['rencana_operasi_tanggal'] ?? '') ?>">
                            <label for="rencanaOperasiTanggal" class="label-floating">Rencana Operasi (Tanggal)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <!-- Tombol aksi utama dipindahkan ke Speed Dial -->
            </div>
        </form>
    </div>
</div>

<!-- Speed Dial: Simpan, Cetak PDF, Kembali -->
<div data-dial-init class="fixed right-6 bottom-6 group">
    <div id="speed-dial-menu-konsultasi-anestesi" class="flex flex-col w-32 justify-end hidden mb-4 space-y-2 bg-neutral-primary-medium border border-default-medium rounded-base shadow-xs speed-dial-menu">
        <ul class="p-2 text-sm text-body font-medium">
            <li>
                <a href="#" onclick="document.getElementById('formKonsultasiAnestesi').submit(); return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
                    <span class="text-sm font-medium">Simpan</span>
                </a>
            </li>
            <li>
                <a href="/process/pdf/pdf-konsultasi-anestesi.php?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" target="_blank" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z"/></svg>
                    <span class="text-sm font-medium">Cetak PDF</span>
                </a>
            </li>
            <li>
                <a href="/index.php?page=detail-pasien&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <!-- Back Arrow Icon -->
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6l-6 6 6 6"/></svg>
                    <span class="text-sm font-medium">Kembali</span>
                </a>
            </li>
        </ul>
    </div>
    <button type="button" data-dial-toggle="speed-dial-menu-konsultasi-anestesi" aria-controls="speed-dial-menu-konsultasi-anestesi" aria-expanded="false" class="flex items-center justify-center ml-auto text-white bg-brand rounded-base w-14 h-14 hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium focus:outline-none">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
        <span class="sr-only">Open actions menu</span>
    </button>
</div>

<script src="/assets/js/autosave.js"></script>
<script>
// JavaScript untuk toggle show/hide field pengobatan
function togglePengobatan() {
    var hasPengobatan = document.querySelector('input[name="has_pengobatan"]:checked');
    var pengobatanDetail = document.getElementById('pengobatanDetail');
    
    if (hasPengobatan && hasPengobatan.value === 'Ya') {
        pengobatanDetail.style.display = 'block';
    } else {
        pengobatanDetail.style.display = 'none';
        // Clear value jika "Tidak"
        document.getElementById('pengobatan').value = '';
    }
}

// JavaScript untuk toggle show/hide field alergi obat
function toggleAlergiObat() {
    var hasAlergiObat = document.querySelector('input[name="has_alergi_obat"]:checked');
    var alergiObatDetail = document.getElementById('alergiObatDetail');
    
    if (hasAlergiObat && hasAlergiObat.value === 'Ya') {
        alergiObatDetail.style.display = 'block';
    } else {
        alergiObatDetail.style.display = 'none';
        // Clear value jika "Tidak"
        document.getElementById('daftarAlergiObat').value = '';
    }
}

// JavaScript untuk toggle show/hide field jalan nafas abnormal
function toggleJalanNafas() {
    var jalanNafas = document.querySelector('input[name="jalan_nafas"]:checked');
    var jalanNafasKeteranganDetail = document.getElementById('jalanNafasKeteranganDetail');
    
    console.log('toggleJalanNafas called');
    console.log('Selected value:', jalanNafas ? jalanNafas.value : 'none');
    console.log('Element found:', jalanNafasKeteranganDetail);
    
    if (jalanNafas && jalanNafas.value === 'Abnormal') {
        console.log('Showing keterangan');
        jalanNafasKeteranganDetail.style.display = 'block';
    } else {
        console.log('Hiding keterangan');
        jalanNafasKeteranganDetail.style.display = 'none';
        // Clear value jika bukan "Abnormal"
        var inputKeterangan = document.getElementById('jalanNafasKeterangan');
        if (inputKeterangan) {
            inputKeterangan.value = '';
        }
    }
}

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

// Run on page load untuk set initial state
document.addEventListener('DOMContentLoaded', function() {
    togglePengobatan();
    toggleAlergiObat();
    toggleJalanNafas();
    
    // Initialize toggle untuk dropdown ruang dan dokter
    const fields = ['ruang', 'dokter'];
    
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
    AutoSave.init('formKonsultasiAnestesi', {
        debounce: 1000,
        exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
        showNotification: true,
        clearOnSubmit: true
    });
});

// Debug: Log form data sebelum submit
document.getElementById('formKonsultasiAnestesi').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    console.log('=== FORM DATA DEBUG ===');
    console.log('jenis_kelamin:', formData.get('jenis_kelamin'));
    console.log('merokok:', formData.get('merokok'));
    console.log('alkohol:', formData.get('alkohol'));
    console.log('emergency:', formData.get('emergency'));
    console.log('asma:', formData.get('asma'));
    console.log('diabetes:', formData.get('diabetes'));
    console.log('======================');
});

// Back to Top Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTopBtn');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        // Scroll to top when button clicked
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
</script>