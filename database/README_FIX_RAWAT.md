# 🔧 Fix: Data R. RAWAT dan Keterangan Tidak Tersimpan

## 🐛 Masalah

**Data yang tidak tersimpan**:
1. ❌ Kolom **R. RAWAT** (`rawat1`, `rawat2`, ..., `rawat38`)
2. ⚠️ Kolom **Keterangan** (sebagian ter-map, sebagian tidak)

## 🔍 Penyebab

### 1. **Database Tidak Punya Kolom untuk R. RAWAT**
```
Table: tbl_anestesi_persiapan_operasi
├── program_ke_ubs ✅ (ada)
├── rawat_program_ke_ubs ❌ (TIDAK ADA!)
├── persetujuan_operasi ✅ (ada)
├── rawat_persetujuan_operasi ❌ (TIDAK ADA!)
└── ... (41 item lainnya)
```

**Database hanya punya kolom untuk UBS**, tidak ada kolom untuk R. RAWAT!

### 2. **Submit Handler Tidak Menyimpan Data R. RAWAT**
```php
// Yang ada sekarang (HANYA UBS):
$program_ke_ubs = radioToBool($formData['ubs1'] ?? '');
$persetujuan_operasi = radioToBool($formData['ubs2'] ?? '');

// Yang TIDAK ADA (R. RAWAT):
$rawat_program_ke_ubs = radioToBool($formData['rawat1'] ?? ''); // ❌
$rawat_persetujuan_operasi = radioToBool($formData['rawat2'] ?? ''); // ❌
```

## ✅ Solusi

### **Langkah 1: Tambahkan Kolom R. RAWAT di Database**

Jalankan SQL script: `add_rawat_columns.sql`

```bash
mysql -u root -p dbanestesi < add_rawat_columns.sql
```

**Script akan menambahkan 28 kolom baru**:
- `rawat_program_ke_ubs`
- `rawat_persetujuan_operasi`
- `rawat_rekam_medis`
- ... (25 kolom lainnya)

### **Langkah 2: Update Submit Handler**

File `submit-persiapan-operasi.php` sudah diupdate dengan:
1. ✅ Mapping data R. RAWAT (28 variabel baru)
2. ⚠️ Query UPDATE dan INSERT perlu diupdate (BELUM SELESAI)

---

## 📋 Detail Kolom yang Ditambahkan

### **Administrasi (11 kolom)**
| No | Field Database | Form Input |
|----|----------------|------------|
| 1 | `rawat_program_ke_ubs` | `rawat1` |
| 2 | `rawat_persetujuan_operasi` | `rawat2` |
| 3 | `rawat_rekam_medis` | `rawat3` |
| 4 | `rawat_laporan_operasi` | `rawat4` |
| 5 | `rawat_laporan_anestesi` | `rawat5` |
| 6 | `rawat_hasil_lab` | `rawat6` |
| 7 | `rawat_hasil_radiologi` | `rawat7` |
| 8 | `rawat_hasil_ct_scan` | `rawat8` |
| 9 | `rawat_hasil_usg` | `rawat9` |
| 10 | `rawat_hasil_ekg` | `rawat10` |
| 11 | `rawat_hasil_lain` | `rawat11` |

### **Fisik (9 kolom)**
| No | Field Database | Form Input |
|----|----------------|------------|
| 12 | `rawat_puasa` | `rawat12` |
| 13 | `rawat_lavement` | `rawat13` |
| 14 | `rawat_pasang_dc` | `rawat14` |
| 15 | `rawat_cukur_daerah_operasi` | `rawat15` |
| 16 | `rawat_rambut_makeup_dibersihkan` | `rawat16` |
| 18 | `rawat_perhiasan_dilepas` | `rawat18` |
| 20 | `rawat_transfusi_whole_blood` | `rawat20` |
| 21 | `rawat_transfusi_prc` | `rawat21` |
| 22 | `rawat_transfusi_ffp` | `rawat22` |
| 23 | `rawat_premedikasi` | `rawat23` |

### **Khusus (8 kolom)**
| No | Field Database | Form Input |
|----|----------------|------------|
| 25 | `rawat_dm_insulin_preop` | `rawat25` |
| 26 | `rawat_hipertensi_obat` | `rawat26` |
| 27 | `rawat_asma_obat` | `rawat27` |
| 29 | `rawat_obat_tidur` | `rawat29` |
| 30 | `rawat_pasang_infus` | `rawat30` |
| 35 | `rawat_obat_ubs` | `rawat35` |
| 37 | `rawat_visit_dokter_bedah` | `rawat37` |
| 38 | `rawat_visit_dokter_anestesi` | `rawat38` |

**Total: 28 kolom R. RAWAT**

---

## 🚀 Cara Implementasi

