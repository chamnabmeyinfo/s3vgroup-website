<?php
session_start();
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

requireAdmin();

$db = getDB();
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.categoryId 
    GROUP BY c.id 
    ORDER BY c.priority DESC, c.name ASC
")->fetchAll();

$pageTitle = 'Categories';
include __DIR__ . '/includes/header.php';
?>

<div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
        <div>
            <h1 style="font-size: 22px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0 0 4px 0;">Categories</h1>
            <p style="margin: 0; color: var(--mac-text-secondary); font-size: 12px;">Manage product categories and their priority</p>
        </div>
        <button type="button" id="new-category-btn" class="admin-btn admin-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            New Category
        </button>
    </div>

    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Priority</th>
                    <th>Products</th>
                    <th>Updated</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo e($category['name']); ?></td>
                        <td style="color: var(--mac-text-secondary);"><?php echo e($category['slug']); ?></td>
                        <td style="color: var(--mac-text-secondary);"><?php echo e($category['priority']); ?></td>
                        <td style="color: var(--mac-text-secondary);"><?php echo e($category['product_count']); ?></td>
                        <td style="color: var(--mac-text-secondary); font-size: 12px;"><?php echo date('M d, Y', strtotime($category['updatedAt'])); ?></td>
                        <td style="text-align: right;">
                            <?php
                                $categoryData = [
                                    'id'          => $category['id'],
                                    'name'        => $category['name'],
                                    'slug'        => $category['slug'],
                                    'description' => $category['description'],
                                    'icon'        => $category['icon'],
                                    'priority'    => $category['priority'],
                                ];
                            ?>
                            <div style="display: flex; align-items: center; gap: 6px; justify-content: flex-end;">
                                <button
                                    type="button"
                                    class="category-edit-btn admin-btn admin-btn-secondary"
                                    style="font-size: 11px; padding: 4px 12px;"
                                    data-category="<?php echo htmlspecialchars(json_encode($categoryData), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="category-delete-btn admin-btn admin-btn-danger"
                                    style="font-size: 11px; padding: 4px 12px;"
                                    data-id="<?php echo htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php if ($category['product_count'] > 0): ?>
                                        disabled
                                        title="Cannot delete category with products"
                                        style="opacity: 0.5; cursor: not-allowed;"
                                    <?php endif; ?>
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="category-modal" class="admin-modal hidden">
    <div class="admin-modal-content" style="max-width: 600px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
            <div>
                <h2 id="category-modal-title" style="font-size: 18px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0 0 4px 0;">New Category</h2>
                <p style="margin: 0; color: var(--mac-text-secondary); font-size: 12px;">Manage catalog categories</p>
            </div>
            <button type="button" id="category-modal-close" style="background: none; border: none; color: var(--mac-text-secondary); cursor: pointer; font-size: 24px; line-height: 1; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.color='var(--mac-text)'" onmouseout="this.style.color='var(--mac-text-secondary)'">&times;</button>
        </div>

        <form id="category-form">
            <input type="hidden" name="id" />
            <div class="admin-form-group">
                <label class="admin-form-label">Name</label>
                <input type="text" name="name" class="admin-form-input" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Slug (optional)</label>
                <input type="text" name="slug" class="admin-form-input">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Description</label>
                <textarea name="description" rows="3" class="admin-form-textarea"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Icon URL</label>
                    <input type="text" name="icon" class="admin-form-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Priority</label>
                    <input type="number" name="priority" class="admin-form-input" value="0">
                </div>
            </div>
            <p id="category-form-error" style="font-size: 12px; color: var(--mac-red); margin: 0 0 16px 0;" class="hidden"></p>
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 16px; border-top: 1px solid #b0b0b0; margin-top: 20px;">
                <button type="button" class="admin-btn admin-btn-secondary" id="category-cancel-btn">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" id="category-submit-btn">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('category-modal');
    const openBtn = document.getElementById('new-category-btn');
    const closeBtn = document.getElementById('category-modal-close');
    const cancelBtn = document.getElementById('category-cancel-btn');
    const form = document.getElementById('category-form');
    const idInput = form.querySelector('input[name="id"]');
    const title = document.getElementById('category-modal-title');
    const errorEl = document.getElementById('category-form-error');
    const submitBtn = document.getElementById('category-submit-btn');
    let mode = 'create';

    function showModal(data) {
        mode = data ? 'edit' : 'create';
        title.textContent = mode === 'create' ? 'New Category' : 'Edit Category';
        submitBtn.textContent = mode === 'create' ? 'Create Category' : 'Update Category';
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        idInput.value = data?.id || '';
        form.name.value = data?.name || '';
        form.slug.value = data?.slug || '';
        form.description.value = data?.description || '';
        form.icon.value = data?.icon || '';
        form.priority.value = data?.priority ?? 0;

        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function handleSubmit(event) {
        event.preventDefault();
        errorEl.textContent = '';
        errorEl.classList.add('hidden');

        const payload = {
            name: form.name.value.trim(),
            slug: form.slug.value.trim() || null,
            description: form.description.value.trim() || null,
            icon: form.icon.value.trim() || null,
            priority: form.priority.value ? parseInt(form.priority.value, 10) : 0,
        };

        if (!payload.name) {
            errorEl.textContent = 'Name is required.';
            errorEl.classList.remove('hidden');
            return;
        }

        const id = idInput.value;
        const url = id ? `/api/admin/categories/item.php?id=${encodeURIComponent(id)}` : '/api/admin/categories/index.php';
        const method = id ? 'PUT' : 'POST';

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save category.');
            }

            window.location.reload();
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = mode === 'create' ? 'Create Category' : 'Update Category';
        }
    }

    openBtn.addEventListener('click', () => showModal(null));
    closeBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);
    form.addEventListener('submit', handleSubmit);

    document.querySelectorAll('.category-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.category);
            showModal(data);
        });
    });

    // Delete functionality
    document.querySelectorAll('.category-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const name = button.dataset.name;

            if (!confirm(`Are you sure you want to delete the category "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/categories/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete category.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
