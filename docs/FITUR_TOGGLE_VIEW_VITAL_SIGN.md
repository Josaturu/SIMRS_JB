# Fitur: Toggle Switch untuk Ganti Tampilan Data

**Tanggal:** 29 Oktober 2025  
**File:** `views/form-vital-sign.php`  
**Fitur:** Toggle button untuk switch antara tampilan Database, Draft, Input Baru, atau Semua

---

## 📋 Ringkasan Fitur

User sekarang bisa **memilih data mana yang ingin ditampilkan** dengan 4 tombol toggle:

1. **🔵 Semua** - Tampilkan semua data (Database + Draft + Input Baru)
2. **🟢 Database** - Hanya data dari database (permanent)
3. **🟠 Draft** - Hanya data dari localStorage (autosave)
4. **⚪ Input Baru** - Hanya data yang baru diinput

---

## 🎨 UI Design

### **Toggle Bar**

```
┌────────────────────────────────────────────────────────┐
│ 👁️ Tampilkan Data:                                     │
│ Menampilkan semua data                                 │
│                                                         │
│ [🔵 Semua] [🟢 Database] [🟠 Draft] [⚪ Input Baru]    │
└────────────────────────────────────────────────────────┘
```

### **Button States**

| Mode | Active Color | Inactive Color | Icon |
|------|-------------|----------------|------|
| Semua | `#2196f3` (Biru) | Border biru | `fa-th-large` |
| Database | `#4caf50` (Hijau) | Border hijau | `fa-database` |
| Draft | `#ff9800` (Orange) | Border orange | `fa-edit` |
| Input Baru | `#9e9e9e` (Abu-abu) | Border abu-abu | `fa-plus-circle` |

**Active:** Background berwarna, text putih  
**Inactive:** Background putih, text & border berwarna

---

## 🔄 Behavior

### **Mode: Semua (Default)**

```
Tampilan:
✅ Tabel Database (jika ada data)
✅ Tabel Draft (jika ada data)
✅ Tabel Input Baru (selalu tampil)
✅ Chart: Gabungan semua data
```

### **Mode: Database**

```
Tampilan:
✅ Tabel Database (selalu tampil)
❌ Tabel Draft (hidden)
❌ Tabel Input Baru (hidden)
✅ Chart: Hanya data database
```

### **Mode: Draft**

```
Tampilan:
❌ Tabel Database (hidden)
✅ Tabel Draft (selalu tampil)
❌ Tabel Input Baru (hidden)
✅ Chart: Hanya data draft
```

### **Mode: Input Baru**

```
Tampilan:
❌ Tabel Database (hidden)
❌ Tabel Draft (hidden)
✅ Tabel Input Baru (selalu tampil)
✅ Chart: Hanya data input baru
```

---

## 💻 Implementasi

### **1. HTML Toggle Bar**

```html
<div style="background: white; padding: 15px; border: 1px solid #dee2e6;">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <strong><i class="fas fa-eye"></i> Tampilkan Data:</strong>
            <div id="view_mode_label">Menampilkan semua data</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <button id="btn_view_all" onclick="setViewMode('all')">
                <i class="fas fa-th-large"></i> Semua
            </button>
            <button id="btn_view_db" onclick="setViewMode('database')">
                <i class="fas fa-database"></i> Database
            </button>
            <button id="btn_view_draft" onclick="setViewMode('draft')">
                <i class="fas fa-edit"></i> Draft
            </button>
            <button id="btn_view_new" onclick="setViewMode('new')">
                <i class="fas fa-plus-circle"></i> Input Baru
            </button>
        </div>
    </div>
</div>
```

---

### **2. JavaScript State Management**

```javascript
let currentViewMode = 'all'; // all, database, draft, new

function setViewMode(mode) {
    currentViewMode = mode;
    
    // Update button states (active/inactive)
    updateButtonStates(mode);
    
    // Update label
    updateLabel(mode);
    
    // Update visibility tabel
    updateViewVisibility();
    
    // Update chart
    updateChartByMode();
    
    // Toast notification
    showToast(messages[mode]);
}
```

