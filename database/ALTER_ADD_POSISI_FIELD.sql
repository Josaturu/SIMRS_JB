-- ============================================
-- ALTER TABLE: Tambah Field POSISI
-- Dibuat: 2025-10-24
-- Deskripsi: Menambahkan field 'posisi' untuk menyimpan checkbox posisi pasien
-- ============================================

-- Tambah field 'posisi' setelah field 'infus_perifer'
ALTER TABLE `tbl_anestesi_catatan_anestesi` 
ADD COLUMN `posisi` text DEFAULT NULL COMMENT 'Posisi pasien (comma separated: SUPINE, LITHOTOMI, PRONE, LATERAL, PERLINDUNGAN MATA)' 
AFTER `infus_perifer`;

-- Verifikasi perubahan
DESCRIBE `tbl_anestesi_catatan_anestesi`;

-- Cek field posisi sudah ada
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

-- ============================================
-- CATATAN
-- ============================================

/*
Field 'posisi' akan menyimpan data checkbox posisi pasien dalam format comma-separated:
Contoh: "SUPINE, LITHOTOMI, PERLINDUNGAN MATA"

Checkbox yang tersedia:
1. SUPINE
2. LITHOTOMI
3. PRONE
4. LATERAL
5. PERLINDUNGAN MATA
6. Lain-lain : (akan disimpan di field 'lain_lain_posisi')

Setelah menjalankan ALTER TABLE ini, data posisi akan bisa disimpan dengan benar.
*/
