#!/bin/bash
# Quick fix script for .htaccess Git conflict
# Run this via cPanel Terminal or SSH

echo "🔧 Fixing .htaccess Git Conflict..."
echo ""

# Navigate to repository (adjust path if needed)
cd ~/public_html || cd /home/*/public_html || exit 1

echo "📍 Current directory: $(pwd)"
echo ""

# Check Git status
echo "📊 Checking Git status..."
git status

echo ""
echo "📋 Options:"
echo "1. See what changed in .htaccess"
echo "2. Commit server changes"
echo "3. Discard server changes (use repo version)"
echo "4. Stash changes"
echo ""
read -p "Choose option (1-4): " choice

case $choice in
    1)
        echo ""
        echo "📝 Changes in .htaccess:"
        git diff .htaccess
        ;;
    2)
        echo ""
        echo "💾 Committing server changes..."
        git add .htaccess
        git commit -m "Keep server .htaccess changes"
        echo "✅ Changes committed. Now pull from cPanel Git Version Control."
        ;;
    3)
        echo ""
        echo "🔄 Discarding server changes..."
        git checkout -- .htaccess
        echo "✅ Server changes discarded. Now pull from cPanel Git Version Control."
        ;;
    4)
        echo ""
        echo "📦 Stashing changes..."
        git stash
        echo "✅ Changes stashed. Now pull from cPanel Git Version Control."
        echo "💡 To restore later: git stash pop"
        ;;
    *)
        echo "❌ Invalid option"
        exit 1
        ;;
esac

echo ""
echo "✅ Done! Now go to cPanel → Git Version Control → Pull/Update"

