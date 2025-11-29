<?php
/**
 * Mobile Bottom Navigation Bar
 * App-like bottom navigation menu for mobile devices
 */

// Wrap everything in try-catch to prevent fatal errors
try {
// Ensure helper functions exist with proper error handling
if (!function_exists('e')) {
    function e($string) {
        if ($string === null || $string === false) {
            return '';
        }
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        if (defined('BASE_URL')) {
            $base = rtrim(BASE_URL, '/');
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $base = $protocol . $host . ($scriptDir !== '/' ? $scriptDir : '');
        }
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentPath, PHP_URL_PATH);

// Get primary color for active state with fallback - don't call option() if it might fail
$primaryColor = '#0b3a63';
if (function_exists('option')) {
    try {
        $primaryColor = option('primary_color', '#0b3a63');
        if (empty($primaryColor)) {
            $primaryColor = '#0b3a63';
        }
    } catch (Exception $e) {
        // Silently use default
        error_log('Bottom nav: Could not get primary color: ' . $e->getMessage());
    } catch (Error $e) {
        // Silently use default
        error_log('Bottom nav: Could not get primary color: ' . $e->getMessage());
    }
}

// Determine active page
function isActive($path, $currentPath) {
    if ($path === '/' && $currentPath === '/') return true;
    if ($path !== '/' && strpos($currentPath, $path) !== false) return true;
    return false;
}

// Check which pages exist - with error handling
try {
    $rootDir = dirname(dirname(__DIR__));
    $pagesExist = [
        'about' => @file_exists($rootDir . '/about.php'),
        'testimonials' => @file_exists($rootDir . '/testimonials.php'),
        'quote' => @file_exists($rootDir . '/quote.php'),
        'contact' => @file_exists($rootDir . '/contact.php')
    ];
} catch (Exception $e) {
    $pagesExist = [
        'about' => false,
        'testimonials' => false,
        'quote' => false,
        'contact' => true // Default to true for contact
    ];
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
        'title' => 'Search',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'url' => '#',
        'active' => false,
        'action' => 'toggleSearch'
    ],
    [
        'title' => 'Quote',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'url' => $pagesExist['quote'] ? base_url('quote.php') : base_url('contact.php'),
        'active' => isActive('quote.php', $currentPath)
    ],
    [
        'title' => 'More',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>',
        'url' => '#',
        'active' => false,
        'action' => 'toggleMobileMenu'
    ]
];
// End try block content - output HTML outside try-catch
?>
}<?php
// Close try block before HTML output
} catch (Throwable $e) {
    // Silently fail - log error but don't break the page
    error_log('Bottom nav widget error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    // Exit if there's an error - don't output anything
    return;
}
?>
<!-- Mobile Bottom Navigation -->
<nav class="app-bottom-nav mobile-only" id="app-bottom-nav">
    <?php foreach ($navItems as $item): ?>
        <?php if (isset($item['action'])): ?>
            <?php if ($item['action'] === 'toggleMobileMenu'): ?>
                <button 
                    type="button"
                    onclick="if (typeof toggleMobileMenu === 'function') { toggleMobileMenu(); } return false;" 
                    class="app-bottom-nav-item <?php echo $item['active'] ? 'active' : ''; ?>"
                    aria-label="<?php echo e($item['title']); ?>"
                >
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <?php echo $item['icon']; ?>
                    </svg>
                    <span class="app-bottom-nav-label"><?php echo e($item['title']); ?></span>
                </button>
            <?php elseif ($item['action'] === 'toggleSearch'): ?>
                <button 
                    type="button"
                    onclick="document.getElementById('product-search')?.focus(); document.querySelector('.modern-search-container')?.scrollIntoView({behavior: 'smooth', block: 'center'}); return false;" 
                    class="app-bottom-nav-item <?php echo $item['active'] ? 'active' : ''; ?>"
                    aria-label="<?php echo e($item['title']); ?>"
                >
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <?php echo $item['icon']; ?>
                    </svg>
                    <span class="app-bottom-nav-label"><?php echo e($item['title']); ?></span>
                </button>
            <?php endif; ?>
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
    const menuButtons = document.querySelectorAll('.app-bottom-nav-item[type="button"]');
    menuButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const onclick = this.getAttribute('onclick');
            if (onclick && onclick.includes('toggleMobileMenu')) {
                if (typeof toggleMobileMenu === 'function') {
                    toggleMobileMenu();
                }
            }
        });
    });
    
    // Ensure all nav items are visible
    const allNavItems = document.querySelectorAll('.app-bottom-nav-item');
    console.log('Total nav items:', allNavItems.length);
    allNavItems.forEach((item, index) => {
        item.style.display = 'flex';
        item.style.visibility = 'visible';
        item.style.opacity = '1';
        item.style.flex = '1';
        item.style.minWidth = '0';
        console.log(`Nav item ${index}:`, item.textContent.trim());
    });
    
    // Ensure container is visible
    const bottomNav = document.getElementById('app-bottom-nav');
    if (bottomNav) {
        bottomNav.style.display = 'flex';
        bottomNav.style.visibility = 'visible';
    }
});
</script>

<?php
} catch (Throwable $e) {
    // Silently fail - log error but don't break the page
    error_log('Bottom nav widget error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    // Output nothing if there's an error
}
?>
