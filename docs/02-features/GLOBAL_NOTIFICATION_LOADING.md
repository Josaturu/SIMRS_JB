# 🔔 Global Notification & Loading System

**Tanggal:** 21 Oktober 2025  
**Status:** ✅ **IMPLEMENTED**

---

## 🎯 **OVERVIEW:**

Sistem notifikasi dan loading global yang konsisten untuk semua halaman di aplikasi SIMRS.

### **Features:**
- ✅ **Global Notification** - Success, Error, Warning, Info
- ✅ **Loading Skeleton** - Page skeleton, Form skeleton, Saving skeleton
- ✅ **Auto-detect URL params** - Otomatis show notification dari URL
- ✅ **Modern animations** - Smooth slide-in/out animations
- ✅ **Responsive design** - Works on mobile & desktop
- ✅ **Easy to use** - Simple JavaScript API

---

## 📁 **FILES CREATED:**

```
assets/
├─ js/
│  ├─ notification.js          (Global notification system)
│  └─ loading-skeleton.js      (Loading skeleton system)
└─ css/
   ├─ notification.css         (Notification styles)
   └─ loading-skeleton.css     (Loading skeleton styles)

includes/
└─ header.php                  (Updated with includes)
```

---

## 🔔 **NOTIFICATION SYSTEM:**

### **Usage:**

```javascript
// Success notification
Notification.success('Data berhasil disimpan!');

// Error notification
Notification.error('Terjadi kesalahan saat menyimpan data.');

// Warning notification
Notification.warning('Harap isi semua field yang wajib!');

// Info notification
Notification.info('Data sedang diproses...');

// Custom duration (default: 4000ms)
Notification.success('Data disimpan!', 3000);

// Remove notification manually
Notification.remove();
```

### **Auto-detect from URL:**

Sistem otomatis mendeteksi parameter URL dan menampilkan notifikasi:

```
?status=sukses&action=saved    → ✅ Data berhasil disimpan
?status=sukses&action=updated  → ✅ Data berhasil diperbarui
?status=gagal&error=...        → ❌ Error message
?status=error&msg=...          → ❌ Error message
```

### **Notification Types:**

| Type | Icon | Color | Use Case |
|------|------|-------|----------|
| **success** | ✅ | Green | Data saved, action completed |
| **error** | ❌ | Red | Errors, failures |
| **warning** | ⚠️ | Orange | Warnings, cautions |
| **info** | ℹ️ | Blue | Information, tips |

---

## ⏳ **LOADING SKELETON SYSTEM:**

### **Usage:**

```javascript
// Show page skeleton
LoadingSkeleton.showPage();

// Show form loading
LoadingSkeleton.showForm();

// Show saving skeleton
LoadingSkeleton.showSaving('Menyimpan data...');

// Hide skeleton
LoadingSkeleton.hide();

// Show then auto-hide
LoadingSkeleton.showThenHide('page', 1000); // Hide after 1s
```

### **Auto-show on Page Load:**

Add attributes to `<body>` tag:

```html
<body data-show-skeleton data-skeleton-type="page" data-skeleton-duration="800">
```

**Attributes:**
- `data-show-skeleton` - Enable auto-show
- `data-skeleton-type` - Type: 'page', 'form', 'saving'
- `data-skeleton-duration` - Duration in ms (default: 800)

### **Auto-show on Form Submit:**

Skeleton otomatis muncul saat form di-submit.

**Disable for specific form:**
```html
<form data-no-loading>
    <!-- Form will not show loading skeleton -->
</form>
```

---

## 🎨 **DESIGN:**

### **Notification Design:**
- Modern card design with shadow
- Slide-in animation from right
- Auto-dismiss after duration
- Manual close button
- Color-coded by type
- Responsive (mobile-friendly)

### **Loading Skeleton Design:**
- Shimmer animation effect
- Realistic page structure
- Smooth transitions
- Backdrop blur effect
- Spinner with text

---

