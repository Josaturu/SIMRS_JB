<?php
session_start();

// ANTI-LOOP PROTECTION
$current_url = $_SERVER['REQUEST_URI'];
$loop_key = 'page_load_' . md5($current_url);

if (isset($_SESSION[$loop_key])) {
    $load_count = $_SESSION[$loop_key];
    if ($load_count > 3) {
        // Terlalu banyak reload, clear semua session dan stop
        echo '<div style="padding:20px; background:#ff0000; color:#fff; font-size:18px; text-align:center;">
            <h2>⚠️ INFINITE LOOP DETECTED!</h2>
            <p>Halaman ini sudah di-load ' . $load_count . ' kali.</p>
            <p>Session telah di-clear. <a href="' . htmlspecialchars($current_url) . '" style="color:#fff; text-decoration:underline;">Klik di sini untuk reload</a></p>
        </div>';
        session_destroy();
        exit;
    }
    $_SESSION[$loop_key]++;
} else {
    $_SESSION[$loop_key] = 1;
}

// Reset counter setelah 5 detik (jika user normal browsing)
if (isset($_SESSION[$loop_key . '_time'])) {
    if (time() - $_SESSION[$loop_key . '_time'] > 5) {
        $_SESSION[$loop_key] = 1;
    }
}
$_SESSION[$loop_key . '_time'] = time();

// Tampilkan notifikasi dengan auto-hide
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success" id="successAlert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); animation: slideInRight 0.3s ease-out;">' . 
         '<strong>✓ Berhasil!</strong> ' . htmlspecialchars($_SESSION['success']) . 
         '</div>';
    echo '<script>setTimeout(function(){ 
        var alert = document.getElementById("successAlert");
        if(alert) { 
            alert.style.animation = "slideOutRight 0.3s ease-out";
            setTimeout(function(){ alert.remove(); }, 300);
    }, 3000);</script>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $errorMsg = $_SESSION['error'];
    echo '<div class="alert alert-danger" style="margin:20px; padding:15px; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; border-radius:5px; max-height:200px; overflow-y:auto;">
        ❌ <strong>ERROR:</strong><br>' . nl2br(htmlspecialchars($errorMsg)) . '
    </div><script>
    console.error("Database Error:", ' . json_encode($errorMsg) . ');
    setTimeout(function(){
        var alert = document.querySelector(".alert-danger");
        if(alert) {
            alert.style.opacity = "0";
            alert.style.transition = "opacity 0.3s";
            setTimeout(function(){ alert.remove(); }, 300);
        }
    }, 10000);</script>';
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

// Jika tidak ada parameter, redirect ke daftar pasien (seragam)
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header('Location: /index.php?page=daftar-pasien');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data booking beserta data pasien, dokter, dan ruang
$query = "SELECT bo.*, 
                 p.kode_rekam_medis, p.nama AS nama_pasien, p.tanggal_lahir, p.alamat, p.jenis_kelamin, 
                 p.tempat_lahir, p.no_hp, p.gol_darah,
                 d.nama_dokter,
                 r.nama_ruang
          FROM booking_operasi bo 
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien 
          LEFT JOIN tbl_dokter d ON bo.dokter_rawat COLLATE utf8mb4_unicode_ci = d.id_dokter
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

if (!$booking) {
    echo "❌ Data pasien tidak ditemukan.";
    exit;
}

// Set $pasien untuk header
$pasien = $booking;

// Debug parameter pencarian
error_log("DEBUG SEARCH PARAMS - no_rawat: '$no_rawat', kode_paket: '$kode_paket', tanggal: '$tanggal', jam_mulai: '$jam_mulai'");

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

// Debug: Log data yang ditemukan
error_log("DEBUG FORM CATATAN SEDASI - Data ditemukan: " . ($catatan ? 'YES' : 'NO'));
error_log("DEBUG - Row count: " . $stmt_cat->rowCount());
if ($catatan) {
    error_log("DEBUG - ID: " . ($catatan['id'] ?? 'NULL'));
    error_log("DEBUG - Nama: " . ($catatan['nama'] ?? 'NULL'));
    error_log("DEBUG - Total fields: " . count($catatan));
} else {
    // Coba query tanpa parameter untuk debug
    $debug_query = "SELECT COUNT(*) as total FROM tbl_anestesi_catatan_anestesi";
    $debug_stmt = $db->query($debug_query);
    $total = $debug_stmt->fetch(PDO::FETCH_ASSOC);
    error_log("DEBUG - Total records in table: " . ($total['total'] ?? '0'));
}

// Gunakan data pasien dari booking jika catatan belum ada
if (!$catatan) {
    $catatan = [
        'no_rm' => $booking['kode_rekam_medis'] ?? '',
        'nama' => $booking['nama_pasien'] ?? '',
        'tgl_lahir' => $booking['tanggal_lahir'] ?? '',
        'golongan_darah' => $booking['gol_darah'] ?? ''
    ];
    error_log("DEBUG - Menggunakan data dari booking");
}

// Query data vital sign untuk grafik dan tabel
$query_vital = "SELECT id, waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2 
                FROM tbl_anestesi_vital_sign 
                WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket AND tanggal = :tanggal AND jam_mulai = :jam_mulai
                ORDER BY waktu";
$stmt_vital = $db->prepare($query_vital);
$stmt_vital->bindParam(':no_rawat', $no_rawat);
$stmt_vital->bindParam(':kode_paket', $kode_paket);
$stmt_vital->bindParam(':tanggal', $tanggal);
$stmt_vital->bindParam(':jam_mulai', $jam_mulai);
$stmt_vital->execute();
$vital_data = $stmt_vital->fetchAll(PDO::FETCH_ASSOC);

// Konversi data ke format JSON untuk JavaScript
 $vital_data_json = json_encode($vital_data);

include __DIR__ . '/../includes/assets.php';
?>
<!-- Prevent browser cache -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<!-- Chart.js untuk Vital Sign -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<!-- Force reload: <?= time() ?> -->

<!-- CSS untuk memastikan layout tetap rapi -->
<style>
  /* Perbaikan untuk notifikasi agar tidak menggeser layout */
  .alert-success, .alert-danger {
    position: fixed !important;
    top: 20px !important;
    right: 20px !important;
    z-index: 9999 !important;
    max-width: 400px !important;
  }
  
  /* Animasi smooth */
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }

  /* ========================================
     KONSISTENSI LABEL FLOATING
     Semua label floating di form catatan sedasi
     akan berada di atas input tapi masih menyatu
     dengan garis border (seperti input readonly)
     ======================================== */
  
  /* Label floating saat input focus atau ada value */
  .input-container input:focus + .label-floating,
  .input-container input:not(:placeholder-shown) + .label-floating,
  .input-container textarea:focus + .label-floating,
  .input-container textarea:not(:placeholder-shown) + .label-floating,
  .input-container select:focus + .label-floating,
  .input-container select:not([value=""]) + .label-floating {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }

  /* Label floating untuk input dengan value attribute */
  .input-container input[value]:not([value=""]) + .label-floating,
  .input-container textarea[value]:not([value=""]) + .label-floating {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }

  /* Label floating untuk input readonly (sudah terisi) */
  .input-container input[readonly] + .label-floating {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }

  /* Label floating dengan class required */
  .input-container input:focus + .label-floating.required,
  .input-container input:not(:placeholder-shown) + .label-floating.required,
  .input-container textarea:focus + .label-floating.required,
  .input-container textarea:not(:placeholder-shown) + .label-floating.required {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }

  /* Label floating untuk input dengan value attribute dan required */
  .input-container input[value]:not([value=""]) + .label-floating.required,
  .input-container textarea[value]:not([value=""]) + .label-floating.required {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }

  /* Force label naik untuk input yang sudah ada value saat page load */
  .label-floating.has-value {
    top: -8px !important;
    font-size: 12px !important;
    background: white !important;
    padding: 0 5px !important;
    left: 10px !important;
  }
</style>

<script>
// Force label floating naik saat page load jika input sudah ada value
document.addEventListener('DOMContentLoaded', function() {
  // Ambil semua input container
  const inputContainers = document.querySelectorAll('.input-container');
  
  inputContainers.forEach(function(container) {
    const input = container.querySelector('input, textarea, select');
    const label = container.querySelector('.label-floating');
    
    if (input && label) {
      // Check jika input sudah ada value
      if (input.value && input.value.trim() !== '') {
        label.classList.add('has-value');
        label.style.top = '-8px';
        label.style.fontSize = '12px';
        label.style.background = 'white';
        label.style.padding = '0 5px';
        label.style.left = '10px';
      }
    }
  });
  
  console.log('✅ Label floating initialized for all inputs with values');
});
</script>

<!-- Debug console log dihapus untuk performa lebih baik -->

