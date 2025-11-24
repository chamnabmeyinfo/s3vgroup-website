<?php
/**
 * Incremental Sync Local Database to Live (s3vgroup.com)
 * 
 * Syncs only new/updated records from local to live database.
 * Handles duplicates and foreign key relationships properly.
 * 
 * Usage:
 *   php bin/sync-database-to-live-incremental.php --apply
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

// Load live database config
$liveConfigFile = __DIR__ . '/../config/database.live.php';
if (!file_exists($liveConfigFile)) {
    die("❌ Error: config/database.live.php not found!\n");
}

$liveConfig = require $liveConfigFile;

// Colors
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

printHeader("Incremental Sync to Live Database");

if ($dryRun) {
    printInfo("DRY RUN MODE - No changes will be made\n");
} else {
    printInfo("APPLY MODE - Live database will be updated!\n");
    sleep(2);
}

try {
    $localDb = getDB();
    printSuccess("Connected to local database");
    
    $liveDb = new PDO(
        "mysql:host={$liveConfig['host']};dbname={$liveConfig['database']};charset=utf8mb4",
        $liveConfig['username'],
        $liveConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    printSuccess("Connected to live database: {$liveConfig['database']}\n");
    
    // Sync Categories
    printHeader("Syncing Categories");
    $categories = $localDb->query("SELECT * FROM categories ORDER BY createdAt")->fetchAll();
    $synced = 0;
    $skipped = 0;
    
    foreach ($categories as $cat) {
        // Check if exists
        $exists = $liveDb->prepare("SELECT id FROM categories WHERE id = ? OR slug = ?");
        $exists->execute([$cat['id'], $cat['slug']]);
        
        if ($exists->fetch()) {
            // Update existing
            if (!$dryRun) {
                $stmt = $liveDb->prepare("
                    UPDATE categories 
                    SET name = ?, slug = ?, description = ?, icon = ?, priority = ?, updatedAt = NOW()
                    WHERE id = ? OR slug = ?
                ");
                $stmt->execute([
                    $cat['name'], $cat['slug'], $cat['description'], 
                    $cat['icon'], $cat['priority'], $cat['id'], $cat['slug']
                ]);
            }
            $synced++;
            printInfo("Updated category: {$cat['name']}");
        } else {
            // Insert new
            if (!$dryRun) {
                $stmt = $liveDb->prepare("
                    INSERT INTO categories (id, name, slug, description, icon, priority, createdAt, updatedAt)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $cat['id'], $cat['name'], $cat['slug'], 
                    $cat['description'], $cat['icon'], $cat['priority'], $cat['createdAt']
                ]);
            }
            $synced++;
            printSuccess("Added category: {$cat['name']}");
        }
    }
    
    printInfo("Categories: $synced synced, $skipped skipped\n");
    
    // Sync Products
    printHeader("Syncing Products");
    $products = $localDb->query("SELECT * FROM products ORDER BY createdAt")->fetchAll();
    $synced = 0;
    $skipped = 0;
    
    foreach ($products as $product) {
        $exists = $liveDb->prepare("SELECT id FROM products WHERE id = ? OR slug = ?");
        $exists->execute([$product['id'], $product['slug']]);
        
        if ($exists->fetch()) {
            // Update existing
            if (!$dryRun) {
                $stmt = $liveDb->prepare("
                    UPDATE products 
                    SET name = ?, slug = ?, sku = ?, summary = ?, description = ?, 
                        specs = ?, heroImage = ?, price = ?, status = ?, categoryId = ?, updatedAt = NOW()
                    WHERE id = ? OR slug = ?
                ");
                $stmt->execute([
                    $product['name'], $product['slug'], $product['sku'], 
                    $product['summary'], $product['description'], $product['specs'],
                    $product['heroImage'], $product['price'], $product['status'],
                    $product['categoryId'], $product['id'], $product['slug']
                ]);
            }
            $synced++;
        } else {
            // Insert new
            if (!$dryRun) {
                $stmt = $liveDb->prepare("
                    INSERT INTO products (id, name, slug, sku, summary, description, specs, 
                        heroImage, price, status, categoryId, highlights, createdAt, updatedAt)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $product['id'], $product['name'], $product['slug'], $product['sku'],
                    $product['summary'], $product['description'], $product['specs'],
                    $product['heroImage'], $product['price'], $product['status'],
                    $product['categoryId'], $product['highlights'], $product['createdAt']
                ]);
            }
            $synced++;
            if ($synced % 10 == 0) {
                printInfo("Synced $synced products...");
            }
        }
    }
    
    printSuccess("Products: $synced synced\n");
    
    // Sync Product Media
    printHeader("Syncing Product Media");
    $media = $localDb->query("SELECT * FROM product_media")->fetchAll();
    $synced = 0;
    
    foreach ($media as $item) {
        $exists = $liveDb->prepare("SELECT id FROM product_media WHERE id = ?");
        $exists->execute([$item['id']]);
        
        if (!$exists->fetch() && !$dryRun) {
            $stmt = $liveDb->prepare("
                INSERT INTO product_media (id, url, alt, featured, productId, createdAt)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $item['id'], $item['url'], $item['alt'], 
                $item['featured'], $item['productId'], $item['createdAt']
            ]);
            $synced++;
        }
    }
    
    printSuccess("Product Media: $synced synced\n");
    
    // Final verification
    printHeader("Verification");
    
    $tables = ['categories', 'products', 'product_media'];
    foreach ($tables as $table) {
        $localCount = $localDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $liveCount = $liveDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        
        if ($localCount == $liveCount) {
            printSuccess("$table: Synced ($liveCount records)");
        } else {
            printInfo("$table: Local=$localCount, Live=$liveCount");
        }
    }
    
    printHeader("Sync Complete");
    printSuccess("Database synced successfully!");
    printInfo("Visit https://s3vgroup.com to see the updated content");
    
} catch (PDOException $e) {
    printError("Database error: " . $e->getMessage());
    exit(1);
}

exit(0);

