# 🚀 Complete Live Website Setup Guide

## Step-by-Step Instructions for cPanel

Follow these steps in order to configure your live website.

---

## 📋 Prerequisites Checklist

Before starting, make sure you have:
- ✅ Code pushed to GitHub
- ✅ Code deployed to cPanel (in `public_html/`)
- ✅ cPanel login credentials
- ✅ Domain name ready
- ✅ Database credentials from cPanel

---

## Step 1: Create MySQL Database in cPanel

### 1.1. Create Database

1. Log into **cPanel**
2. Find **"MySQL Databases"** (usually in "Databases" section)
3. Scroll to **"Create New Database"**
4. Enter name: `s3vgroup_db` (or your preferred name)
5. Click **"Create Database"**
6. **Note the full database name** - it will be like `username_s3vgroup_db`

### 1.2. Create Database User

1. Scroll to **"MySQL Users"** section
2. Enter username: `s3vgroup_user` (or your preferred name)
3. Enter password: **Create a strong password** (save this!)
4. Click **"Create User"**
5. **Note the full username** - it will be like `username_s3vgroup_user`

### 1.3. Add User to Database

1. Scroll to **"Add User to Database"** section
2. Select your **User**: `username_s3vgroup_user`
3. Select your **Database**: `username_s3vgroup_db`
4. Click **"Add"**
5. **Check "ALL PRIVILEGES"** ✅
6. Click **"Make Changes"**

### 1.4. Note Down Your Credentials

