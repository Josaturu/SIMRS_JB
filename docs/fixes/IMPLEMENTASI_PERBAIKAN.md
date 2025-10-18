# 🚀 Panduan Implementasi Perbaikan Style & UX

File ini berisi panduan praktis untuk mengimplementasikan perbaikan yang sudah dibuat.

---

## ✅ Yang Sudah Diterapkan

### 1. **Header Consistency Fix**
- ✅ `daftar-pasien.php` sekarang menggunakan `include header.php`
- ✅ Semua halaman konsisten menggunakan struktur yang sama

### 2. **Improvements CSS & JS**
- ✅ File `assets/css/improvements.css` sudah dibuat dan di-include di `header.php`
- ✅ File `assets/js/improvements.js` sudah dibuat dan di-include di `footer.php`

---

## 🎨 Fitur Baru yang Tersedia

### 1. **Toast Notification System**

Gunakan untuk menampilkan notifikasi elegant:

```javascript
// Success message
Toast.success('Data berhasil disimpan!');

// Error message
Toast.error('Terjadi kesalahan, coba lagi.');

// Warning message
Toast.warning('Periksa kembali inputan Anda.');

// Info message
Toast.info('Proses sedang berjalan...');

// Custom duration (default 3000ms)
Toast.success('Pesan ini 5 detik', 5000);
```

**Contoh implementasi di form:**
```php
<!-- Di process file, setelah sukses simpan -->
<script>
    Toast.success('Booking berhasil dibuat! Kode: <?= $kode_paket ?>');
    setTimeout(() => {
        window.location = 'index.php?page=detail-pasien&...';
    }, 1500);
</script>
```

### 2. **Loading Overlay**

Tampilkan loading saat proses berlangsung:

```javascript
// Show loading
Loading.show('Menyimpan data...');

// Hide loading
Loading.hide();
```

**Auto-loading untuk semua form:**
Form dengan `id` akan otomatis menampilkan loading saat submit. Untuk disable:
```html
<form id="myForm" data-no-loading>
    <!-- Form dengan data-no-loading tidak akan auto-loading -->
</form>
```

### 3. **Form Validation dengan Error Messages**

Validasi otomatis untuk semua field `required`:

```html
<div class="input-container">
    <input type="email" name="email" required>
    <label class="label-floating">Email</label>
    <!-- Error message akan muncul otomatis di sini -->
</div>
```

**Validasi manual:**
```javascript
const isValid = validateInput(inputElement);
if (!isValid) {
    Toast.error('Periksa inputan Anda');
}
```

### 4. **Back to Top Button**

Tombol sudah otomatis muncul saat scroll > 300px. Tidak perlu konfigurasi tambahan!

### 5. **Modal/Dialog**

Buat dialog custom:

```javascript
// Simple modal
Modal.create(
    'Judul Modal',
    'Konten modal di sini...',
    [
        {
            text: 'Tutup',
            type: 'secondary',
            action: 'close',
            handler: (modal) => Modal.close(modal)
        },
        {
            text: 'Simpan',
            type: 'primary',
            action: 'save',
            handler: (modal) => {
                // Do something
                Modal.close(modal);
            }
        }
    ]
);

// Confirm dialog
Modal.confirm(
    'Konfirmasi Hapus',
    'Yakin ingin menghapus data ini?',
    () => {
        // On confirm
        Toast.success('Data dihapus');
    },
    () => {
        // On cancel
        Toast.info('Batal menghapus');
    }
);
```

### 6. **Auto-Save Form Draft**

Simpan draft form otomatis setiap 5 detik:

```javascript
// Di footer form yang butuh auto-save
<script>
    new FormAutoSave('formTambahBooking', 'tambahBookingDraft', 5000);
</script>
```

Parameter:
- `formTambahBooking`: ID form
- `tambahBookingDraft`: Key localStorage
- `5000`: Interval save (ms), opsional

### 7. **Tooltips**

