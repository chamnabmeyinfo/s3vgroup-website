<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Domain\Settings\SiteOptionRepository;

$db = getDB();
$repo = new SiteOptionRepository($db);

echo "Current Site Colors:\n";
echo "Primary:   " . $repo->get('primary_color') . "\n";
echo "Secondary: " . $repo->get('secondary_color') . "\n";
echo "Accent:    " . $repo->get('accent_color') . "\n";

