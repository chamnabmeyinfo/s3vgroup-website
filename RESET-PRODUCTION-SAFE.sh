#!/bin/bash
# Safe Production Reset Script
# This backs up important files before resetting from git

echo "========================================="
echo "Safe Production Reset from Git"
echo "========================================="
echo ""

# Get the directory
PROD_DIR="/home/s3vgroup/public_html"
BACKUP_DIR="/home/s3vgroup/public_html-backup-$(date +%Y%m%d-%H%M%S)"

echo "Production directory: $PROD_DIR"
echo "Backup directory: $BACKUP_DIR"
echo ""

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo "Step 1: Backing up important files..."
echo "--------------------------------------"

# Backup configuration files
if [ -f "$PROD_DIR/config/database.php" ]; then
    echo "✓ Backing up config/database.php"
    mkdir -p "$BACKUP_DIR/config"
    cp "$PROD_DIR/config/database.php" "$BACKUP_DIR/config/database.php"
fi

# Backup .env file
if [ -f "$PROD_DIR/.env" ]; then
    echo "✓ Backing up .env"
    cp "$PROD_DIR/.env" "$BACKUP_DIR/.env"
fi

# Backup uploads directory
if [ -d "$PROD_DIR/uploads" ]; then
    echo "✓ Backing up uploads/ directory"
    cp -r "$PROD_DIR/uploads" "$BACKUP_DIR/uploads"
fi

# Backup ae-content directory (if exists)
if [ -d "$PROD_DIR/ae-content" ]; then
    echo "✓ Backing up ae-content/ directory"
    cp -r "$PROD_DIR/ae-content" "$BACKUP_DIR/ae-content"
fi

echo ""
echo "Step 2: Stashing current changes (if any)..."
echo "--------------------------------------"
cd "$PROD_DIR"
git stash

echo ""
echo "Step 3: Resetting to latest from git..."
echo "--------------------------------------"
git fetch origin
git reset --hard origin/main
git clean -fd

echo ""
echo "Step 4: Restoring important files..."
echo "--------------------------------------"

# Restore database config
if [ -f "$BACKUP_DIR/config/database.php" ]; then
    echo "✓ Restoring config/database.php"
    mkdir -p "$PROD_DIR/config"
    cp "$BACKUP_DIR/config/database.php" "$PROD_DIR/config/database.php"
fi

# Restore .env
if [ -f "$BACKUP_DIR/.env" ]; then
    echo "✓ Restoring .env"
    cp "$BACKUP_DIR/.env" "$PROD_DIR/.env"
fi

# Restore uploads
if [ -d "$BACKUP_DIR/uploads" ]; then
    echo "✓ Restoring uploads/ directory"
    cp -r "$BACKUP_DIR/uploads" "$PROD_DIR/"
fi

# Restore ae-content
if [ -d "$BACKUP_DIR/ae-content" ]; then
    echo "✓ Restoring ae-content/ directory"
    cp -r "$BACKUP_DIR/ae-content" "$PROD_DIR/"
fi

echo ""
echo "========================================="
echo "✅ Reset Complete!"
echo "========================================="
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
echo "You can now test your website. If something goes wrong,"
echo "you can restore files from the backup directory."
echo ""

