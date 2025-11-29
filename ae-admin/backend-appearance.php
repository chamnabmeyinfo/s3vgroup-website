<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
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

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;

$db = getDB();
$repository = new ThemeRepository($db);
$service = new ThemeService($repository);

// Get all themes (both active and inactive)
$allThemes = $repository->all();

// Filter out themes with empty names (they won't display properly)
$allThemes = array_filter($allThemes, function($theme) {
    return !empty($theme['name']) && !empty($theme['slug']);
});

// Re-index array after filtering
$allThemes = array_values($allThemes);

$activeThemes = array_filter($allThemes, fn($t) => $t['is_active']);
$inactiveThemes = array_filter($allThemes, fn($t) => !$t['is_active']);

// Get current backend theme
$userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? 'admin_default';
$currentTheme = null;

try {
    $preferenceRepo = new \App\Domain\Theme\UserThemePreferenceRepository($db);
    $preference = $preferenceRepo->getThemeForUser($userId, 'backend_admin');
    if ($preference) {
        $currentTheme = $preference['id'];
    }
} catch (\Exception $e) {
    // Fallback to default
    $defaultTheme = $repository->getDefault();
    $currentTheme = $defaultTheme ? $defaultTheme['id'] : null;
}

$pageTitle = 'Backend Appearance';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-container">
    <!-- Page Header -->
    <div class="admin-page-header">
        <div>
            <h1>Backend Appearance</h1>
            <p>Customize the admin panel theme, colors, and styling. Switch themes, customize colors, and preview changes.</p>
        </div>
    </div>

    <!-- Active Themes Section -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Available Themes</h2>
            <p class="text-sm text-gray-500">Click on a theme card to activate it, or use the action buttons to preview, customize, or manage themes.</p>
        </div>

        <div class="admin-content-grid" id="theme-selector">
            <?php 
            // Debug: Count themes
            // echo "<!-- Total themes: " . count($allThemes) . " -->";
            foreach ($allThemes as $theme): 
                $isActive = $currentTheme === $theme['id'];
                $themeConfig = is_string($theme['config']) ? json_decode($theme['config'], true) : $theme['config'];
                $colors = $themeConfig['colors'] ?? [];
                $macos = $themeConfig['macos'] ?? null;
                $ios = $themeConfig['ios'] ?? null;
                // Use iOS metadata if available, otherwise fall back to macOS
                $platformInfo = $ios ?? $macos;
            ?>
                <div class="theme-card admin-card border-2 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:scale-[1.02] <?php echo $isActive ? 'border-blue-500 bg-blue-50 shadow-md ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300'; ?>" 
                     data-theme-id="<?php echo htmlspecialchars($theme['id']); ?>"
                     data-theme-slug="<?php echo htmlspecialchars($theme['slug']); ?>"
                     data-theme-active="<?php echo $theme['is_active'] ? '1' : '0'; ?>">
                    
                    <!-- Theme Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($theme['name']); ?></h3>
                                <?php if ($isActive): ?>
                                    <span class="px-2 py-0.5 bg-blue-500 text-white text-xs font-medium rounded-full flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Active
                                    </span>
                                <?php endif; ?>
                                <?php if ($theme['is_default']): ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">Default</span>
                                <?php endif; ?>
                                <?php if (!$theme['is_active']): ?>
                                    <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-xs font-medium rounded-full">Disabled</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($theme['description']): ?>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($theme['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($platformInfo): ?>
                                <div class="mt-2">
                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded"><?php echo htmlspecialchars($platformInfo['version'] ?? $platformInfo['style'] ?? ''); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Color Preview -->
                    <div class="mb-4 p-3 rounded-lg border border-gray-200 bg-white">
                        <div class="flex gap-2 mb-2">
                            <div class="flex-1 h-10 rounded-md shadow-sm" style="background-color: <?php echo htmlspecialchars($colors['background'] ?? '#FFFFFF'); ?>; border: 1px solid <?php echo htmlspecialchars($colors['border'] ?? '#E5E7EB'); ?>;"></div>
                            <div class="flex-1 h-10 rounded-md shadow-sm" style="background-color: <?php echo htmlspecialchars($colors['surface'] ?? '#F5F5F7'); ?>;"></div>
                            <div class="flex-1 h-10 rounded-md shadow-sm" style="background-color: <?php echo htmlspecialchars($colors['primary'] ?? '#007AFF'); ?>;"></div>
                            <div class="flex-1 h-10 rounded-md shadow-sm" style="background-color: <?php echo htmlspecialchars($colors['accent'] ?? '#7C3AED'); ?>;"></div>
                        </div>
                        <div class="text-xs text-gray-500 font-mono"><?php echo htmlspecialchars($theme['slug']); ?></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <!-- Activate/Use Theme Button -->
                        <?php if (!$isActive): ?>
                            <button onclick="activateTheme('<?php echo htmlspecialchars($theme['id']); ?>', '<?php echo htmlspecialchars($theme['slug']); ?>')" 
                                    class="admin-btn admin-btn-primary text-xs px-3 py-2 flex-1 min-w-[100px]">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Use Theme
                            </button>
                        <?php else: ?>
                            <button onclick="deactivateTheme('<?php echo htmlspecialchars($theme['id']); ?>')" 
                                    class="admin-btn bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs px-3 py-2 flex-1 min-w-[100px]">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Switch Off
                            </button>
                        <?php endif; ?>
                        
                        <!-- Preview Button -->
                        <a href="/ae-admin/theme-preview.php?theme=<?php echo htmlspecialchars($theme['slug']); ?>" 
                           target="_blank"
                           class="admin-btn admin-btn-secondary text-xs px-3 py-2 flex-1 min-w-[100px]">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Preview
                        </a>
                        
                        <!-- Customize Button -->
                        <a href="/ae-admin/theme-customize.php?theme=<?php echo htmlspecialchars($theme['slug']); ?>" 
                           class="admin-btn admin-btn-secondary text-xs px-3 py-2 flex-1 min-w-[100px]">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Customize
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Info Box -->
    <div class="admin-card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">Theme Actions</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-blue-700">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong>Use Theme:</strong> Sets this theme as your active backend theme
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong>Switch Off:</strong> Removes theme preference, uses default theme
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.523 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong>Preview:</strong> View detailed preview with all UI elements
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        <div>
                            <strong>Customize:</strong> Modify colors, typography, spacing, and shadows
                        </div>
                    </div>
                </div>
                <p class="text-sm text-blue-600 mt-3 font-medium">💡 Changes apply immediately. Refresh the page to see the full effect.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Activate theme
