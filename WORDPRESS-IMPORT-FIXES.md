# WordPress SQL Import Fixes

## Issues Fixed

### 1. Non-JSON Response Error
**Problem:** The API endpoints were returning HTML instead of JSON, causing "Server returned non-JSON response" errors.

**Root Causes:**
- Output buffers not properly cleared before sending JSON
- CacheControl headers from bootstrap interfering with API responses
- Bootstrap files potentially outputting content before JSON

**Solutions Applied:**

#### `api/admin/wordpress/test-connection.php`
- Added comprehensive output buffering at the very beginning
- Clear all output buffers before sending any JSON response
- Added proper JSON headers with charset and cache control
- Removed duplicate GET request handling code
- Added `DISABLE_CACHE_CONTROL` constant to prevent CacheControl from running

#### `api/admin/wordpress/import-sql.php`
- Added output buffering at the start
- Improved error handling with proper JSON encoding
- Added `DISABLE_CACHE_CONTROL` constant
- Enhanced error responses with proper headers

#### `bootstrap/app.php`
- Modified to skip `CacheControl::apply()` for API endpoints (paths starting with `/api/`)
- Prevents cache headers from interfering with JSON responses

### 2. Connection Issues
**Problem:** Remote database connections from `s3vgroup.com` to `s3vtgroup.com.kh` were failing.

**Solutions:**
- Enhanced error messages with specific troubleshooting tips
- Added support for port specification in host (e.g., `host:3306`)
- Added connection timeout (10 seconds for test, 30 seconds for import)
- Improved diagnostics to show available post types and table information

## Testing Checklist

After deploying these fixes, test the following:

1. **Test Connection Button**
   - ✅ Should return proper JSON response
   - ✅ Should show connection status (success/failure)
   - ✅ Should display product/category counts
   - ✅ Should show helpful error messages if connection fails

2. **Import Process**
   - ✅ Should stream progress updates in JSON format
   - ✅ Should handle image downloads with progress percentages
   - ✅ Should properly detect and skip duplicates
   - ✅ Should create categories if enabled

3. **Error Handling**
   - ✅ Should return JSON even on errors
   - ✅ Should not show HTML error pages
   - ✅ Should provide helpful troubleshooting tips

## Files Modified

1. `api/admin/wordpress/test-connection.php` - Enhanced output buffering and error handling
2. `api/admin/wordpress/import-sql.php` - Added output buffering and improved error responses
3. `bootstrap/app.php` - Skip CacheControl for API endpoints

## Deployment Notes

1. **Git Deployment:** All changes have been committed and pushed to `main` branch
2. **Server Upload:** Ensure all modified files are uploaded to the server
3. **File Permissions:** Verify API files have proper read/execute permissions (644 or 755)
4. **`.htaccess`:** Already configured to exclude `/api/` from rewrite rules

## Next Steps

1. Test the connection from the admin panel
2. If still getting 404 errors, verify files are uploaded to:
   - `/api/admin/wordpress/test-connection.php`
   - `/api/admin/wordpress/import-sql.php`
3. Check server error logs if issues persist
4. Verify database credentials are correct
5. Ensure remote MySQL access is enabled on `s3vtgroup.com.kh`

## Troubleshooting

### Still Getting "Non-JSON Response"?
1. Check browser console for the actual response
2. Verify file exists on server: `https://s3vgroup.com/api/admin/wordpress/test-connection.php`
3. Check server error logs for PHP errors
4. Verify `.htaccess` is not rewriting API requests

### Connection Still Failing?
1. Verify database credentials in WordPress `wp-config.php`
2. Check if remote MySQL access is enabled on source server
3. Verify IP whitelist includes `s3vgroup.com` server IP
4. Test connection from phpMyAdmin on source server
5. Check firewall rules allow MySQL port 3306

### Import Not Working?
1. Verify feature is enabled in Optional Features
2. Check admin session is active
3. Review server error logs for detailed errors
4. Verify database user has proper permissions
5. Check if WordPress database has products

