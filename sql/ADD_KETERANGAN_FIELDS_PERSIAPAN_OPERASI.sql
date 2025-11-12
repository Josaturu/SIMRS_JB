-- =====================================================
-- ADD KETERANGAN FIELDS - PERSIAPAN OPERASI
-- =====================================================
-- Tanggal: 30 Oktober 2025
-- Tujuan: Menambahkan field keterangan untuk Persiapan Fisik & Khusus
-- Total: 14 field baru
-- =====================================================

USE `dbanestesi`;

-- =====================================================
-- PERSIAPAN FISIK (7 fields)
-- =====================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `ket_lavement` VARCHAR(255) NULL COMMENT 'Keterangan Lavement/Garam Inggris' AFTER `ket_hasil_lain`,
ADD COLUMN `ket_cukur_daerah_operasi` VARCHAR(255) NULL COMMENT 'Keterangan Cukur Daerah Operasi' AFTER `ket_lavement`,
ADD COLUMN `ket_rambut_makeup_dibersihkan` VARCHAR(255) NULL COMMENT 'Keterangan Rambut Palsu/Gigi Palsu/Contact Lens' AFTER `ket_cukur_daerah_operasi`,
ADD COLUMN `ket_cat_kuku_dibersihkan` VARCHAR(255) NULL COMMENT 'Keterangan Cat Kuku dan Make Up' AFTER `ket_rambut_makeup_dibersihkan`,
ADD COLUMN `ket_perhiasan_dilepas` VARCHAR(255) NULL COMMENT 'Keterangan Perhiasan dan Arloji' AFTER `ket_cat_kuku_dibersihkan`,
ADD COLUMN `ket_transfusi_darah` VARCHAR(255) NULL COMMENT 'Keterangan Persiapan Darah Transfusi' AFTER `ket_perhiasan_dilepas`,
ADD COLUMN `ket_premedikasi` VARCHAR(255) NULL COMMENT 'Keterangan Premedikasi' AFTER `ket_transfusi_darah`;

-- =====================================================
-- PERSIAPAN KHUSUS (7 fields)
-- =====================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `ket_dm_insulin_preop` VARCHAR(255) NULL COMMENT 'Keterangan DM - Insulin Pre Op' AFTER `ket_premedikasi`,
ADD COLUMN `ket_hipertensi_obat` VARCHAR(255) NULL COMMENT 'Keterangan Hipertensi - Obat Anti Hipertensi' AFTER `ket_dm_insulin_preop`,
ADD COLUMN `ket_asma_obat` VARCHAR(255) NULL COMMENT 'Keterangan Asma - Obat Anti Asma' AFTER `ket_hipertensi_obat`,
ADD COLUMN `ket_obat_tidur` VARCHAR(255) NULL COMMENT 'Keterangan Obat Sebelum Tidur' AFTER `ket_asma_obat`,
ADD COLUMN `ket_obat_ubs` VARCHAR(255) NULL COMMENT 'Keterangan Obat yang Dibawa ke UBS' AFTER `ket_obat_tidur`,
ADD COLUMN `ket_visit_dokter_bedah` VARCHAR(255) NULL COMMENT 'Keterangan Visit Dokter Bedah' AFTER `ket_obat_ubs`,
ADD COLUMN `ket_visit_dokter_anestesi` VARCHAR(255) NULL COMMENT 'Keterangan Visit Dokter Anestesi' AFTER `ket_visit_dokter_bedah`;

-- =====================================================
-- VERIFICATION
-- =====================================================

-- Check if all columns added successfully
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME LIKE 'ket_%'
ORDER BY ORDINAL_POSITION;

-- Expected: 25 rows (11 existing + 14 new)

-- =====================================================
-- NOTES:
-- =====================================================
-- Field yang SUDAH ADA (tidak perlu ditambah):
-- - ket_program_ke_ubs
-- - ket_persetujuan_operasi
-- - ket_rekam_medis
-- - ket_laporan_operasi
-- - ket_laporan_anestesi
-- - ket_hasil_lab
-- - ket_hasil_radiologi
-- - ket_hasil_ct_scan
-- - ket_hasil_usg
-- - ket_hasil_ekg
-- - ket_hasil_lain
--
-- Field KHUSUS yang sudah ada (tidak butuh ket_ tambahan):
-- - waktu_puasa (untuk Puasa)
-- - dc_no, dc_macam (untuk Pasang DC)
-- - kantong_wb, kantong_prc, kantong_ffp (untuk Transfusi)
-- - antibiotik_preops, jam_antibiotik (untuk Antibiotik)
-- - obat_lain (untuk Obat Lain)
-- - iv_catch_no (untuk Pasang Infus)
-- - tekanan_darah, nadi, suhu, pernafasan (untuk Vital Signs)
-- - hasil_skin_test (untuk Skin Test)
-- - nama_dokter_konsul_1/2/3 (untuk Dokter Konsul)
-- =====================================================
