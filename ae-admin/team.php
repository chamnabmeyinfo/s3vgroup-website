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

<div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
        <div>
            <h1 style="font-size: 22px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0 0 4px 0;">Team Members</h1>
            <p style="margin: 0; color: var(--mac-text-secondary); font-size: 12px;">Manage your team members and their profiles</p>
        </div>
        <button type="button" id="new-member-btn" class="admin-btn admin-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            New Team Member
        </button>
    </div>

    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($team)): ?>
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: var(--mac-text-secondary); font-size: 13px;">
                            No team members found. <button type="button" id="new-member-empty-btn" class="admin-btn admin-btn-primary" style="font-size: 11px; padding: 4px 12px; margin-left: 8px;">Add your first team member</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($team as $member): ?>
                        <tr>
                            <td>
                                <?php if ($member['photo']): ?>
                                    <img src="<?php echo e($member['photo']); ?>" alt="<?php echo e($member['name']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #b0b0b0;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(to bottom, #f0f0f0 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center; color: var(--mac-text-secondary); font-weight: 600; font-size: 12px; border: 1px solid #b0b0b0;">
                                        <?php echo strtoupper(substr($member['name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 600;"><?php echo e($member['name']); ?></td>
                            <td style="color: var(--mac-text-secondary);"><?php echo e($member['title']); ?></td>
                            <td style="color: var(--mac-text-secondary);"><?php echo e($member['priority']); ?></td>
                            <td>
                                <span class="admin-badge <?php echo $member['status'] === 'ACTIVE' ? 'admin-badge-success' : ''; ?>">
                                    <?php echo e($member['status']); ?>
                                </span>
                            </td>
                            <td style="color: var(--mac-text-secondary); font-size: 12px;"><?php echo date('M d, Y', strtotime($member['updatedAt'])); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; align-items: center; gap: 6px; justify-content: flex-end;">
                                    <button
                                        type="button"
                                        class="team-edit-btn admin-btn admin-btn-secondary"
                                        style="font-size: 11px; padding: 4px 12px;"
                                        data-member="<?php echo htmlspecialchars(json_encode($member), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="team-delete-btn admin-btn admin-btn-danger"
                                        style="font-size: 11px; padding: 4px 12px;"
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
<div id="team-modal" class="admin-modal hidden">
    <div class="admin-modal-content" style="max-width: 700px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #b0b0b0;">
            <h3 style="font-size: 18px; font-weight: 600; color: var(--mac-text); letter-spacing: -0.3px; margin: 0;" id="modal-title">New Team Member</h3>
            <button type="button" onclick="hideTeamModal()" style="background: none; border: none; color: var(--mac-text-secondary); cursor: pointer; font-size: 24px; line-height: 1; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.color='var(--mac-text)'" onmouseout="this.style.color='var(--mac-text-secondary)'">&times;</button>
        </div>
        <form id="team-form">
            <div id="error-message" style="display: none; margin-bottom: 16px; padding: 10px; background: rgba(255, 59, 48, 0.1); border: 1px solid rgba(255, 59, 48, 0.3); border-radius: 4px; color: var(--mac-red); font-size: 12px;"></div>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Name *</label>
                    <input type="text" name="name" required class="admin-form-input">
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Title/Position *</label>
                    <input type="text" name="title" required placeholder="e.g., CEO, Operations Manager" class="admin-form-input">
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Department</label>
                    <input type="text" name="department" placeholder="e.g., Operations, Sales, Management" class="admin-form-input">
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Photo/Profile Picture</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="url" name="photo" placeholder="https://example.com/photo.jpg or upload" class="admin-form-input" style="flex: 1;" id="team-photo-input">
                        <input type="file" accept="image/*" class="hidden" id="team-photo-file" data-target="team-photo-input">
                        <button type="button" onclick="document.getElementById('team-photo-file').click()" class="admin-btn admin-btn-secondary" style="white-space: nowrap;">Upload</button>
                    </div>
                    <div id="team-photo-preview" style="margin-top: 8px;"></div>
                    <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;">Recommended: Square image, 400x400px or larger</p>
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Bio/Description</label>
                    <textarea name="bio" rows="3" placeholder="Brief bio or description about the team member..." class="admin-form-textarea"></textarea>
                </div>
                
                <div class="admin-form-group">
                    <label class="admin-form-label">Expertise/Skills</label>
                    <textarea name="expertise" rows="2" placeholder="Areas of expertise, skills, certifications..." class="admin-form-textarea"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Email</label>
                        <input type="email" name="email" placeholder="team@example.com" class="admin-form-input">
                    </div>
                    
                    <div class="admin-form-group">
                        <label class="admin-form-label">Phone</label>
                        <input type="tel" name="phone" placeholder="+855 12 345 678" class="admin-form-input">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Location</label>
                        <input type="text" name="location" placeholder="e.g., Phnom Penh, Cambodia" class="admin-form-input">
                    </div>
                    
                    <div class="admin-form-group">
                        <label class="admin-form-label">Languages</label>
                        <input type="text" name="languages" placeholder="e.g., English, Khmer, Chinese" class="admin-form-input">
                    </div>
                </div>
                
                <div style="border-top: 1px solid #b0b0b0; padding-top: 16px; margin-top: 16px;">
                    <h4 style="font-size: 13px; font-weight: 600; color: var(--mac-text); margin-bottom: 12px;">Social Media & Links</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">LinkedIn</label>
                            <input type="url" name="linkedin" placeholder="https://linkedin.com/in/username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Twitter/X</label>
                            <input type="url" name="twitter" placeholder="https://twitter.com/username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Facebook</label>
                            <input type="url" name="facebook" placeholder="https://facebook.com/username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Instagram</label>
                            <input type="url" name="instagram" placeholder="https://instagram.com/username" class="admin-form-input">
                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                            <input type="url" name="instagram" placeholder="https://instagram.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                                        </div>
                                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Website</label>
                            <input type="url" name="website" placeholder="https://example.com" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">GitHub</label>
                            <input type="url" name="github" placeholder="https://github.com/username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">YouTube</label>
                            <input type="url" name="youtube" placeholder="https://youtube.com/@username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Telegram</label>
                            <input type="text" name="telegram" placeholder="@username" class="admin-form-input">
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" placeholder="+855 12 345 678" class="admin-form-input">
                        </div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Priority</label>
                        <input type="number" name="priority" value="0" class="admin-form-input">
                        <p style="font-size: 11px; color: var(--mac-text-secondary); margin-top: 4px;">Higher appears first</p>
                    </div>
                    
                    <div class="admin-form-group">
                        <label class="admin-form-label">Status</label>
                        <select name="status" class="admin-form-select">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #b0b0b0;">
                <button type="button" onclick="hideTeamModal()" class="admin-btn admin-btn-secondary">Cancel</button>
                <button type="submit" id="submit-btn" class="admin-btn admin-btn-primary">Save</button>
            </div>
            <input type="hidden" name="id" id="team-id">
        </form>
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
                photoPreview.innerHTML = `<img src="${data.photo}" alt="Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 1px solid #b0b0b0; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">`;
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
        photoPreview.innerHTML = '<p style="font-size: 12px; color: var(--mac-text-secondary);">Uploading...</p>';

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
                photoPreview.innerHTML = `<img src="${result.data.url}" alt="Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 1px solid #b0b0b0; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">`;
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

