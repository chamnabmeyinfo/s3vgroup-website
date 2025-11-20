<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Get statistics
$productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$categoryCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$quoteCount = $db->query("SELECT COUNT(*) FROM quote_requests WHERE DATE(createdAt) = CURDATE()")->fetchColumn();
$totalQuotes = $db->query("SELECT COUNT(*) FROM quote_requests")->fetchColumn();

// Get latest quotes
$latestQuotes = $db->query("SELECT * FROM quote_requests ORDER BY createdAt DESC LIMIT 5")->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-[#0b3a63]">Operations Overview</h1>
        <p class="text-sm text-gray-600 mt-2">Snapshot of catalog and quote activity</p>
    </div>

    <!-- Statistics -->
    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Products</p>
            <p class="text-3xl font-semibold text-[#0b3a63]"><?php echo $productCount; ?></p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Categories</p>
            <p class="text-3xl font-semibold text-[#0b3a63]"><?php echo $categoryCount; ?></p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Quotes Today</p>
            <p class="text-3xl font-semibold text-[#0b3a63]"><?php echo $quoteCount; ?></p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Total Quotes</p>
            <p class="text-3xl font-semibold text-[#0b3a63]"><?php echo $totalQuotes; ?></p>
        </div>
    </div>

    <!-- Latest Quotes -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-[#0b3a63]">Latest Quote Requests</h2>
            <p class="text-sm text-gray-600">Showing the five most recent entries</p>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($latestQuotes)): ?>
                <p class="py-6 text-sm text-gray-500 text-center">No quote requests yet today.</p>
            <?php else: ?>
                <?php foreach ($latestQuotes as $quote): ?>
                    <div class="flex items-center justify-between py-4">
                        <div>
                            <p class="font-medium text-[#0b3a63]"><?php echo e($quote['companyName']); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php echo e($quote['contactName']); ?> • 
                                <?php echo date('M d, h:i a', strtotime($quote['createdAt'])); ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-semibold text-[#0b3a63]">
                            <?php echo e($quote['status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
