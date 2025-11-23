<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241129_team_enhancements') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        // Remove the new columns
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS twitter');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS facebook');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS instagram');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS website');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS github');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS youtube');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS telegram');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS whatsapp');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS department');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS location');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS expertise');
        $pdo->exec('ALTER TABLE team_members DROP COLUMN IF EXISTS languages');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        // MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
        // So we need to check each column separately
        $columns = [
            'department' => "VARCHAR(255) NULL AFTER title",
            'expertise' => "TEXT NULL AFTER bio",
            'location' => "VARCHAR(255) NULL AFTER phone",
            'languages' => "VARCHAR(255) NULL AFTER location",
            'twitter' => "VARCHAR(500) NULL AFTER linkedin",
            'facebook' => "VARCHAR(500) NULL AFTER twitter",
            'instagram' => "VARCHAR(500) NULL AFTER facebook",
            'website' => "VARCHAR(500) NULL AFTER instagram",
            'github' => "VARCHAR(500) NULL AFTER website",
            'youtube' => "VARCHAR(500) NULL AFTER github",
            'telegram' => "VARCHAR(500) NULL AFTER youtube",
            'whatsapp' => "VARCHAR(100) NULL AFTER telegram",
        ];
        
        $statements = [];
        foreach ($columns as $column => $definition) {
            $statements[] = <<<SQL
ALTER TABLE team_members 
ADD COLUMN `{$column}` {$definition}
SQL;
        }
        
        return $statements;
    }
};

