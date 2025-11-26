# WordPress SQL Import API Files

## Required Files

These files must be uploaded to your server for the WordPress SQL Import feature to work:

- `test-connection.php` - Tests WordPress database connection
- `import-sql.php` - Imports products from WordPress database
- `save-config.php` - Saves WordPress database credentials
- `load-config.php` - Loads saved WordPress database credentials

## File Location

**Server Path:** `/api/admin/wordpress/`

**Full URLs:**
- `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
- `https://s3vgroup.com/api/admin/wordpress/import-sql.php`
- `https://s3vgroup.com/api/admin/wordpress/save-config.php`
- `https://s3vgroup.com/api/admin/wordpress/load-config.php`

## Verification

To verify files are uploaded correctly, try accessing:
```
https://s3vgroup.com/api/admin/wordpress/test-connection.php
```

**Expected Response:**
- If file exists: JSON error (not 404)
- If file missing: 404 Not Found

## Permissions

Files should have:
- **File permissions:** 644
- **Folder permissions:** 755

