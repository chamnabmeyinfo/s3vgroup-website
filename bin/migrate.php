<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;
use App\Database\MigrationRunner;

$command = $argv[1] ?? 'migrate';
$argument = $argv[2] ?? null;

$runner = new MigrationRunner(Connection::getInstance(), base_path('database/migrations'));

switch ($command) {
    case 'migrate':
        $runner->migrate();
        fwrite(STDOUT, "Database migrations executed successfully.\n");
        break;
    case 'migrate:rollback':
        $steps = $argument !== null ? (int) $argument : 1;
        $runner->rollback($steps);
        fwrite(STDOUT, sprintf("Rolled back %d batch(es).\n", $steps));
        break;
    default:
        fwrite(
            STDERR,
            "Unknown command. Available commands:\n" .
            "  php bin/migrate.php migrate\n" .
            "  php bin/migrate.php migrate:rollback [steps]\n"
        );
        exit(1);
}

