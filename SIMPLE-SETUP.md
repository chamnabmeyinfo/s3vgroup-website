# 🚀 Simple Setup: Local + Live (Both Working)

## Goal
Make your code work on **BOTH** local and live cPanel automatically!

---

## ✅ What You Need To Do

### Step 1: Local Setup (Your Computer)

**Create `config/database.local.php`** (if not exists):

```php
<?php
// Local Database Configuration
return [
    'host' => 'localhost',
    'database' => 's3vgroup_local',  // Your local database name
    'username' => 'root',              // Your local MySQL user
    'password' => '',                  // Your local MySQL password (usually empty for XAMPP)
    'charset' => 'utf8mb4',
];
```

**This file is already in `.gitignore`** - so it won't be pushed to GitHub!

---

### Step 2: Push Code to GitHub & cPanel

```powershell
cd C:\xampp\htdocs\s3vgroup
git add .
git commit -m "Update project"
git push
```

Then in cPanel → Git Version Control → Pull or Deploy → Update

---

### Step 3: Live cPanel Setup (One Time)

#### A. Create Database in cPanel

1. cPanel → **MySQL Databases**
2. Create database: `s3vgroup_live` (or any name)
3. Create user and add to database
4. Note down credentials

#### B. Create `.env` File in cPanel

1. cPanel → **File Manager** → `public_html/`
2. Create new file: `.env`
3. Add this content:

```env
DB_HOST=localhost
DB_DATABASE=your_cpanel_database_name
DB_USERNAME=your_cpanel_username
DB_PASSWORD=your_cpanel_password
DB_CHARSET=utf8mb4
```

Replace with your actual cPanel database credentials!

4. Save file
5. Set permissions: `644`

**`.env` is already in `.gitignore`** - safe to use!

#### C. Update Site URL in cPanel

1. File Manager → `public_html/config/site.php`
2. Edit the file
3. Find this line:
   ```php
   'url' => 'http://localhost:8080',
   ```
4. Change to your live domain:
   ```php
   'url' => 'https://yourdomain.com',
   ```

#### D. Change Admin Password in cPanel

1. Same file: `config/site.php`
2. Find:
   ```php
   define('ADMIN_PASSWORD', 'admin123');
   ```
3. Change to secure password:
   ```php
   define('ADMIN_PASSWORD', 'YourSecurePassword123!');
   ```

#### E. Import Database in cPanel

1. cPanel → **phpMyAdmin**
2. Select your database
3. **Import** tab → Choose `public_html/sql/schema.sql` → **Go**

---

## 🎉 Done!

Now your code will:
- ✅ Work on **local** (uses `database.local.php`)
- ✅ Work on **live cPanel** (uses `.env` file)
- ✅ Both environments use the same code!

---

## 📝 File Structure

```
s3vgroup/
├── config/
│   ├── database.php          ← Reads .env or database.local.php
│   ├── database.local.php    ← LOCAL ONLY (in .gitignore)
│   └── site.php              ← Update URL in cPanel
├── .env                      ← LIVE cPanel ONLY (in .gitignore)
├── .gitignore                ← Already configured!
└── ...
```

---

## 🔄 Workflow

### Working Locally:
- Uses `config/database.local.php`
- Works with XAMPP/local MySQL
- URL: `http://localhost:8080`

### Working on Live:
- Uses `.env` file in `public_html/`
- Works with cPanel MySQL
- URL: `https://yourdomain.com`

### Push Updates:
```powershell
git add .
git commit -m "Your changes"
git push
# Then in cPanel: Pull or Deploy
```

---

## ⚡ Quick Checklist

- [ ] `config/database.local.php` exists (local only, not in git)
- [ ] `.env` file created in cPanel `public_html/` with live credentials
- [ ] `config/site.php` updated with live URL in cPanel
- [ ] Admin password changed in `config/site.php` in cPanel
- [ ] Database schema imported via phpMyAdmin
- [ ] Code pushed to GitHub
- [ ] Code pulled to cPanel

---

## 🆘 Troubleshooting

**Local not working?**
- Check `config/database.local.php` exists
- Check local MySQL is running
- Check database name matches

**Live not working?**
- Check `.env` file exists in `public_html/`
- Check database credentials in `.env` are correct
- Check `.env` file permissions: `644`
- Check `config/site.php` has live URL (not localhost)

**Both environments:**
- `database.local.php` = LOCAL ONLY (your computer)
- `.env` = LIVE ONLY (cPanel server)
- Both files are in `.gitignore` (safe!)

---

**That's it!** Simple and works for both environments! 🚀

