<?php

declare(strict_types=1);

namespace App\Config;

final class DatabaseConfig
{
    private const DEFAULTS = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => 3306,
        'database'  => 's3vgroup_mcndbs3vgroup',
        'username'  => 's3vgroup_s3vgroup_mcn_db_user_s3vgroup',
        'password'  => 'ASDasd12345$$$%%%',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options'   => [],
    ];

    public static function build(array $overrides = []): array
    {
        $config = array_merge(self::DEFAULTS, $overrides);

        $config['driver'] = env('DB_CONNECTION', $config['driver']);
        $config['host'] = env('DB_HOST', $config['host']);
        $config['port'] = (int) env('DB_PORT', (string) $config['port']);
        $config['database'] = env('DB_DATABASE', $config['database']);
        $config['username'] = env('DB_USERNAME', $config['username']);
        $config['password'] = env('DB_PASSWORD', $config['password']);
        $config['charset'] = env('DB_CHARSET', $config['charset']);
        $config['collation'] = env('DB_COLLATION', $config['collation']);

        return $config;
    }

    public static function ensureConstants(array $config): void
    {
        self::defineConstant('DB_HOST', $config['host']);
        self::defineConstant('DB_NAME', $config['database']);
        self::defineConstant('DB_USER', $config['username']);
        self::defineConstant('DB_PASS', $config['password']);
        self::defineConstant('DB_CHARSET', $config['charset']);
    }

    private static function defineConstant(string $key, $value): void
    {
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

