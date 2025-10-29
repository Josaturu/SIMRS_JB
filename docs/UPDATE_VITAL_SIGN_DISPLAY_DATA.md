# Update: Menampilkan Data Vital Sign dari Database

**Tanggal:** 29 Oktober 2025  
**File:** `views/form-vital-sign.php`  
**Fitur:** Load dan tampilkan data yang sudah tersimpan di database

---

## 📋 Perubahan yang Dilakukan

### **1. Update Query Database** ✅

**Sebelum:**
```php
$query_vital = "SELECT waktu, respirasi, nadi, td_sistolik, td_diastolik, spo2 
                FROM tbl_anestesi_vital_sign 
                WHERE ...";
```

**Sesudah:**
```php
$query_vital = "SELECT waktu, respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2 
                FROM tbl_anestesi_vital_sign 
                WHERE ...";

// Konversi ke JSON untuk JavaScript
$vital_data_json = json_encode($vital_data);
```

**Perubahan:**
- ✅ Tambah kolom `fio2` di query
- ✅ Konversi data ke JSON untuk digunakan di JavaScript

---

### **2. Fungsi Load Data dari Database** ✅

**Fungsi Baru:** `loadVitalSignsFromDatabase()`

```javascript
function loadVitalSignsFromDatabase() {
    const dbData = <?= $vital_data_json ?>;
    
    if (dbData && dbData.length > 0) {
        // Konversi format database ke format aplikasi
        vitalSignsArray = dbData.map((record, index) => {
            // Extract jam dari waktu (YYYY-MM-DD HH:MM:SS -> HH:MM:SS)
            const waktu = record.waktu;
            const jam = waktu ? waktu.split(' ')[1] : '00:00:00';
            
            return {
                id: Date.now() + index,
                jam: jam,
                respirasi: parseInt(record.respirasi) || 0,
                nadi: parseInt(record.nadi) || 0,
                sistol: parseInt(record.td_sistolik) || 0,
                diastol: parseInt(record.td_diastolik) || 0,
                fio2: parseInt(record.fio2) || 0,
                spo2: parseInt(record.spo2) || 0
            };
        });
        
        // Update UI
        updateTable();
        updateChart();
        updateHiddenInput();
        
        return true;
    }
    
    return false;
}
```

**Fitur:**
- ✅ Ambil data dari PHP variable (JSON)
- ✅ Konversi format database ke format aplikasi
- ✅ Extract jam dari datetime
- ✅ Update tabel, chart, dan hidden input
- ✅ Return true jika berhasil load data

---

### **3. Update Initialization Logic** ✅

**Sebelum:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    restoreVitalSigns(); // Hanya dari localStorage
    updateHiddenInput();
});
```

**Sesudah:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    
    // Load data: Prioritas 1 = Database, Prioritas 2 = localStorage
    const loadedFromDB = loadVitalSignsFromDatabase();
    
    if (!loadedFromDB) {
        // Jika tidak ada data di database, coba restore dari localStorage
        restoreVitalSigns();
    } else {
        // Jika ada data di database, hapus localStorage (sudah tersimpan permanent)
        clearAutosave();
    }
    
    updateHiddenInput();
});
```

**Prioritas Load Data:**
1. **Database** (permanent storage) - Prioritas tertinggi
2. **localStorage** (temporary autosave) - Fallback jika database kosong

**Logic:**
- Jika ada data di database → Load dari database, hapus localStorage
- Jika tidak ada data di database → Restore dari localStorage (autosave)

---

### **4. Indikator Visual Data Tersimpan** ✅

**Tambahan UI:**
```php
<?php if (count($vital_data) > 0): ?>
<div style="padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-database" style="font-size: 24px; color: #4caf50;"></i>
        <div>
            <strong>Data Tersimpan di Database</strong>
            <div>
                <?= count($vital_data) ?> record vital sign sudah tersimpan.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
```

**Fitur:**
- ✅ Muncul jika ada data di database
- ✅ Menampilkan jumlah record
- ✅ Visual hijau (success indicator)

---

## 🔄 Flow Kerja Lengkap

### **Scenario 1: Pertama Kali Buka Form (Belum Ada Data)**

```
1. User buka form
   ↓
2. Query database → Kosong (0 records)
   ↓
3. loadVitalSignsFromDatabase() → Return false
   ↓
4. restoreVitalSigns() → Cek localStorage
   ↓
5. Jika ada autosave → Load dari localStorage
   Jika tidak ada → Tabel kosong
   ↓
6. User bisa mulai input data baru
```

---

### **Scenario 2: Buka Form dengan Data Tersimpan**

```
1. User buka form
   ↓
2. Query database → Ada data (misal 5 records)
   ↓
3. PHP: $vital_data_json = json_encode($vital_data)
   ↓
4. JavaScript: loadVitalSignsFromDatabase()
   ↓
5. Konversi format database → format aplikasi
   ↓
6. Update tabel + chart + hidden input
   ↓
7. Clear localStorage (data sudah permanent)
   ↓
8. Tampilkan indikator hijau: "5 record tersimpan"
   ↓
9. User bisa:
   - Lihat data existing
   - Tambah record baru
   - Simpan semua (replace data lama)
```

