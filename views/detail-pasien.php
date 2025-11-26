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

// Ambil data pasien & operasi (JOIN dengan tabel master)
$query = "
    SELECT 
        b.*, 
        p.nama AS nama_pasien, 
        p.kode_rekam_medis,
        p.alamat,
        p.jenis_kelamin,
        p.tempat_lahir,
        p.tanggal_lahir,
        p.no_hp,
        p.gol_darah,
        d.nama_dokter,
        r.nama_ruang,
        pr.nama_perawat
    FROM booking_operasi AS b
    LEFT JOIN pasien AS p ON b.kd_pasien = p.kd_pasien
    LEFT JOIN tbl_dokter d ON b.dokter_rawat COLLATE utf8mb4_unicode_ci = d.id_dokter
    LEFT JOIN tbl_ruang r ON b.ruang_rawat COLLATE utf8mb4_unicode_ci = r.id_ruang
    LEFT JOIN tbl_perawat pr ON b.perawat COLLATE utf8mb4_unicode_ci = pr.id_perawat
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

// Hitung umur dari tanggal lahir
$umur = '';
if (!empty($pasien['tanggal_lahir'])) {
    $tgl_lahir = new DateTime($pasien['tanggal_lahir']);
    $today = new DateTime();
    $diff = $today->diff($tgl_lahir);
    $umur = $diff->y . ' tahun';
    if ($diff->m > 0) {
        $umur .= ' ' . $diff->m . ' bulan';
    }
}

// Jangan include header.php - kita buat header sendiri
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SIMRS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/improvements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="main-nav improved-navbar" style="margin-bottom: 0;">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-angle-right"></i>
                <span>Detail Pasien</span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </nav>
<?php
?>

<!-- Load Detail Pasien CSS -->
<link rel="stylesheet" href="assets/css/detail-pasien.css">

