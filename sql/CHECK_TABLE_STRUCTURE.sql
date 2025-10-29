-- =====================================================
-- CHECK TABLE STRUCTURE: Persiapan Operasi
-- File: CHECK_TABLE_STRUCTURE.sql
-- =====================================================

USE dbanestesi;

-- Cek apakah tabel ada
SELECT 
    TABLE_NAME,
    TABLE_TYPE,
    ENGINE,
    TABLE_ROWS,
    CREATE_TIME,
    UPDATE_TIME
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi';

-- Tampilkan semua kolom di tabel
SELECT 
    ORDINAL_POSITION,
    COLUMN_NAME, 
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
ORDER BY ORDINAL_POSITION;

-- Cek apakah kolom no_rm ada
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Kolom no_rm DITEMUKAN ✓'
        ELSE 'Kolom no_rm TIDAK DITEMUKAN ✗'
    END AS status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'no_rm';
