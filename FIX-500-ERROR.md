# 🔧 Fix HTTP 500 Error - Quick Guide

## 🚨 Your Issue
**Website showing:** "HTTP ERROR 500" on `s3vgroup.com`

---

## ⚡ Quick Fix Steps

### Step 1: Upload Diagnostic Tool

1. **Push new file to GitHub:**
   ```powershell
   cd C:\xampp\htdocs\s3vgroup
   git add debug-500-error.php
   git commit -m "Add 500 error diagnostic tool"
   git push
   ```

2. **Pull to cPanel:**
   - cPanel → Git Version Control → Pull or Deploy → Update

3. **Visit diagnostic tool:**
   ```
   https://s3vgroup.com/debug-500-error.php
   ```

   **This will show exactly what's wrong!**

---

## 🔍 Common Causes & Quick Fixes

### Cause 1: Missing .env File or Database Config ❌

**Fix:**
1. In cPanel → File Manager → `public_html/`
2. Create `.env` file with:
   ```env
   DB_HOST=localhost
   DB_DATABASE=your_cpanel_db_name
   DB_USERNAME=your_cpanel_db_user
   DB_PASSWORD=your_cpanel_db_password
   DB_CHARSET=utf8mb4
   ```
3. Or create `config/database.local.php`:
   ```php
   <?php
   return [
       'host' => 'localhost',
       'database' => 'your_cpanel_db_name',
       'username' => 'your_cpanel_db_user',
       'password' => 'your_cpanel_db_password',
       'charset' => 'utf8mb4',
   ];
   ```

---

### Cause 2: Database Connection Failed ❌

**Fix:**
1. Verify database credentials in `.env` or `database.local.php`
2. Check database name includes username prefix (e.g., `username_dbname`)
3. Verify user has ALL PRIVILEGES on database
4. Test connection in cPanel → phpMyAdmin

---

### Cause 3: PHP Version Too Old ❌

**Fix:**
1. cPanel → **Select PHP Version**
2. Choose **PHP 7.4** or higher
3. Click **Set as current**

---

### Cause 4: File Permissions Wrong ❌

**Fix:**
1. cPanel → File Manager → `public_html/`
2. Select all folders → Right-click → **Change Permissions** → Set to **`755`**
3. Select all files → Right-click → **Change Permissions** → Set to **`644`**

---

### Cause 5: Missing Required Files ❌

**Fix:**
1. Verify all files were pulled from GitHub
2. Check if `bootstrap/app.php` exists
3. Check if `config/database.php` exists
4. Check if `config/site.php` exists

---

### Cause 6: .htaccess Issues ❌

**Fix:**
1. Check if `.htaccess` exists in `public_html/`
2. If missing, create it (already in your code)
3. Set permissions to `644`
4. Try renaming `.htaccess` to `.htaccess.bak` temporarily to test

---

### Cause 7: PHP Errors ❌

**Fix:**
1. Check cPanel → **Errors** or **Logs**
2. Look for PHP error messages
3. Fix the specific error shown

---

## 🎯 Most Likely Causes (In Order)

1. **Missing `.env` file or database credentials** (most common!)
2. **Database connection failed**
3. **PHP version too old**
4. **File permissions wrong**
5. **Missing required files**

---

## 📋 Quick Diagnostic Checklist

Use `debug-500-error.php` or check manually:

- [ ] `.env` file exists in `public_html/` with correct database credentials
- [ ] OR `config/database.local.php` exists with correct credentials
- [ ] Database name includes username prefix
- [ ] Database user has ALL PRIVILEGES
- [ ] PHP version is 7.4+
- [ ] File permissions: folders = 755, files = 644
- [ ] `.htaccess` file exists
- [ ] `bootstrap/app.php` exists
- [ ] `config/database.php` exists
- [ ] `config/site.php` exists

---

## 🚀 Quick Action Plan

### Option 1: Use Diagnostic Tool (Recommended)

1. **Upload `debug-500-error.php`** to `public_html/`
2. **Visit:** `https://s3vgroup.com/debug-500-error.php`
3. **Read the results** - it will tell you exactly what's wrong
4. **Fix the issues** shown
5. **Delete `debug-500-error.php`** after fixing

---

### Option 2: Manual Check

1. **Check cPanel error logs:**
   - cPanel → **Errors** or **Logs**
   - Look for recent PHP errors

2. **Create `.env` file** if missing:
   - File Manager → `public_html/` → New File → `.env`
   - Add database credentials

3. **Verify database exists:**
   - cPanel → MySQL Databases
   - Check database is created
   - Check user is added to database

4. **Test database connection:**
   - cPanel → phpMyAdmin
   - Try to connect with your credentials

---

## 🔥 Most Common Fix

**90% of 500 errors are caused by missing database configuration!**

**Quick fix:**
1. Create `.env` file in `public_html/`:
   ```env
   DB_HOST=localhost
   DB_DATABASE=your_cpanel_db_name
   DB_USERNAME=your_cpanel_db_user
   DB_PASSWORD=your_cpanel_db_password
   DB_CHARSET=utf8mb4
   ```
2. Replace with your actual cPanel database credentials
3. Save file
4. Visit your website - should work!

---

## 📞 Need More Help?

1. **Run diagnostic tool:** `https://s3vgroup.com/debug-500-error.php`
2. **Check error logs:** cPanel → Errors
3. **Share the error message** from logs or diagnostic tool

---

**After fixing, DELETE `debug-500-error.php` for security!** 🗑️

