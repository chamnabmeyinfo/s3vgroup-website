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

use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService;

$db = getDB();
$repository = new SiteOptionRepository($db);
$service = new SiteOptionService($repository);

// Get current database configuration
$currentDbConfig = [
    'host' => DB_HOST,
    'database' => DB_NAME,
    'username' => DB_USER,
    'port' => defined('DB_PORT') ? DB_PORT : 3306,
];

// Get cPanel database configuration from site options
$cpanelConfig = [
    'host' => $service->get('db_sync_cpanel_host', ''),
    'database' => $service->get('db_sync_cpanel_database', ''),
    'username' => $service->get('db_sync_cpanel_username', ''),
    'password' => $service->get('db_sync_cpanel_password', ''),
    'port' => $service->get('db_sync_cpanel_port', '3306'),
];

$pageTitle = 'Database Sync';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Database Sync</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Import/Export database between local development and cPanel production</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configuration Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Configuration</h2>
                <p class="text-xs text-gray-500 mt-1">Configure cPanel database connection settings</p>
            </div>
            <div class="p-6">

            <form id="config-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">cPanel Database Host</label>
                    <input type="text" name="cpanel_host" value="<?php echo htmlspecialchars($cpanelConfig['host']); ?>" 
                           placeholder="localhost or cpanel_hostname" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1.5">Usually "localhost" for cPanel databases</p>
                </div>

            <form id="config-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">cPanel Database Name</label>
                    <input type="text" name="cpanel_database" value="<?php echo htmlspecialchars($cpanelConfig['database']); ?>" 
                           placeholder="your_cpanel_database" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">cPanel Database Username</label>
                    <input type="text" name="cpanel_username" value="<?php echo htmlspecialchars($cpanelConfig['username']); ?>" 
                           placeholder="your_cpanel_db_user" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">cPanel Database Password</label>
                    <input type="password" name="cpanel_password" value="<?php echo htmlspecialchars($cpanelConfig['password']); ?>" 
                           placeholder="••••••••" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1.5">Stored securely in site options</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">cPanel Database Port</label>
                    <input type="number" name="cpanel_port" value="<?php echo htmlspecialchars($cpanelConfig['port']); ?>" 
                           placeholder="3306" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1.5">Default: 3306 (MySQL/MariaDB)</p>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Production Site URL</label>
                    <input type="url" name="production_url" value="<?php echo htmlspecialchars($service->get('db_sync_production_url', '')); ?>" 
                           placeholder="https://s3vgroup.com" 
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1.5">Used to replace localhost URLs when pushing to cPanel</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-lg text-sm font-semibold hover:from-emerald-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-md hover:shadow-lg">
                        Save Configuration
                    </button>
                    <button type="button" id="test-connection-btn" class="flex-1 px-6 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow">
                        Test Connection
                    </button>
                </div>
                <div id="test-connection-status" class="mt-3 text-sm hidden"></div>
            </form>
            </div>
        </div>

        <!-- Current Database Info -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Current Database</h2>
                <p class="text-xs text-gray-500 mt-1">Your local development database connection</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Host</span>
                        <p class="text-sm font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($currentDbConfig['host']); ?></p>
                    </div>
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Database</span>
                        <p class="text-sm font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($currentDbConfig['database']); ?></p>
                    </div>
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Username</span>
                        <p class="text-sm font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($currentDbConfig['username']); ?></p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Port</span>
                        <p class="text-sm font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($currentDbConfig['port']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Status Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Sync Status</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <span class="text-xs font-medium text-blue-700 uppercase tracking-wide">Last Pull from cPanel</span>
                    <p id="last-pull-time" class="text-lg font-semibold text-blue-900 mt-2">
                        <?php 
                        $lastPull = $service->get('db_sync_last_pull', '');
                        echo !empty($lastPull) ? $lastPull : 'Never';
                        ?>
                    </p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <span class="text-xs font-medium text-green-700 uppercase tracking-wide">Last Push to cPanel</span>
                    <p id="last-push-time" class="text-lg font-semibold text-green-900 mt-2">
                        <?php 
                        $lastPush = $service->get('db_sync_last_push', '');
                        echo !empty($lastPush) ? $lastPush : 'Never';
                        ?>
                    </p>
                </div>
            </div>
            <div id="sync-status-warning" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-yellow-800">
                        <strong>Warning:</strong> You haven't pulled from cPanel recently. 
                        Make sure to pull latest data from production before pushing changes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pull from cPanel Card -->
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-green-100 to-emerald-100 border-b border-green-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Step 1: Pull from cPanel</h2>
                    <p class="text-sm text-gray-600 mt-0.5">Get latest data from production server to your local database</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            
            <div class="bg-white rounded-lg p-4 mb-4 border border-green-100">
                <div class="space-y-3">
                    <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" name="pull-mode" value="full" checked class="mr-3 w-4 h-4 text-green-600 focus:ring-green-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Full Pull</span>
                            <p class="text-xs text-gray-500 mt-0.5">Pull structure + all data (recommended)</p>
                        </div>
                    </label>
                    <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" name="pull-mode" value="structure_only" class="mr-3 w-4 h-4 text-green-600 focus:ring-green-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Structure Only</span>
                            <p class="text-xs text-gray-500 mt-0.5">Pull only table structure (no data)</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4 p-3 bg-white rounded-lg border border-green-100">
                <input type="checkbox" id="pull-backup" checked class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                <label for="pull-backup" class="text-sm text-gray-700 cursor-pointer">Create backup of local database before pull</label>
            </div>

            <button type="button" id="pull-btn" class="w-full px-6 py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all font-semibold text-base shadow-lg hover:shadow-xl">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Pull cPanel → Local Now
                </span>
            </button>
            <div id="pull-status" class="mt-3 text-sm hidden"></div>
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-800">
                        <strong>Full Overwrite:</strong> This will <strong>completely overwrite</strong> your local database with data from cPanel production. 
                        All local tables will be dropped and recreated. Your local changes will be lost unless you have a backup.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Push to cPanel Card -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-blue-100 to-indigo-100 border-b border-blue-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Step 2: Push to cPanel</h2>
                    <p class="text-sm text-gray-600 mt-0.5">Push your local database changes to cPanel production</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            
            <div class="bg-white rounded-lg p-4 mb-4 border border-blue-100">
                <div class="space-y-3">
                    <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" name="sync-mode" value="full" checked class="mr-3 w-4 h-4 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Full Sync</span>
                            <p class="text-xs text-gray-500 mt-0.5">Sync structure + all data (recommended for development)</p>
                        </div>
                    </label>
                    <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" name="sync-mode" value="structure_only" class="mr-3 w-4 h-4 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Structure Only</span>
                            <p class="text-xs text-gray-500 mt-0.5">Sync only table structure (no data - safer for production)</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4 p-3 bg-white rounded-lg border border-blue-100">
                <input type="checkbox" id="auto-sync-backup" checked class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="auto-sync-backup" class="text-sm text-gray-700 cursor-pointer">Create backup before sync</label>
            </div>

            <button type="button" id="auto-sync-btn" class="w-full px-6 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-semibold text-base shadow-lg hover:shadow-xl">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Push Local → cPanel Now
                </span>
            </button>
            <div id="auto-sync-status" class="mt-3 text-sm hidden"></div>
            
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-yellow-800">
                        <strong>Warning:</strong> This will overwrite the cPanel database with your local database. 
                        Make sure you have a backup and that your local database is up-to-date.
                    </p>
                </div>
            </div>
            
            <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-800">
                        <strong>Auto URL Replacement:</strong> All localhost URLs (localhost:8080, localhost:8000, etc.) 
                        will be automatically replaced with your production site URL when pushing to cPanel.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Report Card -->
    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border-2 border-purple-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-purple-100 to-pink-100 border-b border-purple-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Database Comparison Report</h2>
                    <p class="text-sm text-gray-600 mt-0.5">Compare local and cPanel databases to see differences</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <button type="button" id="compare-btn" class="w-full px-6 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all font-semibold text-base shadow-lg hover:shadow-xl mb-4">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Generate Comparison Report
                </span>
            </button>
            
            <div id="compare-status" class="mb-4 text-sm hidden"></div>
            
            <!-- Report Display Area -->
            <div id="comparison-report" class="hidden mt-4 space-y-4">
                <!-- Summary -->
                <div id="report-summary" class="bg-white rounded-lg p-4 border border-purple-100"></div>
                
                <!-- Tables Comparison -->
                <div id="report-tables" class="bg-white rounded-lg p-4 border border-purple-100"></div>
                
                <!-- Data Comparison -->
                <div id="report-data" class="bg-white rounded-lg p-4 border border-purple-100 max-h-96 overflow-y-auto"></div>
            </div>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Manual Operations</h2>
            <p class="text-xs text-gray-500 mt-1">Export from local or import SQL file to cPanel</p>
        </div>
        <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Export Section -->
                <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Export Database</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Export your local database to a SQL file</p>
                
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" id="export-structure" checked class="mr-2">
                        <span class="text-sm text-gray-700">Include structure (tables, indexes)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="export-data" checked class="mr-2">
                        <span class="text-sm text-gray-700">Include data (all records)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="export-drop-tables" class="mr-2">
                        <span class="text-sm text-gray-700">Add DROP TABLE statements</span>
                    </label>
                </div>

                    <button type="button" id="export-btn" class="mt-4 w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-semibold shadow-md hover:shadow-lg">
                        Export Database
                    </button>
                    <div id="export-status" class="mt-2 text-sm hidden"></div>
                </div>

                <!-- Import Section -->
                <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Import to cPanel</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Import exported database to cPanel production</p>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select SQL File</label>
                        <input type="file" id="import-file" accept=".sql" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#0b3a63] file:text-white hover:file:bg-[#1a5a8a]">
                    </div>
                    <label class="flex items-center">
                        <input type="checkbox" id="import-backup" checked class="mr-2">
                        <span class="text-sm text-gray-700">Create backup before import</span>
                    </label>
                </div>

                    <button type="button" id="import-btn" class="mt-4 w-full px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all font-semibold shadow-md hover:shadow-lg">
                        Import to cPanel
                    </button>
                    <div id="import-status" class="mt-2 text-sm hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status/Log Area -->
    <div id="operation-log" class="bg-gray-50 rounded-xl border border-gray-200 p-6 hidden">
        <div class="flex items-center gap-3 mb-3">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Operation Log</h3>
        </div>
        <div id="log-content" class="text-sm text-gray-700 font-mono whitespace-pre-wrap max-h-64 overflow-y-auto bg-white rounded-lg p-4 border border-gray-200"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Save configuration
    document.getElementById('config-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const payload = {
            db_sync_cpanel_host: formData.get('cpanel_host'),
            db_sync_cpanel_database: formData.get('cpanel_database'),
            db_sync_cpanel_username: formData.get('cpanel_username'),
            db_sync_cpanel_password: formData.get('cpanel_password'),
            db_sync_cpanel_port: formData.get('cpanel_port') || '3306',
            db_sync_production_url: formData.get('production_url') || '',
        };

        try {
            const response = await fetch('/api/admin/options/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bulk: payload }),
            });

            const result = await response.json();
            if (result.status === 'success') {
                alert('✅ Configuration saved successfully!');
            } else {
                alert('❌ Failed to save configuration: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    });

    // Test connection
    document.getElementById('test-connection-btn').addEventListener('click', async () => {
        const testBtn = document.getElementById('test-connection-btn');
        const statusDiv = document.getElementById('test-connection-status');
        const form = document.getElementById('config-form');
        const formData = new FormData(form);

        testBtn.disabled = true;
        testBtn.textContent = 'Testing...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Testing connection...';
        statusDiv.className = 'mt-2 text-sm text-blue-600';

        const testPayload = {
            host: formData.get('cpanel_host'),
            database: formData.get('cpanel_database'),
            username: formData.get('cpanel_username'),
            password: formData.get('cpanel_password'),
            port: formData.get('cpanel_port') || '3306',
        };

        try {
            const response = await fetch('/api/admin/database/test-connection.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(testPayload),
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = `✅ ${result.data.message} (${result.data.table_count} tables found, MySQL ${result.data.mysql_version})`;
                statusDiv.className = 'mt-2 text-sm text-green-600 font-semibold';
            } else {
                let errorMsg = '❌ ' + result.message;
                if (result.context && result.context.suggestions) {
                    errorMsg += '\n\nSuggestions:\n' + result.context.suggestions.map(s => '• ' + s).join('\n');
                }
                statusDiv.textContent = errorMsg;
                statusDiv.className = 'mt-2 text-sm text-red-600';
            }
        } catch (error) {
            statusDiv.textContent = '❌ Connection test failed: ' + error.message;
            statusDiv.className = 'mt-2 text-sm text-red-600';
        } finally {
            testBtn.disabled = false;
            testBtn.textContent = 'Test Connection';
        }
    });

    // Export database
    document.getElementById('export-btn').addEventListener('click', async () => {
        const exportBtn = document.getElementById('export-btn');
        const statusDiv = document.getElementById('export-status');
        const logDiv = document.getElementById('operation-log');
        const logContent = document.getElementById('log-content');

        exportBtn.disabled = true;
        exportBtn.textContent = 'Exporting...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Starting export...';
        logDiv.classList.remove('hidden');
        logContent.textContent = 'Starting database export...\n';

        const options = {
            structure: document.getElementById('export-structure').checked,
            data: document.getElementById('export-data').checked,
            drop_tables: document.getElementById('export-drop-tables').checked,
        };

        try {
            const response = await fetch('/api/admin/database/export.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(options),
            });

            // Check content type to determine if it's an error (JSON) or success (SQL file)
            const contentType = response.headers.get('content-type') || '';
            
            if (!response.ok || contentType.includes('application/json')) {
                // It's an error response (JSON)
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            // It's a successful file download (SQL)
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `database-export-${new Date().toISOString().split('T')[0]}.sql`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            statusDiv.textContent = '✅ Export completed! File downloaded.';
            statusDiv.className = 'mt-2 text-sm text-green-600';
            logContent.textContent += '✅ Export completed successfully!\n';
        } catch (error) {
            statusDiv.textContent = '❌ Export failed: ' + error.message;
            statusDiv.className = 'mt-2 text-sm text-red-600';
            logContent.textContent += '❌ Export failed: ' + error.message + '\n';
        } finally {
            exportBtn.disabled = false;
            exportBtn.textContent = 'Export Database';
        }
    });

    // Import database
    document.getElementById('import-btn').addEventListener('click', async () => {
        const fileInput = document.getElementById('import-file');
        const importBtn = document.getElementById('import-btn');
        const statusDiv = document.getElementById('import-status');
        const logDiv = document.getElementById('operation-log');
        const logContent = document.getElementById('log-content');

        if (!fileInput.files[0]) {
            alert('Please select a SQL file to import');
            return;
        }

        importBtn.disabled = true;
        importBtn.textContent = 'Importing...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Starting import...';
        logDiv.classList.remove('hidden');
        logContent.textContent = 'Starting database import to cPanel...\n';

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('create_backup', document.getElementById('import-backup').checked ? '1' : '0');

        try {
            const response = await fetch('/api/admin/database/import.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = '✅ Import completed successfully!';
                statusDiv.className = 'mt-2 text-sm text-green-600';
                logContent.textContent += '✅ Import completed successfully!\n';
                if (result.data.message) {
                    logContent.textContent += result.data.message + '\n';
                }
            } else {
                throw new Error(result.message || 'Import failed');
            }
        } catch (error) {
            statusDiv.textContent = '❌ Import failed: ' + error.message;
            statusDiv.className = 'mt-2 text-sm text-red-600';
            logContent.textContent += '❌ Import failed: ' + error.message + '\n';
        } finally {
            importBtn.disabled = false;
            importBtn.textContent = 'Import to cPanel';
        }
    });

    // Pull from cPanel
    document.getElementById('pull-btn').addEventListener('click', async () => {
        const pullBtn = document.getElementById('pull-btn');
        const statusDiv = document.getElementById('pull-status');
        const logDiv = document.getElementById('operation-log');
        const logContent = document.getElementById('log-content');

        // Confirm before pulling
        const confirmed = confirm(
            '⚠️ WARNING: This will overwrite your local database with data from cPanel!\n\n' +
            'This action cannot be undone. Make sure:\n' +
            '1. You have a backup of your local database\n' +
            '2. You want to proceed with the pull\n\n' +
            'Continue with pull?'
        );

        if (!confirmed) {
            return;
        }

        pullBtn.disabled = true;
        pullBtn.textContent = '⬇️ Pulling...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Starting pull from cPanel...';
        statusDiv.className = 'mt-3 text-sm text-blue-600';
        logDiv.classList.remove('hidden');
        logContent.textContent = 'Starting pull from cPanel to local...\n';

        const pullMode = document.querySelector('input[name="pull-mode"]:checked').value;
        const createBackup = document.getElementById('pull-backup').checked;

        try {
            const response = await fetch('/api/admin/database/pull.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pull_mode: pullMode,
                    create_backup: createBackup,
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = '✅ ' + result.data.message;
                statusDiv.className = 'mt-3 text-sm text-green-600 font-semibold';
                
                // Display detailed operation log
                if (result.data.log && Array.isArray(result.data.log)) {
                    result.data.log.forEach(logEntry => {
                        const icon = logEntry.status === 'success' ? '✓' : 
                                    logEntry.status === 'error' ? '✗' : 
                                    logEntry.status === 'warning' ? '⚠' : 'ℹ';
                        const color = logEntry.status === 'success' ? 'text-green-600' : 
                                     logEntry.status === 'error' ? 'text-red-600' : 
                                     logEntry.status === 'warning' ? 'text-yellow-600' : 'text-blue-600';
                        logContent.textContent += `${icon} [Step ${logEntry.step}] ${logEntry.message}\n`;
                    });
                } else {
                    logContent.textContent += '✅ Pull completed successfully!\n';
                    logContent.textContent += `   - Executed: ${result.data.executed} statements\n`;
                    logContent.textContent += `   - Mode: ${result.data.pull_mode === 'full' ? 'Full Pull' : 'Structure Only'}\n`;
                    if (result.data.errors > 0) {
                        logContent.textContent += `   - Errors: ${result.data.errors}\n`;
                    }
                }
                
                // Update last pull time
                const now = new Date();
                const formattedTime = now.getFullYear() + '-' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(now.getDate()).padStart(2, '0') + ' ' + 
                    String(now.getHours()).padStart(2, '0') + ':' + 
                    String(now.getMinutes()).padStart(2, '0') + ':' + 
                    String(now.getSeconds()).padStart(2, '0');
                document.getElementById('last-pull-time').textContent = formattedTime;
                document.getElementById('sync-status-warning').classList.add('hidden');
            } else {
                // Display error log if available
                if (result.context && result.context.log && Array.isArray(result.context.log)) {
                    result.context.log.forEach(logEntry => {
                        const icon = logEntry.status === 'success' ? '✓' : 
                                    logEntry.status === 'error' ? '✗' : 
                                    logEntry.status === 'warning' ? '⚠' : 'ℹ';
                        const color = logEntry.status === 'success' ? 'text-green-600' : 
                                     logEntry.status === 'error' ? 'text-red-600' : 
                                     logEntry.status === 'warning' ? 'text-yellow-600' : 'text-blue-600';
                        logContent.textContent += `${icon} [Step ${logEntry.step}] ${logEntry.message}\n`;
                    });
                }
                
                let errorMsg = result.message || 'Pull failed';
                if (result.context && result.context.suggestions) {
                    errorMsg += '\n\n💡 Suggestions:\n' + result.context.suggestions.map(s => '   • ' + s).join('\n');
                }
                throw new Error(errorMsg);
            }
        } catch (error) {
            statusDiv.textContent = '❌ Pull failed: ' + error.message;
            statusDiv.className = 'mt-3 text-sm text-red-600 font-semibold whitespace-pre-line';
            logContent.textContent += '❌ Pull failed: ' + error.message + '\n';
        } finally {
            pullBtn.disabled = false;
            pullBtn.textContent = '⬇️ Pull cPanel → Local Now';
        }
    });

    // Push to cPanel
    document.getElementById('auto-sync-btn').addEventListener('click', async () => {
        const syncBtn = document.getElementById('auto-sync-btn');
        const statusDiv = document.getElementById('auto-sync-status');
        const logDiv = document.getElementById('operation-log');
        const logContent = document.getElementById('log-content');

        // Check if pull was done recently
        const lastPullTime = document.getElementById('last-pull-time').textContent;
        const warningDiv = document.getElementById('sync-status-warning');
        
        if (lastPullTime === 'Never' || lastPullTime.includes('Never')) {
            const proceed = confirm(
                '⚠️ WARNING: You haven\'t pulled from cPanel yet!\n\n' +
                'Best practice: Pull latest data from cPanel first to avoid overwriting client updates.\n\n' +
                'Do you want to:\n' +
                '1. Cancel and pull first (recommended)\n' +
                '2. Proceed with push anyway (not recommended)\n\n' +
                'Proceed with push?'
            );
            
            if (!proceed) {
                return;
            }
        }

        // Confirm before syncing
        const confirmed = confirm(
            '⚠️ WARNING: This will overwrite your cPanel database with your local database!\n\n' +
            'This action cannot be undone. Make sure:\n' +
            '1. You have pulled latest data from cPanel\n' +
            '2. You have a backup of cPanel database\n' +
            '3. You want to proceed with the push\n\n' +
            'Continue with push?'
        );

        if (!confirmed) {
            return;
        }

        syncBtn.disabled = true;
        syncBtn.textContent = '🔄 Syncing...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Starting sync...';
        statusDiv.className = 'mt-3 text-sm text-blue-600';
        logDiv.classList.remove('hidden');
        logContent.textContent = 'Starting automatic sync from local to cPanel...\n';

        const syncMode = document.querySelector('input[name="sync-mode"]:checked').value;
        const createBackup = document.getElementById('auto-sync-backup').checked;

        try {
            const response = await fetch('/api/admin/database/sync.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sync_mode: syncMode,
                    create_backup: createBackup,
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = '✅ ' + result.data.message;
                statusDiv.className = 'mt-3 text-sm text-green-600 font-semibold';
                
                // Display detailed operation log
                if (result.data.log && Array.isArray(result.data.log)) {
                    result.data.log.forEach(logEntry => {
                        const icon = logEntry.status === 'success' ? '✓' : 
                                    logEntry.status === 'error' ? '✗' : 
                                    logEntry.status === 'warning' ? '⚠' : 'ℹ';
                        const color = logEntry.status === 'success' ? 'text-green-600' : 
                                     logEntry.status === 'error' ? 'text-red-600' : 
                                     logEntry.status === 'warning' ? 'text-yellow-600' : 'text-blue-600';
                        logContent.textContent += `${icon} [Step ${logEntry.step}] ${logEntry.message}\n`;
                    });
                } else {
                    logContent.textContent += '✅ Push completed successfully!\n';
                    logContent.textContent += `   - Executed: ${result.data.executed} statements\n`;
                    logContent.textContent += `   - Mode: ${result.data.sync_mode === 'full' ? 'Full Push' : 'Structure Only'}\n`;
                    if (result.data.errors > 0) {
                        logContent.textContent += `   - Errors: ${result.data.errors}\n`;
                    }
                    if (result.data.message) {
                        logContent.textContent += '   - ' + result.data.message + '\n';
                    }
                }
                
                // Update last push time
                const now = new Date();
                const formattedTime = now.getFullYear() + '-' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(now.getDate()).padStart(2, '0') + ' ' + 
                    String(now.getHours()).padStart(2, '0') + ':' + 
                    String(now.getMinutes()).padStart(2, '0') + ':' + 
                    String(now.getSeconds()).padStart(2, '0');
                document.getElementById('last-push-time').textContent = formattedTime;
            } else {
                // Display error log if available
                if (result.context && result.context.log && Array.isArray(result.context.log)) {
                    result.context.log.forEach(logEntry => {
                        const icon = logEntry.status === 'success' ? '✓' : 
                                    logEntry.status === 'error' ? '✗' : 
                                    logEntry.status === 'warning' ? '⚠' : 'ℹ';
                        const color = logEntry.status === 'success' ? 'text-green-600' : 
                                     logEntry.status === 'error' ? 'text-red-600' : 
                                     logEntry.status === 'warning' ? 'text-yellow-600' : 'text-blue-600';
                        logContent.textContent += `${icon} [Step ${logEntry.step}] ${logEntry.message}\n`;
                    });
                }
                
                let errorMsg = result.message || 'Sync failed';
                if (result.context && result.context.suggestions) {
                    errorMsg += '\n\n💡 Suggestions:\n' + result.context.suggestions.map(s => '   • ' + s).join('\n');
                }
                throw new Error(errorMsg);
            }
        } catch (error) {
            statusDiv.textContent = '❌ Sync failed: ' + error.message;
            statusDiv.className = 'mt-3 text-sm text-red-600 font-semibold whitespace-pre-line';
            logContent.textContent += '❌ Sync failed: ' + error.message + '\n';
        } finally {
            syncBtn.disabled = false;
            syncBtn.textContent = '⬆️ Push Local → cPanel Now';
        }
    });

    // Compare databases
    document.getElementById('compare-btn').addEventListener('click', async () => {
        const compareBtn = document.getElementById('compare-btn');
        const statusDiv = document.getElementById('compare-status');
        const reportDiv = document.getElementById('comparison-report');
        const summaryDiv = document.getElementById('report-summary');
        const tablesDiv = document.getElementById('report-tables');
        const dataDiv = document.getElementById('report-data');

        compareBtn.disabled = true;
        compareBtn.textContent = '📊 Comparing...';
        statusDiv.classList.remove('hidden');
        statusDiv.textContent = 'Comparing databases...';
        statusDiv.className = 'mb-4 text-sm text-blue-600';
        reportDiv.classList.add('hidden');

        try {
            const response = await fetch('/api/admin/database/compare.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            });

            const result = await response.json();

            if (result.status === 'success') {
                const report = result.data;
                statusDiv.textContent = '✅ Comparison completed!';
                statusDiv.className = 'mb-4 text-sm text-green-600 font-semibold';
                reportDiv.classList.remove('hidden');

                // Display Summary
                const summary = report.summary;
                summaryDiv.innerHTML = `
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">📊 Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded">
                            <div class="text-2xl font-bold text-blue-600">${summary.local_tables}</div>
                            <div class="text-xs text-gray-600">Local Tables</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded">
                            <div class="text-2xl font-bold text-green-600">${summary.cpanel_tables}</div>
                            <div class="text-xs text-gray-600">cPanel Tables</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded">
                            <div class="text-2xl font-bold text-purple-600">${summary.common_tables}</div>
                            <div class="text-xs text-gray-600">Common Tables</div>
                        </div>
                        <div class="text-center p-3 bg-yellow-50 rounded">
                            <div class="text-2xl font-bold text-yellow-600">${summary.total_updated_records}</div>
                            <div class="text-xs text-gray-600">Updated Records</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded">
                            <div class="text-2xl font-bold text-red-600">${summary.total_new_records_local + summary.total_new_records_cpanel}</div>
                            <div class="text-xs text-gray-600">New Records</div>
                        </div>
                    </div>
                `;

                // Display Tables Comparison
                let tablesHtml = '<h3 class="text-lg font-semibold text-gray-900 mb-3">📋 Tables Comparison</h3>';
                
                if (report.tables.new_in_local.length > 0) {
                    tablesHtml += `<div class="mb-3"><span class="font-semibold text-blue-600">New in Local (${report.tables.new_in_local.length}):</span> `;
                    tablesHtml += report.tables.new_in_local.map(t => `<span class="px-2 py-1 bg-blue-100 rounded text-sm">${t}</span>`).join(' ');
                    tablesHtml += '</div>';
                }
                
                if (report.tables.new_in_cpanel.length > 0) {
                    tablesHtml += `<div class="mb-3"><span class="font-semibold text-green-600">New in cPanel (${report.tables.new_in_cpanel.length}):</span> `;
                    tablesHtml += report.tables.new_in_cpanel.map(t => `<span class="px-2 py-1 bg-green-100 rounded text-sm">${t}</span>`).join(' ');
                    tablesHtml += '</div>';
                }
                
                if (report.tables.common.length > 0) {
                    tablesHtml += `<div><span class="font-semibold text-gray-600">Common Tables (${report.tables.common.length}):</span> `;
                    tablesHtml += report.tables.common.map(t => `<span class="px-2 py-1 bg-gray-100 rounded text-sm">${t}</span>`).join(' ');
                    tablesHtml += '</div>';
                }
                
                if (report.tables.new_in_local.length === 0 && report.tables.new_in_cpanel.length === 0) {
                    tablesHtml += '<p class="text-sm text-gray-600">✓ All tables exist in both databases</p>';
                }
                
                tablesDiv.innerHTML = tablesHtml;

                // Display Data Comparison
                let dataHtml = '<h3 class="text-lg font-semibold text-gray-900 mb-3">📝 Data Comparison</h3>';
                
                // New in Local
                if (report.data.new_in_local.length > 0) {
                    dataHtml += `<div class="mb-4"><h4 class="font-semibold text-blue-600 mb-2">New in Local (${report.data.new_in_local.length} records):</h4>`;
                    const groupedByTable = {};
                    report.data.new_in_local.forEach(item => {
                        if (!groupedByTable[item.table]) {
                            groupedByTable[item.table] = [];
                        }
                        groupedByTable[item.table].push(item);
                    });
                    Object.keys(groupedByTable).forEach(table => {
                        dataHtml += `<div class="ml-4 mb-2"><span class="font-medium">${table}:</span> ${groupedByTable[table].length} new records</div>`;
                    });
                    dataHtml += '</div>';
                }
                
                // New in cPanel
                if (report.data.new_in_cpanel.length > 0) {
                    dataHtml += `<div class="mb-4"><h4 class="font-semibold text-green-600 mb-2">New in cPanel (${report.data.new_in_cpanel.length} records):</h4>`;
                    const groupedByTable = {};
                    report.data.new_in_cpanel.forEach(item => {
                        if (!groupedByTable[item.table]) {
                            groupedByTable[item.table] = [];
                        }
                        groupedByTable[item.table].push(item);
                    });
                    Object.keys(groupedByTable).forEach(table => {
                        dataHtml += `<div class="ml-4 mb-2"><span class="font-medium">${table}:</span> ${groupedByTable[table].length} new records</div>`;
                    });
                    dataHtml += '</div>';
                }
                
                // Updated Records
                if (report.data.updated.length > 0) {
                    dataHtml += `<div class="mb-4"><h4 class="font-semibold text-yellow-600 mb-2">Updated Records (${report.data.updated.length}):</h4>`;
                    const groupedByTable = {};
                    report.data.updated.forEach(item => {
                        if (!groupedByTable[item.table]) {
                            groupedByTable[item.table] = [];
                        }
                        groupedByTable[item.table].push(item);
                    });
                    Object.keys(groupedByTable).forEach(table => {
                        dataHtml += `<div class="ml-4 mb-3">`;
                        dataHtml += `<div class="font-medium mb-1">${table} (${groupedByTable[table].length} records):</div>`;
                        groupedByTable[table].slice(0, 5).forEach(item => {
                            const changeFields = Object.keys(item.changes || {});
                            dataHtml += `<div class="ml-4 text-xs text-gray-600">ID: ${item.key} - Changed: ${changeFields.join(', ')}</div>`;
                        });
                        if (groupedByTable[table].length > 5) {
                            dataHtml += `<div class="ml-4 text-xs text-gray-500">... and ${groupedByTable[table].length - 5} more</div>`;
                        }
                        dataHtml += '</div>';
                    });
                    dataHtml += '</div>';
                }
                
                if (report.data.new_in_local.length === 0 && 
                    report.data.new_in_cpanel.length === 0 && 
                    report.data.updated.length === 0) {
                    dataHtml += '<p class="text-sm text-gray-600">✓ Databases are identical - no differences found</p>';
                }
                
                dataDiv.innerHTML = dataHtml;

            } else {
                throw new Error(result.message || 'Comparison failed');
            }
        } catch (error) {
            statusDiv.textContent = '❌ Comparison failed: ' + error.message;
            statusDiv.className = 'mb-4 text-sm text-red-600 font-semibold';
        } finally {
            compareBtn.disabled = false;
            compareBtn.textContent = '📊 Generate Comparison Report';
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

