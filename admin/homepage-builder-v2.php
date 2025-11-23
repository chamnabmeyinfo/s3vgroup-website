<?php
declare(strict_types=1);

session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

use App\Domain\Content\HomepageSectionRepository;
use App\Database\Connection;

requireAdmin();

$db = Connection::getInstance();

// Get page_id from query parameter (defaults to null for homepage)
$pageId = $_GET['page_id'] ?? null;
$currentPage = null;

if ($pageId) {
    $pageRepository = new \App\Domain\Content\PageRepository($db);
    $currentPage = $pageRepository->findById($pageId);
    if (!$currentPage) {
        header('Location: /admin/pages.php');
        exit;
    }
}

$repository = new HomepageSectionRepository($db);
$sections = $repository->all($pageId);

// Available section types with full metadata
$sectionTypes = [
    'hero' => [
        'label' => 'Hero Slider',
        'icon' => '🎯',
        'description' => 'Full-width hero banner with slider',
        'category' => 'hero',
        'defaults' => [
            'title' => 'Welcome to Our Website',
            'subtitle' => 'Your trusted partner for quality solutions',
            'buttonText' => 'Get Started',
            'buttonLink' => '/quote.php',
        ]
    ],
    'heading' => [
        'label' => 'Heading',
        'icon' => '📝',
        'description' => 'Section heading with subtitle',
        'category' => 'content',
        'defaults' => [
            'title' => 'Section Title',
            'subtitle' => 'Section subtitle text',
        ]
    ],
    'text' => [
        'label' => 'Text Block',
        'icon' => '📄',
        'description' => 'Rich text content block',
        'category' => 'content',
        'defaults' => [
            'content' => '<p>Add your content here...</p>',
        ]
    ],
    'categories' => [
        'label' => 'Categories Grid',
        'icon' => '📦',
        'description' => 'Product categories grid',
        'category' => 'content',
        'defaults' => [
            'limit' => 12,
            'columns' => 4,
        ]
    ],
    'products' => [
        'label' => 'Featured Products',
        'icon' => '🛍️',
        'description' => 'Featured products showcase',
        'category' => 'content',
        'defaults' => [
            'limit' => 6,
            'columns' => 3,
        ]
    ],
    'features' => [
        'label' => 'Features',
        'icon' => '✨',
        'description' => 'Key features/benefits grid',
        'category' => 'content',
        'defaults' => [
            'items' => [],
        ]
    ],
    'testimonials' => [
        'label' => 'Testimonials',
        'icon' => '💬',
        'description' => 'Customer testimonials',
        'category' => 'content',
        'defaults' => [
            'limit' => 6,
        ]
    ],
    'newsletter' => [
        'label' => 'Newsletter Signup',
        'icon' => '📧',
        'description' => 'Email subscription form',
        'category' => 'marketing',
        'defaults' => [
            'title' => 'Subscribe to Our Newsletter',
            'subtitle' => 'Get the latest updates and exclusive offers',
        ]
    ],
    'cta' => [
        'label' => 'Call to Action',
        'icon' => '🚀',
        'description' => 'Call-to-action banner',
        'category' => 'marketing',
        'defaults' => [
            'title' => 'Ready to Get Started?',
            'buttonText' => 'Contact Us',
            'buttonLink' => '/quote.php',
        ]
    ],
    'spacer' => [
        'label' => 'Spacer',
        'icon' => '↕️',
        'description' => 'Add spacing between sections',
        'category' => 'layout',
        'defaults' => [
            'height' => 60,
        ]
    ],
    'divider' => [
        'label' => 'Divider',
        'icon' => '➖',
        'description' => 'Horizontal divider line',
        'category' => 'layout',
        'defaults' => [
            'style' => 'solid',
        ]
    ],
    'custom' => [
        'label' => 'Custom HTML',
        'icon' => '⚙️',
        'description' => 'Custom HTML/CSS/JS',
        'category' => 'advanced',
        'defaults' => [
            'html' => '',
            'css' => '',
        ]
    ],
];

$pageTitle = $currentPage ? ($currentPage['title'] . ' - Page Builder') : 'Homepage Builder - Visual Editor';
include __DIR__ . '/includes/header.php';

