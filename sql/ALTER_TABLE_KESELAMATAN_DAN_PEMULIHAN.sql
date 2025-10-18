-- =====================================================
-- ALTER TABLE untuk Form Keselamatan Operasi & Kamar Pemulihan
-- Database: dbanestesi
-- Tanggal: 11 Oktober 2025
-- =====================================================

USE dbanestesi;

-- =====================================================
-- 1. ALTER TABLE: tbl_anestesi_keselamatan_operasi
-- =====================================================
-- Drop kolom JSON lama (signin, timeout, signout)
ALTER TABLE tbl_anestesi_keselamatan_operasi
  DROP COLUMN signin,
  DROP COLUMN timeout,
  DROP COLUMN signout;

-- Tambah kolom untuk Informasi Pasien & Operasi
ALTER TABLE tbl_anestesi_keselamatan_operasi
  ADD COLUMN nama_pasien VARCHAR(150) NULL AFTER tanggal_tindakan,
  ADD COLUMN no_rekam_medis VARCHAR(50) NULL AFTER nama_pasien,
  ADD COLUMN tgl_lahir_umur VARCHAR(100) NULL AFTER no_rekam_medis,
  ADD COLUMN alamat TEXT NULL AFTER tgl_lahir_umur,
  ADD COLUMN operator VARCHAR(150) NULL AFTER alamat;

-- Tambah kolom untuk Sign In (7 items + 3 tim)
ALTER TABLE tbl_anestesi_keselamatan_operasi
  ADD COLUMN signin_time TIME NULL AFTER operator,
  ADD COLUMN signin_1a TINYINT(1) DEFAULT 0 COMMENT 'Identitas pasien' AFTER signin_time,
  ADD COLUMN signin_1b TINYINT(1) DEFAULT 0 COMMENT 'Lokasi operasi' AFTER signin_1a,
  ADD COLUMN signin_1c TINYINT(1) DEFAULT 0 COMMENT 'Prosedur' AFTER signin_1b,
  ADD COLUMN signin_1d TINYINT(1) DEFAULT 0 COMMENT 'Surat izin operasi' AFTER signin_1c,
  ADD COLUMN signin_2a TINYINT(1) DEFAULT 0 COMMENT 'Lokasi operasi diberi tanda: Ya' AFTER signin_1d,
  ADD COLUMN signin_2b TINYINT(1) DEFAULT 0 COMMENT 'Lokasi operasi diberi tanda: Tidak dilakukan' AFTER signin_2a,
  ADD COLUMN signin_3 TINYINT(1) DEFAULT 0 COMMENT 'Mesin anestesi dicek' AFTER signin_2b,
  ADD COLUMN signin_4 TINYINT(1) DEFAULT 0 COMMENT 'Pulse Oximeter terpasang' AFTER signin_3,
  ADD COLUMN signin_5a TINYINT(1) DEFAULT 0 COMMENT 'Riwayat alergi: Tidak' AFTER signin_4,
  ADD COLUMN signin_5b TINYINT(1) DEFAULT 0 COMMENT 'Riwayat alergi: Ya' AFTER signin_5a,
  ADD COLUMN signin_6a TINYINT(1) DEFAULT 0 COMMENT 'Kesulitan jalan nafas: Tidak' AFTER signin_5b,
  ADD COLUMN signin_6b TINYINT(1) DEFAULT 0 COMMENT 'Kesulitan jalan nafas: Peralatan tersedia' AFTER signin_6a,
  ADD COLUMN signin_7a TINYINT(1) DEFAULT 0 COMMENT 'Resiko kehilangan darah: Tidak' AFTER signin_6b,
  ADD COLUMN signin_7b TINYINT(1) DEFAULT 0 COMMENT 'Resiko kehilangan darah: IV terapi' AFTER signin_7a,
  ADD COLUMN dokter_anestesi_signin VARCHAR(100) NULL AFTER signin_7b,
  ADD COLUMN perawat_anestesi_signin VARCHAR(100) NULL AFTER dokter_anestesi_signin,
  ADD COLUMN perawat_sirkuler_signin VARCHAR(100) NULL AFTER perawat_anestesi_signin;

