-- ============================================================================
-- CEK FIELD KETERANGAN YANG HILANG
-- ============================================================================
-- Query ini akan menampilkan field keterangan yang SEHARUSNYA ada
-- Jalankan dan bandingkan dengan hasil SHOW COLUMNS
-- ============================================================================

-- Tampilkan semua kolom yang ada di tabel
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND (
      COLUMN_NAME LIKE '%ket%' 
      OR COLUMN_NAME IN (
          'waktu_puasa',
          'kantong_wb', 'kantong_prc', 'kantong_ffp',
          'antibiotik_preops', 'jam_antibiotik',
          'obat_lain',
          'iv_catch_no',
          'tekanan_darah', 'nadi', 'suhu', 'pernafasan',
          'hasil_skin_test'
      )
  )
ORDER BY ORDINAL_POSITION;

-- ============================================================================
-- FIELD KETERANGAN YANG SEHARUSNYA ADA (CHECKLIST):
-- ============================================================================
-- Item 12: waktu_puasa (VARCHAR)
-- Item 14: dc_no (INT) + dc_macam (VARCHAR) - MUNGKIN HILANG!
-- Item 20: kantong_wb (INT)
-- Item 21: kantong_prc (INT)
-- Item 22: kantong_ffp (INT)
-- Item 24: antibiotik_preops (VARCHAR) + jam_antibiotik (TIME)
-- Item 28: obat_lain (TEXT)
-- Item 30: iv_catch_no (VARCHAR)
-- Item 31: tekanan_darah (VARCHAR)
-- Item 32: nadi (VARCHAR)
-- Item 33: suhu (DECIMAL)
-- Item 34: pernafasan (VARCHAR)
-- Item 36: hasil_skin_test (ENUM)
-- ============================================================================

-- Cek apakah field DC (Item 14) ada
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN '❌ FIELD DC HILANG! Perlu ditambahkan'
        ELSE '✅ Field DC sudah ada'
    END AS status_dc
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME IN ('dc_no', 'dc_macam');

-- Cek field keterangan lainnya
SELECT 
    'waktu_puasa' AS field_name,
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END AS status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'waktu_puasa'

UNION ALL

SELECT 
    'kantong_wb',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'kantong_wb'

UNION ALL

SELECT 
    'kantong_prc',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'kantong_prc'

UNION ALL

SELECT 
    'kantong_ffp',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'kantong_ffp'

UNION ALL

SELECT 
    'antibiotik_preops',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'antibiotik_preops'

UNION ALL

SELECT 
    'jam_antibiotik',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'jam_antibiotik'

UNION ALL

SELECT 
    'obat_lain',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'obat_lain'

UNION ALL

SELECT 
    'iv_catch_no',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'iv_catch_no'

UNION ALL

SELECT 
    'tekanan_darah',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'tekanan_darah'

UNION ALL

SELECT 
    'nadi',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'nadi'

UNION ALL

SELECT 
    'suhu',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'suhu'

UNION ALL

SELECT 
    'pernafasan',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'pernafasan'

UNION ALL

SELECT 
    'hasil_skin_test',
    CASE WHEN COUNT(*) > 0 THEN '✅' ELSE '❌ HILANG' END
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbanestesi'
  AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
  AND COLUMN_NAME = 'hasil_skin_test';
