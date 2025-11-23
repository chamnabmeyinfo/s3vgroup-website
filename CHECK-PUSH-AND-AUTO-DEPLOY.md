# 🔍 How to Check GitHub Push & Auto-Deploy to cPanel

This guide shows you how to verify your code is on GitHub and set up automatic deployment to cPanel.

---

## ✅ Part 1: Check if Code is Pushed to GitHub

### Method 1: Check via GitHub Website (Easiest)

1. **Open your browser** and go to:
   ```
   https://github.com/chamnabmeyinfo/s3vgroup-website
   ```

2. **Check the repository page:**
   - ✅ You should see all your files
   - ✅ You should see recent commits
   - ✅ You should see the main branch
   - ✅ Check the "Last updated" time (should be recent)

3. **Check commits:**
   - Click on the commit count (e.g., "3 commits")
   - You should see:
     - "Remove notepad.txt with exposed token..."
     - "Add GitHub deployment tutorial..."
     - "Initial commit: Complete S3V Group website..."

4. **Check branches:**
   - Click on the branch dropdown (usually shows "main")
   - Verify `main` branch exists and is active

---

### Method 2: Check via Git Commands

Run these commands in PowerShell:

```powershell
cd C:\xampp\htdocs\s3vgroup

# Check remote URL
git remote -v
# Should show: https://github.com/chamnabmeyinfo/s3vgroup-website.git

# Check if local is in sync with remote
git fetch origin
git status
# Should show: "Your branch is up to date with 'origin/main'"

# Compare local vs remote
git log origin/main..HEAD --oneline
# Should be empty (no differences)

# Check remote commits
git log origin/main --oneline -5
# Should show your commits
```

---

### Method 3: Check via GitHub API

```powershell
# Check if repository exists and get info
curl https://api.github.com/repos/chamnabmeyinfo/s3vgroup-website

# Or using PowerShell
Invoke-RestMethod -Uri "https://api.github.com/repos/chamnabmeyinfo/s3vgroup-website" | Select-Object name, updated_at, pushed_at
```

---

## 🚀 Part 2: Set Up Auto-Deploy to cPanel

### Step 1: Verify cPanel Has Git Version Control

1. **Log into cPanel**
2. **Look for "Git Version Control"** in the Files section
3. If you don't see it:
   - Contact your hosting provider to enable it
   - Some hosts call it "Git" or "Git Management"

---

### Step 2: Create Git Repository in cPanel

1. **In cPanel, click "Git Version Control"**

2. **Click "Create" button**