---

### **Scenario 3: Edit/Tambah Data Existing**

```
1. User buka form → Data dimuat dari database (misal 3 records)
   ↓
2. User tambah 2 record baru
   ↓
3. Total di array: 5 records (3 lama + 2 baru)
   ↓
4. User klik "Simpan Semua Data"
   ↓
5. Process file:
   - DELETE semua data lama untuk session ini
   - INSERT 5 records baru
   - COMMIT transaction
   ↓
6. Page reload → Load 5 records dari database
```

---

## 📊 Format Data

### **Database Format:**
```json
[
  {
    "waktu": "2025-10-29 08:15:30",
    "respirasi": "18",
    "nadi": "86",
    "td_sistolik": "120",
    "td_diastolik": "80",
    "fio2": "98",
    "spo2": "98"
  }
]
```

### **Aplikasi Format:**
```json
[
  {
    "id": 1730184123456,
    "jam": "08:15:30",
    "respirasi": 18,
    "nadi": 86,
    "sistol": 120,
    "diastol": 80,
    "fio2": 98,
    "spo2": 98
  }
]
```

### **Konversi:**
- `waktu` → Extract jam saja (`split(' ')[1]`)
- `td_sistolik` → `sistol`
- `td_diastolik` → `diastol`
- String → Integer (`parseInt()`)

---

## ✅ Testing

### **Test 1: Load Data Existing**
1. Pastikan ada data di database:
   ```sql
   SELECT * FROM tbl_anestesi_vital_sign
   WHERE no_rawat = '1' AND kode_paket = '1';
   ```
2. Buka form vital sign
3. **Expected:**
   - ✅ Data muncul di tabel
   - ✅ Chart menampilkan grafik
   - ✅ Indikator hijau muncul
   - ✅ Record count sesuai jumlah data

### **Test 2: Tambah Data Baru ke Existing**
1. Buka form dengan data existing (misal 3 records)
2. Tambah 2 record baru
3. **Expected:**
   - ✅ Total 5 records di tabel
   - ✅ Chart update dengan 5 data points
   - ✅ Status: "5 records siap disimpan"

### **Test 3: Simpan Data (Replace Mode)**
1. Lanjut dari Test 2
2. Klik "Simpan Semua Data"
3. Confirm
4. **Expected:**
   - ✅ Success notification
   - ✅ Page reload
   - ✅ 5 records tersimpan di database
   - ✅ Data lama (3 records) terhapus
   - ✅ Data baru (5 records) tersimpan

### **Test 4: Verifikasi Database**
```sql
SELECT 
    waktu,
    respirasi,
    nadi,
    td_sistolik,
    td_diastolik,
    fio2,
    spo2
FROM tbl_anestesi_vital_sign
WHERE no_rawat = '1' 
  AND kode_paket = '1'
  AND tanggal = '2025-10-29'
  AND jam_mulai = '08:00:00'
ORDER BY waktu;
```

**Expected:** Semua data tersimpan dengan lengkap, termasuk kolom `fio2`

---

## 🔍 Troubleshooting

### Data tidak muncul saat buka form
**Penyebab:** Query database gagal atau kolom tidak sesuai  
**Solusi:** 
- Cek browser console untuk error
- Pastikan kolom `fio2` sudah ada di database
- Cek query di PHP: `var_dump($vital_data);`

### Chart tidak menampilkan data
**Penyebab:** Format data tidak sesuai  
**Solusi:**
- Cek console: `console.log(vitalSignsArray)`
- Pastikan konversi string ke integer berhasil
- Pastikan Chart.js ter-load

### Data lama tidak terhapus saat simpan
**Penyebab:** DELETE query tidak jalan  
**Solusi:**
- Cek transaction di process file
- Pastikan parameter WHERE sesuai
- Cek error di response JSON

---

## 📝 Catatan Penting

1. **Replace Mode:** Data lama akan terhapus dan diganti dengan data baru saat simpan
2. **localStorage vs Database:** Database adalah source of truth, localStorage hanya untuk autosave
3. **Format Waktu:** Database menyimpan datetime lengkap, aplikasi hanya tampilkan jam
4. **Kolom FIO2:** Wajib ada di database, jika tidak ada akan error

---

## 📚 File Terkait

- **View:** `views/form-vital-sign.php`
- **Process:** `process/process-simpan-vital-sign.php`
- **SQL:** `sql/ADD_FIO2_COLUMN_VITAL_SIGN.sql`
- **Tabel:** `tbl_anestesi_vital_sign`

---

**Status:** ✅ Completed  
**Fitur:** Load dan tampilkan data dari database  
**Mode:** Replace (data lama terhapus saat simpan baru)
