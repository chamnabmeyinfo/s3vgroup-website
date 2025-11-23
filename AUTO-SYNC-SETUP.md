# 🚀 Automatic Database Sync - Quick Setup

## ⚡ One-Command Automatic Sync

Your database will automatically sync from cPanel to localhost when live has updates!

---

## 📋 Setup Steps (5 Minutes)

### Step 1: Create Live Database Config

```powershell
# Copy the example file
copy config\database.live.php.example config\database.live.php
```

### Step 2: Edit Live Database Credentials

Open `config/database.live.php` and fill in your cPanel database info:

```php
return [
    'host' => 'your-cpanel-domain.com',  // Your cPanel domain or IP
    'database' => 'username_dbname',      // From cPanel MySQL Databases
    'username' => 'username_dbuser',      // From cPanel MySQL Databases
    'password' => 'your_password',        // Your database password
    'charset' => 'utf8mb4',
];
```

**Where to find these:**
- Login to **cPanel**
- Go to **MySQL Databases**
- You'll see: Database name, Username, Password

### Step 3: Enable Remote MySQL (Important!)

1. Login to **cPanel**
2. Go to **Remote MySQL**
3. Add your **local computer's IP address**
   - Find your IP: https://whatismyipaddress.com/
   - Or use `%` to allow all IPs (less secure, but easier)
4. Click **Add Host**

**Alternative:** If you can't enable Remote MySQL, the script will show you how to use phpMyAdmin export instead.

### Step 4: Test Automatic Sync

```powershell
cd C:\xampp\htdocs\s3vgroup
php bin/auto-sync-database.php
```

**What it does:**
1. ✅ Connects to both databases
2. ✅ Compares last update times
3. ✅ Shows comparison
4. ✅ Auto-imports if live is newer

---

## 🎯 Usage

### Run Automatic Sync

```powershell
php bin/auto-sync-database.php
```

**Output Example:**
```
═══════════════════════════════════════
  Automatic Database Synchronization
═══════════════════════════════════════

ℹ️  Connecting to local database...
✅ Connected to local database: s3vgroup_local

ℹ️  Connecting to live database (cPanel)...
✅ Connected to live database: username_s3vgroup

═══════════════════════════════════════
  Comparing Databases
═══════════════════════════════════════

Local Database:
  Tables: 15
  Rows: 1,234
  Last Update: 2025-01-15 10:30:00

Live Database:
  Tables: 15
  Rows: 1,456
  Last Update: 2025-01-15 14:45:00

Decision: Live database is newer (by 255 minutes)

═══════════════════════════════════════
  Starting Automatic Sync
═══════════════════════════════════════

ℹ️  Step 1: Exporting from live database...
✅ Exported 245,678 bytes

ℹ️  Step 2: Importing to local database...
✅ Imported 1,234 statement(s)

✅ Database synchronized successfully!
```

### Options

```powershell
# Check only (don't sync)
php bin/auto-sync-database.php --check-only

# Force sync (even if same)
php bin/auto-sync-database.php --force
```

---

## ⏰ Schedule Automatic Sync (Optional)

### Windows Scheduled Task

1. Open **Task Scheduler** (search in Windows)
2. Click **Create Basic Task**
3. Name: "Sync Database from cPanel"
4. Trigger: **Daily** or **When I log on**
5. Action: **Start a program**
6. Program: `powershell.exe`
7. Arguments: `-File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-scheduled.ps1"`
8. ✅ Check "Run with highest privileges"
9. Save

**Now it will sync automatically!**

---

## 🔧 Troubleshooting

### Error: "Cannot connect to live database"

**Solution 1: Enable Remote MySQL**
- Go to cPanel → Remote MySQL
- Add your IP address

**Solution 2: Use Alternative Method**
- Export from phpMyAdmin manually
- Use: `php bin/sync-database.php import local database-live.sql --force`

### Error: "Access denied"

**Check:**
- Database credentials are correct
- Remote MySQL is enabled
- Your IP is whitelisted
- Database user has proper permissions

### Error: "Connection timeout"

**Solutions:**
- Check if cPanel allows remote connections
- Try using cPanel server IP instead of domain
- Check firewall settings

---

## ✅ What Gets Synced

**Synced:**
- ✅ All tables and structure
- ✅ All data (products, categories, team, etc.)
- ✅ Site options and settings
- ✅ Everything in the database

**Not Synced:**
- ❌ Uploaded files (`uploads/` folder)
- ❌ Configuration files
- ❌ Code files

---

## 🎉 Result

After setup, just run:
```powershell
php bin/auto-sync-database.php
```

And your local database will automatically match the live database! 🚀

---

**Status:** ✅ Ready to use

**Next:** Run the setup steps above, then test with `php bin/auto-sync-database.php`

