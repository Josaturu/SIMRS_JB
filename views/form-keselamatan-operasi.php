<?php
session_start();

$page_title = "Checklist Keselamatan Operasi";
$document_code = "RMOK-0002";

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
$query = "SELECT b.*, 
                 p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp, p.gol_darah, p.tempat_lahir,
                 d.nama_dokter as nama_dokter_bedah
          FROM booking_operasi b 
          LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
          LEFT JOIN tbl_dokter d ON b.kd_dokter COLLATE utf8mb4_unicode_ci = d.id_dokter
          WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

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

$pasien = $booking ? $booking : [];

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

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

// Cek apakah data checklist sudah ada
$query_checklist = "SELECT * FROM tbl_anestesi_keselamatan_operasi 
                    WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_checklist = $db->prepare($query_checklist);
$stmt_checklist->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$existing_data = $stmt_checklist->fetch(PDO::FETCH_ASSOC);

// Helper function untuk checked state
function isChecked($value) {
    return !empty($value) && $value == 1 ? 'checked' : '';
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<style>
/* Time Input Wrapper - Compact Design */
.time-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    flex-wrap: nowrap;
}

.time-input-wrapper .input-time {
    width: 200px !important;
    min-width: 200px;
    padding: 12px 16px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    background: white;
    flex-shrink: 0;
}

.time-input-wrapper .input-time:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}


/* Section Header Adjustment */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
    gap: 20px;
    flex-wrap: wrap;
}

.section-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.section-header h3 i {
    color: #007bff;
}

/* Ensure time input wrapper doesn't overflow */
.section-header .time-input-wrapper {
    margin-left: auto;
}

/* Checklist Section Spacing */
.checklist-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

/* Prevent icon overlap on time input */
input[type="time"]::-webkit-calendar-picker-indicator {
    margin-left: 5px;
    cursor: pointer;
}

/* Responsive adjustment for smaller screens */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .section-header .time-input-wrapper {
        margin-left: 0;
        width: 100%;
    }
    
    .time-input-wrapper {
        width: 100%;
    }
    
    .time-input-wrapper .input-time {
        flex: 1;
        width: auto !important;
    }
}
</style>

