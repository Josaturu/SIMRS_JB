<?php
session_start();

// Tampilkan notifikasi
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
$page_title = "Catatan Sedasi dan Anestesi";
$document_code = "RMOK-0003";
$configPaths = [
  __DIR__ . '/../config/database.php',
  $_SERVER['DOCUMENT_ROOT'] . '/SIMRS_JB/config/database.php',
  'C:/FOLDER RIZKI/SIMRS_JB/config/database.php'
];

foreach ($configPaths as $path) {
  if (file_exists($path)) {
      require_once $path;
      break;
  }
}

// Jika masih tidak ditemukan, tampilkan error
if (!class_exists('Database')) {
  die("File database.php tidak ditemukan. Periksa konfigurasi.");
}

$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data booking beserta data pasien
$query = "SELECT bo.*, p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.alamat, p.jenis_kelamin, 
                 p.tempat_lahir, p.no_hp, p.gol_darah
          FROM booking_operasi bo 
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien 
          WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "❌ Data pasien tidak ditemukan.";
    exit;
}

// Ambil data vital sign
$q_vs = "SELECT COUNT(*) AS total 
         FROM tbl_anestesi_vital_sign 
         WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket AND tanggal = :tanggal AND jam_mulai = :jam_mulai";
$stmt_vs = $db->prepare($q_vs);
$stmt_vs->execute([
  ':no_rawat' => $no_rawat,
  ':kode_paket' => $kode_paket,
  ':tanggal' => $tanggal,
  ':jam_mulai' => $jam_mulai
]);
$vs = $stmt_vs->fetch(PDO::FETCH_ASSOC);
$vs_done = $vs['total'] > 0;

// Ambil data catatan anestesi jika sudah pernah disimpan
$q_catatan = "SELECT * FROM tbl_anestesi_catatan_anestesi 
              WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket 
              AND tanggal = :tanggal AND jam_mulai = :jam_mulai";
$stmt_cat = $db->prepare($q_catatan);
$stmt_cat->execute([
  ':no_rawat' => $no_rawat,
  ':kode_paket' => $kode_paket,
  ':tanggal' => $tanggal,
  ':jam_mulai' => $jam_mulai
]);
$catatan = $stmt_cat->fetch(PDO::FETCH_ASSOC);

