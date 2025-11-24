# Enable GD Extension for Image Optimization

## Quick Steps

1. **Open PHP Configuration:**
   - Navigate to: `C:\xampp\php\php.ini`
   - Open with Notepad or any text editor

2. **Find GD Extension:**
   - Press `Ctrl+F` to search
   - Search for: `;extension=gd`
   - You should see a line like: `;extension=gd`

3. **Enable GD:**
   - Remove the semicolon (`;`) at the beginning
   - Change: `;extension=gd`
   - To: `extension=gd`

4. **Save the file**

5. **Restart Apache:**
   - Open XAMPP Control Panel
   - Click **Stop** on Apache
   - Wait 2 seconds
   - Click **Start** on Apache

6. **Verify GD is enabled:**
   ```bash
   php -r "echo extension_loaded('gd') ? 'GD Enabled!' : 'GD NOT Enabled';"
   ```

## Alternative: Check if GD is already enabled differently

Sometimes GD might be enabled with a different name. Check for:
- `extension=gd2`
- `extension=php_gd2.dll`

If you find any of these without a semicolon, GD is already enabled!

## After Enabling GD

Run the optimization script:
```bash
php bin/optimize-all-to-1mb.php
```

This will compress all images over 1MB to be under 1MB.

