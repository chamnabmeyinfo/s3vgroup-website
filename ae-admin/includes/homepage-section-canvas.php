<?php
/**
 * Homepage Section Canvas Item
 * Visual representation of a section in the builder
 */

if (!isset($section)) return;

$sectionTypes = [
    'hero' => ['icon' => '🎯', 'color' => '#3b82f6'],
    'heading' => ['icon' => '📝', 'color' => '#8b5cf6'],
    'text' => ['icon' => '📄', 'color' => '#10b981'],
    'categories' => ['icon' => '📦', 'color' => '#f59e0b'],
    'products' => ['icon' => '🛍️', 'color' => '#ef4444'],
    'features' => ['icon' => '✨', 'color' => '#ec4899'],
    'testimonials' => ['icon' => '💬', 'color' => '#06b6d4'],
    'newsletter' => ['icon' => '📧', 'color' => '#6366f1'],
    'cta' => ['icon' => '🚀', 'color' => '#14b8a6'],
    'spacer' => ['icon' => '↕️', 'color' => '#94a3b8'],
    'divider' => ['icon' => '➖', 'color' => '#64748b'],
    'custom' => ['icon' => '⚙️', 'color' => '#475569'],
];

$typeInfo = $sectionTypes[$section['section_type']] ?? $sectionTypes['custom'];
$content = $section['content'] ?? [];
$settings = $section['settings'] ?? [];
$styles = $settings['styles'] ?? [];
$title = $section['title'] ?? ucfirst($section['section_type']);
?>

<div class="canvas-section" 
     data-id="<?php echo e($section['id']); ?>"
     data-type="<?php echo e($section['section_type']); ?>"
     data-order="<?php echo e($section['order_index'] ?? 0); ?>"
     data-status="<?php echo e($section['status'] ?? 'ACTIVE'); ?>"
     data-title="<?php echo e($section['title'] ?? ''); ?>"
     data-content="<?php echo e(json_encode($section['content'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT)); ?>"
     data-settings="<?php echo e(json_encode($section['settings'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT)); ?>"
     style="<?php
        if (!empty($styles['padding'])) echo 'padding: ' . e($styles['padding']) . '; ';
        if (!empty($styles['margin'])) echo 'margin: ' . e($styles['margin']) . '; ';
        if (!empty($styles['background'])) echo 'background: ' . e($styles['background']) . '; ';
        if (!empty($styles['background_color'])) echo 'background-color: ' . e($styles['background_color']) . '; ';
     ?>">
    
    <!-- Section Toolbar -->
    <div class="section-toolbar">
        <span class="drag-handle">☰</span>
        <span class="section-type-icon"><?php echo $typeInfo['icon']; ?></span>
        <span class="section-title"><?php echo e($title); ?></span>
        <button type="button" class="section-toolbar-btn edit-section" title="Edit">
            ✏️
        </button>
        <button type="button" class="section-toolbar-btn duplicate-section" title="Duplicate">
            📋
        </button>
        <button type="button" class="section-toolbar-btn delete-section" title="Delete">
            🗑️
        </button>
    </div>

    <!-- Section Preview Content -->
    <div class="section-preview-content">
        <?php
        switch ($section['section_type']):
            case 'hero':
                ?>
                <div style="padding: 80px 20px; text-align: center; background: linear-gradient(135deg, #0b3a63, #1a5a8a); color: white; min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <div>
                        <h1 style="font-size: 48px; margin-bottom: 20px;"><?php echo e($content['title'] ?? 'Hero Title'); ?></h1>
                        <p style="font-size: 20px; margin-bottom: 30px;"><?php echo e($content['subtitle'] ?? 'Hero subtitle'); ?></p>
                        <button style="padding: 12px 24px; background: white; color: #0b3a63; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                            <?php echo e($content['buttonText'] ?? 'Get Started'); ?>
                        </button>
                    </div>
                </div>
                <?php
                break;
                
            case 'heading':
                ?>
                <div style="padding: 40px 20px; text-align: center;">
                    <h2 style="font-size: 36px; margin-bottom: 10px; color: #333;"><?php echo e($content['title'] ?? 'Section Title'); ?></h2>
                    <p style="font-size: 18px; color: #666;"><?php echo e($content['subtitle'] ?? 'Section subtitle'); ?></p>
                </div>
                <?php
                break;
                
            case 'text':
                ?>
                <div style="padding: 40px 20px;">
                    <?php echo $content['content'] ?? '<p>Add your text content here...</p>'; ?>
                </div>
                <?php
                break;
                
            case 'spacer':
                ?>
                <div style="padding: <?php echo e($content['height'] ?? 60); ?>px 0; background: repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 10px, #fff 10px, #fff 20px); border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd;">
                    <div style="text-align: center; color: #999; font-size: 12px;">
                        Spacer (<?php echo e($content['height'] ?? 60); ?>px)
                    </div>
                </div>
                <?php
                break;
                
            case 'divider':
                $dividerStyle = $content['style'] ?? 'solid';
                $dividerColor = $styles['color'] ?? '#ddd';
                ?>
                <div style="padding: 20px; text-align: center;">
                    <hr style="border: none; border-top: 2px <?php echo e($dividerStyle); ?> <?php echo e($dividerColor); ?>; margin: 0;">
                </div>
                <?php
                break;
                
            default:
                ?>
                <div style="padding: 40px 20px; text-align: center; background: #f9f9f9; border: 2px dashed #ddd; border-radius: 4px;">
                    <div style="font-size: 24px; margin-bottom: 10px;"><?php echo $typeInfo['icon']; ?></div>
                    <div style="font-weight: 600; color: #333; margin-bottom: 5px;"><?php echo e($title); ?></div>
                    <div style="font-size: 12px; color: #999;"><?php echo e(ucfirst(str_replace('_', ' ', $section['section_type']))); ?></div>
                </div>
                <?php
        endswitch;
        ?>
    </div>
</div>

