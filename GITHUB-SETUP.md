# GitHub Configuration Guide

This guide will help you set up your project on GitHub with all the necessary configurations.

## Quick Start

### Option 1: Automated Setup (Recommended)

Run the automated setup script:

```powershell
.\setup-github-config.ps1
```

This script will:
- ✅ Check Git installation
- ✅ Initialize repository if needed
- ✅ Configure GitHub remote
- ✅ Set up main branch
- ✅ Push code to GitHub

### Option 2: Manual Setup

Follow these steps manually:

1. **Create GitHub Repository**
   - Go to: https://github.com/new
   - Repository name: `s3vgroup-website`
   - Description: `S3vgroup - Warehouse & Factory Equipment E-commerce Website`
   - Choose **Private** (recommended) or **Public**
   - **DO NOT** initialize with README, .gitignore, or license
   - Click **Create repository**

2. **Configure Remote**
   ```powershell
   git remote add origin https://github.com/YOUR_USERNAME/s3vgroup-website.git
   git branch -M main
   git push -u origin main
   ```

## GitHub Features Configured

### ✅ GitHub Actions Workflows

The project includes two automated workflows:

1. **CI Workflow** (`.github/workflows/ci.yml`)
   - Runs on every push and pull request
   - Checks PHP syntax
   - Verifies required files
   - Checks for sensitive data

2. **Deploy Workflow** (`.github/workflows/deploy.yml`)
   - Creates deployment package
   - Runs on push to main/master branch
   - Can be triggered manually

### ✅ Dependabot

Automatically updates GitHub Actions dependencies monthly.

### ✅ Issue Templates

Templates for:
- Bug reports
- Feature requests

### ✅ Pull Request Template

Standardized PR template for better code reviews.

## Repository Settings

After pushing to GitHub, configure these settings:

### 1. Branch Protection

1. Go to **Settings** → **Branches**
2. Add rule for `main` branch:
   - ✅ Require pull request reviews
   - ✅ Require status checks to pass
   - ✅ Require branches to be up to date

### 2. GitHub Actions

1. Go to **Settings** → **Actions** → **General**
2. Enable:
   - ✅ Allow all actions and reusable workflows
   - ✅ Read and write permissions

### 3. Security

1. Go to **Settings** → **Security**
2. Enable:
   - ✅ Dependency graph
   - ✅ Dependabot alerts
   - ✅ Dependabot security updates

## Deployment Options

### Option A: cPanel Git Version Control (Recommended)

1. In cPanel, go to **Git Version Control**
2. Click **Create**
3. Repository URL: `https://github.com/YOUR_USERNAME/s3vgroup-website.git`
4. Repository Root: `public_html`
5. Enable **Auto Deploy**
6. Click **Create**

Now every push to GitHub will automatically update your website!

### Option B: Manual Download

1. Download ZIP from GitHub releases or Actions artifacts
2. Upload to cPanel File Manager
3. Extract in `public_html/`

### Option C: GitHub Actions Artifact

1. Go to **Actions** tab in GitHub
2. Download `deployment-package` artifact
3. Upload to cPanel

## Configuration File

The `github-config.json` file contains repository settings:

```json
{
  "repository": {
    "name": "s3vgroup-website",
    "description": "...",
    "visibility": "private"
  },
  "deployment": {
    "method": "cpanel-git",
    "auto_deploy": true
  }
}
```

## Next Steps

1. ✅ Push code to GitHub (done)
2. ⬜ Configure repository settings
3. ⬜ Set up cPanel Git Version Control
4. ⬜ Configure `config/database.php` in cPanel
5. ⬜ Configure `config/site.php` in cPanel
6. ⬜ Import `sql/schema.sql` via phpMyAdmin
7. ⬜ Test website

## Troubleshooting

### Push Failed - Authentication

If push fails due to authentication:

1. **Use Personal Access Token:**
   - Go to: https://github.com/settings/tokens
   - Generate new token (classic)
   - Select scopes: `repo`
   - Use token as password when pushing

2. **Or use SSH:**
   ```powershell
   git remote set-url origin git@github.com:YOUR_USERNAME/s3vgroup-website.git
   ```

### GitHub Actions Not Running

1. Check **Settings** → **Actions** → **General**
2. Ensure workflows are enabled
3. Check workflow file syntax (`.yml` files)

### Branch Name Issues

If you're on `master` branch:

```powershell
git branch -M main
git push -u origin main
```

## Support

For more information:
- See `GITHUB-DEPLOY.md` for deployment details
- See `PUSH-TO-GITHUB.md` for quick push guide
- See `README.md` for project overview

---

**Ready to deploy?** See `GITHUB-DEPLOY.md` for cPanel deployment instructions!

