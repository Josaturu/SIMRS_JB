# ✅ VITAL SIGN FORM - MODERN REDESIGN

**Tanggal:** 15 Oktober 2025  
**File:** `views/form-kamar-pemulihan.php`  
**Status:** ✅ **COMPLETE**

---

## 🎯 **OVERVIEW**

Redesign Form Vital Sign dari tabel statis menjadi sistem modern dengan:
- ✅ Form input interaktif (kiri)
- ✅ Grafik real-time (kanan)
- ✅ Table history records (bawah)
- ✅ Dynamic add/delete records
- ✅ Data disimpan sebagai JSON array

---

## 📐 **LAYOUT STRUCTURE**

```
┌─────────────────────────────────────────────────────────────┐
│                  Monitoring Vital Sign                       │
├─────────────────────┬───────────────────────────────────────┤
│                     │                                        │
│   FORM INPUT        │        GRAFIK CHART.JS                │
│   (400px width)     │        (Flex 1fr)                      │
│                     │                                        │
│   • Jam             │   Line Chart:                          │
│   • Respirasi       │   - Respirasi (blue)                   │
│   • Neurologis      │   - Tekanan Darah/Sistol (red)        │
│   • TD (Sis/Dias)   │   - Skala Nyeri (purple)              │
│   • Skala Nyeri     │   - SPO2 (green)                      │
│   • SPO2            │                                        │
│   [+ Tambah Record] │                                        │
│                     │                                        │
├─────────────────────┴───────────────────────────────────────┤
│                  HISTORY RECORDS TABLE                       │
│  Jam | Respirasi | Neurologis | TD | Nyeri | SPO2 | Aksi   │
│  ----------------------------------------------------------  │
│  06:00 | 18 x/mnt | Normal | 120/80 | 3/10 | 98% | [Hapus] │
│  09:00 | 20 x/mnt | Normal | 125/82 | 2/10 | 99% | [Hapus] │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 **FORM FIELDS**

### **1. Jam (Dropdown)**
```php
<select id="vs_jam">
    <option value="">Pilih Jam</option>
    <option value="00:00">00:00</option>
    <option value="00:15">00:15</option>
    ...
    <option value="23:45">23:45</option>
</select>
```
- Interval: 15 menit
- Format: HH:MM (24 jam)
- Required: Yes

### **2. Respirasi (Number)**
```html
<input type="number" id="vs_respirasi" min="0" max="60" placeholder="Contoh: 18">
```
- Range: 0-60 x/menit
- Required: Yes

### **3. Neurologis (Dropdown)**
```html
<select id="vs_neurologis">
    <option value="">Select an option</option>
    <option value="Normal">Normal</option>
    <option value="Tidak Sadar">Tidak Sadar</option>
    <option value="Gelisah">Gelisah</option>
    <option value="Mengantuk">Mengantuk</option>
    <option value="Sadar Penuh">Sadar Penuh</option>
