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
$query = "SELECT b.*, p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp, p.gol_darah, p.tempat_lahir
          FROM booking_operasi b 
          LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
          WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

$pasien = $booking ? $booking : [];

// Calculate umur from tanggal_lahir
$umur = '';
if (!empty($pasien['tanggal_lahir'])) {
    $tgl_lahir = new DateTime($pasien['tanggal_lahir']);
    $today = new DateTime();
    $umur = $tgl_lahir->diff($today)->y . ' tahun';
}
$tgl_lahir_umur = (!empty($pasien['tanggal_lahir']) ? $pasien['tanggal_lahir'] : '') . ($umur ? ' (' . $umur . ')' : '');

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

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
        <div style="color: #004d80;">CHECKLIST KESELAMATAN OPERASI</div>
        <div>RMOK-0002</div>
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
                        <div class="input-container">
                            <input type="text" id="operator" name="operator" placeholder=" " value="<?php echo htmlspecialchars($existing_data['operator'] ?? ''); ?>" required>
                            <label for="operator" class="label-floating">Operator / dr. Bedah</label>
                        </div>
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
                        <input type="time" class="input-time" name="signin_time" value="<?php echo htmlspecialchars($existing_data['signin_time'] ?? ''); ?>" required>
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
                                <input type="text" name="dokter_anestesi_signin" value="<?php echo htmlspecialchars($existing_data['dokter_anestesi_signin'] ?? ''); ?>" placeholder="Nama Dokter Anestesi" style="width: 100%; padding: 8px;">
                            </td>
                            <td>
                                <input type="text" name="perawat_anestesi_signin" value="<?php echo htmlspecialchars($existing_data['perawat_anestesi_signin'] ?? ''); ?>" placeholder="Nama Perawat Anestesi" style="width: 100%; padding: 8px;">
                            </td>
                            <td>
                                <input type="text" name="perawat_sirkuler_signin" value="<?php echo htmlspecialchars($existing_data['perawat_sirkuler_signin'] ?? ''); ?>" placeholder="Nama Perawat Sirkuler" style="width: 100%; padding: 8px;">
                            </td>
                        </tr>
                    </table> 
                </div>

                <!-- Time Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Time Out</h3>
                        <input type="time" class="input-time" name="timeout_time" value="<?php echo htmlspecialchars($existing_data['timeout_time'] ?? ''); ?>" required>
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
                                <input type="text" name="perawat_sirkuler_timeout" value="<?php echo htmlspecialchars($existing_data['perawat_sirkuler_timeout'] ?? ''); ?>" placeholder="Nama Perawat Sirkuler" style="width: 100%; padding: 8px;">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Sign Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sign-out-alt"></i> Sign Out</h3>
                        <input type="time" class="input-time" name="signout_time" value="<?php echo htmlspecialchars($existing_data['signout_time'] ?? ''); ?>" required>
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
                                <input type="text" name="perawat_sirkuler_signout" value="<?php echo htmlspecialchars($existing_data['perawat_sirkuler_signout'] ?? ''); ?>" placeholder="Nama Perawat Sirkuler" style="width: 100%; padding: 8px;">
                            </td>
                            <td>
                                <input type="text" name="dokter_anestesi_signout" value="<?php echo htmlspecialchars($existing_data['dokter_anestesi_signout'] ?? ''); ?>" placeholder="Nama Dokter Anestesi" style="width: 100%; padding: 8px;">
                            </td>
                            <td>
                                <input type="text" name="operator_signout" value="<?php echo htmlspecialchars($existing_data['operator_signout'] ?? ''); ?>" placeholder="Nama Operator/Dokter Bedah" style="width: 100%; padding: 8px;">
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
</script>