## 📝 **IMPLEMENTATION GUIDE:**

### **Step 1: Already Included in header.php**

```php
<!-- In header.php -->
<link rel="stylesheet" href="assets/css/notification.css">
<link rel="stylesheet" href="assets/css/loading-skeleton.css">
<script src="assets/js/notification.js"></script>
<script src="assets/js/loading-skeleton.js"></script>
```

✅ **All pages automatically have access to the system!**

### **Step 2: Use in Your Forms**

#### **Option A: Use URL Parameters (Recommended)**

In your submit handler:
```php
// Success
header("Location: index.php?page=form&status=sukses&action=saved");

// Error
header("Location: index.php?page=form&status=gagal&error=" . urlencode($error));
```

Notification will automatically show!

#### **Option B: Use JavaScript**

In your page:
```javascript
// After AJAX success
Notification.success('Data berhasil disimpan!');

// After AJAX error
Notification.error('Gagal menyimpan data.');
```

### **Step 3: Add Loading Skeleton**

#### **For Page Load:**

```html
<body data-show-skeleton data-skeleton-type="page" data-skeleton-duration="800">
```

#### **For Form Submit:**

Loading automatically shows on submit. To disable:
```html
<form data-no-loading>
```

#### **Manual Control:**

```javascript
// Before AJAX
LoadingSkeleton.showSaving('Menyimpan data...');

// After AJAX
LoadingSkeleton.hide();
```

---

## 🔧 **CUSTOMIZATION:**

### **Change Notification Duration:**

```javascript
// Default: 4000ms
Notification.success('Message', 5000); // 5 seconds

// No auto-dismiss
Notification.success('Message', 0); // Must close manually
```

### **Change Loading Message:**

```javascript
LoadingSkeleton.showSaving('Memproses data...');
LoadingSkeleton.showSaving('Mengirim email...');
LoadingSkeleton.showSaving('Mengupload file...');
```

### **Custom Skeleton Type:**

```javascript
// Page skeleton (full page structure)
LoadingSkeleton.showPage();

// Form skeleton (spinner overlay)
LoadingSkeleton.showForm();

// Saving skeleton (spinner with message)
LoadingSkeleton.showSaving();
```

---

## 📊 **EXAMPLES:**

### **Example 1: Form Submit with Notification**

```php
// submit-form.php
if ($stmt->execute()) {
    header("Location: index.php?page=form&status=sukses&action=saved");
} else {
    $error = $stmt->errorInfo()[2];
    header("Location: index.php?page=form&status=gagal&error=" . urlencode($error));
}
```

Result:
- ✅ Form submits
- ⏳ Loading skeleton shows
- ✅ Redirect to form
- 🔔 Notification shows automatically

### **Example 2: AJAX with Manual Control**

```javascript
// Show loading
LoadingSkeleton.showSaving('Menyimpan data...');

// AJAX request
fetch('api/save.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    // Hide loading
    LoadingSkeleton.hide();
    
    // Show notification
    if (data.success) {
        Notification.success('Data berhasil disimpan!');
    } else {
        Notification.error(data.message);
    }
})
.catch(error => {
    LoadingSkeleton.hide();
    Notification.error('Terjadi kesalahan: ' + error.message);
});
```

### **Example 3: Page Load with Skeleton**

```html
<!-- In your page -->
<body data-show-skeleton data-skeleton-type="page" data-skeleton-duration="1000">
    <!-- Page content -->
</body>
```

Result:
- ⏳ Skeleton shows immediately
- 📄 Page loads in background
- ✅ Skeleton fades out after 1s
- 🎉 Page content appears

---

## 🎯 **BENEFITS:**

### **1. Consistency**
- ✅ Same notification style across all pages
- ✅ Same loading animation everywhere
- ✅ Unified user experience

### **2. Easy to Use**
- ✅ Simple JavaScript API
- ✅ Auto-detect URL parameters
- ✅ No complex setup needed

