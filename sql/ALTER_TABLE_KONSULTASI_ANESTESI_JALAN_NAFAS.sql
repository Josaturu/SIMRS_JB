-- Menambah field jalan_nafas_keterangan untuk form konsultasi anestesi
-- Field ini digunakan untuk menyimpan keterangan jika jalan nafas abnormal

ALTER TABLE `tbl_anestesi_konsultasi_anestesi`
ADD COLUMN `jalan_nafas_keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan untuk jalan nafas abnormal' AFTER `jalan_nafas`;
