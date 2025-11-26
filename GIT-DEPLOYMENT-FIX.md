# 🔧 Fix Git Deployment Error: .htaccess Conflict

## ❌ Error Message

```
error: Your local changes to the following files would be overwritten by merge:
.htaccess
Please commit your changes or stash them before you merge. Aborting
```

## 🔍 What This Means

The `.htaccess` file on your server has **uncommitted changes** that would be overwritten by the Git pull/merge. Git is protecting you from losing those changes.

---

## ✅ Solution Options

### Option 1: Commit Server Changes (Recommended if changes are important)

If the server has important changes you want to keep:

1. **Login to cPanel**
2. **Go to Terminal** (or use SSH)
3. **Navigate to your repository:**
   ```bash
   cd ~/public_html
   # or wherever your Git repo is located
   ```
4. **Check what changed:**
   ```bash
   git status
   git diff .htaccess
   ```
5. **Commit the changes:**
   ```bash
   git add .htaccess
   git commit -m "Keep server .htaccess changes"
   ```
6. **Pull/Update from cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull" or "Update"

### Option 2: Stash Server Changes (Keep for later)

If you want to save changes but use the repo version:

1. **Login to cPanel Terminal or SSH**
2. **Navigate to repository:**
   ```bash
   cd ~/public_html
   ```
3. **Stash the changes:**
   ```bash
   git stash
   ```
4. **Pull/Update from cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull" or "Update"
5. **If you need the stashed changes later:**
   ```bash
   git stash pop
   ```

### Option 3: Discard Server Changes (Use repo version)

If the server changes aren't important and you want to use the repository version:

1. **Login to cPanel Terminal or SSH**
2. **Navigate to repository:**
   ```bash
   cd ~/public_html
   ```
3. **Discard local changes:**
   ```bash
   git checkout -- .htaccess
   ```
4. **Pull/Update from cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull" or "Update"

### Option 4: Force Overwrite (⚠️ Use with caution)

If you're sure you want to overwrite server changes:

1. **Login to cPanel Terminal or SSH**
2. **Navigate to repository:**
   ```bash
   cd ~/public_html
   ```
3. **Reset to match repository:**
   ```bash
   git fetch origin
   git reset --hard origin/main
   # or origin/master if using master branch
   ```

---

## 🎯 Quick Fix via cPanel File Manager

If you don't have SSH/Terminal access:

1. **Login to cPanel**
2. **Go to File Manager**
3. **Navigate to** `public_html/`
4. **Backup `.htaccess`:**
   - Right-click `.htaccess` → Copy
   - Rename copy to `.htaccess.backup`
5. **Delete `.htaccess`** (or rename it)
6. **Try Git Pull again** in cPanel
7. **If needed, restore from backup:**
   - Rename `.htaccess.backup` back to `.htaccess`

---

## 🔍 Check What Changed

Before deciding, check what's different:

### Via cPanel Terminal:

```bash
cd ~/public_html
git diff .htaccess
```

This will show you:
- Lines removed (marked with `-`)
- Lines added (marked with `+`)

### Common Differences:

- **Server-specific paths** (e.g., different RewriteBase)
- **cPanel auto-generated rules**
- **Security headers added by hosting**
- **Performance optimizations**

---

## 💡 Recommended Approach

**For your case (WordPress SQL Import feature):**

1. **Check what changed:**
   ```bash
   git diff .htaccess
   ```

2. **If changes are important** (security, performance):
   - Commit them: `git add .htaccess && git commit -m "Server .htaccess updates"`
   - Then pull

3. **If changes are not important:**
   - Discard them: `git checkout -- .htaccess`
   - Then pull

4. **After pull, verify `.htaccess` has:**
   - Line 14: `RewriteCond %{REQUEST_URI} !^/api/` (for API endpoints)
   - This is needed for WordPress SQL Import to work

---

## 🚨 Important: After Fixing

After resolving the conflict:

1. **Verify `.htaccess` is correct:**
   - Check that `/api/` exclusion is present (line 14)
   - This is required for API endpoints to work

2. **Test the WordPress SQL Import:**
   - Go to `https://s3vgroup.com/admin/wordpress-sql-import.php`
   - Try testing the connection
   - Should work now! ✅

---

## 📝 Prevention Tips

To avoid this in the future:

1. **Always commit `.htaccess` changes** to Git before deploying
2. **Don't edit `.htaccess` directly on server** - edit locally and push
3. **Use `.htaccess.local`** for server-specific changes (and add to `.gitignore`)

---

## 🆘 Still Having Issues?

If none of the above works:

1. **Contact your hosting support** - they can help with Git conflicts
2. **Manual upload** - Download `.htaccess` from repo and upload via File Manager
3. **Check Git repository path** - Make sure you're in the right directory

---

**Quick Command Reference:**

```bash
# Check status
git status

# See what changed
git diff .htaccess

# Commit changes
git add .htaccess
git commit -m "Message"

# Discard changes
git checkout -- .htaccess

# Stash changes
git stash

# Force reset (⚠️ careful!)
git reset --hard origin/main
```