---

### **3. Update Button States**

```javascript
function updateButtonStates(mode) {
    const buttons = {
        'all': document.getElementById('btn_view_all'),
        'database': document.getElementById('btn_view_db'),
        'draft': document.getElementById('btn_view_draft'),
        'new': document.getElementById('btn_view_new')
    };
    
    // Reset all to inactive
    Object.values(buttons).forEach(btn => {
        btn.style.background = 'white';
        btn.style.color = borderColor;
    });
    
    // Set active button
    const activeBtn = buttons[mode];
    activeBtn.style.background = activeColor;
    activeBtn.style.color = 'white';
}
```

---

### **4. Update Visibility**

```javascript
function updateViewVisibility() {
    const dbSection = document.getElementById('db_data_section');
    const draftSection = document.getElementById('draft_data_section');
    const newSection = document.querySelector('#vital_table').closest('div');
    
    if (currentViewMode === 'all') {
        dbSection.style.display = dbVitalSigns.length > 0 ? 'block' : 'none';
        draftSection.style.display = draftVitalSigns.length > 0 ? 'block' : 'none';
        newSection.style.display = 'block';
    } else if (currentViewMode === 'database') {
        dbSection.style.display = 'block';
        draftSection.style.display = 'none';
        newSection.style.display = 'none';
    } else if (currentViewMode === 'draft') {
        dbSection.style.display = 'none';
        draftSection.style.display = 'block';
        newSection.style.display = 'none';
    } else if (currentViewMode === 'new') {
        dbSection.style.display = 'none';
        draftSection.style.display = 'none';
        newSection.style.display = 'block';
    }
}
```

---

### **5. Update Chart by Mode**

```javascript
function updateChartByMode() {
    let dataToShow = [];
    
    if (currentViewMode === 'all') {
        dataToShow = [...dbVitalSigns, ...draftVitalSigns, ...vitalSignsArray];
    } else if (currentViewMode === 'database') {
        dataToShow = dbVitalSigns;
    } else if (currentViewMode === 'draft') {
        dataToShow = draftVitalSigns;
    } else if (currentViewMode === 'new') {
        dataToShow = vitalSignsArray;
    }
    
    // Update chart dengan dataToShow
    vitalChart.data.labels = dataToShow.map(r => r.jam);
    vitalChart.data.datasets[0].data = dataToShow.map(r => r.respirasi);
    // ... dst
    vitalChart.update();
}
```

---

## 🎯 Use Cases

### **Use Case 1: Lihat Hanya Data Database**

```
User: "Saya ingin lihat data yang sudah tersimpan saja"
Action: Klik tombol "Database"
Result:
- Tabel database tampil
- Tabel draft & input baru hidden
- Chart menampilkan grafik data database
- Label: "Menampilkan data dari database (permanent)"
```

---

### **Use Case 2: Lihat Hanya Data Draft**

```
User: "Saya ingin cek data draft yang belum disimpan"
Action: Klik tombol "Draft"
Result:
- Tabel draft tampil
- Tabel database & input baru hidden
- Chart menampilkan grafik data draft
- Label: "Menampilkan data draft (autosave)"
```

---

### **Use Case 3: Fokus Input Baru**

```
User: "Saya ingin fokus input data baru tanpa distraksi"
Action: Klik tombol "Input Baru"
Result:
- Tabel input baru tampil
- Tabel database & draft hidden
- Chart menampilkan grafik input baru
- Label: "Menampilkan data input baru"
```

---

### **Use Case 4: Lihat Semua Data**

```
User: "Saya ingin lihat overview semua data"
Action: Klik tombol "Semua"
Result:
- Semua tabel yang ada data tampil
- Chart menampilkan gabungan semua data
- Label: "Menampilkan semua data"
```

