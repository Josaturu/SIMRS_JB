-- ============================================
-- ADD KETERANGAN VISIT DOKTER COLUMNS
-- ============================================
-- Menambahkan kolom keterangan untuk visit dokter
-- yang hilang di tabel persiapan operasi
-- ============================================

USE dbanestesi;

-- Tambah kolom keterangan visit dokter bedah
ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN ket_visit_dokter_bedah VARCHAR(255) DEFAULT NULL 
COMMENT 'Keterangan Visit Dokter Bedah'
AFTER rawat_visit_dokter_bedah;

-- Tambah kolom keterangan visit dokter anestesi
ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN ket_visit_dokter_anestesi VARCHAR(255) DEFAULT NULL 
COMMENT 'Keterangan Visit Dokter Anestesi'
AFTER rawat_visit_dokter_anestesi;

-- Tambah kolom keterangan visit dokter konsul 1
ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN ket_visit_dokter_konsul_1 VARCHAR(255) DEFAULT NULL 
COMMENT 'Keterangan Visit Dokter Konsul 1'
AFTER rawat_visit_dokter_konsul_1;

-- Tambah kolom keterangan visit dokter konsul 2
ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN ket_visit_dokter_konsul_2 VARCHAR(255) DEFAULT NULL 
COMMENT 'Keterangan Visit Dokter Konsul 2'
AFTER rawat_visit_dokter_konsul_2;

-- Tambah kolom keterangan visit dokter konsul 3
ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN ket_visit_dokter_konsul_3 VARCHAR(255) DEFAULT NULL 
COMMENT 'Keterangan Visit Dokter Konsul 3'
AFTER rawat_visit_dokter_konsul_3;

-- Verifikasi kolom berhasil ditambahkan
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME IN (
      'ket_visit_dokter_bedah',
      'ket_visit_dokter_anestesi',
      'ket_visit_dokter_konsul_1',
      'ket_visit_dokter_konsul_2',
      'ket_visit_dokter_konsul_3'
  )
ORDER BY ORDINAL_POSITION;

-- Success message
SELECT '✅ Migration completed! 5 keterangan columns added successfully.' AS status;
