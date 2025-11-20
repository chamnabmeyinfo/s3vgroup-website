# PowerShell Script to Deploy to GitHub
# This script helps you push your code to GitHub

Write-Host "S3V Forklift Website - GitHub Deployment" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green
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
    Write-Host "✗ Not a git repository. Run 'git init' first." -ForegroundColor Red
    exit 1
}

# Check for uncommitted changes
$status = git status --porcelain
if ($status) {
    Write-Host "⚠ Uncommitted changes detected:" -ForegroundColor Yellow
    Write-Host $status
    $response = Read-Host "Do you want to commit these changes? (y/n)"
    if ($response -eq "y") {
        git add .
        $commitMsg = Read-Host "Enter commit message (or press Enter for default)"
        if ([string]::IsNullOrWhiteSpace($commitMsg)) {
            $commitMsg = "Update website"
        }
        git commit -m $commitMsg
        Write-Host "✓ Changes committed" -ForegroundColor Green
    }
}

# Check for remote
$remote = git remote -v
if (-not $remote) {
    Write-Host ""
    Write-Host "No GitHub remote configured." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To connect to GitHub:" -ForegroundColor Cyan
    Write-Host "1. Create a repository on GitHub: https://github.com/new" -ForegroundColor White
    Write-Host "2. Then run these commands:" -ForegroundColor White
    Write-Host ""
    Write-Host "   git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git" -ForegroundColor Gray
    Write-Host "   git branch -M main" -ForegroundColor Gray
    Write-Host "   git push -u origin main" -ForegroundColor Gray
    Write-Host ""
    
    $repoUrl = Read-Host "Or enter your GitHub repository URL now (leave empty to skip)"
    if ($repoUrl) {
        git remote add origin $repoUrl
        Write-Host "✓ Remote added: $repoUrl" -ForegroundColor Green
        
        $branch = Read-Host "Branch name (default: main)"
        if ([string]::IsNullOrWhiteSpace($branch)) {
            $branch = "main"
        }
        git branch -M $branch
        
        Write-Host ""
        Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
        git push -u origin $branch
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
        } else {
            Write-Host "✗ Push failed. Check your GitHub credentials." -ForegroundColor Red
        }
    }
} else {
    Write-Host "✓ Remote configured:" -ForegroundColor Green
    Write-Host $remote
    
    $response = Read-Host "Do you want to push to GitHub now? (y/n)"
    if ($response -eq "y") {
        Write-Host ""
        Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
        git push
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
        } else {
            Write-Host "✗ Push failed. Check your GitHub credentials." -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Deploy to cPanel using GITHUB-DEPLOY.md instructions" -ForegroundColor White
Write-Host "2. Configure database.php and site.php in cPanel" -ForegroundColor White
Write-Host "3. Import sql/schema.sql via phpMyAdmin" -ForegroundColor White
