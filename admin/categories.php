<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

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

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Catalog</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Categories</h1>
            <p class="text-sm text-gray-600">Manage product categories and their priority</p>
        </div>
        <button type="button" id="new-category-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New category
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Slug</th>
                    <th class="px-6 py-3 font-medium">Priority</th>
                    <th class="px-6 py-3 font-medium">Products</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="px-6 py-4 font-semibold"><?php echo e($category['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['slug']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['priority']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['product_count']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($category['updatedAt'])); ?></td>
                        <td class="px-6 py-4 text-right">
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
                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="text-sm font-medium text-[#0b3a63] hover:underline category-edit-btn"
                                    data-category="<?php echo htmlspecialchars(json_encode($categoryData), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-red-600 hover:text-red-800 category-delete-btn"
                                    data-id="<?php echo htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php if ($category['product_count'] > 0): ?>
                                        disabled
                                        title="Cannot delete category with products"
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

<div id="category-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-xl rounded-lg bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <div>
                <h2 id="category-modal-title" class="text-xl font-semibold text-[#0b3a63]">New Category</h2>
                <p class="text-sm text-gray-500">Manage catalog categories</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" id="category-modal-close">&times;</button>
        </div>

        <form id="category-form" class="mt-4 space-y-4">
            <input type="hidden" name="id" />
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug (optional)</label>
                <input type="text" name="slug" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Icon URL</label>
                    <input type="text" name="icon" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                    <input type="number" name="priority" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" value="0">
                </div>
            </div>
            <p id="category-form-error" class="text-sm text-red-600 hidden"></p>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" id="category-cancel-btn">Cancel</button>
                <button type="submit" class="rounded bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]" id="category-submit-btn">
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
        modal.classList.add('flex');
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
