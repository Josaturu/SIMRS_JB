# Fitur: Export PDF Laporan Vital Sign

**Tanggal:** 29 Oktober 2025  
**File:** `process/pdf/pdf-vital-sign.php`, `views/form-vital-sign.php`  
**Fitur:** Export grafik dan data vital sign ke PDF dengan tampilan sama seperti halaman web

---

## 📋 Ringkasan Fitur

User dapat **export laporan vital sign ke PDF** yang berisi:

1. ✅ **Header Modern** - Logo dan judul laporan
2. ✅ **Info Pasien** - Data pasien lengkap
3. ✅ **Grafik Chart** - Screenshot grafik vital sign
4. ✅ **Tabel Data** - Semua data vital sign dalam tabel
5. ✅ **Footer** - Timestamp dan info RS

---

## 🎨 Tampilan PDF

### **1. Header**
```
┌────────────────────────────────────────────┐
│   📊 LAPORAN VITAL SIGN                    │
│   Rumah Sakit Josaturu Blora               │
└────────────────────────────────────────────┘
```
- Background: Gradient biru (`#4285f4` → `#3367d6`)
- Font: Bold, putih
- Border radius: 10px

---

### **2. Info Pasien**
```
┌────────────────────────────────────────────┐
│ No. Rekam Medis : 123456    Tanggal : ...  │
│ Nama Pasien     : John Doe  Jam Mulai: ... │
│ Jenis Kelamin   : Laki-laki No. Rawat: ... │
│ Umur            : 35 tahun  Kode Paket: ...│
└────────────────────────────────────────────┘
```
- Background: Putih
- Border: Shadow
- Layout: 2 kolom

---

### **3. Grafik Chart**
```
┌────────────────────────────────────────────┐
│ 📈 Grafik Vital Sign                       │
│                                            │
│   [GRAFIK CHART DARI CANVAS]               │
│                                            │
└────────────────────────────────────────────┘
```
- Chart diambil dari canvas sebagai base64 image
- Full width
- Border: 1px solid

---

### **4. Tabel Data**
```
┌────────────────────────────────────────────┐
│ 📋 Data Vital Sign (5 Records)             │
│                                            │
│ No │ Waktu    │ Resp │ Nadi │ TD │ FIO2 │ │
│ 1  │ 08:15:30 │  18  │  86  │... │  98  │ │
│ 2  │ 08:30:45 │  20  │  88  │... │  97  │ │
└────────────────────────────────────────────┘
```
- Header: Biru dengan text putih
- Rows: Alternating color
- Badges: Warna sesuai parameter

---

### **5. Footer**
```
────────────────────────────────────────────
Dicetak pada: 29 Oktober 2025 16:45:00 WIB
Rumah Sakit Josaturu Blora - SIMRS
```

---

## 💻 Implementasi

### **1. Tombol Export PDF (Form)**

```html
<button type="button" id="btn_export_pdf" class="btn btn-info" 
        onclick="exportToPDF()">
    <i class="fas fa-file-pdf"></i> Export PDF
</button>
```

**Posisi:** Di sebelah tombol "Simpan Semua Data"

---

### **2. Fungsi JavaScript Export**

```javascript
function exportToPDF() {
    // 1. Ambil chart sebagai base64 image
    const chartCanvas = document.getElementById('vitalChart');
    const chartImage = chartCanvas.toDataURL('image/png');
    
    // 2. Prepare data
    const pdfData = {
        no_rawat: '...',
        kode_paket: '...',
        tanggal: '...',
        jam_mulai: '...',
        chart_image: chartImage,
        db_data: dbVitalSigns,
        draft_data: draftVitalSigns,
        new_data: vitalSignsArray,
        current_mode: currentViewMode,
        pasien: { ... }
    };
    
    // 3. Submit form ke PDF generator
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'process/pdf/pdf-vital-sign.php';
    form.target = '_blank';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'pdf_data';
    input.value = JSON.stringify(pdfData);
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
```

**Flow:**
1. Convert chart canvas ke base64 PNG
2. Gabungkan semua data (database, draft, input baru)
3. Kirim via POST ke PDF generator
4. Buka di tab baru

---

### **3. PHP PDF Generator**

**File:** `process/pdf/pdf-vital-sign.php`

```php
<?php
// 1. Terima data dari POST
$pdf_data = json_decode($_POST['pdf_data'], true);

// 2. Extract data
$chart_image = $pdf_data['chart_image'];
$db_data = $pdf_data['db_data'];
$draft_data = $pdf_data['draft_data'];
$new_data = $pdf_data['new_data'];
$current_mode = $pdf_data['current_mode'];

// 3. Gabungkan data berdasarkan mode
if ($current_mode === 'all') {
    $all_data = array_merge($db_data, $draft_data, $new_data);
} elseif ($current_mode === 'database') {
    $all_data = $db_data;
} // ... dst

// 4. Sort by time
usort($all_data, function($a, $b) {
    return strcmp($a['jam'], $b['jam']);
});

// 5. Generate HTML
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        /* CSS untuk PDF */
    </style>
</head>
<body>
    <!-- Header -->
    <!-- Info Pasien -->
    <!-- Chart -->
    <img src="<?= $chart_image ?>">
    <!-- Tabel Data -->
    <!-- Footer -->
</body>
</html>

<script>
    window.onload = function() {
        window.print(); // Auto print
    };
</script>
```

---

## 🔄 Flow Kerja

### **User klik "Export PDF":**

