<?php
/**
 * Local Development Database Configuration
 * 
 * This file is for local testing only
 * Copy settings to config/database.php for local development
 */

// XAMPP Default Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 's3vgroup_local');  // Create this database in phpMyAdmin
define('DB_USER', 'root');             // XAMPP default
define('DB_PASS', '');                 // XAMPP default (empty)
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO Database Connection
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.<br>Error: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