// Show page info if editing a specific page
if ($currentPage):
?>
<div style="background: #f9f9f9; padding: 10px 20px; border-bottom: 1px solid #ddd; margin: -20px -20px 20px -20px;">
    <div style="max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-size: 12px; color: #666; text-transform: uppercase;">Editing:</span>
            <strong style="font-size: 14px; color: #333; margin-left: 8px;"><?php echo e($currentPage['title']); ?></strong>
            <span style="font-size: 12px; color: #999; margin-left: 8px;">/<?php echo e($currentPage['slug']); ?></span>
        </div>
        <a href="/admin/pages.php" style="font-size: 12px; color: #0b3a63; text-decoration: none;">← Back to Pages</a>
    </div>
</div>
<?php endif; ?>

<style>
/* Elementor-style Builder Interface */
.homepage-builder-v2 {
    display: flex;
    height: calc(100vh - 64px);
    background: #f0f0f1;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

/* Ensure all elements are clickable */
.homepage-builder-v2 * {
    pointer-events: auto;
}

.homepage-builder-v2 .dragging-ghost,
.homepage-builder-v2 .canvas-frame.drag-over-active::before {
    pointer-events: none !important;
}

/* Left Panel - Elements Library */
.elements-panel {
    width: 280px;
    background: #fff;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    z-index: 100;
}

.elements-panel-header {
    padding: 15px;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.elements-category {
    margin-bottom: 20px;
}

.elements-category-title {
    padding: 10px 15px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.element-item {
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: move;
    border-bottom: 1px solid #f0f0f1;
    transition: background 0.2s;
}

.element-item:hover:not(.dragging) {
    background: #f0f7ff !important;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(11, 58, 99, 0.1);
}

.element-item-icon {
    font-size: 20px;
}

.element-item-info {
    flex: 1;
}

.element-item-label {
    font-size: 13px;
    font-weight: 500;
    color: #333;
}

.element-item-desc {
    font-size: 11px;
    color: #999;
    margin-top: 2px;
}

/* Center - Canvas/Preview */
.canvas-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
    position: relative;
}

.canvas-toolbar {
    padding: 10px 15px;
    background: #fff;
    border-bottom: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    z-index: 50;
    flex-wrap: wrap;
}

.canvas-preview {
    flex: 1;
    overflow-y: auto;
    background: #f9f9f9;
    position: relative;
}

.canvas-frame {
    min-height: 100%;
    background: #fff;
    margin: 20px auto;
    max-width: 1200px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: relative;
    transition: background-color 0.2s, border-color 0.2s;
}

/* Right Panel - Settings */
.settings-panel {
    width: 320px;
    background: #fff;
    border-left: 1px solid #ddd;
    overflow-y: auto;
    z-index: 100;
}

.settings-panel-header {
    padding: 15px;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
    font-weight: 600;
    font-size: 14px;
}

.settings-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
}

.settings-tab {
    flex: 1;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
}

.settings-tab.active {
    border-bottom-color: #0b3a63;
    color: #0b3a63;
}

.settings-content {
    padding: 15px;
}

.settings-section {
    margin-bottom: 25px;
}

.settings-section-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #666;
    margin-bottom: 12px;
}

.setting-field {
    margin-bottom: 15px;
}

.setting-label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #333;
    margin-bottom: 6px;
}

.setting-input,
.setting-textarea,
.setting-select {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 13px;
}

.setting-textarea {
    min-height: 80px;
    resize: vertical;
}

.setting-help {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
}

