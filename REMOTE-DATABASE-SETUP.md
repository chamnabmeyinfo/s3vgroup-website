# 🔗 Remote Database Connection Setup
## s3vgroup.com → s3vtgroup.com.kh Database

---

## 📋 Overview

You want **s3vgroup.com** to connect to and pull data from **s3vtgroup.com.kh** WordPress database.

---

## ✅ Step-by-Step Setup

### Step 1: Get s3vgroup.com Server IP

**Find the outgoing IP address of s3vgroup.com:**

1. **Method A: cPanel**
   - Login to s3vgroup.com cPanel
   - Go to "Server Information" or "IP Address"
   - Note the **Dedicated IP** or **Shared IP**

2. **Method B: SSH/Terminal**
   ```bash
   curl ifconfig.me
   # or
   hostname -I
   ```

3. **Method C: Online Tool**
   - Visit: `https://www.whatismyip.com/` from s3vgroup.com server
   - Or use: `nslookup s3vgroup.com`

**Important:** You need the **outgoing IP** that s3vgroup.com uses to connect to external servers.

---

### Step 2: Get s3vtgroup.com.kh Database Credentials

**From WordPress wp-config.php:**

1. **Login to s3vtgroup.com.kh cPanel**
2. **Go to File Manager**
3. **Navigate to WordPress root** (usually `public_html`)
4. **Open `wp-config.php`**
5. **Find these values:**
   ```php
   define( 'DB_NAME', 'database_name' );
   define( 'DB_USER', 'database_user' );
   define( 'DB_PASSWORD', 'password' );
   define( 'DB_HOST', 'localhost' );
   $table_prefix = 'wp_';
   ```
6. **Copy these exact values**

---

### Step 3: Enable Remote MySQL Access

**In s3vtgroup.com.kh cPanel:**

1. **Go to "Remote MySQL"** (or "Remote Database Access")
   - Usually in "Databases" section
   - Or search for "Remote MySQL"

2. **Add s3vgroup.com IP:**
   - Enter the IP address from Step 1
   - Format: `123.456.789.012` (numbers only, no domain)
   - Click "Add Host" or "Add"

3. **Verify IP is added:**
   - Should appear in the list of allowed hosts
   - If using wildcard, add `%` (allows all IPs, less secure)

**Important Notes:**
- ❌ Don't add domain name (s3vgroup.com) - use IP only
- ✅ Use the exact outgoing IP from s3vgroup.com
- ✅ Some hosts require you to save/apply changes

---

### Step 4: Get WordPress Server IP/Hostname

**For the connection, you need the MySQL host:**

1. **Check in cPanel → MySQL Databases:**
   - Look for "Current Databases" section
   - Note the MySQL hostname (might be shown there)

2. **Common formats:**
   - IP address: `123.456.789.012`
   - Hostname: `mysql.s3vtgroup.com.kh`
   - Or: `s3vtgroup.com.kh`

3. **If not sure, try:**
   - The server IP from s3vtgroup.com.kh cPanel
   - Or contact hosting support

---

### Step 5: Configure Connection in s3vgroup.com

**In WordPress SQL Import form:**

1. **Go to:** `https://s3vgroup.com/admin/wordpress-sql-import.php`

2. **Enter connection details:**
   - **Host:** WordPress server IP or hostname (from Step 4)
     - Try: `123.456.789.012` (IP)
     - Or: `mysql.s3vtgroup.com.kh` (hostname)
     - Or: `s3vtgroup.com.kh` (domain)
   - **Database:** From wp-config.php `DB_NAME`
   - **Username:** From wp-config.php `DB_USER`
   - **Password:** From wp-config.php `DB_PASSWORD`
   - **Prefix:** From wp-config.php `$table_prefix` (usually `wp_` or `wpg1_`)

3. **Click "Save Configuration"** (optional - saves for later)

4. **Click "Test Connection"**

---

