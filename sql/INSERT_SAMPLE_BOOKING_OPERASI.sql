-- ============================================
-- INSERT SAMPLE DATA BOOKING_OPERASI
-- ============================================
-- Sample data untuk testing form
-- ============================================

USE dbanestesi;

-- ============================================
-- SAMPLE DATA PASIEN (JIKA BELUM ADA)
-- ============================================

INSERT INTO `pasien` (`kd_pasien`, `kode_rekam_medis`, `nama`, `alamat`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `no_hp`, `gol_darah`) VALUES
('PSN001', 'RM001', 'Budi Santoso', 'Jl. Merdeka No. 10, Jakarta', 'L', 'Jakarta', '1985-05-15', '081234567890', 'A'),
('PSN002', 'RM002', 'Siti Nurhaliza', 'Jl. Sudirman No. 25, Bandung', 'P', 'Bandung', '1990-08-20', '081234567891', 'B'),
('PSN003', 'RM003', 'Ahmad Hidayat', 'Jl. Gatot Subroto No. 5, Surabaya', 'L', 'Surabaya', '1978-12-10', '081234567892', 'O'),
('PSN004', 'RM004', 'Dewi Lestari', 'Jl. Diponegoro No. 15, Semarang', 'P', 'Semarang', '1995-03-25', '081234567893', 'AB'),
('PSN005', 'RM005', 'Rudi Hartono', 'Jl. Ahmad Yani No. 30, Yogyakarta', 'L', 'Yogyakarta', '1982-07-18', '081234567894', 'A'),
('PSN006', 'RM006', 'Maya Sari', 'Jl. Pahlawan No. 8, Malang', 'P', 'Malang', '1988-11-05', '081234567895', 'B'),
('PSN007', 'RM007', 'Agus Setiawan', 'Jl. Veteran No. 12, Solo', 'L', 'Solo', '1975-09-30', '081234567896', 'O'),
('PSN008', 'RM008', 'Lina Marlina', 'Jl. Pemuda No. 20, Medan', 'P', 'Medan', '1992-06-12', '081234567897', 'A'),
('PSN009', 'RM009', 'Hendra Gunawan', 'Jl. Kartini No. 7, Palembang', 'L', 'Palembang', '1980-04-22', '081234567898', 'AB'),
('PSN010', 'RM010', 'Rina Wijaya', 'Jl. Gajah Mada No. 18, Denpasar', 'P', 'Denpasar', '1987-01-08', '081234567899', 'B')
ON DUPLICATE KEY UPDATE kd_pasien = kd_pasien;

-- ============================================
-- SAMPLE DATA BOOKING_OPERASI
-- ============================================

-- Hapus data lama (optional)
-- DELETE FROM booking_operasi WHERE no_rawat LIKE 'RW%';

