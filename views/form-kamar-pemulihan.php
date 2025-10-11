<?php
$page_title = "Catatan Kamar Pemulihan";
$document_code = "RMOK - 30";

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

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
    <div class="title">
        <div style="color: #004d80;">CATATAN KAMAR PEMULIHAN</div>
        <div>RMOK - 30</div>
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
    
    <!-- Data Masuk -->
    <div class="card">
        <h2>Data Masuk dan Kondisi Pasien</h2>
        <?php
        // Get base URL for form action
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_url = $protocol . $host . $base_path;
        $form_action = rtrim($base_url, '/') . '/process/submit-kamar-pemulihan.php';
        ?>
        <form id="formKamarPemulihan" action="<?php echo htmlspecialchars($form_action); ?>" method="POST" data-no-loading>
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            <div class="form-grid">
                <!-- Kolom Kiri -->
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <label for="jamMasuk"><strong>Jam Masuk:</strong></label>
                        <input type="time" id="jamMasuk" name="jamMasuk" style="width: 100px" required />
                        <label for="tglMasuk" style="margin-left:10px;"><strong>Tanggal Masuk:</strong></label>
                        <input type="date" id="tglMasuk" name="tglMasuk" style="width: 140px" required />
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Jalan Nafas:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jalanNafas[]" value="bersih_lapang" /> Bersih & lapang</label>
                        </div>
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Pernapasan:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="pernapasan[]" value="spontan" /> Spontan</label>
                            <label><input type="checkbox" name="pernapasan[]" value="dibantu" /> Dibantu</label>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <label><strong>Bila spontan:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="spontan[]" value="adekuat" /> Adekuat Bersuara</label>
                            <label><input type="checkbox" name="spontan[]" value="penyumbatan" /> Penyumbatan</label>
                            <label><input type="checkbox" name="spontan[]" value="alat" /> Membutuhkan alat</label>
                        </div>
                    </div>

                    <div class="keterangan-pasien">
                        <label><strong>Kesadaran:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="kesadaran[]" value="sadar_betul" /> Sadar betul</label>
                            <label><input type="checkbox" name="kesadaran[]" value="belum_sadar" /> Belum sadar betul</label>
                            <label><input type="checkbox" name="kesadaran[]" value="tidur_dalam" /> Tidur dalam</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vital-section">
            <div class="card">
                <h2>Vital Sign</h2>
                <table>
                    <tr>
                        <th>Simbol</th>
                        <th>Waktu 1</th>
                        <th>Waktu 2</th>
                        <th>Waktu 3</th>
                    </tr>
                    <tr>
                        <td>N</td>
                        <td><input type="number" class="nadi" name="nadi_1" data-index="0"></td>
                        <td><input type="number" class="nadi" name="nadi_2" data-index="1"></td>
                        <td><input type="number" class="nadi" name="nadi_3" data-index="2"></td>
                    </tr>
                    <tr>
                        <td>Sis</td>
                        <td><input type="number" class="sistol" name="sistol_1" data-index="0"></td>
                        <td><input type="number" class="sistol" name="sistol_2" data-index="1"></td>
                        <td><input type="number" class="sistol" name="sistol_3" data-index="2"></td>
                    </tr>
                    <tr>
                        <td>Dis</td>
                        <td><input type="number" class="diastol" name="diastol_1" data-index="0"></td>
                        <td><input type="number" class="diastol" name="diastol_2" data-index="1"></td>
                        <td><input type="number" class="diastol" name="diastol_3" data-index="2"></td>
                    </tr>
                    <tr>
                        <td>R</td>
                        <td><input type="number" class="respirasi" name="respirasi_1" data-index="0"></td>
                        <td><input type="number" class="respirasi" name="respirasi_2" data-index="1"></td>
                        <td><input type="number" class="respirasi" name="respirasi_3" data-index="2"></td>
                    </tr>
                    <tr>
                        <td>X Nyeri</td>
                        <td><input type="number" class="nyeri" name="nyeri_1" min="0" max="10" data-index="0"></td>
                        <td><input type="number" class="nyeri" name="nyeri_2" min="0" max="10" data-index="1"></td>
                        <td><input type="number" class="nyeri" name="nyeri_3" min="0" max="10" data-index="2"></td>
                    </tr>
                </table>
            </div>
            <div class="card chart-container">
                <h2>Grafik Vital Sign</h2>
                <canvas id="vitalChart"></canvas>
            </div>
        </div>

        <div class="two-column">
            <div class="card">
                <h2>Instruksi Pasca Sedasi dan Anestesi</h2>
                <label>Pemantauan Tanda Vital Setiap:
                    <input type="text" name="pemantauan_setiap" style="width: 60px" /> Selama
                    <input type="text" name="pemantauan_selama" style="width: 60px" />
                </label>
                <div class="input-container">
                    <input type="text" name="analgesia" placeholder=" " />
                    <label class="label-floating">Analgesia</label>
                </div>
                <div class="input-container">
                    <input type="text" name="anti_muntah" placeholder=" " />
                    <label class="label-floating">Anti Muntah</label>
                </div>
                <div class="input-container">
                    <input type="text" name="antibiotik" placeholder=" " />
                    <label class="label-floating">Antibiotik</label>
                </div>
            </div>
            <div class="card">
                <h2>&nbsp;</h2>
                <div class="input-container">
                    <input type="text" name="posisi_pasien" placeholder=" " />
                    <label class="label-floating">Posisi Pasien</label>
                </div>
                <div class="input-container">
                    <input type="text" name="obat_lain" placeholder=" " />
                    <label class="label-floating">Obat-obatan lain</label>
                </div>
                <div class="input-container">
                    <input type="text" name="diet_nutrisi" placeholder=" " />
                    <label class="label-floating">Diet & Nutrisi</label>
                </div>
                <div class="input-container">
                    <input type="text" name="lain_lain" placeholder=" " />
                    <label class="label-floating">Lain-lain</label>
                </div>
            </div>
        </div>

        <!-- Keluar kamar pulih & penilain -->
        <div class="two-column">
            <div class="card">
                <h2>Keluar Kamar Pulih</h2>
                <label>Jam <input type="time" name="jam_keluar" style="width: 80px" /></label><br /><br />
                <table style="width: 100%; border-collapse: collapse">
                    <tr>
                        <th style="text-align: left">Tanda Vital</th>
                        <th>TD</th>
                        <th>N</th>
                        <th>R</th>
                        <th>S</th>
                        <th>SPO2</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="text" name="td_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="n_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="r_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="s_keluar" style="width: 60px" /></td>
                        <td><input type="text" name="spo2_keluar" style="width: 60px" /></td>
                    </tr>
                </table>
                <div class="input-group">
                    <label><strong>Skrining Nyeri:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="skrining_nyeri" value="ya"> Ya</label>
                        <label><input type="radio" name="skrining_nyeri" value="tidak"> Tidak</label>
                    </div>
                </div>
                
                <div class="input-group">
                    <label><strong>Ke:</strong></label>
                    <div class="radio-group">
                        <label><input type="radio" name="tujuan_keluar" value="ruang_rawat"> Ruang Rawat</label>
                        <label><input type="radio" name="tujuan_keluar" value="icu"> ICU</label>
                        <label><input type="radio" name="tujuan_keluar" value="pulang"> Pulang</label>
                    </div>
                </div>
                
                <div class="input-container" style="margin-top: 15px;">
                    <textarea name="catatan_khusus" rows="3" placeholder=""></textarea>
                    <label for="catatan_khusus" class="label-floating">Catatan Khusus Ruang Pemulihan</label>
                </div>
            </div>

            <div class="card">
                <h2>Penilaian</h2>
                <table class="score-table">
                    <tr>
                        <td>Aldrete Score</td>
                        <td><input type="number" name="aldrete_score" style="width: fit-content" /></td>
                    </tr>
                    <tr>
                        <td>Bromage Score</td>
                        <td><input type="number" name="bromage_score" style="width: fit-content" /></td>
                    </tr>
                    <tr>
                        <td>Steward Score</td>
                        <td><input type="number" name="steward_score" style="width: fit-content" /></td>
                    </tr>
                </table>
                <br />
                <label for="ket1">Catatan: isi scoring sesuai metode penilaian yang tepat</label>
            </div>
        </div>

        <div class="card">
            <h2>Serah Terima Pasien ke Rawat Lanjut</h2>
            <table>
                <tr>
                    <th>Nama Jelas</th>
                    <th>Perawat yang Menyerahkan</th>
                    <th>Perawat yang Menerima</th>
                    <th>Dokter Anestesi</th>
                </tr>
                <tr>
                    <td>
                        <div class="input-container" style="margin-top: 17px;">
                            <input type="text" name="nama_penanggungjawab" placeholder=" ">
                            <label class="label-floating">Nama</label>
                        </div>
                    </td>
                    <td>
                        <select name="perawat_menyerahkan">
                            <option value="">Pilih Perawat</option>
                            <option value="perawat1">Perawat 1</option>
                            <option value="perawat2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="perawat_menerima">
                            <option value="">Pilih Perawat</option>
                            <option value="perawat1">Perawat 1</option>
                            <option value="perawat2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="dokter_anestesi">
                            <option value="">Pilih Dokter</option>
                            <option value="dokter1">Dokter 1</option>
                            <option value="dokter2">Dokter 2</option>
                        </select>
                </tr>
            </table>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?page=detail-pasien&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>'">Kembali</button>
        </div>
        </form>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
