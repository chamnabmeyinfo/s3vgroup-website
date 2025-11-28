<?php
session_start();
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

use App\Domain\Menus\MenuRepository;
use App\Application\Services\MenuService;

$db = getDB();
$repository = new MenuRepository($db);
$menuService = new MenuService($repository);

// Get all menus
$menus = $menuService->getAllMenus();
$selectedMenuId = $_GET['menu'] ?? ($menus[0]['id'] ?? null);
$selectedMenu = $selectedMenuId ? $menuService->getMenuWithItems($selectedMenuId) : null;

$pageTitle = 'Menus';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="admin-page-header">
        <h1 class="admin-page-title">Menu Management</h1>
        <p class="admin-page-subtitle">Create and organize navigation menus with drag & drop, mega menu support</p>
    </div>
    
    <div class="admin-card">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Your Menus</h2>
                    <p class="text-sm text-gray-500">Manage navigation menus and menu items</p>
                </div>
            </div>
            <button 
                id="create-menu-btn"
                class="admin-btn admin-btn-primary"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create New Menu
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Menus List Sidebar -->
        <div class="lg:col-span-1">
            <div class="admin-card">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Menus</h2>
                <div id="menus-list" class="space-y-2">
                    <?php if (empty($menus)): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No menus yet</p>
                    <?php else: ?>
                        <?php foreach ($menus as $menu): ?>
                            <a 
                                href="?menu=<?php echo e($menu['id']); ?>"
                                class="block px-3 py-2 rounded text-sm font-medium transition-all <?php echo $selectedMenuId === $menu['id'] ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-50 border border-transparent'; ?>"
                                data-menu-id="<?php echo e($menu['id']); ?>"
                            >
                                <div class="flex items-center justify-between">
                                    <span><?php echo e($menu['name']); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo e($menu['location']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Menu Editor -->
        <div class="lg:col-span-3">
            <?php if ($selectedMenu): ?>
                <div class="admin-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900"><?php echo e($selectedMenu['name']); ?></h2>
                            <p class="text-xs text-gray-500 mt-1">Location: <?php echo e($selectedMenu['location']); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                id="add-menu-item-btn"
                                class="admin-btn admin-btn-secondary"
                                data-menu-id="<?php echo e($selectedMenu['id']); ?>"
                            >
                                Add Items
                            </button>
                            <button 
                                id="save-menu-btn"
                                class="admin-btn admin-btn-primary"
                            >
                                Save Menu
                            </button>
                        </div>
                    </div>
                    <div>
                        <!-- Menu Items Tree with Drag & Drop -->
                        <div id="menu-items-container" class="space-y-2">
                            <?php if (empty($selectedMenu['items'])): ?>
                                <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-900 mb-1">No menu items</p>
                                    <p class="text-xs text-gray-500">Click "Add Items" to start building your menu</p>
                                </div>
                            <?php else: ?>
                                <ul id="menu-items-sortable" class="space-y-2">
                                    <?php 
                                    function renderMenuItem($item, $level = 0) {
                                        $isMega = !empty($item['is_mega_menu']);
                                        ?>
                                        <li 
                                            class="menu-item bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:shadow-md transition-all"
                                            data-item-id="<?php echo e($item['id']); ?>"
                                            data-parent-id="<?php echo e($item['parent_id'] ?? ''); ?>"
                                            data-level="<?php echo $level; ?>"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3 flex-1">
                                                    <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" title="Drag to reorder">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                        </svg>
                                                    </div>
                                                    <?php if ($item['icon']): ?>
                                                        <span class="text-lg"><?php echo e($item['icon']); ?></span>
                                                    <?php endif; ?>
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-semibold text-gray-900"><?php echo e($item['title']); ?></span>
                                                            <span class="text-xs text-gray-500">(<?php echo e($item['type'] ?? 'custom'); ?>)</span>
                                                            <?php if ($isMega): ?>
                                                                <span class="admin-badge admin-badge-info">Mega</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e($item['url']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button 
                                                        class="edit-item-btn admin-btn admin-btn-secondary text-sm"
                                                        data-item='<?php echo json_encode($item); ?>'
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        class="delete-item-btn admin-btn admin-btn-danger text-sm"
                                                        data-item-id="<?php echo e($item['id']); ?>"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                        if (!empty($item['children'])) {
                                            echo '<ul class="ml-6 space-y-2 mt-2">';
                                            foreach ($item['children'] as $child) {
                                                renderMenuItem($child, $level + 1);
                                            }
                                            echo '</ul>';
                                        }
                                    }
                                    
                                    foreach ($selectedMenu['items'] as $item) {
                                        renderMenuItem($item);
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="admin-card admin-empty">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-900 mb-2">No menu selected</p>
                    <p class="text-sm text-gray-500 mb-6">Create a new menu or select an existing one to start editing</p>
                    <button 
                        id="create-menu-btn-empty"
                        class="admin-btn admin-btn-primary"
                    >
                        Create New Menu
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Menu Modal -->
<div id="create-menu-modal" class="admin-modal hidden">
    <div class="admin-modal-content">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Create New Menu</h3>
        </div>
        <div class="p-6">
            <form id="create-menu-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Menu Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required
                        class="admin-form-input"
                        placeholder="e.g., Main Menu"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                    <select 
                        name="location"
                        class="admin-form-select"
                    >
                        <option value="primary">Primary Navigation</option>
                        <option value="secondary">Secondary Navigation</option>
                        <option value="footer">Footer Menu</option>
                        <option value="mobile">Mobile Menu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
                    <textarea 
                        name="description"
                        rows="2"
                        class="admin-form-textarea"
                        placeholder="Menu description..."
                    ></textarea>
                </div>
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        id="cancel-create-menu"
                        class="flex-1 admin-btn admin-btn-secondary"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 admin-btn admin-btn-primary"
                    >
                        Create Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Menu Items Modal -->
<div id="add-items-modal" class="admin-modal hidden">
    <div class="admin-modal-content max-w-4xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Add Menu Items</h3>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-4" role="tablist">
                    <button class="tab-btn active px-4 py-2 text-sm font-medium text-violet-600 border-b-2 border-violet-600" data-tab="custom">
                        Custom Link
                    </button>
                    <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="pages">
                        Pages
                    </button>
                    <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="posts">
                        Posts
                    </button>
                    <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="categories">
                        Categories
                    </button>
                    <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="products">
                        Products
                    </button>
                </nav>
            </div>

            <!-- Custom Link Tab -->
            <div id="tab-custom" class="tab-content">
                <form id="custom-link-form" class="space-y-4">
                    <input type="hidden" name="menu_id" id="add-item-menu-id">
                    <input type="hidden" name="type" value="custom">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                            <input 
                                type="text" 
                                name="title" 
                                required
                                class="admin-form-input"
                                placeholder="Menu item title"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">URL *</label>
                            <input 
                                type="text" 
                                name="url" 
                                required
                                class="admin-form-input"
                                placeholder="/page or https://..."
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Emoji)</label>
                            <input 
                                type="text" 
                                name="icon"
                                class="admin-form-input"
                                placeholder="🏠"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Target</label>
                            <select 
                                name="target"
                                class="admin-form-input"
                            >
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CSS Classes</label>
                        <input 
                            type="text" 
                            name="css_classes"
                            class="admin-form-input"
                            placeholder="custom-class another-class"
                        >
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <label class="flex items-center gap-3 p-4 bg-violet-50 rounded-lg border border-violet-200 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="is_mega_menu" 
                                value="1"
                                class="w-5 h-5 text-violet-600 rounded focus:ring-violet-500"
                            >
                            <div class="flex-1">
                                <span class="text-sm font-semibold text-gray-900">Enable Mega Menu</span>
                                <p class="text-xs text-gray-600 mt-0.5">Show this item as a mega menu with columns</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button"
                            id="cancel-add-items"
                            class="flex-1 admin-btn admin-btn-secondary"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 admin-btn admin-btn-primary"
                        >
                            Add to Menu
                        </button>
                    </div>
                </form>
            </div>

            <!-- Pages Tab -->
            <div id="tab-pages" class="tab-content hidden">
                <div class="mb-4">
                    <input 
                        type="search" 
                        id="search-pages"
                        placeholder="Search pages..."
                            class="admin-form-input"
                    >
                </div>
                <div id="pages-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-sm text-gray-500 text-center py-8">Loading pages...</p>
                </div>
            </div>

            <!-- Posts Tab -->
            <div id="tab-posts" class="tab-content hidden">
                <div class="mb-4">
                    <input 
                        type="search" 
                        id="search-posts"
                        placeholder="Search posts..."
                            class="admin-form-input"
                    >
                </div>
                <div id="posts-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-sm text-gray-500 text-center py-8">Loading posts...</p>
                </div>
            </div>

            <!-- Categories Tab -->
            <div id="tab-categories" class="tab-content hidden">
                <div class="mb-4">
                    <input 
                        type="search" 
                        id="search-categories"
                        placeholder="Search categories..."
                            class="admin-form-input"
                    >
                </div>
                <div id="categories-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-sm text-gray-500 text-center py-8">Loading categories...</p>
                </div>
            </div>

            <!-- Products Tab -->
            <div id="tab-products" class="tab-content hidden">
                <div class="mb-4">
                    <input 
                        type="search" 
                        id="search-products"
                        placeholder="Search products..."
                            class="admin-form-input"
                    >
                </div>
                <div id="products-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-sm text-gray-500 text-center py-8">Loading products...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div id="menu-item-modal" class="admin-modal hidden">
    <div class="admin-modal-content max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Edit Menu Item</h3>
        </div>
        <div class="p-6">
            <form id="menu-item-form" class="space-y-4">
                <input type="hidden" name="menu_id" id="item-menu-id">
                <input type="hidden" name="item_id" id="item-id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                        <input 
                            type="text" 
                            name="title" 
                            required
                            id="item-title"
                            class="admin-form-input"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">URL *</label>
                        <input 
                            type="text" 
                            name="url" 
                            required
                            id="item-url"
                            class="admin-form-input"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Emoji)</label>
                        <input 
                            type="text" 
                            name="icon" 
                            id="item-icon"
                            class="admin-form-input"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Target</label>
                        <select 
                            name="target"
                            id="item-target"
                            class="admin-form-input"
                        >
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Window</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">CSS Classes</label>
                    <input 
                        type="text" 
                        name="css_classes" 
                        id="item-css-classes"
                            class="admin-form-input"
                    >
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <label class="flex items-center gap-3 p-4 bg-violet-50 rounded-lg border border-violet-200 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_mega_menu" 
                            id="item-is-mega-menu"
                            value="1"
                            class="w-5 h-5 text-violet-600 rounded focus:ring-violet-500"
                        >
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-gray-900">Enable Mega Menu</span>
                            <p class="text-xs text-gray-600 mt-0.5">Show this item as a mega menu with columns</p>
                        </div>
                    </label>
                </div>

                <div id="mega-menu-options" class="hidden space-y-4 border-t border-gray-200 pt-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mega Menu Columns</label>
                        <select 
                            name="mega_menu_columns"
                            id="item-mega-columns"
                            class="admin-form-input"
                        >
                            <option value="2">2 Columns</option>
                            <option value="3" selected>3 Columns</option>
                            <option value="4">4 Columns</option>
                            <option value="5">5 Columns</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mega Menu Image URL</label>
                        <input 
                            type="text" 
                            name="mega_menu_image" 
                            id="item-mega-image"
                            class="admin-form-input"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mega Menu Content (HTML)</label>
                        <textarea 
                            name="mega_menu_content" 
                            id="item-mega-content"
                            rows="4"
                            class="admin-form-textarea font-mono text-sm"
                        ></textarea>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        id="cancel-menu-item"
                        class="flex-1 admin-btn admin-btn-secondary"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 admin-btn admin-btn-primary"
                    >
                        Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
(function() {
    let sortableInstance = null;
    const selectedMenuId = '<?php echo e($selectedMenuId ?? ''); ?>';
    
    // Initialize drag and drop
    function initSortable() {
        const container = document.getElementById('menu-items-sortable');
        if (!container) return;
        
        if (sortableInstance) {
            sortableInstance.destroy();
        }
        
        sortableInstance = new Sortable(container, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            group: 'menu-items',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: async function(evt) {
                // Build items array with proper hierarchy
                const items = [];
                let order = 0;
                
                function processItems(parent, level = 0) {
                    const children = Array.from(parent.children).filter(el => el.classList.contains('menu-item'));
                    
                    children.forEach((el) => {
                        const itemId = el.dataset.itemId;
                        const parentId = el.dataset.parentId || null;
                        
                        // Determine actual parent based on DOM structure
                        let actualParentId = parentId;
                        const parentLi = el.closest('ul')?.previousElementSibling;
                        if (parentLi && parentLi.classList.contains('menu-item')) {
                            actualParentId = parentLi.dataset.itemId;
                        }
                        
                        items.push({
                            id: itemId,
                            parent_id: actualParentId,
                            menu_order: order++
                        });
                        
                        // Process nested children
                        const nestedUl = el.querySelector('ul');
                        if (nestedUl) {
                            processItems(nestedUl, level + 1);
                        }
                    });
                }
                
                processItems(container);
                
                try {
                    const response = await fetch('/api/admin/menus/items.php?action=update-order', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ items }),
                    });
                    
                    const result = await response.json();
                    if (result.status === 'success') {
                        // Show success message
                        const btn = document.getElementById('save-menu-btn');
                        const originalText = btn.textContent;
                        btn.textContent = '✓ Saved!';
                        btn.classList.add('bg-green-600');
                        setTimeout(() => {
                            btn.textContent = originalText;
                            btn.classList.remove('bg-green-600');
                        }, 2000);
                    } else {
                        alert('Error updating order: ' + result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        });
    }
    
    // Initialize on page load
    if (selectedMenuId) {
        setTimeout(initSortable, 100);
    }
    
    // Menu management
    const createMenuBtn = document.getElementById('create-menu-btn');
    const createMenuBtnEmpty = document.getElementById('create-menu-btn-empty');
    const createMenuModal = document.getElementById('create-menu-modal');
    const createMenuForm = document.getElementById('create-menu-form');
    const cancelCreateMenu = document.getElementById('cancel-create-menu');
    
    [createMenuBtn, createMenuBtnEmpty].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => {
                createMenuModal.classList.remove('hidden');
            });
        }
    });
    
    cancelCreateMenu?.addEventListener('click', () => {
        createMenuModal.classList.add('hidden');
        createMenuForm.reset();
    });
    
    createMenuForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/api/admin/menus/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = `?menu=${result.data.menu.id}`;
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
    
    // Add items modal
    const addMenuItemBtn = document.getElementById('add-menu-item-btn');
    const addItemsModal = document.getElementById('add-items-modal');
    const cancelAddItems = document.getElementById('cancel-add-items');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    addMenuItemBtn?.addEventListener('click', () => {
        const menuId = addMenuItemBtn.dataset.menuId;
        document.getElementById('add-item-menu-id').value = menuId;
        addItemsModal.classList.remove('hidden');
        loadTabContent('pages');
        loadTabContent('posts');
        loadTabContent('categories');
        loadTabContent('products');
    });
    
    cancelAddItems?.addEventListener('click', () => {
        addItemsModal.classList.add('hidden');
    });
    
    // Tab switching
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            
            // Update buttons
            tabButtons.forEach(b => {
                b.classList.remove('active', 'text-violet-600', 'border-violet-600');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('active', 'text-violet-600', 'border-violet-600');
            btn.classList.remove('text-gray-500');
            
            // Update content
            tabContents.forEach(c => c.classList.add('hidden'));
            document.getElementById(`tab-${tab}`).classList.remove('hidden');
            
            if (tab !== 'custom') {
                loadTabContent(tab);
            }
        });
    });
    
    // Load tab content
    async function loadTabContent(type) {
        const container = document.getElementById(`${type}-list`);
        if (!container) return;
        
        const searchInput = document.getElementById(`search-${type}`);
        let search = '';
        
        if (searchInput) {
            search = searchInput.value;
            searchInput.addEventListener('input', debounce(() => {
                loadTabContent(type);
            }, 300));
        }
        
        try {
            const response = await fetch(`/api/admin/menus/browse.php?type=${type}&search=${encodeURIComponent(search)}`);
            const result = await response.json();
            
            if (result.status === 'success' && result.data.items) {
                if (result.data.items.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">No items found</p>';
                } else {
                    container.innerHTML = result.data.items.map(item => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200 hover:border-blue-300 transition-all">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${escapeHtml(item.title)}</div>
                                <div class="text-xs text-gray-500 mt-0.5">${escapeHtml(item.url)}</div>
                            </div>
                            <button 
                                class="add-item-btn admin-btn admin-btn-primary text-sm"
                                data-item='${JSON.stringify(item)}'
                            >
                                Add
                            </button>
                        </div>
                    `).join('');
                    
                    // Add event listeners
                    container.querySelectorAll('.add-item-btn').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            const item = JSON.parse(btn.dataset.item);
                            await addItemToMenu({
                                menu_id: document.getElementById('add-item-menu-id').value,
                                title: item.title,
                                url: item.url,
                                type: item.type,
                                object_id: item.id,
                                object_type: item.type
                            });
                        });
                    });
                }
            }
        } catch (error) {
            container.innerHTML = `<p class="text-sm text-red-500 text-center py-8">Error loading ${type}: ${error.message}</p>`;
        }
    }
    
    // Add item to menu
    async function addItemToMenu(data) {
        try {
            const response = await fetch('/api/admin/menus/items.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            if (result.status === 'success') {
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    // Custom link form
    const customLinkForm = document.getElementById('custom-link-form');
    customLinkForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        data.is_mega_menu = data.is_mega_menu === '1' ? 1 : 0;
        await addItemToMenu(data);
    });
    
    // Edit menu item
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = JSON.parse(btn.dataset.item);
            document.getElementById('item-id').value = item.id;
            document.getElementById('item-menu-id').value = item.menu_id;
            document.getElementById('item-title').value = item.title;
            document.getElementById('item-url').value = item.url;
            document.getElementById('item-icon').value = item.icon || '';
            document.getElementById('item-target').value = item.target || '_self';
            document.getElementById('item-css-classes').value = item.css_classes || '';
            document.getElementById('item-is-mega-menu').checked = !!item.is_mega_menu;
            document.getElementById('item-mega-columns').value = item.mega_menu_columns || 3;
            document.getElementById('item-mega-image').value = item.mega_menu_image || '';
            document.getElementById('item-mega-content').value = item.mega_menu_content || '';
            
            const megaOptions = document.getElementById('mega-menu-options');
            if (item.is_mega_menu) {
                megaOptions.classList.remove('hidden');
            } else {
                megaOptions.classList.add('hidden');
            }
            
            document.getElementById('menu-item-modal').classList.remove('hidden');
        });
    });
    
    // Toggle mega menu options
    document.getElementById('item-is-mega-menu')?.addEventListener('change', (e) => {
        const megaOptions = document.getElementById('mega-menu-options');
        if (e.target.checked) {
            megaOptions.classList.remove('hidden');
        } else {
            megaOptions.classList.add('hidden');
        }
    });
    
    // Save menu item
    const menuItemForm = document.getElementById('menu-item-form');
    menuItemForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        const itemId = data.item_id;
        
        data.is_mega_menu = data.is_mega_menu === '1' ? 1 : 0;
        data.mega_menu_columns = parseInt(data.mega_menu_columns) || 3;
        
        try {
            const response = await fetch(`/api/admin/menus/items.php?id=${itemId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            if (result.status === 'success') {
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
    
    // Delete menu item
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this menu item?')) {
                return;
            }
            
            const itemId = btn.dataset.itemId;
            try {
                const response = await fetch(`/api/admin/menus/items.php?id=${itemId}`, {
                    method: 'DELETE',
                });
                
                const result = await response.json();
                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
    
    // Hide modals on outside click
    [createMenuModal, addItemsModal, document.getElementById('menu-item-modal')].forEach(modal => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
    
    document.getElementById('cancel-menu-item')?.addEventListener('click', () => {
        document.getElementById('menu-item-modal').classList.add('hidden');
        menuItemForm.reset();
    });
    
    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
