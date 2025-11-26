# 🔧 Fix: test-connection.php Returns 404

## ❌ Error Found

**Test Results:**
- **URL:** `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
- **Method:** POST
- **Status:** 404 Not Found
- **Response:** HTML 404 page (not JSON)

**Console Error:**
```
Non-JSON response: <!DOCTYPE html>
<html lang="en, kh">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found - S3vgroup</title>
```

---

## 🔍 Root Cause

The file `test-connection.php` is **not accessible on the server**, even though it exists locally.

---

## ✅ Solution

### Step 1: Verify File Location on Server

**Correct path on server:**
```
public_html/api/admin/wordpress/test-connection.php
```

**Check in cPanel File Manager:**
1. Login to s3vgroup.com cPanel
2. Go to **File Manager**
3. Navigate to: `public_html/api/admin/wordpress/`
4. **Verify** `test-connection.php` exists

### Step 2: If File is Missing

**Upload the file:**
1. In cPanel File Manager, go to: `public_html/api/admin/wordpress/`
2. Click **Upload**
3. Select file: `test-connection.php` from your local computer
   - Local path: `C:\xampp\htdocs\s3vgroup\api\admin\wordpress\test-connection.php`
4. Upload the file
5. **Verify** file permissions: `644` or `755`

### Step 3: If File Exists But Still 404

**Check file permissions:**
1. Right-click `test-connection.php` in File Manager
2. Select **Change Permissions**
3. Set to: `644` (or `755` if needed)
4. Click **Change Permissions**

**Check file path:**
- Must be exactly: `public_html/api/admin/wordpress/test-connection.php`
- Not: `public_html/api/admin/wordpress/test-connection.php.txt`
- Not: `public_html/api/admin/wordpress/test-connection (1).php`

### Step 4: Verify .htaccess Configuration

The `.htaccess` file should exclude `/api/` from rewrite rules (already configured):
```apache
RewriteCond %{REQUEST_URI} !^/api/
```

**If this line is missing, add it to `.htaccess`.**

---

## 🧪 Test After Fix

1. **Direct URL test:**
   - Visit: `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
   - Should return JSON (not 404 HTML)

2. **From form:**
   - Go to: `https://s3vgroup.com/admin/wordpress-sql-import.php`
   - Fill in connection details
   - Click "Test Connection"
   - Should show connection result (not 404 error)

---

## 📋 Checklist

- [ ] File exists at: `public_html/api/admin/wordpress/test-connection.php`
- [ ] File permissions: `644` or `755`
- [ ] File name is exactly: `test-connection.php` (no extra extensions)
- [ ] `.htaccess` excludes `/api/` from rewrite rules
- [ ] Direct URL test returns JSON (not 404)
- [ ] Form test connection works

---

## 🔄 Alternative: Use Git Deployment

If manual upload doesn't work, use Git:

1. **Commit the file locally:**
   ```bash
   git add api/admin/wordpress/test-connection.php
   git commit -m "Add test-connection.php"
   git push origin main
   ```

2. **Deploy via cPanel Git:**
   - Login to cPanel
   - Go to **Git Version Control**
   - Click **Pull or Deploy**
   - Select your repository
   - Click **Deploy**

---

**After fixing, test again from the WordPress SQL Import page!** 🚀

