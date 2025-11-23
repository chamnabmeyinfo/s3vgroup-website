# Quick Fix: Team Members Column Error

## Error Message
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'department' in 'SET'
```

## Problem
The `team_members` table in your cPanel database is missing the `department` column (and other columns) that the code expects.

## Solution: 3 Easy Ways to Fix

### Option 1: Use phpMyAdmin (Easiest) ⭐

1. **Login to cPanel**
2. **Open phpMyAdmin**
3. **Select your database**
4. **Click on the "SQL" tab**
5. **Copy and paste this SQL:**

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

6. **Click "Go"**
7. **Done!** ✅

> **Note:** If you see "Duplicate column" errors, that's fine - it means the column already exists. Just ignore those errors.

---

### Option 2: Import SQL File

1. **Download the file:** `sql/fix-team-members-simple.sql` from your GitHub repository
2. **Login to cPanel → phpMyAdmin**
3. **Select your database**
4. **Click "Import" tab**
5. **Choose the SQL file**
6. **Click "Go"**

---

### Option 3: Use Fix Script (If you have SSH access)

```bash
cd /path/to/your/website
php bin/fix-team-members-schema.php
```

---

## Verify It Worked

After running the fix, test by:
1. Going to Admin Panel → Team Members
2. Try to add or edit a team member
3. The error should be gone! ✅

---

## What Columns Are Added?

- `department` - Department name (e.g., "Operations", "Sales")
- `expertise` - Areas of expertise
- `location` - Location/address
- `languages` - Languages spoken
- `twitter` - Twitter profile URL
- `facebook` - Facebook profile URL
- `instagram` - Instagram profile URL
- `website` - Personal website URL
- `github` - GitHub profile URL
- `youtube` - YouTube channel URL
- `telegram` - Telegram username
- `whatsapp` - WhatsApp number

All columns are **optional** (NULL allowed), so existing data won't be affected.

---

## Still Having Issues?

If you still get errors after running the fix:

1. **Check table name:** Make sure the table is called `team_members` (plural), not `team_member` (singular)
2. **Check database:** Make sure you're running the SQL on the correct database
3. **Check permissions:** Make sure your database user has ALTER TABLE permissions

---

## Need Help?

If you're still stuck, check:
- The table structure in phpMyAdmin (click on `team_members` table → Structure tab)
- Make sure all columns listed above exist
- If any are missing, run the SQL again for just those columns

