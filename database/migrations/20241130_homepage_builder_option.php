<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241130_homepage_builder_option') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DELETE FROM site_options WHERE `key` = 'enable_homepage_builder'");
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
INSERT INTO site_options (id, key_name, `value`, type, group_name, label, description, priority)
VALUES (
    CONCAT('opt_', UNIX_TIMESTAMP()),
    'enable_homepage_builder',
    '0',
    'boolean',
    'Design',
    'Enable Homepage Builder',
    'Use drag-and-drop homepage builder instead of default sections',
    50
) ON DUPLICATE KEY UPDATE `value` = `value`
SQL,
        ];
    }
};

