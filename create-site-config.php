<?php
/**
 * Create Missing site.php Configuration File
 * Run this ONCE to create the missing config/site.php file
 * DELETE THIS FILE AFTER RUNNING!
 */

$configDir = __DIR__ . '/config';
$configFile = $configDir . '/site.php';

if (file_exists($configFile)) {
    die("
    <h1>Config File Already Exists</h1>
    <p>config/site.php already exists. If you want to recreate it, delete it first.</p>
    <p><a href='/'>Go to homepage</a></p>
    ");
}

// Create config directory if it doesn't exist
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// Auto-detect URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$autoUrl = $protocol . '://' . $host;

$configContent = "<?php
/**
 * Site Configuration
 * AUTO-GENERATED - Edit this file with your site information
 */

// Auto-detect URL: local or live
\$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
\$host = \$_SERVER['HTTP_HOST'] ?? 'localhost';
\$autoUrl = \$protocol . '://' . \$host;

\$siteConfig = [
    'name' => 'S3V Group',
    'description' => 'Professional warehouse equipment solutions for your business needs.',
    'url' => '$autoUrl',
    'contact' => [
        'phone' => '+855 23 123 456',
        'email' => 'info@s3vgroup.com',
        'address' => 'Phnom Penh, Cambodia',
        'hours' => 'Mon-Fri: 8AM-6PM, Sat: 9AM-5PM',
    ],
    'social' => [
        'facebook' => '',
        'linkedin' => '',
    ],
];

// Admin credentials (CHANGE THESE IN PRODUCTION!)
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@s3vgroup.com');
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'admin123'); // CHANGE THIS!
}
";

if (file_put_contents($configFile, $configContent)) {
    chmod($configFile, 0644);
    echo "<h1 style='color:green'>✅ Site Config Created!</h1>";
    echo "<p>The file <code>config/site.php</code> has been created.</p>";
    echo "<p><strong>IMPORTANT: Delete this file (create-site-config.php) now for security!</strong></p>";
    echo "<p><a href='/'>Go to homepage</a></p>";
} else {
    echo "<h1 style='color:red'>❌ Failed to Create Config</h1>";
    echo "<p>Could not create config/site.php. Check directory permissions.</p>";
    echo "<p>Try running in terminal:</p>";
    echo "<pre>mkdir -p config && chmod 755 config</pre>";
}
?>

