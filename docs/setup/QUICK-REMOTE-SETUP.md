# ⚡ Quick Remote Database Setup
## s3vgroup.com → s3vtgroup.com.kh

---

## 🎯 Quick Steps

### 1. Get s3vgroup.com IP
- Login to s3vgroup.com cPanel
- Check "Server Information" → IP Address
- **Copy this IP**

### 2. Add IP to Remote MySQL
- Login to s3vtgroup.com.kh cPanel
- Go to "Remote MySQL"
- Add the IP from step 1
- Click "Add Host"

### 3. Get WordPress Credentials
- In s3vtgroup.com.kh cPanel → File Manager
- Open `wp-config.php`
- Copy: DB_NAME, DB_USER, DB_PASSWORD, DB_HOST, $table_prefix

### 4. Connect from s3vgroup.com
- Go to: `https://s3vgroup.com/admin/wordpress-sql-import.php`
- Enter:
  - **Host:** WordPress server IP (from s3vtgroup.com.kh cPanel)
  - **Database:** DB_NAME from wp-config.php
  - **Username:** DB_USER from wp-config.php
  - **Password:** DB_PASSWORD from wp-config.php
  - **Prefix:** $table_prefix from wp-config.php
- Click "Test Connection"

---

## 🔧 If Connection Fails

### Try Different Host Formats:
1. WordPress server IP: `123.456.789.012`
2. MySQL hostname: `mysql.s3vtgroup.com.kh`
3. Domain: `s3vtgroup.com.kh`
4. With port: `123.456.789.012:3306`

### Common Issues:
- **Connection refused:** IP not added or firewall blocking
- **Access denied:** Wrong credentials or user not allowed
- **Host not allowed:** IP not in Remote MySQL list

---

## 📞 Need Help?

See full guide: `REMOTE-DATABASE-SETUP.md`

