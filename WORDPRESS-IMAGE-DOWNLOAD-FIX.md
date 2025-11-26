# WordPress Image Download Fix

## Issue
Some products were still loading images directly from `https://s3vtgroup.com.kh/wp-content/uploads/...` instead of being downloaded and optimized locally. This caused:
- Slow page loads (images loading from external server)
- Large file sizes (no optimization)
- Dependency on external server availability

## Solution
Updated the WordPress SQL Import to **automatically detect and download ALL images from WordPress sites** (`s3vtgroup.com.kh` or `s3vgroup.com`), regardless of the "Download & optimize product images" option setting.

## Changes Made

### 1. Automatic WordPress Image Detection
- **Before:** Images were only downloaded if the "Download & optimize product images" option was checked
- **After:** ALL images from `s3vtgroup.com.kh` are automatically downloaded and optimized, even if the option is unchecked

### 2. URL-Encoded Filename Handling
- **Problem:** WordPress URLs contain URL-encoded characters (e.g., `%E1%9E%87%E1%9E%89...` for Khmer characters)
- **Solution:** 
  - Automatically decode URL-encoded filenames using `urldecode()`
  - Sanitize filenames for filesystem compatibility
  - Generate safe filenames if original is too long or contains invalid characters

### 3. Enhanced Error Handling
- Added retry logic for failed WordPress image downloads
- Better error messages indicating WordPress image download status
- If WordPress image download fails, product is saved without image (doesn't use remote URL)

### 4. Improved cURL Configuration
- Added encoding support (gzip, deflate)
- Increased timeout to 60 seconds for large images
- Better connection timeout handling

## Code Changes

### Image Detection Logic
```php
// Check if image is from WordPress site - ALWAYS download and optimize
$isWordPressImage = strpos($imageUrl, 's3vtgroup.com.kh') !== false || 
                   strpos($imageUrl, 's3vgroup.com') !== false;

// Force download if: (1) option enabled, OR (2) image is from WordPress site
$shouldDownload = $options['download_images'] || $isWordPressImage;
```

### URL Decoding
```php
// Decode URL-encoded filename (handles Khmer and special characters)
$decodedFilename = urldecode($filename);

// Sanitize filename for filesystem
$sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $decodedFilename);
```

## Benefits

1. **All WordPress Images Downloaded:** No more remote image dependencies
2. **Automatic Optimization:** All images are resized (max 1920x1920px) and optimized (target 500KB)
3. **Better Performance:** Images served from local server (faster loading)
4. **Unicode Support:** Properly handles Khmer and other Unicode filenames
5. **Reliability:** No dependency on external WordPress server availability

## Testing

After importing products:
1. Check product images are stored in `/uploads/products/` directory
2. Verify images are optimized (check file sizes - should be < 500KB)
3. Confirm no products are loading images from `s3vtgroup.com.kh`
4. Check browser DevTools Network tab - all images should load from local domain

## Example

**Before:**
- Image URL: `https://s3vtgroup.com.kh/wp-content/uploads/2025/03/%E1%9E%87%E1%9E%89%E1%9F%92%E1%9E%85%E1%9E%B8%E1%9E%84%E2%80%8B-S-3-010-copy.jpg`
- Status: Loaded directly from WordPress server (slow, large file)

**After:**
- Image URL: `/uploads/products/prod_xxx_1234567890.jpg`
- Status: Downloaded, decoded, optimized, and stored locally (fast, small file)

## Notes

- Images are automatically downloaded during import, even if "Download & optimize product images" is unchecked
- If download fails, the import will retry once before giving up
- Products with failed image downloads will be saved without images (not using remote URLs)
- All images follow website standards: max 1920x1920px, target 500KB file size

