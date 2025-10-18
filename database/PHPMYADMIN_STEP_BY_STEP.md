# 📝 Panduan phpMyAdmin - Step by Step

## 🎯 Cara Paling Aman: Update Bertahap

Karena file MIGRATE terlalu besar untuk phpMyAdmin, kita akan **update kolom satu per satu**.

---

## Step 1: Buka phpMyAdmin

1. Buka browser: `http://localhost/phpmyadmin`
2. Login (username: `root`, password: kosong atau sesuai setting)
3. Klik database **`dbanestesi`** di sidebar kiri
4. Klik tab **"SQL"** di atas

---

## Step 2: Tambah Kolom R.RAWAT (28 kolom)

**Copy script ini** dan paste di phpMyAdmin, lalu klik **"Go"**:

```sql
-- Tambah 28 kolom R.RAWAT
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `rawat_program_ke_ubs` tinyint(1) DEFAULT 0 AFTER `program_ke_ubs`,
ADD COLUMN `rawat_persetujuan_operasi` tinyint(1) DEFAULT 0 AFTER `persetujuan_operasi`,
ADD COLUMN `rawat_rekam_medis` tinyint(1) DEFAULT 0 AFTER `rekam_medis`,
ADD COLUMN `rawat_laporan_operasi` tinyint(1) DEFAULT 0 AFTER `laporan_operasi`,
ADD COLUMN `rawat_laporan_anestesi` tinyint(1) DEFAULT 0 AFTER `laporan_anestesi`,
ADD COLUMN `rawat_hasil_lab` tinyint(1) DEFAULT 0 AFTER `hasil_lab`,
ADD COLUMN `rawat_hasil_radiologi` tinyint(1) DEFAULT 0 AFTER `hasil_radiologi`,
ADD COLUMN `rawat_hasil_ct_scan` tinyint(1) DEFAULT 0 AFTER `hasil_ct_scan`,
ADD COLUMN `rawat_hasil_usg` tinyint(1) DEFAULT 0 AFTER `hasil_usg`,
ADD COLUMN `rawat_hasil_ekg` tinyint(1) DEFAULT 0 AFTER `hasil_ekg`,
ADD COLUMN `rawat_hasil_lain` tinyint(1) DEFAULT 0 AFTER `hasil_lain`,
ADD COLUMN `rawat_puasa` tinyint(1) DEFAULT 0 AFTER `puasa`,
ADD COLUMN `rawat_lavement` tinyint(1) DEFAULT 0 AFTER `lavement`,
ADD COLUMN `rawat_pasang_dc` tinyint(1) DEFAULT 0 AFTER `pasang_dc`,
ADD COLUMN `rawat_cukur_daerah_operasi` tinyint(1) DEFAULT 0 AFTER `cukur_daerah_operasi`,
ADD COLUMN `rawat_rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0 AFTER `rambut_makeup_dibersihkan`,
ADD COLUMN `rawat_cat_kuku_dibersihkan` tinyint(1) DEFAULT 0 AFTER `cat_kuku_dibersihkan`,
ADD COLUMN `rawat_perhiasan_dilepas` tinyint(1) DEFAULT 0 AFTER `perhiasan_dilepas`,
ADD COLUMN `rawat_transfusi_darah` tinyint(1) DEFAULT 0 AFTER `transfusi_darah`,
ADD COLUMN `rawat_transfusi_whole_blood` tinyint(1) DEFAULT 0 AFTER `transfusi_whole_blood`,
ADD COLUMN `rawat_transfusi_prc` tinyint(1) DEFAULT 0 AFTER `transfusi_prc`,
ADD COLUMN `rawat_transfusi_ffp` tinyint(1) DEFAULT 0 AFTER `transfusi_ffp`,
ADD COLUMN `rawat_premedikasi` tinyint(1) DEFAULT 0 AFTER `premedikasi`,
ADD COLUMN `rawat_antibiotik` tinyint(1) DEFAULT 0 AFTER `antibiotik`,
ADD COLUMN `rawat_dm_insulin_preop` tinyint(1) DEFAULT 0 AFTER `dm_insulin_preop`,
ADD COLUMN `rawat_hipertensi_obat` tinyint(1) DEFAULT 0 AFTER `hipertensi_obat`,
ADD COLUMN `rawat_asma_obat` tinyint(1) DEFAULT 0 AFTER `asma_obat`,
ADD COLUMN `rawat_obat_lain_radio` tinyint(1) DEFAULT 0 AFTER `obat_lain_radio`;

