<?php
// SCRIPT UNTUK MENGISI DATABASE DENGAN DATA BOOKING ACAK
// Cara menjalankan: Buka file ini di browser Anda, contoh: http://localhost/SIMRS_JB/db_seeder.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

function fetch_ids($db, $table, $column) {
    $stmt = $db->prepare("SELECT {$column} FROM {$table}");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function generate_random_time() {
    $hour = str_pad(rand(8, 16), 2, '0', STR_PAD_LEFT); // Jam 8 pagi sampai 4 sore
    $minute = ['00', '15', '30', '45'][rand(0, 3)];
    return "{$hour}:{$minute}:00";
}

function generate_no_rawat() {
    return 'RW' . date('Ymd') . rand(1000, 9999);
}

// --- MULAI PROSES --- //

echo "<style>body { font-family: monospace; line-height: 1.6; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";
echo "<h1>Database Seeder</h1>";

try {
    // 1. Ambil data master yang ada
    $pasien_ids = fetch_ids($db, 'pasien', 'kd_pasien');
    $dokter_ids = fetch_ids($db, 'tbl_dokter', 'id_dokter');
    $ruang_ids = fetch_ids($db, 'tbl_ruang', 'id_ruang');
    $perawat_ids = fetch_ids($db, 'tbl_perawat', 'id_perawat');

    // 2. Validasi apakah data master ada
    if (empty($pasien_ids) || empty($dokter_ids) || empty($ruang_ids) || empty($perawat_ids)) {
        echo "<p class='error'><strong>ERROR:</strong> Data master (pasien, dokter, ruang, atau perawat) tidak ditemukan. Skrip tidak dapat melanjutkan.</p>";
        echo "<p class='info'>Pastikan tabel `pasien`, `tbl_dokter`, `tbl_ruang`, dan `tbl_perawat` sudah terisi data sebelum menjalankan seeder ini.</p>";
        exit();
    }

    echo "<p>Data master ditemukan. Memulai proses seeding...</p>";

    // 3. Siapkan query insert
    $query = "INSERT INTO booking_operasi 
                (no_rawat, kode_paket, tanggal, jam_mulai, status, kd_pasien, dokter_rawat, ruang_rawat, perawat)
              VALUES 
                (:no_rawat, :kode_paket, :tanggal, :jam_mulai, :status, :kd_pasien, :dokter_rawat, :ruang_rawat, :perawat)";
    $stmt = $db->prepare($query);

    $paket_options = ['PKT-GIN', 'PKT-ORT', 'PKT-ANK', 'PKT-MTA', 'PKT-TMR'];
    $inserted_count = 0;

    // 4. Loop untuk membuat 20 data
    for ($i = 0; $i < 20; $i++) {
        $random_date = date('Y-m-d', strtotime('+' . rand(0, 10) . ' days'));

        $params = [
            ':no_rawat' => generate_no_rawat(),
            ':kode_paket' => $paket_options[array_rand($paket_options)],
            ':tanggal' => $random_date,
            ':jam_mulai' => generate_random_time(),
            ':status' => 'Menunggu',
            ':kd_pasien' => $pasien_ids[array_rand($pasien_ids)],
            ':dokter_rawat' => $dokter_ids[array_rand($dokter_ids)],
            ':ruang_rawat' => $ruang_ids[array_rand($ruang_ids)],
            ':perawat' => $perawat_ids[array_rand($perawat_ids)]
        ];

        if ($stmt->execute($params)) {
            $inserted_count++;
            echo "- Data ke-{$inserted_count} berhasil ditambahkan untuk tanggal {$random_date}.<br>";
        }
    }

    echo "<hr>";
    echo "<p class='success'><strong>SELESAI:</strong> Sebanyak {$inserted_count} data booking baru telah berhasil ditambahkan ke database.</p>";

} catch (PDOException $e) {
    echo "<p class='error'><strong>DATABASE ERROR:</strong> " . $e->getMessage() . "</p>";
}

?>
