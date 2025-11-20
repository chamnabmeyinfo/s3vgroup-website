<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo $siteConfig['name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="hidden md:flex w-64 flex-col border-r border-gray-200 bg-white px-6 py-8">
            <div class="mb-8">
                <p class="text-xs uppercase tracking-wide text-gray-500">Admin</p>
                <h2 class="text-xl font-semibold text-[#0b3a63]">S3vgroup</h2>
            </div>
            <nav class="flex flex-1 flex-col gap-2 text-sm font-medium text-gray-700">
                <a href="/admin/" class="rounded-lg px-4 py-2 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    Overview
                </a>
                <a href="/admin/products.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    Products
                </a>
                <a href="/admin/categories.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    Categories
                </a>
                <a href="/admin/quotes.php" class="rounded-lg px-4 py-2 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'quotes.php' ? 'bg-gray-100 text-[#0b3a63]' : ''; ?>">
                    Quotes
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
