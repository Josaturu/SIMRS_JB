# 🗄️ Database Merge Guide

## 📋 Overview

Merge database dari SQL dump file `dbanestesi[1].sql` ke database yang sedang berjalan.

**Source:** `c:\Users\hp\AppData\Local\Microsoft\Windows\INetCache\IE\G5DUM8V3\dbanestesi[1].sql`  
**Target:** Database `dbanestesi` (localhost)  
**Date:** 2025-10-14

---

## ⚠️ IMPORTANT - BACKUP FIRST!

**WAJIB backup database sebelum merge!**

### **Via phpMyAdmin:**
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Tab "Export"
4. Format: SQL
5. Click "Go"
6. Save file: `dbanestesi_backup_20251014.sql`

### **Via Command Line:**
```bash
mysqldump -u root -p dbanestesi > dbanestesi_backup_20251014.sql
```

---

## 🎯 Merge Options

### **Option 1: Full Import (Fresh Database)**

**Use this if:**
- Database `dbanestesi` belum ada
- Mau reset database total
- Tidak ada data penting yang perlu dipertahankan

**Steps:**

#### **A. Via phpMyAdmin:**
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. **Drop existing database** (jika ada):
   - Pilih database `dbanestesi`
   - Tab "Operations"
   - Scroll ke bawah → "Drop the database"
   - Confirm
3. **Create new database:**
   - Click "New" di sidebar
   - Database name: `dbanestesi`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"
4. **Import SQL file:**
   - Pilih database `dbanestesi` yang baru dibuat
   - Tab "Import"
   - Click "Choose File"
   - Select: `c:\Users\hp\AppData\Local\Microsoft\Windows\INetCache\IE\G5DUM8V3\dbanestesi[1].sql`
   - Click "Go"
5. **Wait for completion**
6. **Verify:**
   - Check tables di sidebar
   - Browse data di setiap tabel

#### **B. Via Command Line:**
```bash
# Drop database (jika ada)
mysql -u root -p -e "DROP DATABASE IF EXISTS dbanestesi;"

# Create database
mysql -u root -p -e "CREATE DATABASE dbanestesi CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Import SQL file
mysql -u root -p dbanestesi < "c:\Users\hp\AppData\Local\Microsoft\Windows\INetCache\IE\G5DUM8V3\dbanestesi[1].sql"

# Verify
mysql -u root -p dbanestesi -e "SHOW TABLES;"
```

---

### **Option 2: Selective Merge (Keep Existing Data)**

**Use this if:**
- Database sudah ada data penting
- Hanya mau update struktur tabel
- Mau merge data tanpa overwrite

**Steps:**

#### **1. Copy SQL file ke project folder:**
```bash
# Copy file ke folder yang mudah diakses
copy "c:\Users\hp\AppData\Local\Microsoft\Windows\INetCache\IE\G5DUM8V3\dbanestesi[1].sql" "c:\FOLDER RIZKI\SIMRS_JB\sql\dbanestesi_import.sql"
```

#### **2. Run merge script:**

**Via phpMyAdmin:**
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Tab "SQL"
4. Open file: `c:\FOLDER RIZKI\SIMRS_JB\sql\merge_database.sql`
5. Copy-paste isi file ke SQL editor
6. Click "Go"
7. Check hasil

**Via Command Line:**
```bash
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\sql\merge_database.sql"
```

#### **3. Verify merge:**
```sql
-- Check tables
SHOW TABLES;

-- Check patient data
SELECT * FROM pasien;

-- Check booking data
SELECT * FROM booking_operasi;

-- Check konsultasi data
SELECT COUNT(*) as total FROM tbl_anestesi_konsultasi_anestesi;
```

---

### **Option 3: Manual Selective Import**

**Use this if:**
- Mau kontrol penuh apa yang di-import
- Hanya mau import tabel tertentu
- Mau review setiap statement sebelum execute

**Steps:**

1. **Open SQL dump file:**
   - Buka `dbanestesi[1].sql` di text editor
   
2. **Extract specific parts:**
   - Copy hanya `CREATE TABLE` statements yang dibutuhkan
   - Copy hanya `INSERT` statements yang dibutuhkan
   - Skip yang tidak perlu

3. **Execute via phpMyAdmin:**
   - Pilih database `dbanestesi`
   - Tab "SQL"
   - Paste statement
   - Click "Go"
   - Repeat untuk setiap statement

---

## 🔍 Verification Checklist

After merge, verify:

### **1. Tables Created:**
```sql
SHOW TABLES;
```

**Expected tables:**
- ✅ `booking_operasi`
- ✅ `pasien`
- ✅ `tbl_anestesi_catatan_anestesi`
- ✅ `tbl_anestesi_informed_consent_anestesi`
- ✅ `tbl_anestesi_kamar_pemulihan`
- ✅ `tbl_anestesi_keselamatan_operasi`
- ✅ `tbl_anestesi_konsultasi_anestesi`
- ✅ `tbl_anestesi_vital_pemulihan`
- ✅ `tbl_anestesi_vital_sign`

