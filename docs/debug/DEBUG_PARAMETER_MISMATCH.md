# 🔍 DEBUG: Parameter Mismatch

## ❌ Current Error
```
SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
```

## ✅ Verification Done
- ✅ Columns in INSERT: **96**
- ✅ Placeholders (?): **96**
- ✅ Parameters in execute(): **96**

**All counts MATCH!** But still error → Problem is likely **missing column in database**

---

## 🔍 Diagnosis Steps

### **Step 1: Check if Column Exists**

**Jalankan di phpMyAdmin:**
```sql
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
  AND COLUMN_NAME = 'jenis_diagnosa';
```

**Expected Result:**
```
COLUMN_NAME
jenis_diagnosa
```

**If result is EMPTY:**
- ❌ Column does NOT exist
- ✅ Need to run ALTER TABLE

**If result shows `jenis_diagnosa`:**
- ✅ Column exists
- ❌ Problem is somewhere else

---

### **Step 2: If Column Missing, Add It**

**Jalankan di phpMyAdmin:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
COMMENT 'Jenis diagnosa: Cito atau Elektif'
AFTER diagnosa_pra_operasi;
```

**Expected Result:**
```
Query OK, 0 rows affected
```

---

### **Step 3: Verify Column Added**

```sql
DESCRIBE tbl_anestesi_konsultasi_anestesi;
```

**Look for line:**
```
jenis_diagnosa | varchar(20) | YES | | Elektif | Jenis diagnosa: Cito atau Elektif
```

---

### **Step 4: Test Form Again**

1. Buka form konsultasi anestesi
2. Submit form
3. **Check error message**

**If still error, check error.log:**

Lokasi: `C:\xampp\apache\logs\error.log`

**Look for:**
```
Error konsultasi anestesi: [ERROR MESSAGE]
Error Code: HY093
Placeholder count in query: [NUMBER]
Parameter count in execute: 96
```

**Report:**
- Placeholder count: ___
- Parameter count: 96
- Match? Yes / No

---

## 🔧 Possible Issues & Solutions

### **Issue 1: Column `jenis_diagnosa` Missing**

**Symptom:**
```
Unknown column 'jenis_diagnosa' in 'field list'
```

**Solution:**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```

---

### **Issue 2: Placeholder Count Mismatch**

**Symptom:**
```
Placeholder count in query: 95
Parameter count in execute: 96
```

**Solution:** Query has wrong number of `?`

**Check VALUES line:**
```sql
VALUES (?, ?, ?, ..., ?, ?, ?)
```

**Should have exactly 96 question marks**

---

### **Issue 3: Wrong Column Order**

**Symptom:**
```
Data type mismatch or column doesn't match
```

**Solution:** Check column order in INSERT matches parameter order in execute()

**Verify:**
1. Column #12 in INSERT = `jenis_diagnosa`
2. Parameter #12 in execute() = `$jenis_diagnosa`

---

## 📝 Testing Checklist

### **Before Testing:**
- [ ] Run SQL to check if column exists
- [ ] If missing, run ALTER TABLE
- [ ] Verify column added with DESCRIBE

### **During Testing:**
- [ ] Submit form
- [ ] Note error message
- [ ] Check error.log
- [ ] Note placeholder count

### **After Testing:**
- [ ] Report placeholder count
- [ ] Report parameter count
- [ ] Report if column exists
- [ ] Report exact error message

---

## 🎯 Quick Test Commands

**Copy-paste ke phpMyAdmin:**

```sql
-- 1. Check column
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
AND COLUMN_NAME = 'jenis_diagnosa';

-- 2. If empty, add column
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;

-- 3. Verify
DESCRIBE tbl_anestesi_konsultasi_anestesi;

-- 4. Test query (should not error)
SELECT jenis_diagnosa FROM tbl_anestesi_konsultasi_anestesi LIMIT 1;
```

---

## 📊 Debug Report Template

**Date:** 2025-10-14  
**Time:** 11:05

**1. Column Check:**
```sql
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_anestesi_konsultasi_anestesi' 
AND COLUMN_NAME = 'jenis_diagnosa';
```
**Result:** [PASTE RESULT - Empty or has row?]

**2. ALTER TABLE (if needed):**
```sql
ALTER TABLE tbl_anestesi_konsultasi_anestesi 
ADD COLUMN jenis_diagnosa VARCHAR(20) DEFAULT 'Elektif' 
AFTER diagnosa_pra_operasi;
```
**Result:** [Query OK or Error?]

**3. Form Submit:**
**Error Message:** [PASTE ERROR]

**4. Error Log:**
```
[PASTE RELEVANT LOG LINES]
```

**5. Placeholder Count:**
- Placeholder in query: ___
- Parameter in execute: 96
- Match: Yes / No

---

## ✅ Success Criteria

Form will work if:
1. ✅ Column `jenis_diagnosa` exists in database
2. ✅ Placeholder count = 96
3. ✅ Parameter count = 96
4. ✅ Column order matches parameter order

---

**Silakan test dan laporkan hasilnya!** 🔍