-- Tambah kolom untuk Time Out (5 items + 1 tim + 3 catatan)
ALTER TABLE tbl_anestesi_keselamatan_operasi
  ADD COLUMN timeout_time TIME NULL AFTER perawat_sirkuler_signin,
  ADD COLUMN timeout_1 TINYINT(1) DEFAULT 0 COMMENT 'Konfirmasi tim' AFTER timeout_time,
  ADD COLUMN timeout_2a TINYINT(1) DEFAULT 0 COMMENT 'Konfirmasi: Nama pasien' AFTER timeout_1,
  ADD COLUMN timeout_2b TINYINT(1) DEFAULT 0 COMMENT 'Konfirmasi: Prosedur' AFTER timeout_2a,
  ADD COLUMN timeout_2c TINYINT(1) DEFAULT 0 COMMENT 'Konfirmasi: Lokasi insisi' AFTER timeout_2b,
  ADD COLUMN timeout_3 TINYINT(1) DEFAULT 0 COMMENT 'Antibiotik profilaksis' AFTER timeout_2c,
  ADD COLUMN catatan_dokter_bedah TEXT NULL AFTER timeout_3,
  ADD COLUMN catatan_dokter_anestesi TEXT NULL AFTER catatan_dokter_bedah,
  ADD COLUMN catatan_perawat TEXT NULL AFTER catatan_dokter_anestesi,
  ADD COLUMN timeout_5a TINYINT(1) DEFAULT 0 COMMENT 'Foto Rontgen: Ya' AFTER catatan_perawat,
  ADD COLUMN timeout_5b TINYINT(1) DEFAULT 0 COMMENT 'Foto Rontgen: Tidak' AFTER timeout_5a,
  ADD COLUMN perawat_sirkuler_timeout VARCHAR(100) NULL AFTER timeout_5b;

-- Tambah kolom untuk Sign Out (2 items + 3 tim + tanggal)
ALTER TABLE tbl_anestesi_keselamatan_operasi
  ADD COLUMN signout_time TIME NULL AFTER perawat_sirkuler_timeout,
  ADD COLUMN signout_1a TINYINT(1) DEFAULT 0 COMMENT 'Nama prosedur' AFTER signout_time,
  ADD COLUMN signout_1b TINYINT(1) DEFAULT 0 COMMENT 'Instrumen lengkap' AFTER signout_1a,
  ADD COLUMN signout_1c TINYINT(1) DEFAULT 0 COMMENT 'Spesimen diberi label' AFTER signout_1b,
  ADD COLUMN signout_1d TINYINT(1) DEFAULT 0 COMMENT 'Tidak ada masalah alat' AFTER signout_1c,
  ADD COLUMN signout_2 TINYINT(1) DEFAULT 0 COMMENT 'Tim membahas penyembuhan' AFTER signout_1d,
  ADD COLUMN tanggal_keluar VARCHAR(10) NULL COMMENT 'DD' AFTER signout_2,
  ADD COLUMN tahun_keluar VARCHAR(4) NULL COMMENT 'YYYY' AFTER tanggal_keluar,
  ADD COLUMN perawat_sirkuler_signout VARCHAR(100) NULL AFTER tahun_keluar,
  ADD COLUMN dokter_anestesi_signout VARCHAR(100) NULL AFTER perawat_sirkuler_signout,
  ADD COLUMN operator_signout VARCHAR(100) NULL AFTER dokter_anestesi_signout;

-- Total kolom baru: 10 (existing) + 52 (baru) = 62 kolom


-- =====================================================
-- 2. ALTER TABLE: tbl_anestesi_kamar_pemulihan
-- =====================================================
-- Drop kolom "instruksi" karena akan dipecah
ALTER TABLE tbl_anestesi_kamar_pemulihan
  DROP COLUMN instruksi;

-- Tambah kolom untuk Data Masuk dan Kondisi Pasien
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN jalan_nafas_bersih TINYINT(1) DEFAULT 0 AFTER kesadaran,
  ADD COLUMN pernapasan_spontan TINYINT(1) DEFAULT 0 AFTER jalan_nafas_bersih,
  ADD COLUMN pernapasan_dibantu TINYINT(1) DEFAULT 0 AFTER pernapasan_spontan,
  ADD COLUMN spontan_adekuat TINYINT(1) DEFAULT 0 AFTER pernapasan_dibantu,
  ADD COLUMN spontan_penyumbatan TINYINT(1) DEFAULT 0 AFTER spontan_adekuat,
  ADD COLUMN spontan_alat TINYINT(1) DEFAULT 0 AFTER spontan_penyumbatan,
  ADD COLUMN kesadaran_sadar TINYINT(1) DEFAULT 0 AFTER spontan_alat,
  ADD COLUMN kesadaran_belum_sadar TINYINT(1) DEFAULT 0 AFTER kesadaran_sadar,
  ADD COLUMN kesadaran_tidur_dalam TINYINT(1) DEFAULT 0 AFTER kesadaran_belum_sadar;

