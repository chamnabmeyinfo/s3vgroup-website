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
use App\Domain\Content\TeamMemberRepository;

$db = getDB();
$repository = new TeamMemberRepository($db);
$team = $repository->all();

$pageTitle = 'Team Members';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Content</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Team Members</h1>
            <p class="text-sm text-gray-600">Manage your team members and their profiles</p>
        </div>
        <button type="button" id="new-member-btn" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New Team Member
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Photo</th>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Title</th>
                    <th class="px-6 py-3 font-medium">Priority</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($team)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No team members found. <button type="button" id="new-member-empty-btn" class="text-[#0b3a63] hover:underline">Add your first team member</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($team as $member): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <?php if ($member['photo']): ?>
                                    <img src="<?php echo e($member['photo']); ?>" alt="<?php echo e($member['name']); ?>" class="h-12 w-12 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 font-semibold text-sm">
                                        <?php echo strtoupper(substr($member['name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-semibold"><?php echo e($member['name']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($member['title']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($member['priority']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                    echo $member['status'] === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; 
                                ?>">
                                    <?php echo e($member['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($member['updatedAt'])); ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-[#0b3a63] hover:underline team-edit-btn"
                                        data-member="<?php echo htmlspecialchars(json_encode($member), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-red-600 hover:text-red-800 team-delete-btn"
                                        data-id="<?php echo htmlspecialchars($member['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-name="<?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?>"
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
<div id="team-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideTeamModal()"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="team-form">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">New Team Member</h3>
                            <div id="error-message" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position *</label>
                                    <input type="text" name="title" required placeholder="e.g., CEO, Operations Manager" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                    <input type="text" name="department" placeholder="e.g., Operations, Sales, Management" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo/Profile Picture</label>
                                    <div class="flex items-center gap-3">
                                        <input type="url" name="photo" placeholder="https://example.com/photo.jpg or upload" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]" id="team-photo-input">
                                        <input type="file" accept="image/*" class="hidden" id="team-photo-file" data-target="team-photo-input">
                                        <button type="button" onclick="document.getElementById('team-photo-file').click()" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                                            Upload
                                        </button>
                                    </div>
                                    <div id="team-photo-preview" class="mt-2"></div>
                                    <p class="text-xs text-gray-500 mt-1">Recommended: Square image, 400x400px or larger</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio/Description</label>
                                    <textarea name="bio" rows="3" placeholder="Brief bio or description about the team member..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expertise/Skills</label>
                                    <textarea name="expertise" rows="2" placeholder="Areas of expertise, skills, certifications..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" placeholder="team@example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                        <input type="tel" name="phone" placeholder="+855 12 345 678" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                        <input type="text" name="location" placeholder="e.g., Phnom Penh, Cambodia" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Languages</label>
                                        <input type="text" name="languages" placeholder="e.g., English, Khmer, Chinese" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                    </div>
                                </div>
                                
                                <div class="border-t pt-4 mt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Social Media & Links</h4>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                                            <input type="url" name="linkedin" placeholder="https://linkedin.com/in/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter/X</label>
                                            <input type="url" name="twitter" placeholder="https://twitter.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                                            <input type="url" name="facebook" placeholder="https://facebook.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                            <input type="url" name="instagram" placeholder="https://instagram.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                            <input type="url" name="website" placeholder="https://example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">GitHub</label>
                                            <input type="url" name="github" placeholder="https://github.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                                            <input type="url" name="youtube" placeholder="https://youtube.com/@username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Telegram</label>
                                            <input type="text" name="telegram" placeholder="@username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                                            <input type="text" name="whatsapp" placeholder="+855 12 345 678" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                        <input type="number" name="priority" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        <p class="text-xs text-gray-500 mt-1">Higher appears first</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                            <option value="ACTIVE">Active</option>
                                            <option value="INACTIVE">Inactive</option>
                                        </select>
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
                    <button type="button" onclick="hideTeamModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
                <input type="hidden" name="id" id="team-id">
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('team-modal');
    const form = document.getElementById('team-form');
    const submitBtn = document.getElementById('submit-btn');
    const errorEl = document.getElementById('error-message');
    const modalTitle = document.getElementById('modal-title');
    const photoPreview = document.getElementById('team-photo-preview');
    let mode = 'create';
    let currentId = null;

    function showModal(data = null) {
        mode = data ? 'update' : 'create';
        currentId = data?.id || null;
        modalTitle.textContent = mode === 'create' ? 'New Team Member' : 'Edit Team Member';
        errorEl.classList.add('hidden');
        form.reset();
        photoPreview.innerHTML = '';
        
        if (data) {
            document.getElementById('team-id').value = data.id;
            form.name.value = data.name || '';
            form.title.value = data.title || '';
            form.bio.value = data.bio || '';
            form.photo.value = data.photo || '';
            form.email.value = data.email || '';
            form.phone.value = data.phone || '';
            form.linkedin.value = data.linkedin || '';
            form.priority.value = data.priority || 0;
            form.status.value = data.status || 'ACTIVE';
            
            if (data.photo) {
                photoPreview.innerHTML = `<img src="${data.photo}" alt="Preview" class="h-24 w-24 rounded-full object-cover border-2 border-gray-300">`;
            }
        }
        
        modal.classList.remove('hidden');
    }

    function hideTeamModal() {
        modal.classList.add('hidden');
        form.reset();
        errorEl.classList.add('hidden');
        photoPreview.innerHTML = '';
    }

    // Photo upload
    document.getElementById('team-photo-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const input = document.getElementById('team-photo-input');
        
        // Show loading
        photoPreview.innerHTML = '<p class="text-sm text-gray-500">Uploading...</p>';

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
                photoPreview.innerHTML = `<img src="${result.data.url}" alt="Preview" class="h-24 w-24 rounded-full object-cover border-2 border-gray-300">`;
            } else {
                photoPreview.innerHTML = '';
                alert('Upload failed: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            photoPreview.innerHTML = '';
            alert('Upload error: ' + error.message);
        }
    });

    async function handleSubmit(e) {
        e.preventDefault();
        
        errorEl.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        const payload = {
            name: formData.get('name'),
            title: formData.get('title'),
            department: formData.get('department') || null,
            bio: formData.get('bio') || null,
            expertise: formData.get('expertise') || null,
            photo: formData.get('photo') || null,
            email: formData.get('email') || null,
            phone: formData.get('phone') || null,
            location: formData.get('location') || null,
            languages: formData.get('languages') || null,
            linkedin: formData.get('linkedin') || null,
            twitter: formData.get('twitter') || null,
            facebook: formData.get('facebook') || null,
            instagram: formData.get('instagram') || null,
            website: formData.get('website') || null,
            github: formData.get('github') || null,
            youtube: formData.get('youtube') || null,
            telegram: formData.get('telegram') || null,
            whatsapp: formData.get('whatsapp') || null,
            priority: parseInt(formData.get('priority')) || 0,
            status: formData.get('status'),
        };

        const url = mode === 'create' 
            ? '/api/admin/team/index.php'
            : `/api/admin/team/item.php?id=${encodeURIComponent(currentId)}`;
        const method = mode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save team member.');
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

    document.getElementById('new-member-btn')?.addEventListener('click', () => showModal(null));
    document.getElementById('new-member-empty-btn')?.addEventListener('click', () => showModal(null));
    
    document.querySelectorAll('.team-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.member);
            showModal(data);
        });
    });

    document.querySelectorAll('.team-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const name = button.dataset.name;

            if (!confirm(`Delete team member "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/team/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete team member.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    form.addEventListener('submit', handleSubmit);
    window.hideTeamModal = hideTeamModal;
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

