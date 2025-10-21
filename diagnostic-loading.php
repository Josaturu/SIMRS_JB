<?php
/**
 * DIAGNOSTIC SCRIPT - CEK LOADING LAMA
 * Letakkan di root folder SIMRS_JB
 * Akses: http://localhost/SIMRS_JB/diagnostic-loading.php
 */

// Koneksi database
include_once 'config/database.php';

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
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2196f3; border-bottom: 3px solid #2196f3; padding-bottom: 10px; }
        h2 { color: #1976d2; margin-top: 30px; background: #e3f2fd; padding: 10px; border-radius: 4px; }
        h3 { color: #333; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #2196f3; color: white; }
        table tr:nth-child(even) { background: #f9f9f9; }
        pre { background: #263238; color: #aed581; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 14px; }
        .box { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #2196f3; }
        .box-success { background: #c8e6c9; border-left-color: #4caf50; }
        .box-warning { background: #fff9c4; border-left-color: #ff9800; }
        .box-error { background: #ffcdd2; border-left-color: #f44336; }
        .time-good { background: #4caf50; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .time-ok { background: #ff9800; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .time-bad { background: #f44336; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #4caf50; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .badge-error { background: #f44336; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DIAGNOSTIC: Loading Lama - Informed Consent</h1>
        <p><strong>Test dengan data:</strong></p>
        <ul>
            <li>no_rawat: <code><?= htmlspecialchars($no_rawat) ?></code></li>
            <li>kode_paket: <code><?= htmlspecialchars($kode_paket) ?></code></li>
            <li>tanggal: <code><?= htmlspecialchars($tanggal) ?></code></li>
            <li>jam_mulai: <code><?= htmlspecialchars($jam_mulai) ?></code></li>
        </ul>
        <p><em>💡 Ubah parameter di URL jika ingin test data lain: ?no_rawat=RM001&kode_paket=PKT001&tanggal=2025-10-20&jam_mulai=10:00:00</em></p>
        <hr>

        <?php
        try {
            $database = new Database();
            $db = $database->getConnection();

            // ============================================
            // TEST 1: JUMLAH DATA
            // ============================================
            ?>
            <h2>📊 Test 1: Jumlah Data di Tabel</h2>
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
                    try {
                        $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $total = $result['total'];
                        
                        if ($total > 5000) {
                            $status = '<span class="badge badge-error">❌ Sangat Banyak</span>';
                            $note = 'Perlu index!';
                        } elseif ($total > 1000) {
                            $status = '<span class="badge badge-warning">⚠️ Cukup Banyak</span>';
                            $note = 'Perlu index';
                        } else {
                            $status = '<span class="badge badge-success">✅ Normal</span>';
                            $note = '';
                        }
                        
                        echo "<tr>";
                        echo "<td><strong>$table</strong></td>";
                        echo "<td>" . number_format($total) . " rows</td>";
                        echo "<td>$status $note</td>";
                        echo "</tr>";
                    } catch (Exception $e) {
                        echo "<tr>";
                        echo "<td><strong>$table</strong></td>";
                        echo "<td colspan='2'><span class='error'>Error: " . $e->getMessage() . "</span></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>

            <?php
            // ============================================
            // TEST 2: CEK INDEX
            // ============================================
            ?>
            <h2>🔑 Test 2: Index yang Ada di Setiap Tabel</h2>
            <?php
            foreach ($tables as $table) {
                echo "<h3>📋 Tabel: $table</h3>";
                try {
                    $stmt = $db->query("SHOW INDEX FROM $table");
                    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($indexes)) {
                        echo '<div class="box box-error"><span class="error">❌ TIDAK ADA INDEX!</span> Tabel ini akan sangat lambat saat query!</div>';
                    } else {
                        echo "<table>";
                        echo "<tr><th>Index Name</th><th>Column</th><th>Unique</th><th>Type</th></tr>";
                        $shown = [];
                        foreach ($indexes as $index) {
                            $key = $index['Key_name'] . '_' . $index['Seq_in_index'];
                            if (!in_array($key, $shown)) {
                                $shown[] = $key;
                                $unique = $index['Non_unique'] == 0 ? '<span class="badge badge-success">✅ Ya</span>' : '<span class="badge">Tidak</span>';
                                echo "<tr>";
                                echo "<td><strong>{$index['Key_name']}</strong></td>";
                                echo "<td>{$index['Column_name']}</td>";
                                echo "<td>$unique</td>";
                                echo "<td>{$index['Index_type']}</td>";
                                echo "</tr>";
                            }
                        }
                        echo "</table>";
                        
                        // Cek apakah ada index yang dibutuhkan
                        $has_lookup = false;
                        foreach ($indexes as $index) {
                            if (in_array($index['Key_name'], ['idx_lookup', 'unique_informed_consent'])) {
                                $has_lookup = true;
                                break;
                            }
                        }
                        
                        if (!$has_lookup && $table != 'pasien') {
                            echo '<div class="box box-warning"><span class="warning">⚠️ Index "idx_lookup" tidak ditemukan!</span> Query WHERE akan lambat!</div>';
                        } else {
                            echo '<div class="box box-success"><span class="success">✅ Index sudah ada!</span></div>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="box box-error"><span class="error">Error: ' . $e->getMessage() . '</span></div>';
                }
            }
            ?>

            <?php
            // ============================================
            // TEST 3: PERFORMA QUERY BOOKING
            // ============================================
            ?>
            <h2>⏱️ Test 3: Performa Query Booking Operasi</h2>
            <?php
            $start = microtime(true);
            
            try {
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
                    $box_class = 'box-success';
                } elseif ($time1 < 200) {
                    $time_class = 'time-ok';
                    $time_status = '⚠️ Lumayan (bisa lebih cepat)';
                    $box_class = 'box-warning';
                } else {
                    $time_class = 'time-bad';
                    $time_status = '❌ LAMBAT!';
                    $box_class = 'box-error';
                }
                
                echo "<div class='box $box_class'>";
                echo "<p><strong>⏱️ Waktu Query:</strong> <span class='$time_class'>" . number_format($time1, 2) . " ms</span> $time_status</p>";
                echo "<p><strong>📄 Data Ditemukan:</strong> " . ($booking ? "✅ Ya" : "❌ Tidak (normal jika data test)") . "</p>";
                echo "</div>";
                
                // EXPLAIN
                echo "<h4>🔍 EXPLAIN Query (Detail Teknis):</h4>";
                $explain_query = "EXPLAIN SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin
                                  FROM booking_operasi bo
                                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                                  WHERE bo.no_rawat = '$no_rawat' AND bo.kode_paket = '$kode_paket' AND bo.tanggal = '$tanggal' AND bo.jam_mulai = '$jam_mulai'";
                $explain = $db->query($explain_query)->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table>";
                echo "<tr><th>Table</th><th>Type</th><th>Key</th><th>Rows</th><th>Extra</th></tr>";
                foreach ($explain as $row) {
                    if ($row['type'] == 'ALL') {
                        $type_status = '<span class="badge badge-error">❌ FULL SCAN</span>';
                    } elseif (in_array($row['type'], ['ref', 'eq_ref', 'const'])) {
                        $type_status = '<span class="badge badge-success">✅ ' . $row['type'] . '</span>';
                    } else {
                        $type_status = '<span class="badge badge-warning">' . $row['type'] . '</span>';
                    }
                    
                    $key_status = empty($row['key']) || $row['key'] == 'NULL' 
                        ? '<span class="badge badge-error">❌ NULL</span>' 
                        : '<span class="badge badge-success">✅ ' . $row['key'] . '</span>';
                    
                    echo "<tr>";
                    echo "<td><strong>{$row['table']}</strong></td>";
                    echo "<td>$type_status</td>";
                    echo "<td>$key_status</td>";
                    echo "<td>{$row['rows']}</td>";
                    echo "<td><small>{$row['Extra']}</small></td>";
                    echo "</tr>";
                }
                echo "</table>";
                
            } catch (Exception $e) {
                echo '<div class="box box-error"><span class="error">Error: ' . $e->getMessage() . '</span></div>';
                $time1 = 0;
            }
            ?>

            <?php
            // ============================================
            // TEST 4: PERFORMA QUERY INFORMED CONSENT
            // ============================================
            ?>
            <h2>⏱️ Test 4: Performa Query Informed Consent</h2>
            <?php
            $start = microtime(true);
            
            try {
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
                    $box_class = 'box-success';
                } elseif ($time2 < 200) {
                    $time_class = 'time-ok';
                    $time_status = '⚠️ Lumayan (bisa lebih cepat)';
                    $box_class = 'box-warning';
                } else {
                    $time_class = 'time-bad';
                    $time_status = '❌ LAMBAT!';
                    $box_class = 'box-error';
                }
                
                echo "<div class='box $box_class'>";
                echo "<p><strong>⏱️ Waktu Query:</strong> <span class='$time_class'>" . number_format($time2, 2) . " ms</span> $time_status</p>";
                echo "<p><strong>📄 Data Ditemukan:</strong> " . ($consent ? "✅ Ya" : "❌ Tidak (normal jika data test)") . "</p>";
                echo "</div>";
                
                // EXPLAIN
                echo "<h4>🔍 EXPLAIN Query (Detail Teknis):</h4>";
                $explain_query = "EXPLAIN SELECT * FROM tbl_anestesi_informed_consent_anestesi 
                                  WHERE no_rawat = '$no_rawat' AND kode_paket = '$kode_paket' AND tanggal = '$tanggal' AND jam_mulai = '$jam_mulai'";
                $explain = $db->query($explain_query)->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table>";
                echo "<tr><th>Table</th><th>Type</th><th>Key</th><th>Rows</th><th>Extra</th></tr>";
                foreach ($explain as $row) {
                    if ($row['type'] == 'ALL') {
                        $type_status = '<span class="badge badge-error">❌ FULL SCAN</span>';
                    } elseif (in_array($row['type'], ['ref', 'eq_ref', 'const'])) {
                        $type_status = '<span class="badge badge-success">✅ ' . $row['type'] . '</span>';
                    } else {
                        $type_status = '<span class="badge badge-warning">' . $row['type'] . '</span>';
                    }
                    
                    $key_status = empty($row['key']) || $row['key'] == 'NULL' 
                        ? '<span class="badge badge-error">❌ NULL</span>' 
                        : '<span class="badge badge-success">✅ ' . $row['key'] . '</span>';
                    
                    echo "<tr>";
                    echo "<td><strong>{$row['table']}</strong></td>";
                    echo "<td>$type_status</td>";
                    echo "<td>$key_status</td>";
                    echo "<td>{$row['rows']}</td>";
                    echo "<td><small>{$row['Extra']}</small></td>";
                    echo "</tr>";
                }
                echo "</table>";
                
            } catch (Exception $e) {
                echo '<div class="box box-error"><span class="error">Error: ' . $e->getMessage() . '</span></div>';
                $time2 = 0;
            }
            ?>

            <?php
            // ============================================
            // TEST 5: TOTAL WAKTU
            // ============================================
            ?>
            <h2>📊 Test 5: Total Waktu Loading Form</h2>
            <?php
            $total_time = $time1 + $time2;
            
            if ($total_time < 100) {
                $total_class = 'time-good';
                $total_status = '✅ SANGAT CEPAT! User experience bagus!';
                $box_class = 'box-success';
            } elseif ($total_time < 500) {
                $total_class = 'time-ok';
                $total_status = '⚠️ LUMAYAN. Bisa lebih cepat dengan index.';
                $box_class = 'box-warning';
            } else {
                $total_class = 'time-bad';
                $total_status = '❌ LAMBAT! User akan menunggu lama!';
                $box_class = 'box-error';
            }
            
            echo "<div class='box $box_class'>";
            echo "<p><strong>⏱️ Query Booking:</strong> " . number_format($time1, 2) . " ms</p>";
            echo "<p><strong>⏱️ Query Informed Consent:</strong> " . number_format($time2, 2) . " ms</p>";
            echo "<p><strong>🎯 TOTAL WAKTU:</strong> <span class='$total_class'>" . number_format($total_time, 2) . " ms</span></p>";
            echo "<p><strong>📝 Status:</strong> $total_status</p>";
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
            $stmt = $db->query("SHOW INDEX FROM pasien WHERE Column_name = 'kd_pasien' AND Key_name != 'PRIMARY'");
            if ($stmt->rowCount() == 0) {
                $recommendations[] = "ALTER TABLE pasien ADD INDEX idx_kd_pasien (kd_pasien);";
            }
            
            // Cek index kd_pasien di booking
            $stmt = $db->query("SHOW INDEX FROM booking_operasi WHERE Column_name = 'kd_pasien'");
            if ($stmt->rowCount() == 0) {
                $recommendations[] = "ALTER TABLE booking_operasi ADD INDEX idx_kd_pasien (kd_pasien);";
            }
            
            if (empty($recommendations)) {
                echo '<div class="box box-success">';
                echo '<h3>✅ Semua index sudah ada!</h3>';
                echo '<p>Tidak ada rekomendasi tambahan untuk index.</p>';
                echo '</div>';
                
                if ($total_time > 500) {
                    echo '<div class="box box-warning">';
                    echo '<h3>⚠️ Tapi query masih lambat...</h3>';
                    echo '<p><strong>Kemungkinan masalah:</strong></p>';
                    echo '<ul>';
                    echo '<li>🔄 MySQL belum di-restart setelah tambah index</li>';
                    echo '<li>🌐 Browser cache (clear dengan Ctrl+Shift+Del)</li>';
                    echo '<li>📜 JavaScript yang berat di form</li>';
                    echo '<li>💻 Server resource terbatas (CPU/RAM tinggi)</li>';
                    echo '<li>🗄️ Tabel perlu di-OPTIMIZE</li>';
                    echo '</ul>';
                    echo '<p><strong>Solusi:</strong></p>';
                    echo '<ol>';
                    echo '<li>Restart MySQL (XAMPP Control Panel → Stop → Start)</li>';
                    echo '<li>Jalankan: <code>OPTIMIZE TABLE booking_operasi, tbl_anestesi_informed_consent_anestesi, pasien;</code></li>';
                    echo '<li>Clear browser cache (Ctrl+Shift+Del)</li>';
                    echo '<li>Refresh halaman ini (F5)</li>';
                    echo '</ol>';
                    echo '</div>';
                }
            } else {
                echo '<div class="box box-error">';
                echo '<h3>❌ Index belum lengkap!</h3>';
                echo '<p><strong>Jalankan query ini di phpMyAdmin:</strong></p>';
                echo '<pre>';
                echo "USE dbanestesi;\n\n";
                echo "-- Tambah index untuk mempercepat query\n";
                foreach ($recommendations as $sql) {
                    echo $sql . "\n";
                }
                echo "\n-- Optimize tables\n";
                echo "OPTIMIZE TABLE booking_operasi;\n";
                echo "OPTIMIZE TABLE tbl_anestesi_informed_consent_anestesi;\n";
                echo "OPTIMIZE TABLE pasien;\n";
                echo '</pre>';
                echo '<p><strong>📋 Langkah-langkah:</strong></p>';
                echo '<ol>';
                echo '<li>Copy query di atas</li>';
                echo '<li>Buka phpMyAdmin → Database "dbanestesi" → Tab "SQL"</li>';
                echo '<li>Paste dan klik "Go"</li>';
                echo '<li>Restart MySQL (XAMPP Control Panel → Stop → Start)</li>';
                echo '<li>Clear browser cache (Ctrl+Shift+Del)</li>';
                echo '<li>Refresh halaman ini (F5) untuk test ulang</li>';
                echo '</ol>';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="box box-error">';
            echo '<h3>❌ Error Database Connection</h3>';
            echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
            echo '<p><strong>Solusi:</strong></p>';
            echo '<ul>';
            echo '<li>Pastikan MySQL sudah running (XAMPP Control Panel)</li>';
            echo '<li>Cek file config/database.php</li>';
            echo '<li>Pastikan username/password benar</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <hr>
        <p style="text-align: center; color: #666;">
            <em>✅ Diagnostic selesai. Refresh halaman ini (F5) untuk test ulang setelah fix.</em>
        </p>
    </div>
</body>
</html>
