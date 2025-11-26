# 🔐 How to Find WordPress Database Credentials in cPanel

## ❌ Important: cPanel Login ≠ Database Credentials

**cPanel Login Credentials:**
- Used to log into cPanel
- NOT the same as database credentials

**MySQL Database Credentials:**
- Used to connect to the database
- Found in cPanel's MySQL section
- Or in WordPress `wp-config.php` file

---

## ✅ Where to Find Database Credentials in cPanel

### Method 1: MySQL Databases Section (Recommended)

1. **Login to cPanel**
   - Use your cPanel login credentials

2. **Go to "MySQL Databases"**
   - Look for "MySQL Databases" or "MySQL Database Wizard"
   - Usually in the "Databases" section

3. **Find Your Database User**
   - Scroll down to "Current Users" section
   - Look for user: `kdmedsco_wp768`
   - Click "Change Password" or "Modify User" to see/reset password

4. **Find Your Database**
   - Scroll to "Current Databases" section
   - Look for database: `kdmedsco_wp768`
   - Note the full database name (might have a prefix like `username_dbname`)

5. **Check Database Host**
   - Usually `localhost` for same server
   - Sometimes shown as `127.0.0.1`
   - Or a specific hostname like `mysql.yourdomain.com`

---

### Method 2: WordPress Configuration File (wp-config.php)

1. **Go to File Manager in cPanel**
2. **Navigate to your WordPress installation**
   - Usually in `public_html` or a subdirectory
3. **Open `wp-config.php`**
4. **Find these lines:**
   ```php
   define( 'DB_NAME', 'kdmedsco_wp768' );
   define( 'DB_USER', 'kdmedsco_wp768' );
   define( 'DB_PASSWORD', 'your_password_here' );
   define( 'DB_HOST', 'localhost' );
   ```
5. **Copy the exact values**

---

### Method 3: phpMyAdmin

1. **Go to phpMyAdmin in cPanel**
2. **Try to login**
   - If it works, those are your database credentials
   - Note: phpMyAdmin might use different authentication

---

## 🔍 Common Issues

### Issue 1: Database User Name Format
- cPanel often adds a prefix to database users
- Example: If cPanel username is `kdmedsco`, database user might be `kdmedsco_wp768`
- Check the exact format in MySQL Databases section

### Issue 2: Database Name Format
- Database name might have a prefix
- Example: `kdmedsco_wp768` (username_prefix + database_name)
- Check in "Current Databases" section

### Issue 3: Password Reset
- If password doesn't work, you can reset it in cPanel
- Go to MySQL Databases → Find user → Change Password
- **Important:** After changing password, update `wp-config.php` too!

---

## 📋 Step-by-Step: Get Credentials from cPanel

1. **Login to cPanel**
   ```
   URL: https://yourdomain.com:2083
   or
   https://yourdomain.com/cpanel
   ```

2. **Find MySQL Databases**
   - Search for "MySQL" in cPanel
   - Click "MySQL Databases"

3. **Note the Database Name**
   - Look in "Current Databases"
   - Full name might be: `kdmedsco_wp768` or `kdmedsco_kdmedsco_wp768`

4. **Note the Database User**
   - Look in "Current Users"
   - Full name might be: `kdmedsco_wp768` or `kdmedsco_kdmedsco_wp768`

5. **Get/Reset Password**
   - Click "Change Password" next to the user
   - Set a new password (remember it!)
   - Update `wp-config.php` with new password

6. **Note the Host**
   - Usually `localhost`
   - Check if shown anywhere in MySQL section

---

## ✅ Use These Credentials in Import Form

Once you have the correct credentials:

1. **Database Host**: Usually `localhost` (or what's shown in cPanel)
2. **Database Name**: The full database name from cPanel
3. **Database Username**: The full username from cPanel
4. **Database Password**: The password you set/reset in cPanel
5. **Table Prefix**: Usually `wp_` or `kdmedsco_` (check wp-config.php)

---

## 🔧 If You Need to Reset Password

1. **In cPanel → MySQL Databases**
2. **Find your database user**
3. **Click "Change Password"**
4. **Set a new password** (make it strong but memorable)
5. **Update `wp-config.php`** with the new password
6. **Use the new password** in the import form

---

## 💡 Pro Tip

**Best Practice:**
1. Use the credentials from `wp-config.php` (they're already working for WordPress)
2. If those don't work, reset the password in cPanel
3. Update both `wp-config.php` and the import form with the new password

---

## 🎯 Quick Checklist

- [ ] Login to cPanel
- [ ] Go to MySQL Databases
- [ ] Find database name (exact format)
- [ ] Find database user (exact format)
- [ ] Get/reset password
- [ ] Note the host (usually localhost)
- [ ] Use these in the import form

---

**Remember:** The credentials in `wp-config.php` are the ones WordPress uses, so those should work! If they don't, there might be a permission issue or the password might have been changed.

