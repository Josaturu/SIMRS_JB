# Load Chart dari Database - Form Kamar Pemulihan

**Tanggal:** 30 Oktober 2025  
**Feature:** Menampilkan grafik vital sign dari data yang tersimpan di database  
**Status:** ✅ IMPLEMENTED

---

## 📋 Overview

Saat user membuka form kamar pemulihan dalam mode edit (data sudah ada), sistem akan:
1. Load data vital sign dari kolom `vital_sign_data` (JSON)
2. Parse JSON menjadi array
3. Populate tabel vital sign
4. Render grafik Chart.js dengan data tersebut

---

## 🔧 Implementation

### **File Modified:** `views/form-kamar-pemulihan.php`

### **1. Load Data dari Database**

```javascript
// Load vital sign data from JSON and populate chart
if (data.vital_sign_data) {
    try {
        const vitalSignData = JSON.parse(data.vital_sign_data);
        if (Array.isArray(vitalSignData) && vitalSignData.length > 0) {
            vitalSignsArray = vitalSignData;
            console.log('Loaded vital signs from database:', vitalSignsArray.length + ' records');
            
            // Update table and chart
            updateTable();
            updateChart();
            updateHiddenInput();
            
            showToast('📊 Data vital sign berhasil dimuat (' + vitalSignsArray.length + ' records)');
        }
    } catch (e) {
        console.error('Failed to parse vital_sign_data:', e);
    }
}
```

**Location:** Inside `populateExistingData()` function (lines 925-943)

---

### **2. Update Initialization Order**

**Before:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    populateExistingData();  // ❌ Chart belum initialized
    initChart();             // Chart di-init setelah populate
    restoreVitalSigns();
});
```

**After:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    setCurrentTime();
    
    // Initialize chart first
    initChart();             // ✅ Chart di-init dulu
    
    // Then populate existing data (will update chart if data exists)
    populateExistingData();  // ✅ Populate setelah chart ready
    
    // Restore vital signs from autosave (only if not editing and no data from DB)
    <?php if (!$isEdit): ?>
    if (vitalSignsArray.length === 0) {
        restoreVitalSigns();
    }
    <?php endif; ?>
});
```

**Location:** Lines 959-981

---

## 📊 Data Flow

```
┌─────────────────────────────────────────────────────┐
│ 1. USER OPENS FORM (Edit Mode)                     │
│    - URL: ?no_rawat=1&kode_paket=1&...             │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ 2. PHP LOADS DATA FROM DATABASE                     │
│    - Query: SELECT * FROM tbl_anestesi_kamar_...    │
│    - $existingData = fetch(PDO::FETCH_ASSOC)        │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ 3. JAVASCRIPT INITIALIZATION                        │
│    - initChart() → Create empty chart               │
│    - populateExistingData() → Load data             │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ 4. PARSE JSON DATA                                  │
│    - JSON.parse(data.vital_sign_data)               │
│    - vitalSignsArray = parsed data                  │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ 5. UPDATE UI                                        │
│    - updateTable() → Populate table rows            │
│    - updateChart() → Render chart with data         │
│    - updateHiddenInput() → Set hidden fields        │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ 6. DISPLAY COMPLETE                                 │
│    - ✅ Chart visible with lines                    │
│    - ✅ Table shows all records                     │
│    - ✅ User can add more data                      │
└─────────────────────────────────────────────────────┘
```

---

## 🎨 Visual Result

### **Before (Empty Chart):**
```
┌────────────────────────────────────┐
│ Grafik Vital Sign                  │
├────────────────────────────────────┤
│                                    │
│  [Empty chart - no data]           │
│                                    │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│ History Records                    │
├────────────────────────────────────┤
│ Belum ada data vital sign          │
└────────────────────────────────────┘
```

### **After (Loaded from Database):**
```
┌────────────────────────────────────┐
│ Grafik Vital Sign                  │
├────────────────────────────────────┤
│     [Chart with 5 colored lines]   │
│  120│    ╱╲                        │
│  100│   ╱  ╲╱╲                     │
│   80│  ╱      ╲                    │
│   60│ ╱        ╲                   │
│   40│╱          ╲                  │
│     └─────────────────────         │
│     14:12  14:13  14:14            │
│                                    │
│  Legend:                           │
│  ─ Respirasi  ─ Nadi  ─ TD Sistol │
│  ─ Nyeri      ─ SpO2               │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│ History Records                    │
├────────────────────────────────────┤
│ 14:12:41 | R:22 N:27 S:21 D:34... │
│ 14:13:10 | R:60 N:20 S:99 D:81... │
│ 14:13:38 | R:60 N:44 S:63 D:12... │
│ [🗑️ Delete buttons]                │
└────────────────────────────────────┘
```

---

## 🧪 Testing

### **Test 1: Load Existing Data**

**Steps:**
1. Buka form yang sudah punya data vital sign
2. URL: `?page=kamar-pemulihan&no_rawat=1&kode_paket=1&...`
3. Tunggu page load

**Expected:**
- ✅ Chart langsung tampil dengan data
- ✅ Tabel menampilkan semua records
- ✅ Toast notification: "📊 Data vital sign berhasil dimuat (X records)"
- ✅ Console log: "Loaded vital signs from database: X records"

---

### **Test 2: Edit Mode - Add More Data**

**Steps:**
1. Buka form dengan data existing
2. Chart dan tabel sudah tampil
3. Input vital sign baru
4. Klik "Tambah ke Tabel"

