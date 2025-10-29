-- =====================================================
-- ADD CHART IMAGE FIELD TO KAMAR PEMULIHAN
-- =====================================================
-- Tanggal: 30 Oktober 2025
-- Tujuan: Menyimpan grafik vital sign sebagai base64 image
-- =====================================================

USE `simrs_josaturu`;

-- Tambah kolom untuk menyimpan chart image
ALTER TABLE `tbl_anestesi_kamar_pemulihan` 
ADD COLUMN `chart_image` LONGTEXT DEFAULT NULL COMMENT 'Base64 encoded chart image' 
AFTER `dokter_anestesi`;

-- Tambah kolom untuk menyimpan raw vital sign data (JSON)
ALTER TABLE `tbl_anestesi_kamar_pemulihan` 
ADD COLUMN `vital_sign_data` LONGTEXT DEFAULT NULL COMMENT 'JSON array of vital sign records' 
AFTER `chart_image`;

-- =====================================================
-- NOTES:
-- =====================================================
-- chart_image: Menyimpan gambar grafik dalam format base64
--              untuk ditampilkan di PDF
--
-- vital_sign_data: Menyimpan data vital sign dalam format JSON
--                   untuk regenerate chart jika diperlukan
--
-- Format vital_sign_data:
-- [
--   {
--     "jam": "14:12:41",
--     "respirasi": 22,
--     "nadi": 27,
--     "sistol": 21,
--     "diastol": 34,
--     "nyeri": 5,
--     "spo2": 85
--   },
--   ...
-- ]
-- =====================================================

-- Verify changes
DESCRIBE `tbl_anestesi_kamar_pemulihan`;
