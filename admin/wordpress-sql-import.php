<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Check if feature is enabled
$featureEnabled = $db->prepare("SELECT enabled FROM optional_features WHERE feature_key = 'wordpress_sql_import'");
$featureEnabled->execute();
$isEnabled = $featureEnabled->fetchColumn() == 1;

if (!$isEnabled) {
    header('Location: /admin/optional-features.php?message=feature_disabled');
    exit;
}

$pageTitle = 'WordPress SQL Import';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a href="/admin/optional-features.php" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <span class="text-sm text-gray-500">Optional Features</span>
        </div>
        <h1 class="text-3xl font-semibold text-[#0b3a63]">WordPress SQL Import</h1>
        <p class="text-sm text-gray-600 mt-1">Import products directly from WordPress/WooCommerce database</p>
    </div>

    <!-- Instructions Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <span>📋</span>
            <span>WordPress Database Connection</span>
        </h2>
        <div class="text-sm text-blue-800 space-y-2">
            <p>This method connects directly to your WordPress database to import products. You'll need:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>WordPress database host (usually <code>localhost</code> or your server IP)</li>
                <li>WordPress database name</li>
                <li>WordPress database username</li>
                <li>WordPress database password</li>
                <li>WordPress table prefix (usually <code>wp_</code>)</li>
            </ul>
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="font-semibold text-yellow-900 mb-2">⚠️ Important Notes:</p>
                <ul class="text-xs text-yellow-800 space-y-1 list-disc list-inside">
                    <li>For <strong>remote connections</strong>, the database user must have remote access permissions</li>
                    <li>Your hosting provider may need to whitelist your server IP</li>
                    <li>Some hosts block remote MySQL connections - check with your hosting provider</li>
                    <li>If connecting to cPanel, use the database host from cPanel (not localhost)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Connection Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">WordPress Database Configuration</h2>
        
        <form id="connection-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="wp_host" class="block text-sm font-medium text-gray-700 mb-2">
                        WordPress Database Host *
                    </label>
                    <input 
                        type="text" 
                        id="wp_host" 
                        name="wp_host" 
                        value="localhost"
                        required
                        class="admin-input"
                        placeholder="localhost or IP address"
                    >
                    <p class="mt-1 text-xs text-gray-500">Use <code>localhost</code> if WordPress is on the same server</p>
                </div>

                <div>
                    <label for="wp_database" class="block text-sm font-medium text-gray-700 mb-2">
                        WordPress Database Name *
                    </label>
                    <input 
                        type="text" 
                        id="wp_database" 
                        name="wp_database" 
                        required
                        class="admin-input"
                        placeholder="wordpress_db"
                    >
                </div>

                <div>
                    <label for="wp_username" class="block text-sm font-medium text-gray-700 mb-2">
                        WordPress Database Username *
                    </label>
                    <input 
                        type="text" 
                        id="wp_username" 
                        name="wp_username" 
                        required
                        class="admin-input"
                        placeholder="db_user"
                    >
                </div>

                <div>
                    <label for="wp_password" class="block text-sm font-medium text-gray-700 mb-2">
                        WordPress Database Password *
                    </label>
                    <input 
                        type="password" 
                        id="wp_password" 
                        name="wp_password" 
                        required
                        class="admin-input"
                        placeholder="••••••••"
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="wp_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                        WordPress Table Prefix *
                    </label>
                    <input 
                        type="text" 
                        id="wp_prefix" 
                        name="wp_prefix" 
                        value="wpg1_"
                        required
                        class="admin-input"
                        placeholder="wp_"
                    >
                    <p class="mt-1 text-xs text-gray-500">Usually "wp_" but can be different (e.g., "wpg1_")</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 flex flex-wrap gap-3">
                <button 
                    type="button" 
                    id="save-config-btn"
                    class="admin-btn admin-btn-secondary"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    <span>Save Configuration</span>
                </button>
                <button 
                    type="button" 
                    id="load-config-btn"
                    class="admin-btn admin-btn-secondary"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Load Saved</span>
                </button>
                <button 
                    type="button" 
                    id="test-connection-btn"
                    class="admin-btn admin-btn-secondary"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Test Connection</span>
                </button>
                <button 
                    type="submit" 
                    id="import-btn"
                    class="admin-btn admin-btn-primary"
                    disabled
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Start Import</span>
                </button>
            </div>
        </form>

        <div id="connection-status" class="mt-4 hidden"></div>
    </div>

    <!-- Import Options -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Options</h2>
        
        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <input 
                    type="checkbox" 
                    id="download_images" 
                    name="download_images" 
                    value="1"
                    checked
                    class="mt-1"
                >
                <label for="download_images" class="text-sm text-gray-700">
                    <strong>Download & optimize product images</strong> to local server
                    <span class="block text-xs text-gray-500 mt-1">
                        Images will be automatically resized (max 1920x1920px) and optimized to reduce file size
                    </span>
                </label>
            </div>

            <div class="flex items-start gap-3">
                <input 
                    type="checkbox" 
                    id="create_categories" 
                    name="create_categories" 
                    value="1"
                    checked
                    class="mt-1"
                >
                <label for="create_categories" class="text-sm text-gray-700">
                    <strong>Create missing categories</strong> automatically
                    <span class="block text-xs text-gray-500 mt-1">If unchecked, products without matching categories will be skipped</span>
                </label>
            </div>

            <div class="flex items-start gap-3">
                <input 
                    type="checkbox" 
                    id="skip_duplicates" 
                    name="skip_duplicates" 
                    value="1"
                    checked
                    class="mt-1"
                >
                <label for="skip_duplicates" class="text-sm text-gray-700">
                    <strong>Skip duplicate products</strong>
                    <span class="block text-xs text-gray-500 mt-1">
                        Products are checked by SKU, slug, and name. Duplicates will be skipped automatically.
                    </span>
                </label>
            </div>

            <div class="flex items-start gap-3">
                <input 
                    type="checkbox" 
                    id="import_variations" 
                    name="import_variations" 
                    value="1"
                    class="mt-1"
                >
                <label for="import_variations" class="text-sm text-gray-700">
                    <strong>Import product variations</strong> as separate products
                    <span class="block text-xs text-gray-500 mt-1">WooCommerce variable products will be split into individual products</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div id="progress-section" class="hidden bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Progress</h2>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Processing...</span>
                    <span id="progress-text">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progress-bar" class="bg-[#0b3a63] h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            <div id="progress-log" class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto text-sm font-mono">
                <div class="text-gray-500">Waiting to start...</div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div id="results-section" class="hidden bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Results</h2>
        <div id="results-content" class="space-y-2"></div>
    </div>
</div>

<script>
// Load saved configuration on page load
(async function() {
    try {
        const response = await fetch('/api/admin/wordpress/load-config.php', {
            credentials: 'same-origin'
        });
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            alert('Failed to load: Server returned non-JSON response.');
            return;
        }
        
        let data;
        try {
            data = await response.json();
        } catch (e) {
            const text = await response.text();
            console.error('JSON parse error:', e, text.substring(0, 500));
            alert('Failed to parse response.');
            return;
        }
        
        // Check both response formats
        const config = data.data?.config || data.config;
        if ((data.status === 'success' || data.success) && config) {
            // Fill form with saved configuration
            document.getElementById('wp_host').value = config.host || '';
            document.getElementById('wp_database').value = config.database || '';
            document.getElementById('wp_username').value = config.username || '';
            document.getElementById('wp_password').value = config.password || '';
            document.getElementById('wp_prefix').value = config.prefix || 'wp_';
        }
    } catch (error) {
        console.error('Failed to load saved configuration:', error);
    }
})();

