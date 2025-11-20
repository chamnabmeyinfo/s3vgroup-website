# PowerShell Script to Create Deployment Package
# Run this script to create a ZIP file ready for cPanel upload

$packageName = "s3v-website-cpanel.zip"
$sourceDir = Get-Location

Write-Host "Creating deployment package..." -ForegroundColor Green

# Remove old package if exists
if (Test-Path $packageName) {
    Remove-Item $packageName -Force
    Write-Host "Removed old package" -ForegroundColor Yellow
}

# Create ZIP file
Write-Host "Compressing files..." -ForegroundColor Cyan

# Get all files except node_modules, .git, etc.
$filesToInclude = Get-ChildItem -Path $sourceDir -Recurse | Where-Object {
    $_.FullName -notmatch 'node_modules' -and
    $_.FullName -notmatch '\.git' -and
    $_.FullName -notmatch '\.DS_Store' -and
    $_.FullName -notmatch 'Thumbs\.db'
}

# Create ZIP
Compress-Archive -Path $filesToInclude.FullName -DestinationPath $packageName -Force

Write-Host "`n✓ Package created: $packageName" -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "1. Log into cPanel" -ForegroundColor White
Write-Host "2. Open File Manager" -ForegroundColor White
Write-Host "3. Navigate to public_html/" -ForegroundColor White
Write-Host "4. Upload $packageName" -ForegroundColor White
Write-Host "5. Extract the ZIP file" -ForegroundColor White
Write-Host "6. Configure database.php and site.php" -ForegroundColor White
Write-Host "7. Import sql/schema.sql via phpMyAdmin" -ForegroundColor White
