-- ============================================================================
-- TEST INSERT: Data Lengkap dengan Semua Keterangan
-- ============================================================================
-- Script ini untuk testing apakah:
-- 1. Semua field keterangan bisa tersimpan
-- 2. Form bisa load data dengan benar
-- 3. Radio button tersimpan dengan benar
-- ============================================================================

USE dbanestesi;

-- Hapus data test sebelumnya (jika ada)
DELETE FROM tbl_anestesi_persiapan_operasi 
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';

-- INSERT data test dengan SEMUA field keterangan terisi
INSERT INTO tbl_anestesi_persiapan_operasi (
    -- Informasi Dasar
    no_rawat, kode_paket, tanggal_operasi, macam_operasi, dpjp,
    tinggi_badan, berat_badan, gol_darah, riwayat_alergi,
    
    -- Item 1-11: Administrasi (UBS + R.RAWAT)
    program_ke_ubs, rawat_program_ke_ubs,
    persetujuan_operasi, rawat_persetujuan_operasi,
    rekam_medis, rawat_rekam_medis,
    laporan_operasi, rawat_laporan_operasi,
    laporan_anestesi, rawat_laporan_anestesi,
    hasil_lab, rawat_hasil_lab,
    hasil_radiologi, rawat_hasil_radiologi,
    hasil_ct_scan, rawat_hasil_ct_scan,
    hasil_usg, rawat_hasil_usg,
    hasil_ekg, rawat_hasil_ekg,
    hasil_lain, rawat_hasil_lain,
    
    -- Item 12: Puasa + Waktu Puasa
    puasa, rawat_puasa, waktu_puasa,
    
    -- Item 13: Lavement
    lavement, rawat_lavement,
    
    -- Item 14: DC + No + Macam
    pasang_dc, rawat_pasang_dc, dc_no, dc_macam,
    
    -- Item 15-18: Fisik
    cukur_daerah_operasi, rawat_cukur_daerah_operasi,
    rambut_makeup_dibersihkan, rawat_rambut_makeup_dibersihkan,
    cat_kuku_dibersihkan, rawat_cat_kuku_dibersihkan,
    perhiasan_dilepas, rawat_perhiasan_dilepas,
    
    -- Item 19: Persiapan Darah
    transfusi_darah, rawat_transfusi_darah,
    
    -- Item 20: WB + Kantong
    transfusi_whole_blood, rawat_transfusi_whole_blood, kantong_wb,
    
    -- Item 21: PRC + Kantong
    transfusi_prc, rawat_transfusi_prc, kantong_prc,
    
    -- Item 22: FFP + Kantong
    transfusi_ffp, rawat_transfusi_ffp, kantong_ffp,
    
    -- Item 23: Premedikasi
    premedikasi, rawat_premedikasi,
    
    -- Item 24: Antibiotik + Nama + Jam
    antibiotik, rawat_antibiotik, antibiotik_preops, jam_antibiotik,
    
    -- Item 25-27: DM, Hipertensi, Asma
    dm_insulin_preop, rawat_dm_insulin_preop,
    hipertensi_obat, rawat_hipertensi_obat,
    asma_obat, rawat_asma_obat,
    
    -- Item 28: Obat Lain + Keterangan
    obat_lain_radio, rawat_obat_lain_radio, obat_lain,
    
    -- Item 29: Obat Tidur
    obat_tidur, rawat_obat_tidur,
    
    -- Item 30: Pasang Infus + IV Catch No
    pasang_infus, rawat_pasang_infus, iv_catch_no,
    
    -- Item 31: Tekanan Darah + Nilai
    tekanan_darah_radio, rawat_tekanan_darah_radio, tekanan_darah,
    
    -- Item 32: Nadi + Nilai
    nadi_radio, rawat_nadi_radio, nadi,
    
    -- Item 33: Suhu + Nilai
    suhu_radio, rawat_suhu_radio, suhu,
    
    -- Item 34: Pernapasan + Nilai
    pernafasan_radio, rawat_pernafasan_radio, pernafasan,
    
    -- Item 35: Obat UBS
    obat_ubs, rawat_obat_ubs,
    
    -- Item 36: Skin Test + Hasil
    skin_test_radio, rawat_skin_test_radio, hasil_skin_test,
    
    -- Item 37-41: Visit Dokter
    visit_dokter_bedah, rawat_visit_dokter_bedah,
    visit_dokter_anestesi, rawat_visit_dokter_anestesi,
    visit_dokter_konsul_1, rawat_visit_dokter_konsul_1,
    visit_dokter_konsul_2, rawat_visit_dokter_konsul_2,
    visit_dokter_konsul_3, rawat_visit_dokter_konsul_3
)
VALUES (
    -- Informasi Dasar
    'TEST001', 'PKT001', '2025-01-20', 'Operasi Appendectomy', 'Dr. Budi Santoso, Sp.B',
    170.00, 65.00, 'A+', 'Alergi Penisilin',
    
    -- Item 1-11: Administrasi (semua Ya = 1)
    1, 1,  -- Program ke UBS
    1, 1,  -- Persetujuan Operasi
    1, 1,  -- Rekam Medis
    1, 1,  -- Laporan Operasi
    1, 1,  -- Laporan Anestesi
    1, 1,  -- Hasil Lab
    1, 1,  -- Hasil Radiologi
    1, 1,  -- Hasil CT Scan
    1, 1,  -- Hasil USG
    1, 1,  -- Hasil EKG
    1, 1,  -- Lain-lain
    
    -- Item 12: Puasa + Waktu
    1, 1, 'Sejak kemarin pukul 22:00',
    
    -- Item 13: Lavement
    1, 1,
    
    -- Item 14: DC + No + Macam
    1, 1, 123, 'Foley Catheter No. 16',
    
    -- Item 15-18: Fisik
    1, 1,  -- Cukur daerah operasi
    1, 1,  -- Rambut palsu dilepas
    1, 1,  -- Cat kuku dibersihkan
    1, 1,  -- Perhiasan dilepas
    
    -- Item 19: Persiapan Darah
    1, 1,
    
    -- Item 20: WB + Kantong
    1, 1, 2,
    
    -- Item 21: PRC + Kantong
    1, 1, 3,
    
    -- Item 22: FFP + Kantong
    1, 1, 1,
    
    -- Item 23: Premedikasi
    1, 1,
    
    -- Item 24: Antibiotik + Nama + Jam
    1, 1, 'Ceftriaxone 1 gram IV', '08:00:00',
    
    -- Item 25-27: DM, Hipertensi, Asma
    1, 1,  -- DM Insulin
    1, 1,  -- Hipertensi
    1, 1,  -- Asma
    
    -- Item 28: Obat Lain + Keterangan
    1, 1, 'Paracetamol 500mg 3x1, Omeprazole 20mg 1x1',
    
    -- Item 29: Obat Tidur
    1, 1,
    
    -- Item 30: Pasang Infus + IV Catch No
    1, 1, 'IV-2025-001',
    
    -- Item 31: Tekanan Darah + Nilai
    1, 1, '120/80 mmHg',
    
    -- Item 32: Nadi + Nilai
    1, 1, '80 x/menit',
    
    -- Item 33: Suhu + Nilai
    1, 1, 36.5,
    
    -- Item 34: Pernapasan + Nilai
    1, 1, '20 x/menit',
    
    -- Item 35: Obat UBS
    1, 1,
    
    -- Item 36: Skin Test + Hasil
    1, 1, 'Negatif',
    
    -- Item 37-41: Visit Dokter
    1, 1,  -- Dokter Bedah
    1, 1,  -- Dokter Anestesi
    1, 1,  -- Dokter Konsul 1
    1, 1,  -- Dokter Konsul 2
    1, 1   -- Dokter Konsul 3
);

