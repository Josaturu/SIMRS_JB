<?php
session_start();

$page_title = "Informed Consent Tindakan Anestesi";
$document_code = "RMC 4a Rev-01";

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

// Koneksi database untuk mendapatkan data booking dan pasien
$database = new Database();
$db = $database->getConnection();
$query = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin
          FROM booking_operasi bo
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
          WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

$pasien = $booking;

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

// Cek apakah data informed consent sudah ada (untuk mode edit)
$query_consent = "SELECT * FROM tbl_anestesi_informed_consent_anestesi 
                  WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_consent = $db->prepare($query_consent);
$stmt_consent->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$consent = $stmt_consent->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
    <div class="title">
        <div style="color: #004d80;">INFORMED CONSENT TINDAKAN ANESTESI</div>
        <div>RMC 4a Rev-01</div>
    </div>

    <div class="card">
        <!-- Form Data Booking -->
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

        <!-- Informasi Identitas -->
        <h2>Pemberian Informasi</h2>
        <form id="formInformedConsent" action="process/process-informed-consent-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <input type="hidden" name="id" value="<?php echo isset($consent['id']) ? htmlspecialchars($consent['id']) : ''; ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="ruang" name="ruang" placeholder=" " value="<?= htmlspecialchars($consent['ruang'] ?? '') ?>" required>
                            <label for="ruang" class="label-floating">Ruang Perawatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="dokter" name="dokter" placeholder=" " value="<?= htmlspecialchars($consent['dokter_pelaksana'] ?? '') ?>" required>
                            <label for="dokter" class="label-floating">Dokter Pelaksana Tindakan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="pemberi" name="pemberi" placeholder=" " value="<?= htmlspecialchars($consent['pemberi_info'] ?? '') ?>" required>
                            <label for="pemberi" class="label-floating">Nama Pemberi Informasi</label>
                        </div>
                    </div>
                </div>
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="jabatan" name="jabatan" placeholder=" " value="<?= htmlspecialchars($consent['jabatan'] ?? '') ?>" required>
                            <label for="jabatan" class="label-floating">Jabatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="penerima" name="penerima" placeholder=" " value="<?= htmlspecialchars($consent['penerima_info'] ?? '') ?>" required>
                            <label for="penerima" class="label-floating">Nama Penerima Informasi</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="hubungan" name="hubungan" placeholder=" " value="<?= htmlspecialchars($consent['hubungan_pasien'] ?? '') ?>" required>
                            <label for="hubungan" class="label-floating">Hubungan dengan Pasien</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Utama -->
            <h2>Informasi Tindakan Anestesi</h2>
            <?php
            // Explode data checkbox untuk auto-check saat edit
            $jenis_anestesi_data = isset($consent['jenis_anestesi']) ? explode(', ', $consent['jenis_anestesi']) : [];
            $indikasi_data = isset($consent['indikasi']) ? explode(', ', $consent['indikasi']) : [];
            $tata_cara_data = isset($consent['tata_cara']) ? explode(', ', $consent['tata_cara']) : [];
            $risiko_data = isset($consent['risiko']) ? explode(', ', $consent['risiko']) : [];
            $status_fisik_data = isset($consent['status_fisik']) ? $consent['status_fisik'] : '';
            $checkbox_confirm_data = isset($consent['checkbox_confirm']) ? explode(',', $consent['checkbox_confirm']) : [];
            ?>
            <table>
                <tr>
                    <th colspan="4" style="text-align: right;">RMC 4a Rev-01</th>
                </tr>
                <tr>
                    <th>No</th>
                    <th>Jenis Informasi</th>
                    <th>Isi Informasi</th>
                    <th>Ceklis</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Nama Tindakan Operasi</td>
                    <td>
                        <div class="input-container">
                            <input type="text" id="tindakanOperasi" name="tindakanOperasi" placeholder=" " value="<?= htmlspecialchars($consent['tindakan_operasi'] ?? '') ?>" required>
                            <label for="tindakanOperasi" class="label-floating">Nama Tindakan Operasi</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek1" value="1" <?= in_array('1', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Tindakan yang akan dilakukan</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Umum" <?= in_array('Anestesi Umum', $jenis_anestesi_data) ? 'checked' : '' ?>> Anestesi Umum</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Spinal" <?= in_array('Anestesi Spinal', $jenis_anestesi_data) ? 'checked' : '' ?>> Anestesi Spinal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Epidural" <?= in_array('Anestesi Epidural', $jenis_anestesi_data) ? 'checked' : '' ?>> Anestesi Epidural</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Kaudal" <?= in_array('Anestesi Kaudal', $jenis_anestesi_data) ? 'checked' : '' ?>> Anestesi Kaudal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Kombinasi Spinal-Epidural" <?= in_array('Kombinasi Spinal-Epidural', $jenis_anestesi_data) ? 'checked' : '' ?>> Kombinasi Spinal-Epidural</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Blok Saraf Perifer" <?= in_array('Blok Saraf Perifer', $jenis_anestesi_data) ? 'checked' : '' ?>> Blok Saraf Perifer</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Sedasi" <?= in_array('Sedasi', $jenis_anestesi_data) ? 'checked' : '' ?>> Sedasi</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek2" value="1" <?= in_array('2', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Indikasi Tindakan</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="indikasi[]" value="Menghilangkan kesadaran" <?= in_array('Menghilangkan kesadaran', $indikasi_data) ? 'checked' : '' ?>> Menghilangkan kesadaran selama prosedur atau tindakan pembedahan</label>
                            <label><input type="checkbox" name="indikasi[]" value="Menghilangkan nyeri" <?= in_array('Menghilangkan nyeri', $indikasi_data) ? 'checked' : '' ?>> Menghilangkan nyeri selama prosedur atau tindakan pembedahan</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek3" value="1" <?= in_array('3', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Tata Cara</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="tataCara[]" value="Obat disuntikkan" <?= in_array('Obat disuntikkan', $tata_cara_data) ? 'checked' : '' ?>> Obat disuntikkan ke dalam pembuluh darah, dihisap melalui paru-paru atau dengan dilakukan alat bantu napas</label>
                            <label><input type="checkbox" name="tataCara[]" value="Obat melalui jarum" <?= in_array('Obat melalui jarum', $tata_cara_data) ? 'checked' : '' ?>> Obat disuntikkan melalui jarum atau kateter yang ditempatkan ke dalam rongga belakang atau rongga didekatnya</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek4" value="1" <?= in_array('4', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Tujuan</td>
                    <td>
                        <div class="input-container">
                            <label for="tujuan" style="text-align: left">Memfasilitasi tindakan pembedahan, agar pasien dan dokter operator aman dan nyaman</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek5" value="1" <?= in_array('5', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Risiko Tindakan dan Komplikasi</td>
                    <td>
                        <div class="checkbox-grid">
                            <label><input type="checkbox" name="risiko[]" value="Nyeri Tenggorokan" <?= in_array('Nyeri Tenggorokan', $risiko_data) ? 'checked' : '' ?>> Nyeri Tenggorokan</label>
                            <label><input type="checkbox" name="risiko[]" value="Suara Serak" <?= in_array('Suara Serak', $risiko_data) ? 'checked' : '' ?>> Suara Serak</label>
                            <label><input type="checkbox" name="risiko[]" value="Mual, Muntah" <?= in_array('Mual, Muntah', $risiko_data) ? 'checked' : '' ?>> Mual, Muntah</label>
                            <label><input type="checkbox" name="risiko[]" value="Nyeri Otot" <?= in_array('Nyeri Otot', $risiko_data) ? 'checked' : '' ?>> Nyeri Otot</label>
                            <label><input type="checkbox" name="risiko[]" value="Trauma pada daerah mata" <?= in_array('Trauma pada daerah mata', $risiko_data) ? 'checked' : '' ?>> Trauma pada daerah mata</label>
                            <label><input type="checkbox" name="risiko[]" value="Infeksi" <?= in_array('Infeksi', $risiko_data) ? 'checked' : '' ?>> Infeksi</label>
                            <label><input type="checkbox" name="risiko[]" value="Trauma pada gusi" <?= in_array('Trauma pada gusi', $risiko_data) ? 'checked' : '' ?>> Trauma pada gusi</label>
                            <label><input type="checkbox" name="risiko[]" value="Pendarahan" <?= in_array('Pendarahan', $risiko_data) ? 'checked' : '' ?>> Pendarahan</label>
                            <label><input type="checkbox" name="risiko[]" value="Luka lecet" <?= in_array('Luka lecet', $risiko_data) ? 'checked' : '' ?>> Luka lecet pada daerah bibir, gusi dan lidah</label>
                            <label><input type="checkbox" name="risiko[]" value="Gigi Patah" <?= in_array('Gigi Patah', $risiko_data) ? 'checked' : '' ?>> Gigi Patah</label>
                            <label><input type="checkbox" name="risiko[]" value="Penurunan tekanan darah" <?= in_array('Penurunan tekanan darah', $risiko_data) ? 'checked' : '' ?>> Penurunan tekanan darah</label>
                            <label><input type="checkbox" name="risiko[]" value="Peningkatan tekanan darah" <?= in_array('Peningkatan tekanan darah', $risiko_data) ? 'checked' : '' ?>> Peningkatan tekanan darah</label>
                            <label><input type="checkbox" name="risiko[]" value="Reaksi Alergi" <?= in_array('Reaksi Alergi', $risiko_data) ? 'checked' : '' ?>> Reaksi Alergi</label>
                            <label><input type="checkbox" name="risiko[]" value="Penyempitan jalan nafas" <?= in_array('Penyempitan jalan nafas', $risiko_data) ? 'checked' : '' ?>> Penyempitan jalan nafas</label>
                            <label><input type="checkbox" name="risiko[]" value="Henti Jantung" <?= in_array('Henti Jantung', $risiko_data) ? 'checked' : '' ?>> Henti Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Stroke" <?= in_array('Stroke', $risiko_data) ? 'checked' : '' ?>> Stroke</label>
                            <label><input type="checkbox" name="risiko[]" value="Sakit Punggung" <?= in_array('Sakit Punggung', $risiko_data) ? 'checked' : '' ?>> Sakit Punggung</label>
                            <label><input type="checkbox" name="risiko[]" value="Kerusakan Pada Otak" <?= in_array('Kerusakan Pada Otak', $risiko_data) ? 'checked' : '' ?>> Kerusakan Pada Otak</label>
                            <label><input type="checkbox" name="risiko[]" value="Kerusakan Pada Syaraf" <?= in_array('Kerusakan Pada Syaraf', $risiko_data) ? 'checked' : '' ?>> Kerusakan Pada Syaraf</label>
                            <label><input type="checkbox" name="risiko[]" value="Kelumpuhan" <?= in_array('Kelumpuhan', $risiko_data) ? 'checked' : '' ?>> Kelumpuhan</label>
                            <label><input type="checkbox" name="risiko[]" value="Serangan Jantung" <?= in_array('Serangan Jantung', $risiko_data) ? 'checked' : '' ?>> Serangan Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Gangguan Irama Jantung" <?= in_array('Gangguan Irama Jantung', $risiko_data) ? 'checked' : '' ?>> Gangguan Irama Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Pembentukan bekuan darah" <?= in_array('Pembentukan bekuan darah', $risiko_data) ? 'checked' : '' ?>> Pembentukan bekuan darah</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek6" value="1" <?= in_array('6', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Status fisik</td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="statusFisik" value="ASA I" <?= $status_fisik_data == 'ASA I' ? 'checked' : '' ?>> ASA I</label>
                            <label><input type="radio" name="statusFisik" value="ASA II" <?= $status_fisik_data == 'ASA II' ? 'checked' : '' ?>> ASA II</label>
                            <label><input type="radio" name="statusFisik" value="ASA III" <?= $status_fisik_data == 'ASA III' ? 'checked' : '' ?>> ASA III</label>
                            <label><input type="radio" name="statusFisik" value="ASA IV" <?= $status_fisik_data == 'ASA IV' ? 'checked' : '' ?>> ASA IV</label>
                            <label><input type="radio" name="statusFisik" value="ASA V" <?= $status_fisik_data == 'ASA V' ? 'checked' : '' ?>> ASA V</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek7" value="1" <?= in_array('7', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Prognosis</td>
                    <td>
                        <div class="input-container">
                            <textarea id="prognosis" name="prognosis" rows="5" placeholder=" "><?= htmlspecialchars($consent['prognosis'] ?? '') ?></textarea>
                            <label for="prognosis" class="label-floating">Prognosis</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek8" value="1" <?= in_array('8', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>Alternatif dan Resiko</td>
                    <td>
                        <div class="input-container">
                            <textarea id="alternatif" name="alternatif" rows="5" placeholder=" "><?= htmlspecialchars($consent['alternatif_resiko'] ?? '') ?></textarea>
                            <label for="alternatif" class="label-floating">Alternatif dan Resiko</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek9" value="1" <?= in_array('9', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Lain-lain</td>
                    <td>
                        <div class="input-container">
                            <textarea id="lainLain" name="lainLain" rows="5" placeholder=" "><?= htmlspecialchars($consent['lain_lain'] ?? '') ?></textarea>
                            <label for="lainLain" class="label-floating">Lain-lain</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek10" value="1" <?= in_array('10', $checkbox_confirm_data) ? 'checked' : '' ?>></td>
                </tr>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= isset($consent['id']) ? 'Simpan Perubahan' : 'Simpan Informed Consent' ?></button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='/index.php?page=detail-pasien&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>'">Kembali</button>
            </div>
        </form>
    </div>
</div>

<!-- AUTOSAVE DISABLED: Fitur autosave dinonaktifkan untuk meningkatkan performa
<script src="/assets/js/autosave.js"></script>
<script>
// Initialize AutoSave - OPTIMIZED
document.addEventListener('DOMContentLoaded', function() {
    AutoSave.init('formInformedConsent', {
        debounce: 3000,  // Ubah dari 1000 ke 3000ms (save setiap 3 detik, lebih jarang)
        exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
        showNotification: false,  // Disable notifikasi untuk performa lebih baik
        clearOnSubmit: true
    });
    
    console.log('✅ AutoSave initialized (optimized: 3s debounce, no notification)');
});
</script>
-->

<?php include __DIR__ . '/../includes/footer.php'; ?>