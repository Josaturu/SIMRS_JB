# Troubleshooting: Form Persiapan Operasi

**Error:** `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'no_rm' in 'field list'`

---

## 🔍 Analisis Masalah

Error ini terjadi karena:

1. **Process file** (`process/submit-persiapan-operasi.php`) mencoba menyimpan data ke kolom `no_rm`
2. **Database aktual** tidak memiliki kolom `no_rm` di tabel `tbl_anestesi_persiapan_operasi`
3. Kemungkinan database Anda berbeda dengan backup SQL yang ada

---

## ✅ Solusi Lengkap

### **STEP 1: Cek Struktur Tabel**

Jalankan SQL berikut untuk mengecek kolom apa saja yang ada:

```sql
-- File: sql/CHECK_TABLE_STRUCTURE.sql
```

Jalankan di phpMyAdmin atau MySQL command line:

```bash
mysql -u root -p dbanestesi < sql/CHECK_TABLE_STRUCTURE.sql
```

**Output yang diharapkan:**
- Daftar semua kolom di tabel `tbl_anestesi_persiapan_operasi`
- Status apakah kolom `no_rm` ada atau tidak

---

### **STEP 2: Fix Missing Columns**

Jika kolom `no_rm` dan kolom penting lainnya tidak ada, jalankan:

```sql
-- File: sql/FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql
```

**Cara menjalankan:**

#### Via phpMyAdmin:
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Klik tab "SQL"
4. Copy-paste isi file `FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql`
5. Klik "Go"

#### Via MySQL Command Line:
```bash
mysql -u root -p dbanestesi < sql/FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql
```

**Script ini akan menambahkan kolom-kolom berikut jika belum ada:**
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `no_rm` (VARCHAR 20)
- `no_rawat` (VARCHAR 20)
- `kode_paket` (VARCHAR 50)
- `nama` (VARCHAR 100)
- `jenis_kelamin` (ENUM 'L','P')
- `umur` (INT)
- `tanggal_lahir` (DATE)

---

### **STEP 3: Tambahkan Kolom Keterangan**

Setelah kolom dasar sudah ada, tambahkan kolom keterangan:

```sql
-- File: sql/ALTER_TABLE_PERSIAPAN_OPERASI_KETERANGAN.sql
```

Jalankan dengan cara yang sama seperti STEP 2.

**Script ini akan menambahkan 11 kolom keterangan:**
- `ket_program_ke_ubs`
- `ket_persetujuan_operasi`
- `ket_rekam_medis`
- `ket_laporan_operasi`
- `ket_laporan_anestesi`
- `ket_hasil_lab`
- `ket_hasil_radiologi`
- `ket_hasil_ct_scan`
- `ket_hasil_usg`
- `ket_hasil_ekg`
- `ket_hasil_lain`

---

### **STEP 4: Verifikasi**

Setelah menjalankan semua script, verifikasi dengan query:

```sql
DESCRIBE tbl_anestesi_persiapan_operasi;
```

**Pastikan kolom-kolom berikut ada:**
```
id
no_rm
no_rawat
kode_paket
nama
jenis_kelamin
umur
tanggal_lahir
... (kolom lainnya)
ket_program_ke_ubs
ket_persetujuan_operasi
... (11 kolom keterangan)
```

---

## 🔧 Urutan Eksekusi SQL

**PENTING:** Jalankan script dalam urutan ini:

1. ✅ `CHECK_TABLE_STRUCTURE.sql` (untuk diagnosis)
2. ✅ `FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql` (fix kolom dasar)
3. ✅ `ALTER_TABLE_PERSIAPAN_OPERASI_KETERANGAN.sql` (tambah kolom keterangan)

---

## 🚨 Error Lain yang Mungkin Terjadi

### Error: Table doesn't exist
**Penyebab:** Tabel `tbl_anestesi_persiapan_operasi` belum dibuat  
**Solusi:** Import backup SQL lengkap dari `sql/dbanestesi_backup_20251014.sql`

### Error: Duplicate column name
**Penyebab:** Kolom sudah ada, script dijalankan 2x  
**Solusi:** Tidak masalah, script sudah handle ini dengan prepared statement

### Error: Access denied
**Penyebab:** User MySQL tidak punya privilege ALTER TABLE  
**Solusi:** Login sebagai root atau user dengan privilege penuh

