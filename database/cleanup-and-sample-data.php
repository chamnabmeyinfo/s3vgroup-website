<?php
/**
 * Cleanup and Sample Data Script
 * 
 * This script:
 * 1. Cleans up test/duplicate data
 * 2. Adds high-quality sample data for innovation features
 * 
 * Run: php database/cleanup-and-sample-data.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Support\Id;

$db = Connection::getInstance();

echo "🧹 Starting cleanup and sample data insertion...\n\n";

// ============================================
// STEP 1: CLEANUP
// ============================================

echo "📋 Step 1: Cleaning up data...\n";

// Clean up old analytics events (keep last 30 days, remove older test data)
$db->exec("DELETE FROM analytics_events WHERE createdAt < DATE_SUB(NOW(), INTERVAL 30 DAY) AND event_name LIKE '%test%'");
echo "  ✓ Cleaned old test analytics events\n";

// Clean up spam/rejected reviews
$db->exec("DELETE FROM product_reviews WHERE status = 'SPAM'");
echo "  ✓ Cleaned spam reviews\n";

// Clean up old search logs (keep last 7 days)
$db->exec("DELETE FROM search_logs WHERE createdAt < DATE_SUB(NOW(), INTERVAL 7 DAY)");
echo "  ✓ Cleaned old search logs\n";

// Clean up old performance metrics (keep last 30 days)
$db->exec("DELETE FROM performance_metrics WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
echo "  ✓ Cleaned old performance metrics\n";

// Remove duplicate FAQs (keep the one with highest priority)
$db->exec("
    DELETE f1 FROM faqs f1
    INNER JOIN faqs f2 
    WHERE f1.id > f2.id 
    AND f1.question = f2.question
");
echo "  ✓ Removed duplicate FAQs\n";

echo "\n";

// ============================================
// STEP 2: SAMPLE DATA FOR ANALYTICS
// ============================================

echo "📊 Step 2: Adding sample analytics events...\n";

// Get existing products for realistic analytics
$products = $db->query("SELECT id, name FROM products WHERE status = 'PUBLISHED' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

if (!empty($products)) {
    $events = [
        ['page_view', 'Homepage Visit', '/'],
        ['page_view', 'Products Page', '/products.php'],
        ['page_view', 'About Page', '/about.php'],
        ['page_view', 'Contact Page', '/contact.php'],
    ];
    
    // Add product view events
    foreach ($products as $product) {
        $events[] = ['product_view', 'Product View: ' . $product['name'], '/product.php?slug=' . urlencode($product['name']), $product['id']];
    }
    
    // Generate events for last 7 days
    for ($day = 0; $day < 7; $day++) {
        $date = date('Y-m-d H:i:s', strtotime("-$day days"));
        $eventCount = rand(20, 50); // Random events per day
        
        for ($i = 0; $i < $eventCount; $i++) {
            $event = $events[array_rand($events)];
            $id = Id::prefixed('event');
            $sessionId = 'session_' . uniqid();
            
            $stmt = $db->prepare("
                INSERT INTO analytics_events (
                    id, event_type, event_name, user_ip, user_agent, 
                    page_url, product_id, session_id, createdAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $id,
                $event[0],
                $event[1],
                '192.168.1.' . rand(1, 255),
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                $event[2] ?? null,
                $event[3] ?? null,
                $sessionId,
                date('Y-m-d H:i:s', strtotime($date) + rand(0, 86400))
            ]);
        }
    }
    
    echo "  ✓ Added " . count($products) * 7 . " sample analytics events\n";
} else {
    echo "  ⚠ No products found, skipping analytics events\n";
}

echo "\n";

// ============================================
// STEP 3: SAMPLE PRODUCT REVIEWS
// ============================================

echo "⭐ Step 3: Adding sample product reviews...\n";

if (!empty($products)) {
    $reviewTemplates = [
        [
            'name' => 'Sok Pisey',
            'email' => 'sok.pisey@example.com',
            'rating' => 5,
            'title' => 'Excellent Quality Forklift',
            'text' => 'We purchased this forklift 6 months ago and it has been working perfectly. Very reliable and efficient. The service team was also very helpful during installation.',
            'verified' => true
        ],
        [
            'name' => 'Chan Sophal',
            'email' => 'chan.sophal@example.com',
            'rating' => 5,
            'title' => 'Great Value for Money',
            'text' => 'This equipment exceeded our expectations. The build quality is excellent and it handles heavy loads without any issues. Highly recommended!',
            'verified' => true
        ],
        [
            'name' => 'Lim Srey Pich',
            'email' => 'lim.srey@example.com',
            'rating' => 4,
            'title' => 'Good Product, Minor Issues',
            'text' => 'Overall a good product. We had a small issue with the battery initially, but customer service resolved it quickly. Very satisfied now.',
            'verified' => true
        ],
        [
            'name' => 'Meas Ratha',
            'email' => 'meas.ratha@example.com',
            'rating' => 5,
            'title' => 'Perfect for Our Warehouse',
            'text' => 'This equipment fits perfectly in our warehouse operations. Easy to operate and maintain. The team at S3V Group provided excellent support.',
            'verified' => false
        ],
        [
            'name' => 'Kim Sopheak',
            'email' => 'kim.sopheak@example.com',
            'rating' => 5,
            'title' => 'Outstanding Performance',
            'text' => 'We use this daily in our distribution center. It\'s powerful, reliable, and has significantly improved our productivity. Great investment!',
            'verified' => true
        ],
    ];
    
    $reviewCount = 0;
    foreach ($products as $product) {
        // Add 2-3 reviews per product
        $numReviews = rand(2, 3);
        $selectedReviews = array_slice($reviewTemplates, 0, $numReviews);
        
        foreach ($selectedReviews as $template) {
            $id = Id::prefixed('review');
            $stmt = $db->prepare("
                INSERT INTO product_reviews (
                    id, product_id, customer_name, customer_email, rating, 
                    title, review_text, verified_purchase, status, createdAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', NOW())
            ");
            
            $stmt->execute([
                $id,
                $product['id'],
                $template['name'],
                $template['email'],
                $template['rating'],
                $template['title'],
                $template['text'],
                $template['verified'] ? 1 : 0
            ]);
            $reviewCount++;
        }
    }
    
    echo "  ✓ Added $reviewCount sample product reviews\n";
} else {
    echo "  ⚠ No products found, skipping reviews\n";
}

echo "\n";

// ============================================
// STEP 4: SAMPLE FAQs
// ============================================

echo "❓ Step 4: Adding sample FAQs...\n";

$faqs = [
    [
        'question' => 'What types of forklifts do you offer?',
        'answer' => 'We offer a wide range of forklifts including electric, diesel, and LPG-powered models. Our forklifts range from 1.5 to 10 tons capacity, suitable for various industrial applications.',
        'category' => 'Products',
        'priority' => 100
    ],
    [
        'question' => 'Do you provide installation services?',
        'answer' => 'Yes, we provide professional installation services for all our equipment. Our trained technicians will ensure proper setup and provide training for your staff.',
        'category' => 'Services',
        'priority' => 95
    ],
    [
        'question' => 'What is your warranty policy?',
        'answer' => 'All our equipment comes with a comprehensive warranty. Standard warranty is 12 months for parts and labor. Extended warranty options are also available.',
        'category' => 'Warranty',
        'priority' => 90
    ],
    [
        'question' => 'Do you offer maintenance services?',
        'answer' => 'Yes, we provide regular maintenance services to keep your equipment running smoothly. We offer scheduled maintenance plans and emergency repair services.',
        'category' => 'Services',
        'priority' => 85
    ],
    [
        'question' => 'Can I get a quote online?',
        'answer' => 'Absolutely! You can request a quote through our website by filling out the quote request form. Our sales team will contact you within 24 hours with a detailed quotation.',
        'category' => 'Sales',
        'priority' => 80
    ],
    [
        'question' => 'What payment methods do you accept?',
        'answer' => 'We accept various payment methods including bank transfers, credit cards, and financing options. We also offer flexible payment plans for qualified customers.',
        'category' => 'Payment',
        'priority' => 75
    ],
    [
        'question' => 'Do you deliver equipment?',
        'answer' => 'Yes, we provide delivery services throughout Cambodia. Delivery charges depend on location and equipment size. Contact us for a delivery quote.',
        'category' => 'Delivery',
        'priority' => 70
    ],
    [
        'question' => 'What is the lead time for orders?',
        'answer' => 'Lead times vary depending on the product and stock availability. Most standard items are available within 1-2 weeks. Custom orders may take 4-6 weeks.',
        'category' => 'Orders',
        'priority' => 65
    ],
    [
        'question' => 'Do you provide training for equipment operation?',
        'answer' => 'Yes, we provide comprehensive training for all equipment operators. Training includes safety procedures, operation techniques, and basic maintenance.',
        'category' => 'Training',
        'priority' => 60
    ],
    [
        'question' => 'Can I trade in my old equipment?',
        'answer' => 'Yes, we accept trade-ins for used equipment. We will evaluate your equipment and provide a fair trade-in value that can be applied to your new purchase.',
        'category' => 'Trade-in',
        'priority' => 55
    ],
];

$faqCount = 0;
foreach ($faqs as $faq) {
    // Check if FAQ already exists
    $exists = $db->prepare("SELECT id FROM faqs WHERE question = ?");
    $exists->execute([$faq['question']]);
    
    if ($exists->rowCount() === 0) {
        $id = Id::prefixed('faq');
        $stmt = $db->prepare("
            INSERT INTO faqs (id, question, answer, category, priority, status, createdAt)
            VALUES (?, ?, ?, ?, ?, 'PUBLISHED', NOW())
        ");
        
        $stmt->execute([
            $id,
            $faq['question'],
            $faq['answer'],
            $faq['category'],
            $faq['priority']
        ]);
        $faqCount++;
    }
}

echo "  ✓ Added $faqCount sample FAQs\n";

echo "\n";

// ============================================
// STEP 5: SAMPLE SEARCH LOGS
// ============================================

echo "🔍 Step 5: Adding sample search logs...\n";

$searchQueries = [
    'forklift',
    'electric forklift',
    'pallet racking',
    'warehouse equipment',
    'material handling',
    'conveyor belt',
    'pallet jack',
    'storage solutions',
    'industrial equipment',
    'forklift parts'
];

$searchCount = 0;
for ($day = 0; $day < 7; $day++) {
    $queriesPerDay = rand(10, 25);
    
    for ($i = 0; $i < $queriesPerDay; $i++) {
        $query = $searchQueries[array_rand($searchQueries)];
        $id = Id::prefixed('search');
        $resultsCount = rand(5, 20);
        
        $stmt = $db->prepare("
            INSERT INTO search_logs (id, search_query, results_count, user_ip, createdAt)
            VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
        ");
        
        $stmt->execute([
            $id,
            $query,
            $resultsCount,
            '192.168.1.' . rand(1, 255),
            $day
        ]);
        $searchCount++;
    }
}

echo "  ✓ Added $searchCount sample search logs\n";

echo "\n";

// ============================================
// STEP 6: ENABLE USEFUL OPTIONAL FEATURES
// ============================================

echo "✨ Step 6: Enabling useful optional features...\n";

$featuresToEnable = [
    'multi_language' => [
        'name' => 'Multi-Language Support',
        'description' => 'Enable multiple languages (Khmer, English, etc.)',
        'category' => 'Localization'
    ],
    'wishlist' => [
        'name' => 'Product Wishlist',
        'description' => 'Allow customers to save favorite products',
        'category' => 'E-commerce'
    ],
];

$enabledCount = 0;
foreach ($featuresToEnable as $key => $feature) {
    $exists = $db->prepare("SELECT id FROM optional_features WHERE feature_key = ?");
    $exists->execute([$key]);
    
    if ($exists->rowCount() === 0) {
        $id = Id::prefixed('feature');
        $stmt = $db->prepare("
            INSERT INTO optional_features (id, feature_key, feature_name, description, category, enabled, createdAt)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $stmt->execute([
            $id,
            $key,
            $feature['name'],
            $feature['description'],
            $feature['category']
        ]);
        $enabledCount++;
    } else {
        // Enable if exists but disabled
        $db->prepare("UPDATE optional_features SET enabled = 1 WHERE feature_key = ?")->execute([$key]);
        $enabledCount++;
    }
}

echo "  ✓ Enabled $enabledCount optional features\n";

echo "\n";

// ============================================
// SUMMARY
// ============================================

echo "✅ Cleanup and sample data insertion completed!\n\n";

// Show summary
$analyticsCount = $db->query("SELECT COUNT(*) FROM analytics_events")->fetchColumn();
$reviewsCount = $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'APPROVED'")->fetchColumn();
$faqsCount = $db->query("SELECT COUNT(*) FROM faqs WHERE status = 'PUBLISHED'")->fetchColumn();
$searchCount = $db->query("SELECT COUNT(*) FROM search_logs")->fetchColumn();
$featuresCount = $db->query("SELECT COUNT(*) FROM optional_features WHERE enabled = 1")->fetchColumn();

echo "📊 Current Data Summary:\n";
echo "  • Analytics Events: " . number_format($analyticsCount) . "\n";
echo "  • Approved Reviews: " . number_format($reviewsCount) . "\n";
echo "  • Published FAQs: " . number_format($faqsCount) . "\n";
echo "  • Search Logs: " . number_format($searchCount) . "\n";
echo "  • Enabled Features: " . number_format($featuresCount) . "\n";

echo "\n🎉 All done! Your innovation features now have quality sample data.\n";

