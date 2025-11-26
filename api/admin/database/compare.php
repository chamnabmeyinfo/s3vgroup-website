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

    // Connect to cPanel database
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
    } catch (\PDOException $e) {
        ob_end_clean();
        JsonResponse::error('Failed to connect to cPanel database: ' . $e->getMessage(), 500);
    }

    // Get tables from both databases
    $localTables = [];
    $cpanelTables = [];
    
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
        ob_end_clean();
        JsonResponse::error('Failed to retrieve local database tables: ' . $e->getMessage(), 500);
    }

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

    // Compare tables
    $report = [
        'tables' => [
            'new_in_local' => [],
            'new_in_cpanel' => [],
            'common' => [],
        ],
        'data' => [
            'new_in_local' => [],
            'new_in_cpanel' => [],
            'updated' => [],
            'removed_from_local' => [],
            'removed_from_cpanel' => [],
        ],
        'summary' => [
            'local_tables' => count($localTables),
            'cpanel_tables' => count($cpanelTables),
            'common_tables' => 0,
            'new_tables_local' => 0,
            'new_tables_cpanel' => 0,
            'total_new_records_local' => 0,
            'total_new_records_cpanel' => 0,
            'total_updated_records' => 0,
            'total_removed_local' => 0,
            'total_removed_cpanel' => 0,
        ],
    ];

    // Find new and common tables
    $localTablesSet = array_flip($localTables);
    $cpanelTablesSet = array_flip($cpanelTables);

    foreach ($localTables as $table) {
        if (!isset($cpanelTablesSet[$table])) {
            $report['tables']['new_in_local'][] = $table;
            $report['summary']['new_tables_local']++;
        } else {
            $report['tables']['common'][] = $table;
            $report['summary']['common_tables']++;
        }
    }

    foreach ($cpanelTables as $table) {
        if (!isset($localTablesSet[$table])) {
            $report['tables']['new_in_cpanel'][] = $table;
            $report['summary']['new_tables_cpanel']++;
        }
    }

    // Compare data in common tables
    foreach ($report['tables']['common'] as $table) {
        try {
            // Get all records from local
            $localRows = $localDb->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            $localData = [];
            foreach ($localRows as $row) {
                // Use first column as key (usually ID)
                $key = reset($row);
                if ($key !== false) {
                    $localData[$key] = $row;
                }
            }

            // Get all records from cPanel
            $cpanelRows = $cpanelDb->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            $cpanelData = [];
            foreach ($cpanelRows as $row) {
                $key = reset($row);
                if ($key !== false) {
                    $cpanelData[$key] = $row;
                }
            }

            // Find new, updated, and removed records
            foreach ($localData as $key => $localRow) {
                if (!isset($cpanelData[$key])) {
                    // New in local
                    $report['data']['new_in_local'][] = [
                        'table' => $table,
                        'key' => $key,
                        'data' => $localRow,
                    ];
                    $report['summary']['total_new_records_local']++;
                } else {
                    // Check if updated
                    $cpanelRow = $cpanelData[$key];
                    // Sort arrays for comparison
                    ksort($localRow);
                    ksort($cpanelRow);
                    $localJson = json_encode($localRow, JSON_UNESCAPED_SLASHES);
                    $cpanelJson = json_encode($cpanelRow, JSON_UNESCAPED_SLASHES);
                    
                    if ($localJson !== $cpanelJson) {
                        // Find which fields changed
                        $changes = [];
                        foreach ($localRow as $field => $localValue) {
                            if (isset($cpanelRow[$field])) {
                                if ($localValue != $cpanelRow[$field]) {
                                    $changes[$field] = [
                                        'local' => $localValue,
                                        'cpanel' => $cpanelRow[$field],
                                    ];
                                }
                            }
                        }
                        
                        $report['data']['updated'][] = [
                            'table' => $table,
                            'key' => $key,
                            'changes' => $changes,
                            'local_data' => $localRow,
                            'cpanel_data' => $cpanelRow,
                        ];
                        $report['summary']['total_updated_records']++;
                    }
                }
            }

            foreach ($cpanelData as $key => $cpanelRow) {
                if (!isset($localData[$key])) {
                    // New in cPanel (removed from local)
                    $report['data']['new_in_cpanel'][] = [
                        'table' => $table,
                        'key' => $key,
                        'data' => $cpanelRow,
                    ];
                    $report['summary']['total_new_records_cpanel']++;
                }
            }

        } catch (\PDOException $e) {
            error_log("Error comparing table {$table}: " . $e->getMessage());
            // Continue with next table
        }
    }

    // Also check tables that exist only in one database
    foreach ($report['tables']['new_in_local'] as $table) {
        try {
            $localRows = $localDb->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($localRows as $row) {
                $key = reset($row);
                $report['data']['new_in_local'][] = [
                    'table' => $table,
                    'key' => $key !== false ? $key : 'unknown',
                    'data' => $row,
                ];
                $report['summary']['total_new_records_local']++;
            }
        } catch (\PDOException $e) {
            error_log("Error reading new local table {$table}: " . $e->getMessage());
        }
    }

    foreach ($report['tables']['new_in_cpanel'] as $table) {
        try {
            $cpanelRows = $cpanelDb->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cpanelRows as $row) {
                $key = reset($row);
                $report['data']['new_in_cpanel'][] = [
                    'table' => $table,
                    'key' => $key !== false ? $key : 'unknown',
                    'data' => $row,
                ];
                $report['summary']['total_new_records_cpanel']++;
            }
        } catch (\PDOException $e) {
            error_log("Error reading new cPanel table {$table}: " . $e->getMessage());
        }
    }

    ob_end_clean();
    JsonResponse::success($report);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database compare error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::error('Compare failed: ' . $e->getMessage(), 500);
}

