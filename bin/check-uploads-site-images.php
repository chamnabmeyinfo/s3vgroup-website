<?php
/**
 * Check if images in uploads/site are accessible and loaded
 * 
 * This script verifies:
 * 1. Directory exists and has files
 * 2. Files are readable
 * 3. Images can be accessed via HTTP (if server is running)
 * 4. Sample images are valid image files
 */

require_once __DIR__ . '/../bootstrap/app.php';

echo "🔍 Checking uploads/site Image Accessibility...\n\n";

$uploadsDir = __DIR__ . '/../uploads/site';

// 1. Check if directory exists
if (!is_dir($uploadsDir)) {
    echo "❌ ERROR: Directory does not exist: {$uploadsDir}\n";
    exit(1);
}

echo "✅ Directory exists: {$uploadsDir}\n";

// 2. Check directory permissions
$perms = fileperms($uploadsDir);
$permsStr = substr(sprintf('%o', $perms), -4);
echo "   Permissions: {$permsStr}\n";

if (!is_readable($uploadsDir)) {
    echo "❌ ERROR: Directory is not readable!\n";
    exit(1);
}

echo "✅ Directory is readable\n\n";

// 3. Get list of image files
$imageFiles = [];
$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

foreach ($extensions as $ext) {
    $pattern = $uploadsDir . '/*.' . $ext;
    $files = glob($pattern);
    if ($files) {
        $imageFiles = array_merge($imageFiles, $files);
    }
    
    // Also check uppercase
    $pattern = $uploadsDir . '/*.' . strtoupper($ext);
    $files = glob($pattern);
    if ($files) {
        $imageFiles = array_merge($imageFiles, $files);
    }
}

$totalFiles = count($imageFiles);
echo "📊 Found {$totalFiles} image files\n\n";

if ($totalFiles === 0) {
    echo "⚠️  WARNING: No image files found in uploads/site/\n";
    exit(0);
}

// 4. Test sample files (first 10)
$sampleFiles = array_slice($imageFiles, 0, 10);
echo "🧪 Testing sample files (first 10):\n\n";

$accessibleCount = 0;
$inaccessibleCount = 0;
$invalidCount = 0;

