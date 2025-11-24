<?php
/**
 * Fix localhost URLs in Live Database
 * 
 * Replaces any localhost URLs with https://s3vgroup.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

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

echo "🔍 Fixing localhost URLs in live database...\n\n";

$tables = [
    'products' => ['heroImage'],
    'categories' => ['icon'],
    'team_members' => ['photo'],
    'testimonials' => ['avatar'],
    'sliders' => ['image_url'],
    'ceo_message' => ['photo', 'signature'],
    'company_story' => ['heroImage'],
    'product_media' => ['url'],
];

$totalFixed = 0;

foreach ($tables as $table => $columns) {
    try {
        $exists = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if (!$exists) continue;
        
        foreach ($columns as $column) {
            $columnExists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
            if (!$columnExists) continue;
            
            $rows = $db->query("
                SELECT id, `$column` 
                FROM `$table` 
                WHERE `$column` LIKE '%localhost%'
            ")->fetchAll();
            
            if (empty($rows)) continue;
            
            echo "Fixing $table.$column: " . count($rows) . " row(s)\n";
            
            foreach ($rows as $row) {
                $oldUrl = $row[$column];
                $newUrl = str_replace('http://localhost:8080', 'https://s3vgroup.com', $oldUrl);
                $newUrl = str_replace('http://localhost', 'https://s3vgroup.com', $newUrl);
                
                $stmt = $db->prepare("UPDATE `$table` SET `$column` = ? WHERE id = ?");
                $stmt->execute([$newUrl, $row['id']]);
                $totalFixed++;
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Fix site_options
try {
    $rows = $db->query("
        SELECT key_name, value 
        FROM site_options 
        WHERE value LIKE '%localhost%'
    ")->fetchAll();
    
    if (!empty($rows)) {
        echo "Fixing site_options: " . count($rows) . " row(s)\n";
        
        foreach ($rows as $row) {
            $oldUrl = $row['value'];
            $newUrl = str_replace('http://localhost:8080', 'https://s3vgroup.com', $oldUrl);
            $newUrl = str_replace('http://localhost', 'https://s3vgroup.com', $newUrl);
            
            $stmt = $db->prepare("UPDATE site_options SET value = ? WHERE key_name = ?");
            $stmt->execute([$newUrl, $row['key_name']]);
            $totalFixed++;
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Fixed $totalFixed URL(s)\n";