```
1. User klik tombol "Export PDF"
   ↓
2. JavaScript: exportToPDF()
   ↓
3. Ambil chart canvas → Convert ke base64 PNG
   ↓
4. Gabungkan data:
   - dbVitalSigns (database)
   - draftVitalSigns (localStorage)
   - vitalSignsArray (input baru)
   ↓
5. Prepare JSON data dengan:
   - chart_image (base64)
   - db_data, draft_data, new_data
   - current_mode (all/database/draft/new)
   - pasien info
   ↓
6. Create form POST
   ↓
7. Submit ke process/pdf/pdf-vital-sign.php
   ↓
8. Open in new tab (_blank)
   ↓
9. PHP: Terima data, generate HTML
   ↓
10. Browser: Auto print dialog
   ↓
11. User: Save as PDF atau Print
```

---

## 📊 Data yang Di-export

### **Berdasarkan View Mode:**

| Mode | Data yang Di-export |
|------|---------------------|
| **Semua** | Database + Draft + Input Baru |
| **Database** | Hanya data dari database |
| **Draft** | Hanya data dari localStorage |
| **Input Baru** | Hanya data input baru |

**Contoh:**
- User pilih mode "Database" → Export hanya data database
- User pilih mode "Semua" → Export semua data

---

## 🎨 Styling PDF

### **Color Scheme:**

| Element | Color |
|---------|-------|
| Header Background | Gradient `#4285f4` → `#3367d6` |
| Header Text | White |
| Table Header | `#4285f4` |
| Badge Respirasi | `#e3f2fd` (background), `#1976d2` (text) |
| Badge Nadi | `#fff3e0` (background), `#e65100` (text) |
| Badge TD | `#ffebee` (background), `#c62828` (text) |
| Badge FIO2 | `#f3e5f5` (background), `#7b1fa2` (text) |
| Badge SPO2 | `#e8f5e9` (background), `#2e7d32` (text) |

### **Typography:**

```css
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.6;
}

h1 {
    font-size: 20pt;
    font-weight: 700;
}

h2 {
    font-size: 14pt;
    color: #4285f4;
}

.data-table {
    font-size: 9pt;
}
```

---

## ✅ Testing

### **Test 1: Export dengan Data Database**
1. Pastikan ada data di database
2. Pilih mode "Database"
3. Klik "Export PDF"
4. **Expected:**
   - ✅ Tab baru terbuka
   - ✅ Print dialog muncul
   - ✅ PDF berisi data database
   - ✅ Chart menampilkan grafik database

### **Test 2: Export dengan Data Draft**
1. Pastikan ada data draft
2. Pilih mode "Draft"
3. Klik "Export PDF"
4. **Expected:**
   - ✅ PDF berisi data draft
   - ✅ Chart menampilkan grafik draft

### **Test 3: Export Semua Data**
1. Pastikan ada data database, draft, dan input baru
2. Pilih mode "Semua"
3. Klik "Export PDF"
4. **Expected:**
   - ✅ PDF berisi semua data
   - ✅ Chart menampilkan gabungan semua data
   - ✅ Data terurut by time

### **Test 4: Export Tanpa Data**
1. Tidak ada data sama sekali
2. Klik "Export PDF"
3. **Expected:**
   - ✅ PDF tetap terbuka
   - ✅ Tampilan empty state
   - ✅ Pesan "Tidak ada data vital sign"

### **Test 5: Chart Image**
1. Export PDF
2. **Expected:**
   - ✅ Chart muncul di PDF
   - ✅ Resolusi bagus
   - ✅ Tidak blur

---

## 🔧 Customization

### **Ubah Page Size:**

```css
@page {
    margin: 15mm;
    size: A4 portrait; /* Ubah ke landscape jika perlu */
}
```

### **Ubah Font Size:**

```css
body {
    font-size: 10pt; /* Ubah sesuai kebutuhan */
}
```

### **Tambah Logo RS:**

```html
<div class="page-header">
    <img src="logo-rs.png" style="height: 50px;">
    <h1>LAPORAN VITAL SIGN</h1>
</div>
```

---

## 📝 Catatan Penting

1. **Chart Image:** Diambil dari canvas menggunakan `toDataURL('image/png')`
2. **Data Format:** JSON di-encode dan dikirim via POST
3. **Print Dialog:** Auto muncul saat PDF dibuka
4. **Browser Support:** Semua browser modern support `canvas.toDataURL()`
5. **File Size:** Chart image bisa besar (base64), pastikan server support

---

## 🔍 Troubleshooting

### Chart tidak muncul di PDF
**Penyebab:** Canvas belum ter-render  
**Solusi:** Pastikan chart sudah initialized sebelum export

### Data tidak lengkap
**Penyebab:** JSON tidak ter-encode dengan benar  
**Solusi:** Cek console log, pastikan data valid

### Print dialog tidak muncul
**Penyebab:** Browser block popup  
**Solusi:** Allow popup untuk domain ini

### PDF layout rusak
**Penyebab:** CSS tidak kompatibel dengan print  
**Solusi:** Test dengan `@media print` CSS

---

## 🎁 Benefit

✅ **Professional:** Laporan PDF yang rapi dan profesional  
✅ **Complete:** Berisi grafik dan tabel data  
✅ **Flexible:** Export sesuai view mode (all/database/draft/new)  
✅ **Easy:** Satu klik langsung print  
✅ **Modern:** Design modern sesuai web

---

## 🚀 Future Enhancement

- [ ] Add mPDF/TCPDF library untuk generate PDF server-side
- [ ] Add watermark "DRAFT" untuk data draft
- [ ] Add digital signature
- [ ] Add QR code untuk verifikasi
- [ ] Add multiple page support untuk data banyak
- [ ] Add export to Excel

---

**Status:** ✅ Completed  
**Method:** Browser Print to PDF  
**Library:** Native HTML + CSS (no external library)  
**Output:** PDF dengan grafik dan tabel data
