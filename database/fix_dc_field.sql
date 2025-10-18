-- ============================================================================
-- FIX: Tambahkan Field DC (Item 14)
-- ============================================================================
-- Item 14: Pasang DC - perlu 2 field keterangan (No. dan Macam)
-- ============================================================================
-- CATATAN: Jika field sudah ada, akan muncul error "Duplicate column" - ABAIKAN
-- ============================================================================

-- Tambahkan field dc_no
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `dc_no` INT(11) DEFAULT NULL COMMENT 'Item 14: Nomor DC' AFTER `pasang_dc`;

-- Tambahkan field dc_macam
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `dc_macam` VARCHAR(100) DEFAULT NULL COMMENT 'Item 14: Macam DC' AFTER `dc_no`;

-- ============================================================================
-- SELESAI!
-- ============================================================================
-- Verifikasi dengan query:
-- SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi WHERE Field IN ('dc_no', 'dc_macam');
-- ============================================================================
