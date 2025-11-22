<?php

declare(strict_types=1);

namespace App\Support;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            $prefix = 'App\\';
            $length = strlen($prefix);

            if (strncmp($prefix, $class, $length) !== 0) {
                return;
            }

            $relativeClass = substr($class, $length);
            $path = app_path(str_replace('\\', '/', $relativeClass) . '.php');

            if (file_exists($path)) {
                require_once $path;
            }
        });
    }
}

