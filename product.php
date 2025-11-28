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
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: /products.php');
    exit;
}

$product = getProductBySlug($db, $slug);

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $product['name'];
$pageDescription = $product['summary'] ?? $product['description'] ?? '';

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

<!-- Breadcrumbs -->
<div class="bg-gray-50 border-b border-gray-200 py-4">
    <div class="container mx-auto px-4">
        <div class="breadcrumbs">
            <a href="/">Home</a>
            <span class="breadcrumbs-separator">/</span>
            <a href="/products.php">Products</a>
            <span class="breadcrumbs-separator">/</span>
            <?php if ($product['category_name']): ?>
                <a href="/products.php?category=<?php echo urlencode($product['category_slug'] ?? ''); ?>"><?php echo e($product['category_name']); ?></a>
                <span class="breadcrumbs-separator">/</span>
            <?php endif; ?>
            <span class="text-gray-600"><?php echo e($product['name']); ?></span>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
        <!-- Product Image -->
        <div class="sticky top-20 animate-on-scroll"
             data-animation="fadeInLeft">
            <div class="product-gallery">
                <div class="product-gallery-main">
                    <?php if ($product['heroImage']): ?>
                        <img 
                            src="<?php echo e(fullImageUrl($product['heroImage'])); ?>" 
                            alt="<?php echo e($product['name']); ?>" 
                            class="w-full h-full object-cover"
                            loading="eager"
                            id="main-product-image"
                        >
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="sr-only">No image available</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="animate-on-scroll"
             data-animation="fadeInRight">
            <div class="mb-4">
                <?php if ($product['category_name']): ?>
                    <span class="tag tag-primary"><?php echo e($product['category_name']); ?></span>
                <?php endif; ?>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight" style="color: <?php echo e($primaryColor); ?>;">
                <?php echo e($product['name']); ?>
            </h1>
            
            <?php if ($product['price']): ?>
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <div class="text-4xl md:text-5xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>;">
                        <?php echo display_price($product['price'], '$'); ?>
                    </div>
                    <p class="text-sm text-gray-500">
                        <?php if ($product['price'] < 10000): ?>
                            Approximate price - Contact us for exact pricing
                        <?php else: ?>
                            Price may vary based on configuration
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <p class="text-2xl font-semibold text-gray-600">Price on Request</p>
                    <p class="text-sm text-gray-500">Contact us for pricing information</p>
                </div>
            <?php endif; ?>

            <?php if ($product['summary']): ?>
                <div class="mb-8">
                    <p class="text-lg md:text-xl text-gray-700 leading-relaxed"><?php echo e($product['summary']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($product['highlights'])): ?>
                <div class="mb-8 product-info-card">
                    <h3 class="text-2xl font-bold mb-4" style="color: <?php echo e($primaryColor); ?>;">Key Features</h3>
                    <ul class="space-y-3">
                        <?php foreach ($product['highlights'] as $highlight): ?>
                            <li class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700 text-lg"><?php echo e($highlight); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                <a href="/quote.php?product=<?php echo urlencode($product['slug']); ?>" class="flex-1 btn-primary text-white text-center font-semibold py-4 text-lg rounded-full shadow-lg hover:shadow-xl transition-all" style="background-color: <?php echo e($primaryColor); ?>;">
                    Request Quote
                </a>
                <a href="/contact.php" class="px-8 py-4 border-2 rounded-full font-semibold text-center transition-all hover:bg-gray-50" style="border-color: <?php echo e($primaryColor); ?>; color: <?php echo e($primaryColor); ?>;">
                    Contact Us
                </a>
            </div>

            <!-- Quick Info -->
            <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-gray-700 font-medium">Full Warranty Included</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-gray-700 font-medium">Fast Delivery Available</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="text-gray-700 font-medium">Professional Support & Service</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if ($product['description']): ?>
        <div class="mt-16 animate-on-scroll"
             data-animation="fadeInUp">
            <div class="product-info-card hover-lift">
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>;">Product Description</h2>
                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    <p class="text-lg whitespace-pre-line"><?php echo nl2br(e($product['description'])); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Specifications -->
    <?php if (!empty($product['specs']) && is_array($product['specs'])): ?>
        <div class="mt-12 animate-on-scroll"
             data-animation="scaleIn">
            <div class="product-info-card hover-lift">
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>;">Technical Specifications</h2>
                <div class="product-specs-grid">
                    <?php foreach ($product['specs'] as $key => $value): ?>
                        <div class="spec-item">
                            <div class="spec-label"><?php echo e(ucwords(str_replace('_', ' ', $key))); ?></div>
                            <div class="spec-value"><?php echo e($value); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

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
