# 🗄️ SQL Folder

Folder ini berisi SQL scripts untuk database management.

## 📋 Available SQL Scripts

### **1. `add_jenis_diagnosa_column.sql`**
**Purpose:** Menambah kolom `jenis_diagnosa` ke tabel `tbl_anestesi_konsultasi_anestesi`

**Description:**
- Menambah kolom untuk menyimpan jenis diagnosa (Cito/Elektif)
- Default value: 'Elektif'
- Data type: VARCHAR(20)

**Usage:**
1. Buka phpMyAdmin
2. Pilih database `dbanestesi`
3. Jalankan SQL dari file ini

**SQL:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
COMMENT 'Jenis diagnosa: Cito atau Elektif'
AFTER diagnosa_pra_operasi;
```

**Status:** ✅ Already applied (kolom sudah ada di database)

---

### **2. `check_column_exists.sql`**
**Purpose:** Mengecek apakah kolom tertentu sudah ada di tabel

**Description:**
- Check jika kolom `jenis_diagnosa` ada
- Multiple methods untuk verify
- Useful untuk troubleshooting

**Usage:**
1. Buka phpMyAdmin
2. Jalankan query dari file ini
3. Check hasil

**Methods:**
- Method 1: INFORMATION_SCHEMA
- Method 2: DESCRIBE table
- Method 3: SHOW CREATE TABLE

**When to use:**
- Sebelum menjalankan ALTER TABLE
- Untuk verify kolom sudah ditambahkan
- Debugging "Unknown column" error

---

## 🔍 Common SQL Tasks

### **Check Table Structure:**
```sql
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

### **Check Column Exists:**
```sql
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
  AND COLUMN_NAME = 'jenis_diagnosa';
```

### **Add New Column:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN column_name DATA_TYPE DEFAULT 'default_value' 
AFTER existing_column;
```

### **Update Existing Data:**
```sql
UPDATE tbl_anestesi_konsultasi_anestesi 
SET jenis_diagnosa = 'Elektif' 
WHERE jenis_diagnosa IS NULL;
```

---

## 📝 Database Schema

### **Table: `tbl_anestesi_konsultasi_anestesi`**

**Total Columns:** 97 (including `id`)

**Key Columns:**
- `id` - INT, AUTO_INCREMENT, PRIMARY KEY
- `no_rawat` - VARCHAR(20), NOT NULL
- `kode_paket` - VARCHAR(20), NOT NULL
- `tanggal` - DATE, NOT NULL
- `jam_mulai` - TIME, NOT NULL
- `jenis_diagnosa` - VARCHAR(20), DEFAULT 'Elektif' ✨ NEW
- `jenis_kelamin` - ENUM('Laki-laki','Perempuan')
- ... (91 more columns)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`no_rawat`, `kode_paket`, `tanggal`, `jam_mulai`)
- INDEX (`no_rawat`)
- INDEX (`tanggal`)

---

## ⚠️ Important Notes

### **Before Running SQL:**
1. ✅ **Backup database dulu!**
2. ✅ Check apakah kolom sudah ada
3. ✅ Test di local dulu, jangan langsung di production
4. ✅ Verify syntax SQL benar

### **After Running SQL:**
1. ✅ Verify kolom sudah ditambahkan (DESCRIBE table)
2. ✅ Test query SELECT
3. ✅ Test form submit
4. ✅ Check data tersimpan dengan benar

### **Common Errors:**

**Error: Duplicate column name**
```
Error: Duplicate column name 'jenis_diagnosa'
```
**Solution:** Kolom sudah ada, skip ALTER TABLE

**Error: Unknown column**
```
Error: Unknown column 'jenis_diagnosa' in 'field list'
```
**Solution:** Jalankan ALTER TABLE untuk tambah kolom

---

## 🎯 Quick Reference

### **Add Column Workflow:**

1. **Check if exists:**
```sql
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
AND COLUMN_NAME = 'jenis_diagnosa';
```

2. **If not exists, add it:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

3. **Verify:**
```sql
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

4. **Test:**
```sql
SELECT jenis_diagnosa FROM tbl_anestesi_konsultasi_anestesi LIMIT 1;
```

---

## 📚 Additional Resources

### **Export Database:**
```bash
mysqldump -u root -p dbanestesi > backup_$(date +%Y%m%d).sql
```

### **Import Database:**
```bash
mysql -u root -p dbanestesi < backup_20251014.sql
```

### **Check Database Size:**
```sql
SELECT 
    table_name AS "Table",
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES 
WHERE table_schema = "dbanestesi"
ORDER BY (data_length + index_length) DESC;
```

---

**Last Updated:** 2025-10-14  
**Database:** dbanestesi  
**Maintained By:** Development Team
