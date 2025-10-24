# 🔴 TROUBLESHOOTING: Infinite Loop & Header Rusak

## Masalah yang Terjadi:
1. ❌ Setelah input data posisi, halaman terus refresh tidak berhenti
2. ❌ Tampilan header jadi rusak
3. ❌ Notifikasi "✓ Mode: EDIT - Data ditemukan" tidak hilang-hilang

## Root Cause:
**Field `posisi` belum ditambahkan ke database!**

Ketika form submit:
1. Form mengirim data `posisi[]` ke process
2. Process mencoba INSERT/UPDATE dengan field `posisi`
3. Database error karena field tidak ada
4. Redirect ke form dengan error message
5. Form load → submit lagi → error lagi → **INFINITE LOOP!**

---

## 🛠️ SOLUSI CEPAT

### Step 1: STOP Infinite Loop

**Cara 1: Clear Browser Cache & Session**
```
1. Tekan Ctrl + Shift + Delete
2. Pilih "Cookies and other site data"
3. Pilih "Cached images and files"
4. Klik "Clear data"
5. Close browser
6. Buka lagi
```

**Cara 2: Clear Session Manual**
```php
// Buka file: c:\FOLDER RIZKI\SIMRS_JB\views\form-catatan-sedasi.php
// Tambahkan di baris paling atas (setelah <?php):

session_start();
unset($_SESSION['success']);
unset($_SESSION['error']);
// Lalu comment out setelah 1x refresh
```

**Cara 3: Restart PHP Server**
```bash
# Stop server (Ctrl + C di terminal)
# Start lagi
php -S localhost:8000
```

---

### Step 2: Tambahkan Field `posisi` ke Database

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`
2. Pilih database `dbanestesi`
3. Pilih tabel `tbl_anestesi_catatan_anestesi`
4. Klik tab **SQL**
5. Copy-paste query ini:

```sql
ALTER TABLE `tbl_anestesi_catatan_anestesi` 
ADD COLUMN `posisi` text DEFAULT NULL 
COMMENT 'Posisi pasien (comma separated)' 
AFTER `infus_perifer`;
```

6. Klik **Go**
7. Tunggu sampai muncul pesan sukses

**Via Command Line:**
```bash
cd "C:\FOLDER RIZKI\SIMRS_JB\database"
mysql -u root -p dbanestesi < ALTER_ADD_POSISI_FIELD.sql
```

---

### Step 3: Verifikasi Field Sudah Ada

```sql
-- Jalankan query ini di phpMyAdmin
DESCRIBE tbl_anestesi_catatan_anestesi;
```

**Expected Output:**
```
Field: posisi
Type: text
Null: YES
Default: NULL
```

**Atau cek dengan query ini:**
```sql
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_catatan_anestesi' 
  AND COLUMN_NAME = 'posisi';
```

**Expected:** 1 row (field `posisi` ditemukan)

---

### Step 4: Clear Browser & Test Lagi

1. **Hard Refresh:** `Ctrl + Shift + R`
2. **Clear localStorage:**
   - F12 → Application → Local Storage
   - Klik kanan → Clear
3. **Close & Reopen Browser**
4. **Buka form lagi:**
   ```
   http://localhost:8000/index.php?page=form-catatan-sedasi&no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00
   ```
5. **Centang checkbox posisi**
6. **Klik Simpan**
7. **Seharusnya tidak loop lagi!**

---

## 🔍 Cek Error Log

**Jika masih loop, cek error log:**

**File log:**
```
c:\FOLDER RIZKI\SIMRS_JB\logs\persiapan-operasi-debug.log
```

**Atau cek PHP error log:**
```
C:\xampp\php\logs\php_error_log.txt
```

**Cari error seperti:**
```
Database Error: Unknown column 'posisi' in 'field list'
```

**Jika ada error ini, berarti field `posisi` belum ditambahkan!**

---

## 🎯 Checklist Troubleshooting

- [ ] **Stop infinite loop** (clear cache/session/restart server)
- [ ] **Jalankan ALTER TABLE** (tambah field `posisi`)
- [ ] **Verifikasi** field sudah ada di database
- [ ] **Clear browser cache** (Ctrl + Shift + R)
- [ ] **Clear localStorage** (F12 → Application)
- [ ] **Close & reopen browser**
- [ ] **Test form lagi**
- [ ] **Cek error log** jika masih error

---

## 🚨 Jika Masih Bermasalah

### Cek 1: Apakah field `posisi` benar-benar sudah ada?
```sql
SHOW COLUMNS FROM tbl_anestesi_catatan_anestesi LIKE 'posisi';
```

### Cek 2: Apakah ada error di process?
```bash
# Lihat terminal yang running PHP server
# Seharusnya ada error message
```

### Cek 3: Apakah jumlah parameter match?
```
Error: Jumlah token (76) tidak match dengan parameter (75)
```

**Jika ada error ini:**
- Berarti ada field yang kurang/lebih di query
- Cek file: `process-simpan-catatan-sedasi.php`
- Hitung jumlah `?` di query vs jumlah item di `$params`

### Cek 4: Apakah session tidak ter-clear?
```php
// Tambahkan di form-catatan-sedasi.php (baris paling atas)
<?php
session_start();
var_dump($_SESSION); // Debug session
unset($_SESSION['success']);
unset($_SESSION['error']);
exit; // Temporary, hapus setelah cek
?>
```

---

## ✅ Setelah Selesai

**Expected Result:**
1. ✅ Form tidak loop lagi
2. ✅ Header tampil normal
3. ✅ Notifikasi muncul 3 detik lalu hilang
4. ✅ Data posisi tersimpan di database
5. ✅ Checkbox posisi ter-check sesuai data

---

## 📝 Catatan Penting

**Kenapa ini terjadi?**
- Field `posisi` ditambahkan di code (form & process)
- Tapi belum ditambahkan di database
- Saat save, database error: "Unknown column 'posisi'"
- Error tidak ditampilkan dengan jelas
- Redirect terus ke form → submit lagi → error lagi → **LOOP!**

**Pelajaran:**
- Selalu jalankan ALTER TABLE **SEBELUM** menggunakan field baru
- Cek error log untuk debugging
- Gunakan try-catch yang baik untuk handle error database

---

**Good luck!** 🚀
