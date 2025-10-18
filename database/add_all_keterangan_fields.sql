-- ============================================================================
-- TAMBAHKAN SEMUA FIELD KETERANGAN YANG MUNGKIN HILANG
-- ============================================================================
-- Script ini akan menambahkan field keterangan jika belum ada
-- Jika sudah ada, akan muncul error "Duplicate column" (AMAN, abaikan saja)
-- ============================================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
-- Item 12: Waktu puasa (jika belum ada)
ADD COLUMN IF NOT EXISTS `waktu_puasa` VARCHAR(100) DEFAULT NULL COMMENT 'Item 12: Sejak kapan puasa' AFTER `puasa`,

-- Item 14: DC No dan Macam (MUNGKIN HILANG!)
ADD COLUMN IF NOT EXISTS `dc_no` INT(11) DEFAULT NULL COMMENT 'Item 14: Nomor DC' AFTER `pasang_dc`,
ADD COLUMN IF NOT EXISTS `dc_macam` VARCHAR(100) DEFAULT NULL COMMENT 'Item 14: Macam DC' AFTER `dc_no`,

-- Item 20-22: Kantong transfusi
ADD COLUMN IF NOT EXISTS `kantong_wb` INT(11) DEFAULT NULL COMMENT 'Item 20: Jumlah kantong WB' AFTER `transfusi_whole_blood`,
ADD COLUMN IF NOT EXISTS `kantong_prc` INT(11) DEFAULT NULL COMMENT 'Item 21: Jumlah kantong PRC' AFTER `transfusi_prc`,
ADD COLUMN IF NOT EXISTS `kantong_ffp` INT(11) DEFAULT NULL COMMENT 'Item 22: Jumlah kantong FFP' AFTER `transfusi_ffp`,

-- Item 24: Antibiotik
ADD COLUMN IF NOT EXISTS `antibiotik_preops` VARCHAR(200) DEFAULT NULL COMMENT 'Item 24: Antibiotik pre-ops (nama obat)' AFTER `premedikasi`,
ADD COLUMN IF NOT EXISTS `jam_antibiotik` TIME DEFAULT NULL COMMENT 'Item 24: Jam pemberian antibiotik' AFTER `antibiotik_preops`,

-- Item 28: Obat Lain
ADD COLUMN IF NOT EXISTS `obat_lain` TEXT DEFAULT NULL COMMENT 'Item 28: Obat Lain (keterangan)' AFTER `asma_obat`,

-- Item 30: IV Catch No
ADD COLUMN IF NOT EXISTS `iv_catch_no` VARCHAR(50) DEFAULT NULL COMMENT 'Item 30: IV Catch No' AFTER `pasang_infus`,

-- Item 31-34: Vital Signs
ADD COLUMN IF NOT EXISTS `tekanan_darah` VARCHAR(20) DEFAULT NULL COMMENT 'Item 31: Tekanan Darah' AFTER `iv_catch_no`,
ADD COLUMN IF NOT EXISTS `nadi` VARCHAR(20) DEFAULT NULL COMMENT 'Item 32: Nadi' AFTER `tekanan_darah`,
ADD COLUMN IF NOT EXISTS `suhu` DECIMAL(4,1) DEFAULT NULL COMMENT 'Item 33: Suhu' AFTER `nadi`,
ADD COLUMN IF NOT EXISTS `pernafasan` VARCHAR(20) DEFAULT NULL COMMENT 'Item 34: Pernapasan' AFTER `suhu`,

-- Item 36: Hasil Skin Test
ADD COLUMN IF NOT EXISTS `hasil_skin_test` ENUM('Positif','Negatif') DEFAULT NULL COMMENT 'Item 36: Hasil Skin Test' AFTER `obat_ubs`;

-- ============================================================================
-- CATATAN:
-- ============================================================================
-- 1. Jika field sudah ada, akan muncul error "Duplicate column name" - ABAIKAN
-- 2. Jika MySQL versi lama tidak support "IF NOT EXISTS", hapus bagian itu
-- 3. Setelah jalankan, cek dengan: SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi;
-- ============================================================================

-- Verifikasi
SELECT 'Script selesai! Cek field dengan query berikut:' AS status;
SELECT '========================================' AS separator;
SELECT 'SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi WHERE Field IN (
    "waktu_puasa", "dc_no", "dc_macam",
    "kantong_wb", "kantong_prc", "kantong_ffp",
    "antibiotik_preops", "jam_antibiotik",
    "obat_lain", "iv_catch_no",
    "tekanan_darah", "nadi", "suhu", "pernafasan",
    "hasil_skin_test"
);' AS query_verifikasi;
