<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo $siteConfig['name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/admin/includes/admin-styles.css">
    <style>
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .toast {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Mobile menu animation */
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        .mobile-menu-open {
            animation: slideInLeft 0.3s ease-out;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .responsive-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .responsive-table thead,
            .responsive-table tbody,
            .responsive-table tr,
            .responsive-table td {
                display: block;
            }
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                margin-bottom: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                padding: 1rem;
                background: white;
            }
            .responsive-table td {
                border: none;
                padding: 0.5rem 0;
                text-align: left !important;
            }
            .responsive-table td:before {
                content: attr(data-label) ": ";
                font-weight: 600;
                color: #6b7280;
            }
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Better focus states */
        input:focus, select:focus, textarea:focus, button:focus {
            outline: 2px solid #0b3a63;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 z-40 bg-black/50 hidden md:hidden"></div>
    
    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Mobile Header -->
        <header class="md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <p class="text-xs text-gray-500">Admin</p>
                    <h2 class="text-sm font-semibold text-[#0b3a63]">S3vgroup</h2>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                <?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?>
            </div>
        </header>

        <!-- Sidebar -->
        <aside id="sidebar" class="hidden md:flex w-64 flex-col border-r border-gray-200 bg-white sticky top-0 h-screen overflow-y-auto shadow-sm">
            <div class="px-6 py-6 border-b border-gray-200">
                <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Admin</p>
                <h2 class="text-xl font-bold text-[#0b3a63]">S3vgroup</h2>
            </div>
            <div class="mb-8">
                <p class="text-xs uppercase tracking-wide text-gray-500">Admin</p>
                <h2 class="text-xl font-semibold text-[#0b3a63]">S3vgroup</h2>
            </div>
            <nav class="flex-1 px-4 py-4 overflow-y-auto">
                <div class="space-y-1">
                <!-- Dashboard -->
                <a href="/admin/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📊</span>
                    <span>Dashboard</span>
                </a>

                <div class="border-t border-gray-200 my-3"></div>

                <!-- Catalog / Products -->
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Catalog</p>
                <a href="/admin/products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📦</span>
                    <span>Products</span>
                </a>
                <a href="/admin/categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🏷️</span>
                    <span>Categories</span>
                </a>

                <div class="border-t border-gray-200 my-3"></div>

                <!-- Content / Pages -->
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Content</p>
                <a href="/admin/pages.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📄</span>
                    <span>All Pages</span>
                </a>
                <a href="/admin/homepage-builder-v2.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'homepage-builder-v2.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🎨</span>
                    <span>Visual Builder</span>
                </a>
                <a href="/admin/company-story.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'company-story.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📖</span>
                    <span>Company Story</span>
                </a>
                <a href="/admin/ceo-message.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'ceo-message.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">💼</span>
                    <span>CEO Message</span>
                </a>
                <a href="/admin/team.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">👥</span>
                    <span>Team Members</span>
                </a>
                <?php if (file_exists(__DIR__ . '/posts.php')): ?>
                    <a href="/admin/posts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'posts.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <span class="text-lg">📝</span>
                        <span>Blog Posts</span>
                    </a>
                <?php endif; ?>

                <div class="border-t border-gray-200 my-3"></div>

                <!-- Marketing -->
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Marketing</p>
                <a href="/admin/sliders.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'sliders.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🖼️</span>
                    <span>Hero Slider</span>
                </a>
                <a href="/admin/testimonials.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">⭐</span>
                    <span>Testimonials</span>
                </a>
                <a href="/admin/newsletter.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📧</span>
                    <span>Newsletter</span>
                </a>

                <div class="border-t border-gray-200 my-3"></div>

                <!-- Leads / Requests -->
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Leads</p>
                <a href="/admin/quotes.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'quotes.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📋</span>
                    <span>Quote Requests</span>
                </a>

                <div class="border-t border-gray-200 my-3"></div>

                <!-- Settings -->
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
                <a href="/admin/options.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'options.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">⚙️</span>
                    <span>Site Options</span>
                </a>
                <a href="/admin/database-sync.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'database-sync.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🗄️</span>
                    <span>Database Sync</span>
                </a>
                </div>
            </nav>
            <div class="border-t border-gray-200 px-4 py-4">
                <form action="/admin/logout.php" method="POST">
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Sidebar (Hidden by default) -->
        <aside id="mobile-sidebar" class="fixed left-0 top-0 z-50 h-full w-64 bg-white border-r border-gray-200 transform -translate-x-full transition-transform duration-300 md:hidden">
            <div class="px-6 py-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Admin</p>
                    <h2 class="text-xl font-bold text-[#0b3a63]">S3vgroup</h2>
                </div>
                <button id="mobile-menu-close" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-4 overflow-y-auto">
                <div class="space-y-1">
                <!-- Dashboard -->
                <a href="/admin/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📊</span>
                    <span>Dashboard</span>
                </a>
                <div class="border-t border-gray-200 my-3"></div>
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Catalog</p>
                <a href="/admin/products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📦</span>
                    <span>Products</span>
                </a>
                <a href="/admin/categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🏷️</span>
                    <span>Categories</span>
                </a>
                <div class="border-t border-gray-200 my-3"></div>
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Content</p>
                <a href="/admin/pages.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📄</span>
                    <span>All Pages</span>
                </a>
                <a href="/admin/homepage-builder-v2.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'homepage-builder-v2.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🎨</span>
                    <span>Visual Builder</span>
                </a>
                <a href="/admin/company-story.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'company-story.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📖</span>
                    <span>Company Story</span>
                </a>
                <a href="/admin/ceo-message.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'ceo-message.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">💼</span>
                    <span>CEO Message</span>
                </a>
                <a href="/admin/team.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">👥</span>
                    <span>Team Members</span>
                </a>
                <div class="border-t border-gray-200 my-3"></div>
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Marketing</p>
                <a href="/admin/sliders.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'sliders.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🖼️</span>
                    <span>Hero Slider</span>
                </a>
                <a href="/admin/testimonials.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">⭐</span>
                    <span>Testimonials</span>
                </a>
                <a href="/admin/newsletter.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📧</span>
                    <span>Newsletter</span>
                </a>
                <div class="border-t border-gray-200 my-3"></div>
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Leads</p>
                <a href="/admin/quotes.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'quotes.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">📋</span>
                    <span>Quote Requests</span>
                </a>
                <div class="border-t border-gray-200 my-3"></div>
                <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
                <a href="/admin/options.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'options.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">⚙️</span>
                    <span>Site Options</span>
                </a>
                <a href="/admin/database-sync.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'database-sync.php' ? 'bg-[#0b3a63] text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="text-lg">🗄️</span>
                    <span>Database Sync</span>
                </a>
                </div>
            </nav>
            <div class="border-t border-gray-200 px-4 py-4">
                <form action="/admin/logout.php" method="POST">
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
            <div class="p-4 md:p-6 lg:p-8">