<div class="container">
  <div class="title">
    <div style="color:#004d80;">CATATAN SEDASI DAN ANESTESI</div>
    <div>RMOK 1a Rev-01</div>
  </div>
  
  <!-- Patient Info Card - Top Section -->
  <?php
  $dokter = $booking['kd_dokter'] ?? '';
  $nama_pasien = $booking['nama'] ?? $booking['nama_pasien'] ?? '';
  ?>
  
  <?php if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal)): ?>
  <div class="patient-info-top-card">
      <div class="patient-info-top-header">
          <strong><i class="fas fa-id-badge"></i> Identitas Pasien</strong>
      </div>
      <div class="patient-info-top-content">
          <?php if (!empty($nama_pasien)): ?>
          <div class="patient-info-top-item">
              <span class="patient-info-top-label"><i class="fas fa-user"></i> Nama:</span>
              <span class="patient-info-top-value"><?= htmlspecialchars($nama_pasien) ?></span>
          </div>
          <?php endif; ?>
          <div class="patient-info-top-item">
              <span class="patient-info-top-label"><i class="fas fa-id-card"></i> No. Rawat:</span>
              <span class="patient-info-top-value"><?= htmlspecialchars($no_rawat) ?></span>
          </div>
          <div class="patient-info-top-item">
              <span class="patient-info-top-label"><i class="fas fa-barcode"></i> Kode Paket:</span>
              <span class="patient-info-top-value"><?= htmlspecialchars($kode_paket) ?></span>
          </div>
          <div class="patient-info-top-item">
              <span class="patient-info-top-label"><i class="fas fa-calendar"></i> Tgl Operasi:</span>
              <span class="patient-info-top-value"><?= htmlspecialchars($tanggal) ?></span>
          </div>
          <?php if (!empty($dokter)): ?>
          <div class="patient-info-top-item">
              <span class="patient-info-top-label"><i class="fas fa-user-md"></i> Dokter:</span>
              <span class="patient-info-top-value"><?= htmlspecialchars($dokter) ?></span>
          </div>
          <?php endif; ?>
      </div>
  </div>
  <?php endif; ?>

  <!-- Notifikasi EDIT/INSERT dihapus untuk tampilan yang lebih bersih -->

  <form action="/process/process-simpan-catatan-sedasi.php" method="POST" id="formCatatanSedasi">
    <input type="hidden" name="no_rawat" value="<?= htmlspecialchars($no_rawat) ?>">
    <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($kode_paket) ?>">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
    <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>">
    
    <!-- Hidden field untuk data vital sign -->
    <input type="hidden" name="vital_sign_data" id="vital_sign_data" value="">
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
            <div class="keterangan-pasien">
              <?php
              $current_ruang = $catatan['ruang_perawatan'] ?? $booking['nama_ruang'] ?? '';
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
              <input type="hidden" id="ruangPerawatan" name="ruangPerawatan" value="<?= htmlspecialchars($current_ruang) ?>">
              <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                  <i class="fas fa-info-circle"></i> Default dari booking operasi, dapat diedit jika salah
              </small>
            </div>
            <div class="input-container">
              <?php 
              // Ambil dokter dari booking, fallback ke catatan jika ada
              $dokter_merawat = $booking['nama_dokter'] ?? $catatan['dokter_merawat'] ?? '';
              ?>
              <input type="text" id="dokterPerawat" name="dokterPerawat" placeholder=" " value="<?= htmlspecialchars($dokter_merawat) ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
              <label for="dokterPerawat" class="label-floating" style="top: -22px; font-size: 12px; background: transparent; padding: 0; left: 0;">Dokter yang Merawat (dari Booking)</label>
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
                <?php
                $current_dokter_anestesi = $catatan['dokter_anestesi'] ?? '';
                $is_other_dokter_anestesi = !empty($current_dokter_anestesi) && !in_array($current_dokter_anestesi, array_column($dokter_list, 'nama_dokter'));
                ?>
                <select id="dokter_anestesi_select" name="dokter_anestesi_select" class="form-select" onchange="toggleInput('dokter_anestesi')" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                  <option value="">-- Pilih Dokter Anestesi --</option>
                  <?php
                  foreach ($dokter_list as $dokter) {
                      $selected = ($current_dokter_anestesi == $dokter['nama_dokter']) ? 'selected' : '';
                      echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                  }
                  ?>
                  <option value="lainnya" <?= $is_other_dokter_anestesi ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                </select>
                <label for="dokter_anestesi_select" class="label-floating required" style="top: -8px; font-size: 12px; background: white; padding: 0 5px;">Dokter Anestesi</label>
              </div>
              <div class="input-container" id="dokter_anestesi_input_container" style="display: <?= $is_other_dokter_anestesi ? 'block' : 'none' ?>; margin-top: 10px;">
                <input type="text" id="dokter_anestesi_input" name="dokter_anestesi_input" placeholder=" " value="<?= $is_other_dokter_anestesi ? htmlspecialchars($current_dokter_anestesi) : '' ?>">
                <label for="dokter_anestesi_input" class="label-floating">Nama Dokter Lainnya</label>
              </div>
              <input type="hidden" id="dokter_anestesi" name="dokter_anestesi" value="<?= htmlspecialchars($current_dokter_anestesi) ?>">
            </td>
            <td>
              <div class="input-container">
                <?php
                $current_perawat_anestesi = $catatan['perawat_anestesi'] ?? '';
                $is_other_perawat_anestesi = !empty($current_perawat_anestesi) && !in_array($current_perawat_anestesi, array_column($perawat_list, 'nama_perawat'));
                ?>
                <select id="perawat_anestesi_select" name="perawat_anestesi_select" class="form-select" onchange="toggleInput('perawat_anestesi')" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;" required>
                  <option value="">-- Pilih Perawat Anestesi --</option>
                  <?php
                  foreach ($perawat_list as $perawat) {
                      $selected = ($current_perawat_anestesi == $perawat['nama_perawat']) ? 'selected' : '';
                      echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                  }
                  ?>
                  <option value="lainnya" <?= $is_other_perawat_anestesi ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                </select>
                <label for="perawat_anestesi_select" class="label-floating required" style="top: -8px; font-size: 12px; background: white; padding: 0 5px;">Perawat Anestesi</label>
              </div>
              <div class="input-container" id="perawat_anestesi_input_container" style="display: <?= $is_other_perawat_anestesi ? 'block' : 'none' ?>; margin-top: 10px;">
                <input type="text" id="perawat_anestesi_input" name="perawat_anestesi_input" placeholder=" " value="<?= $is_other_perawat_anestesi ? htmlspecialchars($current_perawat_anestesi) : '' ?>">
                <label for="perawat_anestesi_input" class="label-floating">Nama Perawat Lainnya</label>
              </div>
              <input type="hidden" id="perawat_anestesi" name="perawat_anestesi" value="<?= htmlspecialchars($current_perawat_anestesi) ?>">
            </td>
            <td>
              <div class="input-container">
                <?php
                $current_dokter_bedah = $catatan['dokter_bedah'] ?? '';
                $is_other_dokter_bedah = !empty($current_dokter_bedah) && !in_array($current_dokter_bedah, array_column($dokter_list, 'nama_dokter'));
                ?>
                <select id="dokter_bedah_select" name="dokter_bedah_select" class="form-select" onchange="toggleInput('dokter_bedah')" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                  <option value="">-- Pilih Dokter Bedah --</option>
                  <?php
                  foreach ($dokter_list as $dokter) {
                      $selected = ($current_dokter_bedah == $dokter['nama_dokter']) ? 'selected' : '';
                      echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                  }
                  ?>
                  <option value="lainnya" <?= $is_other_dokter_bedah ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                </select>
                <label for="dokter_bedah_select" class="label-floating" style="top: -8px; font-size: 12px; background: white; padding: 0 5px;">Dokter Bedah</label>
              </div>
              <div class="input-container" id="dokter_bedah_input_container" style="display: <?= $is_other_dokter_bedah ? 'block' : 'none' ?>; margin-top: 10px;">
                <input type="text" id="dokter_bedah_input" name="dokter_bedah_input" placeholder=" " value="<?= $is_other_dokter_bedah ? htmlspecialchars($current_dokter_bedah) : '' ?>">
                <label for="dokter_bedah_input" class="label-floating">Nama Dokter Lainnya</label>
              </div>
              <input type="hidden" id="dokter_bedah" name="dokter_bedah" value="<?= htmlspecialchars($current_dokter_bedah) ?>">
            </td>
            <td>
              <div class="input-container">
                <?php
                $current_perawat_bedah = $catatan['perawat_bedah'] ?? '';
                $is_other_perawat_bedah = !empty($current_perawat_bedah) && !in_array($current_perawat_bedah, array_column($perawat_list, 'nama_perawat'));
                ?>
                <select id="perawat_bedah_select" name="perawat_bedah_select" class="form-select" onchange="toggleInput('perawat_bedah')" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                  <option value="">-- Pilih Perawat Bedah --</option>
                  <?php
                  foreach ($perawat_list as $perawat) {
                      $selected = ($current_perawat_bedah == $perawat['nama_perawat']) ? 'selected' : '';
                      echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                  }
                  ?>
                  <option value="lainnya" <?= $is_other_perawat_bedah ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
                </select>
                <label for="perawat_bedah_select" class="label-floating" style="top: -8px; font-size: 12px; background: white; padding: 0 5px;">Perawat Bedah</label>
              </div>
              <div class="input-container" id="perawat_bedah_input_container" style="display: <?= $is_other_perawat_bedah ? 'block' : 'none' ?>; margin-top: 10px;">
                <input type="text" id="perawat_bedah_input" name="perawat_bedah_input" placeholder=" " value="<?= $is_other_perawat_bedah ? htmlspecialchars($current_perawat_bedah) : '' ?>">
                <label for="perawat_bedah_input" class="label-floating">Nama Perawat Lainnya</label>
              </div>
              <input type="hidden" id="perawat_bedah" name="perawat_bedah" value="<?= htmlspecialchars($current_perawat_bedah) ?>">
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
              // Explode dan trim spasi dari setiap item
              $checklist_raw = isset($catatan['checklist_sebelum_induksi']) ? $catatan['checklist_sebelum_induksi'] : '';
              $checklist_data = array_map('trim', explode(',', $checklist_raw));
              
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
            // Explode dan trim spasi dari setiap item
            $infus_raw = isset($catatan['infus_perifer']) ? $catatan['infus_perifer'] : '';
            $infus_data = array_map('trim', explode(',', $infus_raw));
            // Pastikan minimal 3 item
            while (count($infus_data) < 3) {
              $infus_data[] = '';
            }
          ?>
          <td colspan="4">
            <div class="list-inputs">
              <?php for ($i=0; $i<3; $i++): ?>
                <div class="input-container">
                  <input type="text" id="infus_perifer<?= $i+1 ?>" name="infus_perifer[]" placeholder=" " value="<?= htmlspecialchars($infus_data[$i] ?? '') ?>">
                  <label for="infus_perifer<?= $i+1 ?>" class="label-floating"><?= $i+1 ?>.</label>
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
          // Explode dan trim spasi dari setiap item
          $posisi_raw = isset($catatan['posisi']) ? $catatan['posisi'] : '';
          $checklist_data = array_map('trim', explode(',', $posisi_raw));
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
              // Explode dan trim spasi dari setiap item
              $premedikasi_raw = isset($catatan['premedikasi']) ? $catatan['premedikasi'] : '';
              $checklist_data = array_map('trim', explode(',', $premedikasi_raw));
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

        <!-- PILIHAN JENIS ANESTESI -->
        <h2 style="margin-top: 30px;">Jenis Anestesi</h2>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #dee2e6;">
          <div style="display: flex; align-items: center; justify-content: center; gap: 30px;">
            <label style="font-size: 16px; font-weight: 600; color: #495057;">
              Pilih Jenis Anestesi:
            </label>
            <div style="display: flex; gap: 15px; align-items: center;">
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 20px; background: white; border: 2px solid #007bff; border-radius: 8px; transition: all 0.3s;" id="label-umum">
                <input type="radio" name="jenis_anestesi" value="umum" id="radio-umum" onchange="toggleAnestesiType('umum')" style="width: 18px; height: 18px; cursor: pointer;">
                <span style="font-size: 15px; font-weight: 600; color: #007bff;">
                  <i class="fas fa-procedures"></i> Anestesi Umum
                </span>
              </label>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 20px; background: white; border: 2px solid #28a745; border-radius: 8px; transition: all 0.3s;" id="label-regional">
                <input type="radio" name="jenis_anestesi" value="regional" id="radio-regional" onchange="toggleAnestesiType('regional')" style="width: 18px; height: 18px; cursor: pointer;">
                <span style="font-size: 15px; font-weight: 600; color: #28a745;">
                  <i class="fas fa-syringe"></i> Anestesi Regional
                </span>
              </label>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 20px; background: white; border: 2px solid #ff9800; border-radius: 8px; transition: all 0.3s;" id="label-keduanya">
                <input type="radio" name="jenis_anestesi" value="keduanya" id="radio-keduanya" onchange="toggleAnestesiType('keduanya')" style="width: 18px; height: 18px; cursor: pointer;">
                <span style="font-size: 15px; font-weight: 600; color: #ff9800;">
                  <i class="fas fa-layer-group"></i> Keduanya
                </span>
              </label>
            </div>
          </div>
          <div style="text-align: center; margin-top: 15px; color: #6c757d; font-size: 13px;">
            <i class="fas fa-info-circle"></i> Pilih jenis anestesi. Pilih "Keduanya" untuk mengisi Umum dan Regional sekaligus.
          </div>
        </div>

        <!-- ANESTESI UMUM -->
        <div id="section-anestesi-umum" style="display: none;">
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
              // Explode dan trim spasi dari setiap item
              $induksi_data = array_map('trim', explode(',', isset($catatan['induksi']) ? $catatan['induksi'] : ''));
              $jalan_data = array_map('trim', explode(',', isset($catatan['jalan_nafas']) ? $catatan['jalan_nafas'] : ''));
              $ventilasi_data = array_map('trim', explode(',', isset($catatan['ventilasi']) ? $catatan['ventilasi'] : ''));
              $ventilator_data = array_map('trim', explode(',', isset($catatan['ventilator']) ? $catatan['ventilator'] : ''));
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
              <div style="display: flex; flex-direction: column; gap: 5px;">
                <div>
                  <input type="radio" name="jenis_balon" value="Balon" <?= (isset($catatan['jenis_balon']) && $catatan['jenis_balon']=='Balon')?'checked':''; ?>> Balon <br>
                  <input type="radio" name="jenis_balon" value="Tanpa Balon" <?= (isset($catatan['jenis_balon']) && $catatan['jenis_balon']=='Tanpa Balon')?'checked':''; ?>> Tanpa Balon
                </div>
                <button type="button" onclick="uncheckRadio('jenis_balon')" 
                        style="padding: 3px 8px; font-size: 11px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">
                  <i class="fas fa-times"></i> Clear
                </button>
              </div>
            </td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 5px;">
                <div>
                  <input type="radio" name="posisi_ett" value="Oral" <?= (isset($catatan['posisi_ett']) && $catatan['posisi_ett']=='Oral')?'checked':''; ?>> Oral <br>
                  <input type="radio" name="posisi_ett" value="Nasal" <?= (isset($catatan['posisi_ett']) && $catatan['posisi_ett']=='Nasal')?'checked':''; ?>> Nasal
                </div>
                <button type="button" onclick="uncheckRadio('posisi_ett')" 
                        style="padding: 3px 8px; font-size: 11px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">
                  <i class="fas fa-times"></i> Clear
                </button>
              </div>
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
        </table>
        </div>
        <!-- END ANESTESI UMUM -->

        <!-- ANESTESI REGIONAL -->
        <div id="section-anestesi-regional" style="display: none;">
          <h2>ANESTESI REGIONAL</h2>
          <table>
          <tr>
            <th colspan="2" style="text-align:center;">ANESTESI REGIONAL</th>
            <th colspan="2" style="text-align:center;">HASIL</th>
          </tr>
          <?php 
            // Explode dan trim spasi dari setiap item
            $hasil_regional = array_map('trim', explode(',', isset($catatan['hasil_regional']) ? $catatan['hasil_regional'] : ''));
          ?>
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
        </table>
        </div>
        <!-- END ANESTESI REGIONAL -->

        <!-- OBAT-OBATAN -->
        <table>
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

        <!-- ========== VITAL SIGN SECTION ========== -->
        <h2 style="margin-top: 30px;">Monitoring Vital Sign</h2>
        
        <!-- Setting Waktu -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: white; font-size: 16px;">
                    <i class="fas fa-clock"></i> Pengaturan Waktu Monitoring
                </h3>
                
                <!-- Toggle Switch untuk Mode Input -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="color: white; font-size: 13px; font-weight: 600;">Mode Input:</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="intervalLabel" style="color: white; font-size: 12px; font-weight: 600; opacity: 1;">Interval</span>
                        <div style="position: relative; width: 50px; height: 24px; background: rgba(255,255,255,0.3); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" 
                             onclick="toggleInputMode()" id="toggleSwitch">
                            <div id="toggleSlider" style="position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: white; border-radius: 50%; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                        </div>
                        <span id="manualLabel" style="color: white; font-size: 12px; font-weight: 600; opacity: 0.5;">Manual</span>
                    </div>
                </div>
            </div>
            
            <!-- Container untuk Mode Interval dan Manual -->
            <div id="inputModeContainer">
                <!-- Mode Interval -->
                <div id="intervalMode" style="display: block;">
                    <div style="display: grid; grid-template-columns: 2fr 2fr auto auto; gap: 15px; align-items: end;">
                        <!-- Waktu Mulai (HH:MM:SS) -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                ⏰ Waktu Mulai (HH:MM:SS)
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; gap: 5px; align-items: center;">
                                <input type="number" id="vs_waktu_jam" min="0" max="23" placeholder="HH" 
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_waktu_menit" min="0" max="59" placeholder="MM" 
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_waktu_detik" min="0" max="59" placeholder="SS" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                            </div>
                        </div>
                        
                        <!-- Interval (HH:MM:SS) -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                ⏱️ Interval (HH:MM:SS)
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; gap: 5px; align-items: center;">
                                <input type="number" id="vs_interval_jam" min="0" max="23" placeholder="HH" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_interval_menit" min="0" max="59" placeholder="MM" value="3"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                                <span style="color: white; font-weight: 700; font-size: 18px;">:</span>
                                <input type="number" id="vs_interval_detik" min="0" max="59" placeholder="SS" value="0"
                                       style="width: 100%; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center;">
                            </div>
                        </div>
                        
                        <!-- Button Now -->
                        <div>
                            <button type="button" id="btn_set_now" 
                                    style="padding: 10px 15px; background: rgba(255,255,255,0.3); color: white; border: 2px solid white; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap;">
                                <i class="fas fa-clock"></i> Set Now
                            </button>
                        </div>
                        
                        <!-- Button Set -->
                        <div>
                            <button type="button" id="btn_set_waktu_config" 
                                    style="padding: 10px 20px; background: white; color: #667eea; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap;">
                                <i class="fas fa-check-circle"></i> Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mode Manual -->
                <div id="manualMode" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: end;">
                        <!-- Input Manual -->
                        <div>
                            <label style="display: block; color: white; font-weight: 600; margin-bottom: 5px; font-size: 13px;">
                                📋 Waktu Input Manual
                            </label>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" id="vs_jam" readonly 
                                           style="flex: 1; padding: 10px; border: 2px solid white; border-radius: 5px; font-size: 14px; font-weight: 600; text-align: center; background: rgba(255,255,255,0.3); color: white;">
                                    <button type="button" id="btn_manual_time" 
                                            style="padding: 10px 12px; background: rgba(255,255,255,0.3); color: white; border: 2px solid white; border-radius: 5px; cursor: pointer; font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button type="button" onclick="addManualTimeEntry()" 
                                            style="padding: 6px 12px; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)';" 
                                            onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';">
                                        <i class="fas fa-plus"></i> Add Entry
                                    </button>
                                    <button type="button" onclick="clearManualEntries()" 
                                            style="padding: 6px 12px; background: rgba(220, 53, 69, 0.3); color: white; border: 1px solid rgba(220, 53, 69, 0.5); border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(220, 53, 69, 0.5)'; this.style.transform='scale(1.05)';" 
                                            onmouseout="this.style.background='rgba(220, 53, 69, 0.3)'; this.style.transform='scale(1)';">
                                        <i class="fas fa-trash"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Button Apply Manual -->
                        <div>
                            <button type="button" id="btn_apply_manual" 
                                    style="padding: 10px 20px; background: white; color: #667eea; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap;">
                                <i class="fas fa-check-circle"></i> Terapkan Manual
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div style="margin-top: 12px; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px; color: white; font-size: 12px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Cara Pakai:</strong> Set waktu mulai dan interval, lalu klik "Terapkan". 
                Waktu akan otomatis bertambah setiap kali Anda tambah record.
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 30px; margin-top: 20px; align-items: start;">
            <!-- LEFT: Form Input -->
            <div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 20px;">
                        <i class="fas fa-heartbeat"></i> Form Input
                    </h3>
                    
                    <!-- Respirasi -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Respirasi (R) - x/menit</label>
                        <input type="number" id="vs_respirasi" min="0" max="60" placeholder="Contoh: 18" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Nadi -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Nadi (N) - BPM</label>
                        <input type="number" id="vs_nadi" min="0" max="200" placeholder="Contoh: 86" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Tekanan Darah -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Tekanan Darah (mmHg)</label>
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center;">
                            <input type="number" id="vs_sistol" min="0" max="300" placeholder="Sistol (120)" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                            <span style="font-weight: 700; font-size: 18px; color: #6c757d;">/</span>
                            <input type="number" id="vs_diastol" min="0" max="200" placeholder="Diastol (80)" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                        </div>
                    </div>
                    
                    <!-- FIO2 -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">FIO2 (%)</label>
                        <input type="number" id="vs_fio2" min="0" max="100" placeholder="Contoh: 98" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- SPO2 -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">SPO2 (%)</label>
                        <input type="number" id="vs_spo2" min="0" max="100" placeholder="Contoh: 98" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Button Group -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <button type="button" id="btn_add_vital" 
                                style="padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-plus-circle"></i> Tambah
                        </button>
                        <button type="button" id="btn_clear_form" 
                                style="padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-eraser"></i> Clear
                        </button>
                    </div>
                    
                    <small style="display: block; margin-top: 8px; color: #6c757d; font-size: 11px; text-align: center;">
                        💡 Data vital sign akan otomatis tersimpan saat klik tombol "Simpan" di bawah
                    </small>
                </div>
            </div>
            
            <!-- RIGHT: Chart + History Table -->
            <div>
                <!-- Grafik -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-chart-line"></i> Grafik Vital Sign
                    </h3>
                    <canvas id="vitalChart" style="max-height: 300px;"></canvas>
                </div>
                
                <!-- Table Data Tersimpan (Database) -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-database"></i> Data Tersimpan di Database
                        <span style="background: #28a745; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">
                            <?= count($vital_data) ?> record
                        </span>
                    </h3>
                    <?php if (count($vital_data) > 0): ?>
                    <div style="overflow-x: auto; max-height: 300px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <tr style="background: #28a745; color: white;">
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745; width: 40px;">#</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: left; font-weight: 700; font-size: 13px; background: #28a745;">Waktu</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745;">Resp</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745;">Nadi</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745;">TD</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745;">FIO2</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745;">SPO2</th>
                                    <th style="padding: 12px; border: 1px solid #1e7e34; text-align: center; font-weight: 700; font-size: 13px; background: #28a745; width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="db_vital_tbody">
                                <?php foreach ($vital_data as $index => $record): ?>
                                <tr style="background: <?= $index % 2 === 0 ? '#ffffff' : '#f8f9fa' ?>;" id="db-row-<?= $index ?>" data-id="<?= htmlspecialchars($record['id']) ?>">
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center; font-weight: 600; color: #28a745;">
                                        <?= $index + 1 ?>
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600;">
                                        <span class="view-mode"><?= date('H:i:s', strtotime($record['waktu'])) ?></span>
                                        <input type="text" class="edit-mode" value="<?= date('H:i:s', strtotime($record['waktu'])) ?>" 
                                               style="display: none; width: 80px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; font-family: monospace;">
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <span class="view-mode" style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                                            <?= $record['respirasi'] ?>
                                        </span>
                                        <input type="number" class="edit-mode" value="<?= $record['respirasi'] ?>" 
                                               style="display: none; width: 60px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <span class="view-mode" style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                                            <?= $record['nadi'] ?> bpm
                                        </span>
                                        <input type="number" class="edit-mode" value="<?= $record['nadi'] ?>" 
                                               style="display: none; width: 60px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <span class="view-mode" style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                                            <?= $record['td_sistolik'] ?>/<?= $record['td_diastolik'] ?>
                                        </span>
                                        <div class="edit-mode" style="display: none;">
                                            <input type="number" value="<?= $record['td_sistolik'] ?>" 
                                                   style="width: 50px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                            <span style="margin: 0 3px;">/</span>
                                            <input type="number" value="<?= $record['td_diastolik'] ?>" 
                                                   style="width: 50px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                        </div>
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <span class="view-mode" style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #7b1fa2; font-weight: 600; font-size: 12px;">
                                            <?= $record['fio2'] ?>%
                                        </span>
                                        <input type="number" class="edit-mode" value="<?= $record['fio2'] ?>" 
                                               style="display: none; width: 60px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <span class="view-mode" style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                                            <?= $record['spo2'] ?>%
                                        </span>
                                        <input type="number" class="edit-mode" value="<?= $record['spo2'] ?>" 
                                               style="display: none; width: 60px; padding: 5px; border: 1px solid #007bff; border-radius: 3px; text-align: center;">
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                                        <button type="button" onclick="editDbRecord(<?= $index ?>); return false;" class="btn-edit-db" 
                                                style="padding: 5px 8px; background: #ffc107; color: #000; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; margin-right: 3px;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" onclick="deleteDbRecord(<?= $index ?>, '<?= htmlspecialchars($record['id']) ?>'); return false;" class="btn-delete-db" 
                                                style="padding: 5px 8px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" onclick="saveDbRecord(<?= $index ?>); return false;" class="btn-save-db" 
                                                style="display: none; padding: 5px 8px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; margin-right: 3px;">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" onclick="cancelDbEdit(<?= $index ?>); return false;" class="btn-cancel-db" 
                                                style="display: none; padding: 5px 8px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #6c757d; background: #f8f9fa; border-radius: 5px;">
                        <i class="fas fa-database" style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;"></i>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">Belum Ada Data Tersimpan</div>
                        <div style="font-size: 13px;">Tambahkan record vital sign, data akan tersimpan otomatis saat submit form</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Table Input Baru (Belum Disimpan) -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-edit"></i> Data Input Baru (Belum Disimpan)
                    </h3>
                    <div style="overflow-x: auto; max-height: 350px; overflow-y: auto;">
                        <table id="vital_table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; z-index: 1;">
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Waktu</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Resp</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Nadi</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">TD</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">FIO2</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">SPO2</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="vital_tbody">
                                <tr>
                                    <td colspan="7" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                                        <i class="fas fa-info-circle"></i> Belum ada data. Silakan tambah record baru.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden input untuk vital signs data -->
        <input type="hidden" name="vital_signs_data" id="vital_signs_data" value="[]">
        
        <!-- ========== END VITAL SIGN SECTION ========== -->

        <h2 style="margin-top: 30px;">Waktu Prosedur Anestesi & Pembedahan</h2>
        <div class="time-form" style="margin-top:20px;">
          <!-- COMMENTED OUT - Mulai Anestesi sampai Keterangan -->
          <!-- <div><label>Mulai Anestesi</label><input type="time" name="mulai_anestesi" value="<?= htmlspecialchars($catatan['mulai_anestesi'] ?? '') ?>"></div> -->
          <!-- <div><label>Selesai Anestesi</label><input type="time" name="selesai_anestesi" value="<?= htmlspecialchars($catatan['selesai_anestesi'] ?? '') ?>"></div> -->
          <!-- <div><label>Mulai Pembedahan</label><input type="time" name="mulai_pembedahan" value="<?= htmlspecialchars($catatan['mulai_pembedahan'] ?? '') ?>"></div> -->
          <!-- <div><label>Selesai Pembedahan</label><input type="time" name="selesai_pembedahan" value="<?= htmlspecialchars($catatan['selesai_pembedahan'] ?? '') ?>"></div> -->
          <!-- <div class="full"><label>Keterangan</label><input type="text" name="keterangan_waktu" value="<?= htmlspecialchars($catatan['keterangan_waktu'] ?? '') ?>"></div> -->
          
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
              <?php
              $current_perawat_menyerahkan = $catatan['perawat_menyerahkan'] ?? '';
              $is_other_perawat_menyerahkan = !empty($current_perawat_menyerahkan) && !in_array($current_perawat_menyerahkan, array_column($perawat_list, 'nama_perawat'));
              ?>
              <select id="perawat_menyerahkan_select" name="perawat_menyerahkan_select" onchange="toggleInput('perawat_menyerahkan')" required>
                <option value="">- Pilih Perawat Menyerahkan -</option>
                <?php
                foreach ($perawat_list as $perawat) {
                    $selected = ($current_perawat_menyerahkan == $perawat['nama_perawat']) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                }
                ?>
                <option value="lainnya" <?= $is_other_perawat_menyerahkan ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
              </select>
              <div id="perawat_menyerahkan_input_container" style="display: <?= $is_other_perawat_menyerahkan ? 'block' : 'none' ?>; margin-top: 5px;">
                <input type="text" id="perawat_menyerahkan_input" name="perawat_menyerahkan_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_menyerahkan ? htmlspecialchars($current_perawat_menyerahkan) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              <input type="hidden" id="perawat_menyerahkan" name="perawat_menyerahkan" value="<?= htmlspecialchars($current_perawat_menyerahkan) ?>">
            </td>
            <td>
              <?php
              $current_perawat_menerima = $catatan['perawat_menerima'] ?? '';
              $is_other_perawat_menerima = !empty($current_perawat_menerima) && !in_array($current_perawat_menerima, array_column($perawat_list, 'nama_perawat'));
              ?>
              <select id="perawat_menerima_select" name="perawat_menerima_select" onchange="toggleInput('perawat_menerima')" required>
                <option value="">- Pilih Perawat Menerima -</option>
                <?php
                foreach ($perawat_list as $perawat) {
                    $selected = ($current_perawat_menerima == $perawat['nama_perawat']) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($perawat['nama_perawat']) . "\" $selected>" . htmlspecialchars($perawat['nama_perawat']) . "</option>";
                }
                ?>
                <option value="lainnya" <?= $is_other_perawat_menerima ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
              </select>
              <div id="perawat_menerima_input_container" style="display: <?= $is_other_perawat_menerima ? 'block' : 'none' ?>; margin-top: 5px;">
                <input type="text" id="perawat_menerima_input" name="perawat_menerima_input" placeholder="Nama Perawat Lainnya" value="<?= $is_other_perawat_menerima ? htmlspecialchars($current_perawat_menerima) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              <input type="hidden" id="perawat_menerima" name="perawat_menerima" value="<?= htmlspecialchars($current_perawat_menerima) ?>">
            </td>
            <td>
              <?php
              $current_dokter_anestesi_ttd = $catatan['dokter_anestesi_ttd'] ?? '';
              $is_other_dokter_anestesi_ttd = !empty($current_dokter_anestesi_ttd) && !in_array($current_dokter_anestesi_ttd, array_column($dokter_list, 'nama_dokter'));
              ?>
              <select id="dokter_anestesi_ttd_select" name="dokter_anestesi_ttd_select" onchange="toggleInput('dokter_anestesi_ttd')" required>
                <option value="">- Pilih Dokter Anestesi -</option>
                <?php
                foreach ($dokter_list as $dokter) {
                    $selected = ($current_dokter_anestesi_ttd == $dokter['nama_dokter']) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($dokter['nama_dokter']) . "\" $selected>" . htmlspecialchars($dokter['nama_dokter']) . "</option>";
                }
                ?>
                <option value="lainnya" <?= $is_other_dokter_anestesi_ttd ? 'selected' : '' ?>>Lainnya (Input Manual)</option>
              </select>
              <div id="dokter_anestesi_ttd_input_container" style="display: <?= $is_other_dokter_anestesi_ttd ? 'block' : 'none' ?>; margin-top: 5px;">
                <input type="text" id="dokter_anestesi_ttd_input" name="dokter_anestesi_ttd_input" placeholder="Nama Dokter Lainnya" value="<?= $is_other_dokter_anestesi_ttd ? htmlspecialchars($current_dokter_anestesi_ttd) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              <input type="hidden" id="dokter_anestesi_ttd" name="dokter_anestesi_ttd" value="<?= htmlspecialchars($current_dokter_anestesi_ttd) ?>">
            </td>
          </tr>
        <table>
  1570→
  1571→        <div class="form-actions">
  1572→          <!-- Tombol aksi utama dipindahkan ke Speed Dial -->
  1573→        </div>

  <!-- Speed Dial: Simpan, Cetak PDF, Kembali -->
  <div data-dial-init class="fixed right-6 bottom-6 group">
      <div id="speed-dial-menu-catatan-sedasi" class="flex flex-col w-32 justify-end hidden mb-4 space-y-2 bg-neutral-primary-medium border border-default-medium rounded-base shadow-xs">
          <ul class="p-2 text-sm text-body font-medium">
              <li>
                  <a href="#" onclick="document.getElementById('formCatatanSedasi').submit(); return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                      <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
                      <span class="text-sm font-medium">Simpan</span>
                  </a>
              </li>
              <li>
                  <a href="/process/pdf/pdf-catatan-sedasi.php?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" target="_blank" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
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
      <button type="button" data-dial-toggle="speed-dial-menu-catatan-sedasi" aria-controls="speed-dial-menu-catatan-sedasi" aria-expanded="false" class="flex items-center justify-center ml-auto text-white bg-brand rounded-base w-14 h-14 hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium focus:outline-none">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
          <span class="sr-only">Open actions menu</span>
      </button>
  </div>
  <script src="/assets/js/autosave.js"></script>
  <script>