<script src="/assets/js/autosave.js"></script>
<script>
    const ctx = document.getElementById("vitalChart").getContext("2d");
    const vitalChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: ["Waktu 1", "Waktu 2", "Waktu 3"],
            datasets: [
                { label: "N (Nadi)", data: [], borderColor: "red", fill: false },
                { label: "S (Sistol)", data: [], borderColor: "blue", fill: false },
                { label: "D (Diastol)", data: [], borderColor: "green", fill: false },
                { label: "R (Respirasi)", data: [], borderColor: "orange", fill: false },
                { label: "X Nyeri (Skala Nyeri)", data: [], borderColor: "purple", fill: false },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "bottom" }
            }
        }
    });

    function updateChart() {
        const getValues = (cls) => {
            return Array.from(document.querySelectorAll("." + cls)).map(input => input.value ? Number(input.value) : null);
        };

        vitalChart.data.datasets[0].data = getValues("nadi");
        vitalChart.data.datasets[1].data = getValues("sistol");
        vitalChart.data.datasets[2].data = getValues("diastol");
        vitalChart.data.datasets[3].data = getValues("respirasi");
        vitalChart.data.datasets[4].data = getValues("nyeri");

        vitalChart.update();
    }

    document.querySelectorAll("input").forEach(input => {
        input.addEventListener("input", updateChart);
    });
    
    // Initialize AutoSave
    AutoSave.init('formKamarPemulihan', {
        debounce: 1000,
        exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
        showNotification: true,
        clearOnSubmit: true
    });
</script>