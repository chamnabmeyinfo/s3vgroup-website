# 🔧 Fix 404 Error: test-connection.php Not Found

## ❌ Current Error

```
POST https://s3vgroup.com/api/admin/wordpress/test-connection.php 404 (Not Found)
```

This means the file **does not exist on the server** at that path.

---

## ✅ Solution: Upload the File

### Method 1: cPanel File Manager (Recommended)

1. **Login to cPanel** for s3vgroup.com
2. **Go to File Manager**
3. **Navigate to:** `public_html/api/admin/wordpress/`
   - If folders don't exist, create them:
     - `public_html/api/` (if missing)
     - `public_html/api/admin/` (if missing)
     - `public_html/api/admin/wordpress/` (if missing)
4. **Click "Upload"** button
5. **Select file:** `C:\xampp\htdocs\s3vgroup\api\admin\wordpress\test-connection.php`
6. **Wait for upload** to complete
7. **Verify file exists:**
   - Check in File Manager: `public_html/api/admin/wordpress/test-connection.php`
   - Should show file size: ~8-9 KB

### Method 2: Verify File Path on Server

**Correct Server Path:**
```
/home/s3vgroup/public_html/api/admin/wordpress/test-connection.php
```

**Or relative to web root:**
```
public_html/api/admin/wordpress/test-connection.php
```

**URL should be:**
```
https://s3vgroup.com/api/admin/wordpress/test-connection.php
```

---

## 🔍 Verification Steps

### Step 1: Check File Exists

1. **In cPanel File Manager:**
   - Navigate to: `public_html/api/admin/wordpress/`
   - Look for: `test-connection.php`
   - Should see file with size ~8-9 KB

### Step 2: Test Direct Access

Visit in browser:
```
https://s3vgroup.com/api/admin/wordpress/test-connection.php
```

**Expected Result:**
- ✅ If file exists: JSON response (even if error, not 404)
- ❌ If file missing: 404 Page Not Found

### Step 3: Check File Permissions

After uploading, set permissions:
- **File:** 644
- **Folders:** 755

---

## 🚨 Common Issues

### Issue 1: Wrong Folder Structure

**Problem:** File uploaded to wrong location

**Solution:**
- Must be in: `public_html/api/admin/wordpress/`
- NOT in: `public_html/admin/` or `public_html/wordpress/`

### Issue 2: File Not Uploaded

**Problem:** Upload failed or incomplete

**Solution:**
- Try uploading again
- Check file size matches local file
- Verify file extension is `.php` (not `.php.txt`)

### Issue 3: .htaccess Blocking

**Problem:** .htaccess rewrite rules blocking API

**Solution:**
- Check `.htaccess` has: `RewriteCond %{REQUEST_URI} !^/api/`
- Should be on line 14 of `.htaccess`

---

## 📋 Quick Checklist

- [ ] Login to cPanel
- [ ] Go to File Manager
- [ ] Navigate to `public_html/api/admin/wordpress/`
- [ ] Create folders if they don't exist
- [ ] Upload `test-connection.php`
- [ ] Verify file exists and has correct size
- [ ] Set file permissions to 644
- [ ] Test direct URL access
- [ ] Try "Test Connection" button again

---

## 🎯 After Upload

Once the file is uploaded:

1. **Test direct access:**
   - Visit: `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
   - Should see JSON (not 404)

2. **Test from admin panel:**
   - Go to: `https://s3vgroup.com/admin/wordpress-sql-import.php`
   - Enter WordPress database credentials
   - Click "Test Connection"
   - Should work! ✅

---

## 💡 Alternative: Git Deployment

If you're using Git:

1. **Commit the file:**
   ```bash
   git add api/admin/wordpress/test-connection.php
   git commit -m "Add WordPress test connection API"
   git push
   ```

2. **Fix .htaccess conflict first** (if any)

3. **Pull in cPanel:**
   - Go to Git Version Control
   - Click "Pull" or "Update"

---

**Priority:** Upload `test-connection.php` to the server - this is blocking the feature!

