<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();
$products = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.categoryId = c.id 
    ORDER BY p.updatedAt DESC 
    LIMIT 25
")->fetchAll();
$categoriesList = getAllCategories($db);

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Catalog</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Products</h1>
            <p class="text-sm text-gray-600">Manage published systems and drafts</p>
        </div>
        <button type="button" id="new-product-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New product
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium"></th>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <?php if ($product['heroImage']): ?>
                                <img src="<?php echo e($product['heroImage']); ?>" alt="<?php echo e($product['name']); ?>" class="h-10 w-10 rounded-lg object-cover">
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-semibold"><?php echo e($product['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($product['category_name'] ?? '—'); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                echo $product['status'] === 'PUBLISHED' ? 'bg-green-100 text-green-800' : 
                                    ($product['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                            ?>">
                                <?php echo e($product['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($product['updatedAt'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <?php
                                $productData = [
                                    'id'          => $product['id'],
                                    'name'        => $product['name'],
                                    'slug'        => $product['slug'],
                                    'sku'         => $product['sku'],
                                    'summary'     => $product['summary'],
                                    'description' => $product['description'],
                                    'specs'       => $product['specs'] ? json_decode($product['specs'], true) ?? $product['specs'] : null,
                                    'heroImage'   => $product['heroImage'],
                                    'price'       => $product['price'],
                                    'status'      => $product['status'],
                                    'highlights'  => $product['highlights'] ? json_decode($product['highlights'], true) ?? $product['highlights'] : null,
                                    'categoryId'  => $product['categoryId'],
                                ];
                            ?>
                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="text-sm font-medium text-[#0b3a63] hover:underline product-edit-btn"
                                    data-product="<?php echo htmlspecialchars(json_encode($productData), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-red-600 hover:text-red-800 product-delete-btn"
                                    data-id="<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
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

<div id="product-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <div>
                <h2 id="product-modal-title" class="text-xl font-semibold text-[#0b3a63]">New Product</h2>
                <p class="text-sm text-gray-500">Publish products to the public site</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" id="product-modal-close">&times;</button>
        </div>

        <form id="product-form" class="mt-4 space-y-4">
            <input type="hidden" name="id">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="categoryId" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                        <option value="">Select category</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?php echo e($category['id']); ?>"><?php echo e($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                        <option value="DRAFT">Draft</option>
                        <option value="PUBLISHED">Published</option>
                        <option value="ARCHIVED">Archived</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price (optional)</label>
                    <input type="number" step="0.01" name="price" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                    <input type="text" name="sku" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hero Image</label>
                    <div class="flex items-center gap-3">
                        <input type="url" name="heroImage" id="product-hero-image-input" placeholder="https://example.com/image.jpg or upload" class="flex-1 mt-1 rounded border border-gray-300 px-3 py-2">
                        <input type="file" accept="image/*" class="hidden" id="product-hero-image-file" data-target="product-hero-image-input">
                        <button type="button" onclick="document.getElementById('product-hero-image-file').click()" class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Upload</button>
                    </div>
                    <div id="product-hero-image-preview" class="mt-2"></div>
                    <p class="text-xs text-gray-500 mt-1">Recommended: 1200x800px or larger</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Summary</label>
                <textarea name="summary" rows= "2" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Specs (JSON)</label>
                    <textarea name="specs" rows="4" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder='{"capacity":"2500kg"}'></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Highlights (JSON array)</label>
                    <textarea name="highlights" rows="4" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder='["Heavy duty design","Fast charging"]'></textarea>
                </div>
            </div>

            <p id="product-form-error" class="text-sm text-red-600 hidden"></p>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" id="product-cancel-btn">Cancel</button>
                <button type="submit" class="rounded bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]" id="product-submit-btn">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('product-modal');
    const openBtn = document.getElementById('new-product-btn');
    const closeBtn = document.getElementById('product-modal-close');
    const cancelBtn = document.getElementById('product-cancel-btn');
    const form = document.getElementById('product-form');
    const idInput = form.querySelector('input[name="id"]');
    const errorEl = document.getElementById('product-form-error');
    const title = document.getElementById('product-modal-title');
    const submitBtn = document.getElementById('product-submit-btn');
    let mode = 'create';

    function formatJsonField(value) {
        if (!value) {
            return '';
        }

        if (typeof value === 'string') {
            return value;
        }

        return JSON.stringify(value, null, 2);
    }

    function showModal(data) {
        mode = data ? 'edit' : 'create';
        title.textContent = mode === 'create' ? 'New Product' : 'Edit Product';
        submitBtn.textContent = mode === 'create' ? 'Create Product' : 'Update Product';
        errorEl.textContent = '';
        errorEl.classList.add('hidden');

        idInput.value = data?.id || '';
        form.name.value = data?.name || '';
        form.slug.value = data?.slug || '';
        form.categoryId.value = data?.categoryId || '';
        form.status.value = data?.status || 'DRAFT';
        form.price.value = data?.price ?? '';
        form.sku.value = data?.sku || '';
        form.heroImage.value = data?.heroImage || '';
        form.summary.value = data?.summary || '';
        form.description.value = data?.description || '';
        form.specs.value = formatJsonField(data?.specs);
        form.highlights.value = formatJsonField(data?.highlights);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('product-hero-image-preview').innerHTML = '';
    }

    function parseJsonField(value, fieldName) {
        if (!value) {
            return null;
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            throw new Error(`Invalid JSON in ${fieldName}.`);
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        const payload = {
            name: form.name.value.trim(),
            slug: form.slug.value.trim() || null,
            categoryId: form.categoryId.value,
            status: form.status.value,
            price: form.price.value ? parseFloat(form.price.value) : null,
            sku: form.sku.value.trim() || null,
            heroImage: form.heroImage.value.trim() || null,
            summary: form.summary.value.trim() || null,
            description: form.description.value.trim() || null,
        };

        if (!payload.name || !payload.categoryId) {
            errorEl.textContent = 'Name and category are required.';
            errorEl.classList.remove('hidden');
            return;
        }

        try {
            payload.specs = parseJsonField(form.specs.value.trim(), 'Specs');
            payload.highlights = parseJsonField(form.highlights.value.trim(), 'Highlights');
        } catch (jsonError) {
            errorEl.textContent = jsonError.message;
            errorEl.classList.remove('hidden');
            return;
        }

        const id = idInput.value;
        const url = id ? `/api/admin/products/item.php?id=${encodeURIComponent(id)}` : '/api/admin/products/index.php';
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
                throw new Error(result.message || 'Unable to save product.');
            }

            window.location.reload();
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = mode === 'create' ? 'Create Product' : 'Update Product';
        }
    }

    // Hero image upload
    document.getElementById('product-hero-image-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const input = document.getElementById('product-hero-image-input');
        const preview = document.getElementById('product-hero-image-preview');
        
        // Show loading
        if (preview) {
            preview.innerHTML = '<p class="text-sm text-gray-500">Uploading...</p>';
        }

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await fetch('/api/admin/upload.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.status === 'success') {
                input.value = result.data.url;
                preview.innerHTML = `<img src="${result.data.url}" alt="Preview" class="h-32 w-auto rounded border border-gray-300 object-contain">`;
            } else {
                preview.innerHTML = '';
                alert('Upload failed: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            preview.innerHTML = '';
            alert('Upload error: ' + error.message);
        }
    });

    openBtn.addEventListener('click', () => showModal(null));
    closeBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);
    form.addEventListener('submit', handleSubmit);

    document.querySelectorAll('.product-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.product);
            showModal(data);
        });
    });

    // Delete functionality
    document.querySelectorAll('.product-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const name = button.dataset.name;

            if (!confirm(`Are you sure you want to delete the product "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/products/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete product.');
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
