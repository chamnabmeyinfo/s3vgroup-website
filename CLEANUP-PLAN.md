# Project Cleanup Plan

## Files to REMOVE (Temporary Fixes - Already Applied)

### 1. One-Time Fix Documentation
- `FIX-DEPARTMENT-COLUMN.md` - Issue already fixed
- `FIX-CONNECTION-ISSUE.md` - Connection working now
- `FIX-LIVE-CONNECTION.md` - Connection working now
- `QUICK-FIX-TEAM-MEMBERS.md` - Issue already fixed
- `CREATE-LIVE-CONFIG.md` - One-time setup, already done

### 2. Duplicate/Redundant Documentation
- `SETUP-AUTO-SYNC-NOW.md` - Duplicate of AUTO-SCHEMA-SYNC-SETUP.md
- `QUICK-START-AUTO-SYNC.md` - Info already in AUTO-SCHEMA-SYNC-SETUP.md

### 3. One-Time SQL Fix Files (Already Applied)
- `sql/fix-team-members-simple.sql` - Already applied to live DB
- `sql/fix-team-members-columns.sql` - Already applied to live DB
- `sql/fix-team-members-columns-mysql.sql` - Already applied to live DB

### 4. Old/Unused Scripts (If Any)
- Check for any unused seed scripts
- Check for any old migration files

## Files to KEEP (Essential)

### Core Application
- All PHP files in root (index.php, products.php, etc.)
- All admin/ files
- All api/ files
- All app/ files
- All includes/ files
- bootstrap/ files
- config/ files (except .example files are templates)

### Essential Documentation
- `README.md` - Main project overview
- `LOCAL-SETUP.md` - Local development guide
- `LIVE-SETUP-GUIDE.md` - Live server setup
- `AUTO-SCHEMA-SYNC-SETUP.md` - Schema sync guide
- `AUTO-SYNC-SETUP.md` - Database sync guide
- `DATABASE-SYNC-GUIDE.md` - Database sync details
- `DATABASE-MANAGER-GUIDE.md` - Database manager guide
- `SCHEMA-SYNC-GUIDE.md` - Schema sync details
- `PERFORMANCE-RECOMMENDATIONS.md` - Performance tips
- `FEATURES-OVERVIEW.md` - Features documentation
- `ADMIN-ORGANIZATION.md` - Admin panel docs
- `AUTO-IMPORT-DATABASE.md` - Database import guide

### Essential SQL Files
- `sql/schema.sql` - Main database schema
- `sql/site_options.sql` - Site options
- `sql/sample_data.sql` - Sample data

### Essential Scripts
- `bin/auto-sync-schema.php` - Auto schema sync
- `bin/auto-sync-database.php` - Auto database sync
- `bin/db-manager.php` - Database manager
- `bin/sync-database.php` - Manual database sync
- `bin/sync-schema-to-live.php` - Manual schema sync
- `bin/test-live-connection.php` - Connection tester
- `bin/verify-database-schema.php` - Schema verifier
- `bin/fix-team-members-schema.php` - Utility (keep for future use)
- `bin/fix-image-urls.php` - Utility (keep for future use)
- `bin/migrate.php` - Database migrations
- `bin/seed.php` - Database seeding
- All scheduled task scripts (.ps1 files)

### Configuration Templates
- `config/*.example` files - Templates for setup

## Summary

**Remove:** ~8 files (temporary fixes, duplicates, one-time SQL fixes)
**Keep:** All core application files, essential documentation, active scripts

