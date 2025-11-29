<?php
session_start();

$page_title = "Informed Consent Tindakan Anestesi";
$document_code = "RMC 4a Rev-01";

// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

// Jika tidak ada parameter, redirect ke daftar pasien (seragam ke halaman daftar pasien)
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header('Location: /index.php?page=daftar-pasien');
    exit;
}

// Koneksi database untuk mendapatkan data booking dan pasien
$database = new Database();
$db = $database->getConnection();
$query = "SELECT bo.*, 
                 p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin,
                 r.nama_ruang
          FROM booking_operasi bo
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
          LEFT JOIN tbl_ruang r ON bo.ruang_rawat COLLATE utf8mb4_unicode_ci = r.id_ruang
          WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
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

// Ambil semua ruang dari tbl_ruang untuk dropdown
$query_ruang = "SELECT id_ruang, nama_ruang FROM tbl_ruang ORDER BY nama_ruang ASC";
$stmt_ruang = $db->prepare($query_ruang);
$stmt_ruang->execute();
$ruang_list = $stmt_ruang->fetchAll(PDO::FETCH_ASSOC);

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

include __DIR__ . '/../includes/assets.php';
?>

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
        <h2 style="margin-bottom: 20px;">Pemberian Informasi</h2>
        <form id="formInformedConsent" action="/process/process-informed-consent-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <input type="hidden" name="id" value="<?php echo isset($consent['id']) ? htmlspecialchars($consent['id']) : ''; ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <?php
                        $current_ruang = $consent['ruang'] ?? $booking['nama_ruang'] ?? '';
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
                        <div class="input-container">
                            <?php
                            $current_dokter = $consent['dokter_pelaksana'] ?? '';
                            ?>
                            <select id="dokter" name="dokter" class="form-select" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                                <option value="">-- Pilih Dokter Pelaksana --</option>
                                <?php
                                foreach ($dokter_list as $dokter) {
                                    $selected = ($current_dokter == $dokter['nama_dokter']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                                }
                                ?>
                            </select>
                            <label for="dokter" class="label-floating" style="top: -8px; font-size: 12px; background: white; padding: 0 5px;">Dokter Pelaksana Tindakan</label>
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Pilih dokter yang akan melaksanakan tindakan anestesi
                        </small>
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
            $jenis_anestesi_data = isset($consent['jenis_anestesi']) ? explode('|', $consent['jenis_anestesi']) : [];
            $indikasi_data = isset($consent['indikasi']) ? explode('|', $consent['indikasi']) : [];
            $tata_cara_data = isset($consent['tata_cara']) ? explode('|', $consent['tata_cara']) : [];
            $risiko_data = isset($consent['risiko']) ? explode('|', $consent['risiko']) : [];
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
                <!-- Tombol aksi utama dipindahkan ke Speed Dial -->
            </div>
        </form>
    </div>
</div>

<!-- Speed Dial: Simpan, Cetak PDF, Kembali -->
<div data-dial-init class="fixed right-6 bottom-6 group">
    <div id="speed-dial-menu-informed-consent" class="flex flex-col w-32 justify-end hidden mb-4 space-y-2 bg-neutral-primary-medium border border-default-medium rounded-base shadow-xs">
        <ul class="p-2 text-sm text-body font-medium">
            <li>
                <a href="#" onclick="document.getElementById('formInformedConsent').submit(); return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
                    <span class="text-sm font-medium">Simpan</span>
                </a>
            </li>
            <li>
                <a href="/process/pdf/pdf-informed-consent.php?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" target="_blank" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
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
    <button type="button" data-dial-toggle="speed-dial-menu-informed-consent" aria-controls="speed-dial-menu-informed-consent" aria-expanded="false" class="flex items-center justify-center ml-auto text-white bg-brand rounded-base w-14 h-14 hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium focus:outline-none">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
        <span class="sr-only">Open actions menu</span>
    </button>
</div>

<script>
// Toggle function untuk dropdown dengan input manual
function toggleInput(fieldName) {
    const select = document.getElementById(fieldName + '_select');
    const inputContainer = document.getElementById(fieldName + '_input_container');
    const input = document.getElementById(fieldName + '_input');
    const hiddenField = document.getElementById(fieldName);
    
    if (select.value === 'lainnya') {
        inputContainer.style.display = 'block';
        input.focus();
        hiddenField.value = input.value;
    } else {
        inputContainer.style.display = 'none';
        input.value = '';
        hiddenField.value = select.value;
    }
}

// Update hidden field saat input manual berubah
document.addEventListener('DOMContentLoaded', function() {
    const ruangInput = document.getElementById('ruang_input');
    if (ruangInput) {
        ruangInput.addEventListener('input', function() {
            document.getElementById('ruang').value = this.value;
        });
    }
    
    // Initialize toggle untuk dropdown ruang
    const ruangSelect = document.getElementById('ruang_select');
    if (ruangSelect && ruangSelect.value) {
        toggleInput('ruang');
    }
});
</script>

<!-- Nullable Field Warning Script -->
<script src="/assets/js/nullable-field-warning.js"></script>

