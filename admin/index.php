<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\CategoryRepository;
use App\Domain\Quotes\QuoteRequestRepository;
use App\Domain\Content\TeamMemberRepository;
use App\Domain\Content\TestimonialRepository;
use App\Domain\Content\NewsletterRepository;
use App\Domain\Content\SliderRepository;

try {
    $db = getDB();
} catch (Exception $e) {
    error_log('Admin dashboard database error: ' . $e->getMessage());
    die('Database connection failed. Please check your configuration.');
}

// Get statistics - with error handling
try {
    $productRepo = new ProductRepository($db);
    $categoryRepo = new CategoryRepository($db);
    $quoteRepo = new QuoteRequestRepository($db);
    $teamRepo = new TeamMemberRepository($db);
    $testimonialRepo = new TestimonialRepository($db);
    $newsletterRepo = new NewsletterRepository($db);
    $sliderRepo = new SliderRepository($db);
} catch (Exception $e) {
    error_log('Admin dashboard repository error: ' . $e->getMessage());
    // Continue with empty repos to show dashboard
    $productRepo = null;
    $categoryRepo = null;
    $quoteRepo = null;
    $teamRepo = null;
    $testimonialRepo = null;
    $newsletterRepo = null;
    $sliderRepo = null;
}

// Get product counts using direct queries (ProductRepository doesn't have all()/published() methods)
try {
    // Check if table exists first
    $tableCheck = $db->query("SHOW TABLES LIKE 'products'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        $productTotalStmt = $db->query("SELECT COUNT(*) FROM products");
        $productPublishedStmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'PUBLISHED'");
        $productTotal = (int) ($productTotalStmt ? $productTotalStmt->fetchColumn() : 0);
        $productPublished = (int) ($productPublishedStmt ? $productPublishedStmt->fetchColumn() : 0);
    } else {
        $productTotal = 0;
        $productPublished = 0;
    }
} catch (Exception $e) {
    error_log('Product count error: ' . $e->getMessage());
    $productTotal = 0;
    $productPublished = 0;
}

// Get quote counts - use direct query for better performance
try {
    // Check if table exists first
    $tableCheck = $db->query("SHOW TABLES LIKE 'quote_requests'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        $quoteTotalStmt = $db->query("SELECT COUNT(*) FROM quote_requests");
        $quoteNewStmt = $db->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'NEW'");
        $quoteTotal = (int) ($quoteTotalStmt ? $quoteTotalStmt->fetchColumn() : 0);
        $quoteNew = (int) ($quoteNewStmt ? $quoteNewStmt->fetchColumn() : 0);
    } else {
        $quoteTotal = 0;
        $quoteNew = 0;
    }
} catch (Exception $e) {
    error_log('Quote count error: ' . $e->getMessage());
    $quoteTotal = 0;
    $quoteNew = 0;
}

