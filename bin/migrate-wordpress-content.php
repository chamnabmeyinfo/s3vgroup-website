<?php
/**
 * WordPress to S3vgroup.com Migration Script
 * 
 * Migrates products, categories, images, and content from WordPress to the new site.
 * 
 * Usage:
 *   php bin/migrate-wordpress-content.php              # Dry run (preview)
 *   php bin/migrate-wordpress-content.php --apply      # Actually migrate
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Support\Id;
use App\Support\ImageOptimizer;

// WordPress database connection
$wpDbConfig = [
    'host' => '127.0.0.1',
    'dbname' => 'wpg1_wp',
    'username' => 'root',
    'password' => '', // XAMPP default
];

// Target database (s3vgroup)
$targetDb = getDB();

// Colors for output
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

function printSuccess(string $message): void {
    echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printInfo(string $message): void {
    echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$apply = in_array('--apply', $argv);
$dryRun = !$apply;

printHeader("WordPress to S3vgroup.com Migration");

if ($dryRun) {
    printInfo("DRY RUN MODE - No data will be migrated");
    printInfo("Use --apply flag to actually migrate data\n");
} else {
    printInfo("APPLY MODE - Data will be migrated!");
    printInfo("Press Ctrl+C within 5 seconds to cancel...\n");
    sleep(5);
}

try {
    // Connect to WordPress database
    $wpDb = new PDO(
        "mysql:host={$wpDbConfig['host']};dbname={$wpDbConfig['dbname']};charset=utf8mb4",
        $wpDbConfig['username'],
        $wpDbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    printSuccess("Connected to WordPress database: {$wpDbConfig['dbname']}");
    printSuccess("Connected to target database: s3vgroup\n");
    
    // Paths
    $wpUploadsPath = 'C:/xampp/imports/wp-content/uploads';
    $targetUploadsPath = __DIR__ . '/../uploads/site';
    
    if (!is_dir($targetUploadsPath)) {
        mkdir($targetUploadsPath, 0755, true);
    }
    
    // ============================================
    // STEP 1: Migrate Categories
    // ============================================
    printHeader("Step 1: Migrating Categories");
    
    // Get WooCommerce product categories
    $wpCategories = $wpDb->query("
        SELECT 
            t.term_id,
            t.name,
            t.slug,
            tt.description,
            tt.parent
        FROM wpg1_terms t
        INNER JOIN wpg1_term_taxonomy tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy = 'product_cat'
        ORDER BY tt.parent, t.name
    ")->fetchAll();
    
    $categoryMap = []; // Maps WP term_id to our category ID
    $importedCategories = 0;
    
    foreach ($wpCategories as $wpCat) {
        $categoryId = Id::prefixed('cat');
        $categoryMap[$wpCat['term_id']] = $categoryId;
        
        // Get category image from WooCommerce
        $categoryImage = null;
        $imageMetaStmt = $wpDb->prepare("
            SELECT meta_value 
            FROM wpg1_termmeta 
            WHERE term_id = ? 
            AND meta_key = 'thumbnail_id'
            LIMIT 1
        ");
        $imageMetaStmt->execute([$wpCat['term_id']]);
        $imageMeta = $imageMetaStmt->fetchColumn();
        
        if ($imageMeta) {
            $imagePostStmt = $wpDb->prepare("
                SELECT guid 
                FROM wpg1_posts 
                WHERE ID = ? 
                AND post_type = 'attachment'
                LIMIT 1
            ");
            $imagePostStmt->execute([$imageMeta]);
            $imagePost = $imagePostStmt->fetchColumn();
            
            if ($imagePost) {
                $categoryImage = $imagePost;
            }
        }
        
        if ($dryRun) {
            printInfo("Would create category: {$wpCat['name']} (slug: {$wpCat['slug']})");
        } else {
            // Check if category already exists
            $existing = $targetDb->prepare("SELECT id FROM categories WHERE slug = ?");
            $existing->execute([$wpCat['slug']]);
            $existingId = $existing->fetchColumn();
            
            if (!$existingId) {
                $stmt = $targetDb->prepare("
                    INSERT INTO categories (id, name, slug, description, icon, priority, createdAt)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $categoryId,
                    $wpCat['name'],
                    $wpCat['slug'],
                    $wpCat['description'] ?: null,
                    $categoryImage ?: null,
                    0
                ]);
                
                $importedCategories++;
                printSuccess("Created category: {$wpCat['name']}");
            } else {
                $categoryMap[$wpCat['term_id']] = $existingId;
                printInfo("Category already exists: {$wpCat['name']}");
            }
        }
    }
    
    // Create default category if none exist
    if (empty($categoryMap) && !$dryRun) {
        $defaultId = Id::prefixed('cat');
        $stmt = $targetDb->prepare("
            INSERT INTO categories (id, name, slug, description, priority, createdAt)
            VALUES (?, 'Uncategorized', 'uncategorized', 'Default category', 0, NOW())
        ");
        $stmt->execute([$defaultId]);
        $categoryMap[0] = $defaultId; // Use 0 as fallback
        printInfo("Created default category: Uncategorized");
    }
    
    printInfo("Total categories: " . count($wpCategories) . " (imported: $importedCategories)\n");
    
    // ============================================
    // STEP 2: Migrate Products
    // ============================================
    printHeader("Step 2: Migrating Products");
    
    // Get all published products
    $wpProducts = $wpDb->query("
        SELECT 
            p.ID,
            p.post_title,
            p.post_name,
            p.post_content,
            p.post_excerpt,
            p.post_date,
            p.post_status
        FROM wpg1_posts p
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        ORDER BY p.post_date DESC
    ")->fetchAll();
    
    $importedProducts = 0;
    $skippedProducts = 0;
    
    foreach ($wpProducts as $wpProduct) {
        $productId = Id::prefixed('prod');
        
        // Get product category
        $productCategoryId = null;
        $productTermsStmt = $wpDb->prepare("
            SELECT tt.term_id, tt.taxonomy
            FROM wpg1_term_relationships tr
            INNER JOIN wpg1_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tr.object_id = ?
            AND tt.taxonomy = 'product_cat'
            LIMIT 1
        ");
        $productTermsStmt->execute([$wpProduct['ID']]);
        $productTerm = $productTermsStmt->fetch();
        
        if ($productTerm && isset($categoryMap[$productTerm['term_id']])) {
            $productCategoryId = $categoryMap[$productTerm['term_id']];
        }
        
        // Use default category if none found
        if (!$productCategoryId && !empty($categoryMap)) {
            $productCategoryId = reset($categoryMap);
        } elseif (!$productCategoryId) {
            $skippedProducts++;
            printError("Skipping product '{$wpProduct['post_title']}' - no category found");
            continue;
        }
        
        // Get WooCommerce product data
        $skuStmt = $wpDb->prepare("
            SELECT meta_value 
            FROM wpg1_postmeta 
            WHERE post_id = ? 
            AND meta_key = '_sku'
            LIMIT 1
        ");
        $skuStmt->execute([$wpProduct['ID']]);
        $sku = $skuStmt->fetchColumn() ?: null;
        
        $priceStmt = $wpDb->prepare("
            SELECT meta_value 
            FROM wpg1_postmeta 
            WHERE post_id = ? 
            AND meta_key = '_price'
            LIMIT 1
        ");
        $priceStmt->execute([$wpProduct['ID']]);
        $price = $priceStmt->fetchColumn();
        $price = $price ? (float)$price : null;
        
        // Get product image
        $featuredImageStmt = $wpDb->prepare("
            SELECT meta_value 
            FROM wpg1_postmeta 
            WHERE post_id = ? 
            AND meta_key = '_thumbnail_id'
            LIMIT 1
        ");
        $featuredImageStmt->execute([$wpProduct['ID']]);
        $featuredImageId = $featuredImageStmt->fetchColumn();
        
        $heroImage = null;
        if ($featuredImageId) {
            $imagePostStmt = $wpDb->prepare("
                SELECT guid, post_mime_type 
                FROM wpg1_posts 
                WHERE ID = ? 
                AND post_type = 'attachment'
                LIMIT 1
            ");
            $imagePostStmt->execute([$featuredImageId]);
            $imagePost = $imagePostStmt->fetch();
            
            if ($imagePost) {
                $heroImage = copyAndOptimizeImage($imagePost['guid'], $wpUploadsPath, $targetUploadsPath, $imagePost['post_mime_type'], $dryRun);
            }
        }
        
        // Get product gallery images
        $galleryStmt = $wpDb->prepare("
            SELECT meta_value 
            FROM wpg1_postmeta 
            WHERE post_id = ? 
            AND meta_key = '_product_image_gallery'
            LIMIT 1
        ");
        $galleryStmt->execute([$wpProduct['ID']]);
        $galleryIds = $galleryStmt->fetchColumn();
        
        $galleryImages = [];
        if ($galleryIds) {
            $ids = explode(',', $galleryIds);
            foreach ($ids as $imgId) {
                $imgId = trim($imgId);
                if ($imgId) {
                    $imgPostStmt = $wpDb->prepare("
                        SELECT guid, post_mime_type 
                        FROM wpg1_posts 
                        WHERE ID = ? 
                        AND post_type = 'attachment'
                        LIMIT 1
                    ");
                    $imgPostStmt->execute([$imgId]);
                    $imgPost = $imgPostStmt->fetch();
                    
                    if ($imgPost) {
                        $galleryUrl = copyAndOptimizeImage($imgPost['guid'], $wpUploadsPath, $targetUploadsPath, $imgPost['post_mime_type'], $dryRun);
                        if ($galleryUrl) {
                            $galleryImages[] = $galleryUrl;
                        }
                    }
                }
            }
        }
        
        // Clean HTML content
        $description = strip_tags($wpProduct['post_content']);
        $summary = $wpProduct['post_excerpt'] ?: substr($description, 0, 200);
        
        // Get product attributes/specs
        $specs = [];
        $attributesStmt = $wpDb->prepare("
            SELECT meta_key, meta_value 
            FROM wpg1_postmeta 
            WHERE post_id = ? 
            AND meta_key LIKE '_product_attributes'
            LIMIT 1
        ");
        $attributesStmt->execute([$wpProduct['ID']]);
        $attributes = $attributesStmt->fetch();
        
        if ($attributes && $attributes['meta_value']) {
            $attrs = maybe_unserialize($attributes['meta_value']);
            if (is_array($attrs)) {
                foreach ($attrs as $key => $attr) {
                    if (isset($attr['value'])) {
                        $specs[$key] = $attr['value'];
                    }
                }
            }
        }
        
        if ($dryRun) {
            printInfo("Would create product: {$wpProduct['post_title']} (SKU: " . ($sku ?: 'N/A') . ")");
        } else {
            // Check if product already exists
            $existing = $targetDb->prepare("SELECT id FROM products WHERE slug = ?");
            $existing->execute([$wpProduct['post_name']]);
            $existingId = $existing->fetchColumn();
            
            if (!$existingId) {
                $stmt = $targetDb->prepare("
                    INSERT INTO products (
                        id, name, slug, sku, summary, description, specs, 
                        heroImage, price, status, categoryId, createdAt
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $status = $wpProduct['post_status'] === 'publish' ? 'PUBLISHED' : 'DRAFT';
                
                $stmt->execute([
                    $productId,
                    $wpProduct['post_title'],
                    $wpProduct['post_name'],
                    $sku,
                    $summary,
                    $description,
                    !empty($specs) ? json_encode($specs) : null,
                    $heroImage,
                    $price,
                    $status,
                    $productCategoryId,
                    $wpProduct['post_date']
                ]);
                
                // Add gallery images to product_media
                foreach ($galleryImages as $galleryUrl) {
                    $mediaId = Id::prefixed('media');
                    $mediaStmt = $targetDb->prepare("
                        INSERT INTO product_media (id, url, alt, featured, productId, createdAt)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $mediaStmt->execute([
                        $mediaId,
                        $galleryUrl,
                        $wpProduct['post_title'],
                        false,
                        $productId
                    ]);
                }
                
                $importedProducts++;
                printSuccess("Created product: {$wpProduct['post_title']}");
            } else {
                printInfo("Product already exists: {$wpProduct['post_title']}");
            }
        }
    }
    
    printInfo("Total products: " . count($wpProducts) . " (imported: $importedProducts, skipped: $skippedProducts)\n");
    
    // ============================================
    // Summary
    // ============================================
    printHeader("Migration Summary");
    
    if ($dryRun) {
        printInfo("DRY RUN COMPLETE - No data was migrated");
        printInfo("Run with --apply flag to actually migrate data");
    } else {
        printSuccess("Migration completed successfully!");
        printInfo("Categories imported: $importedCategories");
        printInfo("Products imported: $importedProducts");
        printInfo("Products skipped: $skippedProducts");
    }
    
} catch (PDOException $e) {
    printError("Database error: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

/**
 * Copy and optimize image from WordPress uploads to target directory
 */
