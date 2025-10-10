<?php
$page_title = "Tambah Booking Operasi Baru";
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FormModel.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();
$form = new FormModel($db);
?>

<div class="container">
    <h2>Tambah Booking Operasi Baru</h2>
    
    <div class="card">
        <form id="formTambahBooking" action="process/submit-booking-operasi.php" method="POST">
            <div class="form-grid">
                <div class="form-column">
                    <div class="input-container">
                        <input type="text" id="no_rawat" name="no_rawat" placeholder=" " required>
                        <label for="no_rawat" class="label-floating">No. Rawat</label>
                    </div>
                    
                    <div class="input-container">
                        <input type="text" id="nama_pasien" name="nama_pasien" placeholder=" " required>
                        <label for="nama_pasien" class="label-floating">Nama Pasien</label>
                    </div>
                    
                    <div class="input-container">
                        <input type="date" id="tanggal" name="tanggal" placeholder=" " required>
                        <label for="tanggal" class="label-floating">Tanggal Operasi</label>
                    </div>
                </div>
                
                <div class="form-column">
                    <div class="input-container">
                        <input type="time" id="jam_mulai" name="jam_mulai" placeholder=" " required>
                        <label for="jam_mulai" class="label-floating">Jam Mulai</label>
                    </div>
                    
                    <div class="input-container">
                        <input type="time" id="jam_selesai" name="jam_selesai" placeholder=" ">
                        <label for="jam_selesai" class="label-floating">Jam Selesai (Opsional)</label>
                    </div>
                    
                    <div class="input-container">
                        <select id="kd_dokter" name="kd_dokter" required>
                            <option value="">Pilih Dokter</option>
                            <option value="DR001">Dr. Ahmad Surono</option>
                            <option value="DR002">Dr. Budi Santoso</option>
                            <option value="DR003">Dr. Citra Dewi</option>
                        </select>
                        <label for="kd_dokter" class="label-floating">Dokter Operator</label>
                    </div>
                    
                    <div class="input-container">
                        <select id="kd_ruang_ok" name="kd_ruang_ok" required>
                            <option value="">Pilih Ruang OK</option>
                            <option value="OK1">Ruang OK 1</option>
                            <option value="OK2">Ruang OK 2</option>
                            <option value="OK3">Ruang OK 3</option>
                        </select>
                        <label for="kd_ruang_ok" class="label-floating">Ruang Operasi</label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Booking</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
// Key untuk localStorage
const FORM_KEY = 'tambahBookingFormDraft';

// Ambil data dari localStorage dan isi field
function loadFormDraft() {
    const draft = localStorage.getItem(FORM_KEY);
    if (draft) {
        try {
            const data = JSON.parse(draft);
            for (const key in data) {
                if (data.hasOwnProperty(key) && document.getElementById(key)) {
                    document.getElementById(key).value = data[key];
                }
            }
        } catch (e) {}
    } else {
        // Set tanggal default ke hari ini jika belum ada draft
        if (document.getElementById('tanggal')) {
            document.getElementById('tanggal').valueAsDate = new Date();
        }
        // Set jam mulai default ke jam berikutnya
        if (document.getElementById('jam_mulai')) {
            const now = new Date();
            now.setHours(now.getHours() + 1);
            now.setMinutes(0);
            document.getElementById('jam_mulai').value = now.toTimeString().substr(0, 5);
        }
    }
}

// Simpan data ke localStorage setiap ada perubahan
function saveFormDraft() {
    const form = document.getElementById('formTambahBooking');
    const data = {};
    Array.from(form.elements).forEach(el => {
        if (el.name) data[el.id] = el.value;
    });
    localStorage.setItem(FORM_KEY, JSON.stringify(data));
}

// Hapus draft setelah submit sukses
function clearFormDraft() {
    localStorage.removeItem(FORM_KEY);
}

document.addEventListener('DOMContentLoaded', function() {
    loadFormDraft();
    // Set event listener untuk auto-save
    const form = document.getElementById('formTambahBooking');
    form.addEventListener('input', saveFormDraft);
    // Hapus draft jika submit sukses
    form.addEventListener('submit', function() {
        clearFormDraft();
    });
});
</script>