<?php

declare(strict_types=1);

// Start output buffering to prevent any unwanted output
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Support\Id;

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

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$includeStructure = $payload['structure'] ?? true;
$includeData = $payload['data'] ?? true;
$dropTables = $payload['drop_tables'] ?? false;

try {
    $db = getDB();
    
    // Get all tables - handle different table name formats
    $tables = [];
    try {
        $result = $db->query("SHOW TABLES");
        if ($result) {
            // Try FETCH_COLUMN first (most common)
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            
            // If that didn't work, try FETCH_NUM and get first column
            if (empty($tables)) {
                $result = $db->query("SHOW TABLES");
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
        JsonResponse::error('Failed to retrieve database tables: ' . $e->getMessage(), 500);
    }
    
    if (empty($tables)) {
        ob_end_clean();
        JsonResponse::error('No tables found in database.', 404);
    }

    $output = [];
    $output[] = "-- Database Export";
    $output[] = "-- Generated: " . date('Y-m-d H:i:s');
    $output[] = "-- Database: " . DB_NAME;
    $output[] = "";
    $output[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
    $output[] = "SET time_zone = \"+00:00\";";
    $output[] = "";

    foreach ($tables as $table) {
        if ($includeStructure) {
            if ($dropTables) {
                $output[] = "DROP TABLE IF EXISTS `{$table}`;";
            }
            
            // Get table structure
            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            if ($createTable) {
                $output[] = $createTable['Create Table'] . ";";
                $output[] = "";
            }
        }

        if ($includeData) {
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            
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
                            $values[] = $db->quote((string) $value);
                        }
                    }
                    $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                }
                $output[] = "";
            }
        }
    }

    $sql = implode("\n", $output);

    // Clear output buffer before sending file
    ob_end_clean();

    // Set headers for file download
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="database-export-' . date('Y-m-d-His') . '.sql"');
    header('Content-Length: ' . strlen($sql));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo $sql;
    exit;

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database export error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::error('Export failed: ' . $e->getMessage(), 500);
}

