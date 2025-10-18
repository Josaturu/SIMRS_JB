-- ============================================================================
-- MIGRATE DATA: Pindahkan data dari tabel lama ke tabel baru
-- ============================================================================
-- Script ini akan:
-- 1. Backup tabel lama
-- 2. Buat tabel baru dengan struktur lengkap
-- 3. Copy semua data yang ada ke tabel baru
-- 4. Rename tabel
-- ============================================================================

-- STEP 1: Backup tabel lama
-- ============================================================================
DROP TABLE IF EXISTS `tbl_anestesi_persiapan_operasi_backup`;
CREATE TABLE `tbl_anestesi_persiapan_operasi_backup` AS 
SELECT * FROM `tbl_anestesi_persiapan_operasi`;

SELECT CONCAT('✅ Backup selesai! Total rows: ', COUNT(*)) AS status 
FROM `tbl_anestesi_persiapan_operasi_backup`;

-- STEP 2: Rename tabel lama
-- ============================================================================
RENAME TABLE 
    `tbl_anestesi_persiapan_operasi` TO `tbl_anestesi_persiapan_operasi_old`;

-- STEP 3: Buat tabel baru dengan struktur lengkap
-- ============================================================================
-- (Copy dari CREATE_TABLE_LENGKAP.sql)

CREATE TABLE `tbl_anestesi_persiapan_operasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` varchar(20) NOT NULL,
  `kode_paket` varchar(20) NOT NULL,
  
  -- Informasi Dasar
  `tanggal_operasi` date DEFAULT NULL,
  `macam_operasi` varchar(200) DEFAULT NULL,
  `dpjp` varchar(100) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `gol_darah` varchar(5) DEFAULT NULL,
  `riwayat_alergi` text DEFAULT NULL,
  
  -- Item 1-11: Administrasi
  `program_ke_ubs` tinyint(1) DEFAULT 0,
  `rawat_program_ke_ubs` tinyint(1) DEFAULT 0,
  `persetujuan_operasi` tinyint(1) DEFAULT 0,
  `rawat_persetujuan_operasi` tinyint(1) DEFAULT 0,
  `rekam_medis` tinyint(1) DEFAULT 0,
  `rawat_rekam_medis` tinyint(1) DEFAULT 0,
  `laporan_operasi` tinyint(1) DEFAULT 0,
  `rawat_laporan_operasi` tinyint(1) DEFAULT 0,
  `laporan_anestesi` tinyint(1) DEFAULT 0,
  `rawat_laporan_anestesi` tinyint(1) DEFAULT 0,
  `hasil_lab` tinyint(1) DEFAULT 0,
  `rawat_hasil_lab` tinyint(1) DEFAULT 0,
  `hasil_radiologi` tinyint(1) DEFAULT 0,
  `rawat_hasil_radiologi` tinyint(1) DEFAULT 0,
  `hasil_ct_scan` tinyint(1) DEFAULT 0,
  `rawat_hasil_ct_scan` tinyint(1) DEFAULT 0,
  `hasil_usg` tinyint(1) DEFAULT 0,
  `rawat_hasil_usg` tinyint(1) DEFAULT 0,
  `hasil_ekg` tinyint(1) DEFAULT 0,
  `rawat_hasil_ekg` tinyint(1) DEFAULT 0,
  `hasil_lain` tinyint(1) DEFAULT 0,
  `rawat_hasil_lain` tinyint(1) DEFAULT 0,
  
  -- Item 12-24: Fisik
  `puasa` tinyint(1) DEFAULT 0,
  `rawat_puasa` tinyint(1) DEFAULT 0,
  `waktu_puasa` varchar(100) DEFAULT NULL,
  `lavement` tinyint(1) DEFAULT 0,
  `rawat_lavement` tinyint(1) DEFAULT 0,
  `pasang_dc` tinyint(1) DEFAULT 0,
  `rawat_pasang_dc` tinyint(1) DEFAULT 0,
  `dc_no` int(11) DEFAULT NULL,
  `dc_macam` varchar(100) DEFAULT NULL,
  `cukur_daerah_operasi` tinyint(1) DEFAULT 0,
  `rawat_cukur_daerah_operasi` tinyint(1) DEFAULT 0,
  `rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0,
  `rawat_rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0,
  `cat_kuku_dibersihkan` tinyint(1) DEFAULT 0,
  `rawat_cat_kuku_dibersihkan` tinyint(1) DEFAULT 0,
  `perhiasan_dilepas` tinyint(1) DEFAULT 0,
  `rawat_perhiasan_dilepas` tinyint(1) DEFAULT 0,
  `transfusi_darah` tinyint(1) DEFAULT 0,
  `rawat_transfusi_darah` tinyint(1) DEFAULT 0,
  `transfusi_whole_blood` tinyint(1) DEFAULT 0,
  `rawat_transfusi_whole_blood` tinyint(1) DEFAULT 0,
  `kantong_wb` int(11) DEFAULT NULL,
  `transfusi_prc` tinyint(1) DEFAULT 0,
  `rawat_transfusi_prc` tinyint(1) DEFAULT 0,
  `kantong_prc` int(11) DEFAULT NULL,
  `transfusi_ffp` tinyint(1) DEFAULT 0,
  `rawat_transfusi_ffp` tinyint(1) DEFAULT 0,
  `kantong_ffp` int(11) DEFAULT NULL,
  `premedikasi` tinyint(1) DEFAULT 0,
  `rawat_premedikasi` tinyint(1) DEFAULT 0,
  `antibiotik` tinyint(1) DEFAULT 0,
  `rawat_antibiotik` tinyint(1) DEFAULT 0,
  `antibiotik_preops` varchar(200) DEFAULT NULL,
  `jam_antibiotik` time DEFAULT NULL,
  
  -- Item 25-41: Khusus
  `dm_insulin_preop` tinyint(1) DEFAULT 0,
  `rawat_dm_insulin_preop` tinyint(1) DEFAULT 0,
  `hipertensi_obat` tinyint(1) DEFAULT 0,
  `rawat_hipertensi_obat` tinyint(1) DEFAULT 0,
  `asma_obat` tinyint(1) DEFAULT 0,
  `rawat_asma_obat` tinyint(1) DEFAULT 0,
  `obat_lain_radio` tinyint(1) DEFAULT 0,
  `rawat_obat_lain_radio` tinyint(1) DEFAULT 0,
  `obat_lain` text DEFAULT NULL,
  `obat_tidur` tinyint(1) DEFAULT 0,
  `rawat_obat_tidur` tinyint(1) DEFAULT 0,
  `pasang_infus` tinyint(1) DEFAULT 0,
  `rawat_pasang_infus` tinyint(1) DEFAULT 0,
  `iv_catch_no` varchar(50) DEFAULT NULL,
  `tekanan_darah_radio` tinyint(1) DEFAULT 0,
  `rawat_tekanan_darah_radio` tinyint(1) DEFAULT 0,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `nadi_radio` tinyint(1) DEFAULT 0,
  `rawat_nadi_radio` tinyint(1) DEFAULT 0,
  `nadi` varchar(20) DEFAULT NULL,
  `suhu_radio` tinyint(1) DEFAULT 0,
  `rawat_suhu_radio` tinyint(1) DEFAULT 0,
  `suhu` decimal(4,1) DEFAULT NULL,
  `pernafasan_radio` tinyint(1) DEFAULT 0,
  `rawat_pernafasan_radio` tinyint(1) DEFAULT 0,
  `pernafasan` varchar(20) DEFAULT NULL,
  `obat_ubs` tinyint(1) DEFAULT 0,
  `rawat_obat_ubs` tinyint(1) DEFAULT 0,
  `skin_test_radio` tinyint(1) DEFAULT 0,
  `rawat_skin_test_radio` tinyint(1) DEFAULT 0,
  `hasil_skin_test` enum('Positif','Negatif') DEFAULT NULL,
  `visit_dokter_bedah` tinyint(1) DEFAULT 0,
  `rawat_visit_dokter_bedah` tinyint(1) DEFAULT 0,
  `visit_dokter_anestesi` tinyint(1) DEFAULT 0,
  `rawat_visit_dokter_anestesi` tinyint(1) DEFAULT 0,
  `visit_dokter_konsul_1` tinyint(1) DEFAULT 0,
  `rawat_visit_dokter_konsul_1` tinyint(1) DEFAULT 0,
  `visit_dokter_konsul_2` tinyint(1) DEFAULT 0,
  `rawat_visit_dokter_konsul_2` tinyint(1) DEFAULT 0,
  `visit_dokter_konsul_3` tinyint(1) DEFAULT 0,
  `rawat_visit_dokter_konsul_3` tinyint(1) DEFAULT 0,
  
  -- Metadata
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rawat_paket` (`no_rawat`, `kode_paket`),
  KEY `idx_no_rawat` (`no_rawat`),
  KEY `idx_tanggal` (`tanggal_operasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 4: Copy data dari tabel lama ke tabel baru
