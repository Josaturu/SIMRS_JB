<?php
/**
 * PDF Generator: Catatan Sedasi & Anestesi (RMOK 3B) - MODERN DESIGN
 * Menggunakan design system yang konsisten dengan PDF lainnya
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
        @page { margin: 10mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 8.5pt; line-height: 1.5; color: #333; background: #f4f7fb; }
        
        /* Header Modern */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 12px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2); margin-bottom: 12px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 6px; }
        .page-header .logo { width: 45px; height: 45px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 14pt; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .page-header .hospital-name { font-size: 10pt; margin: 3px 0; opacity: 0.95; }
        .page-header .document-code { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px; font-size: 7.5pt; font-weight: 600; margin-top: 4px; }
        
        /* Patient Info Box */
        .patient-info-box { background: white; border: 2px dashed #4285f4; border-radius: 6px; padding: 10px; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .patient-info-box .title { background: #4285f4; color: white; padding: 4px 8px; border-radius: 3px; font-weight: 600; font-size: 8pt; margin: -10px -10px 8px -10px; display: inline-block; }
        .patient-info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
        .patient-info-item { display: flex; gap: 6px; font-size: 7.5pt; }
        .patient-info-item .label { font-weight: 600; color: #666; min-width: 90px; }
        .patient-info-item .value { color: #333; font-weight: 500; }
        
        /* Section Headers */
        .section-header { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 6px 10px; border-radius: 4px; font-weight: 600; font-size: 9pt; margin: 12px 0 8px 0; box-shadow: 0 2px 6px rgba(66, 133, 244, 0.15); }
        .subsection-header { background: #e3f0ff; color: #4285f4; padding: 5px 8px; border-radius: 3px; font-weight: 600; font-size: 8pt; margin: 10px 0 6px 0; border-left: 3px solid #4285f4; }
        
        /* Modern Table */
        .data-table { width: 100%; border-collapse: collapse; margin: 6px 0; background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 7.5pt; }
        .data-table thead { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; }
        .data-table th { padding: 6px; text-align: left; font-weight: 600; font-size: 7.5pt; }
        .data-table tbody tr { border-bottom: 1px solid #e3f0ff; }
        .data-table tbody tr:nth-child(even) { background: #fafbff; }
        .data-table td { padding: 6px; color: #333; }
        .data-table td.label { font-weight: 600; background: #f4f7fb; color: #666; width: 140px; }
        
        /* Grid Layout */
        .data-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin: 6px 0; }
        .data-grid.five-cols { grid-template-columns: repeat(5, 1fr); }
        .data-grid-item { background: white; border: 1px solid #e3f0ff; border-radius: 4px; padding: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .data-grid-item .label { font-size: 6.5pt; color: #4285f4; font-weight: 600; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.3px; }
        .data-grid-item .value { font-size: 8pt; color: #333; font-weight: 500; }
        
        /* Two Column Layout */
        .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 8px 0; }
        
        /* List Compact */
        .list-compact { background: white; border: 1px solid #e3f0ff; border-radius: 4px; padding: 8px; }
        .list-compact .item { display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px solid #f0f0f0; font-size: 7.5pt; }
        .list-compact .item:last-child { border-bottom: none; }
        .list-compact .item .label { font-weight: 600; color: #666; }
        .list-compact .item .value { color: #333; }
        
        /* Footer */
        .page-footer { margin-top: 20px; background: white; border-top: 2px solid #4285f4; padding: 6px 12px; text-align: center; font-size: 7pt; color: #666; }
        .page-footer .print-info { display: flex; justify-content: space-between; }
        
        /* Print Controls */
        @media print { .no-print { display: none; } body { background: white; } }
        .print-controls { position: fixed; top: 12px; right: 12px; z-index: 1000; display: flex; gap: 6px; }
        .btn-print { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); color: white; border: none; padding: 8px 16px; border-radius: 5px; font-weight: 600; font-size: 9pt; cursor: pointer; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3); }
        .btn-close { background: #f1f3f4; color: #666; border: none; padding: 8px 16px; border-radius: 5px; font-weight: 600; font-size: 9pt; cursor: pointer; }
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
                <h1>CATATAN SEDASI & ANESTESI</h1>
                <div class="hospital-name">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMOK 3B</div>
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
        </div>
    </div>
    
    <!-- Two Column Layout -->
    <div class="two-columns">
        <!-- Left Column -->
        <div>
            <!-- Data Pasien -->
            <div class="section-header">▶ DATA PASIEN</div>
            <div class="data-grid">
                <div class="data-grid-item">
                    <div class="label">Tinggi Badan</div>
                    <div class="value"><?= displayValue($catatan['tb']) ?> cm</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Berat Badan</div>
                    <div class="value"><?= displayValue($catatan['bb']) ?> kg</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Tekanan Darah</div>
                    <div class="value"><?= displayValue($catatan['td']) ?> mmHg</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Nadi</div>
                    <div class="value"><?= displayValue($catatan['nadi']) ?> x/mnt</div>
                </div>
            </div>
            
            <div class="data-grid">
                <div class="data-grid-item">
                    <div class="label">RR</div>
                    <div class="value"><?= displayValue($catatan['respirasi']) ?> x/mnt</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Suhu</div>
                    <div class="value"><?= displayValue($catatan['suhu']) ?> °C</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Hb</div>
                    <div class="value"><?= displayValue($catatan['hb']) ?> g/dL</div>
                </div>
                <div class="data-grid-item">
                    <div class="label">GCS</div>
                    <div class="value"><?= displayValue($catatan['gcs']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Golongan Darah</div>
                    <div class="value"><?= displayValue($catatan['golongan_darah']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Jenis Pembedahan</div>
                    <div class="value"><?= displayValue($catatan['jenis_pembedahan']) ?></div>
                </div>
            </div>
            
            <!-- Assessment Pra Anestesi -->
            <?php if (!empty($catatan['asessment_pra_anestesi'])): ?>
            <div class="subsection-header">Assessment Pra Anestesi</div>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 10px; font-size: 8pt; line-height: 1.5;">
                <?= nl2br(displayValue($catatan['asessment_pra_anestesi'])) ?>
            </div>
            <?php endif; ?>
            
            <!-- Diagnosa -->
            <div class="subsection-header">Diagnosa & Tindakan</div>
            <table class="data-table">
                <tr>
                    <td class="label">Diagnosa Pra Bedah</td>
                    <td><?= displayValue($catatan['diagnosa_pra_bedah']) ?></td>
                </tr>
                <tr>
                    <td class="label">Diagnosa Pasca Bedah</td>
                    <td><?= displayValue($catatan['diagnosa_pasca_bedah']) ?></td>
                </tr>
                <tr>
                    <td class="label">Nama Tindakan</td>
                    <td><?= displayValue($catatan['nama_tindakan']) ?></td>
                </tr>
            </table>
            
            <!-- Jalan Nafas & Ventilasi -->
            <div class="subsection-header">Jalan Nafas & Ventilasi</div>
            <div class="data-grid">
                <div class="data-grid-item">
                    <div class="label">Jalan Nafas</div>
                    <div class="value"><?= displayValue($catatan['jalan_nafas']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Ventilasi</div>
                    <div class="value"><?= displayValue($catatan['ventilasi']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Ukuran Balon</div>
                    <div class="value"><?= displayValue($catatan['ukuran_balon']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Posisi ETT</div>
                    <div class="value"><?= displayValue($catatan['posisi_ett']) ?></div>
                </div>
            </div>
            
            <!-- Teknik Anestesi -->
            <div class="subsection-header">Teknik Anestesi</div>
            <div class="data-grid">
                <div class="data-grid-item">
                    <div class="label">ASA Status</div>
                    <div class="value" style="font-weight: 700; color: #4285f4;"><?= displayValue($catatan['status_fisik_asa']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Teknik Anestesi</div>
                    <div class="value"><?= displayValue($catatan['teknik_anestesi']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Posisi Operasi</div>
                    <div class="value"><?php
                        if (!empty($catatan['posisi'])) {
                            $posisi = is_array($catatan['posisi']) ? $catatan['posisi'] : explode(',', $catatan['posisi']);
                            echo htmlspecialchars(implode(', ', $posisi));
                        } else {
                            echo '-';
                        }
                    ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Infus Line</div>
                    <div class="value"><?php
                        if (!empty($catatan['infus'])) {
                            $infus = is_array($catatan['infus']) ? $catatan['infus'] : explode(',', $catatan['infus']);
                            echo htmlspecialchars(implode(', ', array_filter($infus)));
                        } else {
                            echo '-';
                        }
                    ?></div>
                </div>
            </div>
            
            <!-- Regional Anestesi (jika ada) -->
            <?php if (!empty($catatan['lokasi_regional'])): ?>
            <div class="subsection-header">Regional Anestesi</div>
            <div class="data-grid">
                <div class="data-grid-item">
                    <div class="label">Lokasi</div>
                    <div class="value"><?= displayValue($catatan['lokasi_regional']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Jarum / No</div>
                    <div class="value"><?= displayValue($catatan['jarum_regional']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Kateter</div>
                    <div class="value"><?= displayValue($catatan['kateter_regional']) ?></div>
                </div>
                <div class="data-grid-item">
                    <div class="label">Obat Anestesi Lokal</div>
                    <div class="value"><?= displayValue($catatan['obat_anestesi_lokal']) ?></div>
                </div>
            </div>
            
            <?php if (!empty($catatan['hasil_regional'])): ?>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 8px; font-size: 7.5pt; margin-top: 6px;">
                <strong>Hasil Regional:</strong> 
                <?php
                    $hasil = is_array($catatan['hasil_regional']) ? $catatan['hasil_regional'] : explode(',', $catatan['hasil_regional']);
                    echo htmlspecialchars(implode(', ', array_filter($hasil)));
                ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Obat-obatan -->
            <?php if (!empty($catatan['obat'])): ?>
            <div class="subsection-header">Obat-obatan</div>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 10px;">
                <ul style="margin: 0; padding-left: 20px; font-size: 7.5pt; columns: 2; column-gap: 15px;">
                    <?php
                        $obat = is_array($catatan['obat']) ? $catatan['obat'] : explode(',', $catatan['obat']);
                        $obat = array_filter($obat);
                        foreach ($obat as $item) {
                            echo '<li>' . htmlspecialchars($item) . '</li>';
                        }
                    ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column -->
        <div>
            <!-- Tim Medis -->
            <div class="section-header">▶ TIM MEDIS</div>
            <div class="list-compact">
                <div class="item">
                    <span class="label">Dokter Bedah:</span>
                    <span class="value"><?= displayValue($catatan['dokter_bedah']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Dokter Anestesi:</span>
                    <span class="value"><?= displayValue($catatan['dokter_anestesi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Perawat Anestesi:</span>
                    <span class="value"><?= displayValue($catatan['perawat_anestesi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Perawat Bedah:</span>
                    <span class="value"><?= displayValue($catatan['perawat_bedah']) ?></span>
                </div>
            </div>
            
            <!-- Waktu Operasi -->
            <div class="subsection-header">Timeline Operasi</div>
            <div class="list-compact">
                <div class="item">
                    <span class="label">Tanggal:</span>
                    <span class="value"><?= displayValue($catatan['tanggal_anestesi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Pukul:</span>
                    <span class="value"><?= displayValue($catatan['pukul']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Induksi Pukul:</span>
                    <span class="value"><?= displayValue($catatan['induksi_pukul']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Pasien Siap Insisi:</span>
                    <span class="value"><?= displayValue($catatan['pasien_siap_insisi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Insisi Mulai:</span>
                    <span class="value"><?= displayValue($catatan['insisi_mulai_pukul']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Operasi Mulai:</span>
                    <span class="value"><?= displayValue($catatan['operasi_mulai_pukul']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Mulai Anestesi:</span>
                    <span class="value"><?= displayValue($catatan['mulai_anestesi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Selesai Anestesi:</span>
                    <span class="value"><?= displayValue($catatan['selesai_anestesi']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Mulai Pembedahan:</span>
                    <span class="value"><?= displayValue($catatan['mulai_pembedahan']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Selesai Pembedahan:</span>
                    <span class="value"><?= displayValue($catatan['selesai_pembedahan']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Ekstubasi Pukul:</span>
                    <span class="value"><?= displayValue($catatan['ekstubasi_pukul']) ?></span>
                </div>
                <div class="item">
                    <span class="label">Pasien Keluar OK:</span>
                    <span class="value"><?= displayValue($catatan['pasien_keluar_ok']) ?></span>
                </div>
            </div>
            
            <?php if (!empty($catatan['keterangan_waktu'])): ?>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 8px; font-size: 7.5pt; margin-top: 6px;">
                <strong>Keterangan:</strong> <?= displayValue($catatan['keterangan_waktu']) ?>
            </div>
            <?php endif; ?>
            
            <!-- Cairan -->
            <div class="subsection-header">Cairan Infus & Output</div>
            <table class="data-table">
                <tr>
                    <td class="label">Cairan Infus</td>
                    <td><?php
                        if (!empty($catatan['cairan_infus'])) {
                            $infus = is_array($catatan['cairan_infus']) ? $catatan['cairan_infus'] : explode(',', $catatan['cairan_infus']);
                            $infus = array_filter($infus);
                            if (!empty($infus)) {
                                echo '<ul style="margin: 0; padding-left: 20px;">';
                                foreach ($infus as $item) {
                                    echo '<li>' . htmlspecialchars($item) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '-';
                            }
                        } else {
                            echo '-';
                        }
                    ?></td>
                </tr>
                <tr>
                    <td class="label">Cairan Output</td>
                    <td><?php
                        if (!empty($catatan['cairan_output'])) {
                            $output = is_array($catatan['cairan_output']) ? $catatan['cairan_output'] : explode(',', $catatan['cairan_output']);
                            $output = array_filter($output);
                            if (!empty($output)) {
                                echo '<ul style="margin: 0; padding-left: 20px;">';
                                foreach ($output as $item) {
                                    echo '<li>' . htmlspecialchars($item) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '-';
                            }
                        } else {
                            echo '-';
                        }
                    ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Masalah & Tindakan -->
    <?php if (!empty($catatan['masalah_selama_anestesi']) || !empty($catatan['tindakan'])): ?>
    <div class="section-header">▶ MASALAH & TINDAKAN</div>
    <div class="two-columns">
        <div>
            <div class="subsection-header">Masalah Selama Anestesi</div>
            <?php if (!empty($catatan['masalah_selama_anestesi'])): ?>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 10px;">
                <ul style="margin: 0; padding-left: 20px; font-size: 7.5pt;">
                    <?php
                        $masalah = is_array($catatan['masalah_selama_anestesi']) ? $catatan['masalah_selama_anestesi'] : explode(',', $catatan['masalah_selama_anestesi']);
                        $masalah = array_filter($masalah);
                        foreach ($masalah as $item) {
                            echo '<li>' . htmlspecialchars($item) . '</li>';
                        }
                    ?>
                </ul>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 10px; color: #999; font-size: 7.5pt;">Tidak ada masalah</div>
            <?php endif; ?>
        </div>
        <div>
            <div class="subsection-header">Tindakan</div>
            <?php if (!empty($catatan['tindakan'])): ?>
            <div style="background: white; border: 1px solid #e3f0ff; border-radius: 5px; padding: 10px;">
                <ul style="margin: 0; padding-left: 20px; font-size: 7.5pt;">
                    <?php
                        $tindakan = is_array($catatan['tindakan']) ? $catatan['tindakan'] : explode(',', $catatan['tindakan']);
                        $tindakan = array_filter($tindakan);
                        foreach ($tindakan as $item) {
                            echo '<li>' . htmlspecialchars($item) . '</li>';
                        }
                    ?>
                </ul>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 10px; color: #999; font-size: 7.5pt;">Tidak ada tindakan khusus</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Keterangan Tambahan -->
    <?php if (!empty($catatan['keterangan'])): ?>
    <div class="section-header">▶ KETERANGAN TAMBAHAN</div>
    <div style="background: white; border-left: 4px solid #fbbc04; border-radius: 5px; padding: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-size: 8pt; line-height: 1.5;">
        <?= nl2br(displayValue($catatan['keterangan'])) ?>
    </div>
    <?php endif; ?>
    
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
