# GitHub to cPanel Deployment Guide

## Step 1: Create GitHub Repository

1. **Go to GitHub:** https://github.com/new
2. **Repository name:** `s3v-forklift-website` (or your choice)
3. **Description:** "S3V Forklift Solutions - PHP Website for cPanel"
4. **Visibility:** Private (recommended) or Public
5. **DO NOT** initialize with README, .gitignore, or license
6. Click **Create repository**

## Step 2: Push Code to GitHub

After creating the repository, GitHub will show you commands. Use these:

```bash
cd "C:\Coding Development\s3v-web-php"
git remote add origin https://github.com/YOUR_USERNAME/s3v-forklift-website.git
git branch -M main
git push -u origin main
```

**Replace `YOUR_USERNAME` with your GitHub username!**

## Step 3: Deploy to cPanel

### Option A: cPanel Git Version Control (Recommended)

If your cPanel has Git Version Control:

1. **In cPanel:**
   - Go to **Git Version Control**
   - Click **Create**
   - Repository URL: `https://github.com/YOUR_USERNAME/s3v-forklift-website.git`
   - Repository Root: `public_html`
   - Click **Create**

2. **Clone Repository:**
   - cPanel will clone the repository
   - Files will be in `public_html/`

3. **Configure:**
   - Copy `config/database.php.example` to `config/database.php`
   - Copy `config/site.php.example` to `config/site.php`
   - Edit both files with your credentials
   - Import `sql/schema.sql` via phpMyAdmin

4. **Auto-Deploy (Optional):**
   - In cPanel Git, enable **Auto Deploy**
   - Every push to GitHub will auto-update your website

### Option B: Manual Download from GitHub

1. **Download ZIP:**
   - Go to your GitHub repository
   - Click **Code** → **Download ZIP**
   - Extract the ZIP file

2. **Upload to cPanel:**
   - Use cPanel File Manager
   - Upload all files to `public_html/`
   - Configure as in Option A

### Option C: SSH Git Clone (Advanced)

If you have SSH access to cPanel:

```bash
ssh your_username@your_domain.com
cd public_html
git clone https://github.com/YOUR_USERNAME/s3v-forklift-website.git .
```

## Step 4: Post-Deployment Configuration

1. **Database Setup:**
   - Create MySQL database in cPanel
   - Edit `config/database.php` with credentials
   - Import `sql/schema.sql` via phpMyAdmin

2. **Site Configuration:**
   - Edit `config/site.php` with your site info
   - **Change admin password!**

3. **File Permissions:**
   - Folders: `755`
   - Files: `644`

4. **Test:**
   - Visit your domain
   - Test admin login: `/admin/login.php`

## Updating Website

### If using cPanel Git Version Control:

1. Make changes locally
2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Update website"
   git push
   ```
3. In cPanel Git, click **Pull or Deploy**
4. Website updates automatically!

### If using manual method:

1. Make changes locally
2. Push to GitHub
3. Download ZIP from GitHub
4. Upload to cPanel (replace files)

---

**Need help?** Let me know which method you prefer!
