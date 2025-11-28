<?php
session_start();
// Load bootstrap
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

// Get theme identifier from query parameter (slug or ID)
$themeIdentifier = $_GET['theme'] ?? $_GET['id'] ?? null;

if (!$themeIdentifier) {
    die('Theme identifier is required');
}

// Try to find by slug first, then by ID
$theme = $repository->findBySlug((string) $themeIdentifier);
if (!$theme) {
    $theme = $repository->findById((string) $themeIdentifier);
}

if (!$theme) {
    die('Theme not found: ' . htmlspecialchars($themeIdentifier));
}

// Set this theme temporarily for preview
$userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? 'preview_user';
try {
    $preferenceRepo = new \App\Domain\Theme\UserThemePreferenceRepository($db);
    // Temporarily set theme for preview using upsert method
    $preferenceRepo->upsert([
        'user_id' => $userId,
        'theme_id' => $theme['id'],
        'scope' => 'backend_admin'
    ]);
} catch (\Exception $e) {
    // Continue with theme loaded - preview will still work
    error_log('Theme preview setting error: ' . $e->getMessage());
}

$pageTitle = 'Theme Preview: ' . htmlspecialchars($theme['name'] ?? 'Unknown'); 
include __DIR__ . '/includes/header.php';

// Load theme config using ThemeLoader for consistency
require_once __DIR__ . '/includes/theme-loader.php';
$config = ThemeLoader::getThemeConfig($theme);
$colors = $config['colors'] ?? [];
$macos = $config['macos'] ?? [];
?>

