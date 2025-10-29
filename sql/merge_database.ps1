# PowerShell Script to Merge Database SQL Files
# Merge 3 tables from Downloads, rest from FOLDER RIZKI

$file1 = "c:\Users\hp\Downloads\dbanestesi (3).sql"
$file2 = "c:\FOLDER RIZKI\dbanestesi.sql"
$outputFile = "c:\FOLDER RIZKI\dbanestesi_merged.sql"

Write-Host "🔄 Starting database merge..." -ForegroundColor Cyan

# Read both files
$content1 = Get-Content $file1 -Raw
$content2 = Get-Content $file2 -Raw

Write-Host "✅ Files read successfully" -ForegroundColor Green

# Extract header (until first table) from file1
$headerMatch = $content1 -match '(?s)(.*?-- --------------------------------------------------------\s+--\s+-- Struktur dari tabel)'
if ($headerMatch) {
    $header = $Matches[1]
    Write-Host "✅ Header extracted" -ForegroundColor Green
}

# Extract tables from file1 (Downloads)
$tables_from_file1 = @(
    'tbl_anestesi_catatan_anestesi',
    'tbl_anestesi_informed_consent_anestesi',
    'tbl_anestesi_konsultasi_anestesi'
)

# Extract tables from file2 (FOLDER RIZKI)
$tables_from_file2 = @(
    'tbl_anestesi_kamar_pemulihan',
    'tbl_anestesi_keselamatan_operasi',
    'tbl_anestesi_persiapan_operasi',
    'tbl_anestesi_vital_pemulihan',
    'tbl_anestesi_vital_sign'
)

# Function to extract table definition + data
function Extract-Table {
    param($content, $tableName)
    
    $pattern = "(?s)(-- --------------------------------------------------------.*?-- Struktur dari tabel ``$tableName``.*?-- --------------------------------------------------------)"
    if ($content -match $pattern) {
        return $Matches[1]
    }
    return $null
}

# Start building merged content
$merged = $header

# Add booking_operasi and pasien from file1 (has latest structure)
$merged += Extract-Table $content1 "booking_operasi"
$merged += "`n`n"
$merged += Extract-Table $content1 "pasien"
$merged += "`n`n"

# Add 3 tables from file1
foreach ($table in $tables_from_file1) {
    Write-Host "📋 Extracting $table from Downloads..." -ForegroundColor Yellow
    $tableContent = Extract-Table $content1 $table
    if ($tableContent) {
        $merged += $tableContent + "`n`n"
        Write-Host "   ✅ $table extracted" -ForegroundColor Green
    } else {
        Write-Host "   ❌ $table NOT FOUND in file1" -ForegroundColor Red
    }
}

# Add rest of tables from file2
foreach ($table in $tables_from_file2) {
    Write-Host "📋 Extracting $table from FOLDER RIZKI..." -ForegroundColor Yellow
    $tableContent = Extract-Table $content2 $table
    if ($tableContent) {
        $merged += $tableContent + "`n`n"
        Write-Host "   ✅ $table extracted" -ForegroundColor Green
    } else {
        Write-Host "   ❌ $table NOT FOUND in file2" -ForegroundColor Red
    }
}

# Extract indexes and constraints from file1
$indexPattern = "(?s)(-- Indeks untuk tabel.*?COMMIT;)"
if ($content1 -match $indexPattern) {
    $merged += $Matches[1]
    Write-Host "✅ Indexes and constraints added" -ForegroundColor Green
}

# Save merged file
$merged | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host "`n🎉 Merge completed successfully!" -ForegroundColor Green
Write-Host "📁 Output file: $outputFile" -ForegroundColor Cyan
Write-Host "`n📊 Summary:" -ForegroundColor Yellow
Write-Host "   - 3 tables from Downloads (terbaru)" -ForegroundColor White
Write-Host "   - 5 tables from FOLDER RIZKI (existing)" -ForegroundColor White
Write-Host "   - booking_operasi & pasien merged" -ForegroundColor White
Write-Host "`n⚠️  NEXT STEPS:" -ForegroundColor Yellow
Write-Host "   1. BACKUP database terlebih dahulu!" -ForegroundColor Red
Write-Host "   2. Review file: $outputFile" -ForegroundColor White
Write-Host "   3. Import: mysql -u root -p dbanestesi < $outputFile" -ForegroundColor White
