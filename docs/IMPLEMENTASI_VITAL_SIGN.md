# Implementasi Form Vital Sign - Simpan ke Database

**Tanggal:** 29 Oktober 2025  
**File Terkait:** `views/form-vital-sign.php`, `process/process-simpan-vital-sign.php`  
**Tabel Database:** `tbl_anestesi_vital_sign`

---

## 📋 Ringkasan Implementasi

Form vital sign telah dikembangkan agar bisa **menyimpan data ke database** dengan fitur:

1. ✅ Input multiple records vital sign (Respirasi, Nadi, TD, FIO2, SPO2)
2. ✅ Real-time chart visualization
3. ✅ Auto-save ke localStorage
4. ✅ Batch save ke database via AJAX
5. ✅ Transaction support (rollback jika error)

---

## 🗂️ Struktur Database

### Tabel: `tbl_anestesi_vital_sign`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | VARCHAR(36) | UUID Primary Key |
| `no_rawat` | VARCHAR(17) | Nomor Rawat |
| `kode_paket` | VARCHAR(15) | Kode Paket |
| `tanggal` | DATE | Tanggal Operasi |
| `jam_mulai` | TIME | Jam Mulai Operasi |
| `waktu` | DATETIME | Waktu Pemeriksaan (tanggal + jam) |
| `respirasi` | INT(11) | Respirasi (x/menit) |
| `nadi` | INT(11) | Nadi (BPM) |
| `td_sistolik` | INT(11) | Tekanan Darah Sistolik (mmHg) |
| `td_diastolik` | INT(11) | Tekanan Darah Diastolik (mmHg) |
| `fio2` | INT(11) | FIO2 (%) - **KOLOM BARU** |
| `suhu` | DECIMAL(4,1) | Suhu (°C) - belum digunakan |
| `spo2` | INT(11) | SPO2 (%) |

---

## 🛠️ Langkah Implementasi

### **STEP 1: Tambahkan Kolom FIO2 ke Database**

Kolom `fio2` tidak ada di database asli, jadi perlu ditambahkan:

```bash
# File: sql/ADD_FIO2_COLUMN_VITAL_SIGN.sql
mysql -u root -p dbanestesi < sql/ADD_FIO2_COLUMN_VITAL_SIGN.sql
```

**Atau via phpMyAdmin:**
```sql
ALTER TABLE tbl_anestesi_vital_sign 
ADD COLUMN fio2 INT(11) NULL DEFAULT NULL COMMENT 'FIO2 (%)' 
AFTER td_diastolik;
```

---

### **STEP 2: Perubahan di Form View**

📄 **File:** `views/form-vital-sign.php`

#### A. Tambahkan Tombol Simpan & Status

**Sebelum:**
```html
<button type="button" class="btn btn-secondary">Kembali</button>
```

**Sesudah:**
```html
<div style="display: flex; justify-content: space-between;">
    <div id="save_status">
        <span id="record_count">0 record</span> siap disimpan
    </div>
    <div>
        <button type="button" class="btn btn-secondary">Kembali</button>
        <button type="button" id="btn_save_all" class="btn btn-success">
            Simpan Semua Data
        </button>
    </div>
</div>
```

#### B. Update Fungsi `updateHiddenInput()`

Tambahkan update record count dan enable/disable button:

```javascript
function updateHiddenInput() {
    // ... existing code ...
    
    // Update record count
    const count = vitalSignsArray.length;
    document.getElementById('record_count').textContent = count + ' record' + (count !== 1 ? 's' : '');
    
    // Enable/disable save button
    const saveBtn = document.getElementById('btn_save_all');
    if (count > 0) {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
    } else {
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';
    }
}
```

#### C. Tambahkan Fungsi `saveAllVitalSigns()`

```javascript
function saveAllVitalSigns() {
    if (vitalSignsArray.length === 0) {
        alert('⚠️ Tidak ada data untuk disimpan!');
        return;
    }
    
    const saveBtn = document.getElementById('btn_save_all');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    const dataToSend = {
        no_rawat: '<?= $no_rawat ?>',
        kode_paket: '<?= $kode_paket ?>',
        tanggal: '<?= $tanggal ?>',
        jam_mulai: '<?= $jam_mulai ?>',
        vital_signs: vitalSignsArray
    };
    
    fetch('process/process-simpan-vital-sign.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataToSend)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('✅ ' + data.message);
            clearAutosave();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Semua Data';
    });
}
```

#### D. Tambahkan Event Listener

```javascript
document.getElementById('btn_save_all').addEventListener('click', function() {
    if (confirm('💾 Simpan ' + vitalSignsArray.length + ' record vital sign ke database?')) {
        saveAllVitalSigns();
    }
});
```

---

### **STEP 3: Update Process File**

📄 **File:** `process/process-simpan-vital-sign.php`

#### Perubahan Utama:

**1. Terima Data JSON (bukan POST biasa):**
```php
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$no_rawat = $data['no_rawat'] ?? '';
$kode_paket = $data['kode_paket'] ?? '';
$tanggal = $data['tanggal'] ?? '';
$jam_mulai = $data['jam_mulai'] ?? '';
$vital_signs = $data['vital_signs'] ?? [];
```

**2. Gunakan Transaction:**
```php
$db->beginTransaction();

// Hapus data lama (replace mode)
$delete_query = "DELETE FROM tbl_anestesi_vital_sign 
                 WHERE no_rawat = ? AND kode_paket = ? 
                 AND tanggal = ? AND jam_mulai = ?";
$delete_stmt->execute([...]);

// Insert data baru
foreach ($vital_signs as $record) {
    // Insert satu per satu
}

$db->commit();
```

