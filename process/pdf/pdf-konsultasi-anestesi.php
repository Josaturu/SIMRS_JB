<?php
/**
 * PDF Generator: Konsultasi Anestesi (RMOK 3A) - MODERN DESIGN
 * Menggunakan design system yang konsisten dengan form web
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
$query_booking = "SELECT b.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.tanggal_lahir, p.jenis_kelamin
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

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function displayCheck($value, $compare) {
    return ($value == $compare) ? '✓' : '';
}

$tanggal_cetak = date('d F Y, H:i');

// Generate HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Konsultasi Anestesi - <?= $booking['nama_pasien'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @page { margin: 12mm; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9pt; line-height: 1.5; color: #333; background: #f4f7fb; }
        
        /* Header Modern */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 15px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2); margin-bottom: 15px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px; }
        .page-header .logo { width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 16pt; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
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
        
        /* Modern Grid */
        .data-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 8px 0; }
        .data-grid.three-cols { grid-template-columns: repeat(3, 1fr); }
        .data-grid.four-cols { grid-template-columns: repeat(4, 1fr); }
        .data-grid-item { background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .data-grid-item .label { font-size: 7.5pt; color: #4285f4; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }
        .data-grid-item .value { font-size: 9pt; color: #333; font-weight: 500; }
        
        /* Checkbox List */
        .checkbox-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 8px 0; }
        .checkbox-item { display: flex; align-items: center; gap: 6px; padding: 5px; border-radius: 3px; font-size: 8pt; }
        .checkbox-item.checked { background: #e3f0ff; font-weight: 500; }
        .checkbox-icon { width: 14px; height: 14px; border: 2px solid #4285f4; border-radius: 2px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #4285f4; }
        .checkbox-icon.checked { background: #4285f4; color: white; }
        
        /* Signature */
        .signature-section { margin-top: 20px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .signature-box { text-align: center; padding: 12px; border: 2px solid #e3f0ff; border-radius: 6px; background: white; }
        .signature-box .title { font-weight: 600; color: #4285f4; margin-bottom: 40px; font-size: 9pt; }
        .signature-box .name { border-top: 2px solid #333; padding-top: 6px; font-weight: 600; color: #333; font-size: 9pt; }
        
        /* Footer */
        .page-footer { margin-top: 30px; background: white; border-top: 2px solid #4285f4; padding: 8px 15px; text-align: center; font-size: 7.5pt; color: #666; }
        .page-footer .print-info { display: flex; justify-content: space-between; }
        
        /* Print Controls */
        @media print { .no-print { display: none; } body { background: white; } }
        .print-controls { position: fixed; top: 15px; right: 15px; z-index: 1000; display: flex; gap: 8px; }
        .btn-print { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 10pt; cursor: pointer; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3); }
        .btn-close { background: #f1f3f4; color: #666; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 10pt; cursor: pointer; }
        
        /* Info Box */
        .info-box { background: white; border-left: 4px solid #4285f4; border-radius: 5px; padding: 10px; margin: 8px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-size: 8.5pt; }
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
                <h1>KONSULTASI ANESTESI</h1>
                <div class="hospital-name">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMOK 3A</div>
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
                <div class="label">Tanggal Operasi:</div>
                <div class="value"><?= displayValue($booking['tanggal']) ?></div>
            </div>
            <div class="patient-info-item">
                <div class="label">Jam Operasi:</div>
                <div class="value"><?= displayValue($booking['jam_mulai']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Data Pasien -->
    <div class="section-header">▶ DATA PASIEN</div>
    <div class="data-grid four-cols">
        <div class="data-grid-item">
            <div class="label">Tanggal Konsul</div>
            <div class="value"><?= displayValue($konsul['tanggal_konsul']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Jam Konsul</div>
            <div class="value"><?= displayValue($konsul['jam_konsul']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Tinggi Badan</div>
            <div class="value"><?= displayValue($konsul['tinggi_badan']) ?> cm</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Berat Badan</div>
            <div class="value"><?= displayValue($konsul['berat_badan']) ?> kg</div>
        </div>
    </div>
    
    <table class="data-table">
        <tr>
            <td class="label">Ruang Perawatan</td>
            <td><?= displayValue($konsul['ruang_perawatan']) ?></td>
            <td class="label">Dokter Merawat</td>
            <td><?= displayValue($konsul['dokter_merawat']) ?></td>
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
    <div class="section-header">▶ ANAMNESA</div>
    <div class="data-grid four-cols">
        <div class="data-grid-item">
            <div class="label">Jam Visit</div>
            <div class="value"><?= displayValue($konsul['jam_visit']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Menikah</div>
            <div class="value"><?= displayValue($konsul['menikah']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Jenis Kelamin</div>
            <div class="value"><?= displayValue($konsul['jenis_kelamin']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Merokok</div>
            <div class="value"><?= displayValue($konsul['merokok']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Alkohol</div>
            <div class="value"><?= displayValue($konsul['alkohol']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Pengobatan</div>
            <div class="value"><?= displayValue($konsul['has_pengobatan']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Alergi Makanan</div>
            <div class="value"><?= displayValue($konsul['alergi_makanan']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Alergi Lateks</div>
            <div class="value"><?= displayValue($konsul['alergi_lateks']) ?></div>
        </div>
    </div>
    
    <?php if ($konsul['has_pengobatan'] == 'Ya'): ?>
    <div class="info-box">
        <strong>Detail Pengobatan:</strong> <?= displayValue($konsul['pengobatan']) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($konsul['has_alergi_obat'] == 'Ya'): ?>
    <div class="info-box" style="border-left-color: #fbbc04; background: #fffbeb;">
        <strong>⚠️ Daftar Alergi Obat:</strong> <?= displayValue($konsul['daftar_alergi_obat']) ?>
    </div>
    <?php endif; ?>
    
    <!-- Riwayat Penyakit -->
    <div class="subsection-header">Riwayat Penyakit</div>
    <div class="checkbox-grid">
        <?php
        $penyakit = [
            'asma' => 'Asma',
            'hepatitis' => 'Hepatitis',
            'sesak_nafas' => 'Sesak Nafas',
            'pingsan' => 'Pingsan',
            'sumbatan_jalan_nafas' => 'Sumbatan Jalan Nafas',
            'diabetes' => 'Diabetes',
            'tidur_mengorok' => 'Tidur Mengorok',
            'anemia' => 'Anemia',
            'serangan_jantung' => 'Serangan Jantung',
            'sakit_maag' => 'Sakit Maag',
            'hipertensi' => 'Hipertensi',
            'pendarahan' => 'Pendarahan',
            'stroke' => 'Stroke',
            'pembekuan_darah' => 'Pembekuan Darah',
            'kejang' => 'Kejang'
        ];
        
        foreach ($penyakit as $key => $label) {
            $checked = ($konsul[$key] == 'Ya') ? 'checked' : '';
            echo '<div class="checkbox-item ' . $checked . '">';
            echo '<div class="checkbox-icon ' . $checked . '">' . ($checked ? '✓' : '') . '</div>';
            echo '<span>' . $label . '</span>';
            echo '</div>';
        }
        ?>
    </div>
    
    <!-- Pemeriksaan Fisik -->
    <div class="section-header">▶ PEMERIKSAAN FISIK</div>
    <div class="data-grid four-cols">
        <div class="data-grid-item">
            <div class="label">Kesadaran</div>
            <div class="value"><?= displayValue($konsul['kesadaran']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Tekanan Darah</div>
            <div class="value"><?= displayValue($konsul['td']) ?> mmHg</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Nadi</div>
            <div class="value"><?= displayValue($konsul['nadi']) ?> x/menit</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Respirasi</div>
            <div class="value"><?= displayValue($konsul['rr']) ?> x/menit</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Suhu</div>
            <div class="value"><?= displayValue($konsul['suhu']) ?> °C</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Skrining Nyeri</div>
            <div class="value"><?= displayValue($konsul['skrining_nyeri']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Jalan Nafas</div>
            <div class="value">
                <?= displayValue($konsul['jalan_nafas']) ?>
                <?php if ($konsul['jalan_nafas'] == 'Abnormal' && !empty($konsul['jalan_nafas_keterangan'])): ?>
                    <br><small style="color: #e74c3c; font-weight: 600;">Keterangan: <?= displayValue($konsul['jalan_nafas_keterangan']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Pemeriksaan Organ -->
    <div class="subsection-header">Pemeriksaan Organ</div>
    <table class="data-table">
        <tr>
            <td class="label">Paru-paru</td>
            <td><?= displayValue($konsul['paru_paru']) ?></td>
            <td class="label">Jantung</td>
            <td><?= displayValue($konsul['jantung']) ?></td>
        </tr>
        <tr>
            <td class="label">Abdomen</td>
            <td><?= displayValue($konsul['abdomen']) ?></td>
            <td class="label">Ekstrimitas</td>
            <td><?= displayValue($konsul['ekstrimitas']) ?></td>
        </tr>
        <tr>
            <td class="label">Neurologi</td>
            <td colspan="3"><?= displayValue($konsul['neurologi']) ?></td>
        </tr>
    </table>
    
    <!-- Pemeriksaan Penunjang -->
    <div class="subsection-header">Pemeriksaan Penunjang</div>
    <div class="data-grid three-cols">
        <div class="data-grid-item">
            <div class="label">Hb/Ht/AL/AT</div>
            <div class="value"><?= displayValue($konsul['hb_ht_al_at']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">NA/K/CL</div>
            <div class="value"><?= displayValue($konsul['na_k_cl']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Ureum</div>
            <div class="value"><?= displayValue($konsul['ureum']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">CT/BT</div>
            <div class="value"><?= displayValue($konsul['ct_bt']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Kreatin</div>
            <div class="value"><?= displayValue($konsul['kreatin']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">EKG</div>
            <div class="value"><?= displayValue($konsul['ekg']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">RO Dada</div>
            <div class="value"><?= displayValue($konsul['ro_dada']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Echo</div>
            <div class="value"><?= displayValue($konsul['echo']) ?></div>
        </div>
    </div>
    
    <!-- Diagnosis -->
    <div class="section-header">▶ DIAGNOSIS</div>
    <div class="data-grid">
        <div class="data-grid-item">
            <div class="label">ASA Status</div>
            <div class="value" style="font-size: 11pt; font-weight: 700; color: #4285f4;"><?= displayValue($konsul['asa_status']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Emergency</div>
            <div class="value"><?= displayValue($konsul['emergency']) ?></div>
        </div>
    </div>
    
    <div class="info-box">
        <strong>Rekomendasi Anestesi:</strong> <?= displayValue($konsul['rekomendasi_anestesi']) ?>
    </div>
    
    <!-- Rekomendasi Tindakan Anestesi -->
    <div class="subsection-header">Rekomendasi Tindakan Anestesi</div>
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
    <div class="subsection-header">Saran & Rencana</div>
    <div class="info-box" style="border-left-color: #34a853; background: #f0fdf4;">
        <strong>💡 Saran:</strong> <?= displayValue($konsul['saran']) ?>
    </div>
    
    <div class="data-grid three-cols">
        <div class="data-grid-item">
            <div class="label">Puasa Mulai</div>
            <div class="value"><?= displayValue($konsul['puasa_mulai_tanggal']) ?> <?= displayValue($konsul['puasa_mulai_jam']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Rencana Tiba di OK</div>
            <div class="value"><?= displayValue($konsul['rencana_tiba_tanggal']) ?> <?= displayValue($konsul['rencana_tiba_jam']) ?></div>
        </div>
        <div class="data-grid-item">
            <div class="label">Rencana Operasi</div>
            <div class="value"><?= displayValue($konsul['rencana_operasi_tanggal']) ?> <?= displayValue($konsul['rencana_operasi_jam']) ?></div>
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
