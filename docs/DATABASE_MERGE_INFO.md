# Database Merge Information
**Tanggal**: 28 Oktober 2025

## Tabel yang Diambil dari `dbanestesi (3).sql` (Downloads)

### 1. tbl_anestesi_konsultasi_anestesi ✅
- **Alasan**: Versi terbaru dengan field `sedasi` dan struktur lengkap
- **Fitur Baru**:
  - Field `sedasi` TEXT (untuk catatan sedasi)
  - Field `has_pengobatan` ENUM
  - Field `jenis_diagnosa` dengan comment
  - Field `jalan_nafas_keterangan` (keterangan abnormal)
  - Field `lain_lain_diagnosis` (renamed dari rekomendasi_anestesi)

### 2. tbl_anestesi_informed_consent_anestesi ✅
- **Alasan**: Versi terbaru dengan field tambahan
- **Fitur Baru**:
  - Field `status_fisik` TEXT
  - Field `prognosis` TEXT
  - Field `alternatif_resiko` TEXT
  - Field `lain_lain` TEXT
  - Field `checkbox_confirm` VARCHAR(20)

### 3. tbl_anestesi_catatan_anestesi ✅
- **Alasan**: Versi terbaru dengan COMMENT lengkap dan field `posisi`
- **Fitur Baru**:
  - COMMENT pada semua field (documentasi)
  - Field `posisi` TEXT (posisi pasien dengan comma separated)
  - Struktur ENGINE COMMENT: 'Tabel catatan sedasi dan anestesi'

## Tabel yang Diambil dari `dbanestesi.sql` (FOLDER RIZKI)

### 4. tbl_anestesi_kamar_pemulihan ✅
- Data pemulihan pasien (2 records)

### 5. tbl_anestesi_keselamatan_operasi ✅
- Surgical safety checklist (3 records)

### 6. tbl_anestesi_persiapan_operasi ✅
- Persiapan operasi (struktur berbeda, lebih detail)

### 7. tbl_anestesi_vital_pemulihan ✅
- Vital signs pemulihan

### 8. tbl_anestesi_vital_sign ✅
- Vital signs monitoring

### 9. booking_operasi & pasien ✅
- Merged data dari kedua file

## Differences Identified

| Tabel | Downloads (3) | FOLDER RIZKI | Decision |
|-------|---------------|---------------|----------|
| `tbl_anestesi_konsultasi_anestesi` | Field `sedasi`, `jalan_nafas_keterangan`, `lain_lain_diagnosis` | Field `gerakan_leher`, `rekomendasi_anestesi` | **Use Downloads** |
| `tbl_anestesi_informed_consent_anestesi` | 5 extra fields | Missing fields | **Use Downloads** |
| `tbl_anestesi_catatan_anestesi` | Full COMMENT, field `posisi` | No COMMENT, field split | **Use Downloads** |
| `pasien` | 1 record | 2 records | **Merge both** |
| `booking_operasi` | 2 records | 3 records | **Merge both** |

## Merge Strategy

```
HEADER + booking_operasi + pasien (merged data)
↓
3 TABLES FROM DOWNLOADS
  ├── tbl_anestesi_catatan_anestesi
  ├── tbl_anestesi_informed_consent_anestesi
  └── tbl_anestesi_konsultasi_anestesi
↓
REST FROM FOLDER RIZKI
  ├── tbl_anestesi_kamar_pemulihan
  ├── tbl_anestesi_keselamatan_operasi
  ├── tbl_anestesi_persiapan_operasi
  ├── tbl_anestesi_vital_pemulihan
  └── tbl_anestesi_vital_sign
↓
INDEXES + AUTO_INCREMENT + FOREIGN KEYS
```

## SQL Merge Command

Untuk melakukan merge manual, gunakan command berikut:

```sql
-- 1. Drop existing tables (BACKUP DULU!)
DROP TABLE IF EXISTS tbl_anestesi_vital_sign;
DROP TABLE IF EXISTS tbl_anestesi_vital_pemulihan;
DROP TABLE IF EXISTS tbl_anestesi_informed_consent_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_kamar_pemulihan;
DROP TABLE IF EXISTS tbl_anestesi_keselamatan_operasi;
DROP TABLE IF EXISTS tbl_anestesi_konsultasi_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_catatan_anestesi;
DROP TABLE IF EXISTS tbl_anestesi_persiapan_operasi;
DROP TABLE IF EXISTS booking_operasi;
DROP TABLE IF EXISTS pasien;

-- 2. Import merged SQL file
SOURCE c:/FOLDER RIZKI/dbanestesi_merged.sql;
```

## Testing Checklist

- [ ] Verify tabel `tbl_anestesi_konsultasi_anestesi` has field `sedasi`
- [ ] Verify tabel `tbl_anestesi_konsultasi_anestesi` has field `lain_lain_diagnosis`
- [ ] Verify tabel `tbl_anestesi_informed_consent_anestesi` has 5 extra fields
- [ ] Verify tabel `tbl_anestesi_catatan_anestesi` has COMMENT on fields
- [ ] Verify all data from both files merged correctly
- [ ] Test form konsultasi anestesi
- [ ] Test form informed consent
- [ ] Test form catatan sedasi

## Backup Recommendation

```bash
# Backup before import
mysqldump -u root -p dbanestesi > backup_dbanestesi_before_merge_$(date +%Y%m%d_%H%M%S).sql

# Import merged file
mysql -u root -p dbanestesi < dbanestesi_merged.sql

# Verify
mysql -u root -p -e "SHOW TABLES FROM dbanestesi;"
mysql -u root -p -e "SELECT COUNT(*) FROM dbanestesi.tbl_anestesi_konsultasi_anestesi;"
```

## Notes

- ✅ **Form Konsultasi Anestesi** akan menggunakan struktur dari Downloads (dengan field `sedasi`)
- ✅ **Form Informed Consent** akan menggunakan struktur dari Downloads (lengkap)
- ✅ **Form Catatan Sedasi** akan menggunakan `tbl_anestesi_catatan_anestesi` dari Downloads
- ⚠️ **PENTING**: Field `rekomendasi_anestesi` di-rename jadi `lain_lain_diagnosis`
- ⚠️ **PENTING**: Field `gerakan_leher` dihapus, diganti `jalan_nafas_keterangan`

## Migration Steps for Application

1. Update PHP forms to use new field names
2. Update process files to handle new fields
3. Update PDF generators  
4. Test all forms thoroughly
5. Deploy to production

---

**Created by**: Cascade AI Assistant  
**Date**: 2025-10-28 10:11:27
