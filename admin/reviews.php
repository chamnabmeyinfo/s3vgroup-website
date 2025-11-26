<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    $adminResponse = $_POST['admin_response'] ?? '';
    
    $stmt = $db->prepare("UPDATE product_reviews SET status = ?, admin_response = ? WHERE id = ?");
    $stmt->execute([$status, $adminResponse, $id]);
    
    header('Location: /admin/reviews.php');
    exit;
}

// Get all reviews
$statusFilter = $_GET['status'] ?? 'all';
$where = $statusFilter !== 'all' ? "WHERE status = " . $db->quote($statusFilter) : "";

$reviews = $db->query("
    SELECT r.*, p.name as product_name
    FROM product_reviews r
    LEFT JOIN products p ON r.product_id = p.id
    $where
    ORDER BY r.createdAt DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'PENDING'")->fetchColumn(),
    'approved' => $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'APPROVED'")->fetchColumn(),
    'avg_rating' => $db->query("SELECT AVG(rating) FROM product_reviews WHERE status = 'APPROVED'")->fetchColumn() ?: 0,
];

$pageTitle = 'Product Reviews';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Customer Feedback</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Product Reviews</h1>
            <p class="text-sm text-gray-600">Manage customer reviews and ratings</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="admin-card">
            <div class="text-sm text-gray-600">Total Reviews</div>
            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></div>
        </div>
        <div class="admin-card">
            <div class="text-sm text-gray-600">Pending</div>
            <div class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['pending']); ?></div>
        </div>
        <div class="admin-card">
            <div class="text-sm text-gray-600">Approved</div>
            <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['approved']); ?></div>
        </div>
        <div class="admin-card">
            <div class="text-sm text-gray-600">Avg Rating</div>
            <div class="text-2xl font-bold text-[#0b3a63]"><?php echo number_format($stats['avg_rating'], 1); ?> ⭐</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="admin-card">
        <div class="flex items-center gap-4 mb-4">
            <span class="text-sm font-medium text-gray-700">Filter:</span>
            <a href="?status=all" class="px-3 py-1 rounded <?php echo $statusFilter === 'all' ? 'bg-[#0b3a63] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">All</a>
            <a href="?status=PENDING" class="px-3 py-1 rounded <?php echo $statusFilter === 'PENDING' ? 'bg-[#0b3a63] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">Pending</a>
            <a href="?status=APPROVED" class="px-3 py-1 rounded <?php echo $statusFilter === 'APPROVED' ? 'bg-[#0b3a63] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">Approved</a>
            <a href="?status=REJECTED" class="px-3 py-1 rounded <?php echo $statusFilter === 'REJECTED' ? 'bg-[#0b3a63] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">Rejected</a>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="admin-card">
        <?php if (empty($reviews)): ?>
            <div class="admin-empty">
                <div class="admin-empty-icon">⭐</div>
                <p class="text-lg font-medium">No reviews yet</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="font-semibold text-gray-900"><?php echo e($review['customer_name']); ?></div>
                                    <div class="flex items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="text-lg"><?php echo $i <= $review['rating'] ? '⭐' : '☆'; ?></span>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($review['verified_purchase']): ?>
                                        <span class="admin-badge admin-badge-success text-xs">Verified Purchase</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($review['title']): ?>
                                    <div class="font-medium text-gray-900 mb-1"><?php echo e($review['title']); ?></div>
                                <?php endif; ?>
                                <div class="text-sm text-gray-600 mb-2"><?php echo e($review['review_text']); ?></div>
                                <div class="text-xs text-gray-500">
                                    Product: <a href="/admin/products.php" class="text-[#0b3a63] hover:underline"><?php echo e($review['product_name'] ?: 'Unknown'); ?></a>
                                    • <?php echo date('M d, Y', strtotime($review['createdAt'])); ?>
                                </div>
                            </div>
                            <div>
                                <span class="admin-badge <?php 
                                    echo $review['status'] === 'APPROVED' ? 'admin-badge-success' : 
                                        ($review['status'] === 'PENDING' ? 'admin-badge-warning' : 'admin-badge-danger'); 
                                ?>">
                                    <?php echo e($review['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($review['admin_response']): ?>
                            <div class="mt-3 p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                                <div class="text-xs font-semibold text-blue-900 mb-1">Admin Response:</div>
                                <div class="text-sm text-blue-800"><?php echo e($review['admin_response']); ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" class="admin-btn admin-btn-primary text-xs review-edit-btn" data-review='<?php echo htmlspecialchars(json_encode($review), ENT_QUOTES, 'UTF-8'); ?>'>
                                Manage
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div id="review-modal" class="admin-modal hidden">
    <div class="admin-modal-content">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-[#0b3a63]">Manage Review</h2>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700 text-2xl" id="review-modal-close">&times;</button>
        </div>

        <form id="review-form" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="review-id">

            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select name="status" id="review-status" class="admin-form-select">
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                    <option value="SPAM">Spam</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Admin Response (Optional)</label>
                <textarea name="admin_response" id="review-admin-response" rows="4" class="admin-form-textarea" placeholder="Add a response to this review..."></textarea>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                <button type="button" id="review-form-cancel" class="admin-btn admin-btn-secondary">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Update Review</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('review-modal');
    const closeBtn = document.getElementById('review-modal-close');
    const cancelBtn = document.getElementById('review-form-cancel');
    const form = document.getElementById('review-form');

    function openModal(review) {
        document.getElementById('review-id').value = review.id;
        document.getElementById('review-status').value = review.status;
        document.getElementById('review-admin-response').value = review.admin_response || '';
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    document.querySelectorAll('.review-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const review = JSON.parse(btn.dataset.review);
            openModal(review);
        });
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

