<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\CategoryRepository;
use App\Support\Id;

$db = getDB();
$productRepo = new ProductRepository($db);
$categoryRepo = new CategoryRepository($db);

echo "🏭 Seeding warehouse & factory equipment products...\n\n";

// Get or create categories
$categories = $categoryRepo->all();
$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat['slug']] = $cat['id'];
}

// Create categories if they don't exist
$categoryData = [
    ['slug' => 'forklifts', 'name' => 'Forklifts', 'description' => 'Electric, diesel, and gas forklifts'],
    ['slug' => 'material-handling', 'name' => 'Material Handling', 'description' => 'Conveyors, pallet jacks, and handling equipment'],
    ['slug' => 'storage-racking', 'name' => 'Storage & Racking', 'description' => 'Pallet racking, shelving, and storage solutions'],
    ['slug' => 'loading-equipment', 'name' => 'Loading Equipment', 'description' => 'Loading docks, ramps, and dock equipment'],
    ['slug' => 'safety-equipment', 'name' => 'Safety Equipment', 'description' => 'Safety barriers, signs, and protective equipment'],
];

echo "📂 Setting up categories...\n";
foreach ($categoryData as $catData) {
    if (!isset($categoryMap[$catData['slug']])) {
        try {
            $category = $categoryRepo->create($catData);
            $categoryMap[$catData['slug']] = $category['id'];
            echo "  ✅ Created category: {$catData['name']}\n";
        } catch (Exception $e) {
            echo "  ⚠️  Category {$catData['name']}: " . $e->getMessage() . "\n";
        }
    }
}

