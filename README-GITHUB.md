# 🚀 Quick Start: GitHub to cPanel Deployment

## Step 1: Push to GitHub

### Option A: Use the Setup Script (Easiest)

1. **Run the setup script:**
   ```powershell
   .\setup-github.ps1
   ```

2. **Follow the prompts:**
   - Create a repository on GitHub (if you haven't)
   - Enter your repository URL when prompted
   - Script will push automatically

### Option B: Manual Setup

1. **Create GitHub Repository:**
   - Go to: https://github.com/new
   - Name: `s3v-forklift-website`
   - **Don't** initialize with README
   - Click **Create repository**

2. **Connect and Push:**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/s3v-forklift-website.git
   git branch -M main
   git push -u origin main
   ```

## Step 2: Deploy to cPanel

### Method 1: cPanel Git Version Control (Best)

1. **In cPanel:**
   - Go to **Git Version Control**
   - Click **Create**
   - Repository URL: `https://github.com/YOUR_USERNAME/s3v-forklift-website.git`
   - Repository Root: `public_html`
   - Click **Create**

2. **Auto-Deploy:**
   - Enable **Auto Deploy** in cPanel Git settings
   - Every `git push` will automatically update your website!

3. **Configure:**
   - Copy `config/database.php.example` → `config/database.php`
   - Copy `config/site.php.example` → `config/site.php`
   - Edit both with your credentials
   - Import `sql/schema.sql` via phpMyAdmin

### Method 2: Download from GitHub

1. **Download ZIP:**
   - Go to your GitHub repository
   - Click **Code** → **Download ZIP**

2. **Upload to cPanel:**
   - Use cPanel File Manager
   - Upload and extract to `public_html/`
   - Configure as above

## Step 3: Future Updates

### If using cPanel Git:

Just push to GitHub:
```bash
git add .
git commit -m "Update website"
git push
```

Then in cPanel Git, click **Pull or Deploy** - done!

### If using manual method:

1. Push to GitHub
2. Download ZIP from GitHub
3. Upload to cPanel

---

**That's it!** Your website is now on GitHub and can be deployed to cPanel.

For detailed instructions, see `GITHUB-DEPLOY.md`