SELECT '✅ Step 2 selesai: 28 kolom R.RAWAT ditambahkan' AS status;
```

**Expected**: Muncul pesan "✅ Step 2 selesai"

---

## Step 3: Tambah Kolom Radio Button (16 kolom)

**Copy script ini** dan paste, lalu klik **"Go"**:

```sql
-- Tambah 16 kolom radio button
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `transfusi_darah` tinyint(1) DEFAULT 0 COMMENT 'Item 19: Persiapan darah' AFTER `perhiasan_dilepas`,
ADD COLUMN `antibiotik` tinyint(1) DEFAULT 0 COMMENT 'Item 24: Antibiotik (radio)' AFTER `premedikasi`,
ADD COLUMN `obat_lain_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 28: Obat Lain (radio)' AFTER `asma_obat`,
ADD COLUMN `rawat_obat_tidur` tinyint(1) DEFAULT 0 AFTER `obat_tidur`,
ADD COLUMN `rawat_pasang_infus` tinyint(1) DEFAULT 0 AFTER `pasang_infus`,
ADD COLUMN `tekanan_darah_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 31: Tekanan Darah (radio)' AFTER `pasang_infus`,
ADD COLUMN `rawat_tekanan_darah_radio` tinyint(1) DEFAULT 0 AFTER `tekanan_darah_radio`,
ADD COLUMN `nadi_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 32: Nadi (radio)' AFTER `rawat_tekanan_darah_radio`,
ADD COLUMN `rawat_nadi_radio` tinyint(1) DEFAULT 0 AFTER `nadi_radio`,
ADD COLUMN `suhu_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 33: Suhu (radio)' AFTER `rawat_nadi_radio`,
ADD COLUMN `rawat_suhu_radio` tinyint(1) DEFAULT 0 AFTER `suhu_radio`,
ADD COLUMN `pernafasan_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 34: Pernapasan (radio)' AFTER `rawat_suhu_radio`,
ADD COLUMN `rawat_pernafasan_radio` tinyint(1) DEFAULT 0 AFTER `pernafasan_radio`,
ADD COLUMN `rawat_obat_ubs` tinyint(1) DEFAULT 0 AFTER `obat_ubs`,
ADD COLUMN `skin_test_radio` tinyint(1) DEFAULT 0 COMMENT 'Item 36: Skin Test (radio)' AFTER `obat_ubs`,
ADD COLUMN `rawat_skin_test_radio` tinyint(1) DEFAULT 0 AFTER `skin_test_radio`;

SELECT '✅ Step 3 selesai: 16 kolom radio button ditambahkan' AS status;
```

**Expected**: Muncul pesan "✅ Step 3 selesai"

---

## Step 4: Tambah Kolom Visit Dokter (8 kolom)

**Copy script ini** dan paste, lalu klik **"Go"**:

