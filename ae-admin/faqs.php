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
} elseif (file_exists(__DIR__ . '/../wp-includes/functions.php')) {
    require_once __DIR__ . '/../wp-includes/functions.php';
} elseif (file_exists(__DIR__ . '/../includes/functions.php')) {
    require_once __DIR__ . '/../includes/functions.php';
}

requireAdmin();

$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $id = $_POST['id'] ?? 'faq_' . uniqid();
        $question = $_POST['question'] ?? '';
        $answer = $_POST['answer'] ?? '';
        $category = $_POST['category'] ?? '';
        $priority = (int)($_POST['priority'] ?? 0);
        $status = $_POST['status'] ?? 'DRAFT';
        
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO faqs (id, question, answer, category, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $question, $answer, $category, $priority, $status]);
        } else {
            $stmt = $db->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, priority = ?, status = ? WHERE id = ?");
            $stmt->execute([$question, $answer, $category, $priority, $status, $id]);
        }
        
        header('Location: /admin/faqs.php');
        exit;
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: /admin/faqs.php');
        exit;
    }
}

// Get all FAQs
$faqs = $db->query("SELECT * FROM faqs ORDER BY priority DESC, createdAt DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $db->query("SELECT DISTINCT category FROM faqs WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'FAQs';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Content</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Frequently Asked Questions</h1>
            <p class="text-sm text-gray-600">Manage common questions and answers</p>
        </div>
        <button type="button" id="new-faq-btn" class="admin-btn admin-btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>New FAQ</span>
        </button>
    </div>

    <div class="admin-card">
        <?php if (empty($faqs)): ?>
            <div class="admin-empty">
                <div class="admin-empty-icon">❓</div>
                <p class="text-lg font-medium">No FAQs yet</p>
                <p class="text-sm mt-2">Create your first FAQ to help customers</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 md:px-6 py-3 font-medium">Question</th>
                            <th class="px-4 md:px-6 py-3 font-medium">Category</th>
                            <th class="px-4 md:px-6 py-3 font-medium">Status</th>
                            <th class="px-4 md:px-6 py-3 font-medium">Views</th>
                            <th class="px-4 md:px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($faqs as $faq): ?>
                            <tr>
                                <td class="px-4 md:px-6 py-4 font-semibold text-gray-900" data-label="Question">
                                    <?php echo e(substr($faq['question'], 0, 60)); ?><?php echo strlen($faq['question']) > 60 ? '...' : ''; ?>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-gray-600" data-label="Category">
                                    <?php echo e($faq['category'] ?: '—'); ?>
                                </td>
                                <td class="px-4 md:px-6 py-4" data-label="Status">
                                    <span class="admin-badge <?php echo $faq['status'] === 'PUBLISHED' ? 'admin-badge-success' : ($faq['status'] === 'DRAFT' ? 'admin-badge-warning' : 'admin-badge-info'); ?>">
                                        <?php echo e($faq['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-gray-600" data-label="Views">
                                    <?php echo number_format($faq['views']); ?>
                                </td>
                                <td class="px-4 md:px-6 py-4" data-label="Actions">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="admin-btn admin-btn-primary text-xs faq-edit-btn" data-faq='<?php echo htmlspecialchars(json_encode($faq), ENT_QUOTES, 'UTF-8'); ?>'>
                                            Edit
                                        </button>
                                        <button type="button" class="admin-btn admin-btn-danger text-xs faq-delete-btn" data-id="<?php echo e($faq['id']); ?>" data-question="<?php echo e($faq['question']); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- FAQ Modal -->
<div id="faq-modal" class="admin-modal hidden">
    <div class="admin-modal-content">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
            <div>
                <h2 id="faq-modal-title" class="text-xl font-semibold text-[#0b3a63]">New FAQ</h2>
                <p class="text-sm text-gray-500">Add a frequently asked question</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700 text-2xl" id="faq-modal-close">&times;</button>
        </div>

        <form id="faq-form" method="POST">
            <input type="hidden" name="action" id="faq-action" value="create">
            <input type="hidden" name="id" id="faq-id">

            <div class="admin-form-group">
                <label class="admin-form-label">Question</label>
                <input type="text" name="question" id="faq-question" class="admin-form-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Answer</label>
                <textarea name="answer" id="faq-answer" rows="6" class="admin-form-textarea" required></textarea>
            </div>

            <div class="admin-form-group grid gap-4 md:grid-cols-3">
                <div>
                    <label class="admin-form-label">Category</label>
                    <input type="text" name="category" id="faq-category" class="admin-form-input" list="faq-categories">
                    <datalist id="faq-categories">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo e($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="admin-form-label">Priority</label>
                    <input type="number" name="priority" id="faq-priority" class="admin-form-input" value="0">
                </div>
                <div>
                    <label class="admin-form-label">Status</label>
                    <select name="status" id="faq-status" class="admin-form-select">
                        <option value="DRAFT">Draft</option>
                        <option value="PUBLISHED">Published</option>
                        <option value="ARCHIVED">Archived</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                <button type="button" id="faq-form-cancel" class="admin-btn admin-btn-secondary">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Save FAQ</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('faq-modal');
    const openBtn = document.getElementById('new-faq-btn');
    const closeBtn = document.getElementById('faq-modal-close');
    const cancelBtn = document.getElementById('faq-form-cancel');
    const form = document.getElementById('faq-form');

    function openModal(faq = null) {
        if (faq) {
            document.getElementById('faq-modal-title').textContent = 'Edit FAQ';
            document.getElementById('faq-action').value = 'update';
            document.getElementById('faq-id').value = faq.id;
            document.getElementById('faq-question').value = faq.question;
            document.getElementById('faq-answer').value = faq.answer;
            document.getElementById('faq-category').value = faq.category || '';
            document.getElementById('faq-priority').value = faq.priority || 0;
            document.getElementById('faq-status').value = faq.status || 'DRAFT';
        } else {
            document.getElementById('faq-modal-title').textContent = 'New FAQ';
            document.getElementById('faq-action').value = 'create';
            form.reset();
            document.getElementById('faq-id').value = '';
        }
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    openBtn?.addEventListener('click', () => openModal());
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    // Edit buttons
    document.querySelectorAll('.faq-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const faq = JSON.parse(btn.dataset.faq);
            openModal(faq);
        });
    });

    // Delete buttons
    document.querySelectorAll('.faq-delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm(`Delete FAQ: "${btn.dataset.question}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${btn.dataset.id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