// Debug log dihapus - form sudah stabil

// Prevent accidental form submission
let formSubmitted = false;

// Validasi form sebelum submit
document.getElementById('formCatatanSedasi').addEventListener('submit', function(e) {
  if (formSubmitted) {
    e.preventDefault();
    return false;
  }
  
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
    return false;
  }
  
  // IMPORTANT: Simpan data vital sign ke hidden field sebelum submit
  if (typeof vitalSignsArray !== 'undefined' && vitalSignsArray.length > 0) {
    const vitalSignData = JSON.stringify({
      no_rawat: '<?= $no_rawat ?>',
      kode_paket: '<?= $kode_paket ?>',
      tanggal: '<?= $tanggal ?>',
      jam_mulai: '<?= $jam_mulai ?>',
      vital_signs: vitalSignsArray,
      mode: 'append'
    });
    document.getElementById('vital_sign_data').value = vitalSignData;
    console.log('✅ Vital sign data akan disimpan bersamaan dengan form:', vitalSignsArray.length, 'records');
    console.log('📋 Data:', vitalSignData);
  } else {
    console.log('ℹ️ Tidak ada data vital sign baru untuk disimpan');
  }
  
  formSubmitted = true;
  // Clear autosave data setelah submit
  localStorage.removeItem('autosave_formCatatanSedasi');
  
  return true;
});

