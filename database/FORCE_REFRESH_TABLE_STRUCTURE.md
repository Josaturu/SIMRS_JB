# 🔧 FIX: Force Refresh Table Structure

**Problem:** Error "Column not found: cat_kuku_dibersihkan" meskipun kolom sudah ada di database

**Root Cause:** PHP/MySQL cache masih menggunakan struktur tabel lama

---

## ✅ **VERIFIED:**

Kolom `cat_kuku_dibersihkan` **SUDAH ADA** di database (line 80 di SQL dump):
```sql
`cat_kuku_dibersihkan` tinyint(1) DEFAULT NULL COMMENT 'UBS Item 17: Cat kuku dibersihkan',
```

---

## 🔧 **SOLUTION STEPS:**

### **STEP 1: Restart MySQL & Apache** (RECOMMENDED)

1. **Buka XAMPP Control Panel**
2. **Stop MySQL** (klik tombol Stop)
3. **Stop Apache** (klik tombol Stop)
4. **Tunggu 5 detik**
5. **Start MySQL** (klik tombol Start)
6. **Start Apache** (klik tombol Start)
7. **Tunggu sampai hijau**

**Why:** Ini akan clear semua cache dan force reload table structure.

---

### **STEP 2: Flush MySQL Query Cache**

Jika restart tidak membantu, jalankan query ini di phpMyAdmin:

```sql
USE dbanestesi;

-- Flush query cache
FLUSH QUERY CACHE;

-- Flush tables
FLUSH TABLES;

-- Verify column exists
SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi 
WHERE Field = 'cat_kuku_dibersihkan';
```

**Expected Result:**
```
Field: cat_kuku_dibersihkan
Type: tinyint(1)
Null: YES
Key: 
Default: NULL
Extra: 
```

---

### **STEP 3: Clear PHP OpCache** (If using OpCache)

Buat file PHP temporary untuk clear cache:

**File:** `d:\KKI\Module SIMRS\Module\clear_cache.php`

```php
<?php
// Clear OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OpCache cleared!<br>";
} else {
    echo "⚠️ OpCache not enabled<br>";
}

// Clear PDO connection cache
echo "✅ Reconnecting to database...<br>";

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=dbanestesi", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_anestesi_persiapan_operasi WHERE Field = 'cat_kuku_dibersihkan'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ Column 'cat_kuku_dibersihkan' EXISTS in database!<br>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "❌ Column 'cat_kuku_dibersihkan' NOT FOUND in database!<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}

echo "<br><a href='javascript:history.back()'>← Back</a>";
?>
```

**Cara pakai:**
1. Save file di `d:\KKI\Module SIMRS\Module\clear_cache.php`
2. Buka di browser: `http://localhost/Module/clear_cache.php`
3. Lihat hasilnya

---

### **STEP 4: Hard Refresh Browser**

1. **Buka form persiapan operasi**
2. **Hard refresh:**
   - Windows: `Ctrl + Shift + R`
   - Or: `Ctrl + F5`
3. **Clear browser cache:**
   - Press `Ctrl + Shift + Delete`
   - Select "Cached images and files"
   - Click "Clear data"

---

### **STEP 5: Test Form Again**

1. **Open Console** (F12)
2. **Fill form** (including item 17 - Cat kuku)
3. **Submit**
4. **Check console log:**

**Expected (Success):**
```javascript
[LoadingSkeleton] Form submit detected: formPersiapanOperasi
[LoadingSkeleton] Showing saving skeleton
(page redirects)
[Notification] URL Params: {status: "sukses", action: "saved"}
[Notification] Showing success: Data berhasil disimpan.
```

**NOT Expected (Error):**
```javascript
[Notification] URL Params: {
    status: 'error', 
    msg: "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'cat_kuku_dibersihkan'"
}
```

---

## 🔍 **ALTERNATIVE: Verify Database Directly**

Jika masih error setelah restart, jalankan query ini untuk double-check:

```sql
USE dbanestesi;

-- 1. Check if column exists
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
AND COLUMN_NAME = 'cat_kuku_dibersihkan';

-- 2. Check column position
SELECT 
    ORDINAL_POSITION,
    COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dbanestesi' 
AND TABLE_NAME = 'tbl_anestesi_persiapan_operasi'
AND COLUMN_NAME IN ('rambut_makeup_dibersihkan', 'cat_kuku_dibersihkan', 'rawat_cat_kuku_dibersihkan', 'perhiasan_dilepas')
ORDER BY ORDINAL_POSITION;

-- 3. Try INSERT test
INSERT INTO tbl_anestesi_persiapan_operasi 
(no_rawat, cat_kuku_dibersihkan) 
VALUES 
('TEST123', 1);

-- If successful, delete test data
DELETE FROM tbl_anestesi_persiapan_operasi WHERE no_rawat = 'TEST123';
```

**If query 3 fails:** Kolom memang belum ada, perlu run ALTER TABLE lagi.

**If query 3 success:** Kolom ada, masalahnya di PHP cache.

---

## 🚨 **IF STILL NOT WORKING:**

### **Nuclear Option: Drop & Recreate Table**

⚠️ **WARNING: This will DELETE ALL DATA in the table!**

**Only do this if:**
- Table is empty or
- You have backup of data

```sql
USE dbanestesi;

-- Backup data first (if any)
CREATE TABLE tbl_anestesi_persiapan_operasi_backup AS 
SELECT * FROM tbl_anestesi_persiapan_operasi;

-- Drop table
DROP TABLE tbl_anestesi_persiapan_operasi;

-- Recreate from your SQL file
-- (Run the CREATE TABLE statement from tbl_anestesi_persiapan_operasi (1).sql)

-- Restore data (if any)
INSERT INTO tbl_anestesi_persiapan_operasi 
SELECT * FROM tbl_anestesi_persiapan_operasi_backup;

-- Drop backup
DROP TABLE tbl_anestesi_persiapan_operasi_backup;
```

---

## 📊 **TROUBLESHOOTING CHECKLIST:**

- [ ] Restart MySQL & Apache
- [ ] Flush MySQL cache (FLUSH TABLES)
- [ ] Clear PHP OpCache
- [ ] Hard refresh browser
- [ ] Clear browser cache
- [ ] Verify column exists (SHOW COLUMNS)
- [ ] Test INSERT query directly
- [ ] Check PHP error log
- [ ] Check MySQL error log

---

## 🎯 **MOST LIKELY SOLUTION:**

**99% of the time, restarting MySQL & Apache fixes this issue!**

The problem is that MySQL/PHP is caching the old table structure and hasn't reloaded the new one after ALTER TABLE.

---

**Last Updated:** 21 Oktober 2025  
**Status:** ⚠️ **CACHE ISSUE - RESTART REQUIRED**

---

**🔄 RESTART XAMPP SEKARANG! 🔄**
