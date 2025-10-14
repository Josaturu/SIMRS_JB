<?php
/**
 * PDF Generator: Informed Consent Tindakan Anestesi (RMOK 3C)
 * Generate PDF dari data informed consent yang sudah terisi
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

// Ambil data booking
$query_booking = "SELECT b.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin, p.alamat
                  FROM booking_operasi b 
                  LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
                  WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query_booking);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Data booking tidak ditemukan!");
}

// Ambil data informed consent
$query_consent = "SELECT * FROM tbl_anestesi_informed_consent_anestesi 
                  WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_consent = $db->prepare($query_consent);
$stmt_consent->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$consent = $stmt_consent->fetch(PDO::FETCH_ASSOC);

if (!$consent) {
    die("Data informed consent belum diisi!");
}

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
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
    <title>Informed Consent Anestesi - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #004d80;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16pt;
            color: #004d80;
        }
        
        .header .subtitle {
            font-size: 11pt;
            color: #666;
            margin: 5px 0;
        }
        
        .patient-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .patient-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .patient-info td {
            padding: 5px;
            font-size: 10pt;
        }
        
        .patient-info td:first-child {
            width: 150px;
            font-weight: bold;
        }
        
        .section-title {
            background: #004d80;
            color: white;
            padding: 8px;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .content-box {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
            background: #fafafa;
        }
        
        .content-box h3 {
            margin: 0 0 10px 0;
            font-size: 11pt;
            color: #004d80;
        }
        
        .content-box p {
            margin: 5px 0;
            text-align: justify;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }
        
        .data-table td.label {
            width: 180px;
            font-weight: bold;
            background: #f9f9f9;
        }
        
        .confirmation {
            border: 2px solid #004d80;
            padding: 15px;
            margin: 20px 0;
            background: #f0f8ff;
        }
        
        .confirmation p {
            margin: 8px 0;
            font-size: 10pt;
        }
        
        .signature-area {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-box .title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .signature-box .name {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
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
        <h1>INFORMED CONSENT</h1>
        <h1>TINDAKAN ANESTESI</h1>
        <div class="subtitle">RUMAH SAKIT JOSATURU</div>
        <div class="subtitle" style="font-size: 9pt;">RMC 4a Rev-01</div>
    </div>
    
    <!-- Info Pasien -->
    <div class="patient-info">
        <table>
            <tr>
                <td>No. Rawat</td>
                <td>: <?= displayValue($booking['no_rawat']) ?></td>
                <td>Nama Pasien</td>
                <td>: <?= displayValue($booking['nama_pasien']) ?></td>
            </tr>
            <tr>
                <td>No. Rekam Medis</td>
                <td>: <?= displayValue($booking['kode_rekam_medis']) ?></td>
                <td>Tanggal Lahir</td>
                <td>: <?= displayValue($booking['tanggal_lahir']) ?></td>
            </tr>
            <tr>
                <td>Ruang Perawatan</td>
                <td>: <?= displayValue($consent['ruang']) ?></td>
                <td>Tanggal Operasi</td>
                <td>: <?= displayValue($booking['tanggal']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Pemberi & Penerima Informasi -->
    <div class="section-title">IDENTITAS</div>
    <table class="data-table">
        <tr>
            <td class="label">Dokter Pelaksana</td>
            <td><?= displayValue($consent['dokter_pelaksana']) ?></td>
        </tr>
        <tr>
            <td class="label">Pemberi Informasi</td>
            <td><?= displayValue($consent['pemberi_info']) ?></td>
        </tr>
        <tr>
            <td class="label">Jabatan Pemberi Info</td>
            <td><?= displayValue($consent['jabatan']) ?></td>
        </tr>
        <tr>
            <td class="label">Penerima Informasi</td>
            <td><?= displayValue($consent['penerima_info']) ?></td>
        </tr>
        <tr>
            <td class="label">Hubungan dengan Pasien</td>
            <td><?= displayValue($consent['hubungan_pasien']) ?></td>
        </tr>
    </table>
    
    <!-- Informasi Tindakan -->
    <div class="section-title">INFORMASI TINDAKAN ANESTESI</div>
    
    <div class="content-box">
        <h3>1. Tindakan Operasi</h3>
        <p><?= displayValue($consent['tindakan_operasi']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>2. Jenis Anestesi yang Akan Dilakukan</h3>
        <p><?= displayValue($consent['jenis_anestesi']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>3. Indikasi Tindakan</h3>
        <p><?= displayValue($consent['indikasi']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>4. Tata Cara</h3>
        <p><?= displayValue($consent['tata_cara']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>5. Tujuan</h3>
        <p><?= displayValue($consent['tujuan']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>6. Risiko</h3>
        <p><?= displayValue($consent['risiko']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>7. Status Fisik (ASA)</h3>
        <p><?= displayValue($consent['status_fisik']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>8. Prognosis</h3>
        <p><?= displayValue($consent['prognosis']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>9. Alternatif & Risikonya</h3>
        <p><?= displayValue($consent['alternatif_resiko']) ?></p>
    </div>
    
    <div class="content-box">
        <h3>10. Lain-lain</h3>
        <p><?= displayValue($consent['lain_lain']) ?></p>
    </div>
    
    <!-- Pernyataan Persetujuan -->
    <!-- <div class="confirmation">
        <h3 style="text-align: center; margin-top: 0; color: #004d80;">PERNYATAAN PERSETUJUAN</h3>
        
        <p style="font-weight: bold;">Dengan ini saya menyatakan bahwa:</p>
        
        <?php if ($consent['checkbox_confirm'] == 'setuju'): ?>
        <p>☑ Saya telah menerima informasi sebagaimana tersebut di atas yang saya beri tanda/paraf di kotak kanannya, dan saya telah memahaminya.</p>
        <p>☑ Saya telah mendapat kesempatan untuk bertanya dan mendiskusikan hal-hal yang berkaitan dengan tindakan anestesi tersebut di atas.</p>
        <p>☑ Saya memahami bahwa setiap tindakan anestesi mengandung risiko dan tidak selalu memberikan hasil yang diharapkan.</p>
        <p>☑ Saya mengerti bahwa tindakan anestesi ini dilakukan oleh tenaga medis yang kompeten dan berpengalaman.</p>
        <p style="font-weight: bold; margin-top: 15px;">✓ Saya MENYETUJUI tindakan anestesi yang dijelaskan di atas.</p>
        <?php else: ?>
        <p>☐ Saya telah menerima informasi sebagaimana tersebut di atas.</p>
        <p>☐ Saya telah mendapat kesempatan untuk bertanya dan mendiskusikan hal-hal yang berkaitan dengan tindakan anestesi.</p>
        <p>☐ Saya memahami risiko yang terkait dengan tindakan anestesi.</p>
        <p style="font-weight: bold; margin-top: 15px; color: red;">✗ Saya MENOLAK tindakan anestesi yang dijelaskan di atas.</p>
        <?php endif; ?>
    </div> -->
    
    <!-- Tanda Tangan -->
    <div class="signature-area">
        <div class="signature-box">
            <div class="title">Penerima Informasi</div>
            <div class="name"><?= displayValue($consent['penerima_info']) ?></div>
            <div style="font-size: 9pt; color: #666; margin-top: 5px;">
                (<?= displayValue($consent['hubungan_pasien']) ?>)
            </div>
        </div>
        
        <div class="signature-box">
            <div class="title">Pemberi Informasi</div>
            <div class="name"><?= displayValue($consent['pemberi_info']) ?></div>
            <div style="font-size: 9pt; color: #666; margin-top: 5px;">
                (<?= displayValue($consent['jabatan']) ?>)
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        Dicetak tanggal: <?= $tanggal_cetak ?> | Halaman 1
    </div>
    
    <!-- Tombol Print -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
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
