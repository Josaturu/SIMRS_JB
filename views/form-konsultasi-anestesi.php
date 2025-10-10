<?php
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
        <div style="color: #004d80;">KONSULTASI ANESTESI</div>
        <div>RMOK 3A</div>
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
        <h2>Informasi Pasien</h2>
        <form id="formKonsultasiAnestesi" action="process/process-konsultasi-anestesi.php" method="POST">
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
                            <input type="date" id="tanggalKonsul" name="tanggalKonsul" placeholder=" " required>
                            <label for="tanggalKonsul" class="label-floating">Tanggal</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="jam" name="jam" placeholder=" " required>
                            <label for="jam" class="label-floating">Jam</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="tinggiBadan" name="tinggiBadan" placeholder=" " step="0.1" required>
                            <label for="tinggiBadan" class="label-floating">Tinggi Badan (cm)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="number" id="beratBadan" name="beratBadan" placeholder=" " step="0.1" required>
                            <label for="beratBadan" class="label-floating">Berat Badan (kg)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="diagnosaPraOperasi" name="diagnosaPraOperasi" placeholder=" " required></textarea>
                        <label for="diagnosaPraOperasi" class="label-floating">Diagnosa Pra Operasi</label>
                    </div>
                    <div class="radio-group">
                        <label><input type="radio" name="jenisDiagnosa" value="Cita" required> Cita</label>
                        <label><input type="radio" name="jenisDiagnosa" value="Efektif"> Efektif</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="rencanaTindakanOperasi" name="rencanaTindakanOperasi" placeholder=" " required></textarea>
                        <label for="rencanaTindakanOperasi" class="label-floating">Rencana Tindakan Operasi</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="kondisiKhusus" name="kondisiKhusus" placeholder=" "></textarea>
                        <label for="kondisiKhusus" class="label-floating">Kondisi Khusus atau penyulit yang mungkin terjadi pada pasien</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="datetime-local" id="tanggalDibuat" name="tanggalDibuat" placeholder=" " required>
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
                            <input type="time" id="jamVisit" name="jamVisit" placeholder=" " required>
                            <label for="jamVisit" class="label-floating">Jam Visit</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Menikah</label>
                        <div class="radio-group">
                            <label><input type="radio" name="menikah" value="Ya" required> Ya</label>
                            <label><input type="radio" name="menikah" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Jenis Kelamin</label>
                        <div class="radio-group">
                            <label><input type="radio" name="jenis_kelamin" value="Laki-laki" required> Laki-laki</label>
                            <label><input type="radio" name="jenis_kelamin" value="Wanita"> Wanita</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Kebiasaan Merokok</label>
                        <div class="radio-group">
                            <label><input type="radio" name="merokok" value="Ya" required> Ya</label>
                            <label><input type="radio" name="merokok" value="Sebanyak"> Sebanyak</label>
                            <label><input type="radio" name="merokok" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Kebiasaan Alkohol</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alkohol" value="Ya" required> Ya</label>
                            <label><input type="radio" name="alkohol" value="Sebanyak"> Sebanyak</label>
                            <label><input type="radio" name="alkohol" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="pengobatan" name="pengobatan" placeholder=" ">
                        <label for="pengobatan" class="label-floating">Pengobatan: Sebutkan dosis atau jumlah pil per hari</label>
                    </div>
                </div>

                <div class="form-row">
                    <label>Alergi Obat:</label>
                    <div class="form-row-grid">
                        <div class="form-item">
                            <div class="input-container">
                                <input type="text" id="daftarAlergiObat" name="daftarAlergiObat" placeholder=" ">
                                <label for="daftarAlergiObat" class="label-floating">Ya, Daftar Obat & Tipe Reaksi</label>
                            </div>
                        </div>
                        <div class="form-item">
                            <div class="radio-group">
                                <label><input type="radio" name="has_alergi_obat" value="Tidak"> Tidak</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Alergi Makanan</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alergi_makanan" value="Ya"> Ya</label>
                            <label><input type="radio" name="alergi_makanan" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Alergi Lateks</label>
                        <div class="radio-group">
                            <label><input type="radio" name="alergi_lateks" value="Ya"> Ya</label>
                            <label><input type="radio" name="alergi_lateks" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="tidakAlergi" name="tidakAlergi" placeholder=" ">
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
                                <input type="text" id="komunikasiLainnya" name="komunikasiLainnya" placeholder=" ">
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
                    ?>
                    <div class="checkbox-item">
                        <label><input type="radio" name="<?php echo $key; ?>" value="Ya"> Ya</label>
                        <label><input type="radio" name="<?php echo $key; ?>" value="Tidak"> Tidak</label>
                        <span><?php echo $value; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="subsection-title">Jelaskan penyakit yang dijawab "Ya":</div>
                
                <div class="form-row">
                    <div class="input-container">
                        <textarea id="penjelasanPenyakit" name="penjelasanPenyakit" placeholder=" " rows="3"></textarea>
                        <label for="penjelasanPenyakit" class="label-floating">Penjelasan Penyakit</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <label>Gigi Palsu</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gigi_palsu" value="Ya"> Ya</label>
                            <label><input type="radio" name="gigi_palsu" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="makanTerakhir" name="makanTerakhir" placeholder=" ">
                            <label for="makanTerakhir" class="label-floating">Makan / Minum terakhir jam</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <textarea id="riwayatOperasi" name="riwayatOperasi" placeholder=" " rows="3"></textarea>
                            <label for="riwayatOperasi" class="label-floating">Riwayat Operasi, tahun dan jenis operasi</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <textarea id="jenisAnestesi" name="jenisAnestesi" placeholder=" " rows="3"></textarea>
                        <label for="jenisAnestesi" class="label-floating">Jenis Anestesi yang digunakan dan sebutkan komplikasi / reaksi yang dialami</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="terakhirPeriksa" name="terakhirPeriksa" placeholder=" ">
                            <label for="terakhirPeriksa" class="label-floating">Tanggal terakhir kali periksa kesehatan ke dokter</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="tempatPeriksaTerakhir" name="tempatPeriksaTerakhir" placeholder=" ">
                            <label for="tempatPeriksaTerakhir" class="label-floating">Dimana</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="penyakitGangguan" name="penyakitGangguan" placeholder=" ">
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
                            <input type="text" id="jumlahKehamilan" name="jumlahKehamilan" placeholder=" ">
                            <label for="jumlahKehamilan" class="label-floating">Jumlah Kehamilan</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="jumlahAnak" name="jumlahAnak" placeholder=" ">
                            <label for="jumlahAnak" class="label-floating">Jumlah Anak</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <label>Menyusui</label>
                        <div class="radio-group">
                            <label><input type="radio" name="menyusui" value="Ya"> Ya</label>
                            <label><input type="radio" name="menyusui" value="Tidak"> Tidak</label>
                        </div>
                    </div>
                </div>

                <div class="subsection-title">Diisi oleh : Dokter</div>
                
                <div class="subsection-title">Keadaan Umum</div>
                
                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="kesadaran" name="kesadaran" placeholder=" ">
                        <label for="kesadaran" class="label-floating">Kesadaran</label>
                    </div>
                </div>

                <div class="subsection-title">Pemeriksaan Fisik</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="inline-input">
                            <span>TB:</span>
                            <input type="text" id="tb" name="tb" placeholder=" "> <span>Cm</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>BB:</span>
                            <input type="text" id="bb" name="bb" placeholder=" "> <span>Kg</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>TD:</span>
                            <input type="text" id="td" name="td" placeholder=" "> <span>mmHg</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>Nadi:</span>
                            <input type="text" id="nadi" name="nadi" placeholder=" "> <span>x/mnt</span>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="inline-input">
                            <span>RR:</span>
                            <input type="text" id="rr" name="rr" placeholder=" "> <span>x/mnt</span>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="inline-input">
                            <span>Suhu:</span>
                            <input type="text" id="suhu" name="suhu" placeholder=" "> <span>°C</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label>Skrining Nyeri</label>
                    <div class="radio-group">
                        <label><input type="radio" name="skrining_nyeri" value="Tidak"> Tidak</label>
                        <label><input type="radio" name="skrining_nyeri" value="Ya"> Ya <strong>(Lanjut pengisian Formulir Assesment Nyeri)</strong></label>
                    </div>
                </div>

                <div class="form-row">
                    <label>Jalan Nafas</label>
                    <div class="radio-group">
                        <label><input type="radio" name="jalan_nafas" value="Normal"> Normal</label>
                        <label><input type="radio" name="jalan_nafas" value="Buka mulut"> Buka mulut > 2 jari</label>
                        <label><input type="radio" name="jalan_nafas" value="Jarak Thyrimental"> Jarak Thyrimental > 3 jari</label>
                        <label><input type="radio" name="jalan_nafas" value="Mallampati"> Mallampati I / II / III / IV</label>
                    </div>
                    <div class="radio-group">
                        <label><input type="radio" name="gerakan_leher" value="Maksimal"> Gerakan leher Maksimal</label>
                        <label><input type="radio" name="gerakan_leher" value="Abnormal"> Abnormal</label>
                        <div class="input-container">
                            <input type="text" id="gerakanLeherAbnormal" name="gerakanLeherAbnormal" placeholder=" ">
                            <label for="gerakanLeherAbnormal" class="label-floating">Keterangan</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="paruParu" name="paruParu" placeholder=" ">
                        <label for="paruParu" class="label-floating">Paru - paru</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="jantung" name="jantung" placeholder=" ">
                        <label for="jantung" class="label-floating">Jantung</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="abdomen" name="abdomen" placeholder=" ">
                        <label for="abdomen" class="label-floating">Abdomen</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="ekstrimitas" name="ekstrimitas" placeholder=" ">
                        <label for="ekstrimitas" class="label-floating">Ekstrimitas</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="neurologi" name="neurologi" placeholder=" ">
                        <label for="neurologi" class="label-floating">Neurologi</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="lainLain" name="lainLain" placeholder=" ">
                        <label for="lainLain" class="label-floating">Lain - lain</label>
                    </div>
                </div>

                <div class="subsection-title">Pemeriksaan Penunjang</div>
                
                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="hbHtAlAt" name="hbHtAlAt" placeholder=" ">
                            <label for="hbHtAlAt" class="label-floating">Hb/Ht/AL/AT</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="naKCl" name="naKCl" placeholder=" ">
                            <label for="naKCl" class="label-floating">NA/K/C L</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ureum" name="ureum" placeholder=" ">
                            <label for="ureum" class="label-floating">Ureum</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ctBt" name="ctBt" placeholder=" ">
                            <label for="ctBt" class="label-floating">CT / BT</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="kreatin" name="kreatin" placeholder=" ">
                            <label for="kreatin" class="label-floating">Kreatin</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="ekg" name="ekg" placeholder=" ">
                            <label for="ekg" class="label-floating">EKG</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="roDada" name="roDada" placeholder=" ">
                            <label for="roDada" class="label-floating">RO Dada</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="text" id="echo" name="echo" placeholder=" ">
                            <label for="echo" class="label-floating">Echo</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-container">
                        <input type="text" id="lainLainPemeriksaan" name="lainLainPemeriksaan" placeholder=" ">
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
                        <label><input type="radio" name="asa" value="ASA 1"> ASA 1</label>
                        <label><input type="radio" name="asa" value="ASA 2"> ASA 2</label>
                        <label><input type="radio" name="asa" value="ASA 3"> ASA 3</label>
                        <label><input type="radio" name="asa" value="ASA 4"> ASA 4</label>
                        <label><input type="radio" name="asa" value="ASA 5"> ASA 5</label>
                    </div>
                    <div><strong>B. Emergency</strong></div>
                    <div class="radio-group">
                        <label><input type="radio" name="emergency" value="Ya"> Ya</label>
                        <label><input type="radio" name="emergency" value="Tidak"> Tidak</label>
                    </div>
                    <div><strong>C. Lain - lain</strong></div>
                    <div class="input-container">
                        <input type="text" id="diagnosisLain" name="diagnosisLain" placeholder=" ">
                        <label for="diagnosisLain" class="label-floating">Keterangan lain-lain</label>
                    </div>
                </div>

                <div class="subsection-title">REKOMENDASI TINDAKAN ANESTESI YANG DIPILIH</div>
                
                <div class="anesthesia-options">
                    <div class="option-group">
                        <div class="option-title">Anestesi Umum</div>
                        <div class="radio-group">
                            <label><input type="radio" name="anestesi_umum" value="Intravena"> Intravena</label>
                            <label><input type="radio" name="anestesi_umum" value="Sungkup Muka"> Sungkup Muka</label>
                            <label><input type="radio" name="anestesi_umum" value="LMA"> LMA</label>
                            <label><input type="radio" name="anestesi_umum" value="Pipa ET"> Pipa ET</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Regional Anestesi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="regional" value="Spinal Anestesi"> Spinal Anestesi</label>
                            <label><input type="radio" name="regional" value="Epidural"> Epidural</label>
                            <label><input type="radio" name="regional" value="CSE"> CSE</label>
                            <label><input type="radio" name="regional" value="PNB"> PNB</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Kombinasi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="combined" value="Anestesi Umum + Regional"> Anestesi Umum + Regional Anestesi</label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <div class="option-title">Sedasi</div>
                        <div class="radio-group">
                            <label><input type="radio" name="sedasi" value="Sedasi Sedang"> Sedasi Sedang</label>
                            <label><input type="radio" name="sedasi" value="Sedasi Dalam"> Sedasi Dalam</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label>SARAN</label>
                    <div class="input-container">
                        <textarea id="saran" name="saran" placeholder=" " rows="3"></textarea>
                        <label for="saran" class="label-floating">Saran</label>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="puasaMulaiJam" name="puasaMulaiJam" placeholder=" ">
                            <label for="puasaMulaiJam" class="label-floating">Puasa Mulai (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="puasaMulaiTanggal" name="puasaMulaiTanggal" placeholder=" ">
                            <label for="puasaMulaiTanggal" class="label-floating">Puasa Mulai (Tanggal)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="rencanaTibaJam" name="rencanaTibaJam" placeholder=" ">
                            <label for="rencanaTibaJam" class="label-floating">Rencana Tiba di OK (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="rencanaTibaTanggal" name="rencanaTibaTanggal" placeholder=" ">
                            <label for="rencanaTibaTanggal" class="label-floating">Rencana Tiba di OK (Tanggal)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-item">
                        <div class="input-container">
                            <input type="time" id="rencanaOperasiJam" name="rencanaOperasiJam" placeholder=" ">
                            <label for="rencanaOperasiJam" class="label-floating">Rencana Operasi (Jam)</label>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="input-container">
                            <input type="date" id="rencanaOperasiTanggal" name="rencanaOperasiTanggal" placeholder=" ">
                            <label for="rencanaOperasiTanggal" class="label-floating">Rencana Operasi (Tanggal)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Konsultasi</button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>