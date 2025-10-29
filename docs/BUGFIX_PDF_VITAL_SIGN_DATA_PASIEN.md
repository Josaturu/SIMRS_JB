# Bugfix: Data Pasien Tidak Tampil di PDF Vital Sign

**Tanggal:** 29 Oktober 2025  
**File:** `views/form-vital-sign.php`, `process/pdf/pdf-vital-sign.php`  
**Issue:** No. Rekam Medis, Nama Pasien, dan Umur tidak tampil di PDF

---

## 🐛 Problem

### **Symptoms:**
- PDF vital sign terbuka tapi data pasien kosong
- No. Rekam Medis: kosong
- Nama Pasien: kosong
- Umur: kosong

### **Root Cause:**
1. **Field name mismatch** di JavaScript
   - JavaScript menggunakan: `nm_pasien`, `no_rkm_medis`
   - Database query menggunakan: `nama_pasien`, `kode_rekam_medis`

2. **Missing fields** di query
   - Query tidak select field `jk`, `tgl_lahir`, `umur`

---

## ✅ Solution

### **1. Fix Query Pasien (form-vital-sign.php)**

**Before:**
```php
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE ...";
```

**After:**
```php
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.jk, p.tgl_lahir,
                  TIMESTAMPDIFF(YEAR, p.tgl_lahir, CURDATE()) AS umur
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE ...";
```

**Changes:**
- ✅ Tambah `p.jk` (jenis kelamin)
- ✅ Tambah `p.tgl_lahir` (tanggal lahir)
- ✅ Tambah `TIMESTAMPDIFF(YEAR, p.tgl_lahir, CURDATE()) AS umur` (hitung umur)

---

### **2. Fix JavaScript Data Pasien (form-vital-sign.php)**

**Before:**
```javascript
pasien: {
    nama: '<?= htmlspecialchars($pasien['nm_pasien'] ?? '') ?>',
    no_rkm_medis: '<?= htmlspecialchars($pasien['no_rkm_medis'] ?? '') ?>',
    jk: '<?= htmlspecialchars($pasien['jk'] ?? '') ?>',
    umur: '<?= htmlspecialchars($pasien['umur'] ?? '') ?>'
}
```

**After:**
```javascript
pasien: {
    nama: '<?= htmlspecialchars($pasien['nama_pasien'] ?? '') ?>',
    no_rkm_medis: '<?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '') ?>',
    jk: '<?= htmlspecialchars($pasien['jk'] ?? '') ?>',
    umur: '<?= htmlspecialchars($pasien['umur'] ?? '') ?>',
    tgl_lahir: '<?= htmlspecialchars($pasien['tgl_lahir'] ?? '') ?>'
}
```

**Changes:**
- ✅ `nm_pasien` → `nama_pasien`
- ✅ `no_rkm_medis` → `kode_rekam_medis`
- ✅ Tambah `tgl_lahir`

---

### **3. Fix PDF Display (pdf-vital-sign.php)**

**Before:**
```php
<td><?= htmlspecialchars($pasien['umur']) ?></td>
```

**After:**
```php
<td><?= htmlspecialchars($pasien['umur']) ?> tahun</td>
```

**Changes:**
- ✅ Tambah " tahun" untuk format yang lebih baik

---

### **4. Update Nama RS (pdf-vital-sign.php)**

**Before:**
```html
<div class="subtitle">Rumah Sakit Josaturu Blora</div>
```

**After:**
```html
<div class="subtitle">Rumah Sakit Prasetya Bunda</div>
```

**Changes:**
- ✅ Update nama RS di header
- ✅ Update nama RS di footer

---

## 🔍 Field Mapping

### **Database → JavaScript → PDF**

| Database Field | JavaScript Key | PDF Display |
|----------------|----------------|-------------|
| `nama_pasien` (alias dari `p.nama`) | `nama` | Nama Pasien |
| `kode_rekam_medis` | `no_rkm_medis` | No. Rekam Medis |
| `jk` | `jk` | Jenis Kelamin (L/P) |
| `umur` (calculated) | `umur` | Umur (tahun) |
| `tgl_lahir` | `tgl_lahir` | - |

---

## 📊 Query Structure

### **Tabel yang Di-join:**

```sql
booking_operasi bo
LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
```

### **Fields yang Diambil:**

```sql
SELECT 
    bo.*,                                              -- Semua field booking_operasi
    p.nama AS nama_pasien,                            -- Nama pasien
    p.kode_rekam_medis,                               -- No. Rekam Medis
    p.jk,                                             -- Jenis Kelamin
    p.tgl_lahir,                                      -- Tanggal Lahir
    TIMESTAMPDIFF(YEAR, p.tgl_lahir, CURDATE()) AS umur  -- Umur (calculated)
FROM booking_operasi bo
LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
WHERE bo.no_rawat = ? 
  AND bo.kode_paket = ? 
  AND bo.tanggal = ? 
  AND bo.jam_mulai = ?
```

---

## ✅ Testing

### **Test 1: Data Pasien Tampil**
1. Buka form vital sign
2. Klik "Export PDF"
3. **Expected:**
   - ✅ No. Rekam Medis: 123456
   - ✅ Nama Pasien: John Doe
   - ✅ Jenis Kelamin: Laki-laki
   - ✅ Umur: 35 tahun

### **Test 2: Jenis Kelamin**
1. Pasien dengan jk = 'L'
2. **Expected:** Tampil "Laki-laki"
3. Pasien dengan jk = 'P'
4. **Expected:** Tampil "Perempuan"

### **Test 3: Umur Calculation**
1. Pasien lahir 1990-01-01
2. Tahun sekarang 2025
3. **Expected:** Umur: 35 tahun

### **Test 4: Nama RS**
1. Export PDF
2. **Expected:**
   - ✅ Header: "Rumah Sakit Prasetya Bunda"
   - ✅ Footer: "Rumah Sakit Prasetya Bunda"

---

## 📝 Files Modified

1. **`views/form-vital-sign.php`**
   - Line 17-18: Update query pasien (tambah fields)
   - Line 961-965: Fix field names di JavaScript

2. **`process/pdf/pdf-vital-sign.php`**
   - Line 260: Update nama RS di header
   - Line 293: Tambah " tahun" di umur
   - Line 379: Update nama RS di footer

---

## 🔧 Common Issues

### Issue: Umur masih kosong
**Cause:** Field `tgl_lahir` NULL di database  
**Solution:** Pastikan data pasien memiliki tanggal lahir

### Issue: Nama masih kosong
**Cause:** JOIN gagal (kd_pasien tidak match)  
**Solution:** Cek data di tabel `booking_operasi` dan `pasien`

### Issue: Jenis Kelamin tidak tampil
**Cause:** Field `jk` NULL atau bukan 'L'/'P'  
**Solution:** Validasi data di tabel `pasien`

---

## 📌 Notes

1. **Umur dihitung otomatis** dari `tgl_lahir` menggunakan `TIMESTAMPDIFF`
2. **Jenis Kelamin** dikonversi: 'L' → "Laki-laki", 'P' → "Perempuan"
3. **Nama RS** sudah diupdate ke "Rumah Sakit Prasetya Bunda"
4. **Field names** harus konsisten antara query, JavaScript, dan PDF

---

**Status:** ✅ Fixed  
**Impact:** Data pasien sekarang tampil lengkap di PDF  
**Tested:** ✅ Passed
