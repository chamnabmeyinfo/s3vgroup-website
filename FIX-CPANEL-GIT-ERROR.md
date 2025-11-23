# 🔧 Fix cPanel Git Pull Error

## ❌ Error Message
```
error: Your local changes to the following files would be overwritten by merge:
.htaccess
Please commit your changes or stash them before you merge. Aborting
```

## 🔍 What's Happening

cPanel detected local changes to `.htaccess` that would be lost when pulling from GitHub. Git won't overwrite your local changes automatically.

---

## ✅ Solution Options

### Option 1: Keep Remote Version (Recommended - Use Our Optimized .htaccess)

If you want to use the optimized `.htaccess` from GitHub (with GZIP compression and caching):

1. **Via cPanel File Manager:**
   - Go to **File Manager** in cPanel
   - Navigate to your website root (usually `public_html`)
   - Right-click on `.htaccess`
   - Select **Rename** and rename it to `.htaccess.backup`
   - Go back to **Git Version Control**
   - Click **Pull or Deploy** → **Update**
   - The optimized `.htaccess` from GitHub will be pulled

2. **Via SSH (if available):**
   ```bash
   cd ~/public_html
   mv .htaccess .htaccess.backup
   git pull origin main
   ```

### Option 2: Keep Local Changes (If You Modified .htaccess Manually)

If you made important changes to `.htaccess` locally that you want to keep:

1. **Via SSH (if available):**
   ```bash
   cd ~/public_html
   git stash
   git pull origin main
   git stash pop
   # Resolve any conflicts manually
   ```

2. **Via cPanel File Manager:**
   - Go to **File Manager**
   - Open `.htaccess` and copy all its contents
   - Save to a text file on your computer
   - Delete `.htaccess` from cPanel
   - Go to **Git Version Control** → **Pull or Deploy** → **Update**
   - After pull, manually merge your local changes back

### Option 3: Force Overwrite (Use Remote Version - Easiest)

**⚠️ Warning:** This will permanently delete your local `.htaccess` changes!

**Via SSH (if available):**
```bash
cd ~/public_html
git checkout -- .htaccess
git pull origin main
```

**Via cPanel File Manager:**
1. Delete `.htaccess` from File Manager
2. Go to **Git Version Control** → **Pull or Deploy** → **Update**

---

## 🎯 Recommended Solution

**Use Option 1** - The optimized `.htaccess` from GitHub includes:
- ✅ GZIP compression (70-80% file size reduction)
- ✅ Browser caching (1 year for static assets)
- ✅ Security headers
- ✅ Performance optimizations

Your local `.htaccess` is probably the old version without these optimizations.

---

## 📋 Step-by-Step: Option 1 (Recommended)

### Method A: Using cPanel File Manager

1. **Login to cPanel**
2. **Open File Manager**
3. **Navigate to your website root** (usually `public_html` or your domain folder)
4. **Find `.htaccess` file**
5. **Rename it:**
   - Right-click → **Rename**
   - Change to: `.htaccess.backup`
   - Click **Rename File**
6. **Go to Git Version Control**
   - Find your repository
   - Click **Pull or Deploy**
   - Click **Update**
7. **Verify:**
   - Go back to File Manager
   - Check that `.htaccess` exists
   - Open it and verify it has the performance optimizations

### Method B: Using SSH (If Available)

```bash
# Connect via SSH to your server
cd ~/public_html  # or your website root directory

# Backup current .htaccess
mv .htaccess .htaccess.backup

# Pull from GitHub
git pull origin main

# Verify the new .htaccess
cat .htaccess | head -20
```

---

## 🔍 Verify the Fix

After pulling, check that `.htaccess` contains:

```apache
# PERFORMANCE OPTIMIZATION - GZIP Compression
<IfModule mod_deflate.c>
    # Compress HTML, CSS, JavaScript...
```

If you see this, the optimized version is installed! ✅

---

## 🚨 If Error Persists

If you still get errors:

1. **Check for other modified files:**
   - Git might complain about other files too
   - Check the full error message

2. **Reset Git state (Advanced - Use with caution):**
   ```bash
   cd ~/public_html
   git reset --hard origin/main
   ```
   ⚠️ **Warning:** This will delete ALL local changes!

3. **Contact your hosting provider:**
   - They can help with Git issues
   - Ask them to run the commands for you

---

## 💡 Prevent Future Issues

To avoid this in the future:

1. **Don't edit files directly in cPanel** if they're in Git
2. **Make changes locally** → Commit → Push → Pull in cPanel
3. **Or use SSH** for Git operations (more reliable)

---

## ✅ Quick Checklist

- [ ] Backup current `.htaccess` (rename to `.htaccess.backup`)
- [ ] Pull from GitHub in cPanel
- [ ] Verify new `.htaccess` has performance optimizations
- [ ] Test website still works
- [ ] Check website speed (should be faster!)

---

**Status**: Ready to fix
**Priority**: High (needed for performance optimizations)

