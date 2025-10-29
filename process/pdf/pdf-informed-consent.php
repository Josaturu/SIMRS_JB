<?php
/**
 * PDF Generator: Informed Consent Tindakan Anestesi (RMOK 3C) - MODERN DESIGN
 * Menggunakan design system yang konsisten dengan Konsultasi Anestesi
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
        @page { margin: 12mm; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10pt; line-height: 1.6; color: #333; background: #f4f7fb; }
        
        /* Header Modern */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 15px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2); margin-bottom: 15px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px; }
        .page-header .logo { width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 15pt; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .page-header .hospital-name { font-size: 11pt; margin: 4px 0; opacity: 0.95; }
        .page-header .document-code { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 15px; font-size: 8pt; font-weight: 600; margin-top: 6px; }
        
        /* Patient Info Box */
        .patient-info-box { background: white; border: 2px dashed #4285f4; border-radius: 8px; padding: 12px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .patient-info-box .title { background: #4285f4; color: white; padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 9pt; margin: -12px -12px 10px -12px; display: inline-block; }
        .patient-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .patient-info-item { display: flex; gap: 8px; font-size: 8.5pt; }
        .patient-info-item .label { font-weight: 600; color: #666; min-width: 110px; }
        .patient-info-item .value { color: #333; font-weight: 500; }
        
        /* Section Headers */
        .section-header { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 8px 12px; border-radius: 5px; font-weight: 600; font-size: 10pt; margin: 15px 0 10px 0; box-shadow: 0 2px 6px rgba(66, 133, 244, 0.15); }
        .subsection-header { background: #e3f0ff; color: #4285f4; padding: 6px 10px; border-radius: 4px; font-weight: 600; font-size: 9pt; margin: 12px 0 8px 0; border-left: 3px solid #4285f4; }
        
        /* Modern Table */
        .data-table { width: 100%; border-collapse: collapse; margin: 8px 0; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 8.5pt; }
        .data-table thead { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; }
        .data-table th { padding: 7px; text-align: left; font-weight: 600; font-size: 8.5pt; }
        .data-table tbody tr { border-bottom: 1px solid #e3f0ff; }
        .data-table tbody tr:nth-child(even) { background: #fafbff; }
        .data-table td { padding: 7px; color: #333; }
        .data-table td.label { font-weight: 600; background: #f4f7fb; color: #666; width: 180px; }
        
        /* Content Box */
        .content-box { background: white; border-left: 4px solid #4285f4; border-radius: 5px; padding: 10px; margin: 8px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .content-box h3 { color: #4285f4; font-size: 9.5pt; margin-bottom: 6px; font-weight: 600; }
        .content-box p { color: #333; font-size: 9pt; text-align: justify; line-height: 1.5; }
        
        /* Info Box */
        .info-box { background: white; border-left: 4px solid #4285f4; border-radius: 5px; padding: 10px; margin: 8px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-size: 8.5pt; }
        .info-box.success { border-left-color: #34a853; background: #f0fdf4; }
        .info-box.warning { border-left-color: #fbbc04; background: #fffbeb; }
        
        /* Signature */
        .signature-section { margin-top: 20px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .signature-box { text-align: center; padding: 12px; border: 2px solid #e3f0ff; border-radius: 6px; background: white; }
        .signature-box .title { font-weight: 600; color: #4285f4; margin-bottom: 50px; font-size: 9pt; }
        .signature-box .name { border-top: 2px solid #333; padding-top: 6px; font-weight: 600; color: #333; font-size: 9pt; }
        .signature-box .role { font-size: 8pt; color: #666; margin-top: 4px; }
        
        /* Footer */
        .page-footer { margin-top: 30px; background: white; border-top: 2px solid #4285f4; padding: 8px 15px; text-align: center; font-size: 7.5pt; color: #666; }
        .page-footer .print-info { display: flex; justify-content: space-between; }
        
        /* Print Controls */
        @media print { .no-print { display: none; } body { background: white; } }
        .print-controls { position: fixed; top: 15px; right: 15px; z-index: 1000; display: flex; gap: 8px; }
        .btn-print { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 10pt; cursor: pointer; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3); }
        .btn-close { background: #f1f3f4; color: #666; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 10pt; cursor: pointer; }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak PDF</button>
        <button class="btn-close" onclick="window.close()">✖ Tutup</button>
    </div>
    
    <!-- Header -->
    <div class="page-header">
        <div class="logo-section">
            <div class="logo">RS</div>
            <div>
                <h1>INFORMED CONSENT</h1>
                <h1 style="font-size: 13pt; margin-top: 2px;">TINDAKAN ANESTESI</h1>
                <div class="hospital-name">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMC 4a Rev-01</div>
    </div>
    
    <!-- Patient Info -->
    <div class="patient-info-box">
        <div class="title">📋 Informasi Pasien</div>
        <div class="patient-info-grid">
            <div class="patient-info-item">
                <div class="label">No. Rawat:</div>
                <div class="value"><?= displayValue($booking['no_rawat']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">Nama Pasien:</div>
                <div class="value"><?= displayValue($booking['nama_pasien']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">No. RM:</div>
                <div class="value"><?= displayValue($booking['kode_rekam_medis']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">Tanggal Lahir:</div>
                <div class="value"><?= displayValue($booking['tanggal_lahir']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">Ruang Perawatan:</div>
                <div class="value"><?= displayValue($consent['ruang']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">Tanggal Operasi:</div>
                <div class="value"><?= displayValue($booking['tanggal']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Identitas -->
    <div class="section-header">▶ IDENTITAS</div>
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
    <div class="section-header">▶ INFORMASI TINDAKAN ANESTESI</div>
    
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
    
    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="title">Penerima Informasi</div>
            <div class="name"><?= displayValue($consent['penerima_info']) ?></div>
            <div class="role">(<?= displayValue($consent['hubungan_pasien']) ?>)</div>
        </div>
        
        <div class="signature-box">
            <div class="title">Pemberi Informasi</div>
            <div class="name"><?= displayValue($consent['pemberi_info']) ?></div>
            <div class="role">(<?= displayValue($consent['jabatan']) ?>)</div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="page-footer">
        <div class="print-info">
            <span>Dicetak: <?= $tanggal_cetak ?> WIB</span>
            <span>Halaman 1</span>
            <span>© RS Prasetya Bunda</span>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
echo $html;
?>
