# ⚡ Quick Live Site Setup

## 🚀 Fastest Method: Use Setup Wizard

I've created an automated setup wizard for you! Here's how to use it:

### Step 1: Upload Setup Wizard

1. **Push new files to GitHub:**
   ```powershell
   cd C:\xampp\htdocs\s3vgroup
   git add .
   git commit -m "Add live site setup wizard and guides"
   git push
   ```

2. **Pull to cPanel:**
   - Go to cPanel → Git Version Control
   - Click "Pull or Deploy" on your repository
   - Click "Update"

### Step 2: Run Setup Wizard

1. **Visit in browser:**
   ```
   https://yourdomain.com/setup-live-site.php
   ```

2. **Follow the wizard:**
   - Step 1: System Check (automated)
   - Step 2: Database Configuration (enter your cPanel database credentials)
   - Step 3: Site Configuration (enter your live URL and admin password)
   - Step 4: Database Import (instructions provided)
   - Step 5: Verification (checks everything)

3. **Done!** Your website will be configured automatically!

### Step 3: Delete Setup Wizard

⚠️ **IMPORTANT:** After setup, delete `setup-live-site.php` for security!

---

## 📋 Manual Method: Step-by-Step

If you prefer manual setup, follow `LIVE-SETUP-GUIDE.md`

---

## 🔧 Diagnostic Tools

### Test Database Connection

1. **Upload `test-connection.php`** to `public_html/`
2. **Visit:** `https://yourdomain.com/test-connection.php`
3. **Review results** - it will show what's wrong
4. **Delete the file** after testing

---

## ✅ What I've Created For You

1. **`setup-live-site.php`** - Automated setup wizard (use this!)
2. **`test-connection.php`** - Database connection diagnostic tool
3. **`LIVE-SETUP-GUIDE.md`** - Complete manual setup guide
4. **`config/database.local.php.template`** - Database config template

---

## 🎯 Quick Checklist

- [ ] Push new files to GitHub
- [ ] Pull to cPanel
- [ ] Create MySQL database in cPanel
- [ ] Visit `https://yourdomain.com/setup-live-site.php`
- [ ] Follow wizard steps
- [ ] Delete `setup-live-site.php` after completion
- [ ] Test your website!

---

## 🆘 Troubleshooting

If setup wizard doesn't work:

1. **Check file permissions:** Folders should be `755`, files should be `644`
2. **Upload `test-connection.php`** and check what errors it shows
3. **See `LIVE-SETUP-GUIDE.md`** for detailed troubleshooting

---

**Ready to start?** Push the files and visit the setup wizard! 🚀

