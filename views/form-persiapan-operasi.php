<?php
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

    <!-- Info Operasi -->
    <div class="card">
        <h2>Data Masuk dan Kondisi Pasien</h2>
        <div class="form-grid">
            <!-- Kolom Kiri -->
            <div class="form-column">
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="date" id="tglOperasi" name="tglOperasi" placeholder=" " required>
                        <label for="tglOperasi" class="label-floating">Tanggal Operasi</label>
                    </div>
                </div>
                
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="text" id="macamOperasi" name="macamOperasi" placeholder=" " required>
                        <label for="macamOperasi" class="label-floating">Macam Operasi</label>
                    </div>
                </div>
                
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="number" id="tinggiBadan" name="tinggiBadan" placeholder=" " step="0.1">
                        <label for="tinggiBadan" class="label-floating">Tinggi Badan (cm)</label>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="form-column">
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="text" id="riwayatAlergi" name="riwayatAlergi" placeholder=" ">
                        <label for="riwayatAlergi" class="label-floating">Riwayat Alergi</label>
                    </div>
                </div>
                <div class="keterangan-pasien">
                    <div class="input-container">
                        <input type="number" id="beratBadan" name="beratBadan" placeholder=" " step="0.1">
                        <label for="beratBadan" class="label-floating">Berat Badan (kg)</label>
                    </div>
                </div>
                <div class="keterangan-pasien">
                    <label><strong>Gol. Darah:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="GolDarah" value="A" />A</label>
                        <label><input type="radio" name="GolDarah" value="AB" />AB</label>
                        <label><input type="radio" name="GolDarah" value="O" />O</label>
                        <label><input type="radio" name="GolDarah" value="B" />B</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklist -->
    <div class="card">
        <h2>Checklist Persiapan Operasi</h2>
        <form id="formPersiapanOperasi" action="process/submit-persiapan-operasi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <table>
                <tr>
                    <th></th>
                    <th>R. RAWAT</th>
                    <th>UBS</th>
                    <th>Keterangan</th>
                </tr>

                <!-- Administrasi -->
                <tr class="section-title"><td colspan="4" style="font-weight: bold; background-color: #4285f4; color: #fff;">1. Persiapan Administrasi</td></tr>
                
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
                ?>
                <tr>
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td><input type="text" name="ket<?php echo $i; ?>"></td>
                </tr>
                <?php endfor; ?>

                <!-- Fisik -->
                <tr class="section-title"><td colspan="4" style="text-align: left; font-weight: bold; background-color: #4285f4; color: #fff;">2. Persiapan Fisik</td></tr>
                
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
                ?>
                <tr>
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <?php if($i == 12): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Sejak:">
                        <?php elseif($i == 14): ?>
                            <input style="width: 45%;" type="number" name="ket<?php echo $i; ?>" placeholder="No."> / 
                            <input style="width: 52%;" type="text" name="ket<?php echo $i; ?>_macam" placeholder="Macam">
                        <?php elseif($i >= 20 && $i <= 22): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="Kantong:">
                        <?php elseif($i == 24): ?>
                            <input type="time" name="ket<?php echo $i; ?>_waktu">
                            <label for="ket24_waktu"> WIB</label>
                        <?php else: ?>
                            <input type="text" name="ket<?php echo $i; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>

                <!-- Khusus -->
                <tr class="section-title"><td colspan="4" style="text-align: left; font-weight: bold; background-color: #4285f4; color: #fff;">3. Persiapan Khusus</td></tr>
                
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
                ?>
                <tr>
                    <td style="text-align: left;"><?php echo $item; ?></td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="rawat<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="ya"> Ya</label>
                            <label><input type="radio" name="ubs<?php echo $i; ?>" value="tidak"> Tidak</label>
                        </div>
                    </td>
                    <td>
                        <?php if($i == 30): ?>
                            <input type="text" name="ket<?php echo $i; ?>" placeholder="IV Catch No:">
                        <?php elseif($i == 36): ?>
                            <div class="radio-group">
                                <label><input type="radio" name="ket<?php echo $i; ?>" value="positif"> Positif</label>
                                <label><input type="radio" name="ket<?php echo $i; ?>" value="negatif"> Negatif</label>
                            </div>
                        <?php else: ?>
                            <input type="text" name="ket<?php echo $i; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Checklist</button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
            </div>
        </form>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
