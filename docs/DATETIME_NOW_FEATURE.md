# DateTime Now Button Feature

## 📋 Overview
Fitur ini menambahkan tombol "Now" pada semua input tanggal, waktu, dan datetime yang secara otomatis mengisi input dengan waktu real-time saat ini.

## ✨ Features

### 1. **Automatic Detection**
- Otomatis mendeteksi semua input dengan type:
  - `type="date"` - Input tanggal
  - `type="time"` - Input waktu
  - `type="datetime-local"` - Input tanggal dan waktu

### 2. **Smart Formatting**
- **Date Input**: Format `YYYY-MM-DD` (contoh: 2025-11-03)
- **Time Input**: Format `HH:MM` (contoh: 09:52)
- **DateTime Input**: Format `YYYY-MM-DDTHH:MM` (contoh: 2025-11-03T09:52)

### 3. **Visual Design**
- Tombol biru dengan icon jam
- Posisi di kanan input field
- Hover effect dengan scale animation
- Click feedback (berubah hijau sebentar)

### 4. **Smart Integration**
- Tidak mengganggu input yang readonly atau disabled
- Bekerja dengan input-container yang sudah ada
- Otomatis menyesuaikan padding input
- Support untuk dynamic content (AJAX loaded forms)

## 🚀 Installation

### File yang Ditambahkan:
1. **JavaScript**: `/assets/js/datetime-now.js`
2. **Include di Header**: `/includes/header.php`

### Auto-Initialize:
Script akan otomatis berjalan saat:
- DOM ready
- New content ditambahkan (via MutationObserver)

## 💻 Usage

### Automatic (Recommended)
Tidak perlu konfigurasi apapun! Script akan otomatis mendeteksi dan menambahkan tombol "Now" ke semua input date/time yang tidak readonly.

### Manual Trigger (Optional)
```javascript
// Re-initialize untuk form yang baru ditambahkan
DateTimeNow.init();

// Set waktu sekarang ke input tertentu
const input = document.getElementById('myDateInput');
DateTimeNow.setNow(input);
```

## 🎨 Styling

### Default Style:
```css
.btn-now {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    padding: 5px 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
}
```

### Custom Styling:
Anda bisa override style dengan menambahkan CSS:
```css
.btn-now {
    background: #your-color !important;
    /* custom styles */
}
```

## 📝 Examples

### Example 1: Date Input
```html
<input type="date" id="tanggal" name="tanggal">
<!-- Tombol "Now" akan otomatis muncul -->
<!-- Click akan mengisi dengan: 2025-11-03 -->
```

### Example 2: Time Input
```html
<input type="time" id="jam" name="jam">
<!-- Tombol "Now" akan otomatis muncul -->
<!-- Click akan mengisi dengan: 09:52 -->
```

### Example 3: DateTime Input
```html
<input type="datetime-local" id="waktu" name="waktu">
<!-- Tombol "Now" akan otomatis muncul -->
<!-- Click akan mengisi dengan: 2025-11-03T09:52 -->
```

### Example 4: With Input Container
```html
<div class="input-container">
    <input type="date" id="tanggal" name="tanggal" placeholder=" ">
    <label for="tanggal" class="label-floating">Tanggal</label>
    <!-- Tombol "Now" akan ditambahkan di dalam input-container -->
</div>
```

## 🔧 Technical Details

### Browser Compatibility:
- ✅ Chrome/Edge (Modern)
- ✅ Firefox (Modern)
- ✅ Safari (Modern)
- ✅ Opera (Modern)

### Dependencies:
- Font Awesome (untuk icon clock)
- Modern JavaScript (ES6+)

### Performance:
- Lightweight (~5KB)
- No external dependencies
- Efficient MutationObserver
- Minimal DOM manipulation

## 🎯 Use Cases

### 1. Form Konsultasi Anestesi
- Tanggal konsultasi
- Jam visit
- Waktu pemeriksaan terakhir
- Jadwal puasa
- Jadwal operasi

### 2. Form Catatan Sedasi
- Tanggal tindakan
- Jam mulai anestesi
- Jam selesai anestesi
- Waktu pemberian obat

### 3. Form Vital Sign
- Waktu pengukuran
- Timestamp monitoring

### 4. Semua Form Lainnya
- Otomatis bekerja di semua form yang memiliki input date/time

## 🐛 Troubleshooting

### Tombol tidak muncul?
1. Cek apakah input readonly atau disabled
2. Pastikan script sudah di-load di header
3. Cek console untuk error JavaScript

### Format waktu salah?
1. Pastikan input type sudah benar (date/time/datetime-local)
2. Cek timezone browser

### Tombol overlap dengan elemen lain?
1. Adjust z-index di CSS
2. Adjust padding-right input jika perlu

## 📊 Testing

### Manual Testing:
1. Buka form apapun dengan input date/time
2. Lihat tombol "Now" di sebelah kanan input
3. Click tombol
4. Verifikasi waktu terisi dengan waktu sekarang
5. Submit form dan cek data tersimpan

### Expected Behavior:
- ✅ Tombol muncul di semua input date/time (kecuali readonly)
- ✅ Click mengisi dengan waktu real-time
- ✅ Format sesuai dengan type input
- ✅ Trigger change/input event
- ✅ Visual feedback saat click

## 🔄 Updates & Maintenance

### Version: 1.0.0
- Initial release
- Support date, time, datetime-local
- Auto-initialize
- MutationObserver for dynamic content

### Future Enhancements:
- [ ] Custom date/time format
- [ ] Timezone selection
- [ ] Keyboard shortcut (Ctrl+N)
- [ ] Configurable button position
- [ ] i18n support

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Cek dokumentasi ini
2. Cek console browser untuk error
3. Verifikasi file datetime-now.js sudah ter-load
4. Cek network tab untuk memastikan file tidak 404

---

**Created**: 2025-11-03  
**Author**: Cascade AI  
**Version**: 1.0.0
