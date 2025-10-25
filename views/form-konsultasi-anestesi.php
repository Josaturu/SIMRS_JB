<?php
session_start();

$page_title = "Konsultasi Anestesi";
$document_code = "RMOK 3A";

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
$query = "SELECT bo.*, p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.alamat, p.jenis_kelamin, 
                 p.tempat_lahir, p.no_hp, p.gol_darah
          FROM booking_operasi bo 
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien 
          WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

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
} catch (PDOException $e) {
    // Table mungkin belum dibuat, biarkan $konsul kosong
    error_log("Error loading konsultasi: " . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
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
</style>

<div class="container">
    <div class="title">
        <div style="color: #004d80;">KONSULTASI ANESTESI</div>
        <div>RMOK 3A</div>
    </div>
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
        <form id="formKonsultasiAnestesi" action="process/process-konsultasi-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <input type="hidden" name="id" value="<?php echo isset($konsul['id']) ? htmlspecialchars($konsul['id']) : ''; ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="ruang" name="ruang" placeholder=" " value="<?= htmlspecialchars($konsul['ruang_perawatan'] ?? '') ?>" required>
                            <label for="ruang" class="label-floating">Ruang Perawatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="dokter" name="dokter" placeholder=" " value="<?= htmlspecialchars($konsul['dokter_merawat'] ?? '') ?>" required>
                            <label for="dokter" class="label-floating">Dokter Merawat</label>
                        </div>
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
                            <input type="date" id="tanggalKonsul" name="tanggalKonsul" placeholder=" " value="<?= htmlspecialchars($konsul['tanggal_konsul'] ?? '') ?>" required>
                            <label for="tanggalKonsul" class="label-floating">Tanggal</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="jam" name="jam" placeholder=" " value="<?= htmlspecialchars($konsul['jam_konsul'] ?? '') ?>" required>
                            <label for="jam" class="label-floating">Jam</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="tinggiBadan" name="tinggiBadan" placeholder=" " value="<?= htmlspecialchars($konsul['tinggi_badan'] ?? '') ?>" step="0.1" required>
                            <label for="tinggiBadan" class="label-floating">Tinggi Badan (cm)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="beratBadan" name="beratBadan" placeholder=" " value="<?= htmlspecialchars($konsul['berat_badan'] ?? '') ?>" step="0.1" required>
                            <label for="beratBadan" class="label-floating">Berat Badan (kg)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="diagnosaPraOperasi" name="diagnosaPraOperasi" placeholder=" " required><?= htmlspecialchars($konsul['diagnosa_pra_operasi'] ?? '') ?></textarea>
                        <label for="diagnosaPraOperasi" class="label-floating">Diagnosa Pra Operasi</label>
                    </div>
                    <div class="radio-group">
                        <?php
                        // Cek jenis diagnosa dari konsultasi atau default Elektif
                        $jenis_diagnosa = $konsul['jenis_diagnosa'] ?? 'Elektif';
                        ?>
                        <label><input type="radio" name="jenisDiagnosa" value="Cito" <?= $jenis_diagnosa == 'Cito' ? 'checked' : '' ?> required> Cito</label>
                        <label><input type="radio" name="jenisDiagnosa" value="Elektif" <?= $jenis_diagnosa == 'Elektif' ? 'checked' : '' ?>> Elektif</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="rencanaTindakanOperasi" name="rencanaTindakanOperasi" placeholder=" " required><?= htmlspecialchars($konsul['rencana_tindakan_operasi'] ?? '') ?></textarea>
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
                        <input type="datetime-local" id="tanggalDibuat" name="tanggalDibuat" placeholder=" " value="<?= htmlspecialchars($konsul['tanggal_dibuat'] ?? '') ?>" required>
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
                            <input type="time" id="jamVisit" name="jamVisit" placeholder=" " value="<?= htmlspecialchars($konsul['jam_visit'] ?? '') ?>" required>
                            <label for="jamVisit" class="label-floating">Jam Visit</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Menikah</label>
                        <div class="radio-group">
                            <label><input type="radio" name="menikah" value="Ya" <?= !isset($konsul['menikah']) || $konsul['menikah'] == 'Ya' ? 'checked' : '' ?> required> Ya</label>
                            <label><input type="radio" name="menikah" value="Tidak" <?= isset($konsul['menikah']) && $konsul['menikah'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Jenis Kelamin</label>
                        <div class="radio-group">
                            <?php 
                            // Prioritas: 1. Data konsultasi, 2. Data pasien, 3. Default
                            $jk = $konsul['jenis_kelamin'] ?? $pasien['jenis_kelamin'] ?? 'Laki-laki';
                            error_log("DEBUG FORM - Final jenis_kelamin: $jk");
                            ?>
                            <label><input type="radio" name="jenis_kelamin" value="Laki-laki" <?= $jk == 'Laki-laki' || $jk == 'L' ? 'checked' : '' ?> required> Laki-laki</label>
                            <label><input type="radio" name="jenis_kelamin" value="Perempuan" <?= $jk == 'Perempuan' || $jk == 'Wanita' || $jk == 'P' ? 'checked' : '' ?>> Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Kebiasaan Merokok</label>
                        <div class="radio-group">
                            <label><input type="radio" name="merokok" value="Ya" <?= isset($konsul['merokok']) && $konsul['merokok'] == 'Ya' ? 'checked' : '' ?> required> Ya</label>
                            <label><input type="radio" name="merokok" value="Sebanyak" <?= isset($konsul['merokok']) && $konsul['merokok'] == 'Sebanyak' ? 'checked' : '' ?>> Sebanyak</label>
                            <label><input type="radio" name="merokok" value="Tidak" <?= !isset($konsul['merokok']) || $konsul['merokok'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Kebiasaan Alkohol</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alkohol" value="Ya" <?= isset($konsul['alkohol']) && $konsul['alkohol'] == 'Ya' ? 'checked' : '' ?> required> Ya</label>
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
                        <div class="form-item" id="pengobatanDetail" style="display: <?= isset($konsul['has_pengobatan']) && $konsul['has_pengobatan'] == 'Ya' ? 'block' : 'none' ?>;">
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
                        <div class="form-item" id="alergiObatDetail" style="display: <?= isset($konsul['has_alergi_obat']) && $konsul['has_alergi_obat'] == 'Ya' ? 'block' : 'none' ?>;">
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

                <div class="form-row">
                    <label>Jalan Nafas</label>
                    <div class="radio-group">
                        <label><input type="radio" name="jalan_nafas" value="Normal" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Normal' ? 'checked' : '' ?>> Normal</label>
                        <label><input type="radio" name="jalan_nafas" value="Buka mulut" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Buka mulut' ? 'checked' : '' ?>> Buka mulut > 2 jari</label>
                        <label><input type="radio" name="jalan_nafas" value="Jarak Thyrimental" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Jarak Thyrimental' ? 'checked' : '' ?>> Jarak Thyrimental > 3 jari</label>
                        <label><input type="radio" name="jalan_nafas" value="Mallampati" <?= isset($konsul['jalan_nafas']) && $konsul['jalan_nafas'] == 'Mallampati' ? 'checked' : '' ?>> Mallampati I / II / III / IV</label>
                    </div>
                    <div class="radio-group">
                        <label><input type="radio" name="gerakan_leher" value="Maksimal" <?= isset($konsul['gerakan_leher']) && $konsul['gerakan_leher'] == 'Maksimal' ? 'checked' : '' ?>> Gerakan leher Maksimal</label>
                        <label><input type="radio" name="gerakan_leher" value="Abnormal" <?= isset($konsul['gerakan_leher']) && $konsul['gerakan_leher'] == 'Abnormal' ? 'checked' : '' ?>> Abnormal</label>
                        <div class="input-container">
                            <input type="text" id="gerakanLeherAbnormal" name="gerakanLeherAbnormal" placeholder=" " value="<?= htmlspecialchars($konsul['gerakan_leher_keterangan'] ?? '') ?>">
                            <label for="gerakanLeherAbnormal" class="label-floating">Keterangan</label>
                        </div>
                    </div>
                </div>

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
                    <div><strong>C. Lain - lain</strong></div>
                    <div class="input-container">
                        <input type="text" id="diagnosisLain" name="diagnosisLain" placeholder=" " value="<?= htmlspecialchars($konsul['rekomendasi_anestesi'] ?? '') ?>">
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

                <div class="form-row">
                    <label>SARAN</label>
                    <div class="input-container">
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
                <button type="submit" class="btn btn-primary">Simpan Konsultasi</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?page=detail-pasien&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>'">Kembali</button>            </div>
        </form>
    </div>
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

// JavaScript untuk toggle show/hide field gerakan leher abnormal
function toggleGerakanLeher() {
    var gerakanLeher = document.querySelector('input[name="gerakan_leher"]:checked');
    var gerakanLeherDetail = document.getElementById('gerakanLeherDetail');
    
    if (gerakanLeher && gerakanLeher.value === 'Abnormal') {
        gerakanLeherDetail.style.display = 'block';
    } else {
        gerakanLeherDetail.style.display = 'none';
        // Clear value jika bukan "Abnormal"
        document.getElementById('gerakanLeherAbnormal').value = '';
    }
}

// Run on page load untuk set initial state
document.addEventListener('DOMContentLoaded', function() {
    togglePengobatan();
    toggleAlergiObat();
    toggleGerakanLeher();
    
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