### **2. Data Imported:**
```sql
-- Check patient
SELECT * FROM pasien;
-- Expected: At least 1 row (dadang beton)

-- Check booking
SELECT * FROM booking_operasi;
-- Expected: At least 2 rows

-- Check konsultasi
SELECT COUNT(*) FROM tbl_anestesi_konsultasi_anestesi;
```

### **3. Foreign Keys:**
```sql
-- Check constraints
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'dbanestesi'
ORDER BY TABLE_NAME;
```

**Expected constraints:**
- ✅ `fk_booking_pasien`
- ✅ `fk_catatan_booking`
- ✅ `fk_consent_booking`
- ✅ `fk_pemulihan_booking`
- ✅ `fk_chk_booking`
- ✅ `fk_vital_booking`
- ✅ `fk_vital_pem`
- ✅ `fk_vitals_booking`

### **4. Test Application:**
```bash
# Start PHP server
php -S localhost:8000

# Open browser
http://localhost:8000

# Test:
# - Daftar pasien loads
# - Detail pasien loads
# - Forms can be opened
# - Data can be saved
```

---

## 🚨 Troubleshooting

### **Error: Table already exists**
```
ERROR 1050 (42S01): Table 'booking_operasi' already exists
```

**Solution:**
- Use `CREATE TABLE IF NOT EXISTS` instead
- Or use Option 2 (Selective Merge)
- Or drop existing tables first (⚠️ data loss!)

### **Error: Duplicate entry**
```
ERROR 1062 (23000): Duplicate entry '1' for key 'PRIMARY'
```

**Solution:**
- Use `INSERT IGNORE` instead of `INSERT`
- Or use `INSERT ... ON DUPLICATE KEY UPDATE`
- Or delete existing data first

### **Error: Cannot add foreign key**
```
ERROR 1215 (HY000): Cannot add foreign key constraint
```

**Solution:**
- Check referenced table exists
- Check referenced column exists
- Check data types match
- Check existing data doesn't violate constraint

### **Error: Access denied**
```
ERROR 1045 (28000): Access denied for user 'root'@'localhost'
```

**Solution:**
- Check MySQL username & password
- Grant proper privileges:
  ```sql
  GRANT ALL PRIVILEGES ON dbanestesi.* TO 'root'@'localhost';
  FLUSH PRIVILEGES;
  ```

---

## 📝 Post-Merge Tasks

### **1. Update Application Config:**

Check `config/database.php`:
```php
private $host = "localhost";
private $db_name = "dbanestesi";  // ✅ Verify
private $username = "root";        // ✅ Verify
private $password = "";            // ✅ Verify
```

### **2. Test All Forms:**
- ✅ Form Konsultasi Anestesi
- ✅ Form Informed Consent
- ✅ Form Catatan Sedasi
- ✅ Form Kamar Pemulihan
- ✅ Form Keselamatan Operasi
- ✅ Form Vital Sign

### **3. Test PDF Generation:**
- ✅ PDF Konsultasi Anestesi
- ✅ PDF Informed Consent
- ✅ PDF Catatan Sedasi

### **4. Check Logs:**
```bash
# Check PHP error log
tail -f /xampp/apache/logs/error.log

# Check application log
tail -f logs/persiapan-operasi-debug.log
```

---

## 🎯 Quick Commands

### **Full Import (Fresh):**
```bash
# Backup first
mysqldump -u root -p dbanestesi > backup_$(date +%Y%m%d).sql

# Drop & recreate
mysql -u root -p -e "DROP DATABASE IF EXISTS dbanestesi; CREATE DATABASE dbanestesi;"

# Import
mysql -u root -p dbanestesi < "c:\Users\hp\AppData\Local\Microsoft\Windows\INetCache\IE\G5DUM8V3\dbanestesi[1].sql"

# Verify
mysql -u root -p dbanestesi -e "SHOW TABLES;"
```

### **Selective Merge:**
```bash
# Run merge script
mysql -u root -p dbanestesi < "c:\FOLDER RIZKI\SIMRS_JB\sql\merge_database.sql"
```

### **Verify:**
```bash
# Check tables
mysql -u root -p dbanestesi -e "SHOW TABLES;"

# Check data
mysql -u root -p dbanestesi -e "SELECT COUNT(*) FROM pasien; SELECT COUNT(*) FROM booking_operasi;"
```

---

## ✅ Success Criteria

Merge berhasil jika:
1. ✅ Semua tabel ada di database
2. ✅ Sample data ter-import
3. ✅ Foreign keys ter-create
4. ✅ Aplikasi bisa akses database
5. ✅ Forms bisa save data
6. ✅ PDF bisa di-generate
7. ✅ No errors di log

---

## 📚 Additional Resources

- **SQL Scripts:** `/sql/` folder
- **Database Config:** `config/database.php`
- **Documentation:** `/docs/` folder
- **Troubleshooting:** `/docs/debug/` folder

---

**Created:** 2025-10-14  
**Database:** dbanestesi  
**Status:** Ready to merge
