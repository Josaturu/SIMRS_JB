<?php
/**
 * MODERN PDF TEMPLATE - Mirip dengan Form Web
 * Design System: Konsisten dengan assets/css/style.css
 * 
 * Primary Color: #4285f4 (Google Blue)
 * Background: #f4f7fb
 * Card: White dengan shadow
 * Typography: Segoe UI, Arial, sans-serif
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Medis - RS Josaturu</title>
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
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2);
            margin-bottom: 20px;
            text-align: center;
            color: white;
        }
        
        .page-header .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .page-header .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #4285f4;
            font-weight: bold;
        }
        
        .page-header h1 {
            font-size: 18pt;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-header .hospital-name {
            font-size: 12pt;
            margin: 5px 0;
            opacity: 0.95;
        }
        
        .page-header .document-code {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: 600;
            margin-top: 8px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        /* ===== PATIENT INFO BOX ===== */
        .patient-info-box {
            background: white;
            border: 2px dashed #4285f4;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .patient-info-box .title {
            background: #4285f4;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 10pt;
            margin: -15px -15px 12px -15px;
            display: inline-block;
        }
        
        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .patient-info-item {
            display: flex;
            gap: 10px;
        }
        
        .patient-info-item .label {
            font-weight: 600;
            color: #666;
            min-width: 120px;
        }
        
        .patient-info-item .value {
            color: #333;
            font-weight: 500;
        }
        
        /* ===== SECTION HEADERS ===== */
        .section-header {
            background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11pt;
            margin: 20px 0 15px 0;
            box-shadow: 0 2px 6px rgba(66, 133, 244, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header::before {
            content: '▶';
            font-size: 10pt;
        }
        
        .subsection-header {
            background: #e3f0ff;
            color: #4285f4;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 10pt;
            margin: 15px 0 10px 0;
            border-left: 4px solid #4285f4;
        }
        
        /* ===== MODERN TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .data-table thead {
            background: linear-gradient(90deg, #4285f4 0%, #5a9fff 100%);
            color: white;
        }
        
        .data-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 9pt;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        
        .data-table th:last-child {
            border-right: none;
        }
        
        .data-table tbody tr {
            border-bottom: 1px solid #e3f0ff;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #fafbff;
        }
        
        .data-table tbody tr:hover {
            background: #e3f0ff;
        }
        
        .data-table td {
            padding: 10px;
            font-size: 9pt;
            color: #333;
        }
        
        .data-table td.label {
            font-weight: 600;
            background: #f4f7fb;
            color: #666;
            width: 200px;
        }
        
        /* ===== MODERN GRID ===== */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 10px 0;
        }
        
        .data-grid.three-cols {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .data-grid-item {
            background: white;
            border: 1px solid #e3f0ff;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        
        .data-grid-item:hover {
            border-color: #4285f4;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.1);
        }
        
        .data-grid-item .label {
            font-size: 8pt;
            color: #4285f4;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-grid-item .value {
            font-size: 10pt;
            color: #333;
            font-weight: 500;
        }
        
        /* ===== INFO BOX ===== */
        .info-box {
            background: white;
            border-left: 4px solid #4285f4;
            border-radius: 6px;
            padding: 12px;
            margin: 10px 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        
        .info-box.success {
            border-left-color: #34a853;
            background: #f0fdf4;
        }
        
        .info-box.warning {
            border-left-color: #fbbc04;
            background: #fffbeb;
        }
        
        .info-box.danger {
            border-left-color: #ea4335;
            background: #fef2f2;
        }
        
        .info-box .title {
            font-weight: 600;
            color: #4285f4;
            margin-bottom: 5px;
        }
        
        .info-box .content {
            color: #333;
            line-height: 1.5;
        }
        
        /* ===== CHECKBOX & RADIO ===== */
        .checkbox-list, .radio-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin: 10px 0;
        }
        
        .checkbox-item, .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            border-radius: 4px;
            font-size: 9pt;
        }
        
        .checkbox-item.checked {
            background: #e3f0ff;
            font-weight: 500;
        }
        
        .checkbox-icon {
            width: 16px;
            height: 16px;
            border: 2px solid #4285f4;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #4285f4;
        }
        
        .checkbox-icon.checked {
            background: #4285f4;
            color: white;
        }
        
        /* ===== SIGNATURE AREA ===== */
        .signature-section {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .signature-box {
            text-align: center;
            padding: 15px;
            border: 2px solid #e3f0ff;
            border-radius: 8px;
            background: white;
        }
        
        .signature-box .title {
            font-weight: 600;
            color: #4285f4;
            margin-bottom: 50px;
            font-size: 10pt;
        }
        
        .signature-box .name {
            border-top: 2px solid #333;
            padding-top: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .signature-box .role {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }
        
        /* ===== FOOTER ===== */
        .page-footer {
            margin-top: 30px;
            background: white;
            border-top: 2px solid #4285f4;
            padding: 8px 15px;
            text-align: center;
            font-size: 7.5pt;
            color: #666;
        }
        .page-footer .print-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge.primary {
            background: #e3f0ff;
            color: #4285f4;
        }
        
        .badge.success {
            background: #d4edda;
            color: #34a853;
        }
        
        .badge.warning {
            background: #fff3cd;
            color: #fbbc04;
        }
        
        .badge.danger {
            background: #f8d7da;
            color: #ea4335;
        }
        
        /* ===== PRINT CONTROLS ===== */
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: white;
            }
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #4285f4 0%, #3367d6 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 11pt;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
            transition: all 0.2s;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(66, 133, 244, 0.4);
        }
        
        .btn-close {
            background: #f1f3f4;
            color: #666;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 11pt;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-close:hover {
            background: #e1e3e4;
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak PDF
        </button>
        <button class="btn-close" onclick="window.close()">
            ✖ Tutup
        </button>
    </div>
    
    <!-- Header -->
    <div class="page-header">
        <div class="logo-section">
            <div class="logo">RS</div>
            <div>
                <h1>LAPORAN MEDIS</h1>
                <div class="hospital-name">Rumah Sakit Josaturu</div>
            </div>
        </div>
        <div class="document-code">RMOK 3A - KONSULTASI ANESTESI</div>
    </div>
    
    <!-- Patient Info -->
    <div class="patient-info-box">
        <div class="title">📋 Informasi Pasien</div>
        <div class="patient-info-grid">
            <div class="patient-info-item">
                <div class="label">No. Rawat:</div>
                <div class="value">2024/001/001</div>
            </div>
            <div class="patient-info-item">
                <div class="label">Nama Pasien:</div>
                <div class="value">Dadang Beton</div>
            </div>
            <div class="patient-info-item">
                <div class="label">No. RM:</div>
                <div class="value">RM-12345</div>
            </div>
            <div class="patient-info-item">
                <div class="label">Tanggal Lahir:</div>
                <div class="value">26-10-1995</div>
            </div>
        </div>
    </div>
    
    <!-- Section Example -->
    <div class="section-header">DATA PASIEN</div>
    
    <!-- Grid Example -->
    <div class="data-grid">
        <div class="data-grid-item">
            <div class="label">Tinggi Badan</div>
            <div class="value">170 cm</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Berat Badan</div>
            <div class="value">65 kg</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Tekanan Darah</div>
            <div class="value">120/80 mmHg</div>
        </div>
        <div class="data-grid-item">
            <div class="label">Nadi</div>
            <div class="value">80 x/menit</div>
        </div>
    </div>
    
    <!-- Table Example -->
    <div class="subsection-header">Pemeriksaan Laboratorium</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Hasil</th>
                <th>Satuan</th>
                <th>Nilai Normal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Hemoglobin</td>
                <td>14.5</td>
                <td>g/dL</td>
                <td>12-16</td>
            </tr>
            <tr>
                <td>Leukosit</td>
                <td>8,500</td>
                <td>/μL</td>
                <td>4,000-11,000</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Info Box Example -->
    <div class="info-box success">
        <div class="title">✓ Status Persetujuan</div>
        <div class="content">
            Pasien telah menyetujui tindakan anestesi yang akan dilakukan.
            Informed consent ditandatangani pada tanggal 13 Oktober 2025.
        </div>
    </div>
    
    <!-- Checkbox Example -->
    <div class="subsection-header">Riwayat Penyakit</div>
    <div class="checkbox-list">
        <div class="checkbox-item checked">
            <div class="checkbox-icon checked">✓</div>
            <span>Hipertensi</span>
        </div>
        <div class="checkbox-item">
            <div class="checkbox-icon"></div>
            <span>Diabetes</span>
        </div>
        <div class="checkbox-item checked">
            <div class="checkbox-icon checked">✓</div>
            <span>Asma</span>
        </div>
        <div class="checkbox-item">
            <div class="checkbox-icon"></div>
            <span>Penyakit Jantung</span>
        </div>
    </div>
    
    <!-- Signature Example -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="title">Pasien / Keluarga</div>
            <div class="name">Dadang Beton</div>
            <div class="role">Pasien</div>
        </div>
        <div class="signature-box">
            <div class="title">Dokter Anestesi</div>
            <div class="name">dr. Ahmad Yani, Sp.An</div>
            <div class="role">Dokter Anestesi</div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="page-footer">
        <div class="print-info">
            <span>Dicetak: <?= date('d F Y, H:i') ?> WIB</span>
            <span>Halaman 1 dari 1</span>
            <span>RS Prasetya Bunda</span>
        </div>
    </div>
</body>
</html>
