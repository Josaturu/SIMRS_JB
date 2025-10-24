# Panduan Membuat Ulang Tabel Catatan Anestesi

## 📋 File SQL
`CREATE_TABLE_CATATAN_ANESTESI_COMPLETE.sql`

## ⚠️ PENTING: Backup Data Lama!

Sebelum menjalankan SQL, **WAJIB backup data lama** terlebih dahulu!

### Cara Backup:

**Via phpMyAdmin:**
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Pilih tabel `tbl_anestesi_catatan_anestesi`
4. Klik tab **Export**
5. Pilih format **SQL**
6. Klik **Go** untuk download

**Via Command Line:**
```bash
mysqldump -u root -p dbanestesi tbl_anestesi_catatan_anestesi > backup_catatan_anestesi_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🚀 Cara Menjalankan SQL

### Metode 1: Via phpMyAdmin (Recommended)

1. **Buka phpMyAdmin** di browser: `http://localhost/phpmyadmin`

2. **Login** dengan username/password MySQL Anda

3. **Pilih database** `dbanestesi` di sidebar kiri

4. **Klik tab SQL** di menu atas

5. **Copy-paste** isi file `CREATE_TABLE_CATATAN_ANESTESI_COMPLETE.sql`

6. **Klik Go** untuk execute

7. **Tunggu** sampai muncul pesan sukses

8. **Refresh** halaman untuk melihat tabel baru

### Metode 2: Via Command Line

```bash
# Masuk ke direktori database
cd "C:\FOLDER RIZKI\SIMRS_JB\database"

# Jalankan SQL file
mysql -u root -p dbanestesi < CREATE_TABLE_CATATAN_ANESTESI_COMPLETE.sql
```

### Metode 3: Via MySQL Workbench

1. Buka MySQL Workbench
2. Connect ke database server
3. Pilih database `dbanestesi`
4. File → Open SQL Script
5. Pilih file `CREATE_TABLE_CATATAN_ANESTESI_COMPLETE.sql`
6. Execute (Ctrl + Shift + Enter)

---

## ✅ Verifikasi Setelah Membuat Tabel

### 1. Cek Struktur Tabel
```sql
DESCRIBE tbl_anestesi_catatan_anestesi;
```

**Expected Output:** 75 kolom

### 2. Cek Data Sample
```sql
SELECT * FROM tbl_anestesi_catatan_anestesi;
```

**Expected Output:** 1 row (data sample dadang beton)

### 3. Cek Index
```sql
SHOW INDEX FROM tbl_anestesi_catatan_anestesi;
```

**Expected Output:** 
- PRIMARY (id)
- idx_composite_key
- idx_no_rawat
- idx_tanggal
- idx_no_rm

---

## 🔧 Troubleshooting

### Error: "Table already exists"

**Solusi:**
```sql
-- Hapus tabel lama terlebih dahulu
DROP TABLE IF EXISTS tbl_anestesi_catatan_anestesi;

-- Lalu jalankan CREATE TABLE lagi
```

### Error: "Foreign key constraint fails"

**Penyebab:** Tabel `booking_operasi` tidak ada atau struktur tidak sesuai

**Solusi:** Comment out bagian FOREIGN KEY di SQL file (sudah di-comment by default)

### Error: "Access denied"

**Penyebab:** User MySQL tidak punya permission

