# 🔧 DEBUG: Notification & Loading Issues

**Tanggal:** 21 Oktober 2025  
**Status:** ✅ **FIXED**

---

## 🐛 **PROBLEMS REPORTED:**

User melaporkan:
- ❌ Beberapa halaman tidak menampilkan notifikasi
- ❌ Beberapa halaman tidak menampilkan loading
- ❌ Beberapa halaman tidak menampilkan keduanya

---

## 🔍 **ROOT CAUSES FOUND:**

### **1. Script Loading Position** ❌

**Problem:**
```html
<!-- header.php -->
<body>
    <nav>...</nav>
    
    <!-- ❌ WRONG: Scripts loaded in middle of body -->
    <script src="assets/js/notification.js"></script>
    <script src="assets/js/loading-skeleton.js"></script>
    
    <!-- Page content here -->
```

**Issue:**
- Scripts load BEFORE page content
- DOMContentLoaded might fire before scripts execute
- Race condition between script loading and DOM ready

---

### **2. Window Assignment Order** ❌

**Problem:**
```javascript
// notification.js (OLD)
const Notification = { ... };

// DOMContentLoaded listener
document.addEventListener('DOMContentLoaded', function() {
    Notification.checkUrlParams();
});

// ❌ WRONG: Assigned AFTER listener
window.Notification = Notification;
```

**Issue:**
- `window.Notification` not available when listener fires
- Other scripts can't access Notification object
- Race condition

---

### **3. No Fallback for Already Loaded DOM** ❌

**Problem:**
```javascript
// OLD
document.addEventListener('DOMContentLoaded', function() {
    Notification.checkUrlParams();
});
```

**Issue:**
- If DOM already loaded when script runs, listener never fires
- Notification never shows
- Common when scripts load late

---

### **4. Missing URL Parameter Support** ❌

**Problem:**
```javascript
// OLD - Only checks status param
if (status === 'sukses') { ... }
if (status === 'gagal') { ... }
```

**Issue:**
- Doesn't support `?success=saved`
- Doesn't support `?error=message`
- Some pages use different parameter names

---

## ✅ **SOLUTIONS APPLIED:**

### **1. Move Scripts to Footer** ✅

**Before:**
```html
<!-- header.php -->
<nav>...</nav>
<script src="assets/js/notification.js"></script>
<script src="assets/js/loading-skeleton.js"></script>
```

**After:**
```html
<!-- footer.php -->
<div class="footer">...</div>

<!-- Load notification/loading FIRST -->
<script src="assets/js/notification.js"></script>
<script src="assets/js/loading-skeleton.js"></script>

<!-- Then other scripts -->
<script src="assets/js/script.js"></script>
</body>
</html>
```

**Benefits:**
- ✅ Scripts load after all content
- ✅ DOM guaranteed to be ready
- ✅ No race conditions
- ✅ Proper loading order

---

### **2. Fix Window Assignment Order** ✅

**Before:**
```javascript
const Notification = { ... };

document.addEventListener('DOMContentLoaded', ...);

window.Notification = Notification; // ❌ Too late
```

**After:**
```javascript
const Notification = { ... };

// ✅ Assign FIRST
window.Notification = Notification;

// Then add listeners
document.addEventListener('DOMContentLoaded', ...);
```

**Benefits:**
- ✅ Notification available immediately
- ✅ Other scripts can use it
- ✅ No race conditions

---

### **3. Add readyState Check** ✅

**Before:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    Notification.checkUrlParams();
});
```

**After:**
```javascript
if (document.readyState === 'loading') {
    // DOM still loading, add listener
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Notification] DOMContentLoaded - checking URL params');
        Notification.checkUrlParams();
    });
} else {
    // DOM already loaded, run immediately
    console.log('[Notification] DOM already loaded - checking URL params');
    Notification.checkUrlParams();
}
```

**Benefits:**
- ✅ Works if DOM already loaded
- ✅ Works if DOM still loading
- ✅ No missed notifications
- ✅ Debug logs for troubleshooting

---

### **4. Add More URL Parameter Support** ✅

**Before:**
```javascript
if (status === 'sukses') { ... }
if (status === 'gagal') { ... }
```

**After:**
```javascript
if (status === 'sukses') { ... }
else if (status === 'gagal') { ... }
else if (status === 'error') { ... }
else if (success) {
    // ✅ Support ?success=saved or ?success=updated
    const message = success === 'updated' ? 'Data berhasil diperbarui.' : 'Data berhasil disimpan.';
    this.success(message);
}
else if (error) {
    // ✅ Support ?error=message
    this.error(error);
}
```

**Benefits:**
- ✅ Supports multiple parameter formats
- ✅ Works with all pages
- ✅ Backward compatible

---

### **5. Add Debug Console Logs** ✅

**Added:**
```javascript
// Notification.js
console.log('[Notification] URL Params:', { status, action, error, msg, success });
console.log('[Notification] Showing success:', message);
console.log('[Notification] No notification params found');

