<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Rumah Sakit'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/improvements.css">
    <link rel="stylesheet" href="assets/css/header-sticky.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header header-sticky">
        <div class="logo">
            <img src="assets/images/logo-pb.png" alt="Logo Rumah Sakit Prasetya Bunda" onerror="this.src='assets/images/logo.png'">
            <div class="hospital-name">
                <strong>RUMAH SAKIT UMUM</strong>
                <span class="hospital-brand">PRASETYA BUNDA</span>
            </div>
        </div>
        
        <?php
        // Cek dokter dan nama pasien dari $pasien atau $booking
        $dokter = '';
        $nama_pasien = '';
        
        if (isset($pasien['kd_dokter'])) {
            $dokter = $pasien['kd_dokter'];
        } elseif (isset($booking['kd_dokter'])) {
            $dokter = $booking['kd_dokter'];
        }
        
        if (isset($pasien['nama'])) {
            $nama_pasien = $pasien['nama'];
        } elseif (isset($booking['nm_pasien'])) {
            $nama_pasien = $booking['nm_pasien'];
        }
        ?>
        
        <div class="patient-info-bar">
            <?php if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal)): ?>
                <div class="info-items">
                    <?php if (!empty($nama_pasien)): ?>
                        <span class="info-item">
                            <i class="fas fa-user"></i>
                            <span class="info-value"><?= htmlspecialchars($nama_pasien) ?></span>
                        </span>
                        <span class="info-separator">•</span>
                    <?php endif; ?>
                    <span class="info-item">
                        <i class="fas fa-id-card"></i>
                        <span class="info-value"><?= htmlspecialchars($no_rawat) ?></span>
                    </span>
                    <span class="info-separator">•</span>
                    <span class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span class="info-value"><?= htmlspecialchars($tanggal) ?></span>
                    </span>
                    <?php if (!empty($dokter)): ?>
                        <span class="info-separator">•</span>
                        <span class="info-item">
                            <i class="fas fa-user-md"></i>
                            <span class="info-value"><?= htmlspecialchars($dokter) ?></span>
                        </span>
                    <?php endif; ?>
                </div>
                
                <button class="info-toggle" id="patientInfoToggle" aria-label="Lihat detail pasien" title="Lihat detail lengkap">
                    <i class="fas fa-circle-info"></i>
                </button>
                
                <!-- Popover Detail -->
                <div class="info-popover" id="patientInfoPopover" role="tooltip">
                    <div class="popover-header">
                        <strong>Detail Pasien</strong>
                        <button class="popover-close" id="popoverClose" aria-label="Tutup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="popover-body">
                        <?php if (!empty($nama_pasien)): ?>
                            <div class="popover-item">
                                <span class="popover-label">Nama Pasien:</span>
                                <span class="popover-value"><?= htmlspecialchars($nama_pasien) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="popover-item">
                            <span class="popover-label">No. Rawat:</span>
                            <span class="popover-value"><?= htmlspecialchars($no_rawat) ?></span>
                        </div>
                        <div class="popover-item">
                            <span class="popover-label">Kode Paket:</span>
                            <span class="popover-value"><?= htmlspecialchars($kode_paket) ?></span>
                        </div>
                        <div class="popover-item">
                            <span class="popover-label">Tgl Operasi:</span>
                            <span class="popover-value"><?= htmlspecialchars($tanggal) ?></span>
                        </div>
                        <?php if (!empty($dokter)): ?>
                            <div class="popover-item">
                                <span class="popover-label">Dokter:</span>
                                <span class="popover-value"><?= htmlspecialchars($dokter) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="info-placeholder">
                    <i class="fas fa-id-badge"></i>
                    <span>Tempelkan Stiker Identitas Pasien</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <nav class="main-nav improved-navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-angle-right"></i>
                <span>
                <?php
                    // Judul halaman dinamis
                    $page_map = [
                        '' => 'Daftar Pasien',
                        'tambah-booking' => 'Tambah Booking',
                        'detail-pasien' => 'Detail Pasien',
                        'persiapan-operasi' => 'Checklist Persiapan',
                        'keselamatan-operasi' => 'Checklist Keselamatan',
                        'kamar-pemulihan' => 'Catatan Kamar Pemulihan',
                    ];
                    $page = $_GET['page'] ?? '';
                    echo $page_map[$page] ?? (isset($page_title) ? $page_title : 'Sistem Anestesi');
                ?>
                </span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link <?php echo ($page == '') ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span>Daftar Pasien</span>
                </a>
                
                <?php
                // Tombol Laporan dinamis berdasarkan halaman
                $pdf_link = '';
                $show_pdf = false;
                
                // Ambil variabel dari GET jika belum terdefinisi
                if (!isset($no_rawat)) $no_rawat = $_GET['no_rawat'] ?? '';
                if (!isset($kode_paket)) $kode_paket = $_GET['kode_paket'] ?? '';
                if (!isset($tanggal)) $tanggal = $_GET['tanggal'] ?? '';
                if (!isset($jam_mulai)) $jam_mulai = $_GET['jam_mulai'] ?? '';
                if (!isset($page)) $page = $_GET['page'] ?? '';
                
                // Cek apakah ada parameter lengkap untuk PDF
                if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal) && !empty($jam_mulai)) {
                    $params = http_build_query([
                        'no_rawat' => $no_rawat,
                        'kode_paket' => $kode_paket,
                        'tanggal' => $tanggal,
                        'jam_mulai' => $jam_mulai
                    ]);
                    
                    // Mapping halaman ke file PDF
                    $pdf_map = [
                        'konsultasi-anestesi' => 'process/pdf/pdf-konsultasi-anestesi.php',
                        'form-konsultasi-anestesi' => 'process/pdf/pdf-konsultasi-anestesi.php',
                        'informed-consent-anestesi' => 'process/pdf/pdf-informed-consent.php',
                        'form-informed-consent-anestesi' => 'process/pdf/pdf-informed-consent.php',
                        'catatan-sedasi' => 'process/pdf/pdf-catatan-sedasi.php',
                        'form-catatan-sedasi' => 'process/pdf/pdf-catatan-sedasi.php',
                        'persiapan-operasi' => 'process/pdf/pdf-persiapan-operasi.php',
                        'form-persiapan-operasi' => 'process/pdf/pdf-persiapan-operasi.php',
                        'keselamatan-operasi' => 'process/pdf/pdf-keselamatan-operasi.php',
                        'form-keselamatan-operasi' => 'process/pdf/pdf-keselamatan-operasi.php',
                        'kamar-pemulihan' => 'process/pdf/pdf-kamar-pemulihan.php',
                        'form-kamar-pemulihan' => 'process/pdf/pdf-kamar-pemulihan.php',
                    ];
                    
                    if (isset($pdf_map[$page])) {
                        $pdf_link = $pdf_map[$page] . '?' . $params;
                        $show_pdf = true;
                    }
                }
                
                if ($show_pdf):
                ?>
                <a href="<?= $pdf_link ?>" target="_blank" class="nav-link" title="Lihat Laporan PDF">
                    <i class="fas fa-file-pdf"></i>
                    <span>Laporan PDF</span>
                </a>
                <?php else: ?>
                <a href="#" class="nav-link" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" title="Tidak tersedia di halaman ini">
                    <i class="fas fa-file-pdf"></i>
                    <span>Laporan PDF</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>