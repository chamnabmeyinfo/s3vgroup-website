<?php

session_start();

// Check ae-load.php first, then wp-load.php as fallback
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

$db = getDB();
$uploadsBasePath = BASE_PATH . '/uploads';
$uploadsBaseUrl = '/uploads';
$largeThresholdKb = 500; // default 500 KB

$search = trim((string) ($_GET['search'] ?? ''));
$filter = $_GET['filter'] ?? 'all';
$thresholdParam = (int) ($_GET['threshold_kb'] ?? $largeThresholdKb);
if ($thresholdParam > 0) {
    $largeThresholdKb = $thresholdParam;
}
$largeThresholdBytes = $largeThresholdKb * 1024;

$mediaFiles = [];
$totalSize = 0;
$missingFiles = [];

if (is_dir($uploadsBasePath)) {
    $usageMap = buildMediaUsageIndex($db);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $uploadsBasePath,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
        )
    );

    foreach ($iterator as $fileInfo) {
        /** @var SplFileInfo $fileInfo */
        if (!$fileInfo->isFile()) {
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'avif'], true)) {
            continue;
        }

        $absolutePath = $fileInfo->getRealPath();
        if ($absolutePath === false) {
            continue;
        }

        $relativePath = str_replace('\\', '/', trim(str_replace(BASE_PATH, '', $absolutePath), '/'));
        if ($relativePath === '') {
            continue;
        }

        if ($search !== '' && stripos($relativePath, $search) === false && stripos($fileInfo->getFilename(), $search) === false) {
            continue;
        }

        $sizeBytes = $fileInfo->getSize();
        $isLarge = $sizeBytes >= $largeThresholdBytes;

        if ($filter === 'large' && !$isLarge) {
            continue;
        }

        $dimensions = getImageDimensions($absolutePath);
        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $usageKey = ltrim($relativePath, '/');
        $usage = $usageMap[$usageKey] ?? [];

        $mediaFiles[] = [
            'name' => $fileInfo->getFilename(),
            'relativePath' => $relativePath,
            'url' => $uploadsBaseUrl . substr($relativePath, strlen('uploads')),
            'folder' => dirname($relativePath),
            'sizeBytes' => $sizeBytes,
            'sizeLabel' => humanReadableSize($sizeBytes),
            'dimensions' => $dimensions,
            'mime' => $mimeType,
            'modified' => $fileInfo->getMTime(),
            'isLarge' => $isLarge,
            'usage' => $usage,
        ];

        $totalSize += $sizeBytes;
    }
} else {
    $missingFiles[] = $uploadsBasePath;
}

usort($mediaFiles, static fn ($a, $b) => $b['sizeBytes'] <=> $a['sizeBytes']);

$stats = [
    'total_files' => count($mediaFiles),
    'total_size' => humanReadableSize($totalSize),
    'large_files' => count(array_filter($mediaFiles, static fn ($file) => $file['isLarge'])),
    'largest_file' => $mediaFiles[0]['sizeLabel'] ?? '—',
];

$pageTitle = 'Media Library';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Media Library</h1>
                            <p class="text-sm text-gray-500 mt-0.5">Monitor uploaded images, detect oversized files, and review usage</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($missingFiles): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-900">Warning</p>
                    <p class="text-sm text-red-800 mt-1">The uploads directory was not found: <?php echo e(implode(', ', $missingFiles)); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Files</p>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e(number_format($stats['total_files'])); ?></p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Storage</p>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['total_size']); ?></p>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-yellow-700 uppercase tracking-wide">Large Files</p>
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-yellow-900"><?php echo e(number_format($stats['large_files'])); ?></p>
            <p class="text-xs text-yellow-700 mt-1">&gt; <?php echo e($largeThresholdKb); ?> KB</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Largest File</p>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['largest_file']); ?></p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Filters & Search</h2>
        </div>
        <form method="get" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Files</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="<?php echo e($search); ?>"
                            placeholder="Search by filename or path..."
                            class="w-full pl-10 pr-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        >
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter</label>
                    <select name="filter" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Files</option>
                        <option value="large" <?php echo $filter === 'large' ? 'selected' : ''; ?>>Large Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Large Threshold (KB)</label>
                    <input
                        type="number"
                        name="threshold_kb"
                        value="<?php echo e($largeThresholdKb); ?>"
                        min="50"
                        step="50"
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                    >
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md hover:shadow-lg">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Apply Filters
                    </span>
                </button>
                <a href="?filter=all" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Media Files Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Media Files</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File Information</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dimensions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Modified</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($mediaFiles)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-900">No media files found</p>
                                    <p class="text-xs text-gray-500 mt-1">Try adjusting your filters or search terms</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mediaFiles as $file): ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $file['isLarge'] ? 'bg-yellow-50/50' : ''; ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center w-20 h-20 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                        <?php if ($file['mime'] === 'image/svg+xml'): ?>
                                            <div class="flex items-center justify-center w-full h-full">
                                                <span class="text-xs font-medium text-gray-600">SVG</span>
                                            </div>
                                        <?php else: ?>
                                            <img
                                                src="<?php echo e($file['url']); ?>"
                                                alt="<?php echo e($file['name']); ?>"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                            >
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($file['name']); ?></p>
                                        <p class="text-xs text-gray-500 font-mono truncate max-w-xs"><?php echo e($file['relativePath']); ?></p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                <?php echo e(explode('/', $file['mime'])[1] ?? 'file'); ?>
                                            </span>
                                            <?php if ($file['isLarge']): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    Large
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($file['sizeLabel']); ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e(number_format($file['sizeBytes'])); ?> bytes</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($file['dimensions']): ?>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo e($file['dimensions']['width']); ?> × <?php echo e($file['dimensions']['height']); ?></p>
                                            <p class="text-xs text-gray-500 mt-0.5">Ratio: <?php echo e($file['dimensions']['ratio']); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (empty($file['usage'])): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            Unused
                                        </span>
                                    <?php else: ?>
                                        <div class="space-y-1">
                                            <?php foreach (array_slice($file['usage'], 0, 2) as $usage): ?>
                                                <div class="text-xs">
                                                    <span class="font-semibold text-gray-900"><?php echo e($usage['type']); ?>:</span>
                                                    <span class="text-gray-600"><?php echo e($usage['label']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($file['usage']) > 2): ?>
                                                <p class="text-xs text-gray-500">+<?php echo e(count($file['usage']) - 2); ?> more</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600">
                                        <p><?php echo e(date('M j, Y', $file['modified'])); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e(date('H:i', $file['modified'])); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php

