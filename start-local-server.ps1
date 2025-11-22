# PowerShell Script to Start Local PHP Server
# This script helps you test the website locally

Write-Host "S3vgroup Website - Local Development Server" -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Green
Write-Host ""

# Check if PHP is available
try {
    $phpVersion = php --version 2>&1 | Select-Object -First 1
    Write-Host "✓ PHP found: $phpVersion" -ForegroundColor Cyan
} catch {
    Write-Host "✗ PHP not found in PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Options:" -ForegroundColor Yellow
    Write-Host "1. Install XAMPP (recommended): https://www.apachefriends.org/" -ForegroundColor White
    Write-Host "2. Add PHP to PATH if installed separately" -ForegroundColor White
    Write-Host ""
    Write-Host "For XAMPP:" -ForegroundColor Cyan
    Write-Host "- Copy project to: C:\xampp\htdocs\s3vgroup\" -ForegroundColor White
    Write-Host "- Access at: http://localhost/s3vgroup/" -ForegroundColor White
    exit 1
}

# Check if we're in the project directory
if (-not (Test-Path "config/database.php.example")) {
    Write-Host "✗ Not in project directory" -ForegroundColor Red
    Write-Host "Please run this script from the s3v-web-php folder" -ForegroundColor Yellow
    exit 1
}

# Check for configuration files
if (-not (Test-Path "config/database.php")) {
    Write-Host "⚠ config/database.php not found" -ForegroundColor Yellow
    Write-Host "Creating from example..." -ForegroundColor Cyan
    
    Copy-Item "config/database.php.example" "config/database.php"
    Write-Host "✓ Created config/database.php" -ForegroundColor Green
    Write-Host ""
    Write-Host "⚠ IMPORTANT: Edit config/database.php with your local database settings!" -ForegroundColor Yellow
    Write-Host "   Default for XAMPP:" -ForegroundColor Gray
    Write-Host "   - DB_HOST: localhost" -ForegroundColor Gray
    Write-Host "   - DB_NAME: s3vgroup_local" -ForegroundColor Gray
    Write-Host "   - DB_USER: root" -ForegroundColor Gray
    Write-Host "   - DB_PASS: (empty)" -ForegroundColor Gray
    Write-Host ""
}

if (-not (Test-Path "config/site.php")) {
    Write-Host "⚠ config/site.php not found" -ForegroundColor Yellow
    Write-Host "Creating from example..." -ForegroundColor Cyan
    
    Copy-Item "config/site.php.example" "config/site.php"
    Write-Host "✓ Created config/site.php" -ForegroundColor Green
    Write-Host ""
}

Write-Host "Starting PHP development server..." -ForegroundColor Cyan
Write-Host ""
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start PHP server
php -S localhost:8000