$stats = [
    'products' => [
        'total' => $productTotal,
        'published' => $productPublished,
        'label' => 'Products',
        'icon' => '📦',
        'color' => 'blue',
        'url' => '/admin/products.php',
    ],
    'categories' => [
        'total' => $categoryRepo ? count($categoryRepo->all()) : 0,
        'published' => $categoryRepo ? count($categoryRepo->all()) : 0,
        'label' => 'Categories',
        'icon' => '🏷️',
        'color' => 'purple',
        'url' => '/admin/categories.php',
    ],
    'quotes' => [
        'total' => $quoteTotal,
        'new' => $quoteNew,
        'label' => 'Quote Requests',
        'icon' => '📋',
        'color' => 'orange',
        'url' => '/admin/quotes.php',
    ],
    'team' => [
        'total' => $teamRepo ? count($teamRepo->all()) : 0,
        'active' => $teamRepo ? count($teamRepo->active()) : 0,
        'label' => 'Team Members',
        'icon' => '👥',
        'color' => 'green',
        'url' => '/admin/team.php',
    ],
    'testimonials' => [
        'total' => $testimonialRepo ? count($testimonialRepo->all()) : 0,
        'published' => $testimonialRepo ? count($testimonialRepo->published()) : 0,
        'label' => 'Testimonials',
        'icon' => '⭐',
        'color' => 'yellow',
        'url' => '/admin/testimonials.php',
    ],
    'newsletter' => [
        'total' => $newsletterRepo ? count($newsletterRepo->all()) : 0,
        'active' => $newsletterRepo ? count($newsletterRepo->active()) : 0,
        'label' => 'Newsletter Subscribers',
        'icon' => '📧',
        'color' => 'pink',
        'url' => '/admin/newsletter.php',
    ],
    'sliders' => [
        'total' => $sliderRepo ? count($sliderRepo->all()) : 0,
        'published' => $sliderRepo ? count($sliderRepo->published()) : 0,
        'label' => 'Hero Slider Slides',
        'icon' => '🖼️',
        'color' => 'indigo',
        'url' => '/admin/sliders.php',
    ],
];

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-semibold text-[#0b3a63]">Dashboard</h1>
        <p class="text-sm text-gray-600 mt-1">Welcome back, <?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?>! Here's an overview of your site.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($stats as $key => $stat): ?>
            <a href="<?php echo e($stat['url']); ?>" class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-3xl"><?php echo e($stat['icon']); ?></div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900"><?php echo e($stat['total']); ?></div>
                        <?php if (isset($stat['published'])): ?>
                            <div class="text-xs text-gray-500"><?php echo e($stat['published']); ?> published</div>
                        <?php elseif (isset($stat['active'])): ?>
                            <div class="text-xs text-gray-500"><?php echo e($stat['active']); ?> active</div>
                        <?php elseif (isset($stat['new'])): ?>
                            <div class="text-xs text-orange-600 font-semibold"><?php echo e($stat['new']); ?> new</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-700"><?php echo e($stat['label']); ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Catalog Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>📦</span>
                <span>Catalog</span>
            </h2>
            <div class="space-y-2">
                <a href="/admin/products.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Manage Products</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
                <a href="/admin/categories.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Manage Categories</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
            </div>
        </div>

        <!-- Content Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>📄</span>
                <span>Content</span>
            </h2>
            <div class="space-y-2">
                <a href="/admin/company-story.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Company Story</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
                <a href="/admin/ceo-message.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">CEO Message</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
                <a href="/admin/team.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Team Members</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
            </div>
        </div>

        <!-- Marketing Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>📢</span>
                <span>Marketing</span>
            </h2>
            <div class="space-y-2">
                <a href="/admin/sliders.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Hero Slider</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
                <a href="/admin/testimonials.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Testimonials</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
                <a href="/admin/newsletter.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Newsletter</span>
                    <span class="text-xs text-gray-500">→</span>
                </a>
            </div>
        </div>

        <!-- Leads Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>📋</span>
                <span>Leads & Requests</span>
            </h2>
            <div class="space-y-2">
                <a href="/admin/quotes.php" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-700">Quote Requests</span>
                    <?php if ($stats['quotes']['new'] > 0): ?>
                        <span class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded-full">
                            <?php echo e($stats['quotes']['new']); ?> new
                        </span>
                    <?php else: ?>
                        <span class="text-xs text-gray-500">→</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Quote Requests</h2>
        <?php
        // Get recent quotes using paginate method
        try {
            $recentQuotes = $quoteRepo ? $quoteRepo->paginate([], 5, 0) : [];
        } catch (Exception $e) {
            error_log('Recent quotes error: ' . $e->getMessage());
            $recentQuotes = [];
        }
        ?>
        <?php if (empty($recentQuotes)): ?>
            <p class="text-sm text-gray-500">No quote requests yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentQuotes as $quote): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900"><?php echo e($quote['companyName']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($quote['contactName']); ?> • <?php echo date('M d, Y', strtotime($quote['createdAt'])); ?></div>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?php
                            echo match($quote['status']) {
                                'NEW' => 'bg-orange-100 text-orange-800',
                                'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                'RESOLVED' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        ?>">
                            <?php echo e($quote['status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="/admin/quotes.php" class="text-sm text-[#0b3a63] hover:underline">View all quote requests →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
