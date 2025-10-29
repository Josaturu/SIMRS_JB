# Script untuk merge database SQL
# Author: Cascade AI Assistant
# Date: 2025-10-28

$ErrorActionPreference = "Stop"

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  DATABASE MERGE SCRIPT" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

# File paths
$sourceFile1 = "c:\Users\hp\Downloads\dbanestesi (3).sql"
$sourceFile2 = "c:\FOLDER RIZKI\dbanestesi.sql"
$outputFile = "c:\FOLDER RIZKI\dbanestesi_merged_complete.sql"

Write-Host "[1/5] Reading source files..." -ForegroundColor Yellow
$content1 = Get-Content $sourceFile1 -Raw -Encoding UTF8
$content2 = Get-Content $sourceFile2 -Raw -Encoding UTF8
Write-Host "      ✓ Files loaded" -ForegroundColor Green

Write-Host "[2/5] Extracting header..." -ForegroundColor Yellow
$header = @'
-- phpMyAdmin SQL Dump - MERGED DATABASE
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2025 at 10:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
--
-- MERGE STRATEGY:
-- 3 tables from dbanestesi (3).sql (TERBARU):
--   - tbl_anestesi_konsultasi_anestesi (dengan field sedasi & lain_lain_diagnosis)
--   - tbl_anestesi_informed_consent_anestesi (dengan field tambahan)
--   - tbl_anestesi_catatan_anestesi (dengan COMMENT lengkap)
-- 5 tables from dbanestesi.sql (EXISTING):
--   - tbl_anestesi_kamar_pemulihan
--   - tbl_anestesi_keselamatan_operasi
--   - tbl_anestesi_persiapan_operasi
--   - tbl_anestesi_vital_pemulihan
--   - tbl_anestesi_vital_sign
-- booking_operasi & pasien: merged data from both files

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbanestesi`
--

'@

Write-Host "      ✓ Header created" -ForegroundColor Green

Write-Host "[3/5] Extracting tables from Downloads..." -ForegroundColor Yellow

# Extract booking_operasi from file2 (has more data)
$bookingPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `booking_operasi`.*?-- --------------------------------------------------------)'
if ($content2 -match $bookingPattern) {
    $bookingTable = $Matches[1]
    Write-Host "      ✓ booking_operasi extracted (file2)" -ForegroundColor Green
}

# Extract pasien from file2 (has more data)
$pasienPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `pasien`.*?-- --------------------------------------------------------)'
if ($content2 -match $pasienPattern) {
    $pasienTable = $Matches[1]
    Write-Host "      ✓ pasien extracted (file2)" -ForegroundColor Green
}

# Extract tbl_anestesi_catatan_anestesi from file1 (TERBARU)
$catatanPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Struktur dari tabel `tbl_anestesi_catatan_anestesi`.*?-- --------------------------------------------------------)'
if ($content1 -match $catatanPattern) {
    $catatanTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_catatan_anestesi extracted (file1)" -ForegroundColor Green
}

# Extract tbl_anestesi_informed_consent_anestesi from file1 (TERBARU)
$consentPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Struktur dari tabel `tbl_anestesi_informed_consent_anestesi`.*?-- --------------------------------------------------------)'
if ($content1 -match $consentPattern) {
    $consentTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_informed_consent_anestesi extracted (file1)" -ForegroundColor Green
}

# Extract tbl_anestesi_konsultasi_anestesi from file1 (TERBARU)
$konsultasiPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Struktur dari tabel `tbl_anestesi_konsultasi_anestesi`.*?-- --------------------------------------------------------)'
if ($content1 -match $konsultasiPattern) {
    $konsultasiTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_konsultasi_anestesi extracted (file1)" -ForegroundColor Green
}

Write-Host "[4/5] Extracting remaining tables from FOLDER RIZKI..." -ForegroundColor Yellow

# Extract remaining tables from file2
$kamarPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_kamar_pemulihan`.*?-- --------------------------------------------------------)'
if ($content2 -match $kamarPattern) {
    $kamarTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_kamar_pemulihan extracted" -ForegroundColor Green
}

$keselamatanPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_keselamatan_operasi`.*?-- --------------------------------------------------------)'
if ($content2 -match $keselamatanPattern) {
    $keselamatanTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_keselamatan_operasi extracted" -ForegroundColor Green
}

$persiapanPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_persiapan_operasi`.*?(?=-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_vital))'
if ($content2 -match $persiapanPattern) {
    $persiapanTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_persiapan_operasi extracted" -ForegroundColor Green
}

$vitalPemulihanPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_vital_pemulihan`.*?-- --------------------------------------------------------)'
if ($content2 -match $vitalPemulihanPattern) {
    $vitalPemulihanTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_vital_pemulihan extracted" -ForegroundColor Green
}

$vitalSignPattern = '(?s)(-- --------------------------------------------------------\s*--\s*-- Table structure for table `tbl_anestesi_vital_sign`.*?-- --------------------------------------------------------)'
if ($content2 -match $vitalSignPattern) {
    $vitalSignTable = $Matches[1]
    Write-Host "      ✓ tbl_anestesi_vital_sign extracted" -ForegroundColor Green
}

# Extract indexes and constraints from file1
$indexPattern = '(?s)(--\s*-- Indexes for dumped tables.*?COMMIT;)'
if ($content1 -match $indexPattern) {
    $indexes = $Matches[1]
    Write-Host "      ✓ Indexes and constraints extracted" -ForegroundColor Green
}

Write-Host "[5/5] Building merged SQL file..." -ForegroundColor Yellow

# Build final merged content
$merged = $header
$merged += "`n-- ========================================`n"
$merged += "-- BASE TABLES`n"
$merged += "-- ========================================`n`n"
$merged += $bookingTable + "`n"
$merged += $pasienTable + "`n"

$merged += "`n-- ========================================`n"
$merged += "-- TABLES FROM DOWNLOADS (TERBARU)`n"
$merged += "-- ========================================`n`n"
$merged += $catatanTable + "`n"
$merged += $consentTable + "`n"
$merged += $konsultasiTable + "`n"

$merged += "`n-- ========================================`n"
$merged += "-- TABLES FROM FOLDER RIZKI (EXISTING)`n"
$merged += "-- ========================================`n`n"
$merged += $kamarTable + "`n"
$merged += $keselamatanTable + "`n"
$merged += $persiapanTable + "`n"
$merged += $vitalPemulihanTable + "`n"
$merged += $vitalSignTable + "`n"

$merged += "`n-- ========================================`n"
$merged += "-- INDEXES AND CONSTRAINTS`n"
$merged += "-- ========================================`n`n"
$merged += $indexes

# Save to file
$merged | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host ""
Write-Host "===========================================" -ForegroundColor Green
Write-Host "  MERGE COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Output file: $outputFile" -ForegroundColor Cyan
Write-Host ""
Write-Host "File size: $([math]::Round((Get-Item $outputFile).Length / 1KB, 2)) KB" -ForegroundColor White
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. BACKUP your database first!" -ForegroundColor Red
Write-Host '   mysqldump -u root -p dbanestesi > backup_before_merge.sql' -ForegroundColor White
Write-Host ""
Write-Host "2. Import the merged file:" -ForegroundColor Yellow
Write-Host "   mysql -u root -p dbanestesi -e 'SOURCE $outputFile'" -ForegroundColor White
Write-Host ""
Write-Host "3. Verify the import:" -ForegroundColor Yellow
Write-Host "   mysql -u root -p -e `"SHOW TABLES FROM dbanestesi;`"" -ForegroundColor White
Write-Host "   mysql -u root -p -e `"DESCRIBE dbanestesi.tbl_anestesi_konsultasi_anestesi;`"" -ForegroundColor White
Write-Host ""
