<?php
/**
 * Fix All Image URLs in Database
 * 
 * Converts relative image URLs to full absolute URLs for both local and live databases
 * 
 * Usage:
 *   php bin/fix-all-image-urls.php              # Fix local database
 *   php bin/fix-all-image-urls.php --live       # Fix live database
 *   php bin/fix-all-image-urls.php --dry-run    # Preview only
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';

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

if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$dryRun = in_array('--dry-run', $argv);
$live = in_array('--live', $argv);

printHeader("Fix All Image URLs in Database");

try {
    // Get site URL
    global $siteConfig;
    if ($live) {
        $siteUrl = 'https://s3vgroup.com';
    } else {
        $siteUrl = $siteConfig['url'] ?? 'http://localhost:8080';
    }
    
    if ($live) {
        // Load live database config
        $liveConfigFile = __DIR__ . '/../config/database.live.php';
        if (!file_exists($liveConfigFile)) {
            die("❌ Error: config/database.live.php not found!\n");
        }
        
        $liveConfig = require $liveConfigFile;
        $db = new PDO(
            "mysql:host={$liveConfig['host']};dbname={$liveConfig['database']};charset=utf8mb4",
            $liveConfig['username'],
            $liveConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        printInfo("Connected to LIVE database: {$liveConfig['database']}");
    } else {
        $db = getDB();
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
        printInfo("Connected to LOCAL database: $dbName");
    }
    
    printInfo("Site URL: $siteUrl");
    
    if ($dryRun) {
        printInfo("DRY RUN MODE - No changes will be made\n");
    }
    
    $totalFixed = 0;
    
    // Tables and columns to fix
    $tablesToFix = [
        'products' => ['heroImage'],
        'categories' => ['icon'],
        'team_members' => ['photo'],
        'testimonials' => ['avatar'],
        'sliders' => ['image_url'],
        'ceo_message' => ['photo', 'signature'],
        'company_story' => ['heroImage'],
        'product_media' => ['url'],
    ];
    
    foreach ($tablesToFix as $table => $columns) {
        try {
            // Check if table exists
            $exists = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if (!$exists) {
                printInfo("Table $table does not exist, skipping...");
                continue;
            }
            
            printHeader("Fixing: $table");
            
            foreach ($columns as $column) {
                // Check if column exists
                $columnExists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
                if (!$columnExists) {
                    printInfo("  Column $column does not exist, skipping...");
                    continue;
                }
                
                // Get all rows with relative URLs
                $rows = $db->query("
                    SELECT * FROM `$table` 
                    WHERE `$column` IS NOT NULL 
                    AND `$column` != '' 
                    AND `$column` NOT LIKE 'http%'
                    AND `$column` NOT LIKE 'https%'
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($rows)) {
                    printInfo("  Column $column: All URLs are already full URLs");
                    continue;
                }
                
                printInfo("  Column $column: Found " . count($rows) . " row(s) with relative URLs");
                
                $fixed = 0;
                foreach ($rows as $row) {
                    $oldUrl = $row[$column];
                    $newUrl = toFullUrl($oldUrl, $siteUrl);
                    
                    if ($oldUrl !== $newUrl) {
                        $id = $row['id'] ?? null;
                        if (!$id) {
                            // Try to find primary key
                            $pkQuery = $db->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
                            $pk = $pkQuery->fetch();
                            if ($pk) {
                                $id = $row[$pk['Column_name']] ?? null;
                            }
                        }
                        
                        if ($id) {
                            echo "    - ID $id: " . substr($oldUrl, 0, 60) . " → " . substr($newUrl, 0, 60) . "\n";
                            
                            if (!$dryRun) {
                                $stmt = $db->prepare("UPDATE `$table` SET `$column` = ? WHERE `id` = ?");
                                $stmt->execute([$newUrl, $id]);
                            }
                            
                            $fixed++;
                            $totalFixed++;
                        }
                    }
                }
                
                if ($fixed > 0) {
                    printSuccess("  Fixed $fixed URL(s) in column $column");
                }
            }
        } catch (PDOException $e) {
            printError("Error processing table $table: " . $e->getMessage());
        }
    }
    
    // Fix site_options
    try {
        $exists = $db->query("SHOW TABLES LIKE 'site_options'")->fetch();
        if ($exists) {
            printHeader("Fixing: site_options");
            
            $imageKeys = ['site_logo', 'site_favicon', 'hero_image', 'background_image', 'logo', 'icon', 'photo', 'avatar', 'footer_logo'];
            $placeholders = implode(',', array_fill(0, count($imageKeys), '?'));
            
            $rows = $db->prepare("
                SELECT * FROM site_options 
                WHERE key_name IN ($placeholders) 
                AND value IS NOT NULL 
                AND value != '' 
                AND value NOT LIKE 'http%'
                AND value NOT LIKE 'https%'
            ");
            $rows->execute($imageKeys);
            $options = $rows->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($options)) {
                printInfo("  Found " . count($options) . " option(s) with relative URLs");
                
                $fixed = 0;
                foreach ($options as $option) {
                    $oldUrl = $option['value'];
                    $newUrl = toFullUrl($oldUrl, $siteUrl);
                    
                    if ($oldUrl !== $newUrl) {
                        echo "    - {$option['key_name']}: " . substr($oldUrl, 0, 50) . " → " . substr($newUrl, 0, 50) . "\n";
                        
                        if (!$dryRun) {
                            $stmt = $db->prepare("UPDATE site_options SET value = ? WHERE key_name = ?");
                            $stmt->execute([$newUrl, $option['key_name']]);
                        }
                        
                        $fixed++;
                        $totalFixed++;
                    }
                }
                
                if ($fixed > 0) {
                    printSuccess("  Fixed $fixed option(s)");
                }
            } else {
                printInfo("  All image options already have full URLs");
            }
        }
    } catch (PDOException $e) {
        printError("Error processing site_options: " . $e->getMessage());
    }
    
    printHeader("Summary");
    
    if ($dryRun) {
        printInfo("DRY RUN: Would fix $totalFixed URL(s)");
        printInfo("Run without --dry-run to apply changes");
    } else {
        if ($totalFixed > 0) {
            printSuccess("Fixed $totalFixed URL(s) in database");
        } else {
            printInfo("No URLs needed fixing - all URLs are already full URLs");
        }
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit(0);

