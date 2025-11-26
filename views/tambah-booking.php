<?php
$page_title = "Tambah Booking Operasi Baru";
include __DIR__ . '/../includes/assets.php';
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
                <!-- Tombol aksi utama dipindahkan ke Speed Dial -->
            </div>
        </form>
    </div>
</div>

<!-- Speed Dial: Simpan, Batal -->
<div data-dial-init class="fixed right-6 bottom-6 group">
    <div id="speed-dial-menu-tambah-booking" class="flex flex-col w-32 justify-end hidden mb-4 space-y-2 bg-neutral-primary-medium border border-default-medium rounded-base shadow-xs">
        <ul class="p-2 text-sm text-body font-medium">
            <li>
                <a href="#" onclick="document.getElementById('formTambahBooking').submit(); return false;" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M11 16h2m6.707-9.293-2.414-2.414A1 1 0 0 0 16.586 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.414a1 1 0 0 0-.293-.707ZM16 20v-6a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v6h8ZM9 4h6v3a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
                    <span class="text-sm font-medium">Simpan</span>
                </a>
            </li>
            <li>
                <a href="index.php" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                    <!-- Back Arrow Icon -->
                    <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6l-6 6 6 6"/></svg>
                    <span class="text-sm font-medium">Batal</span>
                </a>
            </li>
        </ul>
    </div>
    <button type="button" data-dial-toggle="speed-dial-menu-tambah-booking" aria-controls="speed-dial-menu-tambah-booking" aria-expanded="false" class="flex items-center justify-center ml-auto text-white bg-brand rounded-base w-14 h-14 hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium focus:outline-none">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
        <span class="sr-only">Open actions menu</span>
    </button>
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