<?php
// Load necessary files
define('AEPATH', __DIR__ . '/../');
require_once AEPATH . 'ae-includes/functions.php';

// Mock site config
$siteConfig = ['url' => 'http://localhost:8080'];

// Test cases
$tests = [
    'uploads/site/img.jpg' => 'http://localhost:8080/ae-content/uploads/site/img.jpg',
    '/uploads/site/img.jpg' => 'http://localhost:8080/ae-content/uploads/site/img.jpg',
    'wp-content/uploads/site/img.jpg' => 'http://localhost:8080/ae-content/uploads/site/img.jpg',
    'ae-content/uploads/site/img.jpg' => 'http://localhost:8080/ae-content/uploads/site/img.jpg',
    '/ae-content/uploads/site/img.jpg' => 'http://localhost:8080/ae-content/uploads/site/img.jpg',
    'https://external.com/image.jpg' => 'https://external.com/image.jpg',
];

echo "Testing fullImageUrl function...\n\n";

$failed = 0;
foreach ($tests as $input => $expected) {
    $result = fullImageUrl($input);
    if ($result === $expected) {
        echo "[PASS] '$input' -> '$result'\n";
    } else {
        echo "[FAIL] '$input'\n";
        echo "  Expected: '$expected'\n";
        echo "  Got:      '$result'\n";
        $failed++;
    }
}

if ($failed === 0) {
    echo "\nAll tests passed!\n";
    exit(0);
} else {
    echo "\n$failed tests failed.\n";
    exit(1);
}
