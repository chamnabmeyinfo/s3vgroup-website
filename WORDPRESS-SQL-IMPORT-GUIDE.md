# 🗄️ WordPress SQL Import - Complete Guide

## ✅ Feature Created Successfully!

The WordPress SQL Import feature has been added as an **Optional Feature** in your admin panel. This method connects directly to your WordPress/WooCommerce database for faster and more comprehensive imports.

---

## 🚀 How to Use

### Step 1: Enable the Feature

1. Go to **Admin → Optional Features**
2. Find **"WordPress SQL Import"** in the **Integration** section
3. Click **"Enable"** button
4. Click **"→ Go to Import Page"**

### Step 2: Configure WordPress Database Connection

1. Go to the WordPress SQL Import page
2. Enter your WordPress database credentials:
   - **Database Host**: Usually `localhost` or your server IP
   - **Database Name**: Your WordPress database name
   - **Database Username**: WordPress database user
   - **Database Password**: WordPress database password
   - **Table Prefix**: Usually `wp_` (check your WordPress config)

3. Click **"Test Connection"** to verify
4. You should see:
   - ✅ Connection successful
   - Number of products found
   - Number of categories found
   - WordPress version

### Step 3: Configure Import Options

- ✅ **Download product images** (recommended)
- ✅ **Create missing categories** (recommended)
- ✅ **Skip duplicate products** (recommended)
- ☐ **Import product variations** (optional - imports WooCommerce variations as separate products)

### Step 4: Start Import

1. Click **"Start Import"**
2. Watch real-time progress
3. Review import results

---

## 📊 What Gets Imported

### Products
- Product name (from `post_title`)
- Product slug (from `post_name`)
- SKU (from `_sku` meta)
- Description (from `post_content`)
- Summary (from `post_excerpt`)
- Price (from `_regular_price` or `_price` meta)
- Status (publish → PUBLISHED, private → DRAFT)
- Featured image (from `_thumbnail_id`)

### Categories
- Category name and slug
- Automatically created if missing (if option enabled)
- Mapped from WooCommerce `product_cat` taxonomy

### Images
- Downloads featured images to `/uploads/products/`
- Falls back to remote URLs if download fails

---

## 🔧 Technical Details

### WordPress Tables Used

| WordPress Table | Purpose |
|----------------|---------|
| `wp_posts` | Product posts (post_type = 'product') |
| `wp_postmeta` | Product metadata (SKU, price, images) |
| `wp_terms` | Category names |
| `wp_term_taxonomy` | Category taxonomy info |
| `wp_term_relationships` | Product-category relationships |
| `wp_options` | WordPress version info |

### Field Mapping

| WordPress Field | S3V Group Field |
|----------------|----------------|
| `post_title` | `name` |
| `post_name` | `slug` |
| `_sku` (meta) | `sku` |
| `post_content` | `description` |
| `post_excerpt` | `summary` |
| `_regular_price` (meta) | `price` |
| `_thumbnail_id` (meta) → `guid` | `heroImage` |
| `product_cat` (taxonomy) | `categoryId` |
| `post_status` | `status` |

---

## ⚙️ Import Options Explained

### Download Product Images
- **Enabled**: Downloads images from WordPress URLs to your local server
- **Disabled**: Uses remote image URLs (faster, but depends on WordPress server)

### Create Missing Categories
- **Enabled**: Automatically creates categories that don't exist
- **Disabled**: Skips products without matching categories

### Skip Duplicate Products
- **Enabled**: Skips products with existing SKU (no updates)
- **Disabled**: Updates existing products with same SKU

### Import Product Variations
- **Enabled**: Imports WooCommerce variable product variations as separate products
- **Disabled**: Skips variable products (only imports simple products)

---

## 🎯 Advantages Over CSV Import

1. **Faster**: Direct database connection (no file upload)
2. **More Complete**: Includes all product metadata
3. **Real-time**: No need to export/import files
4. **Automatic**: Handles relationships automatically
5. **Comprehensive**: Includes images, categories, and all meta data

---

## ⚠️ Requirements

### Database Access
- WordPress database must be accessible from your server
- Both databases should be on the same server or network
- Proper database credentials required

### Network Access
- If WordPress is on a different server, ensure:
  - Firewall allows MySQL connections
  - Database user has remote access permissions
  - Network connectivity between servers

### Permissions
- WordPress database user needs:
  - `SELECT` permission on WordPress tables
  - Access to `wp_posts`, `wp_postmeta`, `wp_terms`, etc.

---

## 🐛 Troubleshooting

### "Connection Failed - Access denied"
- Check database username and password
- Verify user has access to the database
- Check if user has remote access (if different server)

### "Connection Failed - Database not found"
- Verify database name is correct
- Check if database exists
- Ensure user has access to the database

### "Connection Failed - Cannot connect to host"
- Check host address (localhost vs IP)
- Verify MySQL server is running
- Check firewall settings
- If remote, ensure MySQL allows remote connections

### "No products found"
- Check table prefix is correct
- Verify WooCommerce is installed
- Check if products exist in WordPress
- Ensure products are published

### "Images not downloading"
- Check file permissions on `/uploads/products/`
- Verify image URLs are accessible
- Check PHP `allow_url_fopen` setting
- Verify WordPress server is accessible

---

## 📝 Comparison: CSV vs SQL Import

| Feature | CSV Import | SQL Import |
|---------|-----------|------------|
| **Speed** | Slower (file upload) | Faster (direct DB) |
| **Setup** | Export CSV first | Direct connection |
| **Completeness** | Limited to CSV fields | All WordPress data |
| **Images** | Manual download | Automatic download |
| **Categories** | Manual mapping | Automatic mapping |
| **Variations** | Not supported | Supported (option) |
| **Real-time** | No | Yes |
| **Requirements** | CSV file | DB credentials |

---

## 🎉 Next Steps

1. ✅ Enable the feature in Optional Features
2. ✅ Test database connection
3. ✅ Configure import options
4. ✅ Run the import
5. ✅ Review imported products in **Admin → Products**

---

**Ready to import?** Enable the feature and connect to your WordPress database! 🚀

