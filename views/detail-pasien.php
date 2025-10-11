
<?php
$page_title = "Detail Pasien";
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

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Query untuk mengambil data booking operasi dengan data pasien
$query = "SELECT b.*, p.kode_rekam_medis, p.nama, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp, p.gol_darah 
           FROM booking_operasi b 
           LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
           WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$pasien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pasien) {
    echo "Data pasien tidak ditemukan.";
    exit;
}

// Query untuk mengecek form mana yang sudah diisi
$query_persiapan = "SELECT COUNT(*) as total FROM tbl_anestesi_persiapan_operasi WHERE no_rawat = ? AND kode_paket = ?";
$stmt_persiapan = $db->prepare($query_persiapan);
$stmt_persiapan->execute([$no_rawat, $kode_paket]);
$persiapan_terisi = $stmt_persiapan->fetch(PDO::FETCH_ASSOC)['total'] > 0;

$query_keselamatan = "SELECT COUNT(*) as total FROM tbl_anestesi_keselamatan_operasi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_keselamatan = $db->prepare($query_keselamatan);
$stmt_keselamatan->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$keselamatan_terisi = $stmt_keselamatan->fetch(PDO::FETCH_ASSOC)['total'] > 0;

$query_pemulihan = "SELECT COUNT(*) as total FROM tbl_anestesi_kamar_pemulihan WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_pemulihan = $db->prepare($query_pemulihan);
$stmt_pemulihan->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$pemulihan_terisi = $stmt_pemulihan->fetch(PDO::FETCH_ASSOC)['total'] > 0;

$query_catatan = "SELECT COUNT(*) as total FROM tbl_anestesi_catatan_anestesi WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_catatan = $db->prepare($query_catatan);
$stmt_catatan->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$catatan_terisi = $stmt_catatan->fetch(PDO::FETCH_ASSOC)['total'] > 0;

$kd_dokter = $pasien['kd_dokter'];
include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['dev']) && $_GET['dev'] == 1): ?>
  <div style="position:fixed;top:10px;right:10px;z-index:999;">
    <a href="index.php?page=persiapan-operasi&no_rawat=...&kode_paket=...&tanggal=...&jam_mulai=..." class="btn btn-warning btn-sm">Bypass Persiapan</a>
    <a href="index.php?page=keselamatan-operasi&no_rawat=...&kode_paket=...&tanggal=...&jam_mulai=..." class="btn btn-warning btn-sm">Bypass Keselamatan</a>
    <a href="index.php?page=kamar-pemulihan&no_rawat=...&kode_paket=...&tanggal=...&jam_mulai=..." class="btn btn-warning btn-sm">Bypass Pemulihan</a>
  </div>
<?php endif; ?>

