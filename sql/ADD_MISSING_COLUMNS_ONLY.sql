-- =====================================================
-- ADD MISSING COLUMNS ONLY
-- File: ADD_MISSING_COLUMNS_ONLY.sql
-- Tanggal: 2025-10-29
-- =====================================================
-- 
-- DESKRIPSI:
-- Menambahkan HANYA kolom yang benar-benar hilang
-- berdasarkan analisis database dbanestesi (5).sql
--
-- KOLOM YANG HILANG:
-- 1. no_rm - Nomor Rekam Medis (PENYEBAB ERROR!)
-- 2. nama - Nama Pasien
-- 3. jenis_kelamin - Jenis Kelamin
-- 4. umur - Umur
-- 5. tanggal_lahir - Tanggal Lahir
-- 6. nama_dokter_konsul_1/2/3 - Nama Dokter Konsultan
-- 7. perawat_ruangan - Perawat Ruangan
-- 8. tanda_tangan_perawat_ruangan - TTD Perawat Ruangan
-- 9. perawat_ubs - Perawat UBS
-- 10. tanda_tangan_perawat_ubs - TTD Perawat UBS
--
-- =====================================================

USE dbanestesi;

SET @dbname = 'dbanestesi';
SET @tablename = 'tbl_anestesi_persiapan_operasi';

-- =====================================================
-- 1. KOLOM DATA PASIEN
-- =====================================================

-- Kolom: no_rm (PENTING - PENYEBAB ERROR!)
SET @columnname = 'no_rm';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column no_rm already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN no_rm VARCHAR(20) NULL DEFAULT NULL AFTER id;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: nama
SET @columnname = 'nama';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column nama already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN nama VARCHAR(100) NULL DEFAULT NULL AFTER kode_paket;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: jenis_kelamin
SET @columnname = 'jenis_kelamin';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column jenis_kelamin already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN jenis_kelamin ENUM('L','P') NULL DEFAULT NULL AFTER nama;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: umur
SET @columnname = 'umur';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column umur already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN umur INT(11) NULL DEFAULT NULL AFTER jenis_kelamin;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: tanggal_lahir
SET @columnname = 'tanggal_lahir';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column tanggal_lahir already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN tanggal_lahir DATE NULL DEFAULT NULL AFTER umur;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 2. KOLOM DOKTER KONSULTAN
-- =====================================================

-- Kolom: nama_dokter_konsul_1
SET @columnname = 'nama_dokter_konsul_1';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column nama_dokter_konsul_1 already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN nama_dokter_konsul_1 VARCHAR(100) NULL DEFAULT NULL AFTER visit_dokter_konsul_1;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: nama_dokter_konsul_2
SET @columnname = 'nama_dokter_konsul_2';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column nama_dokter_konsul_2 already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN nama_dokter_konsul_2 VARCHAR(100) NULL DEFAULT NULL AFTER visit_dokter_konsul_2;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: nama_dokter_konsul_3
SET @columnname = 'nama_dokter_konsul_3';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column nama_dokter_konsul_3 already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN nama_dokter_konsul_3 VARCHAR(100) NULL DEFAULT NULL AFTER visit_dokter_konsul_3;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 3. KOLOM PERAWAT & TANDA TANGAN
-- =====================================================

-- Kolom: perawat_ruangan
SET @columnname = 'perawat_ruangan';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column perawat_ruangan already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN perawat_ruangan VARCHAR(100) NULL DEFAULT NULL AFTER rawat_visit_dokter_konsul_3;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: tanda_tangan_perawat_ruangan
SET @columnname = 'tanda_tangan_perawat_ruangan';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column tanda_tangan_perawat_ruangan already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN tanda_tangan_perawat_ruangan VARCHAR(255) NULL DEFAULT NULL AFTER perawat_ruangan;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: perawat_ubs
SET @columnname = 'perawat_ubs';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column perawat_ubs already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN perawat_ubs VARCHAR(100) NULL DEFAULT NULL AFTER tanda_tangan_perawat_ruangan;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom: tanda_tangan_perawat_ubs
SET @columnname = 'tanda_tangan_perawat_ubs';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column tanda_tangan_perawat_ubs already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN tanda_tangan_perawat_ubs VARCHAR(255) NULL DEFAULT NULL AFTER perawat_ubs;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- VERIFIKASI
-- =====================================================

SELECT '✓ Checking all required columns...' AS status;

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME IN (
    'no_rm', 'nama', 'jenis_kelamin', 'umur', 'tanggal_lahir',
    'nama_dokter_konsul_1', 'nama_dokter_konsul_2', 'nama_dokter_konsul_3',
    'perawat_ruangan', 'tanda_tangan_perawat_ruangan',
    'perawat_ubs', 'tanda_tangan_perawat_ubs'
  )
ORDER BY ORDINAL_POSITION;

SELECT '✓ All missing columns have been added successfully!' AS status;

-- =====================================================
-- CATATAN:
-- =====================================================
-- 1. Kolom keterangan (ket_*) TIDAK DITAMBAHKAN karena SUDAH ADA
-- 2. Script ini aman dijalankan berulang kali
-- 3. Tidak akan merusak data existing
-- =====================================================
