<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Content\CompanyStoryRepository;

$db = getDB();
$repository = new CompanyStoryRepository($db);
$story = $repository->find();

$pageTitle = 'Company Story';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Content</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Company Story</h1>
            <p class="text-sm text-gray-600">Tell your company's story - history, mission, vision, values, and achievements</p>
        </div>
    </div>

    <form id="company-story-form" class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="<?php echo e($story['title'] ?? 'Our Company Story'); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                    <option value="DRAFT" <?php echo ($story['status'] ?? 'DRAFT') === 'DRAFT' ? 'selected' : ''; ?>>Draft</option>
                    <option value="PUBLISHED" <?php echo ($story['status'] ?? '') === 'PUBLISHED' ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
            <input type="text" name="subtitle" value="<?php echo e($story['subtitle'] ?? ''); ?>" placeholder="A brief subtitle or tagline" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hero Image URL</label>
            <div class="flex items-center gap-3">
                <input type="url" name="heroImage" value="<?php echo e($story['heroImage'] ?? ''); ?>" placeholder="https://example.com/image.jpg or upload" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]" id="hero-image-input">
                <input type="file" accept="image/*" class="hidden" id="hero-image-file" data-target="hero-image-input">
                <button type="button" onclick="document.getElementById('hero-image-file').click()" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Upload</button>
            </div>
            <div id="hero-image-preview" class="mt-2">
                <?php if (!empty($story['heroImage'])): ?>
                    <img src="<?php echo e($story['heroImage']); ?>" alt="Preview" class="h-32 w-auto rounded border border-gray-300 object-contain">
                <?php endif; ?>
            </div>
            <div id="hero-image-preview" class="mt-2">
                <?php if (!empty($story['heroImage'])): ?>
                    <img src="<?php echo e($story['heroImage']); ?>" alt="Hero Preview" class="h-32 w-auto rounded-md object-cover border-2 border-gray-300">
                <?php endif; ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Introduction</label>
            <textarea name="introduction" rows="4" placeholder="Brief introduction to your company..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($story['introduction'] ?? ''); ?></textarea>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold mb-4">Company History</h3>
            <textarea name="history" rows="6" placeholder="Tell your company's history, how it started, major milestones..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($story['history'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold mb-2">Mission</h3>
                <textarea name="mission" rows="5" placeholder="Your company's mission statement..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($story['mission'] ?? ''); ?></textarea>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">Vision</h3>
                <textarea name="vision" rows="5" placeholder="Your company's vision for the future..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($story['vision'] ?? ''); ?></textarea>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-2">Core Values</h3>
            <textarea name="values" rows="4" placeholder='Enter values as JSON array, e.g., ["Integrity", "Innovation", "Customer Focus", "Excellence"]' class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63] font-mono text-sm"><?php 
                $values = $story['values'] ?? null;
                if ($values) {
                    $decoded = json_decode($values, true);
                    echo e(is_array($decoded) ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $values);
                }
            ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Enter as JSON array of strings, or one value per line</p>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-2">Milestones</h3>
            <textarea name="milestones" rows="6" placeholder='Enter milestones as JSON array, e.g., [{"year": "2020", "event": "Company Founded"}, {"year": "2023", "event": "Reached 1000 Customers"}]' class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63] font-mono text-sm"><?php 
                $milestones = $story['milestones'] ?? null;
                if ($milestones) {
                    $decoded = json_decode($milestones, true);
                    echo e(is_array($decoded) ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $milestones);
                }
            ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Enter as JSON array of objects with "year" and "event" fields</p>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-2">Achievements</h3>
            <textarea name="achievements" rows="5" placeholder="List your company's key achievements, awards, certifications..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"><?php echo e($story['achievements'] ?? ''); ?></textarea>
        </div>

        <div id="error-message" class="hidden p-3 bg-red-100 border border-red-400 text-red-700 rounded"></div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button" onclick="window.location.reload()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" id="save-btn" class="px-4 py-2 bg-[#0b3a63] text-white rounded-md text-sm font-medium hover:bg-[#1a5a8a]">Save Company Story</button>
        </div>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('company-story-form');
    const saveBtn = document.getElementById('save-btn');
    const errorEl = document.getElementById('error-message');

    // Hero image upload
    document.getElementById('hero-image-file')?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch('/api/admin/upload.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (result.status === 'success') {
                document.getElementById('hero-image-input').value = result.url;
                document.getElementById('hero-image-preview').innerHTML = `<img src="${result.url}" alt="Hero Preview" class="h-32 w-auto rounded-md object-cover border-2 border-gray-300">`;
            } else {
                alert('Upload failed: ' + result.message);
            }
        } catch (error) {
            alert('Upload error: ' + error.message);
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        errorEl.classList.add('hidden');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        
        // Process values field
        let values = formData.get('values');
        if (values && values.trim()) {
            try {
                values = JSON.parse(values);
            } catch (e) {
                // If not JSON, treat as line-separated list
                values = values.split('\n').filter(v => v.trim()).map(v => v.trim());
            }
        } else {
            values = [];
        }

        // Process milestones field
        let milestones = formData.get('milestones');
        if (milestones && milestones.trim()) {
            try {
                milestones = JSON.parse(milestones);
            } catch (e) {
                // Invalid JSON, set to empty
                milestones = [];
            }
        } else {
            milestones = [];
        }

        const payload = {
            title: formData.get('title'),
            subtitle: formData.get('subtitle') || null,
            heroImage: formData.get('heroImage') || null,
            introduction: formData.get('introduction') || null,
            history: formData.get('history') || null,
            mission: formData.get('mission') || null,
            vision: formData.get('vision') || null,
            values: values,
            milestones: milestones,
            achievements: formData.get('achievements') || null,
            status: formData.get('status'),
        };

        try {
            const response = await fetch('/api/admin/company-story/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save company story.');
            }

            alert('Company story saved successfully!');
            window.location.reload();
        } catch (error) {
            errorEl.textContent = error.message;
            errorEl.classList.remove('hidden');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Company Story';
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