function copyAndOptimizeImage(string $wpGuid, string $wpUploadsPath, string $targetUploadsPath, string $mimeType, bool $dryRun): ?string {
    // Extract relative path from WordPress GUID
    // GUID format: http://s3vtgroup.com.kh/wp-content/uploads/2024/09/image.jpg
    $urlParts = parse_url($wpGuid);
    $wpPath = $urlParts['path'] ?? '';
    
    // Remove /wp-content/uploads from path
    $wpPath = str_replace('/wp-content/uploads/', '', $wpPath);
    $wpPath = str_replace('wp-content/uploads/', '', $wpPath);
    
    $sourceFile = $wpUploadsPath . '/' . $wpPath;
    
    if (!file_exists($sourceFile)) {
        // Try to find file by filename only
        $filename = basename($wpPath);
        $sourceFile = findFileInDirectory($wpUploadsPath, $filename);
        
        if (!$sourceFile) {
            printError("Image not found: $wpPath");
            return null;
        }
    }
    
    // Generate new filename
    $extension = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        default => pathinfo($sourceFile, PATHINFO_EXTENSION),
    };
    
    $newFilename = Id::prefixed('img') . '.' . $extension;
    $targetFile = $targetUploadsPath . '/' . $newFilename;
    
    if ($dryRun) {
        printInfo("Would copy image: " . basename($sourceFile) . " -> $newFilename");
        return '/uploads/site/' . $newFilename;
    }
    
    // Copy file
    if (!copy($sourceFile, $targetFile)) {
        printError("Failed to copy image: $sourceFile");
        return null;
    }
    
    // Optimize image (if supported)
    if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        try {
            ImageOptimizer::resize($targetFile, $mimeType, 1920, 1200, 82);
        } catch (Exception $e) {
            printError("Failed to optimize image: " . $e->getMessage());
        }
    }
    
    return '/uploads/site/' . $newFilename;
}

/**
 * Find file in directory recursively
 */
function findFileInDirectory(string $directory, string $filename): ?string {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }
    
    return null;
}

/**
 * WordPress maybe_unserialize equivalent
 */
function maybe_unserialize($data) {
    if (is_serialized($data)) {
        return unserialize($data);
    }
    return $data;
}

function is_serialized($data): bool {
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (!preg_match('/^([adObis]):/', $data, $badions)) {
        return false;
    }
    return true;
}

exit(0);

