# Fix Connection Issue: "MySQL server has gone away"

## Current Status

✅ Config file exists and is correct  
✅ All credentials are present  
❌ Connection failed: "MySQL server has gone away"

## What This Error Means

The error "MySQL server has gone away" usually means:
1. **Remote MySQL is not enabled** in cPanel
2. **Your IP address is not whitelisted** in cPanel Remote MySQL
3. **Wrong host** - For remote connections, you might need the server IP instead of domain
4. **Firewall blocking** the connection

## Solutions

### Solution 1: Enable Remote MySQL in cPanel (Recommended)

If you're connecting from your local computer to cPanel:

1. **Login to cPanel**
2. **Go to:** Remote MySQL (under Databases section)
3. **Add your IP address:**
   - Find your current IP: https://whatismyipaddress.com/
   - Add it to "Access Hosts"
   - Click "Add Host"
4. **Try again:**
   ```bash
   php bin/test-live-connection.php
   ```

### Solution 2: Use Server IP Instead of Domain

If `s3vgroup.com` doesn't work, try using your server's IP address:

1. **Find your server IP:**
   - In cPanel, look at the server information
   - Or check your hosting account details

2. **Update `config/database.live.php`:**
   ```php
   'host' => '123.456.789.0',  // Your server IP instead of domain
   ```

### Solution 3: Use localhost (If Running on cPanel Server)

If you plan to run the sync script **on the cPanel server itself** (via SSH or cron):

1. **Update `config/database.live.php`:**
   ```php
   'host' => 'localhost',  // Use localhost when on the server
   ```

### Solution 4: Test Connection in phpMyAdmin First

Before troubleshooting further:

1. **Login to cPanel → phpMyAdmin**
2. **Try to connect** using the same credentials
3. **If it works in phpMyAdmin**, the credentials are correct
4. **The issue is likely Remote MySQL** or firewall

## Quick Fix Checklist

- [ ] Check if Remote MySQL is enabled in cPanel
- [ ] Add your local IP to Remote MySQL whitelist
- [ ] Try using server IP instead of domain name
- [ ] Test connection in phpMyAdmin first
- [ ] Check cPanel firewall settings
- [ ] Verify database credentials are correct

## Alternative: Run Sync on cPanel Server

If remote connection is too complicated, you can:

1. **Upload the sync script to cPanel**
2. **Run it via SSH or cPanel Terminal:**
   ```bash
   cd /home/username/public_html
   php bin/auto-sync-schema.php
   ```
3. **Use `localhost` as host** in the config

## Next Steps

1. **Enable Remote MySQL** in cPanel and add your IP
2. **Test again:** `php bin/test-live-connection.php`
3. **Once it works**, run: `php bin/auto-sync-schema.php`

---

## Still Having Issues?

If you're still stuck:

1. **Check cPanel → MySQL Databases** - Verify database name and username are correct
2. **Check cPanel → Remote MySQL** - Make sure it's enabled and your IP is added
3. **Contact your hosting provider** - They can help with firewall/network issues
4. **Try SSH access** - Run the script directly on the server instead of remotely

