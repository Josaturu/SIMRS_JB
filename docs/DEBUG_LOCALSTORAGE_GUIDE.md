# Guide: Debug LocalStorage Tool

**File:** `debug-localstorage.html`  
**Fungsi:** Tool untuk melihat dan mengelola data yang tersimpan di LocalStorage browser

---

## 📋 Cara Menggunakan

### **1. Buka File di Browser**

```
Cara 1: Double click file
- Buka folder: c:\FOLDER RIZKI\SIMRS_JB\
- Double click: debug-localstorage.html

Cara 2: Via URL
- Buka browser
- Ketik: file:///c:/FOLDER%20RIZKI/SIMRS_JB/debug-localstorage.html
```

---

### **2. Fitur yang Tersedia**

#### **🔄 Refresh Data**
- Memuat ulang data dari LocalStorage
- Gunakan setelah melakukan perubahan di aplikasi

#### **🗑️ Clear All LocalStorage**
- Menghapus SEMUA data di LocalStorage
- ⚠️ **PERHATIAN:** Data yang belum disimpan ke database akan hilang!

#### **📥 Export to JSON**
- Export semua data LocalStorage ke file JSON
- Berguna untuk backup atau analisis

#### **🔍 Filter**
- Cari data berdasarkan nama key
- Contoh: ketik "vital_sign" untuk filter data vital sign

#### **🗑️ Delete (per item)**
- Hapus satu item tertentu
- Klik tombol "Delete" di setiap item

---

## 📊 Informasi yang Ditampilkan

### **Statistics Cards:**

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│ Total Items     │  │ Total Size      │  │ Vital Sign      │
│      5          │  │    12.5 KB      │  │ Records: 3      │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

### **Data Display:**

```
┌────────────────────────────────────────────┐
│ 🔑 vital_sign_1_1_2025-02-01_23:59:00     │
│                                            │
│ [                                          │
│   {                                        │
│     "jam": "14:12:41",                     │
│     "respirasi": 22,                       │
│     "nadi": 27,                            │
│     "sistol": 21,                          │
│     "diastol": 34,                         │
│     "fio2": 45,                            │
│     "spo2": 85                             │
│   }                                        │
│ ]                                          │
│                                            │
│ Size: 2.5 KB    📄 JSON    [🗑️ Delete]   │
└────────────────────────────────────────────┘
```

---

## 🔍 Melihat Data Vital Sign

### **Key Format:**
```
vital_sign_{no_rawat}_{kode_paket}_{tanggal}_{jam_mulai}
```

### **Contoh:**
```javascript
Key: vital_sign_1_1_2025-02-01_23:59:00

Value:
[
  {
    "jam": "14:12:41",
    "respirasi": 22,
    "nadi": 27,
    "sistol": 21,
    "diastol": 34,
    "fio2": 45,
    "spo2": 85
  },
  {
    "jam": "14:13:10",
    "respirasi": 60,
    "nadi": 20,
    "sistol": 99,
    "diastol": 81,
    "fio2": 63,
    "spo2": 88
  }
]
```

---

## 🧪 Testing Scenarios

### **Test 1: Cek Data Vital Sign**

**Steps:**
1. Buka form vital sign di aplikasi
2. Input beberapa data
3. Buka `debug-localstorage.html`
4. Cari key yang mengandung "vital_sign"

**Expected:**
- ✅ Data vital sign tampil dalam format JSON
- ✅ Jumlah records sesuai dengan yang diinput
- ✅ Semua field (jam, respirasi, nadi, dll) ada

---

### **Test 2: Verifikasi Autosave**

**Steps:**
1. Input data di form (jangan simpan)
2. Refresh halaman form
3. Buka `debug-localstorage.html`
4. Cek apakah data tersimpan

**Expected:**
- ✅ Data ada di LocalStorage
- ✅ Data ter-restore saat form dibuka lagi

---

### **Test 3: Clear Data**

**Steps:**
1. Buka `debug-localstorage.html`
2. Klik "Clear All LocalStorage"
3. Confirm
4. Refresh page

