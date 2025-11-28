<?php
// Load site options
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../bootstrap/app.php';
}
if (!function_exists('option')) {
    require_once __DIR__ . '/../bootstrap/app.php';
}
if (!function_exists('fullImageUrl')) {
    require_once __DIR__ . '/functions.php';
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// Ensure AssetHelper class is loaded (autoloader should handle it, but safety check)
if (!class_exists('App\Support\AssetHelper')) {
    require_once __DIR__ . '/../app/Support/AssetHelper.php';
}

// Get site config fallback
$siteConfigFallback = [
    'name' => 'S3V Group',
    'description' => 'Professional forklift sales, rental, and service in Cambodia',
    'contact' => [
        'email' => '',
        'phone' => '',
        'address' => '',
        'hours' => '',
    ],
];

$siteName = option('site_name', $siteConfig['name'] ?? $siteConfigFallback['name']);
$siteLogo = option('site_logo', '');
$siteLogoUrl = $siteLogo ? fullImageUrl($siteLogo) : '';
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');
$headerBackground = option('header_background', '#ffffff');
$favicon = option('site_favicon', '');
$faviconUrl = $favicon ? fullImageUrl($favicon) : '';
?>
<!DOCTYPE html>
<html lang="<?php echo e(option('site_language', 'en')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo e($siteName); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : ($siteConfig['description'] ?? ''); ?>">
    
    <?php if ($faviconUrl): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo e($faviconUrl); ?>">
    <?php endif; ?>
    
    <!-- Resource Hints for Performance -->
    <link rel="dns-prefetch" href="https://images.unsplash.com">
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    
    <!-- Tailwind CSS - locally compiled for production -->
    <?php
    // Use AssetVersion for proper cache busting (better than time())
    $assetVersion = class_exists('App\Support\AssetVersion') ? \App\Support\AssetVersion::get() : date('Ymd');
    
    // CSS files are in ae-includes/css/ (confirmed to exist)
    ?>
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/tailwind.css'); ?>?v=<?php echo $assetVersion; ?>">
    
    <!-- Professional Frontend CSS - Using version numbers for proper caching -->
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/frontend.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/theme-styles.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/responsive.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/mobile-fixes.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/homepage-design.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/pages.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/mobile-app.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/categories.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/modern-animations.css'); ?>?v=<?php echo $assetVersion; ?>">
    <link rel="stylesheet" href="<?php echo asset('ae-includes/css/modern-frontend.css'); ?>?v=<?php echo $assetVersion; ?>">
    <script src="<?php echo asset('ae-includes/js/category-images.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <?php
    ?>
    
    <!-- Mobile Viewport - Optimized for Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="<?php echo e($primaryColor); ?>">
    
    <!-- Modern Features Script -->
    <script>
        // Set base path for JavaScript (works on both localhost and live)
        window.BASE_PATH = <?php echo json_encode(\App\Support\AssetHelper::basePath()); ?>;
        
        // Make option() function available globally for JS
        window.option = function(key, defaultValue) {
            const options = {
                'enable_dark_mode': <?php echo json_encode(option('enable_dark_mode', '1')); ?>,
                'enable_search': <?php echo json_encode(option('enable_search', '1')); ?>,
                'enable_animations': <?php echo json_encode(option('enable_animations', '1')); ?>,
                'enable_toast_notifications': <?php echo json_encode(option('enable_toast_notifications', '1')); ?>,
            };
            return options[key] !== undefined ? options[key] : defaultValue;
        };
    </script>
    <!-- JavaScript - Using version numbers and defer for performance -->
    <?php
    // JS files are in ae-includes/js/ (confirmed to exist)
    ?>
    <script src="<?php echo asset('ae-includes/js/theme-toggle.js'); ?>?v=<?php echo $assetVersion; ?>"></script>
    <script src="<?php echo asset('ae-includes/js/modern.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <script src="<?php echo asset('ae-includes/js/modern-animations.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <script src="<?php echo asset('ae-includes/js/animations.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <script src="<?php echo asset('ae-includes/js/mobile-app.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <script src="<?php echo asset('ae-includes/js/mobile-touch.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <script src="<?php echo asset('ae-includes/js/modern-frontend.js'); ?>?v=<?php echo $assetVersion; ?>"></script>
    <?php if (option('enable_social_sharing', '1') === '1'): ?>
        <script src="<?php echo asset('ae-includes/js/social-sharing.js'); ?>?v=<?php echo $assetVersion; ?>" defer></script>
    <?php endif; ?>
    <?php
    ?>
    
    <?php if ($customJSHead = option('custom_js_head', '')): ?>
        <script><?php echo $customJSHead; ?></script>
    <?php endif; ?>
    
    <?php require_once __DIR__ . '/helpers.php'; ?>
    
    
    <!-- Design System - Dynamic CSS -->
    <?php require_once __DIR__ . '/design-system.php'; ?>
    
    <!-- Dynamic Site Options Styles -->
    <style>
        /* Additional utility classes */
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .hover\:text-primary:hover { color: var(--primary-color); }
        .hover\:bg-primary:hover { background-color: var(--primary-color); }
        .text-accent { color: var(--accent-color); }
        .bg-accent { background-color: var(--accent-color); }
        .bg-header { background-color: <?php echo e($headerBackground); ?>; }
    </style>
</head>
<body class="min-h-screen bg-white mobile-app">
    <!-- Mobile App Header -->
    <?php include __DIR__ . '/widgets/mobile-app-header.php'; ?>
    
    <!-- Secondary Menu Top Bar (Optional) -->
    <?php 
    if (file_exists(__DIR__ . '/widgets/secondary-menu.php')) {
        if (!function_exists('getDB')) {
            require_once __DIR__ . '/../config/database.php';
        }
        if (class_exists('App\Domain\Menus\MenuRepository')) {
            $db = getDB();
            $repository = new \App\Domain\Menus\MenuRepository($db);
            $menuService = new \App\Application\Services\MenuService($repository);
            $secondaryMenu = $menuService->getMenuByLocation('secondary');
            if ($secondaryMenu) {
                ?>
                <div class="bg-gray-800 text-white py-2 text-sm border-b border-gray-700 theme-secondary-bar">
                    <div class="container mx-auto px-4">
                        <div class="flex items-center justify-between">
                            <?php 
                            $location = 'secondary';
                            include __DIR__ . '/widgets/secondary-menu.php'; 
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }
    ?>
    
    <!-- Modern Header -->
    <header class="modern-header" id="modern-header">
        <div class="modern-header-container">
            <a href="<?php echo base_url('/'); ?>" class="modern-logo">
                <?php if ($siteLogoUrl): ?>
                    <img src="<?php echo e($siteLogoUrl); ?>" alt="<?php echo e($siteName); ?>">
                <?php else: ?>
                    <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                <?php endif; ?>
                <div class="modern-logo-text">
                    <span class="modern-logo-text-top">GLOBAL</span>
                    <span class="modern-logo-text-bottom">INDUSTRIAL SOLUTIONS</span>
                </div>
            </a>

            <nav class="modern-nav">
                <?php 
                // Load Primary Menu
                $location = 'primary';
                $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                if (file_exists(__DIR__ . '/widgets/dynamic-menu.php')) {
                    $db = getDB();
                    $repository = new \App\Domain\Menus\MenuRepository($db);
                    $menuService = new \App\Application\Services\MenuService($repository);
                    $primaryMenu = $menuService->getMenuByLocation('primary');
                    if ($primaryMenu) {
                        $menuItems = $menuService->getMenuWithItems($primaryMenu['id'])['items'] ?? [];
                        foreach ($menuItems as $item) {
                            $isActive = strpos($currentPath, parse_url($item['url'], PHP_URL_PATH)) !== false;
                            ?>
                            <a href="<?php echo e($item['url']); ?>" class="modern-nav-link <?php echo $isActive ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                            <?php
                        }
                    } else {
                        // Fallback menu
                        ?>
                        <a href="<?php echo base_url('/'); ?>" class="modern-nav-link <?php echo $currentPath === '/' ? 'active' : ''; ?>">Home</a>
                        <a href="<?php echo base_url('products.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'products') !== false ? 'active' : ''; ?>">Products</a>
                        <a href="<?php echo base_url('about.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'about') !== false ? 'active' : ''; ?>">About</a>
                        <a href="<?php echo base_url('contact.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'contact') !== false ? 'active' : ''; ?>">Contact</a>
                        <?php
                    }
                } else {
                    // Fallback menu
                    ?>
                    <a href="<?php echo base_url('/'); ?>" class="modern-nav-link <?php echo $currentPath === '/' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo base_url('products.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'products') !== false ? 'active' : ''; ?>">Products</a>
                    <a href="<?php echo base_url('about.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'about') !== false ? 'active' : ''; ?>">About</a>
                    <a href="<?php echo base_url('contact.php'); ?>" class="modern-nav-link <?php echo strpos($currentPath, 'contact') !== false ? 'active' : ''; ?>">Contact</a>
                    <?php
                }
                ?>
            </nav>

            <div class="modern-header-actions">
                <?php if (option('enable_search', '1') === '1'): ?>
                    <div class="modern-search-container">
                        <svg class="modern-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="search" 
                            id="product-search"
                            placeholder="Search products..." 
                            class="modern-search-input"
                        >
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo base_url('quote.php'); ?>" class="modern-cta-button">
                    <span>Get Quote</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                
                <!-- Theme Toggle Button -->
                <button 
                    id="theme-toggle-btn" 
                    onclick="toggleTheme()" 
                    class="modern-mobile-menu-btn"
                    aria-label="Toggle theme"
                    title="Toggle dark/light mode">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Mobile Menu Button -->
                <button class="modern-mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden" style="border-top: 1px solid var(--color-gray-200); background: var(--color-white); box-shadow: var(--shadow-lg);">
            <div style="max-width: 1400px; margin: 0 auto; padding: var(--space-lg);">
                <?php if (option('enable_search', '1') === '1'): ?>
                    <div class="modern-search-container" style="width: 100%; margin-bottom: var(--space-lg);">
                        <svg class="modern-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="search" 
                            id="mobile-product-search"
                            placeholder="Search products..." 
                            class="modern-search-input"
                            style="width: 100%;"
                        >
                    </div>
                <?php endif; ?>
                <nav style="display: flex; flex-direction: column; gap: var(--space-sm);">
                    <?php 
                    // Load Primary Menu for Mobile
                    $location = 'primary';
                    if (file_exists(__DIR__ . '/widgets/dynamic-menu.php')) {
                        $db = getDB();
                        $repository = new \App\Domain\Menus\MenuRepository($db);
                        $menuService = new \App\Application\Services\MenuService($repository);
                        $primaryMenu = $menuService->getMenuByLocation('primary');
                        if ($primaryMenu) {
                            $mobileMenuItems = $menuService->getMenuWithItems($primaryMenu['id'])['items'] ?? [];
                            foreach ($mobileMenuItems as $item) {
                                $isActive = strpos($currentPath, parse_url($item['url'], PHP_URL_PATH)) !== false;
                                ?>
                                <a href="<?php echo e($item['url']); ?>" 
                                   class="modern-nav-link <?php echo $isActive ? 'active' : ''; ?>"
                                   style="padding: var(--space-md); border-radius: var(--radius-md);">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                                <?php
                                if (!empty($item['children'])) {
                                    foreach ($item['children'] as $child) {
                                        ?>
                                        <a href="<?php echo e($child['url']); ?>" 
                                           class="modern-nav-link"
                                           style="padding: var(--space-sm) var(--space-xl); font-size: 0.875rem;">
                                            <?php echo htmlspecialchars($child['title']); ?>
                                        </a>
                                        <?php
                                    }
                                }
                            }
                        } else {
                            // Fallback
                            ?>
                            <a href="<?php echo base_url('products.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Products</a>
                            <a href="<?php echo base_url('about.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">About Us</a>
                            <a href="<?php echo base_url('team.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Our Team</a>
                            <a href="<?php echo base_url('quote.php'); ?>" class="modern-cta-button" style="width: 100%; justify-content: center; margin-top: var(--space-sm);">Get Quote</a>
                            <a href="<?php echo base_url('contact.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Contact Us</a>
                            <?php
                        }
                    } else {
                        // Fallback
                        ?>
                        <a href="<?php echo base_url('products.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Products</a>
                        <a href="<?php echo base_url('about.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">About Us</a>
                        <a href="<?php echo base_url('team.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Our Team</a>
                        <a href="<?php echo base_url('quote.php'); ?>" class="modern-cta-button" style="width: 100%; justify-content: center; margin-top: var(--space-sm);">Get Quote</a>
                        <a href="<?php echo base_url('contact.php'); ?>" class="modern-nav-link" style="padding: var(--space-md);">Contact Us</a>
                        <?php
                    }
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <main>
