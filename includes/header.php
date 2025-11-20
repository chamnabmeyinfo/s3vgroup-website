<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo $siteConfig['name']; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : $siteConfig['description']; ?>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #0b3a63;
        }
        body {
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur">
        <div class="container mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <svg class="h-6 w-6 text-[#0b3a63]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-xl font-bold text-[#0b3a63]">S3V Forklift</span>
                </a>

                <nav class="hidden md:flex items-center gap-6">
                    <a href="/products.php" class="text-sm font-medium text-gray-700 hover:text-[#0b3a63] transition-colors">
                        Forklifts
                    </a>
                    <a href="/quote.php" class="text-sm font-medium text-gray-700 hover:text-[#0b3a63] transition-colors">
                        Get Quote
                    </a>
                    <a href="/contact.php" class="text-sm font-medium text-gray-700 hover:text-[#0b3a63] transition-colors">
                        Contact
                    </a>
                    <a href="/admin/" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-md hover:bg-gray-50">
                        Admin
                    </a>
                </nav>

                <button class="md:hidden p-2" onclick="toggleMobileMenu()">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main>
