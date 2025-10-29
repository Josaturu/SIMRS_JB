# Implementation Guide: Chart Image untuk PDF Kamar Pemulihan

**Tanggal:** 30 Oktober 2025  
**Fitur:** Menyimpan dan menampilkan grafik vital sign di PDF  
**Files Modified:** 3 files

---

## 📋 Overview

Sistem ini memungkinkan grafik vital sign yang ditampilkan di form web untuk disimpan dan ditampilkan kembali di PDF dengan tampilan yang sama persis.

### **Workflow:**
```
1. User input vital signs di form
2. Chart.js generate grafik
3. Grafik di-capture sebagai base64 image
4. Image disimpan ke database
5. PDF menampilkan grafik yang sama
```

---

## 🗄️ Database Changes

### **File: `sql/ADD_CHART_IMAGE_FIELD.sql`**

```sql
ALTER TABLE `tbl_anestesi_kamar_pemulihan` 
ADD COLUMN `chart_image` LONGTEXT DEFAULT NULL 
COMMENT 'Base64 encoded chart image';

ALTER TABLE `tbl_anestesi_kamar_pemulihan` 
ADD COLUMN `vital_sign_data` LONGTEXT DEFAULT NULL 
COMMENT 'JSON array of vital sign records';
```

### **Field Descriptions:**

| Field | Type | Purpose |
|-------|------|---------|
| `chart_image` | LONGTEXT | Menyimpan gambar grafik dalam format base64 PNG |
| `vital_sign_data` | LONGTEXT | Menyimpan data vital sign dalam format JSON array |

### **Sample Data:**

**vital_sign_data:**
```json
[
  {
    "jam": "14:12:41",
    "respirasi": 22,
    "nadi": 27,
    "sistol": 21,
    "diastol": 34,
    "nyeri": 5,
    "spo2": 85
  },
  {
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

**chart_image:**
```
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA...
```

---

## 🔧 Form Changes

### **File: `views/form-kamar-pemulihan.php`**

#### **1. Add Hidden Inputs**

```php
<form id="formKamarPemulihan" ...>
    <!-- Existing hidden inputs -->
    <input type="hidden" name="no_rawat" value="...">
    <input type="hidden" name="kode_paket" value="...">
    <input type="hidden" name="tanggal" value="...">
    <input type="hidden" name="jam_mulai" value="...">
    
    <!-- NEW: Chart data -->
    <input type="hidden" name="chart_image" id="chart_image" value="">
    <input type="hidden" name="vital_sign_data" id="vital_sign_data" value="">
