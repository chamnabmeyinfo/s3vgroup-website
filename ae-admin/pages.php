<?php
declare(strict_types=1);

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

use App\Domain\Content\PageRepository;
use App\Database\Connection;

requireAdmin();

$db = Connection::getInstance();
$repository = new PageRepository($db);
$pages = $repository->all();

$pageTitle = 'Pages';
include __DIR__ . '/includes/header.php';
?>

<div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
        <div>
            <h1 style="font-size: 22px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0 0 4px 0;">Pages</h1>
            <p style="margin: 0; color: var(--mac-text-secondary); font-size: 12px;">Manage all your website pages and design them with Visual Builder</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/admin/homepage-builder-v2.php" class="admin-btn admin-btn-secondary">
                <span>🎨</span>
                Design Homepage
            </a>
            <button type="button" id="new-page-btn" class="admin-btn admin-btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Page
            </button>
        </div>
    </div>

    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--mac-text-secondary); font-size: 13px;">No pages found. Click "New Page" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pages as $page): ?>
                        <tr data-id="<?php echo e($page['id']); ?>">
                            <td style="font-weight: 600;"><?php echo e($page['title']); ?></td>
                            <td style="color: var(--mac-text-secondary);">/<?php echo e($page['slug']); ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="admin-badge admin-badge-info" style="display: inline-block;">
                                        <?php echo e(ucfirst($page['page_type'])); ?>
                                    </span>
                                    <?php 
                                    $settings = is_array($page['settings']) ? $page['settings'] : json_decode($page['settings'] ?? '{}', true);
                                    if (!empty($settings['is_homepage']) && $settings['is_homepage']): 
                                    ?>
                                        <span class="admin-badge admin-badge-warning" style="display: inline-block;">
                                            🏠 Homepage
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($page['template'])): ?>
                                        <span class="admin-badge admin-badge-info" style="display: inline-block;">
                                            📋 <?php echo e(ucfirst(str_replace('-', ' ', $page['template']))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="admin-badge <?php
                                    echo $page['status'] === 'PUBLISHED' ? 'admin-badge-success' : 
                                        ($page['status'] === 'DRAFT' ? 'admin-badge-warning' : '');
                                ?>">
                                    <?php echo e($page['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; align-items: center; gap: 6px; justify-content: flex-end;">
                                    <a href="/admin/homepage-builder-v2.php?page_id=<?php echo urlencode($page['id']); ?>" 
                                       class="admin-btn admin-btn-secondary" 
                                       style="font-size: 11px; padding: 4px 12px;"
                                       title="Open Visual Builder">
                                        🎨 Builder
                                    </a>
                                    <a href="/page.php?slug=<?php echo urlencode($page['slug']); ?>" 
                                       target="_blank"
                                       class="admin-btn admin-btn-primary" 
                                       style="font-size: 11px; padding: 4px 12px;"
                                       title="View page on frontend">
                                        View
                                    </a>
                                    <button type="button" class="edit-page-btn admin-btn admin-btn-secondary" style="font-size: 11px; padding: 4px 12px; cursor: pointer;">Edit</button>
                                    <button type="button" class="delete-page-btn admin-btn admin-btn-danger" style="font-size: 11px; padding: 4px 12px; cursor: pointer;">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Page Modal -->
<div id="page-modal" class="admin-modal hidden">
    <div class="admin-modal-content" style="max-width: 700px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
            <h3 style="font-size: 18px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0;" id="modal-title">New Page</h3>
            <button type="button" id="page-modal-close" style="background: none; border: none; color: var(--mac-text-secondary); cursor: pointer; font-size: 24px; line-height: 1; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.color='var(--mac-text)'" onmouseout="this.style.color='var(--mac-text-secondary)'">&times;</button>
        </div>
        <form id="page-form">
            <input type="hidden" id="page-id" name="id">
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Page Title *</label>
                    <input type="text" name="title" required class="admin-form-input">
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">URL Slug *</label>
                    <input type="text" name="slug" required placeholder="about-us" class="admin-form-input">
                    <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;">URL-friendly version of the title (e.g., "about-us")</p>
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Description</label>
                    <textarea name="description" rows="3" class="admin-form-textarea"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Page Type</label>
                        <select name="page_type" id="page-type-select" class="admin-form-select">
                            <option value="page">📄 Page (Standard content page)</option>
                            <option value="post">📝 Post (Blog post/article)</option>
                            <option value="custom">⚙️ Custom (Special purpose page)</option>
                            <option value="template">📋 Template (Reusable page template)</option>
                        </select>
                        <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;" id="page-type-desc">Standard content page for general use</p>
                    </div>
                    
                    <div class="admin-form-group">
                        <label class="admin-form-label">Status</label>
                        <select name="status" class="admin-form-select">
                            <option value="DRAFT">📝 Draft (Hidden from public)</option>
                            <option value="PUBLISHED">✅ Published (Visible to public)</option>
                            <option value="ARCHIVED">📦 Archived (Hidden, kept for reference)</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Page Template</label>
                        <select name="template" class="admin-form-select">
                            <option value="">Default Template</option>
                            <option value="full-width">Full Width (No sidebar, full width content)</option>
                            <option value="sidebar-left">Sidebar Left (Content with left sidebar)</option>
                            <option value="sidebar-right">Sidebar Right (Content with right sidebar)</option>
                            <option value="centered">Centered (Narrow centered content)</option>
                            <option value="landing">Landing Page (Full-screen sections)</option>
                            <option value="blog">Blog Layout (Blog post style)</option>
                            <option value="contact">Contact Page (Contact form layout)</option>
                            <option value="about">About Page (About us layout)</option>
                        </select>
                        <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;">Choose a layout template for this page</p>
                    </div>
                    
                    <div class="admin-form-group">
                        <label class="admin-form-label">Priority</label>
                        <input type="number" name="priority" value="0" min="-10" max="10" class="admin-form-input">
                        <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;">Higher priority = appears first in listings (-10 to 10)</p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #b0b0b0; padding-top: 16px; margin-top: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <input type="checkbox" name="is_homepage" id="is-homepage" value="1">
                        <label for="is-homepage" class="admin-form-label" style="margin: 0; cursor: pointer;">
                            🏠 Set as Homepage
                        </label>
                    </div>
                    <p style="font-size: 11px; color: var(--mac-text-secondary); margin-left: 22px;">This page will be shown when visitors visit the root URL (/)</p>
                </div>
                
                <div style="border-top: 1px solid #b0b0b0; padding-top: 16px; margin-top: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Meta Title (SEO)</label>
                        <input type="text" name="meta_title" class="admin-form-input" placeholder="Leave empty to use page title">
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Meta Description (SEO)</label>
                    <textarea name="meta_description" rows="2" class="admin-form-textarea" placeholder="Brief description for search engines"></textarea>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #b0b0b0;">
                <button type="button" id="cancel-page-btn" class="admin-btn admin-btn-secondary">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Save Page</button>
            </div>
        </form>
    </div>
</div>

<script>
// GLOBAL VARIABLES - accessible everywhere
let currentMode = 'create';
let currentId = null;
let currentPageData = null;
let modal, form, newPageBtn, cancelBtn;

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM already loaded
    init();
}

function init() {
    console.log('🚀 Page management script starting...');
    
    modal = document.getElementById('page-modal');
    form = document.getElementById('page-form');
    newPageBtn = document.getElementById('new-page-btn');
    cancelBtn = document.getElementById('cancel-page-btn');
    
    console.log('📋 Elements found:', {
        modal: !!modal,
        form: !!form,
        newPageBtn: !!newPageBtn,
        cancelBtn: !!cancelBtn
    });
    
    if (!modal || !form || !newPageBtn) {
        console.error('❌ Required elements not found! Retrying...');
        setTimeout(init, 200);
        return;
    }
    
    // Define functions inside init so they have access to modal, form, etc.

    // Define showModal function
    window.showModal = function(pageData = null) {
        console.log('🖱️ showModal called with:', pageData ? pageData.title : 'null');
        currentMode = pageData ? 'edit' : 'create';
        currentId = pageData?.id || null;
        currentPageData = pageData;
        
        const modalTitle = document.getElementById('modal-title');
        if (modalTitle) {
            modalTitle.textContent = currentMode === 'create' ? 'New Page' : 'Edit Page';
        }
        
        // Reset form first
        if (form) form.reset();
        
        // Populate form with existing data if editing
        if (pageData && form) {
            setTimeout(() => {
                if (form.id) form.id.value = pageData.id || '';
                if (form.title) form.title.value = pageData.title || '';
                if (form.slug) form.slug.value = pageData.slug || '';
                if (form.description) form.description.value = pageData.description || '';
                if (form.page_type) form.page_type.value = pageData.page_type || 'page';
                if (form.status) form.status.value = pageData.status || 'DRAFT';
                if (form.template) form.template.value = pageData.template || '';
                if (form.priority) form.priority.value = pageData.priority || 0;
                if (form.meta_title) form.meta_title.value = pageData.meta_title || '';
                if (form.meta_description) form.meta_description.value = pageData.meta_description || '';
                
                // Check if this page is set as homepage
                let settings = pageData.settings || {};
                if (typeof settings === 'string') {
                    try {
                        settings = JSON.parse(settings);
                    } catch (e) {
                        settings = {};
                    }
                }
                const isHomepage = settings.is_homepage === true || settings.is_homepage === '1' || settings.is_homepage === 1;
                if (form.is_homepage) {
                    form.is_homepage.checked = isHomepage;
                }
                
                updatePageTypeDescription(pageData.page_type || 'page');
            }, 10);
        } else if (form) {
            currentPageData = null;
            if (form.status) form.status.value = 'DRAFT';
            if (form.page_type) form.page_type.value = 'page';
            if (form.priority) form.priority.value = 0;
            if (form.is_homepage) form.is_homepage.checked = false;
            updatePageTypeDescription('page');
        }
        
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('✅ Modal shown, display:', window.getComputedStyle(modal).display, 'classes:', modal.className);
        } else {
            console.error('❌ Modal element not found!');
            alert('ERROR: Modal element not found! Please refresh the page.');
        }
    };

    // Define hideModal function
    window.hideModal = function() {
        console.log('🖱️ hideModal called');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex'); // Remove flex class
        }
        if (form) {
            form.reset();
        }
    };

    // Update page type description
    function updatePageTypeDescription(pageType) {
        const descriptions = {
            'page': 'Standard content page for general use (About, Contact, Services, etc.)',
            'post': 'Blog post or article with date, author, and content',
            'custom': 'Special purpose page with custom functionality',
            'template': 'Reusable page template that can be applied to other pages'
        };
        const descEl = document.getElementById('page-type-desc');
        if (descEl) {
            descEl.textContent = descriptions[pageType] || descriptions['page'];
        }
    }
    
    // Listen to page type changes
    document.getElementById('page-type-select')?.addEventListener('change', (e) => {
        updatePageTypeDescription(e.target.value);
    });
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        // Disable submit button to prevent double submission
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) {
            console.error('❌ Submit button not found in form!');
            alert('Error: Submit button not found. Please refresh the page.');
            return;
        }
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        const formData = new FormData(form);
        const isHomepage = formData.get('is_homepage') === '1';
        
        // Get existing settings to preserve other settings
        // Use stored page data instead of fetching again
        let existingSettings = {};
        if (currentMode === 'edit' && currentPageData) {
            // Use the data we already have from when we opened the modal
            let settings = currentPageData.settings || {};
            if (typeof settings === 'string') {
                try {
                    settings = JSON.parse(settings);
                } catch (e) {
                    console.warn('Failed to parse stored settings:', e);
                    settings = {};
                }
            }
            existingSettings = settings;
        }
        
        const payload = {
            title: formData.get('title'),
            slug: formData.get('slug'),
            description: formData.get('description') || null,
            page_type: formData.get('page_type'),
            status: formData.get('status'),
            template: formData.get('template') || null,
            priority: parseInt(formData.get('priority')) || 0,
            meta_title: formData.get('meta_title') || null,
            meta_description: formData.get('meta_description') || null,
            settings: {
                ...existingSettings,
                is_homepage: isHomepage
            }
        };
        
        console.log('Submitting page:', {
            mode: currentMode,
            id: currentId,
            payload: payload
        });

        const url = currentMode === 'create' 
            ? '/api/admin/pages/index.php'
            : `/api/admin/pages/item.php?id=${encodeURIComponent(currentId)}`;
        const method = currentMode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();
            
                if (result.status === 'success') {
                    const message = `Page ${currentMode === 'create' ? 'created' : 'updated'} successfully!`;
                    // API returns {status: 'success', data: {page: {...}}}
                    const page = result.data?.page || result.page;
                    
                    if (currentMode === 'create' && page) {
                        // Ask if user wants to design the page immediately
                        if (confirm(message + '\n\nWould you like to design this page with Visual Builder now?')) {
                            window.location.href = `/admin/homepage-builder-v2.php?page_id=${encodeURIComponent(page.id)}`;
                            return;
                        }
                    } else {
                        // For edits, just reload without alert (less intrusive)
                        console.log('Page updated successfully');
                    }
                    
                    hideModal();
                    // Small delay before reload to ensure modal closes smoothly
                    setTimeout(() => {
                        location.reload();
                    }, 100);
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
        } catch (error) {
            alert('Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    // New Page button - SIMPLE like products.php
    newPageBtn.addEventListener('click', () => {
        console.log('🖱️ NEW PAGE BUTTON CLICKED!');
        if (typeof window.showModal === 'function') {
            window.showModal();
        } else {
            alert('ERROR: showModal function not found! Check console.');
            console.error('showModal function:', typeof window.showModal);
        }
    });
    console.log('✅ New Page button handler attached');
    
    // Cancel button
    if (cancelBtn) {
        cancelBtn.onclick = hideModal;
        cancelBtn.addEventListener('click', hideModal);
        console.log('✅ Cancel button handlers attached');
    }

    // Edit buttons - SIMPLE like products.php
    document.querySelectorAll('.edit-page-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            console.log('🖱️ EDIT BUTTON CLICKED!', button);
            
            const row = button.closest('tr');
            if (!row) {
                alert('Could not find table row. Please refresh the page.');
                return;
            }
            
            const id = row.dataset.id;
            console.log('📋 Page ID:', id);
            
            if (!id) {
                alert('Page ID not found. Please refresh the page.');
                return;
            }
            
            // Show loading state
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Loading...';
            
            try {
                console.log('📡 Fetching page data for ID:', id);
                const url = `/api/admin/pages/item.php?id=${encodeURIComponent(id)}`;
                console.log('📡 Fetch URL:', url);
                
                const response = await fetch(url);
                console.log('📡 Response status:', response.status, response.statusText);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ HTTP Error:', response.status, errorText);
                    alert(`Failed to load page (HTTP ${response.status}): ${errorText || response.statusText}`);
                    return;
                }
                
                const result = await response.json();
                console.log('📦 Response JSON:', result);
                console.log('📦 Result status:', result.status);
                console.log('📦 Result data:', result.data);
                console.log('📦 Result page:', result.data?.page);
                
                if (result.status === 'success' && result.data && result.data.page) {
                    console.log('✅ Page loaded, showing modal');
                    if (typeof window.showModal === 'function') {
                        window.showModal(result.data.page);
                    } else {
                        alert('ERROR: showModal function not found! Check console.');
                        console.error('showModal function:', typeof window.showModal);
                    }
                } else {
                    const errorMsg = result.message || result.error || 'Unknown error';
                    console.error('❌ Failed response:', result);
                    alert('Failed to load page: ' + errorMsg);
                }
            } catch (error) {
                console.error('❌ Error loading page:', error);
                console.error('❌ Error stack:', error.stack);
                alert('Error loading page: ' + error.message);
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
        console.log('✅ Edit button handler attached');
    });

    // Delete buttons - SIMPLE like products.php
    document.querySelectorAll('.delete-page-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            console.log('🖱️ DELETE BUTTON CLICKED!', button);
            
            const row = button.closest('tr');
            if (!row) {
                alert('Could not find table row. Please refresh the page.');
                return;
            }
            
            const id = row.dataset.id;
            console.log('📋 Page ID:', id);
            
            if (!id) {
                alert('Page ID not found. Please refresh the page.');
                return;
            }
            
            // Get page title for confirmation
            const pageTitle = row.querySelector('td:first-child')?.textContent?.trim() || 'this page';
            
            if (!confirm(`Are you sure you want to delete "${pageTitle}"?\n\nThis will also delete all sections on this page. This action cannot be undone.`)) {
                return;
            }
            
            // Show loading state
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Deleting...';
            
            try {
                console.log('📡 Deleting page with ID:', id);
                const response = await fetch(`/api/admin/pages/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE'
                });
                const result = await response.json();
                
                console.log('📦 Delete response:', result);
                
                if (result.status === 'success') {
                    console.log('✅ Page deleted successfully, reloading...');
                    location.reload();
                } else {
                    alert('Failed to delete: ' + (result.message || 'Unknown error'));
                    button.disabled = false;
                    button.textContent = originalText;
                }
            } catch (error) {
                console.error('❌ Error deleting page:', error);
                alert('Error deleting page: ' + error.message);
                button.disabled = false;
                button.textContent = originalText;
            }
        });
        console.log('✅ Delete button handler attached');
    });
    
    console.log('✅✅✅ ALL BUTTON HANDLERS ATTACHED SUCCESSFULLY! ✅✅✅');
}
</script>
</script>

<style>
/* Ensure buttons are clickable - CRITICAL */
#new-page-btn,
.edit-page-btn,
.delete-page-btn {
    position: relative !important;
    z-index: 999 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none !important;
}

/* Prevent any overlay from blocking buttons */
#new-page-btn:hover,
.edit-page-btn:hover,
.delete-page-btn:hover {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Ensure table rows and cells don't block clicks */
table tbody tr {
    position: relative;
}

table tbody tr td:last-child {
    position: relative;
    z-index: 100 !important;
}

table tbody tr td:last-child div {
    position: relative;
    z-index: 101 !important;
}

/* Remove any overlay that might block clicks */
body::before,
body::after {
    display: none !important;
}
</style>

<script>
// REMOVED: Verification script was overwriting handlers!
// The handlers are already attached in the main script above
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

