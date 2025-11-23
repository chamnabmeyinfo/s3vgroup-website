<?php
// Load site options
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../bootstrap/app.php';
}
if (!function_exists('option')) {
    require_once __DIR__ . '/../bootstrap/app.php';
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
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');
$headerBackground = option('header_background', '#ffffff');
?>
<!DOCTYPE html>
<html lang="<?php echo e(option('site_language', 'en')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo e($siteName); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : ($siteConfig['description'] ?? ''); ?>">
    
    <?php if ($favicon = option('site_favicon', '')): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo e($favicon); ?>">
    <?php endif; ?>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Professional Frontend CSS -->
    <link rel="stylesheet" href="<?php echo asset('includes/css/frontend.css'); ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo asset('includes/css/pages.css'); ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo asset('includes/css/mobile-app.css'); ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo asset('includes/css/categories.css'); ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo asset('includes/css/modern-animations.css'); ?>?v=<?php echo time(); ?>">
    <script src="<?php echo asset('includes/js/category-images.js'); ?>?v=<?php echo time(); ?>" defer></script>
    
    <!-- Mobile Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
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
    <script src="<?php echo asset('includes/js/modern.js'); ?>?v=<?php echo time(); ?>"></script>
    <script src="<?php echo asset('includes/js/modern-animations.js'); ?>?v=<?php echo time(); ?>" defer></script>
    <script src="<?php echo asset('includes/js/animations.js'); ?>?v=<?php echo time(); ?>"></script>
    <script src="<?php echo asset('includes/js/mobile-app.js'); ?>?v=<?php echo time(); ?>" defer></script>
    <script src="<?php echo asset('includes/js/mobile-touch.js'); ?>?v=<?php echo time(); ?>"></script>
    <?php if (option('enable_social_sharing', '1') === '1'): ?>
        <script src="<?php echo asset('includes/js/social-sharing.js'); ?>?v=<?php echo time(); ?>"></script>
    <?php endif; ?>
    
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
<body class="min-h-screen bg-gray-50 mobile-app">
    <!-- Mobile App Header -->
    <?php include __DIR__ . '/widgets/mobile-app-header.php'; ?>
    
    <!-- Desktop Header -->
    <header class="sticky top-0 z-50 w-full border-b backdrop-blur desktop-only" style="background-color: <?php echo e($headerBackground); ?>;">
        <div class="container mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <a href="<?php echo base_url('/'); ?>" class="flex items-center gap-2">
                    <?php if ($siteLogo): ?>
                        <img src="<?php echo e($siteLogo); ?>" alt="<?php echo e($siteName); ?>" class="h-8 w-auto">
                    <?php else: ?>
                        <svg class="h-6 w-6" style="color: <?php echo e($primaryColor); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    <?php endif; ?>
                    <span class="text-xl font-bold" style="color: <?php echo e($primaryColor); ?>;"><?php echo e($siteName); ?></span>
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <?php if (option('enable_search', '1') === '1'): ?>
                        <div class="relative">
                            <input 
                                type="search" 
                                id="product-search"
                                placeholder="Search products..." 
                                class="px-4 py-2 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
                                style="min-width: 220px; focus:ring-color: <?php echo e($primaryColor); ?>;"
                            >
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo base_url('products.php'); ?>" class="text-sm font-semibold text-gray-700 hover:text-primary transition-colors" style="color: var(--text-color); --hover-color: <?php echo e($primaryColor); ?>;">
                        Products
                    </a>
                    <a href="<?php echo base_url('about.php'); ?>" class="text-sm font-semibold text-gray-700 hover:text-primary transition-colors" style="color: var(--text-color); --hover-color: <?php echo e($primaryColor); ?>;">
                        About
                    </a>
                    <a href="<?php echo base_url('team.php'); ?>" class="text-sm font-semibold text-gray-700 hover:text-primary transition-colors" style="color: var(--text-color); --hover-color: <?php echo e($primaryColor); ?>;">
                        Team
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" class="text-sm font-semibold text-white px-5 py-2 rounded-full transition-all hover:shadow-lg hover:scale-105 transform" style="background-color: <?php echo e($primaryColor); ?>;">
                        Get Quote
                    </a>
                    <a href="<?php echo base_url('contact.php'); ?>" class="text-sm font-semibold text-gray-700 hover:text-primary transition-colors" style="color: var(--text-color); --hover-color: <?php echo e($primaryColor); ?>;">
                        Contact
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 bg-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <?php if (option('enable_search', '1') === '1'): ?>
                    <div class="relative mb-4">
                        <input 
                            type="search" 
                            id="mobile-product-search"
                            placeholder="Search products..." 
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="focus:ring-color: <?php echo e($primaryColor); ?>;"
                        >
                    </div>
                <?php endif; ?>
                <nav class="flex flex-col gap-2">
                    <a href="<?php echo base_url('products.php'); ?>" class="px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Products
                    </a>
                    <a href="<?php echo base_url('about.php'); ?>" class="px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        About Us
                    </a>
                    <a href="<?php echo base_url('team.php'); ?>" class="px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Our Team
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" class="px-4 py-3 text-sm font-semibold text-white rounded-lg transition-colors" style="background-color: <?php echo e($primaryColor); ?>;">
                        Get Quote
                    </a>
                    <a href="<?php echo base_url('contact.php'); ?>" class="px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Contact Us
                    </a>
                </nav>
            </div>
        </div>
            </div>
        </div>
    </header>

    <main>
