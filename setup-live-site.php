<?php
/**
 * Live Site Setup Wizard
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your public_html/ directory
 * 2. Visit: https://yourdomain.com/setup-live-site.php
 * 3. Follow the instructions on screen
 * 4. DELETE this file after setup is complete!
 * 
 * ⚠️ SECURITY: This file helps configure your site but should be deleted after use!
 */

// Security: Prevent direct access in production after setup
$allowed = true; // Set to false after setup

error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 1;
$action = $_POST['action'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S3V Group - Live Site Setup Wizard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
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
            color: #0b3a63;
            border-bottom: 3px solid #fa4f26;
            padding-bottom: 10px;
        }
        h2 {
            color: #1a5a8a;
            margin-top: 30px;
        }
        .step {
            background: #e8f4f8;
            padding: 15px;
            border-left: 4px solid #0b3a63;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="email"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            background: #0b3a63;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px 10px 0;
        }
        button:hover {
            background: #1a5a8a;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        ul {
            line-height: 1.8;
        }
        .checklist {
            list-style: none;
            padding: 0;
        }
        .checklist li:before {
            content: "□ ";
            margin-right: 10px;
        }
        .checklist li.done:before {
            content: "✅ ";
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 S3V Group - Live Site Setup Wizard</h1>
        
        <?php if ($step == 1): ?>
            <!-- Step 1: System Check -->
            <h2>Step 1: System Requirements Check</h2>
            
            <?php
            $checks = [];
            
            // Check PHP version
            $phpVersion = PHP_VERSION;
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            $checks['PHP Version'] = ['status' => $phpOk, 'message' => "PHP $phpVersion " . ($phpOk ? '✓' : '(Need 7.4+)')];
            
            // Check PDO MySQL
            $pdoOk = extension_loaded('pdo_mysql');
            $checks['PDO MySQL'] = ['status' => $pdoOk, 'message' => $pdoOk ? 'Extension loaded ✓' : 'Extension not found'];
            
            // Check config directory
            $configDir = __DIR__ . '/config';
            $configDirExists = is_dir($configDir);
            $checks['Config Directory'] = ['status' => $configDirExists, 'message' => $configDirExists ? 'Exists ✓' : 'Not found'];
            
            // Check .htaccess
            $htaccess = __DIR__ . '/.htaccess';
            $htaccessExists = file_exists($htaccess);
            $checks['.htaccess'] = ['status' => $htaccessExists, 'message' => $htaccessExists ? 'Exists ✓' : 'Not found'];
            
            // Check write permissions
            $configWritable = is_writable($configDir);
            $checks['Config Writable'] = ['status' => $configWritable, 'message' => $configWritable ? 'Writable ✓' : 'Not writable'];
            
            foreach ($checks as $name => $check) {
                if ($check['status']) {
                    echo "<div class='success'>✅ $name: {$check['message']}</div>";
                } else {
                    echo "<div class='error'>❌ $name: {$check['message']}</div>";
                }
            }
            
            $allOk = array_reduce($checks, function($carry, $item) {
                return $carry && $item['status'];
            }, true);
            ?>
            
            <?php if ($allOk): ?>
                <div class="success">
                    <strong>✅ All system requirements met!</strong>
                </div>
                <a href="?step=2"><button class="btn-success">Next: Database Configuration →</button></a>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Some requirements are not met. Please fix the issues above before continuing.</strong>
                </div>
            <?php endif; ?>
            
        <?php elseif ($step == 2): ?>
            <!-- Step 2: Database Configuration -->
            <h2>Step 2: Database Configuration</h2>
            
            <div class="info">
                <strong>📋 Before continuing:</strong>
                <ul>
                    <li>Create a MySQL database in cPanel → MySQL Databases</li>
                    <li>Create a MySQL user and add it to your database</li>
                    <li>Grant ALL PRIVILEGES to the user</li>
                    <li>Note down: Database name, Username, Password</li>
                </ul>
            </div>
            
            <?php if ($action == 'save_database'): ?>
                <?php
                // Save database configuration
                $host = $_POST['host'] ?? 'localhost';
                $database = $_POST['database'] ?? '';
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $configContent = "<?php\n";
                $configContent .= "/**\n";
                $configContent .= " * Live Server Database Configuration\n";
                $configContent .= " * Auto-generated by setup wizard\n";
                $configContent .= " */\n\n";
                $configContent .= "return [\n";
                $configContent .= "    'host' => " . var_export($host, true) . ",\n";
                $configContent .= "    'database' => " . var_export($database, true) . ",\n";
                $configContent .= "    'username' => " . var_export($username, true) . ",\n";
                $configContent .= "    'password' => " . var_export($password, true) . ",\n";
                $configContent .= "    'charset' => 'utf8mb4',\n";
                $configContent .= "];\n";
                
                $configFile = __DIR__ . '/config/database.local.php';
                
                if (file_put_contents($configFile, $configContent)) {
                    // Test connection
                    try {
                        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
                        $pdo = new PDO($dsn, $username, $password, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        ]);
                        
                        echo "<div class='success'>✅ Database configuration saved and connection successful!</div>";
                        echo "<a href='?step=3'><button class='btn-success'>Next: Site Configuration →</button></a>";
                    } catch (PDOException $e) {
                        echo "<div class='error'>❌ Configuration saved but connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
                        echo "<div class='info'>Please check your database credentials and try again.</div>";
                        echo "<a href='?step=2'><button>← Go Back</button></a>";
                    }
                } else {
                    echo "<div class='error'>❌ Failed to save configuration file. Check file permissions.</div>";
                    echo "<a href='?step=2'><button>← Go Back</button></a>";
                }
                ?>
            <?php else: ?>
                <form method="POST" action="?step=2">
                    <input type="hidden" name="action" value="save_database">
                    
                    <label>Database Host:</label>
                    <input type="text" name="host" value="localhost" required>
                    <div class="info">Usually 'localhost' for cPanel hosting</div>
                    
                    <label>Database Name:</label>
                    <input type="text" name="database" placeholder="username_database_name" required>
                    <div class="info">Full database name from cPanel (e.g., myname_s3vgroup_db)</div>
                    
                    <label>Database Username:</label>
                    <input type="text" name="username" placeholder="username_database_user" required>
                    <div class="info">Full username from cPanel (e.g., myname_s3vgroup_user)</div>
                    
                    <label>Database Password:</label>
                    <input type="password" name="password" required>
                    <div class="info">Password you created for the database user</div>
                    
                    <button type="submit">Test Connection & Save</button>
                    <a href="?step=1"><button type="button">← Back</button></a>
                </form>
            <?php endif; ?>
            
        <?php elseif ($step == 3): ?>
            <!-- Step 3: Site Configuration -->
            <h2>Step 3: Site Configuration</h2>
            
            <?php
            $siteConfigFile = __DIR__ . '/config/site.php';
            $siteConfigExists = file_exists($siteConfigFile);
            
            if ($action == 'save_site' && $siteConfigExists) {
                // Read current site.php
                $siteContent = file_get_contents($siteConfigFile);
                
                // Update URL
                $liveUrl = $_POST['url'] ?? '';
                if ($liveUrl) {
                    $siteContent = preg_replace(
                        "/'url' => '[^']*'/",
                        "'url' => " . var_export($liveUrl, true),
                        $siteContent
                    );
                }
                
                // Update admin email
                $adminEmail = $_POST['admin_email'] ?? '';
                if ($adminEmail) {
                    $siteContent = preg_replace(
                        "/define\('ADMIN_EMAIL', '[^']*'\)/",
                        "define('ADMIN_EMAIL', " . var_export($adminEmail, true) . ")",
                        $siteContent
                    );
                }
                
                // Update admin password
                $adminPassword = $_POST['admin_password'] ?? '';
                if ($adminPassword) {
                    $siteContent = preg_replace(
                        "/define\('ADMIN_PASSWORD', '[^']*'\)/",
                        "define('ADMIN_PASSWORD', " . var_export($adminPassword, true) . ")",
                        $siteContent
                    );
                }
                
                if (file_put_contents($siteConfigFile, $siteContent)) {
                    echo "<div class='success'>✅ Site configuration updated successfully!</div>";
                    echo "<a href='?step=4'><button class='btn-success'>Next: Import Database →</button></a>";
                } else {
                    echo "<div class='error'>❌ Failed to update site configuration. Check file permissions.</div>";
                    echo "<a href='?step=3'><button>← Go Back</button></a>";
                }
            } else {
                // Read current values
                if ($siteConfigExists) {
                    require $siteConfigFile;
                    $currentUrl = $siteConfig['url'] ?? 'http://localhost:8080';
                    $currentEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@s3vtgroup.com';
                } else {
                    $currentUrl = 'https://yourdomain.com';
                    $currentEmail = 'admin@s3vtgroup.com';
                }
                ?>
                
                <form method="POST" action="?step=3">
                    <input type="hidden" name="action" value="save_site">
                    
                    <label>Live Website URL:</label>
                    <input type="text" name="url" value="<?php echo htmlspecialchars($currentUrl); ?>" required>
                    <div class="info">Your live domain (e.g., https://s3vgroup.com)</div>
                    
                    <label>Admin Email:</label>
                    <input type="email" name="admin_email" value="<?php echo htmlspecialchars($currentEmail); ?>" required>
                    <div class="info">Email for admin login</div>
                    
                    <label>Admin Password:</label>
                    <input type="password" name="admin_password" required>
                    <div class="warning">⚠️ Enter a secure password (this will replace the current one)</div>
                    
                    <button type="submit">Save Configuration</button>
                    <a href="?step=2"><button type="button">← Back</button></a>
                </form>
            <?php } ?>
            
        <?php elseif ($step == 4): ?>
            <!-- Step 4: Database Import -->
            <h2>Step 4: Import Database Schema</h2>
            
            <div class="info">
                <strong>📋 Instructions:</strong>
                <ol>
                    <li>Log into cPanel</li>
                    <li>Go to <strong>phpMyAdmin</strong></li>
                    <li>Select your database from the left sidebar</li>
                    <li>Click the <strong>Import</strong> tab</li>
                    <li>Click <strong>Choose File</strong></li>
                    <li>Navigate to: <code>public_html/sql/schema.sql</code></li>
                    <li>Click <strong>Go</strong> to import</li>
                </ol>
            </div>
            
            <?php
            // Check if database has tables
            try {
                $configFile = __DIR__ . '/config/database.local.php';
                if (file_exists($configFile)) {
                    $dbConfig = require $configFile;
                    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo "<div class='success'>✅ Database has " . count($tables) . " table(s):</div>";
                        echo "<ul>";
                        foreach ($tables as $table) {
                            echo "<li>$table</li>";
                        }
                        echo "</ul>";
                        echo "<a href='?step=5'><button class='btn-success'>Next: Final Verification →</button></a>";
                    } else {
                        echo "<div class='warning'>⚠️ No tables found. Please import schema.sql via phpMyAdmin.</div>";
                    }
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Could not check database: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
            
            <a href="?step=3"><button>← Back</button></a>
            <a href="?step=5"><button>Skip (Already Imported)</button></a>
            
        <?php elseif ($step == 5): ?>
            <!-- Step 5: Final Verification -->
            <h2>Step 5: Final Verification</h2>
            
            <?php
            $allGood = true;
            
            // Check database config
            $dbConfigFile = __DIR__ . '/config/database.local.php';
            if (file_exists($dbConfigFile)) {
                echo "<div class='success'>✅ Database configuration file exists</div>";
                try {
                    $dbConfig = require $dbConfigFile;
                    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    echo "<div class='success'>✅ Database connection works</div>";
                    
                    // Check tables
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    if (count($tables) > 0) {
                        echo "<div class='success'>✅ Database has " . count($tables) . " table(s)</div>";
                    } else {
                        echo "<div class='error'>❌ No tables found - import schema.sql</div>";
                        $allGood = false;
                    }
                } catch (Exception $e) {
                    echo "<div class='error'>❌ Database connection failed</div>";
                    $allGood = false;
                }
            } else {
                echo "<div class='error'>❌ Database configuration file not found</div>";
                $allGood = false;
            }
            
            // Check site config
            $siteConfigFile = __DIR__ . '/config/site.php';
            if (file_exists($siteConfigFile)) {
                echo "<div class='success'>✅ Site configuration file exists</div>";
                require $siteConfigFile;
                if (isset($siteConfig['url']) && strpos($siteConfig['url'], 'localhost') === false) {
                    echo "<div class='success'>✅ Site URL is set to live domain</div>";
                } else {
                    echo "<div class='warning'>⚠️ Site URL still contains localhost</div>";
                }
            } else {
                echo "<div class='error'>❌ Site configuration file not found</div>";
                $allGood = false;
            }
            
            if ($allGood) {
                echo "<div class='success' style='margin: 20px 0; padding: 20px; font-size: 18px;'>";
                echo "<strong>🎉 Setup Complete!</strong><br><br>";
                echo "Your website should now be working!<br><br>";
                echo "<a href='/' style='color: white;'><button class='btn-success' style='font-size: 18px; padding: 15px 30px;'>Visit Homepage</button></a> ";
                echo "<a href='/admin/login.php' style='color: white;'><button class='btn-success' style='font-size: 18px; padding: 15px 30px;'>Admin Login</button></a>";
                echo "</div>";
                
                echo "<div class='warning' style='margin-top: 20px;'>";
                echo "<strong>⚠️ IMPORTANT SECURITY:</strong><br>";
                echo "Please DELETE this setup file (<code>setup-live-site.php</code>) after completing setup!";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ Some issues need to be fixed. Please go back and complete all steps.</div>";
                echo "<a href='?step=1'><button>← Start Over</button></a>";
            }
            ?>
            
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        <div class="info">
            <strong>Need help?</strong> Check <code>LIVE-SETUP-GUIDE.md</code> for detailed instructions.
        </div>
    </div>
</body>
</html>

