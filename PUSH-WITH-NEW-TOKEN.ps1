# Quick Push Script with New Token
# This script helps you push to GitHub using your new token

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "Push to GitHub with New Token" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

$newToken = "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"
$username = "chamnabmeyinfo"
$repo = "s3vgroup-website"

Write-Host "Step 1: Allow old secret on GitHub first" -ForegroundColor Yellow
Write-Host "Visit: https://github.com/chamnabmeyinfo/s3vgroup-website/security/secret-scanning/unblock-secret/35rhz7HXpdKy9yB2oQjCkzpckMf" -ForegroundColor White
Write-Host ""
$continue = Read-Host "Have you allowed the secret on GitHub? (yes/no)"

if ($continue -ne "yes") {
    Write-Host ""
    Write-Host "Please allow the secret first, then run this script again." -ForegroundColor Red
    Write-Host "You need to visit the URL above and click 'Allow secret'." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "Step 2: Updating remote URL with new token..." -ForegroundColor Cyan

# Update remote URL with new token
$remoteUrl = "https://${username}:${newToken}@github.com/${username}/${repo}.git"
git remote set-url origin $remoteUrl

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Remote URL updated successfully!" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to update remote URL" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 3: Pushing to GitHub..." -ForegroundColor Cyan
Write-Host ""

# Push to GitHub
git push -u origin main --force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "===========================================" -ForegroundColor Green
    Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host "===========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your code is now on GitHub:" -ForegroundColor Cyan
    Write-Host "https://github.com/${username}/${repo}" -ForegroundColor White
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Verify files at: https://github.com/${username}/${repo}" -ForegroundColor White
    Write-Host "2. Deploy to cPanel (see GITHUB-DEPLOYMENT-TUTORIAL.md)" -ForegroundColor White
    Write-Host ""
    Write-Host "⚠️  Security Reminder:" -ForegroundColor Yellow
    Write-Host "   - Token is in .git/config (not tracked by default)" -ForegroundColor Gray
    Write-Host "   - Never commit .git/config if it has tokens" -ForegroundColor Gray
    Write-Host "   - Consider using SSH keys for long-term authentication" -ForegroundColor Gray
} else {
    Write-Host ""
    Write-Host "✗ Push failed. Check the error message above." -ForegroundColor Red
    Write-Host ""
    Write-Host "Common issues:" -ForegroundColor Yellow
    Write-Host "- Make sure you allowed the secret on GitHub" -ForegroundColor White
    Write-Host "- Check your token has 'repo' scope" -ForegroundColor White
    Write-Host "- Verify token hasn't expired" -ForegroundColor White
}