// Warehouse & Factory Equipment Products with reliable image URLs
$products = [
    // Forklifts
    [
        'name' => 'Electric Forklift 3.5 Ton',
        'slug' => 'electric-forklift-3-5-ton',
        'sku' => 'FL-EL-3500',
        'summary' => 'High-performance electric forklift with 3.5 ton capacity. Perfect for indoor warehouse operations with zero emissions.',
        'description' => 'The Electric Forklift 3.5 Ton is designed for efficient material handling in warehouse and distribution centers. Features include advanced battery management system, ergonomic operator compartment, and excellent maneuverability. Ideal for food processing, pharmaceutical, and clean room environments.',
        'heroImage' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&h=600&fit=crop&q=80',
        'price' => 28500.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['forklifts'] ?? null,
        'specs' => [
            'Capacity' => '3.5 Ton',
            'Power Source' => 'Electric Battery',
            'Mast Height' => '3-6 meters',
            'Lift Speed' => '0.5 m/s',
            'Max Travel Speed' => '15 km/h',
        ],
        'highlights' => [
            'Zero emissions - perfect for indoor use',
            'Low noise operation',
            'Advanced battery management system',
            'Ergonomic operator compartment',
            'Excellent maneuverability',
        ],
    ],
    [
        'name' => 'Diesel Forklift 5 Ton',
        'slug' => 'diesel-forklift-5-ton',
        'sku' => 'FL-DS-5000',
        'summary' => 'Powerful diesel forklift with 5 ton lifting capacity. Built for heavy-duty outdoor applications.',
        'description' => 'The Diesel Forklift 5 Ton is engineered for demanding outdoor material handling tasks. Features a powerful diesel engine, robust construction, and excellent stability. Perfect for construction sites, lumber yards, and heavy industrial applications.',
        'heroImage' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=600&fit=crop&q=80',
        'price' => 32000.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['forklifts'] ?? null,
        'specs' => [
            'Capacity' => '5 Ton',
            'Power Source' => 'Diesel Engine',
            'Mast Height' => '4-7 meters',
            'Lift Speed' => '0.6 m/s',
            'Max Travel Speed' => '18 km/h',
        ],
        'highlights' => [
            'Powerful diesel engine',
            'Heavy-duty construction',
            'Excellent for outdoor use',
            'High lifting capacity',
            'Durable and reliable',
        ],
    ],
    [
        'name' => 'LPG Forklift 2.5 Ton',
        'slug' => 'lpg-forklift-2-5-ton',
        'sku' => 'FL-LPG-2500',
        'summary' => 'Versatile LPG forklift suitable for both indoor and outdoor operations. Clean burning fuel option.',
        'description' => 'The LPG Forklift 2.5 Ton offers the flexibility to work both indoors and outdoors. LPG fuel provides cleaner emissions than diesel while offering similar power. Ideal for applications requiring versatility and cost-effective operation.',
        'heroImage' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop&q=80',
        'price' => 24000.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['forklifts'] ?? null,
        'specs' => [
            'Capacity' => '2.5 Ton',
            'Power Source' => 'LPG Gas',
            'Mast Height' => '3-5 meters',
            'Lift Speed' => '0.5 m/s',
            'Max Travel Speed' => '16 km/h',
        ],
        'highlights' => [
            'Indoor and outdoor use',
            'Clean burning LPG fuel',
            'Cost-effective operation',
            'Quick refueling',
            'Versatile applications',
        ],
    ],

    // Material Handling
    [
        'name' => 'Pallet Jack 2.5 Ton',
        'slug' => 'pallet-jack-2-5-ton',
        'sku' => 'MJ-PJ-2500',
        'summary' => 'Heavy-duty manual pallet jack with 2.5 ton capacity. Essential warehouse equipment.',
        'description' => 'The Pallet Jack 2.5 Ton is a reliable manual handling solution for moving palletized loads. Features include ergonomic handle, smooth hydraulic lift, and durable construction. Perfect for warehouses, retail stores, and distribution centers.',
        'heroImage' => 'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=800&h=600&fit=crop&q=80',
        'price' => 850.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['material-handling'] ?? null,
        'specs' => [
            'Capacity' => '2.5 Ton',
            'Type' => 'Manual Pallet Jack',
            'Fork Length' => '1200mm',
            'Lowered Height' => '85mm',
            'Lifted Height' => '200mm',
        ],
        'highlights' => [
            'Heavy-duty construction',
            'Ergonomic handle design',
            'Smooth hydraulic operation',
            'Easy maintenance',
            'Affordable solution',
        ],
    ],
    [
        'name' => 'Electric Pallet Jack',
        'slug' => 'electric-pallet-jack',
        'sku' => 'MJ-EPJ-2000',
        'summary' => 'Battery-powered electric pallet jack for effortless material handling. Increases productivity.',
        'description' => 'The Electric Pallet Jack eliminates manual pushing and pulling, reducing operator fatigue. Features intuitive controls, powerful battery, and excellent maneuverability. Perfect for high-volume warehouses and distribution centers.',
        'heroImage' => 'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=800&h=600&fit=crop&q=80',
        'price' => 3200.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['material-handling'] ?? null,
        'specs' => [
            'Capacity' => '2 Ton',
            'Type' => 'Electric Pallet Jack',
            'Battery' => '24V Lead Acid',
            'Travel Speed' => '6 km/h',
            'Lift Speed' => '0.3 m/s',
        ],
        'highlights' => [
            'Battery-powered operation',
            'Reduces operator fatigue',
            'Increases productivity',
            'Intuitive controls',
            'Long battery life',
        ],
    ],
    [
        'name' => 'Belt Conveyor System',
        'slug' => 'belt-conveyor-system',
        'sku' => 'CNV-BELT-10M',
        'summary' => 'Customizable belt conveyor system for automated material transport. Various lengths available.',
        'description' => 'The Belt Conveyor System provides efficient material transport for production lines and warehouses. Features include adjustable speed, durable construction, and modular design. Can be customized to meet specific requirements.',
        'heroImage' => 'https://images.unsplash.com/photo-1469362102473-8622cfb973cd?w=800&h=600&fit=crop&q=80',
        'price' => 4500.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['material-handling'] ?? null,
        'specs' => [
            'Belt Width' => '400mm - 1200mm',
            'Length' => 'Customizable',
            'Speed' => 'Adjustable 0-30 m/min',
            'Load Capacity' => 'Up to 50 kg/m',
            'Power' => '0.75kW - 3kW',
        ],
        'highlights' => [
            'Modular design',
            'Adjustable speed',
            'Customizable length',
            'Durable construction',
            'Easy installation',
        ],
    ],

    // Storage & Racking
    [
        'name' => 'Pallet Racking System',
        'slug' => 'pallet-racking-system',
        'sku' => 'RK-PALLET-STD',
        'summary' => 'Heavy-duty pallet racking system for maximizing warehouse storage capacity. Easy to install.',
        'description' => 'The Pallet Racking System maximizes vertical storage space in warehouses. Features adjustable beam heights, high load capacity, and easy assembly. Compatible with forklifts and pallet jacks for efficient loading and unloading.',
        'heroImage' => 'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=800&h=600&fit=crop&q=80',
        'price' => 180.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['storage-racking'] ?? null,
        'specs' => [
            'Load Capacity' => 'Up to 3000 kg per level',
            'Height' => 'Up to 12 meters',
            'Material' => 'High-grade steel',
            'Beam Spacing' => 'Adjustable',
            'Finish' => 'Powder coated',
        ],
        'highlights' => [
            'Maximizes storage space',
            'High load capacity',
            'Easy to assemble',
            'Adjustable heights',
            'Forklift compatible',
        ],
    ],
    [
        'name' => 'Cantilever Racking',
        'slug' => 'cantilever-racking',
        'sku' => 'RK-CANT-STD',
        'summary' => 'Specialized cantilever racking for long and bulky items like lumber, pipes, and panels.',
        'description' => 'Cantilever Racking is designed for storing long, bulky items that cannot be stored on standard pallet racking. Features single or double-sided configurations, adjustable arms, and high load capacity. Perfect for lumber yards, pipe storage, and panel materials.',
        'heroImage' => 'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=800&h=600&fit=crop&q=80',
        'price' => 250.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['storage-racking'] ?? null,
        'specs' => [
            'Load Capacity' => 'Up to 5000 kg per arm',
            'Arm Length' => '800mm - 2000mm',
            'Height' => 'Up to 10 meters',
            'Material' => 'Heavy-duty steel',
            'Configuration' => 'Single or double-sided',
        ],
        'highlights' => [
            'For long/bulky items',
            'Adjustable arm heights',
            'High load capacity',
            'Versatile configuration',
            'Easy access to items',
        ],
    ],
    [
        'name' => 'Industrial Shelving Unit',
        'slug' => 'industrial-shelving-unit',
        'sku' => 'SH-IND-4LV',
        'summary' => 'Heavy-duty industrial shelving units for organized storage of smaller items and parts.',
        'description' => 'Industrial Shelving Units provide organized storage for smaller items, parts, and tools. Features adjustable shelf heights, high load capacity, and open design for easy access. Perfect for workshops, warehouses, and manufacturing facilities.',
        'heroImage' => 'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=800&h=600&fit=crop&q=80',
        'price' => 450.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['storage-racking'] ?? null,
        'specs' => [
            'Shelf Capacity' => 'Up to 500 kg per shelf',
            'Number of Shelves' => '4-6 levels',
            'Width' => '900mm - 1800mm',
            'Depth' => '450mm - 600mm',
            'Height' => 'Adjustable up to 2400mm',
        ],
        'highlights' => [
            'Adjustable shelf heights',
            'High load capacity',
            'Organized storage',
            'Easy access design',
            'Durable construction',
        ],
    ],

    // Loading Equipment
    [
        'name' => 'Loading Dock Leveler',
        'slug' => 'loading-dock-leveler',
        'sku' => 'LD-LEVELER-HY',
        'summary' => 'Hydraulic loading dock leveler for safe and efficient truck loading/unloading operations.',
        'description' => 'The Loading Dock Leveler bridges the gap between truck and dock, ensuring safe material handling. Features hydraulic operation, safety edges, and durable construction. Essential equipment for warehouses with truck loading operations.',
        'heroImage' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&fit=crop&q=80',
        'price' => 3200.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['loading-equipment'] ?? null,
        'specs' => [
            'Platform Size' => '2000mm x 2400mm',
            'Capacity' => 'Up to 6800 kg',
            'Operation' => 'Hydraulic',
            'Lift Height' => 'Up to 300mm',
            'Material' => 'Steel with non-slip surface',
        ],
        'highlights' => [
            'Safe truck loading',
            'Hydraulic operation',
            'Durable construction',
            'Safety features',
            'Easy maintenance',
        ],
    ],
    [
        'name' => 'Portable Loading Ramp',
        'slug' => 'portable-loading-ramp',
        'sku' => 'LD-RAMP-AL',
        'summary' => 'Aluminum portable loading ramp for versatile loading and unloading operations.',
        'description' => 'The Portable Loading Ramp provides a lightweight, portable solution for loading operations. Made from aluminum for durability and portability. Perfect for temporary loading operations, smaller vehicles, and versatile applications.',
        'heroImage' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&fit=crop&q=80',
        'price' => 650.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['loading-equipment'] ?? null,
        'specs' => [
            'Length' => '2400mm - 3600mm',
            'Width' => '1200mm - 1800mm',
            'Capacity' => 'Up to 6000 kg',
            'Material' => 'Aluminum',
            'Weight' => 'Lightweight, portable',
        ],
        'highlights' => [
            'Lightweight aluminum',
            'Portable design',
            'Easy to transport',
            'Durable construction',
            'Versatile applications',
        ],
    ],

    // Safety Equipment
    [
        'name' => 'Safety Barrier Post',
        'slug' => 'safety-barrier-post',
        'sku' => 'SF-BARRIER-100',
        'summary' => 'Heavy-duty safety barrier posts for protecting equipment and designated areas.',
        'description' => 'Safety Barrier Posts protect equipment, machinery, and designated areas from vehicle impact. Features high-visibility colors, impact-resistant construction, and flexible design. Essential for warehouse and factory safety.',
        'heroImage' => 'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=800&h=600&fit=crop&q=80',
        'price' => 95.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['safety-equipment'] ?? null,
        'specs' => [
            'Height' => '1000mm',
            'Diameter' => '200mm',
            'Material' => 'Steel with rubber base',
            'Color' => 'High-visibility yellow/black',
            'Impact Rating' => 'High impact resistance',
        ],
        'highlights' => [
            'High visibility',
            'Impact resistant',
            'Flexible design',
            'Easy installation',
            'Protects equipment',
        ],
    ],
    [
        'name' => 'Industrial Safety Sign Set',
        'slug' => 'industrial-safety-sign-set',
        'sku' => 'SF-SIGN-SET',
        'summary' => 'Comprehensive set of industrial safety signs for warehouse and factory compliance.',
        'description' => 'Industrial Safety Sign Set includes essential safety signage for warehouses and factories. Features durable materials, clear messaging, and compliance with safety standards. Includes warning signs, directional signs, and informational signs.',
        'heroImage' => 'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=800&h=600&fit=crop&q=80',
        'price' => 180.00,
        'status' => 'PUBLISHED',
        'categoryId' => $categoryMap['safety-equipment'] ?? null,
        'specs' => [
            'Material' => 'Weather-resistant vinyl',
            'Size' => '300mm x 400mm',
            'Quantity' => '20 signs per set',
            'Languages' => 'Bilingual (English/Khmer)',
            'Mounting' => 'Adhesive or screws',
        ],
        'highlights' => [
            'Comprehensive set',
            'Weather resistant',
            'Bilingual support',
            'Easy installation',
            'Safety compliant',
        ],
    ],
];

