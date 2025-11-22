<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Support\Id;

AdminGuard::requireAuth();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

// Create uploads directory if it doesn't exist
$uploadDir = base_path('uploads');
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadDir .= '/site';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    JsonResponse::error('No file uploaded or upload error occurred.', 400);
}

$file = $_FILES['file'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes, true)) {
    JsonResponse::error('Invalid file type. Only images (JPEG, PNG, GIF, WebP, SVG) are allowed.', 400);
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    JsonResponse::error('File size exceeds maximum allowed size of 5MB.', 400);
}

// Generate unique filename
$extension = match ($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg',
    default => pathinfo($file['name'], PATHINFO_EXTENSION),
};

$filename = Id::prefixed('img') . '.' . $extension;
$destination = $uploadDir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    JsonResponse::error('Failed to save uploaded file.', 500);
}

// Generate URL
$url = '/uploads/site/' . $filename;

JsonResponse::success([
    'url' => $url,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $mimeType,
]);