Tambahkan tooltip pada elemen:

```html
<label>
    Status Fisik ASA
    <i class="fas fa-info-circle" data-tooltip="Klasifikasi ASA I sampai VI untuk risiko anestesi"></i>
</label>

<button data-tooltip="Klik untuk menyimpan">
    <i class="fas fa-save"></i>
</button>
```

### 8. **Print-Friendly Forms**

Semua form otomatis print-friendly. Saat print:
- Navigation, button, footer akan disembunyikan
- Hanya konten utama yang dicetak
- Sticker box akan tercetak dengan border jelas

**Tambah tombol print:**
```html
<button class="btn btn-secondary" data-print>
    <i class="fas fa-print"></i> Cetak Form
</button>
```

---

## 📝 Cara Upgrade Form yang Ada

### Contoh: Upgrade `form-persiapan-operasi.php`

#### 1. Ganti Alert Biasa dengan Toast

**❌ Sebelum:**
```php
<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
    <div class="alert alert-success">Data berhasil disimpan!</div>
<?php endif; ?>
```

**✅ Sesudah:**
```php
<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
    <script>
        Toast.success('Data berhasil disimpan!');
    </script>
<?php endif; ?>
```

#### 2. Tambah Loading ke Form Submit

**❌ Sebelum:**
```html
<form action="process/..." method="POST">
    <!-- form fields -->
    <button type="submit">Simpan</button>
</form>
```

**✅ Sesudah:**
```html
<form id="formPersiapan" action="process/..." method="POST">
    <!-- form fields -->
    <button type="submit">
        <i class="fas fa-save"></i> Simpan
    </button>
</form>
<!-- Loading otomatis karena form punya ID -->
```

#### 3. Tambah Konfirmasi Sebelum Submit

```html
<form id="formPersiapan" onsubmit="return confirmSubmit(event)">
    <!-- form -->
</form>

<script>
function confirmSubmit(e) {
    e.preventDefault();
    Modal.confirm(
        'Konfirmasi Simpan',
        'Pastikan semua data sudah benar. Lanjutkan menyimpan?',
        () => {
            // Submit form
            Loading.show('Menyimpan data...');
            e.target.submit();
        }
    );
    return false;
}
</script>
```

#### 4. Tambah Auto-Save

```html
<!-- Di akhir form, sebelum </form> -->
<script>
    new FormAutoSave('formPersiapan', 'draftPersiapanOperasi');
</script>
```

#### 5. Tambah Breadcrumb Navigation

```html
<!-- Setelah navbar, sebelum container -->
<div class="breadcrumb">
    <a href="index.php">Home</a> › 
    <a href="index.php?page=detail-pasien&...">Detail Pasien</a> › 
    <span>Checklist Persiapan Operasi</span>
</div>
```

---

## 🔧 Customization Tips

### Ganti Warna Toast

Edit `improvements.css`:
```css
.toast.success { border-left-color: #your-color; }
.toast.success .toast-icon { color: #your-color; }
```

### Ganti Durasi Auto-Save

```javascript
new FormAutoSave('formId', 'key', 10000); // 10 detik
```

### Nonaktifkan Back-to-Top di Halaman Tertentu

```javascript
// Di footer halaman tersebut
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.querySelector('.back-to-top');
        if (btn) btn.remove();
    });
</script>
```

---

## 🐛 Troubleshooting

### Toast tidak muncul?
1. Pastikan `improvements.js` sudah di-include
2. Cek console browser untuk error
3. Pastikan dipanggil setelah DOM ready

### Loading tidak hilang setelah submit?
Untuk form biasa (bukan AJAX), loading otomatis hilang saat page reload.
Untuk AJAX:
```javascript
fetch(url, options)
    .then(response => response.json())
    .then(data => {
        Loading.hide(); // Manual hide
        Toast.success('Berhasil!');
    })
    .catch(error => {
        Loading.hide(); // Jangan lupa hide di error juga
        Toast.error('Gagal!');
    });
```