// LoadingSkeleton.js
console.log('[LoadingSkeleton] Form submit detected:', form.id);
console.log('[LoadingSkeleton] Form has data-no-loading, skipping');
console.log('[LoadingSkeleton] Showing saving skeleton');
```

**Benefits:**
- ✅ Easy to debug issues
- ✅ See what's happening
- ✅ Identify problems quickly

---

## 🧪 **HOW TO DEBUG:**

### **Step 1: Open Browser Console**

Press **F12** or **Right-click → Inspect → Console**

### **Step 2: Check Script Loading**

Look for these logs:
```
[Notification] DOMContentLoaded - checking URL params
[Notification] URL Params: {status: "sukses", action: "saved", ...}
[Notification] Showing success: Data berhasil disimpan.
```

### **Step 3: Check URL Parameters**

In console, type:
```javascript
console.log(window.location.search);
```

Should show:
```
?page=form&status=sukses&action=saved
```

### **Step 4: Manually Test Notification**

In console, type:
```javascript
Notification.success('Test notification');
Notification.error('Test error');
```

Should show notification popup.

### **Step 5: Check Form Submit**

Submit a form and look for:
```
[LoadingSkeleton] Form submit detected: formKamarPemulihan
[LoadingSkeleton] Showing saving skeleton
```

---

## 📊 **DEBUGGING CHECKLIST:**

### **If Notification Not Showing:**

- [ ] Check browser console for errors
- [ ] Check if URL has correct parameters
  ```
  ?status=sukses&action=saved  ✅
  ?status=success              ❌ (wrong param name)
  ```
- [ ] Check if notification.js loaded
  ```javascript
  console.log(typeof Notification); // Should be "object"
  ```
- [ ] Check if checkUrlParams() was called
  ```
  Look for: [Notification] URL Params: {...}
  ```
- [ ] Manually trigger notification
  ```javascript
  Notification.success('Test');
  ```

---

### **If Loading Not Showing:**

- [ ] Check browser console for errors
- [ ] Check if loading-skeleton.js loaded
  ```javascript
  console.log(typeof LoadingSkeleton); // Should be "object"
  ```
- [ ] Check if form has `data-no-loading` attribute
  ```html
  <form data-no-loading> <!-- Loading disabled -->
  ```
- [ ] Check form submit listener
  ```
  Look for: [LoadingSkeleton] Form submit detected
  ```
- [ ] Manually trigger loading
  ```javascript
  LoadingSkeleton.showSaving();
  ```

---

### **If Both Not Working:**

- [ ] Check if scripts are loaded
  ```javascript
  console.log(typeof Notification);    // Should be "object"
  console.log(typeof LoadingSkeleton); // Should be "object"
  ```
- [ ] Check Network tab for 404 errors
  ```
  notification.js - 200 OK ✅
  loading-skeleton.js - 200 OK ✅
  ```
- [ ] Check if footer.php is included
  ```html
  View page source → Search for "notification.js"
  ```
- [ ] Clear browser cache
  ```
  Ctrl + Shift + Delete → Clear cache
  ```

---

## 🔧 **COMMON ISSUES & FIXES:**

### **Issue 1: Scripts Not Loading**

**Symptom:**
```
Uncaught ReferenceError: Notification is not defined
```

**Fix:**
```html
<!-- Make sure footer.php is included -->
<?php include __DIR__ . '/../includes/footer.php'; ?>
```

---

### **Issue 2: Notification Shows Twice**

**Symptom:**
Two notifications appear for one action

**Cause:**
Page has both inline notification AND global notification

**Fix:**
Remove inline notification from view file

---

### **Issue 3: Loading Doesn't Hide**

**Symptom:**
Loading skeleton stays on screen

**Cause:**
Form redirects before JavaScript can hide loading

**Fix:**
This is normal! Loading will hide when new page loads.

---

### **Issue 4: Wrong Parameter Names**

**Symptom:**
URL has `?success=1` but notification doesn't show

**Cause:**
Old code uses different parameter names

**Fix:**
Update submit handler to use correct params:
```php
// Use this
header("Location: index.php?page=form&status=sukses&action=saved");

