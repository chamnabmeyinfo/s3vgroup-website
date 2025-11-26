# Published Products Only - Security Fix

## Issue
The public-facing `products.php` page and product APIs needed to ensure only **PUBLISHED** products are displayed to visitors. Draft and archived products should only be visible in the admin panel.

## Changes Made

### 1. ProductRepository::findBySlug()
**File:** `app/Domain/Catalog/ProductRepository.php`

- Added `$publishedOnly` parameter (default: `false`)
- When `$publishedOnly = true`, only returns products with `status = 'PUBLISHED'`
- Admin panel can still access all products (uses default `false`)

### 2. Public Product API - Search
**File:** `api/products/index.php`

- Fixed search functionality to only return published products
- When searching, now filters by `status = 'PUBLISHED'` before applying search filters
- Regular pagination already filtered by published (via `paginate()` method)

### 3. Public Product API - Single Product
**File:** `api/products/show.php`

- Updated to use `findBySlug($slug, true)` to only return published products
- Added double-check to ensure product status is PUBLISHED before returning

### 4. Helper Function
**File:** `includes/functions.php`

- Updated `getProductBySlug()` to use `findBySlug($slug, true)` 
- Ensures public pages only get published products

## What's Already Protected

✅ **ProductRepository::paginate()** - Already filters by `p.status = 'PUBLISHED'`
✅ **ProductRepository::featured()** - Already filters by `p.status = 'PUBLISHED'`
✅ **products.php** - Uses `getAllProducts()` which calls `paginate()` (already protected)
✅ **product.php** - Uses `getProductBySlug()` which now filters by published

## Admin Panel Behavior

The admin panel is **NOT affected** by these changes:
- Admin can still view/edit/delete all products (published, draft, archived)
- Admin uses `findById()` which doesn't filter by status
- Admin uses `findBySlug()` with default `$publishedOnly = false`

## Security Benefits

1. **Draft products** are hidden from public view
2. **Archived products** are hidden from public view
3. **API endpoints** only return published products
4. **Search functionality** only searches published products
5. **Single product pages** only show published products

## Testing Checklist

- [x] Public `products.php` only shows published products
- [x] Public `product.php?slug=...` only shows published products
- [x] API `/api/products/index.php` only returns published products
- [x] API `/api/products/show.php?slug=...` only returns published products
- [x] Search on public site only finds published products
- [x] Admin panel can still edit all products (published, draft, archived)
- [x] Featured products on homepage only show published products

## Files Modified

1. `app/Domain/Catalog/ProductRepository.php` - Added `$publishedOnly` parameter to `findBySlug()`
2. `api/products/index.php` - Fixed search to filter by published status
3. `api/products/show.php` - Updated to only return published products
4. `includes/functions.php` - Updated `getProductBySlug()` to use published-only mode

