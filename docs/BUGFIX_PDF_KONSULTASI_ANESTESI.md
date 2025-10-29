# Bugfix: Undefined Array Key "rekomendasi_anestesi" di PDF Konsultasi Anestesi

**Tanggal:** 29 Oktober 2025  
**File:** `process/pdf/pdf-konsultasi-anestesi.php`  
**Error:** Warning: Undefined array key "rekomendasi_anestesi" on line 418

---

## 🐛 Problem

### **Error Message:**
```
Warning: Undefined array key "rekomendasi_anestesi" in 
C:\FOLDER RIZKI\SIMRS_JB\process\pdf\pdf-konsultasi-anestesi.php on line 418
```

### **Root Cause:**
Field `rekomendasi_anestesi` **tidak ada** di tabel database `tbl_anestesi_konsultasi_anestesi`.

---

## 📊 Struktur Database Aktual

### **Tabel: `tbl_anestesi_konsultasi_anestesi`**

Field yang relevan:
```sql
CREATE TABLE `tbl_anestesi_konsultasi_anestesi` (
  ...
  `asa_status` varchar(20) DEFAULT NULL,
  `emergency` varchar(50) DEFAULT NULL,
  `lain_lain_diagnosis` text DEFAULT NULL,
  `saran` text DEFAULT NULL,                    -- ✅ Field yang benar
  `puasa_mulai_jam` time DEFAULT NULL,
  `puasa_mulai_tanggal` date DEFAULT NULL,
  `rencana_tiba_jam` time DEFAULT NULL,
  `rencana_tiba_tanggal` date DEFAULT NULL,
  `rencana_operasi_jam` time DEFAULT NULL,
  `rencana_operasi_tanggal` date DEFAULT NULL,
  `anestesi_umum` text DEFAULT NULL,
  `regional_anestesi` text DEFAULT NULL,
  `kombinasi_anestesi` text DEFAULT NULL,
  `sedasi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Catatan:**
- ❌ Field `rekomendasi_anestesi` **TIDAK ADA**
- ✅ Field `saran` **ADA** (ini yang digunakan untuk rekomendasi)

---

## ✅ Solution

### **Fix di PDF (pdf-konsultasi-anestesi.php)**

**Before (Line 418):**
```php
<div class="info-box">
    <strong>Rekomendasi Anestesi:</strong> <?= displayValue($konsul['rekomendasi_anestesi']) ?>
</div>
```

**After:**
```php
<div class="info-box">
    <strong>Rekomendasi Anestesi:</strong> <?= displayValue($konsul['saran'] ?? '') ?>
</div>
```

**Changes:**
- ✅ `$konsul['rekomendasi_anestesi']` → `$konsul['saran']`
- ✅ Tambah `?? ''` untuk fallback jika field kosong

---

## 🔍 Field Mapping

### **Database → PDF Display**

| Database Field | PDF Label | Type |
|----------------|-----------|------|
| `saran` | Rekomendasi Anestesi | text |
| `anestesi_umum` | Anestesi Umum | text |
| `regional_anestesi` | Regional Anestesi | text |
| `kombinasi_anestesi` | Kombinasi Anestesi | text |
| `sedasi` | Sedasi | text |

---

## 📝 Sample Data

### **Database:**
```sql
INSERT INTO `tbl_anestesi_konsultasi_anestesi` (..., `saran`, ...) VALUES
(..., 'i qwoi q qi iqn inqijndiqjni d', ...);
```

### **PDF Output:**
```
┌────────────────────────────────────────────┐
│ Rekomendasi Anestesi:                      │
│ i qwoi q qi iqn inqijndiqjni d             │
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│ Rekomendasi Tindakan Anestesi              │
├────────────────────────────────────────────┤
│ Anestesi Umum      : LMA                   │
│ Regional Anestesi  : Epidural              │
│ Kombinasi Anestesi : Anestesi Umum + ...   │
│ Sedasi             : Sedasi Dalam          │
└────────────────────────────────────────────┘
```

---

## 🧪 Testing

### **Test 1: Data dengan Saran**
1. Pastikan ada data di field `saran`
2. Buka PDF konsultasi anestesi
3. **Expected:**
   - ✅ Section "Rekomendasi Anestesi" tampil
   - ✅ Isi sesuai field `saran`
   - ✅ Tidak ada warning

### **Test 2: Data Tanpa Saran**
1. Field `saran` NULL atau kosong
2. Buka PDF
3. **Expected:**
   - ✅ Section "Rekomendasi Anestesi" tampil
   - ✅ Isi: "-" (dari displayValue)
   - ✅ Tidak ada warning

