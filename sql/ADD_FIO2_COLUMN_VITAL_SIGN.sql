-- =====================================================
-- ADD FIO2 COLUMN TO VITAL SIGN TABLE
-- File: ADD_FIO2_COLUMN_VITAL_SIGN.sql
-- Tanggal: 2025-10-29
-- =====================================================
--
-- DESKRIPSI:
-- Menambahkan kolom fio2 ke tabel tbl_anestesi_vital_sign
-- karena form memiliki input FIO2 tapi database belum ada
--
-- =====================================================

USE dbanestesi;

SET @dbname = 'dbanestesi';
SET @tablename = 'tbl_anestesi_vital_sign';

-- Tambahkan kolom fio2
SET @columnname = 'fio2';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column fio2 already exists' AS status;",
  "ALTER TABLE tbl_anestesi_vital_sign ADD COLUMN fio2 INT(11) NULL DEFAULT NULL COMMENT 'FIO2 (%)' AFTER td_diastolik;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verifikasi
SELECT 'Verifying column...' AS status;

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_vital_sign'
  AND COLUMN_NAME = 'fio2';

SELECT '✓ Column fio2 added successfully!' AS status;
