# PANDUAN DEBUG: Jenis Kelamin Tidak Tersimpan

## Langkah-Langkah Debugging

### 1. **Buka Debug Script**
Buka browser dan akses:
```
http://localhost/SIMRS_JB/debug_jenis_kelamin.php
```

Script ini akan menampilkan:
- ✅ Struktur tabel database
- ✅ Data 10 record terbaru
- ✅ PHP error log
- ✅ Rekomendasi debugging

**Yang Harus Dicek:**
- Apakah kolom `jenis_kelamin` ada di tabel?
- Apakah ada data dengan `jenis_kelamin` NULL?
- Apakah ada error di log?

---

### 2. **Test Form di Browser**

#### A. Buka Form Konsultasi Anestesi
1. Buka form konsultasi anestesi
2. **Buka Developer Tools** (F12)
3. Pilih tab **Console**

#### B. Isi Form dan Submit
1. Isi semua field yang required
2. **PILIH JENIS KELAMIN** (Laki-laki atau Perempuan)
3. Klik tombol **Submit**

#### C. Cek Console Browser
Setelah submit, di console harus muncul:

```
=== FORM SUBMIT TRIGGERED ===
Form action: http://localhost/SIMRS_JB/process/process-konsultasi-anestesi.php
Form method: post

=== FIELD PENTING ===
tinggiBadan: 170
beratBadan: 70
tb: 170
bb: 70
jenis_kelamin: Laki-laki  ← HARUS ADA NILAI INI (background kuning)
menikah: Ya
kesadaran: Compos Mentis
...
=====================
```

**PENTING:**
- ✅ Jika `jenis_kelamin` muncul dengan **background kuning** → Data terkirim
- ❌ Jika `jenis_kelamin: NULL/KOSONG` dengan **background merah** → Data TIDAK terkirim

---

### 3. **Cek Network Tab**

1. Buka Developer Tools (F12)
2. Pilih tab **Network**
3. Submit form
4. Klik request ke `process-konsultasi-anestesi.php`
5. Pilih tab **Payload** atau **Form Data**

**Cari baris:**
```
jenis_kelamin: Laki-laki
```

- ✅ Jika ada → Data dikirim ke server
- ❌ Jika tidak ada → Masalah di form HTML atau JavaScript

---

### 4. **Cek PHP Error Log**

#### A. Via Debug Script
Buka `debug_jenis_kelamin.php`, scroll ke bagian **"3. PHP Error Log"**

#### B. Via File Log
Buka file: `logs/persiapan-operasi-debug.log`

**Cari log:**
```
=== PROCESS KONSULTASI ANESTESI DIPANGGIL ===
Request Method: POST
POST Data Count: 95
POST Keys: no_rawat, kode_paket, tanggal, jam_mulai, jenis_kelamin, ...

=== DEBUG PROCESS KONSULTASI ANESTESI ===
POST jenis_kelamin: Laki-laki  ← HARUS ADA INI
Variable jenis_kelamin: Laki-laki
POST menikah: Ya
...

Query Success: YES
Row Count: 1
Last Insert ID: 123
Is Update: NO

Data tersimpan - jenis_kelamin: Laki-laki  ← HARUS ADA INI
Data tersimpan - menikah: Ya
```

**Analisis:**
- ✅ Jika `POST jenis_kelamin: Laki-laki` → Data diterima server
- ✅ Jika `Data tersimpan - jenis_kelamin: Laki-laki` → Data tersimpan di database
- ❌ Jika `POST jenis_kelamin: NOT SET` → Data tidak dikirim dari form
- ❌ Jika `Data tersimpan - jenis_kelamin: NULL` → Data tidak tersimpan meskipun diterima

---

### 5. **Cek Database Langsung**

#### A. Via phpMyAdmin
1. Buka phpMyAdmin
2. Pilih database SIMRS
3. Jalankan query:

```sql
-- Cek record terbaru
SELECT id, no_rawat, jenis_kelamin, menikah, created_at 
FROM tbl_anestesi_konsultasi_anestesi 
ORDER BY id DESC 
LIMIT 5;
```

#### B. Cek Kolom
```sql
-- Cek apakah kolom jenis_kelamin ada
SHOW COLUMNS FROM tbl_anestesi_konsultasi_anestesi 
LIKE 'jenis_kelamin';
```

**Hasil yang diharapkan:**
```
Field          | Type        | Null | Key | Default | Extra
jenis_kelamin  | varchar(20) | YES  |     | NULL    |
```

---

## Kemungkinan Masalah dan Solusi

