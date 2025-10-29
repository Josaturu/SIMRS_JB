# Instruksi Merge Database Manual
**Tanggal**: 28 Oktober 2025

## ⚠️ BACKUP DULU!

```bash
mysqldump -u root -p dbanestesi > backup_dbanestesi_$(date +%Y%m%d).sql
```

## 📋 Strategi Merge

### 3 Tabel dari `dbanestesi (3).sql` (Downloads) - VERSI TERBARU

1. **tbl_anestesi_konsultasi_anestesi**
   - Field `sedasi` TEXT
   - Field `lain_lain_diagnosis` TEXT (renamed dari `rekomendasi_anestesi`)
   - Field `jalan_nafas_keterangan` VARCHAR(255)
   - Field `has_pengobatan` ENUM
   - Field `jenis_diagnosa` VARCHAR(20) DEFAULT 'Elektif'

2. **tbl_anestesi_informed_consent_anestesi**
   - Field `status_fisik` TEXT
   - Field `prognosis` TEXT
   - Field `alternatif_resiko` TEXT
   - Field `lain_lain` TEXT
   - Field `checkbox_confirm` VARCHAR(20)

3. **tbl_anestesi_catatan_anestesi**
   - COMMENT lengkap pada setiap field
   - Field `posisi` TEXT
   - ENGINE COMMENT 'Tabel catatan sedasi dan anestesi'

### Sisanya dari `dbanestesi.sql` (FOLDER RIZKI)

4. tbl_anestesi_kamar_pemulihan
5. tbl_anestesi_keselamatan_operasi
6. tbl_anestesi_persiapan_operasi
7. tbl_anestesi_vital_pemulihan
8. tbl_anestesi_vital_sign
9. booking_operasi (merge data)
10. pasien (merge data)

## 🔧 Cara Manual (Recommended)

### Step 1: Export 3 Tabel dari Downloads

```sql
-- Buka file: c:\Users\hp\Downloads\dbanestesi (3).sql
-- Copy bagian CREATE TABLE + INSERT untuk:
-- 1. tbl_anestesi_konsultasi_anestesi (line 342-450)
-- 2. tbl_anestesi_informed_consent_anestesi (line 174-207)
-- 3. tbl_anestesi_catatan_anestesi (line 80-169)
```

### Step 2: Drop & Recreate

```sql
USE dbanestesi;

-- DROP 3 tabel yang akan diganti
DROP TABLE IF EXISTS tbl_anestesi_konsultasi_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_informed_consent_anestesi;  
DROP TABLE IF EXISTS tbl_anestesi_catatan_anestesi;

-- Jalankan CREATE TABLE + INSERT dari file Downloads
-- untuk ketiga tabel di atas

-- Verify
SHOW CREATE TABLE tbl_anestesi_konsultasi_anestesi;
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

### Step 3: Verify Fields

```sql
-- Check field sedasi exists
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME = 'sedasi';

-- Check field lain_lain_diagnosis exists
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME = 'lain_lain_diagnosis';

-- Check field checkbox_confirm exists
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_informed_consent_anestesi'
  AND COLUMN_NAME = 'checkbox_confirm';
```

## 🚀 Cara Cepat dengan SQL Script

Buat file `merge_tables.sql`:

```sql
USE dbanestesi;

-- BACKUP DATA EXISTING (jika ada)
CREATE TABLE IF NOT EXISTS backup_konsultasi_20251028 AS 
SELECT * FROM tbl_anestesi_konsultasi_anestesi;

CREATE TABLE IF NOT EXISTS backup_informed_consent_20251028 AS 
SELECT * FROM tbl_anestesi_informed_consent_anestesi;

CREATE TABLE IF NOT EXISTS backup_catatan_anestesi_20251028 AS 
SELECT * FROM tbl_anestesi_catatan_anestesi;

-- DROP EXISTING
DROP TABLE IF EXISTS tbl_anestesi_konsultasi_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_informed_consent_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_catatan_anestesi;

-- IMPORT dari file Downloads
SOURCE c:/Users/hp/Downloads/dbanestesi_3_tabel_only.sql;
```

Jalankan:
```bash
mysql -u root -p dbanestesi < merge_tables.sql
```

## ✅ Testing Checklist

```sql
-- 1. Verify struktur tabel
SHOW CREATE TABLE tbl_anestesi_konsultasi_anestesi;
SHOW CREATE TABLE tbl_anestesi_informed_consent_anestesi;
SHOW CREATE TABLE tbl_anestesi_catatan_anestesi;

-- 2. Count records
SELECT 'konsultasi' as tabel, COUNT(*) as total FROM tbl_anestesi_konsultasi_anestesi
UNION ALL
SELECT 'informed_consent', COUNT(*) FROM tbl_anestesi_informed_consent_anestesi
UNION ALL
SELECT 'catatan_anestesi', COUNT(*) FROM tbl_anestesi_catatan_anestesi;

-- 3. Check new fields
SELECT sedasi, lain_lain_diagnosis 
FROM tbl_anestesi_konsultasi_anestesi 
LIMIT 1;

SELECT status_fisik, prognosis, alternatif_resiko, lain_lain, checkbox_confirm 
FROM tbl_anestesi_informed_consent_anestesi 
LIMIT 1;

-- 4. Check COMMENT
SELECT COLUMN_NAME, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_catatan_anestesi'
  AND COLUMN_COMMENT != '';
```

## 📝 Update Application Code

### Files yang perlu di-update:

1. **views/form-konsultasi-anestesi.php**
   - Gunakan field `lain_lain_diagnosis` (bukan `rekomendasi_anestesi`)
   - Tambah field `sedasi` jika belum ada

2. **process/process-konsultasi-anestesi.php**
   - Update field name mapping

3. **views/form-informed-consent-anestesi.php**
   - Sudah diambil dari FINAL branch (OK)

4. **views/form-catatan-sedasi.php**
   - Sudah diambil dari FINAL branch (OK)

## 🔄 Rollback Plan

Jika ada masalah:

```sql
DROP TABLE IF EXISTS tbl_anestesi_konsultasi_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_informed_consent_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_catatan_anestesi;

CREATE TABLE tbl_anestesi_konsultasi_anestesi AS 
SELECT * FROM backup_konsultasi_20251028;

CREATE TABLE tbl_anestesi_informed_consent_anestesi AS 
SELECT * FROM backup_informed_consent_20251028;

CREATE TABLE tbl_anestesi_catatan_anestesi AS 
SELECT * FROM backup_catatan_anestesi_20251028;
```

## 📞 Support

Jika ada error:
1. Check MySQL error log
2. Verify foreign key constraints
3. Check data integrity
4. Review backup files

---

**Status**: Ready for execution  
**Risk**: Medium (3 tables affected)  
**Backup**: Mandatory ✅
