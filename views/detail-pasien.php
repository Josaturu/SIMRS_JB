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
                <span class="info-value"><?= htmlspecialchars($pasien['tanggal']); ?></span>
            </div>
            
            <div class="sidebar-info-item">
                <span class="info-label">Jam Operasi</span>
                <span class="info-value"><?= htmlspecialchars($pasien['jam_mulai']); ?></span>
            </div>
            
            <div class="sidebar-info-item">
                <span class="info-label">Dokter</span>
                <span class="info-value"><?= htmlspecialchars($pasien['kd_dokter']); ?></span>
            </div>
            
            <div class="sidebar-info-item">
                <span class="info-label">Ruang OK</span>
                <span class="info-value"><?= htmlspecialchars($pasien['kd_ruang_ok']); ?></span>
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
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php
        // Group forms by phase
        $pra_operasi = [
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
                'page' => 'persiapan-operasi',
                'label' => 'Persiapan Operasi',
                'desc' => 'Checklist persiapan pra-operasi',
                'icon' => 'clipboard-list',
                'done' => $persiapan_terisi,
                'pdf' => 'pdf-persiapan-operasi.php'
            ]
        ];
        
        $intra_operasi = [
            [
                'page' => 'keselamatan-operasi',
                'label' => 'Keselamatan Operasi',
                'desc' => 'Checklist keselamatan operasi',
                'icon' => 'shield-alt',
                'done' => $keselamatan_terisi,
                'pdf' => 'pdf-keselamatan-operasi.php'
            ],
            [
                'page' => 'vital-sign',
                'label' => 'Vital Sign',
                'desc' => 'Monitoring tanda vital',
                'icon' => 'heartbeat',
                'done' => checkFormStatus($db, 'tbl_anestesi_vital_sign', $no_rawat, $kode_paket, $tanggal, $jam_mulai),
                'pdf' => 'pdf-vital-sign.php'
            ],
            [
                'page' => 'form-catatan-sedasi',
                'label' => 'Catatan Sedasi',
                'desc' => 'Catatan sedasi & anestesi',
                'icon' => 'notes-medical',
                'done' => $catatan_terisi,
                'pdf' => 'pdf-catatan-sedasi.php'
            ]
        ];
        
        $post_operasi = [
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
        $all_forms = array_merge($pra_operasi, $intra_operasi, $post_operasi);
        $total_forms = count($all_forms);
        $completed_forms = count(array_filter($all_forms, function($f) { return $f['done']; }));
        $pending_forms = $total_forms - $completed_forms;
        ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card progress">
                <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-value"><?= $completed_forms ?>/<?= $total_forms ?></div>
                <div class="stat-label">Progress</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?= $completed_forms ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?= $pending_forms ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card updated">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-value"><?= date('d/m/Y') ?></div>
                <div class="stat-label">Last Updated</div>
            </div>
        </div>
        
        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" onclick="switchTab('pra')">
                    <i class="fas fa-clipboard-check"></i> Pra-Operasi
                </button>
                <button class="tab-btn" onclick="switchTab('intra')">
                    <i class="fas fa-procedures"></i> Intra-Operasi
                </button>
                <button class="tab-btn" onclick="switchTab('post')">
                    <i class="fas fa-bed"></i> Post-Operasi
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Pra-Operasi Tab -->
                <div id="tab-pra" class="tab-pane active">
                    <div class="forms-grid">
                        <?php foreach ($pra_operasi as $form): 
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
                
                <!-- Intra-Operasi Tab -->
                <div id="tab-intra" class="tab-pane">
                    <div class="forms-grid">
                        <?php foreach ($intra_operasi as $form): 
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
                
                <!-- Post-Operasi Tab -->
                <div id="tab-post" class="tab-pane">
                    <div class="forms-grid">
                        <?php foreach ($post_operasi as $form): 
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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