<div class="detail-layout">
    <!-- Sidebar Patient Info -->
    <aside class="patient-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <div class="patient-avatar">
                    <?= strtoupper(substr($pasien['nama_pasien'] ?? 'P', 0, 1)); ?>
                </div>
                <div class="patient-name"><?= htmlspecialchars($pasien['nama_pasien'] ?? '-'); ?></div>
                <div class="patient-id">RM: <?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '-'); ?></div>
            </div>
            
            <!-- Data Pribadi Pasien -->
            <div class="sidebar-section is-open">
                <button type="button" class="sidebar-section-title sidebar-toggle">
                    <div class="sidebar-title-left">
                        <i class="fas fa-user"></i>
                        <span>Data Pribadi</span>
                    </div>
                    <span class="sidebar-toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div class="sidebar-section-body">
                    <?php if (!empty($pasien['tempat_lahir']) || !empty($pasien['tanggal_lahir'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Tempat, Tgl Lahir</span>
                        <span class="info-value">
                            <?= htmlspecialchars($pasien['tempat_lahir'] ?? '-'); ?>, 
                            <?= !empty($pasien['tanggal_lahir']) ? date('d/m/Y', strtotime($pasien['tanggal_lahir'])) : '-'; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($umur)): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Umur</span>
                        <span class="info-value"><?= htmlspecialchars($umur); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['jenis_kelamin'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Jenis Kelamin</span>
                        <span class="info-value">
                            <?= $pasien['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['gol_darah'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Golongan Darah</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['gol_darah']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['alamat'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Alamat</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['alamat']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['no_hp'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">No. HP</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['no_hp']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Data Booking Operasi -->
            <div class="sidebar-section">
                <button type="button" class="sidebar-section-title sidebar-toggle">
                    <div class="sidebar-title-left">
                        <i class="fas fa-calendar-check"></i>
                        <span>Data Booking</span>
                    </div>
                    <span class="sidebar-toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div class="sidebar-section-body">
                    <div class="sidebar-info-item">
                        <span class="info-label">No. Rawat</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['no_rawat']); ?></span>
                    </div>
                    
                    <div class="sidebar-info-item">
                        <span class="info-label">Kode Paket</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['kode_paket']); ?></span>
                    </div>
                    
                    <div class="sidebar-info-item">
                        <span class="info-label">Tanggal Operasi</span>
                        <span class="info-value">
                            <?= date('d/m/Y', strtotime($pasien['tanggal'])); ?>
                        </span>
                    </div>
                    
                    <div class="sidebar-info-item">
                        <span class="info-label">Jam Operasi</span>
                        <span class="info-value">
                            <?= date('H:i', strtotime($pasien['jam_mulai'])); ?>
                            <?php if (!empty($pasien['jam_selesai'])): ?>
                                - <?= date('H:i', strtotime($pasien['jam_selesai'])); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="sidebar-info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge-sidebar status-<?= strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                                <?= htmlspecialchars($pasien['status']); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Tim Medis -->
            <div class="sidebar-section">
                <button type="button" class="sidebar-section-title sidebar-toggle">
                    <div class="sidebar-title-left">
                        <i class="fas fa-user-md"></i>
                        <span>Tim Medis</span>
                    </div>
                    <span class="sidebar-toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div class="sidebar-section-body">
                    <?php if (!empty($pasien['nama_dokter'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Dokter Rawat</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['nama_dokter']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['nama_perawat'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Perawat</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['nama_perawat']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pasien['nama_ruang'])): ?>
                    <div class="sidebar-info-item">
                        <span class="info-label">Ruang Rawat</span>
                        <span class="info-value"><?= htmlspecialchars($pasien['nama_ruang']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php
        // Group forms by role (Dokter & Perawat)
        $forms_dokter = [
            [
                'page' => 'konsultasi-anestesi',
                'label' => 'Konsultasi Anestesi',
                'desc' => 'Form konsultasi anestesi',
                'icon' => 'user-md',
                'done' => $konsultasi_terisi,
                'pdf' => 'pdf-konsultasi-anestesi.php'
            ],
            [
                'page' => 'informed-consent-anestesi',
                'label' => 'Informed Consent',
                'desc' => 'Persetujuan tindakan anestesi',
                'icon' => 'file-signature',
                'done' => $informed_terisi,
                'pdf' => 'pdf-informed-consent.php'
            ],
            [
                'page' => 'form-catatan-sedasi',
                'label' => 'Catatan Sedasi',
                'desc' => 'Catatan sedasi & anestesi (termasuk vital sign)',
                'icon' => 'notes-medical',
                'done' => $catatan_terisi,
                'pdf' => 'pdf-catatan-sedasi.php'
            ]
        ];
        
        $forms_perawat = [
            [
                'page' => 'keselamatan-operasi',
                'label' => 'Keselamatan Operasi',
                'desc' => 'Checklist keselamatan operasi',
                'icon' => 'shield-alt',
                'done' => $keselamatan_terisi,
                'pdf' => 'pdf-keselamatan-operasi.php'
            ],
            [
                'page' => 'persiapan-operasi',
                'label' => 'Persiapan Operasi',
                'desc' => 'Checklist persiapan pra-operasi',
                'icon' => 'clipboard-list',
                'done' => $persiapan_terisi,
                'pdf' => 'pdf-persiapan-operasi.php'
            ],
            [
                'page' => 'kamar-pemulihan',
                'label' => 'Kamar Pemulihan',
                'desc' => 'Monitoring pasca operasi',
                'icon' => 'procedures',
                'done' => $pemulihan_terisi,
                'pdf' => 'pdf-kamar-pemulihan.php'
            ]
        ];
        
        // Calculate stats
        $all_forms = array_merge($forms_dokter, $forms_perawat);
        $total_forms = count($all_forms);
        $completed_forms = count(array_filter($all_forms, function($f) { return $f['done']; }));
        $pending_forms = $total_forms - $completed_forms;
        ?>
        
        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" onclick="switchTab('dokter')">
                    <i class="fas fa-user-md"></i> Dokter
                </button>
                <button class="tab-btn" onclick="switchTab('perawat')">
                    <i class="fas fa-user-nurse"></i> Perawat
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Dokter Tab -->
                <div id="tab-dokter" class="tab-pane active">
                    <div class="forms-grid">
                        <?php foreach ($forms_dokter as $form): 
                            $statusClass = $form['done'] ? 'completed' : 'pending';
                            $statusIcon = $form['done'] ? 'check-circle' : 'clock';
                            $formUrl = "index.php?page={$form['page']}&no_rawat=" . urlencode($no_rawat) . 
                                      "&kode_paket=" . urlencode($kode_paket) . 
                                      "&tanggal=" . urlencode($tanggal) . 
                                      "&jam_mulai=" . urlencode($jam_mulai);
                        ?>
                        <a href="<?= $formUrl ?>" class="form-card <?= $statusClass ?>">
                            <div class="form-status-icon <?= $statusClass ?>">
                                <i class="fas fa-<?= $statusIcon ?>"></i>
                            </div>
                            <div class="form-card-title"><?= $form['label'] ?></div>
                            <div class="form-card-desc"><?= $form['desc'] ?></div>
                            <div class="form-card-actions">
                                <span class="form-action-btn primary">
                                    <i class="fas fa-<?= $form['done'] ? 'eye' : 'edit' ?>"></i>
                                    <?= $form['done'] ? 'Lihat' : 'Isi Form' ?>
                                </span>
                                <?php if ($form['done'] && isset($form['pdf'])): ?>
                                <span onclick="window.open('process/pdf/<?= $form['pdf'] ?>?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>', '_blank'); event.preventDefault(); event.stopPropagation();" 
                                      class="form-action-btn success">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Perawat Tab -->
                <div id="tab-perawat" class="tab-pane">
                    <div class="forms-grid">
                        <?php foreach ($forms_perawat as $form): 
                            $statusClass = $form['done'] ? 'completed' : 'pending';
                            $statusIcon = $form['done'] ? 'check-circle' : 'clock';
                            $formUrl = "index.php?page={$form['page']}&no_rawat=" . urlencode($no_rawat) . 
                                      "&kode_paket=" . urlencode($kode_paket) . 
                                      "&tanggal=" . urlencode($tanggal) . 
                                      "&jam_mulai=" . urlencode($jam_mulai);
                        ?>
                        <a href="<?= $formUrl ?>" class="form-card <?= $statusClass ?>">
                            <div class="form-status-icon <?= $statusClass ?>">
                                <i class="fas fa-<?= $statusIcon ?>"></i>
                            </div>
                            <div class="form-card-title"><?= $form['label'] ?></div>
                            <div class="form-card-desc"><?= $form['desc'] ?></div>
                            <div class="form-card-actions">
                                <span class="form-action-btn primary">
                                    <i class="fas fa-<?= $form['done'] ? 'eye' : 'edit' ?>"></i>
                                    <?= $form['done'] ? 'Lihat' : 'Isi Form' ?>
                                </span>
                                <?php if ($form['done'] && isset($form['pdf'])): ?>
                                <span onclick="window.open('process/pdf/<?= $form['pdf'] ?>?no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>', '_blank'); event.preventDefault(); event.stopPropagation();" 
                                      class="form-action-btn success">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function switchTab(tabName) {
    // Remove active class from all tabs and panes
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    // Add active class to selected tab and pane
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

// Auto-hide notifications after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

// Sidebar collapsible sections
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sidebar-section .sidebar-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const section = btn.closest('.sidebar-section');
            if (!section) return;

            // Jika ingin model accordion (satu terbuka sekaligus), uncomment blok berikut:
            // document.querySelectorAll('.sidebar-section').forEach(function (sec) {
            //     if (sec !== section) {
            //         sec.classList.remove('is-open');
            //     }
            // });

            section.classList.toggle('is-open');
        });
    });
});
</script>
</body>
</html>
