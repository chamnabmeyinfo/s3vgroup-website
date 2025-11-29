<?php
/**
 * EMERGENCY FIX: Remove All Widgets to Fix 500 Error
 * Run this file ONCE to disable all problematic widgets
 * DELETE THIS FILE AFTER RUNNING!
 */

$footerFile = __DIR__ . '/ae-includes/footer.php';
$headerFile = __DIR__ . '/ae-includes/header.php';

echo "<h1>Emergency Fix: Remove All Widgets</h1>";

// Fix footer.php
if (file_exists($footerFile)) {
    $footer = file_get_contents($footerFile);
    
    // Disable loading-screen widget
    $footer = preg_replace(
        '/<\?php\s+include\s+__DIR__\s*\.\s*[\'"]\/widgets\/loading-screen\.php[\'"]\s*;\s*\?>/i',
        '<?php // DISABLED: loading-screen widget ?>',
        $footer
    );
    
    // Remove any bottom-nav includes
    $footer = preg_replace(
        '/<\?php[^?]*bottom-nav[^?]*\?>/is',
        '<?php // DISABLED: bottom-nav widget ?>',
        $footer
    );
    
    // Backup
    copy($footerFile, $footerFile . '.backup-' . date('Y-m-d-His'));
    
    // Save
    file_put_contents($footerFile, $footer);
    echo "<p style='color:green'>✓ Fixed footer.php</p>";
} else {
    echo "<p style='color:red'>✗ footer.php not found</p>";
}

// Fix header.php
if (file_exists($headerFile)) {
    $header = file_get_contents($headerFile);
    
    // Disable mobile-app-header widget
    $header = preg_replace(
        '/<\?php\s+include\s+__DIR__\s*\.\s*[\'"]\/widgets\/mobile-app-header\.php[\'"]\s*;\s*\?>/i',
        '<?php // DISABLED: mobile-app-header widget ?>',
        $header
    );
    
    // Disable secondary-menu widget
    $header = preg_replace(
        '/if\s*\(\s*file_exists\s*\([^)]*\/widgets\/secondary-menu\.php[^)]*\)\s*\)\s*\{[^}]+\}/is',
        '// DISABLED: secondary-menu widget',
        $header
    );
    
    // Backup
    copy($headerFile, $headerFile . '.backup-' . date('Y-m-d-His'));
    
    // Save
    file_put_contents($headerFile, $header);
    echo "<p style='color:green'>✓ Fixed header.php</p>";
} else {
    echo "<p style='color:red'>✗ header.php not found</p>";
}

echo "<hr>";
echo "<h2>✅ Fix Complete!</h2>";
echo "<p><a href='/'>Test Homepage</a></p>";
echo "<p><strong>DELETE THIS FILE NOW for security!</strong></p>";
?>