// Toggle untuk Lain-lain Posisi
document.addEventListener('DOMContentLoaded', function() {
  function toggleLainLainPosisi() {
    var checkboxes = document.querySelectorAll('.posisi-checkbox');
    var container = document.getElementById('lain_lain_posisi_container');
    var inputField = document.getElementById('lain_lain_posisi');
    var isChecked = false;
    
    checkboxes.forEach(function(checkbox) {
      if (checkbox.value === 'Lain-lain :' && checkbox.checked) {
        isChecked = true;
      }
    });
    
    if (container) {
      container.style.display = isChecked ? 'block' : 'none';
    }
    
    // Hapus value input jika checkbox di-uncheck
    if (!isChecked && inputField) {
      inputField.value = '';
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
  
  // Cek apakah data sudah ada di database (mode EDIT)
  const hasExistingData = <?= json_encode(isset($catatan['id']) && !empty($catatan['id'])) ?>;
  
  if (hasExistingData) {
    // Mode EDIT: Clear localStorage agar tidak override data dari database
    localStorage.removeItem('form_formCatatanSedasi');
  }
  
  // AutoSave disabled - form sudah stabil
});

// ==================== VITAL SIGN MANAGEMENT ====================
let vitalSignsArray = []; // Data input baru (belum disimpan)
let dbVitalSigns = <?= $vital_data_json ?>; // Data dari database
let vitalChart = null;

// ==================== WAKTU OTOMATIS CONFIG ====================
let waktuMulai = null; // Waktu mulai monitoring (HH:MM:SS)
let intervalJam = 0; // Default 0 jam
let intervalMenit = 3; // Default 3 menit
let intervalDetik = 0; // Default 0 detik
let recordCount = 0; // Counter untuk record ke berapa

// Set current time
function setCurrentTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;
    document.getElementById('vs_jam').value = timeString;
}

// Hitung waktu berikutnya berdasarkan waktu mulai + (interval × record count)
function hitungWaktuBerikutnya() {
    if (!waktuMulai) {
        // Jika belum set waktu mulai, gunakan waktu sekarang
        setCurrentTime();
        return;
    }
    
    // Parse waktu mulai
    const [hours, minutes, seconds] = waktuMulai.split(':').map(Number);
    
    // Hitung total detik untuk interval
    const totalIntervalDetik = (intervalJam * 3600) + (intervalMenit * 60) + intervalDetik;
    
    // Hitung total detik yang harus ditambahkan (interval × recordCount)
    const tambahDetik = totalIntervalDetik * recordCount;
    
    // Buat Date object dari waktu mulai
    const waktu = new Date();
    waktu.setHours(hours, minutes, seconds || 0);
    
    // Tambahkan detik
    waktu.setSeconds(waktu.getSeconds() + tambahDetik);
    
    // Format kembali ke HH:MM:SS
    const newHours = String(waktu.getHours()).padStart(2, '0');
    const newMinutes = String(waktu.getMinutes()).padStart(2, '0');
    const newSeconds = String(waktu.getSeconds()).padStart(2, '0');
    const newTimeString = `${newHours}:${newMinutes}:${newSeconds}`;
    
    document.getElementById('vs_jam').value = newTimeString;
    console.log(`⏰ Waktu ke-${recordCount + 1}: ${newTimeString} (Mulai: ${waktuMulai}, Interval: ${intervalJam}h ${intervalMenit}m ${intervalDetik}s)`);
}

// Initialize Chart.js
function initVitalChart() {
    try {
        const canvas = document.getElementById("vitalChart");
        if (!canvas) {
            console.error('❌ Canvas element "vitalChart" not found!');
            return;
        }
        
        const ctx = canvas.getContext("2d");
        if (!ctx) {
            console.error('❌ Cannot get 2D context from canvas!');
            return;
        }
        
        console.log('📊 Initializing Chart.js...');
        vitalChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [
                { 
                    label: "Respirasi (R)", 
                    data: [], 
                    borderColor: "#3498db", 
                    backgroundColor: "rgba(52, 152, 219, 0.1)",
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                { 
                    label: "Nadi (N)", 
                    data: [], 
                    borderColor: "#ff9800", 
                    backgroundColor: "rgba(255, 152, 0, 0.1)",
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                { 
                    label: "Tekanan Darah (Sistol)", 
                    data: [], 
                    borderColor: "#e74c3c", 
                    backgroundColor: "rgba(231, 76, 60, 0.1)",
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                { 
                    label: "FIO2", 
                    data: [], 
                    borderColor: "#9c27b0", 
                    backgroundColor: "rgba(156, 39, 176, 0.1)",
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                { 
                    label: "SPO2", 
                    data: [], 
                    borderColor: "#2ecc71", 
                    backgroundColor: "rgba(46, 204, 113, 0.1)",
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: "bottom",
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Waktu'
                    }
                }
            }
        }
    });
        
        console.log('✅ Chart initialized successfully!');
    } catch (error) {
        console.error('❌ Error initializing chart:', error);
    }
}

// Update chart dengan data
function updateVitalChart() {
    if (!vitalChart) return;
    
    // Gabungkan data dari database dan input baru
    const allData = [...dbVitalSigns, ...vitalSignsArray];
    
    // Normalize data structure (database vs input baru)
    const normalizedData = allData.map(d => {
        // Jika dari database (ada field waktu)
        if (d.waktu) {
            return {
                waktu: d.waktu,
                respirasi: parseInt(d.respirasi),
                nadi: parseInt(d.nadi),
                sistol: parseInt(d.td_sistolik),
                fio2: parseInt(d.fio2),
                spo2: parseInt(d.spo2)
            };
        }
        // Jika dari input baru (ada field jam)
        else {
            return {
                waktu: d.jam,
                respirasi: parseInt(d.respirasi),
                nadi: parseInt(d.nadi),
                sistol: parseInt(d.sistol),
                fio2: parseInt(d.fio2),
                spo2: parseInt(d.spo2)
            };
        }
    });
    
    // Sort berdasarkan waktu (ascending)
    normalizedData.sort((a, b) => {
        const timeA = a.waktu.includes(' ') ? a.waktu.split(' ')[1] : a.waktu;
        const timeB = b.waktu.includes(' ') ? b.waktu.split(' ')[1] : b.waktu;
        return timeA.localeCompare(timeB);
    });
    
    const labels = normalizedData.map(d => {
        // Extract hanya jam (HH:MM:SS) jika format datetime
        return d.waktu.includes(' ') ? d.waktu.split(' ')[1] : d.waktu;
    });
    const respirasi = normalizedData.map(d => d.respirasi);
    const nadi = normalizedData.map(d => d.nadi);
    const sistol = normalizedData.map(d => d.sistol);
    const fio2 = normalizedData.map(d => d.fio2);
    const spo2 = normalizedData.map(d => d.spo2);
    
    vitalChart.data.labels = labels;
    vitalChart.data.datasets[0].data = respirasi;
    vitalChart.data.datasets[1].data = nadi;
    vitalChart.data.datasets[2].data = sistol;
    vitalChart.data.datasets[3].data = fio2;
    vitalChart.data.datasets[4].data = spo2;
    vitalChart.update();
    
    console.log('📊 Chart updated with', normalizedData.length, 'records (sorted by time)');
}

// Update table
function updateVitalTable() {
    const tbody = document.getElementById('vital_tbody');
    
    if (vitalSignsArray.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                    <i class="fas fa-info-circle"></i> Belum ada data. Silakan tambah record baru.
                </td>
            </tr>
        `;
        return;
    }
    
    // Sort array berdasarkan waktu (ascending) dengan menyimpan index asli
    const sortedArray = vitalSignsArray.map((record, originalIndex) => ({
        ...record,
        originalIndex: originalIndex
    })).sort((a, b) => {
        return a.jam.localeCompare(b.jam);
    });
    
    tbody.innerHTML = '';
    sortedArray.forEach((record, displayIndex) => {
        const row = document.createElement('tr');
        row.style.background = displayIndex % 2 === 0 ? '#ffffff' : '#f8f9fa';
        row.innerHTML = `
            <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600;">
                ${record.jam}
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                    ${record.respirasi}
                </span>
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                    ${record.nadi} bpm
                </span>
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <span style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                    ${record.sistol}/${record.diastol}
                </span>
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #7b1fa2; font-weight: 600; font-size: 12px;">
                    ${record.fio2}%
                </span>
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <span style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                    ${record.spo2}%
                </span>
            </td>
            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                <button onclick="deleteVitalRecord(${record.originalIndex})" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Update hidden input (DEPRECATED - tidak digunakan lagi karena auto-save saat submit)
function updateVitalHiddenInput() {
    // Function ini tidak digunakan lagi
    // Data vital sign akan di-save otomatis saat form submit
}

// Delete record
function deleteVitalRecord(index) {
    if (confirm('Hapus record ini?')) {
        vitalSignsArray.splice(index, 1);
        updateVitalTable();
        updateVitalChart();
        updateVitalHiddenInput();
    }
}

// Add vital sign record
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart
    initVitalChart();
    updateVitalChart(); // Load data dari database
    
    // Set initial time
    setCurrentTime();
    
    // Auto-set waktu mulai dari data terakhir (database atau input baru)
    let lastTime = null;
    
    // Cek data terakhir dari database
    if (dbVitalSigns.length > 0) {
        const lastDbRecord = dbVitalSigns[dbVitalSigns.length - 1];
        lastTime = lastDbRecord.waktu;
    }
    
    // Cek data terakhir dari input baru
    if (vitalSignsArray.length > 0) {
        const lastInputRecord = vitalSignsArray[vitalSignsArray.length - 1];
        lastTime = lastInputRecord.jam;
    }
    
    // Set waktu mulai
    if (lastTime) {
        // Ada data sebelumnya, set waktu mulai = waktu terakhir + interval (3 menit)
        const timeStr = lastTime.includes(' ') ? lastTime.split(' ')[1] : lastTime;
        const [hours, minutes, seconds] = timeStr.split(':').map(Number);
        
        // Tambahkan interval 3 menit
        const date = new Date();
        date.setHours(hours, minutes, seconds || 0);
        date.setMinutes(date.getMinutes() + 3); // Tambah 3 menit
        
        document.getElementById('vs_waktu_jam').value = String(date.getHours()).padStart(2, '0');
        document.getElementById('vs_waktu_menit').value = String(date.getMinutes()).padStart(2, '0');
        document.getElementById('vs_waktu_detik').value = String(date.getSeconds()).padStart(2, '0');
        
        console.log(`⏰ Waktu mulai auto-set dari data terakhir: ${timeStr} + 3 menit = ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`);
    } else {
        // Tidak ada data sebelumnya, set waktu mulai = waktu sekarang
        const now = new Date();
        document.getElementById('vs_waktu_jam').value = String(now.getHours()).padStart(2, '0');
        document.getElementById('vs_waktu_menit').value = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('vs_waktu_detik').value = String(now.getSeconds()).padStart(2, '0');
        
        console.log('⏰ Waktu mulai set ke waktu sekarang (tidak ada data sebelumnya)');
    }
    
    // Event listeners untuk button waktu
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize chart
        initVitalChart();
        
        // Button Set Now (isi waktu sekarang)
        const btnSetNow = document.getElementById('btn_set_now');
        if (btnSetNow) {
            btnSetNow.addEventListener('click', function() {
        const now = new Date();
        document.getElementById('vs_waktu_jam').value = String(now.getHours()).padStart(2, '0');
        document.getElementById('vs_waktu_menit').value = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('vs_waktu_detik').value = String(now.getSeconds()).padStart(2, '0');
        
        // Visual feedback
        this.innerHTML = '<i class="fas fa-check"></i> Diset!';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-clock"></i> Set Now';
        }, 1000);
            });
        }
        
        // Button Set Waktu Config
        const btnSetWaktuConfig = document.getElementById('btn_set_waktu_config');
        if (btnSetWaktuConfig) {
            btnSetWaktuConfig.addEventListener('click', function() {
        // Waktu Mulai
        const jam = document.getElementById('vs_waktu_jam').value;
        const menitWaktu = document.getElementById('vs_waktu_menit').value;
        const detikWaktu = document.getElementById('vs_waktu_detik').value;
        
        // Interval
        const intervalJamVal = document.getElementById('vs_interval_jam').value;
        const intervalMenitVal = document.getElementById('vs_interval_menit').value;
        const intervalDetikVal = document.getElementById('vs_interval_detik').value;
        
        // Validasi Waktu Mulai
        if (!jam || !menitWaktu || detikWaktu === '') {
            alert('⚠️ Waktu mulai harus diisi lengkap (HH:MM:SS)!');
            return;
        }
        
        // Validasi Interval
        if (intervalJamVal === '' || intervalMenitVal === '' || intervalDetikVal === '') {
            alert('⚠️ Interval harus diisi lengkap (HH:MM:SS)!');
            return;
        }
        
        // Parse values
        const jamInt = parseInt(jam);
        const menitInt = parseInt(menitWaktu);
        const detikInt = parseInt(detikWaktu);
        const intervalJamInt = parseInt(intervalJamVal);
        const intervalMenitInt = parseInt(intervalMenitVal);
        const intervalDetikInt = parseInt(intervalDetikVal);
        
        // Validasi range Waktu Mulai
        if (jamInt < 0 || jamInt > 23) {
            alert('⚠️ Jam waktu mulai harus antara 00-23!');
            return;
        }
        if (menitInt < 0 || menitInt > 59) {
            alert('⚠️ Menit waktu mulai harus antara 00-59!');
            return;
        }
        if (detikInt < 0 || detikInt > 59) {
            alert('⚠️ Detik waktu mulai harus antara 00-59!');
            return;
        }
        
        // Validasi range Interval
        if (intervalJamInt < 0 || intervalJamInt > 23) {
            alert('⚠️ Jam interval harus antara 00-23!');
            return;
        }
        if (intervalMenitInt < 0 || intervalMenitInt > 59) {
            alert('⚠️ Menit interval harus antara 00-59!');
            return;
        }
        if (intervalDetikInt < 0 || intervalDetikInt > 59) {
            alert('⚠️ Detik interval harus antara 00-59!');
            return;
        }
        
        // Validasi interval tidak boleh 00:00:00
        if (intervalJamInt === 0 && intervalMenitInt === 0 && intervalDetikInt === 0) {
            alert('⚠️ Interval tidak boleh 00:00:00! Minimal 1 detik.');
            return;
        }
        
        // Format waktu
        const jamStr = String(jamInt).padStart(2, '0');
        const menitStr = String(menitInt).padStart(2, '0');
        const detikStr = String(detikInt).padStart(2, '0');
        const waktuMulaiFormatted = `${jamStr}:${menitStr}:${detikStr}`;
        
        const intervalJamStr = String(intervalJamInt).padStart(2, '0');
        const intervalMenitStr = String(intervalMenitInt).padStart(2, '0');
        const intervalDetikStr = String(intervalDetikInt).padStart(2, '0');
        const intervalFormatted = `${intervalJamStr}:${intervalMenitStr}:${intervalDetikStr}`;
        
        // Set config
        waktuMulai = waktuMulaiFormatted;
        intervalJam = intervalJamInt;
        intervalMenit = intervalMenitInt;
        intervalDetik = intervalDetikInt;
        recordCount = 0; // Reset counter
        
        // Set waktu pertama
        hitungWaktuBerikutnya();
        
        // Visual feedback
        this.innerHTML = '<i class="fas fa-check-circle"></i> Diterapkan!';
        this.style.background = '#28a745';
        this.style.color = 'white';
        
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-check-circle"></i> Terapkan';
            this.style.background = 'white';
            this.style.color = '#667eea';
        }, 2000);
        
        alert(`✅ Pengaturan waktu diterapkan!\n\n` +
              `⏰ Waktu Mulai: ${waktuMulaiFormatted}\n` +
              `⏱️ Interval: ${intervalFormatted}\n\n` +
              `Waktu akan otomatis bertambah setiap kali Anda tambah record.`);
        
        console.log(`✅ Config: Mulai=${waktuMulai}, Interval=${intervalJamInt}h ${intervalMenitInt}m ${intervalDetikInt}s`);
            });
        }
        
        // Button Manual Time
        const btnManualTime = document.getElementById('btn_manual_time');
        if (btnManualTime) {
            btnManualTime.addEventListener('click', function() {
        const newTime = prompt('Masukkan waktu manual (HH:MM:SS):', document.getElementById('vs_jam').value);
        if (newTime) {
            // Validasi format
            const timeRegex = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/;
            if (timeRegex.test(newTime)) {
                document.getElementById('vs_jam').value = newTime;
                console.log('⏰ Waktu diubah manual ke:', newTime);
            } else {
                alert('❌ Format waktu tidak valid! Gunakan format HH:MM:SS (contoh: 14:30:00)');
            }
        }
            });
        }
    });
    
    // Button add vital
    document.getElementById('btn_add_vital').addEventListener('click', function() {
        const jam = document.getElementById('vs_jam').value;
        const respirasi = document.getElementById('vs_respirasi').value;
        const nadi = document.getElementById('vs_nadi').value;
        const sistol = document.getElementById('vs_sistol').value;
        const diastol = document.getElementById('vs_diastol').value;
        const fio2 = document.getElementById('vs_fio2').value;
        const spo2 = document.getElementById('vs_spo2').value;
        
        // Validation
        if (!jam) {
            alert('⚠️ Waktu harus diisi!');
            return;
        }
        
        if (!respirasi || !nadi || !sistol || !diastol || !fio2 || !spo2) {
            alert('⚠️ Semua field harus diisi!');
            return;
        }
        
        // Create record object (gunakan key yang konsisten dengan updateVitalTable)
        const record = {
            id: Date.now(),
            jam: jam,  // Gunakan 'jam' untuk konsistensi dengan updateVitalTable
            respirasi: parseInt(respirasi),  // Gunakan 'respirasi'
            nadi: parseInt(nadi),
            sistol: parseInt(sistol),  // Pisahkan sistol
            diastol: parseInt(diastol),  // Pisahkan diastol
            fio2: parseInt(fio2),
            spo2: parseInt(spo2)
        };
        
        // Add to array
        vitalSignsArray.push(record);
        
        console.log('✅ Vital sign added to array:', record);
        console.log('📊 Total records in array:', vitalSignsArray.length);
        
        // Update UI
        updateVitalTable();
        updateVitalChart();
        updateVitalHiddenInput();
        
        // Increment record count untuk waktu otomatis
        recordCount++;
        
        // Hitung waktu berikutnya (otomatis)
        hitungWaktuBerikutnya();
        
        // TIDAK clear form - biarkan data tetap terisi untuk input berikutnya
        // User bisa langsung edit jika ada perubahan, atau langsung tambah jika sama
        
        // Focus ke field pertama untuk kemudahan edit
        document.getElementById('vs_respirasi').focus();
        document.getElementById('vs_respirasi').select();
        
        // Show success message
        const nextTime = document.getElementById('vs_jam').value;
        console.log(`✅ Record ditambahkan. Waktu berikutnya: ${nextTime}`);
        
        // Visual feedback - highlight button
        this.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
        this.style.background = '#28a745';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-plus"></i> Tambah Record';
            this.style.background = '#007bff';
        }, 1000);
    });
    
    // Button Clear Form
    document.getElementById('btn_clear_form').addEventListener('click', function() {
        if (confirm('🗑️ Kosongkan semua input form?')) {
            document.getElementById('vs_respirasi').value = '';
            document.getElementById('vs_nadi').value = '';
            document.getElementById('vs_sistol').value = '';
            document.getElementById('vs_diastol').value = '';
            document.getElementById('vs_fio2').value = '';
            document.getElementById('vs_spo2').value = '';
            
            // Focus ke field pertama
            document.getElementById('vs_respirasi').focus();
            
            console.log('🗑️ Form cleared');
            
            // Visual feedback
            this.innerHTML = '<i class="fas fa-check"></i> Cleared!';
            this.style.background = '#28a745';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-eraser"></i> Clear';
                this.style.background = '#6c757d';
            }, 1000);
        }
    });
    
    // Button hover effect - Add
    const btnAdd = document.getElementById('btn_add_vital');
    btnAdd.addEventListener('mouseenter', function() {
        this.style.background = '#0056b3';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 8px rgba(0,123,255,0.3)';
    });
    
    btnAdd.addEventListener('mouseleave', function() {
        this.style.background = '#007bff';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });
});

