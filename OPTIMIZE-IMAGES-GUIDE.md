# Optimize Images to Under 1MB - Complete Guide

## Current Status

- **Found:** 150 images over 1MB
- **Total size:** ~8.2 GB
- **GD Extension:** Not enabled (required for optimization)

## Step 1: Enable GD Extension

**Before optimizing, you must enable the GD extension:**

1. Open: `C:\xampp\php\php.ini`
2. Find: `;extension=gd`
3. Change to: `extension=gd` (remove the semicolon)
4. Save the file
5. Restart Apache in XAMPP Control Panel
6. Verify: Run `php -r "echo extension_loaded('gd') ? 'GD Enabled!' : 'GD NOT Enabled';"`

See `ENABLE-GD-EXTENSION.md` for detailed instructions.

## Step 2: Optimize Images

Once GD is enabled, run:

```bash
php bin/optimize-all-to-1mb.php
```

This script will:
- Find all images over 1MB
- Compress and resize them to under 1MB
- Maintain image quality while reducing file size
- Create backups before optimizing

## Step 3: Verify Optimization

After optimization, check results:

```bash
php -r "require 'bootstrap/app.php'; \$images = glob('uploads/site/img_*.{jpg,jpeg,png,webp}', GLOB_BRACE); \$large = 0; foreach(\$images as \$img) { if(filesize(\$img) > 1024*1024) \$large++; } echo 'Large images: ' . \$large;"
```

Should show: `Large images: 0`

## Step 4: Add Optimized Images to Git

```bash
git add uploads/site/
git commit -m "Optimize all images to under 1MB"
git push origin main
```

## Step 5: Deploy to Live Server

On cPanel:
1. Go to Git Version Control
2. Click "Get Update From Remote"
3. This will pull the optimized images

## Alternative: Manual Optimization

If GD cannot be enabled, use external tools:

### Option A: Online Tools
- **TinyPNG:** https://tinypng.com/ (for PNG/JPEG)
- **Squoosh:** https://squoosh.app/ (Google's tool)
- **Compressor.io:** https://compressor.io/

### Option B: Desktop Software
- **ImageOptim** (Mac)
- **FileOptimizer** (Windows)
- **GIMP** (Free, all platforms)

### Option C: Command Line (if ImageMagick installed)
```bash
# Resize and compress JPEG
magick input.jpg -resize 1200x1200 -quality 75 output.jpg

# For PNG
magick input.png -resize 1200x1200 -quality 85 output.png
```

## What Gets Optimized

The script will:
- **Resize** images to max 1200x1200px (or smaller if needed)
- **Compress** JPEG quality to 60-75%
- **Maintain** aspect ratio
- **Target** file size under 1MB
- **Create backups** before optimizing

## Files Affected

All images in `uploads/site/` that are:
- Over 1MB in size
- JPEG, PNG, or WebP format

## Expected Results

- **Before:** 150 images, ~8.2 GB
- **After:** All images under 1MB each
- **Total size reduction:** ~7+ GB saved

---

**Status:** ⚠️ Waiting for GD extension to be enabled

