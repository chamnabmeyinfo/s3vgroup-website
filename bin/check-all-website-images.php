<?php
/**
 * Check ALL Website Images
 * 
 * Comprehensive check of all images across the entire website:
 * - Products
 * - Categories
 * - Team Members
 * - Sliders
 * - CEO Message
 * - Company Story
 * - Testimonials
 * - Site Options (logo, favicon, etc.)
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.live.php';

$liveConfig = require __DIR__ . '/../config/database.live.php';

$db = new PDO(
    "mysql:host={$liveConfig['host']};dbname={$liveConfig['database']};charset=utf8mb4",
    $liveConfig['username'],
    $liveConfig['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

echo "🔍 Checking ALL website images...\n\n";

$allImages = [];
$missing = [];
$working = [];
$external = [];

// ============================================
// 1. PRODUCT IMAGES
// ============================================
echo "📦 Checking Product Images...\n";
$products = $db->query("
    SELECT id, name, heroImage 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != ''
")->fetchAll();

foreach ($products as $product) {
    $url = $product['heroImage'];
    $allImages[] = ['type' => 'Product', 'name' => $product['name'], 'url' => $url];
    
    if (strpos($url, 'unsplash.com') !== false) {
        $external[] = ['type' => 'Product', 'name' => $product['name'], 'url' => $url];
        continue;
    }
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Product', 'name' => $product['name'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Product', 'name' => $product['name'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Products: " . count($products) . " total\n";

// ============================================
// 2. CATEGORY IMAGES
// ============================================
echo "📁 Checking Category Images...\n";
$categories = $db->query("
    SELECT id, name, icon 
    FROM categories 
    WHERE icon IS NOT NULL AND icon != ''
")->fetchAll();

foreach ($categories as $category) {
    $url = $category['icon'];
    $allImages[] = ['type' => 'Category', 'name' => $category['name'], 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Category', 'name' => $category['name'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Category', 'name' => $category['name'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Categories: " . count($categories) . " total\n";

// ============================================
// 3. TEAM MEMBER PHOTOS
// ============================================
echo "👥 Checking Team Member Photos...\n";
$team = $db->query("
    SELECT id, name, photo 
    FROM team_members 
    WHERE photo IS NOT NULL AND photo != ''
")->fetchAll();

foreach ($team as $member) {
    $url = $member['photo'];
    $allImages[] = ['type' => 'Team', 'name' => $member['name'], 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Team', 'name' => $member['name'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Team', 'name' => $member['name'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Team Members: " . count($team) . " total\n";

// ============================================
// 4. SLIDER IMAGES
// ============================================
echo "🖼️  Checking Slider Images...\n";
$sliders = $db->query("
    SELECT id, title, image_url 
    FROM sliders 
    WHERE image_url IS NOT NULL AND image_url != ''
")->fetchAll();

foreach ($sliders as $slider) {
    $url = $slider['image_url'];
    $allImages[] = ['type' => 'Slider', 'name' => $slider['title'], 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Slider', 'name' => $slider['title'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Slider', 'name' => $slider['title'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Sliders: " . count($sliders) . " total\n";

// ============================================
// 5. CEO MESSAGE PHOTO
// ============================================
echo "👔 Checking CEO Message Photo...\n";
try {
    $ceo = $db->query("
        SELECT photo 
        FROM ceo_messages 
        WHERE photo IS NOT NULL AND photo != ''
        LIMIT 1
    ")->fetch();
} catch (PDOException $e) {
    $ceo = false; // Table doesn't exist
}

if ($ceo) {
    $url = $ceo['photo'];
    $allImages[] = ['type' => 'CEO', 'name' => 'CEO Message', 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'CEO', 'name' => 'CEO Message', 'url' => $url];
        } else {
            $missing[] = ['type' => 'CEO', 'name' => 'CEO Message', 'url' => $url, 'code' => $httpCode];
        }
    }
}

// ============================================
// 6. COMPANY STORY IMAGE
// ============================================
echo "📖 Checking Company Story Image...\n";
try {
    $story = $db->query("
        SELECT heroImage 
        FROM company_stories 
        WHERE heroImage IS NOT NULL AND heroImage != ''
        LIMIT 1
    ")->fetch();
} catch (PDOException $e) {
    $story = false; // Table doesn't exist
}

if ($story) {
    $url = $story['heroImage'];
    $allImages[] = ['type' => 'Company Story', 'name' => 'Company Story', 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Company Story', 'name' => 'Company Story', 'url' => $url];
        } else {
            $missing[] = ['type' => 'Company Story', 'name' => 'Company Story', 'url' => $url, 'code' => $httpCode];
        }
    }
}

// ============================================
// 7. TESTIMONIAL AVATARS
// ============================================
echo "💬 Checking Testimonial Avatars...\n";
$testimonials = $db->query("
    SELECT id, name, avatar 
    FROM testimonials 
    WHERE avatar IS NOT NULL AND avatar != ''
")->fetchAll();

foreach ($testimonials as $testimonial) {
    $url = $testimonial['avatar'];
    $allImages[] = ['type' => 'Testimonial', 'name' => $testimonial['name'], 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Testimonial', 'name' => $testimonial['name'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Testimonial', 'name' => $testimonial['name'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Testimonials: " . count($testimonials) . " total\n";

// ============================================
// 8. SITE OPTIONS (LOGO, FAVICON, etc.)
// ============================================
echo "⚙️  Checking Site Options Images...\n";
$imageKeys = ['site_logo', 'site_favicon', 'hero_image', 'background_image'];
$placeholders = implode(',', array_fill(0, count($imageKeys), '?'));
$options = $db->prepare("
    SELECT key_name, value 
    FROM site_options 
    WHERE key_name IN ($placeholders) 
    AND value IS NOT NULL AND value != ''
    AND value LIKE 'http%'
");
$options->execute($imageKeys);
$siteImages = $options->fetchAll();

foreach ($siteImages as $option) {
    $url = $option['value'];
    $allImages[] = ['type' => 'Site Option', 'name' => $option['key_name'], 'url' => $url];
    
    if (strpos($url, 's3vgroup.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $working[] = ['type' => 'Site Option', 'name' => $option['key_name'], 'url' => $url];
        } else {
            $missing[] = ['type' => 'Site Option', 'name' => $option['key_name'], 'url' => $url, 'code' => $httpCode];
        }
    }
}
echo "  Site Options: " . count($siteImages) . " total\n";

// ============================================
// SUMMARY
// ============================================
echo "\n═══════════════════════════════════════\n";
echo "  COMPREHENSIVE IMAGE REPORT\n";
echo "═══════════════════════════════════════\n\n";

// Group by type
$byType = [];
foreach ($missing as $item) {
    $type = $item['type'];
    if (!isset($byType[$type])) {
        $byType[$type] = 0;
    }
    $byType[$type]++;
}

echo "📊 Summary by Type:\n";
foreach ($byType as $type => $count) {
    echo "  $type: $count missing\n";
}

echo "\n📊 Overall Statistics:\n";
echo "  ✅ Working: " . count($working) . "\n";
echo "  ❌ Missing/Broken: " . count($missing) . "\n";
echo "  🌐 External: " . count($external) . "\n";
echo "  📁 Total Images: " . count($allImages) . "\n\n";

if (count($missing) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  MISSING IMAGES DETAILS\n";
    echo "═══════════════════════════════════════\n\n";
    
    foreach ($missing as $item) {
        echo "❌ {$item['type']}: {$item['name']}\n";
        echo "   URL: {$item['url']}\n";
        echo "   HTTP: {$item['code']}\n\n";
    }
    
    echo "💡 Solution:\n";
    echo "  Upload missing images to cPanel:\n";
    echo "  Location: public_html/uploads/site/\n";
} else {
    echo "✅ All images are working correctly!\n";
}

