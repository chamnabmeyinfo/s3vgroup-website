<?php

declare(strict_types=1);

// Start output buffering to prevent any unwanted output (PHP errors, warnings, etc.)
ob_start();

// Suppress error display but log errors
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Support\Id;
use App\Support\ImageOptimizer;

try {
    AdminGuard::requireAuth();
} catch (\Throwable $e) {
    ob_end_clean();
    JsonResponse::error('Authentication required.', 401);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    JsonResponse::error('Method not allowed.', 405);
}

try {
// Create uploads directory if it doesn't exist (use ae-content/uploads for Ant Elite)
$uploadDir = base_path('ae-content/uploads');
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
    ob_end_clean();
    JsonResponse::error($errorMessage, 400);
}

$file = $_FILES['file'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo === false) {
    ob_end_clean();
    JsonResponse::error('Server error: Unable to validate file type.', 500);
}
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes, true)) {
    ob_end_clean();
    JsonResponse::error('Invalid file type. Only images (JPEG, PNG, GIF, WebP, SVG) are allowed.', 400);
}

// Validate file size (max 50MB) - we will optimize after upload
$maxSize = 50 * 1024 * 1024; // 50MB
if ($file['size'] > $maxSize) {
    ob_end_clean();
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
    ob_end_clean();
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
        
    } catch (\Throwable $e) {
        // If optimization fails, log but don't fail the upload
        error_log("Image optimization failed: " . $e->getMessage());
        $finalSize = $file['size'];
        $finalDimensions = $originalDimensions ?? null;
        $optimized = false;
        $sizeReduction = 0;
    }
    
    // Update file size after optimization
    $file['size'] = $finalSize;
    
    // Update relative path if filename changed (e.g., WebP conversion)
$relativePath = '/ae-content/uploads/site/' . $filename;
} else {
    // SVG/GIF - no optimization needed
    $optimized = false;
    $sizeReduction = 0;
    $finalSize = $file['size'];
    $finalDimensions = null;
}

// Generate full URL with domain (works on both localhost and live)
$siteConfig = [];
try {
    require_once __DIR__ . '/../../config/site.php';
} catch (\Throwable $e) {
    // If config file fails, log but continue with auto-detection
    error_log("Site config load failed: " . $e->getMessage());
}

// Auto-detect protocol and host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Get site URL from config or auto-detect
$siteUrl = (isset($siteConfig) && is_array($siteConfig) && isset($siteConfig['url'])) 
    ? $siteConfig['url'] 
    : ($protocol . '://' . $host);

// Remove trailing slash from site URL
$siteUrl = rtrim($siteUrl, '/');

// Ensure relative path is set (in case optimization didn't run)
if (!isset($relativePath)) {
$relativePath = '/ae-content/uploads/site/' . $filename;
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

// Clear any output buffer before sending JSON response
ob_end_clean();
JsonResponse::success($responseData);

} catch (\Throwable $e) {
    // Log the error for debugging with full stack trace
    $errorDetails = sprintf(
        'Upload error: %s in %s:%d. Stack trace: %s',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    error_log($errorDetails);
    
    // Clean output buffer and return JSON error
    ob_end_clean();
    
    // Return detailed error message to help debug (can be made generic in production)
    $errorMessage = 'Upload error: ' . $e->getMessage();
    if (defined('APP_DEBUG') && APP_DEBUG) {
        $errorMessage .= ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
    }
    
    JsonResponse::error($errorMessage, 500);
}