.color-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.color-picker {
    width: 40px;
    height: 35px;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

.color-input {
    flex: 1;
}

/* Section in Canvas */
.canvas-section {
    position: relative;
    border: 2px solid transparent;
    transition: border-color 0.2s, box-shadow 0.2s;
    margin-bottom: 10px;
    cursor: pointer;
    min-height: 60px;
    user-select: none;
}

.canvas-section:hover {
    border-color: #0b3a63;
    box-shadow: 0 2px 8px rgba(11, 58, 99, 0.1);
}

.canvas-section.selected {
    border-color: #0b3a63;
    box-shadow: 0 0 0 2px rgba(11, 58, 99, 0.2), 0 4px 12px rgba(11, 58, 99, 0.15);
    outline: none;
}

/* Ensure preview content doesn't block clicks */
.canvas-section .section-preview-content {
    pointer-events: auto;
    cursor: pointer;
}

.canvas-section .section-preview-content * {
    pointer-events: none; /* Let clicks bubble to section */
}

/* But allow interaction with actual interactive elements */
.canvas-section .section-preview-content a,
.canvas-section .section-preview-content button,
.canvas-section .section-preview-content input,
.canvas-section .section-preview-content textarea,
.canvas-section .section-preview-content select {
    pointer-events: auto;
}

.section-toolbar {
    position: absolute;
    top: -30px;
    left: 0;
    display: none;
    align-items: center;
    gap: 5px;
    background: #0b3a63;
    padding: 4px 8px;
    border-radius: 3px 3px 0 0;
    z-index: 10;
}

.canvas-section.selected .section-toolbar {
    display: flex;
}

.section-toolbar-btn {
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    padding: 4px 6px;
    font-size: 12px;
    border-radius: 2px;
    transition: background 0.2s;
}

.section-toolbar-btn:hover {
    background: rgba(255,255,255,0.2);
}

.drag-handle {
    cursor: move;
    color: #fff;
    font-size: 14px;
}

/* Responsive Breakpoints */
.responsive-controls {
    display: flex;
    gap: 5px;
    margin-left: auto;
}

.responsive-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.responsive-btn.active {
    background: #0b3a63;
    color: #fff;
    border-color: #0b3a63;
}

/* Save Button */
.save-btn {
    background: #0b3a63;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 3px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

.save-btn:hover {
    background: #1a5a8a;
}

.save-btn.saving {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Empty State */
.empty-canvas {
    text-align: center;
    padding: 60px 20px;
    color: #999;
    pointer-events: none; /* Allow drops to pass through */
    position: relative;
    z-index: 1;
}

.empty-canvas-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-canvas-text {
    font-size: 14px;
    margin-bottom: 8px;
}

.empty-canvas-hint {
    font-size: 12px;
    color: #ccc;
}

/* Drag and drop styles */
.element-item {
    cursor: grab !important;
    user-select: none;
    transition: all 0.2s ease;
    position: relative;
}

.element-item:hover {
    background: #f0f7ff !important;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(11, 58, 99, 0.1);
}

.element-item:active {
    cursor: grabbing !important;
}

.element-item[draggable="true"] {
    -webkit-user-drag: element;
}

.element-item.dragging {
    opacity: 0.3 !important;
    transform: scale(0.95);
}

.dragging-ghost {
    z-index: 10000 !important;
    pointer-events: none !important;
}

/* Drop zone animations */
@keyframes pulse-border {
    0%, 100% {
        border-color: #0b3a63;
        box-shadow: 0 0 20px rgba(11, 58, 99, 0.3);
    }
    50% {
        border-color: #1a5a8a;
        box-shadow: 0 0 30px rgba(11, 58, 99, 0.5);
    }
}

.canvas-frame.drag-over-active {
    position: relative;
}

.canvas-frame.drag-over-active::before {
    content: 'Drop element here';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(11, 58, 99, 0.9);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    z-index: 1000;
    pointer-events: none !important; /* Critical - must not block clicks */
    animation: fade-in 0.3s ease;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

.sortable-ghost {
    opacity: 0.4;
    background-color: #f0f7ff;
    border: 2px dashed #0b3a63 !important;
}

.sortable-chosen {
    box-shadow: 0 0 0 2px rgba(11, 58, 99, 0.3);
}

.sortable-drag {
    opacity: 0.8;
}
</style>

<div class="homepage-builder-v2">
    <!-- Left Panel - Elements Library -->
    <div class="elements-panel" id="elements-panel">
        <div class="elements-panel-header">
            <input type="text" placeholder="🔍 Search elements..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 12px;" id="element-search">
        </div>
        <?php
        $categories = [
            'hero' => ['Hero'],
            'content' => ['Content'],
            'marketing' => ['Marketing'],
            'layout' => ['Layout'],
            'advanced' => ['Advanced'],
        ];
        
        foreach ($categories as $catKey => $catLabel):
            $catSections = array_filter($sectionTypes, fn($s) => $s['category'] === $catKey);
            if (empty($catSections)) continue;
        ?>
            <div class="elements-category">
                <div class="elements-category-title"><?php echo $catLabel[0]; ?></div>
                <?php foreach ($catSections as $type => $info): ?>
                    <div class="element-item" 
                         draggable="true"
                         data-type="<?php echo e($type); ?>"
                         data-defaults='<?php echo json_encode($info['defaults']); ?>'>
                        <div class="element-item-icon"><?php echo $info['icon']; ?></div>
                        <div class="element-item-info">
                            <div class="element-item-label"><?php echo e($info['label']); ?></div>
                            <div class="element-item-desc"><?php echo e($info['description']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Center - Canvas -->
    <div class="canvas-container">
        <div class="canvas-toolbar">
            <div class="flex items-center gap-3">
                <?php if ($currentPage): ?>
                    <a href="/admin/pages.php" class="text-sm text-gray-600 hover:text-[#0b3a63] flex items-center gap-1">
                        ← Back to Pages
                    </a>
                    <span class="text-gray-400">|</span>
                    <span class="text-sm font-semibold text-[#0b3a63]"><?php echo e($currentPage['title']); ?></span>
                    <a href="/page.php?slug=<?php echo urlencode($currentPage['slug']); ?>" 
                       target="_blank"
                       class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        👁️ Preview
                    </a>
                <?php else: ?>
                    <a href="/admin/pages.php" class="text-sm text-gray-600 hover:text-[#0b3a63] flex items-center gap-1">
                        ← All Pages
                    </a>
                    <span class="text-gray-400">|</span>
                    <span class="text-sm font-semibold text-[#0b3a63]">Homepage</span>
                    <a href="/" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        👁️ Preview
                    </a>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" class="save-btn" id="save-builder-btn" style="background: #0b3a63; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background 0.2s;">
                    <span>💾</span> Save Changes
                </button>
                <div class="responsive-controls" style="display: flex; gap: 5px; margin-left: auto;">
                    <button class="responsive-btn active" data-device="desktop" title="Desktop" style="width: 32px; height: 32px; border: 1px solid #ddd; background: #0b3a63; color: white; cursor: pointer; border-radius: 3px; display: flex; align-items: center; justify-content: center;">
                        🖥️
                    </button>
                    <button class="responsive-btn" data-device="tablet" title="Tablet" style="width: 32px; height: 32px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 3px; display: flex; align-items: center; justify-content: center;">
                        📱
                    </button>
                    <button class="responsive-btn" data-device="mobile" title="Mobile" style="width: 32px; height: 32px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 3px; display: flex; align-items: center; justify-content: center;">
                        📱
                    </button>
                </div>
            </div>
        </div>
        
        <div class="canvas-preview" id="canvas-preview">
            <div class="canvas-frame" id="canvas-frame">
                <?php if (empty($sections)): ?>
                <div class="empty-canvas">
                    <div class="empty-canvas-icon">📄</div>
                    <div class="empty-canvas-text"><?php echo $currentPage ? 'This page is empty' : 'Your homepage is empty'; ?></div>
                    <div class="empty-canvas-hint">Drag elements from the left panel to get started</div>
                </div>
                <?php else: ?>
                    <?php foreach ($sections as $index => $section): ?>
                        <?php include __DIR__ . '/includes/homepage-section-canvas.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Panel - Settings -->
    <div class="settings-panel" id="settings-panel">
        <div class="settings-panel-header">
            <span id="settings-title">Section Settings</span>
        </div>
        <div class="settings-tabs">
            <div class="settings-tab active" data-tab="content">Content</div>
            <div class="settings-tab" data-tab="style">Style</div>
            <div class="settings-tab" data-tab="advanced">Advanced</div>
        </div>
        <div class="settings-content" id="settings-content">
            <div class="empty-settings">
                <p style="text-align: center; color: #999; padding: 40px 20px;">
                    Select a section to edit its settings
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/admin/js/homepage-builder-v2.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>