**3. Insert Query dengan FIO2:**
```php
$query = "INSERT INTO tbl_anestesi_vital_sign
          (id, no_rawat, kode_paket, tanggal, jam_mulai, waktu, 
           respirasi, nadi, td_sistolik, td_diastolik, fio2, spo2)
          VALUES (UUID(), :no_rawat, :kode_paket, :tanggal, :jam_mulai, :waktu, 
                  :respirasi, :nadi, :td_sistolik, :td_diastolik, :fio2, :spo2)";
```

**4. Konversi Waktu:**
```php
// Gabungkan tanggal + jam menjadi datetime
$waktu_datetime = $tanggal . ' ' . $record['jam'];
```

**5. Error Handling:**
```php
try {
    // ... insert logic ...
    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
```

---

## 🔄 Flow Kerja

### **1. User Input Data**
```
User mengisi form → Klik "Tambah Record" → Data masuk ke array JavaScript
```

### **2. Auto-save ke localStorage**
```
Setiap kali tambah record → Auto-save ke localStorage → Bisa dipulihkan jika refresh
```

### **3. Visualisasi Real-time**
```
Data di array → Update tabel + chart → User bisa lihat grafik langsung
```

### **4. Simpan ke Database**
```
User klik "Simpan Semua Data" → AJAX POST ke process file → 
Delete data lama → Insert data baru → Commit transaction → Reload page
```

---

## 📊 Format Data yang Dikirim

### Request (JSON):
```json
{
  "no_rawat": "1",
  "kode_paket": "1",
  "tanggal": "2025-10-29",
  "jam_mulai": "08:00:00",
  "vital_signs": [
    {
      "id": 1730184123456,
      "jam": "08:15:30",
      "respirasi": 18,
      "nadi": 86,
      "sistol": 120,
      "diastol": 80,
      "fio2": 98,
      "spo2": 98
    },
    {
      "id": 1730184234567,
      "jam": "08:30:45",
      "respirasi": 20,
      "nadi": 88,
      "sistol": 125,
      "diastol": 82,
      "fio2": 97,
      "spo2": 97
    }
  ]
}
```

### Response (Success):
```json
{
  "success": true,
  "message": "Berhasil menyimpan 2 dari 2 data vital sign.",
  "saved_count": 2,
  "total_count": 2
}
```

### Response (Error):
```json
{
  "success": false,
  "message": "Gagal menyimpan data vital sign: Record #1 tidak lengkap"
}
```

---

## ✅ Testing

### **Test Case 1: Tambah Single Record**
1. Buka form vital sign
2. Isi semua field (Respirasi, Nadi, TD, FIO2, SPO2)
3. Klik "Tambah Record"
4. **Expected:** 
   - Record muncul di tabel
   - Chart terupdate
   - Status: "1 record siap disimpan"
   - Button "Simpan" enabled

### **Test Case 2: Tambah Multiple Records**
1. Tambah 5 record dengan waktu berbeda
2. **Expected:**
   - Semua record muncul di tabel
   - Chart menampilkan 5 data points
   - Status: "5 records siap disimpan"

### **Test Case 3: Simpan ke Database**
1. Tambah beberapa record
2. Klik "Simpan Semua Data"
3. Confirm dialog muncul
4. **Expected:**
   - Loading indicator muncul
   - Success notification
   - Page reload
   - Data tersimpan di database

### **Test Case 4: Verifikasi Database**
```sql
SELECT * FROM tbl_anestesi_vital_sign
WHERE no_rawat = '1' 
  AND kode_paket = '1'
  AND tanggal = '2025-10-29'
  AND jam_mulai = '08:00:00'
ORDER BY waktu;
```

**Expected:** Semua record tersimpan dengan lengkap

### **Test Case 5: Auto-save Recovery**
1. Tambah beberapa record (jangan simpan)
2. Refresh page
3. **Expected:** Data dipulihkan dari localStorage

---

## 🔍 Troubleshooting

### Error: Column 'fio2' not found
**Penyebab:** Kolom FIO2 belum ditambahkan ke database  
**Solusi:** Jalankan `ADD_FIO2_COLUMN_VITAL_SIGN.sql`

### Error: Invalid JSON data
**Penyebab:** Data tidak dikirim dalam format JSON  
**Solusi:** Pastikan menggunakan `JSON.stringify()` dan header `Content-Type: application/json`

### Data tidak tersimpan
**Penyebab:** Transaction rollback karena error  
**Solusi:** Cek error message di response, pastikan semua field terisi

### Chart tidak update
**Penyebab:** Chart.js tidak ter-load  
**Solusi:** Pastikan CDN Chart.js ter-load: `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`

---

## 📝 Catatan Penting

1. **Replace Mode:** Setiap kali simpan, data lama untuk session yang sama akan dihapus dan diganti dengan data baru
2. **Transaction:** Menggunakan transaction untuk memastikan data consistency
3. **Auto-save:** Data auto-save ke localStorage setiap kali tambah record
4. **Validation:** Validasi dilakukan di client-side (JavaScript) dan server-side (PHP)
5. **UUID:** Setiap record menggunakan UUID sebagai primary key

---

## 📚 File Terkait

- **View:** `views/form-vital-sign.php`
- **Process:** `process/process-simpan-vital-sign.php`
- **SQL:** `sql/ADD_FIO2_COLUMN_VITAL_SIGN.sql`
- **Tabel:** `tbl_anestesi_vital_sign`

---

**Status:** ✅ Ready to Use  
**Estimasi Testing:** 15 menit  
**Risk Level:** Low (menggunakan transaction)