### **3. Modern Design**
- ✅ Beautiful animations
- ✅ Smooth transitions
- ✅ Professional look

### **4. Better UX**
- ✅ Clear feedback to users
- ✅ Loading states visible
- ✅ Error messages clear

### **5. Maintainability**
- ✅ Centralized system
- ✅ Easy to update
- ✅ Reusable components

---

## 🧪 **TESTING:**

### **Test Notification:**

Open browser console and run:
```javascript
// Test success
Notification.success('Test success notification!');

// Test error
Notification.error('Test error notification!');

// Test warning
Notification.warning('Test warning notification!');

// Test info
Notification.info('Test info notification!');
```

### **Test Loading:**

```javascript
// Test page skeleton
LoadingSkeleton.showPage();
setTimeout(() => LoadingSkeleton.hide(), 2000);

// Test form skeleton
LoadingSkeleton.showForm();
setTimeout(() => LoadingSkeleton.hide(), 2000);

// Test saving skeleton
LoadingSkeleton.showSaving('Testing...');
setTimeout(() => LoadingSkeleton.hide(), 2000);
```

### **Test URL Parameters:**

Navigate to:
```
index.php?page=form&status=sukses&action=saved
index.php?page=form&status=gagal&error=Test+error
```

---

## 📱 **RESPONSIVE:**

### **Desktop:**
- Notification: Top-right corner
- Loading: Full screen overlay
- Animations: Smooth slide-in

### **Mobile:**
- Notification: Full width at top
- Loading: Full screen overlay
- Animations: Optimized for mobile

---

## 🔄 **MIGRATION GUIDE:**

### **Remove Old Inline Notifications:**

**Before:**
```php
<?php if ($_GET['status'] === 'sukses'): ?>
    <div class="alert alert-success">
        Data berhasil disimpan!
    </div>
<?php endif; ?>
```

**After:**
```php
<!-- Nothing needed! System auto-detects URL params -->
```

### **Remove Old Loading Spinners:**

**Before:**
```html
<div id="loading" style="display:none;">
    Loading...
</div>
<script>
    document.getElementById('loading').style.display = 'block';
</script>
```

**After:**
```javascript
LoadingSkeleton.showSaving();
```

---

## 📚 **API REFERENCE:**

### **Notification API:**

| Method | Parameters | Description |
|--------|------------|-------------|
| `show(message, type, duration)` | message, type, duration | Show notification |
| `success(message, duration)` | message, duration | Show success |
| `error(message, duration)` | message, duration | Show error |
| `warning(message, duration)` | message, duration | Show warning |
| `info(message, duration)` | message, duration | Show info |
| `remove()` | - | Remove notification |
| `checkUrlParams()` | - | Check URL and show notification |

### **LoadingSkeleton API:**

| Method | Parameters | Description |
|--------|------------|-------------|
| `showPage()` | - | Show page skeleton |
| `showForm()` | - | Show form skeleton |
| `showSaving(message)` | message | Show saving skeleton |
| `hide()` | - | Hide skeleton |
| `showThenHide(type, duration)` | type, duration | Show then auto-hide |

---

## ✅ **CHECKLIST:**

- ✅ notification.js created
- ✅ loading-skeleton.js created
- ✅ notification.css created
- ✅ loading-skeleton.css created
- ✅ header.php updated
- ✅ Auto-detect URL params
- ✅ Auto-show on form submit
- ✅ Responsive design
- ✅ Documentation complete
- ✅ Synced to XAMPP

---

## 🚀 **NEXT STEPS:**

1. **Test on all forms** - Verify notifications work
2. **Remove old inline notifications** - Clean up code
3. **Add to new forms** - Use in future development
4. **Customize as needed** - Adjust colors, durations, etc.

---

**Last Updated:** 21 Oktober 2025  
**Status:** ✅ **READY TO USE**  
**Available:** All pages automatically

---

**🎉 GLOBAL NOTIFICATION & LOADING SYSTEM READY! 🎉**
