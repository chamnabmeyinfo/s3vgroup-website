# ⚡ Quick Commands - Push Code & Database to cPanel

## 🎯 Your Goal
Push code to cPanel and make it work on both local and live!

---

## 📋 Setup (One Time Only)

### Local (Your Computer):

**1. Create `config/database.local.php`:**
```php
<?php
return [
    'host' => 'localhost',
    'database' => 's3vgroup_local',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
```

**2. Create `config/site.local.php` (optional):**
```php
<?php
$siteConfig['url'] = 'http://localhost:8080';
define('ADMIN_PASSWORD', 'admin123');
```

---

### cPanel (Live Server):

**1. Create `.env` file in `public_html/`:**
```env
DB_HOST=localhost
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password
DB_CHARSET=utf8mb4
```

**2. Update `config/site.php` in cPanel:**
Change admin password in cPanel (one time):
- File Manager → `public_html/config/site.php`
- Edit `ADMIN_PASSWORD` to secure password

**3. Import database:**
- cPanel → phpMyAdmin → Select database → Import → `public_html/sql/schema.sql`

---

## 🚀 Daily Workflow

### Push Code to GitHub & cPanel:

```powershell
# 1. Make changes locally
# 2. Commit and push
cd C:\xampp\htdocs\s3vgroup
git add .
git commit -m "Your update message"
git push

# 3. In cPanel: Pull or Deploy → Update
# Done! Website updates automatically!
```

---

## ✅ That's It!

**Local:** Uses `database.local.php` and `site.local.php` (in .gitignore)

**Live:** Uses `.env` file in `public_html/` (in .gitignore)

**Both:** Same code, different config files!

---

## 📁 File Structure

```
Local Computer:
├── config/
│   ├── database.local.php  ← Local only (not in git)
│   └── site.local.php      ← Local only (optional)
└── (other files)

Live cPanel:
├── .env                    ← Live only (not in git)
├── config/
│   └── site.php            ← Update password here
└── (other files)
```

---

## 🔧 Quick Fixes

**Local not working?**
- Check `config/database.local.php` exists
- Check local MySQL running

**Live not working?**
- Check `.env` file in `public_html/`
- Check database credentials in `.env`
- Check `config/site.php` password changed

---

**Simple and works!** 🎉

