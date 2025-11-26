# 🔗 WordPress Database Connection Setup Guide
## Connecting s3vgroup.com → s3vtgroup.com.kh WordPress Database

---

## 📋 Overview

You want to import products from:
- **WordPress Site**: s3vtgroup.com.kh (source)
- **Your Site**: s3vgroup.com (destination)

---

## 🔍 Step 1: Get WordPress Database Credentials

### Option A: From WordPress cPanel (s3vtgroup.com.kh)

1. **Login to cPanel for s3vtgroup.com.kh**
   - URL: `https://s3vtgroup.com.kh:2083` or `https://s3vtgroup.com.kh/cpanel`

2. **Go to MySQL Databases**
   - Find "MySQL Databases" in cPanel
   - Or search for "MySQL"

3. **Find Database Information**
   - **Database Name**: Look in "Current Databases" section
   - **Database User**: Look in "Current Users" section
   - **Password**: Click "Change Password" to set/reset if needed

4. **Note the Host**
   - Usually `localhost` if WordPress is on the same server
   - Or might be `mysql.s3vtgroup.com.kh` or an IP address

### Option B: From wp-config.php (Easiest)

1. **Login to cPanel for s3vtgroup.com.kh**
2. **Go to File Manager**
3. **Navigate to WordPress root** (usually `public_html` or a subdirectory)
4. **Open `wp-config.php`**
5. **Find these lines:**
   ```php
   define( 'DB_NAME', 'database_name_here' );
   define( 'DB_USER', 'database_user_here' );
   define( 'DB_PASSWORD', 'database_password_here' );
   define( 'DB_HOST', 'localhost' );
   ```
6. **Copy these exact values**

---

## 🔧 Step 2: Configure Connection in s3vgroup.com

### If WordPress is on the SAME server:

1. **Go to**: `https://s3vgroup.com/admin/wordpress-sql-import.php`
2. **Enter credentials**:
   - **Host**: `localhost` (or the host from wp-config.php)
   - **Database**: The database name from wp-config.php
   - **Username**: The database user from wp-config.php
   - **Password**: The database password from wp-config.php
   - **Prefix**: Usually `wp_` or check wp-config.php for `$table_prefix`

### If WordPress is on a DIFFERENT server:

You have two options:

#### Option 1: Remote MySQL Connection (Recommended)

1. **Enable Remote MySQL in WordPress cPanel**:
   - Login to cPanel for s3vtgroup.com.kh
   - Go to "Remote MySQL" or "Remote Database Access"
   - Add your s3vgroup.com server IP address
   - Or add `%` to allow all (less secure but easier)

2. **Use the WordPress server IP or hostname**:
   - **Host**: The IP address or hostname of s3vtgroup.com.kh server
   - **Database**: WordPress database name
   - **Username**: WordPress database user
   - **Password**: WordPress database password
   - **Port**: Usually `3306` (default MySQL port)

#### Option 2: Use SSH Tunnel (Advanced)

If remote MySQL is blocked, you can use an SSH tunnel (requires SSH access).

---

## 🧪 Step 3: Test Connection

1. **Fill in the form** with WordPress database credentials
2. **Click "Test Connection"**
3. **You should see**:
   - ✅ Connection successful
   - Number of products found
   - Number of categories found
   - WordPress version

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Access Denied"

**Problem**: Database user doesn't have permission or wrong credentials

**Solution**:
- Verify username and password in wp-config.php
- Check if user has access to the database in cPanel
- Try resetting the password in cPanel → MySQL Databases

### Issue 2: "Cannot Connect to Host"

**Problem**: Remote MySQL connection not allowed

**Solution**:
- If same server: Use `localhost` or `127.0.0.1`
- If different server:
  1. Enable "Remote MySQL" in WordPress cPanel
  2. Add s3vgroup.com server IP to allowed hosts
  3. Use WordPress server IP/hostname instead of localhost

### Issue 3: "Database Not Found"

**Problem**: Wrong database name

**Solution**:
- Check exact database name in cPanel → MySQL Databases
- Database name might have a prefix (e.g., `username_dbname`)
- Verify in wp-config.php

### Issue 4: "No Products Found"

**Problem**: Wrong table prefix

**Solution**:
- Check `$table_prefix` in wp-config.php
- Common prefixes: `wp_`, `wpg1_`, `kdmedsco_`
- Try different prefixes if needed

---

## 📝 Quick Reference

### Credentials Location

| Source | Location |
|--------|----------|
| **Easiest** | `wp-config.php` file |
| **cPanel** | MySQL Databases section |
| **phpMyAdmin** | Connection settings |

### Connection Settings

| Setting | Value |
|---------|-------|
| **Host (same server)** | `localhost` |
| **Host (different server)** | WordPress server IP/hostname |
| **Port** | `3306` (default) |
| **Database** | From wp-config.php |
| **Username** | From wp-config.php |
| **Password** | From wp-config.php |
| **Prefix** | From wp-config.php (`$table_prefix`) |

---

## 🎯 Step-by-Step Checklist

- [ ] Login to WordPress cPanel (s3vtgroup.com.kh)
- [ ] Get database credentials from wp-config.php or MySQL Databases
- [ ] Note: Database name, username, password, host, prefix
- [ ] If different servers: Enable Remote MySQL in WordPress cPanel
- [ ] Login to s3vgroup.com admin panel
- [ ] Go to WordPress SQL Import page
- [ ] Enter WordPress database credentials
- [ ] Click "Test Connection"
- [ ] Verify connection successful
- [ ] Configure import options
- [ ] Start import

---

## 💡 Pro Tips

1. **Use wp-config.php credentials** - They're already working for WordPress
2. **Save configuration** - Use "Save Configuration" button to store credentials
3. **Test first** - Always test connection before importing
4. **Check table prefix** - Very important! Wrong prefix = no products found
5. **Remote access** - If connecting from different server, ensure Remote MySQL is enabled

---

## 🆘 Still Having Issues?

1. **Check error message** - It usually tells you what's wrong
2. **Verify credentials** - Double-check in wp-config.php
3. **Test in phpMyAdmin** - If you can connect there, use same credentials
4. **Check server logs** - Look for database connection errors
5. **Contact hosting** - If remote MySQL is blocked, ask hosting to enable it

---

**Ready to connect?** Get your WordPress database credentials and test the connection! 🚀

