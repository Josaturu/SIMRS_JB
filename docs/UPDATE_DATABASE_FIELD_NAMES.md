# Update: Penyesuaian Field Names dengan Database Aktual

**Tanggal:** 29 Oktober 2025  
**File:** `views/form-vital-sign.php`  
**Database:** `dbanestesi (6).sql`  
**Issue:** Field names tidak sesuai dengan struktur database aktual

---

## 📊 Struktur Database Aktual

### **Tabel: `pasien`**

```sql
CREATE TABLE `pasien` (
  `kd_pasien` varchar(20) NOT NULL,
  `kode_rekam_medis` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,        -- ✅ Bukan 'jk'
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,             -- ✅ Bukan 'tgl_lahir'
  `no_hp` varchar(15) DEFAULT NULL,
  `gol_darah` enum('A','B','AB','O') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### **Tabel: `tbl_anestesi_vital_sign`**

```sql
CREATE TABLE `tbl_anestesi_vital_sign` (
  `id` varchar(36) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `waktu` datetime DEFAULT NULL,
  `respirasi` int(11) DEFAULT NULL,
  `nadi` int(11) DEFAULT NULL,
  `td_sistolik` int(11) DEFAULT NULL,
  `td_diastolik` int(11) DEFAULT NULL,
  `fio2` int(11) DEFAULT NULL COMMENT 'FIO2 (%)',
  `suhu` decimal(4,1) DEFAULT NULL,
  `spo2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 🔧 Perubahan yang Dilakukan

### **1. Query Pasien (form-vital-sign.php)**

**Before:**
```php
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.jk, p.tgl_lahir,
                  TIMESTAMPDIFF(YEAR, p.tgl_lahir, CURDATE()) AS umur
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE ...";
```

**After:**
```php
$query_pasien = "SELECT bo.*, p.nama AS nama_pasien, p.kode_rekam_medis, p.jenis_kelamin, p.tanggal_lahir,
                  TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur
                  FROM booking_operasi bo
                  LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
                  WHERE ...";
```

**Changes:**
- ✅ `p.jk` → `p.jenis_kelamin`
- ✅ `p.tgl_lahir` → `p.tanggal_lahir`
- ✅ `TIMESTAMPDIFF(YEAR, p.tgl_lahir, ...)` → `TIMESTAMPDIFF(YEAR, p.tanggal_lahir, ...)`

---

### **2. JavaScript Data Pasien (form-vital-sign.php)**

**Before:**
```javascript
pasien: {
    nama: '<?= htmlspecialchars($pasien['nama_pasien'] ?? '') ?>',
    no_rkm_medis: '<?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '') ?>',
    jk: '<?= htmlspecialchars($pasien['jk'] ?? '') ?>',
    umur: '<?= htmlspecialchars($pasien['umur'] ?? '') ?>',
    tgl_lahir: '<?= htmlspecialchars($pasien['tgl_lahir'] ?? '') ?>'
}
```

**After:**
```javascript
pasien: {
    nama: '<?= htmlspecialchars($pasien['nama_pasien'] ?? '') ?>',
    no_rkm_medis: '<?= htmlspecialchars($pasien['kode_rekam_medis'] ?? '') ?>',
    jk: '<?= htmlspecialchars($pasien['jenis_kelamin'] ?? '') ?>',
    umur: '<?= htmlspecialchars($pasien['umur'] ?? '') ?>',
    tgl_lahir: '<?= htmlspecialchars($pasien['tanggal_lahir'] ?? '') ?>'
}
```

**Changes:**
- ✅ `$pasien['jk']` → `$pasien['jenis_kelamin']`
- ✅ `$pasien['tgl_lahir']` → `$pasien['tanggal_lahir']`

---

## 📋 Field Mapping Lengkap

### **Database → PHP Query → JavaScript → PDF**

| Database Field | Query Alias | JavaScript Key | PDF Display |
|----------------|-------------|----------------|-------------|
| `p.nama` | `nama_pasien` | `nama` | Nama Pasien |
| `p.kode_rekam_medis` | `kode_rekam_medis` | `no_rkm_medis` | No. Rekam Medis |
| `p.jenis_kelamin` | `jenis_kelamin` | `jk` | Jenis Kelamin |
| `p.tanggal_lahir` | `tanggal_lahir` | `tgl_lahir` | - |
| `TIMESTAMPDIFF(...)` | `umur` | `umur` | Umur (tahun) |

---

## ✅ Validasi Database

### **Sample Data Pasien:**

```sql
INSERT INTO `pasien` VALUES
('1', '1', 'dadang beton', 'morokosono', 'L', 'moskow', '1995-10-26', '081234567890', 'O', ...);
```

**Expected Output:**
- No. Rekam Medis: `1`
- Nama: `dadang beton`
- Jenis Kelamin: `L` → "Laki-laki"
- Tanggal Lahir: `1995-10-26`
- Umur: `30 tahun` (2025 - 1995)

---

## 🧪 Testing Query

### **Test Query:**

```sql
SELECT 
    bo.*, 
    p.nama AS nama_pasien, 
    p.kode_rekam_medis, 
    p.jenis_kelamin, 
    p.tanggal_lahir,
    TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur
FROM booking_operasi bo
LEFT JOIN pasien p ON bo.kd_pasien = p.kd_pasien
WHERE bo.no_rawat = '1' 
  AND bo.kode_paket = '1' 
  AND bo.tanggal = '2025-02-01' 
  AND bo.jam_mulai = '23:59:00';
```

**Expected Result:**
```
nama_pasien: dadang beton
kode_rekam_medis: 1
jenis_kelamin: L
tanggal_lahir: 1995-10-26
umur: 30
```

---

## 📝 Catatan Penting

### **Perbedaan Field Names:**

| Versi Lama | Database Aktual |
|------------|-----------------|
| `jk` | `jenis_kelamin` |
| `tgl_lahir` | `tanggal_lahir` |
| `nm_pasien` | `nama` (alias `nama_pasien`) |
| `no_rkm_medis` | `kode_rekam_medis` |

### **Enum Values:**

**Jenis Kelamin:**
- `'L'` = Laki-laki
- `'P'` = Perempuan

**Golongan Darah:**
- `'A'`, `'B'`, `'AB'`, `'O'`

---

## 🔍 Troubleshooting

### Issue: Data masih kosong
**Cause:** Field name masih salah  
**Solution:** Pastikan menggunakan `jenis_kelamin` dan `tanggal_lahir`

### Issue: Umur tidak akurat
**Cause:** `tanggal_lahir` NULL  
**Solution:** Pastikan data pasien memiliki tanggal lahir

### Issue: JOIN gagal
**Cause:** `kd_pasien` tidak match  
**Solution:** Cek foreign key di tabel `booking_operasi`

---

## ✅ Files Modified

1. **`views/form-vital-sign.php`**
   - Line 17: `p.jk` → `p.jenis_kelamin`
   - Line 17: `p.tgl_lahir` → `p.tanggal_lahir`
   - Line 18: `p.tgl_lahir` → `p.tanggal_lahir` (di TIMESTAMPDIFF)
   - Line 964: `$pasien['jk']` → `$pasien['jenis_kelamin']`
   - Line 966: `$pasien['tgl_lahir']` → `$pasien['tanggal_lahir']`

---

## 🎯 Hasil Akhir

### **Query akan menghasilkan:**
```php
Array (
    [nama_pasien] => dadang beton
    [kode_rekam_medis] => 1
    [jenis_kelamin] => L
    [tanggal_lahir] => 1995-10-26
    [umur] => 30
)
```

### **PDF akan menampilkan:**
```
No. Rekam Medis : 1
Nama Pasien     : dadang beton
Jenis Kelamin   : Laki-laki
Umur            : 30 tahun
```

---

**Status:** ✅ Updated  
**Database:** Sesuai dengan `dbanestesi (6).sql`  
**Tested:** ✅ Field names valid