// Gunakan data pasien dari booking jika catatan belum ada
if (!$catatan) {
    $catatan = [
        'no_rm' => $booking['kode_rekam_medis'] ?? '',
        'nama' => $booking['nama'] ?? '',
        'tgl_lahir' => $booking['tanggal_lahir'] ?? '',
        'golongan_darah' => $booking['gol_darah'] ?? ''
    ];
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/style.css">

<div class="container">
  <div class="title">
    <div style="color:#004d80;">CATATAN SEDASI DAN ANESTESI</div>
    <div>RMOK 1a Rev-01</div>
  </div>

  <form action="../process/process-simpan-catatan-sedasi.php" method="POST" id="formSedasi">
    <input type="hidden" name="no_rawat" value="<?= htmlspecialchars($no_rawat) ?>">
    <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($kode_paket) ?>">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
    <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($catatan['id'] ?? '') ?>">

    <div class="container">
      <div class="card">
        <h2>Data Booking Operasi</h2>
        <div class="form-grid">
          <div class="form-column">
            <div class="input-container">
              <input type="text" id="no_rawat" name="no_rawat" placeholder=" " value="<?= htmlspecialchars($no_rawat) ?>" readonly>
              <label for="no_rawat" class="label-floating">No. Rawat</label>
            </div>
            <div class="input-container">
              <input type="text" id="kode_paket" name="kode_paket" placeholder=" " value="<?= htmlspecialchars($kode_paket) ?>" readonly>
              <label for="kode_paket" class="label-floating">Kode Paket</label>
            </div>
          </div>
          <div class="form-column">
            <div class="input-container">
              <input type="date" id="tanggal" name="tanggal" placeholder=" " value="<?= htmlspecialchars($tanggal) ?>" readonly>
              <label for="tanggal" class="label-floating">Tanggal Booking</label>
            </div>
            <div class="input-container">
              <input type="time" id="jam_mulai" name="jam_mulai" placeholder=" " value="<?= htmlspecialchars($jam_mulai) ?>" readonly>
              <label for="jam_mulai" class="label-floating">Jam Mulai</label>
            </div>
          </div>
        </div>

        <h2>Informasi Pasien</h2>
        <div class="form-grid">
          <div class="form-column">
            <div class="input-container">
              <input type="text" id="noRm" name="noRm" placeholder=" " value="<?= htmlspecialchars($catatan['no_rm']) ?>" required readonly>
              <label for="noRm" class="label-floating">No. RM</label>
            </div>
            <div class="input-container">
              <input type="text" id="nama" name="nama" placeholder=" " value="<?= htmlspecialchars($catatan['nama']) ?>" required readonly>
              <label for="nama" class="label-floating">Nama</label>
            </div>
            <div class="input-container">
              <input type="date" id="tglLahir" name="tglLahir" placeholder=" " value="<?= htmlspecialchars($catatan['tgl_lahir']) ?>" required readonly>
              <label for="tglLahir" class="label-floating">Tanggal Lahir</label>
            </div>
          </div>
          <div class="form-column">
            <div class="input-container">
              <input type="text" id="ruangPerawatan" name="ruangPerawatan" placeholder=" " value="<?= htmlspecialchars($catatan['ruang_perawatan'] ?? '') ?>" required>
              <label for="ruangPerawatan" class="label-floating">Ruang Perawatan</label>
            </div>
            <div class="input-container">
              <input type="text" id="dokterPerawat" name="dokterPerawat" placeholder=" " value="<?= htmlspecialchars($catatan['dokter_merawat'] ?? '') ?>" required>
              <label for="dokterPerawat" class="label-floating">Dokter yang Merawat</label>
            </div>
          </div>
        </div>

        <h2>Catatan Anestesi</h2>
        <table>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="date" id="tanggal_anestesi" name="tanggal_anestesi" placeholder=" "
                      value="<?= htmlspecialchars($catatan['tanggal_anestesi'] ?? date('Y-m-d')) ?>" required>
                <label for="tanggal_anestesi" class="label-floating required">Tanggal</label>
              </div>
            </td>
            <td colspan="2">
              <div class="input-container">
                <input type="time" id="pukul" name="pukul" placeholder=" "
                      value="<?= htmlspecialchars($catatan['pukul'] ?? date('H:i')) ?>" required>
                <label for="pukul" class="label-floating required">Pukul</label>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="input-container">
                <input type="text" id="dokter_anestesi" name="dokter_anestesi" placeholder=" "
                      value="<?= htmlspecialchars($catatan['dokter_anestesi'] ?? '') ?>" required>
                <label for="dokter_anestesi" class="label-floating required">Dokter Anestesi</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="perawat_anestesi" name="perawat_anestesi" placeholder=" "
                      value="<?= htmlspecialchars($catatan['perawat_anestesi'] ?? '') ?>" required>
                <label for="perawat_anestesi" class="label-floating required">Perawat Anestesi</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="dokter_bedah" name="dokter_bedah" placeholder=" "
                      value="<?= htmlspecialchars($catatan['dokter_bedah'] ?? '') ?>">
                <label for="dokter_bedah" class="label-floating">Dokter Bedah</label>
              </div>
            </td>
            <td>
              <div class="input-container">
                <input type="text" id="perawat_bedah" name="perawat_bedah" placeholder=" "
                      value="<?= htmlspecialchars($catatan['perawat_bedah'] ?? '') ?>">
                <label for="perawat_bedah" class="label-floating">Perawat Bedah</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <label class="required"><strong>Jenis Pembedahan:</strong></label>
              <div>
                <label><input type="radio" name="jenis_pembedahan" value="Elektif" <?= (isset($catatan['jenis_pembedahan']) && $catatan['jenis_pembedahan']=='Elektif')?'checked':''; ?> required> Elektif</label>
                <label><input type="radio" name="jenis_pembedahan" value="Cito" <?= (isset($catatan['jenis_pembedahan']) && $catatan['jenis_pembedahan']=='Cito')?'checked':''; ?>> Cito</label>
                <label><input type="radio" name="jenis_pembedahan" value="ODC" <?= (isset($catatan['jenis_pembedahan']) && $catatan['jenis_pembedahan']=='ODC')?'checked':''; ?>> ODC</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="diagnosa_pra_bedah" name="diagnosa_pra_bedah" rows="3" placeholder=" " required><?= htmlspecialchars($catatan['diagnosa_pra_bedah'] ?? '') ?></textarea>
                <label for="diagnosa_pra_bedah" class="label-floating required">Diagnosa Pra Bedah</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="nama_tindakan" name="nama_tindakan" rows="3" placeholder=" " required><?= htmlspecialchars($catatan['nama_tindakan'] ?? '') ?></textarea>
                <label for="nama_tindakan" class="label-floating required">Nama Tindakan</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="diagnosa_pasca_bedah" name="diagnosa_pasca_bedah" rows="3" placeholder=" " required><?= htmlspecialchars($catatan['diagnosa_pasca_bedah'] ?? '') ?></textarea>
                <label for="diagnosa_pasca_bedah" class="label-floating required">Diagnosa Pasca Bedah</label>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="asessment" name="asessment" placeholder=" " required><?= htmlspecialchars($catatan['asessment_pra_anestesi'] ?? '') ?></textarea>
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
          <td><input type="number" class="form-input" name="bb" step="0.1" min="0" value="<?= htmlspecialchars($catatan['bb'] ?? '') ?>"></td>
          <td>TD (mmHg)</td>
          <td><input type="text" class="form-input" name="td" placeholder="120/80" value="<?= htmlspecialchars($catatan['td'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Suhu (°C)</td>
          <td><input type="number" class="form-input" name="suhu" step="0.1" min="0" value="<?= htmlspecialchars($catatan['suhu'] ?? '') ?>"></td>
          <td>Respirasi</td>
          <td><input type="number" class="form-input" name="respirasi" min="0" value="<?= htmlspecialchars($catatan['respirasi'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>HB</td>
          <td><input type="number" class="form-input" name="hb" step="0.1" min="0" value="<?= htmlspecialchars($catatan['hb'] ?? '') ?>"></td>
          <td>TB (cm)</td>
          <td><input type="number" class="form-input" name="tb" step="0.1" min="0" value="<?= htmlspecialchars($catatan['tb'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Nadi</td>
          <td><input type="number" class="form-input" name="nadi" min="0" value="<?= htmlspecialchars($catatan['nadi'] ?? '') ?>"></td>
          <td>GCS</td>
          <td><input type="number" class="form-input" name="gcs" min="3" max="15" value="<?= htmlspecialchars($catatan['gcs'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Golongan Darah</td>
          <td>
            <select name="golongan_darah" class="form-input">
              <option value="">- Pilih -</option>
              <?php
                $golongan = ['A','B','AB','O'];
                foreach ($golongan as $g) {
                  $sel = (isset($catatan['golongan_darah']) && $catatan['golongan_darah']==$g) ? 'selected' : '';
                  echo "<option value='$g' $sel>$g</option>";
                }
              ?>
            </select>
          </td>
          <td>Skrining Nyeri</td>
          <td>
            <div class="checkbox-group">
              <label><input type="radio" name="skrining_nyeri" value="Tidak" <?= (isset($catatan['skrining_nyeri']) && $catatan['skrining_nyeri']=='Tidak')?'checked':''; ?>> Tidak</label>
              <label><input type="radio" name="skrining_nyeri" value="Ya" <?= (isset($catatan['skrining_nyeri']) && $catatan['skrining_nyeri']=='Ya')?'checked':''; ?>> Ya</label>
            </div>
          </td>
        </tr>
        <tr>
          <td><strong>STATUS FISIK ASA</strong></td>
          <td>
            <select name="status_fisik_asa" class="form-input">
              <option value="">- Pilih -</option>
              <?php
                $asa = ['I','II','III','IV','V','VI'];
                foreach ($asa as $a) {
                  $sel = (isset($catatan['status_fisik_asa']) && $catatan['status_fisik_asa']==$a) ? 'selected' : '';
                  echo "<option value='$a' $sel>$a</option>";
                }
              ?>
            </select>
          </td>
          <td>Penyulit Pra Anestesi</td>
          <td><input type="text" name="penyulit_pra_anestesi" class="form-input" value="<?= htmlspecialchars($catatan['penyulit_pra_anestesi'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td colspan="2"><strong>JENIS ANESTESI</strong></td>
          <td colspan="2"><strong>RESIKO</strong></td>
        </tr>
        <tr>
          <td colspan="2">
            <div>
              <label><input type="radio" name="jenis_anestesi" value="Besar" <?= (isset($catatan['jenis_anestesi']) && $catatan['jenis_anestesi']=='Besar')?'checked':''; ?>> Besar</label>
              <label><input type="radio" name="jenis_anestesi" value="Sedang" <?= (isset($catatan['jenis_anestesi']) && $catatan['jenis_anestesi']=='Sedang')?'checked':''; ?>> Sedang</label>
              <label><input type="radio" name="jenis_anestesi" value="Ringan" <?= (isset($catatan['jenis_anestesi']) && $catatan['jenis_anestesi']=='Ringan')?'checked':''; ?>> Ringan</label>
            </div>
          </td>
          <td colspan="2">
            <div>
              <label><input type="radio" name="resiko" value="Besar" <?= (isset($catatan['resiko']) && $catatan['resiko']=='Besar')?'checked':''; ?>> Besar</label>
              <label><input type="radio" name="resiko" value="Sedang" <?= (isset($catatan['resiko']) && $catatan['resiko']=='Sedang')?'checked':''; ?>> Sedang</label>
              <label><input type="radio" name="resiko" value="Ringan" <?= (isset($catatan['resiko']) && $catatan['resiko']=='Ringan')?'checked':''; ?>> Ringan</label>
            </div>
          </td>
        </tr>
        <tr>
          <th colspan="4" style="text-align:center;">Checklist Sebelum Induksi</th>
        </tr>
        <tr>
          <td colspan="4">
            <?php 
              $checklist_data = isset($catatan['checklist_sebelum_induksi']) ? explode(',', $catatan['checklist_sebelum_induksi']) : [];
              $opsi = [
                "Ijin Operasi & Anestesi","Antibiotika profilaksis","EKG Lead","SpO₂",
                "Urine Catheter","Cek mesin Anestesi","Cek suction unit","NIBP","Temp",
                "Cek Monitor","Persiapan Jalan Napas","Stetoskop","NGT","Persiapan Obat-Obatan Anestesi & Emergency"
              ];
            ?>
            <div class="checkbox-group">
              <?php foreach ($opsi as $o): ?>
                <label><input type="checkbox" name="checklist_sebelum_induksi[]" value="<?= $o ?>" <?= in_array($o,$checklist_data)?'checked':''; ?>> <?= $o ?></label>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>
        <tr>
          <th colspan="4" style="text-align:center;">Teknik Anestesi</th>
        </tr>
        <tr>
          <?php $teknik = ['GA','Regional - Spinal','Regional - Epidural','Regional - Kaudal','Regional - CSE','Sedasi'];
                $val_tek = $catatan['teknik_anestesi'] ?? ''; ?>
          <td colspan="4">
            <div class="checkbox-group">
              <?php foreach ($teknik as $t): ?>
                <label><input type="radio" name="teknik_anestesi" value="<?= $t ?>" <?= ($val_tek==$t)?'checked':''; ?>> <?= $t ?></label>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>
        <tr>
          <th colspan="4" style="text-align:center;">Infus Perifer (Tempat / Ukuran)</th>
        </tr>
        <tr>
          <?php 
            $infus_data = isset($catatan['infus']) ? explode(',', $catatan['infus']) : ["","",""];
          ?>
          <td colspan="4">
            <div class="list-inputs">
              <?php for ($i=0; $i<3; $i++): ?>
                <div class="input-container">
                  <input type="text" id="infus<?= $i+1 ?>" name="infus[]" placeholder=" " value="<?= htmlspecialchars($infus_data[$i] ?? '') ?>">
                  <label for="infus<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                </div>
              <?php endfor; ?>
            </div>
          </td>
        </tr>
        <tr>
    <th colspan="4" style="text-align:center;">Posisi</th>
    </tr>
    <tr>
      <td colspan="4">
        <?php 
          $checklist_data = isset($catatan['posisi']) ? explode(',', $catatan['posisi']) : [];
          $opsi = [
            "SUPINE","LITHOTOMI","PRONE","LATERAL",
            "PERLINDUNGAN MATA","Lain-lain :"
          ];
        ?>
        <div class="checkbox-group">
          <?php foreach ($opsi as $o): ?>
            <label><input type="checkbox" name="posisi[]" value="<?= $o ?>" class="posisi-checkbox" <?= in_array($o,$checklist_data)?'checked':''; ?>> <?= $o ?></label>
          <?php endforeach; ?>
        </div>
        <div id="lain_lain_posisi_container" style="margin-top: 10px; display: none;">
          <div class="input-container">
            <input type="text" id="lain_lain_posisi" name="lain_lain_posisi" placeholder=" " value="<?= htmlspecialchars($catatan['lain_lain_posisi'] ?? '') ?>">
            <label for="lain_lain_posisi" class="label-floating">Lain-lain Posisi</label>
          </div>
        </div>
      </td>
    </tr>
    <tr>
        <th colspan="4" style="text-align:center;">Premedikasi</th>
    </tr>
    <tr>
        <td colspan="4">
            <?php 
              $checklist_data = isset($catatan['premedikasi']) ? explode(',', $catatan['premedikasi']) : [];
              $opsi = [
                "ORAL","I.M","I.V","Nama Obat :",
                "Dosis Obat :"
              ];
            ?>
            <div class="checkbox-group">
              <?php foreach ($opsi as $o): ?>
                <label><input type="checkbox" name="premedikasi[]" value="<?= $o ?>" class="premedikasi-checkbox" <?= in_array($o,$checklist_data)?'checked':''; ?>> <?= $o ?></label>
              <?php endforeach; ?>
            </div>
            <!-- Input text untuk Nama Obat (muncul jika checkbox Nama Obat dicek) -->
            <div id="premedik_nama_obat_container" style="margin-top: 10px; display: none;">
              <div class="input-container">
                <input type="text" id="premedik_nama_obat" name="premedik_nama_obat" placeholder=" " value="<?= htmlspecialchars($catatan['premedik_nama_obat'] ?? '') ?>">
                <label for="premedik_nama_obat" class="label-floating">Nama Obat</label>
              </div>
            </div>
            
            <!-- Input text untuk Dosis Obat (muncul jika checkbox Dosis Obat dicek) -->
            <div id="premedik_dosis_obat_container" style="margin-top: 10px; display: none;">
              <div class="input-container">
                <input type="text" id="premedik_dosis_obat" name="premedik_dosis_obat" placeholder=" " value="<?= htmlspecialchars($catatan['premedik_dosis_obat'] ?? '') ?>">
                <label for="premedik_dosis_obat" class="label-floating">Dosis Obat</label>
              </div>
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
            <th colspan="4" style="text-align:center;">ANESTESI UMUM</th>
          </tr>
          <tr>
            <?php
              $induksi_data = isset($catatan['induksi']) ? explode(',', $catatan['induksi']) : [];
              $jalan_data = isset($catatan['jalan_nafas']) ? explode(',', $catatan['jalan_nafas']) : [];
              $ventilasi_data = isset($catatan['ventilasi']) ? explode(',', $catatan['ventilasi']) : [];
              $ventilator_data = isset($catatan['ventilator']) ? explode(',', $catatan['ventilator']) : [];
            ?>
            <td>
              <label>Induksi</label>
              <div>
                <?php foreach (["Propofol","Thiopental","Etomidate"] as $opt): ?>
                  <label><input type="checkbox" name="induksi[]" value="<?= $opt ?>" <?= in_array($opt,$induksi_data)?'checked':''; ?>> <?= $opt ?></label>
                <?php endforeach; ?>
              </div>
            </td>
            <td style="text-align: center;">
              <label>Jalan Nafas</label>
              <div>
                <?php foreach (["facemask","LMA","ETT"] as $opt): ?>
                  <label><input type="checkbox" name="jalan_nafas[]" value="<?= $opt ?>" <?= in_array($opt,$jalan_data)?'checked':''; ?>> <?= $opt ?></label>
                <?php endforeach; ?>
              </div>
            </td>
            <td style="text-align: center;">
              <label>Ventilasi</label>
              <div>
                <?php foreach (["Spontant","Assist","Control"] as $opt): ?>
                  <label><input type="checkbox" name="ventilasi[]" value="<?= $opt ?>" <?= in_array($opt,$ventilasi_data)?'checked':''; ?>> <?= $opt ?></label>
                <?php endforeach; ?>
              </div>
            </td>
            <td>
              <label>Ventilator</label>
              <div>
                <?php foreach (["TV","RR","SpO2","PEEP"] as $opt): ?>
                  <label><input type="checkbox" name="ventilator[]" value="<?= $opt ?>" <?= in_array($opt,$ventilator_data)?'checked':''; ?>> <?= $opt ?></label>
                <?php endforeach; ?>
              </div>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="text" id="ukuran_balon" name="ukuran_balon" placeholder=" " value="<?= htmlspecialchars($catatan['ukuran_balon'] ?? '') ?>">
                <label for="ukuran_balon" class="label-floating">Ukuran</label>
              </div>
            </td>
            <td>
              <input type="radio" name="jenis_balon" value="Balon" <?= (isset($catatan['jenis_balon']) && $catatan['jenis_balon']=='Balon')?'checked':''; ?>> Balon <br>
              <input type="radio" name="jenis_balon" value="Tanpa Balon" <?= (isset($catatan['jenis_balon']) && $catatan['jenis_balon']=='Tanpa Balon')?'checked':''; ?>> Tanpa Balon
            </td>
            <td>
              <input type="radio" name="posisi_ett" value="Oral" <?= (isset($catatan['posisi_ett']) && $catatan['posisi_ett']=='Oral')?'checked':''; ?>> Oral <br>
              <input type="radio" name="posisi_ett" value="Nasal" <?= (isset($catatan['posisi_ett']) && $catatan['posisi_ett']=='Nasal')?'checked':''; ?>> Nasal
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="input-container">
                <input type="text" id="lain-lain_balon" name="lain-lain_balon" placeholder=" " value="<?= htmlspecialchars($catatan['lain_lain_balon'] ?? '') ?>">
                <label for="lain-lain_balon" class="label-floating">Lain-lain</label>
              </div>
            </td>
          </tr>

          <!-- ANESTESI REGIONAL -->
          <tr>
            <th colspan="2" style="text-align:center;">ANESTESI REGIONAL</th>
            <th colspan="2" style="text-align:center;">HASIL</th>
          </tr>
          <?php $hasil_regional = isset($catatan['hasil_regional']) ? explode(',', $catatan['hasil_regional']) : []; ?>
          <tr>
            <td colspan="2">
              <div class="input-container">
                <input type="text" id="lokasi" name="lokasi_regional" placeholder=" " value="<?= htmlspecialchars($catatan['lokasi_regional'] ?? '') ?>">
                <label for="lokasi" class="label-floating">Lokasi</label>
              </div>
              <div class="input-container">
                <input type="text" id="jarum" name="jarum_regional" placeholder=" " value="<?= htmlspecialchars($catatan['jarum_regional'] ?? '') ?>">
                <label for="jarum" class="label-floating">Jarum / No</label>
              </div>
              <div class="input-container">
                <input type="text" id="kateter" name="kateter_regional" placeholder=" " value="<?= htmlspecialchars($catatan['kateter_regional'] ?? '') ?>">
                <label for="kateter" class="label-floating">Kateter</label>
              </div>
              <div class="input-container">
                <input type="text" id="obat_anestesi_lokal" name="obat_anestesi_lokal" placeholder=" " value="<?= htmlspecialchars($catatan['obat_anestesi_lokal'] ?? '') ?>">
                <label for="obat_anestesi_lokal" class="label-floating">Obat Anestesi Lokal</label>
              </div>
            </td>
            <td colspan="2" style="text-align:left;">
              <?php foreach (["Ketinggian Blok","Total Blok","Gagal Blok","Partial Blok"] as $opt): ?>
                <label><input type="checkbox" name="hasil_regional[]" value="<?= $opt ?>" <?= in_array($opt,$hasil_regional)?'checked':''; ?>> <?= $opt ?></label><br>
              <?php endforeach; ?>
            </td>
          </tr>

          <!-- OBAT-OBATAN -->
          <?php $obat = isset($catatan['obat']) ? explode(',', $catatan['obat']) : ["","","","","","","",""]; ?>
          <tr>
            <th colspan="4" style="text-align:center;">Obat-obatan</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=0;$i<4;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="obat<?= $i+1 ?>" name="obat[]" placeholder=" " value="<?= htmlspecialchars($obat[$i] ?? '') ?>">
                    <label for="obat<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=4;$i<8;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="obat<?= $i+1 ?>" name="obat[]" placeholder=" " value="<?= htmlspecialchars($obat[$i] ?? '') ?>">
                    <label for="obat<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
          </tr>

          <!-- CAIRAN INFUS & OUTPUT -->
          <?php 
            $infus = isset($catatan['cairan_infus']) ? explode(',', $catatan['cairan_infus']) : ["","","",""];
            $output = isset($catatan['cairan_output']) ? explode(',', $catatan['cairan_output']) : ["","","",""];
          ?>
          <tr>
            <th colspan="2" style="text-align:center;">Cairan Infus</th>
            <th colspan="2" style="text-align:center;">Cairan Output</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=0;$i<4;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="cairan_infus<?= $i+1 ?>" name="cairan_infus[]" placeholder=" " value="<?= htmlspecialchars($infus[$i] ?? '') ?>">
                    <label for="cairan_infus<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=0;$i<4;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="cairan_output<?= $i+1 ?>" name="cairan_output[]" placeholder=" " value="<?= htmlspecialchars($output[$i] ?? '') ?>">
                    <label for="cairan_output<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
          </tr>

          <!-- MASALAH & TINDAKAN -->
          <?php 
            $masalah_selama_anestesi = isset($catatan['masalah_selama_anestesi']) ? explode(',', $catatan['masalah_selama_anestesi']) : ["","","",""];
            $tindakan = isset($catatan['tindakan']) ? explode(',', $catatan['tindakan']) : ["","","",""];
          ?>
          <tr>
            <th colspan="2" style="text-align:center;">Masalah Selama Anestesi</th>
            <th colspan="2" style="text-align:center;">Tindakan</th>
          </tr>
          <tr>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=0;$i<4;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="masalah_selama_anestesi<?= $i+1 ?>" name="masalah_selama_anestesi[]" placeholder=" " value="<?= htmlspecialchars($masalah_selama_anestesi[$i] ?? '') ?>">
                    <label for="masalah_selama_anestesi<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
            <td colspan="2">
              <div class="list-inputs">
                <?php for ($i=0;$i<4;$i++): ?>
                  <div class="input-container">
                    <input type="text" id="tindakan<?= $i+1 ?>" name="tindakan[]" placeholder=" " value="<?= htmlspecialchars($tindakan[$i] ?? '') ?>">
                    <label for="tindakan<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
                  </div>
                <?php endfor; ?>
              </div>
            </td>
          </tr>

          <tr>
            <td colspan="4">
              <div class="input-container">
                <textarea id="keterangan" name="keterangan" rows="3" placeholder=" "><?= htmlspecialchars($catatan['keterangan'] ?? '') ?></textarea>
                <label for="keterangan" class="label-floating">Keterangan Tambahan</label>
              </div>
            </td>
          </tr>
        </table>

       <!-- Tombol untuk menuju halaman Vital Sign -->
        <div class="vital-sign-access" style="margin:20px 0; text-align:center;">
          <?php if (!empty($vs_done)): ?>
              <a href="index.php?page=vital-sign&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
                  class="btn btn-success">✅ Vital Sign Sudah Diisi</a>
          <?php else: ?>
              <a href="index.php?page=vital-sign&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
                  class="btn btn-primary">➕ Isi Vital Sign</a>
          <?php endif; ?>
        </div>

        <h2>Waktu Prosedur Anestesi & Pembedahan</h2>
        <div class="time-form" style="margin-top:20px;">
          <div><label>Mulai Anestesi</label><input type="time" name="mulai_anestesi" value="<?= htmlspecialchars($catatan['mulai_anestesi'] ?? '') ?>"></div>
          <div><label>Selesai Anestesi</label><input type="time" name="selesai_anestesi" value="<?= htmlspecialchars($catatan['selesai_anestesi'] ?? '') ?>"></div>
          <div><label>Mulai Pembedahan</label><input type="time" name="mulai_pembedahan" value="<?= htmlspecialchars($catatan['mulai_pembedahan'] ?? '') ?>"></div>
          <div><label>Selesai Pembedahan</label><input type="time" name="selesai_pembedahan" value="<?= htmlspecialchars($catatan['selesai_pembedahan'] ?? '') ?>"></div>
          <div class="full"><label>Keterangan</label><input type="text" name="keterangan_waktu" value="<?= htmlspecialchars($catatan['keterangan_waktu'] ?? '') ?>"></div>
          <div><label>Induksi Pukul</label><input type="time" name="induksi_pukul" value="<?= htmlspecialchars($catatan['induksi_pukul'] ?? '') ?>"></div>
          <div><label>Pasien Siap Insisi</label><input type="time" name="pasien_siap_insisi" value="<?= htmlspecialchars($catatan['pasien_siap_insisi'] ?? '') ?>"></div>
          <div><label>Insisi Mulai Pukul</label><input type="time" name="insisi_mulai_pukul" value="<?= htmlspecialchars($catatan['insisi_mulai_pukul'] ?? '') ?>"></div>
          <div><label>Operasi Mulai Pukul</label><input type="time" name="operasi_mulai_pukul" value="<?= htmlspecialchars($catatan['operasi_mulai_pukul'] ?? '') ?>"></div>
          <div><label>Ekstubasi Pukul</label><input type="time" name="ekstubasi_pukul" value="<?= htmlspecialchars($catatan['ekstubasi_pukul'] ?? '') ?>"></div>
          <div><label>Pasien Keluar OK</label><input type="time" name="pasien_keluar_ok" value="<?= htmlspecialchars($catatan['pasien_keluar_ok'] ?? '') ?>"></div>
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
                <option <?= (isset($catatan['perawat_menyerahkan']) && $catatan['perawat_menyerahkan']=='Perawat Anestesi 1')?'selected':''; ?>>Perawat Anestesi 1</option>
                <option <?= (isset($catatan['perawat_menyerahkan']) && $catatan['perawat_menyerahkan']=='Perawat Anestesi 2')?'selected':''; ?>>Perawat Anestesi 2</option>
              </select>
            </td>
            <td>
              <select name="perawat_menerima" required>
                <option value="">- Pilih Perawat -</option>
                <option <?= (isset($catatan['perawat_menerima']) && $catatan['perawat_menerima']=='Perawat Ruangan 1')?'selected':''; ?>>Perawat Ruangan 1</option>
                <option <?= (isset($catatan['perawat_menerima']) && $catatan['perawat_menerima']=='Perawat Ruangan 2')?'selected':''; ?>>Perawat Ruangan 2</option>
              </select>
            </td>
            <td>
              <select name="dokter_anestesi_ttd" required>
                <option value="">- Pilih Dokter -</option>
                <option <?= (isset($catatan['dokter_anestesi_ttd']) && $catatan['dokter_anestesi_ttd']=='Dokter Anestesi 1')?'selected':''; ?>>Dokter Anestesi 1</option>
                <option <?= (isset($catatan['dokter_anestesi_ttd']) && $catatan['dokter_anestesi_ttd']=='Dokter Anestesi 2')?'selected':''; ?>>Dokter Anestesi 2</option>
              </select>
            </td>
          </tr>
        </table>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= isset($catatan['id']) ? 'Simpan Perubahan' : 'Simpan' ?></button>
          <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?page=detail-pasien&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>'">Kembali</button>
        </div>
<script src="/assets/js/autosave.js"></script>
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

// Toggle untuk Lain-lain Posisi
document.addEventListener('DOMContentLoaded', function() {
  function toggleLainLainPosisi() {
    var checkboxes = document.querySelectorAll('.posisi-checkbox');
    var container = document.getElementById('lain_lain_posisi_container');
    var isChecked = false;
    
    checkboxes.forEach(function(checkbox) {
      if (checkbox.value === 'Lain-lain :' && checkbox.checked) {
        isChecked = true;
      }
    });
    
    if (container) {
      container.style.display = isChecked ? 'block' : 'none';
    }
  }
  
  var posisiCheckboxes = document.querySelectorAll('.posisi-checkbox');
  posisiCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', toggleLainLainPosisi);
  });
  
  // Jalankan saat load untuk data existing
  toggleLainLainPosisi();
});