**Expected:**
- ✅ Semua data hilang
- ✅ Total Items = 0
- ✅ Empty state tampil

---

## 📝 Common Issues

### **Issue 1: Data tidak tampil**

**Cause:** LocalStorage kosong atau berbeda domain  
**Solution:**
- Pastikan aplikasi sudah dijalankan minimal sekali
- Pastikan debug tool dibuka dari domain yang sama
- Cek apakah ada data yang diinput tapi belum disimpan

---

### **Issue 2: Data tidak ter-update**

**Cause:** Cache browser  
**Solution:**
- Klik tombol "Refresh Data"
- Atau refresh halaman (F5)

---

### **Issue 3: Export tidak berfungsi**

**Cause:** Browser block download  
**Solution:**
- Allow download di browser settings
- Atau copy-paste data manual

---

## 💡 Tips

### **1. Backup Sebelum Clear**
```
Sebelum klik "Clear All":
1. Klik "Export to JSON"
2. Simpan file backup
3. Baru clear LocalStorage
```

### **2. Filter untuk Efisiensi**
```
Jika banyak data:
1. Gunakan filter
2. Ketik keyword: "vital_sign", "draft", dll
3. Hanya data yang match yang tampil
```

### **3. Cek Size**
```
LocalStorage limit: ~5-10 MB per domain
Jika mendekati limit:
- Clear data lama yang tidak diperlukan
- Atau simpan ke database
```

---

## 🎯 Use Cases

### **Use Case 1: Debug Autosave**
```
Problem: Data tidak ter-restore setelah refresh
Debug:
1. Buka debug tool
2. Cari key vital_sign
3. Cek apakah data tersimpan
4. Cek format data (harus array of objects)
```

### **Use Case 2: Verify Data Before Save**
```
Problem: Data hilang setelah simpan
Debug:
1. Input data di form
2. Buka debug tool (jangan simpan dulu)
3. Cek data di LocalStorage
4. Baru klik simpan
5. Cek apakah data masih ada atau sudah pindah ke DB
```

### **Use Case 3: Clean Up Old Data**
```
Problem: LocalStorage penuh
Debug:
1. Buka debug tool
2. Lihat semua keys
3. Delete keys yang sudah tidak diperlukan
4. Atau clear all jika semua data sudah di-save
```

---

## 🔐 Security Notes

1. **LocalStorage tidak aman untuk data sensitif**
   - Jangan simpan password
   - Jangan simpan token authentication
   - Hanya untuk temporary data

2. **Data bisa dilihat siapa saja**
   - Siapa saja yang akses browser bisa lihat
   - Gunakan hanya untuk draft/temporary data

3. **Clear setelah selesai**
   - Clear LocalStorage setelah data disimpan
   - Hindari akumulasi data lama

---

## 📚 Reference

### **LocalStorage API:**
```javascript
// Set item
localStorage.setItem('key', 'value');

// Get item
const value = localStorage.getItem('key');

// Remove item
localStorage.removeItem('key');

// Clear all
localStorage.clear();

// Get all keys
for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    console.log(key);
}
```

### **JSON Operations:**
```javascript
// Stringify (object to string)
const json = JSON.stringify(data);

// Parse (string to object)
const data = JSON.parse(json);

// Pretty print
const pretty = JSON.stringify(data, null, 2);
```

---

## 🎨 UI Features

### **Color Coding:**
- 🔵 **Blue:** Primary actions (Refresh)
- 🔴 **Red:** Destructive actions (Delete, Clear)
- 🟢 **Green:** Export actions
- 🟣 **Purple:** Header gradient

### **Icons:**
- 🔑 Key name
- 📄 JSON data
- 📝 Text data
- 🗑️ Delete action
- 🔍 Search/Filter
- 📥 Export
- 🔄 Refresh

---

**Status:** ✅ Ready to use  
**Browser Support:** Chrome, Firefox, Edge, Safari  
**File Size:** ~10 KB