// Save configuration
document.getElementById('save-config-btn').addEventListener('click', async function() {
    const form = document.getElementById('connection-form');
    const formData = new FormData(form);
    const saveBtn = this;
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="admin-loading-spinner"></span> Saving...';
    
    try {
        const response = await fetch('/api/admin/wordpress/save-config.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            alert('Failed to save: Server returned non-JSON response. Check console for details.');
            return;
        }
        
        let data;
        try {
            data = await response.json();
        } catch (e) {
            const text = await response.text();
            console.error('JSON parse error:', e, text.substring(0, 500));
            alert('Failed to parse response. Check console for details.');
            return;
        }
        
        // Check both response formats
        if (data.status === 'success' || data.success) {
            // Show success message
            const statusDiv = document.getElementById('connection-status');
            statusDiv.classList.remove('hidden');
            statusDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-800">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">Configuration saved successfully!</span>
                    </div>
                    <div class="text-sm text-green-700 mt-2">Your database credentials have been saved. You can load them anytime using the "Load Saved" button.</div>
                </div>
            `;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        } else {
            const errorMsg = data.message || data.data?.message || 'Unknown error';
            alert('Failed to save configuration: ' + errorMsg);
            console.error('Save config error:', data);
        }
    } catch (error) {
        alert('Error saving configuration: ' + error.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            <span>Save Configuration</span>
        `;
    }
});

// Load saved configuration
document.getElementById('load-config-btn').addEventListener('click', async function() {
    const loadBtn = this;
    
    loadBtn.disabled = true;
    loadBtn.innerHTML = '<span class="admin-loading-spinner"></span> Loading...';
    
    try {
        const response = await fetch('/api/admin/wordpress/load-config.php', {
            credentials: 'same-origin'
        });
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            alert('Failed to load: Server returned non-JSON response.');
            return;
        }
        
        let data;
        try {
            data = await response.json();
        } catch (e) {
            const text = await response.text();
            console.error('JSON parse error:', e, text.substring(0, 500));
            alert('Failed to parse response.');
            return;
        }
        
        // Check both response formats
        const config = data.data?.config || data.config;
        if ((data.status === 'success' || data.success) && config) {
            // Fill form with saved configuration
            document.getElementById('wp_host').value = config.host || '';
            document.getElementById('wp_database').value = config.database || '';
            document.getElementById('wp_username').value = config.username || '';
            document.getElementById('wp_password').value = config.password || '';
            document.getElementById('wp_prefix').value = config.prefix || 'wp_';
            
            // Show success message
            const statusDiv = document.getElementById('connection-status');
            statusDiv.classList.remove('hidden');
            statusDiv.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-blue-800">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">Configuration loaded!</span>
                    </div>
                    <div class="text-sm text-blue-700 mt-2">Saved database credentials have been loaded into the form.</div>
                </div>
            `;
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 3000);
        } else {
            alert('No saved configuration found.');
        }
    } catch (error) {
        alert('Error loading configuration: ' + error.message);
    } finally {
        loadBtn.disabled = false;
        loadBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <span>Load Saved</span>
        `;
    }
});

// Test connection
document.getElementById('test-connection-btn').addEventListener('click', async function() {
    const form = document.getElementById('connection-form');
    const formData = new FormData(form);
    const statusDiv = document.getElementById('connection-status');
    const importBtn = document.getElementById('import-btn');
    const testBtn = this;
    
    testBtn.disabled = true;
    testBtn.innerHTML = '<span class="admin-loading-spinner"></span> Testing...';
    statusDiv.classList.remove('hidden');
    statusDiv.innerHTML = '<div class="text-blue-600">🔄 Testing connection...</div>';
    
    try {
        const response = await fetch('/api/admin/wordpress/test-connection.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            statusDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-red-800 font-semibold">Connection Error</div>
                    <div class="text-sm text-red-700 mt-2">Server returned non-JSON response. Check browser console for details.</div>
                </div>
            `;
            importBtn.disabled = true;
            return;
        }
        
        const data = await response.json();
        
        // Check both 'success' and 'status === success' for compatibility
        if (data.status === 'success' || data.success) {
            const stats = data.data?.stats || data.stats || {};
            const wpVersion = data.data?.wp_version || data.wp_version || 'Unknown';
            
            statusDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-800">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">Connection Successful!</span>
                    </div>
                    <div class="text-sm text-green-700 mt-2">
                        <div>Found <strong>${stats.products || 0}</strong> products (type: ${stats.product_type || 'product'})</div>
                        <div>Found <strong>${stats.categories || 0}</strong> categories (taxonomy: ${stats.category_taxonomy || 'product_cat'})</div>
                        <div>Total posts in database: <strong>${stats.total_posts || 0}</strong></div>
                        <div>WordPress version: ${wpVersion}</div>
                        ${stats.available_post_types && stats.available_post_types.length > 0 ? 
                            `<div class="mt-2 text-xs text-gray-600">Available post types: ${stats.available_post_types.join(', ')}</div>` : ''}
                    </div>
                </div>
            `;
            importBtn.disabled = false;
        } else {
            const errorMessage = data.message || data.data?.message || 'Unknown error';
            const errorDetails = data.context?.error || data.error || '';
            const suggestions = data.context?.suggestions || data.suggestions || [];
            
            let errorHtml = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-red-800 mb-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">Connection Failed</span>
                    </div>
                    <div class="text-sm text-red-700 font-medium mb-2">${errorMessage}</div>
            `;
            
            if (errorDetails) {
                errorHtml += `<div class="text-xs text-red-600 font-mono bg-red-100 p-2 rounded mb-2">${errorDetails}</div>`;
            }
            
            if (suggestions && suggestions.length > 0) {
                errorHtml += `
                    <div class="mt-3 pt-3 border-t border-red-200">
                        <div class="text-xs font-semibold text-red-800 mb-2">💡 Troubleshooting Tips:</div>
                        <ul class="text-xs text-red-700 space-y-1 list-disc list-inside">
                `;
                suggestions.forEach(suggestion => {
                    errorHtml += `<li>${suggestion}</li>`;
                });
                errorHtml += `</ul></div>`;
            }
            
            errorHtml += `</div>`;
            statusDiv.innerHTML = errorHtml;
            importBtn.disabled = true;
        }
    } catch (error) {
        statusDiv.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-800 font-semibold">Connection Error</div>
                <div class="text-sm text-red-700 mt-2">${error.message}</div>
            </div>
        `;
        importBtn.disabled = true;
    } finally {
        testBtn.disabled = false;
        testBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Test Connection</span>
        `;
    }
});

// Import form submission
document.getElementById('connection-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Add import options
    formData.append('download_images', document.getElementById('download_images').checked ? '1' : '0');
    formData.append('create_categories', document.getElementById('create_categories').checked ? '1' : '0');
    formData.append('skip_duplicates', document.getElementById('skip_duplicates').checked ? '1' : '0');
    formData.append('import_variations', document.getElementById('import_variations').checked ? '1' : '0');
    
    const importBtn = document.getElementById('import-btn');
    const progressSection = document.getElementById('progress-section');
    const resultsSection = document.getElementById('results-section');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressLog = document.getElementById('progress-log');
    
    // Reset UI
    importBtn.disabled = true;
    importBtn.innerHTML = '<span class="admin-loading-spinner"></span> Importing...';
    progressSection.classList.remove('hidden');
    resultsSection.classList.add('hidden');
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    progressLog.innerHTML = '<div class="text-gray-500">Starting import...</div>';
    
    try {
        const response = await fetch('/api/admin/wordpress/import-sql.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            progressLog.innerHTML += `<div class="text-red-600">❌ ${errorData.message || 'Import failed'}</div>`;
            importBtn.disabled = false;
            importBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                <span>Try Again</span>
            `;
            return;
        }
        
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        
        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';
            
            for (const line of lines) {
                if (!line.trim()) continue;
                
                try {
                    const data = JSON.parse(line);
                    
                    if (data.type === 'progress') {
                        progressBar.style.width = data.percent + '%';
                        progressText.textContent = data.percent + '%';
                    }
                    
                    if (data.type === 'log') {
                        const logEntry = document.createElement('div');
                        logEntry.className = data.level === 'error' ? 'text-red-600' : 
                                            data.level === 'success' ? 'text-green-600' : 'text-gray-700';
                        logEntry.textContent = data.message;
                        progressLog.appendChild(logEntry);
                        progressLog.scrollTop = progressLog.scrollHeight;
                    }
                    
                    if (data.type === 'complete') {
                        progressBar.style.width = '100%';
                        progressText.textContent = '100%';
                        
                        // Show results
                        const resultsContent = document.getElementById('results-content');
                        resultsContent.innerHTML = `
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 text-green-800 mb-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-semibold">Import Completed!</span>
                                </div>
                                <div class="text-sm text-green-700 space-y-1">
                                    <div>✅ Imported: ${data.stats.imported} products</div>
                                    <div>⚠️ Skipped: ${data.stats.skipped} products</div>
                                    <div>❌ Errors: ${data.stats.errors} products</div>
                                    <div>📦 Categories: ${data.stats.categories} created</div>
                                </div>
                            </div>
                        `;
                        resultsSection.classList.remove('hidden');
                        
                        importBtn.disabled = false;
                        importBtn.innerHTML = `
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span>Import Again</span>
                        `;
                    }
                } catch (e) {
                    console.error('Parse error:', e, line);
                }
            }
        }
    } catch (error) {
        console.error('Import error:', error);
        progressLog.innerHTML += `<div class="text-red-600">❌ Import failed: ${error.message}</div>`;
        importBtn.disabled = false;
        importBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            <span>Try Again</span>
        `;
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

