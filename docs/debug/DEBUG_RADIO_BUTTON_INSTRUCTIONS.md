# 🔍 DEBUG INSTRUCTIONS - Radio Button Not Saving

## 📋 Problem
Data dari radio button tidak tersimpan:
- Jenis Kelamin
- Kebiasaan Merokok
- Kebiasaan Alkohol
- Emergency (Cito/Elektif)
- Penyakit-penyakit

---

## 🧪 Testing Steps

### **Step 1: Buka Form Konsultasi Anestesi**
1. Buka browser
2. Navigate ke form konsultasi anestesi
3. **Buka Developer Console** (F12 atau Ctrl+Shift+I)
4. Pilih tab **Console**

### **Step 2: Isi Form dan Submit**
1. Isi form dengan data
2. Pastikan pilih radio button:
   - Jenis Kelamin: Pilih salah satu
   - Merokok: Pilih salah satu
   - Alkohol: Pilih salah satu
   - Emergency: Pilih salah satu
   - Penyakit: Pilih beberapa
3. Klik tombol **"Simpan Konsultasi"**

### **Step 3: Cek Console Log**
Di console akan muncul:
```
=== FORM DATA DEBUG ===
jenis_kelamin: Laki-laki
merokok: Tidak
alkohol: Tidak
emergency: Tidak
asma: Tidak
diabetes: Tidak
======================
```

**❓ Pertanyaan:**
- Apakah data muncul di console?
- Apakah nilai sesuai dengan yang dipilih?

---

## 🔍 Scenario Testing

### **Scenario 1: Data Muncul di Console ✅**
**Artinya:** Form mengirim data dengan benar

**Next Step:** Cek PHP error log
```bash
# Lokasi error log (tergantung server):
# - XAMPP: C:\xampp\apache\logs\error.log
# - Laragon: C:\laragon\www\logs\error.log
```

**Cari baris:**
```
DEBUG - jenis_kelamin: Laki-laki
DEBUG - merokok: Tidak
DEBUG - alkohol: Tidak
DEBUG - emergency: Tidak
```

**❓ Pertanyaan:**
- Apakah data muncul di error log?
- Apakah nilai sama dengan console?

---

### **Scenario 2: Data TIDAK Muncul di Console ❌**
**Artinya:** Radio button tidak mengirim data

**Possible Causes:**
1. Radio button tidak ter-check
2. Attribute `name` salah
3. Form tidak submit dengan benar

**Fix:**
Cek HTML radio button, pastikan ada yang `checked`:
```html
<!-- ❌ WRONG - Tidak ada yang checked -->
<input type="radio" name="jenis_kelamin" value="Laki-laki"> Laki-laki
<input type="radio" name="jenis_kelamin" value="Wanita"> Wanita

<!-- ✅ CORRECT - Ada default checked -->
<input type="radio" name="jenis_kelamin" value="Laki-laki" checked> Laki-laki
<input type="radio" name="jenis_kelamin" value="Wanita"> Wanita
```

---

### **Scenario 3: Data Muncul di Console & Error Log, Tapi Tidak Tersimpan ❌**
**Artinya:** Problem di query INSERT/UPDATE

**Check:**
1. Buka database (phpMyAdmin atau MySQL Workbench)
2. Jalankan query:
```sql
SELECT jenis_kelamin, merokok, alkohol, emergency, asma, diabetes
FROM tbl_anestesi_konsultasi_anestesi
WHERE no_rawat = '1' AND kode_paket = '1'
ORDER BY id DESC LIMIT 1;
```

**❓ Pertanyaan:**
- Apakah data NULL?
- Apakah ada error di query?

**Possible Fix:**
- Cek nama kolom di database vs nama parameter di query
- Cek jumlah parameter (harus match dengan placeholder `?`)

---

## 📊 Debug Checklist

| Check | Status | Notes |
|-------|--------|-------|
| Radio button ter-check di form | ⬜ | Visual check |
| Data muncul di Console log | ⬜ | F12 Console |
| Data muncul di PHP error log | ⬜ | Check error.log |
| Data tersimpan di database | ⬜ | Check MySQL |
| Data muncul saat reload form | ⬜ | Refresh page |

---

## 🎯 Expected Result

### **Console Log (Browser):**
```javascript
=== FORM DATA DEBUG ===
jenis_kelamin: Wanita
merokok: Ya
alkohol: Sebanyak
emergency: Ya
asma: Ya
diabetes: Tidak
======================
```

### **Error Log (PHP):**
```
[14-Oct-2025 10:00:00] DEBUG - jenis_kelamin: Wanita
[14-Oct-2025 10:00:00] DEBUG - merokok: Ya
[14-Oct-2025 10:00:00] DEBUG - alkohol: Sebanyak
[14-Oct-2025 10:00:00] DEBUG - emergency: Ya
```

### **Database:**
```sql
jenis_kelamin | merokok | alkohol   | emergency | asma | diabetes
Wanita        | Ya      | Sebanyak  | Ya        | Ya   | Tidak
```

---

## 🚨 Common Issues

### **Issue 1: Radio Button Tidak Ter-check**
**Symptom:** Console log menunjukkan `null`
**Fix:** Tambahkan `checked` attribute pada default option

### **Issue 2: Attribute `name` Salah**
**Symptom:** Console log menunjukkan `null` untuk field tertentu
**Fix:** Pastikan `name` di HTML sama dengan `$_POST['name']` di PHP

### **Issue 3: Query Parameter Mismatch**
**Symptom:** Error "Parameter count mismatch"
**Fix:** Hitung jumlah `?` di query vs jumlah parameter di `execute()`

### **Issue 4: Kolom Database Tidak Ada**
**Symptom:** Error "Unknown column"
**Fix:** Tambahkan kolom di database atau ubah nama parameter

---

## 📝 Report Format

Setelah testing, laporkan hasil dengan format:

```
=== DEBUG REPORT ===
Date: 2025-10-14 10:00

1. Console Log:
   - jenis_kelamin: [VALUE atau NULL]
   - merokok: [VALUE atau NULL]
   - alkohol: [VALUE atau NULL]
   - emergency: [VALUE atau NULL]

2. PHP Error Log:
   - [COPY PASTE LOG LINES]

3. Database Check:
   - [SCREENSHOT atau COPY PASTE RESULT]

4. Issue Found:
   - [DESCRIBE ISSUE]
====================
```

---

## ✅ Success Criteria

Form berhasil jika:
1. ✅ Radio button ter-check di form
2. ✅ Data muncul di console log
3. ✅ Data muncul di PHP error log
4. ✅ Data tersimpan di database
5. ✅ Data muncul saat reload form

---

**Silakan test dan laporkan hasilnya!** 🔍
