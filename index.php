<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users, log them instead
ini_set('log_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$pageDescription = $siteConfig['description'] ?? 'S3V Group - Warehouse Equipment';

// Fetch featured categories - with error handling
try {
    $db = getDB();
    $allCategories = getFeaturedCategories($db, 12);
    $categories = array_slice($allCategories, 0, 12); // Limit to 12 for 3x4 grid
    $products = getFeaturedProducts($db, 6);
} catch (Exception $e) {
    // Log error but don't break the page
    error_log('Homepage error: ' . $e->getMessage());
    $allCategories = [];
    $categories = [];
    $products = [];
}

include __DIR__ . '/includes/header.php';
?>

<?php
require_once __DIR__ . '/bootstrap/app.php';
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

// Check if homepage builder is enabled and has sections (homepage has page_id = null)
$useHomepageBuilder = option('enable_homepage_builder', '0') === '1';
$homepagePageId = null; // Homepage uses null page_id

if ($useHomepageBuilder) {
    // Use homepage builder sections (pass null for homepage)
    $pageId = null; // Homepage
    $sections = include __DIR__ . '/includes/widgets/homepage-section-renderer.php';
    // If no sections returned or empty, fall back to default
    if (!$sections) {
        $useHomepageBuilder = false;
    }
}

if (!$useHomepageBuilder) {
    // Use default homepage sections
    ?>
    <!-- Hero Slider -->
    <?php include __DIR__ . '/includes/widgets/hero-slider.php'; ?>

<!-- Newsletter Section (if enabled) -->
<?php if (option('enable_newsletter', '1') === '1'): ?>
    <section class="py-20 bg-gradient-to-r" style="background: linear-gradient(to right, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
        <div class="container mx-auto px-4">
            <?php include __DIR__ . '/includes/widgets/newsletter-signup.php'; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Testimonials Widget (if enabled) -->
<?php if (option('enable_testimonials', '1') === '1'): ?>
    <?php include __DIR__ . '/includes/widgets/testimonials.php'; ?>
<?php endif; ?>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-6">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center animate-on-scroll hover-lift hover-glow"
                 data-animation="zoomIn">
                <svg class="h-12 w-12 mx-auto mb-4" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Wide Selection</h3>
                <p class="text-gray-600 text-sm">
                    Comprehensive range of warehouse and factory equipment from trusted brands
                </p>
            </div>
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center animate-on-scroll hover-lift hover-glow"
                         data-animation="zoomIn"
                         data-delay="0.1">
                <svg class="h-12 w-12 mx-auto mb-4" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Fast Delivery</h3>
                <p class="text-gray-600 text-sm">
                    Quick delivery and installation across Cambodia
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center animate-on-scroll hover-lift hover-glow"
                 data-animation="zoomIn"
                 data-delay="0.2">
                <svg class="h-12 w-12 mx-auto mb-4" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="font-semibold text-lg mb-2">Expert Service</h3>
                <p class="text-gray-600 text-sm">
                    Professional maintenance and repair services
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center animate-on-scroll hover-lift hover-glow"
                 data-animation="zoomIn"
                 data-delay="0.3">
                <svg class="h-12 w-12 mx-auto mb-4" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>;">
                Shop All Categories
            </h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 stagger-children">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $category): ?>
                    <a href="/products.php?category=<?php echo urlencode($category['slug']); ?>" 
                       class="category-item bg-white rounded-lg border border-gray-200 hover-lift p-4 flex items-center gap-3 animate-on-scroll group"
                       data-animation="fadeInUp"
                       data-delay="<?php echo ($index * 0.05); ?>">
                        <!-- Category Image/Icon -->
                        <div class="category-image-wrapper flex-shrink-0 w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-md overflow-hidden flex items-center justify-center relative">
                            <?php
                            // Get category image (icon or fallback to first product image)
                            $categoryImage = $category['icon'] ?? null;
                            if (!$categoryImage) {
                                try {
                                    $productStmt = $db->prepare('SELECT heroImage FROM products WHERE categoryId = :categoryId AND heroImage IS NOT NULL AND heroImage != "" AND status = "PUBLISHED" LIMIT 1');
                                    $productStmt->execute([':categoryId' => $category['id']]);
                                    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
                                    $categoryImage = $product['heroImage'] ?? null;
                                } catch (Exception $e) {
                                    // Ignore errors
                                }
                            }
                            
                            // Get first letter for icon fallback
                            $firstLetter = strtoupper(substr($category['name'], 0, 1));
                            ?>
                            
                            <!-- Loading Placeholder -->
                            <div class="category-icon-placeholder absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <div class="category-icon-loader">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Category Image -->
                            <?php if ($categoryImage): ?>
                                <img src="<?php echo e($categoryImage); ?>" 
                                     alt="<?php echo e($category['name']); ?>" 
                                     loading="lazy"
                                     class="category-image w-full h-full object-cover opacity-0 transition-opacity duration-300">
                            <?php else: ?>
                                <!-- Fallback Icon with Initial -->
                                <div class="category-fallback-icon w-full h-full flex items-center justify-center absolute inset-0 z-10">
                                    <div class="category-initial-badge w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md" 
                                         style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor ?? $primaryColor); ?>);">
                                        <?php echo e($firstLetter); ?>
                                    </div>
                                </div>
                                <style>
                                    .category-initial-badge {
                                        animation: fadeInScale 0.3s ease-out;
                                    }
                                    @keyframes fadeInScale {
                                        from {
                                            opacity: 0;
                                            transform: scale(0.8);
                                        }
                                        to {
                                            opacity: 1;
                                            transform: scale(1);
                                        }
                                    }
                                </style>
                            <?php endif; ?>
                        </div>
                        <!-- Category Label -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2" style="--hover-color: <?php echo e($primaryColor); ?>;">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default categories if database is empty -->
                <?php
                $defaultCategories = [
                    ['name' => 'Truck Scale', 'icon' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Digital Scale', 'icon' => 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Racking System', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Lifting Equipment', 'icon' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Material Handling Equipment', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Load Cell', 'icon' => 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Lightning Protection System', 'icon' => 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Mobile Conveyor', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                ];
                foreach ($defaultCategories as $index => $cat):
                ?>
                    <a href="/products.php" 
                       class="category-item bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all p-4 flex items-center gap-3 animate-on-scroll group"
                       style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                        <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center">
                            <img src="<?php echo e($cat['icon'] ?? ''); ?>" 
                                 alt="<?php echo e($cat['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2" style="--hover-color: <?php echo e($primaryColor); ?>;">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<?php if (!empty($products)): ?>
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 animate-on-scroll"
             data-animation="fadeInDown">
            <h2 class="text-4xl font-bold text-[#0b3a63] mb-4 text-reveal">Featured Products</h2>
            <p class="text-gray-600">Our most popular warehouse and factory equipment</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <?php foreach ($products as $index => $product): ?>
                <div class="group rounded-xl border border-gray-200 bg-white shadow-sm hover-lift overflow-hidden animate-on-scroll flex flex-col image-hover-scale"
                     data-animation="fadeInUp"
                     data-delay="<?php echo ($index * 0.05); ?>">
                    <!-- Product Image -->
                    <div class="relative w-full bg-gray-100 overflow-hidden" style="aspect-ratio: 4/3;">
                        <?php if ($product['heroImage']): ?>
                            <img 
                                src="<?php echo htmlspecialchars($product['heroImage']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Product Content -->
                    <div class="p-4 sm:p-5 flex flex-col flex-grow">
                        <h3 class="text-lg sm:text-xl font-semibold mb-2 line-clamp-2 group-hover:text-[#1a5a8a] transition-colors" style="color: <?php echo e($primaryColor); ?>;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <?php if ($product['summary']): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 flex-grow">
                                <?php echo htmlspecialchars($product['summary']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($product['price']): ?>
                            <div class="mb-4">
                                <div class="text-xl sm:text-2xl font-bold" style="color: <?php echo e($primaryColor); ?>;">
                                    <?php echo display_price($product['price'], '$'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="/product.php?slug=<?php echo urlencode($product['slug']); ?>" 
                           class="block w-full px-4 py-2.5 text-center text-white rounded-lg font-medium transition-all hover:scale-105 transform shadow-md hover:shadow-lg"
                           style="background-color: <?php echo e($primaryColor); ?>;"
                           onmouseover="this.style.backgroundColor='<?php echo e($secondaryColor); ?>'"
                           onmouseout="this.style.backgroundColor='<?php echo e($primaryColor); ?>'">
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
    <div class="container mx-auto px-4 text-center animate-on-scroll"
         data-animation="zoomIn">
        <h2 class="text-4xl font-bold mb-4 text-reveal">Ready to Equip Your Warehouse or Factory?</h2>
        <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto animate-on-scroll"
           data-animation="fadeInUp"
           data-delay="0.2">
            Contact our experts today for personalized recommendations and competitive pricing on all your industrial equipment needs
        </p>
        <div class="flex gap-4 justify-center stagger-children">
            <a href="/quote.php" class="px-6 py-3 bg-white text-[#0b3a63] rounded-md font-semibold hover:bg-gray-100 transition-colors btn-animate magnetic">
                Get Free Quote
            </a>
            <a href="/contact.php" class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors btn-animate magnetic">
                Contact Us
            </a>
        </div>
    </div>
</section>
<?php } ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