-- ============================================================================
-- Hanya copy kolom yang ADA di tabel lama
-- Kolom baru akan otomatis NULL atau default value

INSERT INTO `tbl_anestesi_persiapan_operasi` (
    id, no_rawat, kode_paket,
    tanggal_operasi, macam_operasi, dpjp,
    tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
    
    -- Kolom yang ADA di tabel lama (sesuaikan dengan struktur tabel lama Anda)
    program_ke_ubs, persetujuan_operasi, rekam_medis, laporan_operasi,
    laporan_anestesi, hasil_lab, hasil_radiologi, hasil_ct_scan,
    hasil_usg, hasil_ekg, hasil_lain,
    puasa, lavement, pasang_dc, cukur_daerah_operasi,
    rambut_makeup_dibersihkan, perhiasan_dilepas,
    transfusi_whole_blood, transfusi_prc, transfusi_ffp,
    premedikasi,
    dm_insulin_preop, hipertensi_obat, asma_obat,
    obat_tidur, pasang_infus, obat_ubs,
    visit_dokter_bedah, visit_dokter_anestesi,
    
    -- Kolom keterangan yang ADA
    waktu_puasa, kantong_wb, kantong_prc, kantong_ffp,
    antibiotik_preops, jam_antibiotik, obat_lain, iv_catch_no,
    tekanan_darah, nadi, suhu, pernafasan, hasil_skin_test,
    
    created_at, updated_at
)
SELECT 
    id, no_rawat, kode_paket,
    tanggal_operasi, macam_operasi, dpjp,
    tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
    
    program_ke_ubs, persetujuan_operasi, rekam_medis, laporan_operasi,
    laporan_anestesi, hasil_lab, hasil_radiologi, hasil_ct_scan,
    hasil_usg, hasil_ekg, hasil_lain,
    puasa, lavement, pasang_dc, cukur_daerah_operasi,
    rambut_makeup_dibersihkan, perhiasan_dilepas,
    transfusi_whole_blood, transfusi_prc, transfusi_ffp,
    premedikasi,
    dm_insulin_preop, hipertensi_obat, asma_obat,
    obat_tidur, pasang_infus, obat_ubs,
    visit_dokter_bedah, visit_dokter_anestesi,
    
    waktu_puasa, kantong_wb, kantong_prc, kantong_ffp,
    antibiotik_preops, jam_antibiotik, obat_lain, iv_catch_no,
    tekanan_darah, nadi, suhu, pernafasan, hasil_skin_test,
    
    created_at, updated_at
FROM `tbl_anestesi_persiapan_operasi_old`;

-- STEP 5: Verifikasi
-- ============================================================================
SELECT 
    'Tabel Lama' AS tabel,
    COUNT(*) AS total_rows
FROM `tbl_anestesi_persiapan_operasi_old`
UNION ALL
SELECT 
    'Tabel Baru' AS tabel,
    COUNT(*) AS total_rows
FROM `tbl_anestesi_persiapan_operasi`;

-- Cek kolom baru
SELECT 
    '✅ Kolom baru berhasil ditambahkan!' AS status,
    COUNT(*) AS total_kolom
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi';

-- ============================================================================
-- SELESAI!
-- ============================================================================
-- Jika semua OK, hapus tabel lama:
-- DROP TABLE `tbl_anestesi_persiapan_operasi_old`;
-- 
-- Jika ada masalah, restore dari backup:
-- DROP TABLE `tbl_anestesi_persiapan_operasi`;
-- RENAME TABLE `tbl_anestesi_persiapan_operasi_old` TO `tbl_anestesi_persiapan_operasi`;
-- ============================================================================
