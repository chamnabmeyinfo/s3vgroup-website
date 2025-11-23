# 🗄️ Automatic Database Import Guide

## 🎯 Overview

Instead of manually importing SQL files through phpMyAdmin, you can now use the automatic import script!

## 🚀 Quick Start

### Step 1: Upload the Script
The script `import-database.php` is already in your project. Just make sure it's uploaded to cPanel.

### Step 2: Access the Script
Visit: **https://s3vgroup.com/import-database.php**

### Step 3: Click "Start Import"
The script will automatically:
- ✅ Connect to your database
- ✅ Import `sql/schema.sql` (all tables)
- ✅ Import `sql/site_options.sql` (default settings)
- ✅ Verify all tables were created
- ✅ Show import statistics

### Step 4: Delete the Script
**⚠️ IMPORTANT:** After import is complete, delete `import-database.php` for security!

---

## 📋 What Gets Imported

### 1. `sql/schema.sql`
- All database tables (categories, products, quote_requests, etc.)
- Indexes and foreign keys
- Sample data (categories)

### 2. `sql/site_options.sql`
- Site configuration options
- Default colors, settings, etc.

---

## ✅ Features

### Safe Import
- Uses `CREATE TABLE IF NOT EXISTS` - won't overwrite existing tables
- Uses `INSERT ... ON DUPLICATE KEY UPDATE` - won't duplicate data
- Preserves existing data

### Automatic Verification
- Checks if all required tables exist
- Shows row counts for each table
- Displays import statistics

### Error Handling
- Shows clear error messages
- Continues even if some statements fail
- Logs all errors for review

---

## 🔍 Pre-Import Check

Before importing, the script shows:
- ✅ Current database status
- ✅ Existing tables (if any)
- ✅ SQL files to be imported
- ✅ File sizes

---

## 📊 Import Statistics

After import, you'll see:
- **SQL Queries Executed:** Total number of SQL statements run
- **Tables Created:** Number of tables created
- **Data Rows Inserted:** Number of data rows inserted

---

## 🔒 Security

### Why Delete the Script?
The script has access to your database credentials and can execute SQL statements. After import, it should be deleted to prevent unauthorized access.

### How to Delete
1. Via cPanel File Manager
2. Via FTP
3. Via SSH: `rm import-database.php`

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check your `.env` file has correct credentials
- Verify database exists in cPanel
- Check database user has proper permissions

### "Table already exists" errors
- These are safe to ignore - the script uses `IF NOT EXISTS`
- Your existing data will be preserved

### "File not found" errors
- Make sure `sql/schema.sql` and `sql/site_options.sql` are uploaded
- Check file paths are correct

### Import seems stuck
- Check PHP execution time limits in cPanel
- Large SQL files may take a few minutes
- Check browser console for errors

---

## 📝 Manual Alternative

If the automatic import doesn't work, you can still import manually:

1. Go to cPanel → phpMyAdmin
2. Select your database
3. Click "Import" tab
4. Choose `sql/schema.sql`
5. Click "Go"
6. Repeat for `sql/site_options.sql`

---

## ✅ Verification

After import, verify:

1. **Check Tables:**
   - Go to phpMyAdmin
   - You should see: `categories`, `products`, `quote_requests`, `site_options`, etc.

2. **Check Website:**
   - Visit: https://s3vgroup.com/
   - Should load without errors
   - Should show categories and products

3. **Check Admin:**
   - Visit: https://s3vgroup.com/admin/login.php
   - Should be able to login

---

## 🎉 Success!

Once import is complete:
- ✅ All tables created
- ✅ Default data inserted
- ✅ Website ready to use
- ✅ Admin panel functional

**Remember:** Delete `import-database.php` after use!

---

**Status:** ✅ **Ready to Use**

**Next Steps:**
1. Visit: https://s3vgroup.com/import-database.php
2. Click "Start Import"
3. Wait for completion
4. Delete the script
5. Enjoy your working website! 🚀

