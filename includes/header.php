<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Rumah Sakit'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/improvements.css">
    <link rel="stylesheet" href="assets/css/header-sticky.css">
    <!-- Global Notification & Loading System -->
    <link rel="stylesheet" href="assets/css/notification.css">
    <link rel="stylesheet" href="assets/css/loading-skeleton.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DateTime Now Button Script -->
    <script src="assets/js/datetime-now.js"></script>
    
    <!-- Form Exit Confirmation Script -->
    <script src="assets/js/form-exit-confirmation.js"></script>
    
    <!-- Mark Required Fields Script -->
    <script src="assets/js/mark-required-fields.js"></script>
    
    <!-- Patient Card Toggle Script -->
    <script src="assets/js/patient-card.js"></script>
</head>
<body>
    
    
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