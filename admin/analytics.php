<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Get analytics data
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last7Days = date('Y-m-d', strtotime('-7 days'));
$last30Days = date('Y-m-d', strtotime('-30 days'));

// Page views
$pageViewsToday = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'page_view' AND DATE(createdAt) = '$today'")->fetchColumn();
$pageViewsYesterday = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'page_view' AND DATE(createdAt) = '$yesterday'")->fetchColumn();
$pageViewsLast7 = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'page_view' AND DATE(createdAt) >= '$last7Days'")->fetchColumn();
$pageViewsLast30 = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'page_view' AND DATE(createdAt) >= '$last30Days'")->fetchColumn();

// Product views
$productViewsToday = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'product_view' AND DATE(createdAt) = '$today'")->fetchColumn();
$productViewsLast7 = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'product_view' AND DATE(createdAt) >= '$last7Days'")->fetchColumn();

// Quote requests
$quotesToday = $db->query("SELECT COUNT(*) FROM quote_requests WHERE DATE(createdAt) = '$today'")->fetchColumn();
$quotesLast7 = $db->query("SELECT COUNT(*) FROM quote_requests WHERE DATE(createdAt) >= '$last7Days'")->fetchColumn();

// Most viewed products
$topProducts = $db->query("
    SELECT p.id, p.name, COUNT(ae.id) as views
    FROM analytics_events ae
    JOIN products p ON ae.product_id = p.id
    WHERE ae.event_type = 'product_view' AND ae.createdAt >= '$last7Days'
    GROUP BY p.id, p.name
    ORDER BY views DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Popular pages
$popularPages = $db->query("
    SELECT page_url, COUNT(*) as views
    FROM analytics_events
    WHERE event_type = 'page_view' AND createdAt >= '$last7Days'
    GROUP BY page_url
    ORDER BY views DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Conversion rate (quotes / product views)
$conversionRate = $productViewsLast7 > 0 ? round(($quotesLast7 / $productViewsLast7) * 100, 2) : 0;

$pageTitle = 'Analytics Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Insights</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Analytics Dashboard</h1>
            <p class="text-sm text-gray-600">Track website performance and visitor behavior</p>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <div class="text-3xl">👁️</div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($pageViewsToday); ?></div>
                    <div class="text-xs text-gray-500">Today</div>
                </div>
            </div>
            <div class="text-sm font-medium text-gray-700">Page Views</div>
            <div class="text-xs text-gray-500 mt-1">
                <?php 
                $change = $pageViewsYesterday > 0 ? round((($pageViewsToday - $pageViewsYesterday) / $pageViewsYesterday) * 100, 1) : 0;
                $color = $change >= 0 ? 'text-green-600' : 'text-red-600';
                $icon = $change >= 0 ? '↑' : '↓';
                ?>
                <span class="<?php echo $color; ?>"><?php echo $icon; ?> <?php echo abs($change); ?>%</span> vs yesterday
            </div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <div class="text-3xl">📦</div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($productViewsToday); ?></div>
                    <div class="text-xs text-gray-500">Today</div>
                </div>
            </div>
            <div class="text-sm font-medium text-gray-700">Product Views</div>
            <div class="text-xs text-gray-500 mt-1"><?php echo number_format($productViewsLast7); ?> in last 7 days</div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <div class="text-3xl">📋</div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($quotesToday); ?></div>
                    <div class="text-xs text-gray-500">Today</div>
                </div>
            </div>
            <div class="text-sm font-medium text-gray-700">Quote Requests</div>
            <div class="text-xs text-gray-500 mt-1"><?php echo number_format($quotesLast7); ?> in last 7 days</div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <div class="text-3xl">📈</div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $conversionRate; ?>%</div>
                    <div class="text-xs text-gray-500">Last 7 days</div>
                </div>
            </div>
            <div class="text-sm font-medium text-gray-700">Conversion Rate</div>
            <div class="text-xs text-gray-500 mt-1">Quotes / Product Views</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products -->
        <div class="admin-card">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🔥 Most Viewed Products (Last 7 Days)</h2>
            <?php if (empty($topProducts)): ?>
                <div class="admin-empty">
                    <div class="admin-empty-icon">📊</div>
                    <p>No product views yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topProducts as $index => $product): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-400">#<?php echo $index + 1; ?></span>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($product['name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo number_format($product['views']); ?> views</div>
                                </div>
                            </div>
                            <a href="/admin/products.php" class="text-sm text-[#0b3a63] hover:underline">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Popular Pages -->
        <div class="admin-card">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📄 Popular Pages (Last 7 Days)</h2>
            <?php if (empty($popularPages)): ?>
                <div class="admin-empty">
                    <div class="admin-empty-icon">📊</div>
                    <p>No page views yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($popularPages as $page): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900 text-sm"><?php echo e($page['page_url']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo number_format($page['views']); ?> views</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

