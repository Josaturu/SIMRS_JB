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
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis
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

// Query data vital sign untuk grafik
$query_vital = "SELECT waktu, respirasi, nadi, td_sistolik, td_diastolik, spo2 
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
                <!-- Grafik -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-chart-line"></i> Grafik Vital Sign
                    </h3>
                    <canvas id="vitalChart" style="max-height: 300px;"></canvas>
                </div>
                
                <!-- Table History Records (dibawah grafik) -->
                <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #495057; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-table"></i> History Records
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
                                </tr>
                            </thead>
                            <tbody id="vital_tbody">
                                <tr>
                                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                                        <i class="fas fa-info-circle"></i> Belum ada data vital sign. Silakan tambah record baru.
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
        
        <!-- Button Kembali -->
        <div style="margin-top: 20px; text-align: right;">
            <button type="button" class="btn btn-secondary" 
                    onclick="window.location.href='index.php?page=catatan-sedasi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>'">
                Kembali
            </button>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
    // ==================== VITAL SIGN MANAGEMENT ====================
    let vitalSignsArray = [];
    let vitalChart = null;
    
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
    
    // Update table
    function updateTable() {
        const tbody = document.getElementById('vital_tbody');
        
        if (vitalSignsArray.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d; border: 1px solid #dee2e6;">
                        <i class="fas fa-info-circle"></i> Belum ada data vital sign. Silakan tambah record baru.
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
            `;
            tbody.appendChild(row);
        });
    }
    
    // Update chart
    function updateChart() {
        if (!vitalChart) return;
        
        const labels = vitalSignsArray.map(r => r.jam);
        const respirasi = vitalSignsArray.map(r => r.respirasi);
        const nadi = vitalSignsArray.map(r => r.nadi);
        const sistol = vitalSignsArray.map(r => r.sistol);
        const fio2 = vitalSignsArray.map(r => r.fio2);
        const spo2 = vitalSignsArray.map(r => r.spo2);
        
        vitalChart.data.labels = labels;
        vitalChart.data.datasets[0].data = respirasi;
        vitalChart.data.datasets[1].data = nadi;
        vitalChart.data.datasets[2].data = sistol;
        vitalChart.data.datasets[3].data = fio2;
        vitalChart.data.datasets[4].data = spo2;
        
        vitalChart.update();
    }
    
    // Update hidden input
    function updateHiddenInput() {
        document.getElementById('vital_signs_data').value = JSON.stringify(vitalSignsArray);
        
        // AutoSave to localStorage
        const storageKey = 'vital_signs_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            localStorage.setItem(storageKey, JSON.stringify(vitalSignsArray));
            console.log('Vital signs auto-saved to localStorage');
        } catch (e) {
            console.error('Failed to auto-save vital signs:', e);
        }
    }
    
    // Restore vital signs from localStorage
    function restoreVitalSigns() {
        const storageKey = 'vital_signs_<?= $no_rawat ?>_<?= $kode_paket ?>_<?= $tanggal ?>_<?= $jam_mulai ?>';
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                vitalSignsArray = JSON.parse(saved);
                if (vitalSignsArray.length > 0) {
                    updateTable();
                    updateChart();
                    updateHiddenInput();
                    showToast('📋 Data vital sign dipulihkan dari autosave');
                    console.log('Vital signs restored from localStorage:', vitalSignsArray.length + ' records');
                }
            }
        } catch (e) {
            console.error('Failed to restore vital signs:', e);
        }
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
    
    // Toast notification
    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #323232;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
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
        
        // Initialize chart
        initChart();
        
        // Restore vital signs from autosave
        restoreVitalSigns();
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
</style>