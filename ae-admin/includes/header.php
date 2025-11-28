<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Ant Elite Admin</title>
    <?php
        $tailwindCssFile = __DIR__ . '/../../ae-includes/css/tailwind.css';
        $tailwindVersion = file_exists($tailwindCssFile) ? filemtime($tailwindCssFile) : time();
    ?>
    <link rel="stylesheet" href="/ae-includes/css/tailwind.css?v=<?php echo $tailwindVersion; ?>">
    <link rel="stylesheet" href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/includes/admin-styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --mac-bg: #ececec;
            --mac-window-bg: #f5f5f7;
            --mac-sidebar: #f6f6f6;
            --mac-border: #d0d0d0;
            --mac-text: #1d1d1f;
            --mac-text-secondary: #6e6e73;
            --mac-blue: #007aff;
            --mac-blue-hover: #0051d5;
            --mac-red: #ff3b30;
            --mac-yellow: #ffcc00;
            --mac-green: #34c759;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: var(--mac-bg);
            color: var(--mac-text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* macOS Window */
        .mac-window {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--mac-window-bg);
            display: flex;
            flex-direction: column;
        }

        /* macOS Title Bar */
        .mac-title-bar {
            height: 40px;
            background: linear-gradient(to bottom, #e8e8e8 0%, #d8d8d8 100%);
            border-bottom: 1px solid #b0b0b0;
            display: flex;
            align-items: center;
            padding: 0 12px;
            -webkit-app-region: drag;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.6) inset;
        }

        .mac-traffic-lights {
            display: flex;
            gap: 8px;
        }

        .mac-traffic-light {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 0.5px solid rgba(0, 0, 0, 0.2);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) inset;
        }

        .mac-traffic-light.red {
            background: var(--mac-red);
        }

        .mac-traffic-light.yellow {
            background: var(--mac-yellow);
        }

        .mac-traffic-light.green {
            background: var(--mac-green);
        }

        .mac-title {
            flex: 1;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            color: #3d3d3d;
            letter-spacing: -0.1px;
        }

        .mac-title-bar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            -webkit-app-region: no-drag;
        }

        .mac-title-bar-link {
            color: var(--mac-text-secondary);
            text-decoration: none;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.15s;
        }

        .mac-title-bar-link:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        /* macOS Toolbar */
        .mac-toolbar {
            height: 44px;
            background: linear-gradient(to bottom, #fafafa 0%, #f0f0f0 100%);
            border-bottom: 1px solid var(--mac-border);
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 16px;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
        }

        .mac-toolbar-brand {
            font-size: 14px;
            font-weight: 600;
            color: var(--mac-text);
            letter-spacing: -0.2px;
        }

        .mac-toolbar-divider {
            width: 1px;
            height: 20px;
            background: var(--mac-border);
        }

        .mac-toolbar-spacer {
            flex: 1;
        }

        /* macOS Sidebar */
        .mac-sidebar {
            width: 220px;
            background: var(--mac-sidebar);
            border-right: 1px solid var(--mac-border);
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 1px 0 0 rgba(255, 255, 255, 0.5) inset;
        }

        .mac-sidebar-section {
            padding: 8px 0;
        }

        .mac-sidebar-section-title {
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--mac-text-secondary);
        }

        .mac-sidebar-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            color: var(--mac-text);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.15s;
            position: relative;
        }

        .mac-sidebar-item:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .mac-sidebar-item.active {
            background: rgba(0, 122, 255, 0.15);
            color: var(--mac-blue);
            font-weight: 500;
        }

        .mac-sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--mac-blue);
        }

        .mac-sidebar-item svg {
            width: 16px;
            height: 16px;
            opacity: 0.8;
        }

        .mac-sidebar-sub-item {
            padding-left: 36px;
            font-size: 12px;
        }

        /* Main Content Area */
        .mac-content-area {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .mac-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: var(--mac-window-bg);
        }

        /* macOS Scrollbar */
        .mac-sidebar::-webkit-scrollbar,
        .mac-content::-webkit-scrollbar {
            width: 16px;
        }

        .mac-sidebar::-webkit-scrollbar-track,
        .mac-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .mac-sidebar::-webkit-scrollbar-thumb,
        .mac-content::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border: 4px solid transparent;
            background-clip: padding-box;
            border-radius: 8px;
        }

        .mac-sidebar::-webkit-scrollbar-thumb:hover,
        .mac-content::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
            background-clip: padding-box;
        }

        /* Toast Container */
        .toast-container {
            position: fixed;
            top: 60px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="mac-window">
        <!-- macOS Title Bar -->
        <div class="mac-title-bar">
            <div class="mac-traffic-lights">
                <div class="mac-traffic-light red"></div>
                <div class="mac-traffic-light yellow"></div>
                <div class="mac-traffic-light green"></div>
            </div>
            <div class="mac-title">Ant Elite Admin</div>
            <div class="mac-title-bar-actions">
                <a href="/" target="_blank" class="mac-title-bar-link" title="Visit Site">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                    </svg>
                </a>
                <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/options.php" class="mac-title-bar-link">Settings</a>
                <span style="color: var(--mac-border);">|</span>
                <span style="color: var(--mac-text-secondary); font-size: 12px;"><?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?></span>
                <form action="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/logout.php" method="POST" style="display: inline;">
                    <button type="submit" style="background: none; border: none; color: var(--mac-text-secondary); cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 12px; transition: background 0.15s;" onmouseover="this.style.background='rgba(0,0,0,0.08)'" onmouseout="this.style.background='none'">
                        Log Out
                    </button>
                </form>
            </div>
        </div>

        <!-- macOS Toolbar -->
        <div class="mac-toolbar">
            <div class="mac-toolbar-brand">Ant Elite</div>
            <div class="mac-toolbar-divider"></div>
            <div class="mac-toolbar-spacer"></div>
        </div>

        <!-- Main Content Area -->
        <div class="mac-content-area">
            <!-- macOS Sidebar -->
            <aside class="mac-sidebar">
                <nav>
                    <!-- Dashboard -->
                    <div class="mac-sidebar-section">
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <!-- Catalog -->
                    <div class="mac-sidebar-section">
                        <div class="mac-sidebar-section-title">Catalog</div>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/products.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Products</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/categories.php" class="mac-sidebar-item mac-sidebar-sub-item <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>Categories</span>
                        </a>
                    </div>

                    <!-- Content -->
                    <div class="mac-sidebar-section">
                        <div class="mac-sidebar-section-title">Content</div>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/pages.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Pages</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/homepage-builder-v2.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'homepage-builder-v2.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <span>Visual Builder</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/team.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Team</span>
                        </a>
                    </div>

                    <!-- Marketing -->
                    <div class="mac-sidebar-section">
                        <div class="mac-sidebar-section-title">Marketing</div>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/sliders.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'sliders.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Sliders</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/testimonials.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span>Testimonials</span>
                        </a>
                    </div>

                    <!-- Settings -->
                    <div class="mac-sidebar-section">
                        <div class="mac-sidebar-section-title">Settings</div>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/options.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'options.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Site Options</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/media-library.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'media-library.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Media</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/plugins.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'plugins.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <span>Plugins</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/database-sync.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'database-sync.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Database Sync</span>
                        </a>
                        <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/optional-features.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'optional-features.php' ? 'active' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            <span>Optional Features</span>
                        </a>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="mac-content">
                <div class="toast-container" id="toast-container"></div>
