# Project Cleanup & Review Plan

## Files to REMOVE (Temporary/Diagnostic)

### Diagnostic Scripts (Delete after use)
- ✅ `check-errors.php` - Temporary error checker
- ✅ `diagnose-production.php` - Production diagnostic
- ✅ `HOTFIX-e-function.php` - Emergency fix script
- ✅ `fix-500-error.php` - Emergency fix script
- ✅ `create-database-config.php` - One-time setup script
- ✅ `create-site-config.php` - One-time setup script

### Setup Scripts (Keep examples, remove one-time scripts)
- ✅ `cpanel-setup.sh` - One-time setup
- ✅ `RESET-PRODUCTION-SAFE.sh` - Emergency reset script
- ✅ `MINIMAL-FIX-REMOVE-WIDGETS.patch` - Temporary patch file

### Temporary Documentation (Consolidate or remove)
- Multiple `ANT-ELITE-*.md` files (keep only essential)
- `EMERGENCY-MINIMAL-FIX.md`
- `FRESH-CLONE-INSTRUCTIONS.md`
- `CPANEL-TERMINAL-SETUP.md`
- `RESET-PRODUCTION-SAFE.md`
- `FRONTEND-*.md` (multiple)
- `MOBILE-AUDIT-FIXES.md`
- `PROJECT-RESTRUCTURE-PLAN.md`
- `RESTRUCTURE-ACTION-PLAN.md`
- `SCALABILITY-AUDIT-AND-PLAN.md`
- `STRUCTURE-CLEANUP-COMPLETE.md`
- `SYSTEM-STATUS.md`
- `VERIFICATION-REPORT.md`
- `WORDPRESS-*.md` (multiple)

## Files to KEEP

### Core Application
- ✅ All `*.php` files in root (index.php, products.php, etc.)
- ✅ `ae-load.php` - Bootstrap file
- ✅ `ae-admin/` - Admin panel
- ✅ `ae-includes/` - Core includes
- ✅ `app/` - Application core
- ✅ `api/` - API endpoints
- ✅ `config/` - Configuration (keep examples)
- ✅ `bootstrap/` - Bootstrap files
- ✅ `database/` - Migrations

### Essential Documentation
- ✅ `README.md` - Main documentation
- ✅ `ARCHITECTURE-QUICK-REFERENCE.md` - Architecture docs
- ✅ `FEATURES-OVERVIEW.md` - Features list
- ✅ `docs/` - Documentation directory

### Essential Scripts
- ✅ `bin/` - Utility scripts (keep useful ones)
- ✅ `.htaccess` - Apache config
- ✅ `.gitignore` - Git ignore rules

## Critical Files Verification

### Must Exist:
1. ✅ `index.php` - Homepage
2. ✅ `ae-load.php` - Bootstrap
3. ✅ `config/database.php.example` - Database config template
4. ✅ `config/site.php.example` - Site config template
5. ✅ `ae-includes/header.php` - Header
6. ✅ `ae-includes/footer.php` - Footer
7. ✅ `ae-includes/functions.php` - Core functions

### Must Work:
1. ✅ Homepage loads without errors
2. ✅ Header includes correctly
3. ✅ Footer includes correctly
4. ✅ Database connection works
5. ✅ Admin panel accessible
6. ✅ Products page works

