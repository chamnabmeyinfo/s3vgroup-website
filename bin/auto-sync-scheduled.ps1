# Automatic Database Sync - Scheduled Task
# 
# This PowerShell script can be run as a scheduled task to automatically
# sync your database from cPanel to localhost.
#
# Setup as Scheduled Task:
# 1. Open Task Scheduler (Windows)
# 2. Create Basic Task
# 3. Set trigger (e.g., every hour, daily, etc.)
# 4. Set action: Start a program
# 5. Program: powershell.exe
# 6. Arguments: -File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-scheduled.ps1"
# 7. Save and enable

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectPath = Split-Path -Parent $scriptPath
$logFile = Join-Path $projectPath "database-sync.log"

# Change to project directory
Set-Location $projectPath

# Log function
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    Add-Content -Path $logFile -Value $logMessage
    
    switch ($Level) {
        "ERROR" { Write-Host $logMessage -ForegroundColor Red }
        "SUCCESS" { Write-Host $logMessage -ForegroundColor Green }
        "WARNING" { Write-Host $logMessage -ForegroundColor Yellow }
        default { Write-Host $logMessage }
    }
}

Write-Log "Starting automatic database sync..." "INFO"

# Run the auto-sync script
$phpPath = "php"  # Adjust if PHP is not in PATH
$syncScript = Join-Path $scriptPath "auto-sync-database.php"

if (-not (Test-Path $syncScript)) {
    Write-Log "Sync script not found: $syncScript" "ERROR"
    exit 1
}

# Execute sync
$output = & $phpPath $syncScript 2>&1
$exitCode = $LASTEXITCODE

if ($exitCode -eq 0) {
    Write-Log "Database sync completed successfully" "SUCCESS"
} else {
    Write-Log "Database sync failed with exit code: $exitCode" "ERROR"
    Write-Log "Output: $output" "ERROR"
}

Write-Log "Automatic database sync finished" "INFO"

exit $exitCode

