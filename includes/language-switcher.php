<?php
/**
 * Language Switcher Component
 * Include this in your header or footer
 */

require_once __DIR__ . '/translation.php';

$languages = getAvailableLanguages(true);
$currentLang = getCurrentLanguage();
?>

<div class="language-switcher relative">
    <button 
        type="button" 
        id="language-switcher-btn" 
        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
        aria-label="Change language"
    >
        <?php
        $currentLangData = null;
        foreach ($languages as $lang) {
            if ($lang['code'] === $currentLang) {
                $currentLangData = $lang;
                break;
            }
        }
        ?>
        <span class="text-lg"><?php echo e($currentLangData['flag'] ?? '🌐'); ?></span>
        <span class="text-sm font-medium"><?php echo e($currentLangData['native_name'] ?? $currentLang); ?></span>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div 
        id="language-switcher-dropdown" 
        class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
    >
        <?php foreach ($languages as $lang): ?>
            <button
                type="button"
                class="language-option w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors flex items-center gap-2 <?php echo $lang['code'] === $currentLang ? 'bg-blue-50 text-blue-700' : 'text-gray-700'; ?>"
                data-lang="<?php echo e($lang['code']); ?>"
            >
                <span class="text-lg"><?php echo e($lang['flag'] ?? '🌐'); ?></span>
                <span class="flex-1"><?php echo e($lang['native_name']); ?></span>
                <?php if ($lang['code'] === $currentLang): ?>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function() {
    const btn = document.getElementById('language-switcher-btn');
    const dropdown = document.getElementById('language-switcher-dropdown');
    const options = document.querySelectorAll('.language-option');

    if (!btn || !dropdown) return;

    // Toggle dropdown
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Handle language selection
    options.forEach(option => {
        option.addEventListener('click', async () => {
            const lang = option.dataset.lang;
            
            try {
                const response = await fetch('/api/translations/set-language.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ language: lang })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Reload page to apply translations
                    window.location.reload();
                } else {
                    console.error('Failed to change language:', result.message);
                    alert('Failed to change language. Please try again.');
                }
            } catch (error) {
                console.error('Error changing language:', error);
                alert('Error changing language. Please try again.');
            }
        });
    });
})();
</script>

