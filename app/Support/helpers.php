<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        return rtrim($base . ($path !== '' ? '/' . ltrim($path, '/\\') : ''), '/\\');
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path !== '' ? '/' . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path !== '' ? '/' . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path !== '' ? '/' . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return \App\Support\Env::get($key, $default);
    }
}

if (!function_exists('option')) {
    function option(string $key, $default = null)
    {
        return \App\Support\SiteOptionHelper::get($key, $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return \App\Support\AssetHelper::asset($path);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return \App\Support\AssetHelper::url($path);
    }
}

