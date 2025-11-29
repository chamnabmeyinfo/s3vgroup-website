<?php

declare(strict_types=1);

/**
 * Export SQL File - Generate a downloadable SQL file for manual upload
 * 
 * This endpoint generates a complete SQL export file that matches
 * what phpMyAdmin exports, ensuring perfect compatibility with cPanel.
 */

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

    // Get production URL for replacement
    $productionUrl = $service->get('db_sync_production_url', 'https://s3vgroup.com');
    $localUrl = $service->get('db_sync_local_url', 'http://localhost/s3vgroup');
    
    // Get request payload
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $dataMode = $payload['data_mode'] ?? 'overwrite';
    $includeData = $payload['include_data'] ?? true;
    
    // Get all tables
    $tables = [];
    $result = $localDb->query("SHOW TABLES");
    if ($result) {
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            // Try alternative method
            $result = $localDb->query("SHOW TABLES");
            $allRows = $result->fetchAll(PDO::FETCH_NUM);
            foreach ($allRows as $row) {
                if (isset($row[0])) {
                    $tables[] = $row[0];
                }
            }
        }
    }

    if (empty($tables)) {
        ob_end_clean();
        JsonResponse::error('No tables found in local database.', 400);
    }

    // Generate SQL file content
    $output = [];
    
    // SQL Header
    $output[] = "-- SQL Export Generated: " . date('Y-m-d H:i:s');
    $output[] = "-- Mode: " . ($dataMode === 'overwrite' ? 'Overwrite (DROP and CREATE)' : 'Append (CREATE IF NOT EXISTS)');
    $output[] = "-- Local URL: {$localUrl}";
    $output[] = "-- Production URL: {$productionUrl}";
    $output[] = "";
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
                $output[] = "-- --------------------------------------------------------";
                $output[] = "-- Table structure for table `{$table}`";
                $output[] = "-- --------------------------------------------------------";
                $output[] = "";
                $output[] = "DROP TABLE IF EXISTS `{$table}`;";
                
                $createTableResult = $localDb->query("SHOW CREATE TABLE `{$table}`");
                $createTable = $createTableResult ? $createTableResult->fetch(PDO::FETCH_ASSOC) : null;
                if ($createTable && isset($createTable['Create Table'])) {
                    $output[] = $createTable['Create Table'] . ";";
                    $output[] = "";
                }
            } else {
                // Append mode: Only create table if it doesn't exist
                $output[] = "-- --------------------------------------------------------";
                $output[] = "-- Table structure for table `{$table}`";
                $output[] = "-- --------------------------------------------------------";
                $output[] = "";
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

            // Include data if requested
            if ($includeData) {
                $rowCountResult = $localDb->query("SELECT COUNT(*) FROM `{$table}`");
                $tableRowCount = $rowCountResult ? (int) $rowCountResult->fetchColumn() : 0;
                
                if ($tableRowCount > 0) {
                    $output[] = "-- --------------------------------------------------------";
                    $output[] = "-- Dumping data for table `{$table}`";
                    $output[] = "-- --------------------------------------------------------";
                    $output[] = "";
                    
                    // Get column metadata
                    $columnInfo = [];
                    try {
                        $columnsResult = $localDb->query("SHOW COLUMNS FROM `{$table}`");
                        if ($columnsResult) {
                            $columnInfo = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
                        }
                    } catch (\PDOException $e) {
                        // Continue without column info
                    }
                    
                    // Process in chunks
                    $chunkSize = 500;
                    $offset = 0;
                    
                    while ($offset < $tableRowCount) {
                        $rowsResult = $localDb->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}");
                        $rows = $rowsResult ? $rowsResult->fetchAll(PDO::FETCH_ASSOC) : [];
                        
                        if (empty($rows)) {
                            break;
                        }
                        
                        $columns = array_keys($rows[0]);
                        $columnList = '`' . implode('`, `', $columns) . '`';
                        
                        // Build multi-row INSERT
                        $valueGroups = [];
                        
                        foreach ($rows as $row) {
                            $values = [];
                            foreach ($row as $column => $value) {
                                $processed = processValueForExport($value, $column, $columnInfo, $localDb, $localUrl, $productionUrl);
                                $values[] = $processed;
                            }
                            $valueGroups[] = '(' . implode(', ', $values) . ')';
                            $rowCount++;
                        }
                        
                        // Generate INSERT statement
                        if (!empty($valueGroups)) {
                            if ($dataMode === 'overwrite') {
                                // Simple INSERT for overwrite mode
                                $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES";
                                $output[] = implode(",\n", $valueGroups) . ";";
                            } else {
                                // INSERT ... ON DUPLICATE KEY UPDATE for append mode
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
                                
                                $updateClause = [];
                                if ($primaryKey || !empty($uniqueKeys)) {
                                    foreach ($columns as $col) {
                                        if ($col !== $primaryKey && !in_array($col, $uniqueKeys)) {
                                            $updateClause[] = "`{$col}` = VALUES(`{$col}`)";
                                        }
                                    }
                                } else {
                                    // If no primary key, update all columns
                                    foreach ($columns as $col) {
                                        $updateClause[] = "`{$col}` = VALUES(`{$col}`)";
                                    }
                                }
                                
                                if (!empty($updateClause)) {
                                    $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES";
                                    $output[] = implode(",\n", $valueGroups);
                                    $output[] = "ON DUPLICATE KEY UPDATE " . implode(', ', $updateClause) . ";";
                                } else {
                                    $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES";
                                    $output[] = implode(",\n", $valueGroups) . ";";
                                }
                            }
                            $output[] = "";
                        }
                        
                        $offset += $chunkSize;
                    }
                }
            }
            
        } catch (\PDOException $e) {
            error_log("Error exporting table {$table}: " . $e->getMessage());
            $output[] = "-- Error exporting table `{$table}`: " . $e->getMessage();
            $output[] = "";
        }
    }
    
    $output[] = "SET FOREIGN_KEY_CHECKS = 1;";
    $output[] = "";
    $output[] = "-- Export completed: {$tableCount} tables, {$rowCount} rows";
    
    // Generate filename
    $filename = 's3vgroup-export-' . date('Y-m-d-His') . '.sql';
    
    // Save to temporary file
    $tempFile = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tempFile, implode("\n", $output));
    
    ob_end_clean();
    
    // Return file info for download
    JsonResponse::success([
        'filename' => $filename,
        'size' => filesize($tempFile),
        'tables' => $tableCount,
        'rows' => $rowCount,
        'download_url' => '/api/admin/database/download-export.php?file=' . urlencode($filename),
        'message' => "SQL export file generated successfully! {$tableCount} tables, {$rowCount} rows.",
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('SQL export error: ' . $e->getMessage());
    JsonResponse::error('Export failed: ' . $e->getMessage(), 500);
}

/**
 * Process a value for SQL export, handling all data types correctly
 */
function processValueForExport($value, $column, $columnInfo, $pdo, $localUrl, $productionUrl): string
{
    if ($value === null) {
        return 'NULL';
    }
    
    // Detect column type
    $columnType = null;
    foreach ($columnInfo as $col) {
        if ($col['Field'] === $column) {
            $columnType = strtoupper($col['Type']);
            break;
        }
    }
    
    // Handle different data types
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    
    if (is_int($value)) {
        return (string) $value;
    }
    
    if (is_float($value)) {
        return (string) $value;
    }
    
    // Handle JSON columns
    if ($columnType && (strpos($columnType, 'JSON') !== false)) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Replace URLs in JSON
                $value = json_encode($decoded);
                $value = str_replace($localUrl, $productionUrl, $value);
            }
        }
    }
    
    // Replace URLs in string values
    if (is_string($value)) {
        $value = str_replace($localUrl, $productionUrl, $value);
    }
    
    // Escape string for SQL
    return $pdo->quote($value);
}

