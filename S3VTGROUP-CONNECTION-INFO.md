# 🔗 s3vtgroup.com.kh Connection Information

## 📋 Server Details

**WordPress Server (s3vtgroup.com.kh):**
- **Server IP:** `65.60.42.226`
- **cPanel Port:** `2082` (for cPanel access only)
- **MySQL Port:** `3306` (for database connections - default)

---

## ⚠️ Important: Port Clarification

- **Port 2082** = cPanel web interface (for logging into cPanel)
- **Port 3306** = MySQL database (for database connections)

**For database connections, use:**
- Host: `65.60.42.226` (or `65.60.42.226:3306`)
- Port: `3306` (default, can be omitted)

---

## 🔧 Connection Configuration

### In WordPress SQL Import Form:

**Host:** `65.60.42.226`
- Or: `65.60.42.226:3306` (explicit port)
- **NOT:** `65.60.42.226:2082` (that's cPanel, not MySQL)

**Database:** [From wp-config.php]
**Username:** [From wp-config.php]
**Password:** [From wp-config.php]
**Prefix:** [From wp-config.php, usually `wp_` or `wpg1_`]

---

## ✅ Setup Checklist

- [ ] s3vgroup.com IP added to s3vtgroup.com.kh Remote MySQL
- [ ] WordPress database credentials obtained from wp-config.php
- [ ] Host set to: `65.60.42.226` (not with :2082)
- [ ] Test connection from s3vgroup.com WordPress SQL Import page
- [ ] Connection successful ✅

---

## 🧪 Test Connection

1. Go to: `https://s3vgroup.com/admin/wordpress-sql-import.php`
2. Enter:
   - **Host:** `65.60.42.226`
   - **Database:** [from wp-config.php]
   - **Username:** [from wp-config.php]
   - **Password:** [from wp-config.php]
   - **Prefix:** [from wp-config.php]
3. Click "Test Connection"

---

**Remember:** Use port 3306 for MySQL, not 2082! 🚀

