# Quick Start: Automatic Schema Sync

## ✅ You Already Have: `config/database.live.php` Configured

Great! Now let's make it work automatically.

## Step 1: Test the Connection

First, let's make sure everything works:

```bash
php bin/auto-sync-schema.php
```

This will:
- ✅ Connect to your local database
- ✅ Connect to your cPanel database
- ✅ Check for missing columns
- ✅ **Automatically add them** (if any are missing)
- ✅ Show you what was done

**Expected output:**
```
═══════════════════════════════════════
  Automatic Schema Sync to Live
═══════════════════════════════════════

ℹ️  Starting schema sync from local: s3vgroup_local
ℹ️  Connected to live database: your_live_db
✅ Added column 'department' to table 'team_members'
✅ Applied 1 change(s) across 1 table(s)
```

If you see errors, check:
- Database credentials in `config/database.live.php`
- cPanel Remote MySQL access (if needed)
- Your IP is whitelisted in cPanel → Remote MySQL

---

## Step 2: Make It Automatic (Windows Task Scheduler)

### Option A: Quick Setup (5 minutes)

1. **Open Task Scheduler:**
   - Press `Win + R`
   - Type: `taskschd.msc`
   - Press Enter

2. **Create Task:**
   - Click "Create Basic Task" (right side)
   - Name: `Auto Sync Database Schema`
   - Description: `Automatically sync database schema from local to cPanel`
   - Click Next

3. **Set Trigger:**
   - Choose: **Daily**
   - Start time: `2:00 AM` (or any time you prefer)
   - Click Next

4. **Set Action:**
   - Action: **Start a program**
   - Program/script: `powershell.exe`
   - Add arguments: `-ExecutionPolicy Bypass -File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-schema-scheduled.ps1"`
   - **⚠️ Important:** Update the path if your project is in a different location!
   - Click Next

5. **Finish:**
   - Check "Open the Properties dialog"
   - Click Finish

6. **Configure Properties:**
   - **General tab:**
     - ✅ Check "Run whether user is logged on or not"
     - ✅ Check "Run with highest privileges"
   - **Conditions tab:**
     - ✅ Uncheck "Start the task only if the computer is on AC power" (if you want it to run on battery too)
   - **Settings tab:**
     - ✅ Check "Allow task to be run on demand"
   - Click **OK**

7. **Test It:**
   - Right-click the task → **Run**
   - Check `storage/logs/schema-sync.log` to see results

### Option B: Manual PowerShell Script

If Task Scheduler doesn't work, you can run the PowerShell script manually:

```powershell
cd C:\xampp\htdocs\s3vgroup
.\bin\auto-sync-schema-scheduled.ps1
```

Or create a batch file (`run-schema-sync.bat`):

```batch
@echo off
cd /d C:\xampp\htdocs\s3vgroup
php bin\auto-sync-schema.php --quiet
pause
```

---

## Step 3: Verify It's Working

### Check Logs

After the sync runs, check the log file:

```bash
# View the log
type storage\logs\schema-sync.log
```

Or open: `storage\logs\schema-sync.log` in a text editor

**What to look for:**
- ✅ `Schema sync completed successfully`
- ✅ `Applied X change(s)`
- ❌ Any ERROR messages

### Test Manually

You can always test manually:

```bash
php bin/auto-sync-schema.php
```

If it says "Schema is up to date! No changes needed." - that's perfect! ✅

---

## Step 4: How It Works

Once set up, the automatic sync will:

1. **Run on schedule** (daily at 2 AM, or whatever you set)
2. **Compare schemas** between local and live databases
3. **Detect missing columns** automatically
4. **Add missing columns** to live database
5. **Log everything** to `storage/logs/schema-sync.log`

**You don't need to do anything!** It just works. 🎉

---

## Troubleshooting

### Error: "Failed to connect to live database"

**Check:**
1. `config/database.live.php` exists and has correct credentials
2. Database host is correct (usually `localhost` for cPanel, or your server IP)
3. Database name, username, password are correct
4. cPanel allows remote MySQL connections (if not using localhost)

**Fix:**
- Verify credentials in `config/database.live.php`
- Test connection in phpMyAdmin
- Check cPanel → Remote MySQL (if needed)

### Error: "Table does not exist"

**Fix:**
- The table doesn't exist in live database yet
- Import `sql/schema.sql` first to create all tables

### Task Scheduler Not Running

**Check:**
1. Task is enabled (right-click → Enable)
2. Task has correct path to PowerShell script
3. Check "Last Run Result" in Task Scheduler
4. Check `storage/logs/schema-sync-scheduled.log` for errors

**Fix:**
- Update the path in Task Scheduler action
- Make sure PowerShell script path is correct
- Test the script manually first

### No Changes Detected

**This is GOOD!** ✅
- It means your databases are already in sync
- The script will continue checking and apply changes when needed

---

## Advanced: Sync Specific Table Only

If you only want to sync one table (faster):

```bash
php bin/auto-sync-schema.php --table=team_members
```

---

## Advanced: Run More Frequently

To run every hour instead of daily:

1. Open Task Scheduler
2. Find your task → Properties
3. Triggers tab → Edit
4. Change to: **Repeat task every: 1 hour**
5. Save

---

## What Gets Synced?

✅ **Automatically synced:**
- Missing columns (like `department`, `expertise`, etc.)
- Column types (VARCHAR, TEXT, INT, etc.)
- NULL/NOT NULL constraints
- Default values
- Column order

❌ **NOT synced:**
- Data (use `bin/sync-database.php` for data)
- Indexes (only columns)
- Foreign keys (only columns)

---

## Summary

**You're all set!** Here's what happens now:

1. ✅ You make schema changes locally (add columns, etc.)
2. ✅ Automatic sync runs on schedule (daily at 2 AM)
3. ✅ Changes are automatically applied to cPanel
4. ✅ Everything is logged
5. ✅ **Zero manual work required!** 🎉

**Next Steps:**
- Test it: `php bin/auto-sync-schema.php`
- Set up Task Scheduler (if you want it automatic)
- Check logs: `storage/logs/schema-sync.log`

That's it! Your databases will stay in sync automatically. 🚀

