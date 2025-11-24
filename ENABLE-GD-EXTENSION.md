# Enable GD Extension for Image Optimization

## ⚠️ Important

The GD extension is **not currently loaded** in your PHP installation. Image optimization **will not work** until GD is enabled.

## What is GD Extension?

The GD (Graphics Draw) extension is a PHP library that allows PHP to create and manipulate image files. It's required for:
- Image resizing
- Image compression
- WebP conversion
- Image optimization

## How to Enable GD in XAMPP

### Step 1: Locate php.ini File

1. Open XAMPP Control Panel
2. Click **Config** next to Apache
3. Select **PHP (php.ini)**
4. This will open `php.ini` in your text editor

**OR** manually find the file:
- Location: `C:\xampp\php\php.ini`

### Step 2: Enable GD Extension

1. In `php.ini`, search for: `;extension=gd`
2. Remove the semicolon (`;`) at the beginning to uncomment it:
   ```ini
   extension=gd
   ```
3. Save the file

### Step 3: Restart Apache

1. In XAMPP Control Panel, click **Stop** for Apache
2. Wait a few seconds
3. Click **Start** for Apache

### Step 4: Verify

Run the check script:
```bash
php bin/check-gd-support.php
```

You should see:
```
✅ GD Extension: Loaded
✅ WebP functions available - WebP conversion will work!
```

## Alternative: Check if GD is Already Available

Sometimes GD is compiled into PHP (not as an extension). Check with:

```bash
php -m | findstr gd
```

If you see `gd` in the output, GD is available even if not listed as an extension.

## Troubleshooting

### GD Still Not Working After Enabling

1. **Check PHP version**: GD should be available in all modern PHP versions
2. **Check for multiple php.ini files**: XAMPP might have different php.ini for CLI vs web
   - CLI: `php --ini` shows which php.ini is used
   - Web: Check `phpinfo()` output
3. **Restart Apache**: Changes only take effect after restart
4. **Check error logs**: Look in `C:\xampp\apache\logs\error.log`

### WebP Support Not Available

If GD loads but WebP doesn't work:

1. **Check PHP version**: WebP requires PHP 7.0+
2. **Check GD version**: Older GD versions don't support WebP
3. **It's OK**: The system will still work with JPEG/PNG optimization

### Still Having Issues?

1. Check XAMPP version (should be recent)
2. Try reinstalling XAMPP
3. Or use a different PHP installation

## After Enabling GD

Once GD is enabled:

1. ✅ Image optimization will work automatically on upload
2. ✅ You can run `php bin/optimize-existing-images.php` to optimize existing images
3. ✅ All new uploads will be automatically optimized

## Quick Test

After enabling GD, test with:

```bash
# Check GD support
php bin/check-gd-support.php

# Test optimization on a sample image
php bin/optimize-existing-images.php
```

---

**Status**: ⚠️ **GD Extension Required** - Please enable GD extension to use image optimization features.

