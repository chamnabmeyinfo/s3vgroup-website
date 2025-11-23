<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Content\SliderRepository;

$db = getDB();
$repository = new SliderRepository($db);
$sliders = $repository->all();

$pageTitle = 'Hero Slider';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Content</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Hero Slider</h1>
            <p class="text-sm text-gray-600">Manage homepage hero slider slides</p>
        </div>
        <button type="button" id="new-slider-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New Slide
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Preview</th>
                    <th class="px-6 py-3 font-medium">Title</th>
                    <th class="px-6 py-3 font-medium">Priority</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($sliders)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No slides found. <button type="button" id="new-slider-empty-btn" class="text-[#0b3a63] hover:underline">Create your first slide</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sliders as $slider): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <?php if ($slider['image_url']): ?>
                                    <img src="<?php echo e($slider['image_url']); ?>" alt="<?php echo e($slider['title']); ?>" class="h-16 w-24 rounded object-cover">
                                <?php else: ?>
                                    <div class="h-16 w-24 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-semibold"><?php echo e($slider['title']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($slider['priority']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                    echo $slider['status'] === 'PUBLISHED' ? 'bg-green-100 text-green-800' : 
                                        ($slider['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                    <?php echo e($slider['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($slider['updatedAt'])); ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-[#0b3a63] hover:underline slider-edit-btn"
                                        data-slider="<?php echo htmlspecialchars(json_encode($slider), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-red-600 hover:text-red-800 slider-delete-btn"
                                        data-id="<?php echo htmlspecialchars($slider['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-title="<?php echo htmlspecialchars($slider['title'], ENT_QUOTES, 'UTF-8'); ?>"
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
<div id="slider-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideSliderModal()"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="slider-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">New Slide</h3>
                            <div id="error-message" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                    <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" name="subtitle" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL *</label>
                                    <div class="flex items-center gap-3">
                                        <input type="url" name="image_url" required placeholder="https://example.com/image.jpg or upload" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]" id="slider-image-input">
                                        <input type="file" accept="image/*" class="hidden" id="slider-image-file" data-target="slider-image-input">
                                        <button type="button" onclick="document.getElementById('slider-image-file').click()" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                                            Upload
                                        </button>
                                    </div>
                                    <div id="slider-image-preview" class="mt-2"></div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                                        <input type="url" name="link_url" placeholder="https://example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                        <input type="text" name="link_text" placeholder="Learn More" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Color</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="button_color" value="#0b3a63" class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                        <input type="text" name="button_color_text" value="#0b3a63" pattern="^#[0-9A-Fa-f]{6}$" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                            <option value="DRAFT">Draft</option>
                                            <option value="PUBLISHED">Published</option>
                                            <option value="ARCHIVED">Archived</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                        <input type="number" name="priority" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        <p class="text-xs text-gray-500 mt-1">Higher appears first</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submit-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0b3a63] text-base font-medium text-white hover:bg-[#1a5a8a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" onclick="hideSliderModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
                <input type="hidden" name="id" id="slider-id">
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('slider-modal');
    const form = document.getElementById('slider-form');
    const submitBtn = document.getElementById('submit-btn');
    const errorEl = document.getElementById('error-message');
    const modalTitle = document.getElementById('modal-title');
    const imagePreview = document.getElementById('slider-image-preview');
    let mode = 'create';
    let currentId = null;

    function showModal(data = null) {
        mode = data ? 'update' : 'create';
        currentId = data?.id || null;
        modalTitle.textContent = mode === 'create' ? 'New Slide' : 'Edit Slide';
        errorEl.classList.add('hidden');
        form.reset();
        imagePreview.innerHTML = '';
        
        if (data) {
            document.getElementById('slider-id').value = data.id;
            form.title.value = data.title || '';
            form.subtitle.value = data.subtitle || '';
            form.description.value = data.description || '';
            form.image_url.value = data.image_url || '';
            form.link_url.value = data.link_url || '';
            form.link_text.value = data.link_text || '';
            form.button_color.value = data.button_color || '#0b3a63';
            form.button_color_text.value = data.button_color || '#0b3a63';
            form.status.value = data.status || 'DRAFT';
            form.priority.value = data.priority || 0;
            
            if (data.image_url) {
                imagePreview.innerHTML = `<img src="${data.image_url}" alt="Preview" class="h-32 w-auto rounded border border-gray-300 object-contain">`;
            }
        }
        
        modal.classList.remove('hidden');
    }

    function hideSliderModal() {
        modal.classList.add('hidden');
        form.reset();
        errorEl.classList.add('hidden');
        imagePreview.innerHTML = '';
    }

    // Image upload
    document.getElementById('slider-image-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const input = document.getElementById('slider-image-input');
        const preview = document.getElementById('slider-image-preview');
        
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
                document.getElementById('slider-image-input').value = result.data.url;
                imagePreview.innerHTML = `<img src="${result.data.url}" alt="Preview" class="h-32 w-auto rounded border border-gray-300 object-contain">`;
            } else {
                alert('Upload failed: ' + result.message);
            }
        } catch (error) {
            alert('Upload error: ' + error.message);
        }
    });

    // Color picker sync
    form.button_color?.addEventListener('input', (e) => {
        form.button_color_text.value = e.target.value;
    });
    form.button_color_text?.addEventListener('input', (e) => {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            form.button_color.value = e.target.value;
        }
    });

    async function handleSubmit(e) {
        e.preventDefault();
        
        errorEl.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        const payload = {
            title: formData.get('title'),
            subtitle: formData.get('subtitle') || null,
            description: formData.get('description') || null,
            image_url: formData.get('image_url'),
            link_url: formData.get('link_url') || null,
            link_text: formData.get('link_text') || null,
            button_color: formData.get('button_color_text') || formData.get('button_color'),
            status: formData.get('status'),
            priority: parseInt(formData.get('priority')) || 0,
        };

        const url = mode === 'create' 
            ? '/api/admin/sliders/index.php'
            : `/api/admin/sliders/item.php?id=${encodeURIComponent(currentId)}`;
        const method = mode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save slide.');
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

    document.getElementById('new-slider-btn')?.addEventListener('click', () => showModal(null));
    document.getElementById('new-slider-empty-btn')?.addEventListener('click', () => showModal(null));
    
    document.querySelectorAll('.slider-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.slider);
            showModal(data);
        });
    });

    document.querySelectorAll('.slider-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const title = button.dataset.title;

            if (!confirm(`Delete slide "${title}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/sliders/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete slide.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    form.addEventListener('submit', handleSubmit);
    window.hideSliderModal = hideSliderModal;
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

