<?php
/**
 * API Files Verification Tool
 * Check if WordPress SQL Import API files are uploaded to server
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/site.php';

$pageTitle = 'API Files Check';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo $siteConfig['name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">WordPress SQL Import - API Files Check</h1>
        
        <div class="space-y-4">
            <?php
            $basePath = __DIR__ . '/../api/admin/wordpress/';
            $files = [
                'test-connection.php' => 'Tests WordPress database connection',
                'import-sql.php' => 'Imports products from WordPress database',
                'save-config.php' => 'Saves WordPress database credentials',
                'load-config.php' => 'Loads saved WordPress database credentials',
            ];
            
            $allExist = true;
            
            foreach ($files as $file => $description) {
                $filePath = $basePath . $file;
                $exists = file_exists($filePath);
                $allExist = $allExist && $exists;
                
                $url = '/api/admin/wordpress/' . $file;
                
                echo '<div class="border rounded-lg p-4 ' . ($exists ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') . '">';
                echo '<div class="flex items-center justify-between">';
                echo '<div>';
                echo '<h3 class="font-semibold ' . ($exists ? 'text-green-800' : 'text-red-800') . '">';
                echo ($exists ? '✅' : '❌') . ' ' . htmlspecialchars($file);
                echo '</h3>';
                echo '<p class="text-sm text-gray-600 mt-1">' . htmlspecialchars($description) . '</p>';
                echo '</div>';
                echo '<div class="text-right">';
                if ($exists) {
                    echo '<span class="text-green-600 font-semibold">EXISTS</span>';
                } else {
                    echo '<span class="text-red-600 font-semibold">MISSING</span>';
                }
                echo '</div>';
                echo '</div>';
                
                if ($exists) {
                    echo '<div class="mt-2 text-xs text-gray-500">';
                    echo 'Path: <code>' . htmlspecialchars($filePath) . '</code><br>';
                    echo 'URL: <code>' . htmlspecialchars($url) . '</code>';
                    echo '</div>';
                } else {
                    echo '<div class="mt-2 text-sm text-red-700">';
                    echo '⚠️ This file needs to be uploaded to the server!';
                    echo '</div>';
                }
                
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="mt-6 p-4 rounded-lg <?php echo $allExist ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200'; ?>">
            <h2 class="font-semibold <?php echo $allExist ? 'text-green-800' : 'text-yellow-800'; ?> mb-2">
                <?php echo $allExist ? '✅ All Files Present' : '⚠️ Action Required'; ?>
            </h2>
            <?php if ($allExist): ?>
                <p class="text-green-700 text-sm">All required API files are present on the server. The WordPress SQL Import feature should work correctly.</p>
            <?php else: ?>
                <p class="text-yellow-800 text-sm mb-3">Some API files are missing. Please upload them to your server.</p>
                <div class="text-sm text-yellow-800">
                    <p class="font-semibold mb-2">How to upload:</p>
                    <ol class="list-decimal list-inside space-y-1 ml-2">
                        <li>Login to cPanel for s3vgroup.com</li>
                        <li>Go to File Manager</li>
                        <li>Navigate to <code>public_html/api/admin/wordpress/</code></li>
                        <li>Upload the missing files from your local <code>api/admin/wordpress/</code> folder</li>
                        <li>Refresh this page to verify</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 pt-6 border-t">
            <a href="/admin/wordpress-sql-import.php" class="text-blue-600 hover:underline">← Back to WordPress SQL Import</a>
            <span class="mx-2">|</span>
            <a href="/admin/" class="text-blue-600 hover:underline">Admin Dashboard</a>
        </div>
    </div>
</body>
</html>

