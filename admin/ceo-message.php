<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Content\CeoMessageRepository;

$db = getDB();
$repository = new CeoMessageRepository($db);
$messages = $repository->all();
$currentMessage = $repository->find();

$pageTitle = 'CEO Message';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Content</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">CEO Message</h1>
            <p class="text-sm text-gray-600">Manage the CEO's message to visitors and stakeholders</p>
        </div>
        <?php if (!$currentMessage): ?>
            <button type="button" id="new-message-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
                + New CEO Message
            </button>
        <?php endif; ?>
    </div>

    <?php if ($currentMessage): ?>
        <form id="ceo-message-form" class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="<?php echo e($currentMessage['title'] ?? 'Message from CEO'); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                        <option value="DRAFT" <?php echo ($currentMessage['status'] ?? 'DRAFT') === 'DRAFT' ? 'selected' : ''; ?>>Draft</option>
                        <option value="PUBLISHED" <?php echo ($currentMessage['status'] ?? '') === 'PUBLISHED' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEO Name *</label>
                    <input type="text" name="name" value="<?php echo e($currentMessage['name'] ?? ''); ?>" required placeholder="e.g., Sok Chen" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position/Title</label>
                    <input type="text" name="position" value="<?php echo e($currentMessage['position'] ?? 'Chief Executive Officer'); ?>" placeholder="e.g., Chief Executive Officer" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CEO Photo</label>
                <div class="flex items-center gap-3">
                    <input type="url" name="photo" value="<?php echo e($currentMessage['photo'] ?? ''); ?>" placeholder="https://example.com/photo.jpg or upload" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]" id="ceo-photo-input">
                    <input type="file" accept="image/*" class="hidden" id="ceo-photo-file" data-target="ceo-photo-input">
                    <button type="button" onclick="document.getElementById('ceo-photo-file').click()" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Upload</button>
                </div>
                <div id="ceo-photo-preview" class="mt-2">
                    <?php if (!empty($currentMessage['photo'])): ?>
                        <img src="<?php echo e($currentMessage['photo']); ?>" alt="CEO Preview" class="h-32 w-32 rounded-full object-cover border-2 border-gray-300">
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 mt-1">Recommended: Square image, 400x400px or larger</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                <textarea name="message" rows="10" required placeholder="Write the CEO's message here..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($currentMessage['message'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Signature Image URL (Optional)</label>
                <input type="url" name="signature" value="<?php echo e($currentMessage['signature'] ?? ''); ?>" placeholder="https://example.com/signature.png" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                <div id="signature-preview" class="mt-2">
                    <?php if (!empty($currentMessage['signature'])): ?>
                        <img src="<?php echo e($currentMessage['signature']); ?>" alt="Signature Preview" class="h-16 w-auto object-contain">
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                <input type="number" name="displayOrder" value="<?php echo e($currentMessage['displayOrder'] ?? 0); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                <p class="text-xs text-gray-500 mt-1">Higher numbers appear first</p>
            </div>

            <input type="hidden" name="id" value="<?php echo e($currentMessage['id']); ?>">
            <div id="error-message" class="hidden p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="window.location.reload()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" id="save-btn" class="px-4 py-2 bg-[#0b3a63] text-white rounded-md text-sm font-medium hover:bg-[#1a5a8a]">Save CEO Message</button>
                <button type="button" id="delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Delete</button>
            </div>
        </form>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-gray-600 mb-4">No CEO message has been created yet.</p>
            <button type="button" id="new-message-empty-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
                + Create CEO Message
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for new message -->
<div id="ceo-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideCeoModal()"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="ceo-modal-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">New CEO Message</h3>
                    <div id="modal-error-message" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input type="text" name="title" value="Message from CEO" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CEO Name *</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <input type="text" name="position" value="Chief Executive Officer" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo URL</label>
                            <input type="url" name="photo" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                            <textarea name="message" rows="8" required class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="DRAFT">Draft</option>
                                <option value="PUBLISHED">Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0b3a63] text-base font-medium text-white hover:bg-[#1a5a8a] sm:ml-3 sm:w-auto sm:text-sm">
                        Create
                    </button>
                    <button type="button" onclick="hideCeoModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('ceo-message-form');
    const modalForm = document.getElementById('ceo-modal-form');
    const modal = document.getElementById('ceo-modal');
    const saveBtn = document.getElementById('save-btn');
    const errorEl = document.getElementById('error-message');
    const modalErrorEl = document.getElementById('modal-error-message');

    function showModal() {
        modal.classList.remove('hidden');
    }

    function hideCeoModal() {
        modal.classList.add('hidden');
        modalForm.reset();
        modalErrorEl.classList.add('hidden');
    }

    // Photo upload
    document.getElementById('ceo-photo-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        try {
            const formData = new FormData();
            formData.append('image', file);

            const response = await fetch('/api/admin/upload.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (result.status === 'success') {
                document.getElementById('ceo-photo-input').value = result.url;
                document.getElementById('ceo-photo-preview').innerHTML = `<img src="${result.url}" alt="CEO Preview" class="h-32 w-32 rounded-full object-cover border-2 border-gray-300">`;
            } else {
                alert('Upload failed: ' + result.message);
            }
        } catch (error) {
            alert('Upload error: ' + error.message);
        }
    });

    // Create new message
    document.getElementById('new-message-btn')?.addEventListener('click', showModal);
    document.getElementById('new-message-empty-btn')?.addEventListener('click', showModal);

    modalForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        modalErrorEl.classList.add('hidden');
        const submitBtn = modalForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        const formData = new FormData(modalForm);
        const payload = {
            title: formData.get('title'),
            name: formData.get('name'),
            position: formData.get('position'),
            photo: formData.get('photo') || null,
            message: formData.get('message'),
            status: formData.get('status'),
        };

        try {
            const response = await fetch('/api/admin/ceo-message/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to create CEO message.');
            }

            window.location.reload();
        } catch (error) {
            modalErrorEl.textContent = error.message;
            modalErrorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create';
        }
    });

    // Update existing message
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        errorEl.classList.add('hidden');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        const id = formData.get('id');

        const payload = {
            title: formData.get('title'),
            name: formData.get('name'),
            position: formData.get('position'),
            photo: formData.get('photo') || null,
            message: formData.get('message'),
            signature: formData.get('signature') || null,
            displayOrder: parseInt(formData.get('displayOrder')) || 0,
            status: formData.get('status'),
        };

        try {
            const response = await fetch(`/api/admin/ceo-message/item.php?id=${encodeURIComponent(id)}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save CEO message.');
            }

            alert('CEO message saved successfully!');
            window.location.reload();
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save CEO Message';
        }
    });

    // Delete message
    document.getElementById('delete-btn')?.addEventListener('click', async () => {
        if (!confirm('Delete this CEO message? This action cannot be undone.')) {
            return;
        }

        const formData = new FormData(form);
        const id = formData.get('id');

        try {
            const response = await fetch(`/api/admin/ceo-message/item.php?id=${encodeURIComponent(id)}`, {
                method: 'DELETE',
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to delete CEO message.');
            }

            window.location.reload();
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });

    window.hideCeoModal = hideCeoModal;
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

