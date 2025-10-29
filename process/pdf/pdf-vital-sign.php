<?php
/**
 * PDF VITAL SIGN - Laporan Grafik Vital Sign
 * Design: Modern dengan Chart
 * Library: mPDF / TCPDF
 */

session_start();
require_once __DIR__ . '/../../config/database.php';

// Get data from POST
$pdf_data = json_decode($_POST['pdf_data'] ?? '{}', true);

if (empty($pdf_data)) {
    die('Error: No data received');
}

$no_rawat = $pdf_data['no_rawat'] ?? '';
$kode_paket = $pdf_data['kode_paket'] ?? '';
$tanggal = $pdf_data['tanggal'] ?? '';
$jam_mulai = $pdf_data['jam_mulai'] ?? '';
$chart_image = $pdf_data['chart_image'] ?? '';
$db_data = $pdf_data['db_data'] ?? [];
$draft_data = $pdf_data['draft_data'] ?? [];
$new_data = $pdf_data['new_data'] ?? [];
$current_mode = $pdf_data['current_mode'] ?? 'all';
$pasien = $pdf_data['pasien'] ?? [];

// Gabungkan data berdasarkan mode
$all_data = [];
if ($current_mode === 'all') {
    $all_data = array_merge($db_data, $draft_data, $new_data);
} elseif ($current_mode === 'database') {
    $all_data = $db_data;
} elseif ($current_mode === 'draft') {
    $all_data = $draft_data;
} elseif ($current_mode === 'new') {
    $all_data = $new_data;
}

// Sort by time
usort($all_data, function($a, $b) {
    return strcmp($a['jam'], $b['jam']);
});

$tanggal_formatted = date('d F Y', strtotime($tanggal));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Vital Sign - <?= htmlspecialchars($pasien['nama']) ?></title>
    <style>
        /* ===== PAGE SETUP ===== */
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
        
        /* ===== RESET & BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
            background: #f4f7fb;
        }
        
        /* ===== HEADER MODERN ===== */
        .page-header {
            background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2);
            margin-bottom: 20px;
            text-align: center;
            color: white;
        }
        
        .page-header h1 {
            font-size: 20pt;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .page-header .subtitle {
            font-size: 11pt;
            opacity: 0.9;
        }
        
        /* ===== INFO PASIEN ===== */
        .info-pasien {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .info-pasien table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-pasien td {
            padding: 6px 10px;
            font-size: 10pt;
        }
        
        .info-pasien td:first-child {
            width: 150px;
            font-weight: 600;
            color: #555;
        }
        
        .info-pasien td:nth-child(2) {
            width: 10px;
            color: #999;
        }
        
        /* ===== CHART SECTION ===== */
        .chart-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .chart-section h2 {
            font-size: 14pt;
            color: #4285f4;
            margin-bottom: 15px;
            text-align: left;
        }
        
        .chart-section img {
            max-width: 100%;
            height: auto;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        
        /* ===== TABLE DATA ===== */
        .data-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .data-section h2 {
            font-size: 14pt;
            color: #4285f4;
            margin-bottom: 15px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .data-table thead {
            background: #4285f4;
            color: white;
        }
        
        .data-table th {
            padding: 10px 8px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #3367d6;
        }
        
        .data-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:hover {
            background: #e3f2fd;
        }
        
        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }
        
        .badge-respirasi {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-nadi {
            background: #fff3e0;
            color: #e65100;
        }
        
        .badge-td {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-fio2 {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-spo2 {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        /* ===== FOOTER ===== */
        .page-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48pt;
            margin-bottom: 10px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="page-header">
        <h1>📊 LAPORAN VITAL SIGN</h1>
        <div class="subtitle">Rumah Sakit Prasetya Bunda</div>
    </div>
    
    <!-- INFO PASIEN -->
    <div class="info-pasien">
        <table>
            <tr>
                <td>No. Rekam Medis</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($pasien['no_rkm_medis']) ?></strong></td>
                <td>Tanggal</td>
                <td>:</td>
                <td><strong><?= $tanggal_formatted ?></strong></td>
            </tr>
            <tr>
                <td>Nama Pasien</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($pasien['nama']) ?></strong></td>
                <td>Jam Mulai</td>
                <td>:</td>
                <td><strong><?= $jam_mulai ?></strong></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td><?= $pasien['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                <td>No. Rawat</td>
                <td>:</td>
                <td><?= htmlspecialchars($no_rawat) ?></td>
            </tr>
            <tr>
                <td>Umur</td>
                <td>:</td>
                <td><?= htmlspecialchars($pasien['umur']) ?> tahun</td>
                <td>Kode Paket</td>
                <td>:</td>
                <td><?= htmlspecialchars($kode_paket) ?></td>
            </tr>
        </table>
    </div>
    
    <!-- CHART -->
    <?php if (!empty($chart_image)): ?>
    <div class="chart-section">
        <h2>📈 Grafik Vital Sign</h2>
        <img src="<?= $chart_image ?>" alt="Grafik Vital Sign">
    </div>
    <?php endif; ?>
    
    <!-- DATA TABLE -->
    <div class="data-section">
        <h2>📋 Data Vital Sign (<?= count($all_data) ?> Records)</h2>
        
        <?php if (empty($all_data)): ?>
            <div class="empty-state">
                <div style="font-size: 48px; opacity: 0.3;">ℹ️</div>
                <p>Tidak ada data vital sign</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Waktu</th>
                        <th>Respirasi<br>(x/menit)</th>
                        <th>Nadi<br>(BPM)</th>
                        <th>TD Sistolik<br>(mmHg)</th>
                        <th>TD Diastolik<br>(mmHg)</th>
                        <th>FIO2<br>(%)</th>
                        <th>SPO2<br>(%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_data as $index => $record): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td style="font-family: monospace; font-weight: 600;">
                            <?= htmlspecialchars($record['jam']) ?>
                        </td>
                        <td>
                            <span class="badge badge-respirasi">
                                <?= htmlspecialchars($record['respirasi']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-nadi">
                                <?= htmlspecialchars($record['nadi']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-td">
                                <?= htmlspecialchars($record['sistol']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-td">
                                <?= htmlspecialchars($record['diastol']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-fio2">
                                <?= htmlspecialchars($record['fio2']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-spo2">
                                <?= htmlspecialchars($record['spo2']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- FOOTER -->
    <div class="page-footer">
        <p>Dicetak pada: <?= date('d F Y H:i:s') ?> WIB</p>
        <p>Rumah Sakit Prasetya Bunda - Sistem Informasi Manajemen Rumah Sakit</p>
    </div>
</body>
</html>

<?php
// Convert HTML to PDF using mPDF or TCPDF
// For now, we'll use browser's print to PDF functionality
?>

<script>
    // Auto print when page loads
    window.onload = function() {
        window.print();
    };
</script>
