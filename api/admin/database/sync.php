<?php

declare(strict_types=1);

// Start output buffering
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;

try {
    AdminGuard::requireAuth();
} catch (\Throwable $e) {
    ob_end_clean();
    JsonResponse::error('Authentication required.', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    JsonResponse::error('Method not allowed.', 405);
}

try {
    $localDb = getDB();
    $repository = new SiteOptionRepository($localDb);
    $service = new SiteOptionService($repository);

    // Get cPanel database configuration
    $cpanelConfig = [
        'host' => $service->get('db_sync_cpanel_host', ''),
        'database' => $service->get('db_sync_cpanel_database', ''),
        'username' => $service->get('db_sync_cpanel_username', ''),
        'password' => $service->get('db_sync_cpanel_password', ''),
        'port' => (int) ($service->get('db_sync_cpanel_port', '3306')),
    ];

    // Validate cPanel configuration
    if (empty($cpanelConfig['host']) || empty($cpanelConfig['database']) || 
        empty($cpanelConfig['username']) || empty($cpanelConfig['password'])) {
        ob_end_clean();
        JsonResponse::error('cPanel database configuration is incomplete. Please configure it in Database Sync settings first.', 400);
    }

    // Get sync options
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $createBackup = $payload['create_backup'] ?? true;
    $syncMode = $payload['sync_mode'] ?? 'full'; // 'full' or 'structure_only'
    $dataMode = $payload['data_mode'] ?? 'overwrite'; // 'overwrite' or 'append'

    // Initialize operation log
    $operationLog = [];
    $operationLog[] = ['step' => 1, 'status' => 'info', 'message' => 'Starting push operation from local to cPanel...'];
    $operationLog[] = ['step' => 1, 'status' => 'info', 'message' => 'Mode: ' . ($syncMode === 'full' ? 'Full Push (structure + data)' : 'Structure Only')];
    $operationLog[] = ['step' => 1, 'status' => 'info', 'message' => 'Data Mode: ' . ($dataMode === 'overwrite' ? 'Overwrite (cPanel will have exactly what local has)' : 'Append (merge local + cPanel data)')];

    // Step 1: Export from local database
    $operationLog[] = ['step' => 2, 'status' => 'info', 'message' => 'Fetching table list from local database...'];
    $tables = [];
    try {
        $result = $localDb->query("SHOW TABLES");
        if ($result) {
            // Try FETCH_COLUMN first (most common)
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            
            // If that didn't work, try FETCH_NUM and get first column
            if (empty($tables)) {
                $result = $localDb->query("SHOW TABLES");
                $allRows = $result->fetchAll(PDO::FETCH_NUM);
                foreach ($allRows as $row) {
                    if (isset($row[0])) {
                        $tables[] = $row[0];
                    }
                }
            }
        }
    } catch (\PDOException $e) {
        ob_end_clean();
        JsonResponse::error('Failed to retrieve local database tables: ' . $e->getMessage(), 500);
    }
    
    if (empty($tables)) {
        ob_end_clean();
        $operationLog[] = ['step' => 2, 'status' => 'error', 'message' => '✗ No tables found in local database'];
        JsonResponse::error('No tables found in local database.', 404, ['log' => $operationLog]);
    }
    
    $operationLog[] = ['step' => 2, 'status' => 'success', 'message' => '✓ Found ' . count($tables) . ' tables in local database'];
    
    // Get production site URL for URL replacement
    $productionUrl = $service->get('db_sync_production_url', '');
    if (empty($productionUrl)) {
        // Try to get from site_url option
        $productionUrl = $service->get('site_url', '');
    }
    if (empty($productionUrl)) {
        // Try to get from site config
        try {
            require_once __DIR__ . '/../../../config/site.php';
            $productionUrl = $siteConfig['url'] ?? '';
        } catch (\Throwable $e) {
            // Ignore
        }
    }
    
    // If still empty, try to construct from cPanel config
    if (empty($productionUrl)) {
        $protocol = 'https'; // Default to https for production
        $productionUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 's3vgroup.com');
    }
    
    // Remove trailing slash
    $productionUrl = rtrim($productionUrl, '/');
    
    // Local URLs to replace
    $localUrls = [
        'http://localhost:8080',
        'http://localhost:8000',
        'http://127.0.0.1:8080',
        'http://127.0.0.1:8000',
        'https://localhost:8080',
        'https://localhost:8000',
    ];
    
    $urlReplacements = 0;
    $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => 'Exporting data from local database...'];
    if (!empty($productionUrl)) {
        $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => 'Will replace localhost URLs with: ' . $productionUrl];
    }

    $output = [];
    $output[] = "-- Enhanced Database Sync Export";
    $output[] = "-- Generated: " . date('Y-m-d H:i:s');
    $output[] = "-- Source: Local Development";
    $output[] = "-- Target: cPanel Production";
    $output[] = "-- Tables: " . count($tables);
    $output[] = "";
    $output[] = "SET NAMES utf8mb4;";
    $output[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
    $output[] = "SET time_zone = \"+00:00\";";
    $output[] = "SET FOREIGN_KEY_CHECKS = 0;";
    $output[] = "";

    $tableCount = 0;
    $rowCount = 0;
    foreach ($tables as $table) {
        $tableCount++;
        try {
            // Include structure based on data mode
            if ($dataMode === 'overwrite') {
                // Overwrite mode: Drop and recreate tables
                $output[] = "DROP TABLE IF EXISTS `{$table}`;";
                
                $createTableResult = $localDb->query("SHOW CREATE TABLE `{$table}`");
                $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
                if ($createTable && isset($createTable['Create Table'])) {
                    $output[] = $createTable['Create Table'] . ";";
                    $output[] = "";
                }
            } else {
                // Append mode: Only create table if it doesn't exist
                $createTableResult = $localDb->query("SHOW CREATE TABLE `{$table}`");
                $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
                if ($createTable && isset($createTable['Create Table'])) {
                    // Replace CREATE TABLE with CREATE TABLE IF NOT EXISTS
                    $createTableSql = $createTable['Create Table'];
                    $createTableSql = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $createTableSql);
                    $output[] = $createTableSql . ";";
                    $output[] = "";
                }
            }

            // Include data if full sync
            if ($syncMode === 'full') {
                // Get row count first
                $rowCountResult = $localDb->query("SELECT COUNT(*) FROM `{$table}`");
                $tableRowCount = $rowCountResult ? (int) $rowCountResult->fetchColumn() : 0;
                
                if ($tableRowCount > 0) {
                    $output[] = "-- Dumping data for table `{$table}` (" . number_format($tableRowCount) . " rows)";
                    $output[] = "";
                    
                    // Get column metadata to detect JSON columns
                    $columnInfo = [];
                    try {
                        $columnsResult = $localDb->query("SHOW COLUMNS FROM `{$table}`");
                        if ($columnsResult) {
                            $columnInfo = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
                        }
                    } catch (\PDOException $e) {
                        // Continue without column info
                    }
                    
                    // For large tables, process in chunks to avoid memory issues
                    $chunkSize = 500;
                    $offset = 0;
                    $processedRows = 0;
                    
                    while ($offset < $tableRowCount) {
                        $rowsResult = $localDb->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}");
                        $rows = $rowsResult ? $rowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
                        
                        if (empty($rows)) {
                            break;
                        }
                        
                        $columns = array_keys($rows[0]);
                        $columnList = '`' . implode('`, `', $columns) . '`';
                        
                        // Build multi-row INSERT for better performance
                        $valueGroups = [];
                        
                        foreach ($rows as $row) {
                            $values = [];
                            foreach ($row as $column => $value) {
                                $processed = processValueForSync($value, $column, $columnInfo, $localDb, $localUrls, $productionUrl, $urlReplacements);
                                $values[] = $processed['value'];
                                if ($processed['replaced_url']) {
                                    $urlReplacements++;
                                }
                            }
                            $valueGroups[] = '(' . implode(', ', $values) . ')';
                            $processedRows++;
                        }
                        
                        // Use multi-row INSERT for efficiency
                        if (!empty($valueGroups)) {
                            if ($dataMode === 'overwrite') {
                                // Overwrite mode: Simple INSERT (table was dropped, so no conflicts)
                                $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES";
                                $output[] = implode(",\n", $valueGroups) . ";";
                            } else {
                                // Append mode: Use INSERT ... ON DUPLICATE KEY UPDATE
                                // First, get primary key or unique columns
                                $primaryKey = null;
                                $uniqueKeys = [];
                                try {
                                    $keyInfo = $localDb->query("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY' OR Non_unique = 0");
                                    if ($keyInfo) {
                                        $keys = $keyInfo->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($keys as $key) {
                                            if ($key['Key_name'] === 'PRIMARY') {
                                                $primaryKey = $key['Column_name'];
                                            } else {
                                                $uniqueKeys[] = $key['Column_name'];
                                            }
                                        }
                                    }
                                } catch (\PDOException $e) {
                                    // Continue without key info
                                }
                                
                                // Build ON DUPLICATE KEY UPDATE clause
                                $updateClause = [];
                                if ($primaryKey || !empty($uniqueKeys)) {
                                    // Update all columns except the key
                                    foreach ($columns as $col) {
                                        if ($col !== $primaryKey && !in_array($col, $uniqueKeys)) {
                                            $updateClause[] = "`{$col}` = VALUES(`{$col}`)";
                                        }
                                    }
                                } else {
                                    // No primary key - update all columns
                                    foreach ($columns as $col) {
                                        $updateClause[] = "`{$col}` = VALUES(`{$col}`)";
                                    }
                                }
                                
                                if (!empty($updateClause)) {
                                    $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES";
                                    $output[] = implode(",\n", $valueGroups);
                                    $output[] = "ON DUPLICATE KEY UPDATE " . implode(", ", $updateClause) . ";";
                                } else {
                                    // Fallback to simple INSERT if no update clause
                                    $output[] = "INSERT IGNORE INTO `{$table}` ({$columnList}) VALUES";
                                    $output[] = implode(",\n", $valueGroups) . ";";
                                }
                            }
                            $output[] = "";
                        }
                        
                        $offset += $chunkSize;
                    }
                    
                    $rowCount += $processedRows;
                    $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => "Exported table `{$table}`: " . number_format($processedRows) . " rows"];
                } else {
                    $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => "Table `{$table}` is empty (skipping data)"];
                }
            }
        } catch (\PDOException $e) {
            error_log("Error processing table {$table}: " . $e->getMessage());
            $operationLog[] = ['step' => 3, 'status' => 'warning', 'message' => '⚠ Error processing table: ' . $table];
            // Continue with next table
        }
    }
    
    $output[] = "SET FOREIGN_KEY_CHECKS = 1;";
    $output[] = "";
    
    $exportMessage = '✓ Exported ' . $tableCount . ' tables' . ($syncMode === 'full' ? ' with ' . number_format($rowCount) . ' rows' : ' (structure only)');
    $exportMessage .= ' [' . ($dataMode === 'overwrite' ? 'Overwrite' : 'Append') . ' mode]';
    if ($urlReplacements > 0) {
        $exportMessage .= ' (replaced ' . $urlReplacements . ' localhost URLs with production URL)';
    }
    $operationLog[] = ['step' => 3, 'status' => 'success', 'message' => $exportMessage];

    $sql = implode("\n", $output);

    // Step 2: Create backup if requested
    $backupMessage = '';
    if ($createBackup) {
        $operationLog[] = ['step' => 4, 'status' => 'info', 'message' => 'Creating backup of cPanel database...'];
        try {
            $cpanelDb = new PDO(
                "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
                $cpanelConfig['username'],
                $cpanelConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $backupTables = [];
            try {
                $backupResult = $cpanelDb->query("SHOW TABLES");
                if ($backupResult) {
                    $backupTables = $backupResult->fetchAll(PDO::FETCH_COLUMN);
                    if (empty($backupTables)) {
                        // Try alternative fetch method
                        $backupResult = $cpanelDb->query("SHOW TABLES");
                        $allRows = $backupResult->fetchAll(PDO::FETCH_NUM);
                        foreach ($allRows as $row) {
                            if (isset($row[0])) {
                                $backupTables[] = $row[0];
                            }
                        }
                    }
                }
            } catch (\PDOException $e) {
                error_log("Failed to fetch backup tables: " . $e->getMessage());
                // Continue without backup
            }
            
            if (!empty($backupTables)) {
                $backupDir = __DIR__ . '/../../../tmp';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                $backupId = date('Y-m-d-His');
                $backupFile = $backupDir . '/backup-before-sync-' . $backupId . '.sql';
                
                $backupOutput = [];
                $backupOutput[] = "-- Backup created before sync: " . date('Y-m-d H:i:s');
                $backupOutput[] = "";

                foreach ($backupTables as $table) {
                    try {
                        $backupOutput[] = "DROP TABLE IF EXISTS `{$table}`;";
                        $createTableResult = $cpanelDb->query("SHOW CREATE TABLE `{$table}`");
                        $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
                        if ($createTable && isset($createTable['Create Table'])) {
                            $backupOutput[] = $createTable['Create Table'] . ";";
                            $backupOutput[] = "";
                        }

                        $backupRowsResult = $cpanelDb->query("SELECT * FROM `{$table}`");
                        $rows = $backupRowsResult ? $backupRowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
                        if (!empty($rows)) {
                            $columns = array_keys($rows[0]);
                            $columnList = '`' . implode('`, `', $columns) . '`';
                            
                            foreach ($rows as $row) {
                                $values = [];
                                foreach ($row as $value) {
                                    if ($value === null) {
                                        $values[] = 'NULL';
                                    } elseif (is_bool($value)) {
                                        $values[] = $value ? '1' : '0';
                                    } elseif (is_int($value) || is_float($value)) {
                                        $values[] = (string) $value;
                                    } else {
                                        $values[] = $cpanelDb->quote((string) $value);
                                    }
                                }
                                $backupOutput[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                            }
                            $backupOutput[] = "";
                        }
                    } catch (\PDOException $e) {
                        error_log("Error backing up table {$table}: " . $e->getMessage());
                        // Continue with next table
                    }
                }

                file_put_contents($backupFile, implode("\n", $backupOutput));
                $backupMessage = "Backup created: " . basename($backupFile);
                $operationLog[] = ['step' => 4, 'status' => 'success', 'message' => '✓ Backup created: ' . basename($backupFile) . ' (' . count($backupTables) . ' tables)'];
            } else {
                $operationLog[] = ['step' => 4, 'status' => 'info', 'message' => 'No cPanel tables to backup'];
            }
        } catch (\Throwable $e) {
            error_log("Backup creation failed: " . $e->getMessage());
            $backupMessage = "Warning: Backup creation failed, but sync will continue.";
            $operationLog[] = ['step' => 4, 'status' => 'warning', 'message' => '⚠ Backup creation failed, but continuing...'];
        }
    } else {
        $operationLog[] = ['step' => 4, 'status' => 'info', 'message' => 'Skipping backup (disabled)'];
    }

    // Step 3: Import to cPanel
    $operationLog[] = ['step' => 5, 'status' => 'info', 'message' => 'Connecting to cPanel database...'];
    try {
        $cpanelDb = new PDO(
            "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
            $cpanelConfig['username'],
            $cpanelConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 300, // 5 minute timeout for large databases
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );
        $operationLog[] = ['step' => 5, 'status' => 'success', 'message' => '✓ Connected to cPanel database successfully'];
    } catch (\PDOException $e) {
        ob_end_clean();
        
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        $userMessage = 'Failed to connect to cPanel database. ';
        $suggestions = [];
        
        if (strpos($errorMessage, 'Access denied') !== false || $errorCode == 1045) {
            $userMessage .= 'Invalid username or password.';
            $suggestions[] = 'Double-check your database username and password in cPanel';
            $suggestions[] = 'Make sure there are no extra spaces in the credentials';
            $suggestions[] = 'Use the "Test Connection" button to verify credentials';
        } elseif (strpos($errorMessage, 'Unknown database') !== false || $errorCode == 1049) {
            $userMessage .= 'Database does not exist.';
            $suggestions[] = 'Verify the database name in cPanel → MySQL Databases';
            $suggestions[] = 'Database names in cPanel usually start with your cPanel username';
        } elseif (strpos($errorMessage, 'Connection refused') !== false || 
                   strpos($errorMessage, 'Connection timed out') !== false ||
                   $errorCode == 2002 || $errorCode == 2003) {
            $userMessage .= 'Cannot connect to database host.';
            $suggestions[] = 'Check if the host is correct (usually "localhost" for cPanel)';
            $suggestions[] = 'Verify the port number (usually 3306)';
            $suggestions[] = 'If using remote host, check if remote MySQL is enabled in cPanel';
        } elseif (strpos($errorMessage, 'No such file or directory') !== false) {
            $userMessage .= 'Database socket connection failed.';
            $suggestions[] = 'Try using "127.0.0.1" instead of "localhost"';
            $suggestions[] = 'Or check with your hosting provider for the correct host';
        } else {
            $userMessage .= $errorMessage;
        }
        
        $operationLog[] = ['step' => 5, 'status' => 'error', 'message' => '✗ Connection failed: ' . $e->getMessage()];
        JsonResponse::error($userMessage, 500, [
            'error_code' => $errorCode,
            'suggestions' => $suggestions,
            'log' => $operationLog,
        ]);
    }

    // Parse and execute SQL
    $operationLog[] = ['step' => 6, 'status' => 'info', 'message' => 'Parsing SQL statements...'];
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    $escaped = false;
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        $prevChar = $i > 0 ? $sql[$i - 1] : '';
        
        if ($escaped) {
            $current .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\' && $inString) {
            $escaped = true;
            $current .= $char;
            continue;
        }
        
        if (!$inString && ($char === '"' || $char === "'" || $char === '`')) {
            $inString = true;
            $stringChar = $char;
            $current .= $char;
        } elseif ($inString && $char === $stringChar) {
            if (($char === '"' || $char === '`') && $i + 1 < strlen($sql) && $sql[$i + 1] === $char) {
                $current .= $char . $char;
                $i++;
            } else {
                $inString = false;
                $current .= $char;
            }
        } elseif (!$inString && $char === ';') {
            $statement = trim($current);
            if (!empty($statement) && !preg_match('/^(SET|USE|\/\*|\-\-)/i', $statement)) {
                $statements[] = $statement;
            }
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    if (!empty(trim($current))) {
        $statements[] = trim($current);
    }
    
    $operationLog[] = ['step' => 6, 'status' => 'success', 'message' => '✓ Parsed ' . count($statements) . ' SQL statements'];
    $operationLog[] = ['step' => 7, 'status' => 'info', 'message' => 'Executing SQL statements on cPanel database...'];

    // Execute statements
    $executed = 0;
    $errors = [];
    $totalStatements = count($statements);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }

        try {
            $cpanelDb->exec($statement);
            $executed++;
            
            // Log progress every 10 statements
            if (($executed % 10 === 0) || $executed === $totalStatements) {
                $operationLog[] = ['step' => 7, 'status' => 'info', 'message' => 'Progress: ' . $executed . '/' . $totalStatements . ' statements executed'];
            }
        } catch (\PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $operationLog[] = ['step' => 7, 'status' => 'warning', 'message' => '⚠ ' . count($errors) . ' errors occurred during execution'];
    } else {
        $operationLog[] = ['step' => 7, 'status' => 'success', 'message' => '✓ All statements executed successfully'];
    }

    // Step 4: Verify data integrity (optional but recommended)
    $verificationResults = null;
    $verifyAfterSync = $payload['verify_after_sync'] ?? true;
    
    if ($verifyAfterSync && $syncMode === 'full') {
        $operationLog[] = ['step' => 8, 'status' => 'info', 'message' => 'Verifying synced data...'];
        $verificationResults = verifyDataSync($localDb, $cpanelDb, $tables);
        
        if ($verificationResults['success']) {
            $operationLog[] = ['step' => 8, 'status' => 'success', 'message' => '✓ Verification passed: All data synced correctly'];
        } else {
            $operationLog[] = ['step' => 8, 'status' => 'warning', 'message' => '⚠ Verification found ' . count($verificationResults['issues']) . ' issues'];
        }
    }

    // Save last push timestamp
    $operationLog[] = ['step' => 9, 'status' => 'info', 'message' => 'Saving operation timestamp...'];
    $repository->set('db_sync_last_push', date('Y-m-d H:i:s'));
    $operationLog[] = ['step' => 9, 'status' => 'success', 'message' => '✓ Timestamp saved'];
    $operationLog[] = ['step' => 10, 'status' => 'success', 'message' => '✅ Push operation completed successfully!'];

    ob_end_clean();

    $message = "Push completed successfully! Executed {$executed} statements.";
    $message .= " Mode: " . ($dataMode === 'overwrite' ? 'Overwrite' : 'Append');
    if (!empty($errors)) {
        $message .= " " . count($errors) . " errors occurred (check logs for details).";
        error_log("Database sync errors: " . implode("\n", $errors));
    }
    if ($backupMessage) {
        $message .= " " . $backupMessage;
    }
    if ($verificationResults && !$verificationResults['success']) {
        $message .= " Verification found issues - check details.";
    }

    JsonResponse::success([
        'executed' => $executed,
        'total_statements' => count($statements),
        'errors' => count($errors),
        'sync_mode' => $syncMode,
        'data_mode' => $dataMode,
        'tables_synced' => $tableCount,
        'rows_synced' => $rowCount,
        'url_replacements' => $urlReplacements,
        'verification' => $verificationResults,
        'message' => $message,
        'log' => $operationLog,
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database sync error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $operationLog[] = ['step' => 0, 'status' => 'error', 'message' => '✗ Fatal error: ' . $e->getMessage()];
    JsonResponse::error('Sync failed: ' . $e->getMessage(), 500, ['log' => $operationLog]);
}

/**
 * Process a value for database sync, handling all data types correctly
 */
function processValueForSync($value, $column, $columnInfo, $pdo, $localUrls, $productionUrl, &$urlReplacements): array
{
    $result = [
        'value' => 'NULL',
        'replaced_url' => false,
    ];
    
    if ($value === null) {
        return $result;
    }
    
    // Handle boolean
    if (is_bool($value)) {
        $result['value'] = $value ? '1' : '0';
        return $result;
    }
    
    // Handle numeric types
    if (is_int($value) || is_float($value)) {
        $result['value'] = (string) $value;
        return $result;
    }
    
    // Handle string types
    $stringValue = (string) $value;
    
    // Check if this is a JSON column
    $isJsonColumn = false;
    foreach ($columnInfo as $col) {
        if ($col['Field'] === $column && stripos($col['Type'], 'JSON') !== false) {
            $isJsonColumn = true;
            break;
        }
    }
    
    // Handle JSON fields - detect and process JSON data
    if ($isJsonColumn || (strpos($stringValue, '{') === 0 || strpos($stringValue, '[') === 0)) {
        $jsonData = json_decode($stringValue, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            // Recursively replace URLs in JSON
            $jsonString = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            if (!empty($productionUrl)) {
                foreach ($localUrls as $localUrl) {
                    if (strpos($jsonString, $localUrl) !== false) {
                        $jsonString = str_replace($localUrl, $productionUrl, $jsonString);
                        $result['replaced_url'] = true;
                    }
                }
            }
            
            // Re-encode if changed
            if ($result['replaced_url']) {
                $jsonData = json_decode($jsonString, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $stringValue = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            } else {
                $stringValue = $jsonString;
            }
        }
    }
    
    // Replace URLs in regular strings
    if (!empty($productionUrl) && !empty($stringValue)) {
        foreach ($localUrls as $localUrl) {
            if (strpos($stringValue, $localUrl) !== false) {
                $stringValue = str_replace($localUrl, $productionUrl, $stringValue);
                $result['replaced_url'] = true;
            }
        }
    }
    
    // Quote the value properly (handles special characters, encoding, etc.)
    $result['value'] = $pdo->quote($stringValue);
    
    return $result;
}

/**
 * Verify that data was synced correctly by comparing row counts
 */
function verifyDataSync(PDO $localDb, PDO $cpanelDb, array $tables): array
{
    $results = [
        'success' => true,
        'issues' => [],
        'verified_tables' => 0,
        'verified_rows' => 0,
    ];
    
    foreach ($tables as $table) {
        try {
            // Check if table exists
            $cpanelTables = $cpanelDb->query("SHOW TABLES LIKE '{$table}'")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($cpanelTables)) {
                $results['issues'][] = "Table `{$table}` does not exist in cPanel";
                $results['success'] = false;
                continue;
            }
            
            // Check row count
            $localCount = (int) $localDb->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $cpanelCount = (int) $cpanelDb->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            
            if ($localCount !== $cpanelCount) {
                $results['issues'][] = "Table `{$table}`: Row count mismatch (Local: {$localCount}, cPanel: {$cpanelCount})";
                $results['success'] = false;
            } else {
                $results['verified_tables']++;
                $results['verified_rows'] += $localCount;
            }
        } catch (\PDOException $e) {
            $results['issues'][] = "Table `{$table}`: Verification error - " . $e->getMessage();
            $results['success'] = false;
        }
    }
    
    return $results;
}

