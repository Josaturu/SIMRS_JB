# 📋 Migrasi Form Keselamatan Operasi & Kamar Pemulihan

**Tanggal:** 11 Oktober 2025  
**Database:** `dbanestesi`  
**Strategi:** Kolom Terpisah (seperti Form Persiapan Operasi)

---

## 🎯 **RINGKASAN**

### **Form Keselamatan Operasi:**
- **Tabel:** `tbl_anestesi_keselamatan_operasi`
- **Struktur Lama:** 10 kolom (3 kolom JSON: signin, timeout, signout)
- **Struktur Baru:** 62 kolom (semua kolom terpisah)
- **Total Field:** ~52 field baru

### **Form Kamar Pemulihan:**
- **Tabel:** `tbl_anestesi_kamar_pemulihan`
- **Struktur Lama:** 11 kolom (kolom `instruksi` untuk JSON)
- **Struktur Baru:** 62 kolom (semua kolom terpisah)
- **Total Field:** ~51 field baru

---

## 📊 **BREAKDOWN KOLOM**

### **A. Form Keselamatan Operasi (62 kolom total)**

#### **1. Kolom Existing (10):**
- `id` - VARCHAR(36) PRIMARY KEY
- `no_rawat` - VARCHAR(17)
- `kode_paket` - VARCHAR(15)
- `tanggal` - DATE
- `jam_mulai` - TIME
- `operasi` - VARCHAR(200)
- `tanggal_tindakan` - DATE
- ~~`signin` - LONGTEXT~~ **→ DIHAPUS**
- ~~`timeout` - LONGTEXT~~ **→ DIHAPUS**
- ~~`signout` - LONGTEXT~~ **→ DIHAPUS**

#### **2. Kolom Baru - Informasi Pasien (5):**
- `nama_pasien` - VARCHAR(150)
- `no_rekam_medis` - VARCHAR(50)
- `tgl_lahir_umur` - VARCHAR(100)
- `alamat` - TEXT
- `operator` - VARCHAR(150)

#### **3. Kolom Baru - Sign In (18):**
- `signin_time` - TIME
- `signin_1a` sampai `signin_7b` - TINYINT(1) - 14 checklist
- `dokter_anestesi_signin` - VARCHAR(100)
- `perawat_anestesi_signin` - VARCHAR(100)
- `perawat_sirkuler_signin` - VARCHAR(100)

#### **4. Kolom Baru - Time Out (13):**
- `timeout_time` - TIME
- `timeout_1`, `timeout_2a/b/c`, `timeout_3`, `timeout_5a/b` - TINYINT(1) - 7 checklist
- `catatan_dokter_bedah` - TEXT
- `catatan_dokter_anestesi` - TEXT
- `catatan_perawat` - TEXT
- `perawat_sirkuler_timeout` - VARCHAR(100)

#### **5. Kolom Baru - Sign Out (11):**
- `signout_time` - TIME
- `signout_1a/b/c/d`, `signout_2` - TINYINT(1) - 5 checklist
- `tanggal_keluar` - VARCHAR(10)
- `tahun_keluar` - VARCHAR(4)
- `perawat_sirkuler_signout` - VARCHAR(100)
- `dokter_anestesi_signout` - VARCHAR(100)
- `operator_signout` - VARCHAR(100)

---

### **B. Form Kamar Pemulihan (62 kolom total)**

#### **1. Kolom Existing (11):**
- `id` - VARCHAR(36) PRIMARY KEY
- `no_rawat` - VARCHAR(17)
- `kode_paket` - VARCHAR(15)
- `tanggal` - DATE
- `jam_mulai` - TIME
- `jam_masuk` - TIME
- `tgl_masuk` - DATE
- `jalan_nafas` - VARCHAR(50) **→ TETAP (untuk backward compatibility)**
- `pernapasan` - VARCHAR(50) **→ TETAP**
- `kesadaran` - VARCHAR(50) **→ TETAP**
- ~~`instruksi` - TEXT~~ **→ DIHAPUS**

#### **2. Kolom Baru - Data Masuk & Kondisi (9):**
- `jalan_nafas_bersih` - TINYINT(1)
- `pernapasan_spontan` - TINYINT(1)
- `pernapasan_dibantu` - TINYINT(1)
- `spontan_adekuat` - TINYINT(1)
- `spontan_penyumbatan` - TINYINT(1)
- `spontan_alat` - TINYINT(1)
- `kesadaran_sadar` - TINYINT(1)
- `kesadaran_belum_sadar` - TINYINT(1)
- `kesadaran_tidur_dalam` - TINYINT(1)

#### **3. Kolom Baru - Vital Signs (15):**
3 waktu × 5 parameter = 15 kolom
- `nadi_1`, `nadi_2`, `nadi_3` - INT
- `sistol_1`, `sistol_2`, `sistol_3` - INT
- `diastol_1`, `diastol_2`, `diastol_3` - INT
- `respirasi_1`, `respirasi_2`, `respirasi_3` - INT
- `nyeri_1`, `nyeri_2`, `nyeri_3` - INT

#### **4. Kolom Baru - Instruksi Pasca Sedasi (9):**
- `pemantauan_setiap` - VARCHAR(50)
- `pemantauan_selama` - VARCHAR(50)
- `analgesia` - VARCHAR(150)
- `anti_muntah` - VARCHAR(150)
- `antibiotik` - VARCHAR(150)
- `posisi_pasien` - VARCHAR(150)
- `obat_lain` - TEXT
- `diet_nutrisi` - VARCHAR(150)
- `lain_lain` - TEXT

