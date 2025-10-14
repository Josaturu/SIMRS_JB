# 🔧 FIX: Cito/Elektif & Jenis Kelamin Not Saving

## 📋 Masalah yang Ditemukan

### **1. Field Cito/Elektif Tidak Tersimpan**
**Root Cause:**
- Field `jenisDiagnosa` (Cito/Elektif) ada di form HTML
- Tapi **TIDAK ADA** di process PHP dan database
- Data tidak pernah tersimpan

### **2. Jenis Kelamin Tidak Tersimpan**
**Root Cause:**
- Radio button tidak punya default `checked`
- Browser tidak mengirim data jika tidak ada yang checked

---

## ✅ Solusi yang Sudah Diterapkan

### **Fix 1: Tambah Kolom Database**
File SQL: `sql/add_jenis_diagnosa_column.sql`

**Jalankan SQL berikut di phpMyAdmin atau MySQL Workbench:**

```sql
-- Tambah kolom jenis_diagnosa
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
COMMENT 'Jenis diagnosa: Cito atau Elektif'
AFTER diagnosa_pra_operasi;

-- Verifikasi
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

### **Fix 2: Update Form HTML**
File: `views/form-konsultasi-anestesi.php`

**Before:**
```html
<input type="radio" name="jenisDiagnosa" value="Cita" required> Cito
<input type="radio" name="jenisDiagnosa" value="Efektif"> Efektif
```

**After:**
```php
<?php
$jenis_diagnosa = $konsul['jenis_diagnosa'] ?? 'Elektif';
?>
<input type="radio" name="jenisDiagnosa" value="Cito" 
    <?= $jenis_diagnosa == 'Cito' ? 'checked' : '' ?> required> Cito
<input type="radio" name="jenisDiagnosa" value="Elektif" 
    <?= $jenis_diagnosa == 'Elektif' ? 'checked' : '' ?>> Elektif
```

**Changes:**
- ✅ Fix typo: `"Cita"` → `"Cito"`
- ✅ Fix typo: `"Efektif"` → `"Elektif"`
- ✅ Add default checked: `Elektif`
- ✅ Load data dari database

### **Fix 3: Update Process PHP**
File: `process/process-konsultasi-anestesi.php`

**Added:**
```php
// Ambil data dari POST
$jenis_diagnosa = $_POST['jenisDiagnosa'] ?? 'Elektif';

// Tambah ke query INSERT
INSERT INTO tbl_anestesi_konsultasi_anestesi 
(..., diagnosa_pra_operasi, jenis_diagnosa, rencana_tindakan_operasi, ...)
VALUES (..., ?, ?, ?, ...)

// Tambah ke ON DUPLICATE KEY UPDATE
ON DUPLICATE KEY UPDATE
  ...
  diagnosa_pra_operasi = VALUES(diagnosa_pra_operasi),
  jenis_diagnosa = VALUES(jenis_diagnosa),
  rencana_tindakan_operasi = VALUES(rencana_tindakan_operasi),
  ...

// Tambah ke execute array
$stmt->execute([
  ..., $diagnosa_pra_operasi, $jenis_diagnosa, $rencana_tindakan_operasi, ...
]);
```

### **Fix 4: Jenis Kelamin Auto-Fill**
File: `views/form-konsultasi-anestesi.php`

**Added:**
```php
// JOIN dengan tabel pasien untuk ambil jenis kelamin
$query = "SELECT bo.*, p.jenis_kelamin, p.nama, ...
          FROM booking_operasi bo 
          LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien 
          WHERE ...";

// Simpan data pasien
$pasien = [
    'jenis_kelamin' => $booking['jenis_kelamin'] ?? 'Laki-laki',
    'kd_dokter' => $booking['kd_dokter'] ?? ''
];

