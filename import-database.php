<?php
/**
 * Automatic Database Import Script
 * 
 * This script automatically imports all SQL files to your cPanel database.
 * 
 * Usage:
 * 1. Upload this file to your cPanel public_html directory
 * 2. Visit: https://s3vgroup.com/import-database.php
 * 3. The script will automatically import all SQL files
 * 4. DELETE this file after use for security!
 * 
 * Security: This file should be deleted after database import is complete.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security: Only allow if accessed directly and not from command line
if (php_sapi_name() === 'cli') {
    die("This script must be run via web browser, not command line.\n");
}

// Load database configuration
require_once __DIR__ . '/config/database.php';

use App\Database\Connection;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import - S3V Group</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0b3a63;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #0b3a63;
            background: #f9f9f9;
        }
        .step h2 {
            margin: 0 0 10px 0;
            color: #0b3a63;
            font-size: 18px;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        .info {
            color: #3b82f6;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0b3a63;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #1a5a8a;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0b3a63;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Automatic Database Import</h1>
        <p class="subtitle">This script will automatically import all SQL files to your database.</p>

        <?php
        $action = $_GET['action'] ?? 'check';
        $imported = false;
        $errors = [];
        $success = [];

        try {
            // Get database connection
            $db = getDB();
            $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
            
            echo "<div class='step'>";
            echo "<h2>📊 Database Connection</h2>";
            echo "<p class='success'>✅ Connected to database: <strong>$dbName</strong></p>";
            echo "</div>";

            if ($action === 'import') {
                echo "<div class='step'>";
                echo "<h2>📥 Importing Database...</h2>";

                // List of SQL files to import (in order)
                $sqlFiles = [
                    'schema.sql' => 'Main database schema (tables, indexes, foreign keys)',
                    'site_options.sql' => 'Site options and default settings',
                    'sample_data.sql' => 'Sample data (products, teams, testimonials, sliders, quotes)',
                ];

                $totalQueries = 0;
                $totalTables = 0;
                $totalRows = 0;

                foreach ($sqlFiles as $sqlFile => $description) {
                    $filePath = __DIR__ . '/sql/' . $sqlFile;
                    
                    if (!file_exists($filePath)) {
                        $errors[] = "File not found: $sqlFile";
                        echo "<p class='warning'>⚠️ Skipping $sqlFile (file not found)</p>";
                        continue;
                    }

                    echo "<h3>📄 Importing: $sqlFile</h3>";
                    echo "<p class='info'>$description</p>";

                    // Read SQL file
                    $sql = file_get_contents($filePath);
                    
                    if ($sql === false) {
                        $errors[] = "Failed to read file: $sqlFile";
                        echo "<p class='error'>❌ Failed to read file: $sqlFile</p>";
                        continue;
                    }

                    // Remove comments and split into individual statements
                    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
                    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
                    
                    // Split by semicolon, but preserve semicolons inside quotes
                    $statements = [];
                    $current = '';
                    $inQuotes = false;
                    $quoteChar = '';
                    
                    for ($i = 0; $i < strlen($sql); $i++) {
                        $char = $sql[$i];
                        $current .= $char;
                        
                        if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
                            if (!$inQuotes) {
                                $inQuotes = true;
                                $quoteChar = $char;
                            } elseif ($char === $quoteChar) {
                                $inQuotes = false;
                                $quoteChar = '';
                            }
                        }
                        
                        if (!$inQuotes && $char === ';') {
                            $statement = trim($current);
                            if (!empty($statement)) {
                                $statements[] = $statement;
                            }
                            $current = '';
                        }
                    }
                    
                    // Add last statement if exists
                    if (!empty(trim($current))) {
                        $statements[] = trim($current);
                    }

                    // Execute each statement
                    $fileQueries = 0;
                    foreach ($statements as $statement) {
                        $statement = trim($statement);
                        
                        // Skip empty statements
                        if (empty($statement) || 
                            strtoupper(substr($statement, 0, 2)) === '--' ||
                            strtoupper(substr($statement, 0, 2)) === '/*') {
                            continue;
                        }

                        try {
                            $db->exec($statement);
                            $fileQueries++;
                            $totalQueries++;

                            // Count CREATE TABLE statements
                            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                                $totalTables++;
                            }

                            // Count INSERT statements
                            if (preg_match('/INSERT\s+INTO/i', $statement)) {
                                $totalRows++;
                            }
                        } catch (PDOException $e) {
                            // Ignore "table already exists" errors for CREATE TABLE IF NOT EXISTS
                            if (strpos($e->getMessage(), 'already exists') === false &&
                                strpos($e->getMessage(), 'Duplicate') === false) {
                                $errors[] = "Error in $sqlFile: " . $e->getMessage();
                                echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            }
                        }
                    }

                    echo "<p class='success'>✅ Imported $sqlFile: $fileQueries queries executed</p>";
                    $success[] = "Imported $sqlFile";
                }

                echo "</div>";

                // Show statistics
                echo "<div class='step'>";
                echo "<h2>📊 Import Statistics</h2>";
                echo "<div class='stats'>";
                echo "<div class='stat-box'>";
                echo "<div class='stat-number'>$totalQueries</div>";
                echo "<div class='stat-label'>SQL Queries Executed</div>";
                echo "</div>";
                echo "<div class='stat-box'>";
                echo "<div class='stat-number'>$totalTables</div>";
                echo "<div class='stat-label'>Tables Created</div>";
                echo "</div>";
                echo "<div class='stat-box'>";
                echo "<div class='stat-number'>$totalRows</div>";
                echo "<div class='stat-label'>Data Rows Inserted</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";

                // Verify tables
                echo "<div class='step'>";
                echo "<h2>✅ Verification</h2>";
                
                $requiredTables = ['categories', 'products', 'quote_requests', 'site_options', 'team_members'];
                $existingTables = [];
                
                try {
                    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($requiredTables as $table) {
                        if (in_array($table, $tables)) {
                            $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                            echo "<p class='success'>✅ Table <strong>$table</strong> exists ($count rows)</p>";
                            $existingTables[] = $table;
                        } else {
                            echo "<p class='error'>❌ Table <strong>$table</strong> not found</p>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ Error checking tables: " . htmlspecialchars($e->getMessage()) . "</p>";
                }

                echo "</div>";

                $imported = true;
            } else {
                // Show check/preview
                echo "<div class='step'>";
                echo "<h2>🔍 Pre-Import Check</h2>";
                
                // Check existing tables
                try {
                    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    if (count($tables) > 0) {
                        echo "<p class='warning'>⚠️ Database already contains " . count($tables) . " table(s):</p>";
                        echo "<ul>";
                        foreach ($tables as $table) {
                            $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                            echo "<li><strong>$table</strong> ($count rows)</li>";
                        }
                        echo "</ul>";
                        echo "<p class='info'>ℹ️ The import will use 'CREATE TABLE IF NOT EXISTS' and 'INSERT ... ON DUPLICATE KEY UPDATE', so existing data will be preserved.</p>";
                    } else {
                        echo "<p class='success'>✅ Database is empty - ready for fresh import</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ Error checking database: " . htmlspecialchars($e->getMessage()) . "</p>";
                }

                // List SQL files
                echo "<h3>📄 SQL Files to Import:</h3>";
                echo "<ul>";
                $sqlFiles = ['schema.sql', 'site_options.sql'];
                foreach ($sqlFiles as $file) {
                    $filePath = __DIR__ . '/sql/' . $file;
                    if (file_exists($filePath)) {
                        $size = filesize($filePath);
                        echo "<li class='success'>✅ <strong>$file</strong> (" . number_format($size) . " bytes)</li>";
                    } else {
                        echo "<li class='error'>❌ <strong>$file</strong> (not found)</li>";
                    }
                }
                echo "</ul>";

                echo "</div>";

                // Show import button
                echo "<div style='text-align: center; margin: 30px 0;'>";
                echo "<a href='?action=import' class='btn' onclick='return confirm(\"Are you sure you want to import the database? This will create tables and insert data.\")'>🚀 Start Import</a>";
                echo "</div>";
            }

        } catch (Exception $e) {
            echo "<div class='step'>";
            echo "<h2 class='error'>❌ Error</h2>";
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }
        ?>

        <?php if ($imported): ?>
            <div class="step">
                <h2>🎉 Import Complete!</h2>
                <p class="success">✅ Database has been successfully imported.</p>
                <p class="info">You can now:</p>
                <ul>
                    <li>Visit your <a href="/">homepage</a> to see the website</li>
                    <li>Login to <a href="/admin/login.php">admin panel</a></li>
                    <li><strong class="warning">⚠️ DELETE this file (import-database.php) for security!</strong></li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="step">
            <h2>🔒 Security Reminder</h2>
            <p class="warning">⚠️ <strong>IMPORTANT:</strong> Delete this file after importing the database!</p>
            <p>This file has access to your database and should not be publicly accessible.</p>
        </div>
    </div>
</body>
</html>

