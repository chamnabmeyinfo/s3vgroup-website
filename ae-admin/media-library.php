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

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Assets</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Media Library</h1>
            <p class="text-sm text-gray-600">
                Monitor uploaded images, detect oversized files, and review usage.
            </p>
        </div>
    </div>

    <?php if ($missingFiles): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Warning</p>
            <p>The uploads directory was not found: <?php echo e(implode(', ', $missingFiles)); ?></p>
        </div>
    <?php endif; ?>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-sm text-gray-500">Total Files</p>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e(number_format($stats['total_files'])); ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-sm text-gray-500">Total Storage</p>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['total_size']); ?></p>
        </div>
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
            <p class="text-sm text-yellow-700">Large Files (&gt; <?php echo e($largeThresholdKb); ?> KB)</p>
            <p class="text-2xl font-semibold text-yellow-900"><?php echo e(number_format($stats['large_files'])); ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-sm text-gray-500">Largest File</p>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['largest_file']); ?></p>
        </div>
    </div>

    <form method="get" class="bg-white border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
                type="text"
                name="search"
                value="<?php echo e($search); ?>"
                placeholder="Search by filename or path..."
                class="admin-form-input w-full"
            >
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
            <select name="filter" class="admin-form-select">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Files</option>
                <option value="large" <?php echo $filter === 'large' ? 'selected' : ''; ?>>Large Only</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Large threshold (KB)</label>
            <input
                type="number"
                name="threshold_kb"
                value="<?php echo e($largeThresholdKb); ?>"
                min="50"
                step="50"
                class="admin-form-input w-32"
            >
        </div>
        <div class="flex gap-2">
            <button type="submit" class="admin-btn admin-btn-primary">Apply</button>
            <a href="?filter=all" class="admin-btn admin-btn-secondary">Reset</a>
        </div>
    </form>

    <div class="overflow-auto bg-white border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Preview</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">File</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Size</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Dimensions</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Usage</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Last Modified</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($mediaFiles)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No media files found with the current filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($mediaFiles as $file): ?>
                        <tr class="<?php echo $file['isLarge'] ? 'bg-yellow-50' : ''; ?>">
                            <td class="px-4 py-3">
                                <?php if ($file['mime'] === 'image/svg+xml'): ?>
                                    <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded">
                                        <span class="text-xs text-gray-600">SVG</span>
                                    </div>
                                <?php else: ?>
                                    <img
                                        src="<?php echo e($file['url']); ?>"
                                        alt="<?php echo e($file['name']); ?>"
                                        class="w-16 h-16 object-cover rounded border border-gray-200"
                                    >
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900"><?php echo e($file['name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($file['relativePath']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($file['mime']); ?></p>
                                <?php if ($file['isLarge']): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-yellow-200 text-yellow-900">
                                        Large file
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900"><?php echo e($file['sizeLabel']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(number_format($file['sizeBytes'])); ?> bytes</p>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($file['dimensions']): ?>
                                    <p class="font-medium text-gray-900"><?php echo e($file['dimensions']['width']); ?> × <?php echo e($file['dimensions']['height']); ?> px</p>
                                    <p class="text-xs text-gray-500">Aspect ratio <?php echo e($file['dimensions']['ratio']); ?></p>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">—</p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (empty($file['usage'])): ?>
                                    <span class="text-xs text-gray-500">Unused</span>
                                <?php else: ?>
                                    <ul class="space-y-1 text-xs text-gray-700">
                                        <?php foreach ($file['usage'] as $usage): ?>
                                            <li>
                                                <span class="font-semibold text-gray-900"><?php echo e($usage['type']); ?>:</span>
                                                <?php echo e($usage['label']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo e(date('Y-m-d H:i', $file['modified'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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


