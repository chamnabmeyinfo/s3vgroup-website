#!/bin/bash
# cPanel Terminal Setup Script
# Copy and paste this entire script into cPanel Terminal

echo "========================================="
echo "cPanel Terminal Setup"
echo "========================================="
echo ""

# Get current directory
CURRENT_DIR=$(pwd)
echo "Current directory: $CURRENT_DIR"
echo ""

# Navigate to home directory
cd ~
echo "Changed to home directory: $(pwd)"
echo ""

# Remove existing public_html if it exists
if [ -d "public_html" ]; then
    echo "Removing existing public_html..."
    rm -rf public_html
    echo "✓ Removed"
fi
echo ""

# Clone repository
echo "Cloning repository from GitHub..."
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html

if [ $? -eq 0 ]; then
    echo "✓ Repository cloned successfully"
else
    echo "✗ Failed to clone repository"
    exit 1
fi
echo ""

# Navigate to public_html
cd public_html
echo "Changed to: $(pwd)"
echo ""

# Create config directory
echo "Creating config directory..."
mkdir -p config
echo "✓ Created"
echo ""

# Set permissions
echo "Setting file permissions..."
chmod -R 755 .
echo "✓ Set permissions"
echo ""

# Create uploads directory
echo "Creating uploads directory..."
mkdir -p uploads
chmod -R 777 uploads
echo "✓ Created with write permissions"
echo ""

# List files to verify
echo "Verifying files..."
echo "Files in public_html:"
ls -la | head -20
echo ""

echo "========================================="
echo "✅ Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Create config/database.php with your database credentials"
echo "2. You can use: nano config/database.php"
echo "3. Or visit: https://s3vgroup.com/create-database-config.php"
echo ""
echo "Your website should be ready after you configure the database!"
echo ""

