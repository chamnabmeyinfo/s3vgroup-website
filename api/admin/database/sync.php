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

    // Step 1: Export from local database
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
        JsonResponse::error('No tables found in local database.', 404);
    }

    $output = [];
    $output[] = "-- Database Sync Export";
    $output[] = "-- Generated: " . date('Y-m-d H:i:s');
    $output[] = "-- Source: Local Development";
    $output[] = "-- Target: cPanel Production";
    $output[] = "";
    $output[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
    $output[] = "SET time_zone = \"+00:00\";";
    $output[] = "";

    foreach ($tables as $table) {
        try {
            // Always include structure
            $output[] = "DROP TABLE IF EXISTS `{$table}`;";
            
            $createTableResult = $localDb->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
            if ($createTable && isset($createTable['Create Table'])) {
                $output[] = $createTable['Create Table'] . ";";
                $output[] = "";
            }

            // Include data if full sync
            if ($syncMode === 'full') {
                $rowsResult = $localDb->query("SELECT * FROM `{$table}`");
                $rows = $rowsResult ? $rowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
                
                if (!empty($rows)) {
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
                                $values[] = $localDb->quote((string) $value);
                            }
                        }
                        $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                    }
                    $output[] = "";
                }
            }
        } catch (\PDOException $e) {
            error_log("Error processing table {$table}: " . $e->getMessage());
            // Continue with next table
        }
    }

    $sql = implode("\n", $output);

    // Step 2: Create backup if requested
    $backupMessage = '';
    if ($createBackup) {
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
            }
        } catch (\Throwable $e) {
            error_log("Backup creation failed: " . $e->getMessage());
            $backupMessage = "Warning: Backup creation failed, but sync will continue.";
        }
    }

    // Step 3: Import to cPanel
    try {
        $cpanelDb = new PDO(
            "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
            $cpanelConfig['username'],
            $cpanelConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30, // 30 second timeout
            ]
        );
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
        
        JsonResponse::error($userMessage, 500, [
            'error_code' => $errorCode,
            'suggestions' => $suggestions,
        ]);
    }

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

    // Execute statements
    $executed = 0;
    $errors = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }

        try {
            $cpanelDb->exec($statement);
            $executed++;
        } catch (\PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }

    ob_end_clean();

    $message = "Sync completed successfully! Executed {$executed} statements.";
    if (!empty($errors)) {
        $message .= " " . count($errors) . " errors occurred (check logs for details).";
        error_log("Database sync errors: " . implode("\n", $errors));
    }
    if ($backupMessage) {
        $message .= " " . $backupMessage;
    }

    JsonResponse::success([
        'executed' => $executed,
        'total_statements' => count($statements),
        'errors' => count($errors),
        'sync_mode' => $syncMode,
        'message' => $message,
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database sync error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::error('Sync failed: ' . $e->getMessage(), 500);
}

