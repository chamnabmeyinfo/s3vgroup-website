<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$categorySlug = $_GET['category'] ?? null;
$products = getAllProducts($db, $categorySlug);
$categories = getAllCategories($db);

$pageTitle = 'Forklifts';
$pageDescription = 'Browse our complete selection of forklifts';

include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-[#0b3a63] mb-4">Our Product Catalog</h1>
        <p class="text-gray-600">Browse our complete selection of warehouse and factory equipment</p>
    </div>

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
    <div class="mb-8 flex flex-wrap gap-2">
        <a href="/products.php" class="px-4 py-2 rounded-md <?php echo !$categorySlug ? 'bg-[#0b3a63] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
            All Categories
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/products.php?category=<?php echo urlencode($cat['slug']); ?>" 
               class="px-4 py-2 rounded-md <?php echo $categorySlug === $cat['slug'] ? 'bg-[#0b3a63] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <?php echo e($cat['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <?php if (!empty($products)): ?>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                    <?php if ($product['heroImage']): ?>
                        <img src="<?php echo e($product['heroImage']); ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-6">
                        <div class="mb-2">
                            <?php if ($product['category_name']): ?>
                                <span class="text-xs text-gray-500"><?php echo e($product['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-semibold text-[#0b3a63] mb-2"><?php echo e($product['name']); ?></h3>
                        <?php if ($product['summary']): ?>
                            <p class="text-gray-600 mb-4 text-sm"><?php echo e($product['summary']); ?></p>
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
    <?php else: ?>
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">No products found.</p>
            <a href="/products.php" class="mt-4 inline-block px-6 py-2 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a]">
                View All Products
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
