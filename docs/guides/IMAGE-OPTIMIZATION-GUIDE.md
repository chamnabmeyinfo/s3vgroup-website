# Image Optimization Guide

## Overview

The image upload system has been enhanced to automatically optimize all uploaded images for fast loading. Images are now automatically:

- **Resized** to maximum 1200×1200 pixels (perfect for web display)
- **Compressed** to under 1MB file size
- **Converted to WebP** when it provides better compression
- **Smart cropped** (optional) to maintain aspect ratios

## What Changed

### 1. Enhanced ImageOptimizer Class

The `ImageOptimizer` class now includes:

- **Smart cropping**: Center-crop option for exact dimensions while maintaining aspect ratio
- **Aggressive compression**: Targets 1MB maximum file size with quality adjustment
- **WebP conversion**: Automatically converts JPEG/PNG to WebP when it saves space
- **Progressive JPEG**: Uses progressive encoding for better perceived performance
- **Quality optimization**: Automatically adjusts quality to meet target file size

### 2. Updated Upload Handler

The upload API (`api/admin/upload.php`) now:

- Automatically optimizes all uploaded images
- Uses optimal settings: 1200×1200 max dimensions, 1MB target size
- Handles WebP conversion seamlessly
- Returns optimized file size in response

### 3. Optimization Script

A new script (`bin/optimize-existing-images.php`) allows you to:

- Optimize all existing large images in `uploads/site/`
- See before/after file sizes and savings
- Process images in batch

## How It Works

### Automatic Optimization on Upload

When you upload an image through the admin panel:

1. Image is saved to `uploads/site/`
2. **ImageOptimizer** processes it:
   - Checks if dimensions exceed 1200×1200 → resizes if needed
   - Checks if file size exceeds 1MB → compresses if needed
   - Tries WebP conversion if it would save space
   - Adjusts quality to meet 1MB target
3. Optimized image is saved (or WebP version if better)
4. Original is removed if WebP is significantly smaller

### Optimization Settings

**Default Settings (Product Images):**
- Max dimensions: 1200×1200 pixels
- Target file size: 1MB (1024 KB)
- Quality: 85% initial, adjusts down to 60% if needed
- Aspect ratio: Maintained (no cropping by default)

**For Hero/Banner Images:**
You can use different settings by calling:
```php
ImageOptimizer::resize($path, $mimeType, 1920, 1080, false, 1024 * 1024);
```

## Usage

### Uploading Images

Just upload images normally through the admin panel. They will be automatically optimized!

### Optimizing Existing Images

To optimize all existing large images:

```bash
php bin/optimize-existing-images.php
```

This will:
- Process all images in `uploads/site/`
- Show progress for each image
- Display summary with total savings
- Skip already optimized images

### Custom Optimization

If you need custom optimization in code:

```php
use App\Support\ImageOptimizer;

// Resize and compress (maintain aspect ratio)
ImageOptimizer::resize(
    $imagePath,
    'image/jpeg',
    1200,        // Max width
    1200,        // Max height
    false,       // Don't crop
    1024 * 1024  // Target: 1MB
);

// Smart crop to exact dimensions
ImageOptimizer::resize(
    $imagePath,
    'image/jpeg',
    800,         // Exact width
    600,         // Exact height
    true,        // Crop to exact size
    500 * 1024   // Target: 500KB
);
```

## Performance Benefits

### Before Optimization
- Images: 58 MB each
- Dimensions: 1080×1080 (but huge file size)
- Load time: Very slow on 3G networks

### After Optimization
- Images: Under 1MB each (typically 200-500 KB)
- Dimensions: Max 1200×1200 (perfect for web)
- Load time: Fast loading, even on slow connections

### Expected Results

- **File size reduction**: 90-95% smaller files
- **Storage savings**: Massive reduction in disk usage
- **Load time**: 10-20x faster image loading
- **User experience**: Much better, especially on mobile/3G

## Technical Details

### Supported Formats

- **JPEG**: Optimized with progressive encoding
- **PNG**: Compressed with quality adjustment
- **WebP**: Automatically used when beneficial
- **SVG**: Skipped (already optimized vector format)
- **GIF**: Skipped (animated images)

### Quality Settings

- **JPEG**: Starts at 85%, reduces to 60% if needed
- **PNG**: Compression level 8 (0-9 scale)
- **WebP**: Starts at 85%, reduces to 60% if needed

### WebP Conversion

WebP conversion happens automatically when:
- Original file is JPEG or PNG
- WebP would be at least 10% smaller
- PHP GD extension supports WebP

## Troubleshooting

### Images Still Too Large

If images are still large after optimization:

1. Check if GD extension is loaded: `php -m | grep gd`
2. Verify file permissions on `uploads/site/`
3. Run the optimization script manually: `php bin/optimize-existing-images.php`

### WebP Not Working

If WebP conversion isn't happening:

1. Check PHP version (WebP support requires PHP 7.0+)
2. Verify GD extension: `php -i | grep webp`
3. WebP conversion only happens if it saves significant space

### Quality Too Low

If images look too compressed:

- Adjust quality settings in `ImageOptimizer.php`
- Increase `JPEG_QUALITY_START` constant (default: 85)
- Increase `TARGET_MAX_SIZE` if needed (default: 1MB)

## Best Practices

1. **Upload high-quality originals**: The optimizer will handle compression
2. **Use appropriate dimensions**: Don't upload 4000×4000 images for thumbnails
3. **Run optimization script**: Periodically optimize existing images
4. **Monitor file sizes**: Check that images stay under 1MB
5. **Test on slow connections**: Verify fast loading on 3G networks

## Files Modified

- `app/Support/ImageOptimizer.php` - Enhanced optimization engine
- `api/admin/upload.php` - Updated to use new optimization
- `bin/optimize-existing-images.php` - New batch optimization script

## Next Steps

1. ✅ Upload a test image to see automatic optimization
2. ✅ Run `php bin/optimize-existing-images.php` to optimize existing images
3. ✅ Check file sizes - they should all be under 1MB
4. ✅ Test loading speed - images should load much faster

---

**Result**: All images are now automatically optimized for fast loading! 🚀