// Load jenis kelamin dengan prioritas
$jk = $konsul['jenis_kelamin'] ?? $pasien['jenis_kelamin'] ?? 'Laki-laki';
```

**Priority:**
1. Data konsultasi (jika sudah pernah diisi)
2. Data pasien (dari tabel pasien)
3. Default (Laki-laki)

**Support Multiple Format:**
- `'L'` atau `'Laki-laki'` → Laki-laki
- `'P'` atau `'Wanita'` → Wanita

---

## 🧪 Testing Steps

### **Step 1: Jalankan SQL**
1. Buka phpMyAdmin
2. Pilih database SIMRS
3. Jalankan SQL dari file `sql/add_jenis_diagnosa_column.sql`
4. Verifikasi kolom `jenis_diagnosa` sudah ada

### **Step 2: Test Form Baru**
1. Buka form konsultasi anestesi untuk pasien baru
2. **Expected:**
   - ✅ Jenis Kelamin: Auto-fill sesuai data pasien
   - ✅ Cito/Elektif: Default "Elektif" ter-check
3. Pilih "Cito"
4. Submit form
5. **Expected:**
   - ✅ Notifikasi "Berhasil disimpan"
   - ✅ Tetap di form

### **Step 3: Verify Database**
```sql
SELECT 
    no_rawat, 
    jenis_kelamin, 
    diagnosa_pra_operasi,
    jenis_diagnosa,
    merokok,
    alkohol,
    emergency
FROM tbl_anestesi_konsultasi_anestesi
WHERE no_rawat = '1' AND kode_paket = '1'
ORDER BY id DESC LIMIT 1;
```

**Expected Result:**
```
jenis_kelamin | jenis_diagnosa | merokok | alkohol | emergency
Laki-laki     | Cito           | Tidak   | Tidak   | Tidak
```

### **Step 4: Test Form Edit**
1. Refresh halaman (reload form)
2. **Expected:**
   - ✅ Jenis Kelamin: Sesuai data tersimpan
   - ✅ Cito/Elektif: Sesuai data tersimpan ("Cito")
   - ✅ Merokok: Sesuai data tersimpan
   - ✅ Alkohol: Sesuai data tersimpan
3. Ubah "Cito" → "Elektif"
4. Submit
5. **Expected:**
   - ✅ Data terupdate ke "Elektif"

---

## 📊 Summary Perbaikan

| Field | Status Before | Status After | Notes |
|-------|---------------|--------------|-------|
| **Cito/Elektif** | ❌ Tidak tersimpan | ✅ Tersimpan | Kolom baru di DB |
| **Jenis Kelamin** | ❌ Tidak tersimpan | ✅ Auto-fill | Dari data pasien |
| **Merokok** | ❌ Tidak tersimpan | ✅ Tersimpan | Default checked |
| **Alkohol** | ❌ Tidak tersimpan | ✅ Tersimpan | Default checked |
| **Emergency** | ❌ Tidak tersimpan | ✅ Tersimpan | Default checked |
| **16 Penyakit** | ❌ Tidak tersimpan | ✅ Tersimpan | Default checked |

---

## 🚨 IMPORTANT: Jalankan SQL Dulu!

**SEBELUM testing, WAJIB jalankan SQL:**

```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

**Jika tidak, akan muncul error:**
```
Unknown column 'jenis_diagnosa' in 'field list'
```

---

## ✅ Checklist

- [ ] Jalankan SQL untuk tambah kolom `jenis_diagnosa`
- [ ] Verifikasi kolom sudah ada di database
- [ ] Test form baru (INSERT)
- [ ] Test form edit (UPDATE)
- [ ] Verifikasi data tersimpan di database
- [ ] Test jenis kelamin auto-fill dari data pasien

---

## 📝 Files Modified

1. ✅ `views/form-konsultasi-anestesi.php`
   - Add JOIN dengan tabel pasien
   - Fix Cito/Elektif radio button
   - Fix jenis kelamin auto-fill

2. ✅ `process/process-konsultasi-anestesi.php`
   - Add `$jenis_diagnosa` parameter
   - Add to INSERT query
   - Add to UPDATE query
   - Add to execute array

3. ✅ `sql/add_jenis_diagnosa_column.sql`
   - SQL script untuk tambah kolom

---

**Setelah jalankan SQL, semua field akan tersimpan dengan benar!** 🎉