echo "\n📦 Creating products...\n";
$created = 0;
$skipped = 0;

foreach ($products as $productData) {
    // Check if product already exists
    try {
        $existing = $productRepo->findBySlug($productData['slug']);
        if ($existing) {
            echo "  ⏭️  Skipped: {$productData['name']} (already exists)\n";
            $skipped++;
            continue;
        }
    } catch (Exception $e) {
        // Slug not found, continue
    }

    // Validate category
    if (!$productData['categoryId']) {
        echo "  ⚠️  Skipped: {$productData['name']} (category not found)\n";
        $skipped++;
        continue;
    }

    try {
        // Encode JSON fields
        $productData['specs'] = json_encode($productData['specs'], JSON_UNESCAPED_UNICODE);
        $productData['highlights'] = json_encode($productData['highlights'], JSON_UNESCAPED_UNICODE);
        
        $productRepo->create($productData);
        echo "  ✅ Created: {$productData['name']}\n";
        $created++;
    } catch (Exception $e) {
        echo "  ⚠️  Error creating {$productData['name']}: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

echo "\n✨ Product seeding completed!\n";
echo "   ✅ Created: {$created} products\n";
echo "   ⏭️  Skipped: {$skipped} products\n";
echo "\n💡 View products at:\n";
echo "   - Products Page: http://localhost:8080/products.php\n";
echo "   - Admin Panel: http://localhost:8080/admin/products.php\n";

