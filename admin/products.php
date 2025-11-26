<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();
$categoriesList = getAllCategories($db);

// Load all products via API (will be loaded via JavaScript)
$products = [];

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Catalog</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Products</h1>
            <p class="text-sm text-gray-600">
                Manage published systems and drafts
                <span id="product-count" class="ml-2 font-medium text-gray-900"></span>
            </p>
        </div>
        <button type="button" id="new-product-btn" class="admin-btn admin-btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>New Product</span>
        </button>
    </div>

    <!-- Filters and Sort -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 md:p-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="product-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input 
                    type="text" 
                    id="product-search" 
                    placeholder="Search by name, SKU, description..."
                    class="admin-form-input w-full"
                >
            </div>
            
            <!-- Category Filter -->
            <div>
                <label for="product-category-filter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="product-category-filter" class="admin-form-select w-full">
                    <option value="">All Categories</option>
                    <?php foreach ($categoriesList as $category): ?>
                        <option value="<?php echo e($category['id']); ?>"><?php echo e($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label for="product-status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="product-status-filter" class="admin-form-select w-full">
                    <option value="">All Status</option>
                    <option value="PUBLISHED">Published</option>
                    <option value="DRAFT">Draft</option>
                    <option value="ARCHIVED">Archived</option>
                </select>
            </div>
            
            <!-- Sort -->
            <div>
                <label for="product-sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                <select id="product-sort" class="admin-form-select w-full">
                    <option value="updatedAt">Last Updated</option>
                    <option value="name">Name (A-Z)</option>
                    <option value="price">Price</option>
                    <option value="status">Status</option>
                    <option value="createdAt">Date Created</option>
                    <option value="category">Category</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="sort-order" value="DESC" checked class="text-blue-600">
                    <span>Descending</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="sort-order" value="ASC" class="text-blue-600">
                    <span>Ascending</span>
                </label>
            </div>
            <button 
                type="button" 
                id="clear-filters-btn"
                class="text-sm text-gray-600 hover:text-gray-900 underline"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
        <div id="products-loading" class="p-8 text-center text-gray-500">
            <div class="admin-loading">Loading products...</div>
        </div>
        <div id="products-error" class="hidden p-8 text-center text-red-600">
            <div class="admin-empty">
                <div class="admin-empty-icon">⚠️</div>
                <p class="text-lg font-medium">Failed to load products</p>
                <p class="text-sm mt-2">Please refresh the page</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="products-table" class="admin-table w-full text-left text-sm hidden">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-4 md:px-6 py-3 font-medium">Image</th>
                        <th class="px-4 md:px-6 py-3 font-medium">Name</th>
                        <th class="px-4 md:px-6 py-3 font-medium">Category</th>
                        <th class="px-4 md:px-6 py-3 font-medium">Status</th>
                        <th class="px-4 md:px-6 py-3 font-medium">Updated</th>
                        <th class="px-4 md:px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-tbody" class="divide-y divide-gray-200">
                    <!-- Products will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="product-modal" class="admin-modal hidden">
    <div class="admin-modal-content">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <div>
                <h2 id="product-modal-title" class="text-xl font-semibold text-[#0b3a63]">New Product</h2>
                <p class="text-sm text-gray-500">Publish products to the public site</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" id="product-modal-close">&times;</button>
        </div>

        <form id="product-form" class="mt-4 space-y-4">
            <input type="hidden" name="id">
            <div class="admin-form-group grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Name</label>
                    <input type="text" name="name" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Slug</label>
                    <input type="text" name="slug" class="admin-form-input">
                </div>
            </div>

            <div class="admin-form-group grid gap-4 md:grid-cols-3">
                <div>
                    <label class="admin-form-label">Category</label>
                    <select name="categoryId" class="admin-form-select" required>
                        <option value="">Select category</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?php echo e($category['id']); ?>"><?php echo e($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="admin-form-select">
                        <option value="DRAFT">Draft</option>
                        <option value="PUBLISHED">Published</option>
                        <option value="ARCHIVED">Archived</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Price (optional)</label>
                    <input type="number" step="0.01" name="price" class="admin-form-input">
                </div>
            </div>

            <div class="admin-form-group grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">SKU</label>
                    <input type="text" name="sku" class="admin-form-input">
                </div>
                <div>
                    <label class="admin-form-label">Hero Image</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                        <input type="url" name="heroImage" id="product-hero-image-input" placeholder="https://example.com/image.jpg or upload" class="admin-form-input flex-1">
                        <input type="file" accept="image/*" class="hidden" id="product-hero-image-file" data-target="product-hero-image-input">
                        <button type="button" onclick="document.getElementById('product-hero-image-file').click()" class="admin-btn admin-btn-secondary whitespace-nowrap">Upload</button>
                    </div>
                    <div id="product-hero-image-preview" class="mt-2"></div>
                    <p class="text-xs text-gray-500 mt-1">Recommended: 1200x800px or larger</p>
                </div>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Summary</label>
                <textarea name="summary" rows="2" class="admin-form-textarea"></textarea>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Description</label>
                <textarea name="description" rows="4" class="admin-form-textarea"></textarea>
            </div>

            <div class="admin-form-group grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Specs (JSON)</label>
                    <textarea name="specs" rows="4" class="admin-form-textarea font-mono text-sm" placeholder='{"capacity":"2500kg"}'></textarea>
                </div>
                <div>
                    <label class="admin-form-label">Highlights (JSON array)</label>
                    <textarea name="highlights" rows="4" class="admin-form-textarea font-mono text-sm" placeholder='["Heavy duty design","Fast charging"]'></textarea>
                </div>
            </div>

            <p id="product-form-error" class="text-sm text-red-600 hidden"></p>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                <button type="button" id="product-form-cancel" class="admin-btn admin-btn-secondary">
                    Cancel
                </button>
                <button type="submit" id="product-submit-btn" class="admin-btn admin-btn-primary">
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('product-modal');
    const openBtn = document.getElementById('new-product-btn');
    const closeBtn = document.getElementById('product-modal-close');
    const cancelBtn = document.getElementById('product-form-cancel');
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

            hideModal();
            loadAllProducts(); // Reload products instead of page reload
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = mode === 'create' ? 'Create Product' : 'Update Product';
        }
    }

    // Hero image upload with automatic optimization
    document.getElementById('product-hero-image-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        // Client-side validation: Check file size before upload
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
            alert('❌ File is too large! Maximum size is 50MB. Please choose a smaller image.');
            e.target.value = '';
            return;
        }

        // Warn about large files (but allow them - server will optimize)
        const largeFileThreshold = 10 * 1024 * 1024; // 10MB
        if (file.size > largeFileThreshold) {
            const proceed = confirm(
                `⚠️ Large image detected (${(file.size / 1024 / 1024).toFixed(1)} MB).\n\n` +
                `The image will be automatically optimized to under 1MB after upload.\n\n` +
                `Continue with upload?`
            );
            if (!proceed) {
                e.target.value = '';
                return;
            }
        }

        const input = document.getElementById('product-hero-image-input');
        const preview = document.getElementById('product-hero-image-preview');
        
        // Show loading with optimization message
        if (preview) {
            preview.innerHTML = '<p class="text-sm text-gray-500">Uploading & Optimizing...</p>';
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
                
                // Show optimization results if available
                if (result.data.optimized && result.data.sizeReduction) {
                    console.log(`Image optimized: Reduced by ${result.data.sizeReduction}%`);
                }
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

    // Get current filters
    function getFilters() {
        return {
            search: document.getElementById('product-search').value.trim(),
            categoryId: document.getElementById('product-category-filter').value,
            status: document.getElementById('product-status-filter').value,
            sortBy: document.getElementById('product-sort').value,
            sortOrder: document.querySelector('input[name="sort-order"]:checked').value
        };
    }

    // Build query string from filters
    function buildQueryString(filters) {
        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.categoryId) params.append('categoryId', filters.categoryId);
        if (filters.status) params.append('status', filters.status);
        if (filters.sortBy) params.append('sortBy', filters.sortBy);
        if (filters.sortOrder) params.append('sortOrder', filters.sortOrder);
        params.append('all', '1');
        return params.toString();
    }

    // Clear all filters
    document.getElementById('clear-filters-btn').addEventListener('click', () => {
        document.getElementById('product-search').value = '';
        document.getElementById('product-category-filter').value = '';
        document.getElementById('product-status-filter').value = '';
        document.getElementById('product-sort').value = 'updatedAt';
        document.querySelector('input[name="sort-order"][value="DESC"]').checked = true;
        loadAllProducts();
    });

    // Add event listeners for filters
    ['product-search', 'product-category-filter', 'product-status-filter', 'product-sort'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            if (id === 'product-search') {
                // Debounce search input
                let searchTimeout;
                el.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadAllProducts(), 500);
                });
            } else {
                el.addEventListener('change', loadAllProducts);
            }
        }
    });

    // Sort order change
    document.querySelectorAll('input[name="sort-order"]').forEach(radio => {
        radio.addEventListener('change', loadAllProducts);
    });

    // Load all products from API
    async function loadAllProducts() {
        const loadingEl = document.getElementById('products-loading');
        const errorEl = document.getElementById('products-error');
        const tableEl = document.getElementById('products-table');
        const tbodyEl = document.getElementById('products-tbody');

        try {
            loadingEl.classList.remove('hidden');
            errorEl.classList.add('hidden');
            tableEl.classList.add('hidden');

            const filters = getFilters();
            const queryString = buildQueryString(filters);
            const response = await fetch(`/api/admin/products/index.php?${queryString}`);
            const result = await response.json();

            if (result.status === 'success' && result.data.products) {
                renderProducts(result.data.products);
                loadingEl.classList.add('hidden');
                tableEl.classList.remove('hidden');
                
                // Update product count
                const countEl = document.getElementById('product-count');
                const total = result.data.total || result.data.count || result.data.products.length;
                countEl.textContent = `(${total} ${total === 1 ? 'product' : 'products'})`;
            } else {
                throw new Error(result.message || 'Failed to load products');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    }

    // Render products in table
    function renderProducts(products) {
        const tbodyEl = document.getElementById('products-tbody');
        tbodyEl.innerHTML = '';

        if (products.length === 0) {
            tbodyEl.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No products found.</td></tr>';
            return;
        }

        products.forEach((product) => {
            const row = document.createElement('tr');
            
            const statusClass = product.status === 'PUBLISHED' 
                ? 'admin-badge-success' 
                : (product.status === 'DRAFT' ? 'admin-badge-warning' : 'admin-badge-danger');
            
            const updatedDate = product.updatedAt 
                ? new Date(product.updatedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';

            row.innerHTML = `
                <td class="px-4 md:px-6 py-4" data-label="Image">
                    ${product.heroImage ? `<img src="${escapeHtml(product.heroImage)}" alt="${escapeHtml(product.name)}" class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-cover">` : '<span class="text-gray-400">—</span>'}
                </td>
                <td class="px-4 md:px-6 py-4 font-semibold text-gray-900" data-label="Name">${escapeHtml(product.name)}</td>
                <td class="px-4 md:px-6 py-4 text-gray-600" data-label="Category">${escapeHtml(product.category_name || '—')}</td>
                <td class="px-4 md:px-6 py-4" data-label="Status">
                    <span class="admin-badge ${statusClass}">
                        ${escapeHtml(product.status)}
                    </span>
                </td>
                <td class="px-4 md:px-6 py-4 text-gray-600" data-label="Updated">${updatedDate}</td>
                <td class="px-4 md:px-6 py-4" data-label="Actions">
                    <div class="flex items-center gap-2 md:gap-3 flex-wrap">
                        <button
                            type="button"
                            class="admin-btn admin-btn-primary text-xs md:text-sm product-edit-btn"
                            data-product-id="${escapeHtml(product.id)}"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="admin-btn admin-btn-danger text-xs md:text-sm product-delete-btn"
                            data-id="${escapeHtml(product.id)}"
                            data-name="${escapeHtml(product.name)}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            `;
            
            tbodyEl.appendChild(row);
        });

        // Attach event listeners to edit buttons
        document.querySelectorAll('.product-edit-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const productId = button.dataset.productId;
                try {
                    const response = await fetch(`/api/admin/products/item.php?id=${encodeURIComponent(productId)}`);
                    const result = await response.json();
                    
                    if (result.status === 'success' && result.data.product) {
                        showModal(result.data.product);
                    } else {
                        alert('Error: ' + (result.message || 'Failed to load product'));
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
        });

        // Attach event listeners to delete buttons
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

                    loadAllProducts(); // Reload products instead of page reload
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Load products on page load
    loadAllProducts();

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
