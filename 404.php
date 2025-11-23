<?php
http_response_code(404);
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = '404 - Page Not Found';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-24 text-center">
    <h1 class="text-6xl font-bold text-[#0b3a63] mb-4">404</h1>
    <p class="text-xl text-gray-600 mb-8">Page not found</p>
    <a href="/" class="inline-block px-6 py-3 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors">
        Go Home
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
