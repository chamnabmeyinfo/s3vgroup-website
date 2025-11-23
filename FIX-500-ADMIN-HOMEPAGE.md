# 🔧 Fix 500 Error: Homepage & Admin Dashboard

## 🚨 Your Issue
- ✅ Admin login page works
- ❌ Homepage (https://s3vgroup.com) - 500 error
- ❌ Admin dashboard (after login) - 500 error

---

## 🔍 Root Cause

The issue is likely:
1. **Database tables don't exist** (schema not imported)
2. **Repository classes failing** when querying non-existent tables
3. **Missing error handling** in index.php and admin/index.php

---

## ✅ What I've Fixed

1. ✅ **Added error handling** to `index.php` (homepage)
2. ✅ **Added error handling** to `admin/index.php` (dashboard)
3. ✅ **Added table existence checks** before querying
4. ✅ **Created `test-homepage.php`** - diagnostic tool

---

## 🚀 Quick Fix Steps

### Step 1: Push Updated Files

```powershell
cd C:\xampp\htdocs\s3vgroup
git add .
git commit -m "Fix 500 errors in homepage and admin dashboard with error handling"
git push
```

Then in cPanel: Git Version Control → Pull or Deploy → Update

---

### Step 2: Import Database Schema (CRITICAL!)

**This is likely the main issue - database tables don't exist!**

1. **cPanel → phpMyAdmin**
2. **Select your database:** `s3vgroup_website`
3. **Click "Import" tab**
4. **Choose file:** `public_html/sql/schema.sql`
5. **Click "Go"**

**Wait for import to complete!** You should see success message.

---

### Step 3: Verify Tables Exist

After importing, check if tables exist:

1. In phpMyAdmin, click on your database
2. You should see tables:
   - ✅ `categories`
   - ✅ `products`
   - ✅ `quote_requests`
   - ✅ `site_options`
   - ✅ `testimonials`
   - ✅ `newsletter_subscribers`
   - ✅ etc.

---

### Step 4: Test Homepage

1. **Visit:** `https://s3vgroup.com/test-homepage.php`
2. **Check results:**
   - Should show all steps passing
   - Should show tables exist
   - Should show categories/products can be fetched

3. **Then test homepage:**
   - Visit: `https://s3vgroup.com`
   - Should work now! ✅

---

### Step 5: Test Admin Dashboard

1. **Login:** `https://s3vgroup.com/admin/login.php`
2. **After login, should redirect to dashboard**
3. **Dashboard should load** (even if tables are empty, it won't crash)

---

## 🔧 If Still Not Working

### Check Error Logs

1. **cPanel → Errors or Logs**
2. **Look for recent PHP errors**
3. **Share the error message** - it will tell us exactly what's wrong

---

### Run Diagnostic Tool

Visit: `https://s3vgroup.com/test-homepage.php`

This will show:
- ✅ What's working
- ❌ What's failing
- 📋 Exact error messages

---

## 📋 Most Likely Issues

### Issue 1: Database Tables Don't Exist ❌

**Fix:** Import `sql/schema.sql` via phpMyAdmin (Step 2 above)

---

### Issue 2: Database Connection Failing ❌

**Fix:** 
1. Verify `.env` file exists in `public_html/`
2. Check database credentials are correct
3. Test connection: `https://s3vgroup.com/test-db.php`

---

### Issue 3: Autoloader Not Working ❌

**Fix:**
1. Check `bootstrap/app.php` loads correctly
2. Check `app/` directory exists in `public_html/`
3. Verify file permissions

---

## ✅ Expected Result After Fix

1. ✅ **Homepage loads:** `https://s3vgroup.com`
   - Shows categories (even if empty)
   - Shows products (even if empty)
   - No 500 error

2. ✅ **Admin dashboard loads:** After login
   - Shows statistics (even if all zeros)
   - No 500 error

---

## 🎯 Action Plan

1. **Push updated files** (with error handling)
2. **Import database schema** via phpMyAdmin ⭐ (MOST IMPORTANT!)
3. **Test homepage:** `https://s3vgroup.com/test-homepage.php`
4. **Test actual homepage:** `https://s3vgroup.com`
5. **Test admin dashboard:** Login and check

---

## 🆘 Still Getting 500 Error?

1. **Check error logs:** cPanel → Errors
2. **Run diagnostic:** `https://s3vgroup.com/test-homepage.php`
3. **Share the error message** from logs or diagnostic tool

---

**The most likely fix: Import the database schema!** The tables probably don't exist yet. 🗄️

