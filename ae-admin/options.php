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
use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService;

$db = getDB();
$repository = new SiteOptionRepository($db);
$service = new SiteOptionService($repository);

$groupedOptions = $service->getGrouped();

// Organized section labels with descriptions
$groupLabels = [
    'general' => [
        'label' => 'General Settings',
        'description' => 'Basic site information and core settings',
        'icon' => '⚙️',
    ],
    'homepage_design' => [
        'label' => 'Home Page Design',
        'description' => 'Customize your homepage hero section and content',
        'icon' => '🏠',
    ],
    'typography_fonts' => [
        'label' => 'Typography & Fonts',
        'description' => 'Configure fonts, sizes, weights, and typography settings',
        'icon' => '✍️',
    ],
    'colors_theme' => [
        'label' => 'Colors & Theme',
        'description' => 'Customize color scheme, primary colors, and theme colors',
        'icon' => '🎨',
    ],
    'layout_spacing' => [
        'label' => 'Layout & Spacing',
        'description' => 'Adjust layout dimensions, spacing units, and container settings',
        'icon' => '📐',
    ],
    'components' => [
        'label' => 'Components & UI Elements',
        'description' => 'Style buttons, cards, shadows, and background patterns',
        'icon' => '🧩',
    ],
    'language_localization' => [
        'label' => 'Language & Localization',
        'description' => 'Set language, locale, date/time formats, and currency',
        'icon' => '🌐',
    ],
    'seo_analytics' => [
        'label' => 'SEO & Analytics',
        'description' => 'Configure SEO meta tags, Open Graph, and analytics tracking',
        'icon' => '📊',
    ],
    'social_media' => [
        'label' => 'Social Media',
        'description' => 'Add social media links and profiles',
        'icon' => '📱',
    ],
    'contact_info' => [
        'label' => 'Contact Information',
        'description' => 'Update contact details, address, and business hours',
        'icon' => '📞',
    ],
    'email_settings' => [
        'label' => 'Email Settings',
        'description' => 'Configure email sender settings and SMTP configuration',
        'icon' => '📧',
    ],
    'features' => [
        'label' => 'Features & Functionality',
        'description' => 'Enable or disable website features and functionality',
        'icon' => '✨',
    ],
    'performance' => [
        'label' => 'Performance & Optimization',
        'description' => 'Optimize website performance with caching and compression',
        'icon' => '⚡',
    ],
    'footer' => [
        'label' => 'Footer Settings',
        'description' => 'Customize footer content and copyright information',
        'icon' => '⬇️',
    ],
    'advanced' => [
        'label' => 'Advanced Settings',
        'description' => 'Custom CSS, JavaScript, and advanced customization options',
        'icon' => '🔧',
    ],
];