console.log('✅ Vital Sign module initialized');

// ==================== DELETE DATABASE RECORD ====================
function deleteDbRecord(index, recordId) {
    const row = document.getElementById(`db-row-${index}`);
    const waktu = row.querySelector('.view-mode').textContent;
    
    // Konfirmasi
    if (!confirm(`🗑️ Hapus record vital sign?\n\nWaktu: ${waktu}\n\nData akan dihapus permanent dari database!`)) {
        return;
    }
    
    // Disable semua tombol di row
    const buttons = row.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading di tombol delete
    const btnDelete = row.querySelector('.btn-delete-db');
    btnDelete.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnDelete.style.background = '#6c757d';
    
    // Kirim request delete ke server
    fetch('../process/process-delete-vital-sign.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: recordId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fade out animation
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            
            setTimeout(() => {
                // Remove dari dbVitalSigns array
                dbVitalSigns.splice(index, 1);
                
                // Update chart
                updateVitalChart();
                
                // Reload page untuk refresh numbering
                location.reload();
            }, 300);
            
            console.log(`✅ Record #${index + 1} deleted from database`);
        } else {
            alert('❌ Gagal menghapus: ' + (data.message || 'Unknown error'));
            
            // Enable button kembali
            buttons.forEach(btn => btn.disabled = false);
            btnDelete.innerHTML = '<i class="fas fa-trash"></i>';
            btnDelete.style.background = '#dc3545';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat menghapus data!');
        
        // Enable button kembali
        buttons.forEach(btn => btn.disabled = false);
        btnDelete.innerHTML = '<i class="fas fa-trash"></i>';
        btnDelete.style.background = '#dc3545';
    });
}