<div class="container">
    <!-- Info Pasien -->
    <div class="card patient-info-card">
        <div class="card-header">
            <h3><i class="fas fa-user-injured"></i> Informasi Pasien</h3>
        </div>
        <div class="card-body">
            <div class="patient-info-grid">
                <div class="info-item">
                    <label>No. Rekam Medis</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['kode_rekam_medis'] ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Nama Pasien</label>
                    <div class="info-value" style="font-weight: 600; color: #2c3e50;"><?php echo htmlspecialchars($pasien['nama'] ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>No. Rawat</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['no_rawat']); ?></div>
                </div>
                <div class="info-item">
                    <label>Kode Paket</label>
                    <div class="info-value code"><?php echo htmlspecialchars($pasien['kode_paket']); ?></div>
                </div>
                <div class="info-item">
                    <label>Tanggal Operasi</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['tanggal']); ?></div>
                </div>
                <div class="info-item">
                    <label>Jam Operasi</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['jam_mulai']); ?></div>
                </div>
                <div class="info-item">
                    <label>Dokter</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['kd_dokter']); ?></div>
                </div>
                <div class="info-item">
                    <label>Ruang OK</label>
                    <div class="info-value"><?php echo htmlspecialchars($pasien['kd_ruang_ok']); ?></div>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <div class="info-value">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                            <?php echo htmlspecialchars($pasien['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Form -->
    <div class="card progress-card">
        <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Progress Formulir</h3>
        </div>
        <div class="card-body">
            <div class="progress-steps">
                <!-- Semua menu progress form selalu aktif -->
                <a href="index.php?page=persiapan-operasi&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                   class="progress-step progress-btn <?php echo $persiapan_terisi ? 'completed' : 'not-completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-<?php echo $persiapan_terisi ? 'check' : 'clipboard-list'; ?>"></i>
                    </div>
                    <div class="step-info">
                        <h4>Checklist Persiapan Operasi</h4>
                        <p>Form persiapan pra-operasi</p>
                        <span class="step-status">
                            <?php echo $persiapan_terisi ? 'Sudah diisi' : 'Belum diisi'; ?>
                        </span>
                        <span class="step-action-label">
                            <i class="fas fa-<?php echo $persiapan_terisi ? 'eye' : 'edit'; ?>"></i>
                            <?php echo $persiapan_terisi ? 'Lihat' : 'Isi'; ?>
                        </span>
                    </div>
                </a>
                <a href="index.php?page=keselamatan-operasi&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                   class="progress-step progress-btn <?php echo $keselamatan_terisi ? 'completed' : 'not-completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-<?php echo $keselamatan_terisi ? 'check' : 'shield-alt'; ?>"></i>
                    </div>
                    <div class="step-info">
                        <h4>Checklist Keselamatan Operasi</h4>
                        <p>Form keselamatan selama operasi</p>
                        <span class="step-status">
                            <?php echo $keselamatan_terisi ? 'Sudah diisi' : 'Belum diisi'; ?>
                        </span>
                        <span class="step-action-label">
                            <i class="fas fa-<?php echo $keselamatan_terisi ? 'eye' : 'edit'; ?>"></i>
                            <?php echo $keselamatan_terisi ? 'Lihat' : 'Isi'; ?>
                        </span>
                    </div>
                </a>
                <a href="index.php?page=kamar-pemulihan&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                   class="progress-step progress-btn <?php echo $pemulihan_terisi ? 'completed' : 'not-completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-<?php echo $pemulihan_terisi ? 'check' : 'procedures'; ?>"></i>
                    </div>
                    <div class="step-info">
                        <h4>Catatan Kamar Pemulihan</h4>
                        <p>Form monitoring pasca operasi</p>
                        <span class="step-status">
                            <?php echo $pemulihan_terisi ? 'Sudah diisi' : 'Belum diisi'; ?>
                        </span>
                        <span class="step-action-label">
                            <i class="fas fa-<?php echo $pemulihan_terisi ? 'eye' : 'edit'; ?>"></i>
                            <?php echo $pemulihan_terisi ? 'Lihat' : 'Isi'; ?>
                        </span>
                    </div>
                </a>
                <!-- Menu progress form baru -->
                <a href="index.php?page=form-catatan-sedasi&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                class="progress-step progress-btn <?php echo $catatan_terisi ? 'completed' : 'not-completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-<?php echo $catatan_terisi ? 'check' : 'notes-medical'; ?>"></i>
                    </div>
                    <div class="step-info">
                        <h4>Catatan Sedasi & Anestesi</h4>
                        <p>Form catatan sedasi dan anestesi</p>
                        <span class="step-status">
                            <?php echo $catatan_terisi ? 'Sudah diisi' : 'Belum diisi'; ?>
                        </span>
                        <span class="step-action-label">
                            <i class="fas fa-<?php echo $catatan_terisi ? 'eye' : 'edit'; ?>"></i>
                            <?php echo $catatan_terisi ? 'Lihat' : 'Isi'; ?>
                        </span>
                    </div>
                </a>
                <a href="index.php?page=informed-consent-anestesi&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                   class="progress-step progress-btn">
                    <div class="step-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="step-info">
                        <h4>Informed Consent Anestesi</h4>
                        <p>Form persetujuan tindakan anestesi</p>
                        <span class="step-status">Akses Form</span>
                        <span class="step-action-label">
                            <i class="fas fa-edit"></i> Isi
                        </span>
                    </div>
                </a>
                <a href="index.php?page=konsultasi-anestesi&no_rawat=<?php echo urlencode($no_rawat); ?>&kode_paket=<?php echo urlencode($kode_paket); ?>&tanggal=<?php echo urlencode($tanggal); ?>&jam_mulai=<?php echo urlencode($jam_mulai); ?>"
                   class="progress-step progress-btn">
                    <div class="step-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="step-info">
                        <h4>Konsultasi Anestesi</h4>
                        <p>Form konsultasi anestesi</p>
                        <span class="step-status">Akses Form</span>
                        <span class="step-action-label">
                            <i class="fas fa-edit"></i> Isi
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
