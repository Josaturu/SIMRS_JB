# 🔧 Scripts Folder

Folder ini berisi utility scripts untuk debugging dan maintenance.

## 📋 Available Scripts

### **1. `count_parameters.php`**
**Purpose:** Menghitung jumlah kolom dan parameter yang dibutuhkan untuk query INSERT

**Usage:**
```bash
php scripts/count_parameters.php
```

**Output:**
- Total kolom yang dibutuhkan
- List semua kolom
- Jumlah placeholder yang dibutuhkan

**When to use:**
- Saat menambah kolom baru ke tabel
- Saat ada error parameter mismatch
- Untuk verify jumlah parameter

---

### **2. `count_placeholders.php`**
**Purpose:** Menghitung jumlah placeholder (?) di query INSERT

**Usage:**
```bash
php scripts/count_placeholders.php
```

**Output:**
- Total placeholder di VALUES
- Breakdown per line
- Comparison dengan expected count

**When to use:**
- Saat ada error "parameter mismatch"
- Untuk verify query INSERT
- Debugging placeholder count

---

### **3. `verify_execute_params.php`**
**Purpose:** Verify jumlah parameter di execute() array

**Usage:**
```bash
php scripts/verify_execute_params.php
```

**Output:**
- Total parameter di execute()
- List semua parameter
- Match status dengan expected count

**When to use:**
- Saat ada error parameter mismatch
- Untuk verify execute() array
- Debugging parameter count

---

### **4. `compare_columns.php`**
**Purpose:** Membandingkan kolom di SQL file vs kolom di query INSERT

**Usage:**
```bash
php scripts/compare_columns.php
```

**Output:**
- Column count comparison
- Columns in query but not in SQL
- Columns in SQL but not in query
- Side-by-side comparison

**When to use:**
- Saat menambah kolom baru
- Untuk verify query INSERT sesuai dengan database
- Debugging column mismatch

---

### **5. `update_checkbox_risiko.php`**
**Purpose:** Update checkbox risiko (legacy script)

**Status:** ⚠️ Legacy - Mungkin sudah tidak terpakai

---

### **6. `update_checkbox_status_fisik.php`**
**Purpose:** Update checkbox status fisik (legacy script)

**Status:** ⚠️ Legacy - Mungkin sudah tidak terpakai

---

## 🔍 Troubleshooting Guide

### **Error: Parameter Mismatch**

**Step 1:** Hitung kolom
```bash
php scripts/count_parameters.php
```

**Step 2:** Hitung placeholder
```bash
php scripts/count_placeholders.php
```

**Step 3:** Verify execute params
```bash
php scripts/verify_execute_params.php
```

**Step 4:** Compare dengan database
```bash
php scripts/compare_columns.php
```

**Expected Result:**
```
Columns: 96
Placeholders: 96
Parameters: 96
✅ ALL MATCH!
```

---

### **Error: Unknown Column**

**Step 1:** Compare columns
```bash
php scripts/compare_columns.php
```

**Step 2:** Check SQL file
- Buka file SQL export dari database
- Verify kolom ada di tabel

**Step 3:** Update query
- Tambah kolom ke query INSERT jika perlu
- Update execute() array

---

## 📝 Maintenance

### **Updating Scripts:**
- Scripts ini di-generate untuk debugging specific issue
- Update jika ada perubahan struktur tabel
- Keep scripts simple dan focused

### **Adding New Scripts:**
- Simpan di folder `/scripts/`
- Tambahkan dokumentasi di README ini
- Buat script reusable dan general purpose

### **Cleanup:**
- Review scripts setiap 6 bulan
- Hapus scripts yang sudah tidak terpakai
- Mark legacy scripts dengan ⚠️

---

## 🎯 Quick Commands

**Verify semua parameter match:**
```bash
cd c:\FOLDER RIZKI\SIMRS_JB
php scripts/count_parameters.php
php scripts/count_placeholders.php
php scripts/verify_execute_params.php
```

**Compare dengan database:**
```bash
php scripts/compare_columns.php
```

---

## ⚠️ Important Notes

1. **Scripts ini untuk debugging only** - Jangan dijalankan di production
2. **Backup dulu sebelum run script** - Especially yang modify data
3. **Test di local dulu** - Jangan langsung di server production
4. **Update scripts jika struktur tabel berubah**

---

**Last Updated:** 2025-10-14  
**Maintained By:** Development Team
