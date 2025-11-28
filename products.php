<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load Ant Elite bootstrap (ae-load.php)
if (file_exists(__DIR__ . '/ae-load.php')) {
    require_once __DIR__ . '/ae-load.php';
} else {
    require_once __DIR__ . '/wp-load.php';
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';

// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/functions.php')) {
    require_once __DIR__ . '/ae-includes/functions.php';
} elseif (file_exists(__DIR__ . '/wp-includes/functions.php')) {
    require_once __DIR__ . '/wp-includes/functions.php';
} elseif (file_exists(__DIR__ . '/includes/functions.php')) {
    require_once __DIR__ . '/includes/functions.php';
}

$db = getDB();
$categorySlug = $_GET['category'] ?? null;
$products = getAllProducts($db, $categorySlug);
$categories = getAllCategories($db);

$pageTitle = 'Product Catalog';
$pageDescription = 'Browse our complete selection of warehouse and factory equipment';
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

// Load header (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/header.php')) {
    include __DIR__ . '/ae-includes/header.php';
} elseif (file_exists(__DIR__ . '/wp-includes/header.php')) {
    include __DIR__ . '/wp-includes/header.php';
} elseif (file_exists(__DIR__ . '/includes/header.php')) {
    include __DIR__ . '/includes/header.php';
}
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Header -->
    <div class="mb-8 sm:mb-12 text-center sm:text-left fade-in-up">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-3" style="color: <?php echo e($primaryColor); ?>;">
            Our Product Catalog
        </h1>
        <p class="text-gray-600 text-base sm:text-lg max-w-2xl">
            Browse our complete selection of warehouse and factory equipment
        </p>
    </div>

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
    <div class="mb-8 sm:mb-12">
        <div class="flex flex-wrap gap-2 sm:gap-3 justify-center sm:justify-start">
            <a href="/products.php" 
               class="px-4 py-2 rounded-full text-sm font-medium transition-all hover:scale-105 transform <?php echo !$categorySlug ? 'text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>"
               style="<?php echo !$categorySlug ? "background-color: {$primaryColor};" : ''; ?>">
                All Categories
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/products.php?category=<?php echo urlencode($cat['slug']); ?>" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-all hover:scale-105 transform <?php echo $categorySlug === $cat['slug'] ? 'text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>"
                   style="<?php echo $categorySlug === $cat['slug'] ? "background-color: {$primaryColor};" : ''; ?>">
                    <?php echo e($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($products as $index => $product): ?>
                <div class="group rounded-xl border border-gray-200 bg-white shadow-sm hover-lift overflow-hidden animate-on-scroll flex flex-col image-hover-scale"
                     data-animation="fadeInUp"
                     data-delay="<?php echo ($index * 0.05); ?>">
                    <!-- Product Image -->
                    <div class="relative w-full bg-gray-100 overflow-hidden" style="aspect-ratio: 4/3;">
                        <?php if ($product['heroImage']): ?>
                            <img 
                                src="<?php echo e(fullImageUrl($product['heroImage'])); ?>" 
                                alt="<?php echo e($product['name']); ?>" 
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
                        <?php if ($product['category_name']): ?>
                            <span class="absolute top-3 left-3 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-gray-700 shadow-sm">
                                <?php echo e($product['category_name']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Product Content -->
                    <div class="p-4 sm:p-5 flex flex-col flex-grow">
                        <h3 class="text-lg sm:text-xl font-semibold mb-2 line-clamp-2 group-hover:text-[#1a5a8a] transition-colors" style="color: <?php echo e($primaryColor); ?>;">
                            <?php echo e($product['name']); ?>
                        </h3>
                        
                        <?php if ($product['summary']): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 flex-grow">
                                <?php echo e($product['summary']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($product['price']): ?>
                            <div class="mb-4">
                                <div class="text-xl sm:text-2xl font-bold" style="color: <?php echo e($primaryColor); ?>;">
                                    <?php echo display_price($product['price'], '$'); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 italic">Price on request</p>
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
    <?php else: ?>
        <div class="text-center py-16 sm:py-20 fade-in-up">
            <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-600 text-lg sm:text-xl mb-4">No products found in this category.</p>
            <a href="/products.php" 
               class="inline-block px-6 py-3 text-white rounded-lg font-medium transition-all hover:scale-105 transform shadow-md hover:shadow-lg"
               style="background-color: <?php echo e($primaryColor); ?>;">
                View All Products
            </a>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="/includes/css/products.css?v=<?php echo time(); ?>">
<style>
/* Line clamp utility for text truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Ensure aspect ratio container */
[style*="aspect-ratio"] {
    position: relative;
}

/* Prevent image stretching */
.product-card img {
    max-width: 100%;
    height: auto;
    object-fit: cover;
}
</style>

<?php
// Load footer (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/footer.php')) {
    include __DIR__ . '/ae-includes/footer.php';
} elseif (file_exists(__DIR__ . '/wp-includes/footer.php')) {
    include __DIR__ . '/wp-includes/footer.php';
} elseif (file_exists(__DIR__ . '/includes/footer.php')) {
    include __DIR__ . '/includes/footer.php';
}
?>