---

## 📊 Struktur Tabel yang Benar

Setelah semua script dijalankan, struktur minimal yang harus ada:

```sql
CREATE TABLE `tbl_anestesi_persiapan_operasi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `no_rm` VARCHAR(20) NULL,
  `no_rawat` VARCHAR(20) NULL,
  `kode_paket` VARCHAR(50) NULL,
  `nama` VARCHAR(100) NULL,
  `jenis_kelamin` ENUM('L','P') NULL,
  `umur` INT(11) NULL,
  `tanggal_lahir` DATE NULL,
  `dpjp` VARCHAR(100) NULL,
  `tanggal_operasi` DATE NULL,
  `gol_darah` ENUM('A','B','AB','O') NULL,
  `riwayat_alergi` TEXT NULL,
  `macam_operasi` VARCHAR(150) NULL,
  `tinggi_badan` DECIMAL(5,2) NULL,
  `berat_badan` DECIMAL(5,2) NULL,
  
  -- Kolom checklist administrasi
  `program_ke_ubs` TINYINT(1) NULL,
  `persetujuan_operasi` TINYINT(1) NULL,
  `rekam_medis` TINYINT(1) NULL,
  `laporan_operasi` TINYINT(1) NULL,
  `laporan_anestesi` TINYINT(1) NULL,
  `hasil_lab` TINYINT(1) NULL,
  `hasil_radiologi` TINYINT(1) NULL,
  `hasil_ct_scan` TINYINT(1) NULL,
  `hasil_usg` TINYINT(1) NULL,
  `hasil_ekg` TINYINT(1) NULL,
  `hasil_lain` TINYINT(1) NULL,
  
  -- Kolom keterangan administrasi (BARU)
  `ket_program_ke_ubs` VARCHAR(255) NULL,
  `ket_persetujuan_operasi` VARCHAR(255) NULL,
  `ket_rekam_medis` VARCHAR(255) NULL,
  `ket_laporan_operasi` VARCHAR(255) NULL,
  `ket_laporan_anestesi` VARCHAR(255) NULL,
  `ket_hasil_lab` VARCHAR(255) NULL,
  `ket_hasil_radiologi` VARCHAR(255) NULL,
  `ket_hasil_ct_scan` VARCHAR(255) NULL,
  `ket_hasil_usg` VARCHAR(255) NULL,
  `ket_hasil_ekg` VARCHAR(255) NULL,
  `ket_hasil_lain` VARCHAR(255) NULL,
  
  -- Kolom lainnya...
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🧪 Testing Setelah Fix

1. **Test Insert:**
   - Buka form persiapan operasi
   - Isi semua field
   - Submit
   - **Expected:** Data tersimpan tanpa error

2. **Test Update:**
   - Edit data yang sudah ada
   - Ubah beberapa field
   - Submit
   - **Expected:** Data terupdate tanpa error

3. **Cek Database:**
   ```sql
   SELECT * FROM tbl_anestesi_persiapan_operasi ORDER BY id DESC LIMIT 1;
   ```
   - **Expected:** Data terbaru muncul dengan lengkap

---

## 📝 Catatan Penting

1. **Backup Database Dulu!**
   ```bash
   mysqldump -u root -p dbanestesi > backup_before_fix.sql
   ```

2. **Script Aman Dijalankan Berulang**
   - Semua script menggunakan prepared statement
   - Cek kolom exists sebelum ALTER TABLE
   - Tidak akan error jika dijalankan 2x

3. **Kompatibilitas MySQL**
   - Script kompatibel dengan MySQL 5.7+
   - Menggunakan prepared statement karena `IF NOT EXISTS` tidak support di semua versi

---

## 📚 File Terkait

- `sql/CHECK_TABLE_STRUCTURE.sql` - Diagnosis
- `sql/FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql` - Fix kolom dasar
- `sql/ALTER_TABLE_PERSIAPAN_OPERASI_KETERANGAN.sql` - Tambah kolom keterangan
- `process/submit-persiapan-operasi.php` - Process file
- `views/form-persiapan-operasi.php` - Form view

---

**Status:** ✅ Ready to Fix  
**Estimasi Waktu:** 10-15 menit  
**Risk Level:** Low (script aman, ada backup)