### **Test 3: Rekomendasi Tindakan**
1. Pastikan ada data di `anestesi_umum`, `regional_anestesi`, dll
2. Buka PDF
3. **Expected:**
   - ✅ Tabel "Rekomendasi Tindakan Anestesi" tampil
   - ✅ Semua field tampil dengan benar

---

## 📋 All Fields in Database

### **Fields yang Digunakan di PDF:**

```php
// Info Dasar
$konsul['tanggal_konsul']
$konsul['jam_konsul']
$konsul['tinggi_badan']
$konsul['berat_badan']
$konsul['ruang_perawatan']
$konsul['dokter_merawat']
$konsul['diagnosa_pra_operasi']
$konsul['rencana_tindakan_operasi']
$konsul['kondisi_khusus']

// Anamnesa
$konsul['jam_visit']
$konsul['menikah']
$konsul['jenis_kelamin']
$konsul['merokok']
$konsul['alkohol']
$konsul['has_pengobatan']
$konsul['pengobatan']
$konsul['has_alergi_obat']
$konsul['daftar_alergi_obat']
$konsul['alergi_makanan']
$konsul['alergi_lateks']

// Riwayat Penyakit
$konsul['asma']
$konsul['hepatitis']
$konsul['sesak_nafas']
$konsul['pingsan']
$konsul['sumbatan_jalan_nafas']
$konsul['diabetes']
$konsul['tidur_mengorok']
$konsul['anemia']
$konsul['serangan_jantung']
$konsul['sakit_maag']
$konsul['hipertensi']
$konsul['pendarahan']
$konsul['stroke']
$konsul['pembekuan_darah']
$konsul['kejang']
$konsul['penyakit_berat_lainnya']

// Pemeriksaan Fisik
$konsul['kesadaran']
$konsul['td']
$konsul['nadi']
$konsul['rr']
$konsul['suhu']
$konsul['skrining_nyeri']
$konsul['jalan_nafas']
$konsul['jalan_nafas_keterangan']
$konsul['paru_paru']
$konsul['jantung']
$konsul['abdomen']
$konsul['ekstrimitas']
$konsul['neurologi']

// Pemeriksaan Penunjang
$konsul['hb_ht_al_at']
$konsul['na_k_cl']
$konsul['ureum']
$konsul['ct_bt']
$konsul['kreatin']
$konsul['ekg']
$konsul['ro_dada']
$konsul['echo']
$konsul['lain_lain_pemeriksaan']

// Diagnosis & Rekomendasi
$konsul['asa_status']
$konsul['emergency']
$konsul['saran']                    // ✅ FIXED
$konsul['anestesi_umum']
$konsul['regional_anestesi']
$konsul['kombinasi_anestesi']
$konsul['sedasi']

// Jadwal
$konsul['puasa_mulai_jam']
$konsul['puasa_mulai_tanggal']
$konsul['rencana_tiba_jam']
$konsul['rencana_tiba_tanggal']
$konsul['rencana_operasi_jam']
$konsul['rencana_operasi_tanggal']
```

---

## 🔧 displayValue Function

Function helper untuk menampilkan value dengan fallback:

```php
function displayValue($value) {
    if (empty($value) || $value === null) {
        return '<span style="color: #999;">-</span>';
    }
    return htmlspecialchars($value);
}
```

**Behavior:**
- Jika value kosong/NULL → tampilkan "-"
- Jika value ada → tampilkan dengan htmlspecialchars

---

## 📝 Files Modified

1. **`process/pdf/pdf-konsultasi-anestesi.php`**
   - Line 418: `$konsul['rekomendasi_anestesi']` → `$konsul['saran'] ?? ''`

---

## 🎯 Verification

### **Checklist:**

- ✅ Field `rekomendasi_anestesi` diganti dengan `saran`
- ✅ Tambah fallback `?? ''`
- ✅ Tidak ada warning di PDF
- ✅ Data tampil dengan benar
- ✅ Empty state handled (tampil "-")

---

## 🔍 Troubleshooting

### Issue: Rekomendasi masih kosong
**Cause:** Field `saran` NULL di database  
**Solution:** Pastikan data diisi saat form konsultasi

### Issue: Warning masih muncul
**Cause:** Cache browser atau PHP  
**Solution:** Clear cache, restart PHP server

### Issue: Field lain juga error
**Cause:** Field name tidak sesuai database  
**Solution:** Cek struktur database, sesuaikan field name

---

**Status:** ✅ Fixed  
**Impact:** Warning hilang, PDF generate dengan benar  
**Tested:** ✅ Passed
