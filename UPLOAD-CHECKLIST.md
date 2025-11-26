# 📤 Files to Upload to Server

## ⚠️ Important: Missing API Files

The following files need to be uploaded to your server for the WordPress SQL Import to work:

---

## 📁 Required Files

### WordPress SQL Import API Files

Upload these files to your server:

```
api/admin/wordpress/
├── test-connection.php      ← MISSING (causing 404 error)
├── import-sql.php
├── save-config.php
├── load-config.php
└── test-connection-debug.php (optional)
```

**Path on server:** `/api/admin/wordpress/`

---

## ✅ How to Upload

### Method 1: Using cPanel File Manager

1. **Login to cPanel** for s3vgroup.com
2. **Go to File Manager**
3. **Navigate to** `public_html/api/admin/wordpress/`
4. **Upload files:**
   - Click "Upload" button
   - Select all files from `api/admin/wordpress/` folder
   - Wait for upload to complete

### Method 2: Using FTP/SFTP

1. **Connect via FTP** to your server
2. **Navigate to** `/public_html/api/admin/wordpress/`
3. **Upload files:**
   - `test-connection.php`
   - `import-sql.php`
   - `save-config.php`
   - `load-config.php`

### Method 3: Using Git (if using Git deployment)

1. **Commit files** to your repository:
   ```bash
   git add api/admin/wordpress/test-connection.php
   git commit -m "Add WordPress test connection API"
   git push
   ```

2. **Pull in cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull" or "Update"

---

## 🔍 Verify Files Are Uploaded

### Check 1: Direct URL Access

Try accessing the file directly:
- `https://s3vgroup.com/api/admin/wordpress/test-connection.php`

**Expected:**
- ✅ If file exists: You'll see a JSON error (not 404)
- ❌ If file missing: 404 Not Found error

### Check 2: File Manager

1. **Login to cPanel**
2. **Go to File Manager**
3. **Navigate to** `public_html/api/admin/wordpress/`
4. **Verify files exist:**
   - `test-connection.php` ✅
   - `import-sql.php` ✅
   - `save-config.php` ✅
   - `load-config.php` ✅

---

## 📋 Complete File Checklist

### Core API Files
- [ ] `api/admin/wordpress/test-connection.php`
- [ ] `api/admin/wordpress/import-sql.php`
- [ ] `api/admin/wordpress/save-config.php`
- [ ] `api/admin/wordpress/load-config.php`

### Admin Pages
- [ ] `admin/wordpress-sql-import.php`
- [ ] `admin/optional-features.php` (should already exist)

### Configuration
- [ ] `.htaccess` (updated with `/api/` exclusion)
- [ ] `includes/functions.php` (with `startAdminSession()`)

---

## 🚨 Common Issues

### Issue 1: 404 Error After Upload

**Problem:** File uploaded but still getting 404

**Solutions:**
- Check file permissions (should be 644)
- Verify file path is correct
- Clear browser cache
- Check if `.htaccess` is blocking it

### Issue 2: Permission Denied

**Problem:** File exists but can't be accessed

**Solutions:**
- Set file permissions to 644
- Set folder permissions to 755
- Check if PHP files are allowed

### Issue 3: Wrong File Path

**Problem:** File in wrong location

**Solutions:**
- Verify path: `public_html/api/admin/wordpress/test-connection.php`
- Check if using subdirectory (e.g., `public_html/s3vgroup/api/...`)

---

## ✅ After Upload

1. **Test the connection:**
   - Go to `https://s3vgroup.com/admin/wordpress-sql-import.php`
   - Enter WordPress database credentials
   - Click "Test Connection"
   - Should work now! ✅

2. **Verify all features:**
   - Test connection ✅
   - Save configuration ✅
   - Load saved configuration ✅
   - Start import ✅

---

## 📝 Quick Upload Command (SSH)

If you have SSH access:

```bash
cd /home/username/public_html
# Upload files via SCP or use git pull
```

---

**Priority:** Upload `test-connection.php` first - this is the file causing the 404 error!

