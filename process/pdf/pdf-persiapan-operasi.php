<?php
/**
 * PDF Generator: Checklist Persiapan Operasi (RMO-1 a) - MODERN DESIGN
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

// Ambil data checklist persiapan operasi
$query_checklist = "SELECT * FROM tbl_anestesi_persiapan_operasi 
                    WHERE no_rawat = ? AND kode_paket = ?";
$stmt_checklist = $db->prepare($query_checklist);
$stmt_checklist->execute([$no_rawat, $kode_paket]);
$checklist = $stmt_checklist->fetch(PDO::FETCH_ASSOC);

// Jika tidak ada data, buat array kosong untuk menghindari error
if (!$checklist) {
    $checklist = [];
}

// Helper functions
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function isChecked($value) {
    return !empty($value) && $value == 1 ? '☑' : '☐';
}

function getYaTidak($value) {
    return !empty($value) && $value == 1 ? 'Ya' : 'Tidak';
}

$tanggal_cetak = date('d F Y, H:i');

// Data checklist items
$checklist_items = [
    'Program ke UBS',
    'Persetujuan Operasi Lengkap dan Terisi',
    'Rekam Medis',
    'Laporan Operasi',
    'Laporan Anestesi',
    'Hasil Laboratorium',
    'Hasil Radiologi',
    'Hasil CT Scan',
    'Hasil USG',
    'Hasil EKG',
    'Lain-lain'
];

$fisik_items = [
    'Puasa',
    'Lavement/garam Inggris',
    'Pasang DC',
    'Cukur dan bersihkan daerah operasi',
    'Rambut palsu, gigi palsu, contact lens, sudah dilepas',
    'Cat kuku dan make up muka sudah dibersihkan',
    'Perhiasan dan arloji dll, sudah dilepas',
    'Persiapan darah untuk transfusi',
    '1) Whole Blood (WB)',
    '2) PRC',
    '3) FFP',
    'Premedikasi',
    'Antibiotik pre-ops'
];

$khusus_items = [
    'DM - Insulin Pre Op',
    'Hipertensi - Obat anti hipertensi pre ops',
    'Asma - Obat anti asma / Corticosteroid pre ops',
    'Obat Lain',
    'Obat-obatan sebelum tidur',
    'Pasang Infus',
    'Tekanan Darah',
    'Nadi',
    'Suhu',
    'Pernapasan',
    'Obat yang dibawa ke UBS',
    'Hasil Skin Test',
    'Kunjungan dokter pra bedah - Dokter Bedah',
    'Kunjungan dokter pra bedah - Dokter Anestesi',
    'Kunjungan dokter pra bedah - Dokter Konsul terkait',
    'Kunjungan dokter pra bedah - Dokter Konsul terkait',
    'Kunjungan dokter pra bedah - Dokter Konsul terkait'
];

// Mapping database fields
$db_fields_ubs = [
    'program_ke_ubs', 'persetujuan_operasi', 'rekam_medis', 'laporan_operasi',
    'laporan_anestesi', 'hasil_lab', 'hasil_radiologi', 'hasil_ct_scan',
    'hasil_usg', 'hasil_ekg', 'hasil_lain',
    'puasa', 'lavement', 'pasang_dc', 'cukur_daerah_operasi',
    'rambut_makeup_dibersihkan', 'perhiasan_dilepas', 'transfusi_whole_blood',
    'transfusi_whole_blood', 'transfusi_prc', 'transfusi_ffp',
    'premedikasi', 'antibiotik_preops',
    'dm_insulin_preop', 'hipertensi_obat', 'asma_obat', 'obat_lain',
    'obat_tidur', 'pasang_infus', 'tekanan_darah', 'nadi',
    'suhu', 'pernafasan', 'obat_ubs', 'hasil_skin_test',
    'visit_dokter_bedah', 'visit_dokter_anestesi'
];

// Generate HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checklist Persiapan Operasi - <?= $booking['nama_pasien'] ?></title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 8.5pt; line-height: 1.3; color: #333; background: #f4f7fb; }
        
        /* Header Modern */
        .page-header { background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%); padding: 12px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2); margin-bottom: 12px; text-align: center; color: white; }
        .page-header .logo-section { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 6px; }
        .page-header .logo { width: 45px; height: 45px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #4285f4; font-weight: bold; }
        .page-header h1 { font-size: 14pt; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .page-header .hospital-name { font-size: 9pt; margin: 3px 0; opacity: 0.95; }
        .page-header .document-code { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px; font-size: 7pt; font-weight: 600; margin-top: 4px; }
        
        /* Booking Info Box */
        .booking-info-box { background: white; border: 2px solid #e3f0ff; border-radius: 6px; padding: 10px; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .booking-info-box h2 { background: #4285f4; color: white; padding: 5px 8px; border-radius: 4px; font-size: 9pt; margin: -10px -10px 8px -10px; }
        .booking-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .booking-info-item { display: flex; gap: 6px; font-size: 7.5pt; }
        .booking-info-item .label { font-weight: 600; color: #666; min-width: 100px; }
        .booking-info-item .value { color: #333; font-weight: 500; }
        
        /* Data Pasien Box */
        .data-pasien-box { background: white; border: 2px dashed #4285f4; border-radius: 6px; padding: 10px; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .data-pasien-box h2 { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 5px 8px; border-radius: 4px; font-size: 9pt; margin: -10px -10px 8px -10px; }
        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        
        /* Checklist Table */
        .checklist-table { width: 100%; border-collapse: collapse; margin: 8px 0; background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 7.5pt; }
        .checklist-table thead { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; }
        .checklist-table th { padding: 6px; text-align: center; font-weight: 600; font-size: 7.5pt; border: 1px solid #d0e4ff; }
        .checklist-table tbody tr { border-bottom: 1px solid #e3f0ff; }
        .checklist-table tbody tr:nth-child(even) { background: #fafbff; }
        .checklist-table tbody tr.section-title { background: #4285f4 !important; color: white; font-weight: bold; }
        .checklist-table tbody tr.section-title td { padding: 5px 8px; }
        .checklist-table td { padding: 5px; border: 1px solid #e3f0ff; font-size: 7.5pt; }
        .checklist-table td:first-child { text-align: left; padding-left: 8px; }
        .checklist-table td:not(:first-child) { text-align: center; }
        .check-icon { font-size: 10pt; color: #4285f4; }
        
        /* Section Header */
        .section-header { background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%); color: white; padding: 6px 10px; border-radius: 4px; font-weight: 600; font-size: 9pt; margin: 12px 0 8px 0; box-shadow: 0 2px 6px rgba(66, 133, 244, 0.15); }
        
        /* Footer */
        .page-footer { margin-top: 15px; background: white; border-top: 2px solid #4285f4; padding: 6px 12px; text-align: center; font-size: 6.5pt; color: #666; }
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
                <h1>CHECKLIST PERSIAPAN OPERASI</h1>
                <div class="hospital-name">Rumah Sakit Prasetya Bunda</div>
            </div>
        </div>
        <div class="document-code">RMO-1 a</div>
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
    
    <!-- Data Masuk dan Kondisi Pasien -->
    <div class="data-pasien-box">
        <h2>👤 Data Masuk dan Kondisi Pasien</h2>
        <div class="data-grid">
            <div class="booking-info-item">
                <div class="label">Tanggal Operasi:</div>
                <div class="value"><?= displayValue($checklist['tanggal_operasi']) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Riwayat Alergi:</div>
                <div class="value"><?= displayValue($checklist['riwayat_alergi']) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Macam Operasi:</div>
                <div class="value"><?= displayValue($checklist['macam_operasi']) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">DPJP:</div>
                <div class="value"><?= displayValue($checklist['dpjp']) ?></div>
            </div>
            <div class="booking-info-item">
                <div class="label">Berat Badan:</div>
                <div class="value"><?= displayValue($checklist['berat_badan']) ?> kg</div>
            </div>
            <div class="booking-info-item">
                <div class="label">Tinggi Badan:</div>
                <div class="value"><?= displayValue($checklist['tinggi_badan']) ?> cm</div>
            </div>
            <div class="booking-info-item">
                <div class="label">Gol. Darah:</div>
                <div class="value"><?= displayValue($checklist['gol_darah']) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Checklist Persiapan Operasi -->
    <div class="section-header">📝 CHECKLIST PERSIAPAN OPERASI</div>
    
    <table class="checklist-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item Persiapan</th>
                <th style="width: 15%;">R. RAWAT</th>
                <th style="width: 15%;">UBS</th>
                <th style="width: 20%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <!-- Administrasi -->
            <tr class="section-title">
                <td colspan="4">1. Persiapan Administrasi</td>
            </tr>
            
            <tr>
                <td>Program ke UBS</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['program_ke_ubs']) ?></span></td>
                <td><?= displayValue($checklist['ket_program_ke_ubs']) ?></td>
            </tr>
            <tr>
                <td>Persetujuan Operasi Lengkap dan Terisi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['persetujuan_operasi']) ?></span></td>
                <td><?= displayValue($checklist['ket_persetujuan_operasi']) ?></td>
            </tr>
            <tr>
                <td>Rekam Medis</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['rekam_medis']) ?></span></td>
                <td><?= displayValue($checklist['ket_rekam_medis']) ?></td>
            </tr>
            <tr>
                <td>Laporan Operasi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['laporan_operasi']) ?></span></td>
                <td><?= displayValue($checklist['ket_laporan_operasi']) ?></td>
            </tr>
            <tr>
                <td>Laporan Anestesi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['laporan_anestesi']) ?></span></td>
                <td><?= displayValue($checklist['ket_laporan_anestesi']) ?></td>
            </tr>
            <tr>
                <td>Hasil Laboratorium</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_lab']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_lab']) ?></td>
            </tr>
            <tr>
                <td>Hasil Radiologi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_radiologi']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_radiologi']) ?></td>
            </tr>
            <tr>
                <td>Hasil CT Scan</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_ct_scan']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_ct_scan']) ?></td>
            </tr>
            <tr>
                <td>Hasil USG</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_usg']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_usg']) ?></td>
            </tr>
            <tr>
                <td>Hasil EKG</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_ekg']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_ekg']) ?></td>
            </tr>
            <tr>
                <td>Lain-lain</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hasil_lain']) ?></span></td>
                <td><?= displayValue($checklist['ket_hasil_lain']) ?></td>
            </tr>
            
            <!-- Fisik -->
            <tr class="section-title">
                <td colspan="4">2. Persiapan Fisik</td>
            </tr>
            
            <tr>
                <td>Puasa</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['puasa']) ?></span></td>
                <td><?= displayValue($checklist['waktu_puasa']) ?></td>
            </tr>
            <tr>
                <td>Lavement/garam Inggris</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['lavement']) ?></span></td>
                <td><?= displayValue($checklist['ket_lavement']) ?></td>
            </tr>
            <tr>
                <td>Pasang DC</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['pasang_dc']) ?></span></td>
                <td>-</td>
            </tr>
            <tr>
                <td>Cukur dan bersihkan daerah operasi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['cukur_daerah_operasi']) ?></span></td>
                <td><?= displayValue($checklist['ket_cukur_daerah_operasi']) ?></td>
            </tr>
            <tr>
                <td>Rambut palsu, gigi palsu, contact lens, sudah dilepas</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['rambut_makeup_dibersihkan']) ?></span></td>
                <td><?= displayValue($checklist['ket_rambut_makeup_dibersihkan']) ?></td>
            </tr>
            <tr>
                <td>Cat kuku dan make up muka sudah dibersihkan</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['cat_kuku_dibersihkan']) ?></span></td>
                <td><?= displayValue($checklist['ket_cat_kuku_dibersihkan']) ?></td>
            </tr>
            <tr>
                <td>Perhiasan dan arloji dll, sudah dilepas</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['perhiasan_dilepas']) ?></span></td>
                <td><?= displayValue($checklist['ket_perhiasan_dilepas']) ?></td>
            </tr>
            <tr>
                <td>Persiapan darah untuk transfusi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['transfusi_darah']) ?></span></td>
                <td><?= displayValue($checklist['ket_transfusi_darah']) ?></td>
            </tr>
            <tr>
                <td>1) Whole Blood (WB)</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['transfusi_whole_blood']) ?></span></td>
                <td><?= displayValue($checklist['kantong_wb']) ?> kantong</td>
            </tr>
            <tr>
                <td>2) PRC</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['transfusi_prc']) ?></span></td>
                <td><?= displayValue($checklist['kantong_prc']) ?> kantong</td>
            </tr>
            <tr>
                <td>3) FFP</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['transfusi_ffp']) ?></span></td>
                <td><?= displayValue($checklist['kantong_ffp']) ?> kantong</td>
            </tr>
            <tr>
                <td>Premedikasi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['premedikasi']) ?></span></td>
                <td><?= displayValue($checklist['ket_premedikasi']) ?></td>
            </tr>
            <tr>
                <td>Antibiotik pre-ops</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['antibiotik']) ?></span></td>
                <td><?= displayValue($checklist['antibiotik_preops']) ?> - <?= displayValue($checklist['jam_antibiotik']) ?> WIB</td>
            </tr>
            
            <!-- Khusus -->
            <tr class="section-title">
                <td colspan="4">3. Persiapan Khusus</td>
            </tr>
            
            <tr>
                <td>DM - Insulin Pre Op</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['dm_insulin_preop']) ?></span></td>
                <td><?= displayValue($checklist['ket_dm_insulin_preop']) ?></td>
            </tr>
            <tr>
                <td>Hipertensi - Obat anti hipertensi pre ops</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['hipertensi_obat']) ?></span></td>
                <td><?= displayValue($checklist['ket_hipertensi_obat']) ?></td>
            </tr>
            <tr>
                <td>Asma - Obat anti asma / Corticosteroid pre ops</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['asma_obat']) ?></span></td>
                <td><?= displayValue($checklist['ket_asma_obat']) ?></td>
            </tr>
            <tr>
                <td>Obat Lain</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['obat_lain_radio']) ?></span></td>
                <td><?= displayValue($checklist['obat_lain']) ?></td>
            </tr>
            <tr>
                <td>Obat-obatan sebelum tidur</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['obat_tidur']) ?></span></td>
                <td><?= displayValue($checklist['ket_obat_tidur']) ?></td>
            </tr>
            <tr>
                <td>Pasang Infus</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['pasang_infus']) ?></span></td>
                <td><?= displayValue($checklist['iv_catch_no']) ?></td>
            </tr>
            <tr>
                <td>Tekanan Darah</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['tekanan_darah_radio']) ?></span></td>
                <td><?= displayValue($checklist['tekanan_darah']) ?></td>
            </tr>
            <tr>
                <td>Nadi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['nadi_radio']) ?></span></td>
                <td><?= displayValue($checklist['nadi']) ?></td>
            </tr>
            <tr>
                <td>Suhu</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['suhu_radio']) ?></span></td>
                <td><?= displayValue($checklist['suhu']) ?> °C</td>
            </tr>
            <tr>
                <td>Pernapasan</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['pernafasan_radio']) ?></span></td>
                <td><?= displayValue($checklist['pernafasan']) ?></td>
            </tr>
            <tr>
                <td>Obat yang dibawa ke UBS</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['obat_ubs']) ?></span></td>
                <td><?= displayValue($checklist['ket_obat_ubs']) ?></td>
            </tr>
            <tr>
                <td>Hasil Skin Test</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['skin_test_radio']) ?></span></td>
                <td><?= displayValue($checklist['hasil_skin_test']) ?></td>
            </tr>
            <tr>
                <td>Kunjungan dokter pra bedah - Dokter Bedah</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['visit_dokter_bedah']) ?></span></td>
                <td><?= displayValue($checklist['ket_visit_dokter_bedah']) ?></td>
            </tr>
            <tr>
                <td>Kunjungan dokter pra bedah - Dokter Anestesi</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['visit_dokter_anestesi']) ?></span></td>
                <td><?= displayValue($checklist['ket_visit_dokter_anestesi']) ?></td>
            </tr>
            <tr>
                <td>Kunjungan dokter pra bedah - Dokter Konsul terkait 1</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['visit_dokter_konsul_1']) ?></span></td>
                <td><?= displayValue($checklist['ket_visit_dokter_konsul_1']) ?></td>
            </tr>
            <tr>
                <td>Kunjungan dokter pra bedah - Dokter Konsul terkait 2</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['visit_dokter_konsul_2']) ?></span></td>
                <td><?= displayValue($checklist['ket_visit_dokter_konsul_2']) ?></td>
            </tr>
            <tr>
                <td>Kunjungan dokter pra bedah - Dokter Konsul terkait 3</td>
                <td>-</td>
                <td><span class="check-icon"><?= isChecked($checklist['visit_dokter_konsul_3']) ?></span></td>
                <td><?= displayValue($checklist['ket_visit_dokter_konsul_3']) ?></td>
            </tr>
        </tbody>
    </table>
    
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