// ==================== EDIT DATABASE RECORD ====================
function editDbRecord(index) {
    const row = document.getElementById(`db-row-${index}`);
    
    // Hide view mode, show edit mode
    row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'inline-block');
    
    // Hide Edit & Delete buttons, show Save & Cancel buttons
    row.querySelector('.btn-edit-db').style.display = 'none';
    row.querySelector('.btn-delete-db').style.display = 'none';
    row.querySelector('.btn-save-db').style.display = 'inline-block';
    row.querySelector('.btn-cancel-db').style.display = 'inline-block';
    
    // Highlight row
    row.style.background = '#fff3cd';
    row.style.border = '2px solid #ffc107';
    
    console.log(`✏️ Edit mode activated for record #${index + 1}`);
}

function cancelDbEdit(index) {
    const row = document.getElementById(`db-row-${index}`);
    
    // Show view mode, hide edit mode
    row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'inline-block');
    row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
    
    // Show Edit & Delete buttons, hide Save & Cancel buttons
    row.querySelector('.btn-edit-db').style.display = 'inline-block';
    row.querySelector('.btn-delete-db').style.display = 'inline-block';
    row.querySelector('.btn-save-db').style.display = 'none';
    row.querySelector('.btn-cancel-db').style.display = 'none';
    
    // Remove highlight
    row.style.background = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
    row.style.border = 'none';
    
    console.log(`❌ Edit cancelled for record #${index + 1}`);
}

