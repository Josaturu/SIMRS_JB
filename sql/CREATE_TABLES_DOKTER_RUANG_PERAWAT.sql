-- ============================================
-- CREATE TABLES: DOKTER, RUANG, PERAWAT
-- ============================================
-- Script untuk membuat tabel master data
-- dokter, ruang, dan perawat
-- ============================================

USE dbanestesi;

-- ============================================
-- TABEL DOKTER
-- ============================================
CREATE TABLE IF NOT EXISTS tbl_dokter (
    id_dokter VARCHAR(20) PRIMARY KEY COMMENT 'ID Dokter (misal: DR001)',
    kode_dokter VARCHAR(20) UNIQUE NOT NULL COMMENT 'Kode Dokter',
    nama_dokter VARCHAR(100) NOT NULL COMMENT 'Nama Lengkap Dokter',
    spesialisasi VARCHAR(50) DEFAULT NULL COMMENT 'Spesialisasi (Anestesi, Bedah, dll)',
    no_sip VARCHAR(50) DEFAULT NULL COMMENT 'Nomor SIP',
    no_telp VARCHAR(20) DEFAULT NULL COMMENT 'Nomor Telepon',
    email VARCHAR(100) DEFAULT NULL COMMENT 'Email',
    alamat TEXT DEFAULT NULL COMMENT 'Alamat Lengkap',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif' COMMENT 'Status Dokter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu Dibuat',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu Diupdate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Data Dokter';

-- Index untuk performa
CREATE INDEX idx_nama_dokter ON tbl_dokter(nama_dokter);
CREATE INDEX idx_spesialisasi ON tbl_dokter(spesialisasi);
CREATE INDEX idx_status ON tbl_dokter(status);

-- ============================================
-- TABEL RUANG
-- ============================================
CREATE TABLE IF NOT EXISTS tbl_ruang (
    id_ruang VARCHAR(20) PRIMARY KEY COMMENT 'ID Ruang (misal: RG001)',
    kode_ruang VARCHAR(20) UNIQUE NOT NULL COMMENT 'Kode Ruang',
    nama_ruang VARCHAR(100) NOT NULL COMMENT 'Nama Ruang',
    jenis_ruang ENUM('OK', 'ICU', 'RAWAT_INAP', 'RAWAT_JALAN', 'LAINNYA') DEFAULT 'OK' COMMENT 'Jenis Ruang',
    lantai VARCHAR(10) DEFAULT NULL COMMENT 'Lantai (misal: 1, 2, 3)',
    kapasitas INT DEFAULT 1 COMMENT 'Kapasitas Ruang',
    fasilitas TEXT DEFAULT NULL COMMENT 'Fasilitas yang tersedia',
    status ENUM('aktif', 'nonaktif', 'maintenance') DEFAULT 'aktif' COMMENT 'Status Ruang',
    keterangan TEXT DEFAULT NULL COMMENT 'Keterangan Tambahan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu Dibuat',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu Diupdate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Data Ruang';

-- Index untuk performa
CREATE INDEX idx_nama_ruang ON tbl_ruang(nama_ruang);
CREATE INDEX idx_jenis_ruang ON tbl_ruang(jenis_ruang);
CREATE INDEX idx_status_ruang ON tbl_ruang(status);

-- ============================================
-- TABEL PERAWAT
-- ============================================
CREATE TABLE IF NOT EXISTS tbl_perawat (
    id_perawat VARCHAR(20) PRIMARY KEY COMMENT 'ID Perawat (misal: PR001)',
    kode_perawat VARCHAR(20) UNIQUE NOT NULL COMMENT 'Kode Perawat',
    nama_perawat VARCHAR(100) NOT NULL COMMENT 'Nama Lengkap Perawat',
    jenis_kelamin ENUM('L', 'P') DEFAULT NULL COMMENT 'Jenis Kelamin (L=Laki-laki, P=Perempuan)',
    no_str VARCHAR(50) DEFAULT NULL COMMENT 'Nomor STR',
    no_telp VARCHAR(20) DEFAULT NULL COMMENT 'Nomor Telepon',
    email VARCHAR(100) DEFAULT NULL COMMENT 'Email',
    alamat TEXT DEFAULT NULL COMMENT 'Alamat Lengkap',
    unit_kerja VARCHAR(50) DEFAULT NULL COMMENT 'Unit Kerja (OK, ICU, dll)',
    shift ENUM('pagi', 'siang', 'malam') DEFAULT NULL COMMENT 'Shift Kerja',
    status ENUM('aktif', 'nonaktif', 'cuti') DEFAULT 'aktif' COMMENT 'Status Perawat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu Dibuat',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu Diupdate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Data Perawat';

-- Index untuk performa
CREATE INDEX idx_nama_perawat ON tbl_perawat(nama_perawat);
CREATE INDEX idx_unit_kerja ON tbl_perawat(unit_kerja);
CREATE INDEX idx_status_perawat ON tbl_perawat(status);
CREATE INDEX idx_shift ON tbl_perawat(shift);

-- ============================================
-- INSERT SAMPLE DATA - DOKTER
-- ============================================
INSERT INTO tbl_dokter (id_dokter, kode_dokter, nama_dokter, spesialisasi, no_sip, status) VALUES
('DR001', 'DOK001', 'dr. Ahmad Hidayat, Sp.An', 'Anestesi', 'SIP/001/2024', 'aktif'),
('DR002', 'DOK002', 'dr. Siti Nurhaliza, Sp.An', 'Anestesi', 'SIP/002/2024', 'aktif'),
('DR003', 'DOK003', 'dr. Budi Santoso, Sp.B', 'Bedah', 'SIP/003/2024', 'aktif'),
('DR004', 'DOK004', 'dr. Rina Wijaya, Sp.B', 'Bedah', 'SIP/004/2024', 'aktif'),
('DR005', 'DOK005', 'dr. Hendra Gunawan, Sp.OG', 'Obstetri & Ginekologi', 'SIP/005/2024', 'aktif'),
('DR006', 'DOK006', 'dr. Maya Sari, Sp.A', 'Anak', 'SIP/006/2024', 'aktif'),
('DR007', 'DOK007', 'dr. Rudi Hartono, Sp.JP', 'Jantung', 'SIP/007/2024', 'aktif'),
('DR008', 'DOK008', 'dr. Dewi Lestari, Sp.PD', 'Penyakit Dalam', 'SIP/008/2024', 'aktif'),
('DR009', 'DOK009', 'dr. Agus Setiawan, Sp.OT', 'Ortopedi', 'SIP/009/2024', 'aktif'),
('DR010', 'DOK010', 'dr. Lina Marlina, Sp.M', 'Mata', 'SIP/010/2024', 'aktif');

-- ============================================
-- INSERT SAMPLE DATA - RUANG
-- ============================================
INSERT INTO tbl_ruang (id_ruang, kode_ruang, nama_ruang, jenis_ruang, lantai, kapasitas, status) VALUES
('RG001', 'OK01', 'Ruang Operasi 1', 'OK', '2', 1, 'aktif'),
('RG002', 'OK02', 'Ruang Operasi 2', 'OK', '2', 1, 'aktif'),
('RG003', 'OK03', 'Ruang Operasi 3', 'OK', '2', 1, 'aktif'),
('RG004', 'OK04', 'Ruang Operasi 4', 'OK', '2', 1, 'aktif'),
('RG005', 'ICU01', 'ICU 1', 'ICU', '3', 10, 'aktif'),
('RG006', 'ICU02', 'ICU 2', 'ICU', '3', 10, 'aktif'),
('RG007', 'RI01', 'Ruang Rawat Inap VIP', 'RAWAT_INAP', '4', 1, 'aktif'),
('RG008', 'RI02', 'Ruang Rawat Inap Kelas 1', 'RAWAT_INAP', '4', 2, 'aktif'),
('RG009', 'RI03', 'Ruang Rawat Inap Kelas 2', 'RAWAT_INAP', '4', 4, 'aktif'),
('RG010', 'RJ01', 'Ruang Rawat Jalan', 'RAWAT_JALAN', '1', 20, 'aktif');

-- ============================================
-- INSERT SAMPLE DATA - PERAWAT
-- ============================================
INSERT INTO tbl_perawat (id_perawat, kode_perawat, nama_perawat, jenis_kelamin, no_str, unit_kerja, shift, status) VALUES
('PR001', 'PER001', 'Ns. Ani Suryani, S.Kep', 'P', 'STR/001/2024', 'OK', 'pagi', 'aktif'),
('PR002', 'PER002', 'Ns. Budi Prasetyo, S.Kep', 'L', 'STR/002/2024', 'OK', 'pagi', 'aktif'),
('PR003', 'PER003', 'Ns. Citra Dewi, S.Kep', 'P', 'STR/003/2024', 'OK', 'siang', 'aktif'),
('PR004', 'PER004', 'Ns. Dedi Kurniawan, S.Kep', 'L', 'STR/004/2024', 'OK', 'siang', 'aktif'),
('PR005', 'PER005', 'Ns. Eka Putri, S.Kep', 'P', 'STR/005/2024', 'OK', 'malam', 'aktif'),
('PR006', 'PER006', 'Ns. Fajar Ramadhan, S.Kep', 'L', 'STR/006/2024', 'OK', 'malam', 'aktif'),
('PR007', 'PER007', 'Ns. Gita Maharani, S.Kep', 'P', 'STR/007/2024', 'ICU', 'pagi', 'aktif'),
('PR008', 'PER008', 'Ns. Hadi Wijaya, S.Kep', 'L', 'STR/008/2024', 'ICU', 'siang', 'aktif'),
('PR009', 'PER009', 'Ns. Indah Permata, S.Kep', 'P', 'STR/009/2024', 'ICU', 'malam', 'aktif'),
('PR010', 'PER010', 'Ns. Joko Susilo, S.Kep', 'L', 'STR/010/2024', 'Rawat Inap', 'pagi', 'aktif'),
('PR011', 'PER011', 'Ns. Kartika Sari, S.Kep', 'P', 'STR/011/2024', 'Rawat Inap', 'siang', 'aktif'),
('PR012', 'PER012', 'Ns. Lukman Hakim, S.Kep', 'L', 'STR/012/2024', 'Rawat Inap', 'malam', 'aktif'),
('PR013', 'PER013', 'Ns. Maya Anggraini, S.Kep', 'P', 'STR/013/2024', 'Rawat Jalan', 'pagi', 'aktif'),
('PR014', 'PER014', 'Ns. Nanda Pratama, S.Kep', 'L', 'STR/014/2024', 'Rawat Jalan', 'siang', 'aktif'),
('PR015', 'PER015', 'Ns. Olivia Putri, S.Kep', 'P', 'STR/015/2024', 'OK', 'pagi', 'aktif');

-- ============================================
-- VERIFY DATA
-- ============================================
-- Cek jumlah data yang berhasil diinsert
SELECT 'DOKTER' AS tabel, COUNT(*) AS jumlah_data FROM tbl_dokter
UNION ALL
SELECT 'RUANG' AS tabel, COUNT(*) AS jumlah_data FROM tbl_ruang
UNION ALL
SELECT 'PERAWAT' AS tabel, COUNT(*) AS jumlah_data FROM tbl_perawat;

-- ============================================
-- SELESAI
-- ============================================
-- Tabel berhasil dibuat dengan sample data:
-- - tbl_dokter: 10 dokter
-- - tbl_ruang: 10 ruang
-- - tbl_perawat: 15 perawat
-- ============================================
