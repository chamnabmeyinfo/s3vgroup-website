<?php
session_start();
// Check ae-load.php first, then wp-load.php as fallback
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

$db = getDB();

// Handle toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $featureKey = $_POST['feature_key'] ?? '';
    $enabled = $_POST['enabled'] === '1' ? 1 : 0;
    
    // Check if feature exists
    $exists = $db->prepare("SELECT id FROM optional_features WHERE feature_key = ?");
    $exists->execute([$featureKey]);
    
    if ($exists->rowCount() > 0) {
        $stmt = $db->prepare("UPDATE optional_features SET enabled = ? WHERE feature_key = ?");
        $stmt->execute([$enabled, $featureKey]);
    } else {
        // Create feature entry
        $id = 'feature_' . uniqid();
        $stmt = $db->prepare("INSERT INTO optional_features (id, feature_key, feature_name, enabled) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $featureKey, ucwords(str_replace('_', ' ', $featureKey)), $enabled]);
    }
    
    header('Location: /admin/optional-features.php');
    exit;
}

// Define available optional features
$availableFeatures = [
    'multi_language' => [
        'name' => 'Multi-Language Support',
        'description' => 'Enable multiple languages (Khmer, English, etc.)',
        'category' => 'Localization',
        'icon' => '🌐',
    ],
    'live_chat' => [
        'name' => 'Live Chat Integration',
        'description' => 'Add live chat support (requires third-party service)',
        'category' => 'Communication',
        'icon' => '💬',
    ],
    'social_auto_post' => [
        'name' => 'Social Media Auto-Post',
        'description' => 'Automatically post new content to social media',
        'category' => 'Marketing',
        'icon' => '📱',
    ],
    'api_management' => [
        'name' => 'API Management',
        'description' => 'REST API for third-party integrations',
        'category' => 'Integration',
        'icon' => '🔌',
    ],
    'advanced_reporting' => [
        'name' => 'Advanced Reporting',
        'description' => 'Detailed analytics and custom reports',
        'category' => 'Analytics',
        'icon' => '📊',
    ],
    'customer_portal' => [
        'name' => 'Customer Portal',
        'description' => 'Customer account management and order tracking',
        'category' => 'E-commerce',
        'icon' => '👤',
    ],
    'wishlist' => [
        'name' => 'Product Wishlist',
        'description' => 'Allow customers to save favorite products',
        'category' => 'E-commerce',
        'icon' => '❤️',
    ],
    'product_comparison' => [
        'name' => 'Product Comparison',
        'description' => 'Side-by-side product comparison tool',
        'category' => 'E-commerce',
        'icon' => '⚖️',
    ],
    'inventory_tracking' => [
        'name' => 'Inventory Tracking',
        'description' => 'Real-time stock level monitoring',
        'category' => 'Inventory',
        'icon' => '📦',
    ],
    'order_management' => [
        'name' => 'Order Management',
        'description' => 'Full order lifecycle management',
        'category' => 'E-commerce',
        'icon' => '📋',
    ],
    'woocommerce_csv_import' => [
        'name' => 'WooCommerce CSV Import',
        'description' => 'Import products from WooCommerce CSV export',
        'category' => 'Integration',
        'icon' => '🛒',
    ],
    'wordpress_sql_import' => [
        'name' => 'WordPress SQL Import',
        'description' => 'Import products directly from WordPress/WooCommerce database',
        'category' => 'Integration',
        'icon' => '🗄️',
    ],
];

// Get enabled features from database
$enabledFeatures = [];
$dbFeatures = $db->query("SELECT feature_key, enabled FROM optional_features")->fetchAll(PDO::FETCH_ASSOC);
foreach ($dbFeatures as $feature) {
    $enabledFeatures[$feature['feature_key']] = (bool)$feature['enabled'];
}

// Group by category
$featuresByCategory = [];
foreach ($availableFeatures as $key => $feature) {
    $category = $feature['category'];
    if (!isset($featuresByCategory[$category])) {
        $featuresByCategory[$category] = [];
    }
    $featuresByCategory[$category][] = ['key' => $key, ...$feature];
}

$pageTitle = 'Optional Features';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Optional Features</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Enable or disable additional website features</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-blue-900 mb-1.5">About Optional Features</div>
                <div class="text-sm text-blue-800 leading-relaxed">
                    These features are available but not required for your website. Enable only the features you need.
                    Some features may require additional configuration or third-party services.
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($featuresByCategory as $category => $features): ?>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900"><?php echo e($category); ?></h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($features as $feature): 
                        $isEnabled = $enabledFeatures[$feature['key']] ?? false;
                    ?>
                        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-5 hover:border-amber-300 hover:shadow-md transition-all <?php echo $isEnabled ? 'ring-2 ring-amber-200 border-amber-300' : ''; ?>">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center flex-shrink-0 text-2xl">
                                    <?php echo $feature['icon']; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 mb-1"><?php echo e($feature['name']); ?></div>
                                    <div class="text-sm text-gray-600 leading-relaxed"><?php echo e($feature['description']); ?></div>
                                </div>
                            </div>
                            
                            <?php if ($isEnabled): ?>
                                <div class="mb-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Currently Enabled
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="feature_key" value="<?php echo e($feature['key']); ?>">
                                <input type="hidden" name="enabled" value="<?php echo $isEnabled ? '0' : '1'; ?>">
                                <button type="submit" class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm hover:shadow <?php echo $isEnabled ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' : 'bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-700 hover:to-amber-800 shadow-md hover:shadow-lg'; ?>">
                                    <?php echo $isEnabled ? 'Disable Feature' : 'Enable Feature'; ?>
                                </button>
                            </form>
                            
                            <?php if ($isEnabled): ?>
                                <?php if ($feature['key'] === 'woocommerce_csv_import'): ?>
                                    <a href="/admin/woocommerce-import.php" class="block mt-3 text-center text-sm text-amber-700 hover:text-amber-800 font-medium hover:underline">
                                        → Go to Import Page
                                    </a>
                                <?php elseif ($feature['key'] === 'wordpress_sql_import'): ?>
                                    <a href="/admin/wordpress-sql-import.php" class="block mt-3 text-center text-sm text-amber-700 hover:text-amber-800 font-medium hover:underline">
                                        → Go to Import Page
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