Write these down (you'll need them):

```
Database Name: username_s3vgroup_db
Database User: username_s3vgroup_user
Database Password: [the password you created]
Database Host: localhost (usually)
```

---

## Step 2: Import Database Schema

### 2.1. Open phpMyAdmin

1. In cPanel, find **"phpMyAdmin"** (in "Databases" section)
2. Click to open it

### 2.2. Select Your Database

1. In the left sidebar, click on your database name: `username_s3vgroup_db`
2. If the database is empty, that's fine - we'll import now

### 2.3. Import Schema

1. Click **"Import"** tab (top menu)
2. Click **"Choose File"** button
3. Navigate to: `public_html/sql/schema.sql`
4. Select the file: `schema.sql`
5. Click **"Go"** at the bottom
6. Wait for import to complete
7. You should see: **"Import has been successfully finished"**

✅ **Success**: You should now see tables in your database:
- `categories`
- `products`
- `quote_requests`
- `site_options`
- `testimonials`
- etc.

---

## Step 3: Create Database Configuration File

### 3.1. Open File Manager

1. In cPanel, find **"File Manager"** (in "Files" section)
2. Navigate to: `public_html/config/`

### 3.2. Create `database.local.php`

1. Click **"File"** → **"New File"**
2. Name: `database.local.php`
3. Click **"Create New File"**
4. Double-click the file to edit it

### 3.3. Add This Code:

Replace `YOUR_*` values with your actual database credentials:

```php
<?php
/**
 * Live Server Database Configuration
 * This file overrides default settings
 */
return [
    'host' => 'localhost',
    'database' => 'YOUR_DATABASE_NAME',      // e.g., username_s3vgroup_db
    'username' => 'YOUR_DATABASE_USER',      // e.g., username_s3vgroup_user
    'password' => 'YOUR_DATABASE_PASSWORD',  // Your password
    'charset' => 'utf8mb4',
];
```

**Example** (replace with yours):
```php
<?php
return [
    'host' => 'localhost',
    'database' => 'myname_s3vgroup_db',
    'username' => 'myname_s3vgroup_user',
    'password' => 'MySecurePassword123!',
    'charset' => 'utf8mb4',
];
```

5. Click **"Save Changes"**
6. Close the editor

---

## Step 4: Update Site Configuration

### 4.1. Edit `site.php`

1. In File Manager, navigate to: `public_html/config/`
2. Right-click `site.php` → **"Edit"**
3. Find this line:
   ```php
   'url' => 'http://localhost:8080',
   ```
4. Change it to your live domain:
   ```php
   'url' => 'https://yourdomain.com',  // Replace with your actual domain
   ```

### 4.2. Change Admin Password

1. Find these lines:
   ```php
   define('ADMIN_EMAIL', 'admin@s3vtgroup.com');
   define('ADMIN_PASSWORD', 'admin123');
   ```
2. Change the password to something secure:
   ```php
   define('ADMIN_EMAIL', 'admin@s3vtgroup.com');
   define('ADMIN_PASSWORD', 'YourSecurePassword123!');  // Change this!
   ```

3. Click **"Save Changes"**

---

## Step 5: Set File Permissions

### 5.1. Set Folder Permissions

1. In File Manager, select these folders:
   - `public_html/uploads/`
   - `public_html/config/`
2. Right-click → **"Change Permissions"**
3. Set to: **`755`**
   - Owner: `7` (Read, Write, Execute)
   - Group: `5` (Read, Execute)
   - Public: `5` (Read, Execute)
4. Click **"Change Permissions"**

### 5.2. Set File Permissions

1. Select all PHP files in `public_html/`
2. Right-click → **"Change Permissions"**
3. Set to: **`644`**
   - Owner: `6` (Read, Write)
   - Group: `4` (Read)
   - Public: `4` (Read)
4. Click **"Change Permissions"**

### 5.3. Verify .htaccess Exists

1. In `public_html/`, check if `.htaccess` file exists
2. If missing, see Step 6 below

---

## Step 6: Verify .htaccess File

### 6.1. Check if .htaccess Exists

1. In File Manager, go to `public_html/`
2. Make sure **"Show Hidden Files"** is enabled (Settings → Show Hidden Files)
3. Look for `.htaccess` file

### 6.2. If .htaccess is Missing

1. Click **"File"** → **"New File"**
2. Name: `.htaccess`
3. Double-click to edit
4. Add this content:

```apache
# Enable Rewrite Engine
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to index.php if file doesn't exist
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security: Prevent directory listing
Options -Indexes

# Security: Protect config files
<FilesMatch "^(database|site)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security: Protect database.local.php
<FilesMatch "database\.local\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable PHP error reporting (disable in production)
# php_flag display_errors Off
# php_value error_reporting 0
```

5. Click **"Save Changes"**
6. Set permissions to `644`

---

## Step 7: Test Your Website

### 7.1. Test Homepage

1. Open your browser
2. Visit: `https://yourdomain.com`
3. You should see the homepage ✅

### 7.2. Test Admin Login

1. Visit: `https://yourdomain.com/admin/login.php`
2. Enter:
   - **Email**: `admin@s3vtgroup.com` (or what you set in `site.php`)
   - **Password**: The password you set in `site.php`
3. Click **"Login"**
4. You should see the admin dashboard ✅

### 7.3. Check Database Connection

If you see database errors:

1. Verify `database.local.php` has correct credentials
2. Check database name includes username prefix
3. Verify user has ALL PRIVILEGES
4. Check error logs in cPanel → Errors

---

## 🔧 Troubleshooting

### Error: "Database connection failed"

**Solutions:**
1. Check `database.local.php` credentials are correct
2. Verify database name includes username prefix: `username_dbname`
3. Try host `127.0.0.1` instead of `localhost`
4. Check database user has ALL PRIVILEGES
5. Verify database exists in phpMyAdmin

---

### Error: "500 Internal Server Error"

**Solutions:**
1. Check error logs: cPanel → Errors or Logs
2. Verify PHP version (needs 7.4+): cPanel → Select PHP Version
3. Check file permissions (folders: 755, files: 644)
4. Verify `.htaccess` file exists
5. Check `config/` folder permissions

---

### Error: "Page not found" or "404"

**Solutions:**
1. Check `.htaccess` file exists
2. Verify RewriteEngine is enabled
3. Check file permissions on `.htaccess` (should be 644)
4. Verify you're using correct domain URL

---

### Admin Login Not Working

**Solutions:**
1. Check `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `site.php`
2. Clear browser cookies
3. Verify session is enabled in PHP
4. Check PHP error logs

---

## ✅ Verification Checklist

Use this checklist to verify everything is set up:

- [ ] Database created in cPanel MySQL Databases
- [ ] Database user created and added to database
- [ ] User has ALL PRIVILEGES on database
- [ ] Database schema imported via phpMyAdmin (tables exist)
- [ ] `database.local.php` created in `public_html/config/`
- [ ] Database credentials correct in `database.local.php`
- [ ] `site.php` updated with live domain URL
- [ ] Admin password changed in `site.php`
- [ ] File permissions set (folders: 755, files: 644)
- [ ] `.htaccess` file exists in `public_html/`
- [ ] Homepage loads: `https://yourdomain.com`
- [ ] Admin login works: `https://yourdomain.com/admin/login.php`
- [ ] No database errors on pages

---

## 📝 Configuration Files Reference

### `config/database.local.php` (Live Server)

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'username_s3vgroup_db',
    'username' => 'username_s3vgroup_user',
    'password' => 'YourPassword123!',
    'charset' => 'utf8mb4',
];
```

### `config/site.php` (Key Settings)

```php
$siteConfig = [
    'url' => 'https://yourdomain.com',  // ← Must change!
    // ... rest stays same
];

define('ADMIN_EMAIL', 'admin@s3vtgroup.com');
define('ADMIN_PASSWORD', 'YourSecurePassword!');  // ← Must change!
```

---

## 🎉 Success!

Once all steps are complete, your website should be live and working!

**Next Steps:**
1. Test all features
2. Add products/categories via admin panel
3. Test contact forms
4. Set up SSL certificate (if not already done)
5. Configure backups

---

## 🆘 Still Not Working?

If you're still having issues:

1. **Check Error Logs**: cPanel → Errors
2. **Run Diagnostic Script**: Upload `test-connection.php` (see below)
3. **Contact Support**: Share error messages for help

---

**Need help?** Share the specific error message you're seeing!

