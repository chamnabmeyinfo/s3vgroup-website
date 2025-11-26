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

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    ob_end_clean();
    JsonResponse::error('No SQL file uploaded or upload error occurred.', 400);
}

$file = $_FILES['file'];
$createBackup = isset($_POST['create_backup']) && $_POST['create_backup'] === '1';

// Validate file type
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension !== 'sql') {
    ob_end_clean();
    JsonResponse::error('Invalid file type. Only .sql files are allowed.', 400);
}

// Read SQL file
$sqlContent = file_get_contents($file['tmp_name']);
if ($sqlContent === false) {
    ob_end_clean();
    JsonResponse::error('Failed to read SQL file.', 500);
}

try {
    // Get cPanel database configuration
    $db = getDB();
    $repository = new SiteOptionRepository($db);
    $service = new SiteOptionService($repository);

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
        JsonResponse::error('cPanel database configuration is incomplete. Please configure it first.', 400);
    }

    // Create backup if requested
    $backupMessage = '';
    if ($createBackup) {
        try {
            $backupDb = new PDO(
                "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
                $cpanelConfig['username'],
                $cpanelConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $backupTables = $backupDb->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($backupTables)) {
                $backupId = Id::prefixed('backup');
                $backupDir = __DIR__ . '/../../../tmp';
                $backupFile = $backupDir . '/backup-' . $backupId . '.sql';
                
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }

                $backupOutput = [];
                $backupOutput[] = "-- Backup created: " . date('Y-m-d H:i:s');
                $backupOutput[] = "";

                foreach ($backupTables as $table) {
                    $backupOutput[] = "DROP TABLE IF EXISTS `{$table}`;";
                    $createTable = $backupDb->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                    if ($createTable) {
                        $backupOutput[] = $createTable['Create Table'] . ";";
                        $backupOutput[] = "";
                    }

                    $rows = $backupDb->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($rows)) {
                        $columns = array_keys($rows[0]);
                        $columnList = '`' . implode('`, `', $columns) . '`';
                        
                        foreach ($rows as $row) {
                            $values = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = $backupDb->quote($value);
                                }
                            }
                            $backupOutput[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                        }
                        $backupOutput[] = "";
                    }
                }

                file_put_contents($backupFile, implode("\n", $backupOutput));
                $backupMessage = "Backup created: " . basename($backupFile);
            }
        } catch (\Throwable $e) {
            // Log backup failure but continue with import
            error_log("Backup creation failed: " . $e->getMessage());
            $backupMessage = "Warning: Backup creation failed, but import will continue.";
        }
    }

    // Connect to cPanel database
    $cpanelDb = new PDO(
        "mysql:host={$cpanelConfig['host']};port={$cpanelConfig['port']};dbname={$cpanelConfig['database']};charset=utf8mb4",
        $cpanelConfig['username'],
        $cpanelConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Execute SQL statements
    // Use a more robust method: split by semicolon but preserve strings
    $statements = [];
    $delimiter = ';';
    $sql = $sqlContent;
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by delimiter, but respect strings
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
            // Check if it's escaped (double quote/backtick)
            if (($char === '"' || $char === '`') && $i + 1 < strlen($sql) && $sql[$i + 1] === $char) {
                $current .= $char . $char;
                $i++; // Skip next char
            } else {
                $inString = false;
                $current .= $char;
            }
        } elseif (!$inString && $char === $delimiter[0]) {
            $statement = trim($current);
            if (!empty($statement) && !preg_match('/^(SET|USE|\/\*|\-\-)/i', $statement)) {
                $statements[] = $statement;
            }
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    // Add remaining statement
    if (!empty(trim($current))) {
        $statements[] = trim($current);
    }

    // Execute statements
    $executed = 0;
    $errors = [];
    $errorDetails = [];

    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        // Skip empty statements
        if (empty($statement)) {
            continue;
        }

        try {
            $cpanelDb->exec($statement);
            $executed++;
        } catch (\PDOException $e) {
            $errors[] = $e->getMessage();
            $errorDetails[] = "Statement " . ($index + 1) . ": " . substr($statement, 0, 100) . "... Error: " . $e->getMessage();
            // Continue with next statement
        }
    }

    ob_end_clean();

    $message = "Import completed. Executed {$executed} statements.";
    if (!empty($errors)) {
        $message .= " " . count($errors) . " errors occurred. Check logs for details.";
        error_log("Database import errors: " . implode("\n", $errorDetails));
    }
    if ($backupMessage) {
        $message .= " " . $backupMessage;
    }

    JsonResponse::success([
        'executed' => $executed,
        'total_statements' => count($statements),
        'errors' => count($errors),
        'message' => $message,
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database import error: ' . $e->getMessage());
    JsonResponse::error('Import failed: ' . $e->getMessage(), 500);
}

