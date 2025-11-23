# Create config/database.live.php

## Quick Setup

The file `config/database.live.php` doesn't exist yet. Let's create it!

### Step 1: Copy the Example File

**In PowerShell:**
```powershell
cd C:\xampp\htdocs\s3vgroup
Copy-Item "config\database.live.php.example" "config\database.live.php"
```

**Or manually:**
1. Go to `config` folder
2. Copy `database.live.php.example`
3. Rename to `database.live.php`

### Step 2: Edit with Your cPanel Credentials

Open `config/database.live.php` and fill in your cPanel database info:

```php
<?php
return [
    // Database host
    // For cPanel, usually 'localhost' or your server IP
    'host' => 'localhost',  // ← Change this
    
    // Database name (from cPanel → MySQL Databases)
    // Format: username_database_name
    'database' => 'your_username_database_name',  // ← Change this
    
    // Database username (from cPanel → MySQL Databases)
    // Format: username_database_user
    'username' => 'your_username_database_user',  // ← Change this
    
    // Database password
    'password' => 'your_database_password',  // ← Change this
    
    // Character set
    'charset' => 'utf8mb4',
];
```

### Step 3: Get Your cPanel Database Info

1. **Login to cPanel**
2. **Go to:** MySQL Databases
3. **Find:**
   - Database name (e.g., `username_s3vgroup_db`)
   - Database user (e.g., `username_s3vgroup_user`)
   - Password (you set this when creating the database)

### Step 4: Test the Connection

After saving the file, test it:

```bash
php bin/test-live-connection.php
```

**Expected output:**
```
✅ Connection successful!
✅ Connected to database: your_database_name
```

### Step 5: Run Auto Sync

Once connection works:

```bash
php bin/auto-sync-schema.php
```

This will automatically sync missing columns!

---

## Important Notes

1. **Security:** This file is in `.gitignore` - it won't be committed to GitHub
2. **Host:** For cPanel, usually `localhost`. If connecting remotely, use server IP
3. **Remote MySQL:** If connecting from outside cPanel, enable Remote MySQL in cPanel and whitelist your IP

---

## Troubleshooting

### Can't Connect?

**Check:**
- Host is correct (`localhost` for cPanel)
- Database name is correct (check cPanel → MySQL Databases)
- Username is correct
- Password is correct
- Remote MySQL enabled (if connecting remotely)

**Test in phpMyAdmin first** to verify credentials work!

---

## Next Steps

After `config/database.live.php` is working:

1. ✅ Test: `php bin/test-live-connection.php`
2. ✅ Sync: `php bin/auto-sync-schema.php`
3. ✅ Automate: Set up Task Scheduler (see `QUICK-START-AUTO-SYNC.md`)

