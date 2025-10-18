-- ============================================================================
-- SOLUSI: Tambahkan Kolom untuk R. RAWAT
-- ============================================================================
-- Masalah: Data kolom R. RAWAT tidak tersimpan karena tidak ada field di database
-- Solusi: Tambahkan kolom untuk menyimpan data R. RAWAT (41 item)
-- ============================================================================

-- OPSI 1: Tambahkan kolom di table yang sama (RECOMMENDED)
-- ============================================================================
ALTER TABLE `tbl_anestesi_persiapan_operasi`
-- Administrasi (Item 1-11)
ADD COLUMN `rawat_program_ke_ubs` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 1: Program ke UBS' AFTER `program_ke_ubs`,
ADD COLUMN `rawat_persetujuan_operasi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 2: Persetujuan Operasi' AFTER `persetujuan_operasi`,
ADD COLUMN `rawat_rekam_medis` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 3: Rekam Medis' AFTER `rekam_medis`,
ADD COLUMN `rawat_laporan_operasi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 4: Laporan Operasi' AFTER `laporan_operasi`,
ADD COLUMN `rawat_laporan_anestesi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 5: Laporan Anestesi' AFTER `laporan_anestesi`,
ADD COLUMN `rawat_hasil_lab` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 6: Hasil Lab' AFTER `hasil_lab`,
ADD COLUMN `rawat_hasil_radiologi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 7: Hasil Radiologi' AFTER `hasil_radiologi`,
ADD COLUMN `rawat_hasil_ct_scan` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 8: Hasil CT Scan' AFTER `hasil_ct_scan`,
ADD COLUMN `rawat_hasil_usg` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 9: Hasil USG' AFTER `hasil_usg`,
ADD COLUMN `rawat_hasil_ekg` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 10: Hasil EKG' AFTER `hasil_ekg`,
ADD COLUMN `rawat_hasil_lain` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 11: Lain-lain' AFTER `hasil_lain`,

-- Fisik (Item 12-24)
ADD COLUMN `rawat_puasa` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 12: Puasa' AFTER `puasa`,
ADD COLUMN `rawat_lavement` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 13: Lavement' AFTER `lavement`,
ADD COLUMN `rawat_pasang_dc` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 14: Pasang DC' AFTER `pasang_dc`,
ADD COLUMN `rawat_cukur_daerah_operasi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 15: Cukur daerah operasi' AFTER `cukur_daerah_operasi`,
ADD COLUMN `rawat_rambut_makeup_dibersihkan` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 16: Rambut palsu dilepas' AFTER `rambut_makeup_dibersihkan`,
ADD COLUMN `rawat_perhiasan_dilepas` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 18: Perhiasan dilepas' AFTER `perhiasan_dilepas`,
ADD COLUMN `rawat_transfusi_whole_blood` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 20: WB' AFTER `transfusi_whole_blood`,
ADD COLUMN `rawat_transfusi_prc` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 21: PRC' AFTER `transfusi_prc`,
ADD COLUMN `rawat_transfusi_ffp` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 22: FFP' AFTER `transfusi_ffp`,
ADD COLUMN `rawat_premedikasi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 23: Premedikasi' AFTER `premedikasi`,

-- Khusus (Item 25-41)
ADD COLUMN `rawat_dm_insulin_preop` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 25: DM Insulin' AFTER `dm_insulin_preop`,
ADD COLUMN `rawat_hipertensi_obat` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 26: Hipertensi' AFTER `hipertensi_obat`,
ADD COLUMN `rawat_asma_obat` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 27: Asma' AFTER `asma_obat`,
ADD COLUMN `rawat_obat_tidur` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 29: Obat tidur' AFTER `obat_tidur`,
ADD COLUMN `rawat_pasang_infus` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 30: Pasang Infus' AFTER `pasang_infus`,
ADD COLUMN `rawat_obat_ubs` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 35: Obat UBS' AFTER `obat_ubs`,
ADD COLUMN `rawat_visit_dokter_bedah` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 37: Visit dokter bedah' AFTER `visit_dokter_bedah`,
ADD COLUMN `rawat_visit_dokter_anestesi` TINYINT(1) DEFAULT 0 COMMENT 'R.RAWAT Item 38: Visit dokter anestesi' AFTER `visit_dokter_anestesi`;

-- Verifikasi
SELECT 'Kolom R. RAWAT berhasil ditambahkan!' AS status;
SHOW COLUMNS FROM `tbl_anestesi_persiapan_operasi` LIKE 'rawat_%';


-- ============================================================================
-- OPSI 2: Buat table terpisah (Alternatif jika table terlalu besar)
-- ============================================================================
/*
CREATE TABLE `tbl_anestesi_persiapan_operasi_rawat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` varchar(20) NOT NULL,
  `kode_paket` varchar(50) NOT NULL,
  
  -- Administrasi (Item 1-11)
  `rawat1` TINYINT(1) DEFAULT 0,
  `rawat2` TINYINT(1) DEFAULT 0,
  `rawat3` TINYINT(1) DEFAULT 0,
  `rawat4` TINYINT(1) DEFAULT 0,
  `rawat5` TINYINT(1) DEFAULT 0,
  `rawat6` TINYINT(1) DEFAULT 0,
  `rawat7` TINYINT(1) DEFAULT 0,
  `rawat8` TINYINT(1) DEFAULT 0,
  `rawat9` TINYINT(1) DEFAULT 0,
  `rawat10` TINYINT(1) DEFAULT 0,
  `rawat11` TINYINT(1) DEFAULT 0,
  
  -- Fisik (Item 12-24)
  `rawat12` TINYINT(1) DEFAULT 0,
  `rawat13` TINYINT(1) DEFAULT 0,
  `rawat14` TINYINT(1) DEFAULT 0,
  `rawat15` TINYINT(1) DEFAULT 0,
  `rawat16` TINYINT(1) DEFAULT 0,
  `rawat18` TINYINT(1) DEFAULT 0,
  `rawat20` TINYINT(1) DEFAULT 0,
  `rawat21` TINYINT(1) DEFAULT 0,
  `rawat22` TINYINT(1) DEFAULT 0,
  `rawat23` TINYINT(1) DEFAULT 0,
  
  -- Khusus (Item 25-41)
  `rawat25` TINYINT(1) DEFAULT 0,
  `rawat26` TINYINT(1) DEFAULT 0,
  `rawat27` TINYINT(1) DEFAULT 0,
  `rawat29` TINYINT(1) DEFAULT 0,
  `rawat30` TINYINT(1) DEFAULT 0,
  `rawat35` TINYINT(1) DEFAULT 0,
  `rawat37` TINYINT(1) DEFAULT 0,
  `rawat38` TINYINT(1) DEFAULT 0,
  
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rawat` (`no_rawat`, `kode_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/


-- ============================================================================
-- CATATAN:
-- ============================================================================
-- 1. OPSI 1 (RECOMMENDED): Tambahkan kolom di table yang sama
--    - Lebih mudah query (tidak perlu JOIN)
--    - Data terpusat dalam 1 table
--    - Lebih mudah maintenance
--
-- 2. OPSI 2: Buat table terpisah
--    - Jika table sudah terlalu besar
--    - Jika ingin memisahkan data R. RAWAT dan UBS
--    - Perlu JOIN saat query
--
-- 3. Setelah menambahkan kolom, update submit handler untuk menyimpan data R. RAWAT
-- ============================================================================
