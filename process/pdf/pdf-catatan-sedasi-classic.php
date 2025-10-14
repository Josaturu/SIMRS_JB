<?php
/**
 * PDF Generator: Catatan Sedasi & Anestesi (RMOK 3B) - FIXED
 * Generate PDF dari data catatan sedasi yang sudah terisi dengan field yang benar
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
                         p.jenis_kelamin, p.alamat
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
        <div class="subtitle">RUMAH SAKIT PRASETYA BUNDA</div>
        <div class="subtitle" style="font-size: 9pt;">RMOK 3B</div>
    </div>
    
    <!-- Info Pasien -->
    <div class="patient-info">
        <table>
            <tr>
                <td>No. Rawat</td>
                <td>: <?= displayValue($booking['no_rawat']) ?></td>
                <td>Nama Pasien</td>
                <td>: <?= displayValue($catatan['nama']) ?></td>
                <td>Jenis Kelamin</td>
                <td>: <?= displayValue($booking['jenis_kelamin']) ?></td>
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
            <strong>Teknik Anestesi:</strong>
            <?= displayValue($catatan['teknik_anestesi']) ?>
        </div>
        <div class="data-item">
            <strong>Lokasi Regional:</strong>
            <?= displayValue($catatan['lokasi_regional']) ?>
        </div>
        <div class="data-item">
            <strong>Posisi:</strong>
            <?= displayValue($catatan['posisi']) ?> <?= displayValue($catatan['lain_lain_posisi']) ?>
        </div>
    </div>
    
    <!-- Status Pasien -->
    <div class="section-title">STATUS PASIEN & PEMERIKSAAN</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Tinggi Badan:</strong>
            <?= displayValue($catatan['tb']) ?> cm
        </div>
        <div class="data-item">
            <strong>Berat Badan:</strong>
            <?= displayValue($catatan['bb']) ?> kg
        </div>
        <div class="data-item">
            <strong>Tekanan Darah:</strong>
            <?= displayValue($catatan['td']) ?> mmHg
        </div>
        <div class="data-item">
            <strong>Nadi:</strong>
            <?= displayValue($catatan['nadi']) ?> x/menit
        </div>
        <div class="data-item">
            <strong>Respirasi:</strong>
            <?= displayValue($catatan['respirasi']) ?> x/menit
        </div>
        <div class="data-item">
            <strong>Suhu:</strong>
            <?= displayValue($catatan['suhu']) ?> °C
        </div>
        <div class="data-item">
            <strong>ASA Status:</strong>
            <?= displayValue($catatan['status_fisik_asa']) ?>
        </div>
        <div class="data-item">
            <strong>GCS:</strong>
            <?= displayValue($catatan['gcs']) ?>
        </div>
        <div class="data-item">
            <strong>Hemoglobin:</strong>
            <?= displayValue($catatan['hb']) ?> g/dL
        </div>
        <div class="data-item">
            <strong>Golongan Darah:</strong>
            <?= displayValue($catatan['golongan_darah']) ?>
        </div>
        <div class="data-item">
            <strong>Skrining Nyeri:</strong>
            <?= displayValue($catatan['skrining_nyeri']) ?>
        </div>
        <div class="data-item">
            <strong>Jenis Pembedahan:</strong>
            <?= displayValue($catatan['jenis_pembedahan']) ?>
        </div>
    </div>
    
    <!-- Waktu Operasi -->
    <div class="section-title">WAKTU ANESTESI & OPERASI</div>
    <table class="data-table">
        <tr>
            <th>Mulai Anestesi</th>
            <th>Induksi</th>
            <th>Pasien Siap Insisi</th>
            <th>Mulai Pembedahan</th>
            <th>Selesai Pembedahan</th>
            <th>Selesai Anestesi</th>
        </tr>
        <tr>
            <td><?= displayValue($catatan['mulai_anestesi']) ?></td>
            <td><?= displayValue($catatan['induksi_pukul']) ?></td>
            <td><?= displayValue($catatan['pasien_siap_insisi']) ?></td>
            <td><?= displayValue($catatan['mulai_pembedahan']) ?></td>
            <td><?= displayValue($catatan['selesai_pembedahan']) ?></td>
            <td><?= displayValue($catatan['selesai_anestesi']) ?></td>
        </tr>
    </table>
    
    <table class="data-table" style="margin-top: 10px;">
        <tr>
            <th>Insisi Mulai</th>
            <th>Operasi Mulai</th>
            <th>Ekstubasi</th>
            <th>Pasien Keluar OK</th>
        </tr>
        <tr>
            <td><?= displayValue($catatan['insisi_mulai_pukul']) ?></td>
            <td><?= displayValue($catatan['operasi_mulai_pukul']) ?></td>
            <td><?= displayValue($catatan['ekstubasi_pukul']) ?></td>
            <td><?= displayValue($catatan['pasien_keluar_ok']) ?></td>
        </tr>
    </table>
    
    <!-- Cairan & Obat -->
    <div class="section-title">CAIRAN & MEDIKASI</div>
    <table class="data-table">
        <tr>
            <th width="50%">Cairan & Input</th>
            <th width="50%">Output</th>
        </tr>
        <tr>
            <td><?= displayValue($catatan['cairan_infus']) ?></td>
            <td><?= displayValue($catatan['cairan_output']) ?></td>
        </tr>
    </table>
    
    <div class="data-grid" style="margin-top: 10px;">
        <div class="data-item">
            <strong>Premedikasi:</strong>
            <?= displayValue($catatan['premedikasi']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Premedikasi:</strong>
            <?= displayValue($catatan['premedik_nama_obat']) ?> (<?= displayValue($catatan['premedik_dosis_obat']) ?>)
        </div>
        <div class="data-item">
            <strong>Induksi:</strong>
            <?= displayValue($catatan['induksi']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Anestesi:</strong>
            <?= displayValue($catatan['obat']) ?>
        </div>
        <div class="data-item">
            <strong>Obat Anestesi Lokal:</strong>
            <?= displayValue($catatan['obat_anestesi_lokal']) ?>
        </div>
        <div class="data-item">
            <strong>Infus Perifer:</strong>
            <?= displayValue($catatan['infus_perifer']) ?>
        </div>
    </div>
    
    <!-- Jalan Nafas & Ventilasi -->
    <div class="section-title">JALAN NAFAS & VENTILASI</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Jalan Nafas:</strong>
            <?= displayValue($catatan['jalan_nafas']) ?>
        </div>
        <div class="data-item">
            <strong>Ventilasi:</strong>
            <?= displayValue($catatan['ventilasi']) ?>
        </div>
        <div class="data-item">
            <strong>Ventilator:</strong>
            <?= displayValue($catatan['ventilator']) ?>
        </div>
        <div class="data-item">
            <strong>Posisi ETT:</strong>
            <?= displayValue($catatan['posisi_ett']) ?>
        </div>
        <div class="data-item">
            <strong>Ukuran Balon:</strong>
            <?= displayValue($catatan['ukuran_balon']) ?>
        </div>
        <div class="data-item">
            <strong>Jenis Balon:</strong>
            <?= displayValue($catatan['jenis_balon']) ?> <?= displayValue($catatan['lain_lain_balon']) ?>
        </div>
    </div>
    
    <!-- Regional Anestesi -->
    <?php if (!empty($catatan['lokasi_regional'])): ?>
    <div class="section-title">REGIONAL ANESTESI</div>
    <div class="data-grid">
        <div class="data-item">
            <strong>Jarum Regional:</strong>
            <?= displayValue($catatan['jarum_regional']) ?>
        </div>
        <div class="data-item">
            <strong>Kateter Regional:</strong>
            <?= displayValue($catatan['kateter_regional']) ?>
        </div>
        <div class="data-item">
            <strong>Hasil Regional:</strong>
            <?= displayValue($catatan['hasil_regional']) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Komplikasi & Catatan -->
    <div class="section-title">KOMPLIKASI, MASALAH & TINDAKAN</div>
    <table class="data-table">
        <tr>
            <th width="30%">Penyulit Pra Anestesi</th>
            <td><?= displayValue($catatan['penyulit_pra_anestesi']) ?></td>
        </tr>
        <tr>
            <th>Resiko</th>
            <td><?= displayValue($catatan['resiko']) ?></td>
        </tr>
        <tr>
            <th>Masalah Selama Anestesi</th>
            <td><?= displayValue($catatan['masalah_selama_anestesi']) ?></td>
        </tr>
        <tr>
            <th>Tindakan</th>
            <td><?= displayValue($catatan['tindakan']) ?></td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td><?= displayValue($catatan['keterangan']) ?></td>
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
            <th>Dokter Bedah</th>
            <td><?= displayValue($catatan['dokter_bedah']) ?></td>
            <th>Perawat Bedah</th>
            <td><?= displayValue($catatan['perawat_bedah']) ?></td>
        </tr>
        <tr>
            <th>Dokter Merawat</th>
            <td><?= displayValue($catatan['dokter_merawat']) ?></td>
            <th>Ruang Perawatan</th>
            <td><?= displayValue($catatan['ruang_perawatan']) ?></td>
        </tr>
        <tr>
            <th>Perawat Menyerahkan</th>
            <td><?= displayValue($catatan['perawat_menyerahkan']) ?></td>
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
