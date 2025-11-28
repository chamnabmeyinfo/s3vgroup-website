# Database Manager - Complete Guide

## Overview

The Database Manager is a powerful CLI tool that lets you manage your database directly from the command line. You can work with both local and live databases.

## Quick Start

### Basic Usage

```bash
php bin/db-manager.php <command> [options] [local|live]
```

**Default:** Commands run on **local** database unless you specify `live` at the end.

---

## Available Commands

### 1. List All Tables

**List tables in local database:**
```bash
php bin/db-manager.php list-tables
```

**List tables in live database:**
```bash
php bin/db-manager.php list-tables live
```

**Output:**
```
 1. team_members              (5 rows)
 2. products                  (12 rows)
 3. categories                (6 rows)
 ...
```

---

### 2. Show Table Structure

**View table structure (local):**
```bash
php bin/db-manager.php describe team_members
```

**View table structure (live):**
```bash
php bin/db-manager.php describe team_members live
```

**Output:**
```
Field               Type                 Null       Key        Default    Extra
------------------- -------------------- ---------- ---------- ---------- ----------
id                  VARCHAR(255)         NO         PRI        NULL       
name                VARCHAR(255)         NO                  NULL       
title               VARCHAR(255)         NO                  NULL       
department          VARCHAR(255)         YES                 NULL       
...
```

---

### 3. Add Column to Table

**Add column to local database:**
```bash
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title
```

**Add column to live database:**
```bash
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title live
```

**Syntax:**
```
add-column <table> <column_name> <type_definition> [after_column] [local|live]
```

**Examples:**
```bash
# Add department column after title
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title live

# Add expertise column after bio
php bin/db-manager.php add-column team_members expertise "TEXT NULL" bio live

# Add column at the end (no after parameter)
php bin/db-manager.php add-column products tags "JSON NULL" live
```

---

### 4. Sync Schema to Live

**Automatically sync missing columns from local to live:**
```bash
php bin/db-manager.php sync-schema
```

This runs the automatic schema sync tool.

---

### 5. Run Custom SQL Query

**Run query on local database:**
```bash
php bin/db-manager.php query "SELECT * FROM team_members LIMIT 5"
```

**Run query on live database:**
```bash
php bin/db-manager.php query "SELECT * FROM team_members LIMIT 5" live
```

**Examples:**
```bash
# Select data
php bin/db-manager.php query "SELECT id, name, title FROM team_members" live

# Update data
php bin/db-manager.php query "UPDATE team_members SET status='ACTIVE' WHERE id='team_001'" live

# Delete data (be careful!)
php bin/db-manager.php query "DELETE FROM team_members WHERE id='team_999'" live

# Insert data
php bin/db-manager.php query "INSERT INTO team_members (id, name, title) VALUES ('team_999', 'John Doe', 'Manager')" live
```

**⚠️ Warning:** Be very careful with UPDATE and DELETE queries! Always test on local first.

---

### 6. Show Help

```bash
php bin/db-manager.php help
```

---

## Common Use Cases

### Fix Missing Column on Live Database

```bash
# 1. Check if column exists
php bin/db-manager.php describe team_members live

# 2. Add missing column
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title live

# 3. Verify it was added
php bin/db-manager.php describe team_members live
```

### Compare Local vs Live Schema

```bash
# Check local table
php bin/db-manager.php describe team_members local

# Check live table
php bin/db-manager.php describe team_members live

# Compare the output
```

### Sync All Missing Columns

```bash
# Automatically sync everything
php bin/db-manager.php sync-schema
```

### Quick Data Check

```bash
# Check how many records
php bin/db-manager.php query "SELECT COUNT(*) as total FROM team_members" live

# View recent records
php bin/db-manager.php query "SELECT * FROM team_members ORDER BY createdAt DESC LIMIT 10" live
```

---

## Safety Features

1. **Default to Local:** Commands default to local database (safer)
2. **Explicit Live:** Must specify `live` to modify live database
3. **Query Validation:** Basic validation for dangerous operations
4. **Error Handling:** Clear error messages if something goes wrong

---

## Tips

1. **Always test on local first** before running on live
2. **Backup your database** before making changes
3. **Use `describe` command** to verify table structure
4. **Check with `list-tables`** to see what tables exist
5. **Use `sync-schema`** for automatic column syncing

---

## Examples

### Complete Workflow: Add Department Column

```bash
# 1. Check current structure
php bin/db-manager.php describe team_members live

# 2. Add the column
php bin/db-manager.php add-column team_members department "VARCHAR(255) NULL" title live

# 3. Verify it was added
php bin/db-manager.php describe team_members live

# 4. Update some records
php bin/db-manager.php query "UPDATE team_members SET department='Operations' WHERE id='team_001'" live

# 5. Check the result
php bin/db-manager.php query "SELECT id, name, department FROM team_members" live
```

### Quick Database Overview

```bash
# List all tables with row counts
php bin/db-manager.php list-tables live

# Check specific table structure
php bin/db-manager.php describe products live

# View sample data
php bin/db-manager.php query "SELECT * FROM products LIMIT 3" live
```

---

## Troubleshooting

### Error: "Failed to connect to live database"

**Solution:** Check `config/database.live.php` and run:
```bash
php bin/test-live-connection.php
```

### Error: "Table not found"

**Solution:** Check table name spelling:
```bash
php bin/db-manager.php list-tables live
```

### Error: "Column already exists"

**Solution:** The column is already there. Check with:
```bash
php bin/db-manager.php describe table_name live
```

---

## Security Notes

⚠️ **Important:**
- Always test commands on **local** first
- Be careful with **UPDATE** and **DELETE** queries
- **Backup your database** before major changes
- The tool requires `config/database.live.php` for live access
- Live database credentials are stored locally (not in git)

---

## Summary

The Database Manager gives you full control over your database from the command line:

✅ **List tables** - See what's in your database  
✅ **View structure** - Check table columns and types  
✅ **Add columns** - Add missing columns easily  
✅ **Run queries** - Execute any SQL query  
✅ **Sync schema** - Automatically sync from local to live  
✅ **Work with both** - Switch between local and live easily  

**You're in full control!** 🎉

