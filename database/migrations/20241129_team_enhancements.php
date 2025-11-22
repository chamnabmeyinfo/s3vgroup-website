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
        return [
            <<<'SQL'
ALTER TABLE team_members 
ADD COLUMN IF NOT EXISTS twitter VARCHAR(500) NULL AFTER linkedin,
ADD COLUMN IF NOT EXISTS facebook VARCHAR(500) NULL AFTER twitter,
ADD COLUMN IF NOT EXISTS instagram VARCHAR(500) NULL AFTER facebook,
ADD COLUMN IF NOT EXISTS website VARCHAR(500) NULL AFTER instagram,
ADD COLUMN IF NOT EXISTS github VARCHAR(500) NULL AFTER website,
ADD COLUMN IF NOT EXISTS youtube VARCHAR(500) NULL AFTER github,
ADD COLUMN IF NOT EXISTS telegram VARCHAR(500) NULL AFTER youtube,
ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(100) NULL AFTER telegram,
ADD COLUMN IF NOT EXISTS department VARCHAR(255) NULL AFTER title,
ADD COLUMN IF NOT EXISTS location VARCHAR(255) NULL AFTER phone,
ADD COLUMN IF NOT EXISTS expertise TEXT NULL AFTER bio,
ADD COLUMN IF NOT EXISTS languages VARCHAR(255) NULL AFTER location
SQL,
        ];
    }
};

