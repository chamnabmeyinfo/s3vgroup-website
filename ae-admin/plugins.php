<?php
session_start();
// Check ae-load.php first, then wp-load.php as fallback
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

$db = getDB();
$registry = new \App\Core\PluginRegistry($db);
$pluginManager = new \App\Core\PluginManager($db, $registry, new \App\Core\HookSystem());

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slug = $_POST['slug'] ?? '';

    switch ($action) {
        case 'activate':
            try {
                $pluginManager->activate($slug);
                $message = ['type' => 'success', 'text' => 'Plugin activated successfully'];
            } catch (\Exception $e) {
                $message = ['type' => 'error', 'text' => 'Error: ' . $e->getMessage()];
            }
            break;

        case 'deactivate':
            try {
                $pluginManager->deactivate($slug);
                $message = ['type' => 'success', 'text' => 'Plugin deactivated successfully'];
            } catch (\Exception $e) {
                $message = ['type' => 'error', 'text' => 'Error: ' . $e->getMessage()];
            }
            break;

        case 'discover':
            $count = $pluginManager->registerDiscoveredPlugins();
            $message = ['type' => 'success', 'text' => "Discovered and registered {$count} plugin(s)"];
            break;
    }
}

// Get all plugins
$allPlugins = $registry->getAll();
$activePlugins = $registry->getActive();
$activeSlugs = array_column($activePlugins, 'slug');

$pageTitle = 'Plugins';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Plugins</h1>
                            <p class="text-sm text-gray-500 mt-0.5">Manage plugins to extend functionality</p>
                        </div>
                    </div>
                </div>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="discover">
                    <button type="submit" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-sm hover:shadow">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Discover Plugins
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="mb-6 bg-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-50 border border-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <?php if ($message['type'] === 'success'): ?>
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                <?php else: ?>
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                <?php endif; ?>
                <p class="text-sm font-medium text-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-800"><?= htmlspecialchars($message['text']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Plugins</p>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?php echo count($allPlugins); ?></p>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-green-700 uppercase tracking-wide">Active Plugins</p>
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-green-900"><?php echo count($activePlugins); ?></p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Inactive Plugins</p>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?php echo count($allPlugins) - count($activePlugins); ?></p>
        </div>
    </div>

    <!-- Plugins List -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Installed Plugins</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plugin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($allPlugins)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-900">No plugins found</p>
                                    <p class="text-xs text-gray-500 mt-1">Click "Discover Plugins" to scan the plugins directory</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allPlugins as $plugin): ?>
                            <?php $isActive = in_array($plugin['slug'], $activeSlugs); ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                <?= htmlspecialchars($plugin['name']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                <?= htmlspecialchars($plugin['slug']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700">
                                        v<?= htmlspecialchars($plugin['version']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 max-w-md">
                                        <?= htmlspecialchars($plugin['metadata']['description'] ?? 'No description available') ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($isActive): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="slug" value="<?= htmlspecialchars($plugin['slug']) ?>">
                                            <button type="submit" class="px-4 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                                Deactivate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="slug" value="<?= htmlspecialchars($plugin['slug']) ?>">
                                            <button type="submit" class="px-4 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                                Activate
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Box -->
    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-indigo-900 mb-2">Plugin Development Guide</h3>
                <p class="text-indigo-800 mb-4 text-sm leading-relaxed">
                    To create a new plugin, create a folder in the <code class="bg-indigo-100 px-2 py-1 rounded text-xs font-mono">plugins/</code> directory
                    and add a PHP file with plugin headers.
                </p>
                <div class="bg-white rounded-lg p-4 border border-indigo-100 mb-4">
                    <p class="text-sm font-mono text-gray-700">
                        <span class="font-semibold text-gray-900">Example structure:</span><br>
                        <span class="text-indigo-600">plugins/my-plugin/my-plugin.php</span>
                    </p>
                </div>
                <p class="text-indigo-800 text-sm">
                    See <code class="bg-indigo-100 px-2 py-1 rounded text-xs font-mono">plugins/example-plugin/</code> for a working example.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

