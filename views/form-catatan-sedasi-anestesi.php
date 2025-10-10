<?php
$page_title = "Catatan Sedasi dan Anestesi";
$document_code = "RMOK-0003";

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

if (!$booking) {
    echo "Data booking tidak ditemukan.";
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
    <div class="title">
        <div style="color: #004d80;">CATATAN SEDASI DAN ANESTESI</div>
        <div>RMOK-0003</div>
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
        <form id="formCatatanAnestesi" action="process/process-catatan-sedasi-anestesi.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?php echo htmlspecialchars($no_rawat); ?>">
            <input type="hidden" name="kode_paket" value="<?php echo htmlspecialchars($kode_paket); ?>">
            <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            <input type="hidden" name="jam_mulai" value="<?php echo htmlspecialchars($jam_mulai); ?>">
            
            <div class="form-grid">
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="noRm" name="noRm" placeholder=" " required>
                            <label for="noRm" class="label-floating">No. RM</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="nama" name="nama" placeholder=" " required>
                            <label for="nama" class="label-floating">Nama</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="date" id="tglLahir" name="tglLahir" placeholder=" " required>
                            <label for="tglLahir" class="label-floating">Tanggal Lahir</label>
                        </div>
                    </div>
                </div>
                <div class="form-column">
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="ruangPerawatan" name="ruangPerawatan" placeholder=" " required>
                            <label for="ruangPerawatan" class="label-floating">Ruang Perawatan</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="dokterPerawat" name="dokterPerawat" placeholder=" " required>
                            <label for="dokterPerawat" class="label-floating">Dokter yang Merawat</label>
                        </div>
                    </div>
                    <div class="keterangan-pasien">
                        <div class="input-container">
                            <input type="text" id="hubungan" name="hubungan" placeholder=" ">
                            <label for="hubungan" class="label-floating">Hubungan dengan Pasien</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Utama -->
            <h2>Catatan Anestesi</h2>
            <table>
                <tr>
                    <th colspan="4" style="text-align: right;">RMOK 1a Rev-01</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="input-container">
                            <input type="date" id="tanggalTindakan" name="tanggalTindakan" placeholder=" " required>
                            <label for="tanggalTindakan" class="label-floating">Tanggal</label>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="input-container">
                            <input type="time" id="pukul" name="pukul" placeholder=" " required>
                            <label for="pukul" class="label-floating">Pukul</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="input-container">
                            <input type="text" id="dokterBedah" name="dokterBedah" placeholder=" " required>
                            <label for="dokterBedah" class="label-floating">Dokter Bedah</label>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" id="perawatBedah" name="perawatBedah" placeholder=" " required>
                            <label for="perawatBedah" class="label-floating">Perawat Bedah</label>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" id="dokterAnestesi" name="dokterAnestesi" placeholder=" " required>
                            <label for="dokterAnestesi" class="label-floating">Dokter Anestesi</label>
                        </div>
                    </td>
                    <td>
                        <div class="input-container">
                            <input type="text" id="perawatAnestesi" name="perawatAnestesi" placeholder=" " required>
                            <label for="perawatAnestesi" class="label-floating">Perawat Anestesi</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <label><strong>Jenis Pembedahan:</strong></label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jenisPembedahan[]" value="Elektif"> Elektif</label>
                            <label><input type="checkbox" name="jenisPembedahan[]" value="Cito"> Cito</label>
                            <label><input type="checkbox" name="jenisPembedahan[]" value="ODC"> ODC</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="input-container">
                            <textarea id="diagnosa" name="diagnosa" rows="3" placeholder=" " required></textarea>
                            <label for="diagnosa" class="label-floating">Diagnosa Pra Bedah</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="input-container">
                            <input type="text" id="assessment" name="assessment" placeholder=" " required>
                            <label for="assessment" class="label-floating">Assessment Pra Anestesi</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Assessment Pra Induksi</th>
                </tr>
                <tr>
                    <td>BB (Kg)</td>
                    <td><input type="text" name="bb" class="form-input" required></td>
                    <td>TD (mmHg)</td>
                    <td><input type="text" name="td" class="form-input" required></td>
                </tr>
                <tr>
                    <td>Suhu (°C)</td>
                    <td><input type="text" name="suhu" class="form-input" required></td>
                    <td>Respirasi</td>
                    <td><input type="text" name="respirasi" class="form-input" required></td>
                </tr>
                <tr>
                    <td>HB</td>
                    <td><input type="text" name="hb" class="form-input" required></td>
                    <td>TB (cm)</td>
                    <td><input type="text" name="tb" class="form-input" required></td>
                </tr>
                <tr>
                    <td>Nadi</td>
                    <td><input type="text" name="nadi" class="form-input" required></td>
                    <td>GCS</td>
                    <td><input type="text" name="gcs" class="form-input" required></td>
                </tr>
                <tr>
                    <td>Golongan Darah</td>
                    <td><input type="text" name="golonganDarah" class="form-input" required></td>
                    <td>Skrining Nyeri</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="radio" name="skriningNyeri" value="Tidak" required> Tidak</label>
                            <label><input type="radio" name="skriningNyeri" value="Ya"> Ya</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Status Fisik ASA</td>
                    <td><input type="text" name="statusFisikASA" class="form-input" required></td>
                    <td>Penyulit Pra Anestesi</td>
                    <td><input type="text" name="penyulitPraAnestesi" class="form-input"></td>
                </tr>
                <tr>
                    <td>Jenis Anestesi</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jenisAnestesi[]" value="GA"> GA</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Regional - Spinal"> Regional - Spinal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Regional - Epidural"> Regional - Epidural</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Regional - Kaudal"> Regional - Kaudal</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Regional - CSE"> Regional - CSE</label>
                            <label><input type="checkbox" name="jenisAnestesi[]" value="Sedasi"> Sedasi</label>
                        </div>
                    </td>
                    <td>Risiko</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="radio" name="risiko" value="Besar" required> Besar</label>
                            <label><input type="radio" name="risiko" value="Sedang"> Sedang</label>
                            <label><input type="radio" name="risiko" value="Ringan"> Ringan</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Checklist Sebelum Induksi</th>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Ijin Operasi & Anestesi"> Ijin Operasi & Anestesi</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Antibiotika profilaksis"> Antibiotika profilaksis</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="EKG Lead"> EKG Lead</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="SpO₂"> SpO₂</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Urine Catheter"> Urine Catheter</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Cek mesin Anestesi"> Cek mesin Anestesi</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Cek suction unit"> Cek suction unit</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="NIBP"> NIBP</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Temp"> Temp</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Cek Monitor"> Cek Monitor</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Persiapan Jalan Napas"> Persiapan Jalan Napas</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Stetoskop"> Stetoskop</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="NGT"> NGT</label>
                            <label><input type="checkbox" name="checklistSebelumInduksi[]" value="Persiapan Obat-Obatan Anestesi & Emergency"> Persiapan Obat-Obatan Anestesi & Emergency</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Infus Perifer (Tempat / Ukuran)</th>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="infusPerifer1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="infusPerifer2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="infusPerifer3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center;">Posisi</th>
                    <th colspan="2" style="text-align:center;">Premedikasi</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="posisi[]" value="Supine"> Supine</label>
                            <label><input type="checkbox" name="posisi[]" value="Lithotomi"> Lithotomi</label>
                            <label><input type="checkbox" name="posisi[]" value="Prone"> Prone</label>
                            <label><input type="checkbox" name="posisi[]" value="Lateral"> Lateral</label>
                            <label><input type="checkbox" name="posisi[]" value="Perlindungan Mata"> Perlindungan Mata</label>
                            <label><input type="checkbox" name="posisi[]" value="Lain-lain"> Lain-lain</label>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="premedikasiJenis[]" value="Oral"> Oral</label>
                            <label><input type="checkbox" name="premedikasiJenis[]" value="I.M"> I.M</label>
                            <label><input type="checkbox" name="premedikasiJenis[]" value="I.V"> I.V</label>
                        </div>
                        <div class="input-container">
                            <input type="text" name="premedikasiNamaObat" placeholder=" ">
                            <label class="label-floating">Nama Obat</label>
                        </div>
                        <div class="input-container">
                            <input type="text" name="premedikasiDosisObat" placeholder=" ">
                            <label class="label-floating">Dosis Obat</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Anestesi Umum</th>
                </tr>
                <tr>
                    <td>Induksi</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="induksi[]" value="Propofol"> Propofol</label>
                            <label><input type="checkbox" name="induksi[]" value="Thiopental"> Thiopental</label>
                            <label><input type="checkbox" name="induksi[]" value="Etomidate"> Etomidate</label>
                        </div>
                    </td>
                    <td>Jalan Nafas</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="jalanNafas[]" value="facemask"> Facemask</label>
                            <label><input type="checkbox" name="jalanNafas[]" value="LMA"> LMA</label>
                            <label><input type="checkbox" name="jalanNafas[]" value="ETT"> ETT</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Ventilasi</td>
                    <td>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="ventilasi[]" value="Spontant"> Spontant</label>
                            <label><input type="checkbox" name="ventilasi[]" value="Assist"> Assist</label>
                            <label><input type="checkbox" name="ventilasi[]" value="Control"> Control</label>
                        </div>
                    </td>
                    <td>Ventilator</td>
                    <td>
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="ventilatorTV" placeholder=" ">
                                <label class="label-floating">TV</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="ventilatorRR" placeholder=" ">
                                <label class="label-floating">RR</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="ventilatorSpO2" placeholder=" ">
                                <label class="label-floating">SpO2</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="ventilatorPEEP" placeholder=" ">
                                <label class="label-floating">PEEP</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Ukuran</td>
                    <td><input type="text" name="ukuran" class="form-input"></td>
                    <td colspan="2">
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="balon[]" value="Balon"> Balon</label>
                            <label><input type="checkbox" name="balon[]" value="Tanpa Balon"> Tanpa Balon</label>
                            <label><input type="checkbox" name="rute[]" value="Oral"> Oral</label>
                            <label><input type="checkbox" name="rute[]" value="Nasal"> Nasal</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Anestesi Regional</th>
                </tr>
                <tr>
                    <td>Lokasi</td>
                    <td><input type="text" name="lokasi" class="form-input"></td>
                    <td>Jarum / No</td>
                    <td><input type="text" name="jarumNo" class="form-input"></td>
                </tr>
                <tr>
                    <td>Kateter</td>
                    <td><input type="text" name="kateter" class="form-input"></td>
                    <td>Obat Anestesi Lokal</td>
                    <td><input type="text" name="obatAnestesiLokal" class="form-input"></td>
                </tr>
                <tr>
                    <td>Hasil</td>
                    <td colspan="3">
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="hasilAnestesiRegional[]" value="Ketinggian Blok"> Ketinggian Blok</label>
                            <label><input type="checkbox" name="hasilAnestesiRegional[]" value="Total Blok"> Total Blok</label>
                            <label><input type="checkbox" name="hasilAnestesiRegional[]" value="Gagal Blok"> Gagal Blok</label>
                            <label><input type="checkbox" name="hasilAnestesiRegional[]" value="Partial Blok"> Partial Blok</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center;">Obat-obatan</th>
                    <th colspan="2" style="text-align:center;">Cairan Infus</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="obat1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="obat2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="obat3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="obat4" placeholder=" ">
                                <label class="label-floating">4.</label>
                            </div>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="infus1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="infus2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="infus3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="infus4" placeholder=" ">
                                <label class="label-floating">4.</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center;">Cairan Output</th>
                    <th colspan="2" style="text-align:center;">Masalah Selama Anestesi</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="output1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="output2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="output3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="output4" placeholder=" ">
                                <label class="label-floating">4.</label>
                            </div>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="masalah1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="masalah2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="masalah3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="masalah4" placeholder=" ">
                                <label class="label-floating">4.</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">Tindakan</th>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="list-inputs">
                            <div class="input-container">
                                <input type="text" name="tindakan1" placeholder=" ">
                                <label class="label-floating">1.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="tindakan2" placeholder=" ">
                                <label class="label-floating">2.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="tindakan3" placeholder=" ">
                                <label class="label-floating">3.</label>
                            </div>
                            <div class="input-container">
                                <input type="text" name="tindakan4" placeholder=" ">
                                <label class="label-floating">4.</label>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Serah Terima Pasien -->
            <h2>Serah Terima Pasien</h2>
            <table>
                <tr>
                    <th>Perawat yang Menyerahkan</th>
                    <th>Perawat yang Menerima</th>
                    <th>Dokter Anestesi</th>
                </tr>
                <tr>
                    <td>
                        <select name="perawatMenyerahkan" required>
                            <option value="">Perawat Anastesi</option>
                            <option value="Perawat 1">Perawat 1</option>
                            <option value="Perawat 2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="perawatMenerima" required>
                            <option value="">Pilih Perawat</option>
                            <option value="Perawat 1">Perawat 1</option>
                            <option value="Perawat 2">Perawat 2</option>
                        </select>
                    </td>
                    <td>
                        <select name="dokterAnestesiSerah" required>
                            <option value="">Pilih Dokter</option>
                            <option value="Dokter 1">Dokter 1</option>
                            <option value="Dokter 2">Dokter 2</option>
                        </select>
                    </td>
                </tr>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Catatan Anestesi</button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>