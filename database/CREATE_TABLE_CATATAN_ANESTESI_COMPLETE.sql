-- ============================================
-- SQL LENGKAP: tbl_anestesi_catatan_anestesi
-- Dibuat: 2025-10-23
-- Deskripsi: Tabel untuk menyimpan catatan sedasi dan anestesi
-- ============================================

-- Drop table jika sudah ada (HATI-HATI: Ini akan menghapus semua data!)
DROP TABLE IF EXISTS `tbl_anestesi_catatan_anestesi`;

-- Buat tabel baru
CREATE TABLE `tbl_anestesi_catatan_anestesi` (
  `id` varchar(36) NOT NULL COMMENT 'UUID primary key',
  `no_rawat` varchar(17) NOT NULL COMMENT 'Nomor rawat pasien',
  `kode_paket` varchar(15) NOT NULL COMMENT 'Kode paket operasi',
  `tanggal` date NOT NULL COMMENT 'Tanggal operasi',
  `jam_mulai` time NOT NULL COMMENT 'Jam mulai operasi',
  
  -- Data Pasien
  `no_rm` varchar(15) NOT NULL COMMENT 'Nomor rekam medis',
  `nama` varchar(100) NOT NULL COMMENT 'Nama pasien',
  `tgl_lahir` date NOT NULL COMMENT 'Tanggal lahir pasien',
  `ruang_perawatan` varchar(50) NOT NULL COMMENT 'Ruang perawatan',
  `dokter_merawat` varchar(100) NOT NULL COMMENT 'Nama dokter yang merawat',
  `dokter_anestesi` varchar(100) NOT NULL COMMENT 'Nama dokter anestesi',
  `perawat_anestesi` varchar(100) NOT NULL COMMENT 'Nama perawat anestesi',
  
  -- Diagnosa & Tindakan
  `diagnosa_pra_bedah` text NOT NULL COMMENT 'Diagnosa sebelum operasi',
  `nama_tindakan` varchar(50) NOT NULL COMMENT 'Nama tindakan operasi',
  `diagnosa_pasca_bedah` text NOT NULL COMMENT 'Diagnosa setelah operasi',
  `asessment_pra_anestesi` text NOT NULL COMMENT 'Assessment sebelum anestesi',
  
  -- Jenis Anestesi
  `jenis_anestesi` varchar(20) DEFAULT NULL COMMENT 'Jenis anestesi: Ringan/Sedang/Berat',
  `keterangan` text DEFAULT NULL COMMENT 'Keterangan tambahan',
  `tanggal_anestesi` date DEFAULT NULL COMMENT 'Tanggal anestesi',
  `pukul` time DEFAULT NULL COMMENT 'Pukul anestesi',
  `dokter_bedah` varchar(100) DEFAULT NULL COMMENT 'Nama dokter bedah',
  `perawat_bedah` varchar(100) DEFAULT NULL COMMENT 'Nama perawat bedah',
  `jenis_pembedahan` varchar(20) DEFAULT NULL COMMENT 'Jenis pembedahan: Elektif/Cito',
  
  -- Vital Sign
  `bb` decimal(5,2) DEFAULT NULL COMMENT 'Berat badan (kg)',
  `td` varchar(10) DEFAULT NULL COMMENT 'Tekanan darah',
  `suhu` decimal(4,2) DEFAULT NULL COMMENT 'Suhu tubuh (°C)',
  `respirasi` int(11) DEFAULT NULL COMMENT 'Respirasi (per menit)',
  `hb` decimal(4,2) DEFAULT NULL COMMENT 'Hemoglobin',
  `tb` decimal(5,2) DEFAULT NULL COMMENT 'Tinggi badan (cm)',
  `nadi` int(11) DEFAULT NULL COMMENT 'Nadi (per menit)',
  `gcs` int(11) DEFAULT NULL COMMENT 'Glasgow Coma Scale',
  `golongan_darah` varchar(5) DEFAULT NULL COMMENT 'Golongan darah',
  `skrining_nyeri` varchar(10) DEFAULT NULL COMMENT 'Skrining nyeri: Ya/Tidak',
  `status_fisik_asa` varchar(10) DEFAULT NULL COMMENT 'Status fisik ASA: I/II/III/IV/V',
  `penyulit_pra_anestesi` text DEFAULT NULL COMMENT 'Penyulit pra anestesi',
  `resiko` varchar(20) DEFAULT NULL COMMENT 'Resiko: Ringan/Sedang/Berat',
  
  -- Checklist & Teknik
  `checklist_sebelum_induksi` text DEFAULT NULL COMMENT 'Checklist sebelum induksi (comma separated)',
  `teknik_anestesi` varchar(50) DEFAULT NULL COMMENT 'Teknik anestesi',
  `infus_perifer` text DEFAULT NULL COMMENT 'Infus perifer (comma separated)',
  `lain_lain_posisi` text DEFAULT NULL COMMENT 'Keterangan posisi lain-lain',
  
  -- Premedikasi
  `premedikasi` text DEFAULT NULL COMMENT 'Premedikasi (comma separated)',
  `premedik_nama_obat` text DEFAULT NULL COMMENT 'Nama obat premedikasi',
  `premedik_dosis_obat` text DEFAULT NULL COMMENT 'Dosis obat premedikasi',
  
  -- Induksi & Jalan Nafas
  `induksi` text DEFAULT NULL COMMENT 'Induksi (comma separated)',
  `jalan_nafas` text DEFAULT NULL COMMENT 'Jalan nafas (comma separated)',
  `ventilasi` text DEFAULT NULL COMMENT 'Ventilasi (comma separated)',
  `ventilator` text DEFAULT NULL COMMENT 'Ventilator (comma separated)',
  `ukuran_balon` varchar(20) DEFAULT NULL COMMENT 'Ukuran balon',
  `jenis_balon` varchar(20) DEFAULT NULL COMMENT 'Jenis balon',
  `posisi_ett` varchar(20) DEFAULT NULL COMMENT 'Posisi ETT',
  
  -- Anestesi Regional
  `lokasi_regional` varchar(100) DEFAULT NULL COMMENT 'Lokasi anestesi regional',
  `jarum_regional` varchar(100) DEFAULT NULL COMMENT 'Jarum regional',
  `kateter_regional` varchar(100) DEFAULT NULL COMMENT 'Kateter regional',
  `obat_anestesi_lokal` varchar(100) DEFAULT NULL COMMENT 'Obat anestesi lokal',
  `hasil_regional` text DEFAULT NULL COMMENT 'Hasil regional (comma separated)',
  
  -- Obat & Cairan
  `obat` text DEFAULT NULL COMMENT 'Obat yang diberikan (comma separated)',
  `cairan_infus` text DEFAULT NULL COMMENT 'Cairan infus (comma separated)',
  `cairan_output` text DEFAULT NULL COMMENT 'Cairan output (comma separated)',
  
  -- Masalah & Tindakan
  `masalah_selama_anestesi` text DEFAULT NULL COMMENT 'Masalah selama anestesi (comma separated)',
  `tindakan` text DEFAULT NULL COMMENT 'Tindakan yang dilakukan (comma separated)',
  
  -- Petugas
  `perawat_menyerahkan` varchar(100) DEFAULT NULL COMMENT 'Perawat yang menyerahkan',
  `perawat_menerima` varchar(100) DEFAULT NULL COMMENT 'Perawat yang menerima',
  `dokter_anestesi_ttd` varchar(100) DEFAULT NULL COMMENT 'Dokter anestesi yang menandatangani',
  
  -- Timestamp
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu dibuat',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu diupdate',
  
  -- Waktu Operasi
  `mulai_anestesi` time DEFAULT NULL COMMENT 'Waktu mulai anestesi',
  `selesai_anestesi` time DEFAULT NULL COMMENT 'Waktu selesai anestesi',
  `mulai_pembedahan` time DEFAULT NULL COMMENT 'Waktu mulai pembedahan',
  `selesai_pembedahan` time DEFAULT NULL COMMENT 'Waktu selesai pembedahan',
  `keterangan_waktu` varchar(255) DEFAULT NULL COMMENT 'Keterangan waktu',
  `induksi_pukul` time DEFAULT NULL COMMENT 'Pukul induksi',
  `pasien_siap_insisi` time DEFAULT NULL COMMENT 'Pasien siap insisi',
  `insisi_mulai_pukul` time DEFAULT NULL COMMENT 'Insisi mulai pukul',
  `operasi_mulai_pukul` time DEFAULT NULL COMMENT 'Operasi mulai pukul',
  `ekstubasi_pukul` time DEFAULT NULL COMMENT 'Ekstubasi pukul',
  `pasien_keluar_ok` time DEFAULT NULL COMMENT 'Pasien keluar OK',
  `lain_lain_balon` varchar(100) DEFAULT NULL COMMENT 'Lain-lain balon',
  
  -- Primary Key
  PRIMARY KEY (`id`),
  
  -- Index untuk composite key (untuk mencegah duplicate)
  KEY `idx_composite_key` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`),
  
  -- Foreign Key (optional, uncomment jika tabel booking_operasi sudah ada)
  -- CONSTRAINT `fk_catatan_booking` FOREIGN KEY (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
  -- REFERENCES `booking_operasi` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
  -- ON DELETE CASCADE ON UPDATE CASCADE
  
  -- Index untuk pencarian
  KEY `idx_no_rawat` (`no_rawat`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_no_rm` (`no_rm`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabel catatan sedasi dan anestesi';

-- ============================================
-- INSERT DATA SAMPLE (Optional)
-- ============================================

INSERT INTO `tbl_anestesi_catatan_anestesi` (
  `id`, `no_rawat`, `kode_paket`, `tanggal`, `jam_mulai`, 
  `no_rm`, `nama`, `tgl_lahir`, `ruang_perawatan`, `dokter_merawat`, 
  `dokter_anestesi`, `perawat_anestesi`, `diagnosa_pra_bedah`, `nama_tindakan`, 
  `diagnosa_pasca_bedah`, `asessment_pra_anestesi`, `jenis_anestesi`, `keterangan`, 
  `tanggal_anestesi`, `pukul`, `dokter_bedah`, `perawat_bedah`, `jenis_pembedahan`, 
  `bb`, `td`, `suhu`, `respirasi`, `hb`, `tb`, `nadi`, `gcs`, `golongan_darah`, 
  `skrining_nyeri`, `status_fisik_asa`, `penyulit_pra_anestesi`, `resiko`, 
  `checklist_sebelum_induksi`, `teknik_anestesi`, `infus_perifer`, `lain_lain_posisi`, 
  `premedikasi`, `premedik_nama_obat`, `premedik_dosis_obat`, `induksi`, `jalan_nafas`, 
  `ventilasi`, `ventilator`, `ukuran_balon`, `jenis_balon`, `posisi_ett`, 
  `lokasi_regional`, `jarum_regional`, `kateter_regional`, `obat_anestesi_lokal`, 
  `hasil_regional`, `obat`, `cairan_infus`, `cairan_output`, `masalah_selama_anestesi`, 
  `tindakan`, `perawat_menyerahkan`, `perawat_menerima`, `dokter_anestesi_ttd`, 
  `mulai_anestesi`, `selesai_anestesi`, `mulai_pembedahan`, `selesai_pembedahan`, 
  `keterangan_waktu`, `induksi_pukul`, `pasien_siap_insisi`, `insisi_mulai_pukul`, 
  `operasi_mulai_pukul`, `ekstubasi_pukul`, `pasien_keluar_ok`, `lain_lain_balon`
) VALUES (
  '199ba82d-a861-4aa6-8fbe-d94576ceabb6', '1', '1', '2025-02-01', '23:59:00',
  '1', 'dadang beton', '1995-10-26', '2', 'BOYKE',
  'boyke', 'dudung', 'djashiu', 'iuhwqiud',
  'qwjdqiudh', 'jndiqdu', 'Sedang', 'jiwnfwkjfqnpiuqkjwfniqwjniunwkjqdnipqubfewkjbviejnfiwuqfnoqjwnfdiqunhij',
  '2025-10-10', '10:56:00', 'sumanto', 'dadeng', 'Cito',
  90.00, '09', 1.60, 22, 1.70, 1.70, 16, 14, 'B',
  'Ya', 'III', 'sulit mikir', 'Sedang',
  'Ijin Operasi & Anestesi, Antibiotika profilaksis, EKG Lead, SpO₂, Urine Catheter, Cek mesin Anestesi, Cek suction unit, NIBP, Temp, Cek Monitor, Persiapan Jalan Napas, Stetoskop, NGT, Persiapan Obat-Obatan Anestesi & Emergency',
  'Regional - CSE', 'selangkangan, selangkangan, selangkangan', 'f iowejdf oiwjoiwjdoiqjoijqwoidj qwoijd',
  'ORAL, I.M, I.V, Nama Obat :, Dosis Obat :', 'i qowij oiqwj oiqwj oiqwj oi', 'i jwoijqoijdoqij qoijqoijdqoiwj',
  'Propofol, Thiopental, Etomidate', 'facemask, LMA, ETT',
  'Spontant, Assist, Control', 'TV, RR, SpO2, PEEP', 'gede', 'Tanpa Balon', 'Nasal',
  'dqwdjni', 'awdklhuioh', 'jwdqniudh', 'wdqkjdnipun',
  'Ketinggian Blok, Total Blok, Gagal Blok, Partial Blok',
  ' komix,   komix,   komix,   komix,   komix,   komix,   komix,   komix',
  'qwdkjh,  wqjdowiqdjoi,  dqwdknqwoidnqwoidnqwklndiqwubfweiujfkewjvnipeuqbgeiqjn,  qwdjo',
  'oiqdnowi,  asldknoiqjqidjoqwdqowidjqwoidj,  oioiwqdjoqidjqwoi,  jqwndiqjdniqun',
  'dkwjndjn,  klqwndfknqwofijnoin,  lk,  nmoiqnfwqjwjfnowjenfowinfweiufiu',
  'kwejnfiwuenfin,  kjewnijfnwijfn,  kjnjefwijfiwjnfipu,  nifjebnwipufnwpiufnqipufnqwiubn',
  'Perawat Anestesi 2', 'Perawat Ruangan 1', 'Dokter Anestesi 1',
  '21:57:00', '20:57:00', '21:57:00', '21:59:00',
  'kjhiuhj', '03:00:00', '01:00:00', '04:01:00',
  '06:04:00', '07:03:00', '08:01:00', ''
);

-- ============================================
-- VERIFIKASI
-- ============================================

-- Cek struktur tabel
DESCRIBE `tbl_anestesi_catatan_anestesi`;

-- Cek data
SELECT 
  id, no_rawat, kode_paket, tanggal, jam_mulai, 
  nama, no_rm, ruang_perawatan, dokter_merawat
FROM `tbl_anestesi_catatan_anestesi`;

-- Cek total records
SELECT COUNT(*) as total_records FROM `tbl_anestesi_catatan_anestesi`;

-- ============================================
-- CATATAN PENTING
-- ============================================

/*
1. Backup data lama sebelum DROP TABLE!
   
   Cara backup:
   mysqldump -u root -p dbanestesi tbl_anestesi_catatan_anestesi > backup_catatan_anestesi.sql

2. Jika ingin menambahkan UNIQUE constraint untuk mencegah duplicate:
   
   ALTER TABLE `tbl_anestesi_catatan_anestesi` 
   ADD UNIQUE KEY `unique_composite` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`);

3. Jika ingin menambahkan Foreign Key ke tabel booking_operasi:
   
   ALTER TABLE `tbl_anestesi_catatan_anestesi`
   ADD CONSTRAINT `fk_catatan_booking` 
   FOREIGN KEY (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
   REFERENCES `booking_operasi` (`no_rawat`,`kode_paket`,`tanggal`,`jam_mulai`) 
   ON DELETE CASCADE ON UPDATE CASCADE;

4. Index yang sudah dibuat:
   - PRIMARY KEY: id
   - INDEX: idx_composite_key (no_rawat, kode_paket, tanggal, jam_mulai)
   - INDEX: idx_no_rawat (no_rawat)
   - INDEX: idx_tanggal (tanggal)
   - INDEX: idx_no_rm (no_rm)

5. Total fields: 75 kolom
   
6. Charset: utf8mb4 (support emoji & karakter khusus)
   
7. Engine: InnoDB (support transactions & foreign keys)
*/