<div class="container">
    <div class="title">
        <div style="color: #004d80;">CHECKLIST KESELAMATAN OPERASI</div>
        <div>RMOK-0002</div>
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

    <!-- Info Pasien & Operator -->
    <div class="card">
        <h2>Keterangan Pasien & Operator</h2>
        <?php
        // Get base URL for form action
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_url = $protocol . $host . $base_path;
        $form_action = rtrim($base_url, '/') . '/process/submit-keselamatan-operasi.php';
        ?>
        <form id="formKeselamatanOperasi" action="<?php echo htmlspecialchars($form_action); ?>" method="POST" data-no-loading>
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <div class="form-grid">
                <!-- Kolom kiri: Info Pasien -->
                <div class="form-column">
                    <h3><i class="fas fa-user-injured"></i> Informasi Pasien</h3>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="namaPasien" name="namaPasien" placeholder=" " value="<?php echo htmlspecialchars($existing_data['nama_pasien'] ?? $pasien['nama'] ?? ''); ?>" required>
                            <label for="namaPasien" class="label-floating">Nama Pasien</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="noRekamMedis" name="noRekamMedis" placeholder=" " value="<?php echo htmlspecialchars($existing_data['no_rekam_medis'] ?? $pasien['kode_rekam_medis'] ?? ''); ?>" required>
                            <label for="noRekamMedis" class="label-floating">No. Rekam Medis</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="tglLahir" name="tglLahir" placeholder=" " value="<?php echo htmlspecialchars($existing_data['tgl_lahir_umur'] ?? $tgl_lahir_umur); ?>">
                            <label for="tglLahir" class="label-floating">Tanggal Lahir / Umur</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="alamat" name="alamat" placeholder=" " value="<?php echo htmlspecialchars($existing_data['alamat'] ?? $pasien['alamat'] ?? ''); ?>">
                            <label for="alamat" class="label-floating">Alamat</label>
                        </div>
                    </div>
                </div>

                <!-- Kolom kanan: Info Dokter/Operasi -->
                <div class="form-column">
                    <h3><i class="fas fa-stethoscope"></i> Informasi Operasi</h3>
                    <div class="keterangan-pasien">
                        <?php
                        // Ambil operator dari existing_data atau booking
                        $current_operator = $existing_data['operator'] ?? $booking['nama_dokter_bedah'] ?? '';
                        $is_other_operator = !empty($current_operator) && !in_array($current_operator, array_column($dokter_list, 'nama_dokter'));
                        ?>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
                            <i class="fas fa-user-md"></i> Operator / Dokter Bedah
                        </label>
                        <select id="operator_select" name="operator_select" onchange="toggleInput('operator')" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                            <option value="">-- Pilih Dokter Bedah --</option>
                            <?php
                            foreach ($dokter_list as $dokter) {
                                $selected = ($current_operator == $dokter['nama_dokter']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                            }
                            ?>
                            <option value="lainnya" <?= $is_other_operator ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                        </select>
                        <div id="operator_input_container" style="display: <?= $is_other_operator ? 'block' : 'none' ?>; margin-top: 8px;">
                            <input type="text" id="operator_input" name="operator_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_operator ? htmlspecialchars($current_operator) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <input type="hidden" id="operator" name="operator" value="<?= htmlspecialchars($current_operator) ?>">
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="operasi" name="operasi" placeholder=" " value="<?php echo htmlspecialchars($existing_data['operasi'] ?? ''); ?>" required>
                            <label for="operasi" class="label-floating">Operasi / Tindakan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="date" id="tglTindakan" name="tglTindakan" placeholder=" " value="<?php echo htmlspecialchars($existing_data['tanggal_tindakan'] ?? ''); ?>" required>
                            <label for="tglTindakan" class="label-floating">Tanggal Tindakan</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Container -->
            <div class="card" style="margin-top: 20px;">
                <h2>Cheklist Keselamatan Operasi (Surgical Safety Checklist)</h2>
                
                <!-- Sign In -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sign-in-alt"></i> Sign In</h3>
                        <div class="time-input-wrapper">
                            <input type="time" class="input-time" name="signin_time" id="signin_time" value="<?php echo htmlspecialchars($existing_data['signin_time'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <ol>
                        <li>Pasien sudah konfirmasikan : 
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_1a" value="identitas" <?php echo isChecked($existing_data['signin_1a'] ?? 0); ?>> Identitas pasien & gelang pasien</label>
                                <label><input type="checkbox" name="signin_1b" value="lokasi" <?php echo isChecked($existing_data['signin_1b'] ?? 0); ?>> Lokasi operasi</label>
                                <label><input type="checkbox" name="signin_1c" value="prosedur" <?php echo isChecked($existing_data['signin_1c'] ?? 0); ?>> Prosedur</label>
                                <label><input type="checkbox" name="signin_1d" value="surat_izin" <?php echo isChecked($existing_data['signin_1d'] ?? 0); ?>> Surat izin operasi</label>
                            </div>
                        </li>
                        <li>Lokasi operasi sudah diberi tanda?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_2a" value="ya" <?php echo isChecked($existing_data['signin_2a'] ?? 0); ?>> Ya</label>
                                <label><input type="checkbox" name="signin_2b" value="tidak_dilakukan" <?php echo isChecked($existing_data['signin_2b'] ?? 0); ?>> Tidak dilakukan</label>
                            </div>
                        </li>
                        <li>Mesin anestesi dan obat-obatan sudah dicek lengkap :
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_3" value="ya" <?php echo isChecked($existing_data['signin_3'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                        <li>Pulse Oximeter terpasang dan berfungsi :
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_4" value="ya" <?php echo isChecked($existing_data['signin_4'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                        <li>Apakah pasien punya riwayat alergi?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_5a" value="tidak" <?php echo isChecked($existing_data['signin_5a'] ?? 0); ?>> Tidak</label>
                                <label><input type="checkbox" name="signin_5b" value="ya" <?php echo isChecked($existing_data['signin_5b'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                        <li>Kemungkinan kesulitan jalan nafas atau risiko aspirasi?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_6a" value="tidak" <?php echo isChecked($existing_data['signin_6a'] ?? 0); ?>> Tidak</label>
                                <label><input type="checkbox" name="signin_6b" value="peralatan_tersedia" <?php echo isChecked($existing_data['signin_6b'] ?? 0); ?>> Peralatan dan sistem telah tersedia</label>
                            </div>
                        </li>
                        <li>Resiko kehilangan darah > 500ml (7ml/kgBB pada anak)?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_7a" value="tidak" <?php echo isChecked($existing_data['signin_7a'] ?? 0); ?>> Tidak</label>
                                <label><input type="checkbox" name="signin_7b" value="iv_terapi" <?php echo isChecked($existing_data['signin_7b'] ?? 0); ?>> IV/sentral, dan terapi cairan telah direncanakan</label>
                            </div>
                        </li>
                    </ol> 
                    <table>
                        <tr>
                            <th>Dokter Anestesi</th>
                            <th>Perawat Anestesi</th>
                            <th>Perawat Sirkuler</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $current_dokter_anestesi_signin = $existing_data['dokter_anestesi_signin'] ?? '';
                                $is_other_dokter_anestesi_signin = !empty($current_dokter_anestesi_signin) && !in_array($current_dokter_anestesi_signin, array_column($dokter_list, 'nama_dokter'));
                                ?>
                                <select id="dokter_anestesi_signin_select" name="dokter_anestesi_signin_select" onchange="toggleInput('dokter_anestesi_signin')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Dokter Anestesi --</option>
                                    <?php
                                    foreach ($dokter_list as $dokter) {
                                        $selected = ($current_dokter_anestesi_signin == $dokter['nama_dokter']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_dokter_anestesi_signin ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="dokter_anestesi_signin_input_container" style="display: <?= $is_other_dokter_anestesi_signin ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="dokter_anestesi_signin_input" name="dokter_anestesi_signin_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_dokter_anestesi_signin ? htmlspecialchars($current_dokter_anestesi_signin) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="dokter_anestesi_signin" name="dokter_anestesi_signin" value="<?= htmlspecialchars($current_dokter_anestesi_signin) ?>">
                            </td>
                            <td>
                                <?php
                                $current_perawat_anestesi_signin = $existing_data['perawat_anestesi_signin'] ?? '';
                                $is_other_perawat_anestesi_signin = !empty($current_perawat_anestesi_signin) && !in_array($current_perawat_anestesi_signin, array_column($perawat_list, 'nama_perawat'));
                                ?>
                                <select id="perawat_anestesi_signin_select" name="perawat_anestesi_signin_select" onchange="toggleInput('perawat_anestesi_signin')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Perawat Anestesi --</option>
                                    <?php
                                    foreach ($perawat_list as $perawat) {
                                        $selected = ($current_perawat_anestesi_signin == $perawat['nama_perawat']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_perawat_anestesi_signin ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="perawat_anestesi_signin_input_container" style="display: <?= $is_other_perawat_anestesi_signin ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="perawat_anestesi_signin_input" name="perawat_anestesi_signin_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_anestesi_signin ? htmlspecialchars($current_perawat_anestesi_signin) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="perawat_anestesi_signin" name="perawat_anestesi_signin" value="<?= htmlspecialchars($current_perawat_anestesi_signin) ?>">
                            </td>
                            <td>
                                <?php
                                $current_perawat_sirkuler_signin = $existing_data['perawat_sirkuler_signin'] ?? '';
                                $is_other_perawat_sirkuler_signin = !empty($current_perawat_sirkuler_signin) && !in_array($current_perawat_sirkuler_signin, array_column($perawat_list, 'nama_perawat'));
                                ?>
                                <select id="perawat_sirkuler_signin_select" name="perawat_sirkuler_signin_select" onchange="toggleInput('perawat_sirkuler_signin')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Perawat Sirkuler --</option>
                                    <?php
                                    foreach ($perawat_list as $perawat) {
                                        $selected = ($current_perawat_sirkuler_signin == $perawat['nama_perawat']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_perawat_sirkuler_signin ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="perawat_sirkuler_signin_input_container" style="display: <?= $is_other_perawat_sirkuler_signin ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="perawat_sirkuler_signin_input" name="perawat_sirkuler_signin_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_sirkuler_signin ? htmlspecialchars($current_perawat_sirkuler_signin) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="perawat_sirkuler_signin" name="perawat_sirkuler_signin" value="<?= htmlspecialchars($current_perawat_sirkuler_signin) ?>">
                            </td>
                        </tr>
                    </table> 
                </div>

                <!-- Time Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Time Out</h3>
                        <div class="time-input-wrapper">
                            <input type="time" class="input-time" name="timeout_time" id="timeout_time" value="<?php echo htmlspecialchars($existing_data['timeout_time'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <ol>
                        <li>Konfirmasi seluruh anggota tim (nama dan peran masing-masing)
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_1" value="ya" <?php echo isChecked($existing_data['timeout_1'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                        <li>Konfirmasi secara verbal
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_2a" value="nama_pasien" <?php echo isChecked($existing_data['timeout_2a'] ?? 0); ?>> Nama Pasien</label>
                                <label><input type="checkbox" name="timeout_2b" value="prosedur" <?php echo isChecked($existing_data['timeout_2b'] ?? 0); ?>> Prosedur</label>
                                <label><input type="checkbox" name="timeout_2c" value="lokasi_insisi" <?php echo isChecked($existing_data['timeout_2c'] ?? 0); ?>> Lokasi dimana insisi akan dibuat</label>
                            </div>
                        </li>
                        <li>Antibiotik profilaksis dalam 60 menit sebelumnya
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_3" value="ya" <?php echo isChecked($existing_data['timeout_3'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                        <li>Antisipasi kejadian kritis: 
                            <div style="margin-top: 10px;" class="input-container">
                                <textarea name="catatan_dokter_bedah" placeholder=""><?php echo htmlspecialchars($existing_data['catatan_dokter_bedah'] ?? ''); ?></textarea>
                                <label class="label-floating">Catatan dokter bedah</label>
                            </div>
                            <div class="input-container">
                                <textarea name="catatan_dokter_anestesi" placeholder=""><?php echo htmlspecialchars($existing_data['catatan_dokter_anestesi'] ?? ''); ?></textarea>
                                <label class="label-floating">Catatan dokter anestesi</label>
                            </div>
                            <div class="input-container">
                                <textarea name="catatan_perawat" placeholder=""><?php echo htmlspecialchars($existing_data['catatan_perawat'] ?? ''); ?></textarea>
                                <label class="label-floating">Catatan perawat</label>
                            </div>
                        </li>
                        <li>Foto Rontgen / CT-Scan / MRI yang diperlukan telah ditayangkan?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_5a" value="ya" <?php echo isChecked($existing_data['timeout_5a'] ?? 0); ?>> Ya</label>
                                <label><input type="checkbox" name="timeout_5b" value="tidak" <?php echo isChecked($existing_data['timeout_5b'] ?? 0); ?>> Tidak</label>
                            </div>
                        </li>
                    </ol>
                    <table>
                        <tr>
                            <th>Perawat Sirkuler</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $current_perawat_sirkuler_timeout = $existing_data['perawat_sirkuler_timeout'] ?? '';
                                $is_other_perawat_sirkuler_timeout = !empty($current_perawat_sirkuler_timeout) && !in_array($current_perawat_sirkuler_timeout, array_column($perawat_list, 'nama_perawat'));
                                ?>
                                <select id="perawat_sirkuler_timeout_select" name="perawat_sirkuler_timeout_select" onchange="toggleInput('perawat_sirkuler_timeout')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Perawat Sirkuler --</option>
                                    <?php
                                    foreach ($perawat_list as $perawat) {
                                        $selected = ($current_perawat_sirkuler_timeout == $perawat['nama_perawat']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_perawat_sirkuler_timeout ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="perawat_sirkuler_timeout_input_container" style="display: <?= $is_other_perawat_sirkuler_timeout ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="perawat_sirkuler_timeout_input" name="perawat_sirkuler_timeout_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_sirkuler_timeout ? htmlspecialchars($current_perawat_sirkuler_timeout) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="perawat_sirkuler_timeout" name="perawat_sirkuler_timeout" value="<?= htmlspecialchars($current_perawat_sirkuler_timeout) ?>">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Sign Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sign-out-alt"></i> Sign Out</h3>
                        <div class="time-input-wrapper">
                            <input type="time" class="input-time" name="signout_time" id="signout_time" value="<?php echo htmlspecialchars($existing_data['signout_time'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <ol>
                        <li>Konfirmasi perawat secara verbal:
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signout_1a" value="nama_prosedur" <?php echo isChecked($existing_data['signout_1a'] ?? 0); ?>> Nama prosedur tindakan</label>
                                <label><input type="checkbox" name="signout_1b" value="instrumen_lengkap" <?php echo isChecked($existing_data['signout_1b'] ?? 0); ?>> Instrumen, kasa & jarum lengkap</label>
                                <label><input type="checkbox" name="signout_1c" value="spesimen_label" <?php echo isChecked($existing_data['signout_1c'] ?? 0); ?>> Spesimen diberi label </label>
                                <label><input type="checkbox" name="signout_1d" value="tidak_masalah_alat" <?php echo isChecked($existing_data['signout_1d'] ?? 0); ?>> Tidak ada masalah alat operasi</label>
                            </div>
                        </li>
                        <li>Operator dokter bedah, dokter anestesi dan perawat membahas masalah utama penyembuhan & manajemen pasien selanjutnya
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signout_2" value="ya" <?php echo isChecked($existing_data['signout_2'] ?? 0); ?>> Ya</label>
                            </div>
                        </li>
                    </ol>
                    <div class="signature-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Tasikmalaya,</span>
                        <input type="text" class="input-date" name="tanggal_keluar" value="<?php echo htmlspecialchars($existing_data['tanggal_keluar'] ?? ''); ?>" placeholder="Tanggal" required>
                        <input type="text" class="input-year" name="tahun_keluar" value="<?php echo htmlspecialchars($existing_data['tahun_keluar'] ?? ''); ?>" placeholder="Tahun" required>
                    </div>
                    <hr>
                    <table>
                        <tr>
                            <th>Perawat Sirkuler</th>
                            <th>Dokter Anestesi</th>
                            <th>Operator / Dokter Bedah</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $current_perawat_sirkuler_signout = $existing_data['perawat_sirkuler_signout'] ?? '';
                                $is_other_perawat_sirkuler_signout = !empty($current_perawat_sirkuler_signout) && !in_array($current_perawat_sirkuler_signout, array_column($perawat_list, 'nama_perawat'));
                                ?>
                                <select id="perawat_sirkuler_signout_select" name="perawat_sirkuler_signout_select" onchange="toggleInput('perawat_sirkuler_signout')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Perawat Sirkuler --</option>
                                    <?php
                                    foreach ($perawat_list as $perawat) {
                                        $selected = ($current_perawat_sirkuler_signout == $perawat['nama_perawat']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_perawat_sirkuler_signout ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="perawat_sirkuler_signout_input_container" style="display: <?= $is_other_perawat_sirkuler_signout ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="perawat_sirkuler_signout_input" name="perawat_sirkuler_signout_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_sirkuler_signout ? htmlspecialchars($current_perawat_sirkuler_signout) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="perawat_sirkuler_signout" name="perawat_sirkuler_signout" value="<?= htmlspecialchars($current_perawat_sirkuler_signout) ?>">
                            </td>
                            <td>
                                <?php
                                $current_dokter_anestesi_signout = $existing_data['dokter_anestesi_signout'] ?? '';
                                $is_other_dokter_anestesi_signout = !empty($current_dokter_anestesi_signout) && !in_array($current_dokter_anestesi_signout, array_column($dokter_list, 'nama_dokter'));
                                ?>
                                <select id="dokter_anestesi_signout_select" name="dokter_anestesi_signout_select" onchange="toggleInput('dokter_anestesi_signout')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Dokter Anestesi --</option>
                                    <?php
                                    foreach ($dokter_list as $dokter) {
                                        $selected = ($current_dokter_anestesi_signout == $dokter['nama_dokter']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_dokter_anestesi_signout ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="dokter_anestesi_signout_input_container" style="display: <?= $is_other_dokter_anestesi_signout ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="dokter_anestesi_signout_input" name="dokter_anestesi_signout_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_dokter_anestesi_signout ? htmlspecialchars($current_dokter_anestesi_signout) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="dokter_anestesi_signout" name="dokter_anestesi_signout" value="<?= htmlspecialchars($current_dokter_anestesi_signout) ?>">
                            </td>
                            <td>
                                <?php
                                $current_operator_signout = $existing_data['operator_signout'] ?? '';
                                $is_other_operator_signout = !empty($current_operator_signout) && !in_array($current_operator_signout, array_column($dokter_list, 'nama_dokter'));
                                ?>
                                <select id="operator_signout_select" name="operator_signout_select" onchange="toggleInput('operator_signout')" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Pilih Operator/Dokter Bedah --</option>
                                    <?php
                                    foreach ($dokter_list as $dokter) {
                                        $selected = ($current_operator_signout == $dokter['nama_dokter']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                                    }
                                    ?>
                                    <option value="lainnya" <?= $is_other_operator_signout ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                                </select>
                                <div id="operator_signout_input_container" style="display: <?= $is_other_operator_signout ? 'block' : 'none' ?>; margin-top: 5px;">
                                    <input type="text" id="operator_signout_input" name="operator_signout_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_operator_signout ? htmlspecialchars($current_operator_signout) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                                <input type="hidden" id="operator_signout" name="operator_signout" value="<?= htmlspecialchars($current_operator_signout) ?>">
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $existing_data ? 'Simpan Perubahan' : 'Simpan Data'; ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='/index.php?page=detail-pasien&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>'">Kembali</button>
                </div>
            </div>
        </form>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script src="/assets/js/autosave.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set tahun otomatis
        const yearInput = document.querySelector('.input-year');
        if (yearInput) {
            yearInput.value = new Date().getFullYear();
        }
        
        // Set tanggal otomatis
        const dateInput = document.querySelector('.input-date');
        if (dateInput) {
            const today = new Date();
            dateInput.value = today.getDate();
        }
        
        // Set waktu sekarang untuk input waktu
        const timeInputs = document.querySelectorAll('.input-time');
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                         now.getMinutes().toString().padStart(2, '0');
        
        timeInputs.forEach(input => {
            input.value = timeString;
        });
        
        // Initialize AutoSave
        AutoSave.init('formKeselamatanOperasi', {
            debounce: 1000,
            exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
            showNotification: true,
            clearOnSubmit: true
        });
    });


// Toggle Input untuk Dropdown dengan opsi "Lainnya"
function toggleInput(fieldName) {
    const select = document.getElementById(fieldName + '_select');
    const inputContainer = document.getElementById(fieldName + '_input_container');
    const inputField = document.getElementById(fieldName + '_input');
    const hiddenField = document.getElementById(fieldName);
    
    if (select.value === 'lainnya') {
        inputContainer.style.display = 'block';
        inputField.required = true;
        select.required = false;
        hiddenField.value = inputField.value;
    } else {
        inputContainer.style.display = 'none';
        inputField.required = false;
        select.required = true;
        hiddenField.value = select.value;
    }
}

// Initialize toggle untuk semua dropdown saat page load
document.addEventListener('DOMContentLoaded', function() {
    const fields = [
        'operator',
        'dokter_anestesi_signin',
        'perawat_anestesi_signin',
        'perawat_sirkuler_signin',
        'perawat_sirkuler_timeout',
        'perawat_sirkuler_signout',
        'dokter_anestesi_signout',
        'operator_signout'
    ];
    
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
            
            // Initialize on page load (untuk mode edit)
            if (select.value === 'lainnya') {
                toggleInput(fieldName);
            }
        }
    });
});
</script>

