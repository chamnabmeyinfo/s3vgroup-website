<?php

/**
 * Download SQL Export File
 */

session_start();
require_once __DIR__ . '/../../../config/database.php';

use App\Http\AdminGuard;

try {
    AdminGuard::requireAuth();
} catch (\Throwable $e) {
    http_response_code(401);
    die('Authentication required.');
}

$filename = $_GET['file'] ?? '';
if (empty($filename) || !preg_match('/^s3vgroup-export-\d{4}-\d{2}-\d{2}-\d{6}\.sql$/', $filename)) {
    http_response_code(400);
    die('Invalid filename.');
}

$filePath = sys_get_temp_dir() . '/' . $filename;

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found.');
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);

// Clean up after download
@unlink($filePath);

exit;

