<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\SliderRepository;
use App\Domain\Content\TestimonialRepository;

$db = getDB();
$sliderRepo = new SliderRepository($db);
$testimonialRepo = new TestimonialRepository($db);

echo "🌱 Seeding sample data...\n\n";

// Sample Hero Slider Slides with warehouse/factory equipment images
$sliderSlides = [
    [
        'title' => 'Warehouse & Factory Equipment Solutions',
        'subtitle' => 'Leading Supplier in Cambodia',
        'description' => 'Complete range of industrial equipment including forklifts, material handling systems, storage solutions, and warehouse automation.',
        'image_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1920&q=80',
        'link_url' => '/products.php',
        'link_text' => 'Explore Products',
        'button_color' => '#0b3a63',
        'priority' => 100,
        'status' => 'PUBLISHED',
    ],
    [
        'title' => 'Forklift Solutions',
        'subtitle' => 'Premium Quality Equipment',
        'description' => 'Electric, diesel, and gas forklifts from trusted manufacturers. Perfect for warehouses, factories, and distribution centers.',
        'image_url' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920&q=80',
        'link_url' => '/products.php?category=forklifts',
        'link_text' => 'View Forklifts',
        'button_color' => '#fa4f26',
        'priority' => 90,
        'status' => 'PUBLISHED',
    ],
    [
        'title' => 'Material Handling Systems',
        'subtitle' => 'Efficient & Reliable',
        'description' => 'Conveyor systems, pallet racking, shelving units, and storage solutions designed to optimize your operations.',
        'image_url' => 'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=1920&q=80',
        'link_url' => '/products.php?category=material-handling',
        'link_text' => 'Learn More',
        'button_color' => '#1a5a8a',
        'priority' => 80,
        'status' => 'PUBLISHED',
    ],
    [
        'title' => 'Professional Installation & Service',
        'subtitle' => 'Expert Support',
        'description' => 'Our experienced team provides installation, maintenance, and repair services to keep your operations running smoothly.',
        'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80',
        'link_url' => '/quote.php',
        'link_text' => 'Request Service',
        'button_color' => '#0b3a63',
        'priority' => 70,
        'status' => 'PUBLISHED',
    ],
    [
        'title' => 'Industrial Storage Solutions',
        'subtitle' => 'Maximize Your Space',
        'description' => 'Custom storage solutions including pallet racking, mezzanine floors, and automated storage systems for maximum efficiency.',
        'image_url' => 'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=1920&q=80',
        'link_url' => '/products.php?category=storage',
        'link_text' => 'View Storage Solutions',
        'button_color' => '#fa4f26',
        'priority' => 60,
        'status' => 'PUBLISHED',
    ],
];

echo "📸 Creating hero slider slides...\n";
foreach ($sliderSlides as $slide) {
    try {
        $sliderRepo->create($slide);
        echo "  ✅ Created: {$slide['title']}\n";
    } catch (Exception $e) {
        echo "  ⚠️  Skipped: {$slide['title']} - " . $e->getMessage() . "\n";
    }
}

// Sample Testimonials
$testimonials = [
    [
        'name' => 'Sok Pisey',
        'company' => 'ABC Logistics Co., Ltd.',
        'position' => 'Operations Manager',
        'content' => 'We\'ve been working with S3V Group for over 3 years. Their forklifts are reliable, and their service team is always quick to respond. Our warehouse operations have improved significantly since using their equipment.',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Sok+Pisey&background=0b3a63&color=fff&size=128',
        'featured' => true,
        'priority' => 100,
        'status' => 'PUBLISHED',
    ],
    [
        'name' => 'Chan Sophal',
        'company' => 'Cambodia Manufacturing Inc.',
        'position' => 'Factory Manager',
        'content' => 'The material handling systems we purchased have transformed our production efficiency. Installation was professional and the training provided was excellent. Highly recommend!',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Chan+Sophal&background=1a5a8a&color=fff&size=128',
        'featured' => true,
        'priority' => 90,
        'status' => 'PUBLISHED',
    ],
    [
        'name' => 'Lim Srey Pich',
        'company' => 'Royal Distribution Center',
        'position' => 'Warehouse Director',
        'content' => 'Excellent quality equipment at competitive prices. The pallet racking system has maximized our storage capacity, and their customer support is outstanding.',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Lim+Srey+Pich&background=fa4f26&color=fff&size=128',
        'featured' => true,
        'priority' => 80,
        'status' => 'PUBLISHED',
    ],
    [
        'name' => 'Meas Ratha',
        'company' => 'Phnom Penh Trading Co.',
        'position' => 'Supply Chain Manager',
        'content' => 'We purchased 5 electric forklifts last year. They\'re energy-efficient, easy to operate, and maintenance costs are low. Great investment for our business.',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Meas+Ratha&background=0b3a63&color=fff&size=128',
        'featured' => true,
        'priority' => 70,
        'status' => 'PUBLISHED',
    ],
    [
        'name' => 'Heng Sokunthea',
        'company' => 'Modern Factory Solutions',
        'position' => 'CEO',
        'content' => 'S3V Group provided complete warehouse automation solutions for our new facility. Their team was professional, knowledgeable, and delivered on time. We\'re very satisfied!',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Heng+Sokunthea&background=1a5a8a&color=fff&size=128',
        'featured' => true,
        'priority' => 60,
        'status' => 'PUBLISHED',
    ],
    [
        'name' => 'Kong Vannak',
        'company' => 'Southeast Distribution',
        'position' => 'Logistics Coordinator',
        'content' => 'Fast delivery, professional installation, and ongoing support. The conveyor system has reduced our handling time by 40%. Thank you S3V Group!',
        'rating' => 5,
        'avatar' => 'https://ui-avatars.com/api/?name=Kong+Vannak&background=fa4f26&color=fff&size=128',
        'featured' => true,
        'priority' => 50,
        'status' => 'PUBLISHED',
    ],
];

echo "\n💬 Creating testimonials...\n";
foreach ($testimonials as $testimonial) {
    try {
        $testimonialRepo->create($testimonial);
        echo "  ✅ Created: {$testimonial['name']} - {$testimonial['company']}\n";
    } catch (Exception $e) {
        echo "  ⚠️  Skipped: {$testimonial['name']} - " . $e->getMessage() . "\n";
    }
}

echo "\n✨ Sample data seeding completed!\n";
echo "\n📋 Summary:\n";
echo "  - Hero Slider Slides: " . count($sliderSlides) . "\n";
echo "  - Testimonials: " . count($testimonials) . "\n";
echo "\n💡 Next steps:\n";
echo "  1. Visit /admin/sliders.php to manage slider slides\n";
echo "  2. Visit /admin/testimonials.php to manage testimonials\n";
echo "  3. View the homepage to see the hero slider and testimonials\n";
echo "  4. Newsletter widget is already available in the footer\n";

