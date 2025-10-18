-- ============================================================================
-- TAMBAHKAN KOLOM UNTUK ITEM YANG HILANG
-- ============================================================================
-- Item 17: Cat kuku dan make up muka sudah dibersihkan
-- Item 39-41: Kunjungan dokter konsul terkait (3 item)
-- Total: 8 kolom baru (4 UBS + 4 R.RAWAT)
-- ============================================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
-- Item 17: Cat kuku (setelah rambut_makeup_dibersihkan)
ADD COLUMN `cat_kuku_dibersihkan` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 17: Cat kuku dibersihkan' AFTER `rambut_makeup_dibersihkan`,
ADD COLUMN `rawat_cat_kuku_dibersihkan` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 17: Cat kuku dibersihkan' AFTER `cat_kuku_dibersihkan`,

-- Item 39: Dokter Konsul 1 (setelah visit_dokter_anestesi)
ADD COLUMN `visit_dokter_konsul_1` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 39: Visit dokter konsul 1' AFTER `visit_dokter_anestesi`,
ADD COLUMN `rawat_visit_dokter_konsul_1` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 39: Visit dokter konsul 1' AFTER `visit_dokter_konsul_1`,

-- Item 40: Dokter Konsul 2
ADD COLUMN `visit_dokter_konsul_2` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 40: Visit dokter konsul 2' AFTER `rawat_visit_dokter_konsul_1`,
ADD COLUMN `rawat_visit_dokter_konsul_2` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 40: Visit dokter konsul 2' AFTER `visit_dokter_konsul_2`,

-- Item 41: Dokter Konsul 3
ADD COLUMN `visit_dokter_konsul_3` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 41: Visit dokter konsul 3' AFTER `rawat_visit_dokter_konsul_2`,
ADD COLUMN `rawat_visit_dokter_konsul_3` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 41: Visit dokter konsul 3' AFTER `visit_dokter_konsul_3`;

-- Verifikasi
SELECT 'Kolom tambahan berhasil ditambahkan!' AS status;
SHOW COLUMNS FROM `tbl_anestesi_persiapan_operasi` WHERE Field IN (
    'cat_kuku_dibersihkan', 'rawat_cat_kuku_dibersihkan',
    'visit_dokter_konsul_1', 'rawat_visit_dokter_konsul_1',
    'visit_dokter_konsul_2', 'rawat_visit_dokter_konsul_2',
    'visit_dokter_konsul_3', 'rawat_visit_dokter_konsul_3'
);

-- ============================================================================
-- CATATAN:
-- ============================================================================
-- 1. Jalankan script ini SETELAH add_rawat_columns.sql berhasil
-- 2. Total kolom yang ditambahkan: 8 kolom
-- 3. Setelah ini, update submit handler dan form untuk mapping kolom baru
-- ============================================================================
