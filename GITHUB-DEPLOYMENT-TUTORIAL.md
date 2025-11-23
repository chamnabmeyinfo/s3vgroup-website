# 📚 Complete GitHub Deployment Tutorial for S3vgroup Project

This is a step-by-step guide to deploy your S3vgroup project using GitHub. Follow along carefully!

---

## 🎯 Prerequisites

Before starting, make sure you have:
- ✅ GitHub account (sign up at https://github.com)
- ✅ Git installed on your computer (check with `git --version`)
- ✅ Your project code ready
- ✅ cPanel hosting account (for deployment)

---

## 📋 Table of Contents

1. [Understanding GitHub Deployment](#1-understanding-github-deployment)
2. [Checking Your Current Setup](#2-checking-your-current-setup)
3. [Step 1: Creating a GitHub Repository](#3-step-1-creating-a-github-repository)
4. [Step 2: Connecting Your Local Project to GitHub](#4-step-2-connecting-your-local-project-to-github)
5. [Step 3: Pushing Your Code to GitHub](#5-step-3-pushing-your-code-to-github)
6. [Step 4: Deploying to cPanel](#6-step-4-deploying-to-cpanel)
7. [Step 5: Updating Your Website](#7-step-5-updating-your-website)
8. [Troubleshooting](#8-troubleshooting)
9. [Best Practices](#9-best-practices)

---

## 1. Understanding GitHub Deployment

### What is GitHub?
GitHub is a code hosting platform that:
- Stores your code in the cloud
- Tracks all changes (version control)
- Makes collaboration easier
- Allows automatic deployment

### Why Use GitHub for Deployment?
- ✅ **Backup**: Your code is safely stored in the cloud
- ✅ **Version Control**: Track all changes and rollback if needed
- ✅ **Easy Updates**: Push changes once, deploy everywhere
- ✅ **Collaboration**: Multiple people can work on the project
- ✅ **History**: See who changed what and when

### Deployment Flow 
```
Local Computer → GitHub → cPanel → Live Website
     (code)      (store)   (host)    (users see)
```

---

## 2. Checking Your Current Setup

Your project is already configured with:
- ✅ Git repository initialized
- ✅ Remote repository: `https://github.com/chamnabmeyinfo/s3vgroup-website.git`
- ✅ Main branch: `main`
- ✅ Initial commit made

Let's verify everything is ready:

### Check Git Status
```powershell
git status
```

Expected output: `working tree clean` (no uncommitted changes)

### Check Remote Repository
```powershell
git remote -v
```

Should show: `origin https://github.com/chamnabmeyinfo/s3vgroup-website.git`

### Check Commit History
```powershell
git log --oneline -5
```

Should show at least one commit.

---

## 3. Step 1: Creating a GitHub Repository

### 3.1. Go to GitHub

1. Open your web browser
2. Go to: **https://github.com**
3. Sign in to your account (or create one if needed)

### 3.2. Create New Repository

1. Click the **"+"** icon in the top right
2. Select **"New repository"**

### 3.3. Configure Repository

Fill in the form:

- **Repository name**: `s3vgroup-website` (or your preferred name)
- **Description**: `S3vgroup - Warehouse & Factory Equipment E-commerce Website`
- **Visibility**:
  - 🔒 **Private** (recommended for business projects)
  - 🌐 **Public** (if you want others to see your code)

⚠️ **IMPORTANT**: 
- ❌ **DO NOT** check "Initialize with README"
- ❌ **DO NOT** add .gitignore
- ❌ **DO NOT** choose a license
- Leave everything unchecked!

### 3.4. Create Repository

Click the green **"Create repository"** button.

**Note**: GitHub will show you setup instructions, but **don't follow them yet** - we'll use the existing connection!

---

## 4. Step 2: Connecting Your Local Project to GitHub

Your project is **already connected** to:
```
https://github.com/chamnabmeyinfo/s3vgroup-website.git
```

### 4.1. Verify Connection

Run this command:
```powershell
git remote -v
```

You should see:
```
origin  https://github.com/chamnabmeyinfo/s3vgroup-website.git (fetch)
origin  https://github.com/chamnabmeyinfo/s3vgroup-website.git (push)
```

### 4.2. If You Need to Change the Remote URL

If you created a different repository name, update the remote:

```powershell
git remote set-url origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
```

Replace:
- `YOUR_USERNAME` with your GitHub username
- `YOUR_REPO_NAME` with your repository name

---

## 5. Step 3: Pushing Your Code to GitHub

This is where your code gets uploaded to GitHub!

### 5.1. Check Current Status

First, make sure everything is committed:

```powershell
git status
```

If you see "Untracked files" or "Changes not staged", you need to commit them first:

```powershell
# Add all files
git add .

# Commit with a message
git commit -m "Initial project setup"
```

### 5.2. Push to GitHub

Now push your code:

```powershell
git push -u origin main
```

### 5.3. Authentication

You'll be prompted for credentials:

**Option A: Personal Access Token (Recommended)**

1. Go to: https://github.com/settings/tokens
2. Click **"Generate new token (classic)"**
3. Give it a name: `s3vgroup-deployment`
4. Select expiration: `90 days` or `No expiration`
5. Check scope: ✅ **`repo`** (full control)
6. Click **"Generate token"**
7. **Copy the token** (you'll only see it once!)
8. When prompted for password, paste the token

**Option B: GitHub CLI (Alternative)**

```powershell
# Install GitHub CLI first
winget install GitHub.cli

# Then authenticate
gh auth login
```

### 5.4. Verify Upload

After pushing, check GitHub:

1. Go to: `https://github.com/chamnabmeyinfo/s3vgroup-website`
2. You should see all your files!
3. Click on files to view them

**Success! 🎉** Your code is now on GitHub!

---

## 6. Step 4: Deploying to cPanel

Now let's get your code on the live website!

### Method 1: cPanel Git Version Control (⭐ Recommended - Easiest)

This method automatically syncs GitHub with your website.

#### Step 6.1.1: Access cPanel Git

1. Log into your **cPanel** account
2. Find and click **"Git Version Control"** (usually in "Files" section)
3. If you don't see it, contact your hosting provider to enable it

#### Step 6.1.2: Create Git Repository in cPanel

1. Click **"Create"** button
2. Fill in the form:
   - **Repository URL**: `https://github.com/chamnabmeyinfo/s3vgroup-website.git`
   - **Repository Root**: `public_html` (or your domain's root directory)
   - **Branch**: `main`
   - **Auto Deploy**: ✅ **Enable** (this makes updates automatic!)
3. Click **"Create"**

#### Step 6.1.3: Clone Repository

cPanel will automatically:
- Clone your GitHub repository
- Download all files to `public_html/`
- Set up the connection

#### Step 6.1.4: Configure Database

1. In cPanel, go to **MySQL Databases**
2. Create a new database (e.g., `s3vgroup_db`)
3. Create a database user
4. Add user to database with **ALL PRIVILEGES**
5. Note down:
   - Database name
   - Username
   - Password
   - Host (usually `localhost`)

#### Step 6.1.5: Create Configuration Files

1. In cPanel **File Manager**, navigate to `public_html/config/`
2. Copy `database.php.example` → `database.php`
3. Copy `site.php.example` → `site.php`
4. Edit both files with your database credentials

**Edit `config/database.php`:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

**Edit `config/site.php`:**
```php
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_PASSWORD', 'your_secure_password'); // Change this!
```

#### Step 6.1.6: Import Database

1. In cPanel, go to **phpMyAdmin**
2. Select your database
3. Click **"Import"** tab
4. Choose file: `sql/schema.sql`
5. Click **"Go"**

#### Step 6.1.7: Test Website

1. Visit your domain: `https://yourdomain.com`
2. Test admin login: `https://yourdomain.com/admin/login.php`

**Done! Your website is live! 🚀**

---

### Method 2: Manual Download from GitHub

If cPanel Git isn't available, use this method.

#### Step 6.2.1: Download ZIP

1. Go to: `https://github.com/chamnabmeyinfo/s3vgroup-website`
2. Click green **"Code"** button
3. Select **"Download ZIP"**
4. Save the ZIP file to your computer

#### Step 6.2.2: Upload to cPanel

1. Log into **cPanel**
2. Open **File Manager**
3. Navigate to `public_html/`
4. Upload the ZIP file
5. Right-click ZIP → **Extract**
6. Move all files from the extracted folder to `public_html/`
7. Delete the ZIP and empty folder

#### Step 6.2.3: Configure (Same as Method 1)

Follow steps 6.1.4 through 6.1.7 above.

---

### Method 3: GitHub Actions Artifact

For automated deployment packages.

#### Step 6.3.1: Check GitHub Actions

1. Go to your repository on GitHub
2. Click **"Actions"** tab
3. Check if workflow runs successfully

#### Step 6.3.2: Download Artifact

1. Open a completed workflow run
2. Scroll to **"Artifacts"** section
3. Download `deployment-package.zip`
4. Extract and upload to cPanel (same as Method 2)

---

## 7. Step 5: Updating Your Website

Once deployed, updating is easy!

### 7.1. Make Changes Locally

Edit your files on your computer as usual.

### 7.2. Commit Changes

```powershell
# Add changed files
git add .

# Commit with descriptive message
git commit -m "Add new product feature"

# Push to GitHub
git push
```

### 7.3. Deploy Updates

**If using cPanel Git (Method 1):**
1. After pushing to GitHub, go to cPanel
2. Open **Git Version Control**
3. Click **"Pull or Deploy"** on your repository
4. Done! Website updates automatically!

**If using Manual Method (Method 2):**
1. Push to GitHub
2. Download new ZIP from GitHub
3. Upload and replace files in cPanel
4. Or use FTP to upload only changed files

---

## 8. Troubleshooting

### Problem: "Authentication Failed" when pushing

**Solution:**
1. Use Personal Access Token instead of password
2. Go to: https://github.com/settings/tokens
3. Generate new token with `repo` scope
4. Use token as password

### Problem: "Repository not found"

**Solution:**
1. Check repository URL is correct: `git remote -v`
2. Make sure repository exists on GitHub
3. Verify you have access (if private repo)

### Problem: "Permission denied" on cPanel

**Solution:**
1. Check file permissions:
   - Folders: `755`
   - Files: `644`
2. Make sure `config/` folder is writable
3. Check `.htaccess` file exists

### Problem: Database connection error

**Solution:**
1. Verify database credentials in `config/database.php`
2. Check database user has proper permissions
3. Ensure database exists in cPanel
4. Try `localhost` or `127.0.0.1` as host

### Problem: Website shows blank page

**Solution:**
1. Check PHP error logs in cPanel
2. Verify PHP version (needs 7.4+)
3. Check if `bootstrap/app.php` is being loaded
4. Verify all files uploaded correctly

---

## 9. Best Practices

### ✅ Do's

- ✅ **Commit often** with descriptive messages
- ✅ **Test locally** before pushing
- ✅ **Use branches** for new features
- ✅ **Keep sensitive files** in `.gitignore`
- ✅ **Regular backups** of database
- ✅ **Monitor error logs** regularly

### ❌ Don'ts

- ❌ **Don't commit** passwords or API keys
- ❌ **Don't push** `.env` or `config/database.php`
- ❌ **Don't deploy** on Friday (harder to fix if broken)
- ❌ **Don't skip** testing before deploying
- ❌ **Don't ignore** error messages

### 📝 Commit Message Guidelines

Write clear, descriptive commit messages:

```powershell
# Good examples:
git commit -m "Add product search functionality"
git commit -m "Fix responsive design on mobile devices"
git commit -m "Update admin panel navigation"

# Bad examples:
git commit -m "fix"
git commit -m "changes"
git commit -m "asdf"
```

---

## 🎓 Quick Reference Commands

```powershell
# Check status
git status

# Add files
git add .
git add specific-file.php

# Commit
git commit -m "Your message here"

# Push to GitHub
git push

# Check remote
git remote -v

# View history
git log --oneline

# Create branch
git checkout -b feature-name

# Switch branch
git checkout main

# Pull latest changes
git pull
```

---

## 📞 Need Help?

If you encounter issues:

1. **Check GitHub Issues**: Search for similar problems
2. **Review Error Messages**: They often point to the solution
3. **Check Logs**: Both GitHub Actions and cPanel error logs
4. **Documentation**: Review your existing guides:
   - `GITHUB-DEPLOY.md`
   - `GITHUB-SETUP.md`
   - `README-GITHUB.md`

---

## ✨ Next Steps

Now that you understand GitHub deployment:

1. ✅ **Set up cPanel Git** for automatic deployments
2. ✅ **Configure branch protection** on GitHub
3. ✅ **Set up GitHub Actions** for automated testing
4. ✅ **Create deployment documentation** for your team
5. ✅ **Schedule regular backups** of your database

---

**Congratulations! 🎉** You now know how to deploy your project using GitHub!

Your website is now:
- ✅ Backed up in the cloud
- ✅ Version controlled
- ✅ Easy to update
- ✅ Ready for collaboration

Happy deploying! 🚀