$pageTitle = 'Site Options';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Site Settings</h1>
                            <p class="text-sm text-gray-500 mt-0.5">Customize your website's appearance and functionality</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Sort groups by priority - Define BEFORE using in sidebar
    $sortedGroups = [];
    foreach ($groupLabels as $key => $config) {
        if (isset($groupedOptions[$key])) {
            $sortedGroups[$key] = $config;
        }
    }
    
    // Find first section key
    $firstSectionKey = null;
    foreach ($sortedGroups as $key => $config) {
        if ($firstSectionKey === null) {
            $firstSectionKey = $key;
            break;
        }
    }
    ?>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Modern Sidebar Navigation -->
        <aside class="lg:w-72 flex-shrink-0">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm sticky top-4 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Settings Sections</h2>
                    <p class="text-xs text-gray-500 mt-1">Navigate between settings categories</p>
                </div>
                <nav class="p-3 space-y-1" id="options-nav">
                    <?php 
                    $navFirstKey = null;
                    foreach ($sortedGroups as $groupKey => $groupConfig): 
                        $groupLabel = is_array($groupConfig) ? $groupConfig['label'] : $groupConfig;
                        $groupIcon = is_array($groupConfig) ? ($groupConfig['icon'] ?? '') : '';
                        $groupDescription = is_array($groupConfig) ? ($groupConfig['description'] ?? '') : '';
                        if ($navFirstKey === null) {
                            $navFirstKey = $groupKey;
                        }
                        $isNavActive = ($navFirstKey === $groupKey);
                    ?>
                        <a
                            href="#section-<?php echo e($groupKey); ?>"
                            data-section="<?php echo e($groupKey); ?>"
                            class="option-nav-link group flex items-start gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $isNavActive ? 'bg-blue-50 border border-blue-200 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 border border-transparent'; ?>"
                        >
                            <span class="text-xl mt-0.5 flex-shrink-0"><?php echo $groupIcon ?: '⚙️'; ?></span>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium"><?php echo e($groupLabel); ?></div>
                                <?php if ($groupDescription && $isNavActive): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 line-clamp-1"><?php echo e($groupDescription); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isNavActive): ?>
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
            <form id="options-form" class="space-y-8">
            <?php 
            // Use already defined $sortedGroups and $firstSectionKey from above
            foreach ($sortedGroups as $groupKey => $groupConfig): 
                $groupLabel = is_array($groupConfig) ? $groupConfig['label'] : $groupConfig;
                $groupDescription = is_array($groupConfig) ? ($groupConfig['description'] ?? '') : '';
                $groupIcon = is_array($groupConfig) ? ($groupConfig['icon'] ?? '') : '';
                $isActive = ($firstSectionKey === $groupKey);
            ?>
                
                <div 
                    id="section-<?php echo e($groupKey); ?>" 
                    class="options-section bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 <?php echo $isActive ? '' : 'hidden'; ?>"
                    data-section-key="<?php echo e($groupKey); ?>"
                >
                    <!-- Section Header -->
                    <div class="px-6 py-5 bg-gradient-to-br from-gray-50 via-white to-gray-50 border-b border-gray-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl"><?php echo $groupIcon ?: '⚙️'; ?></span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-gray-900 mb-1"><?php echo e($groupLabel); ?></h2>
                                <?php if ($groupDescription): ?>
                                    <p class="text-sm text-gray-600 leading-relaxed"><?php echo e($groupDescription); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Section Content -->
                    <div class="p-6 space-y-6">
                        <?php foreach ($groupedOptions[$groupKey] as $option): ?>
                            <div class="option-field bg-gradient-to-br from-gray-50 to-white rounded-xl p-6 border border-gray-200 hover:border-gray-300 hover:shadow-sm transition-all" data-key="<?php echo e($option['key_name']); ?>">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-900 mb-1.5">
                                        <?php echo e($option['label']); ?>
                                    </label>
                                    <?php if ($option['description']): ?>
                                        <p class="text-xs text-gray-600 leading-relaxed"><?php echo e($option['description']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <?php
                                $value = $option['value'];
                                $type = $option['type'];
                                $key = $option['key_name'];
                                ?>

                                <?php if ($type === 'textarea'): ?>
                                    <?php 
                                    $rows = 3;
                                    $isCode = false;
                                    if (strpos($key, 'custom_css') !== false || strpos($key, 'custom_js') !== false) {
                                        $rows = 10;
                                        $isCode = true;
                                    } elseif (strpos($key, 'description') !== false || strpos($key, 'content') !== false || strpos($key, 'subtitle') !== false) {
                                        $rows = 5;
                                    }
                                    ?>
                                    <textarea
                                        name="<?php echo e($key); ?>"
                                        rows="<?php echo $rows; ?>"
                                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all <?php echo $isCode ? 'font-mono text-sm' : ''; ?>"
                                        placeholder="<?php 
                                            if (strpos($key, 'custom_css') !== false) echo '/* Add your custom CSS here */';
                                            elseif (strpos($key, 'custom_js') !== false) echo '// Add your custom JavaScript here';
                                        ?>"
                                    ><?php echo e($value); ?></textarea>
                                    <?php if (strpos($key, 'custom_css') !== false): ?>
                                        <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs text-blue-800">
                                                <span class="font-semibold">💡 Tip:</span> Use CSS selectors like <code class="bg-blue-100 px-1.5 py-0.5 rounded text-xs font-mono">.class-name</code> or <code class="bg-blue-100 px-1.5 py-0.5 rounded text-xs font-mono">#id-name</code>
                                            </p>
                                        </div>
                                    <?php elseif (strpos($key, 'custom_js') !== false): ?>
                                        <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs text-blue-800">
                                                <span class="font-semibold">💡 Tip:</span> Add JavaScript code that will be executed on the page
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif ($type === 'color'): ?>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="color"
                                            name="<?php echo e($key); ?>"
                                            value="<?php echo e($value ?: '#000000'); ?>"
                                            class="h-12 w-20 border-2 border-gray-300 rounded-lg cursor-pointer shadow-sm hover:shadow transition-all"
                                        >
                                        <input
                                            type="text"
                                            name="<?php echo e($key); ?>_text"
                                            value="<?php echo e($value); ?>"
                                            placeholder="#000000"
                                            pattern="^#[0-9A-Fa-f]{6}$"
                                            class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        >
                                    </div>

                                <?php elseif ($type === 'boolean'): ?>
                                    <label class="inline-flex items-center gap-3 p-4 bg-white rounded-xl border-2 border-gray-200 hover:border-blue-300 transition-all cursor-pointer group">
                                        <div class="relative flex-shrink-0">
                                            <input
                                                type="checkbox"
                                                name="<?php echo e($key); ?>"
                                                value="1"
                                                <?php echo $value ? 'checked' : ''; ?>
                                                class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer transition-all"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-sm font-semibold text-gray-900 block">
                                                <?php echo $value ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                            <p class="text-xs text-gray-500 mt-0.5">Toggle this setting on or off</p>
                                        </div>
                                        <?php if ($value): ?>
                                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        <?php endif; ?>
                                    </label>

                                <?php elseif (in_array($key, ['design_button_style', 'design_card_shadow', 'design_background_pattern'])): ?>
                                    <?php
                                    $options = match($key) {
                                        'design_button_style' => [
                                            'rounded' => 'Rounded (default)',
                                            'square' => 'Square (no radius)',
                                            'pill' => 'Pill (fully rounded)',
                                        ],
                                        'design_card_shadow' => [
                                            'none' => 'None',
                                            'small' => 'Small',
                                            'medium' => 'Medium (default)',
                                            'large' => 'Large',
                                        ],
                                        'design_background_pattern' => [
                                            'none' => 'None',
                                            'dots' => 'Dots',
                                            'grid' => 'Grid',
                                            'lines' => 'Lines',
                                        ],
                                        default => [],
                                    };
                                    ?>
                                    <select
                                        name="<?php echo e($key); ?>"
                                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    >
                                        <?php foreach ($options as $optValue => $optLabel): ?>
                                            <option value="<?php echo e($optValue); ?>" <?php echo $value === $optValue ? 'selected' : ''; ?>>
                                                <?php echo e($optLabel); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($type === 'number'): ?>
                                    <input
                                        type="number"
                                        name="<?php echo e($key); ?>"
                                        value="<?php echo e($value); ?>"
                                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    >

                                <?php elseif ($type === 'image'): ?>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="url"
                                                name="<?php echo e($key); ?>"
                                                value="<?php echo e($value); ?>"
                                                placeholder="https://example.com/image.jpg or upload file"
                                                class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                id="input-<?php echo e($key); ?>"
                                            >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                id="file-<?php echo e($key); ?>"
                                                data-target="input-<?php echo e($key); ?>"
                                            >
                                            <button
                                                type="button"
                                                onclick="document.getElementById('file-<?php echo e($key); ?>').click()"
                                                class="px-5 py-3 bg-gradient-to-r from-gray-100 to-gray-50 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:from-gray-200 hover:to-gray-100 transition-all shadow-sm hover:shadow"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    Upload
                                                </span>
                                            </button>
                                        </div>
                                        <?php if ($value): ?>
                                            <div class="mt-2">
                                                <img src="<?php echo e($value); ?>" alt="Preview" class="h-20 w-auto rounded border border-gray-300 object-contain">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <input
                                        type="<?php echo $type === 'url' ? 'url' : 'text'; ?>"
                                        name="<?php echo e($key); ?>"
                                        value="<?php echo e($value); ?>"
                                        placeholder=""
                                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Modern Save Button Bar -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-6 sticky bottom-4 mt-8">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">💡 Tip:</span> Changes are saved to your database immediately
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            id="reset-btn"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset Changes
                            </span>
                        </button>
                        <button
                            type="submit"
                            id="save-btn"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save All Changes
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Tab Navigation
    const navLinks = document.querySelectorAll('.option-nav-link');
    const sections = document.querySelectorAll('.options-section');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            const targetSection = link.dataset.section;
            
            // Update active link
            navLinks.forEach(l => {
                l.classList.remove('bg-blue-50', 'border-blue-200', 'text-blue-700', 'shadow-sm');
                l.classList.add('text-gray-700', 'hover:bg-gray-50', 'border-transparent');
                // Remove description from inactive items
                const desc = l.querySelector('.text-xs');
                if (desc) desc.style.display = 'none';
            });
            link.classList.remove('text-gray-700', 'hover:bg-gray-50', 'border-transparent');
            link.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700', 'shadow-sm');
            // Show description for active item
            const desc = link.querySelector('.text-xs');
            if (desc) desc.style.display = 'block';
            
            // Show/hide sections
            sections.forEach(section => {
                if (section.dataset.sectionKey === targetSection) {
                    section.classList.remove('hidden');
                    // Smooth scroll to section
                    setTimeout(() => {
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                } else {
                    section.classList.add('hidden');
                }
            });
            
            // Update URL hash
            window.history.pushState(null, '', `#section-${targetSection}`);
        });
    });

    // Handle initial hash on page load
    const hash = window.location.hash;
    if (hash) {
        const targetSection = hash.replace('#section-', '');
        const targetLink = document.querySelector(`[data-section="${targetSection}"]`);
        if (targetLink) {
            targetLink.click();
        }
    }

})();
</script>

