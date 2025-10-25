# 🔍 AUDIT: Inline Notification & Loading

**Tanggal:** 21 Oktober 2025  
**Tujuan:** Identifikasi dan hapus semua inline notification/loading, ganti dengan sistem global

---

## 📊 **SUMMARY:**

| File | Inline Notification | Loading | Action Required |
|------|-------------------|---------|-----------------|
| **form-persiapan-operasi.php** | ✅ Yes (4 types) | ✅ data-no-loading | Remove notification, keep data-no-loading |
| **form-keselamatan-operasi.php** | ✅ Yes (4 types) | ✅ data-no-loading | Remove notification, keep data-no-loading |
| **form-konsultasi-anestesi.php** | ✅ Yes (2 types) | ❌ No | Remove notification |
| **form-informed-consent-anestesi.php** | ✅ Yes (2 types) | ❌ No | Remove notification |
| **form-catatan-sedasi.php** | ✅ Yes (2 types) | ❌ No | Remove notification |
| **detail-pasien.php** | ✅ Yes (2 types) | ❌ No | Remove notification |
| **form-kamar-pemulihan.php** | ❌ No (already removed) | ✅ data-no-loading | ✅ Already migrated |
| **form-vital-sign.php** | ❓ Need check | ❓ Need check | Need audit |
| **daftar-pasien.php** | ❓ Need check | ❓ Need check | Need audit |
| **tambah-booking.php** | ❓ Need check | ❓ Need check | Need audit |

---

## 📝 **DETAILED FINDINGS:**

### **1. form-persiapan-operasi.php**

**Inline Notifications Found:**
```php
// Line 91-92: SESSION success
echo '<div class="alert alert-success" style="...">...</div>';

// Line 95-96: SESSION error
echo '<div class="alert alert-danger" style="...">...</div>';

// Line 99: GET status=sukses
echo '<div class="alert alert-success" style="...">Data berhasil disimpan!</div>';

// Line 102: GET status=gagal
echo '<div class="alert alert-danger" style="...">Gagal menyimpan data: ...</div>';
```

**Loading:**
```php
// Line 146: Has data-no-loading attribute
<form id="formPersiapanOperasi" ... data-no-loading>
```

**Action:**
- ❌ Remove lines 88-104 (all inline notifications)
- ✅ Keep data-no-loading (intentional, form has custom handling)
- ✅ Global notification will auto-detect URL params

---

### **2. form-keselamatan-operasi.php**

**Inline Notifications Found:**
```php
// Line 66-67: SESSION success
echo '<div class="alert alert-success" style="...">...</div>';

// Line 70-71: SESSION error
echo '<div class="alert alert-danger" style="...">...</div>';

// Line 74: GET status=sukses
echo '<div class="alert alert-success" style="...">Data berhasil disimpan!</div>';

// Line 77: GET status=gagal
echo '<div class="alert alert-danger" style="...">Gagal menyimpan data: ...</div>';
```

**Loading:**
```php
// Line 123: Has data-no-loading attribute
<form id="formKeselamatanOperasi" ... data-no-loading>
```

**Action:**
- ❌ Remove lines 63-79 (all inline notifications)
- ✅ Keep data-no-loading
- ✅ Global notification will auto-detect URL params

---

### **3. form-konsultasi-anestesi.php**

**Inline Notifications Found:**
```php
// Line 63-65: SESSION success (with animation)
echo '<div class="alert alert-success" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease-out;">...</div>';
echo '<script>setTimeout(function(){ ... }, 3000);</script>';

// Line 77-79: SESSION error (with animation)
echo '<div class="alert alert-danger" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease-out;">...</div>';
echo '<script>setTimeout(function(){ ... }, 3000);</script>';
```

**Loading:**
- ❌ No loading attribute

**Action:**
- ❌ Remove lines 60-82 (all inline notifications + scripts)
- ✅ Add data-no-loading if needed
- ✅ Global notification will handle animations

---

### **4. form-informed-consent-anestesi.php**

**Inline Notifications Found:**
```php
// Line 6-7: SESSION success
echo '<div class="alert alert-success">...</div>';

// Line 11-12: SESSION error
echo '<div class="alert alert-danger">...</div>';
```

**Loading:**
- ❌ No loading attribute

**Action:**
- ❌ Remove lines 3-13 (all inline notifications)
- ✅ Global notification will auto-detect

---

### **5. form-catatan-sedasi.php**

**Inline Notifications Found:**
```php
// Line 6-7: SESSION success
echo '<div class="alert alert-success">...</div>';

// Line 11-12: SESSION error
echo '<div class="alert alert-danger">...</div>';
```

**Loading:**
- ❌ No loading attribute

**Action:**
- ❌ Remove lines 3-13 (all inline notifications)
- ✅ Global notification will auto-detect

---

### **6. detail-pasien.php**

