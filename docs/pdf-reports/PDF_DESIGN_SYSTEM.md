# 🎨 PDF DESIGN SYSTEM - RS Josaturu

## 📋 **DESIGN MIRIP FORM WEB**

Design PDF ini dibuat **konsisten dengan form web** menggunakan design system yang sama dengan `assets/css/style.css`

---

## 🎨 **COLOR PALETTE**

### **Primary Colors**
```css
Primary Blue:    #4285f4  /* Google Blue - Main brand color */
Dark Blue:       #3367d6  /* Gradient end, hover states */
Light Blue:      #5a9fff  /* Lighter variant */
```

### **Secondary Colors**
```css
Success Green:   #34a853  /* Form completed, success state */
Warning Yellow:  #fbbc04  /* Warning, attention needed */
Danger Red:      #ea4335  /* Error, critical state */
```

### **Neutral Colors**
```css
Background:      #f4f7fb  /* Page background */
White:           #ffffff  /* Card background */
Light Gray:      #f4f7fb  /* Table row alternate */
Border Gray:     #e3f0ff  /* Borders, dividers */
Text Dark:       #333333  /* Primary text */
Text Gray:       #666666  /* Secondary text */
```

---

## 📐 **LAYOUT SYSTEM**

### **Page Setup**
```css
@page {
    margin: 15mm;
    size: A4 portrait;  /* or landscape */
}
```

### **Grid System**
```css
/* 2 Columns */
.data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

/* 3 Columns */
.data-grid.three-cols {
    grid-template-columns: repeat(3, 1fr);
}
```

---

## 🖋️ **TYPOGRAPHY**

### **Font Family**
```css
font-family: 'Segoe UI', Arial, sans-serif;
```

### **Font Sizes**
```css
Page Title:      18pt
Section Header:  11pt
Subsection:      10pt
Body Text:       10pt
Small Text:      9pt
Footer:          8pt
```

### **Font Weights**
```css
Regular:         400
Medium:          500
Semibold:        600
Bold:            700
```

---

## 📦 **COMPONENTS**

### **1. Page Header (Gradient)**
```html
<div class="page-header">
    <div class="logo-section">
        <div class="logo">RS</div>
        <div>
            <h1>LAPORAN MEDIS</h1>
            <div class="hospital-name">Rumah Sakit Josaturu</div>
        </div>
    </div>
    <div class="document-code">RMOK 3A</div>
</div>
```

**Features:**
- ✅ Gradient background (Blue to Dark Blue)
- ✅ Logo circular badge
- ✅ Document code badge
- ✅ Box shadow untuk depth

---

### **2. Patient Info Box (Dashed Border)**
```html
<div class="patient-info-box">
    <div class="title">📋 Informasi Pasien</div>
    <div class="patient-info-grid">
        <div class="patient-info-item">
            <div class="label">No. Rawat:</div>
            <div class="value">2024/001/001</div>
        </div>
    </div>
</div>
```

**Features:**
- ✅ Dashed blue border (mirip sticker-box web)
- ✅ Blue title bar
- ✅ 2-column grid layout
- ✅ Shadow untuk depth

---

### **3. Section Header (Gradient)**
```html
<div class="section-header">DATA PASIEN</div>
```

**Features:**
- ✅ Gradient blue background
- ✅ White text
- ✅ Arrow icon (▶)
- ✅ Rounded corners
- ✅ Shadow

---

### **4. Subsection Header (Light Blue)**
```html
<div class="subsection-header">Pemeriksaan Laboratorium</div>
```

