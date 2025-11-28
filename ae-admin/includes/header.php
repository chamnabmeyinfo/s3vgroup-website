<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin Panel</title>
    <?php
        $tailwindCssFile = __DIR__ . '/../../ae-includes/css/tailwind.css';
        $tailwindVersion = file_exists($tailwindCssFile) ? filemtime($tailwindCssFile) : time();
    ?>
    <link rel="stylesheet" href="/ae-includes/css/tailwind.css?v=<?php echo $tailwindVersion; ?>">
    <?php
    $adminDir = (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin';
    $adminStylesPath = '/' . $adminDir . '/includes/admin-styles.css';
    $adminStylesVersion = file_exists(__DIR__ . '/admin-styles.css') ? filemtime(__DIR__ . '/admin-styles.css') : time();
    ?>
    <link rel="stylesheet" href="<?php echo $adminStylesPath; ?>?v=<?php echo $adminStylesVersion; ?>">
    <?php
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Load theme system with error handling
    try {
        require_once __DIR__ . '/theme-loader.php';
        
        // Get database connection
        $db = null;
        try {
            if (file_exists(__DIR__ . '/../../config/database.php')) {
                require_once __DIR__ . '/../../config/database.php';
                $db = getDB();
            }
        } catch (\Throwable $e) {
            error_log('Database connection error in header: ' . $e->getMessage());
        }
        
        // Get active theme first
        $activeTheme = ThemeLoader::getActiveTheme($db);
        
        // Generate and output CSS variables
        echo ThemeLoader::generateCSSVariables($activeTheme);
        
        // Get theme slug for body attribute
        $themeSlug = ThemeLoader::getThemeSlug($activeTheme);
    } catch (\Throwable $e) {
        // Fallback: output default CSS variables if theme loading fails
        error_log('Theme loading error in header: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        ?>
        <style id="theme-variables">
        :root {
            --theme-bg: #FAFBFC;
            --theme-surface: #FFFFFF;
            --theme-primary: #2563EB;
            --theme-primary-text: #FFFFFF;
            --theme-text: #1F2937;
            --theme-text-muted: #6B7280;
            --theme-border: #E5E7EB;
            --theme-error: #DC2626;
            --theme-success: #059669;
            --theme-warning: #D97706;
            --theme-accent: #7C3AED;
            --theme-secondary: #10B981;
            --theme-tertiary: #F59E0B;
            --theme-primary-rgb: 37, 99, 235;
            --theme-success-rgb: 5, 150, 105;
            --theme-error-rgb: 220, 38, 38;
            --theme-warning-rgb: 217, 119, 6;
            --theme-font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --theme-body-size: 15px;
            --theme-line-height: 1.6;
            --theme-heading-scale: 1.25;
            --theme-font-weight-normal: 400;
            --theme-font-weight-medium: 500;
            --theme-font-weight-semibold: 600;
            --theme-font-weight-bold: 700;
            --theme-letter-spacing: normal;
            --theme-radius-sm: 6px;
            --theme-radius-md: 10px;
            --theme-radius-lg: 16px;
            --theme-radius-pill: 9999px;
            --theme-shadow-card: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --theme-shadow-elevated: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --theme-shadow-subtle: 0 1px 2px rgba(0,0,0,0.05);
            --theme-shadow-button: 0 1px 3px rgba(0,0,0,0.1);
            --theme-shadow-button-hover: 0 2px 6px rgba(0,0,0,0.15);
        }
        </style>
        <?php
        $themeSlug = 'ant-elite-default';
    }
    ?>
</head>
<body data-theme="<?php echo htmlspecialchars($themeSlug); ?>">
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/" class="admin-sidebar-brand">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="sidebar-brand-text">Admin Panel</span>
                </a>
                <button class="sidebar-toggle-btn" id="sidebar-toggle" title="Toggle Sidebar">
                    <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
            <nav class="admin-sidebar-nav">
                <?php
                $adminPath = (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin';
                $currentPage = basename($_SERVER['PHP_SELF']);
                ?>
                
                <!-- Dashboard -->
                <div class="admin-nav-section" data-section="dashboard">
                    <a href="/<?php echo $adminPath; ?>/" class="admin-nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="dashboard">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Catalog Management -->
                <div class="admin-nav-section collapsible-section" data-section="catalog">
                    <div class="admin-nav-section-title collapsible-header" data-target="catalog">
                        <span>Catalog Management</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="catalog">
                    <a href="/<?php echo $adminPath; ?>/products.php" class="admin-nav-item <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="products">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Products</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/categories.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="categories">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Categories</span>
                    </a>
                    </div>
                </div>

                <!-- Content Management -->
                <div class="admin-nav-section collapsible-section" data-section="content">
                    <div class="admin-nav-section-title collapsible-header" data-target="content">
                        <span>Content Management</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="content">
                    <a href="/<?php echo $adminPath; ?>/pages.php" class="admin-nav-item <?php echo $currentPage === 'pages.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="pages">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Pages</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/homepage-builder-v2.php" class="admin-nav-item <?php echo $currentPage === 'homepage-builder-v2.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="homepage-builder">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span>Homepage Builder</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/team.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'team.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="team">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Team</span>
                    </a>
                    <?php if (file_exists(__DIR__ . '/../faqs.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/faqs.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'faqs.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="faqs">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>FAQs</span>
                    </a>
                    <?php endif; ?>
                    <?php if (file_exists(__DIR__ . '/../company-story.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/company-story.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'company-story.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="company-story">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <span>Company Story</span>
                    </a>
                    <?php endif; ?>
                    <?php if (file_exists(__DIR__ . '/../ceo-message.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/ceo-message.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'ceo-message.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="ceo-message">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>CEO Message</span>
                    </a>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Marketing & Engagement -->
                <div class="admin-nav-section collapsible-section" data-section="marketing">
                    <div class="admin-nav-section-title collapsible-header" data-target="marketing">
                        <span>Marketing & Engagement</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="marketing">
                    <a href="/<?php echo $adminPath; ?>/sliders.php" class="admin-nav-item <?php echo $currentPage === 'sliders.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="sliders">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Sliders</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/testimonials.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'testimonials.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="testimonials">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span>Testimonials</span>
                    </a>
                    <?php if (file_exists(__DIR__ . '/../reviews.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/reviews.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'reviews.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="reviews">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span>Reviews</span>
                    </a>
                    <?php endif; ?>
                    <?php if (file_exists(__DIR__ . '/../newsletter.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/newsletter.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'newsletter.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="newsletter">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Newsletter</span>
                    </a>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Customer Relations -->
                <?php if (file_exists(__DIR__ . '/../quotes.php')): ?>
                <div class="admin-nav-section collapsible-section" data-section="customer">
                    <div class="admin-nav-section-title collapsible-header" data-target="customer">
                        <span>Customer Relations</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="customer">
                    <a href="/<?php echo $adminPath; ?>/quotes.php" class="admin-nav-item <?php echo $currentPage === 'quotes.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="quotes">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Quote Requests</span>
                    </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Media & Assets -->
                <div class="admin-nav-section collapsible-section" data-section="media">
                    <div class="admin-nav-section-title collapsible-header" data-target="media">
                        <span>Media & Assets</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="media">
                    <a href="/<?php echo $adminPath; ?>/media-library.php" class="admin-nav-item <?php echo $currentPage === 'media-library.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="media">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Media Library</span>
                    </a>
                    </div>
                </div>

                <!-- Design & Appearance -->
                <div class="admin-nav-section collapsible-section" data-section="design">
                    <div class="admin-nav-section-title collapsible-header" data-target="design">
                        <span>Design & Appearance</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="design">
                    <a href="/<?php echo $adminPath; ?>/backend-appearance.php" class="admin-nav-item <?php echo $currentPage === 'backend-appearance.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="themes">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span>Backend Themes</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/menus.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'menus.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="menus">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <span>Menus</span>
                    </a>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="admin-nav-section collapsible-section" data-section="settings">
                    <div class="admin-nav-section-title collapsible-header" data-target="settings">
                        <span>System Settings</span>
                        <svg class="section-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="collapsible-content" data-content="settings">
                    <a href="/<?php echo $adminPath; ?>/options.php" class="admin-nav-item <?php echo $currentPage === 'options.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="options">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Site Options</span>
                    </a>
                    <?php if (file_exists(__DIR__ . '/../seo-tools.php')): ?>
                    <a href="/<?php echo $adminPath; ?>/seo-tools.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'seo-tools.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="seo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>SEO Tools</span>
                    </a>
                    <?php endif; ?>
                    <a href="/<?php echo $adminPath; ?>/plugins.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'plugins.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="plugins">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span>Plugins</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/optional-features.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'optional-features.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="features">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>Optional Features</span>
                    </a>
                    <a href="/<?php echo $adminPath; ?>/database-sync.php" class="admin-nav-item admin-nav-sub-item <?php echo $currentPage === 'database-sync.php' ? 'active' : ''; ?>" draggable="true" data-menu-item="database">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Database Sync</span>
                    </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="mobile-menu-btn" onclick="document.querySelector('.admin-sidebar').classList.toggle('open')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <button class="quick-search-btn" id="quick-search-btn" title="Quick Search (Ctrl+K)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="quick-search-hint">Press <kbd>Ctrl</kbd>+<kbd>K</kbd></span>
                </button>
            </div>
            <div class="admin-topbar-actions">
                <button class="admin-topbar-link focus-mode-btn" id="focus-mode-btn" title="Focus Mode (Ctrl+F)" data-tooltip="Focus Mode">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
                <button class="admin-topbar-link dark-mode-toggle" id="dark-mode-toggle" title="Toggle Dark Mode">
                    <svg class="w-5 h-5 dark-mode-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg class="w-5 h-5 light-mode-icon" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <button class="admin-topbar-link shortcuts-help-btn" id="shortcuts-help-btn" title="Keyboard Shortcuts (?)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
                <button class="admin-topbar-link sticky-notes-btn" id="sticky-notes-btn" title="Sticky Notes">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </button>
                <div class="live-clock" id="live-clock" title="Current Time">
                    <span class="clock-time" id="clock-time">--:--</span>
                    <span class="clock-date" id="clock-date">--</span>
                </div>
                <div class="activity-indicator" id="activity-indicator" title="System Status">
                    <div class="activity-dot"></div>
                    <span class="activity-text">Online</span>
                </div>
                <button class="admin-topbar-link notification-bell" id="notification-bell" title="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="notification-badge" id="notification-badge">0</span>
                </button>
                <button class="admin-topbar-link quick-actions-btn" id="quick-actions-btn" title="Quick Actions">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </button>
                <a href="/" target="_blank" class="admin-topbar-link" title="Visit Site">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span class="hidden md:inline">Visit Site</span>
                </a>
                <div class="admin-user-menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span><?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?></span>
                </div>
                <form action="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="admin-topbar-link text-red-600 hover:text-red-700 hover:bg-red-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden md:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Command Palette / Quick Search Modal -->
        <div class="command-palette" id="command-palette">
            <div class="command-palette-overlay"></div>
            <div class="command-palette-container">
                <div class="command-palette-header">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" class="command-palette-input" id="command-palette-input" placeholder="Search pages, actions, or type a command...">
                    <button class="command-palette-close" id="command-palette-close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="command-palette-results" id="command-palette-results">
                    <!-- Results will be populated here -->
                </div>
                <div class="command-palette-footer">
                    <div class="command-palette-hints">
                        <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                        <span><kbd>Enter</kbd> Select</span>
                        <span><kbd>Esc</kbd> Close</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Menu -->
        <div class="quick-actions-menu" id="quick-actions-menu">
            <div class="quick-actions-item" data-action="new-product">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Product</span>
            </div>
            <div class="quick-actions-item" data-action="new-page">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Page</span>
            </div>
            <div class="quick-actions-item" data-action="media">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Media Library</span>
            </div>
            <div class="quick-actions-item" data-action="settings">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Settings</span>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav" id="breadcrumb-nav" aria-label="Breadcrumb">
            <!-- Breadcrumbs will be populated here -->
        </nav>

        <!-- Quick Actions Toolbar -->
        <div class="quick-actions-toolbar" id="quick-actions-toolbar">
            <div class="toolbar-actions" id="toolbar-actions">
                <!-- Contextual actions will appear here -->
            </div>
        </div>

        <!-- Main Content -->
        <main class="admin-content-wrapper" id="main-content">
            <div class="toast-container" id="toast-container"></div>
            <div class="particle-container" id="particle-container"></div>
            <div class="confetti-container" id="confetti-container"></div>
            <div class="context-menu" id="context-menu"></div>
            <div class="auto-save-indicator" id="auto-save-indicator">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Saved</span>
            </div>
            <div class="performance-monitor" id="performance-monitor" title="Performance Info">
                <span class="perf-load-time" id="perf-load-time">--</span>
            </div>
            <div class="recent-pages-panel" id="recent-pages-panel">
                <div class="recent-pages-header">
                    <h3>Recent Pages</h3>
                    <button class="recent-pages-close" id="recent-pages-close">×</button>
                </div>
                <div class="recent-pages-list" id="recent-pages-list"></div>
            </div>
            <div class="keyboard-shortcuts-overlay" id="keyboard-shortcuts-overlay">
                <div class="shortcuts-modal">
                    <div class="shortcuts-header">
                        <h2>Keyboard Shortcuts</h2>
                        <button class="shortcuts-close" id="shortcuts-close">×</button>
                    </div>
                    <div class="shortcuts-content" id="shortcuts-content"></div>
                </div>
            </div>
            <div class="sidebar-resizer" id="sidebar-resizer"></div>
            <div class="notification-center" id="notification-center">
                <div class="notification-center-header">
                    <h3>Notifications</h3>
                    <button class="notification-center-close" id="notification-center-close">×</button>
                </div>
                <div class="notification-center-list" id="notification-center-list">
                    <div class="notification-empty">No new notifications</div>
                </div>
            </div>
            <div class="quick-stats-bar" id="quick-stats-bar">
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📊</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-label">Products</div>
                        <div class="quick-stat-value" id="stat-products">-</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📄</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-label">Pages</div>
                        <div class="quick-stat-value" id="stat-pages">-</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📁</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-label">Media</div>
                        <div class="quick-stat-value" id="stat-media">-</div>
                    </div>
                </div>
            </div>
            <div class="tab-bar" id="tab-bar">
                <div class="tab-list" id="tab-list"></div>
                <button class="tab-new" id="tab-new" title="New Tab">+</button>
            </div>
            <div class="floating-action-btn" id="floating-action-btn">
                <svg class="fab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <div class="fab-menu" id="fab-menu">
                    <button class="fab-item" data-action="new-product" title="New Product">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>New Product</span>
                    </button>
                    <button class="fab-item" data-action="new-page" title="New Page">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>New Page</span>
                    </button>
                    <button class="fab-item" data-action="media" title="Media Library">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Media</span>
                    </button>
                </div>
            </div>
            <div class="split-screen-container" id="split-screen-container">
                <div class="split-screen-left" id="split-screen-left"></div>
                <div class="split-screen-divider" id="split-screen-divider"></div>
                <div class="split-screen-right" id="split-screen-right"></div>
            </div>
            <div class="sticky-notes-panel" id="sticky-notes-panel">
                <div class="sticky-notes-header">
                    <h3>Sticky Notes</h3>
                    <button class="sticky-notes-close" id="sticky-notes-close">×</button>
                </div>
                <div class="sticky-notes-content" id="sticky-notes-content">
                    <button class="add-note-btn" id="add-note-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Note
                    </button>
                </div>
            </div>
            <div class="activity-feed-panel" id="activity-feed-panel">
                <div class="activity-feed-header">
                    <h3>Activity Feed</h3>
                    <button class="activity-feed-close" id="activity-feed-close">×</button>
                </div>
                <div class="activity-feed-content" id="activity-feed-content">
                    <!-- Activity items will appear here -->
                </div>
            </div>
