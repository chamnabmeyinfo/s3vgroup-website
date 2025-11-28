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

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Settings</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Plugins</h1>
            <p class="text-sm text-gray-600">
                Manage plugins to extend functionality
            </p>
        </div>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="discover">
            <button type="submit" class="admin-btn admin-btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Discover Plugins</span>
            </button>
        </form>
    </div>

    <?php if (isset($message)): ?>
        <div class="bg-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-50 border border-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-200 rounded-lg p-4">
            <p class="text-<?= $message['type'] === 'success' ? 'green' : 'red' ?>-800"><?= htmlspecialchars($message['text']) ?></p>
        </div>
    <?php endif; ?>

    <!-- Plugins List -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plugin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($allPlugins)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No plugins found. Click "Discover Plugins" to scan the plugins directory.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allPlugins as $plugin): ?>
                            <?php $isActive = in_array($plugin['slug'], $activeSlugs); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($plugin['name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($plugin['slug']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($plugin['version']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= htmlspecialchars($plugin['metadata']['description'] ?? 'No description') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($isActive): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($isActive): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="slug" value="<?= htmlspecialchars($plugin['slug']) ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Deactivate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="slug" value="<?= htmlspecialchars($plugin['slug']) ?>">
                                            <button type="submit" class="text-green-600 hover:text-green-900">
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
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">📚 Plugin Development</h3>
        <p class="text-blue-800 mb-4">
            To create a new plugin, create a folder in the <code class="bg-blue-100 px-2 py-1 rounded">plugins/</code> directory
            and add a PHP file with plugin headers.
        </p>
        <div class="bg-white rounded p-4">
            <p class="text-sm font-mono text-gray-700">
                <strong>Example:</strong><br>
                plugins/my-plugin/my-plugin.php
            </p>
        </div>
        <p class="text-blue-800 mt-4">
            See <code class="bg-blue-100 px-2 py-1 rounded">plugins/example-plugin/</code> for a working example.
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

