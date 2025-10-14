<?php
$page_title = "Daftar Pasien Booking Operasi";
require_once __DIR__ . '/../config/database.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Query untuk mengambil data booking operasi
$query = "SELECT * FROM booking_operasi ORDER BY tanggal DESC, jam_mulai DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pasien_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/assets/css/style.css">
<div class="header">
    <div class="logo">
        <img src="assets/images/logo.png" alt="Logo Rumah Sakit" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/a/ac/No_image_available.svg'">
        <div>
            <strong>Rumah Sakit Umum</strong><br>
            <span style="color:#396cf0; font-weight:bold;">PRASETYA BUNDA</span>
        </div>
    </div>
</div>
<div class="container">
    <div class="header-actions">
        <h2>Daftar Pasien Booking Operasi</h2>
        <a href="index.php?page=tambah-booking" class="btn btn-success">+ Tambah Booking Baru</a>
    </div>

    <?php
    $show_notif = false;
    if (isset($_GET['status'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $show_notif = true;
        }
    }
    ?>
    <?php if ($show_notif): ?>
        <div class="alert <?php echo $_GET['status'] == 'sukses' ? 'alert-success' : 'alert-danger'; ?>" id="notif-alert">
            <?php
            if ($_GET['status'] == 'sukses') {
                $kode_paket = $_GET['kode_paket'] ?? '';
                echo "Booking berhasil dibuat! Kode Paket: <strong>" . htmlspecialchars($kode_paket) . "</strong>";
            } elseif ($_GET['status'] == 'gagal') {
                echo "Booking gagal dibuat! Mohon periksa kembali inputan Anda.";
            } elseif ($_GET['status'] == 'error') {
                echo "Terjadi kesalahan sistem! Data gagal masuk ke database.";
            }
            ?>
        </div>
        <script>
        setTimeout(function() {
            var notif = document.getElementById('notif-alert');
            if (notif) notif.style.display = 'none';
        }, 3000);
        </script>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>No. Rawat</th>
                <th>Nama Pasien</th>
                <th>Kode Paket</th>
                <th>Kode RM</th>
                <th>Nama Pasien</th>
                <th>Tanggal Operasi</th>
                <th>Jam Mulai</th>
                <th>Dokter</th>
                <th>Ruang OK</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pasien_list) > 0): ?>
                <?php foreach ($pasien_list as $pasien): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pasien['no_rawat']); ?></td>
                        <td>
                            <span class="kode-paket"><?php echo htmlspecialchars($pasien['kode_paket']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($pasien['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['jam_mulai']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['kd_dokter']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['kd_ruang_ok']); ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                                <?= htmlspecialchars($pasien['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=detail-pasien&no_rawat=<?= urlencode($pasien['no_rawat']); ?>&kode_paket=<?= urlencode($pasien['kode_paket']); ?>&tanggal=<?= urlencode($pasien['tanggal']); ?>&jam_mulai=<?= urlencode($pasien['jam_mulai']); ?>" 
                               class="btn btn-info btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">
                        <p>Belum ada data booking operasi.</p>
                        <a href="index.php?page=tambah-booking" class="btn btn-primary">Tambah Booking Pertama</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
