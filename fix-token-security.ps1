# Security Fix Script - Remove Token from Git History
# This script helps you fix the exposed GitHub token issue

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "GitHub Token Security Fix" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Step 1: IMPORTANT - Revoke the exposed token first!" -ForegroundColor Yellow
Write-Host "Visit: https://github.com/settings/tokens" -ForegroundColor White
Write-Host "Find token: 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" -ForegroundColor White
Write-Host "Click 'Revoke' and confirm" -ForegroundColor White
Write-Host ""

$continue = Read-Host "Have you revoked the token? (yes/no)"

if ($continue -ne "yes") {
    Write-Host "Please revoke the token first! This is critical for security." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2: Choose how to proceed" -ForegroundColor Yellow
Write-Host ""
Write-Host "Option A: Remove token from git history (Recommended - More Secure)" -ForegroundColor Green
Write-Host "  - Removes the token completely from all commits" -ForegroundColor Gray
Write-Host "  - Takes longer but is more secure" -ForegroundColor Gray
Write-Host ""
Write-Host "Option B: Use GitHub's unblock feature (Faster - Less Secure)" -ForegroundColor Yellow
Write-Host "  - Token still in history but push will work" -ForegroundColor Gray
Write-Host "  - Faster but token remains in git history" -ForegroundColor Gray
Write-Host ""

$choice = Read-Host "Choose option (A or B)"

if ($choice -eq "A" -or $choice -eq "a") {
    Write-Host ""
    Write-Host "Removing token from git history..." -ForegroundColor Cyan
    
    # Method 1: Using git filter-branch (built-in)
    Write-Host "Using git filter-branch to remove notepad.txt from all commits..." -ForegroundColor White
    
    # Remove notepad.txt from all commits
    git filter-branch --force --index-filter `
        "git rm --cached --ignore-unmatch notepad.txt" `
        --prune-empty --tag-name-filter cat -- --all
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ Token removed from git history!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Step 3: Push to GitHub" -ForegroundColor Yellow
        Write-Host "Run: git push -u origin main --force" -ForegroundColor White
    } else {
        Write-Host ""
        Write-Host "✗ Error removing from history. Trying alternative method..." -ForegroundColor Red
        
        # Alternative: Use BFG Repo-Cleaner (if installed)
        Write-Host "Alternative: Install BFG Repo-Cleaner" -ForegroundColor Yellow
        Write-Host "Download: https://rtyley.github.io/bfg-repo-cleaner/" -ForegroundColor White
        Write-Host "Then run: java -jar bfg.jar --delete-files notepad.txt" -ForegroundColor White
    }
    
} elseif ($choice -eq "B" -or $choice -eq "b") {
    Write-Host ""
    Write-Host "Using GitHub unblock feature..." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. Visit this URL to allow the secret:" -ForegroundColor White
    Write-Host "   https://github.com/chamnabmeyinfo/s3vgroup-website/security/secret-scanning/unblock-secret/35rhz7HXpdKy9yB2oQjCkzpckMf" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "2. Click 'Allow secret' button" -ForegroundColor White
    Write-Host ""
    
    $unblocked = Read-Host "Have you allowed the secret on GitHub? (yes/no)"
    
    if ($unblocked -eq "yes") {
        Write-Host ""
        Write-Host "Step 3: Pushing to GitHub..." -ForegroundColor Yellow
        git push -u origin main --force
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
            Write-Host ""
            Write-Host "⚠️  Note: The token is still in git history." -ForegroundColor Yellow
            Write-Host "   Make sure you've revoked the old token!" -ForegroundColor Yellow
        } else {
            Write-Host ""
            Write-Host "✗ Push failed. Check the error message above." -ForegroundColor Red
        }
    } else {
        Write-Host "Please allow the secret on GitHub first, then run this script again." -ForegroundColor Yellow
    }
} else {
    Write-Host "Invalid choice. Please run the script again and choose A or B." -ForegroundColor Red
}

Write-Host ""
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "For more details, see: SECURITY-FIX-REQUIRED.md" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan

