# 🔧 Panduan Rebuild Database Lengkap

## 📋 Overview

Saya sudah buatkan **2 SQL script** untuk rebuild database dengan struktur lengkap:

1. **`CREATE_TABLE_LENGKAP.sql`** - Struktur tabel baru (105 kolom)
2. **`MIGRATE_DATA_LENGKAP.sql`** - Script migrate data dari tabel lama

---

## ✅ Struktur Tabel Baru (105 Kolom)

### **Kolom Checklist (80 kolom)**
- 40 kolom UBS (Item 1-41)
- 40 kolom R.RAWAT (Item 1-41)

### **Kolom Keterangan (15 kolom)**
- `waktu_puasa` (Item 12)
- `dc_no`, `dc_macam` (Item 14) ← **BARU!**
- `kantong_wb`, `kantong_prc`, `kantong_ffp` (Item 20-22)
- `antibiotik_preops`, `jam_antibiotik` (Item 24)
- `obat_lain` (Item 28)
- `iv_catch_no` (Item 30)
- `tekanan_darah`, `nadi`, `suhu`, `pernafasan` (Item 31-34)
- `hasil_skin_test` (Item 36)

### **Kolom Informasi Dasar (10 kolom)**
- `id`, `no_rawat`, `kode_paket`
- `tanggal_operasi`, `macam_operasi`, `dpjp`
- `tinggi_badan`, `berat_badan`, `gol_darah`, `riwayat_alergi`

### **Metadata (2 kolom)**
- `created_at`, `updated_at`

---

## 🚀 Cara Rebuild Database

### **Opsi 1: Buat Tabel Baru (Tanpa Data Lama)** ⚠️

**PERINGATAN**: Ini akan **MENGHAPUS semua data lama**!

```bash
# 1. Backup dulu (PENTING!)
mysqldump -u root -p dbanestesi tbl_anestesi_persiapan_operasi > backup_$(date +%Y%m%d).sql

# 2. Drop tabel lama
mysql -u root -p dbanestesi -e "DROP TABLE IF EXISTS tbl_anestesi_persiapan_operasi;"

# 3. Buat tabel baru
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\CREATE_TABLE_LENGKAP.sql"
```

### **Opsi 2: Migrate Data (Dengan Data Lama)** ✅ RECOMMENDED

Script ini akan:
1. ✅ Backup tabel lama
2. ✅ Rename tabel lama
3. ✅ Buat tabel baru
4. ✅ Copy semua data yang ada
5. ✅ Kolom baru otomatis NULL/default

```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\MIGRATE_DATA_LENGKAP.sql"
```

**CATATAN**: Script ini akan **OTOMATIS** menyesuaikan kolom yang ada. Jika ada kolom yang tidak ada di tabel lama, akan di-skip.

---

## 🔍 Verifikasi Setelah Rebuild

### **1. Cek Total Kolom**
```sql
SELECT COUNT(*) AS total_kolom
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi';
```
**Expected**: ~105 kolom

### **2. Cek Kolom Baru**
```sql
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi 
WHERE Field IN (
    'dc_no', 'dc_macam',
    'transfusi_darah', 'rawat_transfusi_darah',
    'antibiotik', 'rawat_antibiotik',
    'obat_lain_radio', 'rawat_obat_lain_radio',
    'tekanan_darah_radio', 'rawat_tekanan_darah_radio',
    'nadi_radio', 'rawat_nadi_radio',
    'suhu_radio', 'rawat_suhu_radio',
    'pernafasan_radio', 'rawat_pernafasan_radio',
    'skin_test_radio', 'rawat_skin_test_radio',
    'cat_kuku_dibersihkan', 'rawat_cat_kuku_dibersihkan',
    'visit_dokter_konsul_1', 'rawat_visit_dokter_konsul_1',
    'visit_dokter_konsul_2', 'rawat_visit_dokter_konsul_2',
    'visit_dokter_konsul_3', 'rawat_visit_dokter_konsul_3'
);
```
**Expected**: 24 rows (semua kolom baru)

### **3. Cek Data Tersimpan**
```sql
SELECT COUNT(*) AS total_data
FROM tbl_anestesi_persiapan_operasi;
```
**Expected**: Sama dengan jumlah data di tabel lama

### **4. Cek Sample Data**
```sql
SELECT 
    no_rawat,
    dc_no, dc_macam,
    antibiotik_preops, jam_antibiotik,
    tekanan_darah, nadi, suhu, pernafasan
FROM tbl_anestesi_persiapan_operasi
ORDER BY id DESC
LIMIT 1;
```

