<?php
/**
 * Check GD Extension Support
 */

echo "🔍 Checking GD Extension Support...\n\n";

if (extension_loaded('gd')) {
    echo "✅ GD Extension: Loaded\n\n";
    
    $info = gd_info();
    echo "GD Version: " . ($info['GD Version'] ?? 'Unknown') . "\n";
    echo "JPEG Support: " . (isset($info['JPEG Support']) && $info['JPEG Support'] ? 'Yes ✓' : 'No ✗') . "\n";
    echo "PNG Support: " . (isset($info['PNG Support']) && $info['PNG Support'] ? 'Yes ✓' : 'No ✗') . "\n";
    echo "WebP Support: " . (isset($info['WebP Support']) && $info['WebP Support'] ? 'Yes ✓' : 'No ✗') . "\n";
    echo "GIF Support: " . (isset($info['GIF Read Support']) && $info['GIF Read Support'] ? 'Yes ✓' : 'No ✗') . "\n";
    
    if (function_exists('imagewebp')) {
        echo "\n✅ WebP functions available - WebP conversion will work!\n";
    } else {
        echo "\n⚠️  WebP functions not available - will use JPEG/PNG only\n";
    }
} else {
    echo "❌ GD Extension: NOT Loaded\n";
    echo "\n⚠️  Image optimization will not work without GD extension!\n";
    echo "   Please enable the GD extension in your PHP configuration.\n";
}

