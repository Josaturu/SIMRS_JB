-- ============================================================================
-- TAMBAHKAN FIELD KETERANGAN (SAFE VERSION - UNTUK MYSQL LAMA)
-- ============================================================================
-- Script ini kompatibel dengan MySQL/MariaDB versi lama
-- Jika field sudah ada, akan error - ABAIKAN error tersebut
-- ============================================================================

-- Item 12: Waktu puasa
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `waktu_puasa` VARCHAR(100) DEFAULT NULL COMMENT 'Item 12: Sejak kapan puasa' AFTER `puasa`;

-- Item 14: DC No dan Macam
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `dc_no` INT(11) DEFAULT NULL COMMENT 'Item 14: Nomor DC' AFTER `pasang_dc`;

ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `dc_macam` VARCHAR(100) DEFAULT NULL COMMENT 'Item 14: Macam DC' AFTER `dc_no`;

-- Item 20: Kantong WB
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `kantong_wb` INT(11) DEFAULT NULL COMMENT 'Item 20: Jumlah kantong WB' AFTER `transfusi_whole_blood`;

-- Item 21: Kantong PRC
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `kantong_prc` INT(11) DEFAULT NULL COMMENT 'Item 21: Jumlah kantong PRC' AFTER `transfusi_prc`;

-- Item 22: Kantong FFP
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `kantong_ffp` INT(11) DEFAULT NULL COMMENT 'Item 22: Jumlah kantong FFP' AFTER `transfusi_ffp`;

-- Item 24: Antibiotik nama
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `antibiotik_preops` VARCHAR(200) DEFAULT NULL COMMENT 'Item 24: Antibiotik pre-ops (nama obat)' AFTER `premedikasi`;

-- Item 24: Antibiotik jam
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `jam_antibiotik` TIME DEFAULT NULL COMMENT 'Item 24: Jam pemberian antibiotik' AFTER `antibiotik_preops`;

-- Item 28: Obat Lain
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `obat_lain` TEXT DEFAULT NULL COMMENT 'Item 28: Obat Lain (keterangan)' AFTER `asma_obat`;

-- Item 30: IV Catch No
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `iv_catch_no` VARCHAR(50) DEFAULT NULL COMMENT 'Item 30: IV Catch No' AFTER `pasang_infus`;

-- Item 31: Tekanan Darah
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `tekanan_darah` VARCHAR(20) DEFAULT NULL COMMENT 'Item 31: Tekanan Darah' AFTER `iv_catch_no`;

-- Item 32: Nadi
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `nadi` VARCHAR(20) DEFAULT NULL COMMENT 'Item 32: Nadi' AFTER `tekanan_darah`;

-- Item 33: Suhu
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `suhu` DECIMAL(4,1) DEFAULT NULL COMMENT 'Item 33: Suhu' AFTER `nadi`;

-- Item 34: Pernapasan
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `pernafasan` VARCHAR(20) DEFAULT NULL COMMENT 'Item 34: Pernapasan' AFTER `suhu`;

-- Item 36: Hasil Skin Test
ALTER TABLE `tbl_anestesi_persiapan_operasi`
ADD COLUMN `hasil_skin_test` ENUM('Positif','Negatif') DEFAULT NULL COMMENT 'Item 36: Hasil Skin Test' AFTER `obat_ubs`;

-- ============================================================================
-- SELESAI!
-- ============================================================================
SELECT 'Field keterangan berhasil ditambahkan!' AS status;
SELECT 'Jika ada error "Duplicate column", abaikan saja (artinya field sudah ada)' AS catatan;
