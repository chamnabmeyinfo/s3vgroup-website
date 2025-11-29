<?php
/**
 * Mobile Bottom Navigation Bar - Safe Version
 * Minimal, safe bottom navigation that won't break the site
 */

// Only output if we're on mobile and all required functions exist
if (!function_exists('base_url') || !function_exists('e')) {
    return; // Exit silently
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentPath, PHP_URL_PATH);

// Simple helper if not exists
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return $protocol . $host . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// Simple active check
function isActiveNav($path, $current) {
    if ($path === '/' && $current === '/') return true;
    if ($path !== '/' && strpos($current, $path) !== false) return true;
    return false;
}

$primaryColor = '#0b3a63'; // Default color

$navItems = [
    ['title' => 'Home', 'url' => base_url('/'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'active' => isActiveNav('/', $currentPath)],
    ['title' => 'Products', 'url' => base_url('products.php'), 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'active' => isActiveNav('products', $currentPath)],
    ['title' => 'Search', 'url' => '#', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'action' => 'search'],
    ['title' => 'Quote', 'url' => base_url('contact.php'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'active' => isActiveNav('contact', $currentPath)],
    ['title' => 'Menu', 'url' => '#', 'icon' => 'M4 6h16M4 12h16M4 18h16', 'action' => 'menu']
];
?>

<nav class="app-bottom-nav mobile-only" id="app-bottom-nav" style="display:none;">
    <?php foreach ($navItems as $item): ?>
        <?php if (isset($item['action']) && $item['action'] === 'menu'): ?>
            <button type="button" onclick="if(typeof toggleMobileMenu==='function')toggleMobileMenu();return false;" class="app-bottom-nav-item <?= $item['active'] ? 'active' : '' ?>" aria-label="<?= e($item['title']) ?>">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?= e($item['icon']) ?>"/></svg>
                <span class="app-bottom-nav-label"><?= e($item['title']) ?></span>
            </button>
        <?php elseif (isset($item['action']) && $item['action'] === 'search'): ?>
            <button type="button" onclick="document.getElementById('product-search')?.focus();return false;" class="app-bottom-nav-item" aria-label="<?= e($item['title']) ?>">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?= e($item['icon']) ?>"/></svg>
                <span class="app-bottom-nav-label"><?= e($item['title']) ?></span>
            </button>
        <?php else: ?>
            <a href="<?= e($item['url']) ?>" class="app-bottom-nav-item <?= $item['active'] ? 'active' : '' ?>" aria-label="<?= e($item['title']) ?>">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?= e($item['icon']) ?>"/></svg>
                <span class="app-bottom-nav-label"><?= e($item['title']) ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<script>
(function(){if(window.innerWidth<=768){var nav=document.getElementById('app-bottom-nav');if(nav)nav.style.display='flex';}})();
</script>

