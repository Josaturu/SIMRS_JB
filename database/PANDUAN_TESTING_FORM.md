# 🧪 Panduan Testing Form - Apakah Bisa Menampilkan Data?

## ✅ Yang Sudah Saya Fix

### **1. Form Load Data** ✅
File: `form-persiapan-operasi.php`
- ✅ Field DC sudah bisa load data (`dc_no`, `dc_macam`)
- ✅ Semua field keterangan lain sudah ada `value="<?php echo $existing_data['...'] ?>"`

### **2. Submit Handler** ✅
File: `submit-persiapan-operasi.php`
- ✅ Mapping variabel lengkap (termasuk DC)
- ✅ Query UPDATE lengkap
- ✅ Query INSERT lengkap
- ✅ Bind parameters lengkap

### **3. Test Data** ✅
File: `TEST_INSERT_KETERANGAN.sql`
- ✅ INSERT data lengkap dengan SEMUA field keterangan terisi
- ✅ Semua radio button = Ya (1)
- ✅ Query verifikasi

---

## 🚀 Cara Testing

### **Step 1: Jalankan SQL Test**

```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\TEST_INSERT_KETERANGAN.sql"
```

**Expected Output**:
```
✅ Data test berhasil diinsert!

=== INFORMASI DASAR ===
no_rawat: TEST001
kode_paket: PKT001
tanggal_operasi: 2025-01-20
macam_operasi: Operasi Appendectomy
dpjp: Dr. Budi Santoso, Sp.B
tinggi_badan: 170.00
berat_badan: 65.00
gol_darah: A+
riwayat_alergi: Alergi Penisilin

=== KETERANGAN LENGKAP ===
Item 12: Sejak kemarin pukul 22:00
Item 14: 123 - Foley Catheter No. 16
Item 20-22: WB:2 PRC:3 FFP:1
Item 24: Ceftriaxone 1 gram IV @ 08:00:00
Item 28: Paracetamol 500mg 3x1, Omeprazole 20mg 1x1
Item 30: IV-2025-001
Item 31: 120/80 mmHg
Item 32: 80 x/menit
Item 33: 36.5°C

=== RADIO BUTTON STATUS ===
Item 19 (Transfusi Darah): UBS=1 R.RAWAT=1
Item 24 (Antibiotik): UBS=1 R.RAWAT=1
Item 28 (Obat Lain): UBS=1 R.RAWAT=1
Item 31 (TD): UBS=1 R.RAWAT=1
Item 32 (Nadi): UBS=1 R.RAWAT=1
Item 33 (Suhu): UBS=1 R.RAWAT=1
Item 34 (Pernapasan): UBS=1 R.RAWAT=1
Item 36 (Skin Test): UBS=1 R.RAWAT=1
```

---

### **Step 2: Buka Form di Browser**

```
http://localhost/index.php?page=persiapan-operasi&no_rawat=TEST001&kode_paket=PKT001
```

Atau sesuaikan dengan URL Anda:
```
http://localhost/SIMRS_JB/index.php?page=persiapan-operasi&no_rawat=TEST001&kode_paket=PKT001
```

---

### **Step 3: Cek Apakah Data Muncul**

#### **✅ Checklist Keterangan yang Harus Muncul:**

| Item | Field | Expected Value | Status |
|------|-------|----------------|--------|
| 12 | Waktu Puasa | "Sejak kemarin pukul 22:00" | ⬜ |
| 14 | DC No | 123 | ⬜ |
| 14 | DC Macam | "Foley Catheter No. 16" | ⬜ |
| 20 | Kantong WB | 2 | ⬜ |
| 21 | Kantong PRC | 3 | ⬜ |
| 22 | Kantong FFP | 1 | ⬜ |
| 24 | Antibiotik | "Ceftriaxone 1 gram IV" | ⬜ |
| 24 | Jam | 08:00 | ⬜ |
| 28 | Obat Lain | "Paracetamol 500mg 3x1, Omeprazole 20mg 1x1" | ⬜ |
| 30 | IV Catch No | "IV-2025-001" | ⬜ |
| 31 | Tekanan Darah | "120/80 mmHg" | ⬜ |
| 32 | Nadi | "80 x/menit" | ⬜ |
| 33 | Suhu | 36.5 | ⬜ |
| 34 | Pernapasan | "20 x/menit" | ⬜ |
| 36 | Skin Test | Negatif (tercentang) | ⬜ |

#### **✅ Checklist Radio Button yang Harus Tercentang "Ya":**

**UBS (Kolom Kiri)**:
- ⬜ Item 1-11: Semua tercentang "Ya"
- ⬜ Item 12-18: Semua tercentang "Ya"
- ⬜ Item 19: Persiapan darah - "Ya"
- ⬜ Item 20-24: Semua tercentang "Ya"
- ⬜ Item 25-41: Semua tercentang "Ya"

