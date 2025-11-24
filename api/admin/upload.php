<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Support\Id;
use App\Support\ImageOptimizer;

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

// Validate file size (max 50MB) - we will downsize after upload
$maxSize = 50 * 1024 * 1024; // 50MB
if ($file['size'] > $maxSize) {
    JsonResponse::error('File size exceeds maximum allowed size of 50MB.', 400);
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

// Aggressively optimize images: resize, compress, and ensure fast loading
// Target: Under 1MB file size, max 1200x1200 for products (good for web display)
if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    // Use smart optimization: maintain aspect ratio, target 1MB max file size
    // For product images, 1200x1200 is plenty for web display and keeps files small
    ImageOptimizer::resize(
        $destination, 
        $mimeType, 
        1200,  // Max width (good for product images)
        1200,  // Max height
        false, // Don't crop - maintain aspect ratio
        1024 * 1024 // Target: 1MB maximum file size
    );
    
    // Check if converted to WebP (ImageOptimizer may convert for better compression)
    $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $destination);
    if (file_exists($webpPath) && $webpPath !== $destination) {
        // WebP version exists and is different - check if it's better
        $originalSize = file_exists($destination) ? filesize($destination) : 0;
        $webpSize = filesize($webpPath);
        
        if ($webpSize < $originalSize * 0.9) {
            // WebP is significantly smaller, use it
            @unlink($destination); // Remove original
            $filename = basename($webpPath);
            $extension = 'webp';
            $mimeType = 'image/webp';
            $destination = $webpPath;
        } else {
            // Original is better, remove WebP
            @unlink($webpPath);
        }
    }
    
    // Update file size after optimization
    $file['size'] = file_exists($destination) ? filesize($destination) : $file['size'];
    
    // Update relative path if filename changed (e.g., WebP conversion)
    $relativePath = '/uploads/site/' . $filename;
}

// Generate full URL with domain (works on both localhost and live)
require_once __DIR__ . '/../../config/site.php';

// Auto-detect protocol and host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Get site URL from config or auto-detect
$siteUrl = $siteConfig['url'] ?? ($protocol . '://' . $host);

// Remove trailing slash from site URL
$siteUrl = rtrim($siteUrl, '/');

// Ensure relative path is set (in case optimization didn't run)
if (!isset($relativePath)) {
    $relativePath = '/uploads/site/' . $filename;
}

// Check if we need to add base path (for localhost subdirectories)
// For live server, siteUrl already includes the full domain
// For localhost, we might need to add subdirectory path
if (strpos($siteUrl, 'localhost') !== false || strpos($siteUrl, '127.0.0.1') !== false) {
    // For localhost, check if we're in a subdirectory
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = rtrim($scriptDir, '/');
    
    if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '' && $scriptDir !== '\\') {
        $relativePath = $scriptDir . $relativePath;
    }
}

// Build full URL
$url = $siteUrl . $relativePath;

JsonResponse::success([
    'url' => $url,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $mimeType,
]);

