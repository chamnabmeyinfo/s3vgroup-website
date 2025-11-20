<?php
/**
 * cPanel Deployment Helper
 * This script helps prepare files for cPanel deployment
 * Run this locally before uploading to cPanel
 */

echo "S3V Forklift Website - cPanel Deployment Helper\n";
echo "==============================================\n\n";

// Check if we're in the right directory
if (!file_exists('config/database.php')) {
    die("Error: Please run this script from the s3v-web-php directory\n");
}

echo "✓ Project structure verified\n";

// Create deployment checklist
$checklist = [
    'config/database.php' => 'Database configuration',
    'config/site.php' => 'Site configuration',
    'sql/schema.sql' => 'Database schema',
    '.htaccess' => 'Apache configuration',
    'index.php' => 'Homepage',
    'admin/login.php' => 'Admin login',
];

echo "\nFiles to upload:\n";
foreach ($checklist as $file => $description) {
    if (file_exists($file)) {
        echo "  ✓ $file - $description\n";
    } else {
        echo "  ✗ $file - MISSING!\n";
    }
}

echo "\nNext Steps:\n";
echo "1. Edit config/database.php with your cPanel database credentials\n";
echo "2. Edit config/site.php with your site info and change admin password\n";
echo "3. Upload all files to your cPanel public_html directory\n";
echo "4. Import sql/schema.sql via phpMyAdmin\n";
echo "5. Test your website\n";

echo "\nFor detailed instructions, see DEPLOYMENT.md\n";
?>
