# Fitur: Tabel Terpisah untuk Data Database dan localStorage

**Tanggal:** 29 Oktober 2025  
**File:** `views/form-vital-sign.php`  
**Fitur:** Pemisahan tampilan data dari database, localStorage (draft), dan input baru

---

## 📋 Ringkasan Fitur

Form vital sign sekarang memiliki **3 tabel terpisah** untuk menampilkan data dari sumber yang berbeda:

1. **🟢 Tabel Database (Hijau)** - Data yang sudah tersimpan permanent
2. **🟠 Tabel Draft (Orange)** - Data dari localStorage (autosave, belum disimpan)
3. **⚪ Tabel Input Baru (Abu-abu)** - Data yang baru diinput user

---

## 🎨 Visual Design

### **1. Tabel Database (Permanent)**
- **Warna:** Hijau (`#e8f5e9`)
- **Icon:** `<i class="fas fa-database"></i>`
- **Badge:** Hijau dengan jumlah record
- **Status:** Read-only (tidak bisa diedit/dihapus)
- **Tampil:** Jika ada data di database

### **2. Tabel Draft (Autosave)**
- **Warna:** Orange (`#fff3e0`)
- **Icon:** `<i class="fas fa-edit"></i>`
- **Badge:** Orange dengan jumlah record
- **Warning:** "Data ini masih dalam bentuk draft (autosave)"
- **Aksi:** Tombol "Pindah" untuk memindahkan ke input baru
- **Tampil:** Jika ada data di localStorage

### **3. Tabel Input Baru**
- **Warna:** Abu-abu (`#f8f9fa`)
- **Icon:** `<i class="fas fa-table"></i>`
- **Aksi:** Tombol "Hapus" untuk menghapus record
- **Tampil:** Selalu (bisa kosong)

---

## 🔄 Flow Kerja

### **Scenario 1: Pertama Kali Buka (Belum Ada Data)**

```
1. User buka form
   ↓
2. Query database → Kosong
   ↓
3. Check localStorage → Kosong
   ↓
4. Tampilan:
   - ❌ Tabel Database: Hidden
   - ❌ Tabel Draft: Hidden
   - ✅ Tabel Input Baru: Kosong
   ↓
5. User input data → Tabel Input Baru terisi
   ↓
6. Auto-save ke localStorage
```

---

### **Scenario 2: Ada Data di Database**

```
1. User buka form
   ↓
2. Query database → Ada 3 records
   ↓
3. Load ke dbVitalSigns array
   ↓
4. Tampilan:
   - ✅ Tabel Database: 3 records (hijau, read-only)
   - ❌ Tabel Draft: Hidden
   - ✅ Tabel Input Baru: Kosong
   ↓
5. User bisa tambah data baru
```

---

### **Scenario 3: Ada Data Draft (localStorage)**

```
1. User buka form
   ↓
2. Query database → Kosong
   ↓
3. Check localStorage → Ada 2 records (autosave sebelumnya)
   ↓
4. Load ke draftVitalSigns array
   ↓
5. Tampilan:
   - ❌ Tabel Database: Hidden
   - ✅ Tabel Draft: 2 records (orange, ada tombol "Pindah")
   - ✅ Tabel Input Baru: Kosong
   ↓
6. User bisa:
   - Pindahkan draft ke input baru
   - Tambah data baru
   - Simpan semua (draft + baru)
```

---

### **Scenario 4: Ada Database DAN Draft**

```
1. User buka form
   ↓
2. Query database → Ada 3 records
   ↓
3. Check localStorage → Ada 2 records draft
   ↓
4. Tampilan:
   - ✅ Tabel Database: 3 records (hijau)
   - ✅ Tabel Draft: 2 records (orange)
   - ✅ Tabel Input Baru: Kosong
   ↓
5. User tambah 1 record baru
   ↓
6. Klik "Simpan Semua Data"
   ↓
7. Yang disimpan: 2 draft + 1 baru = 3 records
   ↓
8. Database: DELETE 3 lama → INSERT 3 baru
   ↓
9. Total di database: 3 records
```

---

## 📊 Struktur Data

### **3 Array Terpisah:**

```javascript
let dbVitalSigns = [];      // Data dari database (read-only)
let draftVitalSigns = [];   // Data dari localStorage (draft)
let vitalSignsArray = [];   // Data input baru
```

### **Saat Simpan:**

```javascript
// Gabungkan draft + input baru
const allData = [...draftVitalSigns, ...vitalSignsArray];

// Kirim ke server
dataToSend = {
    vital_signs: allData  // Total yang akan disimpan
};
```

---

## 🎯 Fitur Interaktif

### **1. Tombol "Pindah" (Tabel Draft)**

```javascript
function moveDraftToInput(index) {
    const record = draftVitalSigns[index];
    vitalSignsArray.push(record);        // Pindah ke input baru
    draftVitalSigns.splice(index, 1);    // Hapus dari draft
    
    updateTable();
    updateDraftTable();
    updateChart();
    updateHiddenInput();
}
```

**Fungsi:**
- Memindahkan 1 record dari draft ke input baru
- User bisa edit sebelum simpan
- Draft berkurang, input baru bertambah

---

### **2. Tombol "Hapus" (Tabel Input Baru)**

