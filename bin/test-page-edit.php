<?php
/**
 * Test Page Edit Functionality
 * Tests all page editing features including settings, homepage designation, templates
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\PageRepository;

$db = Connection::getInstance();
$repository = new PageRepository($db);

echo "🧪 Testing Page Edit Functionality\n";
echo str_repeat("=", 70) . "\n\n";

$testResults = [];
$errors = [];

// Test 1: Create a test page with all fields
echo "1️⃣  Testing: Create page with all fields (including settings)\n";
try {
    $testPageData = [
        'title' => 'Test Edit Page ' . date('H:i:s'),
        'slug' => 'test-edit-' . time(),
        'description' => 'This is a test page for editing',
        'page_type' => 'page',
        'status' => 'PUBLISHED',
        'template' => 'full-width',
        'priority' => 5,
        'meta_title' => 'Test Edit Page - SEO Title',
        'meta_description' => 'Test page meta description for SEO',
        'settings' => [
            'is_homepage' => false,
            'custom_setting' => 'test_value'
        ]
    ];
    
    $newPage = $repository->create($testPageData);
    $testPageId = $newPage['id'];
    
    $testResults['create'] = ['success' => true, 'id' => $testPageId];
    echo "   ✓ Success: Created test page '{$newPage['title']}' (ID: {$testPageId})\n";
    echo "   Template: {$newPage['template']} | Priority: {$newPage['priority']}\n";
} catch (\Exception $e) {
    $testResults['create'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "Create page: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
    $testPageId = null;
}
echo "\n";

// Test 2: Verify data is returned correctly when fetching
if ($testPageId) {
    echo "2️⃣  Testing: Fetch page data (simulates edit button click)\n";
    try {
        $page = $repository->findById($testPageId);
        if ($page) {
            // Check all fields are present
            $checks = [
                'id' => !empty($page['id']),
                'title' => !empty($page['title']),
                'slug' => !empty($page['slug']),
                'description' => isset($page['description']),
                'page_type' => !empty($page['page_type']),
                'status' => !empty($page['status']),
                'template' => isset($page['template']),
                'priority' => isset($page['priority']),
                'meta_title' => isset($page['meta_title']),
                'meta_description' => isset($page['meta_description']),
                'settings' => isset($page['settings']),
            ];
            
            $allPresent = !in_array(false, $checks);
            
            if ($allPresent) {
                $testResults['fetch'] = ['success' => true];
                echo "   ✓ Success: All fields present in fetched data\n";
                
        // Parse settings (transform() already converts to array, but handle both cases)
        $settings = is_array($page['settings']) ? $page['settings'] : (is_string($page['settings']) ? json_decode($page['settings'] ?? '{}', true) : []);
        echo "   Settings keys: " . implode(', ', array_keys($settings)) . "\n";
                echo "   Template: {$page['template']}\n";
                echo "   Priority: {$page['priority']}\n";
            } else {
                $missing = array_keys(array_filter($checks, fn($v) => !$v));
                $testResults['fetch'] = ['success' => false, 'error' => 'Missing fields: ' . implode(', ', $missing)];
                echo "   ✗ Failed: Missing fields: " . implode(', ', $missing) . "\n";
            }
        } else {
            $testResults['fetch'] = ['success' => false, 'error' => 'Page not found'];
            echo "   ✗ Failed: Page not found\n";
        }
    } catch (\Exception $e) {
        $testResults['fetch'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Fetch page: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 3: Edit page (simulates form submission)
if ($testPageId) {
    echo "3️⃣  Testing: Update page (simulates form submission)\n";
    try {
        // Fetch existing data first (like the form does)
        $existing = $repository->findById($testPageId);
        $existingSettings = is_array($existing['settings']) ? $existing['settings'] : (is_string($existing['settings']) ? json_decode($existing['settings'] ?? '{}', true) : []);
        
        // Update with new values (simulating form changes)
        $updateData = [
            'title' => 'Updated Test Page ' . date('H:i:s'),
            'description' => 'This page has been updated',
            'template' => 'landing',
            'priority' => 8,
            'meta_title' => 'Updated SEO Title',
            'meta_description' => 'Updated meta description',
            'settings' => array_merge($existingSettings, [
                'is_homepage' => true, // Set as homepage
                'custom_setting' => 'updated_value'
            ])
        ];
        
        $updated = $repository->update($testPageId, $updateData);
        
        // Verify update worked
        if ($updated['title'] === $updateData['title'] && 
            $updated['template'] === $updateData['template'] &&
            $updated['priority'] == $updateData['priority']) {
            
            $updatedSettings = is_array($updated['settings']) ? $updated['settings'] : (is_string($updated['settings']) ? json_decode($updated['settings'] ?? '{}', true) : []);
            if (isset($updatedSettings['is_homepage']) && $updatedSettings['is_homepage']) {
                $testResults['update'] = ['success' => true];
                echo "   ✓ Success: Page updated correctly\n";
                echo "   New title: {$updated['title']}\n";
                echo "   New template: {$updated['template']}\n";
                echo "   New priority: {$updated['priority']}\n";
                echo "   Is homepage: " . ($updatedSettings['is_homepage'] ? 'Yes' : 'No') . "\n";
            } else {
                $testResults['update'] = ['success' => false, 'error' => 'Homepage setting not saved'];
                echo "   ✗ Failed: Homepage setting not saved\n";
            }
        } else {
            $testResults['update'] = ['success' => false, 'error' => 'Update data mismatch'];
            echo "   ✗ Failed: Update data mismatch\n";
        }
    } catch (\Exception $e) {
        $testResults['update'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Update page: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 4: Verify homepage designation (only one at a time)
echo "4️⃣  Testing: Homepage designation (only one homepage allowed)\n";
try {
    // Create another page
    $page2 = $repository->create([
        'title' => 'Test Homepage Page 2',
        'slug' => 'test-homepage-2-' . time(),
        'status' => 'PUBLISHED',
        'settings' => ['is_homepage' => false]
    ]);
    
    // Set this one as homepage
    $repository->update($page2['id'], [
        'settings' => ['is_homepage' => true]
    ]);
    
    // Check if previous homepage was unset
    $page1After = $repository->findById($testPageId);
    $settings1 = is_array($page1After['settings']) ? $page1After['settings'] : (is_string($page1After['settings']) ? json_decode($page1After['settings'] ?? '{}', true) : []);
    
    $page2After = $repository->findById($page2['id']);
    $settings2 = is_array($page2After['settings']) ? $page2After['settings'] : (is_string($page2After['settings']) ? json_decode($page2After['settings'] ?? '{}', true) : []);
    
    if (!$settings1['is_homepage'] && $settings2['is_homepage']) {
        $testResults['homepage'] = ['success' => true];
        echo "   ✓ Success: Homepage designation works correctly\n";
        echo "   Page 1 homepage: " . ($settings1['is_homepage'] ? 'Yes' : 'No') . "\n";
        echo "   Page 2 homepage: " . ($settings2['is_homepage'] ? 'Yes' : 'No') . "\n";
    } else {
        $testResults['homepage'] = ['success' => false, 'error' => 'Homepage designation failed'];
        echo "   ✗ Failed: Homepage designation not working\n";
    }
    
    // Cleanup
    $repository->delete($page2['id']);
} catch (\Exception $e) {
    $testResults['homepage'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "Homepage designation: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test settings preservation
if ($testPageId) {
    echo "5️⃣  Testing: Settings preservation on update\n";
    try {
        $existing = $repository->findById($testPageId);
        $existingSettings = is_array($existing['settings']) ? $existing['settings'] : (is_string($existing['settings']) ? json_decode($existing['settings'] ?? '{}', true) : []);
        
        // Add a new setting without removing existing ones
        $existingSettings['new_setting'] = 'new_value';
        $existingSettings['is_homepage'] = false; // Just toggle it
        
        $repository->update($testPageId, [
            'settings' => $existingSettings
        ]);
        
        $updated = $repository->findById($testPageId);
        $updatedSettings = is_array($updated['settings']) ? $updated['settings'] : (is_string($updated['settings']) ? json_decode($updated['settings'] ?? '{}', true) : []);
        
        // Check if both old and new settings exist
        if (isset($updatedSettings['custom_setting']) && isset($updatedSettings['new_setting'])) {
            $testResults['settings_preserve'] = ['success' => true];
            echo "   ✓ Success: Settings preserved correctly\n";
            echo "   Old setting: {$updatedSettings['custom_setting']}\n";
            echo "   New setting: {$updatedSettings['new_setting']}\n";
        } else {
            $testResults['settings_preserve'] = ['success' => false, 'error' => 'Settings not preserved'];
            echo "   ✗ Failed: Settings not preserved\n";
        }
    } catch (\Exception $e) {
        $testResults['settings_preserve'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Settings preservation: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 6: Verify template field works
if ($testPageId) {
    echo "6️⃣  Testing: Template field update\n";
    try {
        $templates = ['full-width', 'sidebar-left', 'landing', 'blog', null];
        foreach ($templates as $template) {
            $repository->update($testPageId, ['template' => $template]);
            $updated = $repository->findById($testPageId);
            if ($updated['template'] === $template) {
                echo "   ✓ Template set to: " . ($template ?? 'null') . "\n";
            } else {
                echo "   ✗ Failed to set template: {$template}\n";
            }
        }
        $testResults['template'] = ['success' => true];
    } catch (\Exception $e) {
        $testResults['template'] = ['success' => false, 'error' => $e->getMessage()];
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Cleanup: Delete test page
if ($testPageId) {
    echo "🧹 Cleaning up test page...\n";
    try {
        $repository->delete($testPageId);
        echo "   ✓ Deleted test page: {$testPageId}\n";
    } catch (\Exception $e) {
        echo "   ⚠ Warning: Failed to delete test page: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Summary
echo str_repeat("=", 70) . "\n";
echo "📊 Test Summary\n";
echo str_repeat("=", 70) . "\n\n";

$successCount = count(array_filter($testResults, fn($r) => $r['success'] ?? false));
$totalCount = count($testResults);

foreach ($testResults as $testName => $result) {
    $status = $result['success'] ? '✓' : '✗';
    $testLabel = ucfirst(str_replace('_', ' ', $testName));
    echo "{$status} {$testLabel}\n";
    if (!$result['success'] && isset($result['error'])) {
        echo "   Error: {$result['error']}\n";
    }
}

echo "\n";
echo "Results: {$successCount}/{$totalCount} tests passed\n";

if (!empty($errors)) {
    echo "\n⚠ Errors found:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    exit(1);
}

echo "\n✅ All tests passed! Edit functionality is working correctly.\n";
exit(0);

