# cPanel Terminal Setup Instructions

## Step 1: Navigate to Your Home Directory

In cPanel Terminal, type:

```bash
cd ~
pwd
```

You should see something like `/home/username`

## Step 2: Go to public_html Location

```bash
cd public_html
pwd
```

If the directory is empty or doesn't exist, that's fine.

## Step 3: Clone the Repository

If `public_html` is empty:

```bash
cd ~
rm -rf public_html
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html
```

Or if you're already in `public_html` and it's empty:

```bash
cd ~
cd ..
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html
```

## Step 4: Verify Files Are Cloned

```bash
cd public_html
ls -la
```

You should see all your files including:
- `index.php`
- `ae-load.php`
- `config/`
- `ae-includes/`
- etc.

## Step 5: Create Database Configuration

Create the config directory if needed:

```bash
mkdir -p config
```

Then create the database config file:

```bash
nano config/database.php
```

Paste this (replace with YOUR actual database credentials):

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    return $pdo;
}
```

Press `Ctrl+X`, then `Y`, then `Enter` to save.

## Step 6: Set File Permissions

```bash
cd ~/public_html
chmod -R 755 .
chmod -R 777 uploads 2>/dev/null || (mkdir -p uploads && chmod -R 777 uploads)
```

## Step 7: Test Your Website

Visit: `https://s3vgroup.com/`

## Quick Copy-Paste Commands (All at Once)

Copy and paste this entire block:

```bash
cd ~ && \
rm -rf public_html && \
git clone https://github.com/chamnabmeyinfo/s3vgroup-website.git public_html && \
cd public_html && \
mkdir -p config && \
chmod -R 755 . && \
mkdir -p uploads && \
chmod -R 777 uploads && \
echo "✅ Repository cloned! Now create config/database.php with your database credentials."
```

## Finding Your Database Credentials in cPanel

If you don't remember your database credentials:

1. Go to **cPanel → MySQL Databases**
2. Look for your database name and username
3. To reset password: Click "Change Password" next to your database user

## Alternative: Use Web Interface for Database Config

After cloning, you can also:

1. Visit: `https://s3vgroup.com/create-database-config.php`
2. Fill in the form
3. Submit to create the config file automatically
4. **Delete** `create-database-config.php` after use for security

