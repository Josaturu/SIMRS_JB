# Perbaikan Form Konsultasi Anestesi - Jalan Nafas & Mallampati

## Tanggal: 23 Oktober 2025

## Masalah yang Diperbaiki

1. **Jalan Nafas** hanya memiliki 4 radio button, seharusnya 6
2. **Gerakan Leher** yang seharusnya bagian dari Jalan Nafas malah terpisah ke field `gerakan_leher`
3. **Mallampati** tidak terpisah (I, II, III, IV) menjadi radio button individual
4. Tidak ada input text untuk keterangan **Abnormal** pada Jalan Nafas

## Perubahan Database

### Field Baru yang Ditambahkan:
- `jalan_nafas_keterangan` (varchar 255) - untuk keterangan jika jalan nafas abnormal

### SQL Migration:
```sql
ALTER TABLE `tbl_anestesi_konsultasi_anestesi`
ADD COLUMN `jalan_nafas_keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan untuk jalan nafas abnormal' AFTER `jalan_nafas`;
```

**File SQL:** `sql/ALTER_TABLE_KONSULTASI_ANESTESI_JALAN_NAFAS.sql`

**Catatan:** Field `mallampati` tidak diperlukan karena nilai Mallampati disimpan langsung di field `jalan_nafas`.

## Perubahan Form (form-konsultasi-anestesi.php)

### Jalan Nafas - Sekarang memiliki 9 opsi:
1. Normal
2. Buka mulut > 2 jari
3. Jarak Thyrimental > 3 jari
4. Mallampati I
5. Mallampati II
6. Mallampati III
7. Mallampati IV
8. Gerakan leher Maksimal
9. Abnormal (dengan input text keterangan)

### Gerakan Leher - Tetap terpisah dengan 2 opsi:
1. Maksimal
2. Abnormal (dengan input text keterangan)

## Perubahan JavaScript

Ditambahkan fungsi `toggleJalanNafas()` untuk menampilkan/menyembunyikan input text keterangan ketika "Abnormal" dipilih pada Jalan Nafas.

```javascript
function toggleJalanNafas() {
    var jalanNafas = document.querySelector('input[name="jalan_nafas"]:checked');
    var jalanNafasKeteranganDetail = document.getElementById('jalanNafasKeteranganDetail');
    
    if (jalanNafas && jalanNafas.value === 'Abnormal') {
        jalanNafasKeteranganDetail.style.display = 'block';
    } else {
        jalanNafasKeteranganDetail.style.display = 'none';
        document.getElementById('jalanNafasKeterangan').value = '';
    }
}
```

## Perubahan Process (process-konsultasi-anestesi.php)

### Parameter Baru:
- `$jalan_nafas_keterangan` dari POST `jalanNafasKeterangan`

### Query INSERT/UPDATE:
- Ditambahkan 1 field baru: `jalan_nafas_keterangan`
- Total parameter execute: **97 parameter** (sebelumnya 96)

## Cara Menjalankan

### 1. Jalankan Query SQL
Buka phpMyAdmin atau MySQL client, pilih database `dbanestesi`, lalu jalankan:

```sql
-- File: sql/ALTER_TABLE_KONSULTASI_ANESTESI_JALAN_NAFAS.sql
ALTER TABLE `tbl_anestesi_konsultasi_anestesi`
ADD COLUMN `jalan_nafas_keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan untuk jalan nafas abnormal' AFTER `jalan_nafas`;
```

### 2. Verifikasi Perubahan
Cek struktur tabel:
```sql
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

Pastikan field `jalan_nafas_keterangan` sudah ada.

### 3. Test Form
1. Buka form konsultasi anestesi
2. Pilih salah satu dari 9 opsi Jalan Nafas (termasuk Mallampati I-IV dan Gerakan leher Maksimal)
3. Jika pilih "Abnormal", input text keterangan akan muncul
4. Pilih Gerakan Leher (Maksimal atau Abnormal)
5. Simpan form dan verifikasi data tersimpan dengan benar

## File yang Dimodifikasi

1. **sql/ALTER_TABLE_KONSULTASI_ANESTESI_JALAN_NAFAS.sql** (NEW)
   - Query untuk menambah field baru

2. **views/form-konsultasi-anestesi.php**
   - Menambah 5 radio button baru untuk Jalan Nafas (total 9 opsi)
   - Mallampati I-IV sekarang bagian dari radio button Jalan Nafas
   - Gerakan leher Maksimal sekarang bagian dari radio button Jalan Nafas
   - Menambah input text untuk keterangan abnormal Jalan Nafas
   - Menambah fungsi JavaScript `toggleJalanNafas()`

3. **process/process-konsultasi-anestesi.php**
   - Menambah parameter `$jalan_nafas_keterangan`
   - Update query INSERT dengan 1 field baru
   - Update query ON DUPLICATE KEY UPDATE
   - Update execute statement dengan 97 parameter (dari 96)

## Struktur Data Jalan Nafas

### Sebelum:
- Field: `jalan_nafas` (varchar 255)
- Nilai: "Normal", "Buka mulut", "Jarak Thyrimental", "Mallampati"
- Field: `gerakan_leher` (varchar 50)
- Nilai: "Maksimal", "Abnormal"

### Sesudah:
- Field: `jalan_nafas` (varchar 255)
- Nilai: "Normal", "Buka mulut", "Jarak Thyrimental", "Mallampati I", "Mallampati II", "Mallampati III", "Mallampati IV", "Gerakan leher Maksimal", "Abnormal"
- Field: `jalan_nafas_keterangan` (varchar 255) - NEW
- Nilai: Keterangan jika abnormal
- Field: `gerakan_leher` (varchar 50) - TETAP
- Nilai: "Maksimal", "Abnormal"

## Catatan Penting

1. **Backward Compatibility**: Data lama yang memiliki nilai "Mallampati" di field `jalan_nafas` tidak akan otomatis ter-migrate. Jika diperlukan, buat script migration terpisah.

2. **Validasi**: Form tidak memiliki validasi required untuk Mallampati dan Jalan Nafas. Bisa ditambahkan jika diperlukan.

3. **PDF Generator**: Jika ada PDF generator untuk form ini, perlu diupdate juga untuk menampilkan field baru.

## Testing Checklist

- [ ] Query SQL berhasil dijalankan
- [ ] Field baru `jalan_nafas_keterangan` muncul di database
- [ ] Form menampilkan 9 radio button Jalan Nafas (termasuk Mallampati I-IV dan Gerakan leher Maksimal)
- [ ] Toggle keterangan abnormal Jalan Nafas berfungsi
- [ ] Toggle keterangan abnormal Gerakan Leher tetap berfungsi
- [ ] Data tersimpan dengan benar ke database
- [ ] Data ter-load dengan benar saat edit form
- [ ] Autosave berfungsi dengan field baru

## Troubleshooting

### Error: "Unknown column 'jalan_nafas_keterangan'"
**Solusi**: Jalankan query ALTER TABLE di atas.

### Error: "Parameter mismatch"
**Solusi**: Pastikan jumlah parameter di execute() adalah 97.

### Keterangan abnormal tidak muncul
**Solusi**: Pastikan fungsi `toggleJalanNafas()` dipanggil di `DOMContentLoaded`.

### Data tidak tersimpan
**Solusi**: Cek console browser untuk error JavaScript, dan cek log PHP untuk error database.
