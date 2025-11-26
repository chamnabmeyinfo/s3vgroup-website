<?php
/**
 * Comprehensive Demo Data Entry Script
 * 
 * This script:
 * 1. Cleans up ALL duplicates across all tables
 * 2. Adds comprehensive demo data for all features
 * 3. Ensures data is demo-ready and professional
 * 
 * Run: php database/demo-data-entry.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Support\Id;

$db = Connection::getInstance();

echo "🎬 Starting comprehensive demo data entry...\n\n";

// ============================================
// STEP 1: COMPREHENSIVE CLEANUP
// ============================================

echo "🧹 Step 1: Cleaning up duplicates and test data...\n";

// Clean duplicates in products (by slug)
$db->exec("
    DELETE p1 FROM products p1
    INNER JOIN products p2 
    WHERE p1.id > p2.id 
    AND p1.slug = p2.slug
");
echo "  ✓ Cleaned duplicate products\n";

// Clean duplicates in categories (by slug)
$db->exec("
    DELETE c1 FROM categories c1
    INNER JOIN categories c2 
    WHERE c1.id > c2.id 
    AND c1.slug = c2.slug
");
echo "  ✓ Cleaned duplicate categories\n";

// Clean duplicates in testimonials (by name and company)
$db->exec("
    DELETE t1 FROM testimonials t1
    INNER JOIN testimonials t2 
    WHERE t1.id > t2.id 
    AND t1.name = t2.name 
    AND t1.company = t2.company
");
echo "  ✓ Cleaned duplicate testimonials\n";

// Clean duplicates in team_members (by email)
$db->exec("
    DELETE tm1 FROM team_members tm1
    INNER JOIN team_members tm2 
    WHERE tm1.id > tm2.id 
    AND tm1.email = tm2.email
");
echo "  ✓ Cleaned duplicate team members\n";

// Clean duplicates in sliders (by title)
$db->exec("
    DELETE s1 FROM sliders s1
    INNER JOIN sliders s2 
    WHERE s1.id > s2.id 
    AND s1.title = s2.title
");
echo "  ✓ Cleaned duplicate sliders\n";

// Clean duplicates in faqs (by question)
$db->exec("
    DELETE f1 FROM faqs f1
    INNER JOIN faqs f2 
    WHERE f1.id > f2.id 
    AND f1.question = f2.question
");
echo "  ✓ Cleaned duplicate FAQs\n";

// Clean duplicates in product_reviews (by product_id, customer_email, and similar text)
$db->exec("
    DELETE r1 FROM product_reviews r1
    INNER JOIN product_reviews r2 
    WHERE r1.id > r2.id 
    AND r1.product_id = r2.product_id 
    AND r1.customer_email = r2.customer_email
    AND ABS(LENGTH(r1.review_text) - LENGTH(r2.review_text)) < 10
");
echo "  ✓ Cleaned duplicate reviews\n";

// Clean old test data
$db->exec("DELETE FROM search_logs WHERE search_query LIKE '%test%' OR search_query LIKE '%Test%'");
echo "  ✓ Cleaned test data\n";

echo "\n";

// ============================================
// STEP 2: DEMO CATEGORIES
// ============================================

echo "📦 Step 2: Adding demo categories...\n";

$categories = [
    ['cat_001', 'Forklifts', 'forklifts', 'Material handling forklifts for warehouses and factories', '🚛', 100],
    ['cat_002', 'Pallet Handling', 'pallet-handling', 'Pallet jacks, stackers, and pallet handling equipment', '📦', 90],
    ['cat_003', 'Storage Solutions', 'storage-solutions', 'Racking systems, shelving, and storage equipment', '🗄️', 80],
    ['cat_004', 'Conveyor Systems', 'conveyor-systems', 'Conveyor belts and automated material handling', '⚙️', 70],
    ['cat_005', 'Weighing Equipment', 'weighing-equipment', 'Industrial scales and weighing systems', '⚖️', 60],
    ['cat_006', 'Safety Equipment', 'safety-equipment', 'Safety gear and equipment for industrial operations', '🛡️', 50],
];

$categoryCount = 0;
foreach ($categories as $cat) {
    $exists = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $exists->execute([$cat[0]]);
    
    if ($exists->rowCount() === 0) {
        $stmt = $db->prepare("
            INSERT INTO categories (id, name, slug, description, icon, priority, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute($cat);
        $categoryCount++;
    }
}

echo "  ✓ Added/verified $categoryCount categories\n\n";

// ============================================
// STEP 3: DEMO PRODUCTS
// ============================================

echo "🛍️ Step 3: Adding demo products...\n";

$products = [
    [
        'prod_001', 'Electric Forklift 3.5 Ton', 'electric-forklift-35-ton', 'FL-ELEC-3500',
        'High-performance electric forklift perfect for indoor warehouse operations. Zero emissions and quiet operation.',
        'This electric forklift features advanced battery technology, ergonomic design, and excellent maneuverability. Ideal for warehouses, distribution centers, and manufacturing facilities. Features include: 48V battery system, 6-meter lift height, comfortable operator cabin, and low maintenance requirements.',
        '{"capacity": "3.5 tons", "lift_height": "6 meters", "power": "48V battery", "weight": "4500 kg", "dimensions": "2.3m x 1.2m x 2.1m", "battery_life": "8 hours", "max_speed": "18 km/h"}',
        'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1200&q=80',
        45000.00,
        '["Zero emissions", "Quiet operation", "Low maintenance", "Ergonomic design", "Long battery life"]',
        'cat_001'
    ],
    [
        'prod_002', 'Diesel Forklift 5 Ton', 'diesel-forklift-5-ton', 'FL-DSL-5000',
        'Heavy-duty diesel forklift for outdoor and rugged applications. Powerful engine and excellent lifting capacity.',
        'Built for tough conditions, this diesel forklift delivers reliable performance in outdoor yards, construction sites, and heavy industrial environments. Features a powerful 4.5L diesel engine, 7-meter lift height, and robust construction for durability.',
        '{"capacity": "5 tons", "lift_height": "7 meters", "engine": "4.5L diesel", "weight": "6800 kg", "dimensions": "2.8m x 1.4m x 2.3m", "fuel_tank": "80L", "max_speed": "25 km/h"}',
        'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1200&q=80',
        65000.00,
        '["Heavy duty", "Outdoor capable", "High capacity", "Durable construction", "Powerful engine"]',
        'cat_001'
    ],
    [
        'prod_003', 'Electric Pallet Jack 2.5 Ton', 'electric-pallet-jack-25-ton', 'PJ-ELEC-2500',
        'Compact electric pallet jack for efficient material handling. Easy to operate and maintain.',
        'Perfect for moving pallets in tight spaces. Features ergonomic controls, long-lasting battery, and smooth operation. Ideal for warehouses, retail stores, and distribution centers.',
        '{"capacity": "2.5 tons", "lift_height": "20 cm", "power": "24V battery", "weight": "280 kg", "width": "68 cm", "battery_life": "6 hours"}',
        'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1200&q=80',
        3500.00,
        '["Compact design", "Easy operation", "Battery powered", "Affordable", "Maneuverable"]',
        'cat_002'
    ],
    [
        'prod_004', 'Heavy Duty Pallet Racking', 'heavy-duty-pallet-racking', 'PR-HD-1000',
        'Industrial-grade pallet racking system. Adjustable beam heights and high load capacity.',
        'Maximize your warehouse storage with this robust racking system. Easy to install and configure for your specific needs. Made from high-quality steel with corrosion-resistant coating.',
        '{"capacity": "1000 kg per level", "height": "Up to 10 meters", "width": "2.7m", "depth": "1.0m", "material": "Steel", "coating": "Powder coated"}',
        'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1200&q=80',
        850.00,
        '["High capacity", "Adjustable", "Durable", "Easy installation", "Corrosion resistant"]',
        'cat_003'
    ],
    [
        'prod_005', 'Mobile Conveyor Belt 6m', 'mobile-conveyor-belt-6m', 'CV-MOB-600',
        'Portable conveyor system for loading and unloading operations. Adjustable height and angle.',
        'Increase efficiency with this mobile conveyor. Perfect for trucks, warehouses, and distribution centers. Features variable speed control and easy mobility.',
        '{"length": "6 meters", "width": "60 cm", "speed": "Variable 0-30 m/min", "power": "Electric 220V", "weight": "450 kg", "max_load": "50 kg/m"}',
        'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1200&q=80',
        12000.00,
        '["Portable", "Adjustable", "Efficient", "Versatile", "Variable speed"]',
        'cat_004'
    ],
    [
        'prod_006', 'Digital Weighing Scale 10 Ton', 'digital-weighing-scale-10-ton', 'WS-DIG-10000',
        'Precision digital scale for heavy-duty weighing applications. Large display and durable construction.',
        'Accurate weighing for industrial applications. Features large LED display, multiple weighing units, and durable platform. Perfect for warehouses and factories.',
        '{"capacity": "10 tons", "accuracy": "±0.1%", "display": "LED 20mm", "platform": "1.2m x 1.5m", "power": "AC/DC", "units": "kg, lb, t"}',
        'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?w=1200&q=80',
        2800.00,
        '["High accuracy", "Large display", "Durable", "Multiple units", "Precision"]',
        'cat_005'
    ],
    [
        'prod_007', 'LPG Forklift 2.5 Ton', 'lpg-forklift-25-ton', 'FL-LPG-2500',
        'Versatile LPG-powered forklift suitable for both indoor and outdoor use. Clean burning and cost-effective.',
        'This LPG forklift offers the flexibility of indoor and outdoor operation. Clean-burning fuel, easy refueling, and excellent performance make it ideal for various applications.',
        '{"capacity": "2.5 tons", "lift_height": "5.5 meters", "fuel": "LPG", "weight": "3800 kg", "dimensions": "2.1m x 1.1m x 2.0m", "tank_capacity": "45L"}',
        'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1200&q=80',
        38000.00,
        '["Versatile", "Clean fuel", "Cost effective", "Indoor/outdoor", "Easy refueling"]',
        'cat_001'
    ],
    [
        'prod_008', 'Narrow Aisle Forklift', 'narrow-aisle-forklift', 'FL-NA-2000',
        'Specialized forklift for narrow aisle operations. Maximizes warehouse space utilization.',
        'Designed for narrow aisle applications, this forklift allows you to maximize storage density. Features 90-degree steering and compact design.',
        '{"capacity": "2 tons", "lift_height": "8 meters", "power": "Electric 48V", "width": "1.0m", "turning_radius": "1.5m"}',
        'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1200&q=80',
        52000.00,
        '["Space saving", "High reach", "Maneuverable", "Efficient", "Compact"]',
        'cat_001'
    ],
];

$productCount = 0;
foreach ($products as $prod) {
    $exists = $db->prepare("SELECT id FROM products WHERE id = ?");
    $exists->execute([$prod[0]]);
    
    if ($exists->rowCount() === 0) {
        $stmt = $db->prepare("
            INSERT INTO products (id, name, slug, sku, summary, description, specs, heroImage, price, highlights, categoryId, status, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PUBLISHED', NOW(), NOW())
        ");
        $stmt->execute($prod);
        $productCount++;
    } else {
        // Update existing product
        $stmt = $db->prepare("
            UPDATE products SET name = ?, slug = ?, sku = ?, summary = ?, description = ?, specs = ?, heroImage = ?, price = ?, highlights = ?, categoryId = ?, status = 'PUBLISHED', updatedAt = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$prod[1], $prod[2], $prod[3], $prod[4], $prod[5], $prod[6], $prod[7], $prod[8], $prod[9], $prod[10], $prod[0]]);
    }
}

echo "  ✓ Added/updated " . count($products) . " products\n\n";

// ============================================
// STEP 4: DEMO TEAM MEMBERS
// ============================================

echo "👥 Step 4: Adding demo team members...\n";

$teamMembers = [
    [
        'team_001', 'Sok Pisey', 'General Manager',
        'With over 15 years of experience in industrial equipment and warehouse solutions, Sok leads our team with expertise and dedication. He has a proven track record in helping businesses optimize their operations.',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80',
        'sok.pisey@s3vtgroup.com.kh', '+855 12 345 678', 'https://linkedin.com/in/sokpisey', 100
    ],
    [
        'team_002', 'Chan Sophal', 'Sales Director',
        'Chan brings extensive knowledge of material handling equipment and helps clients find the perfect solutions for their needs. With 12 years in the industry, he understands the unique challenges businesses face.',
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80',
        'chan.sophal@s3vtgroup.com.kh', '+855 12 345 679', 'https://linkedin.com/in/chansophal', 90
    ],
    [
        'team_003', 'Lim Srey Pich', 'Technical Support Manager',
        'Lim ensures all equipment is properly installed and maintained. Expert in forklift maintenance and repair with 10 years of hands-on experience. She leads our technical support team.',
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80',
        'lim.srey@s3vtgroup.com.kh', '+855 12 345 680', 'https://linkedin.com/in/limsrey', 80
    ],
    [
        'team_004', 'Meas Ratha', 'Operations Manager',
        'Meas coordinates logistics and ensures smooth operations across all departments. Expert in warehouse optimization with a focus on efficiency and safety.',
        'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80',
        'meas.ratha@s3vtgroup.com.kh', '+855 12 345 681', 'https://linkedin.com/in/measratha', 70
    ],
    [
        'team_005', 'Kim Sopheak', 'Service Technician',
        'Kim specializes in equipment maintenance and repair. With 8 years of experience, he ensures all equipment operates at peak performance.',
        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&q=80',
        'kim.sopheak@s3vtgroup.com.kh', '+855 12 345 682', 'https://linkedin.com/in/kimsopheak', 60
    ],
];

$teamCount = 0;
foreach ($teamMembers as $member) {
    $exists = $db->prepare("SELECT id FROM team_members WHERE id = ?");
    $exists->execute([$member[0]]);
    
    if ($exists->rowCount() === 0) {
        $stmt = $db->prepare("
            INSERT INTO team_members (id, name, title, bio, photo, email, phone, linkedin, priority, status, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW(), NOW())
        ");
        $stmt->execute($member);
        $teamCount++;
    }
}

echo "  ✓ Added/verified $teamCount team members\n\n";

// ============================================
// STEP 5: DEMO TESTIMONIALS
// ============================================

echo "💬 Step 5: Adding demo testimonials...\n";

$testimonials = [
    [
        'test_001', 'Sok Pisey', 'ABC Logistics Co., Ltd.', 'Operations Manager', 5,
        'S3V Group provided excellent forklift solutions for our warehouse. The equipment is reliable and their service is outstanding. Highly recommended!',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80', 1, 100
    ],
    [
        'test_002', 'Chan Sophal', 'Cambodia Manufacturing Inc.', 'Factory Manager', 5,
        'We purchased material handling equipment from S3V Group and couldn\'t be happier. Professional service and quality products. Our operations have improved significantly.',
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&q=80', 1, 90
    ],
    [
        'test_003', 'Lim Srey Pich', 'Royal Distribution Center', 'Warehouse Director', 5,
        'The storage racking system we got from S3V Group has maximized our warehouse space. Installation was smooth and the team was very professional.',
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&q=80', 1, 80
    ],
    [
        'test_004', 'Meas Ratha', 'Phnom Penh Port Authority', 'Logistics Manager', 5,
        'Outstanding service and quality equipment. The forklifts we purchased have been working flawlessly for over a year. Great investment!',
        'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=80', 1, 70
    ],
    [
        'test_005', 'Kim Sopheak', 'Cambodia Textile Factory', 'Production Manager', 5,
        'S3V Group helped us set up our entire material handling system. Their expertise and support made the process smooth and efficient.',
        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=200&q=80', 0, 60
    ],
];

$testimonialCount = 0;
foreach ($testimonials as $test) {
    $exists = $db->prepare("SELECT id FROM testimonials WHERE id = ?");
    $exists->execute([$test[0]]);
    
    if ($exists->rowCount() === 0) {
        $stmt = $db->prepare("
            INSERT INTO testimonials (id, name, company, position, rating, content, avatar, featured, priority, status, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PUBLISHED', NOW(), NOW())
        ");
        $stmt->execute($test);
        $testimonialCount++;
    }
}

echo "  ✓ Added/verified $testimonialCount testimonials\n\n";

// ============================================
// STEP 6: DEMO SLIDERS
// ============================================

echo "🖼️ Step 6: Adding demo hero sliders...\n";

$sliders = [
    [
        'slider_001', 'Premium Warehouse Solutions', 'Transform Your Operations',
        'Discover our comprehensive range of industrial equipment designed to optimize your warehouse and factory operations.',
        'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1920&q=80',
        '/products.php', 'Explore Products', '#0b3a63', 100
    ],
    [
        'slider_002', 'Expert Support & Service', 'Your Trusted Partner',
        'From installation to maintenance, our experienced team ensures your equipment operates at peak performance.',
        'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920&q=80',
        '/contact.php', 'Contact Us', '#1a5a8a', 90
    ],
    [
        'slider_003', 'Quality Equipment for Every Need', 'Industrial Excellence',
        'Browse our extensive catalog of forklifts, racking systems, and material handling solutions.',
        'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1920&q=80',
        '/quote.php', 'Request Quote', '#0b3a63', 80
    ],
];

$sliderCount = 0;
foreach ($sliders as $slider) {
    $exists = $db->prepare("SELECT id FROM sliders WHERE id = ?");
    $exists->execute([$slider[0]]);
    
    if ($exists->rowCount() === 0) {
        $stmt = $db->prepare("
            INSERT INTO sliders (id, title, subtitle, description, image_url, link_url, link_text, button_color, priority, status, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PUBLISHED', NOW(), NOW())
        ");
        $stmt->execute($slider);
        $sliderCount++;
    }
}

echo "  ✓ Added/verified $sliderCount hero sliders\n\n";

// ============================================
// STEP 7: DEMO PRODUCT REVIEWS
// ============================================

echo "⭐ Step 7: Adding demo product reviews...\n";

// Get product IDs
$productIds = $db->query("SELECT id, name FROM products WHERE status = 'PUBLISHED' LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

$reviewTemplates = [
    ['Sok Pisey', 'sok.pisey@example.com', 5, 'Excellent Quality Forklift', 'We purchased this forklift 6 months ago and it has been working perfectly. Very reliable and efficient. The service team was also very helpful during installation.', true],
    ['Chan Sophal', 'chan.sophal@example.com', 5, 'Great Value for Money', 'This equipment exceeded our expectations. The build quality is excellent and it handles heavy loads without any issues. Highly recommended!', true],
    ['Lim Srey Pich', 'lim.srey@example.com', 4, 'Good Product, Minor Issues', 'Overall a good product. We had a small issue with the battery initially, but customer service resolved it quickly. Very satisfied now.', true],
    ['Meas Ratha', 'meas.ratha@example.com', 5, 'Perfect for Our Warehouse', 'This equipment fits perfectly in our warehouse operations. Easy to operate and maintain. The team at S3V Group provided excellent support.', false],
    ['Kim Sopheak', 'kim.sopheak@example.com', 5, 'Outstanding Performance', 'We use this daily in our distribution center. It\'s powerful, reliable, and has significantly improved our productivity. Great investment!', true],
];

$reviewCount = 0;
foreach ($productIds as $product) {
    $numReviews = rand(2, 4);
    $selectedReviews = array_slice($reviewTemplates, 0, min($numReviews, count($reviewTemplates)));
    
    foreach ($selectedReviews as $template) {
        // Check if review already exists
        $exists = $db->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND customer_email = ?");
        $exists->execute([$product['id'], $template[1]]);
        
        if ($exists->rowCount() === 0) {
            $id = Id::prefixed('review');
            $stmt = $db->prepare("
                INSERT INTO product_reviews (id, product_id, customer_name, customer_email, rating, title, review_text, verified_purchase, status, createdAt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', NOW())
            ");
            
            $stmt->execute([
                $id, $product['id'], $template[0], $template[1], $template[2], $template[3], $template[4], $template[5] ? 1 : 0
            ]);
            $reviewCount++;
        }
    }
}

echo "  ✓ Added $reviewCount product reviews\n\n";

// ============================================
// STEP 8: DEMO SEARCH LOGS
// ============================================

echo "🔍 Step 8: Adding demo search logs...\n";

$searchQueries = [
    'forklift', 'electric forklift', 'diesel forklift', 'pallet racking', 'warehouse equipment',
    'material handling', 'conveyor belt', 'pallet jack', 'storage solutions', 'industrial equipment',
    'forklift parts', 'weighing scale', 'narrow aisle', 'LPG forklift', 'safety equipment'
];

$searchCount = 0;
for ($day = 0; $day < 14; $day++) {
    $queriesPerDay = rand(15, 30);
    
    for ($i = 0; $i < $queriesPerDay; $i++) {
        $query = $searchQueries[array_rand($searchQueries)];
        $id = Id::prefixed('search');
        $resultsCount = rand(5, 25);
        
        $stmt = $db->prepare("
            INSERT INTO search_logs (id, search_query, results_count, user_ip, createdAt)
            VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY) + INTERVAL ? SECOND)
        ");
        
        $stmt->execute([$id, $query, $resultsCount, '192.168.1.' . rand(1, 255), $day, rand(0, 86400)]);
        $searchCount++;
    }
}

echo "  ✓ Added $searchCount search logs\n\n";

// ============================================
// SUMMARY
// ============================================

echo "✅ Demo data entry completed!\n\n";

// Show comprehensive summary
$stats = [
    'Categories' => $db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'Products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'PUBLISHED'")->fetchColumn(),
    'Team Members' => $db->query("SELECT COUNT(*) FROM team_members WHERE status = 'ACTIVE'")->fetchColumn(),
    'Testimonials' => $db->query("SELECT COUNT(*) FROM testimonials WHERE status = 'PUBLISHED'")->fetchColumn(),
    'Sliders' => $db->query("SELECT COUNT(*) FROM sliders WHERE status = 'PUBLISHED'")->fetchColumn(),
    'Reviews' => $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'APPROVED'")->fetchColumn(),
    'FAQs' => $db->query("SELECT COUNT(*) FROM faqs WHERE status = 'PUBLISHED'")->fetchColumn(),
    'Search Logs' => $db->query("SELECT COUNT(*) FROM search_logs")->fetchColumn(),
];

echo "📊 Demo Database Summary:\n";
echo str_repeat("=", 40) . "\n";
foreach ($stats as $label => $count) {
    printf("  %-20s %s\n", $label . ":", number_format($count));
}
echo str_repeat("=", 40) . "\n";

echo "\n🎉 Your demo website is ready with comprehensive data!\n";
echo "✨ All duplicates have been cleaned up.\n";
echo "📈 All features have demo data.\n";