### Step 6: Troubleshoot Connection Issues

**If connection fails, check:**

#### Error: "Connection refused" or "Connection timed out"

**Possible causes:**
- IP not added to Remote MySQL
- Wrong IP address
- Firewall blocking port 3306
- MySQL not allowing remote connections

**Solutions:**
1. Verify IP is in Remote MySQL list
2. Double-check the correct outgoing IP
3. Try different host formats (IP, hostname, domain)
4. Contact hosting to enable remote MySQL

#### Error: "Access denied"

**Possible causes:**
- Wrong username/password
- User doesn't have remote access permission
- User doesn't have SELECT permission

**Solutions:**
1. Verify credentials in wp-config.php
2. Check user has remote access permission
3. Some hosts require separate remote user

#### Error: "Host not allowed"

**Possible causes:**
- IP not in Remote MySQL whitelist
- Wrong IP address added

**Solutions:**
1. Check Remote MySQL list in cPanel
2. Verify correct outgoing IP
3. Try adding `%` (allows all, less secure)

---

## 🧪 Test Connection

### Method 1: Use WordPress SQL Import Page

1. Go to: `https://s3vgroup.com/admin/wordpress-sql-import.php`
2. Enter credentials
3. Click "Test Connection"
4. Should show: ✅ Connection successful with product/category counts

### Method 2: Use Diagnostic Script

1. Upload: `database/test-wp-remote-connection.php` to s3vgroup.com
2. Edit file and update connection details
3. Visit: `https://s3vgroup.com/database/test-wp-remote-connection.php`
4. See detailed connection status and errors

---

## 📝 Connection Details Summary

**Source (s3vgroup.com):**
- Server: s3vgroup.com
- Outgoing IP: [Get from cPanel or SSH]

**Destination (s3vtgroup.com.kh):**
- Database Host: [IP or hostname]
- Database Name: [From wp-config.php]
- Database User: [From wp-config.php]
- Database Password: [From wp-config.php]
- Table Prefix: [From wp-config.php]

**Connection:**
- Port: 3306 (default MySQL port)
- Protocol: MySQL/MariaDB

---

## ✅ Verification Checklist

- [ ] s3vgroup.com outgoing IP identified
- [ ] IP added to s3vtgroup.com.kh Remote MySQL
- [ ] WordPress database credentials obtained from wp-config.php
- [ ] WordPress server IP/hostname identified
- [ ] Connection details entered in WordPress SQL Import form
- [ ] Test Connection successful
- [ ] Can see products/categories count

---

## 🚀 After Connection Works

Once connection is successful:

1. **Save Configuration:**
   - Click "Save Configuration" in WordPress SQL Import page
   - Credentials will be saved for future use

2. **Start Import:**
   - Configure import options
   - Click "Start Import"
   - Products will be imported from s3vtgroup.com.kh

3. **Monitor Progress:**
   - Watch real-time import progress
   - Review import results

---

## 🆘 Still Having Issues?

### Contact Hosting Support

**For s3vtgroup.com.kh:**
- "I need to allow remote MySQL access from s3vgroup.com"
- "Can you verify my Remote MySQL IP whitelist?"
- "Is port 3306 open for remote connections?"

**For s3vgroup.com:**
- "What is my server's outgoing IP address?"
- "Are outbound MySQL connections allowed?"

### Provide This Information:

- Source: s3vgroup.com
- Source IP: [your server IP]
- Destination: s3vtgroup.com.kh MySQL
- Port: 3306
- Error message: [if any]

---

## 💡 Pro Tips

1. **Use IP, not domain** - More reliable for remote connections
2. **Test with diagnostic script first** - Get exact error messages
3. **Save configuration** - Don't re-enter credentials every time
4. **Check both servers** - Firewall on both sides matters
5. **Verify credentials** - Use exact values from wp-config.php

---

**Ready to connect?** Follow the steps above to set up the remote database connection! 🚀