-- Insert sample data booking
INSERT INTO `booking_operasi` (`no_rawat`, `kode_paket`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`, `kd_dokter`, `dokter_rawat`, `perawat`, `kd_ruang_ok`, `ruang_rawat`, `kd_pasien`) VALUES

-- Booking 1: Operasi Appendektomi (Menunggu)
('RW2025001', 'PKT-APP', '2025-11-06', '08:00:00', '10:00:00', 'Menunggu', 'DR003', 'DR008', 'PR010', '1', 'RG007', 'PSN001'),

-- Booking 2: Operasi Hernia (Menunggu)
('RW2025002', 'PKT-HRN', '2025-11-06', '10:30:00', '12:30:00', 'Menunggu', 'DR004', 'DR008', 'PR011', '2', 'RG008', 'PSN002'),

-- Booking 3: Operasi Caesar (Proses Operasi)
('RW2025003', 'PKT-CSR', '2025-11-05', '09:00:00', '11:00:00', 'Proses Operasi', 'DR005', 'DR008', 'PR010', '3', 'RG007', 'PSN003'),

-- Booking 4: Operasi Katarak (Menunggu)
('RW2025004', 'PKT-KTK', '2025-11-07', '08:30:00', '10:00:00', 'Menunggu', 'DR010', 'DR008', 'PR012', '1', 'RG009', 'PSN004'),

-- Booking 5: Operasi Fraktur (Menunggu)
('RW2025005', 'PKT-FRK', '2025-11-07', '11:00:00', '13:00:00', 'Menunggu', 'DR009', 'DR008', 'PR010', '2', 'RG007', 'PSN005'),

-- Booking 6: Operasi Jantung (Menunggu)
('RW2025006', 'PKT-JNT', '2025-11-08', '07:00:00', '12:00:00', 'Menunggu', 'DR007', 'DR008', 'PR011', '4', 'RG007', 'PSN006'),

-- Booking 7: Operasi Usus (Selesai)
('RW2025007', 'PKT-USU', '2025-11-04', '08:00:00', '10:30:00', 'Selesai', 'DR003', 'DR008', 'PR010', '1', 'RG008', 'PSN007'),

-- Booking 8: Operasi Tumor (Menunggu)
('RW2025008', 'PKT-TMR', '2025-11-09', '09:00:00', '14:00:00', 'Menunggu', 'DR003', 'DR008', 'PR012', '3', 'RG007', 'PSN008'),

-- Booking 9: Operasi Batu Ginjal (Menunggu)
('RW2025009', 'PKT-BTG', '2025-11-10', '08:00:00', '10:00:00', 'Menunggu', 'DR004', 'DR008', 'PR011', '1', 'RG009', 'PSN009'),

-- Booking 10: Operasi Mata (Menunggu)
('RW2025010', 'PKT-MTA', '2025-11-11', '10:00:00', '11:30:00', 'Menunggu', 'DR010', 'DR008', 'PR010', '2', 'RG008', 'PSN010'),

-- Booking 11: Operasi Anak (Menunggu)
('RW2025011', 'PKT-ANK', '2025-11-12', '08:30:00', '10:30:00', 'Menunggu', 'DR006', 'DR008', 'PR012', '1', 'RG007', 'PSN001'),

-- Booking 12: Operasi Bedah Umum (Proses Operasi)
('RW2025012', 'PKT-BDU', '2025-11-05', '13:00:00', '15:00:00', 'Proses Operasi', 'DR003', 'DR008', 'PR011', '2', 'RG008', 'PSN002'),

-- Booking 13: Operasi Ortopedi (Menunggu)
('RW2025013', 'PKT-ORT', '2025-11-13', '09:00:00', '11:00:00', 'Menunggu', 'DR009', 'DR008', 'PR010', '3', 'RG009', 'PSN003'),

-- Booking 14: Operasi Ginekologi (Menunggu)
('RW2025014', 'PKT-GIN', '2025-11-14', '08:00:00', '10:00:00', 'Menunggu', 'DR005', 'DR008', 'PR011', '1', 'RG007', 'PSN004'),

-- Booking 15: Operasi Darurat (Menunggu)
('RW2025015', 'PKT-DRT', '2025-11-05', '16:00:00', '18:00:00', 'Menunggu', 'DR003', 'DR008', 'PR012', '4', 'RG007', 'PSN005')

ON DUPLICATE KEY UPDATE 
    status = VALUES(status),
    jam_selesai = VALUES(jam_selesai),
    dokter_rawat = VALUES(dokter_rawat),
    perawat = VALUES(perawat),
    ruang_rawat = VALUES(ruang_rawat);

-- ============================================
-- VERIFY DATA
-- ============================================

-- Cek jumlah data booking
SELECT 
    status,
    COUNT(*) AS jumlah,
    GROUP_CONCAT(no_rawat ORDER BY tanggal SEPARATOR ', ') AS daftar_no_rawat
FROM booking_operasi
GROUP BY status
ORDER BY 
    FIELD(status, 'Menunggu', 'Proses Operasi', 'Selesai');

-- Cek data booking dengan detail
SELECT 
    b.no_rawat,
    b.kode_paket,
    p.nama AS nama_pasien,
    p.kode_rekam_medis AS no_rm,
    b.tanggal,
    b.jam_mulai,
    b.jam_selesai,
    b.status,
    b.kd_ruang_ok,
    b.ruang_rawat,
    b.dokter_rawat,
    b.perawat
FROM booking_operasi b
LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
WHERE b.no_rawat LIKE 'RW%'
ORDER BY b.tanggal DESC, b.jam_mulai ASC;

-- Cek booking per tanggal
SELECT 
    tanggal,
    COUNT(*) AS jumlah_operasi,
    GROUP_CONCAT(CONCAT(no_rawat, ' (', jam_mulai, ')') ORDER BY jam_mulai SEPARATOR ', ') AS jadwal
FROM booking_operasi
WHERE tanggal >= CURDATE()
GROUP BY tanggal
ORDER BY tanggal;

-- ============================================
-- QUERY UNTUK FORM DROPDOWN
-- ============================================

-- Daftar pasien untuk dropdown
SELECT 
    kd_pasien,
    kode_rekam_medis,
    nama,
    CONCAT(nama, ' - ', kode_rekam_medis, ' (', 
           CASE jenis_kelamin 
               WHEN 'L' THEN 'Laki-laki' 
               WHEN 'P' THEN 'Perempuan' 
           END, ')') AS display_text
FROM pasien
ORDER BY nama;

-- Daftar booking untuk dropdown
SELECT 
    CONCAT(no_rawat, '|', kode_paket, '|', tanggal, '|', jam_mulai) AS booking_id,
    CONCAT(no_rawat, ' - ', p.nama, ' (', DATE_FORMAT(tanggal, '%d/%m/%Y'), ' ', 
           TIME_FORMAT(jam_mulai, '%H:%i'), ')') AS display_text,
    status
FROM booking_operasi b
LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
WHERE status IN ('Menunggu', 'Proses Operasi')
ORDER BY tanggal, jam_mulai;

-- ============================================
-- STATISTIK
-- ============================================

-- Statistik booking per status
SELECT 
    'Total Booking' AS kategori,
    COUNT(*) AS jumlah
FROM booking_operasi
UNION ALL
SELECT 
    CONCAT('Status: ', status) AS kategori,
    COUNT(*) AS jumlah
FROM booking_operasi
GROUP BY status
UNION ALL
SELECT 
    'Booking Hari Ini' AS kategori,
    COUNT(*) AS jumlah
FROM booking_operasi
WHERE tanggal = CURDATE()
UNION ALL
SELECT 
    'Booking Minggu Ini' AS kategori,
    COUNT(*) AS jumlah
FROM booking_operasi
WHERE YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1);

-- ============================================
-- SELESAI
-- ============================================
-- Sample data berhasil ditambahkan:
-- - 10 Pasien baru
-- - 15 Booking operasi dengan berbagai status
-- - Data lengkap dengan ruang_rawat, dokter_rawat, perawat
-- 
-- Status Booking:
-- - Menunggu: 12 booking
-- - Proses Operasi: 2 booking
-- - Selesai: 1 booking
-- ============================================
