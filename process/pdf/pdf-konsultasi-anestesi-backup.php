<?php
/**
 * PDF Generator: Konsultasi Anestesi (RMOK 3A)
 * Generate PDF dari data konsultasi anestesi yang sudah terisi
 */

// Cek library mPDF
if (!class_exists('Mpdf\Mpdf')) {
    // Jika mPDF belum terinstall, gunakan HTML print
    $use_mpdf = false;
} else {
    $use_mpdf = true;
}

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
$query_booking = "SELECT b.*, p.nama AS nama_pasien, p.kode_rekam_medis 
                  FROM booking_operasi b 
                  LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien 
                  WHERE b.no_rawat = ? AND b.kode_paket = ? AND b.tanggal = ? AND b.jam_mulai = ?";
$stmt = $db->prepare($query_booking);
$stmt->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Data booking tidak ditemukan!");
}

// Ambil data konsultasi
$query_konsul = "SELECT * FROM tbl_anestesi_konsultasi_anestesi 
                 WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
$stmt_konsul = $db->prepare($query_konsul);
$stmt_konsul->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$konsul = $stmt_konsul->fetch(PDO::FETCH_ASSOC);

if (!$konsul) {
    die("Data konsultasi belum diisi!");
}

// Function helper untuk display
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function displayRadio($value, $compare, $label) {
    return ($value == $compare) ? "[✓] $label" : "[ ] $label";
}

// Tanggal cetak
$tanggal_cetak = date('d F Y, H:i');

