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


$persiapan_terisi  = isFormFilled($db, 'tbl_anestesi_persiapan_operasi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$keselamatan_terisi = isFormFilled($db, 'tbl_anestesi_checklist_keselamatan', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$pemulihan_terisi   = isFormFilled($db, 'tbl_anestesi_kamar_pemulihan', $no_rawat, $kode_paket, $tanggal, $jam_mulai);
$catatan_terisi     = isFormFilled($db, 'tbl_anestesi_catatan_anestesi', $no_rawat, $kode_paket, $tanggal, $jam_mulai);

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
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
                        'done' => $persiapan_terisi
                    ],
                    [
                        'page' => 'keselamatan-operasi',
                        'label' => 'Checklist Keselamatan Operasi',
                        'desc' => 'Form keselamatan selama operasi',
                        'icon' => 'shield-alt',
                        'done' => $keselamatan_terisi
                    ],
                    [
                        'page' => 'kamar-pemulihan',
                        'label' => 'Catatan Kamar Pemulihan',
                        'desc' => 'Form monitoring pasca operasi',
                        'icon' => 'procedures',
                        'done' => $pemulihan_terisi
                    ],
                    [
                        'page' => 'form-catatan-sedasi',
                        'label' => 'Catatan Sedasi & Anestesi',
                        'desc' => 'Form catatan sedasi dan anestesi',
                        'icon' => 'notes-medical',
                        'done' => $catatan_terisi
                    ],
                    [
                        'page' => 'informed-consent-anestesi',
                        'label' => 'Informed Consent Anestesi',
                        'desc' => 'Form persetujuan tindakan anestesi',
                        'icon' => 'file-signature',
                        'done' => false
                    ],
                    [
                        'page' => 'konsultasi-anestesi',
                        'label' => 'Konsultasi Anestesi',
                        'desc' => 'Form konsultasi anestesi',
                        'icon' => 'user-md',
                        'done' => false
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
                        <span class="step-action-label">
                            <i class="fas fa-<?= $actionIcon; ?>"></i> <?= $actionLabel; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
