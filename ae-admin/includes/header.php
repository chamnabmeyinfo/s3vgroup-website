<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Ant Elite Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/includes/admin-styles.css">
    <style>
        /* macOS-inspired Modern Design */
        :root {
            --mac-bg: #f5f5f7;
            --mac-surface: #ffffff;
            --mac-border: #e5e5e7;
            --mac-text: #1d1d1f;
            --mac-text-secondary: #86868b;
            --mac-accent: #007aff;
            --mac-accent-hover: #0051d5;
            --mac-success: #34c759;
            --mac-warning: #ff9500;
            --mac-error: #ff3b30;
            --mac-sidebar: #fafafa;
            --mac-shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --mac-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --mac-shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Helvetica, Arial, sans-serif;
            background: var(--mac-bg);
            color: var(--mac-text);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* macOS-like Top Bar */
        .mac-top-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 0.5px solid var(--mac-border);
            height: 44px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .mac-top-bar-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mac-top-bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mac-top-bar a {
            color: var(--mac-text);
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
            font-size: 13px;
        }

        .mac-top-bar a:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* macOS-like Sidebar */
        .mac-sidebar {
            background: var(--mac-sidebar);
            border-right: 0.5px solid var(--mac-border);
            width: 200px;
            position: fixed;
            left: 0;
            top: 44px;
            bottom: 0;
            overflow-y: auto;
            z-index: 100;
            padding: 12px 8px;
        }

        .mac-sidebar-logo {
            padding: 12px 16px;
            margin-bottom: 8px;
        }

        .mac-sidebar-logo h1 {
            font-size: 18px;
            font-weight: 600;
            color: var(--mac-text);
            margin: 0;
            letter-spacing: -0.3px;
        }

        .mac-sidebar-section {
            margin-bottom: 24px;
        }

        .mac-sidebar-section-title {
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--mac-text-secondary);
            margin-bottom: 4px;
        }

        .mac-sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: var(--mac-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            border-radius: 8px;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .mac-sidebar-item:hover {
            background: rgba(0, 0, 0, 0.04);
        }

        .mac-sidebar-item.active {
            background: rgba(0, 122, 255, 0.1);
            color: var(--mac-accent);
            font-weight: 500;
        }

        .mac-sidebar-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .mac-sidebar-item.active svg {
            opacity: 1;
        }

        /* Main Content */
        .mac-content {
            margin-left: 200px;
            margin-top: 44px;
            padding: 32px;
            min-height: calc(100vh - 44px);
            max-width: 1400px;
        }

        /* macOS-like Cards */
        .mac-card {
            background: var(--mac-surface);
            border: 0.5px solid var(--mac-border);
            border-radius: 12px;
            box-shadow: var(--mac-shadow-sm);
            padding: 24px;
            margin-bottom: 24px;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .mac-card:hover {
            box-shadow: var(--mac-shadow);
        }

        .mac-card-header {
            border-bottom: 0.5px solid var(--mac-border);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .mac-card-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--mac-text);
            margin: 0;
            letter-spacing: -0.3px;
        }

        /* macOS-like Buttons */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
            letter-spacing: -0.1px;
        }

        .button-primary {
            background: var(--mac-accent);
            color: #fff;
            box-shadow: 0 1px 3px rgba(0, 122, 255, 0.3);
        }

        .button-primary:hover {
            background: var(--mac-accent-hover);
            box-shadow: 0 2px 6px rgba(0, 122, 255, 0.4);
            transform: translateY(-1px);
        }

        .button-secondary {
            background: var(--mac-surface);
            color: var(--mac-text);
            border: 0.5px solid var(--mac-border);
        }

        .button-secondary:hover {
            background: rgba(0, 0, 0, 0.04);
        }

        .button-link {
            background: transparent;
            color: var(--mac-accent);
            padding: 8px 0;
        }

        .button-link:hover {
            color: var(--mac-accent-hover);
        }

        /* macOS-like Tables */
        .mac-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--mac-surface);
            border: 0.5px solid var(--mac-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .mac-table thead th {
            background: var(--mac-sidebar);
            border-bottom: 0.5px solid var(--mac-border);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--mac-text);
            letter-spacing: -0.1px;
        }

        .mac-table tbody td {
            padding: 14px 16px;
            border-bottom: 0.5px solid var(--mac-border);
            font-size: 14px;
            color: var(--mac-text);
        }

        .mac-table tbody tr:last-child td {
            border-bottom: none;
        }

        .mac-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        /* macOS-like Forms */
        .mac-form-input,
        .mac-form-textarea,
        .mac-form-select {
            width: 100%;
            max-width: 400px;
            padding: 10px 14px;
            border: 0.5px solid var(--mac-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--mac-surface);
            color: var(--mac-text);
            transition: all 0.2s;
        }

        .mac-form-input:focus,
        .mac-form-textarea:focus,
        .mac-form-select:focus {
            border-color: var(--mac-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }

        .mac-form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--mac-text);
            margin-bottom: 8px;
            letter-spacing: -0.1px;
        }

        /* macOS-like Badges */
        .mac-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            letter-spacing: -0.1px;
        }

        .mac-badge-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--mac-success);
        }

        .mac-badge-warning {
            background: rgba(255, 149, 0, 0.1);
            color: var(--mac-warning);
        }

        .mac-badge-error {
            background: rgba(255, 59, 48, 0.1);
            color: var(--mac-error);
        }

        .mac-badge-info {
            background: rgba(0, 122, 255, 0.1);
            color: var(--mac-accent);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 60px;
            right: 24px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            padding: 14px 18px;
            border-radius: 10px;
            box-shadow: var(--mac-shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 320px;
            animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: var(--mac-surface);
            border: 0.5px solid var(--mac-border);
            backdrop-filter: blur(20px);
        }

        .toast-success {
            border-left: 3px solid var(--mac-success);
        }

        .toast-error {
            border-left: 3px solid var(--mac-error);
        }

        .toast-warning {
            border-left: 3px solid var(--mac-warning);
        }

        .toast-info {
            border-left: 3px solid var(--mac-accent);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Stats Cards */
        .mac-stat-card {
            background: var(--mac-surface);
            border: 0.5px solid var(--mac-border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--mac-shadow-sm);
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .mac-stat-card:hover {
            box-shadow: var(--mac-shadow);
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .mac-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .mac-sidebar.open {
                transform: translateX(0);
            }

            .mac-content {
                margin-left: 0;
                padding: 20px;
            }

            .mac-top-bar {
                padding: 0 16px;
            }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Better focus states */
        *:focus {
            outline: 2px solid var(--mac-accent);
            outline-offset: 2px;
        }

        /* Scrollbar styling */
        .mac-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .mac-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .mac-sidebar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .mac-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="mac-admin">
    <!-- macOS-like Top Bar -->
    <div class="mac-top-bar">
        <div class="mac-top-bar-left">
            <a href="/" target="_blank" title="Visit Site">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                </svg>
            </a>
            <span style="color: var(--mac-text-secondary);">Ant Elite</span>
            <span style="color: var(--mac-border);">|</span>
            <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/" title="Dashboard">
                <?php echo e($pageTitle ?? 'Dashboard'); ?>
            </a>
        </div>
        <div class="mac-top-bar-right">
            <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/options.php" title="Settings">
                Settings
            </a>
            <span style="color: var(--mac-border);">|</span>
            <span style="color: var(--mac-text-secondary);"><?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?></span>
            <form action="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/logout.php" method="POST" style="display: inline;">
                <button type="submit" style="background: none; border: none; color: var(--mac-text); cursor: pointer; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='none'" title="Log Out">
                    Log Out
                </button>
            </form>
        </div>
    </div>

    <div class="flex">
        <!-- macOS-like Sidebar -->
        <aside class="mac-sidebar" id="mac-sidebar">
            <div class="mac-sidebar-logo">
                <h1>Ant Elite</h1>
            </div>
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
                    <a href="/<?php echo (is_dir(__DIR__ . '/../../ae-admin')) ? 'ae-admin' : 'wp-admin'; ?>/categories.php" class="mac-sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
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
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="mac-content">
            <div class="toast-container" id="toast-container"></div>
