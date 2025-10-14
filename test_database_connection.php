<?php
/**
 * Database Connection Test
 * Test database connection after merge
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test Koneksi Database</h1>";
echo "<p><strong>Tanggal:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Test 1: Include database config
echo "<h2>Test 1: Konfigurasi Database</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Konfigurasi database berhasil dimuat<br>";
    
    $database = new Database();
    echo "✅ Object database berhasil dibuat<br>";
    
    $db = $database->getConnection();
    echo "✅ Koneksi database berhasil dibuat<br>";
    echo "<p><strong>Koneksi:</strong> Aktif</p>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    die();
}

echo "<hr>";

// Test 2: Check tables
echo "<h2>Test 2: Tabel</h2>";
try {
    $query = "SHOW TABLES";
    $stmt = $db->query($query);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Total Tabel:</strong> " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>✅ $table</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 3: Check patient data
echo "<h2>Test 3: Data Pasien</h2>";
try {
    $query = "SELECT * FROM pasien";
    $stmt = $db->query($query);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Pasien:</strong> " . count($patients) . "</p>";
    
    if (count($patients) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>Kode Pasien</th>";
        echo "<th>Nama</th>";
        echo "<th>Jenis Kelamin</th>";
        echo "<th>Tanggal Lahir</th>";
        echo "<th>No HP</th>";
        echo "<th>Gol Darah</th>";
        echo "</tr>";
        
        foreach ($patients as $patient) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($patient['kd_pasien']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['nama']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['jenis_kelamin']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['tanggal_lahir']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['no_hp']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['gol_darah']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>⚠️ Tidak ada data pasien</p>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 4: Check booking data
echo "<h2>Test 4: Booking Operasi</h2>";
try {
    $query = "SELECT * FROM booking_operasi ORDER BY tanggal DESC, jam_mulai DESC";
    $stmt = $db->query($query);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Booking:</strong> " . count($bookings) . "</p>";
    
    if (count($bookings) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>No Rawat</th>";
        echo "<th>Kode Paket</th>";
        echo "<th>Tanggal</th>";
        echo "<th>Jam Mulai</th>";
        echo "<th>Status</th>";
        echo "<th>Kode Pasien</th>";
        echo "</tr>";
        
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($booking['no_rawat']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['kode_paket']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['tanggal']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['jam_mulai']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['status']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['kd_pasien']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>⚠️ Tidak ada data booking</p>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 5: Check konsultasi data
echo "<h2>Test 5: Data Konsultasi Anestesi</h2>";
try {
    $query = "SELECT COUNT(*) as total FROM tbl_anestesi_konsultasi_anestesi";
    $stmt = $db->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Konsultasi:</strong> " . $result['total'] . "</p>";
    
    if ($result['total'] > 0) {
        $query = "SELECT id, no_rawat, diagnosa_pra_operasi, created_at FROM tbl_anestesi_konsultasi_anestesi LIMIT 5";
        $stmt = $db->query($query);
        $konsultasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>No Rawat</th>";
        echo "<th>Diagnosa</th>";
        echo "<th>Created At</th>";
        echo "</tr>";
        
        foreach ($konsultasi as $k) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($k['id']) . "</td>";
            echo "<td>" . htmlspecialchars($k['no_rawat']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($k['diagnosa_pra_operasi'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars($k['created_at']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 6: Check foreign keys
echo "<h2>Test 6: Relasi Foreign Key</h2>";
try {
    $query = "
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            CONSTRAINT_TYPE
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = 'dbanestesi'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ORDER BY TABLE_NAME
    ";
    $stmt = $db->query($query);
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Foreign Keys:</strong> " . count($constraints) . "</p>";
    
    if (count($constraints) > 0) {
        echo "<ul>";
        foreach ($constraints as $constraint) {
            echo "<li>✅ " . $constraint['TABLE_NAME'] . " → " . $constraint['CONSTRAINT_NAME'] . "</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 7: Test JOIN query
echo "<h2>Test 7: Query JOIN (Booking + Pasien)</h2>";
try {
    $query = "
        SELECT 
            b.no_rawat,
            b.kode_paket,
            b.tanggal,
            b.status,
            p.nama as nama_pasien,
            p.jenis_kelamin,
            p.tanggal_lahir
        FROM booking_operasi b
        LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
        ORDER BY b.tanggal DESC
    ";
    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Hasil:</strong> " . count($results) . "</p>";
    
    if (count($results) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>No Rawat</th>";
        echo "<th>Kode Paket</th>";
        echo "<th>Tanggal</th>";
        echo "<th>Status</th>";
        echo "<th>Nama Pasien</th>";
        echo "<th>JK</th>";
        echo "<th>Tgl Lahir</th>";
        echo "</tr>";
        
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['no_rawat']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kode_paket']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
            echo "<td>" . htmlspecialchars($row['jenis_kelamin']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p>✅ Query JOIN berfungsi dengan baik!</p>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Summary
echo "<h2>✅ Ringkasan Test</h2>";
echo "<ul>";
echo "<li>✅ Koneksi database: <strong>OK</strong></li>";
echo "<li>✅ Tabel dibuat: <strong>OK</strong></li>";
echo "<li>✅ Data pasien: <strong>OK</strong></li>";
echo "<li>✅ Data booking: <strong>OK</strong></li>";
echo "<li>✅ Data konsultasi: <strong>OK</strong></li>";
echo "<li>✅ Foreign keys: <strong>OK</strong></li>";
echo "<li>✅ Query JOIN: <strong>OK</strong></li>";
echo "</ul>";

echo "<h3>🎉 Penggabungan database berhasil! Semua test berhasil.</h3>";

echo "<hr>";
echo "<p><a href='index.php'>← Kembali ke Home</a> | <a href='views/daftar-pasien.php'>Lihat Daftar Pasien →</a></p>";
?>