### Form validation tidak jalan?
Pastikan input punya atribut `required`:
```html
<input type="text" name="nama" required> ✓
<input type="text" name="nama"> ✗
```

### Print tidak bagus?
1. Pastikan `improvements.css` sudah di-include
2. Cek `@media print` rules di CSS
3. Test dengan Print Preview browser

---

## 📚 Contoh Implementasi Lengkap

### Form dengan Semua Fitur

```html
<?php
$page_title = "Form Example";
// ... database query ...
$pasien = $booking;
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="index.php">Home</a> › 
    <a href="index.php?page=detail-pasien&...">Detail Pasien</a> › 
    <span>Form Example</span>
</div>

<div class="container">
    <div class="card">
        <h2>Form Example</h2>
        
        <!-- Form dengan loading otomatis -->
        <form id="exampleForm" action="process/submit.php" method="POST">
            <input type="hidden" name="no_rawat" value="<?= htmlspecialchars($no_rawat) ?>">
            
            <!-- Field dengan validasi otomatis -->
            <div class="input-container">
                <input type="text" name="nama" placeholder=" " required>
                <label class="label-floating">Nama Pasien</label>
            </div>
            
            <div class="input-container">
                <input type="email" name="email" placeholder=" " required>
                <label class="label-floating">Email</label>
            </div>
            
            <!-- Field dengan tooltip -->
            <div class="input-container">
                <select name="status_asa" required>
                    <option value="">Pilih</option>
                    <option value="I">ASA I</option>
                    <option value="II">ASA II</option>
                </select>
                <label class="label-floating">
                    Status ASA
                    <i class="fas fa-info-circle" 
                       data-tooltip="Klasifikasi risiko anestesi"></i>
                </label>
            </div>
            
            <!-- Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="button" class="btn btn-secondary" data-print>
                    <i class="fas fa-print"></i> Cetak
                </button>
                <a href="index.php?page=detail-pasien&..." class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Auto-save -->
<script>
    new FormAutoSave('exampleForm', 'exampleFormDraft');
</script>

<?php include 'includes/footer.php'; ?>
```

### Process File dengan Toast

```php
<?php
// process/submit-example.php
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Validasi server-side
    if (empty($_POST['nama'])) {
        throw new Exception('Nama wajib diisi');
    }
    
    // Insert data
    $query = "INSERT INTO tabel (...) VALUES (...)";
    $stmt = $db->prepare($query);
    $stmt->execute([...]);
    
    // Redirect dengan toast
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <link rel="stylesheet" href="../assets/css/improvements.css">
    </head>
    <body>
        <div class="toast-container"></div>
        <script src="../assets/js/improvements.js"></script>
        <script>
            Toast.success('Data berhasil disimpan!');
            setTimeout(() => {
                window.location = '../index.php?page=detail-pasien&...';
            }, 1500);
        </script>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <link rel="stylesheet" href="../assets/css/improvements.css">
    </head>
    <body>
        <div class="toast-container"></div>
        <script src="../assets/js/improvements.js"></script>
        <script>
            Toast.error('<?= addslashes($e->getMessage()) ?>');
            setTimeout(() => {
                window.history.back();
            }, 2000);
        </script>
    </body>
    </html>
    <?php
}
?>
```

---

## 🎯 Next Steps

1. **Tes semua fitur baru** di browser
2. **Upgrade form satu per satu** mulai dari yang paling sering dipakai
3. **Tambah error handling** di semua process files
4. **Test print functionality** untuk memastikan form medis ter-cetak dengan baik
5. **Gather feedback** dari user untuk improvement selanjutnya

---

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Cek file `ANALISIS_STYLE_DAN_PERBAIKAN.md` untuk analisis lengkap
2. Cek console browser untuk error JavaScript
3. Review contoh implementasi di file ini

**Selamat mengimplementasikan! 🚀**
