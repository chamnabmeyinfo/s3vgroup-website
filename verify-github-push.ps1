# Quick Verification Script - Check GitHub Push Status
# Run this to verify your code is on GitHub

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "GitHub Push Verification" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

# Check current directory
if (-not (Test-Path ".git")) {
    Write-Host "Error: Not in a git repository!" -ForegroundColor Red
    Write-Host "Please run this script from your project directory." -ForegroundColor Yellow
    exit 1
}

Write-Host "1. Checking remote configuration..." -ForegroundColor Yellow
$remote = git remote -v
if ($remote) {
    Write-Host "   ✓ Remote configured" -ForegroundColor Green
    Write-Host "   $remote" -ForegroundColor Gray
} else {
    Write-Host "   ✗ No remote configured!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "2. Fetching latest from GitHub..." -ForegroundColor Yellow
git fetch origin 2>&1 | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Successfully fetched from GitHub" -ForegroundColor Green
} else {
    Write-Host "   ✗ Failed to fetch from GitHub!" -ForegroundColor Red
    Write-Host "   Check your internet connection and credentials." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "3. Checking sync status..." -ForegroundColor Yellow
$status = git status --porcelain=v1 2>&1
$branchStatus = git status -sb 2>&1 | Select-Object -First 1

if ($branchStatus -match "up to date") {
    Write-Host "   ✓ Branch is up to date with origin/main" -ForegroundColor Green
} else {
    Write-Host "   ⚠ Branch may not be synced" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "4. Checking local vs remote commits..." -ForegroundColor Yellow
$localCommits = git log origin/main..HEAD --oneline 2>&1
$remoteCommits = git log HEAD..origin/main --oneline 2>&1

if ([string]::IsNullOrWhiteSpace($localCommits) -and [string]::IsNullOrWhiteSpace($remoteCommits)) {
    Write-Host "   ✓ Local and remote are in sync!" -ForegroundColor Green
} else {
    if (-not [string]::IsNullOrWhiteSpace($localCommits)) {
        Write-Host "   ⚠ Local has commits not pushed:" -ForegroundColor Yellow
        Write-Host "   $localCommits" -ForegroundColor Gray
        Write-Host "   → Run: git push" -ForegroundColor Cyan
    }
    if (-not [string]::IsNullOrWhiteSpace($remoteCommits)) {
        Write-Host "   ⚠ Remote has commits not pulled:" -ForegroundColor Yellow
        Write-Host "   $remoteCommits" -ForegroundColor Gray
        Write-Host "   → Run: git pull" -ForegroundColor Cyan
    }
}

Write-Host ""
Write-Host "5. Recent commits on GitHub:" -ForegroundColor Yellow
$recentCommits = git log origin/main --oneline -5 2>&1
if ($recentCommits) {
    Write-Host "   Recent commits:" -ForegroundColor Green
    $recentCommits | ForEach-Object {
        Write-Host "   - $_" -ForegroundColor Gray
    }
} else {
    Write-Host "   ⚠ No commits found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "6. Checking repository on GitHub..." -ForegroundColor Yellow
$remoteUrl = git remote get-url origin
if ($remoteUrl -match "github.com/([^/]+)/([^/]+)\.git") {
    $username = $matches[1]
    $repo = $matches[2]
    $githubUrl = "https://github.com/$username/$repo"
    Write-Host "   Repository URL: $githubUrl" -ForegroundColor Cyan
    Write-Host "   → Open this URL to verify files on GitHub" -ForegroundColor White
}

Write-Host ""
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "Summary" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan

if ($branchStatus -match "up to date") {
    Write-Host "✅ Your code IS pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Verify on GitHub: $githubUrl" -ForegroundColor White
    Write-Host "2. Set up auto-deploy in cPanel (see CHECK-PUSH-AND-AUTO-DEPLOY.md)" -ForegroundColor White
} else {
    Write-Host "⚠️  Your code may not be fully synced with GitHub" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To fix:" -ForegroundColor Yellow
    if (-not [string]::IsNullOrWhiteSpace($localCommits)) {
        Write-Host "- Push local commits: git push" -ForegroundColor White
    }
    if (-not [string]::IsNullOrWhiteSpace($remoteCommits)) {
        Write-Host "- Pull remote commits: git pull" -ForegroundColor White
    }
}

Write-Host ""
Write-Host "For auto-deploy setup, see: CHECK-PUSH-AND-AUTO-DEPLOY.md" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan

