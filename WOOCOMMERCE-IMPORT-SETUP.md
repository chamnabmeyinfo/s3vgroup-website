# 🛒 WooCommerce CSV Import - Setup Guide

## ✅ Feature Created Successfully!

The WooCommerce CSV Import feature has been added as an **Optional Feature** in your admin panel.

---

## 🚀 How to Use

### Step 1: Enable the Feature

1. Go to **Admin → Optional Features**
2. Find **"WooCommerce CSV Import"** in the **Integration** section
3. Click **"Enable"** button
4. You'll see a link **"→ Go to Import Page"** appear

### Step 2: Export from WooCommerce

1. Go to your WordPress admin panel
2. Navigate to **WooCommerce → Products**
3. Click **"Export"** button
4. Select **"Export all products"** or choose specific products
5. Click **"Generate CSV"**
6. Download the CSV file

### Step 3: Import to Your System

1. Go to **Admin → Optional Features → WooCommerce CSV Import → Go to Import Page**
   - Or directly: `/admin/woocommerce-import.php`
2. Upload your CSV file
3. Configure import options:
   - ✅ **Download product images** (recommended)
   - ✅ **Create missing categories** (recommended)
   - ✅ **Skip duplicate products** (recommended)
4. Click **"Start Import"**
5. Watch the progress in real-time
6. Review the import results

---

## 📋 Supported CSV Fields

The importer automatically maps these WooCommerce fields:

| WooCommerce Field | Maps To |
|------------------|---------|
| `name`, `product name`, `title`, `post_title` | Product Name |
| `slug`, `post_name` | URL Slug |
| `sku` | Product SKU |
| `short description`, `excerpt`, `post_excerpt` | Summary |
| `description`, `post_content` | Full Description |
| `regular_price`, `price`, `_regular_price` | Price |
| `images`, `image`, `featured_image` | Hero Image |
| `categories`, `product_cat` | Category |
| `status`, `post_status` | Status (publish → PUBLISHED) |

---

## ⚙️ Import Options

### Download Product Images
- **Enabled**: Downloads images from URLs to your local server
- **Disabled**: Uses remote image URLs (faster, but depends on external server)

### Create Missing Categories
- **Enabled**: Automatically creates categories that don't exist
- **Disabled**: Skips products without matching categories

### Skip Duplicate Products
- **Enabled**: Skips products with existing SKU (no updates)
- **Disabled**: Updates existing products with same SKU

---

## 📊 Import Process

1. **File Upload**: Validates CSV file format
2. **Column Mapping**: Automatically detects WooCommerce columns
3. **Category Handling**: Finds or creates categories
4. **Image Download**: Downloads images (if enabled)
5. **Product Import**: Inserts products into database
6. **Progress Tracking**: Real-time progress updates
7. **Results Summary**: Shows import statistics

---

## 🎯 Import Results

After import, you'll see:
- ✅ **Imported**: Number of successfully imported products
- ⏭️ **Skipped**: Number of skipped products (duplicates or missing categories)
- ❌ **Errors**: Number of failed imports
- 📦 **Categories**: Number of new categories created

---

## 🔧 Technical Details

### Files Created:
- `admin/woocommerce-import.php` - Import interface
- `api/admin/woocommerce/import-csv.php` - Import API endpoint
- `database/cleanup-products.php` - Product cleanup script (optional)

### Database:
- Uses existing `products` table
- Creates categories if needed
- Handles foreign key relationships

### Image Handling:
- Downloads images to `/uploads/products/`
- Generates unique filenames: `prod_{id}_{timestamp}.{ext}`
- Falls back to remote URL if download fails

---

## ⚠️ Important Notes

1. **Backup First**: Always backup your database before importing
2. **Test with Small File**: Test with a few products first
3. **Category Mapping**: Categories are matched by name (case-insensitive)
4. **Slug Generation**: Slugs are auto-generated if not provided
5. **Duplicate Handling**: Products are checked by SKU for duplicates

---

## 🐛 Troubleshooting

### "Feature not enabled"
- Go to **Optional Features** and enable **WooCommerce CSV Import**

### "Cannot map CSV columns"
- Ensure your CSV has standard WooCommerce column headers
- Check that the file is a valid CSV format

### "No categories available"
- Create at least one category manually before importing
- Or enable "Create missing categories" option

### Images not downloading
- Check file permissions on `/uploads/products/` directory
- Verify image URLs are accessible
- Check PHP `allow_url_fopen` setting

---

## 📝 Next Steps

1. ✅ Enable the feature in Optional Features
2. ✅ Export products from WooCommerce
3. ✅ Run the import
4. ✅ Review imported products in **Admin → Products**

---

**Ready to import?** Enable the feature and start importing! 🚀

