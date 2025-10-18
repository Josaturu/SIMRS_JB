-- Tambahkan kolom DPJP pada tabel tbl_anestesi_persiapan_operasi
-- Jalankan script ini di database MySQL

ALTER TABLE tbl_anestesi_persiapan_operasi 
ADD COLUMN dpjp VARCHAR(255) NULL AFTER macam_operasi;

-- Keterangan:
-- dpjp = Dokter Penanggung Jawab Pelayanan
-- Ditambahkan setelah kolom macam_operasi
-- Tipe data: VARCHAR(255) untuk menampung nama dokter
-- NULL: boleh kosong (opsional)
