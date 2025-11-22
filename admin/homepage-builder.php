<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../bootstrap/app.php';

use App\Domain\Content\HomepageSectionRepository;
use App\Database\Connection;

requireAdmin();

$db = Connection::getInstance();
$repository = new HomepageSectionRepository($db);
$sections = $repository->all();

$pageTitle = 'Homepage Builder';
include __DIR__ . '/includes/header.php';

// Available section types
$sectionTypes = [
    'hero' => ['label' => 'Hero Slider', 'icon' => '🎯', 'description' => 'Full-width hero banner with slider'],
    'categories' => ['label' => 'Categories Grid', 'icon' => '📦', 'description' => 'Product categories grid'],
    'products' => ['label' => 'Featured Products', 'icon' => '🛍️', 'description' => 'Featured products showcase'],
    'features' => ['label' => 'Features Section', 'icon' => '✨', 'description' => 'Key features/benefits'],
    'testimonials' => ['label' => 'Testimonials', 'icon' => '💬', 'description' => 'Customer testimonials'],
    'newsletter' => ['label' => 'Newsletter Signup', 'icon' => '📧', 'description' => 'Email subscription form'],
    'cta' => ['label' => 'Call to Action', 'icon' => '🚀', 'description' => 'Call-to-action banner'],
    'custom' => ['label' => 'Custom HTML', 'icon' => '📝', 'description' => 'Custom HTML content'],
];
?>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <p class="text-sm uppercase tracking-wide text-gray-500">Page Builder</p>
                <h1 class="text-3xl font-semibold text-[#0b3a63]">Homepage Builder</h1>
                <p class="text-sm text-gray-600">Drag and drop sections to build your homepage</p>
            </div>
            <div class="flex gap-3">
                <a href="/admin/pages.php" class="inline-flex items-center justify-center px-5 py-2 bg-gray-100 text-gray-700 rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    📄 All Pages
                </a>
                <a href="/admin/homepage-builder-v2.php" class="inline-flex items-center justify-center px-5 py-2 bg-purple-600 text-white rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600 transition-colors">
                    <span class="mr-2">🎨</span>
                    Visual Builder
                </a>
                <button type="button" id="add-section-btn" class="inline-flex items-center justify-center px-5 py-2 bg-[#0b3a63] text-white rounded-md shadow-sm hover:bg-[#1a5a8a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3a63] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Section
                </button>
            </div>
        </div>

    <!-- Available Sections -->
    <div id="available-sections" class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Available Sections</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php foreach ($sectionTypes as $type => $info): ?>
                <button type="button" 
                        class="section-type-btn p-4 bg-white rounded-lg border border-gray-300 hover:border-[#0b3a63] hover:shadow-md transition-all text-left group"
                        data-type="<?php echo e($type); ?>"
                        data-label="<?php echo e($info['label']); ?>">
                    <div class="text-3xl mb-2"><?php echo $info['icon']; ?></div>
                    <div class="font-semibold text-sm text-gray-900 group-hover:text-[#0b3a63]"><?php echo e($info['label']); ?></div>
                    <div class="text-xs text-gray-500 mt-1"><?php echo e($info['description']); ?></div>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sections List (Draggable) -->
    <div id="sections-container" class="space-y-4 min-h-[200px]">
        <?php if (empty($sections)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-gray-600 mb-2">No sections yet</p>
                <p class="text-sm text-gray-500">Click "Add Section" or drag a section type above to get started</p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): ?>
                <?php include __DIR__ . '/includes/homepage-section-item.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="mt-6 flex justify-end gap-3 sticky bottom-0 bg-white py-4 border-t border-gray-200">
        <button type="button" id="save-order-btn" class="px-6 py-2 bg-[#0b3a63] text-white rounded-md font-semibold hover:bg-[#1a5a8a] transition-colors">
            Save Order
        </button>
    </div>
</div>

<!-- Section Modal -->
<div id="section-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4 text-gray-900" id="modal-title">Add Section</h3>
        <form id="section-form" class="space-y-4">
            <input type="hidden" id="section-id">
            <input type="hidden" id="section-type">
            
            <div id="section-config-content">
                <!-- Dynamic content based on section type -->
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" id="cancel-section-btn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#0b3a63] text-white rounded-md text-sm font-medium hover:bg-[#1a5a8a]">Save Section</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/admin/js/homepage-builder.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>

