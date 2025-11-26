<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Http\AdminGuard;
use App\Support\Id;

AdminGuard::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$includeStructure = $payload['structure'] ?? true;
$includeData = $payload['data'] ?? true;
$dropTables = $payload['drop_tables'] ?? false;

try {
    $db = getDB();
    
    // Get all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        throw new RuntimeException('No tables found in database');
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
                        } else {
                            $values[] = $db->quote($value);
                        }
                    }
                    $output[] = "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");";
                }
                $output[] = "";
            }
        }
    }

    $sql = implode("\n", $output);

    // Set headers for file download
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="database-export-' . date('Y-m-d-His') . '.sql"');
    header('Content-Length: ' . strlen($sql));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo $sql;
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Export failed: ' . $e->getMessage()
    ]);
    exit;
}