3. **Fill in the form:**
   - **Repository URL**: 
     ```
     https://github.com/chamnabmeyinfo/s3vgroup-website.git
     ```
   - **Repository Root**: 
     ```
     public_html
     ```
     (or your domain's root directory)
   - **Branch**: 
     ```
     main
     ```
   - **Update Period**: 
     ```
     Automatic (recommended)
     ```
   - **Auto Deploy**: 
     ```
     ✅ Enable (CHECK THIS!)
     ```

4. **Click "Create"**

5. **cPanel will clone your repository** to `public_html/`

---

### Step 3: Configure Auto-Deploy

#### Option A: Automatic Auto-Deploy (Recommended)

1. **After creating repository, find it in the list**
2. **Click "Manage"** next to your repository
3. **Enable "Auto Deploy"** if not already enabled
4. **Set "Update Period"** to:
   - **Immediate** (updates on every push)
   - Or **5 minutes** (checks every 5 minutes)

**How it works:**
- Every time you push to GitHub, cPanel automatically pulls the changes
- No manual steps needed!

---

#### Option B: Manual Deploy Button

If auto-deploy isn't available:

1. **Click "Pull or Deploy"** button in cPanel Git
2. **Select your repository**
3. **Click "Update"**

This manually pulls the latest changes from GitHub.

---

### Step 4: Verify Auto-Deploy Works

#### Test 1: Make a Test Change

1. **Make a small change locally:**
   ```powershell
   cd C:\xampp\htdocs\s3vgroup
   
   # Create a test file
   echo "Auto-deploy test $(Get-Date)" > test-deploy.txt
   
   # Commit and push
   git add test-deploy.txt
   git commit -m "Test auto-deploy"
   git push
   ```

2. **Wait for auto-deploy** (immediate or up to 5 minutes)

3. **Check cPanel File Manager:**
   - Go to `public_html/`
   - Look for `test-deploy.txt`
   - It should appear automatically!

4. **Visit your website:**
   ```
   https://yourdomain.com/test-deploy.txt
   ```
   - Should show the test file

5. **Clean up:**
   ```powershell
   git rm test-deploy.txt
   git commit -m "Remove test file"
   git push
   ```

---

#### Test 2: Check Deploy Logs

Some cPanel versions show deployment logs:

1. **In cPanel Git Version Control**
2. **Click on your repository**
3. **Look for "Recent Activity" or "Logs"**
4. **Should show recent pulls/deploys**

---

## 🔧 Troubleshooting Auto-Deploy

### Problem: Auto-Deploy Not Working

**Check these:**

1. **Is Auto-Deploy enabled?**
   - In cPanel Git, click "Manage" on your repository
   - Verify "Auto Deploy" is checked ✅

2. **Is Update Period set correctly?**
   - Should be "Immediate" or a short interval (5 minutes)
   - Not "Never" or "Manual"

3. **Check cPanel error logs:**
   - In cPanel, go to **Errors** or **Logs**
   - Look for Git-related errors

4. **Check file permissions:**
   - In cPanel File Manager
   - Right-click `public_html/`
   - Permissions should be `755`

5. **Verify repository URL:**
   - In cPanel Git, check repository URL is correct
   - Should be: `https://github.com/chamnabmeyinfo/s3vgroup-website.git`

---

### Problem: Push Succeeds but cPanel Doesn't Update

**Solutions:**

1. **Manual trigger:**
   - In cPanel Git, click **"Pull or Deploy"**
   - Click **"Update"** manually

2. **Check update period:**
   - If set to "5 minutes", wait 5 minutes
   - Or change to "Immediate"

3. **Verify credentials:**
   - If repository is private, cPanel needs access
   - You may need to use a Deploy Key or Personal Access Token

---

### Problem: Files in cPanel Don't Match GitHub

**Fix:**

1. **Force sync:**
   ```powershell
   # In cPanel Git, click "Remove" then "Create" again
   # This re-clones the repository
   ```

2. **Or manually pull:**
   - In cPanel Git, click **"Pull or Deploy"**
   - Click **"Update"**

3. **Check for conflicts:**
   - Files modified directly in cPanel may conflict
   - Always edit via GitHub, not directly in cPanel

---

## 📋 Complete Auto-Deploy Checklist

Use this checklist to verify everything:

- [ ] Code pushed to GitHub (verified via GitHub website)
- [ ] Repository exists at: https://github.com/chamnabmeyinfo/s3vgroup-website
- [ ] cPanel has Git Version Control enabled
- [ ] Repository created in cPanel Git
- [ ] Repository URL correct: `https://github.com/chamnabmeyinfo/s3vgroup-website.git`
- [ ] Repository Root set to: `public_html`
- [ ] Branch set to: `main`
- [ ] Auto Deploy enabled ✅
- [ ] Update Period set to "Immediate" or short interval
- [ ] Test push completed successfully
- [ ] Files appear in cPanel automatically
- [ ] Website updates after push

---

## 🎯 Quick Verification Commands

Run these to check everything:

```powershell
cd C:\xampp\htdocs\s3vgroup

# Check if connected to GitHub
git remote -v
# Expected: https://github.com/chamnabmeyinfo/s3vgroup-website.git

# Check if local is synced
git fetch origin
git status
# Expected: "Your branch is up to date with 'origin/main'"

# Check last push time
git log origin/main --oneline -1 --date=relative
# Should show recent commit

# Check for uncommitted changes
git status
# Should show "working tree clean" (no changes)
```

---

## 🔄 Workflow: Update Website

### Your Complete Workflow:

1. **Make changes locally** (edit files in `C:\xampp\htdocs\s3vgroup`)

2. **Commit changes:**
   ```powershell
   git add .
   git commit -m "Your update message"
   git push
   ```

3. **Auto-Deploy happens automatically** (if enabled)
   - cPanel pulls changes from GitHub
   - Website updates automatically

4. **Verify update:**
   - Visit your website
   - Check that changes are live

**That's it!** No manual steps needed if auto-deploy is enabled.

---

## 📞 Need Help?

### Check These Resources:

1. **cPanel Documentation:**
   - https://docs.cpanel.net/whm/advanced/install-git/

2. **GitHub Repository:**
   - https://github.com/chamnabmeyinfo/s3vgroup-website

3. **Your Deployment Guide:**
   - See `GITHUB-DEPLOYMENT-TUTORIAL.md`

---

## ✅ Success Indicators

You know everything is working when:

- ✅ GitHub shows your latest commits
- ✅ cPanel Git shows "Auto Deploy: Enabled"
- ✅ Files in `public_html/` match GitHub
- ✅ Pushing to GitHub automatically updates your website
- ✅ No manual "Pull or Deploy" needed

---

**Congratulations!** 🎉 Once auto-deploy is set up, your workflow becomes:

**Local Edit → Git Push → Auto Deploy → Live Website** 🚀

