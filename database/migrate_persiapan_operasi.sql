-- ============================================================================
-- SCRIPT MIGRASI: tbl_anestesi_persiapan_operasi
-- Deskripsi: Migrasi data dari table lama ke struktur baru
-- ============================================================================

-- LANGKAH 1: Backup table lama
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tbl_anestesi_persiapan_operasi_backup` LIKE `tbl_anestesi_persiapan_operasi`;
INSERT INTO `tbl_anestesi_persiapan_operasi_backup` SELECT * FROM `tbl_anestesi_persiapan_operasi`;

SELECT 'Backup table berhasil dibuat: tbl_anestesi_persiapan_operasi_backup' AS status;


-- LANGKAH 2: Hapus field yang tidak digunakan
-- ============================================================================
-- Field ini tidak digunakan di form, data pasien diambil dari tabel pasien/booking_operasi
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  DROP COLUMN IF EXISTS `no_rm`,
  DROP COLUMN IF EXISTS `nama`,
  DROP COLUMN IF EXISTS `jenis_kelamin`,
  DROP COLUMN IF EXISTS `umur`,
  DROP COLUMN IF EXISTS `tanggal_lahir`,
  DROP COLUMN IF EXISTS `visit_dokter_konsul_1`,
  DROP COLUMN IF EXISTS `nama_dokter_konsul_1`,
  DROP COLUMN IF EXISTS `visit_dokter_konsul_2`,
  DROP COLUMN IF EXISTS `nama_dokter_konsul_2`,
  DROP COLUMN IF EXISTS `visit_dokter_konsul_3`,
  DROP COLUMN IF EXISTS `nama_dokter_konsul_3`,
  DROP COLUMN IF EXISTS `perawat_ruangan`,
  DROP COLUMN IF EXISTS `tanda_tangan_perawat_ruangan`,
  DROP COLUMN IF EXISTS `perawat_ubs`,
  DROP COLUMN IF EXISTS `tanda_tangan_perawat_ubs`;

SELECT 'Field yang tidak digunakan berhasil dihapus' AS status;


-- LANGKAH 3: Modifikasi field yang ada
-- ============================================================================

-- Ubah no_rawat menjadi NOT NULL (wajib)
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `no_rawat` varchar(20) NOT NULL COMMENT 'Nomor rawat pasien (FK ke booking_operasi)';

-- Ubah kode_paket menjadi NOT NULL (wajib)
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `kode_paket` varchar(50) NOT NULL COMMENT 'Kode paket operasi (FK ke booking_operasi)';

-- Ubah macam_operasi menjadi lebih panjang
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `macam_operasi` varchar(200) DEFAULT NULL COMMENT 'Jenis/macam operasi yang akan dilakukan';

-- Ubah dpjp menjadi lebih panjang
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `dpjp` varchar(255) DEFAULT NULL COMMENT 'Dokter Penanggung Jawab Pelayanan';

-- Ubah waktu_puasa dari DATETIME ke VARCHAR untuk fleksibilitas
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `waktu_puasa` varchar(100) DEFAULT NULL COMMENT 'Item 12: Sejak kapan puasa';

-- Ubah antibiotik_preops menjadi lebih panjang
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  MODIFY COLUMN `antibiotik_preops` varchar(200) DEFAULT NULL COMMENT 'Item 24: Antibiotik pre-ops (nama obat)';

SELECT 'Modifikasi field berhasil' AS status;


-- LANGKAH 4: Tambahkan UNIQUE constraint
-- ============================================================================
-- Hapus constraint lama jika ada
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  DROP INDEX IF EXISTS `unique_checklist`;

-- Tambahkan UNIQUE constraint baru
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  ADD UNIQUE KEY `unique_checklist` (`no_rawat`, `kode_paket`);

SELECT 'UNIQUE constraint berhasil ditambahkan' AS status;


-- LANGKAH 5: Tambahkan index untuk performa
-- ============================================================================
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  ADD INDEX IF NOT EXISTS `idx_no_rawat` (`no_rawat`),
  ADD INDEX IF NOT EXISTS `idx_kode_paket` (`kode_paket`),
  ADD INDEX IF NOT EXISTS `idx_tanggal_operasi` (`tanggal_operasi`);

SELECT 'Index berhasil ditambahkan' AS status;


-- LANGKAH 6: Tambahkan komentar pada table
-- ============================================================================
ALTER TABLE `tbl_anestesi_persiapan_operasi` 
  COMMENT = 'Checklist Persiapan Operasi (RMO-1 a)';

SELECT 'Komentar table berhasil ditambahkan' AS status;


-- LANGKAH 7: Bersihkan data duplikat (jika ada)
-- ============================================================================
-- Hapus data duplikat, hanya simpan yang terbaru
DELETE t1 FROM `tbl_anestesi_persiapan_operasi` t1
INNER JOIN `tbl_anestesi_persiapan_operasi` t2 
WHERE 
  t1.id < t2.id 
  AND t1.no_rawat = t2.no_rawat 
  AND t1.kode_paket = t2.kode_paket;

SELECT 'Data duplikat berhasil dibersihkan' AS status;


-- LANGKAH 8: Verifikasi hasil migrasi
-- ============================================================================
SELECT 
  'Migrasi selesai!' AS status,
  COUNT(*) AS total_data,
  COUNT(DISTINCT no_rawat) AS total_pasien
FROM `tbl_anestesi_persiapan_operasi`;

-- Tampilkan struktur table baru
SHOW CREATE TABLE `tbl_anestesi_persiapan_operasi`;


-- ============================================================================
-- CATATAN:
-- ============================================================================
-- 1. Script ini aman dijalankan karena membuat backup terlebih dahulu
-- 2. Jika ada error, data bisa di-restore dari tbl_anestesi_persiapan_operasi_backup
-- 3. Setelah yakin migrasi berhasil, backup table bisa dihapus:
--    DROP TABLE IF EXISTS `tbl_anestesi_persiapan_operasi_backup`;
-- 4. Jika ingin rollback:
--    DROP TABLE `tbl_anestesi_persiapan_operasi`;
--    RENAME TABLE `tbl_anestesi_persiapan_operasi_backup` TO `tbl_anestesi_persiapan_operasi`;
-- ============================================================================
