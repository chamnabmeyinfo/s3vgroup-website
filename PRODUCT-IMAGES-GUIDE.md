# Product Images Guide

## ✅ Products Created with Images

All products have been populated with warehouse & factory equipment related data and images from reliable sources (Unsplash).

### Product Categories & Images

#### 1. Forklifts (3 products)
- **Electric Forklift 3.5 Ton** - Warehouse forklift image
- **Diesel Forklift 5 Ton** - Industrial forklift image  
- **LPG Forklift 2.5 Ton** - Material handling forklift image

#### 2. Material Handling (3 products)
- **Pallet Jack 2.5 Ton** - Warehouse equipment image
- **Electric Pallet Jack** - Material handling image
- **Belt Conveyor System** - Conveyor belt image

#### 3. Storage & Racking (3 products)
- **Pallet Racking System** - Warehouse racking image
- **Cantilever Racking** - Storage system image
- **Industrial Shelving Unit** - Industrial shelving image

#### 4. Loading Equipment (2 products)
- **Loading Dock Leveler** - Loading dock equipment image
- **Portable Loading Ramp** - Loading ramp image

#### 5. Safety Equipment (2 products)
- **Safety Barrier Post** - Industrial safety equipment image
- **Industrial Safety Sign Set** - Safety signage image

## 🖼️ Image Sources

All product images are currently using **Unsplash** URLs which are:
- ✅ Publicly accessible
- ✅ Reliable and fast loading
- ✅ High quality (800x600 resolution)
- ✅ Warehouse/factory equipment themed
- ✅ Optimized for web (auto-format, fit=crop, q=85)

### Image URL Format
```
https://images.unsplash.com/photo-[ID]?w=800&h=600&auto=format&fit=crop&q=85
```

## 🔄 Replacing Images

### Option 1: Through Admin Panel
1. Go to `/admin/products.php`
2. Click "Edit" on any product
3. Update the "Hero Image" field with your image URL
4. Or click "Upload" to upload an image file
5. Save the product

### Option 2: Upload Your Own Images
1. Upload product images to `/uploads/products/` directory
2. Use relative URLs like: `/uploads/products/your-image.jpg`
3. Update products in admin panel with new URLs

### Option 3: Use External Image URLs
- Use any publicly accessible image URL
- Ensure images are optimized (recommended: 800x600 or larger)
- Use HTTPS URLs for better security

## 🎯 Image Best Practices

### Recommended Image Specs
- **Dimensions**: 800x600 pixels (4:3 aspect ratio)
- **File Format**: JPG or WebP
- **File Size**: Under 500KB (optimized)
- **Quality**: High quality but compressed

### Image Loading
- All images use `loading="lazy"` for better performance
- Images are displayed with `object-fit: cover` to prevent stretching
- Aspect ratio containers ensure consistent sizing

## 📋 Current Product Count

**Total Products**: 13
- Forklifts: 3
- Material Handling: 3
- Storage & Racking: 3
- Loading Equipment: 2
- Safety Equipment: 2

## 🔧 Scripts Available

### Create Products
```bash
php bin/seed-warehouse-products.php
```
Creates all warehouse/factory equipment products with sample data.

### Update Images
```bash
php bin/update-product-images.php
```
Updates all product images with reliable URLs.

## ✅ Verification

All product images should:
- ✅ Load properly on the products page
- ✅ Display correctly on product detail pages
- ✅ Show proper aspect ratios (no stretching)
- ✅ Load quickly with lazy loading
- ✅ Be responsive on mobile devices

## 💡 Next Steps

1. **Replace Sample Images**: Upload your actual product photos
2. **Optimize Images**: Ensure images are properly sized and compressed
3. **Add More Products**: Use the admin panel or seed script
4. **Organize Categories**: Manage categories in `/admin/categories.php`

## 🚨 Troubleshooting

### Images Not Loading
- Check if image URLs are publicly accessible
- Verify HTTPS/HTTP protocol
- Check browser console for errors
- Ensure image URLs are not blocked by CORS

### Image Stretching
- Images are set to `object-fit: cover` to prevent stretching
- Ensure aspect ratio containers are used (4:3 ratio)
- Check if images are properly sized

### Slow Loading
- Use optimized images (WebP format recommended)
- Implement lazy loading (already enabled)
- Use CDN for faster delivery
- Compress images to reduce file size

---

**All product images are currently using reliable Unsplash URLs that load properly. You can replace them with your own product images at any time through the admin panel!**