### Masalah 1: Kolom `jenis_kelamin` Tidak Ada di Database

**Gejala:**
- Error di PHP log: `Unknown column 'jenis_kelamin'`
- Query di phpMyAdmin: `SHOW COLUMNS` tidak menampilkan kolom

**Solusi:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_kelamin VARCHAR(20) DEFAULT NULL 
COMMENT 'Jenis kelamin: Laki-laki atau Perempuan';
```

---

### Masalah 2: Data Tidak Terkirim dari Form

**Gejala:**
- Console browser: `jenis_kelamin: NULL/KOSONG` (background merah)
- Network tab: tidak ada `jenis_kelamin` di payload
- PHP log: `POST jenis_kelamin: NOT SET`

**Penyebab:**
- Radio button tidak dipilih
- Name attribute salah
- JavaScript mencegah submit

**Solusi:**
1. Pastikan radio button dipilih
2. Cek HTML: `<input type="radio" name="jenis_kelamin" value="Laki-laki">`
3. Disable JavaScript sementara untuk test

---

### Masalah 3: Data Diterima Server Tapi Tidak Tersimpan

**Gejala:**
- Console browser: `jenis_kelamin: Laki-laki` (background kuning) ✓
- Network tab: ada `jenis_kelamin: Laki-laki` ✓
- PHP log: `POST jenis_kelamin: Laki-laki` ✓
- PHP log: `Data tersimpan - jenis_kelamin: NULL` ✗

**Penyebab:**
- Parameter mismatch di query
- Kolom tidak ada di INSERT statement
- Tipe data tidak cocok

**Solusi:**
1. Cek query INSERT di `process-konsultasi-anestesi.php`
2. Pastikan `jenis_kelamin` ada di daftar kolom
3. Pastikan ada placeholder `?` yang sesuai
4. Pastikan ada di array `execute()`

---

### Masalah 4: Query Berhasil Tapi Data NULL

**Gejala:**
- PHP log: `Query Success: YES` ✓
- PHP log: `Row Count: 1` ✓
- Database: kolom `jenis_kelamin` tetap NULL

**Penyebab:**
- Variable `$jenis_kelamin` kosong saat execute
- Urutan parameter salah

**Solusi:**
Cek di `process-konsultasi-anestesi.php` baris 261:
```php
$jenis_kelamin = $_POST['jenis_kelamin'] ?? null;

// Pastikan ada di execute
$stmt->execute([
    $no_rawat, $kode_paket, $tanggal, $jam_mulai, 
    ..., 
    $jenis_kelamin,  // ← HARUS ADA DI POSISI YANG BENAR
    $menikah, 
    ...
]);
```

---

## Checklist Debugging

Gunakan checklist ini untuk debugging sistematis:

### Frontend (Browser)
- [ ] Buka Developer Tools (F12)
- [ ] Pilih jenis kelamin di form
- [ ] Submit form
- [ ] Cek Console: ada log `jenis_kelamin: Laki-laki` dengan background kuning?
- [ ] Cek Network tab: ada `jenis_kelamin` di payload?
- [ ] Tidak ada error di Console?

### Backend (PHP)
- [ ] Cek file log: `logs/persiapan-operasi-debug.log`
- [ ] Ada log `POST jenis_kelamin: Laki-laki`?
- [ ] Ada log `Variable jenis_kelamin: Laki-laki`?
- [ ] Ada log `Query Success: YES`?
- [ ] Ada log `Data tersimpan - jenis_kelamin: Laki-laki`?
- [ ] Tidak ada error PDO?

### Database
- [ ] Buka phpMyAdmin
- [ ] Cek kolom `jenis_kelamin` ada?
- [ ] Cek tipe data `VARCHAR(20)` atau sejenisnya?
- [ ] Cek data terbaru: kolom `jenis_kelamin` terisi?

---

## Kontak Debug

Jika masih bermasalah setelah mengikuti panduan ini:

1. **Screenshot Console Browser** (tab Console dan Network)
2. **Copy PHP Error Log** (50 baris terakhir)
3. **Screenshot Database** (hasil query SELECT)
4. **Kirimkan semua informasi di atas**

---

## File Penting

- `views/form-konsultasi-anestesi.php` - Form HTML
- `assets/js/form-konsultasi-fix.js` - JavaScript validasi
- `process/process-konsultasi-anestesi.php` - Proses penyimpanan
- `debug_jenis_kelamin.php` - Script debugging
- `logs/persiapan-operasi-debug.log` - PHP error log

---

**Terakhir diupdate:** 2025-10-21 10:47
