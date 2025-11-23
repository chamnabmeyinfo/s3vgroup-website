<?php
/**
 * Dynamic Page Router
 * Renders custom-designed pages based on slug
 */

// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

use App\Database\Connection;
use App\Domain\Content\PageRepository;
use App\Domain\Content\HomepageSectionRepository;

$slug = $_GET['slug'] ?? 'home';

// Get page from database
$db = Connection::getInstance();
$pageRepository = new PageRepository($db);
$page = $pageRepository->findBySlug($slug);

if (!$page) {
    // Page not found, show 404
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Get sections for this page
$sectionRepository = new HomepageSectionRepository($db);
$sections = $sectionRepository->active($page['id']);

// Set page metadata
$pageTitle = $page['meta_title'] ?? $page['title'];
$pageDescription = $page['meta_description'] ?? $page['description'] ?? '';

// Get site colors
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

include __DIR__ . '/includes/header.php';
?>

<?php if (empty($sections)): ?>
    <!-- Default page content if no sections -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <?php if (!empty($page['featured_image'])): ?>
            <img src="<?php echo e($page['featured_image']); ?>" alt="<?php echo e($page['title']); ?>" class="w-full h-64 object-cover rounded-lg mb-8">
        <?php endif; ?>
        
        <h1 class="text-4xl sm:text-5xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>;">
            <?php echo e($page['title']); ?>
        </h1>
        
        <?php if ($page['description']): ?>
            <div class="text-xl text-gray-600 mb-8 prose max-w-none">
                <?php echo nl2br(e($page['description'])); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <p class="text-yellow-800 text-sm">
                ⚠️ This page has no sections yet. <a href="/admin/page-builder.php?page_id=<?php echo urlencode($page['id']); ?>" class="font-semibold underline">Design this page</a> to add content.
            </p>
        </div>
    </div>
<?php else: ?>
    <!-- Render page sections -->
    <?php
    foreach ($sections as $section) {
        include __DIR__ . '/includes/widgets/page-section-renderer.php';
    }
    ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

