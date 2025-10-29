# PDF Generator: Catatan Kamar Pemulihan (RMOK-30)

**File:** `process/pdf/pdf-kamar-pemulihan.php`  
**Form:** `views/form-kamar-pemulihan.php`  
**Database:** `tbl_anestesi_kamar_pemulihan`, `tbl_anestesi_vital_pemulihan`

---

## 📋 Overview

PDF ini menampilkan catatan lengkap kamar pemulihan pasien setelah operasi, termasuk:
- Data pasien dan booking operasi
- Kondisi masuk (jalan nafas, pernapasan, kesadaran)
- Monitoring vital sign (tabel dengan grafik)
- Terapi dan tindakan
- Data keluar
- Scoring systems (Aldrete, Bromage, Steward)
- Tanda tangan penanggung jawab

---

## 🎨 Design Features

### **1. Modern Layout**
- Gradient blue header
- Color-coded sections
- Responsive grid layout
- Professional typography

### **2. Data Visualization**
- Vital sign table dengan alternating row colors
- Score boxes dengan highlight
- Checkbox dengan icon ☑/☐
- Info boxes untuk catatan khusus

### **3. Print-Ready**
- A4 size optimized
- Proper margins (10mm)
- Page break handling
- Print controls (hide on print)

---

## 📊 Database Structure

### **Tabel: `tbl_anestesi_kamar_pemulihan`**

```sql
CREATE TABLE `tbl_anestesi_kamar_pemulihan` (
  `id` varchar(36) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  
  -- Data Masuk
  `jam_masuk` time DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL,
  
  -- Kondisi Pasien
  `jalan_nafas_bersih` tinyint(1) DEFAULT 0,
  `pernapasan_spontan` tinyint(1) DEFAULT 0,
  `pernapasan_dibantu` tinyint(1) DEFAULT 0,
  `spontan_adekuat` tinyint(1) DEFAULT 0,
  `spontan_penyumbatan` tinyint(1) DEFAULT 0,
  `spontan_alat` tinyint(1) DEFAULT 0,
  `kesadaran_sadar` tinyint(1) DEFAULT 0,
  `kesadaran_belum_sadar` tinyint(1) DEFAULT 0,
  `kesadaran_tidur_dalam` tinyint(1) DEFAULT 0,
  
  -- Pemantauan
  `pemantauan_setiap` varchar(50) DEFAULT NULL,
  `pemantauan_selama` varchar(50) DEFAULT NULL,
  
  -- Terapi
  `analgesia` varchar(150) DEFAULT NULL,
  `anti_muntah` varchar(150) DEFAULT NULL,
  `antibiotik` varchar(150) DEFAULT NULL,
  `posisi_pasien` varchar(150) DEFAULT NULL,
  `obat_lain` text DEFAULT NULL,
  `diet_nutrisi` varchar(150) DEFAULT NULL,
  `lain_lain` text DEFAULT NULL,
  
  -- Data Keluar
  `jam_keluar` time DEFAULT NULL,
  `td_keluar` varchar(20) DEFAULT NULL,
  `n_keluar` varchar(20) DEFAULT NULL,
  `r_keluar` varchar(20) DEFAULT NULL,
  `s_keluar` varchar(20) DEFAULT NULL,
  `spo2_keluar` varchar(20) DEFAULT NULL,
  `skrining_nyeri` enum('ya','tidak') DEFAULT NULL,
  `tujuan_keluar` enum('ruang_rawat','icu','pulang') DEFAULT NULL,
  `catatan_khusus` text DEFAULT NULL,
  
  -- Scoring
  `aldrete_score` int(11) DEFAULT NULL,
  `bromage_score` int(11) DEFAULT NULL,
  `steward_score` int(11) DEFAULT NULL,
  
  -- Penanggung Jawab
  `nama_penanggungjawab` varchar(150) DEFAULT NULL,
  `perawat_menyerahkan` varchar(100) DEFAULT NULL,
  `perawat_menerima` varchar(100) DEFAULT NULL,
  `dokter_anestesi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **Tabel: `tbl_anestesi_vital_pemulihan`**

```sql
CREATE TABLE `tbl_anestesi_vital_pemulihan` (
  `id` varchar(36) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `id_pemulihan` varchar(36) DEFAULT NULL,
  `waktu_label` varchar(50) DEFAULT NULL,
  `nadi` int(11) DEFAULT NULL,
  `sistol` int(11) DEFAULT NULL,
  `diastol` int(11) DEFAULT NULL,
  `respirasi` int(11) DEFAULT NULL,
  `nyeri` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔧 Cara Menggunakan

### **1. Dari Form Kamar Pemulihan**

```javascript
// Klik tombol "Cetak PDF"
<button onclick="cetakPDF()">
    <i class="fas fa-file-pdf"></i> Cetak PDF
