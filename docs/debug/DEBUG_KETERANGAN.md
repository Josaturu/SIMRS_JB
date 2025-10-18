# 🐛 Debug: Keterangan Tidak Muncul (Field Sudah Ada)

## ✅ Konfirmasi
- Field keterangan **SUDAH ADA** di database
- Submit handler **SUDAH BENAR**
- Form **SUDAH BENAR**
- Tapi data keterangan **TIDAK MUNCUL** setelah reload

---

## 🔍 Debug Step by Step

### **Step 1: Aktifkan Debug Mode**

Edit file `submit-persiapan-operasi.php` baris 20-39, **hapus comment** `/*` dan `*/`:

**SEBELUM**:
```php
// DEBUG: Tampilkan data keterangan yang dikirim
/*
echo "<h3>DEBUG: Data Keterangan yang Dikirim</h3><pre>";
...
die("DEBUG STOP - Hapus comment untuk lanjut");
*/
```

**SESUDAH** (hapus `/*` dan `*/`):
```php
// DEBUG: Tampilkan data keterangan yang dikirim
echo "<h3>DEBUG: Data Keterangan yang Dikirim</h3><pre>";
echo "ket12 (waktu_puasa): " . ($formData['ket12'] ?? 'KOSONG') . "\n";
echo "ket14 (dc_no): " . ($formData['ket14'] ?? 'KOSONG') . "\n";
echo "ket14_macam (dc_macam): " . ($formData['ket14_macam'] ?? 'KOSONG') . "\n";
echo "ket20 (kantong_wb): " . ($formData['ket20'] ?? 'KOSONG') . "\n";
echo "ket21 (kantong_prc): " . ($formData['ket21'] ?? 'KOSONG') . "\n";
echo "ket22 (kantong_ffp): " . ($formData['ket22'] ?? 'KOSONG') . "\n";
echo "ket24 (antibiotik): " . ($formData['ket24'] ?? 'KOSONG') . "\n";
echo "ket24_waktu (jam): " . ($formData['ket24_waktu'] ?? 'KOSONG') . "\n";
echo "ket28 (obat_lain): " . ($formData['ket28'] ?? 'KOSONG') . "\n";
echo "ket30 (iv_catch_no): " . ($formData['ket30'] ?? 'KOSONG') . "\n";
echo "ket31 (tekanan_darah): " . ($formData['ket31'] ?? 'KOSONG') . "\n";
echo "ket32 (nadi): " . ($formData['ket32'] ?? 'KOSONG') . "\n";
echo "ket33 (suhu): " . ($formData['ket33'] ?? 'KOSONG') . "\n";
echo "ket34 (pernafasan): " . ($formData['ket34'] ?? 'KOSONG') . "\n";
echo "ket36 (skin_test): " . ($formData['ket36'] ?? 'KOSONG') . "\n";
echo "</pre>";
die("DEBUG STOP - Hapus comment untuk lanjut");
```

### **Step 2: Submit Form dengan Data Keterangan**

1. Buka form persiapan operasi
2. Isi field keterangan:
   - Waktu puasa: "Sejak kemarin"
   - Antibiotik: "Ceftriaxone"
   - Jam: "08:00"
   - Obat Lain: "Paracetamol"
   - Tekanan Darah: "120/80"
   - Nadi: "80"
   - Suhu: "36.5"
   - Pernapasan: "20"
3. **Submit**

### **Step 3: Lihat Output Debug**

Setelah submit, Anda akan lihat output seperti ini:

#### **Skenario A: Data TERKIRIM** ✅
```
DEBUG: Data Keterangan yang Dikirim
ket12 (waktu_puasa): Sejak kemarin
ket24 (antibiotik): Ceftriaxone
ket24_waktu (jam): 08:00
ket28 (obat_lain): Paracetamol
ket31 (tekanan_darah): 120/80
ket32 (nadi): 80
ket33 (suhu): 36.5
ket34 (pernafasan): 20
```

**Artinya**: Data terkirim dengan benar. Masalahnya di **query INSERT/UPDATE** atau **bind parameters**.

**Solusi**: Lanjut ke Step 4.

#### **Skenario B: Data KOSONG** ❌
```
DEBUG: Data Keterangan yang Dikirim
ket12 (waktu_puasa): KOSONG
ket24 (antibiotik): KOSONG
ket24_waktu (jam): KOSONG
ket28 (obat_lain): KOSONG
...
```

**Artinya**: Data **TIDAK TERKIRIM** dari form. Masalahnya di **form HTML**.

**Solusi**: Cek apakah input field punya `name` yang benar.

---

### **Step 4: Cek Query INSERT/UPDATE**

Jika data terkirim tapi tidak tersimpan, tambahkan debug di query:

Edit `submit-persiapan-operasi.php`, cari bagian execute query (sekitar baris 424):

**SEBELUM**:
```php
// Execute query
if ($stmt->execute()) {
    $_SESSION['success'] = $existing ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!';
    header("Location: ...");
```

