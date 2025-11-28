# Automatic Database Schema Sync Guide

## Overview

This guide explains how to automatically sync your database schema from local (XAMPP) to live (cPanel). When you add new columns to your local database, you can automatically add them to your live database without manually running SQL.

## Quick Start

### 1. Setup Live Database Configuration

Create `config/database.live.php` from the example:

```bash
cp config/database.live.php.example config/database.live.php
```

Edit `config/database.live.php` with your cPanel database credentials:

```php
<?php
$liveDbConfig = [
    'host' => 'localhost',  // Usually 'localhost' for cPanel
    'name' => 'your_database_name',
    'user' => 'your_database_user',
    'pass' => 'your_database_password',
];
```

**Important:** Add this file to `.gitignore` so it's not committed to GitHub!

### 2. Test the Connection (Dry Run)

Run the sync script in dry-run mode to see what would be changed:

```bash
php bin/sync-schema-to-live.php
```

This will:
- ✅ Connect to both local and live databases
- ✅ Compare table schemas
- ✅ Show what columns are missing
- ✅ **NOT make any changes** (safe to run)

### 3. Apply Changes

When you're ready to sync, run with the `--apply` flag:

```bash
php bin/sync-schema-to-live.php --apply
```

This will:
- ✅ Connect to both databases
- ✅ Compare schemas
- ✅ **Automatically add missing columns** to live database
- ✅ Show progress and results

### 4. Sync Specific Table Only

To sync just one table (faster):

```bash
php bin/sync-schema-to-live.php --table=team_members --apply
```

## Example Workflow

### Scenario: Adding New Column to team_members

1. **Add column locally** (in your code or via migration):
   ```sql
   ALTER TABLE team_members ADD COLUMN department VARCHAR(255) NULL AFTER title;
   ```

2. **Test locally** - Make sure everything works

3. **Sync to live**:
   ```bash
   php bin/sync-schema-to-live.php --table=team_members --apply
   ```

4. **Done!** ✅ The column is now in your live database

## What Gets Synced?

The script compares:
- ✅ **Column names** - Missing columns are added
- ✅ **Column types** - VARCHAR, TEXT, INT, etc.
- ✅ **NULL/NOT NULL** - Preserves nullability
- ✅ **Default values** - Preserves defaults
- ✅ **Column order** - Adds columns in the same position

**What doesn't get synced:**
- ❌ Data (use `bin/sync-database.php` for data)
- ❌ Indexes (only columns)
- ❌ Foreign keys (only columns)
- ❌ Table structure changes (only missing columns)

## Safety Features

1. **Dry Run by Default** - Won't make changes unless you use `--apply`
2. **Column Existence Check** - Won't duplicate columns
3. **Error Handling** - Continues even if one column fails
4. **Connection Verification** - Checks both databases before starting

## Common Use Cases

### Fix Missing Columns (Like team_members)

```bash
# Check what's missing
php bin/sync-schema-to-live.php --table=team_members

# Apply the fix
php bin/sync-schema-to-live.php --table=team_members --apply
```

### Sync All Tables After Major Update

```bash
# See what needs updating
php bin/sync-schema-to-live.php

# Apply all changes
php bin/sync-schema-to-live.php --apply
```

### Regular Maintenance

Add to your workflow:
1. Make schema changes locally
2. Test locally
3. Run sync script
4. Deploy code to cPanel

## Troubleshooting

### Error: "Live database config not found"

**Solution:** Create `config/database.live.php` from the example file.

### Error: "Failed to connect to live database"

**Solutions:**
1. Check your database credentials in `config/database.live.php`
2. Make sure your cPanel allows remote MySQL connections (if not using localhost)
3. Check if your IP is whitelisted in cPanel → Remote MySQL

### Error: "Duplicate column"

**Solution:** This is fine! It means the column already exists. The script handles this gracefully.

### Columns Not in Right Order

**Solution:** The script tries to preserve column order, but if it's not perfect, you can manually reorder in phpMyAdmin. Functionality is not affected.

## Security Notes

⚠️ **Important:**
- Never commit `config/database.live.php` to GitHub
- Use strong database passwords
- Only run `--apply` when you're sure about the changes
- Always test in dry-run mode first

## Integration with Git Workflow

### Recommended Workflow

1. **Local Development:**
   ```bash
   # Make schema changes locally
   php bin/fix-team-members-schema.php
   
   # Test locally
   # ... test your changes ...
   ```

2. **Commit Code:**
   ```bash
   git add .
   git commit -m "Add department column to team_members"
   git push
   ```

3. **Deploy to cPanel:**
   - Use cPanel Git Version Control to pull latest code

4. **Sync Schema:**
   ```bash
   # SSH into cPanel or use cPanel Terminal
   php bin/sync-schema-to-live.php --apply
   ```

## Advanced Usage

### Sync Multiple Specific Tables

```bash
php bin/sync-schema-to-live.php --table=team_members --apply
php bin/sync-schema-to-live.php --table=products --apply
php bin/sync-schema-to-live.php --table=categories --apply
```

### Automated Sync (Cron Job)

You can set up a cron job to automatically sync schema, but **be careful** - only do this if you're confident about your workflow:

```bash
# Run daily at 2 AM (example)
0 2 * * * cd /path/to/website && php bin/sync-schema-to-live.php --apply >> /tmp/schema-sync.log 2>&1
```

## Related Tools

- **`bin/sync-database.php`** - Sync data (not schema) between local and live
- **`bin/auto-sync-database.php`** - Auto-sync data if live is newer
- **`bin/fix-team-members-schema.php`** - Fix specific table schema
- **`bin/verify-database-schema.php`** - Verify schema is correct

## Need Help?

If you encounter issues:
1. Check the error message
2. Verify database credentials
3. Test connection manually in phpMyAdmin
4. Run in dry-run mode first to see what would happen

---

**Remember:** Always test in dry-run mode first! 🛡️

