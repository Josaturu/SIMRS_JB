-- ============================================================================
-- SCRIPT CEK TABEL KONSULTASI ANESTESI
-- ============================================================================
-- Script ini untuk mengecek struktur tabel tbl_anestesi_konsultasi_anestesi
-- dan memastikan kolom jenis_kelamin ada
-- ============================================================================

-- 1. Cek apakah tabel ada
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME,
    UPDATE_TIME
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi';

-- 2. Cek struktur kolom tabel
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_KEY
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
ORDER BY ORDINAL_POSITION;

-- 3. Cek apakah kolom jenis_kelamin ada
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME = 'jenis_kelamin';

-- 4. Cek data jenis_kelamin yang ada
SELECT 
    id,
    no_rawat,
    jenis_kelamin,
    menikah,
    created_at
FROM tbl_anestesi_konsultasi_anestesi
ORDER BY created_at DESC
LIMIT 10;

-- 5. Cek apakah ada data dengan jenis_kelamin NULL
SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN jenis_kelamin IS NULL THEN 1 ELSE 0 END) as null_jenis_kelamin,
    SUM(CASE WHEN jenis_kelamin IS NOT NULL THEN 1 ELSE 0 END) as filled_jenis_kelamin
FROM tbl_anestesi_konsultasi_anestesi;
