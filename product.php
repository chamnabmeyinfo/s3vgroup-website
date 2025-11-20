<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

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

include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="grid md:grid-cols-2 gap-12">
        <!-- Product Image -->
        <div>
            <?php if ($product['heroImage']): ?>
                <img src="<?php echo e($product['heroImage']); ?>" alt="<?php echo e($product['name']); ?>" class="w-full rounded-lg shadow-lg">
            <?php else: ?>
                <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">No image available</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div>
            <div class="mb-4">
                <?php if ($product['category_name']): ?>
                    <span class="text-sm text-gray-500"><?php echo e($product['category_name']); ?></span>
                <?php endif; ?>
            </div>
            <h1 class="text-4xl font-bold text-[#0b3a63] mb-4"><?php echo e($product['name']); ?></h1>
            
            <?php if ($product['price']): ?>
                <p class="text-3xl font-bold text-[#0b3a63] mb-6">$<?php echo number_format($product['price'], 2); ?></p>
            <?php endif; ?>

            <?php if ($product['summary']): ?>
                <p class="text-lg text-gray-700 mb-6"><?php echo e($product['summary']); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['highlights'])): ?>
                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-3">Key Features</h3>
                    <ul class="space-y-2">
                        <?php foreach ($product['highlights'] as $highlight): ?>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <?php echo e($highlight); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="flex gap-4">
                <a href="/quote.php?product=<?php echo urlencode($product['slug']); ?>" class="flex-1 px-6 py-3 bg-[#0b3a63] text-white text-center rounded-md hover:bg-[#1a5a8a] transition-colors font-semibold">
                    Request Quote
                </a>
                <a href="/contact.php" class="px-6 py-3 border-2 border-[#0b3a63] text-[#0b3a63] rounded-md hover:bg-[#0b3a63] hover:text-white transition-colors font-semibold">
                    Contact Us
                </a>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if ($product['description']): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-[#0b3a63] mb-4">Description</h2>
            <div class="prose max-w-none">
                <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br(e($product['description'])); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Specifications -->
    <?php if (!empty($product['specs']) && is_array($product['specs'])): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-[#0b3a63] mb-4">Specifications</h2>
            <div class="bg-gray-50 rounded-lg p-6">
                <dl class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($product['specs'] as $key => $value): ?>
                        <div>
                            <dt class="font-semibold text-gray-700"><?php echo e(ucfirst($key)); ?></dt>
                            <dd class="text-gray-600"><?php echo e($value); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
