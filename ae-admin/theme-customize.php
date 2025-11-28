<?php
session_start();
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

$db = getDB();
$repository = new ThemeRepository($db);

// Get theme ID or slug from query
$themeIdentifier = $_GET['theme'] ?? $_GET['id'] ?? null;

if (!$themeIdentifier) {
    header('Location: /ae-admin/backend-appearance.php');
    exit;
}

// Find theme
$theme = $repository->findById($themeIdentifier) ?? $repository->findBySlug($themeIdentifier);

if (!$theme) {
    die('Theme not found');
}

// Parse config
$config = is_string($theme['config']) ? json_decode($theme['config'], true) : $theme['config'];
$colors = $config['colors'] ?? [];
$typography = $config['typography'] ?? [];
$radius = $config['radius'] ?? [];
$shadows = $config['shadows'] ?? [];

$pageTitle = 'Customize Theme: ' . htmlspecialchars($theme['name']);
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-container">
    <div class="admin-page-header">
        <div class="flex items-center justify-between">
            <div>
                <h1>Customize Theme: <?php echo htmlspecialchars($theme['name']); ?></h1>
                <p>Adjust colors, typography, spacing, and more to personalize this theme. Changes are saved to the theme configuration.</p>
            </div>
            <div class="flex gap-3">
                <a href="/ae-admin/theme-preview.php?theme=<?php echo htmlspecialchars($theme['slug'] ?? $theme['id']); ?>" class="admin-btn admin-btn-secondary" target="_blank">
                    👁️ Preview
                </a>
                <a href="/ae-admin/backend-appearance.php" class="admin-btn admin-btn-secondary">
                    ← Back to Themes
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customization Panel -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Colors Section -->
            <div class="admin-card">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Colors</h2>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <?php
                    $colorFields = [
                        'background' => 'Background',
                        'surface' => 'Surface',
                        'primary' => 'Primary',
                        'primaryText' => 'Primary Text',
                        'text' => 'Text',
                        'mutedText' => 'Muted Text',
                        'border' => 'Border',
                        'error' => 'Error',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'accent' => 'Accent',
                        'secondary' => 'Secondary',
                        'tertiary' => 'Tertiary'
                    ];
                    foreach ($colorFields as $key => $label):
                        $value = $colors[$key] ?? '#FFFFFF';
                    ?>
                        <div class="color-picker-group">
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">
                                <?php echo htmlspecialchars($label); ?>
                            </label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    id="color-<?php echo htmlspecialchars($key); ?>" 
                                    value="<?php echo htmlspecialchars($value); ?>"
                                    class="color-input w-16 h-10 rounded border cursor-pointer"
                                    data-color-key="<?php echo htmlspecialchars($key); ?>"
                                >
                                <input 
                                    type="text" 
                                    id="color-text-<?php echo htmlspecialchars($key); ?>" 
                                    value="<?php echo htmlspecialchars($value); ?>"
                                    class="admin-form-input flex-1 color-text-input"
                                    data-color-key="<?php echo htmlspecialchars($key); ?>"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Typography Section -->
            <div class="admin-card">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Typography</h2>
                </div>
                <div class="space-y-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Font Family</label>
                        <input 
                            type="text" 
                            id="typography-fontFamily" 
                            value="<?php echo htmlspecialchars($typography['fontFamily'] ?? '-apple-system, sans-serif'); ?>"
                            class="admin-form-input w-full"
                            placeholder="-apple-system, BlinkMacSystemFont, sans-serif"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Body Size (px)</label>
                            <input 
                                type="number" 
                                id="typography-bodySize" 
                                value="<?php echo htmlspecialchars($typography['bodySize'] ?? 15); ?>"
                                class="admin-form-input w-full"
                                min="10"
                                max="24"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Line Height</label>
                            <input 
                                type="number" 
                                id="typography-lineHeight" 
                                value="<?php echo htmlspecialchars($typography['lineHeight'] ?? 1.5); ?>"
                                class="admin-form-input w-full"
                                min="1"
                                max="2"
                                step="0.1"
                            >
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Heading Scale</label>
                            <input 
                                type="number" 
                                id="typography-headingScale" 
                                value="<?php echo htmlspecialchars($typography['headingScale'] ?? 1.25); ?>"
                                class="admin-form-input w-full"
                                min="1"
                                max="2"
                                step="0.05"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Letter Spacing</label>
                            <input 
                                type="text" 
                                id="typography-letterSpacing" 
                                value="<?php echo htmlspecialchars($typography['letterSpacing'] ?? '-0.01em'); ?>"
                                class="admin-form-input w-full"
                                placeholder="-0.01em"
                            >
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Normal Weight</label>
                            <input 
                                type="number" 
                                id="typography-fontWeightNormal" 
                                value="<?php echo htmlspecialchars($typography['fontWeightNormal'] ?? 400); ?>"
                                class="admin-form-input w-full"
                                min="100"
                                max="900"
                                step="100"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Medium Weight</label>
                            <input 
                                type="number" 
                                id="typography-fontWeightMedium" 
                                value="<?php echo htmlspecialchars($typography['fontWeightMedium'] ?? 500); ?>"
                                class="admin-form-input w-full"
                                min="100"
                                max="900"
                                step="100"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Semibold Weight</label>
                            <input 
                                type="number" 
                                id="typography-fontWeightSemibold" 
                                value="<?php echo htmlspecialchars($typography['fontWeightSemibold'] ?? 600); ?>"
                                class="admin-form-input w-full"
                                min="100"
                                max="900"
                                step="100"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Bold Weight</label>
                            <input 
                                type="number" 
                                id="typography-fontWeightBold" 
                                value="<?php echo htmlspecialchars($typography['fontWeightBold'] ?? 700); ?>"
                                class="admin-form-input w-full"
                                min="100"
                                max="900"
                                step="100"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Radius Section -->
            <div class="admin-card">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Border Radius</h2>
                </div>
                <div class="grid grid-cols-4 gap-4 mt-4">
                    <?php
                    $radiusFields = [
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                        'pill' => 'Pill'
                    ];
                    foreach ($radiusFields as $key => $label):
                        $value = $radius[$key] ?? ($key === 'pill' ? 9999 : 8);
                    ?>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);"><?php echo htmlspecialchars($label); ?> (px)</label>
                            <input 
                                type="number" 
                                id="radius-<?php echo htmlspecialchars($key); ?>" 
                                value="<?php echo htmlspecialchars($value); ?>"
                                class="admin-form-input w-full radius-input"
                                data-radius-key="<?php echo htmlspecialchars($key); ?>"
                                min="0"
                                max="<?php echo $key === 'pill' ? 9999 : 50; ?>"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shadows Section -->
            <div class="admin-card">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Shadows</h2>
                </div>
                <div class="space-y-4 mt-4">
                    <?php
                    $shadowFields = [
                        'card' => 'Card',
                        'elevated' => 'Elevated',
                        'subtle' => 'Subtle',
                        'button' => 'Button',
                        'buttonHover' => 'Button Hover'
                    ];
                    foreach ($shadowFields as $key => $label):
                        $value = $shadows[$key] ?? '0 1px 3px rgba(0,0,0,0.1)';
                    ?>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);"><?php echo htmlspecialchars($label); ?></label>
                            <input 
                                type="text" 
                                id="shadow-<?php echo htmlspecialchars($key); ?>" 
                                value="<?php echo htmlspecialchars($value); ?>"
                                class="admin-form-input w-full shadow-input"
                                data-shadow-key="<?php echo htmlspecialchars($key); ?>"
                                placeholder="0 1px 3px rgba(0,0,0,0.1)"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4">
                    <button id="reset-btn" class="admin-btn admin-btn-secondary">
                        Reset to Default
                    </button>
                    <div class="flex gap-3">
                        <button id="preview-btn" class="admin-btn admin-btn-secondary">
                            Preview Changes
                        </button>
                        <button id="save-btn" class="admin-btn admin-btn-primary">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Panel -->
        <div class="lg:col-span-1">
            <div class="admin-card sticky top-6">
                <div class="admin-section-header">
                    <h2 class="admin-section-title">Live Preview</h2>
                </div>
                <div id="theme-preview" class="mt-4 space-y-4">
                    <!-- Preview will be updated via JavaScript -->
                    <div class="p-4 rounded-lg" style="background: var(--preview-bg, #FFFFFF); border: 1px solid var(--preview-border, #E5E7EB);">
                        <h3 style="color: var(--preview-text, #111111); font-size: 18px; font-weight: 600; margin-bottom: 12px;">Sample Card</h3>
                        <p style="color: var(--preview-text-muted, #666666); font-size: 14px; margin-bottom: 16px;">This is a preview of how your theme will look.</p>
                        <button class="admin-btn admin-btn-primary" style="margin-right: 8px;">Primary Button</button>
                        <button class="admin-btn admin-btn-secondary">Secondary Button</button>
                    </div>
                    <div class="p-3 rounded" style="background: var(--preview-surface, #F5F5F7);">
                        <div class="text-sm" style="color: var(--preview-text, #111111);">Surface Color</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="admin-badge admin-badge-success">Success</span>
                        <span class="admin-badge admin-badge-warning">Warning</span>
                        <span class="admin-badge admin-badge-danger">Danger</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeId = '<?php echo htmlspecialchars($theme['id']); ?>';
    const originalConfig = <?php echo json_encode($config); ?>;
    let currentConfig = JSON.parse(JSON.stringify(originalConfig));

    // Color picker sync
    document.querySelectorAll('.color-input, .color-text-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.colorKey;
            const value = this.type === 'color' ? this.value : this.value.toUpperCase();
            
            // Sync both inputs
            document.getElementById(`color-${key}`).value = value;
            document.getElementById(`color-text-${key}`).value = value;
            
            // Update config
            if (!currentConfig.colors) currentConfig.colors = {};
            currentConfig.colors[key] = value;
            
            // Update preview
            updatePreview();
        });
    });

    // Typography inputs
    document.querySelectorAll('[id^="typography-"]').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.id.replace('typography-', '');
            if (!currentConfig.typography) currentConfig.typography = {};
            currentConfig.typography[key] = this.type === 'number' ? parseFloat(this.value) : this.value;
            updatePreview();
        });
    });

    // Radius inputs
    document.querySelectorAll('.radius-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.radiusKey;
            if (!currentConfig.radius) currentConfig.radius = {};
            currentConfig.radius[key] = parseInt(this.value);
            updatePreview();
        });
    });

    // Shadow inputs
    document.querySelectorAll('.shadow-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.shadowKey;
            if (!currentConfig.shadows) currentConfig.shadows = {};
            currentConfig.shadows[key] = this.value;
            updatePreview();
        });
    });

    // Update preview
    function updatePreview() {
        const colors = currentConfig.colors || {};
        const root = document.documentElement;
        
        // Update CSS variables for preview
        root.style.setProperty('--preview-bg', colors.background || '#FFFFFF');
        root.style.setProperty('--preview-surface', colors.surface || '#F5F5F7');
        root.style.setProperty('--preview-text', colors.text || '#111111');
        root.style.setProperty('--preview-text-muted', colors.mutedText || '#666666');
        root.style.setProperty('--preview-border', colors.border || '#E5E7EB');
        root.style.setProperty('--preview-primary', colors.primary || '#007AFF');
    }

    // Save button
    document.getElementById('save-btn').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            // Ensure all required config sections exist
            if (!currentConfig.colors) currentConfig.colors = {};
            if (!currentConfig.typography) currentConfig.typography = {};
            if (!currentConfig.radius) currentConfig.radius = {};
            if (!currentConfig.shadows) currentConfig.shadows = {};

            // Ensure required color fields exist
            if (!currentConfig.colors.background) currentConfig.colors.background = '#FFFFFF';
            if (!currentConfig.colors.surface) currentConfig.colors.surface = '#F5F5F7';
            if (!currentConfig.colors.primary) currentConfig.colors.primary = '#007AFF';
            if (!currentConfig.colors.text) currentConfig.colors.text = '#111111';

            const response = await fetch(`/api/admin/themes/item.php?id=${encodeURIComponent(themeId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    config: currentConfig
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();

            if (data.status === 'success') {
                alert('Theme customized successfully! The page will reload to apply changes.');
                setTimeout(() => {
                    window.location.href = '/ae-admin/backend-appearance.php';
                }, 500);
            } else {
                const errorMsg = data.error?.message || data.message || 'Unknown error';
                alert('Failed to save: ' + errorMsg);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            console.error('Error saving theme:', error);
            alert('Error saving theme: ' + error.message + '\n\nPlease check the browser console for details.');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Reset button
    document.getElementById('reset-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all changes? This cannot be undone.')) {
            currentConfig = JSON.parse(JSON.stringify(originalConfig));
            location.reload();
        }
    });

    // Preview button
    document.getElementById('preview-btn').addEventListener('click', function() {
        window.open(`/ae-admin/theme-preview.php?theme=<?php echo htmlspecialchars($theme['slug']); ?>`, '_blank');
    });

    // Initial preview update
    updatePreview();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

