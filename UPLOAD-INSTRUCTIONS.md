# Quick Upload Instructions for cPanel

## Method 1: cPanel File Manager (Easiest)

### Step 1: Create ZIP File
1. Select all files in `s3v-web-php/` folder
2. Right-click → Compress/Archive
3. Create `s3v-website.zip`

### Step 2: Upload via cPanel
1. Log into cPanel
2. Open **File Manager**
3. Navigate to `public_html/`
4. Click **Upload** button
5. Select `s3v-website.zip`
6. Wait for upload to complete
7. Right-click the ZIP file → **Extract**
8. Delete the ZIP file after extraction

### Step 3: Configure
1. Edit `config/database.php` with your database info
2. Edit `config/site.php` with your site info
3. Import `sql/schema.sql` via phpMyAdmin

## Method 2: FTP/SFTP Upload

If you have FTP credentials, I can help you create an automated upload script.

## Method 3: Git Deployment

If your cPanel supports Git:
1. Create a Git repository
2. Push code to repository
3. Clone in cPanel via Git Version Control

---

**Need help?** Share your preferred method and I'll provide detailed steps!