</select>
```
- Required: Yes
- Badge colors:
  - Normal: Green
  - Sadar Penuh: Blue
  - Mengantuk: Orange
  - Gelisah: Red
  - Tidak Sadar: Gray

### **4. Tekanan Darah (2 Number Inputs)**
```html
<input type="number" id="vs_sistol" min="0" max="300" placeholder="Sistol (120)">
<span>/</span>
<input type="number" id="vs_diastol" min="0" max="200" placeholder="Diastol (80)">
```
- Sistol: 0-300 mmHg
- Diastol: 0-200 mmHg
- Format display: 120/80 mmHg
- Required: Yes

### **5. Skala Nyeri (Range Slider)**
```html
<input type="range" id="vs_nyeri" min="0" max="10" value="5">
<span id="nyeri_value">5</span>/10
```
- Range: 0-10
- Default: 5
- Real-time update display
- Gradient: Green → Yellow → Red
- Badge colors:
  - 0-3: Green (Nyeri Ringan)
  - 4-6: Orange (Nyeri Sedang)
  - 7-10: Red (Nyeri Berat)

### **6. SPO2 (Number)**
```html
<input type="number" id="vs_spo2" min="0" max="100" placeholder="Contoh: 98">
```
- Range: 0-100 %
- Required: Yes

---

## 📊 **GRAFIK CHART.JS**

### **Configuration:**
```javascript
{
    type: "line",
    data: {
        labels: ["06:00", "09:00", "12:00", ...],
        datasets: [
            { label: "Respirasi", borderColor: "#3498db" },
            { label: "Tekanan Darah (Sistol)", borderColor: "#e74c3c" },
            { label: "Skala Nyeri", borderColor: "#9b59b6" },
            { label: "SPO2", borderColor: "#2ecc71" }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: "bottom" },
            tooltip: { mode: 'index', intersect: false }
        }
    }
}
```

### **Colors:**
| Dataset | Color | Hex |
|---------|-------|-----|
| Respirasi | Blue | #3498db |
| Tekanan Darah | Red | #e74c3c |
| Skala Nyeri | Purple | #9b59b6 |
| SPO2 | Green | #2ecc71 |

---

## 🎬 **INTERAKSI USER**

### **1. Tambah Record:**
1. User isi semua field
2. Klik tombol "Tambah Record"
3. Validasi:
   - Jam harus diisi
   - Semua field harus diisi
4. Data ditambahkan ke array
5. Table + Chart di-update
6. Form di-clear
7. Toast notification muncul

### **2. Hapus Record:**
1. User klik tombol "Hapus" di tabel
2. Confirmation dialog
3. Data dihapus dari array
4. Table + Chart di-update
5. Toast notification muncul

### **3. Submit Form:**
1. Data vital signs disimpan ke hidden input sebagai JSON
2. Form di-submit dengan semua data
3. AutoSave localStorage di-clear

---

## 💾 **DATA STORAGE**

### **JavaScript Array:**
```javascript
vitalSignsArray = [
    {
        id: 1697123456789,
        jam: "06:00",
        respirasi: 18,
        neurologis: "Normal",
        sistol: 120,
        diastol: 80,
        nyeri: 3,
        spo2: 98
    },
    // ...
]
```

### **Hidden Input (JSON):**
```html
<input type="hidden" name="vital_signs_data" id="vital_signs_data" 
       value='[{"id":123,"jam":"06:00","respirasi":18,...}]'>
```

### **Submit ke Backend:**
```php
$vital_signs_data = $_POST['vital_signs_data'];
$vital_array = json_decode($vital_signs_data, true);

// Process each record
foreach ($vital_array as $record) {
    $jam = $record['jam'];
    $respirasi = $record['respirasi'];
    $neurologis = $record['neurologis'];
    $sistol = $record['sistol'];
    $diastol = $record['diastol'];
    $nyeri = $record['nyeri'];
    $spo2 = $record['spo2'];
    
    // Insert to database or process
}
```

---

## 🎨 **UI COMPONENTS**

### **Form Input Style:**
- Background: #f8f9fa
- Border: 1px solid #dee2e6
- Border-radius: 8px
- Padding: 20px
- Input height: 40px (padding 10px)

### **Button Style:**
- Background: #007bff
- Hover: #0056b3
- Transform: translateY(-2px)
- Box-shadow on hover
- Icon: FontAwesome plus-circle

### **Table Badges:**
```css
/* Respirasi */
background: #e3f2fd;
color: #1976d2;
border-radius: 15px;
padding: 4px 12px;

/* Tekanan Darah */
background: #ffebee;
color: #c62828;

/* Nyeri (Dynamic) */
background: #4caf50 | #ff9800 | #f44336;
color: white;

