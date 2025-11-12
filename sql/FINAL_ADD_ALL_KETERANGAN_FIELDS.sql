-- =====================================================
-- FINAL: ADD ALL KETERANGAN FIELDS
-- Persiapan Operasi - Complete Migration
-- =====================================================
-- Tanggal: 30 Oktober 2025
-- Tujuan: Menambahkan SEMUA field keterangan yang belum ada
-- Total: 14 field baru (Fisik: 7, Khusus: 7)
-- =====================================================

USE `dbanestesi`;

-- =====================================================
-- PERSIAPAN FISIK - KETERANGAN (7 fields)
-- =====================================================
-- Yang DIKECUALIKAN (sudah ada field khusus):
-- - Puasa: sudah ada waktu_puasa
-- - Pasang DC: sudah ada dc_no, dc_macam
-- - WB/PRC/FFP: sudah ada kantong_wb, kantong_prc, kantong_ffp
-- - Antibiotik: sudah ada antibiotik_preops, jam_antibiotik
-- =====================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN IF NOT EXISTS `ket_lavement` VARCHAR(255) NULL 
    COMMENT 'Keterangan Lavement/Garam Inggris' 
    AFTER `ket_hasil_lain`,

ADD COLUMN IF NOT EXISTS `ket_cukur_daerah_operasi` VARCHAR(255) NULL 
    COMMENT 'Keterangan Cukur dan Bersihkan Daerah Operasi' 
    AFTER `ket_lavement`,

ADD COLUMN IF NOT EXISTS `ket_rambut_makeup_dibersihkan` VARCHAR(255) NULL 
    COMMENT 'Keterangan Rambut Palsu/Gigi Palsu/Contact Lens/Make Up' 
    AFTER `ket_cukur_daerah_operasi`,

ADD COLUMN IF NOT EXISTS `ket_cat_kuku_dibersihkan` VARCHAR(255) NULL 
    COMMENT 'Keterangan Cat Kuku dan Make Up Muka' 
    AFTER `ket_rambut_makeup_dibersihkan`,

ADD COLUMN IF NOT EXISTS `ket_perhiasan_dilepas` VARCHAR(255) NULL 
    COMMENT 'Keterangan Perhiasan dan Arloji' 
    AFTER `ket_cat_kuku_dibersihkan`,

ADD COLUMN IF NOT EXISTS `ket_transfusi_darah` VARCHAR(255) NULL 
    COMMENT 'Keterangan Persiapan Darah untuk Transfusi' 
    AFTER `ket_perhiasan_dilepas`,

ADD COLUMN IF NOT EXISTS `ket_premedikasi` VARCHAR(255) NULL 
    COMMENT 'Keterangan Premedikasi' 
    AFTER `ket_transfusi_darah`;

-- =====================================================
-- PERSIAPAN KHUSUS - KETERANGAN (7 fields)
-- =====================================================
-- Yang DIKECUALIKAN (sudah ada field khusus):
-- - Obat Lain: sudah ada obat_lain
-- - Pasang Infus: sudah ada iv_catch_no
-- - Tekanan Darah: sudah ada tekanan_darah
-- - Nadi: sudah ada nadi
-- - Suhu: sudah ada suhu
-- - Pernapasan: sudah ada pernafasan
-- - Skin Test: sudah ada hasil_skin_test
-- =====================================================

ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN IF NOT EXISTS `ket_dm_insulin_preop` VARCHAR(255) NULL 
    COMMENT 'Keterangan DM - Insulin Pre Op' 
    AFTER `ket_premedikasi`,

ADD COLUMN IF NOT EXISTS `ket_hipertensi_obat` VARCHAR(255) NULL 
    COMMENT 'Keterangan Hipertensi - Obat Anti Hipertensi Pre Ops' 
    AFTER `ket_dm_insulin_preop`,

ADD COLUMN IF NOT EXISTS `ket_asma_obat` VARCHAR(255) NULL 
    COMMENT 'Keterangan Asma - Obat Anti Asma/Corticosteroid Pre Ops' 
    AFTER `ket_hipertensi_obat`,

ADD COLUMN IF NOT EXISTS `ket_obat_tidur` VARCHAR(255) NULL 
    COMMENT 'Keterangan Obat-obatan Sebelum Tidur' 
    AFTER `ket_asma_obat`,

ADD COLUMN IF NOT EXISTS `ket_obat_ubs` VARCHAR(255) NULL 
    COMMENT 'Keterangan Obat yang Dibawa ke UBS' 
    AFTER `ket_obat_tidur`,

ADD COLUMN IF NOT EXISTS `ket_visit_dokter_bedah` VARCHAR(255) NULL 
    COMMENT 'Keterangan Kunjungan Dokter Pra Bedah - Dokter Bedah' 
    AFTER `ket_obat_ubs`,

ADD COLUMN IF NOT EXISTS `ket_visit_dokter_anestesi` VARCHAR(255) NULL 
    COMMENT 'Keterangan Kunjungan Dokter Pra Bedah - Dokter Anestesi' 
    AFTER `ket_visit_dokter_bedah`,

ADD COLUMN IF NOT EXISTS `ket_visit_dokter_konsul_1` VARCHAR(255) NULL 
    COMMENT 'Keterangan Kunjungan Dokter Pra Bedah - Dokter Konsul 1' 
    AFTER `ket_visit_dokter_anestesi`,

ADD COLUMN IF NOT EXISTS `ket_visit_dokter_konsul_2` VARCHAR(255) NULL 
    COMMENT 'Keterangan Kunjungan Dokter Pra Bedah - Dokter Konsul 2' 
    AFTER `ket_visit_dokter_konsul_1`,

ADD COLUMN IF NOT EXISTS `ket_visit_dokter_konsul_3` VARCHAR(255) NULL 
    COMMENT 'Keterangan Kunjungan Dokter Pra Bedah - Dokter Konsul 3' 
    AFTER `ket_visit_dokter_konsul_2`;

-- =====================================================
-- VERIFICATION QUERY
-- =====================================================

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME LIKE 'ket_%'
ORDER BY ORDINAL_POSITION;

-- =====================================================
-- EXPECTED RESULT: 28 rows
-- =====================================================
-- Administrasi (11):
--   1. ket_program_ke_ubs
--   2. ket_persetujuan_operasi
--   3. ket_rekam_medis
--   4. ket_laporan_operasi
--   5. ket_laporan_anestesi
--   6. ket_hasil_lab
--   7. ket_hasil_radiologi
--   8. ket_hasil_ct_scan
--   9. ket_hasil_usg
--  10. ket_hasil_ekg
--  11. ket_hasil_lain
--
-- Fisik (7):
--  12. ket_lavement
--  13. ket_cukur_daerah_operasi
--  14. ket_rambut_makeup_dibersihkan
--  15. ket_cat_kuku_dibersihkan
--  16. ket_perhiasan_dilepas
--  17. ket_transfusi_darah
--  18. ket_premedikasi
--
-- Khusus (10):
--  19. ket_dm_insulin_preop
--  20. ket_hipertensi_obat
--  21. ket_asma_obat
--  22. ket_obat_tidur
--  23. ket_obat_ubs
--  24. ket_visit_dokter_bedah
--  25. ket_visit_dokter_anestesi
--  26. ket_visit_dokter_konsul_1
--  27. ket_visit_dokter_konsul_2
--  28. ket_visit_dokter_konsul_3
-- =====================================================

-- Success message
SELECT 'Migration completed successfully! 17 new keterangan fields added.' AS status;