### **Step 1: Backup Database**
```bash
mysqldump -u root -p dbanestesi tbl_anestesi_persiapan_operasi > backup_before_rawat.sql
```

### **Step 2: Jalankan SQL Script**
```bash
mysql -u root -p dbanestesi < add_rawat_columns.sql
```

### **Step 3: Verifikasi Kolom Baru**
```sql
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi LIKE 'rawat_%';
```

**Output yang diharapkan**: 28 rows

### **Step 4: Update Query di Submit Handler**

File: `process/submit-persiapan-operasi.php`

**UPDATE Query** - Tambahkan kolom R. RAWAT:
```sql
UPDATE tbl_anestesi_persiapan_operasi SET
    -- UBS columns
    program_ke_ubs = :program_ke_ubs,
    -- R. RAWAT columns (BARU!)
    rawat_program_ke_ubs = :rawat_program_ke_ubs,
    ...
```

**INSERT Query** - Tambahkan kolom R. RAWAT:
```sql
INSERT INTO tbl_anestesi_persiapan_operasi 
    (no_rawat, kode_paket, 
     program_ke_ubs, rawat_program_ke_ubs, ...)
VALUES 
    (:no_rawat, :kode_paket, 
     :program_ke_ubs, :rawat_program_ke_ubs, ...)
```

**Bind Parameters** - Tambahkan binding:
```php
$stmt->bindParam(':rawat_program_ke_ubs', $rawat_program_ke_ubs);
$stmt->bindParam(':rawat_persetujuan_operasi', $rawat_persetujuan_operasi);
// ... 26 binding lainnya
```

---

## ⚠️ Status Implementasi

### ✅ **Selesai**
- [x] SQL script untuk tambah kolom R. RAWAT
- [x] Mapping variabel R. RAWAT di submit handler
- [x] Dokumentasi lengkap

### ⏳ **Belum Selesai** (Perlu dilanjutkan)
- [ ] Update query UPDATE dengan kolom R. RAWAT
- [ ] Update query INSERT dengan kolom R. RAWAT
- [ ] Tambahkan bind parameters untuk R. RAWAT
- [ ] Update form untuk load data R. RAWAT yang sudah tersimpan
- [ ] Testing save & load data

---

## 📝 Catatan Penting

1. **Keterangan Sudah Tersimpan Sebagian**
   - `ket12` (waktu puasa) ✅
   - `ket20`, `ket21`, `ket22` (kantong darah) ✅
   - `ket24` (antibiotik) ✅
   - `ket28`, `ket30`, `ket31`, `ket32`, `ket33`, `ket34`, `ket36` ✅
   - Keterangan lain yang tidak ter-map akan hilang ❌

2. **Item yang Tidak Punya Field Database**
   - Item 17: Cat kuku (tidak ada field)
   - Item 19: Header "Persiapan darah" (tidak ada field)
   - Item 24: Antibiotik (hanya nama obat & jam, tidak ada ya/tidak)
   - Item 28: Obat lain (hanya keterangan, tidak ada ya/tidak)
   - Item 31-34: Vital sign (hanya nilai, tidak ada ya/tidak)
   - Item 36: Skin test (hanya positif/negatif, tidak ada ya/tidak)

3. **Kolom R. RAWAT yang Tidak Ada**
   - Item 17, 19, 24, 28, 31-34, 36 tidak punya kolom R. RAWAT
   - Karena memang tidak ada radio button ya/tidak di form

---

## 🧪 Testing

### **Test 1: Ceklis R. RAWAT**
1. Buka form persiapan operasi
2. Klik tombol "✓ Ya" di header R. RAWAT
3. Submit form
4. Cek database: `SELECT rawat_* FROM tbl_anestesi_persiapan_operasi WHERE no_rawat='...'`
5. **Expected**: Semua kolom `rawat_*` = 1

### **Test 2: Load Data R. RAWAT**
1. Buka form yang sudah punya data
2. **Expected**: Radio button R. RAWAT tercentang sesuai data

### **Test 3: Update Data R. RAWAT**
1. Ubah beberapa radio button R. RAWAT
2. Submit form
3. **Expected**: Data ter-update di database

---

## 🔗 File Terkait

1. `database/add_rawat_columns.sql` - SQL script tambah kolom
2. `process/submit-persiapan-operasi.php` - Submit handler (perlu dilanjutkan)
3. `views/form-persiapan-operasi.php` - Form (perlu update load data)

---

## 📞 Next Steps

1. **Jalankan SQL script** untuk tambah kolom
2. **Lanjutkan update** query UPDATE dan INSERT
3. **Tambahkan bind parameters** untuk semua kolom R. RAWAT
4. **Update form** untuk load data R. RAWAT
5. **Testing** save & load data

---

**Status**: 🟡 **Dalam Progress** (50% selesai)