function saveDbRecord(index) {
    const row = document.getElementById(`db-row-${index}`);
    const cells = row.querySelectorAll('td');
    
    // Get edited values
    const waktu = cells[1].querySelector('.edit-mode').value;
    const respirasi = parseInt(cells[2].querySelector('.edit-mode').value);
    const nadi = parseInt(cells[3].querySelector('.edit-mode').value);
    const tdInputs = cells[4].querySelectorAll('.edit-mode input');
    const sistol = parseInt(tdInputs[0].value);
    const diastol = parseInt(tdInputs[1].value);
    const fio2 = parseInt(cells[5].querySelector('.edit-mode').value);
    const spo2 = parseInt(cells[6].querySelector('.edit-mode').value);
    
    // Validasi
    if (!waktu || !respirasi || !nadi || !sistol || !diastol || !fio2 || !spo2) {
        alert('⚠️ Semua field harus diisi!');
        return;
    }
    
    // Validasi format waktu
    const timeRegex = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/;
    if (!timeRegex.test(waktu)) {
        alert('⚠️ Format waktu tidak valid! Gunakan format HH:MM:SS');
        return;
    }
    
    // Konfirmasi
    if (!confirm(`💾 Simpan perubahan untuk record #${index + 1}?`)) {
        return;
    }
    
    // Update dbVitalSigns array
    dbVitalSigns[index] = {
        waktu: waktu,
        respirasi: respirasi,
        nadi: nadi,
        td_sistolik: sistol,
        td_diastolik: diastol,
        fio2: fio2,
        spo2: spo2
    };
    
    // Update view mode dengan nilai baru
    cells[1].querySelector('.view-mode').textContent = waktu;
    cells[2].querySelector('.view-mode').textContent = respirasi;
    cells[3].querySelector('.view-mode').textContent = nadi + ' bpm';
    cells[4].querySelector('.view-mode').textContent = sistol + '/' + diastol;
    cells[5].querySelector('.view-mode').textContent = fio2 + '%';
    cells[6].querySelector('.view-mode').textContent = spo2 + '%';
    
    // Show view mode, hide edit mode
    row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'inline-block');
    row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
    
    // Show Edit & Delete buttons, hide Save & Cancel buttons
    row.querySelector('.btn-edit-db').style.display = 'inline-block';
    row.querySelector('.btn-delete-db').style.display = 'inline-block';
    row.querySelector('.btn-save-db').style.display = 'none';
    row.querySelector('.btn-cancel-db').style.display = 'none';
    
    // Remove highlight
    row.style.background = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
    row.style.border = 'none';
    
    // Update chart
    updateVitalChart();
    
    alert(`✅ Perubahan berhasil disimpan!\n\n⚠️ Jangan lupa klik "Simpan ke Database" untuk menyimpan ke database.`);
    console.log(`✅ Record #${index + 1} updated in memory`);
}

// ==================== UNCHECK RADIO BUTTON ====================
function uncheckRadio(radioName) {
    const radios = document.getElementsByName(radioName);
    radios.forEach(radio => {
        radio.checked = false;
    });
    console.log(`✅ Radio button "${radioName}" cleared`);
}

// ==================== TOGGLE ANESTESI TYPE ====================
let currentAnestesiType = null; // Track pilihan saat ini

function toggleAnestesiType(type) {
    const sectionUmum = document.getElementById('section-anestesi-umum');
    const sectionRegional = document.getElementById('section-anestesi-regional');
    const labelUmum = document.getElementById('label-umum');
    const labelRegional = document.getElementById('label-regional');
    const labelKeduanya = document.getElementById('label-keduanya');
    
    if (type === 'umum') {
        // Cek apakah ada data di Regional
        if (hasDataInRegional() && currentAnestesiType !== 'umum') {
            if (!confirm('⚠️ Data Anestesi Regional akan dihapus. Lanjutkan?')) {
                // Batalkan perubahan, kembalikan ke pilihan sebelumnya
                restorePreviousSelection();
                return;
            }
        }
        
        // Tampilkan Anestesi Umum
        sectionUmum.style.display = 'block';
        labelUmum.style.background = '#007bff';
        labelUmum.querySelector('span').style.color = 'white';
        
        // Sembunyikan Anestesi Regional
        sectionRegional.style.display = 'none';
        labelRegional.style.background = 'white';
        labelRegional.querySelector('span').style.color = '#28a745';
        
        // Reset label keduanya
        labelKeduanya.style.background = 'white';
        labelKeduanya.querySelector('span').style.color = '#ff9800';
        
        // Clear semua input di Anestesi Regional
        clearAnestesiRegional();
        
        currentAnestesiType = 'umum';
        console.log('✅ Anestesi Umum ditampilkan, Regional disembunyikan');
        
    } else if (type === 'regional') {
        // Cek apakah ada data di Umum
        if (hasDataInUmum() && currentAnestesiType !== 'regional') {
            if (!confirm('⚠️ Data Anestesi Umum akan dihapus. Lanjutkan?')) {
                // Batalkan perubahan, kembalikan ke pilihan sebelumnya
                restorePreviousSelection();
                return;
            }
        }
        
        // Tampilkan Anestesi Regional
        sectionRegional.style.display = 'block';
        labelRegional.style.background = '#28a745';
        labelRegional.querySelector('span').style.color = 'white';
        
        // Sembunyikan Anestesi Umum
        sectionUmum.style.display = 'none';
        labelUmum.style.background = 'white';
        labelUmum.querySelector('span').style.color = '#007bff';
        
        // Reset label keduanya
        labelKeduanya.style.background = 'white';
        labelKeduanya.querySelector('span').style.color = '#ff9800';
        
        // Clear semua input di Anestesi Umum
        clearAnestesiUmum();
        
        currentAnestesiType = 'regional';
        console.log('✅ Anestesi Regional ditampilkan, Umum disembunyikan');
        
    } else if (type === 'keduanya') {
        // Tampilkan KEDUA section
        sectionUmum.style.display = 'block';
        sectionRegional.style.display = 'block';
        
        // Highlight label keduanya
        labelKeduanya.style.background = '#ff9800';
        labelKeduanya.querySelector('span').style.color = 'white';
        
        // Reset label lainnya
        labelUmum.style.background = 'white';
        labelUmum.querySelector('span').style.color = '#007bff';
        labelRegional.style.background = 'white';
        labelRegional.querySelector('span').style.color = '#28a745';
        
        // TIDAK clear data (biarkan user isi keduanya)
        
        currentAnestesiType = 'keduanya';
        console.log('✅ Kedua section ditampilkan (Umum & Regional)');
    }
}

