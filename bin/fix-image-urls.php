<?php
/**
 * Fix Image URLs in Database
 * 
 * Converts relative image URLs to full URLs in the database
 * 
 * Usage:
 *   php bin/fix-image-urls.php
 *   php bin/fix-image-urls.php --dry-run (check only, don't update)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';

use App\Database\Connection;

// Colors for terminal output
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
}

function printSuccess(string $message): void {
    echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printWarning(string $message): void {
    echo Colors::YELLOW . "⚠️  $message" . Colors::RESET . "\n";
}

function printInfo(string $message): void {
    echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

/**
 * Convert relative URL to full URL
 */
function toFullUrl(string $url, string $siteUrl): string {
    // If already full URL, return as is
    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }
    
    $siteUrl = rtrim($siteUrl, '/');
    $url = '/' . ltrim($url, '/');
    
    return $siteUrl . $url;
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$dryRun = in_array('--dry-run', $argv);

printHeader("Fix Image URLs in Database");

try {
    $db = getDB();
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    
    printInfo("Database: $dbName");
    
    // Get site URL
    global $siteConfig;
    $siteUrl = $siteConfig['url'] ?? '';
    
    if (empty($siteUrl)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $siteUrl = $protocol . '://' . $host;
    }
    
    printInfo("Site URL: $siteUrl");
    
    if ($dryRun) {
        printWarning("DRY RUN MODE - No changes will be made");
    }
    
    $totalFixed = 0;
    $tablesToFix = [
        'products' => ['heroImage'],
        'categories' => ['icon'],
        'team_members' => ['photo'],
        'testimonials' => ['avatar'],
        'sliders' => ['image_url'],
        'ceo_messages' => ['photo', 'signature'],
        'company_stories' => ['heroImage'],
        'pages' => ['heroImage'],
        'site_options' => ['value'], // Only if key contains 'image', 'logo', 'icon', 'photo'
    ];
    
    foreach ($tablesToFix as $table => $columns) {
        try {
            // Check if table exists
            $exists = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if (!$exists) {
                printInfo("Table $table does not exist, skipping...");
                continue;
            }
            
            printInfo("Checking table: $table");
            
            foreach ($columns as $column) {
                // Check if column exists
                $columnExists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
                if (!$columnExists) {
                    continue;
                }
                
                // Get all rows with relative URLs
                $rows = $db->query("SELECT * FROM `$table` WHERE `$column` IS NOT NULL AND `$column` != '' AND `$column` NOT LIKE 'http%'")->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($rows)) {
                    printInfo("  Column $column: No relative URLs found");
                    continue;
                }
                
                printInfo("  Column $column: Found " . count($rows) . " row(s) with relative URLs");
                
                foreach ($rows as $row) {
                    $oldUrl = $row[$column];
                    $newUrl = toFullUrl($oldUrl, $siteUrl);
                    
                    if ($oldUrl !== $newUrl) {
                        echo "    - " . substr($oldUrl, 0, 50) . " → " . substr($newUrl, 0, 50) . "\n";
                        
                        if (!$dryRun) {
                            // Get primary key
                            $primaryKey = 'id'; // Most tables use 'id'
                            $id = $row[$primaryKey];
                            
                            $stmt = $db->prepare("UPDATE `$table` SET `$column` = :url WHERE `$primaryKey` = :id");
                            $stmt->execute([':url' => $newUrl, ':id' => $id]);
                        }
                        
                        $totalFixed++;
                    }
                }
            }
        } catch (PDOException $e) {
            printWarning("Error processing table $table: " . $e->getMessage());
        }
    }
    
    // Special handling for site_options (check value field for image-related keys)
    try {
        $exists = $db->query("SHOW TABLES LIKE 'site_options'")->fetch();
        if ($exists) {
            printInfo("Checking site_options for image URLs...");
            
            $imageKeys = ['site_logo', 'site_favicon', 'hero_image', 'background_image', 'logo', 'icon', 'photo', 'avatar'];
            $placeholders = implode(',', array_fill(0, count($imageKeys), '?'));
            
            $rows = $db->prepare("SELECT * FROM site_options WHERE key_name IN ($placeholders) AND value IS NOT NULL AND value != '' AND value NOT LIKE 'http%'");
            $rows->execute($imageKeys);
            $options = $rows->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($options)) {
                printInfo("  Found " . count($options) . " option(s) with relative URLs");
                
                foreach ($options as $option) {
                    $oldUrl = $option['value'];
                    $newUrl = toFullUrl($oldUrl, $siteUrl);
                    
                    if ($oldUrl !== $newUrl) {
                        echo "    - " . $option['key_name'] . ": " . substr($oldUrl, 0, 50) . " → " . substr($newUrl, 0, 50) . "\n";
                        
                        if (!$dryRun) {
                            $stmt = $db->prepare("UPDATE site_options SET value = :url WHERE key_name = :key");
                            $stmt->execute([':url' => $newUrl, ':key' => $option['key_name']]);
                        }
                        
                        $totalFixed++;
                    }
                }
            }
        }
    } catch (PDOException $e) {
        printWarning("Error processing site_options: " . $e->getMessage());
    }
    
    printHeader("Summary");
    
    if ($dryRun) {
        printInfo("DRY RUN: Would fix $totalFixed URL(s)");
        printInfo("Run without --dry-run to apply changes");
    } else {
        if ($totalFixed > 0) {
            printSuccess("Fixed $totalFixed URL(s) in database");
        } else {
            printInfo("No URLs needed fixing");
        }
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit(0);