</button>

// Function
function cetakPDF() {
    const url = `/process/pdf/pdf-kamar-pemulihan.php?no_rawat=${no_rawat}&kode_paket=${kode_paket}&tanggal=${tanggal}&jam_mulai=${jam_mulai}`;
    window.open(url, '_blank');
}
```

### **2. Direct URL**

```
http://localhost/process/pdf/pdf-kamar-pemulihan.php?no_rawat=1&kode_paket=1&tanggal=2025-02-01&jam_mulai=23:59:00
```

---

## 📄 PDF Layout

### **Section 1: Header**
```
┌────────────────────────────────────────────┐
│ [RS Logo]                                  │
│ CATATAN KAMAR PEMULIHAN                    │
│ Rumah Sakit Prasetya Bunda                 │
│ [RMOK - 30]                                │
└────────────────────────────────────────────┘
```

### **Section 2: Patient Info**
```
┌────────────────────────────────────────────┐
│ 📋 Informasi Pasien & Data Booking        │
├────────────────────────────────────────────┤
│ Nama: dadang beton                         │
│ No. RM: 1          No. Rawat: 1            │
│ Tgl Lahir: 1995-10-26  Umur: 30 Tahun      │
│ Jenis Kelamin: Laki-laki                   │
│ Kode Paket: 1      Tanggal: 2025-02-01     │
│ Jam Mulai: 23:59:00                        │
└────────────────────────────────────────────┘
```

### **Section 3: Data Masuk**
```
┌────────────────────────────────────────────┐
│ Data Masuk dan Kondisi Pasien             │
├────────────────────────────────────────────┤
│ Jam Masuk: 11:00:00                        │
│ Tanggal Masuk: 2025-01-13                  │
│                                            │
│ Jalan Nafas:                               │
│ ☑ Bersih & lapang                          │
│                                            │
│ Pernapasan:                                │
│ ☑ Spontan  ☑ Dibantu                       │
│                                            │
│ Bila spontan:                              │
│ ☑ Adekuat Bersuara                         │
│ ☑ Penyumbatan                              │
│ ☑ Membutuhkan alat                         │
│                                            │
│ Kesadaran:                                 │
│ ☑ Sadar betul                              │
│ ☑ Belum sadar betul                        │
│ ☑ Tidur dalam                              │
└────────────────────────────────────────────┘
```

### **Section 4: Vital Sign Monitoring**
```
┌────────────────────────────────────────────┐
│ 📊 Monitoring Vital Sign                  │
├────────────────────────────────────────────┤
│ Waktu    │ RR │ Nadi │ Sistol │ Diastol │ │
│──────────┼────┼──────┼────────┼─────────┤ │
│ 14:12:41 │ 22 │  27  │   21   │   34    │ │
│ 14:13:10 │ 60 │  20  │   99   │   81    │ │
│ 14:13:38 │ 60 │  44  │   63   │   12    │ │
│                                            │
│ Pemantauan Setiap: 3 Menit                 │
│ Pemantauan Selama: 9 menit                 │
└────────────────────────────────────────────┘
```

### **Section 5: Terapi**
```
┌────────────────────────────────────────────┐
│ 💊 Terapi dan Tindakan                    │
├────────────────────────────────────────────┤
│ Analgesia     : iqwj oidjqoi               │
│ Anti Muntah   : ij owiqjf oqiwjfoqwij      │
│ Antibiotik    : i jwqoidjqoi jqoi          │
│ Posisi Pasien : ioj oiqwjoiq joij          │
│ Obat Lain     : oij owiqjdoqiwjd           │
│ Diet/Nutrisi  : oi jqwoidj qoij            │
│ Lain-lain     : oid wjoqid jqwoij          │
└────────────────────────────────────────────┘
```

### **Section 6: Data Keluar**
```
┌────────────────────────────────────────────┐
│ 🚪 Data Keluar Kamar Pemulihan            │
├────────────────────────────────────────────┤
│ Jam Keluar: 18:13:00                       │
│ Skrining Nyeri: YA                         │
│                                            │
│ Vital Sign Saat Keluar:                    │
│ TD: 90 mmHg    Nadi: 40 x/menit            │
│ RR: 120 x/menit  Suhu: 90 °C               │
│ SpO2: 109 %                                │
│ Tujuan: Ruang Rawat                        │
│                                            │
│ 📝 Catatan Khusus:                         │
│ fk woij qowijoqiwj oiqwj oqwijoiqj         │
└────────────────────────────────────────────┘
```

### **Section 7: Scoring**
```
┌────────────────────────────────────────────┐
│ 📈 Scoring Systems                        │
├────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐       │
│  │ Aldrete Score│  │ Bromage Score│       │
│  │      32      │  │      18      │       │
│  └──────────────┘  └──────────────┘       │
│         ┌──────────────┐                   │
│         │ Steward Score│                   │
│         │      26      │                   │
│         └──────────────┘                   │
└────────────────────────────────────────────┘
```

### **Section 8: Tanda Tangan**
```
┌────────────────────────────────────────────┐
│ 👥 Penanggung Jawab                       │
├────────────────────────────────────────────┤
│ Nama: q duq iuqwh iuhiuqh iuqh             │
│                                            │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│ │ Perawat  │ │ Perawat  │ │  Dokter  │   │
│ │Menyerahkan│ │ Menerima │ │ Anestesi │   │
│ │          │ │          │ │          │   │
│ │          │ │          │ │          │   │
│ │perawat2  │ │perawat1  │ │ dokter2  │   │
│ └──────────┘ └──────────┘ └──────────┘   │
└────────────────────────────────────────────┘
```

---

## 🎯 Features

### **1. Comprehensive Data**
- ✅ Patient demographics
- ✅ Admission data
- ✅ Vital signs monitoring (table)
- ✅ Therapy and interventions
- ✅ Discharge data
- ✅ Scoring systems
- ✅ Signatures

### **2. Visual Elements**
- ✅ Color-coded sections
- ✅ Checkbox indicators
- ✅ Score highlight boxes
- ✅ Info boxes for notes
- ✅ Professional table styling

### **3. Print Optimization**
- ✅ A4 size
- ✅ Proper margins
- ✅ Page break handling
- ✅ Print/Close buttons (hidden on print)

---

## 🧪 Testing

### **Test 1: Complete Data**
```
1. Isi form kamar pemulihan lengkap
2. Input vital signs (minimal 3 records)
3. Klik "Cetak PDF"
4. Expected:
   ✅ PDF terbuka di tab baru
   ✅ Semua data tampil
   ✅ Vital sign table lengkap
   ✅ Scoring tampil
