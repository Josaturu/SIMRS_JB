<?php
// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

if (empty($no_rawat) || empty($kode_paket) || empty($tanggal) || empty($jam_mulai)) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data pasien untuk header
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.jenis_kelamin, p.tanggal_lahir,
                  TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE bo.no_rawat = :no_rawat AND bo.kode_paket = :kode_paket 
                  AND bo.tanggal = :tanggal AND bo.jam_mulai = :jam_mulai";
$stmt_pasien = $db->prepare($query_pasien);
$stmt_pasien->bindParam(':no_rawat', $no_rawat);
$stmt_pasien->bindParam(':kode_paket', $kode_paket);
$stmt_pasien->bindParam(':tanggal', $tanggal);
$stmt_pasien->bindParam(':jam_mulai', $jam_mulai);
$stmt_pasien->execute();
$pasien = $stmt_pasien->fetch(PDO::FETCH_ASSOC);

if (!$pasien) {
    echo "<div style='padding:20px;color:red;'>❌ Data pasien tidak ditemukan.</div>";
    exit;
}

// Query data vital sign untuk grafik dan tabel
$query_vital = "SELECT waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2 
                FROM tbl_anestesi_vital_sign 
                WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket AND tanggal = :tanggal AND jam_mulai = :jam_mulai
                ORDER BY waktu";
$stmt = $db->prepare($query_vital);
$stmt->bindParam(':no_rawat', $no_rawat);
$stmt->bindParam(':kode_paket', $kode_paket);
$stmt->bindParam(':tanggal', $tanggal);
$stmt->bindParam(':jam_mulai', $jam_mulai);
$stmt->execute();
$vital_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Konversi data ke format JSON untuk JavaScript
$vital_data_json = json_encode($vital_data);

