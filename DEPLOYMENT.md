# cPanel Deployment Guide

Complete step-by-step guide for deploying the S3V Forklift website to cPanel hosting.

## Prerequisites

- cPanel hosting account
- FTP access or cPanel File Manager
- MySQL database access
- Domain name configured

## Step 1: Prepare Files

1. Download or clone the `s3v-web-php` folder
2. Ensure all files are present:
   - PHP files in root
   - `config/` folder
   - `includes/` folder
   - `admin/` folder
   - `sql/` folder
   - `.htaccess` file

## Step 2: Upload Files to cPanel

### Option A: Using cPanel File Manager

1. Log into cPanel
2. Open **File Manager**
3. Navigate to `public_html` (or your domain's root)
4. Upload all files:
   - Select all files from `s3v-web-php/`
   - Upload to `public_html/`
   - Maintain folder structure

### Option B: Using FTP

1. Connect via FTP client (FileZilla, etc.)
2. Connect to your server
3. Navigate to `public_html/`
4. Upload all files maintaining structure

## Step 3: Create MySQL Database

1. In cPanel, go to **MySQL Databases**
2. Create new database:
   - Name: `s3v_website` (or your choice)
   - Click **Create Database**
3. Create database user:
   - Username: `s3v_user` (or your choice)
   - Password: (generate strong password)
   - Click **Create User**
4. Add user to database:
   - Select user and database
   - Click **Add**
   - Grant **ALL PRIVILEGES**
   - Click **Make Changes**
5. **Note down:**
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

## Step 4: Import Database Schema

1. In cPanel, go to **phpMyAdmin**
2. Select your database from left sidebar
3. Click **Import** tab
4. Click **Choose File**
5. Select `sql/schema.sql` from your uploaded files
6. Click **Go** to import
7. Verify tables were created:
   - `categories`
   - `products`
   - `quote_requests`
   - etc.

## Step 5: Configure Database Connection

1. In cPanel File Manager, navigate to `config/`
2. Edit `database.php`
3. Update these lines:
   ```php
   define('DB_HOST', 'localhost');  // Usually localhost
   define('DB_NAME', 'your_database_name');  // From Step 3
   define('DB_USER', 'your_database_user');  // From Step 3
   define('DB_PASS', 'your_database_password');  // From Step 3
   ```
4. Save file

## Step 6: Configure Site Settings

1. Edit `config/site.php`
2. Update:
   - Company name
   - Contact information
   - URLs
   - **Admin credentials** (IMPORTANT!)

```php
// Change these!
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_PASSWORD', 'your_secure_password_here');
```

## Step 7: Set File Permissions

In cPanel File Manager:

1. Right-click on folders → **Change Permissions**
   - Set to `755`
   - Apply to: `config/`, `includes/`, `admin/`, `sql/`

2. Right-click on PHP files → **Change Permissions**
   - Set to `644`
   - Apply to all `.php` files

3. Right-click on `.htaccess` → **Change Permissions**
   - Set to `644`

## Step 8: Test Website

1. Visit your domain: `https://yourdomain.com`
2. You should see the homepage
3. Test navigation:
   - Products page
   - Quote form
   - Contact page

## Step 9: Test Admin Login

1. Visit: `https://yourdomain.com/admin/login.php`
2. Login with credentials from `config/site.php`
3. You should see the admin dashboard
4. Test features:
   - View products
   - View categories
   - View quotes

## Step 10: Add Initial Content

### Add Categories

1. Login to admin
2. Go to **Categories**
3. Click **+ New category**
4. Add categories:
   - Electric Forklifts
   - Diesel Forklifts
   - Gas Forklifts

### Add Products

1. Go to **Products**
2. Click **+ New product**
3. Fill in product details
4. Set status to **PUBLISHED**
5. Save

## Troubleshooting

### "Database connection failed"

- Check database credentials in `config/database.php`
- Verify database exists in cPanel
- Check user has proper permissions
- Try database host: `localhost` or `127.0.0.1`

### "404 Not Found" errors

- Check `.htaccess` file exists
- Verify file permissions
- Check Apache mod_rewrite is enabled
- Contact hosting support

### "500 Internal Server Error"

- Check PHP error logs in cPanel
- Verify PHP version (needs 7.4+)
- Check file permissions
- Review `.htaccess` syntax

### Admin login not working

- Verify credentials in `config/site.php`
- Check session is enabled in PHP
- Clear browser cookies
- Try different browser

### Images not showing

- Check image URLs are correct
- Verify file paths
- Use absolute URLs for external images
- Check file permissions on image directories

## Security Checklist

- [ ] Changed admin password from default
- [ ] Updated admin email
- [ ] Enabled SSL/HTTPS in cPanel
- [ ] Set proper file permissions
- [ ] Protected `config/` directory (via `.htaccess`)
- [ ] Regular backups enabled
- [ ] PHP version 7.4 or higher

## Post-Deployment

1. **Enable SSL:**
   - In cPanel, go to **SSL/TLS**
   - Install free Let's Encrypt certificate

2. **Set up backups:**
   - Use cPanel backup feature
   - Schedule regular backups

3. **Monitor:**
   - Check error logs regularly
   - Monitor quote submissions
   - Review admin activity

## Support

If you encounter issues:
1. Check cPanel error logs
2. Review PHP error logs
3. Contact your hosting provider
4. Verify all steps were completed

---

**Congratulations!** Your website should now be live on cPanel.
