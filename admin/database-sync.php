<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

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

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-wide text-gray-500">Database Management</p>
        <h1 class="text-3xl font-semibold text-[#0b3a63]">Database Sync</h1>
        <p class="text-sm text-gray-600">Import/Export database between local development and cPanel production</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configuration Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Configuration</h2>
            <p class="text-sm text-gray-600 mb-6">Configure cPanel database connection settings</p>

            <form id="config-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">cPanel Database Host</label>
                    <input type="text" name="cpanel_host" value="<?php echo htmlspecialchars($cpanelConfig['host']); ?>" 
                           placeholder="localhost or cpanel_hostname" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                    <p class="text-xs text-gray-500 mt-1">Usually "localhost" for cPanel databases</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">cPanel Database Name</label>
                    <input type="text" name="cpanel_database" value="<?php echo htmlspecialchars($cpanelConfig['database']); ?>" 
                           placeholder="your_cpanel_database" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">cPanel Database Username</label>
                    <input type="text" name="cpanel_username" value="<?php echo htmlspecialchars($cpanelConfig['username']); ?>" 
                           placeholder="your_cpanel_db_user" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">cPanel Database Password</label>
                    <input type="password" name="cpanel_password" value="<?php echo htmlspecialchars($cpanelConfig['password']); ?>" 
                           placeholder="••••••••" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                    <p class="text-xs text-gray-500 mt-1">Stored securely in site options</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">cPanel Database Port</label>
                    <input type="number" name="cpanel_port" value="<?php echo htmlspecialchars($cpanelConfig['port']); ?>" 
                           placeholder="3306" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0b3a63] focus:border-[#0b3a63]">
                    <p class="text-xs text-gray-500 mt-1">Default: 3306 (MySQL/MariaDB)</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors font-semibold">
                        Save Configuration
                    </button>
                    <button type="button" id="test-connection-btn" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-semibold">
                        Test Connection
                    </button>
                </div>
                <div id="test-connection-status" class="mt-2 text-sm hidden"></div>
            </form>
        </div>

        <!-- Current Database Info -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Database</h2>
            <p class="text-sm text-gray-600 mb-6">Your local development database connection</p>

            <div class="space-y-3">
                <div>
                    <span class="text-xs font-medium text-gray-500">Host:</span>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($currentDbConfig['host']); ?></p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500">Database:</span>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($currentDbConfig['database']); ?></p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500">Username:</span>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($currentDbConfig['username']); ?></p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500">Port:</span>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($currentDbConfig['port']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto Sync Card -->
    <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-2 border-blue-200 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">🚀</span>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Auto Sync to cPanel</h2>
                <p class="text-sm text-gray-600">One-click sync: Push all local database changes to cPanel production</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 mb-4 border border-blue-100">
            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="radio" name="sync-mode" value="full" checked class="mr-2">
                    <div>
                        <span class="text-sm font-medium text-gray-900">Full Sync</span>
                        <p class="text-xs text-gray-500">Sync structure + all data (recommended for development)</p>
                    </div>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="sync-mode" value="structure_only" class="mr-2">
                    <div>
                        <span class="text-sm font-medium text-gray-900">Structure Only</span>
                        <p class="text-xs text-gray-500">Sync only table structure (no data - safer for production)</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4 mb-4">
            <label class="flex items-center">
                <input type="checkbox" id="auto-sync-backup" checked class="mr-2">
                <span class="text-sm text-gray-700">Create backup before sync</span>
            </label>
        </div>

        <button type="button" id="auto-sync-btn" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-md hover:from-blue-700 hover:to-indigo-700 transition-all font-semibold text-lg shadow-lg">
            🔄 Sync Local → cPanel Now
        </button>
        <div id="auto-sync-status" class="mt-3 text-sm hidden"></div>
        
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
            <p class="text-xs text-yellow-800">
                <strong>⚠️ Warning:</strong> This will overwrite the cPanel database with your local database. 
                Make sure you have a backup and that your local database is up-to-date.
            </p>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Manual Operations</h2>
        <p class="text-sm text-gray-600 mb-6">Export from local or import SQL file to cPanel</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Export Section -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">📤 Export Database</h3>
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

                <button type="button" id="export-btn" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-semibold">
                    Export Database
                </button>
                <div id="export-status" class="mt-2 text-sm hidden"></div>
            </div>

            <!-- Import Section -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">📥 Import to cPanel</h3>
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

                <button type="button" id="import-btn" class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-semibold">
                    Import to cPanel
                </button>
                <div id="import-status" class="mt-2 text-sm hidden"></div>
            </div>
        </div>
    </div>

    <!-- Status/Log Area -->
    <div id="operation-log" class="mt-6 bg-gray-50 rounded-lg border border-gray-200 p-4 hidden">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Operation Log</h3>
        <div id="log-content" class="text-sm text-gray-700 font-mono whitespace-pre-wrap max-h-64 overflow-y-auto"></div>
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

    // Auto Sync to cPanel
    document.getElementById('auto-sync-btn').addEventListener('click', async () => {
        const syncBtn = document.getElementById('auto-sync-btn');
        const statusDiv = document.getElementById('auto-sync-status');
        const logDiv = document.getElementById('operation-log');
        const logContent = document.getElementById('log-content');

        // Confirm before syncing
        const confirmed = confirm(
            '⚠️ WARNING: This will overwrite your cPanel database with your local database!\n\n' +
            'This action cannot be undone. Make sure:\n' +
            '1. Your local database is up-to-date\n' +
            '2. You have a backup of cPanel database\n' +
            '3. You want to proceed with the sync\n\n' +
            'Continue with sync?'
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
                logContent.textContent += '✅ Sync completed successfully!\n';
                logContent.textContent += `   - Executed: ${result.data.executed} statements\n`;
                logContent.textContent += `   - Mode: ${result.data.sync_mode === 'full' ? 'Full Sync' : 'Structure Only'}\n`;
                if (result.data.errors > 0) {
                    logContent.textContent += `   - Errors: ${result.data.errors}\n`;
                }
                if (result.data.message) {
                    logContent.textContent += '   - ' + result.data.message + '\n';
                }
            } else {
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
            syncBtn.textContent = '🔄 Sync Local → cPanel Now';
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

