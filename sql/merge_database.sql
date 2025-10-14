-- ============================================
-- DATABASE MERGE SCRIPT
-- Purpose: Merge dbanestesi[1].sql dengan database existing
-- Date: 2025-10-14
-- ============================================

-- IMPORTANT: Backup database dulu sebelum run script ini!
-- mysqldump -u root -p dbanestesi > backup_before_merge_$(date +%Y%m%d).sql

-- ============================================
-- STEP 1: CREATE TABLES (jika belum ada)
-- ============================================

-- Table: booking_operasi
CREATE TABLE IF NOT EXISTS `booking_operasi` (
  `no_rawat` varchar(17) NOT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time DEFAULT NULL,
  `status` enum('Menunggu','Proses Operasi','Selesai') DEFAULT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `kd_ruang_ok` varchar(3) NOT NULL,
  `kd_pasien` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`, `kode_paket`, `tanggal`, `jam_mulai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: pasien
CREATE TABLE IF NOT EXISTS `pasien` (
  `kd_pasien` varchar(20) NOT NULL,
  `kode_rekam_medis` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `gol_darah` enum('A','B','AB','O') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kd_pasien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- STEP 2: INSERT SAMPLE DATA (jika belum ada)
-- ============================================

-- Insert sample patient (jika belum ada)
INSERT IGNORE INTO `pasien` 
(`kd_pasien`, `kode_rekam_medis`, `nama`, `alamat`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `no_hp`, `gol_darah`) 
VALUES
('1', '1', 'dadang beton', 'morokosono', 'L', 'moskow', '1995-10-26', '081234567890', 'O');

-- Insert sample booking (jika belum ada)
INSERT IGNORE INTO `booking_operasi` 
(`no_rawat`, `kode_paket`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`, `kd_dokter`, `kd_ruang_ok`, `kd_pasien`) 
VALUES
('1', '1', '2025-02-01', '23:59:00', '00:00:00', 'Menunggu', '1', '1', '1'),
('TEST-001', 'PKT-001', '2025-10-10', '08:00:00', '10:00:00', 'Menunggu', '1', '1', '1');

-- ============================================
-- STEP 3: ADD FOREIGN KEY CONSTRAINTS (jika belum ada)
-- ============================================

-- Check & add FK for booking_operasi
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'booking_operasi'
    AND CONSTRAINT_NAME = 'fk_booking_pasien'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE booking_operasi ADD CONSTRAINT fk_booking_pasien FOREIGN KEY (kd_pasien) REFERENCES pasien(kd_pasien) ON UPDATE CASCADE',
    'SELECT "FK fk_booking_pasien already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- STEP 4: VERIFY MERGE
-- ============================================

-- Check tables
SHOW TABLES;

-- Check patient data
SELECT * FROM pasien;

-- Check booking data
SELECT * FROM booking_operasi;

-- Check table structures
DESCRIBE booking_operasi;
DESCRIBE pasien;

-- ============================================
-- EXPECTED RESULT:
-- - All tables created
-- - Sample data inserted
-- - Foreign keys added
-- - No errors
-- ============================================
