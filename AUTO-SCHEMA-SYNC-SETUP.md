# Automatic Schema Sync Setup

## Overview

This guide shows you how to set up **fully automatic** database schema synchronization from your local database to cPanel. Once set up, it will automatically detect and apply schema changes without any manual intervention.

## Quick Setup

### Step 1: Configure Live Database Connection

Create `config/database.live.php` (if not already created):

```bash
copy config\database.live.php.example config\database.live.php
```

Edit `config/database.live.php` with your cPanel credentials:

```php
<?php
return [
    'host' => 'your-cpanel-domain.com',  // or IP address
    'database' => 'your_live_database_name',
    'username' => 'your_live_database_user',
    'password' => 'your_live_database_password',
    'charset' => 'utf8mb4',
];
```

**Important:** This file is in `.gitignore` and won't be committed to GitHub.

### Step 2: Test the Automatic Sync

Run the automatic sync script manually first to make sure it works:

```bash
php bin/auto-sync-schema.php
```

This will:
- ✅ Connect to both databases
- ✅ Detect missing columns
- ✅ **Automatically apply changes** (no --apply flag needed!)
- ✅ Log everything to `storage/logs/schema-sync.log`

### Step 3: Set Up Automatic Execution

Choose one method below:

---

## Method 1: Windows Task Scheduler (Recommended for Windows)

### Setup

1. **Open Task Scheduler:**
   - Press `Win + R`
   - Type `taskschd.msc`
   - Press Enter

2. **Create Basic Task:**
   - Click "Create Basic Task" in the right panel
   - Name: "Auto Sync Database Schema"
   - Description: "Automatically sync database schema from local to cPanel"

3. **Set Trigger:**
   - Choose when to run (e.g., "Daily")
   - Set time (e.g., 2:00 AM)
   - Click Next

4. **Set Action:**
   - Action: "Start a program"
   - Program: `powershell.exe`
   - Arguments: `-ExecutionPolicy Bypass -File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-schema-scheduled.ps1"`
   - **Important:** Update the path to match your project location!

5. **Finish:**
   - Check "Open the Properties dialog"
   - Click Finish

6. **Configure Properties:**
   - General tab: Check "Run whether user is logged on or not"
   - General tab: Check "Run with highest privileges"
   - Settings tab: Check "Allow task to be run on demand"
   - Click OK

### Test It

1. Right-click the task → "Run"
2. Check `storage/logs/schema-sync-scheduled.log` for results
3. Check `storage/logs/schema-sync.log` for detailed sync info

---

## Method 2: Cron Job (Linux/Mac/cPanel)

### On Your Local Machine (Linux/Mac)

Add to crontab:

```bash
crontab -e
```

Add this line (runs daily at 2 AM):

```cron
0 2 * * * cd /path/to/s3vgroup && php bin/auto-sync-schema.php --quiet >> /dev/null 2>&1
```

**Or** run every hour:

```cron
0 * * * * cd /path/to/s3vgroup && php bin/auto-sync-schema.php --quiet >> /dev/null 2>&1
```

### On cPanel Server

If you want to run it on the cPanel server itself:

1. **SSH into cPanel**
2. **Add to crontab:**

```bash
crontab -e
```

```cron
0 2 * * * cd /home/username/public_html && php bin/auto-sync-schema.php --quiet >> /dev/null 2>&1
```

---

## Method 3: Git Hook (After Push)

Automatically sync schema after you push code to GitHub.

### Setup

Create `.git/hooks/post-push` (or use existing post-commit):

```bash
#!/bin/bash
# Auto-sync schema after push

cd /path/to/s3vgroup
php bin/auto-sync-schema.php --quiet
```

Make it executable:

```bash
chmod +x .git/hooks/post-push
```

---

## How It Works

1. **Detects Changes:**
   - Compares local database schema with live database
   - Finds missing columns automatically

2. **Applies Changes:**
   - Automatically adds missing columns
   - Preserves column types, nullability, defaults
   - Maintains column order

3. **Logs Everything:**
   - All actions logged to `storage/logs/schema-sync.log`
   - Errors are logged with full details
   - Success messages include what was changed

4. **Safe to Run:**
   - Won't duplicate columns
   - Won't delete data
   - Only adds missing columns
   - Can run multiple times safely

## Usage Examples

### Sync All Tables

```bash
php bin/auto-sync-schema.php
```

### Sync Specific Table

```bash
php bin/auto-sync-schema.php --table=team_members
```

### Quiet Mode (for cron/scheduled tasks)

```bash
php bin/auto-sync-schema.php --quiet
```

## Log Files

All sync operations are logged to:

- **`storage/logs/schema-sync.log`** - Detailed sync log
- **`storage/logs/schema-sync-scheduled.log`** - Scheduled task log (Windows)

Check these files to see what was synced and when.

## Troubleshooting

### Error: "Failed to connect to live database"

**Solution:**
1. Check `config/database.live.php` exists and has correct credentials
2. Verify cPanel allows remote MySQL connections
3. Check if your IP is whitelisted in cPanel → Remote MySQL

### Error: "Table does not exist"

**Solution:**
- The table doesn't exist in live database yet
- Create it first using `sql/schema.sql` or migrations

### No Changes Detected

**Solution:**
- This is good! It means your databases are already in sync
- The script will continue to check and apply changes when needed

### Script Not Running Automatically

**Solution:**
1. Check Task Scheduler (Windows) or crontab (Linux)
2. Verify the script path is correct
3. Check log files for errors
4. Test manually first: `php bin/auto-sync-schema.php`

## Best Practices

1. **Test First:** Always test manually before setting up automation
2. **Monitor Logs:** Check logs regularly to ensure it's working
3. **Backup:** Keep regular database backups (the script doesn't delete data, but backups are always good)
4. **Schedule Wisely:** Run during low-traffic hours (e.g., 2 AM)
5. **Version Control:** Keep `config/database.live.php` out of git (already in `.gitignore`)

## What Gets Synced?

✅ **Automatically synced:**
- Missing columns
- Column types (VARCHAR, TEXT, INT, etc.)
- NULL/NOT NULL constraints
- Default values
- Column order

❌ **NOT synced:**
- Data (use `bin/sync-database.php` for data)
- Indexes (only columns)
- Foreign keys (only columns)
- Table structure changes (only missing columns)

## Related Tools

- **`bin/sync-schema-to-live.php`** - Manual sync with dry-run mode
- **`bin/sync-database.php`** - Sync data (not schema)
- **`bin/auto-sync-database.php`** - Auto-sync data if live is newer
- **`bin/verify-database-schema.php`** - Verify schema is correct

## Security Notes

⚠️ **Important:**
- Never commit `config/database.live.php` to GitHub
- Use strong database passwords
- Only allow trusted IPs for remote MySQL access
- Monitor logs for suspicious activity

---

## Summary

Once set up, the automatic schema sync will:

1. ✅ Run automatically on schedule
2. ✅ Detect schema changes
3. ✅ Apply changes to cPanel
4. ✅ Log everything
5. ✅ Require zero manual intervention

**You'll never have to manually sync schema again!** 🎉

