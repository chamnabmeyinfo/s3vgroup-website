<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo $siteConfig['name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="hidden md:flex w-64 flex-col border-r border-gray-200 bg-white px-6 py-8 sticky top-0 h-screen overflow-y-auto">
            <div class="mb-8">
                <p class="text-xs uppercase tracking-wide text-gray-500">Admin</p>
                <h2 class="text-xl font-semibold text-[#0b3a63]">S3vgroup</h2>
            </div>
            <nav class="flex flex-1 flex-col gap-1 text-sm font-medium text-gray-700">
                <!-- Dashboard -->
                <a href="/admin/" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📊</span>
                    <span>Dashboard</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- Catalog / Products -->
                <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Catalog</p>
                <a href="/admin/products.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📦</span>
                    <span>Products</span>
                </a>
                <a href="/admin/categories.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">🏷️</span>
                    <span>Categories</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- Content / Pages -->
                <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Content</p>
                <a href="/admin/pages.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📄</span>
                    <span>All Pages</span>
                </a>
                <a href="/admin/homepage-builder-v2.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'homepage-builder-v2.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">🎨</span>
                    <span>Visual Builder</span>
                </a>
                <a href="/admin/company-story.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'company-story.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📖</span>
                    <span>Company Story</span>
                </a>
                <a href="/admin/ceo-message.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'ceo-message.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">💼</span>
                    <span>CEO Message</span>
                </a>
                <a href="/admin/team.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">👥</span>
                    <span>Team Members</span>
                </a>
                <?php if (file_exists(__DIR__ . '/posts.php')): ?>
                    <a href="/admin/posts.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'posts.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                        <span class="text-lg">📝</span>
                        <span>Blog Posts</span>
                    </a>
                <?php endif; ?>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- Marketing -->
                <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Marketing</p>
                <a href="/admin/sliders.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'sliders.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">🖼️</span>
                    <span>Hero Slider</span>
                </a>
                <a href="/admin/testimonials.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">⭐</span>
                    <span>Testimonials</span>
                </a>
                <a href="/admin/newsletter.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📧</span>
                    <span>Newsletter</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- Leads / Requests -->
                <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Leads</p>
                <a href="/admin/quotes.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'quotes.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">📋</span>
                    <span>Quote Requests</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- Settings -->
                <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
                <a href="/admin/options.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'options.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">⚙️</span>
                    <span>Site Options</span>
                </a>
                <a href="/admin/database-sync.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 flex items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) === 'database-sync.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    <span class="text-lg">🗄️</span>
                    <span>Database Sync</span>
                </a>
            </nav>
            <form action="/admin/logout.php" method="POST" class="mt-auto">
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                    Sign out
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="border-b border-gray-200 bg-white px-6 py-4 md:hidden">
                <p class="text-sm font-semibold text-[#0b3a63]">
                    Welcome, <?php echo e($_SESSION['admin_email'] ?? 'Admin'); ?>
                </p>
            </div>
            <div class="p-6 md:p-10">
