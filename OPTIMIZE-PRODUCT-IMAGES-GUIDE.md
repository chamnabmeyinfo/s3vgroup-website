# Optimize Product Images - Complete Guide

## Current Status

⚠️ **GD Extension Required**: The GD extension must be enabled for image optimization to work.

**Current Issue**: Product images are very large (58MB each) and need to be optimized to under 1MB.

## Quick Solution

### Step 1: Enable GD Extension

1. Open `C:\xampp\php\php.ini`
2. Find: `;extension=gd`
3. Change to: `extension=gd` (remove semicolon)
4. Save file
5. **Restart Apache** in XAMPP Control Panel

### Step 2: Verify GD is Enabled

```bash
php bin/check-gd-support.php
```

Should show: `✅ GD Extension: Loaded`

### Step 3: Optimize All Product Images

```bash
php bin/optimize-product-images.php
```

This will:
- Find all products with images
- Optimize each image to under 1MB
- Resize to max 1200×1200
- Update database if filename changes (WebP conversion)

## What the Script Does

1. **Finds all products** with `heroImage` URLs
2. **Extracts local images** from `uploads/site/` directory
3. **Checks file size** - if > 1MB or dimensions > 1200×1200, optimizes
4. **Resizes** to max 1200×1200 (maintains aspect ratio)
5. **Compresses** to under 1MB target
6. **Converts to WebP** if beneficial
7. **Updates database** if filename changed

## Expected Results

**Before:**
- Images: 58 MB each
- Total: ~10 GB for 180 products

**After:**
- Images: 200-500 KB each (under 1MB)
- Total: ~90 MB for 180 products
- **Savings: ~99% reduction!**

## Troubleshooting

### Images Not Optimizing

If images are still large after running the script:

1. **Check GD Extension**: Run `php bin/check-gd-support.php`
2. **Check File Permissions**: Ensure `uploads/site/` is writable
3. **Check PHP Memory**: May need to increase `memory_limit` in php.ini
4. **Check Error Logs**: Look for errors in PHP error log

### GD Extension Not Available

If you can't enable GD:

1. **Use External Tools**: 
   - TinyPNG: https://tinypng.com/
   - Squoosh: https://squoosh.app/
   - ImageOptim: https://imageoptim.com/

2. **Manual Optimization**:
   - Open images in image editor
   - Resize to 1200×1200
   - Save with quality 80-85%
   - Target: under 1MB

3. **Batch Processing**:
   - Use image editing software with batch processing
   - Process all images in `uploads/site/` at once

## Automatic Optimization

**Good News**: All NEW uploads are automatically optimized!

- When you upload a new image through admin panel
- It's automatically resized to 1200×1200
- Compressed to under 1MB
- No manual steps needed

## Files

- `bin/optimize-product-images.php` - Script to optimize existing product images
- `bin/check-gd-support.php` - Check if GD extension is available
- `app/Support/ImageOptimizer.php` - Image optimization engine

---

**Next Steps**: Enable GD → Run optimization script → All product images will be under 1MB! 🚀

