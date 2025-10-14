-- ============================================
-- ADD COLUMN: jenis_diagnosa
-- Table: tbl_anestesi_konsultasi_anestesi
-- Purpose: Menyimpan jenis diagnosa (Cito/Elektif)
-- Date: 2025-10-14
-- ============================================

-- Step 1: Cek apakah kolom sudah ada
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
  AND COLUMN_NAME = 'jenis_diagnosa';
-- Jika hasil kosong, kolom belum ada (lanjut ke Step 2)
-- Jika ada hasil, kolom sudah ada (skip Step 2)

-- Step 2: Tambah kolom jenis_diagnosa
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
COMMENT 'Jenis diagnosa: Cito atau Elektif'
AFTER diagnosa_pra_operasi;

-- Step 3: Verifikasi kolom sudah ditambahkan
DESCRIBE tbl_anestesi_konsultasi_anestesi;
-- Cari baris: jenis_diagnosa | varchar(20) | YES | | Elektif

-- Step 4: Update data existing (opsional)
UPDATE tbl_anestesi_konsultasi_anestesi 
SET jenis_diagnosa = 'Elektif' 
WHERE jenis_diagnosa IS NULL;

-- Step 5: Test query
SELECT 
    no_rawat,
    diagnosa_pra_operasi,
    jenis_diagnosa,
    rencana_tindakan_operasi
FROM tbl_anestesi_konsultasi_anestesi
LIMIT 1;

-- ============================================
-- EXPECTED RESULT:
-- Column 'jenis_diagnosa' added successfully
-- Default value: 'Elektif'
-- ============================================