**Features:**
- ✅ Light blue background (#e3f0ff)
- ✅ Blue text
- ✅ Left border accent (4px solid)

---

### **5. Modern Table**
```html
<table class="data-table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
        </tr>
    </tbody>
</table>
```

**Features:**
- ✅ Gradient blue header
- ✅ Alternating row colors
- ✅ Hover effect
- ✅ Rounded corners
- ✅ Shadow

---

### **6. Data Grid (Cards)**
```html
<div class="data-grid">
    <div class="data-grid-item">
        <div class="label">Tinggi Badan</div>
        <div class="value">170 cm</div>
    </div>
</div>
```

**Features:**
- ✅ White card dengan border
- ✅ Hover effect (border blue)
- ✅ Shadow
- ✅ Label uppercase
- ✅ Responsive grid

---

### **7. Info Box (Alert)**
```html
<div class="info-box success">
    <div class="title">✓ Status</div>
    <div class="content">Message here</div>
</div>
```

**Variants:**
- ✅ `.info-box` (default blue)
- ✅ `.info-box.success` (green)
- ✅ `.info-box.warning` (yellow)
- ✅ `.info-box.danger` (red)

---

### **8. Checkbox List**
```html
<div class="checkbox-list">
    <div class="checkbox-item checked">
        <div class="checkbox-icon checked">✓</div>
        <span>Hipertensi</span>
    </div>
</div>
```

**Features:**
- ✅ Grid layout (2 columns)
- ✅ Custom checkbox icon
- ✅ Checked state styling
- ✅ Blue accent color

---

### **9. Signature Area**
```html
<div class="signature-section">
    <div class="signature-box">
        <div class="title">Dokter Anestesi</div>
        <div class="name">dr. Ahmad Yani</div>
        <div class="role">Dokter Anestesi</div>
    </div>
</div>
```

**Features:**
- ✅ 2-column grid
- ✅ White boxes dengan border
- ✅ Space untuk tanda tangan (60px)
- ✅ Name underline

---

### **10. Badges**
```html
<span class="badge primary">Primary</span>
<span class="badge success">Success</span>
<span class="badge warning">Warning</span>
<span class="badge danger">Danger</span>
```

**Use Cases:**
- Status form
- ASA classification
- Risk level
- Priority

---

### **11. Print Controls (Fixed Position)**
```html
<div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">
        🖨️ Cetak PDF
    </button>
    <button class="btn-close" onclick="window.close()">
        ✖ Tutup
    </button>
</div>
```

**Features:**
- ✅ Fixed position (top-right)
- ✅ Gradient blue button
- ✅ Hover effects
- ✅ Auto-hide saat print

---

### **12. Footer**
```html
<div class="page-footer no-print">
    <div class="print-info">
        <span>Dicetak: 13 Oktober 2025</span>
        <span>Halaman 1 dari 1</span>
        <span>RS Josaturu</span>
    </div>
</div>
```

**Features:**
- ✅ Fixed bottom
- ✅ Blue top border
- ✅ 3-column layout (flex)
- ✅ Small text (8pt)

---

## 📱 **RESPONSIVE FEATURES**

### **Print-Specific**
```css
@media print {
    .no-print {
        display: none;  /* Hide controls */
    }
    
    body {
        background: white;  /* Pure white background */
    }
}
```

---

## 🎯 **USAGE GUIDE**

### **1. Copy Template**
```php
// Start from:
process/pdf/pdf-template-modern.php

// Customize:
- Change title
- Add data fields
- Adjust grid columns
- Add/remove sections
```

### **2. Replace Placeholder Data**
```php
// From:
<div class="value">Dadang Beton</div>

// To:
<div class="value"><?= displayValue($data['nama']) ?></div>
```

### **3. Use Helper Functions**
```php
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

function formatDate($date) {
    return date('d F Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d-m-Y H:i', strtotime($datetime));
}
```

---

## ✅ **CHECKLIST IMPLEMENTATION**

### **Every PDF Should Have:**
- [x] Modern gradient header
- [x] Patient info box (dashed border)
- [x] Section headers dengan gradient
- [x] Consistent color scheme
- [x] Print controls (fixed button)
- [x] Footer dengan print info
- [x] Proper spacing & margins
- [x] Shadow untuk depth
- [x] Hover effects (subtle)

---

## 🎨 **DESIGN PRINCIPLES**

### **1. Consistency**
- Same colors across all PDFs
- Same spacing (12px grid)
- Same border radius (6-10px)
- Same typography

### **2. Hierarchy**
- Clear visual hierarchy
- Bold headers stand out
- Proper contrast ratios
- Consistent sizing

### **3. Readability**
- Adequate line height (1.6)
- Good font size (10pt body)
- High contrast text
- Proper spacing

### **4. Professional**
- Clean & modern design
- Subtle shadows
- Smooth gradients
- Rounded corners

---

## 📊 **COMPARISON**

### **OLD PDF (Before)**
```
❌ Plain text
❌ No colors
❌ Basic borders
❌ No hierarchy
❌ Hard to read
```

### **NEW PDF (After)**
```
✅ Modern gradient header
✅ Blue color scheme
✅ Card-based layout
✅ Clear visual hierarchy
✅ Easy to read
✅ Mirip form web
```

---

## 🚀 **QUICK START**

### **Create New PDF (Based on Template)**

```php
<?php
// 1. Copy template
$template = file_get_contents('pdf-template-modern.php');

// 2. Replace data
$pdf = str_replace(
    ['{{title}}', '{{patient_name}}', ...],
    [$title, $patient_name, ...],
    $template
);

// 3. Output
echo $pdf;
?>
```

### **Or Build Manually:**
```php
<?php
require_once 'pdf-template-modern.php';

// Add your data sections here
?>
<div class="section-header">YOUR SECTION</div>
<div class="data-grid">
    <div class="data-grid-item">
        <div class="label">Label</div>
        <div class="value"><?= $data ?></div>
    </div>
</div>
```

---

## 📁 **FILE STRUCTURE**

```
process/pdf/
├── pdf-template-modern.php           ← Template dasar
├── pdf-konsultasi-anestesi.php       ← Apply template
├── pdf-informed-consent.php          ← Apply template
├── pdf-catatan-sedasi.php            ← Apply template
└── ... (other PDFs)
```

---

## 🎓 **BEST PRACTICES**

### **DO's** ✅
- Use template as base
- Keep colors consistent
- Use helper functions
- Add proper spacing
- Test print preview
- Use semantic HTML

### **DON'Ts** ❌
- Don't use inline styles excessively
- Don't mix different color schemes
- Don't forget print media queries
- Don't hardcode data
- Don't use complex layouts
- Don't forget accessibility

---

## 🔧 **CUSTOMIZATION**

### **Change Primary Color**
```css
/* In template, replace all instances of: */
#4285f4  →  #YOUR_COLOR
#3367d6  →  #YOUR_DARKER_COLOR
#5a9fff  →  #YOUR_LIGHTER_COLOR
```

### **Change Layout**
```css
/* For landscape: */
@page {
    size: A4 landscape;
}

/* Adjust grid columns */
.data-grid {
    grid-template-columns: repeat(3, 1fr);  /* 3 cols */
}
```

---

**Template siap digunakan!** 🎨✨

**Next Step:** Apply template ini ke semua PDF yang ada (Konsultasi Anestesi, Catatan Sedasi, dll)
