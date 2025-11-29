<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    private const TABLE = 'migrations';

    /** @var PDO */
    private $pdo;

    /** @var string */
    private $directory;

    public function __construct(
        PDO $pdo,
        string $directory
    ) {
        $this->pdo = $pdo;
        $this->directory = $directory;
        $this->ensureDirectory();
        $this->createMigrationsTable();
    }

    public function migrate(): void
    {
        $batch = $this->nextBatch();

        foreach ($this->migrationFiles() as $file) {
            /** @var Migration $migration */
            $migration = require $file;

            if (!$migration instanceof Migration) {
                throw new RuntimeException(sprintf('Invalid migration returned from %s', $file));
            }

            if ($this->hasRun($migration->getName())) {
                continue;
            }

            $inTransaction = $this->pdo->inTransaction();
            
            if (!$inTransaction) {
                $this->pdo->beginTransaction();
            }

            try {
                $migration->up($this->pdo);
                $this->record($migration->getName(), $batch);
                
                if (!$inTransaction && $this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
            } catch (\Throwable $throwable) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $throwable;
            }
        }
    }

    public function rollback(int $steps = 1): void
    {
        $steps = max(1, $steps);

        while ($steps > 0) {
            $batch = $this->currentBatch();

            if ($batch === 0) {
                break;
            }

            $migrations = $this->migrationsForBatch($batch);

            foreach (array_reverse($migrations) as $migrationName) {
                $file = $this->fileForMigration($migrationName);

                if (!file_exists($file)) {
                    continue;
                }

                /** @var Migration $migration */
                $migration = require $file;

                $this->pdo->beginTransaction();

                try {
                    $migration->down($this->pdo);
                    $this->forget($migrationName);
                    $this->pdo->commit();
                } catch (\Throwable $throwable) {
                    $this->pdo->rollBack();
                    throw $throwable;
                }
            }

            $steps--;
        }
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec(
            sprintf(
                'CREATE TABLE IF NOT EXISTS %s (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL, batch INT NOT NULL)',
                self::TABLE
            )
        );
    }

    private function nextBatch(): int
    {
        return $this->currentBatch() + 1;
    }

    private function currentBatch(): int
    {
        $statement = $this->pdo->query(sprintf('SELECT MAX(batch) as batch FROM %s', self::TABLE));
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['batch'] ?? 0);
    }

    private function record(string $migration, int $batch): void
    {
        $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (migration, batch) VALUES (:migration, :batch)', self::TABLE));
        $statement->execute([
            ':migration' => $migration,
            ':batch'     => $batch,
        ]);
    }

    private function forget(string $migration): void
    {
        $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE migration = :migration', self::TABLE));
        $statement->execute([':migration' => $migration]);
    }

    private function hasRun(string $migration): bool
    {
        $statement = $this->pdo->prepare(sprintf('SELECT COUNT(*) as count FROM %s WHERE migration = :migration', self::TABLE));
        $statement->execute([':migration' => $migration]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ((int) ($result['count'] ?? 0)) > 0;
    }

    private function migrationFiles(): array
    {
        $files = glob($this->directory . '/*.php') ?: [];
        sort($files);

        return $files;
    }

    private function migrationsForBatch(int $batch): array
    {
        $statement = $this->pdo->prepare(sprintf('SELECT migration FROM %s WHERE batch = :batch ORDER BY id DESC', self::TABLE));
        $statement->execute([':batch' => $batch]);

        return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'migration');
    }

    private function fileForMigration(string $name): string
    {
        $path = $this->directory . '/' . $name . '.php';

        if (!file_exists($path)) {
            $matches = glob($this->directory . '/*' . $name . '*.php') ?: [];
            return $matches[0] ?? $path;
        }

        return $path;
    }
}

