# Automatic Schema Sync - Windows Scheduled Task
# 
# This script runs the automatic schema sync and can be scheduled via Task Scheduler
# 
# Setup:
# 1. Open Task Scheduler (taskschd.msc)
# 2. Create Basic Task
# 3. Set trigger (e.g., daily at 2 AM)
# 4. Set action: Start a program
# 5. Program: powershell.exe
# 6. Arguments: -ExecutionPolicy Bypass -File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-schema-scheduled.ps1"
# 7. Save and test

$ErrorActionPreference = "Stop"

# Get script directory
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectDir = Split-Path -Parent $scriptDir

# Change to project directory
Set-Location $projectDir

# PHP executable path (adjust if needed)
$phpExe = "C:\xampp\php\php.exe"

if (-not (Test-Path $phpExe)) {
    Write-Host "ERROR: PHP not found at $phpExe" -ForegroundColor Red
    Write-Host "Please update the PHP path in this script." -ForegroundColor Yellow
    exit 1
}

# Log file
$logDir = Join-Path $projectDir "storage\logs"
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}
$logFile = Join-Path $logDir "schema-sync-scheduled.log"

# Function to log messages
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] $Message"
    Add-Content -Path $logFile -Value $logMessage
    Write-Host $logMessage
}

Write-Log "Starting automatic schema sync..."

try {
    # Run the PHP script
    & $phpExe bin\auto-sync-schema.php --quiet
    
    if ($LASTEXITCODE -eq 0) {
        Write-Log "Schema sync completed successfully"
    } else {
        Write-Log "Schema sync failed with exit code: $LASTEXITCODE"
        exit $LASTEXITCODE
    }
} catch {
    Write-Log "ERROR: Schema sync failed: $_"
    exit 1
}

Write-Log "Automatic schema sync finished"

