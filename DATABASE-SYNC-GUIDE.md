# 🔄 Database Synchronization Guide

Sync your database between **localhost** (local development) and **cPanel** (live server).

---

## 📋 Quick Start

### Sync from Live to Local (Recommended)

Get the latest data from your live website to your local development:

```powershell
# 1. Export from cPanel (live)
php bin/sync-database.php export live database-live.sql

# 2. Import to localhost
php bin/sync-database.php import local database-live.sql --force
```

### Sync from Local to Live

Push your local database changes to the live server:

```powershell
# 1. Export from localhost
php bin/sync-database.php export local database-local.sql

# 2. Upload database-local.sql to cPanel via File Manager

# 3. Import on cPanel (via SSH or phpMyAdmin)
# Or use the import-database.php script on cPanel
```

---

## 🚀 Detailed Instructions

### Method 1: Export/Import (Step by Step)

#### Step 1: Export from cPanel (Live Server)

**Option A: Using the sync script on cPanel (if you have SSH access)**

1. SSH into your cPanel server
2. Navigate to your website directory
3. Run:
   ```bash
   php bin/sync-database.php export live database-live.sql
   ```
4. Download the `database-live.sql` file via File Manager

**Option B: Using phpMyAdmin (Easier)**

1. Login to **cPanel** → **phpMyAdmin**
2. Select your database
3. Click **Export** tab
4. Choose **Quick** export method
5. Format: **SQL**
6. Click **Go**
7. Save the file as `database-live.sql`

#### Step 2: Import to Localhost

1. Copy `database-live.sql` to your local project:
   ```
   C:\xampp\htdocs\s3vgroup\database-live.sql
   ```

2. Open PowerShell/Terminal:
   ```powershell
   cd C:\xampp\htdocs\s3vgroup
   php bin/sync-database.php import local database-live.sql --force
   ```

3. ✅ Done! Your local database now matches the live database.

---

### Method 2: One-Command Sync (Advanced)

If you have SSH access to both servers, you can sync directly:

```bash
# Sync from live to local (requires SSH access to both)
php bin/sync-database.php sync live-to-local --force
```

**Note:** This method requires SSH access and proper configuration.

---

## 📝 Commands Reference

### Export Database

Export database to SQL file:

```powershell
php bin/sync-database.php export [target] [output.sql]
```

**Examples:**
```powershell
# Export from localhost
php bin/sync-database.php export local database-local.sql

# Export from live (on cPanel via SSH)
php bin/sync-database.php export live database-live.sql
```

### Import Database

Import SQL file to database:

```powershell
php bin/sync-database.php import [target] [input.sql] [--force]
```

**Examples:**
```powershell
# Import to localhost (with force to overwrite)
php bin/sync-database.php import local database-live.sql --force

# Import to live (on cPanel via SSH)
php bin/sync-database.php import live database-local.sql --force
```

**Flags:**
- `--force` - Overwrite existing tables (required if database has data)

### Sync Database

One-command sync between local and live:

```powershell
php bin/sync-database.php sync [direction] [--force]
```

**Examples:**
```powershell
# Sync from live to local
php bin/sync-database.php sync live-to-local --force

# Sync from local to live
php bin/sync-database.php sync local-to-live --force
```

---

## ⚠️ Important Notes

### Before Syncing

1. **Backup First!** Always backup your database before syncing:
   ```powershell
   # Backup local database
   php bin/sync-database.php export local backup-local-$(date +%Y%m%d).sql
   ```

2. **Check Database Config:**
   - Local: `config/database.local.php` or `.env`
   - Live: `config/database.php` (on cPanel)

3. **File Permissions:**
   - Make sure the script has write permissions for SQL files
   - SQL files should be in `.gitignore` (they contain sensitive data)

### What Gets Synced

✅ **Synced:**
- All tables and their structure
- All data (products, categories, team, testimonials, etc.)
- Site options and settings

❌ **Not Synced:**
- Uploaded files (`uploads/` directory)
- Configuration files (`config/database.local.php`)
- Environment variables (`.env`)

### After Syncing

1. **Verify Data:**
   - Check admin panel to see if data is correct
   - Test a few pages to ensure everything works

2. **Clean Up:**
   - Delete SQL files after syncing (they contain sensitive data)
   - Don't commit SQL files to Git

---

## 🔒 Security

### SQL Files Contain Sensitive Data

- **Never commit SQL files to Git**
- **Delete SQL files after use**
- **Don't share SQL files publicly**

### Best Practices

1. Use `.gitignore` to exclude SQL files:
   ```
   *.sql
   database-*.sql
   ```

2. Store SQL files in a secure location

3. Delete SQL files after syncing

---

## 🆘 Troubleshooting

### Error: "Database connection failed"

**Solution:**
- Check your database configuration
- Make sure MySQL is running
- Verify database credentials

### Error: "Cannot create output file"

**Solution:**
- Check file permissions
- Make sure directory exists
- Try a different output location

### Error: "Table already exists"

**Solution:**
- Use `--force` flag to overwrite
- Or manually drop tables first

### Error: "File not found"

**Solution:**
- Check file path
- Make sure file exists
- Use absolute path if needed

---

## 📊 Sync Workflow Example

### Daily Development Workflow

```powershell
# Morning: Get latest data from live
php bin/sync-database.php export live database-live.sql
php bin/sync-database.php import local database-live.sql --force

# Work on local development...

# Evening: Push changes to live (if needed)
php bin/sync-database.php export local database-local.sql
# Then upload and import on cPanel
```

---

## ✅ Quick Checklist

Before syncing:
- [ ] Backup current database
- [ ] Check database configuration
- [ ] Verify MySQL is running
- [ ] Check file permissions

After syncing:
- [ ] Verify data is correct
- [ ] Test admin panel
- [ ] Test frontend pages
- [ ] Delete SQL files
- [ ] Update `.gitignore` if needed

---

**Status:** ✅ Ready to use

**Last Updated:** 2025

