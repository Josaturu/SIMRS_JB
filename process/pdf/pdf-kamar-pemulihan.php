<?php
/**
 * PDF Generator: Catatan Kamar Pemulihan (RMOK-30) - MODERN DESIGN
 * Tampilan sama persis dengan form asli
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

// Hitung umur
$umur = 0;
if (!empty($booking['tanggal_lahir'])) {
    $tanggal_lahir = new DateTime($booking['tanggal_lahir']);
    $today = new DateTime('today');
    $umur = $tanggal_lahir->diff($today)->y;
}

// Ambil data kamar pemulihan dengan JOIN untuk mendapatkan nama
$query_pemulihan = "SELECT 
                        kp.*,
                        p1.nama_perawat AS nama_perawat_menyerahkan,
                        p2.nama_perawat AS nama_perawat_menerima,
                        d.nama_dokter AS nama_dokter_anestesi
                    FROM tbl_anestesi_kamar_pemulihan kp
                    LEFT JOIN tbl_perawat p1 ON kp.perawat_menyerahkan = p1.id_perawat COLLATE utf8mb4_unicode_ci
                    LEFT JOIN tbl_perawat p2 ON kp.perawat_menerima = p2.id_perawat COLLATE utf8mb4_unicode_ci
                    LEFT JOIN tbl_dokter d ON kp.dokter_anestesi = d.id_dokter COLLATE utf8mb4_unicode_ci
                    WHERE kp.no_rawat = ? AND kp.kode_paket = ? AND kp.tanggal = ? AND kp.jam_mulai = ?";
$stmt_pemulihan = $db->prepare($query_pemulihan);
$stmt_pemulihan->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
$pemulihan = $stmt_pemulihan->fetch(PDO::FETCH_ASSOC);

if (!$pemulihan) {
    // Jika data utama tidak ada, tidak perlu melanjutkan
    die("Data catatan kamar pemulihan tidak ditemukan!");
}

// Ambil data vital sign dari tabel terpisah menggunakan ID pemulihan sebagai foreign key
$id_pemulihan = $pemulihan['id']; // Ambil ID dari data pemulihan yang sudah di-fetch
$query_vital = "SELECT id, waktu, respirasi, nadi, sistol, diastol, nyeri, spo2 
                FROM tbl_anestesi_vital_pemulihan 
                WHERE id_pemulihan = ?
                ORDER BY waktu ASC";
$stmt_vital = $db->prepare($query_vital);
$stmt_vital->execute([$id_pemulihan]);
$vital_signs = $stmt_vital->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function isChecked($value) {
    return !empty($value) && $value == 1 ? '☑' : '☐';
}

function formatJenisKelamin($jk) {
    return $jk == 'L' ? 'Laki-laki' : ($jk == 'P' ? 'Perempuan' : '-');
}

$tanggal_cetak = date('d F Y, H:i');

// Generate HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Catatan Kamar Pemulihan - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page { margin: 10mm; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9pt; line-height: 1.4; color: #333; }
        
        /* Header */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 12px; border-radius: 0 0 6px 6px; box-shadow: 0 3px 10px rgba(66, 133, 244, 0.2); margin-bottom: 12px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 6px; }
        .page-header .logo { width: 45px; height: 45px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 14pt; font-weight: 700; margin: 0; }
        .page-header .document-code { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px; font-size: 8pt; font-weight: 600; margin-top: 5px; }
        
        /* Patient Info */
        .patient-info-box { background: #e3f2fd; border: 2px solid #2196F3; border-radius: 6px; padding: 10px; margin-bottom: 12px; }
        .patient-info-box h2 { color: #1976d2; font-size: 11pt; margin-bottom: 8px; border-bottom: 2px solid #2196F3; padding-bottom: 5px; }
        .patient-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; font-size: 8.5pt; }
        .patient-item { display: flex; gap: 5px; }
        .patient-item .label { font-weight: 600; color: #666; min-width: 90px; }
        .patient-item .value { color: #333; font-weight: 500; }
        
        /* Section */
        .section { background: white; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .section h2 { color: #4285f4; font-size: 11pt; margin-bottom: 8px; border-bottom: 2px solid #4285f4; padding-bottom: 5px; }
        .section h3 { color: #495057; font-size: 10pt; margin: 8px 0 5px 0; }
        
        /* Form Grid */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 10px; }
        .form-item { font-size: 8.5pt; }
        .form-item .label { font-weight: 600; color: #495057; margin-bottom: 3px; }
        .form-item .value { background: #f8f9fa; padding: 5px 8px; border-radius: 3px; border: 1px solid #dee2e6; }
        
        /* Checkbox Group */
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 5px; }
        .checkbox-item { display: flex; align-items: center; gap: 5px; font-size: 8.5pt; }
        .checkbox-icon { width: 14px; height: 14px; border: 1px solid #4285f4; border-radius: 2px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
        .checkbox-icon.checked { background: #4285f4; color: white; }
        
        /* Vital Sign Table */
        .vital-table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8pt; }
        .vital-table thead { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; }
        .vital-table th { padding: 6px; text-align: center; font-weight: 600; border: 1px solid #dee2e6; }
        .vital-table td { padding: 6px; text-align: center; border: 1px solid #dee2e6; }
        .vital-table tbody tr:nth-child(even) { background: #f8f9fa; }
        .vital-table tbody tr:hover { background: #e3f2fd; }
        
        /* Data Table */
        .data-table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8.5pt; }
        .data-table th { background: #f8f9fa; padding: 6px; text-align: left; font-weight: 600; border: 1px solid #dee2e6; }
        .data-table td { padding: 6px; border: 1px solid #dee2e6; }
        .data-table td.label { font-weight: 600; background: #f8f9fa; width: 30%; }
        
        /* Score Box */
        .score-box { display: inline-block; background: #e3f2fd; border: 2px solid #2196F3; border-radius: 5px; padding: 8px 15px; text-align: center; margin: 5px; }
        .score-box .label { font-size: 8pt; color: #666; margin-bottom: 3px; }
        .score-box .value { font-size: 16pt; font-weight: bold; color: #2196F3; }
        
        /* Info Box */
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 8px; margin: 8px 0; border-radius: 3px; font-size: 8.5pt; }
        .info-box.success { background: #d4edda; border-left-color: #28a745; }
        .info-box.danger { background: #f8d7da; border-left-color: #dc3545; }
        
        /* Signature */
        .signature-section { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px; }
        .signature-box { text-align: center; padding: 8px; border: 1px solid #dee2e6; border-radius: 5px; background: #f8f9fa; }
        .signature-box .title { font-weight: 600; color: #4285f4; margin-bottom: 40px; font-size: 8.5pt; }
        .signature-box .name { border-top: 1px solid #333; padding-top: 5px; font-weight: 600; font-size: 8.5pt; }
        
        /* Footer */
        .page-footer { margin-top: 20px; padding: 8px; text-align: center; font-size: 7pt; color: #666; border-top: 1px solid #dee2e6; }
        
        /* Print Controls */
        @media print { .no-print { display: none; } }
        .print-controls { position: fixed; top: 10px; right: 10px; z-index: 1000; display: flex; gap: 5px; }
        .btn-print { background: #4285f4; color: white; border: none; padding: 8px 15px; border-radius: 5px; font-weight: 600; cursor: pointer; }
        .btn-close { background: #f1f3f4; color: #666; border: none; padding: 8px 15px; border-radius: 5px; font-weight: 600; cursor: pointer; }
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
                <h1>CATATAN KAMAR PEMULIHAN</h1>
                <div style="font-size: 10pt; margin-top: 2px;">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMOK - 30</div>
    </div>
    
    <!-- Patient Info -->
    <div class="patient-info-box">
        <h2>📋 Informasi Pasien & Data Booking Operasi</h2>
        <div class="patient-grid">
            <div class="patient-item">
                <div class="label">Nama Lengkap:</div>
                <div class="value"><?= displayValue($booking['nama_pasien']) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">No. Rekam Medis:</div>
                <div class="value"><?= displayValue($booking['kode_rekam_medis']) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">No. Rawat:</div>
                <div class="value"><?= displayValue($no_rawat) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">Tanggal Lahir:</div>
                <div class="value"><?= displayValue($booking['tanggal_lahir']) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">Umur:</div>
                <div class="value"><?= $umur ?> Tahun</div>
            </div>
            <div class="patient-item">
                <div class="label">Jenis Kelamin:</div>
                <div class="value"><?= formatJenisKelamin($booking['jenis_kelamin']) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">Kode Paket:</div>
                <div class="value"><?= displayValue($kode_paket) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">Tanggal Booking:</div>
                <div class="value"><?= displayValue($tanggal) ?></div>
            </div>
            <div class="patient-item">
                <div class="label">Jam Mulai:</div>
                <div class="value"><?= displayValue($jam_mulai) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Data Masuk dan Kondisi Pasien -->
    <div class="section">
        <h2>Data Masuk dan Kondisi Pasien</h2>
        
        <div class="form-grid">
            <div class="form-item">
                <div class="label">Jam Masuk:</div>
                <div class="value"><?= displayValue($pemulihan['jam_masuk']) ?></div>
            </div>
            <div class="form-item">
                <div class="label">Tanggal Masuk:</div>
                <div class="value"><?= displayValue($pemulihan['tgl_masuk']) ?></div>
            </div>
        </div>
        
        <div class="form-grid" style="margin-top: 10px;">
            <div>
                <div class="label" style="font-weight: 600; margin-bottom: 5px;">Jalan Nafas:</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['jalan_nafas_bersih'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['jalan_nafas_bersih']) ?>
                        </span>
                        <span>Bersih & lapang</span>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="label" style="font-weight: 600; margin-bottom: 5px;">Kesadaran:</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['kesadaran_sadar'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['kesadaran_sadar']) ?>
                        </span>
                        <span>Sadar betul</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['kesadaran_belum_sadar'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['kesadaran_belum_sadar']) ?>
                        </span>
                        <span>Belum sadar betul</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['kesadaran_tidur_dalam'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['kesadaran_tidur_dalam']) ?>
                        </span>
                        <span>Tidur dalam</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-grid" style="margin-top: 10px;">
            <div>
                <div class="label" style="font-weight: 600; margin-bottom: 5px;">Pernapasan:</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['pernapasan_spontan'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['pernapasan_spontan']) ?>
                        </span>
                        <span>Spontan</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['pernapasan_dibantu'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['pernapasan_dibantu']) ?>
                        </span>
                        <span>Dibantu</span>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="label" style="font-weight: 600; margin-bottom: 5px;">Bila spontan:</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['spontan_adekuat'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['spontan_adekuat']) ?>
                        </span>
                        <span>Adekuat Bersuara</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['spontan_penyumbatan'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['spontan_penyumbatan']) ?>
                        </span>
                        <span>Penyumbatan</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox-icon <?= $pemulihan['spontan_alat'] ? 'checked' : '' ?>">
                            <?= isChecked($pemulihan['spontan_alat']) ?>
                        </span>
                        <span>Membutuhkan alat</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monitoring Vital Sign -->
    <div class="section">
        <h2>📊 Monitoring Vital Sign</h2>
        
        <?php 
        // Decode vital sign data dari JSON
        $vital_sign_json = [];
        if (!empty($pemulihan['vital_sign_data'])) {
            $vital_sign_json = json_decode($pemulihan['vital_sign_data'], true);
        }
        ?>
        
        <?php if (!empty($vital_sign_json)): ?>
        <!-- Grafik Vital Sign -->
        <?php if (!empty($pemulihan['chart_image'])): ?>
        <div style="text-align: center; margin-bottom: 15px; background: white; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6;">
            <h3 style="margin: 0 0 10px 0; color: #495057; font-size: 10pt;">Grafik Vital Sign</h3>
            <img src="<?= $pemulihan['chart_image'] ?>" style="max-width: 100%; height: auto; border-radius: 4px;" alt="Grafik Vital Sign">
        </div>
        <?php endif; ?>
        
        <!-- Tabel Vital Sign -->
        <table class="vital-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Waktu</th>
                    <th style="width: 14%;">Respirasi<br>(x/menit)</th>
                    <th style="width: 14%;">Nadi<br>(BPM)</th>
                    <th style="width: 14%;">TD Sistol<br>(mmHg)</th>
                    <th style="width: 14%;">TD Diastol<br>(mmHg)</th>
                    <th style="width: 15%;">Skala Nyeri<br>(0-10)</th>
                    <th style="width: 15%;">SpO2<br>(%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vital_signs as $vs):
                    // Ekstrak hanya waktu (HH:MM:SS) dari datetime
                    $waktu_tampil = date('H:i:s', strtotime($vs['waktu']));
                ?>
                <tr>
                    <td><?= displayValue($waktu_tampil) ?></td>
                    <td><?= displayValue($vs['respirasi'] ?? '') ?></td>
                    <td><?= displayValue($vs['nadi'] ?? '') ?></td>
                    <td><?= displayValue($vs['sistol'] ?? '') ?></td>
                    <td><?= displayValue($vs['diastol'] ?? '') ?></td>
                    <td><?= displayValue($vs['nyeri'] ?? '') ?></td>
                    <td><?= displayValue($vs['spo2'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="info-box">
            <strong>ℹ️ Info:</strong> Belum ada data vital sign yang tercatat.
        </div>
        <?php endif; ?>
        
        <div class="form-grid" style="margin-top: 10px;">
            <div class="form-item">
                <div class="label">Pemantauan Setiap:</div>
                <div class="value"><?= displayValue($pemulihan['pemantauan_setiap']) ?></div>
            </div>
            <div class="form-item">
                <div class="label">Pemantauan Selama:</div>
                <div class="value"><?= displayValue($pemulihan['pemantauan_selama']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Terapi dan Tindakan -->
    <div class="section">
        <h2>💊 Terapi dan Tindakan</h2>
        
        <table class="data-table">
            <tr>
                <td class="label">Analgesia</td>
                <td><?= displayValue($pemulihan['analgesia']) ?></td>
            </tr>
            <tr>
                <td class="label">Anti Muntah</td>
                <td><?= displayValue($pemulihan['anti_muntah']) ?></td>
            </tr>
            <tr>
                <td class="label">Antibiotik</td>
                <td><?= displayValue($pemulihan['antibiotik']) ?></td>
            </tr>
            <tr>
                <td class="label">Posisi Pasien</td>
                <td><?= displayValue($pemulihan['posisi_pasien']) ?></td>
            </tr>
            <tr>
                <td class="label">Obat Lain</td>
                <td><?= displayValue($pemulihan['obat_lain']) ?></td>
            </tr>
            <tr>
                <td class="label">Diet/Nutrisi</td>
                <td><?= displayValue($pemulihan['diet_nutrisi']) ?></td>
            </tr>
            <tr>
                <td class="label">Lain-lain</td>
                <td><?= displayValue($pemulihan['lain_lain']) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Data Keluar -->
    <div class="section">
        <h2>🚪 Data Keluar Kamar Pemulihan</h2>
        
        <div class="form-grid">
            <div class="form-item">
                <div class="label">Jam Keluar:</div>
                <div class="value"><?= displayValue($pemulihan['jam_keluar']) ?></div>
            </div>
            <div class="form-item">
                <div class="label">Skrining Nyeri:</div>
                <div class="value"><?= displayValue(strtoupper($pemulihan['skrining_nyeri'])) ?></div>
            </div>
        </div>
        
        <h3>Vital Sign Saat Keluar:</h3>
        <div class="form-grid">
            <div class="form-item">
                <div class="label">Tekanan Darah:</div>
                <div class="value"><?= displayValue($pemulihan['td_keluar']) ?> mmHg</div>
            </div>
            <div class="form-item">
                <div class="label">Nadi:</div>
                <div class="value"><?= displayValue($pemulihan['n_keluar']) ?> x/menit</div>
            </div>
            <div class="form-item">
                <div class="label">Respirasi:</div>
                <div class="value"><?= displayValue($pemulihan['r_keluar']) ?> x/menit</div>
            </div>
            <div class="form-item">
                <div class="label">Suhu:</div>
                <div class="value"><?= displayValue($pemulihan['s_keluar']) ?> °C</div>
            </div>
            <div class="form-item">
                <div class="label">SpO2:</div>
                <div class="value"><?= displayValue($pemulihan['spo2_keluar']) ?> %</div>
            </div>
            <div class="form-item">
                <div class="label">Tujuan Keluar:</div>
                <div class="value">
                    <?php 
                    $tujuan = $pemulihan['tujuan_keluar'] ?? '';
                    if ($tujuan == 'ruang_rawat') echo 'Ruang Rawat';
                    elseif ($tujuan == 'icu') echo 'ICU';
                    elseif ($tujuan == 'pulang') echo 'Pulang';
                    else echo '-';
                    ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($pemulihan['catatan_khusus'])): ?>
        <div class="info-box" style="margin-top: 10px;">
            <strong>📝 Catatan Khusus:</strong><br>
            <?= displayValue($pemulihan['catatan_khusus']) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Scoring Systems -->
    <div class="section">
        <h2>📈 Scoring Systems</h2>
        
        <div style="text-align: center; margin: 10px 0;">
            <div class="score-box">
                <div class="label">Aldrete Score</div>
                <div class="value"><?= displayValue($pemulihan['aldrete_score'], '0') ?></div>
            </div>
            
            <div class="score-box">
                <div class="label">Bromage Score</div>
                <div class="value"><?= displayValue($pemulihan['bromage_score'], '0') ?></div>
            </div>
            
            <div class="score-box">
                <div class="label">Steward Score</div>
                <div class="value"><?= displayValue($pemulihan['steward_score'], '0') ?></div>
            </div>
        </div>
    </div>
    
    <!-- Penanggung Jawab -->
    <div class="section">
        <h2>👥 Penanggung Jawab</h2>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama Penanggung Jawab</td>
                <td><?= displayValue($pemulihan['nama_penanggungjawab']) ?></td>
            </tr>
        </table>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="title">Perawat Menyerahkan</div>
                <div class="name"><?= displayValue($pemulihan['nama_perawat_menyerahkan']) ?></div>
            </div>
            
            <div class="signature-box">
                <div class="title">Perawat Menerima</div>
                <div class="name"><?= displayValue($pemulihan['nama_perawat_menerima']) ?></div>
            </div>
            
            <div class="signature-box">
                <div class="title">Dokter Anestesi</div>
                <div class="name"><?= displayValue($pemulihan['nama_dokter_anestesi']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="page-footer">
        <div style="display: flex; justify-content: space-between;">
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
