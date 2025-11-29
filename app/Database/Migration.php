<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

abstract class Migration
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function up(PDO $pdo): void;

    abstract public function down(PDO $pdo): void;
}

