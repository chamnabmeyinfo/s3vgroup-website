# 🚀 START HERE: Simple Setup Guide

## Your Goal ✅
**Push code & database to cPanel and make it work on BOTH local and live!**

---

## 📝 3 Simple Steps

### Step 1: Local Setup (Your Computer - One Time)

Create **`config/database.local.php`**:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 's3vgroup_local',  // Your local database name
    'username' => 'root',              // Usually 'root' for XAMPP
    'password' => '',                  // Usually empty for XAMPP
    'charset' => 'utf8mb4',
];
```

**This file is NOT in git** - safe for local only!

---

### Step 2: Push Code to GitHub & cPanel

```powershell
cd C:\xampp\htdocs\s3vgroup
git add .
git commit -m "Update project"
git push
```

Then in **cPanel**:
- Go to **Git Version Control**
- Click **"Pull or Deploy"** → **"Update"**

---

### Step 3: Live cPanel Setup (One Time)

#### A. Create Database in cPanel
1. cPanel → **MySQL Databases**
2. Create database, user, and add user to database

#### B. Create `.env` File in cPanel
1. cPanel → **File Manager** → `public_html/`
2. Create file: `.env`
3. Add:
```env
DB_HOST=localhost
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password
DB_CHARSET=utf8mb4
```
Replace with your actual cPanel credentials!

4. Save and set permissions: `644`

#### C. Change Admin Password in cPanel
1. File Manager → `public_html/config/site.php`
2. Edit `ADMIN_PASSWORD` to secure password
3. Save

#### D. Import Database
1. cPanel → **phpMyAdmin**
2. Select your database → **Import** → `public_html/sql/schema.sql` → **Go**

---

## ✅ Done!

**Now it works:**
- ✅ **Local**: Uses `config/database.local.php`
- ✅ **Live**: Uses `.env` file in `public_html/`
- ✅ **Same code**: Works on both!

---

## 🔄 Daily Workflow

**Make changes:**
```powershell
cd C:\xampp\htdocs\s3vgroup
git add .
git commit -m "Your changes"
git push
```

**In cPanel:** Pull or Deploy → Update

**Done!** Website updates automatically!

---

## 📁 What Files Work Where

| File | Local | Live | In Git? |
|------|-------|------|---------|
| `config/database.local.php` | ✅ | ❌ | ❌ No |
| `public_html/.env` | ❌ | ✅ | ❌ No |
| All other files | ✅ | ✅ | ✅ Yes |

---

## 🆘 Quick Troubleshooting

**Local not working?**
- Check `config/database.local.php` exists
- Check MySQL is running

**Live not working?**
- Check `.env` file in `public_html/`
- Check database credentials are correct
- Check `config/site.php` password changed

**Code not updating on live?**
- Push to GitHub
- In cPanel: Pull or Deploy → Update

---

## 📚 More Help

- **`SIMPLE-SETUP.md`** - Detailed step-by-step guide
- **`QUICK-COMMANDS.md`** - Quick reference commands

---

**That's it! Simple and works on both! 🎉**

