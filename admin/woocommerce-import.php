<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Check if feature is enabled
$featureEnabled = $db->prepare("SELECT enabled FROM optional_features WHERE feature_key = 'woocommerce_csv_import'");
$featureEnabled->execute();
$isEnabled = $featureEnabled->fetchColumn() == 1;

if (!$isEnabled) {
    header('Location: /admin/optional-features.php?message=feature_disabled');
    exit;
}

$pageTitle = 'WooCommerce CSV Import';
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
        <h1 class="text-3xl font-semibold text-[#0b3a63]">WooCommerce CSV Import</h1>
        <p class="text-sm text-gray-600 mt-1">Import products from WooCommerce CSV export</p>
    </div>

    <!-- Instructions Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <span>📋</span>
            <span>How to Export from WooCommerce</span>
        </h2>
        <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
            <li>Go to your WordPress admin panel</li>
            <li>Navigate to <strong>WooCommerce → Products</strong></li>
            <li>Click <strong>Export</strong> button</li>
            <li>Select <strong>"Export all products"</strong> or choose specific products</li>
            <li>Click <strong>"Generate CSV"</strong></li>
            <li>Download the CSV file</li>
            <li>Upload it below to import into this system</li>
        </ol>
    </div>

    <!-- Import Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload CSV File</h2>
        
        <form id="import-form" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                    WooCommerce CSV File
                </label>
                <input 
                    type="file" 
                    id="csv_file" 
                    name="csv_file" 
                    accept=".csv,text/csv"
                    required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#0b3a63] file:text-white hover:file:bg-[#0a2d4f] cursor-pointer"
                >
                <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB. Supported format: CSV</p>
            </div>

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
                    <strong>Skip duplicate products</strong> (by SKU)
                    <span class="block text-xs text-gray-500 mt-1">If unchecked, duplicates will be updated</span>
                </label>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="import-btn"
                    class="admin-btn admin-btn-primary"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Start Import</span>
                </button>
            </div>
        </form>
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
document.getElementById('import-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
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
        const response = await fetch('/api/admin/woocommerce/import-csv.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        // Check if response is an error
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

