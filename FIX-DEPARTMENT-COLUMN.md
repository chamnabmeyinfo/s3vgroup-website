# Fix: Department Column Missing in Database

## Problem

The code references `department` column in `team_members` table, but your database doesn't have it yet.

**Error you might see:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'department' in 'SET'
```

## Quick Fix (Choose One Method)

### Method 1: Automatic Sync (Recommended) ⭐

If you have `config/database.live.php` set up:

```bash
php bin/sync-schema-to-live.php --table=team_members --apply
```

This will automatically add all missing columns from your local database to cPanel.

---

### Method 2: Run Fix Script on cPanel

**If you have SSH access to cPanel:**

```bash
cd /path/to/your/website
php bin/fix-team-members-schema.php
```

---

### Method 3: Manual SQL in phpMyAdmin

1. **Login to cPanel → phpMyAdmin**
2. **Select your database**
3. **Click "SQL" tab**
4. **Copy and paste this:**

```sql
ALTER TABLE `team_members` ADD COLUMN `department` VARCHAR(255) NULL AFTER `title`;
ALTER TABLE `team_members` ADD COLUMN `expertise` TEXT NULL AFTER `bio`;
ALTER TABLE `team_members` ADD COLUMN `location` VARCHAR(255) NULL AFTER `phone`;
ALTER TABLE `team_members` ADD COLUMN `languages` VARCHAR(255) NULL AFTER `location`;
ALTER TABLE `team_members` ADD COLUMN `twitter` VARCHAR(500) NULL AFTER `linkedin`;
ALTER TABLE `team_members` ADD COLUMN `facebook` VARCHAR(500) NULL AFTER `twitter`;
ALTER TABLE `team_members` ADD COLUMN `instagram` VARCHAR(500) NULL AFTER `facebook`;
ALTER TABLE `team_members` ADD COLUMN `website` VARCHAR(500) NULL AFTER `instagram`;
ALTER TABLE `team_members` ADD COLUMN `github` VARCHAR(500) NULL AFTER `website`;
ALTER TABLE `team_members` ADD COLUMN `youtube` VARCHAR(500) NULL AFTER `github`;
ALTER TABLE `team_members` ADD COLUMN `telegram` VARCHAR(500) NULL AFTER `youtube`;
ALTER TABLE `team_members` ADD COLUMN `whatsapp` VARCHAR(100) NULL AFTER `telegram`;
```

5. **Click "Go"**

> **Note:** If you see "Duplicate column" errors, ignore them - those columns already exist.

---

## Verify It's Fixed

After running the fix, test by:

1. **Go to Admin Panel → Team Members**
2. **Try to add or edit a team member**
3. **The error should be gone!** ✅

---

## What Columns Are Added?

The fix adds these 12 columns to `team_members` table:

- ✅ `department` - Department name (e.g., "Operations", "Sales")
- ✅ `expertise` - Areas of expertise
- ✅ `location` - Location/address
- ✅ `languages` - Languages spoken
- ✅ `twitter` - Twitter profile URL
- ✅ `facebook` - Facebook profile URL
- ✅ `instagram` - Instagram profile URL
- ✅ `website` - Personal website URL
- ✅ `github` - GitHub profile URL
- ✅ `youtube` - YouTube channel URL
- ✅ `telegram` - Telegram username
- ✅ `whatsapp` - WhatsApp number

All columns are **optional** (NULL allowed), so existing data won't be affected.

---

## Why This Happened?

The code was updated to use new columns (`department`, `expertise`, etc.), but the database schema wasn't updated on cPanel. This is normal when:

- Code is deployed before database migration
- Database was created from old schema
- Manual database changes weren't synced

---

## Prevention: Use Automatic Sync

To prevent this in the future, use the automatic schema sync:

1. **Setup:** Create `config/database.live.php` with cPanel credentials
2. **After code changes:** Run `php bin/sync-schema-to-live.php --apply`
3. **Done!** Schema is automatically synced

See `SCHEMA-SYNC-GUIDE.md` for complete instructions.

---

## Still Having Issues?

If you still get errors:

1. **Check table name:** Make sure it's `team_members` (plural), not `team_member`
2. **Check database:** Make sure you're running SQL on the correct database
3. **Check permissions:** Make sure your database user has ALTER TABLE permissions
4. **Verify columns:** In phpMyAdmin, check the `team_members` table structure

---

## Need Help?

Check these files for more details:
- `QUICK-FIX-TEAM-MEMBERS.md` - Quick fix guide
- `SCHEMA-SYNC-GUIDE.md` - Automatic sync guide
- `sql/fix-team-members-simple.sql` - Simple SQL file