<div class="admin-page-container">
    <!-- Theme Info Header -->
    <div class="admin-page-header">
        <div class="flex items-center justify-between">
            <div>
                <h1><?php echo htmlspecialchars($theme['name']); ?> Theme Preview</h1>
                <p><?php echo htmlspecialchars($theme['description'] ?? ''); ?></p>
            </div>
            <a href="/ae-admin/backend-appearance.php" class="admin-btn admin-btn-secondary">
                ← Back to Themes
            </a>
        </div>
    </div>

    <!-- Theme Details Card -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Theme Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Theme Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name:</dt>
                        <dd class="font-medium"><?php echo htmlspecialchars($theme['name']); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Slug:</dt>
                        <dd class="font-medium font-mono text-xs"><?php echo htmlspecialchars($theme['slug']); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status:</dt>
                        <dd class="font-medium">
                            <?php if ($theme['is_default']): ?>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs">Default</span>
                            <?php endif; ?>
                            <?php if ($theme['is_active']): ?>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">Inactive</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <?php if (!empty($macos)): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-xs font-semibold text-gray-600 mb-2">macOS Specific</h4>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Version:</dt>
                            <dd class="font-medium"><?php echo htmlspecialchars($macos['version'] ?? 'Big Sur / Monterey (2020-2022)'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Design System:</dt>
                            <dd class="font-medium"><?php echo htmlspecialchars($macos['designSystem'] ?? 'Apple Human Interface Guidelines'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Font System:</dt>
                            <dd class="font-medium"><?php echo htmlspecialchars($macos['fontSystem'] ?? 'SF Pro Display & SF Pro Text'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Effects:</dt>
                            <dd class="font-medium"><?php echo htmlspecialchars($macos['effects'] ?? 'Glassmorphism, Vibrancy, Depth'); ?></dd>
                        </div>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Color Palette</h3>
                <div class="grid grid-cols-3 gap-3">
                    <?php 
                    // $colors is already defined globally above
                    $colorLabels = [
                        'background' => 'Background',
                        'surface' => 'Surface',
                        'primary' => 'Primary',
                        'text' => 'Text',
                        'mutedText' => 'Muted',
                        'border' => 'Border'
                    ];
                    foreach ($colorLabels as $key => $label): 
                        if (isset($colors[$key])):
                    ?>
                        <div>
                            <div class="h-12 rounded-lg mb-1 border border-gray-200" style="background-color: <?php echo htmlspecialchars($colors[$key]); ?>;"></div>
                            <p class="text-xs text-gray-600"><?php echo $label; ?></p>
                            <p class="text-xs font-mono text-gray-500"><?php echo htmlspecialchars($colors[$key]); ?></p>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Buttons</h2>
            <p class="text-sm text-gray-500">Theme-styled buttons with rounded corners, shadows, and smooth animations</p>
        </div>
        <div class="flex flex-wrap gap-4 items-center">
            <button class="admin-btn admin-btn-primary">Primary Button</button>
            <button class="admin-btn admin-btn-secondary">Secondary Button</button>
            <button class="admin-btn admin-btn-danger">Danger Button</button>
            <button class="admin-btn admin-btn-success">Success Button</button>
            <button class="admin-btn" disabled>Disabled Button</button>
        </div>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-2"><strong>Button Style Details:</strong></p>
            <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                <li>Border radius: <?php echo htmlspecialchars($config['radius']['medium'] ?? 10); ?>px (medium)</li>
                <li>Primary color: <?php echo htmlspecialchars($config['colors']['primary'] ?? '#007AFF'); ?></li>
                <li>Shadow: <?php echo htmlspecialchars($config['shadows']['button'] ?? '0 1px 3px rgba(0,0,0,0.1)'); ?></li>
                <li>Smooth hover animations with enhanced shadow</li>
                <li>Active state with slight scale down effect</li>
            </ul>
        </div>
    </div>

    <!-- Links Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Links</h2>
            <p class="text-sm text-gray-500">Theme-styled links with primary color and hover effects</p>
        </div>
        <div class="space-y-3">
            <div>
                <a href="#" class="text-blue-600 hover:underline">Standard Link</a>
            </div>
            <div>
                <a href="#" class="text-blue-600 hover:underline font-medium">Bold Link</a>
            </div>
            <div>
                <a href="#" class="text-blue-600 hover:underline text-sm">Small Link</a>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-600 mb-2"><strong>Link Style Details:</strong></p>
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Primary color: <?php echo htmlspecialchars($config['colors']['primary'] ?? '#007AFF'); ?></li>
                    <li>Underline appears on hover</li>
                    <li>Smooth transition animations</li>
                    <li>No underline by default (clean look)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Typography Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Typography</h2>
            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($config['typography']['fontFamily'] ?? 'System fonts'); ?></p>
        </div>
        <div class="space-y-4">
            <div>
                <h1 class="text-4xl font-bold mb-2">Heading 1 - SF Pro Display</h1>
                <p class="text-sm text-gray-500">Font: <?php echo htmlspecialchars($config['typography']['fontFamily'] ?? 'SF Pro'); ?></p>
            </div>
            <div>
                <h2 class="text-3xl font-semibold mb-2">Heading 2 - SF Pro Display</h2>
                <p class="text-sm text-gray-500">Weight: 600 (Semibold)</p>
            </div>
            <div>
                <h3 class="text-2xl font-medium mb-2">Heading 3 - SF Pro Text</h3>
                <p class="text-sm text-gray-500">Weight: 500 (Medium)</p>
            </div>
            <div>
                <p class="text-base mb-2">Body Text - SF Pro Text (15px, Line Height: 1.47)</p>
                <p class="text-sm text-gray-500">This is the standard body text size used throughout macOS Big Sur and Monterey interfaces.</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Small Text - Muted Color</p>
                <p class="text-xs text-gray-500">Used for secondary information and captions</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-600 mb-2"><strong>Typography Details:</strong></p>
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Font family: <?php echo htmlspecialchars($config['typography']['fontFamily'] ?? 'system-ui'); ?></li>
                    <li>Body size: <?php echo htmlspecialchars($config['typography']['bodySize'] ?? 15); ?>px</li>
                    <li>Line height: <?php echo htmlspecialchars($config['typography']['lineHeight'] ?? 1.6); ?></li>
                    <li>Heading scale: <?php echo htmlspecialchars($config['typography']['headingScale'] ?? 1.25); ?>x</li>
                    <li>Letter spacing: <?php echo htmlspecialchars($config['typography']['letterSpacing'] ?? 'normal'); ?></li>
                    <li>Font weights: <?php echo htmlspecialchars($config['typography']['fontWeightNormal'] ?? 400); ?> (Normal), 
                        <?php echo htmlspecialchars($config['typography']['fontWeightMedium'] ?? 500); ?> (Medium), 
                        <?php echo htmlspecialchars($config['typography']['fontWeightSemibold'] ?? 600); ?> (Semibold), 
                        <?php echo htmlspecialchars($config['typography']['fontWeightBold'] ?? 700); ?> (Bold)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Forms Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Form Elements</h2>
            <p class="text-sm text-gray-500">Theme-styled form inputs with rounded corners and focus states</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Text Input</label>
                    <input type="text" class="admin-form-input" placeholder="Enter text..." value="Sample text">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Select Dropdown</label>
                    <select class="admin-form-select">
                        <option>Option 1</option>
                        <option selected>Option 2 (Selected)</option>
                        <option>Option 3</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Textarea</label>
                    <textarea class="admin-form-textarea" rows="3" placeholder="Enter multiple lines...">Sample textarea content</textarea>
                </div>
            </div>
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" checked>
                        <span>Checked Checkbox</span>
                    </label>
                </div>
                <div class="admin-form-group">
                    <label class="flex items-center gap-2">
                        <input type="checkbox">
                        <span>Unchecked Checkbox</span>
                    </label>
                </div>
                <div class="admin-form-group">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="radio-demo" checked>
                        <span>Selected Radio</span>
                    </label>
                </div>
                <div class="admin-form-group">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="radio-demo">
                        <span>Unselected Radio</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-2"><strong>Form Style Details:</strong></p>
            <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                <li>Border radius: <?php echo htmlspecialchars($config['radius']['small'] ?? 6); ?>px (small)</li>
                <li>Border color: <?php echo htmlspecialchars($config['colors']['border'] ?? '#E5E7EB'); ?></li>
                <li>Focus ring: <?php echo htmlspecialchars($config['colors']['primary'] ?? '#007AFF'); ?> (Primary color)</li>
                <li>Smooth focus transitions</li>
                <li>Clean, minimal appearance</li>
            </ul>
        </div>
    </div>

    <!-- Cards & Surfaces Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Cards & Surfaces</h2>
            <p class="text-sm text-gray-500">Theme-styled cards with shadows and depth effects</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="admin-card">
                <h3 class="font-semibold mb-2">Card with Shadow</h3>
                <p class="text-sm text-gray-600">Standard card elevation</p>
            </div>
            <div class="admin-card" style="box-shadow: var(--theme-shadow-elevated);">
                <h3 class="font-semibold mb-2">Elevated Card</h3>
                <p class="text-sm text-gray-600">Higher elevation shadow</p>
            </div>
            <div class="admin-card" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(20px);">
                <h3 class="font-semibold mb-2">Glassmorphism</h3>
                <p class="text-sm text-gray-600">Frosted glass effect</p>
            </div>
        </div>
    </div>

    <!-- What This Theme Does -->
    <div class="admin-card mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200">
        <div class="admin-section-header">
            <h2 class="admin-section-title">What This Theme Does</h2>
            <p class="text-sm text-gray-600">Key features and design characteristics of this theme</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="p-4 bg-white rounded-lg border border-blue-100">
                <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Visual Design
                </h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Background: <?php echo htmlspecialchars($colors['background'] ?? '#FFFFFF'); ?></li>
                    <li>Surface: <?php echo htmlspecialchars($colors['surface'] ?? '#F5F5F7'); ?></li>
                    <li>Primary Color: <?php echo htmlspecialchars($colors['primary'] ?? '#007AFF'); ?></li>
                    <li>Border Radius: <?php echo htmlspecialchars($config['radius']['medium'] ?? 10); ?>px (medium)</li>
                    <li>Card Shadow: <?php echo htmlspecialchars(substr($config['shadows']['card'] ?? '0 1px 3px rgba(0,0,0,0.1)', 0, 30)); ?>...</li>
                </ul>
            </div>
            <div class="p-4 bg-white rounded-lg border border-blue-100">
                <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Typography
                </h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Font: <?php echo htmlspecialchars(substr($config['typography']['fontFamily'] ?? 'system-ui', 0, 40)); ?>...</li>
                    <li>Body Size: <?php echo htmlspecialchars($config['typography']['bodySize'] ?? 15); ?>px</li>
                    <li>Line Height: <?php echo htmlspecialchars($config['typography']['lineHeight'] ?? 1.6); ?></li>
                    <li>Heading Scale: <?php echo htmlspecialchars($config['typography']['headingScale'] ?? 1.25); ?>x</li>
                    <li>Letter Spacing: <?php echo htmlspecialchars($config['typography']['letterSpacing'] ?? 'normal'); ?></li>
                </ul>
            </div>
            <div class="p-4 bg-white rounded-lg border border-blue-100">
                <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Interactive Elements
                </h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Buttons use primary color with rounded corners</li>
                    <li>Hover effects with enhanced shadows</li>
                    <li>Focus states use primary color ring</li>
                    <li>Smooth transitions on all interactions</li>
                    <li>Active states with scale animations</li>
                </ul>
            </div>
            <div class="p-4 bg-white rounded-lg border border-blue-100">
                <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Customization
                </h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>All colors are customizable</li>
                    <li>Typography settings adjustable</li>
                    <li>Border radius values configurable</li>
                    <li>Shadow effects can be modified</li>
                    <li>Live preview available</li>
                </ul>
            </div>
        </div>
        <div class="mt-4 p-4 bg-white rounded-lg border border-blue-100">
            <h3 class="font-semibold text-gray-900 mb-2">Theme Description</h3>
            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($theme['description']); ?></p>
        </div>
    </div>

    <!-- Badges & Status Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Badges & Status</h2>
        </div>
        <div class="flex flex-wrap gap-3 items-center">
            <span class="admin-badge admin-badge-success">Success</span>
            <span class="admin-badge admin-badge-danger">Error</span>
            <span class="admin-badge admin-badge-warning">Warning</span>
            <span class="admin-badge">Default</span>
        </div>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-2"><strong>Badge Colors:</strong></p>
            <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                <li>Success: <?php echo htmlspecialchars($colors['success'] ?? '#34C759'); ?></li>
                <li>Error: <?php echo htmlspecialchars($colors['error'] ?? '#FF3B30'); ?></li>
                <li>Warning: <?php echo htmlspecialchars($colors['warning'] ?? '#FF9500'); ?></li>
            </ul>
        </div>
    </div>

    <!-- Table Preview -->
    <div class="admin-card mb-6">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Table</h2>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sample Item 1</td>
                        <td><span class="admin-badge admin-badge-success">Active</span></td>
                        <td>Jan 15, 2024</td>
                        <td>
                            <button class="admin-btn admin-btn-secondary text-xs px-3 py-1">Edit</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Sample Item 2</td>
                        <td><span class="admin-badge admin-badge-warning">Pending</span></td>
                        <td>Jan 14, 2024</td>
                        <td>
                            <button class="admin-btn admin-btn-secondary text-xs px-3 py-1">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Design Principles -->
    <div class="admin-card">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Design Principles</h2>
            <p class="text-sm text-gray-500">Core design philosophy and interaction patterns</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-3">Visual Design</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• <strong>Depth:</strong> Layered shadows create hierarchy</li>
                    <li>• <strong>Glassmorphism:</strong> Frosted glass effects for modals (if supported)</li>
                    <li>• <strong>Spacing:</strong> Generous whitespace for clarity</li>
                    <li>• <strong>Rounded Corners:</strong> <?php echo htmlspecialchars($config['radius']['small'] ?? 6); ?>-<?php echo htmlspecialchars($config['radius']['large'] ?? 16); ?>px radius for modern look</li>
                    <li>• <strong>Color Harmony:</strong> Carefully selected color palette</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-3">Interaction Design</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• <strong>Smooth Animations:</strong> 0.3s cubic-bezier transitions</li>
                    <li>• <strong>Hover States:</strong> Subtle lift and shadow increase</li>
                    <li>• <strong>Focus States:</strong> <?php echo htmlspecialchars($colors['primary'] ?? '#007AFF'); ?> ring for accessibility</li>
                    <li>• <strong>Active States:</strong> Slight scale down on click</li>
                    <li>• <strong>Feedback:</strong> Clear visual response to actions</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="admin-card mb-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <a href="/ae-admin/backend-appearance.php" class="admin-btn admin-btn-secondary">
                ← Back to Themes
            </a>
            <div class="flex gap-3">
                <a href="/ae-admin/theme-customize.php?theme=<?php echo htmlspecialchars($theme['slug'] ?? $theme['id']); ?>" class="admin-btn admin-btn-primary">
                    ✏️ Customize This Theme
                </a>
                <button onclick="setAsActive()" class="admin-btn admin-btn-secondary">
                    ✓ Use This Theme
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function setAsActive() {
    if (!confirm('Set this theme as your active backend theme?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/admin/theme/backend.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                theme_id: '<?php echo htmlspecialchars($theme['id']); ?>'
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Theme activated! The page will reload.');
            setTimeout(() => {
                window.location.href = '/ae-admin/backend-appearance.php';
            }, 500);
        } else {
            alert('Failed to activate theme: ' + (data.error?.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error activating theme:', error);
        alert('Error activating theme. Please check the console for details.');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

