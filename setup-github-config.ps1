# GitHub Configuration Setup Script
# This script helps you configure your project for GitHub

Write-Host "GitHub Configuration Setup" -ForegroundColor Green
Write-Host "=========================" -ForegroundColor Green
Write-Host ""

# Check if git is installed
try {
    $gitVersion = git --version
    Write-Host "✓ Git is installed: $gitVersion" -ForegroundColor Cyan
} catch {
    Write-Host "✗ Git is not installed. Please install Git first." -ForegroundColor Red
    Write-Host "Download from: https://git-scm.com/download/win" -ForegroundColor Yellow
    exit 1
}

# Check if we're in a git repository
if (-not (Test-Path ".git")) {
    Write-Host "✗ Not a git repository. Initializing..." -ForegroundColor Yellow
    git init
    Write-Host "✓ Git repository initialized" -ForegroundColor Green
}

# Check current branch
$currentBranch = git branch --show-current
Write-Host "Current branch: $currentBranch" -ForegroundColor Cyan

# Check for remote
$remote = git remote -v
if ($remote) {
    Write-Host ""
    Write-Host "Current remote configuration:" -ForegroundColor Cyan
    Write-Host $remote
    Write-Host ""
    $response = Read-Host "Remote already exists. Do you want to update it? (y/n)"
    if ($response -ne "y") {
        Write-Host "Keeping existing remote configuration." -ForegroundColor Yellow
        exit 0
    }
}

Write-Host ""
Write-Host "Step 1: Create GitHub Repository" -ForegroundColor Yellow
Write-Host "-----------------------------------" -ForegroundColor Yellow
Write-Host "1. Go to: https://github.com/new" -ForegroundColor White
Write-Host "2. Repository name: s3vgroup-website (or your choice)" -ForegroundColor White
Write-Host "3. Description: S3vgroup - Warehouse & Factory Equipment E-commerce Website" -ForegroundColor White
Write-Host "4. Choose Private (recommended) or Public" -ForegroundColor White
Write-Host "5. DO NOT initialize with README, .gitignore, or license" -ForegroundColor White
Write-Host "6. Click 'Create repository'" -ForegroundColor White
Write-Host ""

$repoUrl = Read-Host "Step 2: Enter your GitHub repository URL (e.g., https://github.com/username/repo.git)"

if ([string]::IsNullOrWhiteSpace($repoUrl)) {
    Write-Host "No URL provided. Exiting." -ForegroundColor Red
    exit 1
}

# Remove existing remote if updating
if ($remote) {
    git remote remove origin
    Write-Host "✓ Removed existing remote" -ForegroundColor Green
}

# Add remote
Write-Host ""
Write-Host "Adding remote..." -ForegroundColor Cyan
git remote add origin $repoUrl
Write-Host "✓ Remote added: $repoUrl" -ForegroundColor Green

# Set default branch to main
Write-Host "Setting default branch to main..." -ForegroundColor Cyan
if ($currentBranch -ne "main") {
    git branch -M main
    Write-Host "✓ Branch renamed to main" -ForegroundColor Green
}

# Check for uncommitted changes
$status = git status --porcelain
if ($status) {
    Write-Host ""
    Write-Host "⚠ Uncommitted changes detected:" -ForegroundColor Yellow
    Write-Host $status
    Write-Host ""
    $response = Read-Host "Do you want to commit these changes? (y/n)"
    if ($response -eq "y") {
        git add .
        $commitMsg = Read-Host "Enter commit message (or press Enter for default)"
        if ([string]::IsNullOrWhiteSpace($commitMsg)) {
            $commitMsg = "Initial commit - GitHub configuration"
        }
        git commit -m $commitMsg
        Write-Host "✓ Changes committed" -ForegroundColor Green
    }
}

# Push to GitHub
Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
Write-Host "You may be prompted for GitHub credentials." -ForegroundColor Yellow
Write-Host ""

git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Successfully configured and pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Repository URL: $repoUrl" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Set up cPanel Git Version Control (see GITHUB-DEPLOY.md)" -ForegroundColor White
    Write-Host "2. Configure database.php and site.php in cPanel" -ForegroundColor White
    Write-Host "3. Import sql/schema.sql via phpMyAdmin" -ForegroundColor White
    Write-Host "4. Enable GitHub Actions workflows in repository settings" -ForegroundColor White
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

