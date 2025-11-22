<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

abstract class Migration
{
    public function __construct(private readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function up(PDO $pdo): void;

    abstract public function down(PDO $pdo): void;
}

