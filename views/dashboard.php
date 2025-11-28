<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Dashboard - Jadwal Hari Ini";
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk mengambil data booking HARI INI saja, diurutkan berdasarkan jam mulai
$query = "SELECT 
            b.no_rawat, b.kode_paket, b.tanggal, b.jam_mulai, b.status,
            p.nama as nama_pasien,
            d.nama_dokter,
            r.nama_ruang
          FROM booking_operasi b
          LEFT JOIN pasien p ON b.kd_pasien COLLATE utf8mb4_unicode_ci = p.kd_pasien
          LEFT JOIN tbl_dokter d ON b.dokter_rawat COLLATE utf8mb4_unicode_ci = d.id_dokter
          LEFT JOIN tbl_ruang r ON b.ruang_rawat COLLATE utf8mb4_unicode_ci = r.id_ruang
          WHERE b.tanggal = CURDATE()
          ORDER BY b.jam_mulai ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$jadwal_hari_ini = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/assets.php';
?>
<link rel="stylesheet" href="../assets/css/dashboard.css">

<div class="container">
    <div class="user-info-header">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="../process/logout_process.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php endif; ?>
    </div>

    <div class="dashboard-header">
        <h2><i class="fas fa-calendar-day"></i> Jadwal Operasi Hari Ini (<?php echo date('d F Y'); ?>)</h2>
        <a href="../index.php?page=daftar-pasien" class="btn btn-light"><i class="fas fa-list-alt"></i> Lihat Semua Daftar Pasien</a>
    </div>

    <div class="dashboard-grid">
        <?php if (count($jadwal_hari_ini) > 0): ?>
            <?php foreach ($jadwal_hari_ini as $item): ?>
                <a href="../index.php?page=detail-pasien&from=dashboard&no_rawat=<?php echo urlencode($item['no_rawat']); ?>&kode_paket=<?php echo urlencode($item['kode_paket']); ?>&tanggal=<?php echo urlencode($item['tanggal']); ?>&jam_mulai=<?php echo urlencode($item['jam_mulai']); ?>" class="schedule-card-link">
                    <div class="schedule-card status-<?php echo strtolower(str_replace(' ', '-', $item['status'])); ?>">
                        <div class="card-time"><strong><?php echo htmlspecialchars(substr($item['jam_mulai'], 0, 5)); ?></strong></div>
                        <div class="card-patient">
                            <strong><?php echo htmlspecialchars($item['nama_pasien'] ?? 'N/A'); ?></strong>
                            <span><?php echo htmlspecialchars($item['no_rawat']); ?></span>
                        </div>
                        <div class="card-details">
                            <span><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($item['nama_dokter'] ?? 'N/A'); ?></span>
                            <span><i class="fas fa-procedures"></i> <?php echo htmlspecialchars($item['nama_ruang'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="card-status">
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $item['status'])); ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-schedule">
                <i class="fas fa-check-circle"></i>
                <p>Tidak ada jadwal operasi untuk hari ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
