# ✅ QUICK TEST CHECKLIST

## 📋 Status SQL

- [x] SQL untuk `jenis_diagnosa` sudah dijalankan
- [ ] Verifikasi kolom sudah ada di database
- [ ] Test form submit
- [ ] Cek error log
- [ ] Verifikasi data tersimpan

---

## 🧪 Test Steps

### **1. Verifikasi Kolom di Database**

**Jalankan di phpMyAdmin:**
```sql
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

**Cari baris:**
```
jenis_diagnosa | varchar(20) | YES | | Elektif
```

**Status:** [ ] Kolom ada ✅ / [ ] Kolom tidak ada ❌

---

### **2. Test Form Submit**

1. Buka browser: `http://localhost:8000/index.php?page=konsultasi-anestesi&no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00`
2. Isi form:
   - Jenis Kelamin: Pilih salah satu
   - Cito/Elektif: Pilih salah satu
   - Merokok: Pilih salah satu
   - Alkohol: Pilih salah satu
3. Klik **"Simpan Konsultasi"**

**Expected Result:**
```
✓ Berhasil! Data konsultasi berhasil disimpan!
```

**Actual Result:**
- [ ] Berhasil ✅
- [ ] Error ❌ (tulis error di bawah)

**Error Message (jika ada):**
```
[Paste error message here]
```

---

### **3. Cek Error Log**

**Lokasi:**
- XAMPP: `C:\xampp\apache\logs\error.log`
- Laragon: `C:\laragon\www\logs\error.log`

**Buka file dan cari baris terakhir dengan "DEBUG":**

**Expected:**
```
[14-Oct-2025 10:40:00] DEBUG FORM - Jenis Kelamin dari DB: L
[14-Oct-2025 10:40:00] DEBUG FORM - Pasien jenis_kelamin: Laki-laki
[14-Oct-2025 10:40:00] DEBUG FORM - Final jenis_kelamin: Laki-laki
[14-Oct-2025 10:40:00] DEBUG - jenis_kelamin: Laki-laki
[14-Oct-2025 10:40:00] DEBUG - merokok: Tidak
[14-Oct-2025 10:40:00] DEBUG - alkohol: Tidak
[14-Oct-2025 10:40:00] DEBUG - emergency: Tidak
```

**Actual (paste dari error.log):**
```
[Paste log lines here]
```

---

### **4. Verifikasi Data di Database**

**Jalankan di phpMyAdmin:**
```sql
SELECT 
    no_rawat,
    jenis_kelamin,
    jenis_diagnosa,
    merokok,
    alkohol,
    emergency,
    asma,
    diabetes
FROM tbl_anestesi_konsultasi_anestesi
WHERE no_rawat = '1' AND kode_paket = '1'
ORDER BY id DESC LIMIT 1;
```

**Expected Result:**
```
jenis_kelamin | jenis_diagnosa | merokok | alkohol | emergency | asma | diabetes
Laki-laki     | Elektif        | Tidak   | Tidak   | Tidak     | Tidak| Tidak
```

**Actual Result:**
```
[Paste query result here]
```

---

### **5. Test Form Reload (Edit Mode)**

1. Refresh halaman form (F5)
2. **Expected:**
   - Jenis Kelamin: Sesuai data tersimpan
   - Cito/Elektif: Sesuai data tersimpan
   - Merokok: Sesuai data tersimpan
   - Alkohol: Sesuai data tersimpan

**Status:**
- [ ] Semua field terisi dengan benar ✅
- [ ] Ada field yang kosong ❌

**Field yang kosong (jika ada):**
```
[List field yang kosong]
```

---

## 🚨 Common Issues & Solutions

### **Issue 1: Error "Unknown column 'jenis_diagnosa'"**
**Solution:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

### **Issue 2: Error "Parameter mismatch"**
**Solution:** Sudah diperbaiki di code (96 parameter)

### **Issue 3: Jenis Kelamin NULL di error log**
**Check:**
```sql
-- Cek tabel pasien
DESCRIBE pasien;

-- Cek data pasien
SELECT kd_pasien, nama, jenis_kelamin 
FROM pasien 
WHERE kd_pasien = '1';
```

**Solution (jika kolom tidak ada):**
```sql
ALTER TABLE pasien 
ADD COLUMN jenis_kelamin CHAR(1) DEFAULT 'L';
```

**Solution (jika data NULL):**
```sql
UPDATE pasien 
SET jenis_kelamin = 'L' 
WHERE jenis_kelamin IS NULL;
```

---

## 📊 Final Checklist

- [ ] Kolom `jenis_diagnosa` ada di database
- [ ] Form submit berhasil tanpa error
- [ ] Error log menunjukkan data terkirim
- [ ] Data tersimpan di database
- [ ] Form reload menampilkan data dengan benar
- [ ] Jenis kelamin auto-fill dari data pasien

---

## 📝 Test Report Template

**Date:** 2025-10-14  
**Time:** 10:40

**Test Results:**
1. SQL Execution: [ ] Success / [ ] Failed
2. Form Submit: [ ] Success / [ ] Failed
3. Data Saved: [ ] Yes / [ ] No
4. Form Reload: [ ] Correct / [ ] Incorrect

**Issues Found:**
```
[Describe any issues]
```

**Error Messages:**
```
[Paste error messages]
```

**Error Log Output:**
```
[Paste relevant log lines]
```

**Database Query Result:**
```
[Paste query result]
```

---

## ✅ Success Criteria

Form dianggap berhasil jika:
1. ✅ No error saat submit
2. ✅ Notifikasi "Berhasil disimpan" muncul
3. ✅ Error log menunjukkan data terkirim
4. ✅ Data tersimpan di database
5. ✅ Form reload menampilkan data dengan benar

---

**Silakan test dan isi checklist di atas!** 📝
