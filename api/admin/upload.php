<?php

declare(strict_types=1);

// Start output buffering to prevent any unwanted output (PHP errors, warnings, etc.)
ob_start();

// Suppress error display but log errors
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Load bootstrap to ensure base_path() function is available
if (file_exists(__DIR__ . '/../../bootstrap/app.php')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/../../ae-load.php')) {
    require_once __DIR__ . '/../../ae-load.php';
}

require_once __DIR__ . '/../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Support\Id;
use App\Support\ImageOptimizer;

// Ensure base_path function exists
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        return rtrim($base . ($path !== '' ? '/' . ltrim($path, '/\\') : ''), '/\\');
    }
}

if (!function_exists('ae_store_uploaded_file')) {
    /**
     * Persist an uploaded file using a secure stream copy fallback.
     *
     * @throws \RuntimeException when the upload cannot be saved.
     */
    function ae_store_uploaded_file(array $file, string $destination): void
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload payload.');
        }

        $input = fopen($file['tmp_name'], 'rb');
        if ($input === false) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        $output = fopen($destination, 'wb');
        if ($output === false) {
            fclose($input);
            throw new \RuntimeException('Unable to write uploaded file.');
        }

        $bytes = stream_copy_to_stream($input, $output);
        fclose($input);
        fclose($output);

        // Ensure PHP flushes buffers and fresh stats are used.
        clearstatcache(true, $destination);

        if ($bytes === false || $bytes === 0 || !file_exists($destination) || filesize($destination) === 0) {
            @unlink($destination);
            throw new \RuntimeException('Uploaded file could not be written to disk.');
        }

        // Remove the temporary upload to free disk space.
        @unlink($file['tmp_name']);
    }
}

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
// Determine upload type (default: 'site', can be 'product')
$requestedType = $_POST['type'] ?? $_GET['type'] ?? 'site';
// Sanitize upload type to prevent directory traversal
$requestedType = preg_replace('/[^a-z0-9_-]/', '', strtolower($requestedType));

// Map requested types to actual directory names
$uploadTypeMap = [
    'site' => 'site',
    'product' => 'products',
    'products' => 'products', // Allow both singular/plural inputs
];

if (empty($requestedType) || !isset($uploadTypeMap[$requestedType])) {
    $requestedType = 'site'; // Default to 'site' if invalid
}

$uploadSubdir = $uploadTypeMap[$requestedType];

// Create uploads directory if it doesn't exist (use ae-content/uploads for Ant Elite)
$uploadDir = base_path('ae-content/uploads');
if (!is_dir($uploadDir)) {
    if (!@mkdir($uploadDir, 0755, true)) {
        error_log("Failed to create upload directory: " . $uploadDir);
        ob_end_clean();
        JsonResponse::error('Failed to create upload directory. Please check server permissions.', 500);
    }
}

// Use the appropriate subdirectory based on type
$uploadDir .= '/' . $uploadSubdir;
if (!is_dir($uploadDir)) {
    if (!@mkdir($uploadDir, 0755, true)) {
        error_log("Failed to create upload subdirectory: " . $uploadDir);
        ob_end_clean();
        JsonResponse::error('Failed to create upload subdirectory. Please check server permissions.', 500);
    }
}

