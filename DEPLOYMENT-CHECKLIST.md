# Production Deployment Checklist

## Pre-Deployment

### ✅ Code Review
- [x] All temporary diagnostic files removed
- [x] All emergency fix scripts removed
- [x] All temporary documentation consolidated
- [x] .gitignore updated to exclude temporary files

### ✅ Critical Files Verification
- [x] `index.php` - Has fallback for missing config/site.php
- [x] `ae-load.php` - Loads functions.php early
- [x] `ae-includes/footer.php` - No duplicate e() function
- [x] `ae-includes/functions.php` - e() function has safety check
- [x] `ae-includes/header.php` - All widgets properly disabled/commented
- [x] `config/database.php.example` - Template exists
- [x] `config/site.php.example` - Template exists

### ✅ Widget Status
- [x] Loading screen widget - DISABLED
- [x] Mobile app header widget - DISABLED
- [x] Secondary menu widget - DISABLED
- [x] Bottom navigation - DISABLED

## Deployment Steps

### 1. Clone/Update Repository
```bash
cd ~/public_html
git pull origin main
```

### 2. Create Required Config Files

**Database Config:**
```bash
cp config/database.php.example config/database.php
nano config/database.php
# Enter your database credentials
```

**Site Config:**
```bash
cp config/site.php.example config/site.php
nano config/site.php
# Update site information
```

OR use web interface:
- Visit: `https://s3vgroup.com/create-database-config.php` (if exists)
- Visit: `https://s3vgroup.com/create-site-config.php` (if exists)
- **DELETE these files after use!**

### 3. Set Permissions
```bash
chmod -R 755 .
chmod -R 777 uploads
mkdir -p uploads && chmod -R 777 uploads
```

### 4. Verify Files
```bash
# Check critical files exist
ls -la index.php
ls -la ae-load.php
ls -la config/database.php
ls -la config/site.php
ls -la ae-includes/footer.php
ls -la ae-includes/functions.php
```

### 5. Test Website
- [ ] Homepage loads: `https://s3vgroup.com/`
- [ ] Products page works: `https://s3vgroup.com/products.php`
- [ ] Admin panel accessible: `https://s3vgroup.com/ae-admin/`
- [ ] No PHP errors in error log

### 6. Clean Up
```bash
# Remove any diagnostic files
rm -f check-errors.php
rm -f diagnose-production.php
rm -f HOTFIX-e-function.php
rm -f fix-500-error.php
rm -f create-*.php
rm -f cleanup-project.php
```

## Post-Deployment Verification

### ✅ Functionality Tests
- [ ] Homepage displays correctly
- [ ] Navigation menu works
- [ ] Products page loads
- [ ] Product details page works
- [ ] Contact form works (if exists)
- [ ] Admin login works
- [ ] Admin panel loads

### ✅ Error Checks
```bash
# Check error log
tail -50 ~/public_html/error_log

# Should see no fatal errors
```

### ✅ Performance
- [ ] Page loads in < 3 seconds
- [ ] Images load correctly
- [ ] CSS/JS files load
- [ ] No 404 errors for assets

## Rollback Plan

If something goes wrong:

1. **Restore from backup:**
   ```bash
   cd ~
   mv public_html public_html-broken
   mv public_html-backup-YYYYMMDD public_html
   ```

2. **Or reset from git:**
   ```bash
   cd ~/public_html
   git reset --hard HEAD~1
   ```

## Maintenance

### Regular Tasks
- [ ] Monitor error logs weekly
- [ ] Update dependencies monthly
- [ ] Backup database weekly
- [ ] Review security updates

### Files to Monitor
- `error_log` - Check for PHP errors
- `config/database.php` - Keep secure
- `config/site.php` - Keep updated
- `.gitignore` - Ensure sensitive files excluded