#### **5. Kolom Baru - Keluar Kamar Pulih (9):**
- `jam_keluar` - TIME
- `td_keluar` - VARCHAR(20)
- `n_keluar` - VARCHAR(20)
- `r_keluar` - VARCHAR(20)
- `s_keluar` - VARCHAR(20)
- `spo2_keluar` - VARCHAR(20)
- `skrining_nyeri` - ENUM('ya','tidak')
- `tujuan_keluar` - ENUM('ruang_rawat','icu','pulang')
- `catatan_khusus` - TEXT

#### **6. Kolom Baru - Penilaian/Scoring (3):**
- `aldrete_score` - INT
- `bromage_score` - INT
- `steward_score` - INT

#### **7. Kolom Baru - Serah Terima (4):**
- `nama_penanggungjawab` - VARCHAR(150)
- `perawat_menyerahkan` - VARCHAR(100)
- `perawat_menerima` - VARCHAR(100)
- `dokter_anestesi` - VARCHAR(100)

---

## 🚀 **LANGKAH MIGRASI**

### **STEP 1: Backup Database**
```sql
-- Backup seluruh database
mysqldump -u root -p dbanestesi > backup_dbanestesi_before_alter.sql
```

### **STEP 2: Jalankan ALTER TABLE**
```bash
# Akses MySQL
mysql -u root -p dbanestesi

# Load script SQL
source d:/KKI/Module SIMRS/Module/ALTER_TABLE_KESELAMATAN_DAN_PEMULIHAN.sql
```

**Atau via phpMyAdmin:**
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Tab **SQL**
4. Copy-paste isi file `ALTER_TABLE_KESELAMATAN_DAN_PEMULIHAN.sql`
5. Klik **Go**

### **STEP 3: Verifikasi Struktur**
```sql
-- Cek struktur tabel
DESCRIBE tbl_anestesi_keselamatan_operasi;
DESCRIBE tbl_anestesi_kamar_pemulihan;

-- Cek total kolom
SELECT 
  'tbl_anestesi_keselamatan_operasi' as tabel,
  COUNT(*) as total_kolom
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_keselamatan_operasi'
UNION ALL
SELECT 
  'tbl_anestesi_kamar_pemulihan' as tabel,
  COUNT(*) as total_kolom
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_kamar_pemulihan';

-- Expected result:
-- tbl_anestesi_keselamatan_operasi: 62 kolom
-- tbl_anestesi_kamar_pemulihan: 62 kolom
```

### **STEP 4: Buat File Submit (Saya yang akan buat)**
Setelah ALTER TABLE berhasil, saya akan buat:
1. `process/submit-keselamatan-operasi.php`
2. `process/submit-kamar-pemulihan.php`
3. Update form action di kedua form
4. Update query progress di `detail-pasien.php`

---

## ⚠️ **PERHATIAN**

### **❗ Data Lama Akan Hilang:**
Karena kita DROP kolom JSON (`signin`, `timeout`, `signout`, `instruksi`), **data lama akan hilang**.

**SOLUSI:**
- Tabel masih **KOSONG** (Sample Data: Tabel kosong), jadi **AMAN**
- Tidak ada data yang akan hilang

### **❗ Estimasi Waktu ALTER:**
- Tabel kosong: **< 1 detik**
- Tabel 1,000 rows: **~5 detik**
- Tabel 10,000 rows: **~30 detik**

### **❗ Jika Ada Error:**
```sql
-- Rollback ke backup
mysql -u root -p dbanestesi < backup_dbanestesi_before_alter.sql
```

---

## 📝 **CHECKLIST SEBELUM ALTER**

- [ ] Backup database (`mysqldump` atau export via phpMyAdmin)
- [ ] Pastikan tidak ada proses yang sedang akses tabel
- [ ] Pastikan tabel kosong (atau data lama boleh hilang)
- [ ] Test di database development dulu (jika ada)
- [ ] Siap rollback jika ada error

---

## 🎯 **SETELAH ALTER TABLE BERHASIL**

Beritahu saya dengan:
```
✅ ALTER TABLE berhasil!
Total kolom:
- tbl_anestesi_keselamatan_operasi: XX kolom
- tbl_anestesi_kamar_pemulihan: XX kolom
```

Lalu saya akan lanjutkan dengan:
1. ✅ Buat file `submit-keselamatan-operasi.php`
2. ✅ Buat file `submit-kamar-pemulihan.php`
3. ✅ Update form action di kedua form
4. ✅ Update query progress di `detail-pasien.php`
5. ✅ Copy semua file ke htdocs
6. ✅ Test submit form
7. ✅ Dokumentasi final

---

## 📊 **MAPPING PREVIEW**

### **Form Keselamatan Operasi:**
```php
// Sign In Checklist
$signin_1a = isset($_POST['signin_1a']) ? 1 : 0;  // Identitas pasien
$signin_1b = isset($_POST['signin_1b']) ? 1 : 0;  // Lokasi operasi
// ... dst

// Time Out Checklist
$timeout_1 = isset($_POST['timeout_1']) ? 1 : 0;
// ... dst

// Sign Out Checklist
$signout_1a = isset($_POST['signout_1a']) ? 1 : 0;
// ... dst
```

### **Form Kamar Pemulihan:**
```php
// Vital Signs
$nadi_1 = $_POST['nadi_1'] ?? null;
$nadi_2 = $_POST['nadi_2'] ?? null;
$nadi_3 = $_POST['nadi_3'] ?? null;
// ... dst

// Scoring
$aldrete_score = $_POST['aldrete_score'] ?? null;
$bromage_score = $_POST['bromage_score'] ?? null;
$steward_score = $_POST['steward_score'] ?? null;
```

---

**Silakan jalankan ALTER TABLE, lalu beritahu saya hasilnya!** 🚀
