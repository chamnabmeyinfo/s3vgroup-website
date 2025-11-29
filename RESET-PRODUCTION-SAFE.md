# Safe Production Reset Guide

## ⚠️ IMPORTANT: Backup First!

Before resetting, make sure to backup these important files:

1. **Configuration files** (database credentials, API keys)
   - `config/database.php`
   - `.env` file
   
2. **User-uploaded content**
   - `uploads/` directory
   - `ae-content/` directory (if exists)

## Method 1: Using the Safe Reset Script (Recommended)

1. Upload `RESET-PRODUCTION-SAFE.sh` to your server
2. Make it executable:
   ```bash
   chmod +x RESET-PRODUCTION-SAFE.sh
   ```
3. Run it:
   ```bash
   ./RESET-PRODUCTION-SAFE.sh
   ```

This script will:
- ✅ Backup important files automatically
- ✅ Reset from git
- ✅ Restore your config files
- ✅ Keep your uploads

## Method 2: Manual Steps

### Step 1: Backup Important Files

```bash
cd /home/s3vgroup/public_html

# Create backup directory
mkdir -p ~/backup-$(date +%Y%m%d)

# Backup database config
cp config/database.php ~/backup-$(date +%Y%m%d)/

# Backup .env if exists
cp .env ~/backup-$(date +%Y%m%d)/ 2>/dev/null || echo "No .env file"

# Backup uploads
cp -r uploads ~/backup-$(date +%Y%m%d)/ 2>/dev/null || echo "No uploads directory"
```

### Step 2: Reset from Git

```bash
cd /home/s3vgroup/public_html

# Discard all local changes
git fetch origin
git reset --hard origin/main

# Remove untracked files
git clean -fd
```

### Step 3: Restore Important Files

```bash
# Restore database config
cp ~/backup-$(date +%Y%m%d)/database.php config/

# Restore .env
cp ~/backup-$(date +%Y%m%d)/.env . 2>/dev/null || echo "No .env to restore"

# Restore uploads
cp -r ~/backup-$(date +%Y%m%d)/uploads . 2>/dev/null || echo "No uploads to restore"
```

### Step 4: Clear Cache (if needed)

```bash
# Clear PHP opcache
sudo service php7.4-fpm restart

# Or restart Apache
sudo service apache2 restart
```

## Method 3: Complete Fresh Install (Nuclear Option)

⚠️ **USE ONLY IF YOU'RE SURE!**

```bash
cd /home/s3vgroup

# Backup entire public_html
mv public_html public_html-old-$(date +%Y%m%d)

# Clone fresh from git
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html

# Restore config files
cp public_html-old-*/config/database.php public_html/config/
cp public_html-old-*/.env public_html/ 2>/dev/null || true
cp -r public_html-old-*/uploads public_html/ 2>/dev/null || true

# Set permissions
chmod -R 755 public_html
chmod -R 777 public_html/uploads 2>/dev/null || true
```

## ✅ After Reset

1. Test your website: `https://s3vgroup.com/`
2. Check the homepage loads
3. Test admin panel: `https://s3vgroup.com/ae-admin/`
4. Delete diagnostic files:
   - `check-errors.php`
   - `diagnose-production.php`
   - `HOTFIX-e-function.php`

## 🆘 If Something Goes Wrong

Restore from backup:
```bash
cp ~/backup-YYYYMMDD/database.php config/
cp -r ~/backup-YYYYMMDD/uploads ./
```

Or restore entire old directory:
```bash
mv public_html public_html-broken
mv public_html-old-YYYYMMDD public_html
```

