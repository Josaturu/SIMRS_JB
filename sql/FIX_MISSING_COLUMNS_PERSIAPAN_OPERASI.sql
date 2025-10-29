-- =====================================================
-- FIX MISSING COLUMNS: Persiapan Operasi
-- File: FIX_MISSING_COLUMNS_PERSIAPAN_OPERASI.sql
-- Tanggal: 2025-10-29
-- =====================================================
-- 
-- DESKRIPSI:
-- Menambahkan kolom-kolom yang mungkin hilang di tabel
-- tbl_anestesi_persiapan_operasi
--
-- KOLOM YANG DICEK DAN DITAMBAHKAN JIKA TIDAK ADA:
-- 1. no_rm - Nomor Rekam Medis
-- 2. id - Primary Key (jika belum ada)
-- 3. Kolom-kolom lain yang diperlukan
--
-- =====================================================

USE dbanestesi;

-- Cek struktur tabel saat ini
SELECT 'Checking current table structure...' AS status;

-- =====================================================
-- TAMBAHKAN KOLOM YANG HILANG
-- =====================================================

-- 1. Tambahkan kolom id jika belum ada (sebagai PRIMARY KEY)
SET @dbname = 'dbanestesi';
SET @tablename = 'tbl_anestesi_persiapan_operasi';
SET @columnname = 'id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column id already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Tambahkan kolom no_rm jika belum ada
SET @columnname = 'no_rm';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column no_rm already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL DEFAULT NULL AFTER id;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Tambahkan kolom no_rawat jika belum ada
SET @columnname = 'no_rawat';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column no_rawat already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL DEFAULT NULL AFTER no_rm;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Tambahkan kolom kode_paket jika belum ada
SET @columnname = 'kode_paket';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column kode_paket already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(50) NULL DEFAULT NULL AFTER no_rawat;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Tambahkan kolom nama jika belum ada
SET @columnname = 'nama';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column nama already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100) NULL DEFAULT NULL AFTER kode_paket;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 6. Tambahkan kolom jenis_kelamin jika belum ada
SET @columnname = 'jenis_kelamin';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column jenis_kelamin already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('L','P') NULL DEFAULT NULL AFTER nama;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 7. Tambahkan kolom umur jika belum ada
SET @columnname = 'umur';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column umur already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT(11) NULL DEFAULT NULL AFTER jenis_kelamin;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 8. Tambahkan kolom tanggal_lahir jika belum ada
SET @columnname = 'tanggal_lahir';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column tanggal_lahir already exists' AS status;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " DATE NULL DEFAULT NULL AFTER umur;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verifikasi kolom yang sudah ditambahkan
SELECT 'Verification: Checking all required columns...' AS status;

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME IN ('id', 'no_rm', 'no_rawat', 'kode_paket', 'nama', 'jenis_kelamin', 'umur', 'tanggal_lahir')
ORDER BY ORDINAL_POSITION;

SELECT '✓ Fix completed! All required columns checked and added if missing.' AS status;

-- =====================================================
-- CATATAN:
-- =====================================================
-- Script ini menggunakan prepared statement untuk cek
-- dan menambahkan kolom hanya jika belum ada.
-- Aman dijalankan berulang kali.
-- =====================================================
