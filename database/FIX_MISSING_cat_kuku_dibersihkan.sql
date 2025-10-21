-- ============================================================================
-- FIX: Tambahkan Kolom cat_kuku_dibersihkan yang Hilang
-- ============================================================================
-- Database: dbanestesi
-- Table: tbl_anestesi_persiapan_operasi
-- Tanggal: 21 Oktober 2025
-- ============================================================================
-- PROBLEM: 
-- Submit handler menggunakan kolom 'cat_kuku_dibersihkan' tapi tidak ada di database
-- Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'cat_kuku_dibersihkan'
-- ============================================================================

USE `dbanestesi`;

-- Tambahkan kolom cat_kuku_dibersihkan di UBS
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `cat_kuku_dibersihkan` TINYINT(1) DEFAULT NULL 
COMMENT 'UBS Item 17: Cat kuku dibersihkan'
AFTER `rambut_makeup_dibersihkan`;

-- Verifikasi
SELECT 'Kolom cat_kuku_dibersihkan berhasil ditambahkan!' AS status;

SHOW COLUMNS FROM `tbl_anestesi_persiapan_operasi` 
WHERE Field = 'cat_kuku_dibersihkan';

-- ============================================================================
-- SELESAI!
-- ============================================================================
-- CARA MENJALANKAN:
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Pilih database dbanestesi
-- 3. Klik tab SQL
-- 4. Copy-paste script ini
-- 5. Klik Go
-- 6. Test form persiapan operasi
-- ============================================================================
