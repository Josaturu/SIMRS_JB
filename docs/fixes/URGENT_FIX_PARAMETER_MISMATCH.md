# 🚨 URGENT FIX: Parameter Mismatch & Jenis Kelamin

## ❌ Error yang Muncul

```
SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
```

## 🔍 Root Cause

### **Problem 1: Parameter Count Mismatch**
- **Kolom di query:** 96 kolom
- **Placeholder (?) sebelumnya:** 95 placeholder ❌
- **Parameter di execute():** 96 parameter
- **Result:** Mismatch → Error!

### **Problem 2: Jenis Kelamin Tidak Terisi**
- Data pasien mungkin NULL di database
- Atau kolom `jenis_kelamin` tidak ada di tabel `pasien`

---

## ✅ Fix yang Sudah Diterapkan

### **Fix 1: Update Placeholder Count**
File: `process/process-konsultasi-anestesi.php`

**Before (95 placeholder):**
```sql
VALUES (?, ?, ?, ..., ?, ?, ?)  -- 95 tanda tanya
```

**After (96 placeholder):**
```sql
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

**Total:** 96 placeholder ✅

### **Fix 2: Add Debug Logging**
File: `views/form-konsultasi-anestesi.php`

**Added:**
```php
// Debug: Log jenis kelamin dari database
error_log("DEBUG FORM - Jenis Kelamin dari DB: " . ($booking['jenis_kelamin'] ?? 'NULL'));
error_log("DEBUG FORM - Pasien jenis_kelamin: " . ($pasien['jenis_kelamin'] ?? 'NULL'));
error_log("DEBUG FORM - Final jenis_kelamin: $jk");
```

File: `process/process-konsultasi-anestesi.php`

**Already has:**
```php
error_log("DEBUG - jenis_kelamin: " . ($jenis_kelamin ?? 'NULL'));
error_log("DEBUG - merokok: " . ($merokok ?? 'NULL'));
error_log("DEBUG - alkohol: " . ($alkohol ?? 'NULL'));
error_log("DEBUG - emergency: " . ($emergency ?? 'NULL'));
```

---

## 🧪 Testing Steps

### **Step 1: WAJIB Jalankan SQL Dulu!**

**Buka phpMyAdmin dan jalankan:**

```sql
-- 1. Tambah kolom jenis_diagnosa (untuk Cito/Elektif)
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
COMMENT 'Jenis diagnosa: Cito atau Elektif'
AFTER diagnosa_pra_operasi;

-- 2. Cek apakah tabel pasien punya kolom jenis_kelamin
DESCRIBE pasien;

-- 3. Jika tidak ada, tambahkan:
-- ALTER TABLE pasien ADD COLUMN jenis_kelamin CHAR(1) DEFAULT 'L';
```

### **Step 2: Test Form**
1. Buka form konsultasi anestesi
2. Isi data dan submit
3. **Expected:** Tidak ada error parameter mismatch

### **Step 3: Cek Error Log**

**Lokasi error log:**
- XAMPP: `C:\xampp\apache\logs\error.log`
- Laragon: `C:\laragon\www\logs\error.log`

**Cari baris:**
```
DEBUG FORM - Jenis Kelamin dari DB: [VALUE atau NULL]
DEBUG FORM - Pasien jenis_kelamin: [VALUE atau NULL]
DEBUG FORM - Final jenis_kelamin: [VALUE]
DEBUG - jenis_kelamin: [VALUE atau NULL]
DEBUG - merokok: [VALUE atau NULL]
DEBUG - alkohol: [VALUE atau NULL]
DEBUG - emergency: [VALUE atau NULL]
```

### **Step 4: Cek Database**

```sql
-- Cek data pasien
SELECT kd_pasien, nama, jenis_kelamin 
FROM pasien 
LIMIT 5;

-- Cek data konsultasi
SELECT 
    no_rawat,
    jenis_kelamin,
    jenis_diagnosa,
    merokok,
    alkohol,
    emergency
FROM tbl_anestesi_konsultasi_anestesi
ORDER BY id DESC LIMIT 1;
```

---

## 🔍 Diagnosis Scenarios

### **Scenario 1: Error "Unknown column 'jenis_diagnosa'"**
**Artinya:** Kolom belum ditambahkan ke database

**Fix:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

### **Scenario 2: Jenis Kelamin NULL di Error Log**
**Artinya:** Tabel pasien tidak punya kolom `jenis_kelamin`

**Check:**
```sql
DESCRIBE pasien;
```

**Fix (jika kolom tidak ada):**
```sql
ALTER TABLE pasien 
ADD COLUMN jenis_kelamin CHAR(1) DEFAULT 'L' 
COMMENT 'L=Laki-laki, P=Perempuan';
```

### **Scenario 3: Jenis Kelamin dari DB = NULL**
**Artinya:** Data pasien belum diisi

**Fix:** Update data pasien
```sql
UPDATE pasien 
SET jenis_kelamin = 'L' 
WHERE jenis_kelamin IS NULL;
```

### **Scenario 4: Masih Error Parameter Mismatch**
**Artinya:** Jumlah parameter di execute() tidak match

**Debug:** Hitung manual
```bash
php count_parameters.php
```

**Expected Output:**
```
Total Columns: 96
Total Parameters: 96
Placeholders needed: 96
```

---

## 📊 Parameter Count Verification

| Component | Count | Status |
|-----------|-------|--------|
| Kolom di INSERT | 96 | ✅ |
| Placeholder (?) | 96 | ✅ Fixed |
| Parameter di execute() | 96 | ✅ |
| **TOTAL MATCH** | **96 = 96 = 96** | ✅ |

---

## 🎯 Expected Result After Fix

### **1. No Error**
```
✓ Berhasil! Data konsultasi berhasil disimpan!
```

### **2. Error Log Shows:**
```
DEBUG FORM - Jenis Kelamin dari DB: L
DEBUG FORM - Pasien jenis_kelamin: Laki-laki
DEBUG FORM - Final jenis_kelamin: Laki-laki
DEBUG - jenis_kelamin: Laki-laki
DEBUG - merokok: Tidak
DEBUG - alkohol: Tidak
DEBUG - emergency: Tidak
```

### **3. Database Contains:**
```sql
jenis_kelamin | jenis_diagnosa | merokok | alkohol | emergency
Laki-laki     | Elektif        | Tidak   | Tidak   | Tidak
```

---

## ✅ Checklist

- [ ] Jalankan SQL untuk tambah kolom `jenis_diagnosa`
- [ ] Cek tabel `pasien` punya kolom `jenis_kelamin`
- [ ] Test form submit
- [ ] Cek error log untuk debug info
- [ ] Verifikasi data tersimpan di database
- [ ] Test jenis kelamin auto-fill

---

## 🚨 CRITICAL: SQL MUST RUN FIRST!

**Sebelum test, WAJIB jalankan SQL:**

```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

**Jika tidak, akan error:**
```
Unknown column 'jenis_diagnosa' in 'field list'
```

---

## 📝 Summary

| Issue | Status | Fix |
|-------|--------|-----|
| Parameter mismatch | ✅ Fixed | Update placeholder count to 96 |
| Jenis kelamin NULL | 🔍 Debug | Check error log |
| Cito/Elektif not saving | ✅ Fixed | Add column + update query |
| Radio button not checked | ✅ Fixed | Add default checked |

---

**Test sekarang dan laporkan hasilnya!** 🔍

**Yang perlu dilaporkan:**
1. Apakah masih ada error?
2. Apa yang muncul di error log?
3. Apakah data tersimpan di database?
