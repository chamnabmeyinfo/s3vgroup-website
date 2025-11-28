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

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Configuration</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Optional Features</h1>
            <p class="text-sm text-gray-600">Enable or disable additional website features</p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="text-2xl">ℹ️</div>
            <div>
                <div class="font-semibold text-blue-900 mb-1">About Optional Features</div>
                <div class="text-sm text-blue-800">
                    These features are available but not required for your website. Enable only the features you need.
                    Some features may require additional configuration or third-party services.
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($featuresByCategory as $category => $features): ?>
        <div class="admin-card">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e($category); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($features as $feature): 
                    $isEnabled = $enabledFeatures[$feature['key']] ?? false;
                ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-[#0b3a63] transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl"><?php echo $feature['icon']; ?></div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900"><?php echo e($feature['name']); ?></div>
                                    <div class="text-sm text-gray-600 mt-1"><?php echo e($feature['description']); ?></div>
                                </div>
                            </div>
                        </div>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="feature_key" value="<?php echo e($feature['key']); ?>">
                            <input type="hidden" name="enabled" value="<?php echo $isEnabled ? '0' : '1'; ?>">
                            <button type="submit" class="admin-btn <?php echo $isEnabled ? 'admin-btn-danger' : 'admin-btn-primary'; ?> w-full">
                                <?php echo $isEnabled ? 'Disable' : 'Enable'; ?>
                            </button>
                        </form>
                        <?php if ($isEnabled): ?>
                            <div class="mt-2 text-xs text-green-600 font-medium">✓ Currently Enabled</div>
                            <?php if ($feature['key'] === 'woocommerce_csv_import'): ?>
                                <a href="/admin/woocommerce-import.php" class="block mt-2 text-center text-sm text-[#0b3a63] hover:underline font-medium">
                                    → Go to Import Page
                                </a>
                            <?php elseif ($feature['key'] === 'wordpress_sql_import'): ?>
                                <a href="/admin/wordpress-sql-import.php" class="block mt-2 text-center text-sm text-[#0b3a63] hover:underline font-medium">
                                    → Go to Import Page
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