async function activateTheme(themeId, themeSlug) {
    if (!confirm('Activate this theme? The page will reload to apply changes.')) {
        return;
    }
    
    // Disable button to prevent double-clicks
    const buttons = document.querySelectorAll(`button[onclick*="${themeId}"]`);
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.textContent = 'Activating...';
    });
    
    try {
        const response = await fetch('/api/admin/theme/backend.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ theme_id: themeId })
        });
        
        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error Response:', errorText);
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch (e) {
                errorData = { error: { message: `HTTP ${response.status}: ${errorText}` } };
            }
            throw new Error(errorData.error?.message || `HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Show success message
            showToast('Theme activated successfully! Reloading...', 'success');
            // Reload page with cache-busting parameter to ensure fresh theme load
            setTimeout(() => {
                window.location.href = window.location.pathname + '?theme_refresh=' + Date.now();
            }, 800);
        } else {
            const errorMsg = data.error?.message || data.message || 'Unknown error';
            console.error('Theme activation failed:', data);
            showToast('Failed to activate theme: ' + errorMsg, 'error');
            // Re-enable buttons
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Use Theme';
            });
        }
    } catch (error) {
        console.error('Error activating theme:', error);
        showToast('Error activating theme: ' + error.message + '. Check console for details.', 'error');
        // Re-enable buttons
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Use Theme';
        });
    }
}

// Deactivate theme (switch to default)
async function deactivateTheme(themeId) {
    if (!confirm('Switch to default theme? Your current theme preference will be removed.')) {
        return;
    }
    
    try {
        // Get all themes to find default
        const themesResponse = await fetch('/api/admin/themes');
        const themesData = await themesResponse.json();
        
        if (themesData.status === 'success' && themesData.data && themesData.data.themes && themesData.data.themes.length > 0) {
            const defaultTheme = themesData.data.themes.find(t => t.is_default) || themesData.data.themes[0];
            
            if (defaultTheme && defaultTheme.id !== themeId) {
                // Switch to default theme
                await activateTheme(defaultTheme.id, defaultTheme.slug);
            } else {
                // Just remove preference by setting to default
                const response = await fetch('/api/admin/theme/backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ theme_id: defaultTheme.id })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast('Switched to default theme.', 'success');
                    setTimeout(() => {
                        window.location.href = window.location.pathname + '?theme_refresh=' + Date.now();
                    }, 800);
                } else {
                    showToast('Failed to switch theme: ' + (data.error?.message || 'Unknown error'), 'error');
                }
            }
        } else {
            showToast('Could not find default theme.', 'error');
        }
    } catch (error) {
        console.error('Error deactivating theme:', error);
        showToast('Error switching theme. Please try again.', 'error');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    toast.innerHTML = `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            ${type === 'success' ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' : 
              type === 'error' ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>' :
              '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>'}
        </svg>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Legacy click handler for theme cards (for backward compatibility)
document.addEventListener('DOMContentLoaded', function() {
    const themeCards = document.querySelectorAll('.theme-card');
    
    themeCards.forEach(card => {
        // Only handle click if not clicking on buttons
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons or links
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const themeId = this.dataset.themeId;
            const themeSlug = this.dataset.themeSlug;
            const isActive = this.dataset.themeActive === '1';
            
            if (!isActive) {
                activateTheme(themeId, themeSlug);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
