-- ============================================
-- ALTER TABLE BOOKING_OPERASI
-- ============================================
-- Menambahkan kolom ruang_rawat, dokter_rawat, 
-- dan perawat dengan foreign key ke tabel master
-- ============================================

USE dbanestesi;

-- ============================================
-- CEK TABEL MASTER (HARUS ADA DULU!)
-- ============================================
-- Pastikan tabel master sudah dibuat sebelum menjalankan script ini
-- Jalankan CREATE_TABLES_DOKTER_RUANG_PERAWAT.sql terlebih dahulu

-- Cek apakah tabel master ada
SELECT 
    CASE 
        WHEN COUNT(*) = 3 THEN 'OK - Semua tabel master ada'
        ELSE CONCAT('ERROR - Hanya ada ', COUNT(*), ' dari 3 tabel master')
    END AS status
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'dbanestesi'
AND TABLE_NAME IN ('tbl_ruang', 'tbl_dokter', 'tbl_perawat');

-- ============================================
-- DROP FOREIGN KEY JIKA SUDAH ADA (SAFETY)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;

-- Drop constraint jika ada (ignore error jika tidak ada)
SET @sql1 = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND CONSTRAINT_NAME = 'fk_booking_ruang_rawat') > 0,
    'ALTER TABLE booking_operasi DROP FOREIGN KEY fk_booking_ruang_rawat',
    'SELECT "FK fk_booking_ruang_rawat tidak ada"');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND CONSTRAINT_NAME = 'fk_booking_dokter_rawat') > 0,
    'ALTER TABLE booking_operasi DROP FOREIGN KEY fk_booking_dokter_rawat',
    'SELECT "FK fk_booking_dokter_rawat tidak ada"');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql3 = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'dbanestesi' 
    AND TABLE_NAME = 'booking_operasi' 
    AND CONSTRAINT_NAME = 'fk_booking_perawat') > 0,
    'ALTER TABLE booking_operasi DROP FOREIGN KEY fk_booking_perawat',
    'SELECT "FK fk_booking_perawat tidak ada"');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- ALTER TABLE - TAMBAH KOLOM
-- ============================================

-- Tambah kolom ruang_rawat (FK ke tbl_ruang dengan jenis RAWAT_INAP)
-- Gunakan CHARACTER SET dan COLLATE yang sama dengan tabel master
ALTER TABLE booking_operasi 
ADD COLUMN IF NOT EXISTS ruang_rawat VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
COMMENT 'ID Ruang Rawat Inap (FK ke tbl_ruang)'
AFTER kd_ruang_ok;

-- Tambah kolom dokter_rawat (FK ke tbl_dokter)
ALTER TABLE booking_operasi 
ADD COLUMN IF NOT EXISTS dokter_rawat VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
COMMENT 'ID Dokter Rawat (FK ke tbl_dokter)'
AFTER kd_dokter;

-- Tambah kolom perawat (FK ke tbl_perawat)
ALTER TABLE booking_operasi 
ADD COLUMN IF NOT EXISTS perawat VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
COMMENT 'ID Perawat (FK ke tbl_perawat)'
AFTER dokter_rawat;

-- ============================================
-- TAMBAH INDEX UNTUK PERFORMA (SEBELUM FK)
-- ============================================

-- Index diperlukan untuk foreign key
CREATE INDEX IF NOT EXISTS idx_ruang_rawat ON booking_operasi(ruang_rawat);
CREATE INDEX IF NOT EXISTS idx_dokter_rawat ON booking_operasi(dokter_rawat);
CREATE INDEX IF NOT EXISTS idx_perawat ON booking_operasi(perawat);

-- ============================================
-- TAMBAH FOREIGN KEY CONSTRAINTS
-- ============================================

-- Foreign Key: ruang_rawat -> tbl_ruang
ALTER TABLE booking_operasi
ADD CONSTRAINT fk_booking_ruang_rawat
FOREIGN KEY (ruang_rawat) REFERENCES tbl_ruang(id_ruang)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- Foreign Key: dokter_rawat -> tbl_dokter
ALTER TABLE booking_operasi
ADD CONSTRAINT fk_booking_dokter_rawat
FOREIGN KEY (dokter_rawat) REFERENCES tbl_dokter(id_dokter)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- Foreign Key: perawat -> tbl_perawat
ALTER TABLE booking_operasi
ADD CONSTRAINT fk_booking_perawat
FOREIGN KEY (perawat) REFERENCES tbl_perawat(id_perawat)
ON DELETE SET NULL
ON UPDATE CASCADE;


