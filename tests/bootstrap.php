<?php

/**
 * Test bootstrap file
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load application bootstrap
require_once BASE_PATH . '/bootstrap/app.php';

// Set test environment
define('APP_ENV', 'testing');
define('AE_DEBUG', true);

