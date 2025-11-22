<?php

declare(strict_types=1);

echo "🔍 Verifying image accessibility before assignment...\n\n";

/**
 * Verify if an image URL is accessible
 */
function isImageAccessible(string $url): bool
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    return $httpCode === 200 && strpos($contentType, 'image') !== false;
}

/**
 * Test image URL using get_headers (fallback)
 */
function isImageAccessibleFallback(string $url): bool
{
    $headers = @get_headers($url, 1);
    if (!$headers) {
        return false;
    }
    
    $statusLine = $headers[0];
    if (strpos($statusLine, '200') === false) {
        return false;
    }
    
    // Check if it's an image
    $contentType = $headers['Content-Type'] ?? '';
    if (is_array($contentType)) {
        $contentType = end($contentType);
    }
    
    return strpos($contentType, 'image') !== false;
}

// Test pool of images to verify accessibility
$imagePool = [
    'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1469362102473-8622cfb973cd?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=800&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&auto=format&fit=crop&q=85',
];

echo "🧪 Testing image accessibility...\n";
$accessibleImages = [];
$inaccessibleImages = [];

foreach ($imagePool as $index => $imageUrl) {
    echo "  Testing image " . ($index + 1) . "/" . count($imagePool) . "... ";
    
    // Try curl first, then fallback
    if (function_exists('curl_init')) {
        $accessible = isImageAccessible($imageUrl);
    } else {
        $accessible = isImageAccessibleFallback($imageUrl);
    }
    
    if ($accessible) {
        echo "✅ Accessible\n";
        $accessibleImages[] = $imageUrl;
    } else {
        echo "❌ Not accessible\n";
        $inaccessibleImages[] = $imageUrl;
    }
}

echo "\n📊 Results:\n";
echo "   ✅ Accessible: " . count($accessibleImages) . " images\n";
echo "   ❌ Inaccessible: " . count($inaccessibleImages) . " images\n";

if (!empty($inaccessibleImages)) {
    echo "\n⚠️  Inaccessible images:\n";
    foreach ($inaccessibleImages as $img) {
        echo "   - " . substr($img, 0, 80) . "...\n";
    }
}

// Return accessible images
if (count($accessibleImages) > 0) {
    echo "\n✅ Using " . count($accessibleImages) . " accessible images\n";
    return $accessibleImages;
} else {
    echo "\n❌ No accessible images found! Check your internet connection.\n";
    return [];
}

