===============================================
CARA MENJALANKAN QUERY ALTER TABLE
UNTUK PERBAIKAN JALAN NAFAS & MALLAMPATI
===============================================

LANGKAH 1: Buka phpMyAdmin
---------------------------
1. Buka browser
2. Ketik: http://localhost/phpmyadmin
3. Login dengan username dan password MySQL Anda

LANGKAH 2: Pilih Database
--------------------------
1. Klik database "dbanestesi" di sidebar kiri
2. Pastikan database sudah terpilih (terlihat di bagian atas)

LANGKAH 3: Jalankan Query
--------------------------
1. Klik tab "SQL" di bagian atas
2. Copy paste query berikut ke dalam text area:

ALTER TABLE `tbl_anestesi_konsultasi_anestesi`
ADD COLUMN `jalan_nafas_keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan untuk jalan nafas abnormal' AFTER `jalan_nafas`;

3. Klik tombol "Go" atau "Kirim"

LANGKAH 4: Verifikasi
---------------------
Jika berhasil, akan muncul pesan:
"1 row affected"

Untuk memastikan, jalankan query berikut:

DESCRIBE tbl_anestesi_konsultasi_anestesi;

Pastikan ada field:
- jalan_nafas_keterangan (varchar 255)

LANGKAH 5: Test Form
--------------------
1. Buka aplikasi SIMRS
2. Buka form Konsultasi Anestesi
3. Cek bagian "Jalan Nafas" sekarang ada 9 radio button:
   - Normal
   - Buka mulut > 2 jari
   - Jarak Thyrimental > 3 jari
   - Mallampati I
   - Mallampati II
   - Mallampati III
   - Mallampati IV
   - Gerakan leher Maksimal
   - Abnormal
4. Pilih "Abnormal" pada Jalan Nafas, input text keterangan akan muncul
5. Simpan form dan pastikan data tersimpan dengan benar

===============================================
TROUBLESHOOTING
===============================================

ERROR: "Table 'dbanestesi.tbl_anestesi_konsultasi_anestesi' doesn't exist"
SOLUSI: Pastikan Anda sudah memilih database "dbanestesi"

ERROR: "Duplicate column name 'jalan_nafas_keterangan'"
SOLUSI: Query sudah pernah dijalankan sebelumnya, tidak perlu dijalankan lagi

ERROR: "Access denied"
SOLUSI: Login dengan user yang memiliki privilege ALTER TABLE

===============================================
FILE TERKAIT
===============================================

1. sql/ALTER_TABLE_KONSULTASI_ANESTESI_JALAN_NAFAS.sql
   - File SQL untuk ALTER TABLE

2. docs/fixes/FIX_JALAN_NAFAS_MALLAMPATI.md
   - Dokumentasi lengkap perubahan

3. views/form-konsultasi-anestesi.php
   - Form yang sudah diupdate

4. process/process-konsultasi-anestesi.php
   - Process handler yang sudah diupdate

===============================================
KONTAK
===============================================

Jika ada masalah, hubungi developer atau cek log error di:
- Browser Console (F12)
- PHP Error Log (logs/persiapan-operasi-debug.log)
- MySQL Error Log

===============================================
