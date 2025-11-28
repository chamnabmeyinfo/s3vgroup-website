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
$categoriesList = getAllCategories($db);

// Load all products via API (will be loaded via JavaScript)
$products = [];

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';
?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 mb-1">Products</h1>
                <p class="text-sm text-gray-500">
                    Manage your product catalog
                    <span id="product-count" class="ml-2 font-medium text-gray-700"></span>
                </p>
            </div>
            <button type="button" id="new-product-btn" class="admin-btn admin-btn-primary flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg shadow-sm hover:shadow transition-all">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Product
            </button>
        </div>
    </div>

    <!-- Content -->
    <div class="p-6">

    <!-- Statistics Dashboard - Modern Card Design -->
    <div id="product-statistics" class="mb-6">
        <div id="statistics-loading" class="text-center py-8 text-sm text-gray-500">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-400"></div>
            <p class="mt-2">Loading statistics...</p>
        </div>
        
        <div id="statistics-content" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Products Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Products</p>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="text-2xl font-semibold text-gray-900" id="stat-total">0</p>
            </div>

            <!-- Published Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Published</p>
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-2xl font-semibold text-green-600" id="stat-published">0</p>
            </div>

            <!-- Drafts Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Drafts</p>
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <p class="text-2xl font-semibold text-yellow-600" id="stat-draft">0</p>
            </div>

            <!-- With Images Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">With Images</p>
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-2xl font-semibold text-gray-900" id="stat-with-images">0</p>
            </div>

            <!-- With Price Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">With Price</p>
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-2xl font-semibold text-gray-900" id="stat-with-prices">0</p>
            </div>
        </div>
        
        <div id="statistics-error" class="hidden text-center py-8">
            <div class="inline-flex items-center gap-2 text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">Failed to load statistics</p>
            </div>
            <button type="button" id="refresh-statistics" class="mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium">
                Try again
            </button>
        </div>
    </div>

    <!-- Categories Quick Access -->
    <div class="pb-3 border-b border-gray-100">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-xs text-gray-500 font-medium">Categories:</span>
            <div id="categories-quick-list" class="flex flex-wrap gap-1.5">
                <div class="text-xs text-gray-400">Loading...</div>
            </div>
            <a href="/ae-admin/categories.php" class="ml-auto text-xs text-gray-400 hover:text-gray-600">
                Manage →
            </a>
        </div>
    </div>

    <!-- Filters and Sort -->
    <div class="pb-3 border-b border-gray-100">
        <div class="flex items-center gap-3 flex-wrap">
            <input 
                type="text" 
                id="product-search" 
                placeholder="Search..."
                class="admin-form-input"
                style="flex: 1; min-width: 200px;"
            >
            <select id="product-category-filter" class="admin-form-select">
                <option value="">All Categories</option>
                <?php foreach ($categoriesList as $category): ?>
                    <option value="<?php echo e($category['id']); ?>"><?php echo e($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="product-status-filter" class="admin-form-select">
                <option value="">All Status</option>
                <option value="PUBLISHED">Published</option>
                <option value="DRAFT">Draft</option>
                <option value="ARCHIVED">Archived</option>
            </select>
            <select id="product-sort" class="admin-form-select">
                <option value="updatedAt">Last Updated</option>
                <option value="name">Name</option>
                <option value="sku">SKU</option>
                <option value="price">Price</option>
                <option value="status">Status</option>
            </select>
            <select id="items-per-page" class="admin-form-select" style="width: 80px;">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-1 text-xs text-gray-600">
                    <input type="radio" name="sort-order" value="DESC" checked class="w-3 h-3">
                    <span>↓</span>
                </label>
                <label class="flex items-center gap-1 text-xs text-gray-600">
                    <input type="radio" name="sort-order" value="ASC" class="w-3 h-3">
                    <span>↑</span>
                </label>
            </div>
            <button 
                type="button" 
                id="toggle-advanced-filters"
                    class="text-xs text-gray-500 hover:text-gray-700"
                >
                    <span id="advanced-filters-text">Advanced</span>
                </button>
                <button 
                    type="button" 
                    id="clear-filters-btn"
                    class="text-xs text-gray-500 hover:text-gray-700"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Advanced Filters (Collapsible) -->
        <div id="advanced-filters" class="hidden mt-3 pt-3 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Advanced Filters</h3>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Price Range -->
                <div>
                    <label for="product-price-min" class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                    <input 
                        type="number" 
                        id="product-price-min" 
                        step="0.01"
                        placeholder="0.00"
                        class="admin-form-input w-full"
                    >
                </div>
                <div>
                    <label for="product-price-max" class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                    <input 
                        type="number" 
                        id="product-price-max" 
                        step="0.01"
                        placeholder="999999.99"
                        class="admin-form-input w-full"
                    >
                </div>
                
                <!-- Date Created Range -->
                <div>
                    <label for="product-created-from" class="block text-sm font-medium text-gray-700 mb-1">Created From</label>
                    <input 
                        type="date" 
                        id="product-created-from" 
                        class="admin-form-input w-full"
                    >
                </div>
                <div>
                    <label for="product-created-to" class="block text-sm font-medium text-gray-700 mb-1">Created To</label>
                    <input 
                        type="date" 
                        id="product-created-to" 
                        class="admin-form-input w-full"
                    >
                </div>
                
                <!-- Date Updated Range -->
                <div>
                    <label for="product-updated-from" class="block text-sm font-medium text-gray-700 mb-1">Updated From</label>
                    <input 
                        type="date" 
                        id="product-updated-from" 
                        class="admin-form-input w-full"
                    >
                </div>
                <div>
                    <label for="product-updated-to" class="block text-sm font-medium text-gray-700 mb-1">Updated To</label>
                    <input 
                        type="date" 
                        id="product-updated-to" 
                        class="admin-form-input w-full"
                    >
                </div>
                
                <!-- Has Image -->
                <div>
                    <label for="product-has-image" class="block text-sm font-medium text-gray-700 mb-1">Has Image</label>
                    <select id="product-has-image" class="admin-form-select w-full">
                        <option value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                
                <!-- Has SKU -->
                <div>
                    <label for="product-has-sku" class="block text-sm font-medium text-gray-700 mb-1">Has SKU</label>
                    <select id="product-has-sku" class="admin-form-select w-full">
                        <option value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                
                <!-- Has Price -->
                <div>
                    <label for="product-has-price" class="block text-sm font-medium text-gray-700 mb-1">Has Price</label>
                    <select id="product-has-price" class="admin-form-select w-full">
                        <option value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Column Visibility Controls -->
    <div class="pb-2 mb-2 flex justify-end">
        <div class="relative">
            <button
                type="button"
                id="column-visibility-toggle"
                class="text-xs text-gray-500 hover:text-gray-700"
            >
                Columns
            </button>
                <div
                    id="column-visibility-panel"
                    class="hidden absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-20 p-4 space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700">Visible columns</p>
                        <button type="button" id="column-visibility-close" class="text-gray-500 hover:text-gray-900 text-sm">✕</button>
                    </div>
                    <div id="column-visibility-options" class="space-y-2 max-h-60 overflow-auto text-sm"></div>
                    <p class="text-xs text-gray-500">Selections are saved to your browser.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="border border-gray-200 overflow-hidden">
        <div id="products-loading" class="p-6 text-center text-sm text-gray-500">
            Loading...
        </div>
        <div id="products-error" class="hidden p-8 text-center text-red-600">
            <div class="admin-empty">
                <div class="admin-empty-icon">⚠️</div>
                <p class="text-lg font-medium">Failed to load products</p>
                <p class="text-sm mt-2">Please refresh the page</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="products-table" class="admin-table w-full text-left text-sm" style="display: none;">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide" data-column="image">Image</th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="name" data-sort="name">
                            Name
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="sku" data-sort="sku">
                            SKU
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="category" data-sort="category">
                            Category
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="price" data-sort="price">
                            Price
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide" data-column="summary">
                            Summary
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="status" data-sort="status">
                            Status
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide" data-column="hero">
                            Hero Image URL
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="created" data-sort="createdAt">
                            Created
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide cursor-pointer hover:bg-gray-50" data-column="updated" data-sort="updatedAt">
                            Updated
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-tbody" class="divide-y divide-gray-200">
                    <!-- Products will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="pagination-container" class="hidden px-3 py-2 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-gray-600">
                    Showing <span id="pagination-from">0</span> to <span id="pagination-to">0</span> of <span id="pagination-total">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        id="pagination-prev" 
                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        Previous
                    </button>
                    <div id="pagination-pages" class="flex items-center gap-1">
                        <!-- Page numbers will be inserted here -->
                    </div>
                    <button 
                        id="pagination-next" 
                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="product-modal" class="admin-modal hidden">
    <div class="admin-modal-content">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
            <h2 id="product-modal-title" class="text-sm font-medium text-gray-900">New Product</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-xl leading-none" id="product-modal-close">&times;</button>
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
                        <button type="button" onclick="document.getElementById('product-hero-image-file').click()" class="admin-btn admin-btn-secondary" style="white-space: nowrap;">Upload</button>
                    </div>
                    <div id="product-hero-image-preview" class="mt-2"></div>
                    <p class="text-xs text-gray-500 mt-1">All image sizes are accepted. Images will be automatically optimized after upload.</p>
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

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 mt-3">
                <button type="button" id="product-form-cancel" class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1.5">
                    Cancel
                </button>
                <button type="submit" id="product-submit-btn" class="text-xs font-medium bg-gray-900 text-white px-3 py-1.5 hover:bg-gray-800">
                    Save
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

    // Helper function to check if URL is external
    function isExternalUrl(url) {
        if (!url) return false;
        // Check if URL starts with http:// or https:// and is not from same origin
        if (url.startsWith('http://') || url.startsWith('https://')) {
            try {
                const urlObj = new URL(url);
                const currentOrigin = window.location.origin;
                return urlObj.origin !== currentOrigin;
            } catch (e) {
                // If URL parsing fails, assume it's external if it starts with http
                return true;
            }
        }
        return false;
    }

    // Helper function to convert relative path to full URL for display
    function getFullUrlForDisplay(url) {
        if (!url) return '';
        const trimmed = url.trim();
        
        // If it's already a full URL, return as-is
        if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
            return trimmed;
        }
        
        // If it's a relative path, convert to full URL
        if (trimmed.startsWith('/')) {
            return window.location.origin + trimmed;
        }
        
        // If it doesn't start with /, add it
        return window.location.origin + '/' + trimmed;
    }

    // Update image preview based on status and URL
    function updateImagePreview() {
        const preview = document.getElementById('product-hero-image-preview');
        let imageUrl = form.heroImage.value.trim();
        const status = form.status.value;
        
        if (!imageUrl) {
            preview.innerHTML = '';
            return;
        }
        
        // Ensure we have a full URL for preview (convert relative paths)
        if (imageUrl.startsWith('/') && !imageUrl.startsWith('//')) {
            imageUrl = window.location.origin + imageUrl;
        }
        
        // For DRAFT products, don't show external images
        if (status === 'DRAFT' && isExternalUrl(imageUrl)) {
            preview.innerHTML = `
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ External images are disabled for draft products.</strong><br>
                        Please upload a local image or publish the product to view external images.
                    </p>
                </div>
            `;
        } else {
            preview.innerHTML = `<img src="${escapeHtml(imageUrl)}" alt="Preview" class="h-32 w-auto rounded border border-gray-300 object-contain">`;
        }
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
        // Convert relative path to full URL for display
        form.heroImage.value = data?.heroImage ? getFullUrlForDisplay(data.heroImage) : '';
        form.summary.value = data?.summary || '';
        form.description.value = data?.description || '';
        form.specs.value = formatJsonField(data?.specs);
        form.highlights.value = formatJsonField(data?.highlights);

        // Update image preview based on status
        updateImagePreview();

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

        // Helper function to convert full URL to relative path if it's from our domain
        function normalizeImageUrl(url) {
            if (!url) return null;
            const trimmed = url.trim();
            if (!trimmed) return null;
            
            // If it's already a relative path, return as-is
            if (trimmed.startsWith('/')) {
                return trimmed;
            }
            
            // If it's a full URL, check if it's from our domain
            try {
                const urlObj = new URL(trimmed);
                const currentOrigin = window.location.origin;
                
                // If it's from our domain, extract the path
                if (urlObj.origin === currentOrigin) {
                    return urlObj.pathname;
                }
                
                // If it's an external URL, return as-is (for external images)
                return trimmed;
            } catch (e) {
                // If URL parsing fails, assume it's a relative path or invalid
                return trimmed.startsWith('/') ? trimmed : '/' + trimmed;
            }
        }

        const payload = {
            name: form.name.value.trim(),
            slug: form.slug.value.trim() || null,
            categoryId: form.categoryId.value,
            status: form.status.value,
            price: form.price.value ? parseFloat(form.price.value) : null,
            sku: form.sku.value.trim() || null,
            heroImage: normalizeImageUrl(form.heroImage.value),
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
        console.log('Upload triggered, file:', file);
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
            formData.append('type', 'product'); // Specify this is a product image upload

            const response = await fetch('/api/admin/upload.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();
            console.log('Upload response:', result);
            console.log('Response data:', result.data);
            console.log('Relative path:', result.data?.relativePath);
            console.log('Full URL:', result.data?.url);

            if (result.status === 'success') {
                // Display the full URL in the input field for user visibility
                input.value = result.data.url || result.data.relativePath;
                
                // Show preview using the full URL
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

    // Add event listeners for status and image URL changes
    form.status.addEventListener('change', updateImagePreview);
    form.heroImage.addEventListener('input', updateImagePreview);
    form.heroImage.addEventListener('change', updateImagePreview);

    openBtn.addEventListener('click', () => showModal(null));
    closeBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);
    form.addEventListener('submit', handleSubmit);

    // Pagination state
    let currentPage = 1;
    let itemsPerPage = 25;
    let totalPages = 1;
    let totalProducts = 0;

    const columnDefinitions = [
        { key: 'image', label: 'Image', default: true },
        { key: 'name', label: 'Name', default: true },
        { key: 'sku', label: 'SKU', default: false },
        { key: 'category', label: 'Category', default: true },
        { key: 'price', label: 'Price', default: true },
        { key: 'summary', label: 'Summary', default: false },
        { key: 'status', label: 'Status', default: true },
        { key: 'hero', label: 'Hero Image URL', default: false },
        { key: 'created', label: 'Created', default: false },
        { key: 'updated', label: 'Updated', default: true },
    ];
    const columnStateKey = 'adminProductColumns';
    let columnVisibility = loadColumnVisibility();

    // Get current filters
    function getFilters() {
        return {
            search: document.getElementById('product-search').value.trim(),
            categoryId: document.getElementById('product-category-filter').value,
            status: document.getElementById('product-status-filter').value,
            sortBy: document.getElementById('product-sort').value,
            sortOrder: document.querySelector('input[name="sort-order"]:checked').value,
            priceMin: document.getElementById('product-price-min').value,
            priceMax: document.getElementById('product-price-max').value,
            createdFrom: document.getElementById('product-created-from').value,
            createdTo: document.getElementById('product-created-to').value,
            updatedFrom: document.getElementById('product-updated-from').value,
            updatedTo: document.getElementById('product-updated-to').value,
            hasImage: document.getElementById('product-has-image').value,
            hasSku: document.getElementById('product-has-sku').value,
            hasPrice: document.getElementById('product-has-price').value
        };
    }

    // Build query string from filters
    function buildQueryString(filters, page = 1, limit = 25) {
        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.categoryId) params.append('categoryId', filters.categoryId);
        if (filters.status) params.append('status', filters.status);
        if (filters.sortBy) params.append('sortBy', filters.sortBy);
        if (filters.sortOrder) params.append('sortOrder', filters.sortOrder);
        if (filters.priceMin) params.append('priceMin', filters.priceMin);
        if (filters.priceMax) params.append('priceMax', filters.priceMax);
        if (filters.createdFrom) params.append('createdFrom', filters.createdFrom);
        if (filters.createdTo) params.append('createdTo', filters.createdTo);
        if (filters.updatedFrom) params.append('updatedFrom', filters.updatedFrom);
        if (filters.updatedTo) params.append('updatedTo', filters.updatedTo);
        if (filters.hasImage) params.append('hasImage', filters.hasImage);
        if (filters.hasSku) params.append('hasSku', filters.hasSku);
        if (filters.hasPrice) params.append('hasPrice', filters.hasPrice);
        params.append('limit', limit);
        params.append('offset', (page - 1) * limit);
        return params.toString();
    }

    // Toggle advanced filters
    document.getElementById('toggle-advanced-filters').addEventListener('click', () => {
        const advancedFilters = document.getElementById('advanced-filters');
        const text = document.getElementById('advanced-filters-text');
        if (advancedFilters.classList.contains('hidden')) {
            advancedFilters.classList.remove('hidden');
            text.textContent = 'Hide Advanced Filters';
        } else {
            advancedFilters.classList.add('hidden');
            text.textContent = 'Show Advanced Filters';
        }
    });

    // Clear all filters
    document.getElementById('clear-filters-btn').addEventListener('click', () => {
        document.getElementById('product-search').value = '';
        document.getElementById('product-category-filter').value = '';
        document.getElementById('product-status-filter').value = '';
        document.getElementById('product-sort').value = 'updatedAt';
        document.querySelector('input[name="sort-order"][value="DESC"]').checked = true;
        document.getElementById('product-price-min').value = '';
        document.getElementById('product-price-max').value = '';
        document.getElementById('product-created-from').value = '';
        document.getElementById('product-created-to').value = '';
        document.getElementById('product-updated-from').value = '';
        document.getElementById('product-updated-to').value = '';
        document.getElementById('product-has-image').value = '';
        document.getElementById('product-has-sku').value = '';
        document.getElementById('product-has-price').value = '';
        currentPage = 1;
        loadAllProducts();
    });

    // Items per page change
    document.getElementById('items-per-page').addEventListener('change', (e) => {
        itemsPerPage = parseInt(e.target.value);
        currentPage = 1;
        loadAllProducts();
    });

    // Add event listeners for filters
    ['product-search', 'product-category-filter', 'product-status-filter', 'product-sort',
     'product-price-min', 'product-price-max', 'product-created-from', 'product-created-to',
     'product-updated-from', 'product-updated-to', 'product-has-image', 'product-has-sku', 'product-has-price'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            if (id === 'product-search') {
                // Debounce search input
                let searchTimeout;
                el.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    currentPage = 1;
                    searchTimeout = setTimeout(() => loadAllProducts(), 500);
                });
            } else {
                el.addEventListener('change', () => {
                    currentPage = 1;
                    loadAllProducts();
                });
            }
        }
    });

    // Sort order change
    document.querySelectorAll('input[name="sort-order"]').forEach(radio => {
        radio.addEventListener('change', () => {
            currentPage = 1;
            loadAllProducts();
        });
    });

    // Load products from API with pagination
    async function loadAllProducts() {
        const loadingEl = document.getElementById('products-loading');
        const errorEl = document.getElementById('products-error');
        const tableEl = document.getElementById('products-table');
        const tbodyEl = document.getElementById('products-tbody');
        const paginationEl = document.getElementById('pagination-container');

        try {
            loadingEl.classList.remove('hidden');
            errorEl.classList.add('hidden');
            tableEl.style.display = 'none';
            paginationEl.classList.add('hidden');

            const filters = getFilters();
            const queryString = buildQueryString(filters, currentPage, itemsPerPage);
            const response = await fetch(`/api/admin/products/index.php?${queryString}`);
            const result = await response.json();

            if (result.status === 'success' && result.data.products) {
                renderProducts(result.data.products);
                loadingEl.classList.add('hidden');
                tableEl.style.display = 'table';
                
                // Update pagination info
                if (result.data.pagination) {
                    totalProducts = result.data.pagination.total;
                    totalPages = result.data.pagination.totalPages;
                    currentPage = result.data.pagination.currentPage;
                    
                    updatePagination(result.data.pagination);
                    paginationEl.classList.remove('hidden');
                }
                
                // Update product count
                const countEl = document.getElementById('product-count');
                countEl.textContent = `(${totalProducts} ${totalProducts === 1 ? 'product' : 'products'})`;
            } else {
                throw new Error(result.message || 'Failed to load products');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    }

    // Update pagination controls
    function updatePagination(pagination) {
        const from = pagination.offset + 1;
        const to = Math.min(pagination.offset + pagination.count, pagination.total);
        
        document.getElementById('pagination-from').textContent = from;
        document.getElementById('pagination-to').textContent = to;
        document.getElementById('pagination-total').textContent = pagination.total;
        
        // Update prev/next buttons
        document.getElementById('pagination-prev').disabled = !pagination.hasPrevious;
        document.getElementById('pagination-next').disabled = !pagination.hasMore;
        
        // Generate page numbers
        const pagesContainer = document.getElementById('pagination-pages');
        pagesContainer.innerHTML = '';
        
        const maxPagesToShow = 7;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        
        if (endPage - startPage < maxPagesToShow - 1) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }
        
        // First page
        if (startPage > 1) {
            const btn = createPageButton(1);
            pagesContainer.appendChild(btn);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2 text-gray-500';
                ellipsis.textContent = '...';
                pagesContainer.appendChild(ellipsis);
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const btn = createPageButton(i);
            if (i === currentPage) {
                btn.classList.add('text-gray-900', 'font-medium', 'border-b-2', 'border-gray-900');
                btn.classList.remove('text-gray-600', 'hover:text-gray-900');
            }
            pagesContainer.appendChild(btn);
        }
        
        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2 text-gray-500';
                ellipsis.textContent = '...';
                pagesContainer.appendChild(ellipsis);
            }
            const btn = createPageButton(totalPages);
            pagesContainer.appendChild(btn);
        }
    }

    // Create page button
    function createPageButton(page) {
        const btn = document.createElement('button');
        btn.className = 'text-sm text-gray-600 hover:text-gray-900 px-3 py-1 min-w-[2.5rem]';
        btn.textContent = page;
        btn.addEventListener('click', () => {
            currentPage = page;
            loadAllProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        return btn;
    }

    // Update sort indicators
    function updateSortIndicators() {
        const sortBy = document.getElementById('product-sort').value;
        const sortOrder = document.querySelector('input[name="sort-order"]:checked').value;
        
        document.querySelectorAll('th[data-sort]').forEach(th => {
            const indicator = th.querySelector('.sort-indicator');
            if (th.dataset.sort === sortBy) {
                indicator.textContent = sortOrder === 'ASC' ? ' ↑' : ' ↓';
                indicator.className = 'sort-indicator text-blue-600 font-bold';
            } else {
                indicator.textContent = '';
                indicator.className = 'sort-indicator';
            }
        });
    }

    // Make table headers sortable
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const sortField = th.dataset.sort;
            const currentSort = document.getElementById('product-sort').value;
            const currentOrder = document.querySelector('input[name="sort-order"]:checked').value;
            
            if (sortField === currentSort) {
                // Toggle order if clicking same field
                const newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
                document.querySelector(`input[name="sort-order"][value="${newOrder}"]`).checked = true;
            } else {
                // Change sort field
                document.getElementById('product-sort').value = sortField;
            }
            
            currentPage = 1;
            loadAllProducts();
        });
    });

    // Pagination prev/next handlers
    document.getElementById('pagination-prev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadAllProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('pagination-next').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadAllProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Get primary action button based on product status
    function getPrimaryActionButton(product) {
        const status = product.status || 'DRAFT';
        const slug = product.slug || '';
        const productId = escapeHtml(product.id);
        const productName = escapeHtml(product.name);

        if (status === 'PUBLISHED') {
            // View on frontend
            if (slug) {
                return `
                    <a
                        href="/product.php?slug=${encodeURIComponent(slug)}"
                        target="_blank"
                        class="text-xs text-gray-500 hover:text-gray-700"
                        title="View on website"
                    >
                        View
                    </a>
                `;
            }
        } else if (status === 'DRAFT') {
            // Quick Publish
            return `
                <button
                    type="button"
                    class="text-xs text-gray-500 hover:text-gray-700 product-publish-btn"
                    data-product-id="${productId}"
                    data-product-name="${productName}"
                    title="Publish this product"
                >
                    Publish
                </button>
            `;
        } else if (status === 'ARCHIVED') {
            // Restore/Publish
            return `
                <button
                    type="button"
                    class="text-xs text-gray-500 hover:text-gray-700 product-publish-btn"
                    data-product-id="${productId}"
                    data-product-name="${productName}"
                    title="Restore and publish this product"
                >
                    Restore
                </button>
            `;
        }

        // Fallback: Edit button
        return `
            <button
                type="button"
                class="text-xs text-gray-500 hover:text-gray-700 product-edit-btn"
                data-product-id="${productId}"
            >
                Edit
            </button>
        `;
    }

    // Render products in table
    function renderProducts(products) {
        const tbodyEl = document.getElementById('products-tbody');
        tbodyEl.innerHTML = '';

        if (products.length === 0) {
            tbodyEl.innerHTML = '<tr><td colspan="11" class="px-6 py-8 text-center text-gray-500">No products found.</td></tr>';
            applyColumnVisibility();
            return;
        }

        // Update sort indicators
        updateSortIndicators();

        products.forEach((product) => {
            const row = document.createElement('tr');
            
            const statusClass = product.status === 'PUBLISHED' 
                ? 'admin-badge-success' 
                : (product.status === 'DRAFT' ? 'admin-badge-warning' : 'admin-badge-danger');
            
            const updatedDate = product.updatedAt 
                ? new Date(product.updatedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';

            const createdDate = product.createdAt
                ? new Date(product.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';

            const price = product.price ? '$' + parseFloat(product.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
            const sku = product.sku ? escapeHtml(product.sku) : '—';
            const summary = product.summary ? escapeHtml(truncateText(product.summary, 120)) : '—';
            const heroDisplay = product.heroImage
                ? `<div class="text-xs text-gray-600 max-w-[220px] truncate" title="${escapeHtml(product.heroImage)}">${escapeHtml(product.heroImage)}</div>`
                : '<span class="text-gray-400">—</span>';
            
            // For DRAFT products, don't load external images
            let imageHtml = '<span class="text-gray-400">—</span>';
            if (product.heroImage) {
                if (product.status === 'DRAFT' && isExternalUrl(product.heroImage)) {
                    // Show placeholder for draft products with external images
                    imageHtml = '<div class="h-10 w-10 md:h-12 md:w-12 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 text-xs" title="External images disabled for draft products">Draft</div>';
                } else {
                    // Show image for published products or local images
                    imageHtml = `<img src="${escapeHtml(product.heroImage)}" alt="${escapeHtml(product.name)}" class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-cover">`;
                }
            }
            
            row.innerHTML = `
                <td class="px-3 py-2" data-label="Image" data-column="image">
                    ${imageHtml}
                </td>
                <td class="px-3 py-2 text-sm font-medium text-gray-900" data-label="Name" data-column="name">${escapeHtml(product.name)}</td>
                <td class="px-3 py-2 text-xs text-gray-600" data-label="SKU" data-column="sku">${sku}</td>
                <td class="px-3 py-2" data-label="Category" data-column="category">
                    ${product.category_name 
                        ? `<span class="text-xs text-gray-600 hover:text-gray-900 cursor-pointer category-badge border-b border-transparent hover:border-gray-400" data-category-id="${escapeHtml(product.categoryId || '')}" title="Click to filter by ${escapeHtml(product.category_name)}">${escapeHtml(product.category_name)}</span>`
                        : '<span class="text-gray-400 text-xs">—</span>'
                    }
                </td>
                <td class="px-3 py-2 text-xs text-gray-600" data-label="Price" data-column="price">${price}</td>
                <td class="px-3 py-2 text-xs text-gray-600" data-label="Summary" data-column="summary">
                    <div class="max-w-xl">${summary}</div>
                </td>
                <td class="px-3 py-2" data-label="Status" data-column="status">
                    <span class="text-xs text-gray-500 uppercase">${escapeHtml(product.status)}</span>
                </td>
                <td class="px-3 py-2" data-label="Hero URL" data-column="hero">
                    <div class="text-xs text-gray-500 truncate max-w-xs">${heroDisplay}</div>
                </td>
                <td class="px-3 py-2 text-xs text-gray-500" data-label="Created" data-column="created">${createdDate}</td>
                <td class="px-3 py-2 text-xs text-gray-500" data-label="Updated" data-column="updated">${updatedDate}</td>
                <td class="px-3 py-2" data-label="Actions">
                    <div class="flex items-center gap-2 flex-wrap">
                        ${getPrimaryActionButton(product)}
                        <button
                            type="button"
                            class="text-xs text-gray-500 hover:text-gray-700 product-edit-btn"
                            data-product-id="${escapeHtml(product.id)}"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="text-xs text-gray-500 hover:text-red-600 product-delete-btn"
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

        applyColumnVisibility();

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

        // Attach event listeners to publish/restore buttons
        document.querySelectorAll('.product-publish-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;
                const currentText = button.textContent.trim();

                if (!confirm(`Are you sure you want to publish "${productName}"?`)) {
                    return;
                }

                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="animate-spin">⏳</span> Publishing...';

                try {
                    const response = await fetch(`/api/admin/products/item.php?id=${encodeURIComponent(productId)}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: 'PUBLISHED' }),
                    });

                    const result = await response.json();

                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'Unable to publish product.');
                    }

                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Product published successfully!', 'success');
                    }

                    loadAllProducts(); // Reload products to update status
                } catch (error) {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    alert('Error: ' + error.message);
                }
            });
        });
    }

    function setupColumnControls() {
        const container = document.getElementById('column-visibility-options');
        const panel = document.getElementById('column-visibility-panel');
        const toggleBtn = document.getElementById('column-visibility-toggle');
        const closeBtn = document.getElementById('column-visibility-close');

        if (!container || !panel || !toggleBtn) {
            return;
        }

        container.innerHTML = '';

        columnDefinitions.forEach((def) => {
            const id = `column-toggle-${def.key}`;
            const wrapper = document.createElement('label');
            wrapper.className = 'flex items-center gap-2 text-gray-700';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = id;
            checkbox.checked = columnVisibility[def.key] !== false;
            checkbox.className = 'rounded text-blue-600';
            checkbox.addEventListener('change', () => {
                columnVisibility[def.key] = checkbox.checked;
                saveColumnVisibility();
                applyColumnVisibility();
            });

            const span = document.createElement('span');
            span.textContent = def.label;

            wrapper.appendChild(checkbox);
            wrapper.appendChild(span);
            container.appendChild(wrapper);
        });

        toggleBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            panel.classList.toggle('hidden');
        });

        closeBtn?.addEventListener('click', () => panel.classList.add('hidden'));

        document.addEventListener('click', (event) => {
            if (!panel.contains(event.target) && !toggleBtn.contains(event.target)) {
                panel.classList.add('hidden');
            }
        });
    }

    function applyColumnVisibility() {
        columnDefinitions.forEach((def) => {
            const visible = columnVisibility[def.key] !== false;
            document.querySelectorAll(`[data-column="${def.key}"]`).forEach((el) => {
                el.classList.toggle('hidden', !visible);
            });
        });
    }

    function loadColumnVisibility() {
        const defaults = {};
        columnDefinitions?.forEach((def) => {
            defaults[def.key] = def.default;
        });

        try {
            const stored = localStorage.getItem(columnStateKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                return { ...defaults, ...parsed };
            }
        } catch (error) {
            console.warn('Failed to load column preferences', error);
        }
        return defaults;
    }

    function saveColumnVisibility() {
        try {
            localStorage.setItem(columnStateKey, JSON.stringify(columnVisibility));
        } catch (error) {
            console.warn('Failed to save column preferences', error);
        }
    }

    function truncateText(text, length = 100) {
        if (!text || text.length <= length) {
            return text || '';
        }
        return `${text.substring(0, length - 1)}…`;
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Simple toast notification function
    function showToast(message, type = 'success') {
        if (window.toast && typeof window.toast.show === 'function') {
            window.toast.show(message, type);
            return;
        }

        // Fallback: create a simple toast
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full opacity-0`;
        toast.textContent = message;
        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Initialize column controls and render
    setupColumnControls();
    applyColumnVisibility();

    // Load products on page load
    loadAllProducts();

    // ============================================
    // PRODUCT STATISTICS
    // ============================================
    
    async function loadStatistics() {
        const loadingEl = document.getElementById('statistics-loading');
        const contentEl = document.getElementById('statistics-content');
        const errorEl = document.getElementById('statistics-error');
        
        try {
            loadingEl.classList.remove('hidden');
            contentEl.classList.add('hidden');
            errorEl.classList.add('hidden');
            
            const response = await fetch('/api/admin/products/statistics.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                const stats = result.data;
                
                // Update overview cards (safely check for element existence)
                const setTextContent = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                };
                
                setTextContent('stat-total', stats.total.toLocaleString());
                setTextContent('stat-published', (stats.byStatus.PUBLISHED || 0).toLocaleString());
                setTextContent('stat-draft', (stats.byStatus.DRAFT || 0).toLocaleString());
                
                // Update statistics (simplified display)
                const withImages = stats.images?.with_image || 0;
                const withPrices = stats.prices?.with_price || 0;
                
                setTextContent('stat-with-images', withImages.toLocaleString());
                setTextContent('stat-with-prices', withPrices.toLocaleString());
                
                
                loadingEl.classList.add('hidden');
                contentEl.classList.remove('hidden');
            } else {
                throw new Error(result.message || 'Failed to load statistics');
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
            loadingEl.classList.add('hidden');
            contentEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    }
    
    // Refresh statistics button (handle both error state and normal refresh)
    document.getElementById('refresh-statistics')?.addEventListener('click', loadStatistics);
    
    // Load statistics on page load
    loadStatistics();

    // ============================================
    // CATEGORIES QUICK ACCESS
    // ============================================
    
    async function loadCategories() {
        const categoriesListEl = document.getElementById('categories-quick-list');
        if (!categoriesListEl) return;
        
        try {
            const response = await fetch('/api/admin/categories/index.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.data && result.data.categories) {
                categoriesListEl.innerHTML = '';
                
                if (result.data.categories.length === 0) {
                    categoriesListEl.innerHTML = '<p class="text-sm text-gray-500">No categories found. <a href="/ae-admin/categories.php" class="text-blue-600 hover:underline">Create one</a></p>';
                    return;
                }
                
                // Add "All Categories" button first
                const allBtn = document.createElement('button');
                allBtn.type = 'button';
                allBtn.className = 'category-filter-badge inline-flex items-center px-2 py-1 text-xs font-medium text-gray-900 border-b-2 border-gray-900';
                allBtn.textContent = 'All';
                allBtn.addEventListener('click', () => {
                    const filterSelect = document.getElementById('product-category-filter');
                    filterSelect.value = '';
                    filterSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    currentPage = 1;
                    loadAllProducts();
                    updateCategoryBadges(null);
                });
                categoriesListEl.appendChild(allBtn);
                
                result.data.categories.forEach(category => {
                    const badge = document.createElement('button');
                    badge.type = 'button';
                    badge.className = 'category-filter-badge inline-flex items-center px-2 py-1 text-xs font-normal text-gray-600 hover:text-gray-900 border-b border-transparent hover:border-gray-400 transition-colors';
                    badge.dataset.categoryId = category.id;
                    const productCount = category.product_count || category.productCount || 0;
                    badge.innerHTML = `
                        <span>${escapeHtml(category.name)}</span>
                        <span class="ml-1.5 text-gray-400">${productCount}</span>
                    `;
                    
                    badge.addEventListener('click', () => {
                        const filterSelect = document.getElementById('product-category-filter');
                        filterSelect.value = category.id;
                        // Trigger change event to ensure filter is applied
                        filterSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        currentPage = 1;
                        loadAllProducts();
                        updateCategoryBadges(category.id);
                    });
                    
                    categoriesListEl.appendChild(badge);
                });
                
            } else {
                categoriesListEl.innerHTML = '<p class="text-sm text-red-500">Failed to load categories</p>';
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            const categoriesListEl = document.getElementById('categories-quick-list');
            if (categoriesListEl) {
                categoriesListEl.innerHTML = '<p class="text-sm text-red-500">Error loading categories</p>';
            }
        }
    }
    
    function updateCategoryBadges(activeCategoryId) {
        document.querySelectorAll('.category-filter-badge').forEach(badge => {
            if (badge.dataset.categoryId === activeCategoryId || (!activeCategoryId && badge.textContent.trim() === 'All')) {
                badge.classList.remove('text-gray-600', 'border-transparent');
                badge.classList.add('text-gray-900', 'border-gray-900', 'border-b-2');
            } else {
                badge.classList.remove('text-gray-900', 'border-gray-900', 'border-b-2');
                badge.classList.add('text-gray-600', 'border-transparent');
            }
        });
    }
    
    // Make category badges in table clickable
    document.addEventListener('click', (e) => {
        const categoryBadge = e.target.closest('.category-badge');
        if (categoryBadge && categoryBadge.dataset.categoryId) {
            const categoryId = categoryBadge.dataset.categoryId;
            if (categoryId) {
                const filterSelect = document.getElementById('product-category-filter');
                filterSelect.value = categoryId;
                filterSelect.dispatchEvent(new Event('change', { bubbles: true }));
                currentPage = 1;
                loadAllProducts();
                updateCategoryBadges(categoryId);
            }
        }
    });
    
    // Load categories on page load
    loadCategories();

})();
</script>

    </div> <!-- End content wrapper -->
</div> <!-- End main container -->

<?php include __DIR__ . '/includes/footer.php'; ?>
