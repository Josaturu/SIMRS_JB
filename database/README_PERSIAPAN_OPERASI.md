# Database: tbl_anestesi_persiapan_operasi

## 📋 Deskripsi
Table untuk menyimpan data **Checklist Persiapan Operasi (RMO-1 a)**

## 🗂️ Struktur Table

### Primary Key
- `id` (INT, AUTO_INCREMENT)

### Foreign Keys & Identifiers
- `no_rawat` (VARCHAR 20, NOT NULL) - Nomor rawat pasien
- `kode_paket` (VARCHAR 50, NOT NULL) - Kode paket operasi

### Data Masuk dan Kondisi Pasien
| Field | Type | Nullable | Keterangan |
|-------|------|----------|------------|
| `tanggal_operasi` | DATE | YES | Tanggal operasi dijadwalkan |
| `macam_operasi` | VARCHAR(200) | YES | Jenis/macam operasi |
| `dpjp` | VARCHAR(255) | YES | Dokter Penanggung Jawab Pelayanan |
| `tinggi_badan` | DECIMAL(5,2) | YES | Tinggi badan (cm) |
| `berat_badan` | DECIMAL(5,2) | YES | Berat badan (kg) |
| `gol_darah` | ENUM('A','B','AB','O') | YES | Golongan darah |
| `riwayat_alergi` | TEXT | YES | Riwayat alergi pasien |

### Checklist Administrasi (Item 1-11)
| Field | Item | Keterangan |
|-------|------|------------|
| `program_ke_ubs` | 1 | Program ke UBS |
| `persetujuan_operasi` | 2 | Persetujuan Operasi Lengkap |
| `rekam_medis` | 3 | Rekam Medis |
| `laporan_operasi` | 4 | Laporan Operasi |
| `laporan_anestesi` | 5 | Laporan Anestesi |
| `hasil_lab` | 6 | Hasil Laboratorium |
| `hasil_radiologi` | 7 | Hasil Radiologi |
| `hasil_ct_scan` | 8 | Hasil CT Scan |
| `hasil_usg` | 9 | Hasil USG |
| `hasil_ekg` | 10 | Hasil EKG |
| `hasil_lain` | 11 | Lain-lain |

**Type**: TINYINT(1) - 0=Tidak, 1=Ya

### Checklist Fisik (Item 12-24)
| Field | Item | Type | Keterangan |
|-------|------|------|------------|
| `puasa` | 12 | TINYINT(1) | Puasa |
| `waktu_puasa` | 12 | VARCHAR(100) | Sejak kapan puasa |
| `lavement` | 13 | TINYINT(1) | Lavement/garam Inggris |
| `pasang_dc` | 14 | TINYINT(1) | Pasang DC |
| `cukur_daerah_operasi` | 15 | TINYINT(1) | Cukur daerah operasi |
| `rambut_makeup_dibersihkan` | 16 | TINYINT(1) | Rambut palsu, gigi palsu dilepas |
| `perhiasan_dilepas` | 18 | TINYINT(1) | Perhiasan dan arloji dilepas |
| `transfusi_whole_blood` | 20 | TINYINT(1) | Whole Blood (WB) |
| `kantong_wb` | 20 | INT | Jumlah kantong WB |
| `transfusi_prc` | 21 | TINYINT(1) | PRC |
| `kantong_prc` | 21 | INT | Jumlah kantong PRC |
| `transfusi_ffp` | 22 | TINYINT(1) | FFP |
| `kantong_ffp` | 22 | INT | Jumlah kantong FFP |
| `premedikasi` | 23 | TINYINT(1) | Premedikasi |
| `antibiotik_preops` | 24 | VARCHAR(200) | Nama antibiotik |
| `jam_antibiotik` | 24 | TIME | Jam pemberian antibiotik |

### Checklist Khusus (Item 25-41)
| Field | Item | Type | Keterangan |
|-------|------|------|------------|
| `dm_insulin_preop` | 25 | TINYINT(1) | DM - Insulin Pre Op |
| `hipertensi_obat` | 26 | TINYINT(1) | Hipertensi - Obat anti hipertensi |
| `asma_obat` | 27 | TINYINT(1) | Asma - Obat anti asma |
| `obat_lain` | 28 | TEXT | Obat Lain (keterangan) |
| `obat_tidur` | 29 | TINYINT(1) | Obat sebelum tidur |
| `pasang_infus` | 30 | TINYINT(1) | Pasang Infus |
| `iv_catch_no` | 30 | VARCHAR(50) | IV Catch No |
| `tekanan_darah` | 31 | VARCHAR(20) | Tekanan Darah |
| `nadi` | 32 | VARCHAR(20) | Nadi |
| `suhu` | 33 | DECIMAL(4,1) | Suhu |
| `pernafasan` | 34 | VARCHAR(20) | Pernapasan |
| `obat_ubs` | 35 | TINYINT(1) | Obat yang dibawa ke UBS |
| `hasil_skin_test` | 36 | ENUM | Hasil Skin Test (Positif/Negatif) |
| `visit_dokter_bedah` | 37 | TINYINT(1) | Kunjungan dokter bedah |
| `visit_dokter_anestesi` | 38 | TINYINT(1) | Kunjungan dokter anestesi |

### Metadata
- `created_at` (TIMESTAMP) - Waktu data dibuat
- `updated_at` (TIMESTAMP) - Waktu data terakhir diupdate

## 🔑 Indexes & Constraints

### Primary Key
```sql
PRIMARY KEY (`id`)
```

