-- ============================================================================
-- CREATE TABLE LENGKAP: tbl_anestesi_persiapan_operasi
-- ============================================================================
-- Tabel ini berisi SEMUA field yang diperlukan untuk form persiapan operasi
-- Termasuk:
-- - 40 kolom checklist UBS (Item 1-41)
-- - 40 kolom checklist R.RAWAT (Item 1-41)
-- - 15 kolom keterangan (waktu_puasa, DC, kantong, antibiotik, vital signs, dll)
-- - Kolom metadata (id, timestamps, dll)
-- Total: ~100 kolom
-- ============================================================================

-- BACKUP dulu data yang ada (PENTING!)
-- CREATE TABLE tbl_anestesi_persiapan_operasi_backup AS SELECT * FROM tbl_anestesi_persiapan_operasi;

-- DROP table lama (HATI-HATI! Backup dulu!)
-- DROP TABLE IF EXISTS `tbl_anestesi_persiapan_operasi`;

-- Buat table baru dengan struktur lengkap
CREATE TABLE IF NOT EXISTS `tbl_anestesi_persiapan_operasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` varchar(20) NOT NULL,
  `kode_paket` varchar(20) NOT NULL,
  
  -- ========================================================================
  -- INFORMASI DASAR
  -- ========================================================================
  `tanggal_operasi` date DEFAULT NULL,
  `macam_operasi` varchar(200) DEFAULT NULL,
  `dpjp` varchar(100) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL COMMENT 'Tinggi badan (cm)',
  `berat_badan` decimal(5,2) DEFAULT NULL COMMENT 'Berat badan (kg)',
  `gol_darah` varchar(5) DEFAULT NULL COMMENT 'Golongan darah',
  `riwayat_alergi` text DEFAULT NULL,
  
  -- ========================================================================
  -- ITEM 1-11: ADMINISTRASI
  -- ========================================================================
  `program_ke_ubs` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 1: Program ke UBS',
  `rawat_program_ke_ubs` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 1',
  
  `persetujuan_operasi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 2: Persetujuan Operasi',
  `rawat_persetujuan_operasi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 2',
  
  `rekam_medis` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 3: Rekam Medis',
  `rawat_rekam_medis` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 3',
  
  `laporan_operasi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 4: Laporan Operasi',
  `rawat_laporan_operasi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 4',
  
  `laporan_anestesi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 5: Laporan Anestesi',
  `rawat_laporan_anestesi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 5',
  
  `hasil_lab` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 6: Hasil Lab',
  `rawat_hasil_lab` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 6',
  
  `hasil_radiologi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 7: Hasil Radiologi',
  `rawat_hasil_radiologi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 7',
  
  `hasil_ct_scan` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 8: Hasil CT Scan',
  `rawat_hasil_ct_scan` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 8',
  
  `hasil_usg` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 9: Hasil USG',
  `rawat_hasil_usg` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 9',
  
  `hasil_ekg` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 10: Hasil EKG',
  `rawat_hasil_ekg` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 10',
  
  `hasil_lain` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 11: Lain-lain',
  `rawat_hasil_lain` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 11',
  
  -- ========================================================================
  -- ITEM 12-24: FISIK
  -- ========================================================================
  `puasa` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 12: Puasa',
  `rawat_puasa` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 12',
  `waktu_puasa` varchar(100) DEFAULT NULL COMMENT 'Item 12: Sejak kapan puasa',
  
  `lavement` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 13: Lavement',
  `rawat_lavement` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 13',
  
  `pasang_dc` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 14: Pasang DC',
  `rawat_pasang_dc` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 14',
  `dc_no` int(11) DEFAULT NULL COMMENT 'Item 14: Nomor DC',
  `dc_macam` varchar(100) DEFAULT NULL COMMENT 'Item 14: Macam DC',
  
  `cukur_daerah_operasi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 15: Cukur daerah operasi',
  `rawat_cukur_daerah_operasi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 15',
  
  `rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 16: Rambut palsu dilepas',
  `rawat_rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 16',
  
  `cat_kuku_dibersihkan` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 17: Cat kuku dibersihkan',
  `rawat_cat_kuku_dibersihkan` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 17',
  
  `perhiasan_dilepas` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 18: Perhiasan dilepas',
  `rawat_perhiasan_dilepas` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 18',
  
  `transfusi_darah` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 19: Persiapan darah untuk transfusi',
  `rawat_transfusi_darah` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 19',
  
  `transfusi_whole_blood` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 20: Whole Blood (WB)',
  `rawat_transfusi_whole_blood` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 20',
  `kantong_wb` int(11) DEFAULT NULL COMMENT 'Item 20: Jumlah kantong WB',
  
  `transfusi_prc` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 21: PRC',
  `rawat_transfusi_prc` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 21',
  `kantong_prc` int(11) DEFAULT NULL COMMENT 'Item 21: Jumlah kantong PRC',
  
  `transfusi_ffp` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 22: FFP',
  `rawat_transfusi_ffp` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 22',
  `kantong_ffp` int(11) DEFAULT NULL COMMENT 'Item 22: Jumlah kantong FFP',
  
  `premedikasi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 23: Premedikasi',
  `rawat_premedikasi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 23',
  
  `antibiotik` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 24: Antibiotik pre-ops (radio)',
  `rawat_antibiotik` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 24',
  `antibiotik_preops` varchar(200) DEFAULT NULL COMMENT 'Item 24: Nama antibiotik',
  `jam_antibiotik` time DEFAULT NULL COMMENT 'Item 24: Jam pemberian antibiotik',
  
  -- ========================================================================
  -- ITEM 25-41: KHUSUS
  -- ========================================================================
  `dm_insulin_preop` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 25: DM - Insulin Pre Op',
  `rawat_dm_insulin_preop` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 25',
  
  `hipertensi_obat` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 26: Hipertensi - Obat anti hipertensi',
  `rawat_hipertensi_obat` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 26',
  
  `asma_obat` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 27: Asma - Obat anti asma',
  `rawat_asma_obat` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 27',
  
  `obat_lain_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 28: Obat Lain (radio)',
  `rawat_obat_lain_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 28',
  `obat_lain` text DEFAULT NULL COMMENT 'Item 28: Obat Lain (keterangan)',
  
  `obat_tidur` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 29: Obat-obatan sebelum tidur',
  `rawat_obat_tidur` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 29',
  
  `pasang_infus` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 30: Pasang Infus',
  `rawat_pasang_infus` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 30',
  `iv_catch_no` varchar(50) DEFAULT NULL COMMENT 'Item 30: IV Catch No',
  
  `tekanan_darah_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 31: Tekanan Darah (radio)',
  `rawat_tekanan_darah_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 31',
  `tekanan_darah` varchar(20) DEFAULT NULL COMMENT 'Item 31: Tekanan Darah (nilai)',
  
  `nadi_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 32: Nadi (radio)',
  `rawat_nadi_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 32',
  `nadi` varchar(20) DEFAULT NULL COMMENT 'Item 32: Nadi (nilai)',
  
  `suhu_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 33: Suhu (radio)',
  `rawat_suhu_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 33',
  `suhu` decimal(4,1) DEFAULT NULL COMMENT 'Item 33: Suhu (nilai)',
  
  `pernafasan_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 34: Pernapasan (radio)',
  `rawat_pernafasan_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 34',
  `pernafasan` varchar(20) DEFAULT NULL COMMENT 'Item 34: Pernapasan (nilai)',
  
  `obat_ubs` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 35: Obat yang dibawa ke UBS',
  `rawat_obat_ubs` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 35',
  
  `skin_test_radio` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 36: Skin Test (radio)',
  `rawat_skin_test_radio` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 36',
  `hasil_skin_test` enum('Positif','Negatif') DEFAULT NULL COMMENT 'Item 36: Hasil Skin Test',
  
  `visit_dokter_bedah` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 37: Kunjungan dokter bedah',
  `rawat_visit_dokter_bedah` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 37',
  
  `visit_dokter_anestesi` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 38: Kunjungan dokter anestesi',
  `rawat_visit_dokter_anestesi` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 38',
  
  `visit_dokter_konsul_1` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 39: Dokter konsul 1',
  `rawat_visit_dokter_konsul_1` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 39',
  
  `visit_dokter_konsul_2` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 40: Dokter konsul 2',
  `rawat_visit_dokter_konsul_2` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 40',
  
  `visit_dokter_konsul_3` tinyint(1) DEFAULT 0 COMMENT 'UBS Item 41: Dokter konsul 3',
  `rawat_visit_dokter_konsul_3` tinyint(1) DEFAULT 0 COMMENT 'R.RAWAT Item 41',
  
  -- ========================================================================
  -- METADATA
  -- ========================================================================
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rawat_paket` (`no_rawat`, `kode_paket`),
  KEY `idx_no_rawat` (`no_rawat`),
  KEY `idx_tanggal` (`tanggal_operasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SELESAI!
-- ============================================================================
-- Total kolom: ~105 kolom
-- - 40 kolom UBS checklist
-- - 40 kolom R.RAWAT checklist
-- - 15 kolom keterangan
-- - 10 kolom informasi dasar + metadata
-- ============================================================================
