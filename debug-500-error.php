<?php
/**
 * Debug 500 Error Script
 * 
 * Upload this to public_html/ and visit it to diagnose 500 errors
 * DELETE THIS FILE after fixing the issue!
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Error Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d32f2f;
            border-bottom: 3px solid #d32f2f;
            padding-bottom: 10px;
        }
        h2 {
            color: #1976d2;
            margin-top: 30px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            display: block;
            padding: 10px;
            margin: 5px 0;
            overflow-x: auto;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border-left: 4px solid #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 500 Error Diagnostic Tool</h1>
        <p>This script checks common causes of 500 errors.</p>
        
        <?php
        $issues = [];
        $warnings = [];
        
        // Check 1: PHP Version
        echo "<h2>1. PHP Version</h2>";
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        if ($phpOk) {
            echo "<div class='success'>✅ PHP Version: $phpVersion (OK)</div>";
        } else {
            echo "<div class='error'>❌ PHP Version: $phpVersion (Need 7.4+)</div>";
            $issues[] = "PHP version too old. Update to 7.4+ in cPanel → Select PHP Version";
        }
        
        // Check 2: Required Extensions
        echo "<h2>2. Required PHP Extensions</h2>";
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                echo "<div class='success'>✅ $ext extension loaded</div>";
            } else {
                echo "<div class='error'>❌ $ext extension NOT loaded</div>";
                $issues[] = "Missing PHP extension: $ext";
            }
        }
        
        // Check 3: File Permissions
        echo "<h2>3. File & Directory Permissions</h2>";
        $baseDir = __DIR__;
        $dirs = [
            $baseDir,
            $baseDir . '/config',
            $baseDir . '/uploads',
        ];
        
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                $writable = is_writable($dir);
                if ($perms === '0755' && $writable) {
                    echo "<div class='success'>✅ $dir - Permissions: $perms (OK)</div>";
                } else {
                    echo "<div class='warning'>⚠️ $dir - Permissions: $perms " . ($writable ? "(writable)" : "(not writable)") . "</div>";
                    if (!$writable) {
                        $warnings[] = "Directory not writable: $dir - Set to 755";
                    }
                }
            } else {
                echo "<div class='warning'>⚠️ Directory not found: $dir</div>";
            }
        }
        
        // Check 4: Configuration Files
        echo "<h2>4. Configuration Files</h2>";
        
        $configDir = __DIR__ . '/config';
        $configFiles = [
            'database.php',
            'site.php',
        ];
        
        foreach ($configFiles as $file) {
            $path = $configDir . '/' . $file;
            if (file_exists($path)) {
                $readable = is_readable($path);
                echo "<div class='success'>✅ config/$file exists" . ($readable ? " (readable)" : " (not readable)") . "</div>";
                if (!$readable) {
                    $issues[] = "Config file not readable: config/$file - Set permissions to 644";
                }
            } else {
                echo "<div class='error'>❌ config/$file NOT FOUND!</div>";
                $issues[] = "Missing config file: config/$file";
            }
        }
        
        // Check 5: .env file
        echo "<h2>5. Environment Configuration</h2>";
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            echo "<div class='success'>✅ .env file exists</div>";
            
            // Try to read .env
            $envContent = @file_get_contents($envFile);
            if ($envContent) {
                echo "<div class='info'>Checking .env file contents...</div>";
                
                // Check for required DB variables
                $requiredVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
                foreach ($requiredVars as $var) {
                    if (preg_match("/^$var=(.+)$/m", $envContent, $matches)) {
                        $value = trim($matches[1]);
                        if (!empty($value) && strpos($value, 'YOUR_') === false) {
                            echo "<div class='success'>✅ $var is set</div>";
                        } else {
                            echo "<div class='warning'>⚠️ $var is not set or has placeholder value</div>";
                            $warnings[] = "Update $var in .env file";
                        }
                    } else {
                        echo "<div class='error'>❌ $var is missing in .env</div>";
                        $issues[] = "Missing $var in .env file";
                    }
                }
            } else {
                echo "<div class='warning'>⚠️ Cannot read .env file</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ .env file not found</div>";
            echo "<div class='info'>Creating .env file is recommended for live server. Check database.local.php if using that instead.</div>";
        }
        
        // Check 6: database.local.php
        echo "<h2>6. Database Local Configuration</h2>";
        $dbLocalFile = __DIR__ . '/config/database.local.php';
        if (file_exists($dbLocalFile)) {
            echo "<div class='success'>✅ config/database.local.php exists</div>";
            
            // Try to load and test
            try {
                $dbConfig = require $dbLocalFile;
                if (is_array($dbConfig)) {
                    echo "<div class='success'>✅ database.local.php is valid array</div>";
                    
                    // Check required keys
                    $requiredKeys = ['host', 'database', 'username', 'password'];
                    foreach ($requiredKeys as $key) {
                        if (isset($dbConfig[$key])) {
                            $value = $dbConfig[$key];
                            if (!empty($value) && strpos($value, 'YOUR_') === false) {
                                echo "<div class='success'>✅ $key is set</div>";
                            } else {
                                echo "<div class='warning'>⚠️ $key has placeholder or empty value</div>";
                                $warnings[] = "Update $key in database.local.php";
                            }
                        } else {
                            echo "<div class='error'>❌ $key is missing</div>";
                            $issues[] = "Missing $key in database.local.php";
                        }
                    }
                } else {
                    echo "<div class='error'>❌ database.local.php does not return an array</div>";
                    $issues[] = "database.local.php must return an array";
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error loading database.local.php: " . htmlspecialchars($e->getMessage()) . "</div>";
                $issues[] = "Cannot load database.local.php: " . $e->getMessage();
            }
        } else {
            echo "<div class='info'>ℹ️ config/database.local.php not found (using .env or defaults)</div>";
        }
        
        // Check 7: Bootstrap File
        echo "<h2>7. Bootstrap & Core Files</h2>";
        $coreFiles = [
            'bootstrap/app.php',
            'index.php',
        ];
        
        foreach ($coreFiles as $file) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                echo "<div class='success'>✅ $file exists</div>";
            } else {
                echo "<div class='error'>❌ $file NOT FOUND!</div>";
                $issues[] = "Missing core file: $file";
            }
        }
        
        // Check 8: Try Loading Bootstrap
        echo "<h2>8. Testing Bootstrap Load</h2>";
        try {
            $bootstrapFile = __DIR__ . '/bootstrap/app.php';
            if (file_exists($bootstrapFile)) {
                ob_start();
                @require_once $bootstrapFile;
                $output = ob_get_clean();
                echo "<div class='success'>✅ Bootstrap loaded successfully</div>";
                if (!empty($output)) {
                    echo "<div class='warning'>⚠️ Bootstrap produced output:</div>";
                    echo "<code>" . htmlspecialchars($output) . "</code>";
                }
            } else {
                echo "<div class='error'>❌ Bootstrap file not found</div>";
                $issues[] = "Bootstrap file missing";
            }
        } catch (Throwable $e) {
            echo "<div class='error'>❌ Error loading bootstrap: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            $issues[] = "Bootstrap error: " . $e->getMessage();
        }
        
        // Check 9: Test Database Connection
        echo "<h2>9. Database Connection Test</h2>";
        try {
            // Try to load database config
            $dbConfigPath = __DIR__ . '/config/database.php';
            if (file_exists($dbConfigPath)) {
                ob_start();
                $dbConfig = @require $dbConfigPath;
                $output = ob_get_clean();
                
                if (!empty($output) && strpos($output, 'Error') !== false) {
                    echo "<div class='error'>❌ Error loading database.php:</div>";
                    echo "<code>" . htmlspecialchars($output) . "</code>";
                    $issues[] = "Error in database.php";
                } else {
                    echo "<div class='success'>✅ database.php loaded</div>";
                    
                    // Try to get DB connection
                    if (function_exists('getDB')) {
                        try {
                            $db = getDB();
                            echo "<div class='success'>✅ Database connection successful!</div>";
                            
                            // Test query
                            $stmt = $db->query("SELECT VERSION() as version");
                            $version = $stmt->fetch();
                            echo "<div class='info'>MySQL Version: " . htmlspecialchars($version['version']) . "</div>";
                        } catch (PDOException $e) {
                            echo "<div class='error'>❌ Database connection failed!</div>";
                            echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                            $issues[] = "Database connection failed: " . $e->getMessage();
                        }
                    } else {
                        echo "<div class='warning'>⚠️ getDB() function not available</div>";
                    }
                }
            } else {
                echo "<div class='error'>❌ database.php not found</div>";
                $issues[] = "Missing database.php";
            }
        } catch (Throwable $e) {
            echo "<div class='error'>❌ Error testing database: " . htmlspecialchars($e->getMessage()) . "</div>";
            $issues[] = "Database test error: " . $e->getMessage();
        }
        
        // Check 10: .htaccess
        echo "<h2>10. .htaccess File</h2>";
        $htaccess = __DIR__ . '/.htaccess';
        if (file_exists($htaccess)) {
            echo "<div class='success'>✅ .htaccess exists</div>";
            $readable = is_readable($htaccess);
            if (!$readable) {
                echo "<div class='warning'>⚠️ .htaccess is not readable</div>";
                $warnings[] = "Set .htaccess permissions to 644";
            }
        } else {
            echo "<div class='warning'>⚠️ .htaccess not found</div>";
            echo "<div class='info'>Creating .htaccess may be needed</div>";
        }
        
        // Summary
        echo "<h2>📋 Summary</h2>";
        
        if (empty($issues) && empty($warnings)) {
            echo "<div class='success' style='font-size: 18px; padding: 20px;'>";
            echo "<strong>✅ All checks passed! No obvious issues found.</strong><br>";
            echo "The 500 error might be caused by:<br>";
            echo "1. PHP errors (check error logs in cPanel)<br>";
            echo "2. Memory limits<br>";
            echo "3. Execution time limits<br>";
            echo "4. .htaccess configuration<br>";
            echo "<br>Check cPanel → Errors or Logs for specific error messages.";
            echo "</div>";
        } else {
            if (!empty($issues)) {
                echo "<div class='error' style='font-size: 18px; padding: 20px;'>";
                echo "<strong>❌ Critical Issues Found:</strong><br><br>";
                foreach ($issues as $issue) {
                    echo "• $issue<br>";
                }
                echo "</div>";
            }
            
            if (!empty($warnings)) {
                echo "<div class='warning' style='font-size: 16px; padding: 20px;'>";
                echo "<strong>⚠️ Warnings:</strong><br><br>";
                foreach ($warnings as $warning) {
                    echo "• $warning<br>";
                }
                echo "</div>";
            }
        }
        
        // Recommendations
        echo "<h2>🔧 Recommended Actions</h2>";
        echo "<ol style='line-height: 2;'>";
        
        if (!empty($issues)) {
            echo "<li><strong>Fix all critical issues listed above</strong></li>";
        }
        
        echo "<li>Check cPanel → <strong>Errors</strong> or <strong>Logs</strong> for detailed error messages</li>";
        echo "<li>Verify PHP version in cPanel → <strong>Select PHP Version</strong> (should be 7.4+)</li>";
        
        if (!file_exists($envFile) && !file_exists($dbLocalFile)) {
            echo "<li>Create <strong>.env</strong> file in public_html/ with database credentials</li>";
        }
        
        echo "<li>Set file permissions: folders = 755, files = 644</li>";
        echo "<li>Test your database connection in cPanel → phpMyAdmin</li>";
        echo "<li><strong>Delete this debug file</strong> after fixing issues (security)</li>";
        echo "</ol>";
        ?>
        
        <hr style="margin: 30px 0;">
        <div class="info">
            <strong>⚠️ Security Reminder:</strong> Delete this file (<code>debug-500-error.php</code>) after fixing the issue!
        </div>
    </div>
</body>
</html>