```javascript
function removeRecord(index) {
    if (confirm('Hapus record ini?')) {
        vitalSignsArray.splice(index, 1);  // Hapus dari array
        updateTable();
        updateChart();
        updateHiddenInput();
    }
}
```

**Fungsi:**
- Menghapus 1 record dari input baru
- Tidak mempengaruhi draft atau database
- Perlu konfirmasi

---

### **3. Status Counter**

```javascript
// Update record count
const totalCount = allData.length;
const draftCount = draftVitalSigns.length;
const newCount = vitalSignsArray.length;

if (draftCount > 0 && newCount > 0) {
    countText = `${totalCount} record (${draftCount} draft + ${newCount} baru)`;
} else if (draftCount > 0) {
    countText = `${draftCount} record draft`;
} else if (newCount > 0) {
    countText = `${newCount} record baru`;
}
```

**Contoh Output:**
- "5 record (3 draft + 2 baru) siap disimpan"
- "3 record draft siap disimpan"
- "2 record baru siap disimpan"

---

## 🔧 Fungsi Update

### **updateDatabaseTable()**
- Update tabel hijau (database)
- Show/hide section berdasarkan data
- Update badge count

### **updateDraftTable()**
- Update tabel orange (draft)
- Show/hide section berdasarkan data
- Update badge count
- Tambahkan tombol "Pindah"

### **updateTable()**
- Update tabel abu-abu (input baru)
- Tambahkan tombol "Hapus"
- Show empty state jika kosong

---

## 💾 Logika Save

### **Sebelum:**
```javascript
// Hanya save vitalSignsArray
vital_signs: vitalSignsArray
```

### **Sesudah:**
```javascript
// Gabungkan draft + input baru
const allData = [...draftVitalSigns, ...vitalSignsArray];
vital_signs: allData
```

### **Di Server:**
```php
// DELETE semua data lama
DELETE FROM tbl_anestesi_vital_sign WHERE ...;

// INSERT semua data baru (draft + input baru)
foreach ($vital_signs as $record) {
    INSERT INTO ...;
}
```

---

## ✅ Testing

### **Test 1: Tampilan Tabel Database**
1. Pastikan ada data di database
2. Buka form
3. **Expected:**
   - ✅ Tabel hijau muncul
   - ✅ Badge menampilkan jumlah record
   - ✅ Data tampil dengan icon check hijau

### **Test 2: Tampilan Tabel Draft**
1. Tambah beberapa record (jangan simpan)
2. Refresh page
3. **Expected:**
   - ✅ Tabel orange muncul
   - ✅ Warning message muncul
   - ✅ Tombol "Pindah" ada di setiap row

### **Test 3: Pindah Draft ke Input**
1. Klik tombol "Pindah" di tabel draft
2. **Expected:**
   - ✅ Record pindah ke tabel input baru
   - ✅ Tabel draft berkurang 1 record
   - ✅ Toast notification muncul

### **Test 4: Hapus dari Input Baru**
1. Klik tombol "Hapus" di tabel input baru
2. Confirm dialog
3. **Expected:**
   - ✅ Record terhapus
   - ✅ Chart update
   - ✅ Toast notification muncul

### **Test 5: Save Gabungan**
1. Ada 2 draft + 3 input baru
2. Klik "Simpan Semua Data"
3. **Expected:**
   - ✅ Status: "5 record (2 draft + 3 baru)"
   - ✅ Confirm: "Simpan 5 record..."
   - ✅ Semua tersimpan ke database
   - ✅ Page reload
   - ✅ Tabel database menampilkan 5 record

---

## 🎨 Color Scheme

| Tabel | Background | Border | Icon Color | Badge |
|-------|------------|--------|------------|-------|
| Database | `#e8f5e9` | `#c8e6c9` | `#4caf50` | `#4caf50` |
| Draft | `#fff3e0` | `#ffe0b2` | `#ff9800` | `#ff9800` |
| Input Baru | `#f8f9fa` | `#dee2e6` | `#007bff` | - |

---

## 📝 Catatan Penting

1. **Tabel Database:** Read-only, tidak bisa diedit atau dihapus dari UI
2. **Tabel Draft:** Bisa dipindahkan ke input baru untuk diedit
3. **Tabel Input Baru:** Bisa dihapus sebelum disimpan
4. **Auto-save:** Gabungan draft + input baru disimpan ke localStorage
5. **Save ke Database:** Gabungan draft + input baru dikirim ke server
6. **Replace Mode:** Data lama di database akan terhapus dan diganti dengan data baru

---

## 🔍 Troubleshooting

### Tabel tidak muncul
**Penyebab:** Data kosong atau section hidden  
**Solusi:** Cek console log, pastikan data ter-load

### Draft tidak muncul setelah refresh
**Penyebab:** localStorage tidak ter-save  
**Solusi:** Cek browser console untuk error

### Data draft tidak ikut tersimpan
**Penyebab:** Fungsi gabungan tidak jalan  
**Solusi:** Cek `saveAllVitalSigns()`, pastikan `allData` berisi draft + input

---

**Status:** ✅ Completed  
**Fitur:** 3 tabel terpisah (Database, Draft, Input Baru)  
**Benefit:** User bisa membedakan data permanent, draft, dan input baru
