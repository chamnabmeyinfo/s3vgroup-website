# 🐜 Ant Elite (AE) System - Rename Instructions

## ✅ Code Updated!

All code references have been updated to use **Ant Elite (AE)** instead of WordPress (WP).

---

## ⚠️ Manual Step Required

The directories and files still have `wp-` prefix. Please manually rename them via **File Explorer**:

### Directories to Rename:
1. **`wp-admin`** → **`ae-admin`**
2. **`wp-includes`** → **`ae-includes`**
3. **`wp-content`** → **`ae-content`**

### Files to Rename:
1. **`wp-load.php`** → **`ae-load.php`**
2. **`wp-config.php`** → **`ae-config.php`**

---

## 📋 How to Rename (Windows File Explorer)

1. Open **File Explorer**
2. Navigate to: `C:\xampp\htdocs\s3vgroup\`
3. Right-click each directory/file → **Rename**
4. Change `wp-` to `ae-`

**OR use PowerShell:**
```powershell
cd C:\xampp\htdocs\s3vgroup
Rename-Item wp-admin ae-admin
Rename-Item wp-includes ae-includes
Rename-Item wp-content ae-content
Rename-Item wp-load.php ae-load.php
Rename-Item wp-config.php ae-config.php
```

---

## ✅ What's Already Done

- ✅ All PHP files updated to use `ae-` paths
- ✅ `.htaccess` updated to `/ae-admin/`
- ✅ All constants updated (`AEINC`, `AE_CONTENT_DIR`, etc.)
- ✅ `index.php` updated
- ✅ All admin files updated

---

## 🎯 After Renaming

Once you rename the directories/files, your **Ant Elite (AE) System** will be complete!

**Access your system:**
- Admin: `http://localhost:8080/ae-admin/`
- Login: `http://localhost:8080/ae-admin/login.php`
- Frontend: `http://localhost:8080/`

---

**Status:** ✅ Code Updated | ⏳ Waiting for Directory Rename

