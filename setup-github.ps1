# Quick GitHub Setup Script
# This script helps you connect to GitHub

Write-Host "GitHub Repository Setup" -ForegroundColor Green
Write-Host "======================" -ForegroundColor Green
Write-Host ""

# Check current remote
$remote = git remote -v
if ($remote) {
    Write-Host "Current remote:" -ForegroundColor Cyan
    Write-Host $remote
    Write-Host ""
    Write-Host "Repository is already connected!" -ForegroundColor Green
    exit 0
}

Write-Host "Step 1: Create a GitHub Repository" -ForegroundColor Yellow
Write-Host "-----------------------------------" -ForegroundColor Yellow
Write-Host "1. Go to: https://github.com/new" -ForegroundColor White
Write-Host "2. Repository name: s3v-forklift-website" -ForegroundColor White
Write-Host "3. Description: S3V Forklift Solutions - PHP Website" -ForegroundColor White
Write-Host "4. Choose Private or Public" -ForegroundColor White
Write-Host "5. DO NOT initialize with README" -ForegroundColor White
Write-Host "6. Click 'Create repository'" -ForegroundColor White
Write-Host ""

$repoUrl = Read-Host "Step 2: Enter your GitHub repository URL (e.g., https://github.com/username/repo.git)"

if ([string]::IsNullOrWhiteSpace($repoUrl)) {
    Write-Host "No URL provided. Exiting." -ForegroundColor Red
    exit 1
}

# Add remote
Write-Host ""
Write-Host "Adding remote..." -ForegroundColor Cyan
git remote add origin $repoUrl

# Set branch to main
Write-Host "Setting branch to main..." -ForegroundColor Cyan
git branch -M main

# Push to GitHub
Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
Write-Host "You may be prompted for GitHub credentials." -ForegroundColor Yellow
Write-Host ""

git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Deploy to cPanel (see GITHUB-DEPLOY.md)" -ForegroundColor White
    Write-Host "2. Configure database and site settings" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "✗ Push failed. Common issues:" -ForegroundColor Red
    Write-Host "- Check your GitHub credentials" -ForegroundColor Yellow
    Write-Host "- Make sure the repository exists" -ForegroundColor Yellow
    Write-Host "- Try using a Personal Access Token instead of password" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To retry manually:" -ForegroundColor Cyan
    Write-Host "  git push -u origin main" -ForegroundColor Gray
}
