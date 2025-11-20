# S3V Forklift Website - PHP Version for cPanel

A complete PHP website for S3V Forklift Solutions, designed for easy deployment on cPanel hosting.

## 📋 Features

- **Product Catalog** - Browse and view forklift products
- **Quote Request System** - Customers can request quotes online
- **Admin Dashboard** - Manage products, categories, and quotes
- **Responsive Design** - Works on all devices
- **MySQL Database** - Uses standard MySQL/MariaDB
- **Clean Code** - Simple PHP structure, easy to customize

## 🚀 Quick Start for cPanel

### 1. Upload Files

1. Log into your cPanel account
2. Go to **File Manager**
3. Navigate to `public_html` (or your domain's root directory)
4. Upload all files from this project

### 2. Create Database

1. In cPanel, go to **MySQL Databases**
2. Create a new database (e.g., `s3v_website`)
3. Create a new database user
4. Add the user to the database with **ALL PRIVILEGES**
5. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### 3. Import Database Schema

1. In cPanel, go to **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose `sql/schema.sql` file
5. Click **Go** to import

### 4. Configure Database Connection

1. Edit `config/database.php`
2. Update these values:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   ```

### 5. Configure Site Settings

1. Edit `config/site.php`
2. Update contact information, URLs, and admin credentials:
   ```php
   // Change admin password!
   define('ADMIN_EMAIL', 'admin@s3vtgroup.com');
   define('ADMIN_PASSWORD', 'your_secure_password');
   ```

### 6. Set File Permissions

In cPanel File Manager, set permissions:
- Folders: `755`
- PHP files: `644`
- `.htaccess`: `644`

### 7. Test Your Website

Visit your domain in a browser. You should see the homepage.

**Admin Login:**
- URL: `https://yourdomain.com/admin/login.php`
- Email: (as set in `config/site.php`)
- Password: (as set in `config/site.php`)

## 📁 Project Structure

```
s3v-web-php/
├── admin/              # Admin dashboard
│   ├── index.php      # Dashboard overview
│   ├── products.php   # Product management
│   ├── categories.php # Category management
│   ├── quotes.php     # Quote management
│   └── login.php      # Admin login
├── config/            # Configuration files
│   ├── database.php   # Database connection
│   └── site.php       # Site settings
├── includes/          # Reusable components
│   ├── header.php     # Site header
│   ├── footer.php     # Site footer
│   └── functions.php   # Helper functions
├── sql/               # Database files
│   └── schema.sql     # Database schema
├── index.php          # Homepage
├── products.php       # Products listing
├── product.php        # Product detail
├── quote.php          # Quote request form
├── contact.php        # Contact page
├── .htaccess          # Apache configuration
└── README.md          # This file
```

## 🔧 Configuration

### Database Configuration

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

### Site Configuration

Edit `config/site.php`:
```php
$siteConfig = [
    'name' => 'Your Company Name',
    'contact' => [
        'phone' => '+855 23 123 456',
        'email' => 'sales@yourdomain.com',
        // ...
    ],
];
```

### Admin Credentials

**IMPORTANT:** Change the default admin password in `config/site.php`:
```php
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_PASSWORD', 'your_secure_password'); // Change this!
```

## 📝 Adding Content

### Add Products via Admin

1. Log into admin dashboard
2. Go to **Products**
3. Click **+ New product**
4. Fill in product details
5. Save

### Add Categories via Admin

1. Log into admin dashboard
2. Go to **Categories**
3. Click **+ New category**
4. Fill in category details
5. Save

## 🔒 Security Recommendations

1. **Change Admin Password** - Update `ADMIN_PASSWORD` in `config/site.php`
2. **Protect Config Files** - `.htaccess` already protects `config/` directory
3. **Use HTTPS** - Enable SSL certificate in cPanel
4. **Regular Backups** - Use cPanel backup feature
5. **Update PHP Version** - Use PHP 7.4 or higher
6. **File Permissions** - Keep files at `644`, folders at `755`

## 🐛 Troubleshooting

### Database Connection Error

- Check database credentials in `config/database.php`
- Verify database user has proper permissions
- Ensure database exists in cPanel

### 500 Internal Server Error

- Check `.htaccess` file exists
- Verify PHP version (needs 7.4+)
- Check file permissions
- Review error logs in cPanel

### Admin Login Not Working

- Verify admin credentials in `config/site.php`
- Check session is enabled in PHP
- Clear browser cookies

### Images Not Displaying

- Check image URLs are correct
- Verify file permissions on image directories
- Use absolute URLs for external images

## 📞 Support

For issues or questions:
- Check error logs in cPanel
- Review PHP error logs
- Contact your hosting provider

## 📄 License

This project is proprietary software for S3V Trading Group.

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Compatible with:** cPanel, PHP 7.4+, MySQL 5.7+
