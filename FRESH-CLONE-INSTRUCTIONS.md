# Fresh Git Clone Instructions

Since you deleted all files from public_html, you need to clone the repository fresh.

## Step-by-Step Instructions

### Option 1: Clone Fresh (Recommended)

SSH into your server and run:

```bash
cd /home/s3vgroup

# Remove empty public_html if it exists
rm -rf public_html

# Clone the repository
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html

# Go into the directory
cd public_html

# Set up your configuration files
# (You'll need to recreate these or restore from backup)
```

### Option 2: Initialize Git in Existing Directory

If public_html still exists but has no git:

```bash
cd /home/s3vgroup/public_html

# Initialize git
git init

# Add remote repository
git remote add origin https://github.com/chamnabmeyinfo/s3vgroup-website.git

# Fetch and checkout main branch
git fetch origin
git checkout -b main origin/main
```

### After Cloning: Important Configuration

1. **Restore database config** (if you have a backup):
   ```bash
   # Create config directory
   mkdir -p config
   
   # Create database.php with your production credentials
   # (You'll need to manually recreate this or restore from backup)
   ```

2. **Set proper permissions**:
   ```bash
   cd /home/s3vgroup/public_html
   chmod -R 755 .
   chmod -R 777 uploads 2>/dev/null || mkdir -p uploads && chmod -R 777 uploads
   ```

3. **Create .env file** (if needed):
   ```bash
   # Copy from example or create new
   cp .env.example .env
   # Edit with your production values
   ```

## If You Have a Backup

If you saved `config/database.php` somewhere:

```bash
# After cloning, restore your config
cp ~/backup/database.php config/database.php
```

## Quick Clone Command (All-in-One)

```bash
cd /home/s3vgroup && \
rm -rf public_html && \
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html && \
cd public_html && \
echo "Repository cloned! Now restore your config files."
```