-- ============================================================================
-- VERIFIKASI: Cek apakah data tersimpan dengan benar
-- ============================================================================

SELECT '✅ Data test berhasil diinsert!' AS status;

-- Cek semua field keterangan
SELECT 
    '=== INFORMASI DASAR ===' AS section,
    no_rawat, kode_paket, tanggal_operasi, macam_operasi, dpjp,
    tinggi_badan, berat_badan, gol_darah, riwayat_alergi
FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001'

UNION ALL

SELECT 
    '=== KETERANGAN LENGKAP ===' AS section,
    waktu_puasa AS 'Item 12: Waktu Puasa',
    CONCAT(dc_no, ' - ', dc_macam) AS 'Item 14: DC',
    CONCAT('WB:', kantong_wb, ' PRC:', kantong_prc, ' FFP:', kantong_ffp) AS 'Item 20-22: Kantong',
    CONCAT(antibiotik_preops, ' @ ', jam_antibiotik) AS 'Item 24: Antibiotik',
    obat_lain AS 'Item 28: Obat Lain',
    iv_catch_no AS 'Item 30: IV Catch',
    tekanan_darah AS 'Item 31: TD',
    nadi AS 'Item 32: Nadi',
    CONCAT(suhu, '°C') AS 'Item 33: Suhu'
FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';

-- Cek radio button (harus semua = 1)
SELECT 
    '=== RADIO BUTTON STATUS ===' AS section,
    CONCAT('Item 19 (Transfusi Darah): UBS=', transfusi_darah, ' R.RAWAT=', rawat_transfusi_darah) AS status1,
    CONCAT('Item 24 (Antibiotik): UBS=', antibiotik, ' R.RAWAT=', rawat_antibiotik) AS status2,
    CONCAT('Item 28 (Obat Lain): UBS=', obat_lain_radio, ' R.RAWAT=', rawat_obat_lain_radio) AS status3,
    CONCAT('Item 31 (TD): UBS=', tekanan_darah_radio, ' R.RAWAT=', rawat_tekanan_darah_radio) AS status4,
    CONCAT('Item 32 (Nadi): UBS=', nadi_radio, ' R.RAWAT=', rawat_nadi_radio) AS status5,
    CONCAT('Item 33 (Suhu): UBS=', suhu_radio, ' R.RAWAT=', rawat_suhu_radio) AS status6,
    CONCAT('Item 34 (Pernapasan): UBS=', pernafasan_radio, ' R.RAWAT=', rawat_pernafasan_radio) AS status7,
    CONCAT('Item 36 (Skin Test): UBS=', skin_test_radio, ' R.RAWAT=', rawat_skin_test_radio) AS status8
FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';

-- ============================================================================
-- TESTING FORM
-- ============================================================================
-- Setelah jalankan script ini:
-- 1. Buka form: http://localhost/index.php?page=persiapan-operasi&no_rawat=TEST001&kode_paket=PKT001
-- 2. Cek apakah SEMUA data muncul dengan benar:
--    ✅ Waktu Puasa: "Sejak kemarin pukul 22:00"
--    ✅ DC No: 123, Macam: "Foley Catheter No. 16"
--    ✅ Kantong WB: 2, PRC: 3, FFP: 1
--    ✅ Antibiotik: "Ceftriaxone 1 gram IV" @ 08:00
--    ✅ Obat Lain: "Paracetamol 500mg 3x1, Omeprazole 20mg 1x1"
--    ✅ IV Catch No: "IV-2025-001"
--    ✅ Tekanan Darah: "120/80 mmHg"
--    ✅ Nadi: "80 x/menit"
--    ✅ Suhu: "36.5"
--    ✅ Pernapasan: "20 x/menit"
--    ✅ Skin Test: "Negatif"
-- 3. Cek apakah SEMUA radio button tercentang "Ya"
-- ============================================================================

-- Cleanup (hapus data test jika sudah selesai testing)
-- DELETE FROM tbl_anestesi_persiapan_operasi WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
