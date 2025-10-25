<?php
$page_title = "Daftar Pasien Booking Operasi";
require_once __DIR__ . '/../config/database.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Query untuk mengambil data booking operasi dengan JOIN ke tabel pasien
$query = "SELECT 
            b.no_rawat, 
            b.kode_paket, 
            b.tanggal, 
            b.jam_mulai, 
            b.jam_selesai,
            b.status, 
            b.kd_dokter, 
            b.kd_ruang_ok,
            b.kd_pasien,
            p.nama as nama_pasien,
            p.kode_rekam_medis,
            p.alamat,
            p.tanggal_lahir,
            p.jenis_kelamin
          FROM booking_operasi b
          LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
          ORDER BY b.tanggal DESC, b.jam_mulai DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pasien_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variabel kosong untuk header (tidak perlu stiker pasien di daftar)
$no_rawat = '';
$kode_paket = '';
$tanggal = '';
$pasien = [];

include __DIR__ . '/../includes/header.php';
?>

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

    <table class="table">
        <thead>
            <tr>
                <th>No. Rawat</th>
                <th>Nama Pasien</th>
                <th>Kode Paket</th>
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
                        <td><strong><?php echo htmlspecialchars($pasien['nama_pasien'] ?? '-'); ?></strong></td>
                        <td>
                            <span class="kode-paket"><?php echo htmlspecialchars($pasien['kode_paket']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($pasien['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['jam_mulai']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['kd_dokter']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['kd_ruang_ok']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                                <?php echo htmlspecialchars($pasien['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=detail-pasien&no_rawat=<?php echo urlencode($pasien['no_rawat']); ?>&kode_paket=<?php echo urlencode($pasien['kode_paket']); ?>&tanggal=<?php echo urlencode($pasien['tanggal']); ?>&jam_mulai=<?php echo urlencode($pasien['jam_mulai']); ?>" 
                               class="btn btn-info btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <p>Belum ada data booking operasi.</p>
                        <a href="index.php?page=tambah-booking" class="btn btn-primary">Tambah Booking Pertama</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
