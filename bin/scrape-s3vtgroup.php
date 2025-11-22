<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;
use App\Support\Id;

echo "🌐 Scraping data from www.s3vtgroup.com.kh...\n\n";

$url = 'https://www.s3vtgroup.com.kh';

// Fetch the website
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Error fetching website: {$error}\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "❌ HTTP Error: {$httpCode}\n";
    exit(1);
}

if (empty($html)) {
    echo "❌ Empty response from website\n";
    exit(1);
}

echo "✅ Successfully fetched website content\n";
echo "   Size: " . number_format(strlen($html)) . " bytes\n\n";

// Simple HTML parsing (basic extraction)
// Note: This is a simplified parser. For complex sites, consider using DOMDocument or SimpleHTMLDom

// Extract title
preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $titleMatch);
$siteTitle = isset($titleMatch[1]) ? trim(html_entity_decode($titleMatch[1])) : 'S3V Group';

echo "📄 Site Title: {$siteTitle}\n\n";

// Try to find product/category information
// Look for common patterns in the HTML

$pdo = Connection::getInstance();

// Extract text content (remove scripts and styles)
$textContent = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
$textContent = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $textContent);
$textContent = strip_tags($textContent);
$textContent = preg_replace('/\s+/', ' ', $textContent);

// Look for contact information
$contacts = [];
if (preg_match_all('/(\+?855[\d\s-]{8,12})/i', $textContent, $phoneMatches)) {
    $contacts['phones'] = array_unique($phoneMatches[1]);
}
if (preg_match_all('/([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,})/i', $textContent, $emailMatches)) {
    $contacts['emails'] = array_unique($emailMatches[1]);
}

if (!empty($contacts)) {
    echo "📞 Contact Information Found:\n";
    if (!empty($contacts['phones'])) {
        echo "   Phones: " . implode(', ', $contacts['phones']) . "\n";
    }
    if (!empty($contacts['emails'])) {
        echo "   Emails: " . implode(', ', $contacts['emails']) . "\n";
    }
    echo "\n";
}

// Try to find product/service names (look for common patterns)
// This is a heuristic approach - actual extraction would need site-specific parsing

$potentialProducts = [];
$productKeywords = ['forklift', 'equipment', 'warehouse', 'storage', 'material handling', 'pallet', 'shelving', 'safety', 'industrial'];

foreach ($productKeywords as $keyword) {
    if (stripos($textContent, $keyword) !== false) {
        // Try to extract sentences containing the keyword
        $pattern = '/[^.!?]*' . preg_quote($keyword, '/') . '[^.!?]*[.!?]/i';
        if (preg_match_all($pattern, $textContent, $matches)) {
            $potentialProducts[$keyword] = $matches[0];
        }
    }
}

if (!empty($potentialProducts)) {
    echo "📦 Potential Products/Services Found:\n";
    foreach ($potentialProducts as $keyword => $matches) {
        $count = min(count($matches), 3); // Show max 3 examples
        echo "   {$keyword}: " . $count . " references found\n";
    }
    echo "\n";
}

// Update site configuration if we found contact info
if (!empty($contacts)) {
    echo "💾 Updating site configuration...\n";
    
    $siteConfigFile = base_path('config/site.php');
    if (file_exists($siteConfigFile)) {
        $configContent = file_get_contents($siteConfigFile);
        
        // Update phone if found
        if (!empty($contacts['phones'])) {
            $phone = $contacts['phones'][0];
            $configContent = preg_replace(
                "/'phone'\s*=>\s*'[^']*'/",
                "'phone' => '{$phone}'",
                $configContent
            );
        }
        
        // Update email if found
        if (!empty($contacts['emails'])) {
            $email = $contacts['emails'][0];
            $configContent = preg_replace(
                "/'email'\s*=>\s*'[^']*'/",
                "'email' => '{$email}'",
                $configContent
            );
        }
        
        file_put_contents($siteConfigFile, $configContent);
        echo "   ✅ Site configuration updated\n\n";
    }
}

// Save raw HTML for manual review
$htmlFile = base_path('scraped-content.html');
file_put_contents($htmlFile, $html);
echo "💾 Saved full HTML content to: scraped-content.html\n";
echo "   (You can review this file manually to identify specific data to extract)\n\n";

echo "✨ Scraping complete!\n";
echo "\n📝 Next steps:\n";
echo "   1. Review scraped-content.html to identify specific product/service data\n";
echo "   2. Manually extract products and add them via admin panel or update bin/seed.php\n";
echo "   3. For automated extraction, you may need to inspect the actual HTML structure\n\n";