```

### **Test 2: Partial Data**
```
1. Isi hanya data wajib
2. Klik "Cetak PDF"
3. Expected:
   ✅ PDF tetap terbuka
   ✅ Field kosong tampil "-"
   ✅ Tidak ada error
```

### **Test 3: No Vital Signs**
```
1. Tidak ada data vital sign
2. Klik "Cetak PDF"
3. Expected:
   ✅ Info box: "Belum ada data vital sign"
   ✅ Section lain tetap tampil
```

### **Test 4: Print**
```
1. Buka PDF
2. Klik "Cetak PDF" button
3. Expected:
   ✅ Print dialog muncul
   ✅ Button cetak/tutup hilang
   ✅ Layout rapi
```

---

## 📝 Helper Functions

### **displayValue()**
```php
function displayValue($value, $default = '-') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}
```

### **isChecked()**
```php
function isChecked($value) {
    return !empty($value) && $value == 1 ? '☑' : '☐';
}
```

### **formatJenisKelamin()**
```php
function formatJenisKelamin($jk) {
    return $jk == 'L' ? 'Laki-laki' : ($jk == 'P' ? 'Perempuan' : '-');
}
```

---

## 🔍 Troubleshooting

### **Issue 1: Data tidak tampil**
**Cause:** Data belum disimpan ke database  
**Solution:** Simpan form dulu sebelum cetak PDF

### **Issue 2: Vital sign kosong**
**Cause:** Belum input vital sign  
**Solution:** Input minimal 1 vital sign record

### **Issue 3: PDF blank**
**Cause:** Parameter tidak lengkap  
**Solution:** Pastikan ada no_rawat, kode_paket, tanggal, jam_mulai

---

## 💡 Tips

### **1. Best Practice**
```
Workflow:
1. Isi form kamar pemulihan
2. Input vital signs (minimal 3x)
3. Simpan data
4. Cetak PDF untuk dokumentasi
```

### **2. Data Validation**
```
Sebelum cetak:
- Pastikan data sudah disimpan
- Cek vital signs sudah diinput
- Verifikasi scoring sudah diisi
```

### **3. Print Settings**
```
Recommended:
- Paper: A4
- Orientation: Portrait
- Margins: Default
- Scale: 100%
```

---

## 📚 Related Files

1. **`views/form-kamar-pemulihan.php`** - Form input
2. **`process/submit-kamar-pemulihan.php`** - Save handler
3. **`process/pdf/pdf-kamar-pemulihan.php`** - PDF generator
4. **Database:**
   - `tbl_anestesi_kamar_pemulihan` - Main data
   - `tbl_anestesi_vital_pemulihan` - Vital signs

---

**Status:** ✅ Ready to use  
**Version:** 1.0  
**Last Updated:** 30 Oktober 2025
