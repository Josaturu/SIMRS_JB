-- ============================================
-- CHECK IF COLUMN EXISTS
-- ============================================

-- Method 1: Check column in INFORMATION_SCHEMA
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
  AND COLUMN_NAME = 'jenis_diagnosa';

-- Expected Result:
-- COLUMN_NAME     | DATA_TYPE   | COLUMN_DEFAULT | IS_NULLABLE
-- jenis_diagnosa  | varchar     | Elektif        | YES

-- If result is EMPTY, column does NOT exist!

-- ============================================

-- Method 2: DESCRIBE table
DESCRIBE tbl_anestesi_konsultasi_anestesi;

-- Look for line:
-- jenis_diagnosa | varchar(20) | YES | | Elektif

-- ============================================

-- Method 3: Show CREATE TABLE
SHOW CREATE TABLE tbl_anestesi_konsultasi_anestesi;

-- Look for: `jenis_diagnosa` varchar(20) DEFAULT 'Elektif'

-- ============================================

-- If column does NOT exist, run this:
-- ALTER TABLE tbl_anestesi_konsultasi_anestesi 
-- ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
-- COMMENT 'Jenis diagnosa: Cito atau Elektif'
-- AFTER diagnosa_pra_operasi;

-- ============================================
