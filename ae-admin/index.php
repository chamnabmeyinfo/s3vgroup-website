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
include __DIR__ . '/includes/header.php';
?>

<div>
    <!-- macOS-like Header -->
    <div class="mac-card" style="margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 600; margin: 0 0 8px 0; padding: 0; letter-spacing: -0.5px;">Dashboard</h1>
        <p style="margin: 0; color: var(--mac-text-secondary); font-size: 15px;">Welcome back, <?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?>! Here's an overview of your site.</p>
    </div>

    <!-- macOS-like Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php foreach ($stats as $key => $stat): ?>
            <a href="<?php echo e($stat['url']); ?>" class="mac-stat-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 36px; opacity: 0.8;"><?php echo e($stat['icon']); ?></div>
                    <div style="text-align: right;">
                        <div style="font-size: 32px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.5px;"><?php echo e($stat['total']); ?></div>
                        <?php if (isset($stat['published'])): ?>
                            <div style="font-size: 13px; color: var(--mac-text-secondary); margin-top: 2px;"><?php echo e($stat['published']); ?> published</div>
                        <?php elseif (isset($stat['active'])): ?>
                            <div style="font-size: 13px; color: var(--mac-text-secondary); margin-top: 2px;"><?php echo e($stat['active']); ?> active</div>
                        <?php elseif (isset($stat['new'])): ?>
                            <div style="font-size: 13px; color: var(--mac-warning); font-weight: 500; margin-top: 2px;"><?php echo e($stat['new']); ?> new</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="font-size: 14px; font-weight: 500; color: var(--mac-text); letter-spacing: -0.1px;"><?php echo e($stat['label']); ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- macOS-like Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- Catalog -->
        <div class="mac-card">
            <div class="mac-card-header">
                <h2 class="mac-card-title">Catalog</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/products.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>Manage Products</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/categories.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>Manage Categories</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="mac-card">
            <div class="mac-card-header">
                <h2 class="mac-card-title">Content</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/pages.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>All Pages</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/team.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>Team Members</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>

        <!-- Marketing -->
        <div class="mac-card">
            <div class="mac-card-header">
                <h2 class="mac-card-title">Marketing</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/sliders.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>Hero Slider</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/testimonials.php" class="button button-link" style="justify-content: space-between; padding: 10px 0; border-radius: 8px;">
                    <span>Testimonials</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.5;"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- macOS-like Recent Activity -->
    <div class="mac-card">
        <div class="mac-card-header">
            <h2 class="mac-card-title">Recent Quote Requests</h2>
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
            <p style="color: var(--mac-text-secondary); font-size: 14px; margin: 0;">No quote requests yet.</p>
        <?php else: ?>
            <table class="mac-table">
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
                            <td><strong style="font-weight: 600;"><?php echo e($quote['companyName']); ?></strong></td>
                            <td><?php echo e($quote['contactName']); ?></td>
                            <td style="color: var(--mac-text-secondary);"><?php echo date('M d, Y', strtotime($quote['createdAt'])); ?></td>
                            <td>
                                <span class="mac-badge <?php
                                    echo match($quote['status']) {
                                        'NEW' => 'mac-badge-warning',
                                        'IN_PROGRESS' => 'mac-badge-info',
                                        'RESOLVED' => 'mac-badge-success',
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
            <div style="margin-top: 20px; text-align: center;">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/quotes.php" class="button button-link">View all quote requests</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