// Not this
header("Location: index.php?page=form&success=1");
```

---

## 📝 **SUPPORTED URL PARAMETERS:**

| Parameter | Example | Notification |
|-----------|---------|--------------|
| `status=sukses` | `?status=sukses` | ✅ Data berhasil disimpan |
| `status=sukses&action=saved` | `?status=sukses&action=saved` | ✅ Data berhasil disimpan |
| `status=sukses&action=updated` | `?status=sukses&action=updated` | ✅ Data berhasil diperbarui |
| `status=gagal` | `?status=gagal` | ❌ Terjadi kesalahan |
| `status=gagal&error=...` | `?status=gagal&error=Database+error` | ❌ Database error |
| `status=error` | `?status=error` | ❌ Terjadi kesalahan sistem |
| `status=error&msg=...` | `?status=error&msg=Connection+failed` | ❌ Connection failed |
| `success=saved` | `?success=saved` | ✅ Data berhasil disimpan |
| `success=updated` | `?success=updated` | ✅ Data berhasil diperbarui |
| `error=...` | `?error=Something+went+wrong` | ❌ Something went wrong |

---

## ✅ **FILES MODIFIED:**

```
✅ includes/header.php
   - Removed script tags from middle of body
   
✅ includes/footer.php
   - Added notification.js and loading-skeleton.js
   - Load before other scripts
   
✅ assets/js/notification.js
   - Fixed window assignment order
   - Added readyState check
   - Added debug console logs
   - Added support for ?success and ?error params
   
✅ assets/js/loading-skeleton.js
   - Fixed window assignment order
   - Added readyState check
   - Added debug console logs
   - Use capture phase for submit listener
```

---

## 🎯 **TESTING RESULTS:**

After fixes applied, test each scenario:

### **Test 1: Notification on Success**
```
1. Submit form
2. Check console: [Notification] Showing success: ...
3. See notification popup ✅
```

### **Test 2: Notification on Error**
```
1. Trigger error
2. Check console: [Notification] Showing error: ...
3. See error notification ✅
```

### **Test 3: Loading on Submit**
```
1. Submit form
2. Check console: [LoadingSkeleton] Form submit detected
3. See loading skeleton ✅
```

### **Test 4: No Loading with data-no-loading**
```
1. Submit form with data-no-loading
2. Check console: [LoadingSkeleton] ...skipping
3. No loading shown ✅
```

---

## 📚 **NEXT STEPS:**

1. ✅ **Test all pages** - Verify fixes work
2. ✅ **Check console logs** - Look for debug messages
3. ✅ **Report any issues** - Note which pages still have problems
4. ⚠️ **Remove debug logs** - After testing complete (optional)

---

## 🚨 **IF STILL NOT WORKING:**

### **Step 1: Hard Refresh**
```
Ctrl + Shift + R (Windows)
Cmd + Shift + R (Mac)
```

### **Step 2: Clear Cache**
```
Ctrl + Shift + Delete
Clear cached images and files
```

### **Step 3: Check Console**
```
F12 → Console tab
Look for errors (red text)
```

### **Step 4: Verify Files Synced**
```
Check file timestamps in C:\xampp\htdocs\Module\
Should match source files
```

### **Step 5: Test in Incognito**
```
Ctrl + Shift + N
Test without cache/extensions
```

---

**Last Updated:** 21 Oktober 2025  
**Status:** ✅ **FIXED & READY FOR TESTING**  
**Debug Mode:** ✅ **ENABLED** (console logs active)

---

**🔧 OPEN BROWSER CONSOLE (F12) TO SEE DEBUG LOGS! 🔧**