// Toggle untuk Nama Obat dan Dosis Obat
document.addEventListener('DOMContentLoaded', function() {
  function togglePremedikasiInputs() {
    var checkboxes = document.querySelectorAll('.premedikasi-checkbox');
    var namaContainer = document.getElementById('premedik_nama_obat_container');
    var dosisContainer = document.getElementById('premedik_dosis_obat_container');
    var namaChecked = false;
    var dosisChecked = false;
    
    checkboxes.forEach(function(checkbox) {
      if (checkbox.value === 'Nama Obat :' && checkbox.checked) {
        namaChecked = true;
      }
      if (checkbox.value === 'Dosis Obat :' && checkbox.checked) {
        dosisChecked = true;
      }
    });
    
    if (namaContainer) {
      namaContainer.style.display = namaChecked ? 'block' : 'none';
    }
    if (dosisContainer) {
      dosisContainer.style.display = dosisChecked ? 'block' : 'none';
    }
  }
  
  var premedikasiCheckboxes = document.querySelectorAll('.premedikasi-checkbox');
  premedikasiCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', togglePremedikasiInputs);
  });
  
  // Jalankan saat load untuk data existing
  togglePremedikasiInputs();
  
  // Initialize AutoSave
  AutoSave.init('formCatatanSedasi', {
    debounce: 1000,
    exclude: ['no_rawat', 'kode_paket', 'tanggal', 'jam_mulai'],
    showNotification: true,
    clearOnSubmit: true
  });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>