-- Tambah kolom untuk Vital Signs (3 waktu x 5 parameter = 15 kolom)
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN nadi_1 INT NULL AFTER kesadaran_tidur_dalam,
  ADD COLUMN nadi_2 INT NULL AFTER nadi_1,
  ADD COLUMN nadi_3 INT NULL AFTER nadi_2,
  ADD COLUMN sistol_1 INT NULL AFTER nadi_3,
  ADD COLUMN sistol_2 INT NULL AFTER sistol_1,
  ADD COLUMN sistol_3 INT NULL AFTER sistol_2,
  ADD COLUMN diastol_1 INT NULL AFTER sistol_3,
  ADD COLUMN diastol_2 INT NULL AFTER diastol_1,
  ADD COLUMN diastol_3 INT NULL AFTER diastol_2,
  ADD COLUMN respirasi_1 INT NULL AFTER diastol_3,
  ADD COLUMN respirasi_2 INT NULL AFTER respirasi_1,
  ADD COLUMN respirasi_3 INT NULL AFTER respirasi_2,
  ADD COLUMN nyeri_1 INT NULL AFTER respirasi_3,
  ADD COLUMN nyeri_2 INT NULL AFTER nyeri_1,
  ADD COLUMN nyeri_3 INT NULL AFTER nyeri_2;

-- Tambah kolom untuk Instruksi Pasca Sedasi
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN pemantauan_setiap VARCHAR(50) NULL AFTER nyeri_3,
  ADD COLUMN pemantauan_selama VARCHAR(50) NULL AFTER pemantauan_setiap,
  ADD COLUMN analgesia VARCHAR(150) NULL AFTER pemantauan_selama,
  ADD COLUMN anti_muntah VARCHAR(150) NULL AFTER analgesia,
  ADD COLUMN antibiotik VARCHAR(150) NULL AFTER anti_muntah,
  ADD COLUMN posisi_pasien VARCHAR(150) NULL AFTER antibiotik,
  ADD COLUMN obat_lain TEXT NULL AFTER posisi_pasien,
  ADD COLUMN diet_nutrisi VARCHAR(150) NULL AFTER obat_lain,
  ADD COLUMN lain_lain TEXT NULL AFTER diet_nutrisi;

-- Tambah kolom untuk Keluar Kamar Pulih
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN jam_keluar TIME NULL AFTER lain_lain,
  ADD COLUMN td_keluar VARCHAR(20) NULL AFTER jam_keluar,
  ADD COLUMN n_keluar VARCHAR(20) NULL AFTER td_keluar,
  ADD COLUMN r_keluar VARCHAR(20) NULL AFTER n_keluar,
  ADD COLUMN s_keluar VARCHAR(20) NULL AFTER r_keluar,
  ADD COLUMN spo2_keluar VARCHAR(20) NULL AFTER s_keluar,
  ADD COLUMN skrining_nyeri ENUM('ya','tidak') NULL AFTER spo2_keluar,
  ADD COLUMN tujuan_keluar ENUM('ruang_rawat','icu','pulang') NULL AFTER skrining_nyeri,
  ADD COLUMN catatan_khusus TEXT NULL AFTER tujuan_keluar;

-- Tambah kolom untuk Penilaian/Scoring
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN aldrete_score INT NULL AFTER catatan_khusus,
  ADD COLUMN bromage_score INT NULL AFTER aldrete_score,
  ADD COLUMN steward_score INT NULL AFTER bromage_score;

-- Tambah kolom untuk Serah Terima Pasien
ALTER TABLE tbl_anestesi_kamar_pemulihan
  ADD COLUMN nama_penanggungjawab VARCHAR(150) NULL AFTER steward_score,
  ADD COLUMN perawat_menyerahkan VARCHAR(100) NULL AFTER nama_penanggungjawab,
  ADD COLUMN perawat_menerima VARCHAR(100) NULL AFTER perawat_menyerahkan,
  ADD COLUMN dokter_anestesi VARCHAR(100) NULL AFTER perawat_menerima;

-- Total kolom baru: 11 (existing) + 51 (baru) = 62 kolom


-- =====================================================
-- VERIFIKASI STRUKTUR
-- =====================================================
-- Jalankan query ini untuk verifikasi:
DESCRIBE tbl_anestesi_keselamatan_operasi;
DESCRIBE tbl_anestesi_kamar_pemulihan;

-- Cek total kolom:
SELECT 
  'tbl_anestesi_keselamatan_operasi' as tabel,
  COUNT(*) as total_kolom
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_keselamatan_operasi'
UNION ALL
SELECT 
  'tbl_anestesi_kamar_pemulihan' as tabel,
  COUNT(*) as total_kolom
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi' 
  AND TABLE_NAME = 'tbl_anestesi_kamar_pemulihan';

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. Backup database sebelum menjalankan ALTER TABLE!
-- 2. ALTER TABLE akan memakan waktu (tergantung jumlah data)
-- 3. Setelah ALTER, test insert data
-- 4. Kolom lama (signin, timeout, signout) akan dihapus
-- 5. Data lama akan hilang jika ada (tabel masih kosong jadi aman)
-- =====================================================
