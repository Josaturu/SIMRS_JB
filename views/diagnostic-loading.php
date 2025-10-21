<?php
/**
 * DIAGNOSTIC SCRIPT - CEK LOADING LAMA
 * Akses: http://localhost/SIMRS_JB/views/diagnostic-loading.php
 */

// Koneksi database
include_once '../config/database.php';

// Test data (bisa diganti dengan data real)
$no_rawat = $_GET['no_rawat'] ?? 'TEST001';
$kode_paket = $_GET['kode_paket'] ?? 'PKT001';
$tanggal = $_GET['tanggal'] ?? '2025-10-20';
$jam_mulai = $_GET['jam_mulai'] ?? '10:00:00';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Loading</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2196f3; border-bottom: 3px solid #2196f3; padding-bottom: 10px; }
        h2 { color: #1976d2; margin-top: 30px; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #2196f3; color: white; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .box { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #2196f3; }
        .time-good { background: #c8e6c9; color: #2e7d32; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .time-ok { background: #fff9c4; color: #f57f17; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .time-bad { background: #ffcdd2; color: #c62828; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DIAGNOSTIC: Loading Lama</h1>
        <p><strong>Test dengan data:</strong> no_rawat=<?= htmlspecialchars($no_rawat) ?>, kode_paket=<?= htmlspecialchars($kode_paket) ?>, tanggal=<?= htmlspecialchars($tanggal) ?>, jam_mulai=<?= htmlspecialchars($jam_mulai) ?></p>
        <p><em>Ubah parameter di URL jika ingin test data lain</em></p>
        <hr>

        <?php
        $database = new Database();
        $db = $database->getConnection();

        // ============================================
        // TEST 1: JUMLAH DATA
        // ============================================
        ?>
        <h2>📊 Test 1: Jumlah Data</h2>
        <table>
            <tr>
                <th>Tabel</th>
                <th>Jumlah Data</th>
                <th>Status</th>
            </tr>
            <?php
            $tables = [
                'booking_operasi',
                'tbl_anestesi_informed_consent_anestesi',
                'pasien'
            ];
            
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = $result['total'];
                
                if ($total > 5000) {
                    $status = '<span class="error">❌ Banyak sekali (perlu index!)</span>';
                } elseif ($total > 1000) {
                    $status = '<span class="warning">⚠️ Cukup banyak (perlu index)</span>';
                } else {
                    $status = '<span class="success">✅ Normal</span>';
                }
                
                echo "<tr>";
                echo "<td><strong>$table</strong></td>";
                echo "<td>" . number_format($total) . " rows</td>";
                echo "<td>$status</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <?php
        // ============================================
        // TEST 2: CEK INDEX
        // ============================================
        ?>
        <h2>🔑 Test 2: Index yang Ada</h2>
        <?php
        foreach ($tables as $table) {
            echo "<h3>$table:</h3>";
            $stmt = $db->query("SHOW INDEX FROM $table");
            $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($indexes)) {
                echo '<div class="box"><span class="error">❌ TIDAK ADA INDEX!</span> Tabel ini sangat lambat!</div>';
            } else {
                echo "<table>";
                echo "<tr><th>Index Name</th><th>Column</th><th>Unique</th></tr>";
                foreach ($indexes as $index) {
                    $unique = $index['Non_unique'] == 0 ? '✅ Ya' : '❌ Tidak';
                    echo "<tr>";
                    echo "<td><strong>{$index['Key_name']}</strong></td>";
                    echo "<td>{$index['Column_name']}</td>";
                    echo "<td>$unique</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Cek apakah ada index yang dibutuhkan
                $has_lookup = false;
                foreach ($indexes as $index) {
                    if ($index['Key_name'] == 'idx_lookup' || $index['Key_name'] == 'unique_informed_consent') {
                        $has_lookup = true;
                        break;
                    }
                }
                
                if (!$has_lookup && $table != 'pasien') {
                    echo '<div class="box"><span class="warning">⚠️ Index "idx_lookup" tidak ditemukan!</span> Query akan lambat!</div>';
                }
            }
        }
        ?>

        <?php
        // ============================================
        // TEST 3: PERFORMA QUERY BOOKING
        // ============================================
        ?>
        <h2>⏱️ Test 3: Performa Query Booking</h2>
        <?php
        $start = microtime(true);
        
        $query = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE bo.no_rawat = ? AND bo.kode_paket = ? AND bo.tanggal = ? AND bo.jam_mulai = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $end = microtime(true);
        $time1 = ($end - $start) * 1000; // ms
        
        if ($time1 < 50) {
            $time_class = 'time-good';
            $time_status = '✅ SANGAT CEPAT!';
        } elseif ($time1 < 200) {
            $time_class = 'time-ok';
            $time_status = '⚠️ Lumayan';
        } else {
            $time_class = 'time-bad';
            $time_status = '❌ LAMBAT!';
        }
        
        echo "<div class='box'>";
        echo "<p><strong>Waktu Query:</strong> <span class='$time_class'>" . number_format($time1, 2) . " ms</span> $time_status</p>";
        echo "<p><strong>Data Ditemukan:</strong> " . ($booking ? "✅ Ya" : "❌ Tidak") . "</p>";
        echo "</div>";
        
        // EXPLAIN
        echo "<h4>EXPLAIN Query (Detail Teknis):</h4>";
        $explain_query = "EXPLAIN SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin
                          FROM booking_operasi bo
                          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                          WHERE bo.no_rawat = '$no_rawat' AND bo.kode_paket = '$kode_paket' AND bo.tanggal = '$tanggal' AND bo.jam_mulai = '$jam_mulai'";
        $explain = $db->query($explain_query)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Table</th><th>Type</th><th>Key</th><th>Rows</th></tr>";
        foreach ($explain as $row) {
            $type_status = $row['type'] == 'ALL' ? '<span class="error">❌ FULL SCAN</span>' : '<span class="success">✅ ' . $row['type'] . '</span>';
            $key_status = empty($row['key']) ? '<span class="error">❌ NULL</span>' : '<span class="success">✅ ' . $row['key'] . '</span>';
            
            echo "<tr>";
            echo "<td>{$row['table']}</td>";
            echo "<td>$type_status</td>";
            echo "<td>$key_status</td>";
            echo "<td>{$row['rows']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>

        <?php
        // ============================================
        // TEST 4: PERFORMA QUERY INFORMED CONSENT
        // ============================================
        ?>
        <h2>⏱️ Test 4: Performa Query Informed Consent</h2>
        <?php
        $start = microtime(true);
        
        $query_consent = "SELECT * FROM tbl_anestesi_informed_consent_anestesi 
                          WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
        $stmt_consent = $db->prepare($query_consent);
        $stmt_consent->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
        $consent = $stmt_consent->fetch(PDO::FETCH_ASSOC);
        
        $end = microtime(true);
        $time2 = ($end - $start) * 1000;
        
        if ($time2 < 50) {
            $time_class = 'time-good';
            $time_status = '✅ SANGAT CEPAT!';
        } elseif ($time2 < 200) {
            $time_class = 'time-ok';
            $time_status = '⚠️ Lumayan';
        } else {
            $time_class = 'time-bad';
            $time_status = '❌ LAMBAT!';
        }
        
        echo "<div class='box'>";
        echo "<p><strong>Waktu Query:</strong> <span class='$time_class'>" . number_format($time2, 2) . " ms</span> $time_status</p>";
        echo "<p><strong>Data Ditemukan:</strong> " . ($consent ? "✅ Ya" : "❌ Tidak") . "</p>";
        echo "</div>";
        
        // EXPLAIN
        echo "<h4>EXPLAIN Query (Detail Teknis):</h4>";
        $explain_query = "EXPLAIN SELECT * FROM tbl_anestesi_informed_consent_anestesi 
                          WHERE no_rawat = '$no_rawat' AND kode_paket = '$kode_paket' AND tanggal = '$tanggal' AND jam_mulai = '$jam_mulai'";
        $explain = $db->query($explain_query)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Table</th><th>Type</th><th>Key</th><th>Rows</th></tr>";
        foreach ($explain as $row) {
            $type_status = $row['type'] == 'ALL' ? '<span class="error">❌ FULL SCAN</span>' : '<span class="success">✅ ' . $row['type'] . '</span>';
            $key_status = empty($row['key']) ? '<span class="error">❌ NULL</span>' : '<span class="success">✅ ' . $row['key'] . '</span>';
            
            echo "<tr>";
            echo "<td>{$row['table']}</td>";
            echo "<td>$type_status</td>";
            echo "<td>$key_status</td>";
            echo "<td>{$row['rows']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>

        <?php
        // ============================================
        // TEST 5: TOTAL WAKTU
        // ============================================
        ?>
        <h2>📊 Test 5: Total Waktu Loading</h2>
        <?php
        $total_time = $time1 + $time2;
        
        if ($total_time < 100) {
            $total_class = 'time-good';
            $total_status = '✅ SANGAT CEPAT! User experience bagus!';
        } elseif ($total_time < 500) {
            $total_class = 'time-ok';
            $total_status = '⚠️ LUMAYAN. Bisa lebih cepat dengan index.';
        } else {
            $total_class = 'time-bad';
            $total_status = '❌ LAMBAT! User akan menunggu lama!';
        }
        
        echo "<div class='box'>";
        echo "<p><strong>Query Booking:</strong> " . number_format($time1, 2) . " ms</p>";
        echo "<p><strong>Query Informed Consent:</strong> " . number_format($time2, 2) . " ms</p>";
        echo "<p><strong>TOTAL WAKTU:</strong> <span class='$total_class'>" . number_format($total_time, 2) . " ms</span></p>";
        echo "<p>$total_status</p>";
        echo "</div>";
        ?>

        <?php
        // ============================================
        // REKOMENDASI
        // ============================================
        ?>
        <h2>💡 Rekomendasi Fix</h2>
        <?php
        $recommendations = [];
        
        // Cek index booking_operasi
        $stmt = $db->query("SHOW INDEX FROM booking_operasi WHERE Key_name = 'idx_lookup'");
        if ($stmt->rowCount() == 0) {
            $recommendations[] = "ALTER TABLE booking_operasi ADD INDEX idx_lookup (no_rawat, kode_paket, tanggal, jam_mulai);";
        }
        
        // Cek index informed consent
        $stmt = $db->query("SHOW INDEX FROM tbl_anestesi_informed_consent_anestesi WHERE Key_name = 'idx_lookup'");
        if ($stmt->rowCount() == 0) {
            $recommendations[] = "ALTER TABLE tbl_anestesi_informed_consent_anestesi ADD INDEX idx_lookup (no_rawat, kode_paket, tanggal, jam_mulai);";
        }
        
        // Cek index pasien
        $stmt = $db->query("SHOW INDEX FROM pasien WHERE Column_name = 'kd_pasien'");
        if ($stmt->rowCount() == 0) {
            $recommendations[] = "ALTER TABLE pasien ADD INDEX idx_kd_pasien (kd_pasien);";
        }
        
        // Cek index kd_pasien di booking
        $stmt = $db->query("SHOW INDEX FROM booking_operasi WHERE Column_name = 'kd_pasien'");
        if ($stmt->rowCount() == 0) {
            $recommendations[] = "ALTER TABLE booking_operasi ADD INDEX idx_kd_pasien (kd_pasien);";
        }
        
        if (empty($recommendations)) {
            echo '<div class="box"><span class="success">✅ Semua index sudah ada! Tidak ada rekomendasi.</span></div>';
            
            if ($total_time > 500) {
                echo '<div class="box">';
                echo '<span class="warning">⚠️ Tapi query masih lambat. Kemungkinan masalah:</span>';
                echo '<ul>';
                echo '<li>MySQL belum di-restart setelah tambah index</li>';
                echo '<li>Browser cache (clear dengan Ctrl+Shift+Del)</li>';
                echo '<li>JavaScript yang berat di form</li>';
                echo '<li>Server resource terbatas (CPU/RAM)</li>';
                echo '</ul>';
                echo '</div>';
            }
        } else {
            echo '<div class="box">';
            echo '<span class="error">❌ Index belum lengkap! Jalankan query ini di phpMyAdmin:</span>';
            echo '<pre>';
            echo "USE dbanestesi;\n\n";
            foreach ($recommendations as $sql) {
                echo $sql . "\n";
            }
            echo "\n-- Optimize tables\n";
            echo "OPTIMIZE TABLE booking_operasi;\n";
            echo "OPTIMIZE TABLE tbl_anestesi_informed_consent_anestesi;\n";
            echo "OPTIMIZE TABLE pasien;\n";
            echo '</pre>';
            echo '<p><strong>Setelah jalankan query:</strong></p>';
            echo '<ol>';
            echo '<li>Restart MySQL (XAMPP Control Panel → Stop → Start)</li>';
            echo '<li>Clear browser cache (Ctrl+Shift+Del)</li>';
            echo '<li>Refresh halaman ini (F5)</li>';
            echo '</ol>';
            echo '</div>';
        }
        ?>

        <hr>
        <p><em>Diagnostic selesai. Refresh halaman ini (F5) untuk test ulang.</em></p>
    </div>
</body>
</html>