```sql
-- Tambah 8 kolom visit dokter
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `rawat_visit_dokter_bedah` tinyint(1) DEFAULT 0 AFTER `visit_dokter_bedah`,
ADD COLUMN `rawat_visit_dokter_anestesi` tinyint(1) DEFAULT 0 AFTER `visit_dokter_anestesi`,
ADD COLUMN `visit_dokter_konsul_1` tinyint(1) DEFAULT 0 COMMENT 'Item 39: Dokter konsul 1' AFTER `visit_dokter_anestesi`,
ADD COLUMN `rawat_visit_dokter_konsul_1` tinyint(1) DEFAULT 0 AFTER `visit_dokter_konsul_1`,
ADD COLUMN `visit_dokter_konsul_2` tinyint(1) DEFAULT 0 COMMENT 'Item 40: Dokter konsul 2' AFTER `rawat_visit_dokter_konsul_1`,
ADD COLUMN `rawat_visit_dokter_konsul_2` tinyint(1) DEFAULT 0 AFTER `visit_dokter_konsul_2`,
ADD COLUMN `visit_dokter_konsul_3` tinyint(1) DEFAULT 0 COMMENT 'Item 41: Dokter konsul 3' AFTER `rawat_visit_dokter_konsul_2`,
ADD COLUMN `rawat_visit_dokter_konsul_3` tinyint(1) DEFAULT 0 AFTER `visit_dokter_konsul_3`;

SELECT '✅ Step 4 selesai: 8 kolom visit dokter ditambahkan' AS status;
```

**Expected**: Muncul pesan "✅ Step 4 selesai"

---

## Step 5: Tambah Kolom DC (2 kolom)

**Copy script ini** dan paste, lalu klik **"Go"**:

```sql
-- Tambah 2 kolom DC
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `dc_no` INT(11) DEFAULT NULL COMMENT 'Item 14: Nomor DC' AFTER `pasang_dc`,
ADD COLUMN `dc_macam` VARCHAR(100) DEFAULT NULL COMMENT 'Item 14: Macam DC' AFTER `dc_no`;

SELECT '✅ Step 5 selesai: 2 kolom DC ditambahkan' AS status;
```

**Expected**: Muncul pesan "✅ Step 5 selesai"

---

## Step 6: Verifikasi Total Kolom

**Copy script ini** dan paste, lalu klik **"Go"**:

```sql
-- Cek total kolom
SELECT COUNT(*) AS total_kolom
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi';

-- Cek kolom baru
SELECT '=== KOLOM BARU ===' AS info;
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi 
WHERE Field IN (
    'dc_no', 'dc_macam',
    'rawat_program_ke_ubs', 'rawat_persetujuan_operasi',
    'transfusi_darah', 'antibiotik', 'obat_lain_radio',
    'tekanan_darah_radio', 'nadi_radio', 'suhu_radio', 'pernafasan_radio',
    'skin_test_radio',
    'visit_dokter_konsul_1', 'visit_dokter_konsul_2', 'visit_dokter_konsul_3'
);
```

**Expected**: 
- Total kolom: ~100-105
- Semua kolom baru muncul

---

## Step 7: Insert Data Test

**Copy script ini** dan paste, lalu klik **"Go"**:

```sql
-- Hapus data test lama (jika ada)
DELETE FROM tbl_anestesi_persiapan_operasi 
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';

-- Insert data test lengkap
INSERT INTO tbl_anestesi_persiapan_operasi (
    no_rawat, kode_paket, tanggal_operasi, macam_operasi, dpjp,
    tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
    
    -- Checklist (semua Ya = 1)
    program_ke_ubs, rawat_program_ke_ubs,
    persetujuan_operasi, rawat_persetujuan_operasi,
    puasa, rawat_puasa, waktu_puasa,
    pasang_dc, rawat_pasang_dc, dc_no, dc_macam,
    transfusi_darah, rawat_transfusi_darah,
    transfusi_whole_blood, rawat_transfusi_whole_blood, kantong_wb,
    transfusi_prc, rawat_transfusi_prc, kantong_prc,
    transfusi_ffp, rawat_transfusi_ffp, kantong_ffp,
    antibiotik, rawat_antibiotik, antibiotik_preops, jam_antibiotik,
    obat_lain_radio, rawat_obat_lain_radio, obat_lain,
    pasang_infus, rawat_pasang_infus, iv_catch_no,
    tekanan_darah_radio, rawat_tekanan_darah_radio, tekanan_darah,
    nadi_radio, rawat_nadi_radio, nadi,
    suhu_radio, rawat_suhu_radio, suhu,
    pernafasan_radio, rawat_pernafasan_radio, pernafasan,
    skin_test_radio, rawat_skin_test_radio, hasil_skin_test
)
VALUES (
    'TEST001', 'PKT001', '2025-01-20', 'Operasi Appendectomy', 'Dr. Budi Santoso, Sp.B',
    170.00, 65.00, 'A+', 'Alergi Penisilin',
    
    1, 1,  -- Program ke UBS
    1, 1,  -- Persetujuan Operasi
    1, 1, 'Sejak kemarin pukul 22:00',  -- Puasa
    1, 1, 123, 'Foley Catheter No. 16',  -- DC
    1, 1,  -- Transfusi Darah
    1, 1, 2,  -- WB
    1, 1, 3,  -- PRC
    1, 1, 1,  -- FFP
    1, 1, 'Ceftriaxone 1 gram IV', '08:00:00',  -- Antibiotik
    1, 1, 'Paracetamol 500mg 3x1, Omeprazole 20mg 1x1',  -- Obat Lain
    1, 1, 'IV-2025-001',  -- IV Catch
    1, 1, '120/80 mmHg',  -- Tekanan Darah
    1, 1, '80 x/menit',  -- Nadi
    1, 1, 36.5,  -- Suhu
    1, 1, '20 x/menit',  -- Pernapasan
    1, 1, 'Negatif'  -- Skin Test
);

SELECT '✅ Data test berhasil diinsert!' AS status;

-- Verifikasi
SELECT 
    no_rawat, dc_no, dc_macam, antibiotik_preops, 
    tekanan_darah, nadi, suhu, pernafasan, hasil_skin_test
FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
```

**Expected**: Data test muncul dengan lengkap

---

## Step 8: Test Form di Browser

1. Buka browser
2. Ketik URL:
   ```
   http://localhost/index.php?page=persiapan-operasi&no_rawat=TEST001&kode_paket=PKT001
   ```
   
   Atau sesuaikan dengan struktur URL Anda:
   ```
   http://localhost/SIMRS_JB/index.php?page=persiapan-operasi&no_rawat=TEST001&kode_paket=PKT001
   ```

3. **Cek apakah data muncul**:
   - ✅ DC No: 123
   - ✅ DC Macam: Foley Catheter No. 16
   - ✅ Antibiotik: Ceftriaxone 1 gram IV
   - ✅ Jam: 08:00
   - ✅ Tekanan Darah: 120/80 mmHg
   - ✅ Nadi: 80 x/menit
   - ✅ Suhu: 36.5
   - ✅ Pernapasan: 20 x/menit
   - ✅ Skin Test: Negatif (tercentang)
   - ✅ Semua radio button tercentang "Ya"

---

## ✅ Jika Berhasil

Hapus data test:
```sql
DELETE FROM tbl_anestesi_persiapan_operasi 
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
```

**Form siap digunakan!** 🎉

---

## ❌ Jika Ada Error

### **Error: "Duplicate column name"**
✅ **ABAIKAN** - Artinya kolom sudah ada, lanjut ke step berikutnya

### **Error: "Unknown column in field list"**
Kemungkinan ada kolom yang belum ada. Cek dengan:
```sql
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi;
```

### **Data tidak muncul di form**
1. Cek apakah data ada di database (Step 7 verifikasi)
2. Cek URL apakah benar
3. Screenshot error dan beri tahu saya

---

## 📊 Progress Checklist

- ⬜ Step 2: Kolom R.RAWAT (28 kolom)
- ⬜ Step 3: Kolom Radio Button (16 kolom)
- ⬜ Step 4: Kolom Visit Dokter (8 kolom)
- ⬜ Step 5: Kolom DC (2 kolom)
- ⬜ Step 6: Verifikasi
- ⬜ Step 7: Insert Data Test
- ⬜ Step 8: Test Form

---

**Mulai dari Step 2 dan lakukan satu per satu!** 🚀

Beri tahu saya jika ada error di step mana pun!