// Generate HTML untuk PDF
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Konsultasi Anestesi - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
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
            font-size: 18pt;
            color: #004d80;
        }
        
        .header .subtitle {
            font-size: 12pt;
            color: #666;
            margin: 5px 0;
        }
        
        .header .doc-code {
            font-size: 10pt;
            color: #999;
            font-weight: bold;
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
        
        .subsection-title {
            background: #e0e0e0;
            padding: 6px;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }
        
        .data-table td.label {
            width: 200px;
            font-weight: bold;
            background: #f9f9f9;
        }
        
        .checkbox-group {
            margin: 5px 0;
        }
        
        .radio-group {
            margin: 5px 0;
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
        <h1>KONSULTASI ANESTESI</h1>
        <div class="subtitle">RUMAH SAKIT PRASETYA BUNDA</div>
        <div class="doc-code">RMOK 3A</div>
    </div>
    
    <!-- Informasi Pasien -->
    <div class="patient-info">
        <table>
            <tr>
                <td>No. Rawat</td>
                <td>: <?= displayValue($booking['no_rawat']) ?></td>
                <td>Nama Pasien</td>
                <td>: <?= displayValue($booking['nama_pasien']) ?></td>
            </tr>
            <tr>
                <td>Kode Rekam Medis</td>
                <td>: <?= displayValue($booking['kode_rekam_medis']) ?></td>
                <td>Tanggal Operasi</td>
                <td>: <?= displayValue($booking['tanggal']) ?></td>
            </tr>
            <tr>
                <td>Kode Paket</td>
                <td>: <?= displayValue($booking['kode_paket']) ?></td>
                <td>Jam Operasi</td>
                <td>: <?= displayValue($booking['jam_mulai']) ?></td>
            </tr>
            <tr>
                <td>Ruang Perawatan</td>
                <td>: <?= displayValue($konsul['ruang_perawatan']) ?></td>
                <td>Dokter Merawat</td>
                <td>: <?= displayValue($konsul['dokter_merawat']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Data Pasien -->
    <div class="section-title">DATA PASIEN</div>
    <table class="data-table">
        <tr>
            <td class="label">Tanggal Konsul</td>
            <td><?= displayValue($konsul['tanggal_konsul']) ?></td>
            <td class="label">Jam Konsul</td>
            <td><?= displayValue($konsul['jam_konsul']) ?></td>
        </tr>
        <tr>
            <td class="label">Tinggi Badan</td>
            <td><?= displayValue($konsul['tinggi_badan']) ?> cm</td>
            <td class="label">Berat Badan</td>
            <td><?= displayValue($konsul['berat_badan']) ?> kg</td>
        </tr>
        <tr>
            <td class="label">Diagnosa Pra Operasi</td>
            <td colspan="3"><?= displayValue($konsul['diagnosa_pra_operasi']) ?></td>
        </tr>
        <tr>
            <td class="label">Rencana Tindakan Operasi</td>
            <td colspan="3"><?= displayValue($konsul['rencana_tindakan_operasi']) ?></td>
        </tr>
        <tr>
            <td class="label">Kondisi Khusus</td>
            <td colspan="3"><?= displayValue($konsul['kondisi_khusus']) ?></td>
        </tr>
    </table>
    
    <!-- Anamnesa -->
    <div class="section-title">ANAMNESA</div>
    <table class="data-table">
        <tr>
            <td class="label">Jam Visit</td>
            <td><?= displayValue($konsul['jam_visit']) ?></td>
            <td class="label">Menikah</td>
            <td><?= displayValue($konsul['menikah']) ?></td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td><?= displayValue($konsul['jenis_kelamin']) ?></td>
            <td class="label">Merokok</td>
            <td><?= displayValue($konsul['merokok']) ?></td>
        </tr>
        <tr>
            <td class="label">Alkohol</td>
            <td><?= displayValue($konsul['alkohol']) ?></td>
            <td class="label">Pengobatan</td>
            <td><?= displayValue($konsul['has_pengobatan']) ?></td>
        </tr>
        <?php if ($konsul['has_pengobatan'] == 'Ya'): ?>
        <tr>
            <td class="label">Detail Pengobatan</td>
            <td colspan="3"><?= displayValue($konsul['pengobatan']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Alergi Obat</td>
            <td><?= displayValue($konsul['has_alergi_obat']) ?></td>
            <td class="label">Alergi Makanan</td>
            <td><?= displayValue($konsul['alergi_makanan']) ?></td>
        </tr>
        <?php if ($konsul['has_alergi_obat'] == 'Ya'): ?>
        <tr>
            <td class="label">Daftar Alergi Obat</td>
            <td colspan="3"><?= displayValue($konsul['daftar_alergi_obat']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Alergi Lateks</td>
            <td><?= displayValue($konsul['alergi_lateks']) ?></td>
            <td class="label">Komunikasi</td>
            <td><?= displayValue($konsul['komunikasi']) ?></td>
        </tr>
    </table>
    
    <!-- Riwayat Penyakit -->
    <div class="subsection-title">RIWAYAT PENYAKIT</div>
    <div class="checkbox-group">
        <?php
        $penyakit = [
            'asma' => 'Asma',
            'hepatitis' => 'Hepatitis / Sakit Kuning',
            'sesak_nafas' => 'Sesak Nafas',
            'pingsan' => 'Pingsan',
            'sumbatan_jalan_nafas' => 'Sumbatan Jalan Nafas',
            'diabetes' => 'Diabetes',
            'tidur_mengorok' => 'Tidur / Mengorok',
            'anemia' => 'Anemia',
            'serangan_jantung' => 'Serangan Jantung',
            'sakit_maag' => 'Sakit Maag',
            'hipertensi' => 'Hipertensi',
            'pendarahan' => 'Pendarahan',
            'stroke' => 'Stroke',
            'pembekuan_darah' => 'Pembekuan Darah',
            'kejang' => 'Kejang',
            'penyakit_berat_lainnya' => 'Penyakit Berat Lainnya'
        ];
        
        foreach ($penyakit as $key => $label) {
            $checked = ($konsul[$key] == 'Ya') ? '[✓]' : '[ ]';
            echo "$checked $label &nbsp;&nbsp;&nbsp; ";
            if (($key == 'tidur_mengorok') || ($key == 'pembekuan_darah')) echo "<br>";
        }
        ?>
    </div>
    
    <!-- Pemeriksaan Fisik -->
    <div class="section-title">PEMERIKSAAN FISIK</div>
    <table class="data-table">
        <tr>
            <td class="label">Kesadaran</td>
            <td><?= displayValue($konsul['kesadaran']) ?></td>
            <td class="label">Tekanan Darah</td>
            <td><?= displayValue($konsul['td']) ?> mmHg</td>
        </tr>
        <tr>
            <td class="label">Nadi</td>
            <td><?= displayValue($konsul['nadi']) ?> x/menit</td>
            <td class="label">RR</td>
            <td><?= displayValue($konsul['rr']) ?> x/menit</td>
        </tr>
        <tr>
            <td class="label">Suhu</td>
            <td><?= displayValue($konsul['suhu']) ?> °C</td>
            <td class="label">Skrining Nyeri</td>
            <td><?= displayValue($konsul['skrining_nyeri']) ?></td>
        </tr>
        <tr>
            <td class="label">Jalan Nafas</td>
            <td><?= displayValue($konsul['jalan_nafas']) ?></td>
            <td class="label">Gerakan Leher</td>
            <td><?= displayValue($konsul['gerakan_leher']) ?></td>
        </tr>
        <?php if ($konsul['gerakan_leher'] == 'Abnormal'): ?>
        <tr>
            <td class="label">Keterangan Gerakan Leher</td>
            <td colspan="3"><?= displayValue($konsul['gerakan_leher_keterangan']) ?></td>
        </tr>
        <?php endif; ?>
    </table>
    
    <!-- Pemeriksaan Organ -->
    <div class="subsection-title">PEMERIKSAAN ORGAN</div>
    <table class="data-table">
        <tr>
            <td class="label">Paru-paru</td>
            <td><?= displayValue($konsul['paru_paru']) ?></td>
        </tr>
        <tr>
            <td class="label">Jantung</td>
            <td><?= displayValue($konsul['jantung']) ?></td>
        </tr>
        <tr>
            <td class="label">Abdomen</td>
            <td><?= displayValue($konsul['abdomen']) ?></td>
        </tr>
        <tr>
            <td class="label">Ekstrimitas</td>
            <td><?= displayValue($konsul['ekstrimitas']) ?></td>
        </tr>
        <tr>
            <td class="label">Neurologi</td>
            <td><?= displayValue($konsul['neurologi']) ?></td>
        </tr>
    </table>
    
    <!-- Pemeriksaan Penunjang -->
    <div class="subsection-title">PEMERIKSAAN PENUNJANG</div>
    <table class="data-table">
        <tr>
            <td class="label">Hb/Ht/AL/AT</td>
            <td><?= displayValue($konsul['hb_ht_al_at']) ?></td>
            <td class="label">NA/K/CL</td>
            <td><?= displayValue($konsul['na_k_cl']) ?></td>
        </tr>
        <tr>
            <td class="label">Ureum</td>
            <td><?= displayValue($konsul['ureum']) ?></td>
            <td class="label">CT/BT</td>
            <td><?= displayValue($konsul['ct_bt']) ?></td>
        </tr>
        <tr>
            <td class="label">Kreatin</td>
            <td><?= displayValue($konsul['kreatin']) ?></td>
            <td class="label">EKG</td>
            <td><?= displayValue($konsul['ekg']) ?></td>
        </tr>
        <tr>
            <td class="label">RO Dada</td>
            <td><?= displayValue($konsul['ro_dada']) ?></td>
            <td class="label">Echo</td>
            <td><?= displayValue($konsul['echo']) ?></td>
        </tr>
    </table>
    
    <!-- Diagnosis -->
    <div class="section-title">DIAGNOSIS</div>
    <table class="data-table">
        <tr>
            <td class="label">ASA Status</td>
            <td><?= displayValue($konsul['asa_status']) ?></td>
            <td class="label">Emergency</td>
            <td><?= displayValue($konsul['emergency']) ?></td>
        </tr>
        <tr>
            <td class="label">Rekomendasi Anestesi</td>
            <td colspan="3"><?= displayValue($konsul['rekomendasi_anestesi']) ?></td>
        </tr>
    </table>
    
    <!-- Rekomendasi Tindakan Anestesi -->
    <div class="subsection-title">REKOMENDASI TINDAKAN ANESTESI</div>
    <table class="data-table">
        <tr>
            <td class="label">Anestesi Umum</td>
            <td><?= displayValue($konsul['anestesi_umum']) ?></td>
        </tr>
        <tr>
            <td class="label">Regional Anestesi</td>
            <td><?= displayValue($konsul['regional_anestesi']) ?></td>
        </tr>
        <tr>
            <td class="label">Kombinasi Anestesi</td>
            <td><?= displayValue($konsul['kombinasi_anestesi']) ?></td>
        </tr>
        <tr>
            <td class="label">Sedasi</td>
            <td><?= displayValue($konsul['sedasi']) ?></td>
        </tr>
    </table>
    
    <!-- Saran & Rencana -->
    <div class="subsection-title">SARAN & RENCANA</div>
    <table class="data-table">
        <tr>
            <td class="label">Saran</td>
            <td><?= displayValue($konsul['saran']) ?></td>
        </tr>
        <tr>
            <td class="label">Puasa Mulai</td>
            <td><?= displayValue($konsul['puasa_mulai_tanggal']) ?> <?= displayValue($konsul['puasa_mulai_jam']) ?></td>
        </tr>
        <tr>
            <td class="label">Rencana Tiba di OK</td>
            <td><?= displayValue($konsul['rencana_tiba_tanggal']) ?> <?= displayValue($konsul['rencana_tiba_jam']) ?></td>
        </tr>
        <tr>
            <td class="label">Rencana Operasi</td>
            <td><?= displayValue($konsul['rencana_operasi_tanggal']) ?> <?= displayValue($konsul['rencana_operasi_jam']) ?></td>
        </tr>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        Dicetak tanggal: <?= $tanggal_cetak ?> | Halaman 1
    </div>
    
    <!-- Tombol Print (hide saat print) -->
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

// Jika menggunakan mPDF
if ($use_mpdf) {
    require_once __DIR__ . '/../../vendor/autoload.php'; // Jika pakai composer
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_left' => 15,
        'margin_right' => 15
    ]);
    
    $mpdf->WriteHTML($html);
    $mpdf->Output("Konsultasi_Anestesi_{$no_rawat}.pdf", 'I'); // I = inline, D = download
} else {
    // Output HTML langsung (bisa di-print via browser)
    echo $html;
}
?>
