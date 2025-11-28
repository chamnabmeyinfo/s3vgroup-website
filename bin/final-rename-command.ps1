# Final Rename Command for Ant Elite System
# Run this in PowerShell as Administrator if needed

$root = "C:\xampp\htdocs\s3vgroup"
Set-Location $root

Write-Host "🔄 Renaming to Ant Elite (AE) System..." -ForegroundColor Cyan
Write-Host ""

# Stop Apache if running (to unlock files)
Write-Host "⚠️  Please stop Apache/XAMPP before running this script!" -ForegroundColor Yellow
Write-Host "   Press any key to continue after stopping Apache..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Rename directories
$items = @{
    'wp-admin' = 'ae-admin'
    'wp-includes' = 'ae-includes'
    'wp-content' = 'ae-content'
}

foreach ($old in $items.Keys) {
    $new = $items[$old]
    if (Test-Path $old) {
        if (Test-Path $new) {
            Write-Host "  ⊘ Already exists: $new" -ForegroundColor Yellow
        } else {
            try {
                Rename-Item -Path $old -NewName $new -Force -ErrorAction Stop
                Write-Host "  ✓ Renamed: $old → $new" -ForegroundColor Green
            } catch {
                Write-Host "  ✗ Failed: $old - $_" -ForegroundColor Red
                Write-Host "    Trying copy method..." -ForegroundColor Yellow
                Copy-Item -Path $old -Destination $new -Recurse -Force
                Remove-Item -Path $old -Recurse -Force
                Write-Host "    ✓ Copied and removed: $old → $new" -ForegroundColor Green
            }
        }
    } else {
        Write-Host "  ⊘ Not found: $old" -ForegroundColor Gray
    }
}

# Rename files
$files = @{
    'wp-load.php' = 'ae-load.php'
    'wp-config.php' = 'ae-config.php'
}

foreach ($old in $files.Keys) {
    $new = $files[$old]
    if (Test-Path $old) {
        if (Test-Path $new) {
            Write-Host "  ⊘ Already exists: $new" -ForegroundColor Yellow
        } else {
            try {
                Rename-Item -Path $old -NewName $new -Force -ErrorAction Stop
                Write-Host "  ✓ Renamed: $old → $new" -ForegroundColor Green
            } catch {
                Write-Host "  ✗ Failed: $old - $_" -ForegroundColor Red
                Copy-Item -Path $old -Destination $new -Force
                Remove-Item -Path $old -Force
                Write-Host "    ✓ Copied and removed: $old → $new" -ForegroundColor Green
            }
        }
    } else {
        Write-Host "  ⊘ Not found: $old" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "✅ Renaming completed!" -ForegroundColor Green
Write-Host ""
Write-Host "🎉 Your Ant Elite (AE) system is ready!" -ForegroundColor Cyan