---

## 🧪 Testing Setelah Rebuild

### **Test 1: Input Data Baru**
1. Buka form persiapan operasi
2. Isi semua field:
   - **DC No**: 123
   - **DC Macam**: Foley Catheter
   - **Antibiotik**: Ceftriaxone
   - **Jam**: 08:00
   - **Tekanan Darah**: 120/80
   - **Nadi**: 80
   - **Suhu**: 36.5
   - **Pernapasan**: 20
3. Ceklis radio button:
   - Item 19: Persiapan darah (Ya)
   - Item 24: Antibiotik (Ya)
   - Item 28: Obat Lain (Ya)
   - Item 31-34: Vital Signs (Ya)
   - Item 36: Skin Test (Ya)
4. **Submit**

### **Test 2: Cek Database**
```sql
SELECT * FROM tbl_anestesi_persiapan_operasi
ORDER BY id DESC LIMIT 1\G
```

**Expected**: Semua field terisi dengan benar

### **Test 3: Reload Form**
1. Reload halaman form
2. **Expected**: Semua data muncul dengan benar

---

## 🔄 Rollback (Jika Ada Masalah)

### **Jika Pakai Opsi 1 (Tanpa Migrate)**
```bash
# Restore dari backup
mysql -u root -p dbanestesi < backup_YYYYMMDD.sql
```

### **Jika Pakai Opsi 2 (Dengan Migrate)**
```sql
-- Drop tabel baru
DROP TABLE IF EXISTS tbl_anestesi_persiapan_operasi;

-- Rename tabel lama kembali
RENAME TABLE 
    tbl_anestesi_persiapan_operasi_old 
    TO tbl_anestesi_persiapan_operasi;
```

---

## 📊 Perbandingan Struktur

### **Tabel Lama** (Sebelum Rebuild)
```
Total Kolom: ~60-70 kolom
- Kolom UBS: 32 kolom
- Kolom R.RAWAT: 0-28 kolom (tidak lengkap)
- Kolom Keterangan: 8-13 kolom (tidak lengkap)
- Kolom Radio Button: Tidak ada untuk beberapa item
```

### **Tabel Baru** (Setelah Rebuild)
```
Total Kolom: 105 kolom ✅
- Kolom UBS: 40 kolom ✅
- Kolom R.RAWAT: 40 kolom ✅
- Kolom Keterangan: 15 kolom ✅
- Kolom Radio Button: Lengkap untuk semua item ✅
```

---

## ✅ Checklist Rebuild

### **Sebelum Rebuild**
- [ ] Backup database
- [ ] Backup file PHP (submit handler & form)
- [ ] Catat jumlah data yang ada
- [ ] Test form di environment development dulu

### **Saat Rebuild**
- [ ] Jalankan script migrate
- [ ] Cek error di console
- [ ] Verifikasi total kolom
- [ ] Verifikasi total data

### **Setelah Rebuild**
- [ ] Test input data baru
- [ ] Test load data lama
- [ ] Test semua field keterangan
- [ ] Test semua radio button
- [ ] Hapus tabel backup jika semua OK

---

## 🎯 Rekomendasi

### **Untuk Production**
1. ✅ **Gunakan Opsi 2** (Migrate dengan data)
2. ✅ **Backup dulu** sebelum jalankan
3. ✅ **Test di development** dulu
4. ✅ **Jalankan saat jam sepi** (malam/weekend)

### **Untuk Development**
1. ✅ **Gunakan Opsi 1** (Buat baru tanpa data)
2. ✅ **Test semua fitur**
3. ✅ **Jika OK, baru migrate production**

---

## 📞 Support

Jika ada masalah saat rebuild:

1. **Error "Duplicate column"**
   - Abaikan, artinya kolom sudah ada

2. **Error "Unknown column"**
   - Edit script MIGRATE, hapus kolom yang tidak ada di tabel lama

3. **Data tidak muncul**
   - Cek apakah data ter-copy dengan query SELECT
   - Cek apakah form load data dengan benar

4. **Error saat submit**
   - Cek apakah submit handler sudah update
   - Cek bind parameters

---

## 🚀 Ready to Rebuild?

**Pilih opsi Anda**:

### **Opsi A: Saya punya data penting (PRODUCTION)** ✅
```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\MIGRATE_DATA_LENGKAP.sql"
```

### **Opsi B: Saya tidak punya data / development** ⚠️
```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\CREATE_TABLE_LENGKAP.sql"
```

**Beri tahu saya hasilnya!** 🎯
