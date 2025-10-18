-- ============================================================================
-- TABLE: tbl_anestesi_persiapan_operasi (FIXED VERSION)
-- Deskripsi: Table untuk Checklist Persiapan Operasi
-- Form: RMO-1 a
-- ============================================================================

-- Drop table jika sudah ada (HATI-HATI: Ini akan menghapus data!)
-- DROP TABLE IF EXISTS `tbl_anestesi_persiapan_operasi`;

CREATE TABLE `tbl_anestesi_persiapan_operasi` (
  -- PRIMARY KEY
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  -- FOREIGN KEYS & IDENTIFIERS
  `no_rawat` varchar(20) NOT NULL COMMENT 'Nomor rawat pasien (FK ke booking_operasi)',
  `kode_paket` varchar(50) NOT NULL COMMENT 'Kode paket operasi (FK ke booking_operasi)',
  
  -- DATA MASUK DAN KONDISI PASIEN
  `tanggal_operasi` date DEFAULT NULL COMMENT 'Tanggal operasi dijadwalkan',
  `macam_operasi` varchar(200) DEFAULT NULL COMMENT 'Jenis/macam operasi yang akan dilakukan',
  `dpjp` varchar(255) DEFAULT NULL COMMENT 'Dokter Penanggung Jawab Pelayanan',
  `tinggi_badan` decimal(5,2) DEFAULT NULL COMMENT 'Tinggi badan pasien (cm)',
  `berat_badan` decimal(5,2) DEFAULT NULL COMMENT 'Berat badan pasien (kg)',
  `gol_darah` enum('A','B','AB','O') DEFAULT NULL COMMENT 'Golongan darah pasien',
  `riwayat_alergi` text DEFAULT NULL COMMENT 'Riwayat alergi pasien',
  
  -- CHECKLIST ADMINISTRASI (Item 1-11)
  `program_ke_ubs` tinyint(1) DEFAULT 0 COMMENT 'Item 1: Program ke UBS',
  `persetujuan_operasi` tinyint(1) DEFAULT 0 COMMENT 'Item 2: Persetujuan Operasi Lengkap',
  `rekam_medis` tinyint(1) DEFAULT 0 COMMENT 'Item 3: Rekam Medis',
  `laporan_operasi` tinyint(1) DEFAULT 0 COMMENT 'Item 4: Laporan Operasi',
  `laporan_anestesi` tinyint(1) DEFAULT 0 COMMENT 'Item 5: Laporan Anestesi',
  `hasil_lab` tinyint(1) DEFAULT 0 COMMENT 'Item 6: Hasil Laboratorium',
  `hasil_radiologi` tinyint(1) DEFAULT 0 COMMENT 'Item 7: Hasil Radiologi',
  `hasil_ct_scan` tinyint(1) DEFAULT 0 COMMENT 'Item 8: Hasil CT Scan',
  `hasil_usg` tinyint(1) DEFAULT 0 COMMENT 'Item 9: Hasil USG',
  `hasil_ekg` tinyint(1) DEFAULT 0 COMMENT 'Item 10: Hasil EKG',
  `hasil_lain` tinyint(1) DEFAULT 0 COMMENT 'Item 11: Lain-lain',
  
  -- CHECKLIST FISIK (Item 12-24)
  `puasa` tinyint(1) DEFAULT 0 COMMENT 'Item 12: Puasa',
  `waktu_puasa` varchar(100) DEFAULT NULL COMMENT 'Item 12: Sejak kapan puasa',
  `lavement` tinyint(1) DEFAULT 0 COMMENT 'Item 13: Lavement/garam Inggris',
  `pasang_dc` tinyint(1) DEFAULT 0 COMMENT 'Item 14: Pasang DC',
  `cukur_daerah_operasi` tinyint(1) DEFAULT 0 COMMENT 'Item 15: Cukur daerah operasi',
  `rambut_makeup_dibersihkan` tinyint(1) DEFAULT 0 COMMENT 'Item 16: Rambut palsu, gigi palsu, contact lens dilepas',
  -- Item 17: Cat kuku dan makeup dibersihkan (tidak ada field karena tidak ada di database mapping)
  `perhiasan_dilepas` tinyint(1) DEFAULT 0 COMMENT 'Item 18: Perhiasan dan arloji dilepas',
  -- Item 19: Persiapan darah untuk transfusi (header, tidak ada field)
  `transfusi_whole_blood` tinyint(1) DEFAULT 0 COMMENT 'Item 20: Whole Blood (WB)',
  `kantong_wb` int(11) DEFAULT NULL COMMENT 'Item 20: Jumlah kantong WB',
  `transfusi_prc` tinyint(1) DEFAULT 0 COMMENT 'Item 21: PRC',
  `kantong_prc` int(11) DEFAULT NULL COMMENT 'Item 21: Jumlah kantong PRC',
  `transfusi_ffp` tinyint(1) DEFAULT 0 COMMENT 'Item 22: FFP',
  `kantong_ffp` int(11) DEFAULT NULL COMMENT 'Item 22: Jumlah kantong FFP',
  `premedikasi` tinyint(1) DEFAULT 0 COMMENT 'Item 23: Premedikasi',
  `antibiotik_preops` varchar(200) DEFAULT NULL COMMENT 'Item 24: Antibiotik pre-ops (nama obat)',
  `jam_antibiotik` time DEFAULT NULL COMMENT 'Item 24: Jam pemberian antibiotik',
  
  -- CHECKLIST KHUSUS (Item 25-41)
  `dm_insulin_preop` tinyint(1) DEFAULT 0 COMMENT 'Item 25: DM - Insulin Pre Op',
  `hipertensi_obat` tinyint(1) DEFAULT 0 COMMENT 'Item 26: Hipertensi - Obat anti hipertensi',
  `asma_obat` tinyint(1) DEFAULT 0 COMMENT 'Item 27: Asma - Obat anti asma',
  `obat_lain` text DEFAULT NULL COMMENT 'Item 28: Obat Lain (keterangan)',
  `obat_tidur` tinyint(1) DEFAULT 0 COMMENT 'Item 29: Obat-obatan sebelum tidur',
  `pasang_infus` tinyint(1) DEFAULT 0 COMMENT 'Item 30: Pasang Infus',
  `iv_catch_no` varchar(50) DEFAULT NULL COMMENT 'Item 30: IV Catch No',
  `tekanan_darah` varchar(20) DEFAULT NULL COMMENT 'Item 31: Tekanan Darah',
  `nadi` varchar(20) DEFAULT NULL COMMENT 'Item 32: Nadi',
  `suhu` decimal(4,1) DEFAULT NULL COMMENT 'Item 33: Suhu',
  `pernafasan` varchar(20) DEFAULT NULL COMMENT 'Item 34: Pernapasan',
  `obat_ubs` tinyint(1) DEFAULT 0 COMMENT 'Item 35: Obat yang dibawa ke UBS',
  `hasil_skin_test` enum('Positif','Negatif') DEFAULT NULL COMMENT 'Item 36: Hasil Skin Test',
  `visit_dokter_bedah` tinyint(1) DEFAULT 0 COMMENT 'Item 37: Kunjungan dokter bedah',
  `visit_dokter_anestesi` tinyint(1) DEFAULT 0 COMMENT 'Item 38: Kunjungan dokter anestesi',
  -- Item 39-41: Dokter konsul terkait (tidak digunakan di form saat ini)
  
  -- METADATA
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu data dibuat',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu data terakhir diupdate',
  
  -- INDEXES
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_checklist` (`no_rawat`, `kode_paket`) COMMENT 'Satu pasien hanya punya satu checklist per booking',
  KEY `idx_no_rawat` (`no_rawat`),
  KEY `idx_kode_paket` (`kode_paket`),
  KEY `idx_tanggal_operasi` (`tanggal_operasi`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Checklist Persiapan Operasi (RMO-1 a)';

-- ============================================================================
-- CATATAN PENTING:
-- ============================================================================
-- 1. Field yang menggunakan tinyint(1) adalah untuk checkbox/radio Ya/Tidak
--    - 0 = Tidak
--    - 1 = Ya
--
-- 2. UNIQUE KEY pada (no_rawat, kode_paket) memastikan:
--    - Satu pasien hanya punya satu checklist per booking operasi
--    - Mencegah duplikasi data
--    - Mendukung logika INSERT/UPDATE di submit handler
--
-- 3. Field yang NULL-able adalah field yang opsional
--
-- 4. Perbedaan dengan table lama:
--    - Menghapus field yang tidak digunakan: no_rm, nama, jenis_kelamin, umur, tanggal_lahir
--    - Menambahkan UNIQUE constraint untuk mencegah duplikasi
--    - Mengubah waktu_puasa dari DATETIME ke VARCHAR untuk fleksibilitas input
--    - Menambahkan komentar pada setiap field untuk dokumentasi
--    - Menambahkan index untuk performa query
--
-- 5. Migrasi data dari table lama:
--    Jika sudah ada data di table lama, gunakan script migrasi terpisah
-- ============================================================================
