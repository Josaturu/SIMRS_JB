<?php
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

// Koneksi database untuk mendapatkan data booking
$database = new Database();
$db = $database->getConnection();
$query = "SELECT * FROM booking_operasi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

$pasien = $booking;

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

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
        <h2>Informasi Identitas</h2>
        <form id="formInformedConsent" action="process/process-informed-consent-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="ruang" name="ruang" placeholder=" " required>
                            <label for="ruang" class="label-floating">Ruang Perawatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="dokter" name="dokter" placeholder=" " required>
                            <label for="dokter" class="label-floating">Dokter Pelaksana Tindakan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="pemberi" name="pemberi" placeholder=" " required>
                            <label for="pemberi" class="label-floating">Nama Pemberi Informasi</label>
                        </div>
                    </div>
                </div>
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="jabatan" name="jabatan" placeholder=" " required>
                            <label for="jabatan" class="label-floating">Jabatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="penerima" name="penerima" placeholder=" " required>
                            <label for="penerima" class="label-floating">Nama Penerima Informasi</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="hubungan" name="hubungan" placeholder=" " required>
                            <label for="hubungan" class="label-floating">Hubungan dengan Pasien</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Utama -->
            <h2>Informasi Tindakan Anestesi</h2>
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
                            <input type="text" id="tindakanOperasi" name="tindakanOperasi" placeholder=" " required>
                            <label for="tindakanOperasi" class="label-floating">Nama Tindakan Operasi</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek1" value="1"></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Tindakan yang akan dilakukan</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Umum"> Anestesi Umum</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Spinal"> Anestesi Spinal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Epidural"> Anestesi Epidural</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Anestesi Kaudal"> Anestesi Kaudal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Kombinasi Spinal-Epidural"> Kombinasi Spinal-Epidural</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Blok Saraf Perifer"> Blok Saraf Perifer</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Sedasi"> Sedasi</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek2" value="1"></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Indikasi Tindakan</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="indikasi[]" value="Menghilangkan kesadaran"> Menghilangkan kesadaran selama prosedur atau tindakan pembedahan</label>
                            <label><input type="checkbox" name="indikasi[]" value="Menghilangkan nyeri"> Menghilangkan nyeri selama prosedur atau tindakan pembedahan</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek3" value="1"></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Tata Cara</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="tataCara[]" value="Obat disuntikkan"> Obat disuntikkan ke dalam pembuluh darah, dihisap melalui paru-paru atau dengan dilakukan alat bantu napas</label>
                            <label><input type="checkbox" name="tataCara[]" value="Obat melalui jarum"> Obat disuntikkan melalui jarum atau kateter yang ditempatkan ke dalam rongga belakang atau rongga didekatnya</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek4" value="1"></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Tujuan</td>
                    <td>
                        <div class="input-container">
                            <label for="tujuan" style="text-align: left">Memfasilitasi tindakan pembedahan, agar pasien dan dokter operator aman dan nyaman</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek5" value="1"></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Risiko Tindakan dan Komplikasi</td>
                    <td>
                        <div class="checkbox-grid">
                            <label><input type="checkbox" name="risiko[]" value="Nyeri Tenggorokan"> Nyeri Tenggorokan</label>
                            <label><input type="checkbox" name="risiko[]" value="Suara Serak"> Suara Serak</label>
                            <label><input type="checkbox" name="risiko[]" value="Mual, Muntah"> Mual, Muntah</label>
                            <label><input type="checkbox" name="risiko[]" value="Nyeri Otot"> Nyeri Otot</label>
                            <label><input type="checkbox" name="risiko[]" value="Trauma pada daerah mata"> Trauma pada daerah mata</label>
                            <label><input type="checkbox" name="risiko[]" value="Infeksi"> Infeksi</label>
                            <label><input type="checkbox" name="risiko[]" value="Trauma pada gusi"> Trauma pada gusi</label>
                            <label><input type="checkbox" name="risiko[]" value="Pendarahan"> Pendarahan</label>
                            <label><input type="checkbox" name="risiko[]" value="Luka lecet"> Luka lecet pada daerah bibir, gusi dan lidah</label>
                            <label><input type="checkbox" name="risiko[]" value="Gigi Patah"> Gigi Patah</label>
                            <label><input type="checkbox" name="risiko[]" value="Stroke"> Stroke</label>
                            <label><input type="checkbox" name="risiko[]" value="Penurunan tekanan darah"> Penurunan tekanan darah</label>
                            <label><input type="checkbox" name="risiko[]" value="Peningkatan tekanan darah"> Peningkatan tekanan darah</label>
                            <label><input type="checkbox" name="risiko[]" value="Reaksi Alergi"> Reaksi Alergi</label>
                            <label><input type="checkbox" name="risiko[]" value="Penyempitan jalan nafas"> Penyempitan jalan nafas</label>
                            <label><input type="checkbox" name="risiko[]" value="Henti Jantung"> Henti Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Sakit Punggung"> Sakit Punggung</label>
                            <label><input type="checkbox" name="risiko[]" value="Kerusakan Pada Otak"> Kerusakan Pada Otak</label>
                            <label><input type="checkbox" name="risiko[]" value="Kerusakan Pada Syaraf"> Kerusakan Pada Syaraf</label>
                            <label><input type="checkbox" name="risiko[]" value="Kelumpuhan"> Kelumpuhan</label>
                            <label><input type="checkbox" name="risiko[]" value="Serangan Jantung"> Serangan Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Gangguan Irama Jantung"> Gangguan Irama Jantung</label>
                            <label><input type="checkbox" name="risiko[]" value="Pembentukan bekuan darah"> Pembentukan bekuan darah</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek6" value="1"></td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Status fisik</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="statusFisik[]" value="ASA I"> ASA I</label>
                            <label><input type="checkbox" name="statusFisik[]" value="ASA II"> ASA II</label>
                            <label><input type="checkbox" name="statusFisik[]" value="ASA III"> ASA III</label>
                            <label><input type="checkbox" name="statusFisik[]" value="ASA IV"> ASA IV</label>
                            <label><input type="checkbox" name="statusFisik[]" value="ASA V"> ASA V</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek7" value="1"></td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Prognosis</td>
                    <td>
                        <div class="input-container">
                            <textarea id="prognosis" name="prognosis" rows="5" placeholder=" "></textarea>
                            <label for="prognosis" class="label-floating">Prognosis</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek8" value="1"></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>Alternatif dan Resiko</td>
                    <td>
                        <div class="input-container">
                            <textarea id="alternatif" name="alternatif" rows="5" placeholder=" "></textarea>
                            <label for="alternatif" class="label-floating">Alternatif dan Resiko</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek9" value="1"></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Lain-lain</td>
                    <td>
                        <div class="input-container">
                            <textarea id="lainLain" name="lainLain" rows="5" placeholder=" "></textarea>
                            <label for="lainLain" class="label-floating">Lain-lain</label>
                        </div>
                    </td>
                    <td><input type="checkbox" name="cek10" value="1"></td>
                </tr>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Informed Consent</button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>