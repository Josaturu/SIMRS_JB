-- ============================================================================
-- TAMBAHKAN KOLOM RADIO BUTTON YANG HILANG
-- ============================================================================
-- Item yang tidak punya kolom TINYINT untuk radio button Ya/Tidak:
-- - Item 19: Persiapan darah untuk transfusi
-- - Item 24: Antibiotik pre-ops (sudah ada keterangan, tambah radio)
-- - Item 28: Obat Lain (sudah ada keterangan, tambah radio)
-- - Item 31: Tekanan Darah (sudah ada keterangan, tambah radio)
-- - Item 32: Nadi (sudah ada keterangan, tambah radio)
-- - Item 33: Suhu (sudah ada keterangan, tambah radio)
-- - Item 34: Pernapasan (sudah ada keterangan, tambah radio)
-- - Item 36: Hasil Skin Test (sudah ada ENUM, tambah radio)
-- Total: 16 kolom baru (8 UBS + 8 R.RAWAT)
-- ============================================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
-- Item 19: Persiapan darah untuk transfusi (setelah perhiasan_dilepas)
ADD COLUMN `transfusi_darah` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 19: Persiapan darah untuk transfusi' AFTER `rawat_perhiasan_dilepas`,
ADD COLUMN `rawat_transfusi_darah` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 19: Persiapan darah' AFTER `transfusi_darah`,

-- Item 24: Antibiotik pre-ops (radio button, setelah rawat_premedikasi)
ADD COLUMN `antibiotik` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 24: Antibiotik pre-ops (radio)' AFTER `rawat_premedikasi`,
ADD COLUMN `rawat_antibiotik` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 24: Antibiotik (radio)' AFTER `antibiotik`,

-- Item 28: Obat Lain (radio button, setelah rawat_asma_obat)
ADD COLUMN `obat_lain_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 28: Obat Lain (radio)' AFTER `rawat_asma_obat`,
ADD COLUMN `rawat_obat_lain_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 28: Obat Lain (radio)' AFTER `obat_lain_radio`,

-- Item 31: Tekanan Darah (radio button, setelah rawat_pasang_infus)
ADD COLUMN `tekanan_darah_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 31: Tekanan Darah (radio)' AFTER `iv_catch_no`,
ADD COLUMN `rawat_tekanan_darah_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 31: Tekanan Darah (radio)' AFTER `tekanan_darah_radio`,

-- Item 32: Nadi (radio button, setelah tekanan_darah)
ADD COLUMN `nadi_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 32: Nadi (radio)' AFTER `tekanan_darah`,
ADD COLUMN `rawat_nadi_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 32: Nadi (radio)' AFTER `nadi_radio`,

-- Item 33: Suhu (radio button, setelah nadi)
ADD COLUMN `suhu_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 33: Suhu (radio)' AFTER `nadi`,
ADD COLUMN `rawat_suhu_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 33: Suhu (radio)' AFTER `suhu_radio`,

-- Item 34: Pernapasan (radio button, setelah suhu)
ADD COLUMN `pernafasan_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 34: Pernapasan (radio)' AFTER `suhu`,
ADD COLUMN `rawat_pernafasan_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 34: Pernapasan (radio)' AFTER `pernafasan_radio`,

-- Item 36: Hasil Skin Test (radio button, setelah rawat_obat_ubs)
ADD COLUMN `skin_test_radio` TINYINT(1) DEFAULT 0 COMMENT 'UBS Item 36: Skin Test (radio)' AFTER `rawat_obat_ubs`,
ADD COLUMN `rawat_skin_test_radio` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 36: Skin Test (radio)' AFTER `skin_test_radio`;

-- Verifikasi
SELECT 'Kolom radio button tambahan berhasil ditambahkan!' AS status;
SHOW COLUMNS FROM `tbl_anestesi_persiapan_operasi` WHERE Field LIKE '%_radio' OR Field LIKE '%transfusi_darah%' OR Field LIKE '%antibiotik' AND Field NOT LIKE '%antibiotik_preops%';

-- ============================================================================
-- CATATAN:
-- ============================================================================
-- 1. Jalankan script ini SETELAH add_rawat_columns.sql dan add_missing_columns.sql
-- 2. Total kolom yang ditambahkan: 16 kolom (8 UBS + 8 R.RAWAT)
-- 3. Kolom keterangan yang sudah ada tetap dipertahankan
-- 4. Setelah ini, update submit handler dan form untuk mapping kolom baru
-- ============================================================================