/* SPO2 */
background: #e8f5e9;
color: #2e7d32;
```

### **Toast Notification:**
```css
position: fixed;
bottom: 20px;
right: 20px;
background: #323232;
color: white;
padding: 15px 20px;
border-radius: 5px;
box-shadow: 0 4px 12px rgba(0,0,0,0.3);
animation: slideIn 0.3s ease;
```

---

## 🔧 **FUNCTIONS JAVASCRIPT**

### **Main Functions:**
1. `initChart()` - Initialize Chart.js
2. `updateTable()` - Update table display
3. `updateChart()` - Update chart data
4. `updateHiddenInput()` - Update JSON hidden input
5. `deleteVitalRecord(id)` - Delete record by ID
6. `getNeurologisBadge(status)` - Get badge color for neurologis
7. `getNyeriBadge(value)` - Get badge color for nyeri
8. `showToast(message)` - Show toast notification

### **Event Listeners:**
- `vs_nyeri` input → Update slider value display
- `btn_add_vital` click → Add record
- `btn_add_vital` hover → Button animation
- Table delete buttons → Delete record

---

## 🧪 **TESTING CHECKLIST**

### **✅ Form Input:**
- [ ] Jam dropdown populated (24h × 4 = 96 options)
- [ ] Respirasi accepts 0-60
- [ ] Neurologis dropdown works
- [ ] Tekanan darah 2 inputs (sistol/diastol)
- [ ] Nyeri slider shows real-time value (0-10)
- [ ] SPO2 accepts 0-100
- [ ] Button hover animation works

### **✅ Add Record:**
- [ ] Validation: Jam required
- [ ] Validation: All fields required
- [ ] Record added to table
- [ ] Chart updated
- [ ] Form cleared after add
- [ ] Toast notification appears

### **✅ Table Display:**
- [ ] Jam displayed correctly
- [ ] Respirasi badge blue
- [ ] Neurologis badge colored correctly
- [ ] TD format: 120/80 mmHg
- [ ] Nyeri badge colored by value
- [ ] SPO2 badge green
- [ ] Delete button works

### **✅ Chart:**
- [ ] 4 lines displayed (Respirasi, Sistol, Nyeri, SPO2)
- [ ] Colors correct
- [ ] Labels = jam records
- [ ] Data updates when add/delete
- [ ] Tooltip shows on hover
- [ ] Legend at bottom

### **✅ Delete Record:**
- [ ] Confirmation dialog
- [ ] Record removed from table
- [ ] Chart updated
- [ ] Toast notification

### **✅ Submit:**
- [ ] Hidden input contains JSON
- [ ] JSON valid format
- [ ] All records in JSON
- [ ] Form submits successfully

---

## 📱 **RESPONSIVE DESIGN**

### **Desktop (> 1200px):**
- Form: 400px fixed width
- Chart: Flex 1fr
- Grid: 2 columns

### **Tablet (768px - 1200px):**
```css
@media (max-width: 1200px) {
    grid-template-columns: 350px 1fr;
}
```

### **Mobile (< 768px):**
```css
@media (max-width: 768px) {
    grid-template-columns: 1fr;
    /* Form full width, chart below */
}
```

---

## 🎯 **BENEFITS**

### **SEBELUM (Old Table):**
- ❌ Tabel statis 3 kolom
- ❌ Tidak bisa tambah/hapus
- ❌ Grafik update manual
- ❌ Data hard-coded

### **SESUDAH (Modern Form):**
- ✅ Form interaktif + dropdown
- ✅ Dynamic add/delete records
- ✅ Real-time chart update
- ✅ Unlimited records
- ✅ JSON data structure
- ✅ Toast notifications
- ✅ Better UX/UI
- ✅ Mobile responsive

---

## 📚 **DEPENDENCIES**

1. **Chart.js** (Already included in project)
2. **FontAwesome** (Icons)
3. **AutoSave.js** (LocalStorage)

---

## 🚀 **TESTING URL**

```
http://localhost/Module/index.php?page=kamar-pemulihan&no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00
```

**Test Steps:**
1. Scroll ke section "Monitoring Vital Sign"
2. Isi form input (kiri):
   - Jam: 06:00
   - Respirasi: 18
   - Neurologis: Normal
   - Sistol: 120, Diastol: 80
   - Nyeri: 3 (slider)
   - SPO2: 98
3. Klik "Tambah Record"
4. Lihat data muncul di table + chart
5. Tambah 2-3 record lagi
6. Test delete record
7. Submit form
8. Verifikasi data tersimpan

---

## 📝 **NEXT IMPROVEMENTS**

### **Future Features:**
- [ ] Import/Export CSV
- [ ] Print vital sign chart
- [ ] Alert jika nilai abnormal
- [ ] History comparison between records
- [ ] Auto-calculate average
- [ ] Waktu input otomatis (current time)

---

**Dokumentasi dibuat:** 15 Oktober 2025  
**Status:** ✅ **COMPLETE & READY TO TEST**  
**Author:** Cascade AI Assistant
