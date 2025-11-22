<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

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
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Configuration</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Site Options</h1>
            <p class="text-sm text-gray-600">Customize your website's appearance, content, and settings</p>
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
        <!-- Sidebar Navigation -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm sticky top-4">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900">Sections</h2>
                </div>
                <nav class="p-2 space-y-1" id="options-nav">
                    <?php 
                    $navFirstKey = null;
                    foreach ($sortedGroups as $groupKey => $groupConfig): 
                        $groupLabel = is_array($groupConfig) ? $groupConfig['label'] : $groupConfig;
                        $groupIcon = is_array($groupConfig) ? ($groupConfig['icon'] ?? '') : '';
                        if ($navFirstKey === null) {
                            $navFirstKey = $groupKey;
                        }
                        $isNavActive = ($navFirstKey === $groupKey);
                    ?>
                        <a
                            href="#section-<?php echo e($groupKey); ?>"
                            data-section="<?php echo e($groupKey); ?>"
                            class="option-nav-link flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $isNavActive ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>"
                        >
                            <?php if ($groupIcon): ?>
                                <span class="text-lg"><?php echo $groupIcon; ?></span>
                            <?php endif; ?>
                            <span><?php echo e($groupLabel); ?></span>
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
                    class="options-section bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden <?php echo $isActive ? '' : 'hidden'; ?>"
                    data-section-key="<?php echo e($groupKey); ?>"
                >
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <?php if ($groupIcon): ?>
                                <span class="text-2xl"><?php echo $groupIcon; ?></span>
                            <?php endif; ?>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900"><?php echo e($groupLabel); ?></h2>
                                <?php if ($groupDescription): ?>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo e($groupDescription); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <?php foreach ($groupedOptions[$groupKey] as $option): ?>
                            <div class="option-field" data-key="<?php echo e($option['key_name']); ?>">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php echo e($option['label']); ?>
                                </label>
                                <?php if ($option['description']): ?>
                                    <p class="text-xs text-gray-500 mb-2"><?php echo e($option['description']); ?></p>
                                <?php endif; ?>

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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63] <?php echo $isCode ? 'font-mono text-sm' : ''; ?>"
                                        placeholder="<?php 
                                            if (strpos($key, 'custom_css') !== false) echo '/* Add your custom CSS here */';
                                            elseif (strpos($key, 'custom_js') !== false) echo '// Add your custom JavaScript here';
                                        ?>"
                                    ><?php echo e($value); ?></textarea>
                                    <?php if (strpos($key, 'custom_css') !== false): ?>
                                        <p class="text-xs text-gray-500 mt-1">💡 Use CSS selectors like <code class="bg-gray-100 px-1 rounded text-xs">.class-name</code> or <code class="bg-gray-100 px-1 rounded text-xs">#id-name</code>. Example: <code class="bg-gray-100 px-1 rounded text-xs">body { background: #f0f0f0; }</code></p>
                                    <?php elseif (strpos($key, 'custom_js') !== false): ?>
                                        <p class="text-xs text-gray-500 mt-1">💡 Add JavaScript code that will be executed on the page. Example: <code class="bg-gray-100 px-1 rounded text-xs">console.log('Hello!');</code></p>
                                    <?php endif; ?>

                                <?php elseif ($type === 'color'): ?>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="color"
                                            name="<?php echo e($key); ?>"
                                            value="<?php echo e($value ?: '#000000'); ?>"
                                            class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                                        >
                                        <input
                                            type="text"
                                            name="<?php echo e($key); ?>_text"
                                            value="<?php echo e($value); ?>"
                                            placeholder="#000000"
                                            pattern="^#[0-9A-Fa-f]{6}$"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"
                                        >
                                    </div>

                                <?php elseif ($type === 'boolean'): ?>
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            name="<?php echo e($key); ?>"
                                            value="1"
                                            <?php echo $value ? 'checked' : ''; ?>
                                            class="rounded border-gray-300 text-[#0b3a63] focus:ring-[#0b3a63]"
                                        >
                                        <span class="ml-2 text-sm text-gray-600">
                                            <?php echo $value ? 'Enabled' : 'Disabled'; ?>
                                        </span>
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"
                                    >

                                <?php elseif ($type === 'image'): ?>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="url"
                                                name="<?php echo e($key); ?>"
                                                value="<?php echo e($value); ?>"
                                                placeholder="https://example.com/image.jpg or upload file"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"
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
                                                class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
                                            >
                                                Upload
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Save Button Bar -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 sticky bottom-0">
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        id="reset-btn"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63]"
                    >
                        Reset Changes
                    </button>
                    <button
                        type="submit"
                        id="save-btn"
                        class="px-6 py-2 bg-[#0b3a63] text-white rounded-md text-sm font-medium hover:bg-[#0a2d4f] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Save All Changes
                    </button>
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
                l.classList.remove('bg-[#0b3a63]', 'text-white');
                l.classList.add('text-gray-700', 'hover:bg-gray-100');
            });
            link.classList.remove('text-gray-700', 'hover:bg-gray-100');
            link.classList.add('bg-[#0b3a63]', 'text-white');
            
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

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <button
                    type="button"
                    id="reset-btn"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63]"
                >
                    Reset Changes
                </button>
                <button
                    type="submit"
                    id="save-btn"
                    class="px-6 py-2 bg-[#0b3a63] text-white rounded-md text-sm font-medium hover:bg-[#0a2d4f] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Save All Changes
                </button>
            </div>
        </form>
</div>

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

    // Handle image uploads
    form.querySelectorAll('input[type="file"]').forEach(fileInput => {
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const targetInputId = fileInput.dataset.target;
            const targetInput = document.getElementById(targetInputId);
            const preview = targetInput.parentElement.querySelector('img');

            // Show loading state
            if (preview) {
                preview.style.opacity = '0.5';
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

                alert('✅ Image uploaded successfully!');
            } catch (error) {
                alert('❌ Upload error: ' + error.message);
            } finally {
                if (preview) {
                    preview.style.opacity = '1';
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