// Cek apakah ada data di Anestesi Umum
function hasDataInUmum() {
    const section = document.getElementById('section-anestesi-umum');
    
    // Cek checkbox yang checked
    const hasCheckedCheckbox = section.querySelector('input[type="checkbox"]:checked') !== null;
    
    // Cek radio yang checked
    const hasCheckedRadio = section.querySelector('input[type="radio"]:checked') !== null;
    
    // Cek text input yang terisi
    const hasFilledText = Array.from(section.querySelectorAll('input[type="text"]'))
        .some(input => input.value.trim() !== '');
    
    return hasCheckedCheckbox || hasCheckedRadio || hasFilledText;
}

// Cek apakah ada data di Anestesi Regional
function hasDataInRegional() {
    const section = document.getElementById('section-anestesi-regional');
    
    // Cek checkbox yang checked
    const hasCheckedCheckbox = section.querySelector('input[type="checkbox"]:checked') !== null;
    
    // Cek text input yang terisi
    const hasFilledText = Array.from(section.querySelectorAll('input[type="text"]'))
        .some(input => input.value.trim() !== '');
    
    return hasCheckedCheckbox || hasFilledText;
}

// Kembalikan ke pilihan sebelumnya jika user cancel
function restorePreviousSelection() {
    if (currentAnestesiType === 'umum') {
        document.getElementById('radio-umum').checked = true;
    } else if (currentAnestesiType === 'regional') {
        document.getElementById('radio-regional').checked = true;
    } else if (currentAnestesiType === 'keduanya') {
        document.getElementById('radio-keduanya').checked = true;
    } else {
        // Uncheck semua jika belum ada pilihan
        document.querySelectorAll('input[name="jenis_anestesi"]').forEach(radio => {
            radio.checked = false;
        });
    }
    console.log('❌ Perubahan dibatalkan, kembali ke pilihan sebelumnya');
}

// Clear semua input Anestesi Umum
function clearAnestesiUmum() {
    const section = document.getElementById('section-anestesi-umum');
    
    // Clear checkboxes
    section.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Clear radio buttons
    section.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
    
    // Clear text inputs
    section.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
    
    console.log('🗑️ Anestesi Umum inputs cleared');
}

// Clear semua input Anestesi Regional
function clearAnestesiRegional() {
    const section = document.getElementById('section-anestesi-regional');
    
    // Clear checkboxes
    section.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Clear text inputs
    section.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
    
    console.log('🗑️ Anestesi Regional inputs cleared');
}

// Initialize on page load - detect which type is filled
document.addEventListener('DOMContentLoaded', function() {
    // Check if any Anestesi Umum field is filled
    const umumFilled = document.querySelector('#section-anestesi-umum input[type="checkbox"]:checked') ||
                       document.querySelector('#section-anestesi-umum input[type="radio"]:checked') ||
                       Array.from(document.querySelectorAll('#section-anestesi-umum input[type="text"]')).some(input => input.value.trim() !== '');
    
    // Check if any Anestesi Regional field is filled
    const regionalFilled = document.querySelector('#section-anestesi-regional input[type="checkbox"]:checked') ||
                          Array.from(document.querySelectorAll('#section-anestesi-regional input[type="text"]')).some(input => input.value.trim() !== '');
    
    if (umumFilled && regionalFilled) {
        // Kedua section terisi
        document.getElementById('radio-keduanya').checked = true;
        currentAnestesiType = 'keduanya'; // Set current type
        toggleAnestesiType('keduanya');
        console.log('📋 Auto-detected: Keduanya (Umum & Regional)');
    } else if (umumFilled) {
        // Hanya Umum yang terisi
        document.getElementById('radio-umum').checked = true;
        currentAnestesiType = 'umum'; // Set current type
        toggleAnestesiType('umum');
        console.log('📋 Auto-detected: Anestesi Umum');
    } else if (regionalFilled) {
        // Hanya Regional yang terisi
        document.getElementById('radio-regional').checked = true;
        currentAnestesiType = 'regional'; // Set current type
        toggleAnestesiType('regional');
        console.log('📋 Auto-detected: Anestesi Regional');
    } else {
        // Default: tidak ada yang aktif
        currentAnestesiType = null; // No selection
        console.log('⚠️ Belum ada jenis anestesi yang dipilih');
    }
});

// Toggle Input untuk Dropdown dengan opsi "Lainnya"
function toggleInput(fieldName) {
    const select = document.getElementById(fieldName + '_select');
    const inputContainer = document.getElementById(fieldName + '_input_container');
    const inputField = document.getElementById(fieldName + '_input');
    const hiddenField = document.getElementById(fieldName);
    
    if (select.value === 'lainnya') {
        inputContainer.style.display = 'block';
        inputField.required = true;
        select.required = false;
        hiddenField.value = inputField.value;
    } else {
        inputContainer.style.display = 'none';
        inputField.required = false;
        select.required = true;
        hiddenField.value = select.value;
    }
}

// Initialize toggle untuk semua dropdown saat page load
document.addEventListener('DOMContentLoaded', function() {
    const fields = [
        'ruang',
        'dokter_anestesi', 
        'perawat_anestesi', 
        'dokter_bedah', 
        'perawat_bedah',
        'perawat_menyerahkan',
        'perawat_menerima',
        'dokter_anestesi_ttd'
    ];
    
    fields.forEach(function(fieldName) {
        const select = document.getElementById(fieldName + '_select');
        const inputField = document.getElementById(fieldName + '_input');
        const hiddenField = document.getElementById(fieldName);
        
        if (select && inputField && hiddenField) {
            // Event listener untuk dropdown
            select.addEventListener('change', function() {
                if (this.value !== 'lainnya') {
                    hiddenField.value = this.value;
                }
                toggleInput(fieldName);
            });
            
            // Event listener untuk input manual
            inputField.addEventListener('input', function() {
                hiddenField.value = this.value;
            });
            
            // Initialize on page load (untuk mode edit)  
            if (select.value === 'lainnya') {
                toggleInput(fieldName);
            }
        }
    });
    
});

// Fungsi global untuk set waktu vital sign ke waktu sekarang
function setCurrentTimeVitalWaktu() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    document.getElementById('vs_waktu_jam').value = parseInt(hours);
    document.getElementById('vs_waktu_menit').value = parseInt(minutes);
    document.getElementById('vs_waktu_detik').value = parseInt(seconds);
    
    // Visual feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Set!';
    button.style.background = 'rgba(40, 167, 69, 0.3)';
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = 'rgba(255,255,255,0.2)';
    }, 1500);
    
    console.log('⏰ Waktu vital sign diset ke:', `${hours}:${minutes}:${seconds}`);
}

// Fungsi global untuk set interval default (3 menit untuk sedasi)
function setDefaultInterval() {
    document.getElementById('vs_interval_jam').value = 0;
    document.getElementById('vs_interval_menit').value = 3;
    document.getElementById('vs_interval_detik').value = 0;
    
    // Visual feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Set!';
    button.style.background = 'rgba(40, 167, 69, 0.3)';
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = 'rgba(255,255,255,0.2)';
    }, 1500);
    
    console.log('⏱️ Interval diset ke default: 00:03:00');
}

// Fungsi global untuk set interval custom
function setCustomInterval() {
    const minutes = prompt('Masukkan interval dalam menit (1-60):', '3');
    if (minutes !== null && !isNaN(minutes) && minutes >= 1 && minutes <= 60) {
        document.getElementById('vs_interval_jam').value = 0;
        document.getElementById('vs_interval_menit').value = parseInt(minutes);
        document.getElementById('vs_interval_detik').value = 0;
        
        // Visual feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Set!';
        button.style.background = 'rgba(40, 167, 69, 0.3)';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = 'rgba(255,255,255,0.2)';
        }, 1500);
        
        console.log('⏱️ Interval custom diset ke:', `00:${String(minutes).padStart(2, '0')}:00`);
    } else if (minutes !== null) {
        alert('⚠️ Masukkan angka antara 1-60 menit!');
    }
}

// Variable untuk mode input
let isManualMode = false;
let manualTimeEntries = [];

// Fungsi global untuk toggle mode input
function toggleInputMode() {
    isManualMode = !isManualMode;
    
    const intervalMode = document.getElementById('intervalMode');
    const manualMode = document.getElementById('manualMode');
    const toggleSlider = document.getElementById('toggleSlider');
    const intervalLabel = document.getElementById('intervalLabel');
    const manualLabel = document.getElementById('manualLabel');
    
    if (isManualMode) {
        // Switch ke Manual Mode
        intervalMode.style.display = 'none';
        manualMode.style.display = 'block';
        toggleSlider.style.left = '28px';
        intervalLabel.style.opacity = '0.5';
        manualLabel.style.opacity = '1';
        console.log('🔄 Mode diubah ke: Manual Input');
    } else {
        // Switch ke Interval Mode
        intervalMode.style.display = 'block';
        manualMode.style.display = 'none';
        toggleSlider.style.left = '2px';
        intervalLabel.style.opacity = '1';
        manualLabel.style.opacity = '0.5';
        console.log('🔄 Mode diubah ke: Interval Input');
    }
}

// Fungsi untuk menambah entry waktu manual
function addManualTimeEntry() {
    const jamInput = document.getElementById('vs_jam');
    const currentTime = jamInput.value || getCurrentTimeString();
    
    if (currentTime && !manualTimeEntries.includes(currentTime)) {
        manualTimeEntries.push(currentTime);
        console.log('➕ Entry waktu manual ditambahkan:', currentTime);
        console.log('📋 Total entries:', manualTimeEntries.length);
        
        // Visual feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Added!';
        button.style.background = 'rgba(40, 167, 69, 0.3)';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = 'rgba(255,255,255,0.2)';
        }, 1500);
        
        // Update display
        jamInput.value = '';
    } else if (manualTimeEntries.includes(currentTime)) {
        alert('⚠️ Waktu ini sudah ada dalam daftar!');
    } else {
        alert('⚠️ Silakan masukkan waktu terlebih dahulu!');
    }
}

// Fungsi untuk clear semua entry manual
function clearManualEntries() {
    if (manualTimeEntries.length > 0) {
        const confirm = window.confirm(`🗑️ Hapus ${manualTimeEntries.length} entry waktu manual?`);
        if (confirm) {
            manualTimeEntries = [];
            document.getElementById('vs_jam').value = '';
            console.log('🗑️ Semua entry waktu manual dihapus');
            
            // Visual feedback
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Cleared!';
            button.style.background = 'rgba(40, 167, 69, 0.3)';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = 'rgba(220, 53, 69, 0.3)';
            }, 1500);
        }
    } else {
        alert('ℹ️ Tidak ada entry untuk dihapus.');
    }
}

// Helper function untuk get current time string
function getCurrentTimeString() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    return `${hours}:${minutes}:${seconds}`;
}

</script>