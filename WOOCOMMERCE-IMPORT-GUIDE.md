# 🛒 WooCommerce Product Import Guide

## 📋 Overview

This guide explains how to clean existing products and import products from WooCommerce WordPress into your S3V Group website.

---

## 🎯 Recommended Methods

### **Method 1: WooCommerce REST API (Recommended) ⭐**

**Best for:** Live WordPress site with WooCommerce REST API enabled

**Advantages:**
- ✅ Real-time data sync
- ✅ Automatic image handling
- ✅ Category mapping
- ✅ Can be automated
- ✅ No manual file handling

**Requirements:**
- WooCommerce REST API enabled
- API Consumer Key & Secret
- WordPress site URL

---

### **Method 2: CSV Export/Import**

**Best for:** One-time migration or manual control

**Advantages:**
- ✅ Full control over data
- ✅ Can edit before import
- ✅ Works offline
- ✅ Easy to review

**Requirements:**
- Export products from WooCommerce as CSV
- Map fields manually
- Import script

---

### **Method 3: Direct Database Import**

**Best for:** Advanced users with database access

**Advantages:**
- ✅ Fastest method
- ✅ Direct data transfer
- ✅ Can handle large catalogs

**Requirements:**
- Access to both databases
- SQL knowledge
- Careful field mapping

---

## 📊 Field Mapping

### WooCommerce → S3V Group Database

| WooCommerce Field | S3V Group Field | Notes |
|------------------|----------------|-------|
| `id` | `id` | Generate new ID (e.g., `prod_001`) |
| `name` | `name` | Product title |
| `slug` | `slug` | URL-friendly name |
| `sku` | `sku` | Product SKU |
| `short_description` | `summary` | Brief description |
| `description` | `description` | Full description |
| `regular_price` | `price` | Product price |
| `images[0].src` | `heroImage` | Main product image |
| `categories[].name` | `categoryId` | Map to existing category |
| `meta_data` | `specs` (JSON) | Product specifications |
| `tags[].name` | `highlights` (JSON) | Product highlights |
| `status` | `status` | `publish` → `PUBLISHED` |

---

## 🚀 Implementation Steps

### Step 1: Clean Existing Products

**Script:** `database/cleanup-products.php`

This will:
- Delete all products
- Delete related product_media
- Delete related product_tags
- Keep categories intact

### Step 2: Choose Import Method

#### Option A: REST API Import (Recommended)

**Script:** `database/import-woocommerce-api.php`

**Configuration needed:**
```php
$woocommerce_url = 'https://your-wordpress-site.com';
$consumer_key = 'ck_xxxxxxxxxxxxx';
$consumer_secret = 'cs_xxxxxxxxxxxxx';
```

**Features:**
- Fetches products via REST API
- Downloads images automatically
- Maps categories
- Handles variations
- Progress tracking

#### Option B: CSV Import

**Script:** `database/import-woocommerce-csv.php`

**Steps:**
1. Export products from WooCommerce as CSV
2. Place CSV file in `tmp/woocommerce-products.csv`
3. Run import script
4. Review results

**CSV Format:**
```csv
name,slug,sku,summary,description,price,image_url,category,specs,highlights
Product Name,product-name,SKU-001,Short desc,Full desc,1000.00,https://...,Category Name,"{""key"":""value""}","[""Feature 1"",""Feature 2""]"
```

---

## 🔧 What I'll Build For You

1. **Cleanup Script** (`database/cleanup-products.php`)
   - Safely removes all products
   - Preserves categories
   - Handles foreign keys

2. **REST API Import Script** (`database/import-woocommerce-api.php`)
   - Connects to WooCommerce API
   - Fetches all products
   - Downloads images
   - Maps categories
   - Imports to database

3. **CSV Import Script** (`database/import-woocommerce-csv.php`)
   - Reads CSV file
   - Validates data
   - Maps fields
   - Imports to database

4. **Admin UI** (Optional)
   - Import interface in admin panel
   - Progress tracking
   - Error reporting

---

## ❓ Questions Before I Start

1. **Do you have WooCommerce REST API access?**
   - If yes: Consumer Key & Secret?
   - If no: Can you export products as CSV?

2. **What's your WordPress site URL?**
   - Example: `https://yourstore.com`

3. **How many products do you have?**
   - This helps optimize the import process

4. **Do you want to preserve existing categories or create new ones?**
   - Current categories will be kept
   - WooCommerce categories can be mapped or created

5. **Image handling:**
   - Download images to local server?
   - Or keep remote URLs?

6. **Do you want an admin UI for future imports?**
   - Or just command-line scripts?

---

## 📝 Next Steps

Once you provide the information above, I'll:
1. ✅ Create the cleanup script
2. ✅ Create the import script (API or CSV based on your preference)
3. ✅ Test the import process
4. ✅ Provide usage instructions

---

**Ready to proceed?** Please answer the questions above, and I'll build the import solution for you! 🚀

