-- ============================================
-- ALTER TABLE BOOKING_OPERASI (SIMPLE VERSION)
-- ============================================
-- Menambahkan kolom ruang_rawat, dokter_rawat, dan perawat
-- TANPA foreign key constraint (lebih aman)
-- ============================================

USE dbanestesi;

-- ============================================
-- TAMBAH KOLOM SAJA (TANPA FK)
-- ============================================

-- Cek apakah kolom sudah ada, jika belum baru tambah
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND COLUMN_NAME = 'ruang_rawat');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE booking_operasi ADD COLUMN ruang_rawat VARCHAR(20) DEFAULT NULL COMMENT "ID Ruang Rawat Inap" AFTER kd_ruang_ok',
    'SELECT "Kolom ruang_rawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tambah kolom dokter_rawat
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND COLUMN_NAME = 'dokter_rawat');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE booking_operasi ADD COLUMN dokter_rawat VARCHAR(20) DEFAULT NULL COMMENT "ID Dokter Rawat" AFTER kd_dokter',
    'SELECT "Kolom dokter_rawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tambah kolom perawat
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND COLUMN_NAME = 'perawat');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE booking_operasi ADD COLUMN perawat VARCHAR(20) DEFAULT NULL COMMENT "ID Perawat" AFTER dokter_rawat',
    'SELECT "Kolom perawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TAMBAH INDEX UNTUK PERFORMA
-- ============================================

-- Index untuk ruang_rawat
SET @idx_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND INDEX_NAME = 'idx_ruang_rawat');

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_ruang_rawat ON booking_operasi(ruang_rawat)',
    'SELECT "Index idx_ruang_rawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk dokter_rawat
SET @idx_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND INDEX_NAME = 'idx_dokter_rawat');

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_dokter_rawat ON booking_operasi(dokter_rawat)',
    'SELECT "Index idx_dokter_rawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk perawat
SET @idx_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND INDEX_NAME = 'idx_perawat');

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_perawat ON booking_operasi(perawat)',
    'SELECT "Index idx_perawat sudah ada" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- VERIFY HASIL
-- ============================================

-- Cek kolom yang baru ditambahkan
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
AND TABLE_NAME = 'booking_operasi'
AND COLUMN_NAME IN ('ruang_rawat', 'dokter_rawat', 'perawat')
ORDER BY ORDINAL_POSITION;

-- Cek index yang baru ditambahkan
SELECT 
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX,
    NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'dbanestesi'
AND TABLE_NAME = 'booking_operasi'
AND INDEX_NAME IN ('idx_ruang_rawat', 'idx_dokter_rawat', 'idx_perawat')
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- ============================================
-- QUERY HELPER - UNTUK DROPDOWN/SELECT
-- ============================================

-- Query untuk dropdown Ruang Rawat Inap (jika tabel tbl_ruang ada)
-- Uncomment jika tabel master sudah dibuat
/*
SELECT 
    id_ruang,
    kode_ruang,
    nama_ruang,
    lantai,
    kapasitas,
    CONCAT(nama_ruang, ' - Lantai ', lantai, ' (Kapasitas: ', kapasitas, ')') AS display_text
FROM tbl_ruang
WHERE jenis_ruang = 'RAWAT_INAP'
AND status = 'aktif'
ORDER BY nama_ruang;
*/

-- Query untuk dropdown Dokter Rawat (jika tabel tbl_dokter ada)
/*
SELECT 
    id_dokter,
    kode_dokter,
    nama_dokter,
    spesialisasi,
    CONCAT(nama_dokter, ' - ', IFNULL(spesialisasi, 'Umum')) AS display_text
FROM tbl_dokter
WHERE status = 'aktif'
ORDER BY nama_dokter;
*/

-- Query untuk dropdown Perawat (jika tabel tbl_perawat ada)
/*
SELECT 
    id_perawat,
    kode_perawat,
    nama_perawat,
    unit_kerja,
    shift,
    CONCAT(nama_perawat, ' - ', IFNULL(unit_kerja, 'Umum'), ' (', IFNULL(shift, '-'), ')') AS display_text
FROM tbl_perawat
WHERE status = 'aktif'
ORDER BY nama_perawat;
*/

-- ============================================
-- SAMPLE UPDATE DATA (OPTIONAL)
-- ============================================
-- Contoh update data booking dengan ruang rawat, dokter rawat, dan perawat
-- Uncomment jika ingin mengisi data sample dan tabel master sudah ada

/*
UPDATE booking_operasi 
SET 
    ruang_rawat = 'RG007',  -- Ruang Rawat Inap VIP
    dokter_rawat = 'DR008',  -- dr. Dewi Lestari, Sp.PD
    perawat = 'PR010'        -- Ns. Joko Susilo, S.Kep
WHERE no_rawat = '1' AND kode_paket = '1' AND tanggal = '2025-02-01' AND jam_mulai = '23:59:00';
*/

-- ============================================
-- SELESAI
-- ============================================
-- Kolom berhasil ditambahkan:
-- 1. ruang_rawat VARCHAR(20)
-- 2. dokter_rawat VARCHAR(20)
-- 3. perawat VARCHAR(20)
--
-- Index berhasil ditambahkan:
-- 1. idx_ruang_rawat
-- 2. idx_dokter_rawat
-- 3. idx_perawat
--
-- CATATAN:
-- - Foreign key TIDAK ditambahkan untuk menghindari error
-- - Kolom bisa diisi manual tanpa constraint
-- - Jika ingin tambah FK, pastikan tabel master sudah ada
-- ============================================
