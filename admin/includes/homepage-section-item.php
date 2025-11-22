<?php
/**
 * Homepage Section Item Template
 * Used in homepage builder for draggable sections
 */
$sectionTypes = [
    'hero' => ['icon' => '🎯', 'color' => 'bg-blue-100'],
    'categories' => ['icon' => '📦', 'color' => 'bg-green-100'],
    'products' => ['icon' => '🛍️', 'color' => 'bg-purple-100'],
    'features' => ['icon' => '✨', 'color' => 'bg-yellow-100'],
    'testimonials' => ['icon' => '💬', 'color' => 'bg-pink-100'],
    'newsletter' => ['icon' => '📧', 'color' => 'bg-indigo-100'],
    'cta' => ['icon' => '🚀', 'color' => 'bg-red-100'],
    'custom' => ['icon' => '📝', 'color' => 'bg-gray-100'],
];

$typeInfo = $sectionTypes[$section['section_type']] ?? $sectionTypes['custom'];
?>

<div class="section-item bg-white rounded-lg border-2 border-gray-200 p-4 hover:border-[#0b3a63] hover:shadow-md transition-all cursor-move group"
     data-id="<?php echo e($section['id']); ?>"
     data-order="<?php echo e($section['order_index']); ?>">
    <div class="flex items-start gap-4">
        <!-- Drag Handle -->
        <div class="flex-shrink-0 pt-1 text-gray-400 group-hover:text-[#0b3a63] cursor-move">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
        </div>
        
        <!-- Section Icon & Info -->
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl <?php echo e($typeInfo['color']); ?>">
                    <?php echo $typeInfo['icon']; ?>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">
                        <?php echo e($section['title'] ?? ucfirst($section['section_type'])); ?>
                    </h4>
                    <p class="text-xs text-gray-500 capitalize"><?php echo e(str_replace('_', ' ', $section['section_type'])); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="sr-only peer section-status-toggle" 
                               data-id="<?php echo e($section['id']); ?>"
                               <?php echo $section['status'] === 'ACTIVE' ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#0b3a63] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0b3a63"></div>
                        <span class="ml-3 text-sm text-gray-700 font-medium"><?php echo e($section['status']); ?></span>
                    </label>
                    <button type="button" 
                            class="edit-section-btn text-[#0b3a63] hover:text-[#1a5a8a] font-medium text-sm px-3 py-1 rounded hover:bg-blue-50 transition-colors"
                            data-id="<?php echo e($section['id']); ?>">
                        Edit
                    </button>
                    <button type="button" 
                            class="delete-section-btn text-red-600 hover:text-red-800 font-medium text-sm px-3 py-1 rounded hover:bg-red-50 transition-colors"
                            data-id="<?php echo e($section['id']); ?>">
                        Delete
                    </button>
                </div>
            </div>
            
            <?php if (!empty($section['content'])): ?>
                <div class="ml-13 text-xs text-gray-500">
                    <?php
                    $contentPreview = is_array($section['content']) 
                        ? json_encode($section['content'], JSON_UNESCAPED_UNICODE) 
                        : $section['content'];
                    echo e(substr($contentPreview, 0, 100)) . (strlen($contentPreview) > 100 ? '...' : '');
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

