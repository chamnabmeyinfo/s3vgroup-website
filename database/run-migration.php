<?php
/**
 * Run database migrations
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Database\MigrationRunner;

$db = Connection::getInstance();
$runner = new MigrationRunner($db, __DIR__ . '/migrations');

try {
    $runner->migrate();
    echo "✅ Migrations completed successfully!\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

