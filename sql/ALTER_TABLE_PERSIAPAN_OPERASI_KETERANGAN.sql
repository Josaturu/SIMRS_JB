-- =====================================================
-- ALTER TABLE: Menambahkan Kolom Keterangan untuk Persiapan Administrasi
-- File: ALTER_TABLE_PERSIAPAN_OPERASI_KETERANGAN.sql
-- Tanggal: 2025-10-29
-- =====================================================
-- 
-- DESKRIPSI:
-- Menambahkan 11 kolom keterangan untuk bagian Persiapan Administrasi
-- di form persiapan operasi (item 1-11)
--
-- KOLOM YANG DITAMBAHKAN:
-- 1. ket_program_ke_ubs          - Keterangan untuk "Program ke UBS"
-- 2. ket_persetujuan_operasi     - Keterangan untuk "Persetujuan Operasi Lengkap dan Terisi"
-- 3. ket_rekam_medis             - Keterangan untuk "Rekam Medis"
-- 4. ket_laporan_operasi         - Keterangan untuk "Laporan Operasi"
-- 5. ket_laporan_anestesi        - Keterangan untuk "Laporan Anestesi"
-- 6. ket_hasil_lab               - Keterangan untuk "Hasil Laboratorium"
-- 7. ket_hasil_radiologi         - Keterangan untuk "Hasil Radiologi"
-- 8. ket_hasil_ct_scan           - Keterangan untuk "Hasil CT Scan"
-- 9. ket_hasil_usg               - Keterangan untuk "Hasil USG"
-- 10. ket_hasil_ekg              - Keterangan untuk "Hasil EKG"
-- 11. ket_hasil_lain             - Keterangan untuk "Lain-lain"
--
-- =====================================================

USE dbanestesi;

-- Cek apakah tabel ada
SELECT 'Checking table tbl_anestesi_persiapan_operasi...' AS status;

-- Tambahkan kolom keterangan untuk Persiapan Administrasi (item 1-11)
-- Menggunakan prepared statement untuk kompatibilitas dengan MySQL yang tidak support IF NOT EXISTS di ALTER TABLE

SET @dbname = 'dbanestesi';
SET @tablename = 'tbl_anestesi_persiapan_operasi';

-- Kolom 1: ket_program_ke_ubs
SET @columnname = 'ket_program_ke_ubs';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_program_ke_ubs already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_program_ke_ubs VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Program ke UBS';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 2: ket_persetujuan_operasi
SET @columnname = 'ket_persetujuan_operasi';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_persetujuan_operasi already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_persetujuan_operasi VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Persetujuan Operasi';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 3: ket_rekam_medis
SET @columnname = 'ket_rekam_medis';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_rekam_medis already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_rekam_medis VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Rekam Medis';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 4: ket_laporan_operasi
SET @columnname = 'ket_laporan_operasi';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_laporan_operasi already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_laporan_operasi VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Laporan Operasi';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 5: ket_laporan_anestesi
SET @columnname = 'ket_laporan_anestesi';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_laporan_anestesi already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_laporan_anestesi VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Laporan Anestesi';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 6: ket_hasil_lab
SET @columnname = 'ket_hasil_lab';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_lab already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_lab VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Hasil Laboratorium';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 7: ket_hasil_radiologi
SET @columnname = 'ket_hasil_radiologi';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_radiologi already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_radiologi VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Hasil Radiologi';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 8: ket_hasil_ct_scan
SET @columnname = 'ket_hasil_ct_scan';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_ct_scan already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_ct_scan VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Hasil CT Scan';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 9: ket_hasil_usg
SET @columnname = 'ket_hasil_usg';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_usg already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_usg VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Hasil USG';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 10: ket_hasil_ekg
SET @columnname = 'ket_hasil_ekg';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_ekg already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_ekg VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Hasil EKG';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kolom 11: ket_hasil_lain
SET @columnname = 'ket_hasil_lain';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 'Column ket_hasil_lain already exists' AS status;",
  "ALTER TABLE tbl_anestesi_persiapan_operasi ADD COLUMN ket_hasil_lain VARCHAR(255) NULL DEFAULT NULL COMMENT 'Keterangan Lain-lain';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verifikasi kolom yang ditambahkan
SELECT 'Verifying new columns...' AS status;

SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME LIKE 'ket_%'
ORDER BY ORDINAL_POSITION;

SELECT '✓ ALTER TABLE completed successfully!' AS status;

-- =====================================================
-- CATATAN PENGGUNAAN:
-- =====================================================
-- 
-- 1. Jalankan script ini di MySQL/phpMyAdmin
-- 2. Pastikan database 'dbanestesi' sudah ada
-- 3. Script ini aman dijalankan berulang kali (menggunakan IF NOT EXISTS)
-- 
-- MAPPING FORM KE DATABASE:
-- Form Input         | Database Column
-- -------------------|---------------------------
-- ket1               | ket_program_ke_ubs
-- ket2               | ket_persetujuan_operasi
-- ket3               | ket_rekam_medis
-- ket4               | ket_laporan_operasi
-- ket5               | ket_laporan_anestesi
-- ket6               | ket_hasil_lab
-- ket7               | ket_hasil_radiologi
-- ket8               | ket_hasil_ct_scan
-- ket9               | ket_hasil_usg
-- ket10              | ket_hasil_ekg
-- ket11              | ket_hasil_lain
--
-- =====================================================