**Solusi:**
```sql
-- Login sebagai root, lalu:
GRANT ALL PRIVILEGES ON dbanestesi.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

### Data Lama Hilang

**Solusi:** Restore dari backup
```bash
mysql -u root -p dbanestesi < backup_catatan_anestesi_YYYYMMDD_HHMMSS.sql
```

---

## 📊 Struktur Tabel

### Total Kolom: 75

#### 1. Primary Key & Composite Key (5)
- `id` - UUID primary key
- `no_rawat` - Nomor rawat
- `kode_paket` - Kode paket
- `tanggal` - Tanggal operasi
- `jam_mulai` - Jam mulai

#### 2. Data Pasien (7)
- `no_rm`, `nama`, `tgl_lahir`
- `ruang_perawatan`, `dokter_merawat`
- `dokter_anestesi`, `perawat_anestesi`

#### 3. Diagnosa & Tindakan (4)
- `diagnosa_pra_bedah`, `nama_tindakan`
- `diagnosa_pasca_bedah`, `asessment_pra_anestesi`

#### 4. Jenis Anestesi (7)
- `jenis_anestesi`, `keterangan`, `tanggal_anestesi`
- `pukul`, `dokter_bedah`, `perawat_bedah`
- `jenis_pembedahan`

#### 5. Vital Sign (13)
- `bb`, `td`, `suhu`, `respirasi`, `hb`, `tb`
- `nadi`, `gcs`, `golongan_darah`, `skrining_nyeri`
- `status_fisik_asa`, `penyulit_pra_anestesi`, `resiko`

#### 6. Checklist & Teknik (4)
- `checklist_sebelum_induksi`, `teknik_anestesi`
- `infus_perifer`, `lain_lain_posisi`

#### 7. Premedikasi (3)
- `premedikasi`, `premedik_nama_obat`, `premedik_dosis_obat`

#### 8. Induksi & Jalan Nafas (8)
- `induksi`, `jalan_nafas`, `ventilasi`, `ventilator`
- `ukuran_balon`, `jenis_balon`, `posisi_ett`

#### 9. Anestesi Regional (5)
- `lokasi_regional`, `jarum_regional`, `kateter_regional`
- `obat_anestesi_lokal`, `hasil_regional`

#### 10. Obat & Cairan (3)
- `obat`, `cairan_infus`, `cairan_output`

#### 11. Masalah & Tindakan (2)
- `masalah_selama_anestesi`, `tindakan`

#### 12. Petugas (3)
- `perawat_menyerahkan`, `perawat_menerima`, `dokter_anestesi_ttd`

#### 13. Timestamp (2)
- `created_at`, `updated_at`

#### 14. Waktu Operasi (9)
- `mulai_anestesi`, `selesai_anestesi`
- `mulai_pembedahan`, `selesai_pembedahan`
- `keterangan_waktu`, `induksi_pukul`
- `pasien_siap_insisi`, `insisi_mulai_pukul`
- `operasi_mulai_pukul`, `ekstubasi_pukul`
- `pasien_keluar_ok`

#### 15. Lain-lain (1)
- `lain_lain_balon`

---

## 🎯 Setelah Membuat Tabel

### 1. Test Form Catatan Sedasi

```
http://localhost:8000/index.php?page=form-catatan-sedasi&no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00
```

**Expected:**
- Mode: EDIT (hijau)
- Semua field terisi dengan data sample
- Tidak ada error di console

### 2. Test Insert Data Baru

```
http://localhost:8000/index.php?page=form-catatan-sedasi&no_rawat=2&kode_paket=2&tanggal=2025-02-02&jam_mulai=10:00:00
```

**Expected:**
- Mode: INSERT (kuning)
- Field kosong (kecuali data dari booking)
- Bisa simpan data baru

### 3. Test Update Data

1. Buka form dengan data existing
2. Edit beberapa field
3. Klik Simpan
4. Refresh halaman
5. Data seharusnya terupdate, tidak duplicate

---

## 📝 Catatan Tambahan

### Menambahkan UNIQUE Constraint

Untuk mencegah duplicate data berdasarkan composite key:

```sql
ALTER TABLE `tbl_anestesi_catatan_anestesi` 
ADD UNIQUE KEY `unique_composite` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`);
```

**Keuntungan:**
- Database akan reject INSERT jika data sudah ada
- Harus pakai INSERT ... ON DUPLICATE KEY UPDATE

**Kerugian:**
- Jika ada data duplicate, ALTER TABLE akan gagal
- Harus clean data dulu

### Menambahkan Foreign Key

Jika tabel `booking_operasi` sudah ada:

```sql
ALTER TABLE `tbl_anestesi_catatan_anestesi`
ADD CONSTRAINT `fk_catatan_booking` 
FOREIGN KEY (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
REFERENCES `booking_operasi` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
ON DELETE CASCADE ON UPDATE CASCADE;
```

**Keuntungan:**
- Data integrity terjaga
- Auto delete jika booking dihapus

**Kerugian:**
- Tidak bisa insert jika booking tidak ada
- Performa sedikit menurun

---

## 🆘 Bantuan

Jika ada masalah:

1. **Cek error log MySQL:**
   - Windows: `C:\xampp\mysql\data\*.err`
   - Linux: `/var/log/mysql/error.log`

2. **Cek PHP error log:**
   - File: `logs/persiapan-operasi-debug.log`

3. **Cek browser console:**
   - F12 → Console tab

4. **Test query manual:**
   ```sql
   SELECT * FROM tbl_anestesi_catatan_anestesi 
   WHERE no_rawat = '1' 
     AND kode_paket = '1' 
     AND tanggal = '2025-02-01' 
     AND jam_mulai = '23:59:00';
   ```

---

## ✅ Checklist

- [ ] Backup data lama
- [ ] Jalankan CREATE TABLE SQL
- [ ] Verifikasi struktur tabel (75 kolom)
- [ ] Verifikasi data sample (1 row)
- [ ] Verifikasi index (5 index)
- [ ] Test form mode EDIT
- [ ] Test form mode INSERT
- [ ] Test form mode UPDATE
- [ ] Clear localStorage browser
- [ ] Hard refresh browser (Ctrl + Shift + R)

---

**Good luck!** 🚀
