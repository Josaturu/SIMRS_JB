# Bugfix: Column 'waktu' Not Found di PDF Kamar Pemulihan

**Tanggal:** 30 Oktober 2025  
**File:** `process/pdf/pdf-kamar-pemulihan.php`  
**Error:** SQLSTATE[42S22]: Column not found: 1054 Unknown column 'waktu' in 'order clause'

---

## 🐛 Problem

### **Error Message:**
```
Fatal error: Uncaught PDOException: SQLSTATE[42S22]: 
Column not found: 1054 Unknown column 'waktu' in 'order clause' 
in C:\FOLDER RIZKI\SIMRS_JB\process\pdf\pdf-kamar-pemulihan.php:59
```

### **Root Cause:**
Query menggunakan kolom `waktu` yang tidak ada di tabel `tbl_anestesi_vital_pemulihan`. 

Tabel ini menggunakan `waktu_label` (varchar) bukan `waktu` (datetime).

---

## 📊 Database Structure

### **Tabel: `tbl_anestesi_vital_pemulihan`**

```sql
CREATE TABLE `tbl_anestesi_vital_pemulihan` (
  `id` varchar(36) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `id_pemulihan` varchar(36) DEFAULT NULL,
  `waktu_label` varchar(50) DEFAULT NULL,    -- ✅ Bukan 'waktu'
  `nadi` int(11) DEFAULT NULL,
  `sistol` int(11) DEFAULT NULL,              -- ✅ Bukan 'td_sistolik'
  `diastol` int(11) DEFAULT NULL,             -- ✅ Bukan 'td_diastolik'
  `respirasi` int(11) DEFAULT NULL,
  `nyeri` int(11) DEFAULT NULL                -- ✅ Bukan 'skala_nyeri'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Catatan:**
- ❌ Tidak ada kolom: `waktu`, `td_sistolik`, `td_diastolik`, `skala_nyeri`, `spo2`, `suhu`
- ✅ Yang ada: `waktu_label`, `sistol`, `diastol`, `nyeri`

---

## ✅ Solution

### **1. Fix Query ORDER BY**

**Before:**
```php
$query_vital = "SELECT * FROM tbl_anestesi_vital_pemulihan 
                WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?
                ORDER BY waktu ASC";  // ❌ Column 'waktu' not found
```

**After:**
```php
$query_vital = "SELECT * FROM tbl_anestesi_vital_pemulihan 
                WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?
                ORDER BY waktu_label ASC";  // ✅ Correct column name
```

---

### **2. Fix Table Display**

**Before:**
```php
<thead>
    <tr>
        <th>Waktu</th>
        <th>Respirasi</th>
        <th>Nadi</th>
        <th>TD Sistol</th>
        <th>TD Diastol</th>
        <th>Skala Nyeri</th>
        <th>SpO2</th>      <!-- ❌ Column not exist -->
        <th>Suhu</th>      <!-- ❌ Column not exist -->
    </tr>
</thead>
<tbody>
    <?php foreach ($vital_signs as $vs): ?>
    <tr>
        <td><?= date('H:i:s', strtotime($vs['waktu'])) ?></td>  <!-- ❌ -->
        <td><?= $vs['respirasi'] ?></td>
        <td><?= $vs['nadi'] ?></td>
        <td><?= $vs['td_sistolik'] ?></td>  <!-- ❌ -->
        <td><?= $vs['td_diastolik'] ?></td> <!-- ❌ -->
        <td><?= $vs['skala_nyeri'] ?></td>  <!-- ❌ -->
        <td><?= $vs['spo2'] ?></td>         <!-- ❌ -->
        <td><?= $vs['suhu'] ?></td>         <!-- ❌ -->
    </tr>
    <?php endforeach; ?>
</tbody>
```

**After:**
```php
<thead>
    <tr>
        <th style="width: 20%;">Waktu</th>
        <th style="width: 16%;">Respirasi<br>(x/menit)</th>
        <th style="width: 16%;">Nadi<br>(BPM)</th>
        <th style="width: 16%;">TD Sistol<br>(mmHg)</th>
        <th style="width: 16%;">TD Diastol<br>(mmHg)</th>
        <th style="width: 16%;">Skala Nyeri<br>(0-10)</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($vital_signs as $vs): ?>
    <tr>
        <td><?= displayValue($vs['waktu_label']) ?></td>  <!-- ✅ -->
        <td><?= displayValue($vs['respirasi']) ?></td>
        <td><?= displayValue($vs['nadi']) ?></td>
        <td><?= displayValue($vs['sistol']) ?></td>       <!-- ✅ -->
        <td><?= displayValue($vs['diastol']) ?></td>      <!-- ✅ -->
        <td><?= displayValue($vs['nyeri']) ?></td>        <!-- ✅ -->
    </tr>
    <?php endforeach; ?>
