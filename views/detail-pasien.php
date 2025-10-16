<?php
$page_title = "Detail Pasien";

// Ambil parameter dari URL
$no_rawat   = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal    = $_GET['tanggal'] ?? '';
$jam_mulai  = $_GET['jam_mulai'] ?? '';

// Jika parameter tidak lengkap, kembali ke daftar
if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header("Location: index.php?page=daftar-pasien");
    exit;
}

// Koneksi database
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Ambil data pasien & operasi (JOIN)
$query = "
    SELECT 
        b.*, 
        p.nama AS nama_pasien, 
        p.kode_rekam_medis
    FROM booking_operasi AS b
    LEFT JOIN pasien AS p ON b.kd_pasien = p.kd_pasien
    WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$pasien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pasien) {
    echo "<div style='padding:20px;color:red;'>❌ Data pasien tidak ditemukan.</div>";
    exit;
}

// Cek status setiap form
function isFormFilled($db, $table, $no_rawat, $kode_paket, $tanggal, $jam_mulai) {
    // Cek struktur tabel
    $cols = [];
    $check = $db->prepare("SHOW COLUMNS FROM $table");
    $check->execute();
    $columns = $check->fetchAll(PDO::FETCH_COLUMN);

    $query = "SELECT COUNT(*) AS total FROM $table WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket";
    $params = [
        ':no_rawat' => $no_rawat,
        ':kode_paket' => $kode_paket
    ];

    if (in_array('tanggal', $columns)) {
        $query .= " AND tanggal = :tanggal";
        $params[':tanggal'] = $tanggal;
    }

    if (in_array('jam_mulai', $columns)) {
        $query .= " AND jam_mulai = :jam_mulai";
        $params[':jam_mulai'] = $jam_mulai;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
}


// Cek status setiap form (dengan error handling)
function checkFormStatus($db, $table, $no_rawat, $kode_paket, $tanggal, $jam_mulai) {
    try {
        return isFormFilled($db, $table, $no_rawat, $kode_paket, $tanggal, $jam_mulai);
    } catch (PDOException $e) {
        // Table mungkin belum dibuat
        error_log("Error checking $table: " . $e->getMessage());
        return false;
    }
}

$persiapan_terisi  = checkFormStatus($db, 'tbl_anestesi_persiapan_operasi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$keselamatan_terisi = checkFormStatus($db, 'tbl_anestesi_keselamatan_operasi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$pemulihan_terisi   = checkFormStatus($db, 'tbl_anestesi_kamar_pemulihan', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$catatan_terisi     = checkFormStatus($db, 'tbl_anestesi_catatan_anestesi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$informed_terisi    = checkFormStatus($db, 'tbl_anestesi_informed_consent_anestesi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$konsultasi_terisi  = checkFormStatus($db, 'tbl_anestesi_konsultasi_anestesi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <?php
    // Tampilkan notifikasi success atau error
    if (isset($_GET['success'])) {
        $message = $_GET['success'] === 'saved' ? 'Data berhasil disimpan!' : 'Data berhasil diperbarui!';
        echo '<div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
                <i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '
              </div>';
    }
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
                <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat menyimpan data. Silakan coba lagi.
              </div>';
    }
    ?>
    <!-- Informasi Pasien -->
    <div class="card patient-info-card">
        <div class="card-header">
            <h3><i class="fas fa-user-injured"></i> Informasi Pasien</h3>
        </div>
        <div class="card-body">
            <div class="patient-info-grid">
                <div class="info-item">
                    <label>No. Rawat</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['no_rawat']); ?></div>
                </div>
                <div class="info-item">
                    <label>Kode Rekam Medis</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Nama Pasien</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['nama_pasien'] ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Kode Paket</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['kode_paket']); ?></div>
                </div>
                <div class="info-item">
                    <label>Tanggal Operasi</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['tanggal']); ?></div>
                </div>
                <div class="info-item">
                    <label>Jam Operasi</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['jam_mulai']); ?></div>
                </div>
                <div class="info-item">
                    <label>Dokter</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['kd_dokter']); ?></div>
                </div>
                <div class="info-item">
                    <label>Ruang OK</label>
                    <div class="info-value"><?= htmlspecialchars($pasien['kd_ruang_ok']); ?></div>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <div class="info-value">
                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                            <?= htmlspecialchars($pasien['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Formulir -->
    <div class="card progress-card">
        <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Progress Formulir</h3>
        </div>
        <div class="card-body">
            <div class="progress-steps">
                <?php
                $steps = [
                    [
                        'page' => 'persiapan-operasi',
                        'label' => 'Checklist Persiapan Operasi',
                        'desc' => 'Form persiapan pra-operasi',
                        'icon' => 'clipboard-list',
                        'done' => $persiapan_terisi,
                        'pdf' => 'pdf-persiapan-operasi.php'
                    ],
                    [
                        'page' => 'keselamatan-operasi',
                        'label' => 'Checklist Keselamatan Operasi',
                        'desc' => 'Form keselamatan selama operasi',
                        'icon' => 'shield-alt',
                        'done' => $keselamatan_terisi,
                        'pdf' => 'pdf-keselamatan-operasi.php'
                    ],
                    [
                        'page' => 'kamar-pemulihan',
                        'label' => 'Catatan Kamar Pemulihan',
                        'desc' => 'Form monitoring pasca operasi',
                        'icon' => 'procedures',
                        'done' => $pemulihan_terisi,
                        'pdf' => 'pdf-kamar-pemulihan.php'
                    ],
                    [
                        'page' => 'vital-sign',
                        'label' => 'Vital Sign',
                        'desc' => 'Form monitoring tanda vital intra/post',
                        'icon' => 'heartbeat',
                        'done' => checkFormStatus($db, 'tbl_anestesi_vital_sign', $no_rawat, $kode_paket, $tanggal, $jam_mulai),
                        'pdf' => 'pdf-vital-sign.php'
                    ],
                    [
                        'page' => 'form-catatan-sedasi',
                        'label' => 'Catatan Sedasi & Anestesi',
                        'desc' => 'Form catatan sedasi dan anestesi',
                        'icon' => 'notes-medical',
                        'done' => $catatan_terisi,
                        'pdf' => 'pdf-catatan-sedasi.php'
                    ],
                    [
                        'page' => 'informed-consent-anestesi',
                        'label' => 'Informed Consent Anestesi',
                        'desc' => 'Form persetujuan tindakan anestesi',
                        'icon' => 'file-signature',
                        'done' => $informed_terisi,
                        'pdf' => 'pdf-informed-consent.php'
                    ],
                    [
                        'page' => 'konsultasi-anestesi',
                        'label' => 'Konsultasi Anestesi',
                        'desc' => 'Form konsultasi anestesi',
                        'icon' => 'user-md',
                        'done' => $konsultasi_terisi,
                        'pdf' => 'pdf-konsultasi-anestesi.php'
                    ]
                ];

                foreach ($steps as $step):
                    $statusClass = $step['done'] ? 'completed' : 'not-completed';
                    $icon = $step['done'] ? 'check' : $step['icon'];
                    $actionLabel = $step['done'] ? 'Lihat' : 'Isi';
                    $actionIcon = $step['done'] ? 'eye' : 'edit';
                ?>
                <a href="index.php?page=<?= $step['page'] ?>&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>"
                   class="progress-step progress-btn <?= $statusClass; ?>">
                    <div class="step-icon"><i class="fas fa-<?= $icon; ?>"></i></div>
                    <div class="step-info">
                        <h4><?= $step['label']; ?></h4>
                        <p><?= $step['desc']; ?></p>
                        <span class="step-status"><?= $step['done'] ? 'Sudah diisi' : 'Belum diisi'; ?></span>
                        <div class="step-actions" style="margin-top: 8px; display: flex; gap: 8px;">
                            <span class="step-action-label" style="padding: 6px 12px; background: <?= $step['done'] ? '#17a2b8' : '#007bff' ?>; color: white; border-radius: 4px; font-size: 11px; display: inline-block;">
                                <i class="fas fa-<?= $actionIcon; ?>"></i> <?= $actionLabel; ?>
                            </span>
                            <?php if ($step['done'] && isset($step['pdf'])): ?>
                            <span onclick="window.open('process/pdf/<?= $step['pdf'] ?>?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>', '_blank'); event.preventDefault(); event.stopPropagation();" 
                                  class="step-action-label" style="padding: 6px 12px; background: #28a745; color: white; border-radius: 4px; font-size: 11px; display: inline-block; cursor: pointer;">
                                <i class="fas fa-print"></i> PDF
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
