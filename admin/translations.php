<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;

$db = getDB();
$repository = new TranslationRepository($db);
$service = new TranslationService($repository);

$languages = $service->getLanguages();
$defaultLang = $service->getDefaultLanguage();
$currentLang = $_GET['lang'] ?? ($defaultLang['code'] ?? 'en');
$currentNamespace = $_GET['namespace'] ?? 'general';

$translations = $service->getTranslations($currentLang, $currentNamespace);
$allTranslations = $repository->getAllTranslations($currentNamespace);

// Get namespaces
$namespaces = [];
foreach ($allTranslations as $trans) {
    $ns = $trans['namespace'] ?? 'general';
    if (!in_array($ns, $namespaces)) {
        $namespaces[] = $ns;
    }
}
sort($namespaces);

$pageTitle = 'Translations';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Localization</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Translations</h1>
            <p class="text-sm text-gray-600">Manage translations for multiple languages</p>
        </div>
    </div>

    <!-- Language and Namespace Selector -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 md:p-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                <select id="language-selector" class="admin-form-select w-full">
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?php echo e($lang['code']); ?>" <?php echo $lang['code'] === $currentLang ? 'selected' : ''; ?>>
                            <?php echo e($lang['flag'] ?? ''); ?> <?php echo e($lang['native_name']); ?> (<?php echo e($lang['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Namespace</label>
                <select id="namespace-selector" class="admin-form-select w-full">
                    <option value="general" <?php echo $currentNamespace === 'general' ? 'selected' : ''; ?>>General</option>
                    <?php foreach ($namespaces as $ns): ?>
                        <?php if ($ns !== 'general'): ?>
                            <option value="<?php echo e($ns); ?>" <?php echo $ns === $currentNamespace ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($ns)); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Translation Actions -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 md:p-6">
        <div class="flex flex-wrap gap-3">
            <button type="button" id="add-translation-btn" class="admin-btn admin-btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Translation</span>
            </button>
            <button type="button" id="auto-translate-btn" class="admin-btn admin-btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                <span>Auto Translate Missing</span>
            </button>
            <button type="button" id="export-btn" class="admin-btn admin-btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export</span>
            </button>
        </div>
    </div>

    <!-- Translations Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Translation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="translations-tbody" class="bg-white divide-y divide-gray-200">
                    <!-- Translations will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Translation Modal -->
<div id="translation-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold text-gray-900" id="modal-title">Add Translation</h2>
                <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="translation-form" class="space-y-4">
                <input type="hidden" id="translation-id" name="id">
                
                <div>
                    <label class="admin-form-label">Key</label>
                    <input type="text" id="translation-key" name="key" class="admin-form-input" required>
                </div>

                <div>
                    <label class="admin-form-label">Language</label>
                    <select id="translation-language" name="language_code" class="admin-form-select" required>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?php echo e($lang['code']); ?>">
                                <?php echo e($lang['native_name']); ?> (<?php echo e($lang['code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="admin-form-label">Namespace</label>
                    <input type="text" id="translation-namespace" name="namespace" class="admin-form-input" value="<?php echo e($currentNamespace); ?>" required>
                </div>

                <div>
                    <label class="admin-form-label">Translation</label>
                    <textarea id="translation-value" name="value" class="admin-form-input" rows="4" required></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="translation-auto" name="is_auto_translated" class="rounded">
                        <span class="text-sm text-gray-700">Auto-translated</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="translation-review" name="needs_review" class="rounded">
                        <span class="text-sm text-gray-700">Needs Review</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="cancel-translation" class="admin-btn admin-btn-secondary">Cancel</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const currentLang = '<?php echo e($currentLang); ?>';
const currentNamespace = '<?php echo e($currentNamespace); ?>';

// Load translations
async function loadTranslations() {
    try {
        const response = await fetch(`/api/admin/translations/index.php?lang=${currentLang}&namespace=${currentNamespace}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderTranslations(data.data.translations);
        }
    } catch (error) {
        console.error('Error loading translations:', error);
    }
}

function renderTranslations(translations) {
    const tbody = document.getElementById('translations-tbody');
    tbody.innerHTML = '';

    if (translations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No translations found. Click "Add Translation" to create one.</td></tr>';
        return;
    }

    translations.forEach(trans => {
        const row = document.createElement('tr');
        const statusBadges = [];
        
        if (trans.is_auto_translated) {
            statusBadges.push('<span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Auto</span>');
        }
        if (trans.needs_review) {
            statusBadges.push('<span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Review</span>');
        }

        row.innerHTML = `
            <td class="px-6 py-4 font-mono text-sm">${escapeHtml(trans.key_name)}</td>
            <td class="px-6 py-4">${escapeHtml(trans.value || '—')}</td>
            <td class="px-6 py-4">${statusBadges.join(' ') || '<span class="text-gray-400">—</span>'}</td>
            <td class="px-6 py-4 text-right">
                <button class="text-blue-600 hover:text-blue-800 edit-translation" data-key="${escapeHtml(trans.key_name)}" data-lang="${escapeHtml(trans.language_code)}" data-namespace="${escapeHtml(trans.namespace)}">
                    Edit
                </button>
                <button class="text-red-600 hover:text-red-800 ml-3 delete-translation" data-key="${escapeHtml(trans.key_name)}" data-lang="${escapeHtml(trans.language_code)}" data-namespace="${escapeHtml(trans.namespace)}">
                    Delete
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event listeners
document.getElementById('language-selector').addEventListener('change', (e) => {
    window.location.href = `?lang=${e.target.value}&namespace=${currentNamespace}`;
});

document.getElementById('namespace-selector').addEventListener('change', (e) => {
    window.location.href = `?lang=${currentLang}&namespace=${e.target.value}`;
});

document.getElementById('add-translation-btn').addEventListener('click', () => {
    document.getElementById('translation-modal').classList.remove('hidden');
    document.getElementById('translation-form').reset();
    document.getElementById('translation-language').value = currentLang;
    document.getElementById('translation-namespace').value = currentNamespace;
    document.getElementById('modal-title').textContent = 'Add Translation';
});

document.getElementById('close-modal').addEventListener('click', () => {
    document.getElementById('translation-modal').classList.add('hidden');
});

document.getElementById('cancel-translation').addEventListener('click', () => {
    document.getElementById('translation-modal').classList.add('hidden');
});

document.getElementById('translation-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/admin/translations/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            document.getElementById('translation-modal').classList.add('hidden');
            loadTranslations();
            showToast('Translation saved successfully', 'success');
        } else {
            showToast(result.message || 'Error saving translation', 'error');
        }
    } catch (error) {
        showToast('Error saving translation', 'error');
    }
});

document.getElementById('auto-translate-btn').addEventListener('click', async () => {
    if (!confirm('This will automatically translate all missing translations. Continue?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/admin/translations/auto-translate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                source_lang: '<?php echo e($defaultLang['code'] ?? 'en'); ?>',
                target_lang: currentLang,
                namespace: currentNamespace
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast(`Translated ${result.data.count || 0} items`, 'success');
            loadTranslations();
        } else {
            showToast(result.message || 'Error during auto-translation', 'error');
        }
    } catch (error) {
        showToast('Error during auto-translation', 'error');
    }
});

// Edit and Delete handlers
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('edit-translation')) {
        const key = e.target.dataset.key;
        const lang = e.target.dataset.lang;
        const namespace = e.target.dataset.namespace;
        
        try {
            const response = await fetch(`/api/admin/translations/index.php?lang=${lang}&namespace=${namespace}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                const trans = data.data.translations.find(t => t.key_name === key);
                if (trans) {
                    document.getElementById('translation-key').value = trans.key_name;
                    document.getElementById('translation-language').value = trans.language_code;
                    document.getElementById('translation-namespace').value = trans.namespace;
                    document.getElementById('translation-value').value = trans.value || '';
                    document.getElementById('translation-auto').checked = trans.is_auto_translated || false;
                    document.getElementById('translation-review').checked = trans.needs_review || false;
                    document.getElementById('modal-title').textContent = 'Edit Translation';
                    document.getElementById('translation-modal').classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Error loading translation:', error);
        }
    }
    
    if (e.target.classList.contains('delete-translation')) {
        if (!confirm('Are you sure you want to delete this translation?')) {
            return;
        }
        
        const key = e.target.dataset.key;
        const lang = e.target.dataset.lang;
        const namespace = e.target.dataset.namespace;
        
        try {
            const response = await fetch(`/api/admin/translations/index.php?key=${encodeURIComponent(key)}&lang=${lang}&namespace=${namespace}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                loadTranslations();
                showToast('Translation deleted successfully', 'success');
            } else {
                showToast(result.message || 'Error deleting translation', 'error');
            }
        } catch (error) {
            showToast('Error deleting translation', 'error');
        }
    }
});

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white`;
    toast.textContent = message;
    
    const container = document.querySelector('.toast-container') || document.body;
    if (!document.querySelector('.toast-container')) {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    document.querySelector('.toast-container').appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Load translations on page load
loadTranslations();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