foreach ($sampleFiles as $filePath) {
    $filename = basename($filePath);
    $relativePath = '/uploads/site/' . $filename;
    
    echo "📄 {$filename}\n";
    
    // Check file exists
    if (!file_exists($filePath)) {
        echo "   ❌ File does not exist\n\n";
        $inaccessibleCount++;
        continue;
    }
    
    // Check file is readable
    if (!is_readable($filePath)) {
        echo "   ❌ File is not readable\n";
        echo "   Permissions: " . substr(sprintf('%o', fileperms($filePath)), -4) . "\n\n";
        $inaccessibleCount++;
        continue;
    }
    
    // Check file size
    $fileSize = filesize($filePath);
    if ($fileSize === 0) {
        echo "   ⚠️  File is empty (0 bytes)\n\n";
        $invalidCount++;
        continue;
    }
    
    echo "   Size: " . number_format($fileSize) . " bytes (" . round($fileSize / 1024, 2) . " KB)\n";
    
    // Check if it's a valid image (for non-SVG)
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension !== 'svg') {
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            echo "   ⚠️  Not a valid image file (getimagesize failed)\n\n";
            $invalidCount++;
            continue;
        }
        
        echo "   Dimensions: {$imageInfo[0]}x{$imageInfo[1]}\n";
        echo "   MIME Type: {$imageInfo['mime']}\n";
    } else {
        // For SVG, just check if it contains SVG content
        $content = @file_get_contents($filePath, false, null, 0, 100);
        if (strpos($content, '<svg') === false && strpos($content, '<?xml') === false) {
            echo "   ⚠️  May not be a valid SVG file\n\n";
            $invalidCount++;
            continue;
        }
        echo "   Type: SVG\n";
    }
    
    // Test HTTP accessibility (if we can determine the base URL)
    $baseUrl = null;
    
    // Try to detect base URL from config
    if (file_exists(__DIR__ . '/../config/site.php')) {
        try {
            $siteConfig = require __DIR__ . '/../config/site.php';
            if (isset($siteConfig['url']) && !empty($siteConfig['url'])) {
                $baseUrl = rtrim($siteConfig['url'], '/');
            }
        } catch (Exception $e) {
            // Config might not be available, continue with auto-detect
        }
    }
    
    // Fallback: Try common localhost URLs
    if (!$baseUrl) {
        // For XAMPP, try common patterns
        $possibleUrls = [
            'http://localhost/s3vgroup',
            'http://localhost:8080/s3vgroup',
            'http://127.0.0.1/s3vgroup',
            'http://localhost',
        ];
        
        // Test which one works
        foreach ($possibleUrls as $testUrl) {
            if (function_exists('curl_init')) {
                $ch = curl_init($testUrl);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                @curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode > 0 && $httpCode < 500) {
                    $baseUrl = $testUrl;
                    break;
                }
            }
        }
        
        // If none worked, use first one as default
        if (!$baseUrl) {
            $baseUrl = $possibleUrls[0];
        }
    }
    
    $imageUrl = $baseUrl . $relativePath;
    echo "   URL: {$imageUrl}\n";
    
    // Test HTTP access (only if curl is available)
    if (function_exists('curl_init')) {
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        @curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            if (strpos($contentType, 'image/') === 0) {
                echo "   ✅ HTTP Accessible (returns image)\n";
                $accessibleCount++;
            } elseif (strpos($contentType, 'text/html') === 0) {
                echo "   ❌ HTTP returns HTML (404 page or redirect)\n";
                echo "      This means the image URL is not directly accessible\n";
                $inaccessibleCount++;
            } else {
                echo "   ⚠️  HTTP {$httpCode} - Content-Type: {$contentType}\n";
                $inaccessibleCount++;
            }
        } else {
            echo "   ❌ HTTP {$httpCode} - Not accessible\n";
            $inaccessibleCount++;
        }
    } else {
        echo "   ⚠️  Cannot test HTTP (curl not available)\n";
        $accessibleCount++; // Assume accessible if we can't test
    }
    
    echo "\n";
}

// Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 SUMMARY\n\n";
echo "Total files in uploads/site/: {$totalFiles}\n";
echo "Files tested: " . count($sampleFiles) . "\n";
echo "✅ Accessible: {$accessibleCount}\n";
echo "❌ Inaccessible: {$inaccessibleCount}\n";
echo "⚠️  Invalid: {$invalidCount}\n\n";

if ($inaccessibleCount > 0) {
    echo "⚠️  WARNING: Some images are not accessible via HTTP!\n";
    echo "   This could mean:\n";
    echo "   1. Web server is not running\n";
    echo "   2. .htaccess is blocking direct access\n";
    echo "   3. File permissions are incorrect\n";
    echo "   4. Images are being served through PHP instead of directly\n\n";
} else {
    echo "✅ All tested images are accessible!\n\n";
}

// Check .htaccess configuration
echo "🔧 Checking .htaccess configuration...\n";
$htaccessPath = __DIR__ . '/../.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    
    // Check if images are excluded from rewrite rules
    if (preg_match('/RewriteCond.*\.(jpg|jpeg|png|gif|webp|svg)/i', $htaccessContent)) {
        echo "✅ .htaccess excludes image files from rewrite rules\n";
    } else {
        echo "⚠️  .htaccess may not properly exclude image files\n";
    }
    
    // Check if directory listing is disabled
    if (strpos($htaccessContent, 'Options -Indexes') !== false) {
        echo "✅ Directory listing is disabled (good for security)\n";
    }
} else {
    echo "⚠️  .htaccess file not found\n";
}

echo "\n";

