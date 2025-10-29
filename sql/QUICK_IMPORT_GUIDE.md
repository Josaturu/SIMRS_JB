# Quick Import Guide - Database Merged
**File**: `c:\FOLDER RIZKI\dbanestesi_merged_complete.sql`  
**Size**: 42.7 KB  
**Date**: 28 Oktober 2025, 10:48 WIB

---

## ✅ File Generated Successfully!

File SQL lengkap sudah berhasil dibuat dengan **10 tabel**:

### 📋 Tabel List:
1. ✅ `booking_operasi` (base table)
2. ✅ `pasien` (base table)
3. ✅ `tbl_anestesi_catatan_anestesi` ⭐ **TERBARU dari Downloads**
4. ✅ `tbl_anestesi_informed_consent_anestesi` ⭐ **TERBARU dari Downloads**
5. ✅ `tbl_anestesi_kamar_pemulihan`
6. ✅ `tbl_anestesi_keselamatan_operasi`
7. ✅ `tbl_anestesi_konsultasi_anestesi` ⭐ **TERBARU dari Downloads**
8. ✅ `tbl_anestesi_persiapan_operasi`
9. ✅ `tbl_anestesi_vital_pemulihan`
10. ✅ `tbl_anestesi_vital_sign`

---

## 🚀 Cara Import (3 Langkah)

### Step 1: BACKUP Database (WAJIB!)

```bash
# Buka CMD/PowerShell
cd C:\xampp\mysql\bin

# Backup database
.\mysqldump.exe -u root -p dbanestesi > c:\backup_dbanestesi_20251028.sql
```

**Tekan Enter, masukkan password MySQL**

---

### Step 2: Drop & Recreate Database

```sql
-- Buka MySQL
mysql -u root -p

-- Di MySQL prompt:
DROP DATABASE IF EXISTS dbanestesi;
CREATE DATABASE dbanestesi;
USE dbanestesi;
```

---

### Step 3: Import File Merged

```sql
-- Masih di MySQL prompt:
SOURCE c:/FOLDER RIZKI/dbanestesi_merged_complete.sql;
```

**⏱️ Proses import ~5-10 detik**

---

## ✅ Verification (Wajib Cek!)

Setelah import, jalankan query ini untuk verify:

```sql
-- 1. Cek jumlah tabel (harus 10)
SHOW TABLES FROM dbanestesi;

-- 2. Cek struktur tbl_anestesi_konsultasi_anestesi (harus ada field sedasi)
DESCRIBE dbanestesi.tbl_anestesi_konsultasi_anestesi;

-- 3. Cek field sedasi ada
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME = 'sedasi';
-- Hasil: harus muncul 1 row (sedasi | text)

-- 4. Cek field lain_lain_diagnosis ada
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME = 'lain_lain_diagnosis';
-- Hasil: harus muncul 1 row (lain_lain_diagnosis | text)

-- 5. Cek field checkbox_confirm di informed_consent
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_informed_consent_anestesi'
  AND COLUMN_NAME = 'checkbox_confirm';
-- Hasil: harus muncul 1 row (checkbox_confirm | varchar)

-- 6. Count data test
SELECT 
    'booking_operasi' as tabel, COUNT(*) as total 
FROM booking_operasi
UNION ALL
SELECT 'pasien', COUNT(*) FROM pasien
UNION ALL
SELECT 'konsultasi_anestesi', COUNT(*) FROM tbl_anestesi_konsultasi_anestesi
UNION ALL
SELECT 'informed_consent', COUNT(*) FROM tbl_anestesi_informed_consent_anestesi
UNION ALL
SELECT 'catatan_anestesi', COUNT(*) FROM tbl_anestesi_catatan_anestesi;
```

**Expected Result:**
```
booking_operasi         : 2-3 rows
pasien                  : 1-2 rows  
konsultasi_anestesi     : 1 row
informed_consent        : 1 row
catatan_anestesi        : 1 row
```

---

## 🎯 Fitur Baru Setelah Import

### 1. **Form Konsultasi Anestesi**
- ✅ Field `sedasi` TEXT (untuk catatan sedasi)
- ✅ Field `lain_lain_diagnosis` TEXT (renamed dari `rekomendasi_anestesi`)
- ✅ Field `jalan_nafas_keterangan` VARCHAR(255)
- ✅ Field `has_pengobatan` ENUM('Ya','Tidak')
- ✅ Field `jenis_diagnosa` VARCHAR(20) DEFAULT 'Elektif'

### 2. **Form Informed Consent Anestesi**
- ✅ Field `status_fisik` TEXT
- ✅ Field `prognosis` TEXT
- ✅ Field `alternatif_resiko` TEXT
- ✅ Field `lain_lain` TEXT
- ✅ Field `checkbox_confirm` VARCHAR(20)