**Expected:**
- ✅ Data baru ditambahkan ke tabel
- ✅ Chart ter-update dengan data baru
- ✅ Total records bertambah

---

### **Test 3: New Record - No Data**

**Steps:**
1. Buka form baru (belum ada data)
2. Chart kosong

**Expected:**
- ✅ Chart tampil tapi kosong
- ✅ Tabel menampilkan "Belum ada data"
- ✅ No error di console
- ✅ User bisa input data baru

---

### **Test 4: Invalid JSON**

**Steps:**
1. Manually corrupt `vital_sign_data` di database
2. Set to: `{invalid json}`
3. Buka form

**Expected:**
- ✅ Error caught: "Failed to parse vital_sign_data"
- ✅ Chart tetap kosong (tidak crash)
- ✅ Form tetap bisa digunakan

---

## 🔍 Debugging

### **Check if Data Loaded:**

```javascript
// In browser console
console.log('Vital signs array:', vitalSignsArray);
console.log('Chart object:', vitalChart);
console.log('Chart data:', vitalChart.data);
```

### **Check Database:**

```sql
SELECT 
    no_rawat,
    kode_paket,
    LENGTH(vital_sign_data) as json_length,
    LEFT(vital_sign_data, 100) as json_preview
FROM tbl_anestesi_kamar_pemulihan
WHERE no_rawat = '1';
```

### **Check JSON Format:**

```javascript
// Test JSON parsing
try {
    const data = JSON.parse('<?= $existingData["vital_sign_data"] ?? "" ?>');
    console.log('Parsed data:', data);
} catch (e) {
    console.error('JSON parse error:', e);
}
```

---

## 💡 Key Features

### **1. Automatic Loading**
- ✅ Data loaded automatically saat page load
- ✅ No manual refresh needed
- ✅ Works in edit mode only

### **2. Error Handling**
- ✅ Try-catch untuk JSON parsing
- ✅ Check if array is valid
- ✅ Graceful fallback jika error

### **3. UI Feedback**
- ✅ Toast notification saat data loaded
- ✅ Console log untuk debugging
- ✅ Chart animation saat render

### **4. Data Integrity**
- ✅ Validate JSON format
- ✅ Check array length
- ✅ Preserve existing data

---

## 🔧 Technical Details

### **JSON Data Format:**

```json
[
  {
    "id": "uuid-1",
    "jam": "14:12:41",
    "respirasi": 22,
    "nadi": 27,
    "sistol": 21,
    "diastol": 34,
    "nyeri": 5,
    "spo2": 85
  },
  {
    "id": "uuid-2",
    "jam": "14:13:10",
    "respirasi": 60,
    "nadi": 20,
    "sistol": 99,
    "diastol": 81,
    "nyeri": 3,
    "spo2": 88
  }
]
```

### **Chart Update Process:**

```javascript
// 1. Parse JSON
const vitalSignData = JSON.parse(data.vital_sign_data);

// 2. Set to global array
vitalSignsArray = vitalSignData;

// 3. Update table (populate rows)
updateTable();

// 4. Update chart (render lines)
updateChart();
  ├─ Extract labels: vitalSignsArray.map(r => r.jam)
  ├─ Extract data: vitalSignsArray.map(r => r.respirasi)
  ├─ Update chart.data.labels
  ├─ Update chart.data.datasets[].data
  └─ Call chart.update()

// 5. Update hidden input (for re-save)
updateHiddenInput();
```

---

## ⚠️ Important Notes

### **1. Initialization Order Matters**

```javascript
// ❌ WRONG - Chart not initialized yet
populateExistingData();  // Tries to update chart
initChart();             // Chart created after

// ✅ CORRECT - Chart ready before populate
initChart();             // Chart created first
populateExistingData();  // Can update chart
```

### **2. Don't Restore from localStorage in Edit Mode**

```javascript
// Only restore from localStorage if:
// 1. Not in edit mode ($isEdit = false)
// 2. No data from database (vitalSignsArray.length === 0)

<?php if (!$isEdit): ?>
if (vitalSignsArray.length === 0) {
    restoreVitalSigns();  // Only if no DB data
}
<?php endif; ?>
```

### **3. JSON Must Be Valid**

```php
// In PHP - ensure valid JSON
$vital_sign_data = json_encode($array);

// In JavaScript - validate before parse
if (data.vital_sign_data) {
    try {
        const parsed = JSON.parse(data.vital_sign_data);
        // Use parsed data
    } catch (e) {
        console.error('Invalid JSON');
    }
}
```

---

## 📚 Related Files

1. **`views/form-kamar-pemulihan.php`** - Form with chart loading
2. **`process/submit-kamar-pemulihan.php`** - Save vital_sign_data
3. **`process/pdf/pdf-kamar-pemulihan.php`** - Display chart in PDF
4. **`sql/ADD_CHART_IMAGE_FIELD.sql`** - Database migration

---

## ✅ Completion Checklist

- ✅ **Load JSON from database** - Implemented
- ✅ **Parse JSON to array** - Implemented
- ✅ **Update table** - Implemented
- ✅ **Update chart** - Implemented
- ✅ **Error handling** - Implemented
- ✅ **Toast notification** - Implemented
- ✅ **Console logging** - Implemented
- ✅ **Initialization order** - Fixed
- ✅ **localStorage conflict** - Resolved

---

**Status:** ✅ READY TO USE  
**Version:** 1.0  
**Last Updated:** 30 Oktober 2025
