# 🚀 Quick Database Setup - Make Website Work

## Your Database Credentials
- **Database:** `s3vgroup_website`
- **Username:** `s3vgroup_main`
- **Password:** `ASDasd12345$$$%%%`
- **Host:** `localhost`

---

## ⚡ Quick Setup (Choose One Method)

### Method 1: Use Auto-Creator (Easiest) ⭐

1. **Push files to GitHub:**
   ```powershell
   cd C:\xampp\htdocs\s3vgroup
   git add .
   git commit -m "Add database setup files"
   git push
   ```

2. **Pull to cPanel:**
   - cPanel → Git Version Control → Pull or Deploy → Update

3. **Create .env file automatically:**
   - Visit: `https://s3vgroup.com/create-env-file.php?create=1`
   - This will create the `.env` file automatically
   - **DELETE `create-env-file.php` after use!**

4. **Test your website:**
   - Visit: `https://s3vgroup.com`
   - Should work now! ✅

---

### Method 2: Manual .env File Creation

1. **In cPanel → File Manager:**
   - Navigate to `public_html/`
   - Click **"File"** → **"New File"**
   - Name: `.env`

2. **Add this content:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=s3vgroup_website
   DB_USERNAME=s3vgroup_main
   DB_PASSWORD=ASDasd12345$$$%%%
   DB_CHARSET=utf8mb4
   DB_COLLATION=utf8mb4_unicode_ci
   
   SITE_URL=https://s3vgroup.com
   ```

3. **Save file** and set permissions to `644`

4. **Test your website:**
   - Visit: `https://s3vgroup.com`
   - Should work now! ✅

---

### Method 3: Use database.local.php (Alternative)

If `.env` doesn't work, create `public_html/config/database.local.php`:

1. **In cPanel → File Manager:**
   - Navigate to `public_html/config/`
   - Click **"File"** → **"New File"**
   - Name: `database.local.php`

2. **Add this content:**
   ```php
   <?php
   return [
       'host' => 'localhost',
       'database' => 's3vgroup_website',
       'username' => 's3vgroup_main',
       'password' => 'ASDasd12345$$$%%%',
       'charset' => 'utf8mb4',
   ];
   ```

3. **Save file** and set permissions to `644`

4. **Test your website**

---

## ✅ What I've Done

1. ✅ **Fixed `config/database.php`** - Now properly defines `getDB()` function
2. ✅ **Created `.env.live`** - Template with your credentials
3. ✅ **Created `create-env-file.php`** - Auto-creates .env file
4. ✅ **Updated database.php** - Better error handling

---

## 🧪 Test After Setup

1. **Test database connection:**
   - Visit: `https://s3vgroup.com/test-db.php`
   - Should show: ✅ Database connection successful!

2. **Test homepage:**
   - Visit: `https://s3vgroup.com`
   - Should load without 500 error! ✅

3. **Test admin:**
   - Visit: `https://s3vgroup.com/admin/login.php`
   - Should show login page ✅

---

## 🔧 If Still Not Working

1. **Check file permissions:**
   - `.env` file: `644`
   - `config/` folder: `755`

2. **Verify database exists:**
   - cPanel → phpMyAdmin
   - Check if `s3vgroup_website` database exists

3. **Check database user:**
   - cPanel → MySQL Databases
   - Verify `s3vgroup_main` user has access to `s3vgroup_website` database
   - User should have **ALL PRIVILEGES**

4. **Check error logs:**
   - cPanel → Errors or Logs
   - Look for specific error messages

---

## 📋 Quick Checklist

- [ ] Push updated files to GitHub
- [ ] Pull to cPanel
- [ ] Create `.env` file (Method 1 or 2)
- [ ] Set `.env` permissions to `644`
- [ ] Test website: `https://s3vgroup.com`
- [ ] Delete `create-env-file.php` after use (security)
- [ ] Delete `test-db.php` after testing (security)
- [ ] Delete `debug-500-error.php` after fixing (security)

---

## 🎉 Expected Result

After setup:
- ✅ Website loads: `https://s3vgroup.com`
- ✅ No 500 errors
- ✅ Database connection works
- ✅ Admin login works

---

**Ready to go!** Follow Method 1 (easiest) or Method 2 (manual). Your website should work! 🚀

