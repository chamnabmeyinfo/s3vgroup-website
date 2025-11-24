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
    $errorMessage = match ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large. Maximum size is 50MB.',
        UPLOAD_ERR_PARTIAL => 'File upload was incomplete. Please try again.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error: temporary folder missing.',
        UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'File upload blocked by server extension.',
        default => 'Upload error occurred. Please try again.',
    };
    JsonResponse::error($errorMessage, 400);
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

// Validate file size (max 50MB) - we will optimize after upload
$maxSize = 50 * 1024 * 1024; // 50MB
if ($file['size'] > $maxSize) {
    JsonResponse::error('File is too large. Maximum size is 50MB. The image will be automatically optimized after upload.', 400);
}

// Warn about very large files (but still allow them, we'll optimize)
$largeFileThreshold = 10 * 1024 * 1024; // 10MB
$isLargeFile = $file['size'] > $largeFileThreshold;

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

// AUTOMATIC IMAGE OPTIMIZATION - Always runs on upload
// This prevents users from uploading large images that slow down the website
if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    $originalSize = $file['size'];
    $originalDimensions = @getimagesize($destination);
    
    // Use smart optimization: maintain aspect ratio, target 300KB max file size
    // For product images, 1200x1200 is plenty for web display and keeps files small
    try {
        ImageOptimizer::resize(
            $destination, 
            $mimeType, 
            1200,  // Max width (good for product images)
            1200,  // Max height
            false, // Don't crop - maintain aspect ratio
            300 * 1024 // Target: 300KB maximum file size (aggressive compression)
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
        
        // Get final file size and dimensions after optimization
        $finalSize = file_exists($destination) ? filesize($destination) : $originalSize;
        $finalDimensions = @getimagesize($destination);
        
        // Calculate optimization stats
        $sizeReduction = $originalSize > 0 ? round((($originalSize - $finalSize) / $originalSize) * 100, 1) : 0;
        $optimized = $finalSize < $originalSize;
        
    } catch (Exception $e) {
        // If optimization fails, log but don't fail the upload
        error_log("Image optimization failed: " . $e->getMessage());
        $finalSize = $file['size'];
        $finalDimensions = $originalDimensions;
        $optimized = false;
        $sizeReduction = 0;
    }
    
    // Update file size after optimization
    $file['size'] = $finalSize;
    
    // Update relative path if filename changed (e.g., WebP conversion)
    $relativePath = '/uploads/site/' . $filename;
} else {
    // SVG/GIF - no optimization needed
    $optimized = false;
    $sizeReduction = 0;
    $finalSize = $file['size'];
    $finalDimensions = null;
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

// Prepare response with optimization info
$responseData = [
    'url' => $url,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $mimeType,
];

// Add optimization details if image was optimized
if (isset($optimized) && $optimized) {
    $responseData['optimized'] = true;
    $responseData['originalSize'] = $originalSize ?? $file['size'];
    $responseData['sizeReduction'] = $sizeReduction ?? 0;
    if (isset($finalDimensions) && $finalDimensions) {
        $responseData['dimensions'] = [
            'width' => $finalDimensions[0],
            'height' => $finalDimensions[1],
        ];
    }
    $responseData['message'] = sprintf(
        'Image optimized: Reduced by %s%% (%.1f MB → %.1f MB)',
        $sizeReduction,
        ($originalSize ?? $file['size']) / 1024 / 1024,
        $file['size'] / 1024 / 1024
    );
} elseif (isset($isLargeFile) && $isLargeFile) {
    $responseData['message'] = 'Large image uploaded. Optimization recommended.';
}

JsonResponse::success($responseData);

