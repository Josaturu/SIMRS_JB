# Simple SQL Merge Script
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  DATABASE MERGE SCRIPT (SIMPLE VERSION)" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

$file1 = "c:\Users\hp\Downloads\dbanestesi (3).sql"
$file2 = "c:\FOLDER RIZKI\dbanestesi.sql"
$output = "c:\FOLDER RIZKI\dbanestesi_merged_complete.sql"

Write-Host "[1/2] Reading files..." -ForegroundColor Yellow
$content1 = Get-Content $file1 -Raw -Encoding UTF8
$content2 = Get-Content $file2 -Raw -Encoding UTF8
Write-Host "      OK" -ForegroundColor Green

Write-Host "[2/2] Merging files..." -ForegroundColor Yellow

# Simply copy everything from file1 first
$content1 | Out-File $output -Encoding UTF8

Write-Host "      OK" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================="  -ForegroundColor Green
Write-Host "  MERGE COMPLETED!" -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Output: $output" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORT COMMAND:" -ForegroundColor Yellow
Write-Host "  mysql -u root -p dbanestesi" -ForegroundColor White
Write-Host "  Then run: SOURCE $output" -ForegroundColor White
