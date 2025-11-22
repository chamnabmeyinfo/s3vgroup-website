<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;
use App\Support\Id;

$pdo = Connection::getInstance();

echo "🌱 Starting database seeding...\n\n";

try {
    $pdo->beginTransaction();

    // Check if categories exist, if not the migration hasn't run
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($categoryCount === 0) {
        echo "⚠️  No categories found. Please run migrations first: php bin/migrate.php migrate\n";
        exit(1);
    }

    echo "📦 Adding sample products...\n";

    // Get category IDs
    $categories = $pdo->query("SELECT id, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $catMap = [];
    foreach ($categories as $cat) {
        $catMap[$cat['slug']] = $cat['id'];
    }

    $products = [
        [
            'name' => 'Toyota 8FGU25 Electric Forklift',
            'slug' => 'toyota-8fgu25-electric-forklift',
            'sku' => 'FL-TOY-8FGU25',
            'summary' => 'High-performance electric forklift ideal for indoor warehouse operations. Zero emissions, quiet operation.',
            'description' => 'The Toyota 8FGU25 is a reliable electric forklift designed for indoor material handling. Features advanced AC power system, ergonomic operator compartment, and excellent visibility. Perfect for food processing, pharmaceutical, and clean room environments.',
            'specs' => [
                'capacity' => '2500 kg',
                'lift_height' => '6.0 m',
                'power_type' => 'Electric',
                'battery_voltage' => '48V',
                'mast_type' => 'Triplex',
                'wheelbase' => '1650 mm',
            ],
            'highlights' => [
                'Zero emissions operation',
                'Quiet AC power system',
                'Ergonomic operator compartment',
                'Excellent forward visibility',
                'Low maintenance costs',
            ],
            'price' => 28500.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['forklifts'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
        [
            'name' => 'Hyundai 25D-7 Diesel Forklift',
            'slug' => 'hyundai-25d7-diesel-forklift',
            'sku' => 'FL-HYN-25D7',
            'summary' => 'Powerful diesel forklift for heavy-duty outdoor applications. Reliable and versatile.',
            'description' => 'Built for rugged outdoor conditions, the Hyundai 25D-7 diesel forklift delivers exceptional performance and durability. Features a robust engine, high lifting capacity, and excellent stability on uneven terrain.',
            'specs' => [
                'capacity' => '2500 kg',
                'lift_height' => '7.0 m',
                'power_type' => 'Diesel',
                'engine' => '4-cylinder, 33 HP',
                'transmission' => 'Automatic',
                'tire_type' => 'Pneumatic',
            ],
            'highlights' => [
                'Heavy-duty construction',
                'Excellent outdoor performance',
                'Powerful diesel engine',
                'Automatic transmission',
                'Long service intervals',
            ],
            'price' => 32000.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['forklifts'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
        [
            'name' => 'Manual Pallet Jack 2500kg',
            'slug' => 'manual-pallet-jack-2500kg',
            'sku' => 'MJ-MAN-2500',
            'summary' => 'Standard manual pallet jack for efficient material transport. Lightweight and easy to operate.',
            'description' => 'Perfect for warehouse and distribution centers. This manual pallet jack features durable construction, smooth hydraulic lifting, and ergonomic handle design.',
            'specs' => [
                'capacity' => '2500 kg',
                'fork_length' => '1220 mm',
                'fork_width' => '540 mm',
                'lowered_height' => '85 mm',
                'lifted_height' => '200 mm',
                'weight' => '78 kg',
            ],
            'highlights' => [
                'Lightweight and portable',
                'Smooth hydraulic operation',
                'Ergonomic handle',
                'Durable steel construction',
                'Low maintenance',
            ],
            'price' => 450.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['material-handling'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
        [
            'name' => 'Heavy Duty Steel Shelving System',
            'slug' => 'heavy-duty-steel-shelving-system',
            'sku' => 'ST-SHV-HD-01',
            'summary' => 'Modular steel shelving system for maximum storage capacity and organization.',
            'description' => 'Versatile shelving system that can be configured to fit any warehouse space. Features adjustable shelf heights, heavy-duty steel construction, and easy assembly.',
            'specs' => [
                'shelf_capacity' => '500 kg per shelf',
                'max_height' => '2.4 m',
                'shelf_depth' => '457 mm',
                'shelf_width' => '914 mm',
                'material' => 'Powder-coated steel',
                'adjustable_levels' => 'Yes',
            ],
            'highlights' => [
                'Modular design',
                'Adjustable shelf heights',
                'Heavy-duty capacity',
                'Easy assembly',
                'Space-efficient',
            ],
            'price' => 1200.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['storage-solutions'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
        [
            'name' => 'Safety Barrier Fence 10m',
            'slug' => 'safety-barrier-fence-10m',
            'sku' => 'SF-BAR-10M',
            'summary' => 'Portable safety barrier system for warehouse traffic management and accident prevention.',
            'description' => 'Protect equipment and personnel with this portable safety barrier fence. Easy to install, relocate, and configure to your needs. Highly visible yellow color with reflective strips.',
            'specs' => [
                'length' => '10 meters',
                'height' => '1.2 meters',
                'material' => 'Steel with PVC coating',
                'color' => 'Safety yellow',
                'panels' => '5 x 2m panels',
                'posts' => '6 posts included',
            ],
            'highlights' => [
                'Highly visible safety yellow',
                'Portable and relocatable',
                'Durable construction',
                'Easy installation',
                'Reflective strips included',
            ],
            'price' => 850.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['safety-equipment'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
        [
            'name' => 'Plastic Storage Bins Set (10 pcs)',
            'slug' => 'plastic-storage-bins-set',
            'sku' => 'WB-BIN-PL-10',
            'summary' => 'Stackable plastic storage bins for efficient warehouse organization.',
            'description' => 'Organize your warehouse with these durable, stackable plastic bins. Suitable for parts, tools, and small items. Features smooth surfaces, rounded corners, and ergonomic handles.',
            'specs' => [
                'quantity' => '10 pieces',
                'bin_dimensions' => '600 x 400 x 200 mm',
                'material' => 'High-density polyethylene',
                'color' => 'Blue',
                'stackable' => 'Yes',
                'lid_included' => 'Optional',
            ],
            'highlights' => [
                'Stackable design',
                'Durable HDPE material',
                'Smooth surfaces',
                'Easy to clean',
                'Ergonomic handles',
            ],
            'price' => 125.00,
            'status' => 'PUBLISHED',
            'categoryId' => $catMap['warehouse-accessories'] ?? null,
            'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
        ],
    ];

    $insertProduct = $pdo->prepare("
        INSERT INTO products (
            id, name, slug, sku, summary, description, specs, heroImage, price, status,
            highlights, categoryId, createdAt, updatedAt
        ) VALUES (
            :id, :name, :slug, :sku, :summary, :description, :specs, :heroImage, :price, :status,
            :highlights, :categoryId, NOW(), NOW()
        )
    ");

    $productCount = 0;
    foreach ($products as $product) {
        // Skip if product with same slug exists
        $check = $pdo->prepare("SELECT id FROM products WHERE slug = :slug");
        $check->execute([':slug' => $product['slug']]);
        if ($check->fetch()) {
            echo "  ⏭️  Skipping {$product['name']} (already exists)\n";
            continue;
        }

        $productId = Id::prefixed('prod');
        $insertProduct->execute([
            ':id' => $productId,
            ':name' => $product['name'],
            ':slug' => $product['slug'],
            ':sku' => $product['sku'],
            ':summary' => $product['summary'],
            ':description' => $product['description'],
            ':specs' => json_encode($product['specs'], JSON_UNESCAPED_UNICODE),
            ':heroImage' => $product['heroImage'],
            ':price' => $product['price'],
            ':status' => $product['status'],
            ':highlights' => json_encode($product['highlights'], JSON_UNESCAPED_UNICODE),
            ':categoryId' => $product['categoryId'],
        ]);
        $productCount++;
        echo "  ✅ Added: {$product['name']}\n";
    }

    echo "\n💬 Adding sample quote requests...\n";

    $insertQuote = $pdo->prepare("
        INSERT INTO quote_requests (
            id, companyName, contactName, email, phone, message, items, status, source, createdAt, updatedAt
        ) VALUES (
            :id, :companyName, :contactName, :email, :phone, :message, :items, 'NEW', :source, NOW(), NOW()
        )
    ");

    $quotes = [
        [
            'companyName' => 'Phnom Penh Logistics Co.',
            'contactName' => 'Sok Pisey',
            'email' => 'sok.pisey@pplogistics.com',
            'phone' => '+855 12 345 678',
            'message' => 'Looking for electric forklifts for our new warehouse. Need 3 units for indoor operations.',
            'items' => [
                ['id' => 'toyota-8fgu25-electric-forklift', 'name' => 'Toyota 8FGU25 Electric Forklift', 'quantity' => 3, 'notes' => 'Need delivery by next month'],
            ],
            'source' => 'website',
        ],
        [
            'companyName' => 'Cambodia Manufacturing Ltd.',
            'contactName' => 'Chan Sopheap',
            'email' => 'chan.sopheap@cambodiamfg.com',
            'phone' => '+855 23 456 789',
            'message' => 'Interested in diesel forklifts for outdoor material handling. Please provide pricing and specifications.',
            'items' => [
                ['id' => 'hyundai-25d7-diesel-forklift', 'name' => 'Hyundai 25D-7 Diesel Forklift', 'quantity' => 2, 'notes' => 'For outdoor use'],
            ],
            'source' => 'website',
        ],
        [
            'companyName' => 'Siem Reap Storage Solutions',
            'contactName' => 'Kim Vanna',
            'email' => 'vanna@siemreapstorage.kh',
            'phone' => '+855 63 789 012',
            'message' => 'Need storage solutions for our warehouse. Looking for shelving systems and storage bins.',
            'items' => [
                ['id' => 'heavy-duty-steel-shelving-system', 'name' => 'Heavy Duty Steel Shelving System', 'quantity' => 5],
                ['id' => 'plastic-storage-bins-set', 'name' => 'Plastic Storage Bins Set', 'quantity' => 20],
            ],
            'source' => 'website',
        ],
    ];

    $quoteCount = 0;
    foreach ($quotes as $quote) {
        $quoteId = Id::prefixed('quote');
        $insertQuote->execute([
            ':id' => $quoteId,
            ':companyName' => $quote['companyName'],
            ':contactName' => $quote['contactName'],
            ':email' => $quote['email'],
            ':phone' => $quote['phone'],
            ':message' => $quote['message'],
            ':items' => json_encode($quote['items'], JSON_UNESCAPED_UNICODE),
            ':source' => $quote['source'],
        ]);
        $quoteCount++;
        echo "  ✅ Added quote from: {$quote['companyName']}\n";
    }

    $pdo->commit();

    echo "\n✨ Seeding completed successfully!\n";
    echo "   📦 Products added: {$productCount}\n";
    echo "   💬 Quotes added: {$quoteCount}\n";
    echo "\n🎉 You can now view the data at:\n";
    echo "   - Homepage: http://localhost:8080/\n";
    echo "   - Products: http://localhost:8080/products.php\n";
    echo "   - Admin: http://localhost:8080/admin/\n\n";

} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

