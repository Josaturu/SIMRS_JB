-- =====================================================
-- Migration: Add Mallampati and Gerakan Leher Fields
-- Date: 2025-11-03
-- Description: Menambahkan field mallampati, gerakan_leher, dan gerakan_leher_keterangan
--              untuk memisahkan input jalan nafas menjadi 3 bagian terpisah
-- =====================================================

USE dbanestesi;

-- Add mallampati field
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN IF NOT EXISTS `mallampati` varchar(50) DEFAULT NULL COMMENT 'Mallampati I/II/III/IV' 
AFTER `jalan_nafas_keterangan`;

-- Add gerakan_leher field
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN IF NOT EXISTS `gerakan_leher` varchar(50) DEFAULT NULL COMMENT 'Maksimal/Abnormal' 
AFTER `mallampati`;

-- Add gerakan_leher_keterangan field
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN IF NOT EXISTS `gerakan_leher_keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan jika gerakan leher abnormal' 
AFTER `gerakan_leher`;

-- Verify columns added
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi'
  AND COLUMN_NAME IN ('mallampati', 'gerakan_leher', 'gerakan_leher_keterangan')
ORDER BY ORDINAL_POSITION;

-- Success message
SELECT 'Migration completed! 3 new fields added: mallampati, gerakan_leher, gerakan_leher_keterangan' AS status;
