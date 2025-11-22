<?php
/**
 * Loading Screen Widget
 * Usage: include __DIR__ . '/widgets/loading-screen.php'; (place before </body>)
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

if (option('enable_loading_animation', '1') === '0') {
    return;
}

$primaryColor = option('primary_color', '#0b3a63');
$siteName = option('site_name', 'S3V Group');
$siteLogo = option('site_logo', '');
?>

<div id="page-loader" class="fixed inset-0 z-[9999] flex items-center justify-center transition-opacity duration-500" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e(option('secondary_color', '#1a5a8a')); ?>);">
    <div class="text-center">
        <?php if ($siteLogo): ?>
            <img src="<?php echo e($siteLogo); ?>" 
                 alt="<?php echo e($siteName); ?> Logo" 
                 class="h-20 w-auto mx-auto mb-6 animate-pulse"
                 style="filter: brightness(0) invert(1);">
        <?php else: ?>
            <div class="modern-spinner mx-auto mb-6" style="border-color: rgba(255, 255, 255, 0.2); border-top-color: white; width: 64px; height: 64px;"></div>
        <?php endif; ?>
        
        <div class="text-white">
            <?php if ($siteName): ?>
                <p class="text-xl md:text-2xl font-bold mb-2 animate-pulse"><?php echo e($siteName); ?></p>
            <?php endif; ?>
            <div class="flex items-center justify-center gap-2 mt-4">
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize loading screen immediately (inline to prevent FOUC)
(function() {
    'use strict';
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.style.opacity = '1';
        loader.style.display = 'flex';
        loader.style.visibility = 'visible';
        loader.classList.add('show');
        
        // Prevent body scroll
        if (document.body) {
            document.body.style.overflow = 'hidden';
            document.body.classList.add('loading');
        }
    }
})();
</script>