</tbody>
```

---

## 🔄 Field Mapping

### **Database → PDF Display**

| Database Field | PDF Column | Type | Notes |
|----------------|------------|------|-------|
| `waktu_label` | Waktu | varchar(50) | Label seperti "5 menit", "10 menit" |
| `respirasi` | Respirasi (x/menit) | int(11) | Frekuensi napas |
| `nadi` | Nadi (BPM) | int(11) | Denyut nadi |
| `sistol` | TD Sistol (mmHg) | int(11) | Tekanan darah sistolik |
| `diastol` | TD Diastol (mmHg) | int(11) | Tekanan darah diastolik |
| `nyeri` | Skala Nyeri (0-10) | int(11) | Skala nyeri pasien |

### **Columns Removed:**
- ❌ `spo2` - Tidak ada di tabel
- ❌ `suhu` - Tidak ada di tabel

---

## 📝 Sample Data

### **Database:**
```sql
INSERT INTO tbl_anestesi_vital_pemulihan VALUES
('id1', '1', '1', '2025-02-01', '23:59:00', 'pemulihan_id', 
 '5 menit', 80, 120, 80, 18, 3);
```

### **PDF Output:**
```
┌────────────────────────────────────────────────────┐
│ Waktu    │ RR │ Nadi │ Sistol │ Diastol │ Nyeri  │
├──────────┼────┼──────┼────────┼─────────┼────────┤
│ 5 menit  │ 18 │  80  │  120   │   80    │   3    │
└────────────────────────────────────────────────────┘
```

---

## 🧪 Testing

### **Test 1: With Vital Signs**
```
1. Input vital signs di form kamar pemulihan
2. Simpan data
3. Klik "Cetak PDF"
4. Expected:
   ✅ PDF terbuka tanpa error
   ✅ Tabel vital sign tampil
   ✅ Data sesuai dengan input
```

### **Test 2: Without Vital Signs**
```
1. Tidak input vital signs
2. Klik "Cetak PDF"
3. Expected:
   ✅ PDF terbuka
   ✅ Info box: "Belum ada data vital sign yang tercatat"
   ✅ Section lain tetap tampil
```

### **Test 3: Multiple Records**
```
1. Input 5 vital sign records
2. Klik "Cetak PDF"
3. Expected:
   ✅ Semua 5 records tampil di tabel
   ✅ Urutan sesuai waktu_label
   ✅ Format rapi
```

---

## 🔍 Comparison

### **Tabel `tbl_anestesi_vital_sign` (Intra-Operasi)**
```sql
CREATE TABLE `tbl_anestesi_vital_sign` (
  `waktu` datetime DEFAULT NULL,        -- ✅ Ada waktu (datetime)
  `respirasi` int(11),
  `nadi` int(11),
  `td_sistolik` int(11),                -- ✅ Nama berbeda
  `td_diastolik` int(11),               -- ✅ Nama berbeda
  `fio2` int(11),
  `suhu` decimal(4,1),                  -- ✅ Ada suhu
  `spo2` int(11)                        -- ✅ Ada spo2
);
```

### **Tabel `tbl_anestesi_vital_pemulihan` (Post-Operasi)**
```sql
CREATE TABLE `tbl_anestesi_vital_pemulihan` (
  `waktu_label` varchar(50),            -- ❌ Bukan datetime
  `respirasi` int(11),
  `nadi` int(11),
  `sistol` int(11),                     -- ❌ Nama berbeda
  `diastol` int(11),                    -- ❌ Nama berbeda
  `nyeri` int(11)                       -- ❌ Ada nyeri, tidak ada fio2/suhu/spo2
);
```

**Kesimpulan:**
- Tabel vital sign **intra-operasi** dan **post-operasi** memiliki struktur berbeda
- Jangan asumsikan field name sama antar tabel

---

## 💡 Lessons Learned

### **1. Always Check Database Schema**
```
Sebelum query:
1. Cek struktur tabel di database
2. Verifikasi nama kolom
3. Cek tipe data
```

### **2. Don't Assume Field Names**
```
Jangan asumsikan:
- tbl_anestesi_vital_sign = tbl_anestesi_vital_pemulihan
- Meskipun fungsi mirip, struktur bisa berbeda
```

### **3. Use Proper Error Handling**
```php
try {
    $stmt_vital->execute([$no_rawat, $kode_paket, $tanggal, $jam_mulai]);
    $vital_signs = $stmt_vital->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching vital signs: " . $e->getMessage());
    $vital_signs = [];
}
```

---

## 📋 Files Modified

1. **`process/pdf/pdf-kamar-pemulihan.php`**
   - Line 57: `ORDER BY waktu ASC` → `ORDER BY waktu_label ASC`
   - Line 330-336: Update table headers (remove SpO2, Suhu)
   - Line 341-346: Update field names (waktu_label, sistol, diastol, nyeri)

2. **`docs/PDF_KAMAR_PEMULIHAN_GUIDE.md`**
   - Update database structure documentation
   - Update field mapping table

---

## ✅ Verification

### **Query Test:**
```sql
-- Test query
SELECT * FROM tbl_anestesi_vital_pemulihan 
WHERE no_rawat = '1' 
  AND kode_paket = '1' 
  AND tanggal = '2025-02-01' 
  AND jam_mulai = '23:59:00'
ORDER BY waktu_label ASC;

-- Expected: No error, returns data
```

### **PDF Test:**
```
URL: /process/pdf/pdf-kamar-pemulihan.php?
     no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00

Expected:
✅ PDF opens successfully
✅ Vital sign table displays
✅ No SQL errors
```

---

**Status:** ✅ Fixed  
**Impact:** PDF sekarang bisa dibuka tanpa error  
**Tested:** ✅ Passed
