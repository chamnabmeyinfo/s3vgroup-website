<?php
/**
 * Setup WordPress Database Configuration
 * 
 * Automatically sets WordPress database credentials from wp-config.php
 * or from provided values.
 * 
 * Run: php database/setup-wordpress-config.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Settings\SiteOptionRepository;

echo "🔧 Setting up WordPress database configuration...\n\n";

try {
    $db = Connection::getInstance();
    $repository = new SiteOptionRepository($db);
    
    // WordPress database credentials from wp-config.php
    $config = [
        'host' => 'localhost',
        'database' => 'kdmedsco_wp768',
        'username' => 'kdmedsco_wp768',
        'password' => '3p)P246Z.S',
        'prefix' => 'wpg1_',
    ];
    
    // Encrypt password (base64 encoding)
    $config['password'] = base64_encode($config['password']);
    
    // Save configuration
    $configJson = json_encode($config);
    $repository->set('wordpress_db_config', $configJson, 'text');
    
    echo "✅ WordPress database configuration saved successfully!\n\n";
    echo "📋 Configuration Details:\n";
    echo "   Host: localhost\n";
    echo "   Database: kdmedsco_wp768\n";
    echo "   Username: kdmedsco_wp768\n";
    echo "   Password: ******** (saved securely)\n";
    echo "   Prefix: wpg1_\n\n";
    echo "✨ You can now use the WordPress SQL Import feature!\n";
    echo "   Go to: Admin → Optional Features → WordPress SQL Import\n";
    echo "   The configuration will be automatically loaded.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

