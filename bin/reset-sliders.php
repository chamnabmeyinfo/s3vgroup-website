<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\SliderRepository;

$db = getDB();
$sliderRepo = new SliderRepository($db);

echo "🔄 Resetting sliders...\n\n";

// Delete all existing sliders
try {
    $statement = $db->query('DELETE FROM sliders');
    $deleted = $statement->rowCount();
    echo "  ✅ Deleted {$deleted} existing slider(s)\n";
} catch (Exception $e) {
    echo "  ⚠️  Error deleting sliders: " . $e->getMessage() . "\n";
}

echo "\n✅ Reset complete. Now run bin/seed-sample-data.php to create new slides.\n";

