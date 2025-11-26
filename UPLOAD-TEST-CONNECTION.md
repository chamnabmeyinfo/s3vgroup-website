# 📤 Upload test-connection.php to Server

## ⚠️ Missing File

The file `api/admin/wordpress/test-connection.php` is **missing on the server** but exists locally.

---

## 🚀 Quick Upload Methods

### Method 1: cPanel File Manager (Easiest)

1. **Login to cPanel** for s3vgroup.com
2. **Go to File Manager**
3. **Navigate to:** `public_html/api/admin/wordpress/`
   - If the `wordpress` folder doesn't exist, create it first
4. **Click "Upload"** button
5. **Select file:** `api/admin/wordpress/test-connection.php` from your local computer
6. **Wait for upload** to complete
7. **Verify:** Visit `https://s3vgroup.com/admin/check-api-files.php`

### Method 2: FTP/SFTP

1. **Connect via FTP** to your server
2. **Navigate to:** `/public_html/api/admin/wordpress/`
3. **Upload:** `test-connection.php` from your local `api/admin/wordpress/` folder

### Method 3: Git (Recommended for future)

Since you're using Git deployment:

1. **Commit the file locally:**
   ```bash
   git add api/admin/wordpress/test-connection.php
   git commit -m "Add WordPress test connection API"
   git push
   ```

2. **Fix the .htaccess conflict first** (see GIT-DEPLOYMENT-FIX.md)

3. **Then pull in cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull" or "Update"

---

## 📁 File Location

**Local Path:**
```
C:\xampp\htdocs\s3vgroup\api\admin\wordpress\test-connection.php
```

**Server Path:**
```
/home/s3vgroup/public_html/api/admin/wordpress/test-connection.php
```

**URL:**
```
https://s3vgroup.com/api/admin/wordpress/test-connection.php
```

---

## ✅ Verification

After uploading, verify the file:

1. **Check via File Manager:**
   - File should exist at: `public_html/api/admin/wordpress/test-connection.php`

2. **Check via URL:**
   - Visit: `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
   - Should see JSON response (even if error, not 404)

3. **Check via verification page:**
   - Visit: `https://s3vgroup.com/admin/check-api-files.php`
   - Should show ✅ for test-connection.php

---

## 🔧 File Permissions

After uploading, set permissions:
- **File:** 644
- **Folder:** 755

---

## 🎯 Why This File is Important

The `test-connection.php` file is required for:
- Testing WordPress database connection
- Verifying credentials before import
- Showing connection status and statistics

**Without this file:**
- ❌ "Test Connection" button won't work
- ❌ You'll get 404 errors
- ❌ Can't verify database credentials

**With this file:**
- ✅ Can test WordPress database connection
- ✅ Can verify credentials
- ✅ Can see how many products/categories available
- ✅ Can proceed with import

---

## 📝 Quick Checklist

- [ ] Login to cPanel
- [ ] Go to File Manager
- [ ] Navigate to `public_html/api/admin/wordpress/`
- [ ] Upload `test-connection.php`
- [ ] Verify file exists
- [ ] Test connection in WordPress SQL Import page

---

**Priority:** Upload this file ASAP - it's blocking the WordPress SQL Import feature!

