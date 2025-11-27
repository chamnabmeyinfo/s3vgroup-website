<?php
/**
 * Update Languages to 3: English, Khmer, Chinese
 * This script removes Thai and ensures only 3 languages are active
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

$pdo = Connection::getInstance();

try {
    // Deactivate Thai if it exists
    $pdo->exec("UPDATE languages SET is_active = FALSE WHERE code = 'th'");
    
    // Ensure only 3 languages are active
    $pdo->exec(<<<'SQL'
UPDATE languages SET is_active = TRUE, sort_order = 1 WHERE code = 'en';
UPDATE languages SET is_active = TRUE, sort_order = 2 WHERE code = 'km';
UPDATE languages SET is_active = TRUE, sort_order = 3 WHERE code = 'zh';
SQL
    );

    // Ensure English is default
    $pdo->exec("UPDATE languages SET is_default = FALSE");
    $pdo->exec("UPDATE languages SET is_default = TRUE WHERE code = 'en'");

    echo "✅ Languages updated successfully!\n";
    echo "✅ Active languages: English, Khmer, Chinese\n";
    echo "✅ Thai has been deactivated (if it existed)\n";
} catch (\PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

