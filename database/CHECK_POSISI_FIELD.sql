-- ============================================
-- CHECK: Apakah Field POSISI Sudah Ada?
-- ============================================

-- Cek field 'posisi' di tabel
SELECT 
  COLUMN_NAME, 
  COLUMN_TYPE, 
  IS_NULLABLE, 
  COLUMN_DEFAULT, 
  COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_catatan_anestesi' 
  AND COLUMN_NAME = 'posisi';

-- Jika hasil kosong (0 rows), berarti field belum ada
-- Jika ada 1 row, berarti field sudah ditambahkan

-- ============================================
-- JIKA FIELD BELUM ADA, JALANKAN INI:
-- ============================================

ALTER TABLE `tbl_anestesi_catatan_anestesi` 
ADD COLUMN `posisi` text DEFAULT NULL 
COMMENT 'Posisi pasien (comma separated)' 
AFTER `infus_perifer`;

-- ============================================
-- VERIFIKASI SETELAH ALTER
-- ============================================

DESCRIBE `tbl_anestesi_catatan_anestesi`;