include __DIR__ . '/../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <!-- Title -->
    <div class="title">
        <div style="color: #004d80;">VITAL SIGN</div>
        <div>RMOK-VS</div>
    </div>

    <!-- Vital Sign Section - Modern Layout -->
    <div class="card">
        <h2>Monitoring Vital Sign</h2>
        
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; margin-top: 20px;">
            <!-- LEFT: Form Input -->
            <div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 20px;">
                        <i class="fas fa-heartbeat"></i> Form Input
                    </h3>
                    
                    <!-- Jam (Auto Real-time) -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">
                            Waktu Pemeriksaan
                        </label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="vs_jam" readonly 
                                   style="flex: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 16px; font-weight: 600; color: #007bff; background: #e7f3ff;">
                            <button type="button" id="btn_set_time" 
                                    style="padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; white-space: nowrap;">
                                <i class="fas fa-clock"></i> Update
                            </button>
                        </div>
                        <small style="color: #6c757d; font-size: 11px; margin-top: 3px; display: block;">
                            ⏰ Waktu akan otomatis di-update saat Anda klik tombol "Update"
                        </small>
                    </div>
                    
                    <!-- Respirasi -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Respirasi (R) - x/menit</label>
                        <input type="number" id="vs_respirasi" min="0" max="60" placeholder="Contoh: 18" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Nadi -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Nadi (N) - BPM</label>
                        <input type="number" id="vs_nadi" min="0" max="200" placeholder="Contoh: 86" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Tekanan Darah -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">Tekanan Darah (mmHg)</label>
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center;">
                            <input type="number" id="vs_sistol" min="0" max="300" placeholder="Sistol (120)" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                            <span style="font-weight: 700; font-size: 18px; color: #6c757d;">/</span>
                            <input type="number" id="vs_diastol" min="0" max="200" placeholder="Diastol (80)" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                        </div>
                    </div>
                    
                    <!-- FIO2 -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">FIO2 (%)</label>
                        <input type="number" id="vs_fio2" min="0" max="100" placeholder="Contoh: 98" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- SPO2 -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #495057;">SPO2 (%)</label>
                        <input type="number" id="vs_spo2" min="0" max="100" placeholder="Contoh: 98" 
                               style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px;">
                    </div>
                    
                    <!-- Button Tambah -->
                    <button type="button" id="btn_add_vital" 
                            style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus-circle"></i> Tambah Record
                    </button>
                </div>
            </div>
            
            <!-- RIGHT: Chart + History Table -->
            <div>
                <!-- Toggle Switch untuk Pilih Sumber Data -->
                <div style="background: white; padding: 15px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #495057; font-size: 15px;">
                                <i class="fas fa-eye"></i> Tampilkan Data:
                            </strong>
                            <div id="view_mode_label" style="color: #6c757d; font-size: 12px; margin-top: 3px;">
                                Menampilkan semua data
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <!-- Toggle Buttons -->
                            <button id="btn_view_all" class="view-toggle-btn active" onclick="setViewMode('all')" 
                                    style="padding: 8px 15px; border: 2px solid #2196f3; background: #2196f3; color: white; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                <i class="fas fa-th-large"></i> Semua
                            </button>
                            <button id="btn_view_db" class="view-toggle-btn" onclick="setViewMode('database')" 
                                    style="padding: 8px 15px; border: 2px solid #4caf50; background: white; color: #4caf50; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                <i class="fas fa-database"></i> Database
                            </button>
                            <button id="btn_view_draft" class="view-toggle-btn" onclick="setViewMode('draft')" 
                                    style="padding: 8px 15px; border: 2px solid #ff9800; background: white; color: #ff9800; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                <i class="fas fa-edit"></i> Draft
                            </button>
                            <button id="btn_view_new" class="view-toggle-btn" onclick="setViewMode('new')" 
                                    style="padding: 8px 15px; border: 2px solid #9e9e9e; background: white; color: #9e9e9e; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s;">
                                <i class="fas fa-plus-circle"></i> Input Baru
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Grafik -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-chart-line"></i> Grafik Vital Sign
                    </h3>
                    <canvas id="vitalChart" style="max-height: 300px;"></canvas>
                </div>
                
                <!-- Table Data dari Database (Permanent) -->
                <div id="db_data_section" style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px; display: none;">
                    <h3 style="margin-top: 0; color: #2e7d32; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-database"></i> Data Tersimpan (Database)
                        <span id="db_count" style="background: #4caf50; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">0</span>
                    </h3>
                    <div style="overflow-x: auto; max-height: 250px; overflow-y: auto;">
                        <table id="db_vital_table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; z-index: 1;">
                                <tr style="background: #e8f5e9;">
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: left;">Waktu</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">Resp</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">Nadi</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">TD</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">FIO2</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">SPO2</th>
                                </tr>
                            </thead>
                            <tbody id="db_vital_tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Table Data Draft (localStorage/Autosave) -->
                <div id="draft_data_section" style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px; display: none;">
                    <h3 style="margin-top: 0; color: #ff9800; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-edit"></i> Data Draft (Belum Disimpan)
                        <span id="draft_count" style="background: #ff9800; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">0</span>
                    </h3>
                    <div style="padding: 10px; background: #fff3e0; border-left: 4px solid #ff9800; margin-bottom: 10px; border-radius: 4px;">
                        <small style="color: #e65100;">
                            <i class="fas fa-exclamation-triangle"></i> Data ini masih dalam bentuk draft (autosave). Klik <strong>"Simpan Semua Data"</strong> untuk menyimpan ke database.
                        </small>
                    </div>
                    <div style="overflow-x: auto; max-height: 250px; overflow-y: auto;">
                        <table id="draft_vital_table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; z-index: 1;">
                                <tr style="background: #fff3e0;">
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: left;">Waktu</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">Resp</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">Nadi</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">TD</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">FIO2</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">SPO2</th>
                                    <th style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="draft_vital_tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Table Combined View (untuk input baru) -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-table"></i> Input Baru
                    </h3>
                    <div style="overflow-x: auto; max-height: 350px; overflow-y: auto;">
                        <table id="vital_table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; z-index: 1;">
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Waktu</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Resp</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Nadi</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">TD</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">FIO2</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">SPO2</th>
                                    <th style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="vital_tbody">
                                <tr>
                                    <td colspan="7" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                                        <i class="fas fa-info-circle"></i> Belum ada data baru. Silakan tambah record baru.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden inputs untuk submit (JSON array) -->
        <input type="hidden" name="vital_signs_data" id="vital_signs_data" value="[]">
        
        <!-- Info Status Data -->
        <?php if (count($vital_data) > 0): ?>
        <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 5px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-database" style="font-size: 24px; color: #4caf50;"></i>
                <div>
                    <strong style="color: #2e7d32; font-size: 15px;">Data Tersimpan di Database</strong>
                    <div style="color: #558b2f; font-size: 13px; margin-top: 3px;">
                        <?= count($vital_data) ?> record vital sign sudah tersimpan. Anda bisa menambah record baru atau mengedit yang ada.
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Button Simpan & Kembali -->
        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div id="save_status" style="color: #6c757d; font-size: 14px;">
                <i class="fas fa-info-circle"></i> <span id="record_count">0 record</span> siap disimpan
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-secondary" 
                        onclick="window.location.href='index.php?page=catatan-sedasi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>'">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button type="button" id="btn_export_pdf" class="btn btn-info" 
                        style="min-width: 150px; font-weight: 600;"
                        onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button type="button" id="btn_save_all" class="btn btn-success" 
                        style="min-width: 150px; font-weight: 600;">
                    <i class="fas fa-save"></i> Simpan Semua Data
                </button>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
    // ==================== VITAL SIGN MANAGEMENT ====================
    let vitalSignsArray = []; // Data input baru (belum disimpan)
    let dbVitalSigns = []; // Data dari database (permanent)
    let draftVitalSigns = []; // Data dari localStorage (draft/autosave)
    let vitalChart = null;
    let currentViewMode = 'all'; // all, database, draft, new
    
    // Set current time
    function setCurrentTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = `${hours}:${minutes}:${seconds}`;
        document.getElementById('vs_jam').value = timeString;
    }
    
    // Initialize Chart.js
    function initChart() {
        const ctx = document.getElementById("vitalChart").getContext("2d");
        vitalChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [],
                datasets: [
                    { 
                        label: "Respirasi (R)", 
                        data: [], 
                        borderColor: "#3498db", 
                        backgroundColor: "rgba(52, 152, 219, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Nadi (N)", 
                        data: [], 
                        borderColor: "#ff9800", 
                        backgroundColor: "rgba(255, 152, 0, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "Tekanan Darah (Sistol)", 
                        data: [], 
                        borderColor: "#e74c3c", 
                        backgroundColor: "rgba(231, 76, 60, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "FIO2", 
                        data: [], 
                        borderColor: "#9c27b0", 
                        backgroundColor: "rgba(156, 39, 176, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    { 
                        label: "SPO2", 
                        data: [], 
                        borderColor: "#2ecc71", 
                        backgroundColor: "rgba(46, 204, 113, 0.1)",
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nilai'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Waktu'
                        }
                    }
                }
            }
        });
    }
    
    // Button hover effect
    document.getElementById('btn_add_vital').addEventListener('mouseenter', function() {
        this.style.background = '#0056b3';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 8px rgba(0,123,255,0.3)';
    });
    
    document.getElementById('btn_add_vital').addEventListener('mouseleave', function() {
        this.style.background = '#007bff';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });
    
    // Add vital sign record
    document.getElementById('btn_add_vital').addEventListener('click', function() {
        const jam = document.getElementById('vs_jam').value;
        const respirasi = document.getElementById('vs_respirasi').value;
        const nadi = document.getElementById('vs_nadi').value;
        const sistol = document.getElementById('vs_sistol').value;
        const diastol = document.getElementById('vs_diastol').value;
        const fio2 = document.getElementById('vs_fio2').value;
        const spo2 = document.getElementById('vs_spo2').value;
        
        // Validation
        if (!jam) {
            alert('⚠️ Jam harus diisi!');
            document.getElementById('vs_jam').focus();
            return;
        }
        
        if (!respirasi || !nadi || !sistol || !diastol || !fio2 || !spo2) {
            alert('⚠️ Semua field harus diisi!');
            return;
        }
        
        // Create record object
        const record = {
            id: Date.now(),
            jam: jam,
            respirasi: parseInt(respirasi),
            nadi: parseInt(nadi),
            sistol: parseInt(sistol),
            diastol: parseInt(diastol),
            fio2: parseInt(fio2),
            spo2: parseInt(spo2)
        };
        
        // Add to array
        vitalSignsArray.push(record);
        
        // Update UI
        updateTable();
        updateChart();
        updateHiddenInput();
        updateViewVisibility();
        
        // Clear form
        setCurrentTime(); // Update ke waktu sekarang
        document.getElementById('vs_respirasi').value = '';
        document.getElementById('vs_nadi').value = '';
        document.getElementById('vs_sistol').value = '';
        document.getElementById('vs_diastol').value = '';
        document.getElementById('vs_fio2').value = '';
        document.getElementById('vs_spo2').value = '';
        
        // Show success message
        showToast('✅ Data vital sign berhasil ditambahkan!');
    });
    
    // Update table untuk data database (read-only)
    function updateDatabaseTable() {
        const section = document.getElementById('db_data_section');
        const tbody = document.getElementById('db_vital_tbody');
        const countBadge = document.getElementById('db_count');
        
        countBadge.textContent = dbVitalSigns.length;
        tbody.innerHTML = '';
        
        if (dbVitalSigns.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #c8e6c9;">
                        <i class="fas fa-info-circle"></i> Tidak ada data di database
                    </td>
                </tr>
            `;
        }
        
        dbVitalSigns.forEach((record, index) => {
            const row = document.createElement('tr');
            row.style.background = index % 2 === 0 ? '#ffffff' : '#f1f8e9';
            row.innerHTML = `
                <td style="padding: 10px; border: 1px solid #c8e6c9; font-family: monospace; font-size: 13px; font-weight: 600; color: #2e7d32;">
                    <i class="fas fa-check-circle" style="color: #4caf50; margin-right: 5px;"></i>${record.jam}
                </td>
                <td style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">
                    <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                        ${record.respirasi}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">
                    <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                        ${record.nadi} bpm
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">
                    <span style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                        ${record.sistol}/${record.diastol}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">
                    <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #7b1fa2; font-weight: 600; font-size: 12px;">
                        ${record.fio2}%
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #c8e6c9; text-align: center;">
                    <span style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                        ${record.spo2}%
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Update table untuk data draft (dari localStorage)
    function updateDraftTable() {
        const section = document.getElementById('draft_data_section');
        const tbody = document.getElementById('draft_vital_tbody');
        const countBadge = document.getElementById('draft_count');
        
        countBadge.textContent = draftVitalSigns.length;
        tbody.innerHTML = '';
        
        if (draftVitalSigns.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #ffe0b2;">
                        <i class="fas fa-info-circle"></i> Tidak ada data draft
                    </td>
                </tr>
            `;
        }
        
        draftVitalSigns.forEach((record, index) => {
            const row = document.createElement('tr');
            row.style.background = index % 2 === 0 ? '#ffffff' : '#fff8e1';
            row.innerHTML = `
                <td style="padding: 10px; border: 1px solid #ffe0b2; font-family: monospace; font-size: 13px; font-weight: 600; color: #e65100;">
                    <i class="fas fa-clock" style="color: #ff9800; margin-right: 5px;"></i>${record.jam}
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                        ${record.respirasi}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                        ${record.nadi} bpm
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <span style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                        ${record.sistol}/${record.diastol}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #7b1fa2; font-weight: 600; font-size: 12px;">
                        ${record.fio2}%
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <span style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                        ${record.spo2}%
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #ffe0b2; text-align: center;">
                    <button onclick="moveDraftToInput(${index})" style="padding: 5px 10px; background: #2196f3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                        <i class="fas fa-arrow-down"></i> Pindah
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Update table untuk input baru
    function updateTable() {
        const tbody = document.getElementById('vital_tbody');
        
        if (vitalSignsArray.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                        <i class="fas fa-info-circle"></i> Belum ada data baru. Silakan tambah record baru.
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = '';
        vitalSignsArray.forEach((record, index) => {
            const row = document.createElement('tr');
            row.style.background = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
            row.innerHTML = `
                <td style="padding: 10px; border: 1px solid #dee2e6; font-family: monospace; font-size: 13px; font-weight: 600; color: #495057;">
                    <i class="fas fa-clock" style="color: #007bff; margin-right: 5px;"></i>${record.jam}
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #e3f2fd; padding: 4px 10px; border-radius: 15px; color: #1976d2; font-weight: 600; font-size: 12px;">
                        ${record.respirasi}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #fff3e0; padding: 4px 10px; border-radius: 15px; color: #e65100; font-weight: 600; font-size: 12px;">
                        ${record.nadi} bpm
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #ffebee; padding: 4px 10px; border-radius: 15px; color: #c62828; font-weight: 600; font-size: 12px;">
                        ${record.sistol}/${record.diastol}
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #f3e5f5; padding: 4px 10px; border-radius: 15px; color: #7b1fa2; font-weight: 600; font-size: 12px;">
                        ${record.fio2}%
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <span style="background: #e8f5e9; padding: 4px 10px; border-radius: 15px; color: #2e7d32; font-weight: 600; font-size: 12px;">
                        ${record.spo2}%
                    </span>
                </td>
                <td style="padding: 10px; border: 1px solid #dee2e6; text-align: center;">
                    <button onclick="removeRecord(${index})" style="padding: 5px 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Fungsi untuk memindahkan draft ke input baru
    function moveDraftToInput(index) {
        const record = draftVitalSigns[index];
        vitalSignsArray.push(record);
        draftVitalSigns.splice(index, 1);
        
        updateTable();
        updateDraftTable();
        updateChart();
        updateHiddenInput();
        updateViewVisibility();
        
        showToast('✅ Data draft dipindahkan ke input baru');
    }
    
    // Fungsi untuk menghapus record dari input baru
    function removeRecord(index) {
        if (confirm('Hapus record ini?')) {
            vitalSignsArray.splice(index, 1);
            updateTable();
            updateChart();
            updateHiddenInput();
            updateViewVisibility();
            showToast('🗑️ Record dihapus');
        }
    }
    
    // Fungsi untuk set view mode
    function setViewMode(mode) {
        currentViewMode = mode;
        
        // Update button states
        const buttons = {
            'all': document.getElementById('btn_view_all'),
            'database': document.getElementById('btn_view_db'),
            'draft': document.getElementById('btn_view_draft'),
            'new': document.getElementById('btn_view_new')
        };
        
        // Reset all buttons
        Object.values(buttons).forEach(btn => {
            const color = btn.id === 'btn_view_all' ? '#2196f3' :
                         btn.id === 'btn_view_db' ? '#4caf50' :
                         btn.id === 'btn_view_draft' ? '#ff9800' : '#9e9e9e';
            btn.style.background = 'white';
            btn.style.color = color;
        });
        
        // Set active button
        const activeBtn = buttons[mode];
        const activeColor = mode === 'all' ? '#2196f3' :
                           mode === 'database' ? '#4caf50' :
                           mode === 'draft' ? '#ff9800' : '#9e9e9e';
        activeBtn.style.background = activeColor;
        activeBtn.style.color = 'white';
        
        // Update label
        const labels = {
            'all': 'Menampilkan semua data',
            'database': 'Menampilkan data dari database (permanent)',
            'draft': 'Menampilkan data draft (autosave)',
            'new': 'Menampilkan data input baru'
        };
        document.getElementById('view_mode_label').textContent = labels[mode];
        
        // Update visibility
        updateViewVisibility();
        
        // Update chart berdasarkan mode
        updateChartByMode();
        
        // Toast notification
        const messages = {
            'all': '👁️ Menampilkan semua data',
            'database': '💾 Menampilkan data database',
            'draft': '📝 Menampilkan data draft',
            'new': '➕ Menampilkan input baru'
        };
        showToast(messages[mode]);
    }
    
    // Update visibility berdasarkan view mode
    function updateViewVisibility() {
        const dbSection = document.getElementById('db_data_section');
        const draftSection = document.getElementById('draft_data_section');
        const newSection = document.querySelector('#vital_table').closest('div').parentElement;
        
        if (currentViewMode === 'all') {
            // Show all sections that have data
            dbSection.style.display = dbVitalSigns.length > 0 ? 'block' : 'none';
            draftSection.style.display = draftVitalSigns.length > 0 ? 'block' : 'none';
            newSection.style.display = 'block';
        } else if (currentViewMode === 'database') {
            dbSection.style.display = 'block';
            draftSection.style.display = 'none';
            newSection.style.display = 'none';
        } else if (currentViewMode === 'draft') {
            dbSection.style.display = 'none';
            draftSection.style.display = 'block';
            newSection.style.display = 'none';
        } else if (currentViewMode === 'new') {
            dbSection.style.display = 'none';
            draftSection.style.display = 'none';
            newSection.style.display = 'block';
        }
    }
    
    // Update chart berdasarkan mode
    function updateChartByMode() {
        if (!vitalChart) return;
        
        let dataToShow = [];
        
        if (currentViewMode === 'all') {
            // Gabungkan semua data
            dataToShow = [...dbVitalSigns, ...draftVitalSigns, ...vitalSignsArray];
        } else if (currentViewMode === 'database') {
            dataToShow = dbVitalSigns;
        } else if (currentViewMode === 'draft') {
            dataToShow = draftVitalSigns;
        } else if (currentViewMode === 'new') {
            dataToShow = vitalSignsArray;
        }
        
        const labels = dataToShow.map(r => r.jam);
        const respirasi = dataToShow.map(r => r.respirasi);
        const nadi = dataToShow.map(r => r.nadi);
        const sistol = dataToShow.map(r => r.sistol);
        const fio2 = dataToShow.map(r => r.fio2);
        const spo2 = dataToShow.map(r => r.spo2);
        
        vitalChart.data.labels = labels;
        vitalChart.data.datasets[0].data = respirasi;
        vitalChart.data.datasets[1].data = nadi;
        vitalChart.data.datasets[2].data = sistol;
        vitalChart.data.datasets[3].data = fio2;
        vitalChart.data.datasets[4].data = spo2;
        
        vitalChart.update();
    }
    
    // Update chart (gunakan updateChartByMode)
    function updateChart() {
        updateChartByMode();
    }
    
    // Update hidden input
    function updateHiddenInput() {
        // Gabungkan draft dan input baru untuk save
        const allData = [...draftVitalSigns, ...vitalSignsArray];
        document.getElementById('vital_signs_data').value = JSON.stringify(allData);
        
        // Update record count (total yang akan disimpan)
        const totalCount = allData.length;
        const draftCount = draftVitalSigns.length;
        const newCount = vitalSignsArray.length;
        
        let countText = '';
        if (draftCount > 0 && newCount > 0) {
            countText = `${totalCount} record (${draftCount} draft + ${newCount} baru)`;
        } else if (draftCount > 0) {
            countText = `${draftCount} record draft`;
        } else if (newCount > 0) {
            countText = `${newCount} record baru`;
        } else {
            countText = '0 record';
        }
        
        document.getElementById('record_count').textContent = countText;
        
        // Enable/disable save button
        const saveBtn = document.getElementById('btn_save_all');
        if (totalCount > 0) {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            saveBtn.style.cursor = 'pointer';
        } else {
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
        }
        
        // AutoSave HANYA input baru ke localStorage (draft sudah ada)
        const storageKey = 'vital_signs_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            // Gabungkan draft dan input baru untuk autosave
            localStorage.setItem(storageKey, JSON.stringify(allData));
            console.log('Vital signs auto-saved to localStorage');
        } catch (e) {
            console.error('Failed to auto-save vital signs:', e);
        }
    }
    
    // Load vital signs from database (tampilkan di tabel terpisah)
    function loadVitalSignsFromDatabase() {
        const dbData = <?= $vital_data_json ?>;
        
        if (dbData && dbData.length > 0) {
            console.log('Loading ' + dbData.length + ' records from database...');
            
            // Konversi format database ke format aplikasi dan simpan di dbVitalSigns
            dbVitalSigns = dbData.map((record, index) => {
                // Extract jam dari waktu (YYYY-MM-DD HH:MM:SS -> HH:MM:SS)
                const waktu = record.waktu;
                const jam = waktu ? waktu.split(' ')[1] : '00:00:00';
                
                return {
                    id: Date.now() + index,
                    jam: jam,
                    respirasi: parseInt(record.respirasi) || 0,
                    nadi: parseInt(record.nadi) || 0,
                    sistol: parseInt(record.td_sistolik) || 0,
                    diastol: parseInt(record.td_diastolik) || 0,
                    fio2: parseInt(record.fio2) || 0,
                    spo2: parseInt(record.spo2) || 0
                };
            });
            
            // Update UI untuk tabel database
            updateDatabaseTable();
            updateViewVisibility();
            
            console.log('✓ Loaded ' + dbVitalSigns.length + ' records from database');
            showToast('📊 ' + dbVitalSigns.length + ' record vital sign dimuat dari database');
            
            return true;
        }
        
        return false;
    }
    
    // Restore vital signs from localStorage (tampilkan di tabel draft)
    function restoreVitalSigns() {
        const storageKey = 'vital_signs_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const savedData = JSON.parse(saved);
                if (savedData.length > 0) {
                    // Simpan ke draftVitalSigns (bukan vitalSignsArray)
                    draftVitalSigns = savedData;
                    
                    // Update UI untuk tabel draft
                    updateDraftTable();
                    updateViewVisibility();
                    
                    showToast('📋 ' + draftVitalSigns.length + ' data draft dipulihkan dari autosave');
                    console.log('Vital signs restored from localStorage:', draftVitalSigns.length + ' records');
                    
                    return true;
                }
            }
        } catch (e) {
            console.error('Failed to restore vital signs:', e);
        }
        return false;
    }
    
    // Clear autosave from localStorage
    function clearAutosave() {
        const storageKey = 'vital_signs_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            localStorage.removeItem(storageKey);
            console.log('Vital signs autosave cleared');
        } catch (e) {
            console.error('Failed to clear autosave:', e);
        }
    }
    
    // Toast notification - use global Notification system
    function showToast(message) {
        if (typeof Notification !== 'undefined' && Notification.info) {
            Notification.info(message, 3000);
        } else {
            // Fallback if global notification not loaded
            console.log(message);
        }
    }
    
    // Export to PDF
    function exportToPDF() {
        // Get chart as base64 image
        const chartCanvas = document.getElementById('vitalChart');
        const chartImage = chartCanvas.toDataURL('image/png');
        
        // Prepare data
        const pdfData = {
            no_rawat: '<?= $no_rawat ?>',
            kode_paket: '<?= $kode_paket ?>',
            tanggal: '<?= $tanggal ?>',
            jam_mulai: '<?= $jam_mulai ?>',
            chart_image: chartImage,
            db_data: dbVitalSigns,
            draft_data: draftVitalSigns,
            new_data: vitalSignsArray,
            current_mode: currentViewMode,
            pasien: {
                nama: '<?= htmlspecialchars($pasien['nama_pasien'] ?? '') ?>',
                no_rkm_medis: '<?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '') ?>',
                jk: '<?= htmlspecialchars($pasien['jenis_kelamin'] ?? '') ?>',
                umur: '<?= htmlspecialchars($pasien['umur'] ?? '') ?>',
                tgl_lahir: '<?= htmlspecialchars($pasien['tanggal_lahir'] ?? '') ?>'
            }
        };
        
        // Open PDF in new tab
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process/pdf/pdf-vital-sign.php';
        form.target = '_blank';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'pdf_data';
        input.value = JSON.stringify(pdfData);
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        showToast('📄 Membuka PDF...');
    }
    
    // Save all vital signs to database
    function saveAllVitalSigns() {
        // Gabungkan draft dan input baru
        const allData = [...draftVitalSigns, ...vitalSignsArray];
        
        if (allData.length === 0) {
            alert('⚠️ Tidak ada data untuk disimpan!');
            return;
        }
        
        const saveBtn = document.getElementById('btn_save_all');
        const originalText = saveBtn.innerHTML;
        
        // Disable button and show loading
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        // Prepare data (gabungkan draft + input baru)
        const dataToSend = {
            no_rawat: '<?= $no_rawat ?>',
            kode_paket: '<?= $kode_paket ?>',
            tanggal: '<?= $tanggal ?>',
            jam_mulai: '<?= $jam_mulai ?>',
            vital_signs: allData
        };
        
        // Send AJAX request
        fetch('process/process-simpan-vital-sign.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dataToSend)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ ' + data.message);
                
                // Clear autosave
                clearAutosave();
                
                // Clear array
                vitalSignsArray = [];
                updateTable();
                updateChart();
                updateHiddenInput();
                
                // Reload page after 1.5 seconds to show saved data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Gagal menyimpan data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error: ' + error.message);
            
            // Re-enable button
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial time
        setCurrentTime();
        
        // Update time button click
        document.getElementById('btn_set_time').addEventListener('click', function() {
            setCurrentTime();
            showToast('⏰ Waktu diperbarui!');
        });
        
        // Save all button click
        document.getElementById('btn_save_all').addEventListener('click', function() {
            if (confirm('💾 Simpan ' + vitalSignsArray.length + ' record vital sign ke database?')) {
                saveAllVitalSigns();
            }
        });
        
        // Initialize chart
        initChart();
        
        // Load data dari database (tampilkan di tabel hijau)
        const loadedFromDB = loadVitalSignsFromDatabase();
        
        // Load data dari localStorage (tampilkan di tabel orange/draft)
        const loadedFromDraft = restoreVitalSigns();
        
        if (loadedFromDB && loadedFromDraft) {
            console.log('✓ Data dimuat dari database dan draft localStorage');
        } else if (loadedFromDB) {
            console.log('✓ Data dimuat dari database saja');
        } else if (loadedFromDraft) {
            console.log('✓ Data dimuat dari draft localStorage saja');
        }
        
        // Initialize save button state
        updateHiddenInput();
    });
</script>

<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    /* Toggle button hover effects */
    .view-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .view-toggle-btn:active {
        transform: translateY(0);
    }
</style>