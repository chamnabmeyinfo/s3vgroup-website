<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Handle sitemap generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_sitemap') {
    $baseUrl = $_POST['base_url'] ?? 'https://s3vgroup.com';
    
    // Get all pages
    $pages = $db->query("SELECT slug, updatedAt FROM pages WHERE status = 'PUBLISHED'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all products
    $products = $db->query("SELECT slug, updatedAt FROM products WHERE status = 'PUBLISHED'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all categories
    $categories = $db->query("SELECT slug, updatedAt FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Homepage
    $sitemap .= "  <url>\n";
    $sitemap .= "    <loc>{$baseUrl}/</loc>\n";
    $sitemap .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $sitemap .= "    <changefreq>daily</changefreq>\n";
    $sitemap .= "    <priority>1.0</priority>\n";
    $sitemap .= "  </url>\n";
    
    // Pages
    foreach ($pages as $page) {
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>{$baseUrl}/page.php?slug=" . urlencode($page['slug']) . "</loc>\n";
        $sitemap .= "    <lastmod>" . date('Y-m-d', strtotime($page['updatedAt'])) . "</lastmod>\n";
        $sitemap .= "    <changefreq>weekly</changefreq>\n";
        $sitemap .= "    <priority>0.8</priority>\n";
        $sitemap .= "  </url>\n";
    }
    
    // Products
    foreach ($products as $product) {
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>{$baseUrl}/product.php?slug=" . urlencode($product['slug']) . "</loc>\n";
        $sitemap .= "    <lastmod>" . date('Y-m-d', strtotime($product['updatedAt'])) . "</lastmod>\n";
        $sitemap .= "    <changefreq>weekly</changefreq>\n";
        $sitemap .= "    <priority>0.9</priority>\n";
        $sitemap .= "  </url>\n";
    }
    
    // Categories
    foreach ($categories as $category) {
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>{$baseUrl}/category.php?slug=" . urlencode($category['slug']) . "</loc>\n";
        $sitemap .= "    <lastmod>" . date('Y-m-d', strtotime($category['updatedAt'])) . "</lastmod>\n";
        $sitemap .= "    <changefreq>weekly</changefreq>\n";
        $sitemap .= "    <priority>0.7</priority>\n";
        $sitemap .= "  </url>\n";
    }
    
    $sitemap .= '</urlset>';
    
    // Save to file
    file_put_contents(__DIR__ . '/../sitemap.xml', $sitemap);
    
    $sitemapGenerated = true;
}

// Get site options for SEO
$siteOptions = $db->query("SELECT key_name, value FROM site_options WHERE key_name LIKE 'seo_%'")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'SEO Tools';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Optimization</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">SEO Tools</h1>
            <p class="text-sm text-gray-600">Manage SEO settings and generate sitemaps</p>
        </div>
    </div>

    <?php if (isset($sitemapGenerated)): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-green-800">
                <span class="text-xl">✓</span>
                <span class="font-medium">Sitemap generated successfully!</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sitemap Generator -->
    <div class="admin-card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🗺️ XML Sitemap Generator</h2>
        <p class="text-sm text-gray-600 mb-4">Generate an XML sitemap to help search engines index your website.</p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="generate_sitemap">
            
            <div class="admin-form-group">
                <label class="admin-form-label">Base URL</label>
                <input type="url" name="base_url" class="admin-form-input" value="https://s3vgroup.com" required>
                <p class="text-xs text-gray-500 mt-1">Your website's full URL (with https://)</p>
            </div>
            
            <button type="submit" class="admin-btn admin-btn-primary">
                Generate Sitemap
            </button>
        </form>
        
        <?php if (file_exists(__DIR__ . '/../sitemap.xml')): ?>
            <div class="mt-4 p-3 bg-gray-50 rounded">
                <div class="text-sm text-gray-700 mb-2">
                    <strong>Sitemap Location:</strong> <code class="bg-white px-2 py-1 rounded">/sitemap.xml</code>
                </div>
                <div class="text-sm text-gray-700 mb-2">
                    <strong>Last Generated:</strong> <?php echo date('F d, Y H:i', filemtime(__DIR__ . '/../sitemap.xml')); ?>
                </div>
                <div class="text-xs text-gray-600">
                    Submit to Google Search Console: <a href="https://search.google.com/search-console" target="_blank" class="text-[#0b3a63] hover:underline">Google Search Console</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SEO Meta Tags -->
    <div class="admin-card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">📝 SEO Meta Tags</h2>
        <p class="text-sm text-gray-600 mb-4">Configure default meta tags for your website.</p>
        
        <div class="space-y-4">
            <div class="admin-form-group">
                <label class="admin-form-label">Meta Title</label>
                <input type="text" class="admin-form-input" value="<?php echo e($siteOptions['seo_title'] ?? ''); ?>" id="seo-title">
                <p class="text-xs text-gray-500 mt-1">Default title for pages without specific meta title</p>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label">Meta Description</label>
                <textarea class="admin-form-textarea" rows="3" id="seo-description"><?php echo e($siteOptions['seo_description'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Default description for search engines (150-160 characters recommended)</p>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label">Meta Keywords</label>
                <input type="text" class="admin-form-input" value="<?php echo e($siteOptions['seo_keywords'] ?? ''); ?>" id="seo-keywords">
                <p class="text-xs text-gray-500 mt-1">Comma-separated keywords</p>
            </div>
            
            <button type="button" class="admin-btn admin-btn-primary" onclick="saveSEOSettings()">
                Save SEO Settings
            </button>
        </div>
    </div>

    <!-- Robots.txt Editor -->
    <div class="admin-card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🤖 Robots.txt Editor</h2>
        <p class="text-sm text-gray-600 mb-4">Configure how search engines crawl your website.</p>
        
        <?php
        $robotsFile = __DIR__ . '/../robots.txt';
        $robotsContent = file_exists($robotsFile) ? file_get_contents($robotsFile) : "User-agent: *\nAllow: /\n\nSitemap: https://s3vgroup.com/sitemap.xml";
        ?>
        
        <form method="POST" action="/api/admin/seo/save-robots.php" class="space-y-4">
            <div class="admin-form-group">
                <label class="admin-form-label">Robots.txt Content</label>
                <textarea name="content" class="admin-form-textarea font-mono text-sm" rows="10"><?php echo e($robotsContent); ?></textarea>
            </div>
            
            <button type="submit" class="admin-btn admin-btn-primary">
                Save Robots.txt
            </button>
        </form>
    </div>
</div>

<script>
async function saveSEOSettings() {
    const data = {
        seo_title: document.getElementById('seo-title').value,
        seo_description: document.getElementById('seo-description').value,
        seo_keywords: document.getElementById('seo-keywords').value,
    };
    
    try {
        const response = await fetch('/api/admin/seo/save-meta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('SEO settings saved successfully!');
        } else {
            alert('Error: ' + (result.message || 'Failed to save'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

