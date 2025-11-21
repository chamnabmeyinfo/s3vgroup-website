# Local Development Setup Guide

Test your S3vgroup website locally before deploying to GitHub and cPanel.

## Prerequisites

You need PHP and MySQL installed locally. Choose one option:

### Option 1: XAMPP (Easiest - Recommended for Windows)

1. **Download XAMPP:** https://www.apachefriends.org/
2. **Install XAMPP** (includes PHP, MySQL, Apache)
3. **Start XAMPP Control Panel**
4. **Start Apache** and **MySQL** services

### Option 2: PHP Built-in Server (If PHP is already installed)

If you have PHP installed, you can use the built-in server.

### Option 3: Docker (Advanced)

Use Docker Compose for a complete environment.

---

## Quick Start with XAMPP

### Step 1: Install XAMPP

1. Download from: https://www.apachefriends.org/
2. Install to: `C:\xampp\` (default)
3. Start XAMPP Control Panel
4. Click **Start** for **Apache** and **MySQL**

### Step 2: Copy Project Files

1. Copy the entire `s3v-web-php` folder to:
   ```
   C:\xampp\htdocs\s3vgroup\
   ```

### Step 3: Create Local Database

1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **New** to create a database
3. Database name: `s3vgroup_local`
4. Click **Create**
5. Click **Import** tab
6. Choose file: `C:\xampp\htdocs\s3vgroup\sql\schema.sql`
7. Click **Go**

### Step 4: Configure Local Settings

1. Copy `config/database.php.example` to `config/database.php`
2. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 's3vgroup_local');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // XAMPP default is empty
   ```

3. Copy `config/site.php.example` to `config/site.php`
4. Edit `config/site.php` with your local settings:
   ```php
   'url' => 'http://localhost/s3vgroup',
   ```

### Step 5: Test Locally

1. Open browser: **http://localhost/s3vgroup/**
2. You should see the homepage!
3. Test admin: **http://localhost/s3vgroup/admin/login.php**
   - Email: `admin@s3vtgroup.com`
   - Password: `admin123` (or what you set in site.php)

---

## Using PHP Built-in Server (Alternative)

If you have PHP installed separately:

### Step 1: Navigate to Project

```powershell
cd "C:\Coding Development\s3v-web-php"
```

### Step 2: Start PHP Server

```powershell
php -S localhost:8000
```

### Step 3: Open Browser

Visit: **http://localhost:8000**

**Note:** You'll still need MySQL running (use XAMPP MySQL or install MySQL separately).

---

## Local Development Checklist

- [ ] XAMPP installed and running
- [ ] Apache started
- [ ] MySQL started
- [ ] Project files in `C:\xampp\htdocs\s3vgroup\`
- [ ] Database created in phpMyAdmin
- [ ] `sql/schema.sql` imported
- [ ] `config/database.php` configured
- [ ] `config/site.php` configured
- [ ] Website loads at `http://localhost/s3vgroup/`
- [ ] Admin login works

---

## Troubleshooting

### "Database connection failed"

- Check MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Make sure database exists in phpMyAdmin

### "404 Not Found"

- Check file path: should be `C:\xampp\htdocs\s3vgroup\`
- Verify `.htaccess` file exists
- Check Apache is running

### "Access Denied" for MySQL

- XAMPP default: username `root`, password empty
- If you set a password, update `config/database.php`

### Images not showing

- Check image URLs are correct
- Use absolute paths for local testing

---

## Next Steps After Local Testing

Once everything works locally:

1. ✅ Test all pages
2. ✅ Test admin features
3. ✅ Add sample products/categories
4. ✅ Push to GitHub
5. ✅ Deploy to cPanel

---

**Ready to test?** Follow the XAMPP setup above!