<script>
(function() {
    const form = document.getElementById('options-form');
    const saveBtn = document.getElementById('save-btn');
    const resetBtn = document.getElementById('reset-btn');
    const originalData = {};

    // Store original values
    form.querySelectorAll('input, textarea').forEach(input => {
        if (input.name && !input.name.endsWith('_text')) {
            originalData[input.name] = input.type === 'checkbox' ? input.checked : input.value;
        }
    });

    // Sync color inputs
    form.querySelectorAll('input[type="color"]').forEach(colorInput => {
        const textInput = form.querySelector(`input[name="${colorInput.name}_text"]`);
        if (textInput) {
            colorInput.addEventListener('input', () => {
                textInput.value = colorInput.value;
            });
            textInput.addEventListener('input', () => {
                if (/^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
                    colorInput.value = textInput.value;
                }
            });
        }
    });

    // Handle image uploads with automatic optimization
    form.querySelectorAll('input[type="file"]').forEach(fileInput => {
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Client-side validation: Check file size before upload
            const maxSize = 50 * 1024 * 1024; // 50MB
            if (file.size > maxSize) {
                alert('❌ File is too large! Maximum size is 50MB. Please choose a smaller image.');
                e.target.value = ''; // Clear selection
                return;
            }

            // Warn about large files (but allow them - server will optimize)
            const largeFileThreshold = 10 * 1024 * 1024; // 10MB
            if (file.size > largeFileThreshold) {
                const proceed = confirm(
                    `⚠️ Large image detected (${(file.size / 1024 / 1024).toFixed(1)} MB).\n\n` +
                    `The image will be automatically optimized to under 1MB after upload.\n\n` +
                    `Continue with upload?`
                );
                if (!proceed) {
                    e.target.value = '';
                    return;
                }
            }

            const targetInputId = fileInput.dataset.target;
            const targetInput = document.getElementById(targetInputId);
            const preview = targetInput.parentElement.querySelector('img');

            // Show loading state with optimization message
            if (preview) {
                preview.style.opacity = '0.5';
            }
            
            // Show upload progress
            const originalButton = fileInput.parentElement.querySelector('button');
            const originalButtonText = originalButton ? originalButton.textContent : '';
            if (originalButton) {
                originalButton.disabled = true;
                originalButton.textContent = 'Uploading & Optimizing...';
            }

            try {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('/api/admin/upload.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Upload failed');
                }

                // Update input value
                targetInput.value = result.data.url;

                // Update preview
                if (preview) {
                    preview.src = result.data.url;
                    preview.style.opacity = '1';
                } else {
                    // Create preview
                    const img = document.createElement('img');
                    img.src = result.data.url;
                    img.alt = 'Preview';
                    img.className = 'h-20 w-auto rounded border border-gray-300 object-contain mt-2';
                    targetInput.parentElement.appendChild(img);
                }

                // Show optimization results if available
                let successMessage = '✅ Image uploaded successfully!';
                if (result.data.optimized && result.data.sizeReduction) {
                    successMessage = `✅ Image uploaded and optimized!\n\n` +
                        `Size reduced by ${result.data.sizeReduction}%\n` +
                        `Final size: ${(result.data.size / 1024 / 1024).toFixed(2)} MB`;
                } else if (result.data.message) {
                    successMessage = `✅ ${result.data.message}`;
                }
                
                alert(successMessage);
            } catch (error) {
                alert('❌ Upload error: ' + error.message);
            } finally {
                if (preview) {
                    preview.style.opacity = '1';
                }
                if (originalButton) {
                    originalButton.disabled = false;
                    originalButton.textContent = originalButtonText;
                }
            }
        });
    });

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const bulk = {};

        form.querySelectorAll('input, textarea').forEach(input => {
            if (!input.name || input.name.endsWith('_text')) return;

            if (input.type === 'checkbox') {
                bulk[input.name] = input.checked ? '1' : '0';
            } else {
                bulk[input.name] = input.value || '';
            }
        });

        try {
            const response = await fetch('/api/admin/options/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bulk }),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to save options.');
            }

            alert('✅ Site options saved successfully!');
            
            // Update original data
            Object.keys(bulk).forEach(key => {
                originalData[key] = bulk[key];
            });

            // Reload to get any server-side processing
            window.location.reload();
        } catch (error) {
            alert('❌ Error: ' + error.message);
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save All Changes';
        }
    });

    // Reset changes
    resetBtn.addEventListener('click', () => {
        if (!confirm('Reset all changes to original values?')) {
            return;
        }

        form.querySelectorAll('input, textarea').forEach(input => {
            if (!input.name || input.name.endsWith('_text')) return;

            if (input.type === 'checkbox') {
                input.checked = originalData[input.name] === true || originalData[input.name] === '1';
            } else {
                input.value = originalData[input.name] || '';
                
                // Update color picker if it's a color input
                if (input.type === 'color') {
                    const textInput = form.querySelector(`input[name="${input.name}_text"]`);
                    if (textInput) {
                        textInput.value = originalData[input.name] || '';
                    }
                } else if (input.name.endsWith('_text')) {
                    const colorInput = form.querySelector(`input[name="${input.name.replace('_text', '')}"]`);
                    if (colorInput && colorInput.type === 'color') {
                        colorInput.value = originalData[input.name.replace('_text', '')] || '#000000';
                    }
                }
            }
        });
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

