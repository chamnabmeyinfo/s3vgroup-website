<?php
// Load bootstrap FIRST to ensure env() function is available
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

// Ensure e() function exists
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
    }
}

startAdminSession();

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

// Get counts safely with error handling
$categoryTotal = 0;
$categoryPublished = 0;
if ($categoryRepo) {
    try {
        $categoryTotal = count($categoryRepo->all());
        // Categories don't have a status field, so all categories are considered "published"
        // If categories had status, this would call: $categoryRepo->published()
        $categoryPublished = $categoryTotal;
    } catch (Exception $e) {
        error_log('Categories count error: ' . $e->getMessage());
    }
}

$teamTotal = 0;
$teamActive = 0;
if ($teamRepo) {
    try {
        $teamTotal = count($teamRepo->all());
        $teamActive = count($teamRepo->active());
    } catch (Exception $e) {
        error_log('Team count error: ' . $e->getMessage());
    }
}

$testimonialTotal = 0;
$testimonialPublished = 0;
if ($testimonialRepo) {
    try {
        $testimonialTotal = count($testimonialRepo->all());
        $testimonialPublished = count($testimonialRepo->published());
    } catch (Exception $e) {
        error_log('Testimonials count error: ' . $e->getMessage());
    }
}

$newsletterTotal = 0;
$newsletterActive = 0;
if ($newsletterRepo) {
    try {
        $newsletterTotal = count($newsletterRepo->all());
        $newsletterActive = count($newsletterRepo->active());
    } catch (Exception $e) {
        error_log('Newsletter count error: ' . $e->getMessage());
    }
}

$sliderTotal = 0;
$sliderPublished = 0;
if ($sliderRepo) {
    try {
        $sliderTotal = count($sliderRepo->all());
        $sliderPublished = count($sliderRepo->published());
    } catch (Exception $e) {
        error_log('Sliders count error: ' . $e->getMessage());
    }
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
        'total' => $categoryTotal,
        'published' => $categoryPublished,
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
        'total' => $teamTotal,
        'active' => $teamActive,
        'label' => 'Team Members',
        'icon' => '👥',
        'color' => 'green',
        'url' => '/admin/team.php',
    ],
    'testimonials' => [
        'total' => $testimonialTotal,
        'published' => $testimonialPublished,
        'label' => 'Testimonials',
        'icon' => '⭐',
        'color' => 'yellow',
        'url' => '/admin/testimonials.php',
    ],
    'newsletter' => [
        'total' => $newsletterTotal,
        'active' => $newsletterActive,
        'label' => 'Newsletter Subscribers',
        'icon' => '📧',
        'color' => 'pink',
        'url' => '/admin/newsletter.php',
    ],
    'sliders' => [
        'total' => $sliderTotal,
        'published' => $sliderPublished,
        'label' => 'Hero Slider Slides',
        'icon' => '🖼️',
        'color' => 'indigo',
        'url' => '/admin/sliders.php',
    ],
];

$pageTitle = 'Dashboard';

// Include header - errors are handled within header.php itself
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-container">
    <!-- Page Header -->
    <div class="admin-page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?>! Here's an overview of your site.</p>
    </div>

    <!-- Dashboard Layout -->
    <div class="admin-dashboard-layout">
        <!-- Stats Cards - Smart Grid -->
        <div class="admin-dashboard-stats">
            <div class="admin-stats-grid">
        <?php foreach ($stats as $key => $stat): ?>
            <a href="<?php echo e($stat['url']); ?>" class="admin-stat-card">
                <div class="admin-stat-card-content">
                    <div class="admin-stat-card-icon"><?php echo e($stat['icon']); ?></div>
                    <div class="admin-stat-card-value">
                        <div class="admin-stat-card-number"><?php echo e($stat['total']); ?></div>
                        <?php if (isset($stat['published'])): ?>
                            <div class="admin-stat-card-meta"><?php echo e($stat['published']); ?> published</div>
                        <?php elseif (isset($stat['active'])): ?>
                            <div class="admin-stat-card-meta"><?php echo e($stat['active']); ?> active</div>
                        <?php elseif (isset($stat['new'])): ?>
                            <div class="admin-stat-card-meta admin-stat-card-meta-warning"><?php echo e($stat['new']); ?> new</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="admin-stat-card-label"><?php echo e($stat['label']); ?></div>
            </a>
        <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions - Smart Grid -->
        <div class="admin-dashboard-content">
        <!-- Catalog -->
        <div class="admin-card">
            <div class="admin-section-header">
                <h2 class="admin-section-title">Catalog</h2>
            </div>
            <div class="admin-nav-list">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/products.php" class="admin-nav-link">
                    <span>Manage Products</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/categories.php" class="admin-nav-link">
                    <span>Manage Categories</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="admin-card">
            <div class="admin-section-header">
                <h2 class="admin-section-title">Content</h2>
            </div>
            <div class="admin-nav-list">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/pages.php" class="admin-nav-link">
                    <span>All Pages</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/team.php" class="admin-nav-link">
                    <span>Team Members</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Marketing -->
        <div class="admin-card">
            <div class="admin-section-header">
                <h2 class="admin-section-title">Marketing</h2>
            </div>
            <div class="admin-nav-list">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/sliders.php" class="admin-nav-link">
                    <span>Hero Slider</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/testimonials.php" class="admin-nav-link">
                    <span>Testimonials</span>
                    <svg class="admin-nav-link-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Recent Activity - Full Width -->
        <div class="admin-dashboard-full-width">
            <div class="admin-card">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Recent Quote Requests</h2>
        </div>
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
            <p class="admin-empty-state">No quote requests yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentQuotes as $quote): ?>
                        <tr>
                            <td><strong><?php echo e($quote['companyName']); ?></strong></td>
                            <td><?php echo e($quote['contactName']); ?></td>
                            <td class="admin-text-muted"><?php echo date('M d, Y', strtotime($quote['createdAt'])); ?></td>
                            <td>
                                <span class="admin-badge <?php
                                    echo match($quote['status']) {
                                        'NEW' => 'admin-badge-warning',
                                        'IN_PROGRESS' => 'admin-badge-info',
                                        'RESOLVED' => 'admin-badge-success',
                                        default => '',
                                    };
                                ?>">
                                    <?php echo e($quote['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="admin-table-actions">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/quotes.php" class="admin-btn admin-btn-secondary">View all quote requests</a>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
