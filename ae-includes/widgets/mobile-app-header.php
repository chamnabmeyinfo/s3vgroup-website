<?php
/**
 * Mobile App Header Widget
 * Only shows on mobile/tablet devices
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}
if (!function_exists('fullImageUrl')) {
    require_once __DIR__ . '/../functions.php';
}

$siteName = option('site_name', 'S3V Group');
$siteLogo = option('site_logo', '');
$siteLogoUrl = $siteLogo ? fullImageUrl($siteLogo) : '';
$primaryColor = option('primary_color', '#0b3a63');
?>

<div class="app-header mobile-only" style="background-color: white;">
    <button class="app-header-button" onclick="document.querySelector('.side-menu')?.classList.toggle('open'); document.querySelector('.side-menu-overlay')?.classList.toggle('active');">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <div class="app-header-title">
        <?php if ($siteLogoUrl): ?>
            <img src="<?php echo e($siteLogoUrl); ?>" alt="<?php echo e($siteName); ?>" style="max-height: 32px; max-width: 180px; width: auto; height: auto; object-fit: contain;">
        <?php else: ?>
            <?php echo e($siteName); ?>
        <?php endif; ?>
    </div>
    <div style="width: 40px;"></div> <!-- Spacer for centering -->
</div>

