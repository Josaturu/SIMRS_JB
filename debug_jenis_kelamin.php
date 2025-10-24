<?php
/**
 * DEBUG SCRIPT: Jenis Kelamin Konsultasi Anestesi
 * 
 * Script ini untuk debugging masalah jenis kelamin tidak tersimpan
 * Jalankan script ini di browser untuk melihat:
 * 1. Struktur tabel database
 * 2. Data yang tersimpan
 * 3. PHP error log terbaru
 */

session_start();
include_once 'config/database.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Jenis Kelamin - Konsultasi Anestesi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #004d80; border-bottom: 3px solid #004d80; padding-bottom: 10px; }
        h2 { color: #0066a1; margin-top: 30px; border-left: 4px solid #0066a1; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #004d80; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f4f4f4; padding: 10px; border-left: 3px solid #004d80; font-family: monospace; margin: 10px 0; }
        .null { color: #999; font-style: italic; }
        .highlight { background: yellow; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug: Jenis Kelamin Konsultasi Anestesi</h1>
        <div class="info">
            <strong>Waktu Debug:</strong> <?= date('Y-m-d H:i:s') ?><br>
            <strong>Server:</strong> <?= $_SERVER['SERVER_NAME'] ?><br>
            <strong>PHP Version:</strong> <?= phpversion() ?>
        </div>

        <?php
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // ========================================
            // 1. CEK STRUKTUR TABEL
            // ========================================
            echo '<h2>1. Struktur Tabel: tbl_anestesi_konsultasi_anestesi</h2>';
            
            $query = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY 
                      FROM information_schema.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
                      ORDER BY ORDINAL_POSITION";
            $stmt = $db->query($query);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($columns) > 0) {
                echo '<div class="success">✓ Tabel ditemukan dengan ' . count($columns) . ' kolom</div>';
                
                // Cari kolom jenis_kelamin
                $jk_column = null;
                foreach ($columns as $col) {
                    if ($col['COLUMN_NAME'] == 'jenis_kelamin') {
                        $jk_column = $col;
                        break;
                    }
                }
                
                if ($jk_column) {
                    echo '<div class="success">✓ Kolom <strong>jenis_kelamin</strong> ditemukan!</div>';
                    echo '<div class="code">';
                    echo '<strong>Nama:</strong> ' . $jk_column['COLUMN_NAME'] . '<br>';
                    echo '<strong>Tipe:</strong> ' . $jk_column['COLUMN_TYPE'] . '<br>';
                    echo '<strong>Nullable:</strong> ' . $jk_column['IS_NULLABLE'] . '<br>';
                    echo '<strong>Default:</strong> ' . ($jk_column['COLUMN_DEFAULT'] ?? '<span class="null">NULL</span>') . '<br>';
                    echo '</div>';
                } else {
                    echo '<div class="error">✗ Kolom <strong>jenis_kelamin</strong> TIDAK DITEMUKAN!</div>';
                    echo '<div class="warning">Solusi: Jalankan query berikut di phpMyAdmin:</div>';
                    echo '<div class="code">ALTER TABLE tbl_anestesi_konsultasi_anestesi<br>ADD COLUMN jenis_kelamin VARCHAR(20) DEFAULT NULL<br>COMMENT \'Jenis kelamin: Laki-laki atau Perempuan\';</div>';
                }
                
                // Tampilkan beberapa kolom penting
                echo '<h3>Kolom Penting:</h3>';
                echo '<table>';
                echo '<tr><th>Nama Kolom</th><th>Tipe</th><th>Nullable</th><th>Default</th></tr>';
                $important_cols = ['id', 'no_rawat', 'kode_paket', 'tanggal', 'jam_mulai', 'jenis_kelamin', 'menikah', 'merokok', 'alkohol'];
                foreach ($columns as $col) {
                    if (in_array($col['COLUMN_NAME'], $important_cols)) {
                        $highlight = ($col['COLUMN_NAME'] == 'jenis_kelamin') ? ' class="highlight"' : '';
                        echo '<tr' . $highlight . '>';
                        echo '<td><strong>' . $col['COLUMN_NAME'] . '</strong></td>';
                        echo '<td>' . $col['COLUMN_TYPE'] . '</td>';
                        echo '<td>' . $col['IS_NULLABLE'] . '</td>';
                        echo '<td>' . ($col['COLUMN_DEFAULT'] ?? '<span class="null">NULL</span>') . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
                
            } else {
                echo '<div class="error">✗ Tabel <strong>tbl_anestesi_konsultasi_anestesi</strong> TIDAK DITEMUKAN!</div>';
            }
            
            // ========================================
            // 2. CEK DATA TERBARU
            // ========================================
            echo '<h2>2. Data 10 Record Terbaru</h2>';
            
            $query = "SELECT id, no_rawat, kode_paket, jenis_kelamin, menikah, merokok, alkohol, created_at 
                      FROM tbl_anestesi_konsultasi_anestesi 
                      ORDER BY id DESC 
                      LIMIT 10";
            $stmt = $db->query($query);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($records) > 0) {
                echo '<div class="success">✓ Ditemukan ' . count($records) . ' record</div>';
                echo '<table>';
                echo '<tr><th>ID</th><th>No Rawat</th><th>Kode Paket</th><th>Jenis Kelamin</th><th>Menikah</th><th>Merokok</th><th>Alkohol</th><th>Created At</th></tr>';
                
                foreach ($records as $rec) {
                    $jk_class = empty($rec['jenis_kelamin']) ? ' class="null"' : '';
                    echo '<tr>';
                    echo '<td>' . $rec['id'] . '</td>';
                    echo '<td>' . $rec['no_rawat'] . '</td>';
                    echo '<td>' . $rec['kode_paket'] . '</td>';
                    echo '<td' . $jk_class . '>' . ($rec['jenis_kelamin'] ?? '<em>NULL</em>') . '</td>';
                    echo '<td>' . ($rec['menikah'] ?? '<span class="null">NULL</span>') . '</td>';
                    echo '<td>' . ($rec['merokok'] ?? '<span class="null">NULL</span>') . '</td>';
                    echo '<td>' . ($rec['alkohol'] ?? '<span class="null">NULL</span>') . '</td>';
                    echo '<td>' . ($rec['created_at'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                // Statistik
                $null_count = 0;
                $filled_count = 0;
                foreach ($records as $rec) {
                    if (empty($rec['jenis_kelamin'])) {
                        $null_count++;
                    } else {
                        $filled_count++;
                    }
                }
                
                if ($null_count > 0) {
                    echo '<div class="warning">⚠ Ditemukan <strong>' . $null_count . '</strong> record dengan jenis_kelamin NULL</div>';
                }
                if ($filled_count > 0) {
                    echo '<div class="success">✓ Ditemukan <strong>' . $filled_count . '</strong> record dengan jenis_kelamin terisi</div>';
                }
                
            } else {
                echo '<div class="warning">⚠ Belum ada data di tabel</div>';
            }
            
            // ========================================
            // 3. CEK PHP ERROR LOG
            // ========================================
            echo '<h2>3. PHP Error Log (50 baris terakhir)</h2>';
            
            $log_file = __DIR__ . '/logs/persiapan-operasi-debug.log';
            if (file_exists($log_file)) {
                $log_content = file($log_file);
                $last_50 = array_slice($log_content, -50);
                
                if (count($last_50) > 0) {
                    echo '<div class="code" style="max-height: 400px; overflow-y: auto;">';
                    foreach ($last_50 as $line) {
                        $highlight = (stripos($line, 'jenis_kelamin') !== false) ? ' style="background: yellow;"' : '';
                        echo '<div' . $highlight . '>' . htmlspecialchars($line) . '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="warning">⚠ Log file kosong</div>';
                }
            } else {
                echo '<div class="warning">⚠ Log file tidak ditemukan: ' . $log_file . '</div>';
            }
            
            // ========================================
            // 4. TEST QUERY INSERT
            // ========================================
            echo '<h2>4. Test Query (Simulasi)</h2>';
            echo '<div class="info">Berikut adalah query yang akan dijalankan saat submit form:</div>';
            echo '<div class="code" style="font-size: 11px; max-height: 300px; overflow-y: auto;">';
            echo 'INSERT INTO tbl_anestesi_konsultasi_anestesi<br>';
            echo '&nbsp;&nbsp;(no_rawat, kode_paket, tanggal, jam_mulai, <strong style="background: yellow;">jenis_kelamin</strong>, menikah, ...)<br>';
            echo 'VALUES<br>';
            echo '&nbsp;&nbsp;(?, ?, ?, ?, <strong style="background: yellow;">?</strong>, ?, ...)<br>';
            echo 'ON DUPLICATE KEY UPDATE<br>';
            echo '&nbsp;&nbsp;<strong style="background: yellow;">jenis_kelamin = VALUES(jenis_kelamin)</strong>,<br>';
            echo '&nbsp;&nbsp;menikah = VALUES(menikah), ...';
            echo '</div>';
            
            // ========================================
            // 5. REKOMENDASI
            // ========================================
            echo '<h2>5. Rekomendasi Debugging</h2>';
            echo '<ol>';
            echo '<li><strong>Cek Console Browser:</strong> Buka Developer Tools (F12) → Console, lalu submit form. Lihat apakah ada log <code>jenis_kelamin: Laki-laki</code></li>';
            echo '<li><strong>Cek Network Tab:</strong> Buka Developer Tools (F12) → Network, lalu submit form. Klik request ke <code>process-konsultasi-anestesi.php</code>, lihat di tab "Payload" apakah <code>jenis_kelamin</code> terkirim</li>';
            echo '<li><strong>Cek PHP Error Log:</strong> Lihat file <code>logs/persiapan-operasi-debug.log</code> atau PHP error log server</li>';
            echo '<li><strong>Test Manual:</strong> Isi form, pilih jenis kelamin, submit, lalu refresh halaman ini untuk melihat apakah data tersimpan</li>';
            echo '<li><strong>Cek Database Langsung:</strong> Buka phpMyAdmin, jalankan query: <code>SELECT * FROM tbl_anestesi_konsultasi_anestesi ORDER BY id DESC LIMIT 1</code></li>';
            echo '</ol>';
            
        } catch (PDOException $e) {
            echo '<div class="error">✗ Database Error: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <h2>6. Quick Actions</h2>
        <div class="info">
            <a href="index.php?page=konsultasi-anestesi" style="display: inline-block; padding: 10px 20px; background: #004d80; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Buka Form Konsultasi</a>
            <a href="javascript:location.reload()" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Refresh Halaman</a>
        </div>
    </div>
</body>
</html>