**Inline Notifications Found:**
```php
// Line 99-101: GET success
echo '<div class="alert alert-success" style="position: fixed; top: 90px; right: 20px; z-index: 9999; min-width: 300px;">...</div>';

// Line 104-106: GET error
echo '<div class="alert alert-danger" style="position: fixed; top: 90px; right: 20px; z-index: 9999; min-width: 300px;">...</div>';
```

**Loading:**
- ❌ No loading attribute

**Action:**
- ❌ Remove lines 96-107 (all inline notifications)
- ✅ Global notification will auto-detect

---

### **7. form-kamar-pemulihan.php** ✅

**Status:** Already migrated!

**Changes Made:**
- ✅ Inline notifications removed
- ✅ Using global notification system
- ✅ Has data-no-loading attribute
- ✅ Auto-detect URL params working

**No Action Needed!**

---

### **8. form-vital-sign.php** ❓

**Status:** Need to check

---

### **9. daftar-pasien.php** ❓

**Status:** Need to check

---

### **10. tambah-booking.php** ❓

**Status:** Need to check

---

## 🎯 **MIGRATION PLAN:**

### **Phase 1: Remove Inline Notifications**

**Files to Update:**
1. ✅ form-persiapan-operasi.php (remove lines 88-104)
2. ✅ form-keselamatan-operasi.php (remove lines 63-79)
3. ✅ form-konsultasi-anestesi.php (remove lines 60-82)
4. ✅ form-informed-consent-anestesi.php (remove lines 3-13)
5. ✅ form-catatan-sedasi.php (remove lines 3-13)
6. ✅ detail-pasien.php (remove lines 96-107)

### **Phase 2: Check Remaining Files**

**Files to Audit:**
1. ❓ form-vital-sign.php
2. ❓ daftar-pasien.php
3. ❓ tambah-booking.php

### **Phase 3: Update Submit Handlers**

**Ensure all submit handlers use correct redirect:**
```php
// Success
header("Location: index.php?page=form&status=sukses&action=saved");

// Error
header("Location: index.php?page=form&status=gagal&error=" . urlencode($error));
```

### **Phase 4: Test All Pages**

**Test checklist:**
- ✅ Notification shows on success
- ✅ Notification shows on error
- ✅ Loading shows on form submit
- ✅ No duplicate notifications
- ✅ Animations smooth
- ✅ Mobile responsive

---

## 📋 **CHECKLIST:**

### **Inline Notifications:**
- [ ] form-persiapan-operasi.php
- [ ] form-keselamatan-operasi.php
- [ ] form-konsultasi-anestesi.php
- [ ] form-informed-consent-anestesi.php
- [ ] form-catatan-sedasi.php
- [ ] detail-pasien.php
- [x] form-kamar-pemulihan.php (done)
- [ ] form-vital-sign.php (need check)
- [ ] daftar-pasien.php (need check)
- [ ] tambah-booking.php (need check)

### **Loading Skeleton:**
- [x] form-persiapan-operasi.php (has data-no-loading)
- [x] form-keselamatan-operasi.php (has data-no-loading)
- [x] form-kamar-pemulihan.php (has data-no-loading)
- [ ] form-konsultasi-anestesi.php (need to add)
- [ ] form-informed-consent-anestesi.php (need to add)
- [ ] form-catatan-sedasi.php (need to add)
- [ ] form-vital-sign.php (need check)
- [ ] daftar-pasien.php (need check)
- [ ] tambah-booking.php (need check)

---

## 🚨 **IMPORTANT NOTES:**

### **data-no-loading Attribute:**

Beberapa form sudah punya `data-no-loading`:
- form-persiapan-operasi.php
- form-keselamatan-operasi.php
- form-kamar-pemulihan.php

**Reason:** Form ini kemungkinan punya custom loading handling atau AutoSave feature yang conflict dengan global loading.

**Action:** **KEEP** attribute ini, jangan dihapus!

### **SESSION vs GET Parameters:**

**Old System:**
```php
$_SESSION['success'] = 'Data berhasil disimpan';
$_SESSION['error'] = 'Terjadi kesalahan';
```

**New System:**
```php
// Redirect with URL params
header("Location: index.php?page=form&status=sukses&action=saved");
header("Location: index.php?page=form&status=gagal&error=" . urlencode($error));
```

**Migration:** Update submit handlers to use URL params instead of SESSION.

---

## 📊 **STATISTICS:**

| Metric | Count |
|--------|-------|
| **Total Files** | 10 |
| **Files with Inline Notification** | 6 |
| **Files Already Migrated** | 1 (form-kamar-pemulihan.php) |
| **Files Need Migration** | 5 |
| **Files Need Audit** | 3 |
| **Total Inline Notifications** | ~20 blocks |
| **Estimated Time** | 30-45 minutes |

---

## 🎯 **NEXT STEPS:**

1. ✅ Audit remaining 3 files
2. ✅ Remove all inline notifications
3. ✅ Verify global notification works
4. ✅ Test all pages
5. ✅ Update documentation

---

**Last Updated:** 21 Oktober 2025  
**Status:** Audit Complete, Ready for Migration