---

## ✅ Testing

### **Test 1: Switch Mode**
1. Buka form dengan data database, draft, dan input baru
2. Klik "Database"
3. **Expected:**
   - ✅ Hanya tabel database tampil
   - ✅ Button "Database" active (hijau)
   - ✅ Chart update dengan data database
   - ✅ Toast: "💾 Menampilkan data database"

### **Test 2: Switch ke Draft**
1. Klik "Draft"
2. **Expected:**
   - ✅ Hanya tabel draft tampil
   - ✅ Button "Draft" active (orange)
   - ✅ Chart update dengan data draft
   - ✅ Toast: "📝 Menampilkan data draft"

### **Test 3: Switch ke Input Baru**
1. Klik "Input Baru"
2. **Expected:**
   - ✅ Hanya tabel input baru tampil
   - ✅ Button "Input Baru" active (abu-abu)
   - ✅ Chart update dengan data input baru
   - ✅ Toast: "➕ Menampilkan input baru"

### **Test 4: Kembali ke Semua**
1. Klik "Semua"
2. **Expected:**
   - ✅ Semua tabel tampil
   - ✅ Button "Semua" active (biru)
   - ✅ Chart update dengan gabungan semua data
   - ✅ Toast: "👁️ Menampilkan semua data"

### **Test 5: Chart Update**
1. Switch antara mode
2. **Expected:**
   - ✅ Chart selalu update sesuai mode
   - ✅ Jumlah data points sesuai mode
   - ✅ Tidak ada error di console

---

## 🎨 Visual States

### **Button Active:**
```css
background: #2196f3; /* atau warna lain sesuai mode */
color: white;
border: 2px solid #2196f3;
```

### **Button Inactive:**
```css
background: white;
color: #2196f3; /* atau warna lain sesuai mode */
border: 2px solid #2196f3;
```

### **Button Hover:**
```css
transform: translateY(-2px);
box-shadow: 0 4px 8px rgba(0,0,0,0.15);
```

---

## 📊 Data Flow

```
User klik toggle button
    ↓
setViewMode(mode)
    ↓
Update button states (active/inactive)
    ↓
Update label text
    ↓
updateViewVisibility()
    ↓
Show/hide tabel sesuai mode
    ↓
updateChartByMode()
    ↓
Filter data sesuai mode
    ↓
Update chart dengan data filtered
    ↓
showToast(message)
```

---

## 📝 Catatan Penting

1. **Default Mode:** "Semua" (tampilkan semua data)
2. **Chart Update:** Chart selalu update sesuai mode yang dipilih
3. **Empty State:** Jika tidak ada data di mode tertentu, tampilkan pesan "Tidak ada data"
4. **Persistent:** Mode tidak tersimpan, reset ke "Semua" saat reload
5. **Responsive:** Button tetap responsif di berbagai ukuran layar

---

## 🔍 Troubleshooting

### Toggle tidak berfungsi
**Penyebab:** Function `setViewMode` tidak terdefinisi  
**Solusi:** Pastikan JavaScript ter-load dengan benar

### Chart tidak update
**Penyebab:** `updateChartByMode` tidak dipanggil  
**Solusi:** Cek console untuk error, pastikan chart initialized

### Tabel tidak hide/show
**Penyebab:** Selector element salah  
**Solusi:** Cek `getElementById` dan `querySelector`

---

## 🎁 Benefit

✅ **Fokus:** User bisa fokus pada data tertentu  
✅ **Clarity:** Tidak overwhelmed dengan terlalu banyak data  
✅ **Flexibility:** Bisa switch kapan saja  
✅ **Visual:** Chart update sesuai mode  
✅ **UX:** Smooth transition dengan toast notification

---

**Status:** ✅ Completed  
**Fitur:** Toggle switch untuk ganti tampilan data  
**Modes:** 4 mode (Semua, Database, Draft, Input Baru)
