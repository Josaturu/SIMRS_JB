<?php
/**
 * PDF Generator: Catatan Sedasi & Anestesi (RMOK 3B)
 * Generate PDF dari data catatan sedasi yang sudah terisi
 */

// Ambil parameter
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    die("Parameter tidak lengkap!");
}

// Koneksi database
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Ambil data booking dengan data pasien
$query_booking = "SELECT b.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, 
                         p.jenis_kelamin AS jenis_kelamin_pasien, p.alamat
                  FROM booking_operasi b 
                  LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
                  WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query_booking);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Data booking tidak ditemukan!");
}

// Ambil data catatan sedasi
$query_catatan = "SELECT * FROM tbl_anestesi_catatan_anestesi 
                  WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_catatan = $db->prepare($query_catatan);
$stmt_catatan->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$catatan = $stmt_catatan->fetch(PDO::FETCH_ASSOC);

if (!$catatan) {
    die("Data catatan sedasi belum diisi!");
}

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    return date('d-m-Y H:i', strtotime($datetime));
}

// Tanggal cetak
$tanggal_cetak = date('d F Y, H:i');

// Generate HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Catatan Sedasi & Anestesi - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page {
            margin: 10mm;
            size: A4 landscape;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #004d80;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16pt;
            color: #004d80;
        }
        
        .header .subtitle {
            font-size: 11pt;
            color: #666;
            margin: 3px 0;
        }
        
        .patient-info {
            background: #f5f5f5;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        
        .patient-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .patient-info td {
            padding: 3px;
            font-size: 9pt;
        }
        
        .patient-info td:nth-child(odd) {
            width: 120px;
            font-weight: bold;
        }
        
        .section-title {
            background: #004d80;
            color: white;
            padding: 6px;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        
        .data-table th {
            background: #e0e0e0;
            font-weight: bold;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .data-item {
            border: 1px solid #ddd;
            padding: 5px;
        }
        
        .data-item strong {
            display: block;
            font-size: 8pt;
            color: #666;
            margin-bottom: 2px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CATATAN SEDASI & ANESTESI</h1>
        <div class="subtitle">RUMAH SAKIT JOSATURU</div>
        <div class="subtitle" style="font-size: 9pt;">RMOK 3B</div>
    </div>
    
    <!-- Info Pasien -->
    <div class="patient-info">
        <table>
            <tr>
                <td>No. Rawat</td>
                <td>: <?= displayValue($booking['no_rawat']) ?></td>
                <td>Nama Pasien</td>
                <td>: <?= displayValue($booking['nama_pasien']) ?></td>
                <td>Jenis Kelamin</td>
                <td>: <?= displayValue($catatan['jenis_kelamin']) ?></td>
            </tr>
            <tr>
                <td>No. RM</td>
                <td>: <?= displayValue($catatan['no_rm']) ?></td>
                <td>Tanggal Lahir</td>
                <td>: <?= displayValue($catatan['tgl_lahir']) ?></td>
                <td>Tanggal Operasi</td>
                <td>: <?= displayValue($booking['tanggal']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Diagnosa & Tindakan -->
    <div class="section-title">DIAGNOSA & TINDAKAN</div>
    <table class="data-table">
        <tr>
            <th width="25%">Diagnosa Pra Bedah</th>
            <th width="25%">Nama Tindakan</th>
            <th width="25%">Diagnosa Pasca Bedah</th>
            <th width="25%">Asessment Pra Anestesi</th>
        </tr>
        <tr>
            <td><?= displayValue($catatan['diagnosa_pra_bedah']) ?></td>
            <td><?= displayValue($catatan['nama_tindakan']) ?></td>
            <td><?= displayValue($catatan['diagnosa_pasca_bedah']) ?></td>
            <td><?= displayValue($catatan['asessment_pra_anestesi']) ?></td>
        </tr>
    </table>
    
    <!-- Teknik Anestesi -->
    <div class="section-title">TEKNIK ANESTESI</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Jenis Anestesi:</strong>
            <?= displayValue($catatan['jenis_anestesi']) ?>
        </div>
        <div class="data-item">
            <strong>Teknik Regional:</strong>
            <?= displayValue($catatan['teknik_regional']) ?>
        </div>
        <div class="data-item">
            <strong>Teknik Khusus:</strong>
            <?= displayValue($catatan['teknik_khusus']) ?>
        </div>
        <div class="data-item">
            <strong>Posisi Operasi:</strong>
            <?= displayValue($catatan['posisi_operasi']) ?>
        </div>
    </div>
    
    <!-- Status Pasien -->
    <div class="section-title">STATUS PASIEN</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Tinggi Badan:</strong>
            <?= displayValue($catatan['tinggi_badan']) ?> cm
        </div>
        <div class="data-item">
            <strong>Berat Badan:</strong>
            <?= displayValue($catatan['berat_badan']) ?> kg
        </div>
        <div class="data-item">
            <strong>ASA:</strong>
            <?= displayValue($catatan['asa']) ?>
        </div>
        <div class="data-item">
            <strong>Mallampati:</strong>
            <?= displayValue($catatan['mallampati']) ?>
        </div>
    </div>
    
    <!-- Monitoring Vital Sign -->
    <div class="section-title">MONITORING VITAL SIGN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>TD</th>
                <th>Nadi</th>
                <th>RR</th>
                <th>SpO2</th>
                <th>EtCO2</th>
                <th>Suhu</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Parse vital signs dari JSON atau array
            $vital_times = ['pre_induksi', 'induksi', 'intubasi', '15_menit', '30_menit', '45_menit', '60_menit', 'ekstubasi', 'keluar_ok'];
            $vital_labels = [
                'pre_induksi' => 'Pre Induksi',
                'induksi' => 'Induksi',
                'intubasi' => 'Intubasi',
                '15_menit' => '15 Menit',
                '30_menit' => '30 Menit',
                '45_menit' => '45 Menit',
                '60_menit' => '60 Menit',
                'ekstubasi' => 'Ekstubasi',
                'keluar_ok' => 'Keluar OK'
            ];
            
            foreach ($vital_times as $time) {
                echo "<tr>";
                echo "<td>" . ($vital_labels[$time] ?? $time) . "</td>";
                echo "<td>" . displayValue($catatan["td_$time"]) . "</td>";
                echo "<td>" . displayValue($catatan["nadi_$time"]) . "</td>";
                echo "<td>" . displayValue($catatan["rr_$time"]) . "</td>";
                echo "<td>" . displayValue($catatan["spo2_$time"]) . "</td>";
                echo "<td>" . displayValue($catatan["etco2_$time"]) . "</td>";
                echo "<td>" . displayValue($catatan["suhu_$time"]) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    
    <!-- Waktu Operasi -->
    <div class="section-title">WAKTU OPERASI</div>
    <table class="data-table">
        <tr>
            <th>Masuk Kamar Operasi</th>
            <th>Induksi</th>
            <th>Insisi</th>
            <th>Operasi Selesai</th>
            <th>Keluar OK</th>
        </tr>
        <tr>
            <td><?= formatDateTime($catatan['waktu_masuk_ok']) ?></td>
            <td><?= formatDateTime($catatan['waktu_induksi']) ?></td>
            <td><?= formatDateTime($catatan['waktu_insisi']) ?></td>
            <td><?= formatDateTime($catatan['waktu_operasi_selesai']) ?></td>
            <td><?= formatDateTime($catatan['waktu_keluar_ok']) ?></td>
        </tr>
    </table>
    
    <!-- Cairan & Obat -->
    <div class="section-title">CAIRAN & MEDIKASI</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Cairan Masuk:</strong>
            <?= displayValue($catatan['cairan_masuk']) ?>
        </div>
        <div class="data-item">
            <strong>Cairan Keluar:</strong>
            <?= displayValue($catatan['cairan_keluar']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Premedikasi:</strong>
            <?= displayValue($catatan['obat_premedikasi']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Induksi:</strong>
            <?= displayValue($catatan['obat_induksi']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Maintenance:</strong>
            <?= displayValue($catatan['obat_maintenance']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Tambahan:</strong>
            <?= displayValue($catatan['obat_tambahan']) ?>
        </div>
    </div>
    
    <!-- Komplikasi & Catatan -->
    <div class="section-title">KOMPLIKASI & CATATAN</div>
    <table class="data-table">
        <tr>
            <th width="30%">Komplikasi</th>
            <td><?= displayValue($catatan['komplikasi']) ?></td>
        </tr>
        <tr>
            <th>Catatan Khusus</th>
            <td><?= displayValue($catatan['catatan_khusus']) ?></td>
        </tr>
        <tr>
            <th>Kondisi Keluar OK</th>
            <td><?= displayValue($catatan['kondisi_keluar_ok']) ?></td>
        </tr>
    </table>
    
    <!-- Tim Medis -->
    <div class="section-title">TIM MEDIS</div>
    <table class="data-table">
        <tr>
            <th width="25%">Dokter Anestesi</th>
            <td width="25%"><?= displayValue($catatan['dokter_anestesi_ttd']) ?></td>
            <th width="25%">Perawat Anestesi</th>
            <td width="25%"><?= displayValue($catatan['perawat_anestesi']) ?></td>
        </tr>
        <tr>
            <th>Dokter Operator</th>
            <td><?= displayValue($catatan['dokter_merawat']) ?></td>
            <th>Perawat Menerima</th>
            <td><?= displayValue($catatan['perawat_menerima']) ?></td>
        </tr>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        Dicetak tanggal: <?= $tanggal_cetak ?> | Halaman 1
    </div>
    
    <!-- Tombol Print -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14pt; background: #004d80; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Cetak PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 14pt; background: #666; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            ✖ Tutup
        </button>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
echo $html;
?>
