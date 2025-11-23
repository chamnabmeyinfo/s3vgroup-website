# Setup Automatic Schema Sync - Step by Step

## ✅ You Said You Already Configured `config/database.live.php`

Let's verify and get it working!

## Step 1: Verify Config File Exists

Check if the file exists:

```powershell
# In PowerShell
Test-Path "C:\xampp\htdocs\s3vgroup\config\database.live.php"
```

**If it returns `False`**, the file doesn't exist. Create it:

```powershell
# Copy the example file
Copy-Item "config\database.live.php.example" "config\database.live.php"
```

**If it returns `True`**, the file exists. Continue to Step 2.

---

## Step 2: Test the Connection

Run the connection test:

```bash
php bin/test-live-connection.php
```

This will:
- ✅ Check if config file exists
- ✅ Verify config format
- ✅ Test database connection
- ✅ Show you exactly what's wrong (if anything)

**Expected output if working:**
```
✅ Connection successful!
✅ Connected to database: your_database_name
✅ Found X table(s)
```

**If you see errors**, the test will tell you exactly what's wrong.

---

## Step 3: Fix Common Issues

### Issue 1: File Not Found

**Error:** `config/database.live.php not found!`

**Fix:**
```powershell
cd C:\xampp\htdocs\s3vgroup
Copy-Item "config\database.live.php.example" "config\database.live.php"
```

Then edit `config/database.live.php` with your credentials.

---

### Issue 2: Wrong Config Format

**Error:** `Config file must return an array!`

**Fix:** Make sure your `config/database.live.php` looks like this:

```php
<?php
return [
    'host' => 'localhost',  // or your server IP/domain
    'database' => 'your_database_name',
    'username' => 'your_database_user',
    'password' => 'your_database_password',
    'charset' => 'utf8mb4',
];
```

**Important:** It must `return` an array, not just define variables!

---

### Issue 3: Connection Failed

**Error:** `Connection FAILED!`

**Common causes:**

1. **Wrong Host:**
   - For cPanel, try `localhost` first
   - Or use your server IP address
   - Or use your domain name

2. **Wrong Credentials:**
   - Check cPanel → MySQL Databases
   - Database name format: `username_database_name`
   - Username format: `username_database_user`

3. **Remote MySQL Not Enabled:**
   - If connecting from outside cPanel, enable Remote MySQL
   - cPanel → Remote MySQL
   - Add your local IP address

4. **Firewall Blocking:**
   - Check server firewall
   - Check cPanel firewall
   - Port 3306 must be open (if remote)

---

## Step 4: Once Connection Works

After `php bin/test-live-connection.php` shows success:

### Test the Auto Sync

```bash
php bin/auto-sync-schema.php
```

This will automatically sync any missing columns!

### Make It Automatic

**Windows Task Scheduler:**

1. Press `Win + R` → Type `taskschd.msc`
2. Create Basic Task
3. Name: "Auto Sync Database Schema"
4. Trigger: Daily at 2 AM
5. Action: Start a program
   - Program: `powershell.exe`
   - Arguments: `-ExecutionPolicy Bypass -File "C:\xampp\htdocs\s3vgroup\bin\auto-sync-schema-scheduled.ps1"`
6. Save

**Done!** It will run automatically every day.

---

## Quick Checklist

- [ ] `config/database.live.php` exists
- [ ] Config file returns array with: host, database, username, password
- [ ] `php bin/test-live-connection.php` shows success
- [ ] `php bin/auto-sync-schema.php` works
- [ ] Task Scheduler set up (optional, for automation)

---

## Need Help?

Run the test script and share the output:

```bash
php bin/test-live-connection.php
```

It will tell you exactly what's wrong and how to fix it!

