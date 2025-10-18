<?php
/**
 * PDF Generator: Checklist Keselamatan Operasi (RMOK-0002) - MODERN DESIGN
 * Tampilan sama persis dengan form web
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
                         p.jenis_kelamin, p.alamat, p.tempat_lahir
                  FROM booking_operasi b 
                  LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
                  WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query_booking);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Data booking tidak ditemukan!");
}

// Calculate umur from tanggal_lahir
$umur = '';
if (!empty($booking['tanggal_lahir'])) {
    $tgl_lahir = new DateTime($booking['tanggal_lahir']);
    $today = new DateTime();
    $umur = $tgl_lahir->diff($today)->y . ' tahun';
}
$tgl_lahir_umur = (!empty($booking['tanggal_lahir']) ? $booking['tanggal_lahir'] : '') . ($umur ? ' (' . $umur . ')' : '');

// Ambil data checklist keselamatan operasi
$query_checklist = "SELECT * FROM tbl_anestesi_keselamatan_operasi 
                    WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_checklist = $db->prepare($query_checklist);
$stmt_checklist->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$checklist = $stmt_checklist->fetch(PDO::FETCH_ASSOC);

if (!$checklist) {
    die("Data checklist keselamatan operasi belum diisi!");
}

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function isChecked($value) {
    return !empty($value) && $value == 1 ? '☑' : '☐';
}

$tanggal_cetak = date('d F Y, H:i');

// Generate HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checklist Keselamatan Operasi - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9pt; line-height: 1.4; color: #333; background: #f4f7fb; }
        
        /* Header Modern */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 15px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2); margin-bottom: 15px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px; }
        .page-header .logo { width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 16pt; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .page-header .hospital-name { font-size: 11pt; margin: 4px 0; opacity: 0.95; }
        .page-header .document-code { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 8pt; font-weight: 600; margin-top: 5px; }
        
        /* Booking Info Box */
        .booking-info-box { background: white; border: 2px solid #e3f0ff; border-radius: 6px; padding: 12px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .booking-info-box h2 { background: #4285f4; color: white; padding: 6px 10px; border-radius: 4px; font-size: 10pt; margin: -12px -12px 10px -12px; }
        .booking-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .booking-info-item { display: flex; gap: 8px; font-size: 8.5pt; }
        .booking-info-item .label { font-weight: 600; color: #666; min-width: 110px; }
        .booking-info-item .value { color: #333; font-weight: 500; }
        
        /* Patient Info Box */
        .patient-info-box { background: white; border: 2px dashed #4285f4; border-radius: 6px; padding: 12px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .patient-info-box h2 { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 6px 10px; border-radius: 4px; font-size: 10pt; margin: -12px -12px 10px -12px; display: flex; align-items: center; gap: 8px; }
        .patient-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        
        /* Checklist Section */
        .checklist-section { background: white; border-radius: 6px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); page-break-inside: avoid; }
        .section-header { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 8px 12px; border-radius: 4px; font-weight: 600; font-size: 10pt; margin: -15px -15px 12px -15px; display: flex; align-items: center; justify-content: space-between; }
        .section-header .time-badge { background: rgba(255,255,255,0.3); padding: 4px 10px; border-radius: 12px; font-size: 9pt; }
        
        /* Checklist Items */
        .checklist-section ol { margin: 0 0 15px 20px; padding: 0; }
        .checklist-section ol li { margin-bottom: 12px; font-size: 9pt; line-height: 1.5; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 6px; padding-left: 10px; }
        .checkbox-group label { display: flex; align-items: center; gap: 5px; font-size: 8.5pt; }
        .checkbox-icon { font-size: 14pt; color: #4285f4; }
        
        /* Textarea Display */
        .textarea-display { background: #f8f9fa; border: 1px solid #e3f0ff; border-radius: 4px; padding: 10px; margin-top: 6px; font-size: 8.5pt; line-height: 1.5; min-height: 40px; }
        .textarea-display .label { font-weight: 600; color: #4285f4; margin-bottom: 4px; }
        
        /* Team Table */
        .team-table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; border-radius: 5px; overflow: hidden; }
        .team-table th { background: #e3f0ff; color: #4285f4; padding: 8px; text-align: center; font-weight: 600; font-size: 8.5pt; border: 1px solid #d0e4ff; }
        .team-table td { padding: 8px; text-align: center; border: 1px solid #e3f0ff; font-size: 8.5pt; }
        
        /* Signature Section */
        .signature-section { margin-top: 15px; padding-top: 15px; border-top: 2px dashed #e3f0ff; }
        .signature-date { text-align: right; font-size: 9pt; margin-bottom: 10px; color: #666; }
        
        /* Footer */
        .page-footer { margin-top: 20px; background: white; border-top: 2px solid #4285f4; padding: 8px 12px; text-align: center; font-size: 7pt; color: #666; }
        .page-footer .print-info { display: flex; justify-content: space-between; }
        
        /* Print Controls */
        @media print { .no-print { display: none; } body { background: white; } }
        .print-controls { position: fixed; top: 12px; right: 12px; z-index: 1000; display: flex; gap: 6px; }
        .btn-print { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); color: white; border: none; padding: 10px 18px; border-radius: 5px; font-weight: 600; font-size: 9pt; cursor: pointer; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3); }
        .btn-close { background: #f1f3f4; color: #666; border: none; padding: 10px 18px; border-radius: 5px; font-weight: 600; font-size: 9pt; cursor: pointer; }
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
                <h1>CHECKLIST KESELAMATAN OPERASI</h1>
                <div class="hospital-name">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMOK-0002</div>
    </div>
    
    <!-- Booking Info -->
    <div class="booking-info-box">
        <h2>📋 Data Booking Operasi</h2>
        <div class="booking-info-grid">
            <div class="booking-info-item">
                <div class="label">No. Rawat:</div>
                <div class="value"><?= displayValue($no_rawat) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Tanggal Booking:</div>
                <div class="value"><?= displayValue($tanggal) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Kode Paket:</div>
                <div class="value"><?= displayValue($kode_paket) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Jam Mulai:</div>
                <div class="value"><?= displayValue($jam_mulai) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Patient & Operator Info -->
    <div class="patient-info-box">
        <h2>👤 Keterangan Pasien & Operator</h2>
        <div class="patient-info-grid">
            <!-- Kolom Kiri: Info Pasien -->
            <div>
                <div class="booking-info-item">
                    <div class="label">Nama Pasien:</div>
                    <div class="value"><?= displayValue($checklist['nama_pasien']) ?></div>
                </div>
                <div class="booking-info-item">
                    <div class="label">No. Rekam Medis:</div>
                    <div class="value"><?= displayValue($checklist['no_rekam_medis']) ?></div>
                </div>
                <div class="booking-info-item">
                    <div class="label">Tanggal Lahir / Umur:</div>
                    <div class="value"><?= displayValue($checklist['tgl_lahir_umur']) ?></div>
                </div>
                <div class="booking-info-item">
                    <div class="label">Alamat:</div>
                    <div class="value"><?= displayValue($checklist['alamat']) ?></div>
                </div>
            </div>
            
            <!-- Kolom Kanan: Info Operasi -->
            <div>
                <div class="booking-info-item">
                    <div class="label">Operator / dr. Bedah:</div>
                    <div class="value"><?= displayValue($checklist['operator']) ?></div>
                </div>
                <div class="booking-info-item">
                    <div class="label">Operasi / Tindakan:</div>
                    <div class="value"><?= displayValue($checklist['operasi']) ?></div>
                </div>
                <div class="booking-info-item">
                    <div class="label">Tanggal Tindakan:</div>
                    <div class="value"><?= displayValue($checklist['tanggal_tindakan']) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sign In Section -->
    <div class="checklist-section">
        <div class="section-header">
            <span>🔵 SIGN IN</span>
            <span class="time-badge">⏰ <?= displayValue($checklist['signin_time']) ?></span>
        </div>
        
        <ol>
            <li>Pasien sudah konfirmasikan:
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_1a']) ?></span> Identitas pasien & gelang pasien</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_1b']) ?></span> Lokasi operasi</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_1c']) ?></span> Prosedur</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_1d']) ?></span> Surat izin operasi</label>
                </div>
            </li>
            <li>Lokasi operasi sudah diberi tanda?
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_2a']) ?></span> Ya</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_2b']) ?></span> Tidak dilakukan</label>
                </div>
            </li>
            <li>Mesin anestesi dan obat-obatan sudah dicek lengkap:
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_3']) ?></span> Ya</label>
                </div>
            </li>
            <li>Pulse Oximeter terpasang dan berfungsi:
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_4']) ?></span> Ya</label>
                </div>
            </li>
            <li>Apakah pasien punya riwayat alergi?
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_5a']) ?></span> Tidak</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_5b']) ?></span> Ya</label>
                </div>
            </li>
            <li>Kemungkinan kesulitan jalan nafas atau risiko aspirasi?
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_6a']) ?></span> Tidak</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_6b']) ?></span> Peralatan dan sistem telah tersedia</label>
                </div>
            </li>
            <li>Resiko kehilangan darah > 500ml (7ml/kgBB pada anak)?
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_7a']) ?></span> Tidak</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signin_7b']) ?></span> IV/sentral, dan terapi cairan telah direncanakan</label>
                </div>
            </li>
        </ol>
        
        <table class="team-table">
            <tr>
                <th>Dokter Anestesi</th>
                <th>Perawat Anestesi</th>
                <th>Perawat Sirkuler</th>
            </tr>
            <tr>
                <td><?= displayValue($checklist['dokter_anestesi_signin']) ?></td>
                <td><?= displayValue($checklist['perawat_anestesi_signin']) ?></td>
                <td><?= displayValue($checklist['perawat_sirkuler_signin']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Time Out Section -->
    <div class="checklist-section">
        <div class="section-header">
            <span>⏸️ TIME OUT</span>
            <span class="time-badge">⏰ <?= displayValue($checklist['timeout_time']) ?></span>
        </div>
        
        <ol>
            <li>Konfirmasi seluruh anggota tim (nama dan peran masing-masing)
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_1']) ?></span> Ya</label>
                </div>
            </li>
            <li>Konfirmasi secara verbal
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_2a']) ?></span> Nama Pasien</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_2b']) ?></span> Prosedur</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_2c']) ?></span> Lokasi dimana insisi akan dibuat</label>
                </div>
            </li>
            <li>Antibiotik profilaksis dalam 60 menit sebelumnya
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_3']) ?></span> Ya</label>
                </div>
            </li>
            <li>Antisipasi kejadian kritis:
                <?php if (!empty($checklist['catatan_dokter_bedah'])): ?>
                <div class="textarea-display">
                    <div class="label">Catatan dokter bedah:</div>
                    <?= nl2br(displayValue($checklist['catatan_dokter_bedah'])) ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($checklist['catatan_dokter_anestesi'])): ?>
                <div class="textarea-display">
                    <div class="label">Catatan dokter anestesi:</div>
                    <?= nl2br(displayValue($checklist['catatan_dokter_anestesi'])) ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($checklist['catatan_perawat'])): ?>
                <div class="textarea-display">
                    <div class="label">Catatan perawat:</div>
                    <?= nl2br(displayValue($checklist['catatan_perawat'])) ?>
                </div>
                <?php endif; ?>
            </li>
            <li>Foto Rontgen / CT-Scan / MRI yang diperlukan telah ditayangkan?
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_5a']) ?></span> Ya</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['timeout_5b']) ?></span> Tidak</label>
                </div>
            </li>
        </ol>
        
        <table class="team-table">
            <tr>
                <th>Perawat Sirkuler</th>
            </tr>
            <tr>
                <td><?= displayValue($checklist['perawat_sirkuler_timeout']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Sign Out Section -->
    <div class="checklist-section">
        <div class="section-header">
            <span>🔴 SIGN OUT</span>
            <span class="time-badge">⏰ <?= displayValue($checklist['signout_time']) ?></span>
        </div>
        
        <ol>
            <li>Konfirmasi perawat secara verbal:
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signout_1a']) ?></span> Nama prosedur tindakan</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signout_1b']) ?></span> Instrumen, kasa & jarum lengkap</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signout_1c']) ?></span> Spesimen diberi label</label>
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signout_1d']) ?></span> Tidak ada masalah alat operasi</label>
                </div>
            </li>
            <li>Operator dokter bedah, dokter anestesi dan perawat membahas masalah utama penyembuhan & manajemen pasien selanjutnya
                <div class="checkbox-group">
                    <label><span class="checkbox-icon"><?= isChecked($checklist['signout_2']) ?></span> Ya</label>
                </div>
            </li>
        </ol>
        
        <div class="signature-section">
            <div class="signature-date">
                📅 Tasikmalaya, <?= displayValue($checklist['tanggal_keluar']) ?> - <?= displayValue($checklist['tahun_keluar']) ?>
            </div>
            
            <table class="team-table">
                <tr>
                    <th>Perawat Sirkuler</th>
                    <th>Dokter Anestesi</th>
                    <th>Operator / Dokter Bedah</th>
                </tr>
                <tr>
                    <td><?= displayValue($checklist['perawat_sirkuler_signout']) ?></td>
                    <td><?= displayValue($checklist['dokter_anestesi_signout']) ?></td>
                    <td><?= displayValue($checklist['operator_signout']) ?></td>
                </tr>
            </table>
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