</form>
```

#### **2. Update JavaScript Function**

```javascript
// Update hidden input
function updateHiddenInput() {
    // Save vital sign data as JSON
    document.getElementById('vital_sign_data').value = JSON.stringify(vitalSignsArray);
    
    // Capture chart as base64 image
    if (vitalChart && vitalSignsArray.length > 0) {
        setTimeout(() => {
            const chartImage = document.getElementById('vitalChart').toDataURL('image/png');
            document.getElementById('chart_image').value = chartImage;
            console.log('Chart image captured');
        }, 500); // Delay to ensure chart is fully rendered
    }
    
    // AutoSave to localStorage
    const storageKey = 'vital_signs_kamar_pemulihan_...';
    try {
        localStorage.setItem(storageKey, JSON.stringify(vitalSignsArray));
        console.log('Vital signs auto-saved to localStorage');
    } catch (e) {
        console.error('Failed to auto-save vital signs:', e);
    }
}
```

**Key Points:**
- `toDataURL('image/png')` converts canvas to base64 PNG
- `setTimeout(500)` ensures chart is fully rendered before capture
- Data saved both to hidden input and localStorage

---

## 📄 PDF Changes

### **File: `process/pdf/pdf-kamar-pemulihan.php`**

#### **1. Decode JSON Data**

```php
<?php 
// Decode vital sign data dari JSON
$vital_sign_json = [];
if (!empty($pemulihan['vital_sign_data'])) {
    $vital_sign_json = json_decode($pemulihan['vital_sign_data'], true);
}
?>
```

#### **2. Display Chart Image**

```php
<?php if (!empty($vital_sign_json)): ?>
    <!-- Grafik Vital Sign -->
    <?php if (!empty($pemulihan['chart_image'])): ?>
    <div style="text-align: center; margin-bottom: 15px; background: white; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6;">
        <h3 style="margin: 0 0 10px 0; color: #495057; font-size: 10pt;">Grafik Vital Sign</h3>
        <img src="<?= $pemulihan['chart_image'] ?>" style="max-width: 100%; height: auto; border-radius: 4px;" alt="Grafik Vital Sign">
    </div>
    <?php endif; ?>
    
    <!-- Tabel Vital Sign -->
    <table class="vital-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Respirasi</th>
                <th>Nadi</th>
                <th>TD Sistol</th>
                <th>TD Diastol</th>
                <th>Skala Nyeri</th>
                <th>SpO2</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vital_sign_json as $vs): ?>
            <tr>
                <td><?= displayValue($vs['jam'] ?? '') ?></td>
                <td><?= displayValue($vs['respirasi'] ?? '') ?></td>
                <td><?= displayValue($vs['nadi'] ?? '') ?></td>
                <td><?= displayValue($vs['sistol'] ?? '') ?></td>
                <td><?= displayValue($vs['diastol'] ?? '') ?></td>
                <td><?= displayValue($vs['nyeri'] ?? '') ?></td>
                <td><?= displayValue($vs['spo2'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="info-box">
        <strong>ℹ️ Info:</strong> Belum ada data vital sign yang tercatat.
    </div>
<?php endif; ?>
```

---

## 🎨 PDF Output

### **With Chart:**

```
┌────────────────────────────────────────────────────┐
│ 📊 Monitoring Vital Sign                          │
├────────────────────────────────────────────────────┤
│                                                    │
│           Grafik Vital Sign                        │
│   ┌──────────────────────────────────────┐        │
│   │                                      │        │
│   │  [Line Chart showing vital signs]    │        │
│   │  - Respirasi (blue line)             │        │
│   │  - Nadi (orange line)                │        │
│   │  - TD Sistol (red line)              │        │
│   │  - Skala Nyeri (purple line)         │        │
│   │  - SpO2 (green line)                 │        │
│   │                                      │        │
│   └──────────────────────────────────────┘        │
│                                                    │
│ ┌────────────────────────────────────────────┐   │
│ │ Waktu  │ RR │ Nadi │ Sistol │ Diastol │...│   │
│ ├────────┼────┼──────┼────────┼─────────┼───┤   │
│ │14:12:41│ 22 │  27  │   21   │   34    │...│   │
│ │14:13:10│ 60 │  20  │   99   │   81    │...│   │
│ │14:13:38│ 60 │  44  │   63   │   12    │...│   │
│ └────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────┘
```

---

## 🧪 Testing

### **Test 1: Save Chart**

**Steps:**
1. Buka form kamar pemulihan
2. Input 3-5 vital sign records
3. Lihat grafik ter-generate
4. Klik "Simpan"
5. Cek database

**Expected:**
```sql
SELECT chart_image, vital_sign_data 
FROM tbl_anestesi_kamar_pemulihan 
WHERE no_rawat = '1';

-- chart_image: data:image/png;base64,iVBORw0KG...
-- vital_sign_data: [{"jam":"14:12:41","respirasi":22,...}]
```

---

### **Test 2: Display in PDF**

**Steps:**
1. Setelah save, klik "Cetak PDF"
2. Lihat section Monitoring Vital Sign

**Expected:**
- ✅ Grafik tampil dengan warna yang sama
- ✅ Tabel data tampil lengkap
- ✅ Grafik dan tabel match

---

### **Test 3: No Vital Signs**

**Steps:**
1. Tidak input vital signs
2. Klik "Cetak PDF"

**Expected:**
- ✅ Info box: "Belum ada data vital sign yang tercatat"
- ✅ Tidak ada error
- ✅ Section lain tetap tampil

---

### **Test 4: Edit Mode**

**Steps:**
1. Buka form yang sudah ada data
2. Tambah vital sign baru
3. Simpan
4. Cetak PDF

**Expected:**
- ✅ Grafik ter-update dengan data baru
- ✅ Tabel menampilkan semua data (lama + baru)

---

## 🔍 Technical Details

### **Canvas to Base64**

```javascript
// Get canvas element
const canvas = document.getElementById('vitalChart');

// Convert to base64 PNG
const base64Image = canvas.toDataURL('image/png');

// Result format:
// data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA...
```

### **Image Size Considerations**

**Typical Sizes:**
- Chart dimension: 800x400 pixels
- Base64 PNG size: ~50-150 KB
- LONGTEXT max: 4 GB (more than enough)

**Optimization:**
```javascript
// For smaller file size, use JPEG with quality
const base64Image = canvas.toDataURL('image/jpeg', 0.8);
// Quality: 0.0 (worst) to 1.0 (best)
```

---

## 💡 Best Practices

### **1. Delay Before Capture**

```javascript
setTimeout(() => {
    const chartImage = canvas.toDataURL('image/png');
    // ...
}, 500);
```

**Why?**
- Chart.js animations take time
- Ensure chart is fully rendered
- Prevent capturing blank/partial chart

---

### **2. Error Handling**

```javascript
try {
    const chartImage = document.getElementById('vitalChart').toDataURL('image/png');
    document.getElementById('chart_image').value = chartImage;
} catch (error) {
    console.error('Failed to capture chart:', error);
    // Fallback: save without chart
}
```

---

### **3. Data Validation**

```php
// In PDF
if (!empty($pemulihan['chart_image'])) {
    // Validate base64 format
    if (strpos($pemulihan['chart_image'], 'data:image') === 0) {
        // Display image
    }
}
```

---

## 🔧 Troubleshooting

### **Issue 1: Chart tidak ter-capture**

**Symptoms:**
- `chart_image` field kosong
- PDF tidak menampilkan grafik

**Causes:**
1. Chart belum fully rendered
2. Canvas element tidak ditemukan
3. JavaScript error

**Solutions:**
```javascript
// Increase delay
setTimeout(() => {
    // ...
}, 1000); // Increase from 500ms to 1000ms

// Check if chart exists
if (vitalChart && document.getElementById('vitalChart')) {
    // Capture
}

// Check console for errors
console.log('Chart object:', vitalChart);
```

---

### **Issue 2: Image terlalu besar**

**Symptoms:**
- Form submit lambat
- Database insert error

**Solutions:**
```javascript
// Use JPEG with compression
const chartImage = canvas.toDataURL('image/jpeg', 0.7);

// Or resize canvas before capture
canvas.width = 600;  // Smaller width
canvas.height = 300; // Smaller height
```

---

### **Issue 3: Grafik blur di PDF**

**Symptoms:**
- Image tidak tajam
- Text tidak jelas

**Solutions:**
```javascript
// Increase canvas resolution
const scale = 2; // 2x resolution
canvas.width = 800 * scale;
canvas.height = 400 * scale;
ctx.scale(scale, scale);

// Then capture
const chartImage = canvas.toDataURL('image/png');
```

---

## 📊 Data Flow Diagram

```
┌─────────────────┐
│  User Input     │
│  Vital Signs    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Chart.js       │
│  Generate Chart │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Canvas.toDataURL()│
│  Capture as PNG │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Hidden Input   │
│  chart_image    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Form Submit    │
│  POST to Server │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Database       │
│  Save Base64    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  PDF Generator  │
│  Display Image  │
└─────────────────┘
```

---

## 📚 Related Files

1. **`sql/ADD_CHART_IMAGE_FIELD.sql`** - Database migration
2. **`views/form-kamar-pemulihan.php`** - Form with chart capture
3. **`process/pdf/pdf-kamar-pemulihan.php`** - PDF with chart display
4. **`process/submit-kamar-pemulihan.php`** - Save handler (needs update)

---

## ⚠️ Important Notes

1. **Run SQL Migration First:**
   ```sql
   -- Execute this before testing
   source sql/ADD_CHART_IMAGE_FIELD.sql;
   ```

2. **Update Submit Handler:**
   - Ensure `submit-kamar-pemulihan.php` saves `chart_image` and `vital_sign_data`

3. **Browser Compatibility:**
   - `toDataURL()` supported in all modern browsers
   - IE 11+ supported

4. **Security:**
   - Base64 data is safe (no executable code)
   - Validate data:image format before display

---

**Status:** ✅ Ready to implement  
**Version:** 1.0  
**Last Updated:** 30 Oktober 2025