**R.RAWAT (Kolom Kanan)**:
- ⬜ Item 1-41: Semua tercentang "Ya"

---

### **Step 4: Test Edit & Submit**

1. **Edit beberapa field**:
   - Ubah DC No menjadi: **456**
   - Ubah Antibiotik menjadi: **"Amoxicillin 500mg"**
   - Ubah Tekanan Darah menjadi: **"130/85"**

2. **Submit form**

3. **Reload halaman**

4. **Cek apakah perubahan tersimpan**:
   ```sql
   SELECT dc_no, antibiotik_preops, tekanan_darah
   FROM tbl_anestesi_persiapan_operasi
   WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
   ```
   
   **Expected**:
   ```
   dc_no: 456
   antibiotik_preops: Amoxicillin 500mg
   tekanan_darah: 130/85
   ```

---

## 🐛 Troubleshooting

### **Masalah 1: Form Tidak Muncul Data**

**Cek 1**: Apakah data ada di database?
```sql
SELECT * FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001'\G
```

**Cek 2**: Apakah form load data dengan benar?
Tambahkan debug di `form-persiapan-operasi.php` (sekitar baris 100):
```php
echo "<pre>DEBUG: Existing Data\n";
print_r($existing_data);
echo "</pre>";
die();
```

**Cek 3**: Apakah URL benar?
URL harus punya parameter: `no_rawat=TEST001&kode_paket=PKT001`

---

### **Masalah 2: Beberapa Field Tidak Muncul**

**Kemungkinan**: Field tidak ada di query SELECT.

Cek di `form-persiapan-operasi.php`, cari bagian query SELECT (sekitar baris 90-100):
```php
$query = "SELECT * FROM tbl_anestesi_persiapan_operasi 
          WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket";
```

Pastikan pakai `SELECT *` atau list semua kolom.

---

### **Masalah 3: Radio Button Tidak Tercentang**

**Kemungkinan**: Nilai di database = 0 atau NULL.

Cek nilai di database:
```sql
SELECT 
    transfusi_darah, rawat_transfusi_darah,
    antibiotik, rawat_antibiotik,
    tekanan_darah_radio, rawat_tekanan_darah_radio
FROM tbl_anestesi_persiapan_operasi
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
```

**Expected**: Semua = 1

---

### **Masalah 4: Field DC Tidak Muncul**

**Kemungkinan**: Field `dc_no` dan `dc_macam` belum ada di database.

Jalankan:
```sql
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi WHERE Field IN ('dc_no', 'dc_macam');
```

**Jika kosong**, jalankan:
```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\database\fix_dc_field.sql"
```

---

### **Masalah 5: Data Tersimpan Tapi Tidak Muncul**

**Debug Form Load**:

Edit `form-persiapan-operasi.php`, tambahkan setelah query:
```php
if ($stmt->execute()) {
    $existing_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG
    echo "<h3>DEBUG: Data yang Di-load</h3><pre>";
    echo "dc_no: " . ($existing_data['dc_no'] ?? 'NULL') . "\n";
    echo "dc_macam: " . ($existing_data['dc_macam'] ?? 'NULL') . "\n";
    echo "antibiotik_preops: " . ($existing_data['antibiotik_preops'] ?? 'NULL') . "\n";
    echo "tekanan_darah: " . ($existing_data['tekanan_darah'] ?? 'NULL') . "\n";
    echo "</pre>";
    die("DEBUG STOP");
}
```

---

## 📊 Summary Testing

### **Jika SEMUA Data Muncul** ✅
```
✅ Form bisa load data dengan benar
✅ Semua field keterangan tersimpan
✅ Semua radio button tersimpan
✅ Edit & submit berfungsi normal
```

**Action**: Hapus data test
```sql
DELETE FROM tbl_anestesi_persiapan_operasi 
WHERE no_rawat = 'TEST001' AND kode_paket = 'PKT001';
```

---

### **Jika Ada Field yang TIDAK Muncul** ❌

**Checklist Debug**:
1. ⬜ Cek apakah field ada di database (`SHOW COLUMNS`)
2. ⬜ Cek apakah data tersimpan (`SELECT * FROM ...`)
3. ⬜ Cek apakah form load data (`print_r($existing_data)`)
4. ⬜ Cek apakah input punya `value="<?php echo ... ?>"`

**Beri tahu saya field mana yang tidak muncul!**

---

## 🎯 Expected Result

Setelah testing, Anda harus bisa:

1. ✅ **Lihat data test** di form dengan lengkap
2. ✅ **Edit data** dan tersimpan
3. ✅ **Radio button** tercentang sesuai database
4. ✅ **Semua 15 field keterangan** muncul dengan benar

---

**Silakan jalankan testing dan beri tahu saya hasilnya!** 🚀

Field mana yang:
- ✅ **Muncul dengan benar**
- ❌ **Tidak muncul**
- ⚠️ **Muncul tapi salah**