**SESUDAH**:
```php
// Execute query
if ($stmt->execute()) {
    // DEBUG: Cek data yang tersimpan
    echo "<h3>✅ Query berhasil execute!</h3>";
    echo "<p>Cek database dengan query:</p>";
    echo "<pre>SELECT * FROM tbl_anestesi_persiapan_operasi WHERE no_rawat = '{$formData['no_rawat']}' ORDER BY id DESC LIMIT 1;</pre>";
    die("DEBUG: Cek database sekarang");
    
    $_SESSION['success'] = $existing ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!';
    header("Location: ...");
```

Kemudian jalankan query yang ditampilkan di MySQL untuk cek apakah data tersimpan.

---

### **Step 5: Cek Bind Parameters**

Jika query execute tapi data NULL, kemungkinan **bind parameters salah**.

Cari di `submit-persiapan-operasi.php` bagian bind parameters (sekitar baris 310-420):

**Cek apakah ada**:
```php
$stmt->bindParam(':waktu_puasa', $waktu_puasa);
$stmt->bindParam(':kantong_wb', $kantong_wb);
$stmt->bindParam(':kantong_prc', $kantong_prc);
$stmt->bindParam(':kantong_ffp', $kantong_ffp);
$stmt->bindParam(':antibiotik_preops', $antibiotik_preops);
$stmt->bindParam(':jam_antibiotik', $jam_antibiotik);
$stmt->bindParam(':obat_lain', $obat_lain);
$stmt->bindParam(':iv_catch_no', $iv_catch_no);
$stmt->bindParam(':tekanan_darah', $tekanan_darah);
$stmt->bindParam(':nadi', $nadi);
$stmt->bindParam(':suhu', $suhu);
$stmt->bindParam(':pernafasan', $pernafasan);
$stmt->bindParam(':hasil_skin_test', $hasil_skin_test);
```

**Jika TIDAK ADA**, berarti bind parameters hilang!

---

### **Step 6: Cek Form Load Data**

Jika data tersimpan di database tapi tidak muncul di form, masalahnya di **load data**.

Edit `form-persiapan-operasi.php`, tambahkan debug setelah query load data (sekitar baris 100):

```php
// Load existing data
$existing_data = [];
if (!empty($_GET['no_rawat']) && !empty($_GET['kode_paket'])) {
    // ... query load data ...
    
    // DEBUG: Tampilkan data yang di-load
    echo "<h3>DEBUG: Data yang Di-load dari Database</h3><pre>";
    print_r($existing_data);
    echo "</pre>";
    die("DEBUG STOP");
}
```

**Expected**: Array berisi semua field keterangan dengan nilai yang tersimpan.

---

## 🎯 Checklist Troubleshooting

### **1. Data Terkirim dari Form?**
- [ ] Ya → Lanjut ke #2
- [ ] Tidak → Cek `name` attribute di form HTML

### **2. Query Execute Berhasil?**
- [ ] Ya → Lanjut ke #3
- [ ] Tidak → Cek error dengan `print_r($stmt->errorInfo())`

### **3. Data Tersimpan di Database?**
- [ ] Ya → Lanjut ke #4
- [ ] Tidak → Cek bind parameters

### **4. Data Di-load dari Database?**
- [ ] Ya → Lanjut ke #5
- [ ] Tidak → Cek query SELECT

### **5. Data Muncul di Form?**
- [ ] Ya → ✅ **SELESAI!**
- [ ] Tidak → Cek `value="<?php echo $existing_data['...'] ?>"` di form

---

## 🔧 Quick Fix: Kemungkinan Masalah

### **Masalah 1: Field DC (Item 14) Tidak Ada Mapping**

Cek di `submit-persiapan-operasi.php` apakah ada:

```php
$dc_no = !empty($formData['ket14']) ? (int)$formData['ket14'] : null;
$dc_macam = $formData['ket14_macam'] ?? null;
```

**Jika TIDAK ADA**, tambahkan di bagian mapping variabel (sekitar baris 58).

### **Masalah 2: Field DC Tidak Ada di Query**

Cek di query UPDATE dan INSERT apakah ada:

```sql
-- UPDATE
dc_no = :dc_no, dc_macam = :dc_macam,

-- INSERT
dc_no, dc_macam, ... VALUES (:dc_no, :dc_macam, ...)
```

**Jika TIDAK ADA**, field DC tidak akan tersimpan!

### **Masalah 3: Field DC Tidak Ada Bind Parameter**

Cek apakah ada:

```php
$stmt->bindParam(':dc_no', $dc_no);
$stmt->bindParam(':dc_macam', $dc_macam);
```

**Jika TIDAK ADA**, tambahkan!

---

## 📞 Beri Tahu Saya

Setelah jalankan debug Step 1-3, **beri tahu saya**:

1. **Apakah data terkirim?** (Output debug Step 2)
2. **Field mana yang KOSONG?**
3. **Apakah ada error?**

Dengan informasi ini, saya bisa bantu fix masalahnya dengan tepat! 🎯
