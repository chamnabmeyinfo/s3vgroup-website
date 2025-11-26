# 🔧 Remote Database Connection Troubleshooting
## s3vgroup.com → s3vtgroup.com.kh WordPress Database

---

## ❌ Problem

You've added s3vgroup.com IP to s3vtgroup.com.kh cPanel Remote MySQL, but connection still fails.

---

## 🔍 Step-by-Step Troubleshooting

### Step 1: Verify the Correct IP Address

**Find s3vgroup.com server IP:**

1. **Method A: Check cPanel**
   - Login to s3vgroup.com cPanel
   - Look for "Server Information" or "IP Address"
   - Note the **dedicated IP** (not shared IP)

2. **Method B: Use Terminal/SSH**
   ```bash
   # On s3vgroup.com server
   curl ifconfig.me
   # or
   hostname -I
   ```

3. **Method C: Check DNS**
   - Visit: `https://www.whatismyip.com/`
   - Or use: `nslookup s3vgroup.com`

**Important:** Use the **outgoing IP** of s3vgroup.com, not the domain name.

---

### Step 2: Verify IP Added Correctly

**In s3vtgroup.com.kh cPanel:**

1. **Go to "Remote MySQL"** (or "Remote Database Access")
2. **Check the IP list:**
   - Should see s3vgroup.com IP address
   - Format: `123.456.789.012` (numbers only, no domain names)
3. **If using wildcard:**
   - `%` allows all IPs (less secure)
   - Specific IP is more secure

**Common Mistakes:**
- ❌ Adding domain name instead of IP
- ❌ Adding wrong IP address
- ❌ Not saving after adding
- ❌ Adding to wrong cPanel account

---

### Step 3: Check Database User Permissions

**In s3vtgroup.com.kh cPanel:**

1. **Go to "MySQL Databases"**
2. **Find your WordPress database user**
3. **Check user permissions:**
   - User should have `SELECT` permission
   - User should be allowed for remote access

**If user doesn't have remote access:**

1. **Option A: Create new user for remote access**
   - Create new MySQL user
   - Grant permissions to WordPress database
   - Add to Remote MySQL

2. **Option B: Modify existing user**
   - Some hosts require separate remote user
   - Check with hosting support

---

### Step 4: Verify Host Address

**In WordPress SQL Import form:**

**Try these host addresses (in order):**

1. **WordPress server IP:**
   - Get IP from s3vtgroup.com.kh cPanel
   - Use: `123.456.789.012` (the IP address)

2. **MySQL hostname:**
   - Check in cPanel → MySQL Databases
   - Might be: `mysql.s3vtgroup.com.kh`
   - Or: `localhost` (if same server)

3. **Domain name:**
   - Try: `s3vtgroup.com.kh`
   - Some hosts allow this

4. **With port:**
   - Try: `123.456.789.012:3306`
   - Or: `mysql.s3vtgroup.com.kh:3306`

---

### Step 5: Check Firewall Settings

**On s3vtgroup.com.kh server:**

1. **Check if port 3306 is open:**
   - MySQL uses port 3306
   - Firewall might block it

2. **Contact hosting support:**
   - Ask if port 3306 is open for remote connections
   - Some hosts block MySQL port by default

---

### Step 6: Test Connection Manually

**Create a test script on s3vgroup.com:**

```php
<?php
// test-wp-connection.php
$host = '123.456.789.012'; // WordPress server IP
$database = 'your_wp_database';
$username = 'your_wp_user';
$password = 'your_wp_password';

try {
    $dsn = "mysql:host={$host};port=3306;dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    echo "✅ Connection successful!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
```

**Upload to s3vgroup.com and test:**
- Visit: `https://s3vgroup.com/test-wp-connection.php`
- See exact error message

---

### Step 7: Common Error Messages & Solutions

#### Error: "Access denied for user"

**Causes:**
- Wrong username/password
- User not allowed for remote access
- User doesn't have permission

**Solutions:**
- Verify credentials in wp-config.php
- Check user has remote access permission
- Try creating new remote user

#### Error: "Connection refused" or "Connection timed out"

**Causes:**
- Firewall blocking port 3306
- Wrong IP address
- MySQL not allowing remote connections

**Solutions:**
- Verify IP is correct
- Check firewall settings
- Contact hosting to enable remote MySQL

#### Error: "Unknown database"

**Causes:**
- Wrong database name
- Database doesn't exist
- User doesn't have access to database

**Solutions:**
- Verify database name in wp-config.php
- Check database exists
- Verify user has access

#### Error: "Host not allowed"

**Causes:**
- IP not added to Remote MySQL
- Wrong IP address
- MySQL bind-address restriction

**Solutions:**
- Double-check IP in Remote MySQL
- Verify correct outgoing IP
- Contact hosting support

---

### Step 8: Alternative Solutions

#### Option 1: Use SSH Tunnel (If Available)

If you have SSH access:

1. **Create SSH tunnel:**
   ```bash
   ssh -L 3307:localhost:3306 user@s3vtgroup.com.kh
   ```

2. **Connect via localhost:3307:**
   - Host: `localhost`
   - Port: `3307`

#### Option 2: Export/Import via CSV

Instead of direct database connection:
1. Export products from WordPress as CSV
2. Use CSV import feature in s3vgroup.com

#### Option 3: Use WordPress REST API

If WooCommerce REST API is available:
1. Enable REST API in WordPress
2. Use API to fetch products
3. Import via API instead of direct DB

---

## ✅ Verification Checklist

- [ ] s3vgroup.com server IP identified correctly
- [ ] IP added to s3vtgroup.com.kh Remote MySQL
- [ ] Database user has remote access permission
- [ ] Correct host address used (IP, not domain)
- [ ] Port 3306 is open (check with hosting)
- [ ] Firewall allows MySQL connections
- [ ] Tested with manual connection script
- [ ] Error message checked and addressed

---

## 🆘 Still Not Working?

### Contact Hosting Support

**For s3vtgroup.com.kh hosting:**
- Ask: "Is remote MySQL enabled for my account?"
- Ask: "Is port 3306 open for remote connections?"
- Ask: "Can you verify my Remote MySQL IP whitelist?"

**For s3vgroup.com hosting:**
- Ask: "What is my server's outgoing IP address?"
- Ask: "Are outbound MySQL connections allowed?"

### Provide This Information:

1. **Source server:** s3vgroup.com
2. **Source IP:** [your server IP]
3. **Destination:** s3vtgroup.com.kh MySQL
4. **Port:** 3306
5. **Error message:** [exact error]

---

## 💡 Pro Tips

1. **Use IP, not domain** - More reliable for remote connections
2. **Test with simple script first** - Isolate the issue
3. **Check both servers** - Firewall on both sides matters
4. **Verify credentials** - Use exact values from wp-config.php
5. **Try different host formats** - IP, hostname, with/without port

---

## 🎯 Quick Test

**Try this in WordPress SQL Import form:**

1. **Host:** WordPress server IP (from cPanel)
2. **Database:** From wp-config.php
3. **Username:** From wp-config.php
4. **Password:** From wp-config.php
5. **Prefix:** From wp-config.php

**If still fails:**
- Check error message
- Try manual test script
- Contact hosting support

---

**Remember:** Remote MySQL connections can be blocked by hosting providers for security. Some hosts require special setup or don't allow remote connections at all. Check with your hosting support if the above steps don't work.

