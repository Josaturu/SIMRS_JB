<?php
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

// Koneksi database untuk mendapatkan data booking
$database = new Database();
$db = $database->getConnection();
$query = "SELECT * FROM booking_operasi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

$pasien = $booking ? $booking : [];

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
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
        <form id="formKeselamatanOperasi" action="process/submit-keselamatan-operasi.php" method="POST">
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
                            <input type="text" id="namaPasien" name="namaPasien" placeholder=" " required>
                            <label for="namaPasien" class="label-floating">Nama Pasien</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="noRekamMedis" name="noRekamMedis" placeholder=" " required>
                            <label for="noRekamMedis" class="label-floating">No. Rekam Medis</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="tglLahir" name="tglLahir" placeholder=" ">
                            <label for="tglLahir" class="label-floating">Tanggal Lahir / Umur</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="alamat" name="alamat" placeholder=" ">
                            <label for="alamat" class="label-floating">Alamat</label>
                        </div>
                    </div>
                </div>

                <!-- Kolom kanan: Info Dokter/Operasi -->
                <div class="form-column">
                    <h3><i class="fas fa-stethoscope"></i> Informasi Operasi</h3>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="operator" name="operator" placeholder=" " required>
                            <label for="operator" class="label-floating">Operator / dr. Bedah</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="operasi" name="operasi" placeholder=" " required>
                            <label for="operasi" class="label-floating">Operasi / Tindakan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="date" id="tglTindakan" name="tglTindakan" placeholder=" " required>
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
                        <input type="time" class="input-time" name="signin_time" required>
                    </div>
                    <ol>
                        <li>Pasien sudah konfirmasikan : 
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_1a" value="identitas"> Identitas pasien & gelang pasien</label>
                                <label><input type="checkbox" name="signin_1b" value="lokasi"> Lokasi operasi</label>
                                <label><input type="checkbox" name="signin_1c" value="prosedur"> Prosedur</label>
                                <label><input type="checkbox" name="signin_1d" value="surat_izin"> Surat izin operasi</label>
                            </div>
                        </li>
                        <li>Lokasi operasi sudah diberi tanda?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_2a" value="ya"> Ya</label>
                                <label><input type="checkbox" name="signin_2b" value="tidak_dilakukan"> Tidak dilakukan</label>
                            </div>
                        </li>
                        <li>Mesin anestesi dan obat-obatan sudah dicek lengkap :
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_3" value="ya"> Ya</label>
                            </div>
                        </li>
                        <li>Pulse Oximeter terpasang dan berfungsi :
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_4" value="ya"> Ya</label>
                            </div>
                        </li>
                        <li>Apakah pasien punya riwayat alergi?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_5a" value="tidak"> Tidak</label>
                                <label><input type="checkbox" name="signin_5b" value="ya"> Ya</label>
                            </div>
                        </li>
                        <li>Kemungkinan kesulitan jalan nafas atau risiko aspirasi?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_6a" value="tidak"> Tidak</label>
                                <label><input type="checkbox" name="signin_6b" value="peralatan_tersedia"> Peralatan dan sistem telah tersedia</label>
                            </div>
                        </li>
                        <li>Resiko kehilangan darah > 500ml (7ml/kgBB pada anak)?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signin_7a" value="tidak"> Tidak</label>
                                <label><input type="checkbox" name="signin_7b" value="iv_terapi"> IV/sentral, dan terapi cairan telah direncanakan</label>
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
                                <select name="dokter_anestesi_signin">
                                    <option value="">Dokter Anestesi</option>
                                    <option value="dokter1">Dokter 1</option>
                                    <option value="dokter2">Dokter 2</option>
                                </select>
                            </td>
                            <td>
                                <select name="perawat_anestesi_signin">
                                    <option value="">Pilih Perawat</option>
                                    <option value="perawat1">Perawat 1</option>
                                    <option value="perawat2">Perawat 2</option>
                                </select>
                            </td>
                            <td>
                                <select name="perawat_sirkuler_signin">
                                    <option value="">Pilih Perawat</option>
                                    <option value="perawat1">Perawat 1</option>
                                    <option value="perawat2">Perawat 2</option>
                                </select>
                            </td>
                        </tr>
                    </table> 
                </div>

                <!-- Time Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Time Out</h3>
                        <input type="time" class="input-time" name="timeout_time" required>
                    </div>
                    <ol>
                        <li>Konfirmasi seluruh anggota tim (nama dan peran masing-masing)
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_1" value="ya"> Ya</label>
                            </div>
                        </li>
                        <li>Konfirmasi secara verbal
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_2a" value="nama_pasien"> Nama Pasien</label>
                                <label><input type="checkbox" name="timeout_2b" value="prosedur"> Prosedur</label>
                                <label><input type="checkbox" name="timeout_2c" value="lokasi_insisi"> Lokasi dimana insisi akan dibuat</label>
                            </div>
                        </li>
                        <li>Antibiotik profilaksis dalam 60 menit sebelumnya
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_3" value="ya"> Ya</label>
                            </div>
                        </li>
                        <li>Antisipasi kejadian kritis: 
                            <div style="margin-top: 10px;" class="input-container">
                                <textarea name="catatan_dokter_bedah" placeholder=""></textarea>
                                <label class="label-floating">Catatan dokter bedah</label>
                            </div>
                            <div class="input-container">
                                <textarea name="catatan_dokter_anestesi" placeholder=""></textarea>
                                <label class="label-floating">Catatan dokter anestesi</label>
                            </div>
                            <div class="input-container">
                                <textarea name="catatan_perawat" placeholder=""></textarea>
                                <label class="label-floating">Catatan perawat</label>
                            </div>
                        </li>
                        <li>Foto Rontgen / CT-Scan / MRI yang diperlukan telah ditayangkan?
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="timeout_5a" value="ya"> Ya</label>
                                <label><input type="checkbox" name="timeout_5b" value="tidak"> Tidak</label>
                            </div>
                        </li>
                    </ol>
                    <table>
                        <tr>
                            <th>Perawat Sirkuler</th>
                        </tr>
                        <tr>
                            <td>
                                <select name="perawat_sirkuler_timeout">
                                    <option value="">Pilih Perawat</option>
                                    <option value="perawat1">Perawat 1</option>
                                    <option value="perawat2">Perawat 2</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Sign Out -->
                <div class="checklist-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sign-out-alt"></i> Sign Out</h3>
                        <input type="time" class="input-time" name="signout_time" required>
                    </div>
                    <ol>
                        <li>Konfirmasi perawat secara verbal:
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signout_1a" value="nama_prosedur"> Nama prosedur tindakan</label>
                                <label><input type="checkbox" name="signout_1b" value="instrumen_lengkap"> Instrumen, kasa & jarum lengkap</label>
                                <label><input type="checkbox" name="signout_1c" value="spesimen_label"> Spesimen diberi label </label>
                                <label><input type="checkbox" name="signout_1d" value="tidak_masalah_alat"> Tidak ada masalah alat operasi</label>
                            </div>
                        </li>
                        <li>Operator dokter bedah, dokter anestesi dan perawat membahas masalah utama penyembuhan & manajemen pasien selanjutnya
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="signout_2" value="ya"> Ya</label>
                            </div>
                        </li>
                    </ol>
                    <div class="signature-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Tasikmalaya,</span>
                        <input type="text" class="input-date" name="tanggal_keluar" placeholder="Tanggal" required>
                        <input type="text" class="input-year" name="tahun_keluar" placeholder="Tahun" required>
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
                                <select name="perawat_sirkuler_signout">
                                    <option value="">Pilih Perawat</option>
                                    <option value="perawat1">Perawat 1</option>
                                    <option value="perawat2">Perawat 2</option>
                                </select>
                            </td>
                            <td>
                                <select name="dokter_anestesi_signout">
                                    <option value="">Dokter Anestesi</option>
                                    <option value="dokter1">Dokter 1</option>
                                    <option value="dokter2">Dokter 2</option>
                                </select>
                            </td>
                            <td>
                                <select name="operator_signout">
                                    <option value="">Operator / Dokter Bedah</option>
                                    <option value="dokter1">Dokter 1</option>
                                    <option value="dokter2">Dokter 2</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Checklist</button>
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                </div>
            </div>
        </form>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

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
    });
</script>