function humanReadableSize(int $bytes): string
{
    if ($bytes === 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = (int) floor(log($bytes, 1024));

    return sprintf('%.1f %s', $bytes / pow(1024, $power), $units[$power]);
}

function getImageDimensions(string $path): ?array
{
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if ($extension === 'svg') {
        return null;
    }

    if (!is_readable($path)) {
        return null;
    }

    $info = @getimagesize($path);
    if (!$info || count($info) < 2) {
        return null;
    }

    $width = (int) $info[0];
    $height = (int) $info[1];
    if ($width === 0 || $height === 0) {
        return null;
    }

    $ratio = $height === 0 ? 0 : $width / $height;

    return [
        'width' => $width,
        'height' => $height,
        'ratio' => number_format($ratio, 2),
    ];
}

function buildMediaUsageIndex(PDO $pdo): array
{
    $index = [];

    $add = static function (?string $path, string $label, string $type) use (&$index): void {
        $normalized = normalizeMediaPath($path);
        if ($normalized === '') {
            return;
        }
        $index[$normalized][] = ['type' => $type, 'label' => $label];
    };

    // Products
    $products = $pdo->query('SELECT name, heroImage FROM products')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        $add($product['heroImage'] ?? '', $product['name'], 'Product');
    }

    // Team members
    $teamMembers = $pdo->query('SELECT name, photo FROM team_members')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($teamMembers as $member) {
        $add($member['photo'] ?? '', $member['name'], 'Team member');
    }

    // Sliders
    $sliders = $pdo->query('SELECT title, image_url FROM sliders')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sliders as $slider) {
        $add($slider['image_url'] ?? '', $slider['title'] ?: 'Slider slide', 'Slider');
    }

    // Pages
    $pages = $pdo->query('SELECT title, featured_image FROM pages')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pages as $page) {
        $add($page['featured_image'] ?? '', $page['title'] ?: 'Page', 'Page');
    }

    // Testimonials
    $testimonials = $pdo->query('SELECT name, avatar FROM testimonials')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($testimonials as $testimonial) {
        $add($testimonial['avatar'] ?? '', $testimonial['name'] ?: 'Testimonial', 'Testimonial');
    }

    // CEO message
    $ceoMessages = $pdo->query('SELECT name, photo FROM ceo_message')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ceoMessages as $message) {
        $add($message['photo'] ?? '', $message['name'] ?: 'CEO message', 'CEO message');
    }

    // Company story
    $stories = $pdo->query('SELECT title, heroImage FROM company_story')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($stories as $story) {
        $add($story['heroImage'] ?? '', $story['title'] ?: 'Company story', 'Company story');
    }

    return $index;
}

function normalizeMediaPath(?string $path): string
{
    if (!$path) {
        return '';
    }

    $path = trim($path);

    if ($path === '') {
        return '';
    }

    if (filter_var($path, FILTER_VALIDATE_URL)) {
        $components = parse_url($path);
        if (!isset($components['path'])) {
            return '';
        }
        $path = $components['path'];
    }

    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');

    if (!str_starts_with($path, 'uploads/')) {
        return '';
    }

    return $path;
}


