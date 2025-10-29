-- =====================================================
-- Script Migrasi: Fix Checkbox Delimiter
-- =====================================================
-- Problem: Nilai "Mual, Muntah" tidak terdeteksi karena 
--          menggunakan delimiter ', ' (koma + spasi)
--          yang konflik dengan koma dalam nilai checkbox
--
-- Solution: Ganti delimiter menjadi '|' (pipe)
-- 
-- Date: 28 Oktober 2025, 20:35 WIB
-- =====================================================

USE dbanestesi;

-- Backup data sebelum migrasi (PENTING!)
-- CREATE TABLE tbl_anestesi_informed_consent_anestesi_backup AS 
-- SELECT * FROM tbl_anestesi_informed_consent_anestesi;

-- =====================================================
-- MIGRASI DATA LAMA
-- =====================================================

-- Update jenis_anestesi: ganti ', ' dengan '|'
UPDATE tbl_anestesi_informed_consent_anestesi
SET jenis_anestesi = REPLACE(jenis_anestesi, ', ', '|')
WHERE jenis_anestesi IS NOT NULL 
  AND jenis_anestesi LIKE '%,%';

-- Update indikasi: ganti ', ' dengan '|'
UPDATE tbl_anestesi_informed_consent_anestesi
SET indikasi = REPLACE(indikasi, ', ', '|')
WHERE indikasi IS NOT NULL 
  AND indikasi LIKE '%,%';

-- Update tata_cara: ganti ', ' dengan '|'
UPDATE tbl_anestesi_informed_consent_anestesi
SET tata_cara = REPLACE(tata_cara, ', ', '|')
WHERE tata_cara IS NOT NULL 
  AND tata_cara LIKE '%,%';

-- Update risiko: ganti ', ' dengan '|'
-- INI YANG PALING PENTING untuk fix "Mual, Muntah"
UPDATE tbl_anestesi_informed_consent_anestesi
SET risiko = REPLACE(risiko, ', ', '|')
WHERE risiko IS NOT NULL 
  AND risiko LIKE '%,%';

-- =====================================================
-- VERIFIKASI HASIL MIGRASI
-- =====================================================

-- Cek data sebelum & sesudah migrasi
SELECT 
    id,
    no_rawat,
    LEFT(risiko, 50) as risiko_sample,
    CASE 
        WHEN risiko LIKE '%|%' THEN 'MIGRATED ✓'
        WHEN risiko LIKE '%,%' THEN 'OLD FORMAT ✗'
        ELSE 'EMPTY'
    END as migration_status
FROM tbl_anestesi_informed_consent_anestesi
ORDER BY id DESC
LIMIT 10;

-- Count data yang sudah vs belum migrasi
SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN risiko LIKE '%|%' THEN 1 ELSE 0 END) as migrated,
    SUM(CASE WHEN risiko LIKE '%,%' AND risiko NOT LIKE '%|%' THEN 1 ELSE 0 END) as old_format,
    SUM(CASE WHEN risiko IS NULL OR risiko = '' THEN 1 ELSE 0 END) as empty_risiko
FROM tbl_anestesi_informed_consent_anestesi;

-- =====================================================
-- TEST QUERY: Cek "Mual, Muntah" bisa terdeteksi
-- =====================================================

-- Test: Cari records yang punya risiko "Mual, Muntah"
SELECT 
    id,
    no_rawat,
    risiko,
    CASE 
        WHEN FIND_IN_SET('Mual, Muntah', REPLACE(risiko, '|', ',')) > 0 
        THEN 'DETECTED ✓'
        ELSE 'NOT DETECTED ✗'
    END as mual_muntah_status
FROM tbl_anestesi_informed_consent_anestesi
WHERE risiko LIKE '%Mual%'
   OR risiko LIKE '%Muntah%';

-- =====================================================
-- ROLLBACK (jika ada masalah)
-- =====================================================

-- Uncomment baris di bawah jika ingin rollback
-- UPDATE tbl_anestesi_informed_consent_anestesi t1
-- INNER JOIN tbl_anestesi_informed_consent_anestesi_backup t2 
--   ON t1.id = t2.id
-- SET t1.jenis_anestesi = t2.jenis_anestesi,
--     t1.indikasi = t2.indikasi,
--     t1.tata_cara = t2.tata_cara,
--     t1.risiko = t2.risiko;

-- =====================================================
-- CATATAN PENTING
-- =====================================================

/*
SETELAH MIGRASI:

1. Data LAMA (sebelum fix):
   risiko = "Nyeri Tenggorokan, Mual, Muntah, Nyeri Otot"
   Hasil explode: ["Nyeri Tenggorokan", "Mual", " Muntah", "Nyeri Otot"]
   Problem: "Mual, Muntah" terpisah ✗

2. Data BARU (setelah fix):
   risiko = "Nyeri Tenggorokan|Mual, Muntah|Nyeri Otot"
   Hasil explode: ["Nyeri Tenggorokan", "Mual, Muntah", "Nyeri Otot"]
   Result: "Mual, Muntah" tetap utuh ✓

3. Display di PDF:
   Data akan otomatis diconvert: "Nyeri Tenggorokan|Mual, Muntah"
   Menjadi: "Nyeri Tenggorokan, Mual, Muntah" (readable)

4. Checkbox di Form:
   Sekarang akan otomatis tercentang jika data = "Mual, Muntah" ✓

TESTING:
1. Buka form edit informed consent
2. Cek apakah checkbox "Mual, Muntah" tercentang
3. Simpan form
4. Generate PDF
5. Verify data tampil dengan benar
*/

-- =====================================================
-- SUMMARY
-- =====================================================

SELECT '✓ Migrasi delimiter selesai!' as status,
       'Cek checkbox Mual, Muntah di form edit' as next_action;
