# Draft Products - External Images Prevention

## Issue
Draft products should not load images from external URLs to prevent:
- Unnecessary network requests
- Potential security issues
- Performance degradation
- Dependency on external resources

## Solution
Implemented checks to prevent draft products from loading external images in the admin panel.

## Changes Made

### 1. Helper Function: `isExternalUrl()`
**File:** `admin/products.php`

- Checks if a URL is external (from a different origin)
- Returns `true` if URL starts with `http://` or `https://` and is from a different domain
- Returns `false` for local/relative URLs or same-origin URLs

### 2. Product Table Display
**File:** `admin/products.php` (line ~970)

- When rendering products in the table:
  - **Draft products with external images**: Shows a placeholder div with "Draft" text
  - **Draft products with local images**: Shows the image normally
  - **Published products**: Shows all images (external or local) normally

### 3. Image Preview in Modal
**File:** `admin/products.php` (line ~442)

- Added `updateImagePreview()` function that:
  - Checks if product status is DRAFT
  - Checks if image URL is external
  - If both conditions are true, shows a warning message instead of the image
  - Otherwise, shows the image preview normally

### 4. Event Listeners
**File:** `admin/products.php`

- Added event listeners to:
  - Status dropdown: Updates preview when status changes
  - Image input field: Updates preview when image URL changes
- Ensures real-time feedback when switching between draft and published status

## Behavior

### Draft Products
- ✅ **Local images** (relative paths, same domain): Load normally
- ❌ **External images** (different domain): Show placeholder/warning
- ✅ **No images**: Show "—" placeholder

### Published Products
- ✅ **All images**: Load normally (external or local)

## User Experience

### In Product Table
- Draft products with external images show a gray placeholder box with "Draft" text
- Tooltip explains: "External images disabled for draft products"

### In Edit Modal
- When editing a draft product with an external image:
  - Shows a yellow warning box: "⚠️ External images are disabled for draft products. Please upload a local image or publish the product to view external images."
- When changing status from DRAFT to PUBLISHED:
  - External images automatically become visible
- When changing status from PUBLISHED to DRAFT:
  - External images are hidden and warning is shown

## Technical Details

### External URL Detection
```javascript
function isExternalUrl(url) {
    if (!url) return false;
    if (url.startsWith('http://') || url.startsWith('https://')) {
        try {
            const urlObj = new URL(url);
            const currentOrigin = window.location.origin;
            return urlObj.origin !== currentOrigin;
        } catch (e) {
            return true; // Assume external if parsing fails
        }
    }
    return false; // Relative paths are considered local
}
```

### Image Display Logic
```javascript
if (product.status === 'DRAFT' && isExternalUrl(product.heroImage)) {
    // Show placeholder
} else {
    // Show image
}
```

## Benefits

1. **Performance**: Reduces unnecessary HTTP requests for draft products
2. **Security**: Prevents loading potentially malicious external resources
3. **User Experience**: Clear visual feedback about why images aren't loading
4. **Data Integrity**: Encourages using local images for draft products
5. **Bandwidth**: Saves bandwidth by not loading external images for unpublished content

## Testing Checklist

- [x] Draft product with external image shows placeholder in table
- [x] Draft product with local image shows image in table
- [x] Published product with external image shows image in table
- [x] Edit modal shows warning for draft products with external images
- [x] Changing status from DRAFT to PUBLISHED shows external image
- [x] Changing status from PUBLISHED to DRAFT hides external image
- [x] Event listeners update preview in real-time

## Files Modified

1. `admin/products.php` - Added external URL detection and conditional image rendering

