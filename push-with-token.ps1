# PowerShell Script to Push to GitHub with Personal Access Token
# This script helps you push your code after setting up authentication

Write-Host "GitHub Push Helper" -ForegroundColor Green
Write-Host "==================" -ForegroundColor Green
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path ".git")) {
    Write-Host "✗ Not a git repository" -ForegroundColor Red
    exit 1
}

# Check remote
$remote = git remote get-url origin
Write-Host "Repository: $remote" -ForegroundColor Cyan
Write-Host ""

# Check if already pushed
$localCommit = git rev-parse HEAD
$remoteCommit = git ls-remote origin HEAD 2>&1

if ($LASTEXITCODE -eq 0 -and $remoteCommit -match $localCommit) {
    Write-Host "✓ Code is already pushed to GitHub!" -ForegroundColor Green
    Write-Host "View at: https://github.com/chamnabmeyinfo/s3vgroup-website" -ForegroundColor Cyan
    exit 0
}

Write-Host "To push to GitHub, you need a Personal Access Token." -ForegroundColor Yellow
Write-Host ""
Write-Host "Step 1: Create Token" -ForegroundColor Yellow
Write-Host "-------------------" -ForegroundColor Yellow
Write-Host "1. Go to: https://github.com/settings/tokens" -ForegroundColor White
Write-Host "2. Click 'Generate new token' → 'Generate new token (classic)'" -ForegroundColor White
Write-Host "3. Name: s3vgroup-website-push" -ForegroundColor White
Write-Host "4. Expiration: 90 days (or your choice)" -ForegroundColor White
Write-Host "5. Select scope: ✅ repo" -ForegroundColor White
Write-Host "6. Click 'Generate token'" -ForegroundColor White
Write-Host "7. COPY THE TOKEN (you won't see it again!)" -ForegroundColor Red
Write-Host ""

$token = Read-Host "Step 2: Paste your Personal Access Token here" -AsSecureString
$tokenPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($token)
)

if ([string]::IsNullOrWhiteSpace($tokenPlain)) {
    Write-Host "✗ No token provided. Exiting." -ForegroundColor Red
    exit 1
}

# Configure Git to use token
Write-Host ""
Write-Host "Configuring Git credentials..." -ForegroundColor Cyan

# Update remote URL to include token (temporary)
$remoteWithToken = $remote -replace 'https://', "https://chamnabmeyinfo:$tokenPlain@"
git remote set-url origin $remoteWithToken

Write-Host "✓ Credentials configured" -ForegroundColor Green

# Push to GitHub
Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Repository URL: https://github.com/chamnabmeyinfo/s3vgroup-website" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Visit your repository on GitHub" -ForegroundColor White
    Write-Host "2. Set up cPanel Git Version Control (see GITHUB-DEPLOY.md)" -ForegroundColor White
    Write-Host "3. Configure database.php and site.php in cPanel" -ForegroundColor White
    
    # Remove token from remote URL for security
    git remote set-url origin $remote
    Write-Host ""
    Write-Host "✓ Removed token from Git config (stored in credential helper)" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "✗ Push failed. Please check:" -ForegroundColor Red
    Write-Host "- Token is correct and has 'repo' scope" -ForegroundColor Yellow
    Write-Host "- Repository exists and you have access" -ForegroundColor Yellow
    Write-Host "- Internet connection is working" -ForegroundColor Yellow
    
    # Restore original remote
    git remote set-url origin $remote
}

