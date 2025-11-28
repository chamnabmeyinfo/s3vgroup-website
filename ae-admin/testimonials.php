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

use App\Database\Connection;
use App\Domain\Content\TestimonialRepository;

$db = getDB();
$repository = new TestimonialRepository($db);
$testimonials = $repository->all();

$pageTitle = 'Testimonials';
include __DIR__ . '/includes/header.php';
?>

<div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
        <div>
            <h1 style="font-size: 22px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0 0 4px 0;">Testimonials</h1>
            <p style="margin: 0; color: var(--mac-text-secondary); font-size: 12px;">Manage customer testimonials and reviews</p>
        </div>
        <button type="button" id="new-testimonial-btn" class="admin-btn admin-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            New Testimonial
        </button>
    </div>

    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Rating</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Featured</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($testimonials)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No testimonials found. <button type="button" id="new-testimonial-empty-btn" class="text-[#0b3a63] hover:underline">Create your first testimonial</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <tr>
                            <td class="px-6 py-4 font-semibold"><?php echo e($testimonial['name']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($testimonial['company'] ?? '—'); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-4 h-4 <?php echo $i <= ($testimonial['rating'] ?? 5) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                    <span class="ml-1 text-sm text-gray-600"><?php echo e($testimonial['rating']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                    echo $testimonial['status'] === 'PUBLISHED' ? 'bg-green-100 text-green-800' : 
                                        ($testimonial['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                    <?php echo e($testimonial['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($testimonial['featured']): ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">★ Featured</span>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($testimonial['updatedAt'])); ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-[#0b3a63] hover:underline testimonial-edit-btn"
                                        data-testimonial="<?php echo htmlspecialchars(json_encode($testimonial), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-red-600 hover:text-red-800 testimonial-delete-btn"
                                        data-id="<?php echo htmlspecialchars($testimonial['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-name="<?php echo htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="testimonial-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideModal()"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="testimonial-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">New Testimonial</h3>
                            <div id="error-message" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input type="text" name="company" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                    <input type="text" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                                    <textarea name="content" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating *</label>
                                    <select name="rating" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Avatar URL</label>
                                    <input type="url" name="avatar" placeholder="https://example.com/avatar.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        <option value="DRAFT">Draft</option>
                                        <option value="PUBLISHED">Published</option>
                                        <option value="ARCHIVED">Archived</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="featured" value="1" class="rounded border-gray-300 text-[#0b3a63] focus:ring-[#0b3a63]">
                                        <span class="ml-2 text-sm text-gray-700">Featured</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <input type="number" name="priority" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    <p class="text-xs text-gray-500 mt-1">Higher priority appears first</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submit-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0b3a63] text-base font-medium text-white hover:bg-[#1a5a8a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" onclick="hideModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
                <input type="hidden" name="id" id="testimonial-id">
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('testimonial-modal');
    const form = document.getElementById('testimonial-form');
    const submitBtn = document.getElementById('submit-btn');
    const errorEl = document.getElementById('error-message');
    const modalTitle = document.getElementById('modal-title');
    let mode = 'create';
    let currentId = null;

    function showModal(data = null) {
        mode = data ? 'update' : 'create';
        currentId = data?.id || null;
        modalTitle.textContent = mode === 'create' ? 'New Testimonial' : 'Edit Testimonial';
        errorEl.classList.add('hidden');
        form.reset();
        
        if (data) {
            document.getElementById('testimonial-id').value = data.id;
            form.name.value = data.name || '';
            form.company.value = data.company || '';
            form.position.value = data.position || '';
            form.content.value = data.content || '';
            form.rating.value = data.rating || 5;
            form.avatar.value = data.avatar || '';
            form.status.value = data.status || 'DRAFT';
            form.featured.checked = data.featured == 1;
            form.priority.value = data.priority || 0;
        }
        
        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        form.reset();
        errorEl.classList.add('hidden');
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        errorEl.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        const payload = {
            name: formData.get('name'),
            company: formData.get('company') || null,
            position: formData.get('position') || null,
            content: formData.get('content'),
            rating: parseInt(formData.get('rating')),
            avatar: formData.get('avatar') || null,
            status: formData.get('status'),
            featured: formData.get('featured') === '1',
            priority: parseInt(formData.get('priority')) || 0,
        };

        const url = mode === 'create' 
            ? '/api/admin/testimonials/index.php'
            : `/api/admin/testimonials/item.php?id=${encodeURIComponent(currentId)}`;
        const method = mode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save testimonial.');
            }

            window.location.reload();
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    }

    document.getElementById('new-testimonial-btn')?.addEventListener('click', () => showModal(null));
    document.getElementById('new-testimonial-empty-btn')?.addEventListener('click', () => showModal(null));
    
    document.querySelectorAll('.testimonial-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.testimonial);
            showModal(data);
        });
    });

    document.querySelectorAll('.testimonial-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const name = button.dataset.name;

            if (!confirm(`Are you sure you want to delete the testimonial from "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/testimonials/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete testimonial.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    form.addEventListener('submit', handleSubmit);
    window.hideModal = hideModal;
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

