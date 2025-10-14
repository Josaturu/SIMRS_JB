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

// include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Sign</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .form-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .form-container div { display: flex; flex-direction: column; }
        .form-container input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .chart-wrapper { margin: 20px 0; }
        .button-container { margin-top: 20px; text-align: right; }
        .btn-add { padding: 10px 20px; background: #396cf0; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-back { padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ac/No_image_available.svg" alt="Logo Rumah Sakit">
        <div>
            <strong>Rumah Sakit Umum</strong><br>
            <span style="color:#396cf0; font-weight:bold;">PRASETYA BUNDA</span>
        </div>
    </div>
    <div class="sticker-box">
        No. Rawat: <?= htmlspecialchars($no_rawat) ?><br>
        Kode Paket: <?= htmlspecialchars($kode_paket) ?><br>
        Tanggal: <?= htmlspecialchars(date('d/m/Y', strtotime($tanggal))) ?><br>
        Jam Mulai: <?= htmlspecialchars($jam_mulai) ?>
    </div>
</div>

<div class="container">
    <!-- Navigation -->
    <!-- <div style="margin: 20px 0; padding: 10px; background: #e9ecef; border-radius: 5px;">
        <strong>Proses Laporan:</strong>
        <a href="index.php?page=konsultasi-anestesi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
           style="padding: 5px 10px; text-decoration: none;">1. Konsultasi</a>
        <a href="index.php?page=informed-consent-anestesi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
           style="padding: 5px 10px; text-decoration: none;">2. Informed Consent</a>
        <a href="index.php?page=catatan-sedasi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
           style="padding: 5px 10px; text-decoration: none;">3. Catatan Sedasi & Anestesi</a>
        <a href="index.php?page=vital-sign&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
           style="background:#396cf0; color:white; padding:5px 10px; text-decoration:none;">4. Vital Sign</a>
    </div> -->

    <!-- Title -->
    <div class="title">
        <div style="color: #004d80;">VITAL SIGN</div>
        <div>RMOK-VS</div>
    </div>

    <div class="card">
        <!-- Form Input Vital Sign -->
        <h2>Tambah Data Vital Sign</h2>
        <div class="form-container">
            <div>
                <label>Respirasi (R)</label>
                <input type="number" id="resp" name="resp" required>
            </div>
            <div>
                <label>Nadi (N)</label>
                <input type="number" id="nadi" name="nadi" required>
            </div>
            <div>
                <label>TD Sistolik</label>
                <input type="number" id="sistolik" name="sistolik" required>
            </div>
            <div>
                <label>TD Diastolik</label>
                <input type="number" id="diastolik" name="diastolik" required>
            </div>
            <div>
                <label>SPO₂ (%)</label>
                <input type="number" id="spo2" name="spo2" required>
            </div>
        </div>

        <div class="button-container">
            <button type="button" class="btn-add" onclick="tambahDataVital()">Tambah Data Vital</button>
            <a href="index.php?page=catatan-sedasi&no_rawat=<?= urlencode($no_rawat) ?>&kode_paket=<?= urlencode($kode_paket) ?>&tanggal=<?= urlencode($tanggal) ?>&jam_mulai=<?= urlencode($jam_mulai) ?>" 
               class="btn-back">Kembali</a>
        </div>
    </div>

    <!-- Grafik Vital Sign -->
    <div class="card">
        <h2>Grafik Vital Sign</h2>
        <div class="chart-wrapper">
            <canvas id="chartVital"></canvas>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
const vitalData = <?= json_encode($vital_data) ?>;
const labels = vitalData.map(item => new Date(item.waktu).toLocaleTimeString('id-ID'));
const dataResp = vitalData.map(item => item.respirasi);
const dataNadi = vitalData.map(item => item.nadi);
const dataSistolik = vitalData.map(item => item.td_sistolik);
const dataDiastolik = vitalData.map(item => item.td_diastolik);
const dataSpo2 = vitalData.map(item => item.spo2);

const ctx = document.getElementById('chartVital').getContext('2d');
const chartVital = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Resp (R)', data: dataResp, borderColor: '#396cf0', backgroundColor: '#396cf0', tension: 0.3 },
            { label: 'Nadi (N)', data: dataNadi, borderColor: '#e63946', backgroundColor: '#e63946', tension: 0.3 },
            { label: 'TD Sistolik', data: dataSistolik, borderColor: '#2a9d8f', backgroundColor: '#2a9d8f', tension: 0.3 },
            { label: 'TD Diastolik', data: dataDiastolik, borderColor: '#f4a261', backgroundColor: '#f4a261', tension: 0.3 },
            { label: 'SPO₂', data: dataSpo2, borderColor: '#6a4c93', backgroundColor: '#6a4c93', tension: 0.3 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            title: { display: true, text: 'Grafik Vital Sign', color: '#396cf0', font: { size: 16 } },
            legend: { position: 'bottom' }
        },
        scales: { 
            y: { beginAtZero: true }, 
            x: { title: { display: true, text: 'Waktu', color: '#396cf0' } } 
        }
    }
});

function tambahDataVital() {
    // Validasi input
    const resp = document.getElementById('resp').value;
    const nadi = document.getElementById('nadi').value;
    const sistolik = document.getElementById('sistolik').value;
    const diastolik = document.getElementById('diastolik').value;
    const spo2 = document.getElementById('spo2').value;

    if (!resp || !nadi || !sistolik || !diastolik || !spo2) {
        alert('Harap lengkapi semua field vital sign!');
        return;
    }

    const now = new Date();
    const waktuNow = now.getFullYear() + "-" +
        String(now.getMonth()+1).padStart(2, '0') + "-" +
        String(now.getDate()).padStart(2, '0') + " " +
        String(now.getHours()).padStart(2, '0') + ":" +
        String(now.getMinutes()).padStart(2, '0') + ":" +
        String(now.getSeconds()).padStart(2, '0');

    const formData = new FormData();
    formData.append('no_rawat', '<?= htmlspecialchars($no_rawat) ?>');
    formData.append('kode_paket', '<?= htmlspecialchars($kode_paket) ?>');
    formData.append('tanggal', '<?= htmlspecialchars($tanggal) ?>');
    formData.append('jam_mulai', '<?= htmlspecialchars($jam_mulai) ?>');
    formData.append('waktu', waktuNow);
    formData.append('resp', resp);
    formData.append('nadi', nadi);
    formData.append('sistolik', sistolik);
    formData.append('diastolik', diastolik);
    formData.append('spo2', spo2);

    fetch('process/process-simpan-vital-sign.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error menyimpan data vital: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error jaringan: ' + error.message);
    });
}
</script>
</body>
</html>