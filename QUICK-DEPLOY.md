# Quick Deployment to cPanel

## 🚀 Fastest Method: cPanel File Manager

### Step 1: Create ZIP Package

**Windows (PowerShell):**
```powershell
cd s3v-web-php
.\create-deployment-package.ps1
```

**Or manually:**
1. Select all files in `s3v-web-php/` folder
2. Right-click → Send to → Compressed (zipped) folder
3. Name it `s3v-website.zip`

### Step 2: Upload to cPanel

1. **Log into cPanel**
2. **Open File Manager**
3. **Navigate to `public_html/`** (or your domain root)
4. **Click Upload** button (top menu)
5. **Select `s3v-website.zip`**
6. **Wait for upload** (may take a few minutes)
7. **Right-click the ZIP file** → **Extract**
8. **Delete the ZIP file** after extraction

### Step 3: Configure Database

1. **Create MySQL Database:**
   - cPanel → MySQL Databases
   - Create database: `s3v_website`
   - Create user and add to database
   - Note down: database name, username, password

2. **Edit Database Config:**
   - File Manager → `config/database.php`
   - Click **Edit**
   - Update:
     ```php
     define('DB_NAME', 'your_database_name');
     define('DB_USER', 'your_database_user');
     define('DB_PASS', 'your_database_password');
     ```
   - Click **Save Changes**

3. **Import Database Schema:**
   - cPanel → phpMyAdmin
   - Select your database
   - Click **Import** tab
   - Choose file: `sql/schema.sql`
   - Click **Go**

### Step 4: Configure Site Settings

1. **Edit Site Config:**
   - File Manager → `config/site.php`
   - Click **Edit**
   - Update contact information
   - **IMPORTANT:** Change admin password:
     ```php
     define('ADMIN_PASSWORD', 'your_secure_password');
     ```
   - Click **Save Changes**

### Step 5: Test Website

1. Visit: `https://yourdomain.com`
2. Should see homepage
3. Test admin: `https://yourdomain.com/admin/login.php`

---

## 🔄 Alternative: Automated FTP Upload

If you prefer automated upload:

1. **Edit `auto-upload-ftp.php`:**
   - Fill in your FTP credentials
   - Save file

2. **Run upload script:**
   ```bash
   php auto-upload-ftp.php
   ```

3. **Follow Steps 3-5 above** (configure database, etc.)

**⚠️ Security:** Delete `auto-upload-ftp.php` after deployment!

---

## ✅ Verification Checklist

- [ ] Files uploaded to `public_html/`
- [ ] Database created in cPanel
- [ ] `config/database.php` updated
- [ ] `sql/schema.sql` imported
- [ ] `config/site.php` updated (admin password changed!)
- [ ] Website loads at your domain
- [ ] Admin login works
- [ ] Can add products/categories

---

## 🆘 Need Help?

If you get stuck:
1. Check cPanel error logs
2. Verify file permissions (folders: 755, files: 644)
3. Check PHP version (needs 7.4+)
4. Verify database credentials

**Ready to deploy?** Follow Step 1 above!
