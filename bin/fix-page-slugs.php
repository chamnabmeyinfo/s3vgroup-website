<?php
/**
 * Fix page slugs - Remove whitespace and ensure uniqueness
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

$db = Connection::getInstance();

echo "🔍 Checking and fixing page slugs...\n\n";

// Get all pages
$stmt = $db->query('SELECT id, slug, title FROM pages ORDER BY slug');
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pages)) {
    echo "No pages found.\n";
    exit(0);
}

$fixed = 0;
$errors = 0;

foreach ($pages as $page) {
    $oldSlug = $page['slug'];
    $title = $page['title'] ?? '';
    
    // Sanitize slug
    $newSlug = trim($oldSlug);
    $newSlug = strtolower($newSlug);
    $newSlug = preg_replace('/[^a-z0-9]+/i', '-', $newSlug) ?? '';
    $newSlug = trim($newSlug, '-');
    
    // If empty after sanitization, generate from title
    if (empty($newSlug) && !empty($title)) {
        $newSlug = strtolower($title);
        $newSlug = preg_replace('/[^a-z0-9]+/i', '-', $newSlug) ?? '';
        $newSlug = trim($newSlug, '-');
    }
    
    // If still empty, use fallback
    if (empty($newSlug)) {
        $newSlug = 'page-' . bin2hex(random_bytes(4));
    }
    
    // Ensure unique slug
    $uniqueSlug = $newSlug;
    $counter = 1;
    while (true) {
        $checkStmt = $db->prepare('SELECT COUNT(*) FROM pages WHERE slug = :slug AND id != :id');
        $checkStmt->execute([':slug' => $uniqueSlug, ':id' => $page['id']]);
        $count = (int) $checkStmt->fetchColumn();
        
        if ($count === 0) {
            break;
        }
        
        $uniqueSlug = $newSlug . '-' . $counter;
        $counter++;
        
        if ($counter > 100) {
            $uniqueSlug = $newSlug . '-' . bin2hex(random_bytes(4));
            break;
        }
    }
    
    // Update if slug changed
    if ($oldSlug !== $uniqueSlug) {
        try {
            $updateStmt = $db->prepare('UPDATE pages SET slug = :new_slug WHERE id = :id');
            $updateStmt->execute([':new_slug' => $uniqueSlug, ':id' => $page['id']]);
            echo "✓ Fixed: '{$oldSlug}' -> '{$uniqueSlug}' (Page: {$title})\n";
            $fixed++;
        } catch (\PDOException $e) {
            echo "✗ Error updating page {$page['id']}: {$e->getMessage()}\n";
            $errors++;
        }
    }
}

echo "\n✅ Done!\n";
echo "Fixed: {$fixed} pages\n";
if ($errors > 0) {
    echo "Errors: {$errors} pages\n";
}

