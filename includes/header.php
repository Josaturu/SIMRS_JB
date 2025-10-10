<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Rumah Sakit'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/improvements.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="logo">
            <div>
                <strong>Rumah Sakit Umum</strong><br>
                <span style="color:#396cf0; font-weight:bold;">PRASETYA BUNDA</span>
            </div>
        </div>
        <div class="sticker-box">
        <?php
        // Tampilkan info pasien jika variabel tersedia
        if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal) && !empty($pasien['kd_dokter'])) {
            echo '<b>Pasien:</b><br>';
            echo 'No. Rawat: <span style="font-weight:600">' . htmlspecialchars($no_rawat) . '</span><br>';
            echo 'Kode Paket: <span style="font-weight:600">' . htmlspecialchars($kode_paket) . '</span><br>';
            echo 'Tgl Operasi: <span style="font-weight:600">' . htmlspecialchars($tanggal) . '</span><br>';
            echo 'Dokter: <span style="font-weight:600">' . htmlspecialchars($pasien['kd_dokter']) . '</span>';
        } else {
            echo 'Tempelkan Stiker Identitas Pasien';
        }
        ?>
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
                <a href="index.php" class="nav-link <?php echo ($page == '') ? 'active' : ''; ?>">
                    <i class="fa fa-file"></i>
                    <span>Laporan</span>
                </a>
            </div>
        </div>
    </nav>