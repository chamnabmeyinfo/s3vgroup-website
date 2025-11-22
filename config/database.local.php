<?php
/**
 * Local Development Database Configuration
 *
 * Return an array of overrides that will be merged into the default
 * database configuration. You can still rely on the .env file for
 * local credentials; this file is optional.
 */

return [
    'host'      => '127.0.0.1',
    'port'      => 3306,
    'database'  => 's3vgroup_local',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
