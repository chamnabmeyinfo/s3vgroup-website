<?php

declare(strict_types=1);

/**
 * Verify Database Sync - Compare local and cPanel databases
 * 
 * This endpoint compares local and cPanel databases to identify:
 * - Missing tables
 * - Missing rows
 * - Data differences
 * - Row count mismatches
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
        JsonResponse::error('cPanel database configuration is incomplete. Please configure it first.', 400);
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
            ]
        );
    } catch (\PDOException $e) {
        ob_end_clean();
        JsonResponse::error('Failed to connect to cPanel database: ' . $e->getMessage(), 500);
    }

    // Get all tables from local
    $localTables = [];
    $result = $localDb->query("SHOW TABLES");
    if ($result) {
        $localTables = $result->fetchAll(PDO::FETCH_COLUMN);
        if (empty($localTables)) {
            $result = $localDb->query("SHOW TABLES");
            $allRows = $result->fetchAll(PDO::FETCH_NUM);
            foreach ($allRows as $row) {
                if (isset($row[0])) {
                    $localTables[] = $row[0];
                }
            }
        }
    }

    // Get all tables from cPanel
    $cpanelTables = [];
    $result = $cpanelDb->query("SHOW TABLES");
    if ($result) {
        $cpanelTables = $result->fetchAll(PDO::FETCH_COLUMN);
        if (empty($cpanelTables)) {
            $result = $cpanelDb->query("SHOW TABLES");
            $allRows = $result->fetchAll(PDO::FETCH_NUM);
            foreach ($allRows as $row) {
                if (isset($row[0])) {
                    $cpanelTables[] = $row[0];
                }
            }
        }
    }

    $comparison = [
        'summary' => [
            'local_tables' => count($localTables),
            'cpanel_tables' => count($cpanelTables),
            'common_tables' => 0,
            'missing_in_cpanel' => [],
            'extra_in_cpanel' => [],
        ],
        'tables' => [],
        'issues' => [],
    ];

    // Compare tables
    foreach ($localTables as $table) {
        $localCount = (int) $localDb->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        
        if (in_array($table, $cpanelTables)) {
            $comparison['summary']['common_tables']++;
            $cpanelCount = (int) $cpanelDb->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            
            $tableInfo = [
                'table' => $table,
                'local_rows' => $localCount,
                'cpanel_rows' => $cpanelCount,
                'match' => $localCount === $cpanelCount,
                'difference' => $localCount - $cpanelCount,
            ];
            
            if ($localCount !== $cpanelCount) {
                $comparison['issues'][] = "Table `{$table}`: Row count mismatch (Local: {$localCount}, cPanel: {$cpanelCount})";
            }
            
            // Sample data comparison for key tables
            if (in_array($table, ['products', 'categories', 'site_options', 'themes'])) {
                // Get sample records to compare
                $localSample = $localDb->query("SELECT * FROM `{$table}` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                $cpanelSample = $cpanelDb->query("SELECT * FROM `{$table}` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                
                $tableInfo['local_sample_count'] = count($localSample);
                $tableInfo['cpanel_sample_count'] = count($cpanelSample);
            }
            
            $comparison['tables'][] = $tableInfo;
        } else {
            $comparison['summary']['missing_in_cpanel'][] = $table;
            $comparison['issues'][] = "Table `{$table}`: Missing in cPanel (Local has {$localCount} rows)";
        }
    }

    // Find extra tables in cPanel
    foreach ($cpanelTables as $table) {
        if (!in_array($table, $localTables)) {
            $comparison['summary']['extra_in_cpanel'][] = $table;
        }
    }

    // Overall status
    $isExactMatch = empty($comparison['issues']) && 
                    count($comparison['summary']['missing_in_cpanel']) === 0 &&
                    count($comparison['summary']['extra_in_cpanel']) === 0;

    ob_end_clean();

    JsonResponse::success([
        'exact_match' => $isExactMatch,
        'comparison' => $comparison,
        'message' => $isExactMatch 
            ? '✅ Databases match exactly!' 
            : '⚠️ ' . count($comparison['issues']) . ' issues found',
    ]);

} catch (\Throwable $e) {
    ob_end_clean();
    error_log('Database verification error: ' . $e->getMessage());
    JsonResponse::error('Verification failed: ' . $e->getMessage(), 500);
}

