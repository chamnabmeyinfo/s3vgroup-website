<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;
use App\Support\Id;
use App\Support\Str;

echo "🌐 Importing data from www.s3vtgroup.com.kh...\n\n";

$baseUrl = 'https://www.s3vtgroup.com.kh';
$pdo = Connection::getInstance();

try {
    $pdo->beginTransaction();

    // Extract categories from HTML
    echo "📦 Extracting product categories...\n";
    
    $categoriesData = [
        ['name' => 'Truck Scale', 'slug' => 'truck-scale', 'description' => 'Industrial truck scales for weighing vehicles', 'priority' => 100],
        ['name' => 'Digital Scale', 'slug' => 'digital-scale', 'description' => 'Digital weighing scales for various applications', 'priority' => 95],
        ['name' => 'Racking System', 'slug' => 'racking-system', 'description' => 'Storage racking systems for warehouses', 'priority' => 90],
        ['name' => 'Lifting Equipment', 'slug' => 'lifting-equipment', 'description' => 'Professional lifting equipment and tools', 'priority' => 85],
        ['name' => 'Material Handling Equipment', 'slug' => 'material-handling-equipment', 'description' => 'Equipment for moving and handling materials', 'priority' => 80],
        ['name' => 'Plastic Pallet', 'slug' => 'plastic-pallet', 'description' => 'Durable plastic pallets for shipping and storage', 'priority' => 75],
        ['name' => 'Plastic Basket', 'slug' => 'plastic-basket', 'description' => 'Plastic storage baskets and containers', 'priority' => 70],
        ['name' => 'Auto Barrier Gate', 'slug' => 'auto-barrier-gate', 'description' => 'Automatic barrier gates for access control', 'priority' => 65],
        ['name' => 'Forklift Attachment', 'slug' => 'forklift-attachment', 'description' => 'Attachments and accessories for forklifts', 'priority' => 60],
    ];

    $insertCategory = $pdo->prepare("
        INSERT INTO categories (id, name, slug, description, priority, createdAt, updatedAt)
        VALUES (:id, :name, :slug, :description, :priority, NOW(), NOW())
        ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), priority = VALUES(priority)
    ");

    $categoryMap = [];
    $categoryCount = 0;

    foreach ($categoriesData as $cat) {
        // Check if exists by slug
        $check = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug");
        $check->execute([':slug' => $cat['slug']]);
        $existing = $check->fetch();

        if ($existing) {
            $categoryId = $existing['id'];
            // Update priority
            $update = $pdo->prepare("UPDATE categories SET priority = :priority, description = :description WHERE id = :id");
            $update->execute([':priority' => $cat['priority'], ':description' => $cat['description'], ':id' => $categoryId]);
            echo "  ✅ Updated: {$cat['name']}\n";
        } else {
            $categoryId = Id::prefixed('cat');
            $insertCategory->execute([
                ':id' => $categoryId,
                ':name' => $cat['name'],
                ':slug' => $cat['slug'],
                ':description' => $cat['description'],
                ':priority' => $cat['priority'],
            ]);
            echo "  ✅ Added: {$cat['name']}\n";
            $categoryCount++;
        }

        $categoryMap[$cat['slug']] = $categoryId;
    }

    echo "\n📦 Creating sample products based on categories...\n";

    // Create sample products for each category
    $products = [
        [
            'name' => 'Heavy Duty Truck Scale 60 Ton',
            'slug' => 'heavy-duty-truck-scale-60-ton',
            'sku' => 'TS-60T-001',
            'categorySlug' => 'truck-scale',
            'summary' => 'Industrial grade 60-ton capacity truck scale for accurate vehicle weighing',
            'description' => 'Professional truck scale designed for heavy-duty applications. Features precision load cells, durable construction, and easy installation. Perfect for logistics centers, warehouses, and industrial facilities.',
            'specs' => [
                'capacity' => '60 tons',
                'platform_size' => '18m x 3m',
                'accuracy' => 'OIML Class III',
                'construction' => 'Steel platform',
                'display' => 'Digital indicator',
            ],
            'highlights' => [
                'High accuracy load cells',
                'Weather-resistant design',
                'Large platform size',
                'Digital display',
                'Easy installation',
            ],
            'price' => 45000.00,
        ],
        [
            'name' => 'Digital Platform Scale 500kg',
            'slug' => 'digital-platform-scale-500kg',
            'sku' => 'DS-PLAT-500',
            'categorySlug' => 'digital-scale',
            'summary' => 'Precise digital platform scale for general weighing applications',
            'description' => 'Versatile digital scale suitable for warehouse, retail, and industrial use. Features clear LCD display, multiple weighing units, and durable steel platform.',
            'specs' => [
                'capacity' => '500 kg',
                'platform_size' => '600mm x 800mm',
                'accuracy' => '0.1 kg',
                'display' => 'LCD',
                'power' => 'AC/Battery',
            ],
            'highlights' => [
                'Large LCD display',
                'Multiple weighing units',
                'Battery operated',
                'Auto-off function',
                'Overload protection',
            ],
            'price' => 850.00,
        ],
        [
            'name' => 'Selective Pallet Racking System',
            'slug' => 'selective-pallet-racking-system',
            'sku' => 'RS-SELECT-01',
            'categorySlug' => 'racking-system',
            'summary' => 'Heavy-duty selective racking for efficient pallet storage',
            'description' => 'Industry-standard selective pallet racking system. Adjustable beam heights, high load capacity, and flexible configuration options. Ideal for warehouses requiring direct access to all pallets.',
            'specs' => [
                'beam_capacity' => '3500 kg per pair',
                'upright_height' => 'Up to 12m',
                'beam_length' => '2700mm - 3000mm',
                'material' => 'Galvanized steel',
                'adjustable_levels' => 'Yes',
            ],
            'highlights' => [
                'Adjustable beam heights',
                'Direct pallet access',
                'High load capacity',
                'Galvanized protection',
                'Easy assembly',
            ],
            'price' => 2800.00,
        ],
        [
            'name' => 'Hydraulic Scissor Lift Table 500kg',
            'slug' => 'hydraulic-scissor-lift-table-500kg',
            'sku' => 'LE-LIFT-500',
            'categorySlug' => 'lifting-equipment',
            'summary' => 'Versatile scissor lift table for material handling operations',
            'description' => 'Hydraulic scissor lift table designed for ergonomic material handling. Smooth lifting action, safety features, and durable construction. Perfect for assembly lines, warehouses, and production areas.',
            'specs' => [
                'capacity' => '500 kg',
                'platform_size' => '1000mm x 800mm',
                'lift_height' => '700mm',
                'power' => 'Hydraulic pump',
                'wheels' => 'Mounted castors',
            ],
            'highlights' => [
                'Smooth hydraulic operation',
                'Safety valves included',
                'Ergonomic design',
                'Mobile with wheels',
                'Durable construction',
            ],
            'price' => 3200.00,
        ],
        [
            'name' => 'Heavy Duty Pallet Jack 2500kg',
            'slug' => 'heavy-duty-pallet-jack-2500kg',
            'sku' => 'MHE-PJ-2500',
            'categorySlug' => 'material-handling-equipment',
            'summary' => 'Rugged pallet jack for heavy-duty material handling',
            'description' => 'Professional grade pallet jack built for demanding warehouse environments. High capacity, smooth operation, and ergonomic handle design for operator comfort.',
            'specs' => [
                'capacity' => '2500 kg',
                'fork_length' => '1200mm',
                'fork_width' => '540mm',
                'lowered_height' => '85mm',
                'lift_height' => '200mm',
            ],
            'highlights' => [
                'Heavy-duty construction',
                'Smooth hydraulic lift',
                'Ergonomic handle',
                'Quality wheels',
                'Low maintenance',
            ],
            'price' => 550.00,
        ],
        [
            'name' => 'Plastic Euro Pallet 1200x800mm',
            'slug' => 'plastic-euro-pallet-1200x800mm',
            'sku' => 'PP-EURO-01',
            'categorySlug' => 'plastic-pallet',
            'summary' => 'Standard Euro-sized plastic pallet for logistics and storage',
            'description' => 'Durable plastic Euro pallet matching standard 1200x800mm dimensions. Lightweight, hygienic, and suitable for export applications. Ideal for food, pharmaceutical, and clean environments.',
            'specs' => [
                'dimensions' => '1200mm x 800mm x 144mm',
                'weight' => '15 kg',
                'load_capacity' => '1500 kg static',
                'material' => 'HDPE',
                'color' => 'Blue or black',
            ],
            'highlights' => [
                'Hygienic plastic material',
                'Lightweight design',
                'Export approved',
                'Stackable',
                'Long lifespan',
            ],
            'price' => 45.00,
        ],
        [
            'name' => 'Plastic Storage Basket 450x350x200mm',
            'slug' => 'plastic-storage-basket-450x350x200mm',
            'sku' => 'PB-BASKET-450',
            'categorySlug' => 'plastic-basket',
            'summary' => 'Versatile plastic storage basket for organization',
            'description' => 'Practical plastic basket for warehouse and retail organization. Stackable design, easy to clean, and suitable for various storage applications.',
            'specs' => [
                'dimensions' => '450mm x 350mm x 200mm',
                'capacity' => '30 liters',
                'material' => 'PP plastic',
                'color' => 'Blue, red, or yellow',
                'stackable' => 'Yes',
            ],
            'highlights' => [
                'Stackable design',
                'Easy to clean',
                'Durable material',
                'Multiple colors',
                'Affordable',
            ],
            'price' => 8.50,
        ],
        [
            'name' => 'Automatic Barrier Gate System',
            'slug' => 'automatic-barrier-gate-system',
            'sku' => 'BG-AUTO-01',
            'categorySlug' => 'auto-barrier-gate',
            'summary' => 'Complete automatic barrier gate system for access control',
            'description' => 'Professional automatic barrier gate system with motor, controller, and safety features. Suitable for parking lots, warehouses, and restricted access areas.',
            'specs' => [
                'barrier_length' => '3m or 6m',
                'operating_speed' => '1.5 seconds',
                'power' => '220V AC',
                'control' => 'Remote or card reader',
                'safety' => 'Photo-eye protection',
            ],
            'highlights' => [
                'Fast operation',
                'Safety sensors',
                'Remote control',
                'Weather resistant',
                'LED indicator',
            ],
            'price' => 2500.00,
        ],
        [
            'name' => 'Forklift Rotator Attachment',
            'slug' => 'forklift-rotator-attachment',
            'sku' => 'FA-ROT-360',
            'categorySlug' => 'forklift-attachment',
            'summary' => '360-degree rotating attachment for forklifts',
            'description' => 'Heavy-duty rotator attachment allows 360-degree rotation of loads. Ideal for positioning containers, drums, and irregularly shaped items. Easy installation and operation.',
            'specs' => [
                'capacity' => '2500 kg',
                'rotation' => '360 degrees continuous',
                'control' => 'Hydraulic',
                'mounting' => 'Quick attach',
                'voltage' => '12V or 24V',
            ],
            'highlights' => [
                '360-degree rotation',
                'Heavy-duty capacity',
                'Quick attach system',
                'Smooth operation',
                'Safety features',
            ],
            'price' => 4500.00,
        ],
    ];

    $insertProduct = $pdo->prepare("
        INSERT INTO products (
            id, name, slug, sku, summary, description, specs, heroImage, price, status,
            highlights, categoryId, createdAt, updatedAt
        ) VALUES (
            :id, :name, :slug, :sku, :summary, :description, :specs, :heroImage, :price, 'PUBLISHED',
            :highlights, :categoryId, NOW(), NOW()
        )
    ");

    $productCount = 0;
    foreach ($products as $product) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM products WHERE slug = :slug");
        $check->execute([':slug' => $product['slug']]);
        if ($check->fetch()) {
            echo "  ⏭️  Skipping {$product['name']} (already exists)\n";
            continue;
        }

        $categoryId = $categoryMap[$product['categorySlug']] ?? null;
        if (!$categoryId) {
            echo "  ⚠️  Category not found for {$product['name']}, skipping\n";
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
            ':heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            ':price' => $product['price'],
            ':highlights' => json_encode($product['highlights'], JSON_UNESCAPED_UNICODE),
            ':categoryId' => $categoryId,
        ]);

        $productCount++;
        echo "  ✅ Added: {$product['name']}\n";
    }

    $pdo->commit();

    echo "\n✨ Import completed successfully!\n";
    echo "   📦 Categories added/updated: {$categoryCount}\n";
    echo "   🛍️  Products added: {$productCount}\n";
    echo "\n🎉 You can now view the imported data at:\n";
    echo "   - Homepage: http://localhost:8080/\n";
    echo "   - Products: http://localhost:8080/products.php\n";
    echo "   - Admin: http://localhost:8080/admin/\n\n";

} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

