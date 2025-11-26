# Database Auto-Sync Configuration Guide

This guide explains how to configure automatic database synchronization from your local development environment to cPanel production.

## 🎯 Goal

**Automatically sync all database changes from local development to cPanel production** with one click.

## 📋 Prerequisites

1. ✅ Local development environment set up (XAMPP)
2. ✅ cPanel hosting account with database access
3. ✅ Admin access to the website admin panel

## 🔧 Step-by-Step Configuration

### Step 1: Get Your cPanel Database Credentials

1. Log in to your **cPanel** account
2. Go to **MySQL Databases** section
3. Find your database name (usually: `username_dbname`)
4. Find your database username (usually: `username_dbuser`)
5. Note your database password (or reset it if needed)
6. Database host is usually: `localhost` (or check cPanel for specific host)

### Step 2: Configure in Admin Panel

1. Log in to your **Admin Panel**: `http://localhost:8080/admin/login.php`
2. Navigate to: **Settings → Database Sync**
3. Fill in the **Configuration** form:
   - **cPanel Database Host**: `localhost` (or your cPanel host)
   - **cPanel Database Name**: Your database name from Step 1
   - **cPanel Database Username**: Your database username from Step 1
   - **cPanel Database Password**: Your database password from Step 1
   - **cPanel Database Port**: `3306` (default MySQL port)
4. Click **Save Configuration**

### Step 3: Test the Connection

1. After saving, try the **Auto Sync** feature:
   - Select sync mode: **Full Sync** (recommended for development)
   - Check **Create backup before sync** (recommended)
   - Click **🔄 Sync Local → cPanel Now**
2. Confirm the warning dialog
3. Wait for sync to complete
4. Check the operation log for success/errors

## 🚀 How to Use Auto Sync

### When to Use Auto Sync

Use Auto Sync whenever you:
- ✅ Add new products, categories, or content locally
- ✅ Update site options or settings
- ✅ Run database migrations
- ✅ Make any database changes you want on production

### Sync Modes

#### Full Sync (Recommended for Development)
- **What it does**: Syncs both database structure AND all data
- **Use when**: You want production to match local exactly
- **Warning**: Overwrites all production data

#### Structure Only (Safer for Production)
- **What it does**: Syncs only table structure (CREATE TABLE statements)
- **Use when**: You only changed table structure, not data
- **Warning**: Production data remains unchanged

### Workflow

1. **Make changes locally** (add products, update settings, etc.)
2. **Go to Admin → Database Sync**
3. **Click "🔄 Sync Local → cPanel Now"**
4. **Confirm the warning**
5. **Wait for completion** (check the log)
6. **Verify on production** (visit your live site)

## ⚙️ Advanced Configuration

### Automatic Sync on Admin Actions (Optional)

You can enable automatic sync after specific admin actions by adding hooks. This requires code modification:

```php
// Example: Auto-sync after product creation
// In api/admin/products/item.php after successful creation:
if (defined('AUTO_SYNC_ENABLED') && AUTO_SYNC_ENABLED) {
    // Trigger sync in background
    // (Implementation depends on your needs)
}
```

### Scheduled Sync (Optional)

For automatic daily/hourly sync, you can set up a cron job:

```bash
# Run sync every day at 2 AM
0 2 * * * php /path/to/api/admin/database/sync.php
```

**Note**: This requires additional setup and should be used carefully.

## 🔒 Security Best Practices

1. **Never commit database credentials** to Git
   - Credentials are stored in `site_options` table (not in code)
   - `config/database.php` is in `.gitignore`

2. **Always create backups** before syncing
   - Enable "Create backup before sync" checkbox
   - Backups are saved in `tmp/backup-before-sync-*.sql`

3. **Test locally first**
   - Make sure your local changes work correctly
   - Verify data integrity before syncing

4. **Use Structure Only mode** when possible
   - Safer for production environments
   - Preserves production data

## 🐛 Troubleshooting

### Error: "cPanel database configuration is incomplete"
- **Solution**: Go to Database Sync settings and fill in all fields

### Error: "Connection refused" or "Access denied"
- **Solution**: 
  - Check your cPanel database credentials
  - Verify database host (might not be `localhost`)
  - Check if your IP is allowed in cPanel remote MySQL settings

### Error: "No tables found in local database"
- **Solution**: Make sure your local database is set up correctly

### Sync completes but data doesn't appear on production
- **Solution**: 
  - Check the operation log for errors
  - Verify cPanel database credentials are correct
  - Check cPanel error logs

### Backup creation fails
- **Solution**: 
  - Check if `tmp/` directory exists and is writable
  - Check disk space
  - Sync will continue even if backup fails

## 📝 Notes

- **Backups**: Backups are stored in `tmp/` directory (not committed to Git)
- **Large Databases**: Sync may take time for large databases (>100MB)
- **Network**: Requires stable internet connection to cPanel
- **Production Data**: Always backup before syncing to avoid data loss

## 🎉 Success!

Once configured, you can sync your local database to cPanel with one click!

**Remember**: 
- ✅ Always test locally first
- ✅ Create backups before syncing
- ✅ Verify changes on production after sync

---

**Need Help?** Check the operation log in Database Sync page for detailed error messages.

