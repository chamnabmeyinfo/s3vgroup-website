<?php
http_response_code(404);

// If this is an image request, return proper 404 without loading screen
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $requestUri)) {
    // Image file not found - return minimal 404 response
    header('Content-Type: text/plain');
    http_response_code(404);
    echo '404 Image Not Found';
    exit;
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load Ant Elite bootstrap (ae-load.php)
if (file_exists(__DIR__ . '/ae-load.php')) {
    require_once __DIR__ . '/ae-load.php';
} else {
    require_once __DIR__ . '/wp-load.php';
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';

// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/functions.php')) {
    require_once __DIR__ . '/ae-includes/functions.php';
} elseif (file_exists(__DIR__ . '/wp-includes/functions.php')) {
    require_once __DIR__ . '/wp-includes/functions.php';
} elseif (file_exists(__DIR__ . '/includes/functions.php')) {
    require_once __DIR__ . '/includes/functions.php';
}

$pageTitle = '404 - Page Not Found';

// Load header (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/header.php')) {
    include __DIR__ . '/ae-includes/header.php';
} elseif (file_exists(__DIR__ . '/wp-includes/header.php')) {
    include __DIR__ . '/wp-includes/header.php';
} elseif (file_exists(__DIR__ . '/includes/header.php')) {
    include __DIR__ . '/includes/header.php';
}
?>

<div class="container mx-auto px-4 py-24 text-center">
    <h1 class="text-6xl font-bold text-[#0b3a63] mb-4">404</h1>
    <p class="text-xl text-gray-600 mb-8">Page not found</p>
    <a href="/" class="inline-block px-6 py-3 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors">
        Go Home
    </a>
</div>

<?php
// Load footer (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/ae-includes/footer.php')) {
    include __DIR__ . '/ae-includes/footer.php';
} elseif (file_exists(__DIR__ . '/wp-includes/footer.php')) {
    include __DIR__ . '/wp-includes/footer.php';
} elseif (file_exists(__DIR__ . '/includes/footer.php')) {
    include __DIR__ . '/includes/footer.php';
}
?>
