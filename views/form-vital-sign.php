<?php
// Ambil parameter dari URL
$no_rawat = $_GET['no_rawat'] ?? '';
$kode_paket = $_GET['kode_paket'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';

include __DIR__ . '/../includes/header.php';

// Koneksi PDO
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
?>

<!-- Header -->
<div class="header">
  <div class="logo">
    <img src="https://upload.wikimedia.org/wikipedia/commons/a/ac/No_image_available.svg" alt="Logo Rumah Sakit">
    <div>
      <strong>Rumah Sakit Umum</strong><br>
      <span style="color:#396cf0; font-weight:bold;">PRASETYA BUNDA</span>
    </div>
  </div>
  <div class="sticker-box">
    No. Rawat: <?= htmlspecialchars($no_rawat) ?>
  </div>
</div>

<div class="container">
  <!-- Title -->
  <div class="title">
    <div style="color: #004d80;">CATATAN SEDASI DAN ANESTESI</div>
    <div>RMOK-0003</div>
  </div>

  <div class="card">
    <form action="simpan_catatan_sedasi.php" method="POST">
      <input type="hidden" name="no_rawat" value="<?= htmlspecialchars($no_rawat) ?>">
      <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($kode_paket) ?>">
      <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
      <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>">

      <!-- Grafik Vital Sign -->
      <h2>Grafik Vital Sign</h2>
      <div class="chart-wrapper">
          <canvas id="chartVital"></canvas>
      </div>

      <!-- Form Input Vital Sign -->
      <div class="form-container">
          <div><label>Respirasi (R)</label><input type="number" id="resp" name="resp"></div>
          <div><label>Nadi (N)</label><input type="number" id="nadi" name="nadi"></div>
          <div><label>TD Sistolik</label><input type="number" id="sistolik" name="sistolik"></div>
          <div><label>TD Diastolik</label><input type="number" id="diastolik" name="diastolik"></div>
          <div><label>SPO₂ (%)</label><input type="number" id="spo2" name="spo2"></div>
      </div>

      <div class="button-container">
          <button type="button" class="btn-add" onclick="tambahDataVital()">Tambah Data Vital</button>
          <button type="submit" class="btn-add">Simpan Catatan Anestesi</button>
      </div>

      <!-- Lanjutkan dengan form catatan anestesi lainnya -->
      
    </form>
  </div>
</div>

<script>
  // Data dari PHP
  const vitalData = <?= json_encode($vital_data) ?>;

  // Siapkan data untuk chart
  const labels = vitalData.map(item => new Date(item.waktu).toLocaleTimeString('id-ID'));
  const dataResp = vitalData.map(item => item.respirasi);
  const dataNadi = vitalData.map(item => item.nadi);
  const dataSistolik = vitalData.map(item => item.td_sistolik);
  const dataDiastolik = vitalData.map(item => item.td_diastolik);
  const dataSpo2 = vitalData.map(item => item.spo2);

  // Inisialisasi chart
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
    const now = new Date();
    const waktuNow = now.getFullYear() + "-" +
        String(now.getMonth()+1).padStart(2, '0') + "-" +
        String(now.getDate()).padStart(2, '0') + " " +
        String(now.getHours()).padStart(2, '0') + ":" +
        String(now.getMinutes()).padStart(2, '0') + ":" +
        String(now.getSeconds()).padStart(2, '0');

      const formData = new FormData();
      formData.append('no_rawat', '<?= $no_rawat ?>');
      formData.append('kode_paket', '<?= $kode_paket ?>');
      formData.append('tanggal', '<?= $tanggal ?>');
      formData.append('jam_mulai', '<?= $jam_mulai ?>');
      formData.append('waktu', waktuNow);
      formData.append('resp', document.getElementById('resp').value);
      formData.append('nadi', document.getElementById('nadi').value);
      formData.append('sistolik', document.getElementById('sistolik').value);
      formData.append('diastolik', document.getElementById('diastolik').value);
      formData.append('spo2', document.getElementById('spo2').value);

fetch('process/process-simpan-vital-sign.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert('Error menyimpan data vital');
          }
      });
  }
</script>
<?php include __DIR__ . '/../includes/footer.php';?>