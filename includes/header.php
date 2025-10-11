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
        if (!empty($no_rawat) && !empty($kode_paket) && !empty($tanggal)) {
            // Hitung umur jika ada tanggal lahir
            $umur_text = '';
            if (!empty($pasien['tanggal_lahir'])) {
                try {
                    $tgl_lahir = new DateTime($pasien['tanggal_lahir']);
                    $today = new DateTime();
                    $umur = $tgl_lahir->diff($today)->y;
                    $umur_text = ' (' . $umur . ' thn)';
                } catch (Exception $e) {
                    $umur_text = '';
                }
            }
            
            // Gender icon
            $gender_icon = '';
            if (!empty($pasien['jenis_kelamin'])) {
                $gender_icon = $pasien['jenis_kelamin'] == 'L' 
                    ? '<i class="fas fa-mars" style="color:#4A90E2;"></i>' 
                    : '<i class="fas fa-venus" style="color:#E91E63;"></i>';
            }
            
            echo '<div style="line-height:1.6;">';
            
            // Nama Pasien (bold & larger)
            if (!empty($pasien['nama'])) {
                echo '<div style="font-size:15px; font-weight:700; color:#2c3e50; margin-bottom:4px;">';
                echo $gender_icon . ' ' . htmlspecialchars($pasien['nama']);
                echo '</div>';
            }
            
            // No. Rekam Medis
            if (!empty($pasien['kode_rekam_medis'])) {
                echo '<div style="font-size:12px; margin-bottom:2px;">';
                echo '<span style="color:#7f8c8d;">RM:</span> ';
                echo '<span style="font-weight:600; color:#34495e;">' . htmlspecialchars($pasien['kode_rekam_medis']) . '</span>';
                echo '</div>';
            }
            
            // Tanggal Lahir / Umur
            if (!empty($pasien['tanggal_lahir'])) {
                echo '<div style="font-size:12px; margin-bottom:2px;">';
                echo '<span style="color:#7f8c8d;">Lahir:</span> ';
                echo '<span style="font-weight:600; color:#34495e;">' . date('d/m/Y', strtotime($pasien['tanggal_lahir'])) . $umur_text . '</span>';
                echo '</div>';
            }
            
            echo '<hr style="margin:6px 0; border:none; border-top:1px solid #ecf0f1;">';
            
            // No. Rawat
            echo '<div style="font-size:11px; margin-bottom:2px;">';
            echo '<span style="color:#7f8c8d;">No.Rawat:</span> ';
            echo '<span style="font-weight:600; color:#34495e;">' . htmlspecialchars($no_rawat) . '</span>';
            echo '</div>';
            
            // Kode Paket
            echo '<div style="font-size:11px; margin-bottom:2px;">';
            echo '<span style="color:#7f8c8d;">Paket:</span> ';
            echo '<span style="font-weight:600; color:#34495e;">' . htmlspecialchars($kode_paket) . '</span>';
            echo '</div>';
            
            // Tanggal Operasi
            echo '<div style="font-size:11px; margin-bottom:2px;">';
            echo '<span style="color:#7f8c8d;">Tgl.Op:</span> ';
            echo '<span style="font-weight:600; color:#34495e;">' . date('d/m/Y', strtotime($tanggal)) . '</span>';
            echo '</div>';
            
            // Dokter
            if (!empty($pasien['kd_dokter'])) {
                echo '<div style="font-size:11px;">';
                echo '<span style="color:#7f8c8d;">Dokter:</span> ';
                echo '<span style="font-weight:600; color:#34495e;">' . htmlspecialchars($pasien['kd_dokter']) . '</span>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div style="text-align:center; padding:20px; color:#95a5a6; font-style:italic;">';
            echo '<i class="fas fa-user-injured" style="font-size:24px; margin-bottom:8px;"></i><br>';
            echo 'Tempelkan Stiker<br>Identitas Pasien';
            echo '</div>';
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