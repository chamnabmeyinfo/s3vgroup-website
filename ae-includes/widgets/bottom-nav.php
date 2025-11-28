<?php
/**
 * Mobile Bottom Navigation Bar
 * App-like bottom navigation menu for mobile devices
 */

if (!function_exists('base_url')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentPath, PHP_URL_PATH);

// Get primary color for active state
$primaryColor = option('primary_color', '#0b3a63');

// Determine active page
function isActive($path, $currentPath) {
    if ($path === '/' && $currentPath === '/') return true;
    if ($path !== '/' && strpos($currentPath, $path) !== false) return true;
    return false;
}

$navItems = [
    [
        'title' => 'Home',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'url' => base_url('/'),
        'active' => isActive('/', $currentPath)
    ],
    [
        'title' => 'Products',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'url' => base_url('products.php'),
        'active' => isActive('products.php', $currentPath) || isActive('product.php', $currentPath)
    ],
    [
        'title' => 'Categories',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'url' => base_url('products.php'),
        'active' => false
    ],
    [
        'title' => 'Contact',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'url' => base_url('contact.php'),
        'active' => isActive('contact.php', $currentPath)
    ],
    [
        'title' => 'Menu',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>',
        'url' => '#',
        'active' => false,
        'action' => 'toggleMobileMenu'
    ]
];
?>

<!-- Mobile Bottom Navigation -->
<nav class="app-bottom-nav mobile-only" id="app-bottom-nav">
    <?php foreach ($navItems as $item): ?>
        <?php if (isset($item['action']) && $item['action'] === 'toggleMobileMenu'): ?>
            <button 
                onclick="if (typeof toggleMobileMenu === 'function') toggleMobileMenu();" 
                class="app-bottom-nav-item <?php echo $item['active'] ? 'active' : ''; ?>"
                aria-label="<?php echo e($item['title']); ?>"
            >
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <?php echo $item['icon']; ?>
                </svg>
                <span class="app-bottom-nav-label"><?php echo e($item['title']); ?></span>
            </button>
        <?php else: ?>
            <a 
                href="<?php echo e($item['url']); ?>" 
                class="app-bottom-nav-item <?php echo $item['active'] ? 'active' : ''; ?>"
                aria-label="<?php echo e($item['title']); ?>"
            >
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <?php echo $item['icon']; ?>
                </svg>
                <span class="app-bottom-nav-label"><?php echo e($item['title']); ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<script>
// Highlight active bottom nav item on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.app-bottom-nav-item');
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && href !== '#' && currentPath.includes(href.replace(/^\//, '').replace('.php', ''))) {
            item.classList.add('active');
        }
    });
    
    // Handle menu button click
    const menuButton = document.querySelector('.app-bottom-nav-item[onclick]');
    if (menuButton) {
        menuButton.addEventListener('click', function(e) {
            if (typeof toggleMobileMenu === 'function') {
                toggleMobileMenu();
            }
        });
    }
});
</script>

