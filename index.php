<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$pageDescription = $siteConfig['description'];

// Fetch featured categories
$db = getDB();
$categories = getFeaturedCategories($db, 6);
$products = getFeaturedProducts($db, 6);

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-[#0b3a63] to-[#1a5a8a] text-white">
    <div class="container mx-auto px-4 py-24">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            <h1 class="text-5xl md:text-6xl font-bold">
                Warehouse & Factory Equipment Solutions
            </h1>
            <p class="text-xl text-gray-200 max-w-2xl mx-auto">
                Leading supplier of industrial equipment in Cambodia. Forklifts, material handling systems, storage solutions, and warehouse equipment.
            </p>
            <div class="flex gap-4 justify-center pt-4">
                <a href="/products.php" class="px-6 py-3 bg-white text-[#0b3a63] rounded-md font-semibold hover:bg-gray-100 transition-colors">
                    Browse Products
                </a>
                <a href="/quote.php" class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors">
                    Request Quote
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-6">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center">
                <svg class="h-12 w-12 text-[#0b3a63] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Wide Selection</h3>
                <p class="text-gray-600 text-sm">
                    Comprehensive range of warehouse and factory equipment from trusted brands
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center">
                <svg class="h-12 w-12 text-[#0b3a63] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Fast Delivery</h3>
                <p class="text-gray-600 text-sm">
                    Quick delivery and installation across Cambodia
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center">
                <svg class="h-12 w-12 text-[#0b3a63] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Expert Service</h3>
                <p class="text-gray-600 text-sm">
                    Professional maintenance and repair services
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center">
                <svg class="h-12 w-12 text-[#0b3a63] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Warranty</h3>
                <p class="text-gray-600 text-sm">
                    Comprehensive warranty and support packages
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-[#0b3a63] mb-4">
                Product Categories
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Explore our comprehensive range of warehouse and factory equipment
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow p-6">
                        <h3 class="text-xl font-semibold text-[#0b3a63] mb-3">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>
                        <?php if ($category['description']): ?>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($category['description']); ?></p>
                        <?php endif; ?>
                        <a href="/products.php?category=<?php echo urlencode($category['slug']); ?>" class="block w-full px-4 py-2 text-center border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            View Models →
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default categories if database is empty -->
                <?php
                $defaultCategories = [
                    ['name' => 'Forklifts', 'description' => 'Electric, diesel, and gas forklifts for all your material handling needs'],
                    ['name' => 'Material Handling', 'description' => 'Pallet jacks, hand trucks, and lifting equipment'],
                    ['name' => 'Storage Solutions', 'description' => 'Shelving, racks, and warehouse storage systems'],
                    ['name' => 'Industrial Equipment', 'description' => 'Conveyors, dock equipment, and factory machinery'],
                ];
                foreach ($defaultCategories as $cat):
                ?>
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow p-6">
                        <h3 class="text-xl font-semibold text-[#0b3a63] mb-3"><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($cat['description']); ?></p>
                        <a href="/products.php" class="block w-full px-4 py-2 text-center border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            View Products →
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<?php if (!empty($products)): ?>
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-[#0b3a63] mb-4">Featured Products</h2>
            <p class="text-gray-600">Our most popular warehouse and factory equipment</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                    <?php if ($product['heroImage']): ?>
                        <img src="<?php echo htmlspecialchars($product['heroImage']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-[#0b3a63] mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <?php if ($product['summary']): ?>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['summary']); ?></p>
                        <?php endif; ?>
                        <?php if ($product['price']): ?>
                            <p class="text-2xl font-bold text-[#0b3a63] mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                        <?php endif; ?>
                        <a href="/product.php?slug=<?php echo urlencode($product['slug']); ?>" class="block w-full px-4 py-2 text-center bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-20 bg-[#0b3a63] text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-4">Ready to Equip Your Warehouse or Factory?</h2>
        <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto">
            Contact our experts today for personalized recommendations and competitive pricing on all your industrial equipment needs
        </p>
        <div class="flex gap-4 justify-center">
            <a href="/quote.php" class="px-6 py-3 bg-white text-[#0b3a63] rounded-md font-semibold hover:bg-gray-100 transition-colors">
                Get Free Quote
            </a>
            <a href="/contact.php" class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors">
                Contact Us
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