### 3. **Form Catatan Sedasi/Anestesi**
- ✅ COMMENT lengkap pada semua field (untuk dokumentasi)
- ✅ Field `posisi` TEXT (comma separated: SUPINE, LITHOTOMI, PRONE, LATERAL)
- ✅ ENGINE COMMENT: 'Tabel catatan sedasi dan anestesi'

---

## 📝 Update Application Code

Setelah import database, perlu update beberapa file PHP:

### File yang Perlu Diupdate:

#### 1. `views/form-konsultasi-anestesi.php`
**Update field name:**
- ❌ OLD: `rekomendasi_anestesi`
- ✅ NEW: `lain_lain_diagnosis`

```php
// Contoh perubahan:
// OLD:
<textarea name="rekomendasi_anestesi">

// NEW:
<textarea name="lain_lain_diagnosis">
```

#### 2. `process/process-konsultasi-anestesi.php`
**Update INSERT/UPDATE query:**
```php
// Tambahkan field baru:
$sedasi = $_POST['sedasi'] ?? '';
$lain_lain_diagnosis = $_POST['lain_lain_diagnosis'] ?? '';
$jalan_nafas_keterangan = $_POST['jalan_nafas_keterangan'] ?? '';
```

#### 3. `process/pdf/pdf-konsultasi-anestesi.php`
**Update field mapping untuk PDF:**
```php
$sedasi = $data['sedasi'] ?? '';
$lain_lain_diagnosis = $data['lain_lain_diagnosis'] ?? '';
```

---

## 🔄 Rollback Plan (Jika Ada Masalah)

Jika import gagal atau ada masalah:

```bash
# Restore dari backup
mysql -u root -p

# Di MySQL:
DROP DATABASE IF EXISTS dbanestesi;
CREATE DATABASE dbanestesi;
USE dbanestesi;
SOURCE c:/backup_dbanestesi_20251028.sql;
```

---

## 🧪 Testing Checklist

Setelah import, test semua form:

- [ ] **Form Konsultasi Anestesi**
  - [ ] Buka form
  - [ ] Isi semua field termasuk `sedasi`
  - [ ] Save data
  - [ ] Cek data tersimpan di database
  - [ ] Generate PDF
  
- [ ] **Form Informed Consent**
  - [ ] Buka form
  - [ ] Isi semua field termasuk `status_fisik`, `prognosis`, `alternatif_resiko`
  - [ ] Check checkbox confirmation
  - [ ] Save data
  - [ ] Generate PDF
  
- [ ] **Form Catatan Sedasi/Anestesi**
  - [ ] Buka form
  - [ ] Isi data vital signs
  - [ ] Pilih posisi pasien (SUPINE, LITHOTOMI, dll)
  - [ ] Save data
  - [ ] Generate PDF

---

## 📊 Database Statistics

**After Import:**
```
Total Tables     : 10
Total Columns    : ~200+
Test Data        : ~5-7 records
Indexes          : ~15
Foreign Keys     : ~6
```

---

## ⚡ Quick Commands Reference

```bash
# Cek MySQL service
net start MySQL

# Login MySQL
mysql -u root -p

# Show databases
SHOW DATABASES;

# Use database
USE dbanestesi;

# Show tables
SHOW TABLES;

# Describe table structure
DESCRIBE tbl_anestesi_konsultasi_anestesi;

# Count records
SELECT COUNT(*) FROM tbl_anestesi_konsultasi_anestesi;

# Exit MySQL
EXIT;
```

---

## 🆘 Troubleshooting

### Problem 1: ERROR 1064 (Syntax Error)
**Solution:**  
File SQL corrupt, download ulang atau regenerate

### Problem 2: ERROR 1062 (Duplicate Entry)
**Solution:**  
Database sudah ada data, drop database dulu:
```sql
DROP DATABASE dbanestesi;
CREATE DATABASE dbanestesi;
```

### Problem 3: ERROR 1452 (Foreign Key Constraint)
**Solution:**  
Import ulang dari awal, pastikan urutan tabel benar

### Problem 4: Field 'sedasi' doesn't exist
**Solution:**  
Import belum berhasil, cek file SQL atau import ulang

---

## 📞 Support

Jika ada masalah:
1. Cek error message di MySQL
2. Verifikasi file SQL tidak corrupt
3. Pastikan MySQL version compatible (5.7+)
4. Check backup file exists

---

## ✅ Summary

**Before Import:**
- Database lama dengan struktur berbeda
- Missing fields: `sedasi`, `lain_lain_diagnosis`, `checkbox_confirm`, dll

**After Import:**
- ✅ Database terbaru dengan semua field
- ✅ 10 tabel dengan struktur lengkap
- ✅ Test data ready
- ✅ Indexes & foreign keys configured
- ✅ Ready for production

---

**Created**: 28 Oktober 2025, 10:48 WIB  
**File**: `c:\FOLDER RIZKI\dbanestesi_merged_complete.sql`  
**Status**: ✅ READY TO IMPORT

**NEXT ACTION: BACKUP DATABASE LAMA → IMPORT FILE BARU → VERIFY → TEST FORMS** 🚀
