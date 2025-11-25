<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/app/Support/helpers.php';
require_once base_path('app/Support/Autoloader.php');

// Load additional helper functions if available
if (file_exists(base_path('includes/helpers.php'))) {
    require_once base_path('includes/helpers.php');
}

\App\Support\Autoloader::register();
\App\Support\Env::load(base_path('.env'));
\App\Support\Env::load(base_path('env.example'));
\App\Support\CacheControl::apply();

