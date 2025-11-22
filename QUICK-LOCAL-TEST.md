# 🚀 Quick Local Test Guide

Test your S3vgroup website locally in 5 minutes!

## Option 1: XAMPP (Easiest)

### Step 1: Install XAMPP
- Download: https://www.apachefriends.org/
- Install (default location: `C:\xampp\`)
- Start XAMPP Control Panel
- Click **Start** for **Apache** and **MySQL**

### Step 2: Setup Project
1. Copy `s3v-web-php` folder to: `C:\xampp\htdocs\s3vgroup\`
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create database: `s3vgroup_local`
4. Import `sql/schema.sql` file
5. Copy `config/database.php.example` → `config/database.php`
6. Edit `config/database.php`:
   ```php
   define('DB_NAME', 's3vgroup_local');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for XAMPP
   ```
7. Copy `config/site.php.example` → `config/site.php`

### Step 3: Test!
- Open: **http://localhost/s3vgroup/**
- Admin: **http://localhost/s3vgroup/admin/login.php**
  - Email: `admin@s3vtgroup.com`
  - Password: `admin123`

## Option 2: PHP Built-in Server

If you have PHP installed:

```powershell
cd "C:\Coding Development\s3v-web-php"
.\start-local-server.ps1
```

Then open: **http://localhost:8000**

---

**That's it!** Your website is running locally. Test everything before deploying!
