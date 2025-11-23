# Fix Live Database Connection

## Current Issue

The database manager can't connect to your live database. Let's fix it!

## Step 1: Check Your Current Configuration

Your `config/database.live.php` currently has:
- Host: `s3vgroup.com`
- Database: `s3vgroup_website`
- Username: `s3vgroup_main`

## Step 2: Enable Remote MySQL in cPanel

The connection is failing because Remote MySQL is likely not enabled or your IP isn't whitelisted.

### Option A: Enable Remote MySQL (Recommended)

1. **Login to cPanel**
2. **Go to:** Remote MySQL (under Databases section)
3. **Find your current IP:**
   - Visit: https://whatismyipaddress.com/
   - Copy your IP address (e.g., `123.456.789.0`)
4. **Add your IP:**
   - In Remote MySQL, paste your IP in "Access Hosts"
   - Click "Add Host"
5. **Save**

### Option B: Use localhost (If Running on Server)

If you plan to run the script **on the cPanel server itself** (via SSH), change the host to `localhost`:

```php
'host' => 'localhost',  // When running on the server
```

### Option C: Use Server IP

If the domain doesn't work, try your server's IP address:

1. **Find your server IP** (check cPanel server info or hosting account)
2. **Update `config/database.live.php`:**
   ```php
   'host' => '123.456.789.0',  // Your server IP
   ```

## Step 3: Test the Connection

After making changes, test:

```bash
php bin/test-live-connection.php
```

**Expected output:**
```
✅ Connection successful!
✅ Connected to database: s3vgroup_website
✅ Found X table(s)
```

## Step 4: Try Database Manager Again

Once connection works:

```bash
# List tables
php bin/db-manager.php list-tables live

# Describe a table
php bin/db-manager.php describe team_members live
```

## Common Issues

### Issue 1: "MySQL server has gone away"

**Cause:** Remote MySQL not enabled or IP not whitelisted

**Fix:**
- Enable Remote MySQL in cPanel
- Add your IP to whitelist
- Or use `localhost` if running on server

### Issue 2: "Access denied"

**Cause:** Wrong username or password

**Fix:**
- Verify credentials in cPanel → MySQL Databases
- Check username format: `username_database_user`
- Reset password if needed

### Issue 3: "Unknown database"

**Cause:** Wrong database name

**Fix:**
- Check exact database name in cPanel → MySQL Databases
- Format is usually: `username_database_name`

## Quick Test Checklist

- [ ] Remote MySQL enabled in cPanel
- [ ] Your IP added to Remote MySQL whitelist
- [ ] Host is correct (`localhost`, IP, or domain)
- [ ] Database name is correct
- [ ] Username is correct
- [ ] Password is correct
- [ ] Test connection: `php bin/test-live-connection.php`

## Alternative: Test in phpMyAdmin First

Before troubleshooting further:

1. **Login to cPanel → phpMyAdmin**
2. **Try to connect** using the same credentials
3. **If it works in phpMyAdmin**, credentials are correct
4. **The issue is Remote MySQL** or network/firewall

## After Connection Works

Once `php bin/test-live-connection.php` shows success, you can use all database manager commands:

```bash
# List all tables
php bin/db-manager.php list-tables live

# View table structure
php bin/db-manager.php describe team_members live

# Add a column
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title live

# Run queries
php bin/db-manager.php query "SELECT * FROM team_members LIMIT 5" live
```

---

**The connection must work first before you can use the database manager!**