// Verify directory is writable
if (!is_writable($uploadDir)) {
    error_log("Upload directory is not writable: " . $uploadDir);
    ob_end_clean();
    JsonResponse::error('Upload directory is not writable. Please check server permissions.', 500);
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

// Persist uploaded file with a safe stream copy (handles random move failures on Windows)
try {
    ae_store_uploaded_file($file, $destination);
} catch (\Throwable $e) {
    error_log('Upload storage failure: ' . $e->getMessage());
    error_log('Destination: ' . $destination);
    error_log('Upload dir: ' . $uploadDir);
    ob_end_clean();
    JsonResponse::error('Failed to save uploaded file: ' . $e->getMessage(), 500);
}

// Verify file was saved before optimization
if (!file_exists($destination)) {
    error_log("CRITICAL: File does not exist immediately after save: " . $destination);
    ob_end_clean();
    JsonResponse::error('File was not saved correctly. Please check server permissions.', 500);
}

// AUTOMATIC IMAGE OPTIMIZATION - Always runs on upload
// This prevents users from uploading large images that slow down the website
// Only optimize if GD extension is available
if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true) && extension_loaded('gd')) {
    $originalSize = file_exists($destination) ? filesize($destination) : ($file['size'] ?? 0);
    $originalDimensions = @getimagesize($destination);
    
    // Store original destination in case optimization fails
    $originalDestination = $destination;
    $originalFilename = $filename;
    
    // Use smart optimization: maintain aspect ratio, target 300KB max file size
    // Accept all image sizes - only optimize if image is very large
    try {
        // Make a backup copy before optimization (in case optimization deletes the original)
        $backupPath = $destination . '.backup';
        if (!@copy($destination, $backupPath)) {
            error_log("Warning: Could not create backup before optimization");
        }
        
        ImageOptimizer::resize(
            $destination, 
            $mimeType, 
            10000,  // Max width (accepts all reasonable image sizes)
            10000,  // Max height (accepts all reasonable image sizes)
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
        
        // CRITICAL: Ensure file exists after optimization
        // If optimization deleted the file, restore from backup
        if (!file_exists($destination)) {
            error_log("WARNING: File missing after optimization, attempting restore from backup");
            if (file_exists($backupPath)) {
                if (@copy($backupPath, $destination)) {
                    error_log("SUCCESS: Restored file from backup");
                } else {
                    error_log("ERROR: Failed to restore from backup");
                    ob_end_clean();
                    JsonResponse::error('Image optimization failed and file could not be restored.', 500);
                }
            } else {
                error_log("ERROR: No backup available and file is missing");
                ob_end_clean();
                JsonResponse::error('Image optimization failed and file was lost.', 500);
            }
        }
        
        // Clean up backup
        if (file_exists($backupPath)) {
            @unlink($backupPath);
        }
        
        // Get final file size and dimensions after optimization
        $finalSize = file_exists($destination) ? filesize($destination) : $originalSize;
        $finalDimensions = @getimagesize($destination);
        
        // Calculate optimization stats
        $sizeReduction = $originalSize > 0 ? round((($originalSize - $finalSize) / $originalSize) * 100, 1) : 0;
        $optimized = $finalSize < $originalSize;
        
    } catch (\Throwable $e) {
        // If optimization fails, restore from backup if available
        error_log("Image optimization failed: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        if (!file_exists($destination)) {
            $backupPath = $originalDestination . '.backup';
            if (file_exists($backupPath)) {
                if (@copy($backupPath, $destination)) {
                    error_log("Restored file from backup after optimization error");
                }
                @unlink($backupPath);
            }
        }
        
        // Ensure we have a valid file
        if (!file_exists($destination)) {
            error_log("CRITICAL: No file exists after optimization error");
            ob_end_clean();
            JsonResponse::error('Image optimization failed: ' . $e->getMessage(), 500);
        }
        
        $finalSize = $file['size'];
        $finalDimensions = $originalDimensions ?? null;
        $optimized = false;
        $sizeReduction = 0;
    }
    
    
    // Update file size after optimization
    $file['size'] = $finalSize;
    
    // Update relative path if filename changed (e.g., WebP conversion)
    $relativePath = '/ae-content/uploads/' . $uploadSubdir . '/' . $filename;
} else {
    // SVG/GIF - no optimization needed
    $optimized = false;
    $sizeReduction = 0;
    $finalSize = $file['size'];
    $finalDimensions = null;
    // Ensure relative path is set for non-optimized images
    $relativePath = '/ae-content/uploads/' . $uploadSubdir . '/' . $filename;
}

// Verify file actually exists before proceeding
if (!file_exists($destination)) {
    error_log("CRITICAL: Uploaded file does not exist at: " . $destination);
    error_log("Upload directory: " . $uploadDir);
    error_log("Directory exists: " . (is_dir($uploadDir) ? 'yes' : 'no'));
    error_log("Directory writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
    error_log("Destination path: " . $destination);
    ob_end_clean();
    JsonResponse::error('File upload failed: file was not saved correctly. Please check server logs for details.', 500);
}

// Verify file is readable
if (!is_readable($destination)) {
    error_log("CRITICAL: Uploaded file is not readable at: " . $destination);
    ob_end_clean();
    JsonResponse::error('File upload failed: file permissions error.', 500);
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

// Ensure relative path is set (should always be set by now, but safety check)
if (!isset($relativePath) || empty($relativePath)) {
    $relativePath = '/ae-content/uploads/' . $uploadSubdir . '/' . $filename;
}

// Build full URL - ALWAYS use relativePath, never construct from filename
$url = $siteUrl . $relativePath;

// Prepare response with optimization info
$responseData = [
    'url' => $url,
    'relativePath' => $relativePath, // Add relative path for database storage
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

