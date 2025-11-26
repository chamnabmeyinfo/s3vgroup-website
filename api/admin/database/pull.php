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

    // Get pull options
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $createBackup = $payload['create_backup'] ?? true;
    $pullMode = $payload['pull_mode'] ?? 'full'; // 'full' or 'structure_only'

    // Initialize operation log
    $operationLog = [];
    $operationLog[] = ['step' => 1, 'status' => 'info', 'message' => 'Starting pull operation from cPanel to local...'];
    $operationLog[] = ['step' => 1, 'status' => 'info', 'message' => 'Mode: ' . ($pullMode === 'full' ? 'Full Pull (structure + data)' : 'Structure Only')];

    // Step 1: Connect to cPanel database
    $operationLog[] = ['step' => 2, 'status' => 'info', 'message' => 'Connecting to cPanel database...'];
    try {
        $cpanelDb = new PDO(
            "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
            $cpanelConfig['username'],
            $cpanelConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30,
            ]
        );
        $operationLog[] = ['step' => 2, 'status' => 'success', 'message' => '✓ Connected to cPanel database successfully'];
    } catch (\PDOException $e) {
        ob_end_clean();
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        $operationLog[] = ['step' => 2, 'status' => 'error', 'message' => '✗ Connection failed: ' . $errorMessage];
        
        $userMessage = 'Failed to connect to cPanel database. ';
        $suggestions = [];
        
        if (strpos($errorMessage, 'Access denied') !== false || $errorCode == 1045) {
            $userMessage .= 'Invalid username or password.';
            $suggestions[] = 'Double-check your database username and password in cPanel';
            $suggestions[] = 'Use the "Test Connection" button to verify credentials';
        } elseif (strpos($errorMessage, 'Unknown database') !== false || $errorCode == 1049) {
            $userMessage .= 'Database name is incorrect.';
            $suggestions[] = 'Verify the database name in cPanel → MySQL Databases';
        } else {
            $userMessage .= $errorMessage;
        }
        
        JsonResponse::error($userMessage, 500, ['suggestions' => $suggestions, 'log' => $operationLog]);
    }

    // Step 2: Create backup of local database if requested
    $backupMessage = '';
    if ($createBackup) {
        $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => 'Creating backup of local database...'];
        try {
            $localTables = [];
            try {
                $localResult = $localDb->query("SHOW TABLES");
                if ($localResult) {
                    $localTables = $localResult->fetchAll(PDO::FETCH_COLUMN);
                    if (empty($localTables)) {
                        $localResult = $localDb->query("SHOW TABLES");
                        $allRows = $localResult->fetchAll(PDO::FETCH_NUM);
                        foreach ($allRows as $row) {
                            if (isset($row[0])) {
                                $localTables[] = $row[0];
                            }
                        }
                    }
                }
            } catch (\PDOException $e) {
                error_log("Failed to fetch local tables for backup: " . $e->getMessage());
            }

            if (!empty($localTables)) {
                $backupDir = __DIR__ . '/../../../tmp';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                $backupId = date('Y-m-d-His');
                $backupFile = $backupDir . '/backup-before-pull-' . $backupId . '.sql';
                
                $backupOutput = [];
                $backupOutput[] = "-- Backup created before pull: " . date('Y-m-d H:i:s');
                $backupOutput[] = "";

                foreach ($localTables as $table) {
                    try {
                        $backupOutput[] = "DROP TABLE IF EXISTS `{$table}`;";
                        $createTableResult = $localDb->query("SHOW CREATE TABLE `{$table}`");
                        $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
                        if ($createTable && isset($createTable['Create Table'])) {
                            $backupOutput[] = $createTable['Create Table'] . ";";
                            $backupOutput[] = "";
                        }

                        $rowsResult = $localDb->query("SELECT * FROM `{$table}`");
                        $rows = $rowsResult ? $rowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
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
                                        $values[] = $localDb->quote((string) $value);
                                    }
                                }
                                $backupOutput[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                            }
                            $backupOutput[] = "";
                        }
                    } catch (\PDOException $e) {
                        error_log("Error backing up table {$table}: " . $e->getMessage());
                    }
                }

                file_put_contents($backupFile, implode("\n", $backupOutput));
                $backupMessage = "Local backup created: " . basename($backupFile);
                $operationLog[] = ['step' => 3, 'status' => 'success', 'message' => '✓ Backup created: ' . basename($backupFile) . ' (' . count($localTables) . ' tables)'];
            } else {
                $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => 'No local tables to backup'];
            }
        } catch (\Throwable $e) {
            error_log("Backup creation failed: " . $e->getMessage());
            $backupMessage = "Warning: Local backup creation failed, but pull will continue.";
            $operationLog[] = ['step' => 3, 'status' => 'warning', 'message' => '⚠ Backup creation failed, but continuing...'];
        }
    } else {
        $operationLog[] = ['step' => 3, 'status' => 'info', 'message' => 'Skipping backup (disabled)'];
    }

    // Step 3: Export from cPanel database
    $operationLog[] = ['step' => 4, 'status' => 'info', 'message' => 'Fetching table list from cPanel...'];
    $cpanelTables = [];
    try {
        $cpanelResult = $cpanelDb->query("SHOW TABLES");
        if ($cpanelResult) {
            $cpanelTables = $cpanelResult->fetchAll(PDO::FETCH_COLUMN);
            if (empty($cpanelTables)) {
                $cpanelResult = $cpanelDb->query("SHOW TABLES");
                $allRows = $cpanelResult->fetchAll(PDO::FETCH_NUM);
                foreach ($allRows as $row) {
                    if (isset($row[0])) {
                        $cpanelTables[] = $row[0];
                    }
                }
            }
        }
    } catch (\PDOException $e) {
        ob_end_clean();
        JsonResponse::error('Failed to retrieve cPanel database tables: ' . $e->getMessage(), 500);
    }

    if (empty($cpanelTables)) {
        ob_end_clean();
        $operationLog[] = ['step' => 4, 'status' => 'error', 'message' => '✗ No tables found in cPanel database'];
        JsonResponse::error('No tables found in cPanel database.', 404, ['log' => $operationLog]);
    }
    
    $operationLog[] = ['step' => 4, 'status' => 'success', 'message' => '✓ Found ' . count($cpanelTables) . ' tables in cPanel database'];
    
    // Step 4.5: Drop ALL local tables first to ensure complete overwrite
    $operationLog[] = ['step' => 5, 'status' => 'info', 'message' => 'Dropping all local tables for complete overwrite...'];
    $localTables = [];
    try {
        $localResult = $localDb->query("SHOW TABLES");
        if ($localResult) {
            $localTables = $localResult->fetchAll(PDO::FETCH_COLUMN);
            if (empty($localTables)) {
                $localResult = $localDb->query("SHOW TABLES");
                $allRows = $localResult->fetchAll(PDO::FETCH_NUM);
                foreach ($allRows as $row) {
                    if (isset($row[0])) {
                        $localTables[] = $row[0];
                    }
                }
            }
        }
    } catch (\PDOException $e) {
        error_log("Failed to fetch local tables: " . $e->getMessage());
    }
    
    if (!empty($localTables)) {
        // Disable foreign key checks temporarily
        $localDb->exec("SET FOREIGN_KEY_CHECKS = 0");
        $droppedCount = 0;
        foreach ($localTables as $localTable) {
            try {
                $localDb->exec("DROP TABLE IF EXISTS `{$localTable}`");
                $droppedCount++;
            } catch (\PDOException $e) {
                error_log("Error dropping local table {$localTable}: " . $e->getMessage());
            }
        }
        $localDb->exec("SET FOREIGN_KEY_CHECKS = 1");
        $operationLog[] = ['step' => 5, 'status' => 'success', 'message' => '✓ Dropped ' . $droppedCount . ' local tables (complete overwrite ensured)'];
    } else {
        $operationLog[] = ['step' => 5, 'status' => 'info', 'message' => 'No local tables to drop'];
    }
    
    $operationLog[] = ['step' => 6, 'status' => 'info', 'message' => 'Exporting data from cPanel...'];

    $output = [];
    $output[] = "-- Database Pull from cPanel";
    $output[] = "-- Generated: " . date('Y-m-d H:i:s');
    $output[] = "-- Source: cPanel Production";
    $output[] = "-- Target: Local Development";
    $output[] = "";
    $output[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
    $output[] = "SET time_zone = \"+00:00\";";
    $output[] = "";

    $tableCount = 0;
    $rowCount = 0;
    foreach ($cpanelTables as $table) {
        try {
            $tableCount++;
            // Always include structure
            $output[] = "DROP TABLE IF EXISTS `{$table}`;";
            
            $createTableResult = $cpanelDb->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
            if ($createTable && isset($createTable['Create Table'])) {
                $output[] = $createTable['Create Table'] . ";";
                $output[] = "";
            }

            // Include data if full pull
            if ($pullMode === 'full') {
                $rowsResult = $cpanelDb->query("SELECT * FROM `{$table}`");
                $rows = $rowsResult ? $rowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
                
                if (!empty($rows)) {
                    $rowCount += count($rows);
                    $output[] = "-- Dumping data for table `{$table}`";
                    $output[] = "";
                    
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
                        $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                    }
                    $output[] = "";
                }
            }
        } catch (\PDOException $e) {
            error_log("Error processing table {$table}: " . $e->getMessage());
            $operationLog[] = ['step' => 5, 'status' => 'warning', 'message' => '⚠ Error processing table: ' . $table];
            // Continue with next table
        }
    }
    
    $operationLog[] = ['step' => 6, 'status' => 'success', 'message' => '✓ Exported ' . $tableCount . ' tables' . ($pullMode === 'full' ? ' with ' . number_format($rowCount) . ' rows' : ' (structure only)')];

    $sql = implode("\n", $output);
    $operationLog[] = ['step' => 7, 'status' => 'info', 'message' => 'Parsing SQL statements...'];

    // Step 4: Import to local database
    // Parse and execute SQL
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
    
    $operationLog[] = ['step' => 7, 'status' => 'success', 'message' => '✓ Parsed ' . count($statements) . ' SQL statements'];
    $operationLog[] = ['step' => 8, 'status' => 'info', 'message' => 'Executing SQL statements on local database (full overwrite)...'];

    // Execute statements
    $executed = 0;
    $errors = [];
    $totalStatements = count($statements);

    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }

        try {
            $localDb->exec($statement);
            $executed++;
            
            // Log progress every 10 statements
            if (($executed % 10 === 0) || $executed === $totalStatements) {
                $operationLog[] = ['step' => 8, 'status' => 'info', 'message' => 'Progress: ' . $executed . '/' . $totalStatements . ' statements executed'];
            }
        } catch (\PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $operationLog[] = ['step' => 8, 'status' => 'warning', 'message' => '⚠ ' . count($errors) . ' errors occurred during execution'];
    } else {
        $operationLog[] = ['step' => 8, 'status' => 'success', 'message' => '✓ All statements executed successfully - Local database fully overwritten'];
    }

    // Save last pull timestamp
    $operationLog[] = ['step' => 9, 'status' => 'info', 'message' => 'Saving operation timestamp...'];
    $repository->set('db_sync_last_pull', date('Y-m-d H:i:s'));
    $operationLog[] = ['step' => 9, 'status' => 'success', 'message' => '✓ Timestamp saved'];
    $operationLog[] = ['step' => 10, 'status' => 'success', 'message' => '✅ Pull operation completed! Local database fully overwritten with cPanel data.'];

    ob_end_clean();

    $message = "Pull completed successfully! Executed {$executed} statements.";
    if (!empty($errors)) {
        $message .= " " . count($errors) . " errors occurred (check logs for details).";
        error_log("Database pull errors: " . implode("\n", $errors));
    }
    if ($backupMessage) {
        $message .= " " . $backupMessage;
    }

    JsonResponse::success([
        'executed' => $executed,
        'total_statements' => count($statements),
        'errors' => count($errors),
        'pull_mode' => $pullMode,
        'message' => $message,
        'log' => $operationLog,
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database pull error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $operationLog[] = ['step' => 0, 'status' => 'error', 'message' => '✗ Fatal error: ' . $e->getMessage()];
    JsonResponse::error('Pull failed: ' . $e->getMessage(), 500, ['log' => $operationLog]);
}

