<?php
/**
 * Upload Verification Script
 * Use this to verify test-connection.php is uploaded correctly
 * 
 * Upload this file to: public_html/api/admin/wordpress/VERIFY-UPLOAD.php
 * Then visit: https://s3vgroup.com/api/admin/wordpress/VERIFY-UPLOAD.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Files Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .file { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .exists { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .missing { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 20px 0; border-radius: 4px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 WordPress SQL Import - API Files Verification</h1>
        
        <div class="info">
            <strong>Current Directory:</strong><br>
            <code><?php echo __DIR__; ?></code><br><br>
            <strong>Expected Path:</strong><br>
            <code>/home/s3vgroup/public_html/api/admin/wordpress/</code>
        </div>
        
        <h2>Required Files:</h2>
        
        <?php
        $files = [
            'test-connection.php' => 'Tests WordPress database connection (REQUIRED)',
            'import-sql.php' => 'Imports products from WordPress database',
            'save-config.php' => 'Saves WordPress database credentials',
            'load-config.php' => 'Loads saved WordPress database credentials',
        ];
        
        $allExist = true;
        
        foreach ($files as $file => $description) {
            $filePath = __DIR__ . '/' . $file;
            $exists = file_exists($filePath);
            $allExist = $allExist && $exists;
            
            $class = $exists ? 'exists' : 'missing';
            $icon = $exists ? '✅' : '❌';
            
            echo "<div class='file $class'>";
            echo "<strong>$icon $file</strong><br>";
            echo "<small>$description</small><br>";
            
            if ($exists) {
                $size = filesize($filePath);
                echo "<small>Size: " . number_format($size) . " bytes</small><br>";
                echo "<small>Path: <code>$filePath</code></small>";
            } else {
                echo "<strong style='color: #721c24;'>⚠️ FILE MISSING!</strong><br>";
                echo "<small>Expected at: <code>$filePath</code></small>";
            }
            
            echo "</div>";
        }
        ?>
        
        <div class="info">
            <?php if ($allExist): ?>
                <h3>✅ All Files Present!</h3>
                <p>All required API files are uploaded correctly. The WordPress SQL Import feature should work now.</p>
                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>Go to <a href="/admin/wordpress-sql-import.php">WordPress SQL Import</a> page</li>
                    <li>Enter your WordPress database credentials</li>
                    <li>Click "Test Connection"</li>
                    <li>Should work! ✅</li>
                </ol>
            <?php else: ?>
                <h3>⚠️ Action Required</h3>
                <p><strong>Missing files detected!</strong> Please upload the missing files to this directory.</p>
                <p><strong>How to upload:</strong></p>
                <ol>
                    <li>Login to cPanel</li>
                    <li>Go to File Manager</li>
                    <li>Navigate to: <code>public_html/api/admin/wordpress/</code></li>
                    <li>Upload the missing files from your local computer</li>
                    <li>Refresh this page to verify</li>
                </ol>
                <p><strong>Local file location:</strong><br>
                <code>C:\xampp\htdocs\s3vgroup\api\admin\wordpress\</code></p>
            <?php endif; ?>
        </div>
        
        <hr>
        <p><small>
            <a href="/admin/wordpress-sql-import.php">← Back to WordPress SQL Import</a> |
            <a href="/admin/">Admin Dashboard</a>
        </small></p>
    </div>
</body>
</html>