### Unique Constraint
```sql
UNIQUE KEY `unique_checklist` (`no_rawat`, `kode_paket`)
```
**Fungsi**: Mencegah duplikasi data - satu pasien hanya punya satu checklist per booking operasi

### Indexes
```sql
KEY `idx_no_rawat` (`no_rawat`)
KEY `idx_kode_paket` (`kode_paket`)
KEY `idx_tanggal_operasi` (`tanggal_operasi`)
```

## 📦 File SQL

### 1. `tbl_anestesi_persiapan_operasi_FIXED.sql`
**Fungsi**: Create table baru dengan struktur yang benar
**Kapan digunakan**: 
- Instalasi baru (belum ada table)
- Ingin membuat table dari awal

**Cara pakai**:
```bash
mysql -u username -p database_name < tbl_anestesi_persiapan_operasi_FIXED.sql
```

### 2. `migrate_persiapan_operasi.sql`
**Fungsi**: Migrasi dari table lama ke struktur baru
**Kapan digunakan**: 
- Sudah ada table lama dengan data
- Ingin update struktur tanpa kehilangan data

**Cara pakai**:
```bash
mysql -u username -p database_name < migrate_persiapan_operasi.sql
```

**Fitur**:
- ✅ Backup otomatis ke `tbl_anestesi_persiapan_operasi_backup`
- ✅ Hapus field yang tidak digunakan
- ✅ Modifikasi field yang ada
- ✅ Tambah UNIQUE constraint
- ✅ Tambah indexes
- ✅ Bersihkan data duplikat
- ✅ Verifikasi hasil

## 🔄 Perbedaan Table Lama vs Baru

### Field yang Dihapus ❌
- `no_rm` - Data diambil dari table pasien
- `nama` - Data diambil dari table pasien
- `jenis_kelamin` - Data diambil dari table pasien
- `umur` - Data diambil dari table pasien
- `tanggal_lahir` - Data diambil dari table pasien
- `visit_dokter_konsul_1/2/3` - Tidak digunakan di form
- `nama_dokter_konsul_1/2/3` - Tidak digunakan di form
- `perawat_ruangan` - Tidak digunakan di form
- `tanda_tangan_perawat_ruangan` - Tidak digunakan di form
- `perawat_ubs` - Tidak digunakan di form
- `tanda_tangan_perawat_ubs` - Tidak digunakan di form

### Field yang Dimodifikasi 🔧
- `no_rawat`: VARCHAR(20) NULL → VARCHAR(20) NOT NULL
- `kode_paket`: VARCHAR(50) NULL → VARCHAR(50) NOT NULL
- `macam_operasi`: VARCHAR(150) → VARCHAR(200)
- `dpjp`: VARCHAR(100) → VARCHAR(255)
- `waktu_puasa`: DATETIME → VARCHAR(100)
- `antibiotik_preops`: VARCHAR(100) → VARCHAR(200)

### Constraint Baru ✨
- UNIQUE KEY pada (`no_rawat`, `kode_paket`)
- Index pada `no_rawat`, `kode_paket`, `tanggal_operasi`

## 🚀 Instalasi

### Opsi 1: Instalasi Baru (Belum Ada Table)
```sql
-- Jalankan file ini
source tbl_anestesi_persiapan_operasi_FIXED.sql;
```

### Opsi 2: Migrasi dari Table Lama
```sql
-- Jalankan file ini (sudah include backup)
source migrate_persiapan_operasi.sql;
```

### Opsi 3: Manual via phpMyAdmin
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Klik tab "SQL"
4. Copy-paste isi file SQL
5. Klik "Go"

## 🔍 Verifikasi

### Cek Struktur Table
```sql
SHOW CREATE TABLE tbl_anestesi_persiapan_operasi;
```

### Cek Data
```sql
SELECT COUNT(*) AS total_data FROM tbl_anestesi_persiapan_operasi;
```

### Cek Indexes
```sql
SHOW INDEXES FROM tbl_anestesi_persiapan_operasi;
```

### Cek Constraint
```sql
SELECT 
  CONSTRAINT_NAME, 
  CONSTRAINT_TYPE 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'tbl_anestesi_persiapan_operasi';
```

## 🔙 Rollback (Jika Ada Masalah)

Jika migrasi bermasalah, restore dari backup:
```sql
-- Hapus table baru
DROP TABLE tbl_anestesi_persiapan_operasi;

-- Restore dari backup
RENAME TABLE tbl_anestesi_persiapan_operasi_backup 
TO tbl_anestesi_persiapan_operasi;
```

## 📝 Catatan Penting

1. **UNIQUE Constraint**: Mencegah duplikasi data per pasien per booking
2. **NOT NULL Fields**: `no_rawat` dan `kode_paket` wajib diisi
3. **Default Values**: Semua checkbox default 0 (Tidak)
4. **Timestamps**: Auto-update saat data berubah
5. **Backup**: Selalu backup sebelum migrasi

## 🐛 Troubleshooting

### Error: Duplicate entry
**Penyebab**: Ada data duplikat dengan `no_rawat` dan `kode_paket` yang sama
**Solusi**: Script migrasi sudah handle ini, data duplikat akan dihapus otomatis

### Error: Column not found
**Penyebab**: Field di form tidak sesuai dengan database
**Solusi**: Pastikan semua field di form sesuai dengan struktur table

### Data tidak tersimpan
**Penyebab**: UNIQUE constraint violation
**Solusi**: Gunakan logika INSERT/UPDATE di submit handler (sudah diimplementasi)

## 📞 Support
Jika ada masalah, cek:
1. Error log MySQL
2. PHP error log
3. Browser console
4. Network tab di DevTools
