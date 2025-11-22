# Fix GitHub Authentication Script
# This script helps fix authentication issues with GitHub

Write-Host "GitHub Authentication Fix" -ForegroundColor Green
Write-Host "========================" -ForegroundColor Green
Write-Host ""

# Check current configuration
Write-Host "Current Configuration:" -ForegroundColor Cyan
Write-Host "Repository: https://github.com/chamnabmeyinfo/s3vgroup-website.git" -ForegroundColor White
Write-Host "Git User: $(git config --global user.name)" -ForegroundColor White
Write-Host "Git Email: $(git config --global user.email)" -ForegroundColor White
Write-Host ""

# Clear cached credentials
Write-Host "Clearing cached credentials..." -ForegroundColor Yellow

# Clear Windows Credential Manager
try {
    cmdkey /list | Select-String "git:https://github.com" | ForEach-Object {
        $line = $_.Line
        if ($line -match "Target: (.*)") {
            cmdkey /delete:$matches[1] 2>&1 | Out-Null
        }
    }
    Write-Host "✓ Cleared Windows Credential Manager entries" -ForegroundColor Green
} catch {
    Write-Host "⚠ Could not clear Credential Manager (may not exist)" -ForegroundColor Yellow
}

# Clear stored credentials file
if (Test-Path "$env:USERPROFILE\.git-credentials") {
    Remove-Item "$env:USERPROFILE\.git-credentials" -Force
    Write-Host "✓ Cleared stored credentials file" -ForegroundColor Green
} else {
    Write-Host "✓ No stored credentials file found" -ForegroundColor Green
}

Write-Host ""
Write-Host "Step 1: Create Personal Access Token" -ForegroundColor Yellow
Write-Host "--------------------------------------" -ForegroundColor Yellow
Write-Host "1. Go to: https://github.com/settings/tokens" -ForegroundColor White
Write-Host "2. Click 'Generate new token' → 'Generate new token (classic)'" -ForegroundColor White
Write-Host "3. Name: s3vgroup-website-push" -ForegroundColor White
Write-Host "4. Expiration: 90 days (or your preference)" -ForegroundColor White
Write-Host "5. Select scope: ✅ repo (Full control of private repositories)" -ForegroundColor White
Write-Host "6. Click 'Generate token'" -ForegroundColor White
Write-Host "7. COPY THE TOKEN IMMEDIATELY (you won't see it again!)" -ForegroundColor Red
Write-Host ""

$token = Read-Host "Step 2: Paste your Personal Access Token here" -AsSecureString
$tokenPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($token)
)

if ([string]::IsNullOrWhiteSpace($tokenPlain)) {
    Write-Host "✗ No token provided. Exiting." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 3: Updating remote URL with authentication..." -ForegroundColor Yellow

# Update remote URL to include token
$remoteUrl = "https://chamnabmeyinfo:$tokenPlain@github.com/chamnabmeyinfo/s3vgroup-website.git"
git remote set-url origin $remoteUrl

Write-Host "✓ Remote URL updated" -ForegroundColor Green

Write-Host ""
Write-Host "Step 4: Testing connection..." -ForegroundColor Yellow
$testConnection = git ls-remote origin HEAD 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Connection successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Step 5: Pushing to GitHub..." -ForegroundColor Yellow
    git push -u origin main
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓✓✓ Successfully pushed to GitHub! ✓✓✓" -ForegroundColor Green
        Write-Host ""
        Write-Host "Repository: https://github.com/chamnabmeyinfo/s3vgroup-website" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Your code is now on GitHub!" -ForegroundColor Green
        
        # Remove token from URL for security (credentials are stored)
        git remote set-url origin https://github.com/chamnabmeyinfo/s3vgroup-website.git
        Write-Host "✓ Removed token from URL (credentials stored securely)" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "✗ Push failed. Please check:" -ForegroundColor Red
        Write-Host "- Token has 'repo' scope" -ForegroundColor Yellow
        Write-Host "- Repository exists and you have access" -ForegroundColor Yellow
        Write-Host "- Internet connection is working" -ForegroundColor Yellow
    }
} else {
    Write-Host "✗ Connection test failed" -ForegroundColor Red
    Write-Host "Please verify:" -ForegroundColor Yellow
    Write-Host "- Token is correct" -ForegroundColor White
    Write-Host "- Token has 'repo' scope" -ForegroundColor White
    Write-Host "- Repository exists: https://github.com/chamnabmeyinfo/s3vgroup-website" -ForegroundColor White
}

