<?php
$page_title = "Catatan Sedasi dan Anestesi";
$document_code = "RMOK-0003";

// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header("Location: index.php");
    exit;
}

// Ambil data pasien dan booking
$database = new Database();
$db = $database->getConnection();
$query = "SELECT * FROM booking_operasi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Cek vital sign
$q_vs = "SELECT COUNT(*) AS total 
         FROM tbl_anestesi_vital_sign 
         WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket AND tanggal = :tanggal AND jam_mulai = :jam_mulai";
$stmt_vs = $db->prepare($q_vs);
$stmt_vs->bindParam(':no_rawat', $no_rawat);
$stmt_vs->bindParam(':kode_paket', $kode_paket);
$stmt_vs->bindParam(':tanggal', $tanggal);
$stmt_vs->bindParam(':jam_mulai', $jam_mulai);
$stmt_vs->execute();
$vs = $stmt_vs->fetch(PDO::FETCH_ASSOC);
$vs_done = $vs['total'] > 0;

if (!$booking) {
    echo "Data pasien tidak ditemukan.";
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
  <div class="title">
    <div style="color:#004d80;">CATATAN SEDASI DAN ANESTESI</div>
    <div>RMOK 1a Rev-01</div>
  </div>

  <!-- FORM START -->
  <form action="process/process-simpan-catatan-sedasi.php" method="POST" id="formSedasi">
    <input type="hidden" name="no_rawat" value="<?= htmlspecialchars($no_rawat) ?>">
    <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($kode_paket) ?>">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
    <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>">

    <div class="container">
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
        <!-- Informasi Identitas -->
        <h2>Informasi Pasien</h2>
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
          </div>
        </div>

        <!-- Tabel Utama -->
        <h2>Catatan Anestesi</h2>
        <table>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="date" id="tanggal_anestesi" name="tanggal_anestesi" placeholder=" " 
                       value="<?= date('Y-m-d') ?>" required>
                <label for="tanggal_anestesi" class="label-floating required">Tanggal</label>
              </div>
            </td>
            <td colspan="2">
              <div class="input-container">
                <input type="time" id="pukul" name="pukul" placeholder=" " 
                       value="<?= date('H:i') ?>" required>
                <label for="pukul" class="label-floating required">Pukul</label>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="input-container">
                <input type="text" id="dokter_anestesi" name="dokter_anestesi" placeholder=" " required>
                <label for="dokter_anestesi" class="label-floating required">Dokter Anestesi</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="perawat_anestesi" name="perawat_anestesi" placeholder=" " required>
                <label for="perawat_anestesi" class="label-floating required">Perawat Anestesi</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="dokter_bedah" name="dokter_bedah" placeholder=" ">
                <label for="dokter_bedah" class="label-floating">Dokter Bedah</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="perawat_bedah" name="perawat_bedah" placeholder=" ">
                <label for="perawat_bedah" class="label-floating">Perawat Bedah</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <label class="required"><strong>Jenis Pembedahan:</strong></label>
              <div>
                <label><input type="radio" name="jenis_pembedahan" value="Elektif" required> Elektif</label>
                <label><input type="radio" name="jenis_pembedahan" value="Cito"> Cito</label>
                <label><input type="radio" name="jenis_pembedahan" value="ODC"> ODC</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="diagnosa_pra_bedah" name="diagnosa_pra_bedah" rows="3" placeholder=" " required></textarea>
                <label for="diagnosa_pra_bedah" class="label-floating required">Diagnosa Pra Bedah</label>
              </div>
            </td>
          </tr>
          <tr>
          <td colspan="4">
              <div class="input-container">
                <textarea id="nama_tindakan" name="nama_tindakan" rows="3" placeholder=" " required></textarea>
                <label for="nama_tindakan" class="label-floating required">Nama Tindakan</label>
              </div>
            </td>
          </tr>
          <tr>
            <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="diagnosa_pasca_bedah" name="diagnosa_pasca_bedah" rows="3" placeholder=" " required></textarea>
                <label for="diagnosa_pasca_bedah" class="label-floating required">Diagnosa Pasca Bedah</label>
              </div>
            </td>
            </tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="asessment" name="asessment" placeholder=" " required></textarea>
                <label for="asessment" class="label-floating required">Asessmen Pra Anestesi</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <h3>Asessmen Pra Induksi</h3>
            </td>
          </tr>
          <tr>
            <td>BB (Kg)</td>
            <td><input type="number" class="form-input" name="bb" step="0.1" min="0"></td>
            <td>TD (mmHg)</td>
            <td><input type="text" class="form-input" name="td" placeholder="120/80"></td>
          </tr>
          <tr>
            <td>Suhu (°C)</td>
            <td><input type="number" class="form-input" name="suhu" step="0.1" min="0"></td>
            <td>Respirasi</td>
            <td><input type="number" class="form-input" name="respirasi" min="0"></td>
          </tr>
          <tr>
            <td>HB</td>
            <td><input type="number" class="form-input" name="hb" step="0.1" min="0"></td>
            <td>TB (cm)</td>
            <td><input type="number" class="form-input" name="tb" step="0.1" min="0"></td>
          </tr>
          <tr>
            <td>Nadi</td>
            <td><input type="number" class="form-input" name="nadi" min="0"></td>
            <td>GCS</td>
            <td><input type="number" class="form-input" name="gcs" min="3" max="15"></td>
          </tr>
          <tr>
            <td>Golongan Darah</td>
            <td>
              <select name="golongan_darah" class="form-input">
                <option value="">- Pilih -</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
              </select>
            </td>
            <td>Skrining Nyeri</td>
            <td>
              <div class="checkbox-group">
                <label><input type="radio" name="skrining_nyeri" value="Tidak"> Tidak</label>
                <label><input type="radio" name="skrining_nyeri" value="Ya"> Ya</label>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <strong>STATUS FISIK ASA</strong>
            </td>
            <td>
              <select name="status_fisik_asa" class="form-input">
                <option value="">- Pilih -</option>
                <option value="I">I</option>
                <option value="II">II</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
                <option value="V">V</option>
                <option value="VI">VI</option>
              </select>
            </td>
            <td>
              Penyulit pra Anestesi
            </td>
            <td>
              <input type="text" name="penyulit_pra_anestesi" class="form-input">
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <strong>JENIS ANESTESI</strong>
            </td>
            <td colspan="2">
              <strong>RESIKO</strong>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div>
                <label><input type="radio" name="jenis_anestesi" value="Besar"> Besar</label>
                <label><input type="radio" name="jenis_anestesi" value="Sedang"> Sedang</label>
                <label><input type="radio" name="jenis_anestesi" value="Ringan"> Ringan</label>
              </div>
            </td>
            <td colspan="2">
              <div>
                <label><input type="radio" name="resiko" value="Besar"> Besar</label>
                <label><input type="radio" name="resiko" value="Sedang"> Sedang</label>
                <label><input type="radio" name="resiko" value="Ringan"> Ringan</label>
              </div>
            </td>
          </tr>
          <tr>
            <th colspan="4" style="text-align:center;">Checklist Sebelum Induksi</th>
          </tr>
          <tr>
            <td colspan="4">
              <div class="checkbox-group">
                <label><input type="checkbox" name="checklist[]" value="Ijin Operasi & Anestesi"> Ijin Operasi & Anestesi</label>
                <label><input type="checkbox" name="checklist[]" value="Antibiotika profilaksis"> Antibiotika profilaksis</label>
                <label><input type="checkbox" name="checklist[]" value="EKG Lead"> EKG Lead</label>
                <label><input type="checkbox" name="checklist[]" value="SpO₂"> SpO₂</label>
                <label><input type="checkbox" name="checklist[]" value="Urine Catheter"> Urine Catheter</label>
                <label><input type="checkbox" name="checklist[]" value="Cek mesin Anestesi"> Cek mesin Anestesi</label>
                <label><input type="checkbox" name="checklist[]" value="Cek suction unit"> Cek suction unit</label>
                <label><input type="checkbox" name="checklist[]" value="NIBP"> NIBP</label>
                <label><input type="checkbox" name="checklist[]" value="Temp"> Temp</label>
                <label><input type="checkbox" name="checklist[]" value="Cek Monitor"> Cek Monitor</label>
                <label><input type="checkbox" name="checklist[]" value="Persiapan Jalan Napas"> Persiapan Jalan Napas</label>
                <label><input type="checkbox" name="checklist[]" value="Stetoskop"> Stetoskop</label>
                <label><input type="checkbox" name="checklist[]" value="NGT"> NGT</label>
                <label><input type="checkbox" name="checklist[]" value="Persiapan Obat-Obatan Anestesi & Emergency"> Persiapan Obat-Obatan Anestesi & Emergency</label>
              </div>
            </td>
          </tr>
          <tr>
            <th colspan="4" style="text-align:center;">Teknik Anestesi</th>
          </tr>
          <tr>
            <td colspan="4">
              <div class="checkbox-group">
                <label><input type="radio" name="teknik_anestesi" value="GA"> GA</label>
                <label><input type="radio" name="teknik_anestesi" value="Regional - Spinal"> Regional - Spinal</label>
                <label><input type="radio" name="teknik_anestesi" value="Regional - Epidural"> Regional - Epidural</label>
                <label><input type="radio" name="teknik_anestesi" value="Regional - Kaudal"> Regional - Kaudal</label>
                <label><input type="radio" name="teknik_anestesi" value="Regional - CSE"> Regional - CSE</label>
                <label><input type="radio" name="teknik_anestesi" value="Sedasi"> Sedasi</label>
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
                  <input type="text" id="infus1" name="infus[]" placeholder=" ">
                  <label for="infus1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="infus2" name="infus[]" placeholder=" ">
                  <label for="infus2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="infus3" name="infus[]" placeholder=" ">
                  <label for="infus3" class="label-floating">3.</label>
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
              </div>
              <div class="input-container">
                <input type="text" id="lain_lain_posisi" name="lain_lain_posisi" placeholder="">
                <label for="lain_lain_posisi" class="label-floating">Lain-lain</label>
              </div>
            </td>
            <td colspan="2">
              <div class="checkbox-group">
                <label><input type="checkbox" name="premedikasi_route[]" value="Oral"> Oral</label>
                <label><input type="checkbox" name="premedikasi_route[]" value="I.M"> I.M</label>
                <label><input type="checkbox" name="premedikasi_route[]" value="I.V"> I.V</label>
              </div>
              <div class="input-container">
                <input type="text" id="premedik_nama_obat" name="premedik_nama_obat" placeholder="">
                <label for="premedik_nama_obat" class="label-floating">Nama Obat</label>
              </div>
              <div class="input-container">
                <input type="text" id="premedik_dosis_obat" name="premedik_dosis_obat" placeholder=" ">
                <label for="premedik_dosis_obat" class="label-floating">Dosis Obat</label>
              </div>
            </td>
          </tr>
        </table>
        
        <!-- ANESTESI UMUM -->
        <h2>ANESTESI UMUM</h2>
        <table>
          <tr>
            <th colspan="4" style="text-align: right;"><strong>RMOK 1b Rev-01</strong></th>
          </tr>  
          <tr>
            <td>
              <label style="text-align: center;"> Induksi</label>
              <div>
                <label><input type="checkbox" name="induksi[]" value="Propofol"> Propofol</label>
                <label><input type="checkbox" name="induksi[]" value="Thiopental"> Thiopental</label>
                <label><input type="checkbox" name="induksi[]" value="Etomidate"> Etomidate</label>
              </div>
            </td>
            <td style="text-align: center;">
              <label> Jalan Nafas</label>
              <div>
                <label><input type="checkbox" name="jalan_nafas[]" value="facemask"> facemask</label>
                <label><input type="checkbox" name="jalan_nafas[]" value="LMA"> LMA</label>
                <label><input type="checkbox" name="jalan_nafas[]" value="ETT"> ETT</label>
              </div>
            </td>
            <td style="text-align: center;">
              <label> Ventilasi</label>
              <div>
                <label><input type="checkbox" name="ventilasi[]" value="Spontant"> Spontant</label>
                <label><input type="checkbox" name="ventilasi[]" value="Assist"> Assist</label>
                <label><input type="checkbox" name="ventilasi[]" value="Control"> Control</label>
              </div>
            </td>
            <td>
              <label style="text-align: center;"> Ventilator</label>
              <div>
                <label><input type="checkbox" name="ventilator[]" value="TV"> TV</label>
                <label><input type="checkbox" name="ventilator[]" value="RR"> RR</label>
                <label><input type="checkbox" name="ventilator[]" value="SpO2"> SpO2</label>
                <label><input type="checkbox" name="ventilator[]" value="PEEP"> PEEP</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="text" id="ukuran_balon" name="ukuran_balon" placeholder=" ">
                <label for="ukuran_balon" class="label-floating">Ukuran</label>
              </div>
            </td>
            <td>
              <input type="radio" name="jenis_balon" value="Balon" id="balon"> Balon <br>
              <input type="radio" name="jenis_balon" value="Tanpa Balon" id="tanpa_balon"> Tanpa Balon
            </td>
            <td>
              <input type="radio" name="posisi_ett" value="Oral" id="oral"> Oral <br>
              <input type="radio" name="posisi_ett" value="Nasal" id="nasal"> Nasal
            </td>
          </tr>
          <tr>
          <td colspan="4">
              <div class="input-container">
                <input type="text" id="lain-lain_balon" name="lain-lain_balon" placeholder=" ">
                <label for="lain-lain_balon" class="label-floating">lain-lain</label>
              </div>
            </td>
          </tr>
        
          <!-- ANESTESI REGIONAL -->
          <tr>
            <th colspan="2" style="text-align:center;">ANESTESI REGIONAL</th>
            <th colspan="2" style="text-align:center;">HASIL</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="text" id="lokasi" name="lokasi_regional" placeholder=" ">
                <label for="lokasi" class="label-floating">Lokasi</label>
              </div>
              <div class="input-container">
                <input type="text" id="jarum" name="jarum_regional" placeholder="">
                <label for="jarum" class="label-floating">Jarum / No</label>
              </div>
              <div class="input-container">
                <input type="text" id="kateter" name="kateter_regional" placeholder="">
                <label for="kateter" class="label-floating">Kateter</label>
              </div>
              <div class="input-container">
                <input type="text" id="obat_anestesi_lokal" name="obat_anestesi_lokal" placeholder=" ">
                <label for="obat_anestesi_lokal" class="label-floating">Obat Anestesi Lokal</label>
              </div>
            </td>
            <td colspan="2" style="text-align: left;">
              <label><input type="checkbox" name="hasil_regional[]" value="Ketinggian Blok"> Ketinggian Blok</label><br>
              <label><input type="checkbox" name="hasil_regional[]" value="Total Blok"> Total Blok</label><br>
              <label><input type="checkbox" name="hasil_regional[]" value="Gagal Blok"> Gagal Blok</label><br>
              <label><input type="checkbox" name="hasil_regional[]" value="Partial Blok"> Partial Blok</label>
            </td>
          </tr>
          <tr>
            <th colspan="4" style="text-align:center;">Obat-obatan</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="obat1" name="obat[]" placeholder=" ">
                  <label for="obat1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat2" name="obat[]" placeholder=" ">
                  <label for="obat2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat3" name="obat[]" placeholder=" ">
                  <label for="obat3" class="label-floating">3.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat4" name="obat[]" placeholder=" ">
                  <label for="obat4" class="label-floating">4.</label>
                </div>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="obat5" name="obat[]" placeholder=" ">
                  <label for="obat5" class="label-floating">5.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat6" name="obat[]" placeholder=" ">
                  <label for="obat6" class="label-floating">6.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat7" name="obat[]" placeholder=" ">
                  <label for="obat7" class="label-floating">7.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="obat8" name="obat[]" placeholder=" ">
                  <label for="obat8" class="label-floating">8.</label>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th colspan="2" style="text-align:center;">Cairan Infus</th>
            <th colspan="2" style="text-align:center;">Cairan Output</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="cairan_infus1" name="cairan_infus[]" placeholder=" ">
                  <label for="cairan_infus1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_infus2" name="cairan_infus[]" placeholder=" ">
                  <label for="cairan_infus2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_infus3" name="cairan_infus[]" placeholder=" ">
                  <label for="cairan_infus3" class="label-floating">3.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_infus4" name="cairan_infus[]" placeholder=" ">
                  <label for="cairan_infus4" class="label-floating">4.</label>
                </div>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="cairan_output1" name="cairan_output[]" placeholder=" ">
                  <label for="cairan_output1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_output2" name="cairan_output[]" placeholder=" ">
                  <label for="cairan_output2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_output3" name="cairan_output[]" placeholder=" ">
                  <label for="cairan_output3" class="label-floating">3.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="cairan_output4" name="cairan_output[]" placeholder=" ">
                  <label for="cairan_output4" class="label-floating">4.</label>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th colspan="2" style="text-align:center;">Masalah Selama Anestesi</th>
            <th colspan="2" style="text-align:center;">Tindakan</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="masalah1" name="masalah[]" placeholder=" ">
                  <label for="masalah1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="masalah2" name="masalah[]" placeholder=" ">
                  <label for="masalah2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="masalah3" name="masalah[]" placeholder=" ">
                  <label for="masalah3" class="label-floating">3.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="masalah4" name="masalah[]" placeholder=" ">
                  <label for="masalah4" class="label-floating">4.</label>
                </div>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <div class="input-container">
                  <input type="text" id="tindakan1" name="tindakan[]" placeholder=" ">
                  <label for="tindakan1" class="label-floating">1.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="tindakan2" name="tindakan[]" placeholder=" ">
                  <label for="tindakan2" class="label-floating">2.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="tindakan3" name="tindakan[]" placeholder=" ">
                  <label for="tindakan3" class="label-floating">3.</label>
                </div>
                <div class="input-container">
                  <input type="text" id="tindakan4" name="tindakan[]" placeholder=" ">
                  <label for="tindakan4" class="label-floating">4.</label>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="keterangan" name="keterangan" rows="3" placeholder=" "></textarea>
                <label for="keterangan" class="label-floating">Keterangan Tambahan</label>
              </div>
            </td>
          </tr>
        </table>

        <!-- Tombol untuk menuju halaman Vital Sign -->
        <div class="vital-sign-access" style="margin:20px 0; text-align:center;">
          <?php if ($vs_done): ?>
              <a href="index.php?page=vital-sign&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
                  class="btn btn-success">✅ Vital Sign Sudah Diisi</a>
          <?php else: ?>
              <a href="index.php?page=vital-sign&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
                  class="btn btn-primary">➕ Isi Vital Sign</a>
          <?php endif; ?>
        </div>

        <h2>Waktu Prosedur Anestesi & Pembedahan</h2>
        <div class="time-form" style="margin-top:20px;">
          <div><label>Mulai Anestesi</label><input type="time" name="mulai_anestesi"></div>
          <div><label>Selesai Anestesi</label><input type="time" name="selesai_anestesi"></div>
          <div><label>Mulai Pembedahan</label><input type="time" name="mulai_pembedahan"></div>
          <div><label>Selesai Pembedahan</label><input type="time" name="selesai_pembedahan"></div>
          <div class="full"><label>Keterangan</label><input type="text" name="keterangan_waktu"></div>
          <div><label>Induksi Pukul</label><input type="time" name="induksi_pukul"></div>
          <div><label>Pasien Siap Insisi</label><input type="time" name="pasien_siap_insisi"></div>
          <div><label>Insisi Mulai Pukul</label><input type="time" name="insisi_mulai_pukul"></div>
          <div><label>Operasi Mulai Pukul</label><input type="time" name="operasi_mulai_pukul"></div>
          <div><label>Ekstubasi Pukul</label><input type="time" name="ekstubasi_pukul"></div>
          <div><label>Pasien Keluar OK</label><input type="time" name="pasien_keluar_ok"></div>
        </div>



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
                <select name="perawat_menyerahkan" required>
                  <option value="">- Pilih Perawat -</option>
                  <option>Perawat Anestesi 1</option>
                  <option>Perawat Anestesi 2</option>
                </select>
              </td>
              <td>
                <select name="perawat_menerima" required>
                  <option value="">- Pilih Perawat -</option>
                  <option>Perawat Ruangan 1</option>
                  <option>Perawat Ruangan 2</option>
                </select>
              </td>
              <td>
                <select name="dokter_anestesi_ttd" required>
                  <option value="">- Pilih Dokter -</option>
                  <option>Dokter Anestesi 1</option>
                  <option>Dokter Anestesi 2</option>
                </select>
              </td>
            </tr>
          </table>
      </div>
      <?php include __DIR__ . '/../includes/footer.php'; ?>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
      </div>
    </div>
  </form>
</div>

<script>
// Validasi form sebelum submit
document.getElementById('formSedasi').addEventListener('submit', function(e) {
  let isValid = true;
  const requiredFields = this.querySelectorAll('[required]');
  
  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      isValid = false;
      field.style.borderColor = 'red';
    } else {
      field.style.borderColor = '';
    }
  });
  
  if (!isValid) {
    e.preventDefault();
    alert('Harap lengkapi semua field yang wajib diisi!');
  }
});
</script>