-- ============================================
-- VERIFY STRUKTUR TABEL
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

-- Cek foreign key constraints
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'dbanestesi'
AND TABLE_NAME = 'booking_operasi'
AND CONSTRAINT_NAME IN ('fk_booking_ruang_rawat', 'fk_booking_dokter_rawat', 'fk_booking_perawat');

-- ============================================
-- SAMPLE UPDATE DATA (OPTIONAL)
-- ============================================
-- Contoh update data booking dengan ruang rawat, dokter rawat, dan perawat
-- Uncomment jika ingin mengisi data sample

/*
UPDATE booking_operasi 
SET 
    ruang_rawat = 'RG007',  -- Ruang Rawat Inap VIP
    dokter_rawat = 'DR008',  -- dr. Dewi Lestari, Sp.PD
    perawat = 'PR010'        -- Ns. Joko Susilo, S.Kep
WHERE no_rawat = '1' AND kode_paket = '1' AND tanggal = '2025-02-01' AND jam_mulai = '23:59:00';

UPDATE booking_operasi 
SET 
    ruang_rawat = 'RG008',  -- Ruang Rawat Inap Kelas 1
    dokter_rawat = 'DR008',  -- dr. Dewi Lestari, Sp.PD
    perawat = 'PR011'        -- Ns. Kartika Sari, S.Kep
WHERE no_rawat = 'TEST-001' AND kode_paket = 'PKT-001' AND tanggal = '2025-10-10' AND jam_mulai = '08:00:00';
*/

-- ============================================
-- QUERY HELPER - UNTUK DROPDOWN/SELECT
-- ============================================

-- Query untuk dropdown Ruang Rawat Inap (hanya RAWAT_INAP yang aktif)
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

-- Query untuk dropdown Dokter Rawat (semua dokter aktif)
SELECT 
    id_dokter,
    kode_dokter,
    nama_dokter,
    spesialisasi,
    CONCAT(nama_dokter, ' - ', IFNULL(spesialisasi, 'Umum')) AS display_text
FROM tbl_dokter
WHERE status = 'aktif'
ORDER BY nama_dokter;

-- Query untuk dropdown Perawat (semua perawat aktif)
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

-- ============================================
-- QUERY UNTUK FORM - JOIN DATA
-- ============================================

-- Query untuk menampilkan booking dengan detail ruang, dokter, dan perawat
SELECT 
    b.no_rawat,
    b.kode_paket,
    b.tanggal,
    b.jam_mulai,
    b.jam_selesai,
    b.status,
    
    -- Ruang Rawat
    r.kode_ruang AS kode_ruang_rawat,
    r.nama_ruang AS nama_ruang_rawat,
    r.lantai,
    
    -- Dokter Rawat
    d.kode_dokter AS kode_dokter_rawat,
    d.nama_dokter AS nama_dokter_rawat,
    d.spesialisasi AS spesialisasi_dokter,
    
    -- Perawat
    p.kode_perawat,
    p.nama_perawat,
    p.unit_kerja AS unit_kerja_perawat,
    p.shift AS shift_perawat
    
FROM booking_operasi b
LEFT JOIN tbl_ruang r ON b.ruang_rawat = r.id_ruang
LEFT JOIN tbl_dokter d ON b.dokter_rawat = d.id_dokter
LEFT JOIN tbl_perawat p ON b.perawat = p.id_perawat
WHERE b.status IN ('Menunggu', 'Proses Operasi')
ORDER BY b.tanggal DESC, b.jam_mulai DESC;

-- ============================================
-- SELESAI
-- ============================================
-- Kolom berhasil ditambahkan:
-- 1. ruang_rawat (FK ke tbl_ruang - RAWAT_INAP)
-- 2. dokter_rawat (FK ke tbl_dokter)
-- 3. perawat (FK ke tbl_perawat)
--
-- Foreign Key Constraints:
-- - ON DELETE SET NULL (jika master data dihapus, FK jadi NULL)
-- - ON UPDATE CASCADE (jika ID master berubah, FK ikut update)
-- ============================================
