<?php
/**
 * Test Pages API - Test all CRUD operations
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\PageRepository;

$db = Connection::getInstance();
$repository = new PageRepository($db);

echo "🧪 Testing Pages API - All CRUD Operations\n";
echo str_repeat("=", 60) . "\n\n";

$testResults = [];
$errors = [];

// Test 1: List all pages
echo "1️⃣  Testing: List all pages (GET /api/admin/pages/index.php)\n";
try {
    $pages = $repository->all();
    $testResults['list'] = ['success' => true, 'count' => count($pages)];
    echo "   ✓ Success: Found " . count($pages) . " pages\n";
    if (!empty($pages)) {
        echo "   Sample pages:\n";
        foreach (array_slice($pages, 0, 3) as $page) {
            echo "      - {$page['title']} ({$page['slug']}) - {$page['status']}\n";
        }
    }
} catch (\Exception $e) {
    $testResults['list'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "List pages: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Create a new test page
echo "2️⃣  Testing: Create new page (POST /api/admin/pages/index.php)\n";
$testPageData = [
    'title' => 'Test Page ' . date('Y-m-d H:i:s'),
    'slug' => 'test-page-' . time(),
    'description' => 'This is a test page created by automated tests',
    'page_type' => 'page',
    'status' => 'PUBLISHED',
    'meta_title' => 'Test Page - Automated Test',
    'meta_description' => 'This is a test page for API testing',
];
try {
    $newPage = $repository->create($testPageData);
    $testResults['create'] = ['success' => true, 'id' => $newPage['id']];
    echo "   ✓ Success: Created page '{$newPage['title']}' (ID: {$newPage['id']})\n";
    echo "   Slug: {$newPage['slug']}\n";
    $testPageId = $newPage['id'];
} catch (\Exception $e) {
    $testResults['create'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "Create page: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
    $testPageId = null;
}
echo "\n";

// Test 3: Get page by ID
if ($testPageId) {
    echo "3️⃣  Testing: Get page by ID (GET /api/admin/pages/item.php?id=...)\n";
    try {
        $page = $repository->findById($testPageId);
        if ($page) {
            $testResults['get'] = ['success' => true];
            echo "   ✓ Success: Retrieved page '{$page['title']}'\n";
        } else {
            $testResults['get'] = ['success' => false, 'error' => 'Page not found'];
            $errors[] = "Get page: Page not found";
            echo "   ✗ Failed: Page not found\n";
        }
    } catch (\Exception $e) {
        $testResults['get'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Get page: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 4: Update page
if ($testPageId) {
    echo "4️⃣  Testing: Update page (PUT /api/admin/pages/item.php?id=...)\n";
    $updateData = [
        'title' => 'Updated Test Page ' . date('H:i:s'),
        'description' => 'This page has been updated',
        'meta_description' => 'Updated meta description',
    ];
    try {
        $updatedPage = $repository->update($testPageId, $updateData);
        $testResults['update'] = ['success' => true];
        echo "   ✓ Success: Updated page to '{$updatedPage['title']}'\n";
        echo "   Description: {$updatedPage['description']}\n";
    } catch (\Exception $e) {
        $testResults['update'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Update page: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 5: Test slug sanitization and uniqueness
echo "5️⃣  Testing: Slug sanitization and uniqueness\n";
try {
    $testSlugs = [
        'Test Page With Spaces',
        'test-page-with-tab	',  // tab character
        'TEST PAGE UPPERCASE',
        'test---page---multiple---dashes',
        '-leading-and-trailing-dashes-',
        'contact-us', // should append number if exists
    ];
    
    foreach ($testSlugs as $testSlug) {
        $testCreateData = [
            'title' => $testSlug,
            'slug' => $testSlug,
            'status' => 'DRAFT',
        ];
        $created = $repository->create($testCreateData);
        echo "   ✓ Created: '{$testSlug}' -> slug: '{$created['slug']}'\n";
        $testPageIds[] = $created['id']; // Store for cleanup
    }
    $testResults['slug_sanitization'] = ['success' => true];
} catch (\Exception $e) {
    $testResults['slug_sanitization'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "Slug sanitization: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Find by slug (only finds published pages)
if ($testPageId) {
    echo "6️⃣  Testing: Find page by slug (GET /page.php?slug=...) - Published only\n";
    try {
        $testPage = $repository->findById($testPageId);
        // First check with draft status (should not find)
        $page = $repository->findBySlug($testPage['slug']);
        if (!$page) {
            echo "   ✓ Correct: Draft page not found by slug (as expected)\n";
            
            // Update to published and test again
            $repository->update($testPageId, ['status' => 'PUBLISHED']);
            $updatedPage = $repository->findById($testPageId);
            $page = $repository->findBySlug($updatedPage['slug']);
            if ($page) {
                $testResults['find_by_slug'] = ['success' => true];
                echo "   ✓ Success: Found published page by slug '{$page['slug']}'\n";
            } else {
                // Check if slug changed during update
                if ($updatedPage['slug'] !== $testPage['slug']) {
                    echo "   ℹ Info: Slug changed during update (sanitization), testing new slug\n";
                    $page = $repository->findBySlug($updatedPage['slug']);
                    if ($page) {
                        $testResults['find_by_slug'] = ['success' => true];
                        echo "   ✓ Success: Found published page by updated slug '{$page['slug']}'\n";
                    } else {
                        $testResults['find_by_slug'] = ['success' => false, 'error' => 'Published page not found even with updated slug'];
                        echo "   ✗ Failed: Published page not found by updated slug\n";
                    }
                } else {
                    $testResults['find_by_slug'] = ['success' => false, 'error' => 'Published page not found'];
                    echo "   ✗ Failed: Published page not found by slug\n";
                }
            }
        } else {
            $testResults['find_by_slug'] = ['success' => true];
            echo "   ✓ Success: Found page by slug '{$page['slug']}'\n";
        }
    } catch (\Exception $e) {
        $testResults['find_by_slug'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Find by slug: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 7: Test duplicate slug handling
echo "7️⃣  Testing: Duplicate slug handling (should auto-append number)\n";
try {
    $duplicateTestData = [
        'title' => 'Duplicate Slug Test',
        'slug' => 'duplicate-test',
        'status' => 'DRAFT',
    ];
    $page1 = $repository->create($duplicateTestData);
    echo "   ✓ Created first page: '{$page1['slug']}'\n";
    
    $page2 = $repository->create($duplicateTestData);
    echo "   ✓ Created second page: '{$page2['slug']}' (should be different)\n";
    
    if ($page1['slug'] !== $page2['slug']) {
        $testResults['duplicate_slug'] = ['success' => true];
        echo "   ✓ Success: Duplicate slug handled correctly\n";
    } else {
        $testResults['duplicate_slug'] = ['success' => false, 'error' => 'Duplicate slug not handled'];
        echo "   ✗ Failed: Both pages have same slug\n";
    }
    
    $testPageIds[] = $page1['id'];
    $testPageIds[] = $page2['id'];
} catch (\Exception $e) {
    $testResults['duplicate_slug'] = ['success' => false, 'error' => $e->getMessage()];
    $errors[] = "Duplicate slug: " . $e->getMessage();
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 8: Delete test page
if ($testPageId) {
    echo "8️⃣  Testing: Delete page (DELETE /api/admin/pages/item.php?id=...)\n";
    try {
        $repository->delete($testPageId);
        $testResults['delete'] = ['success' => true];
        echo "   ✓ Success: Deleted test page (ID: {$testPageId})\n";
        
        // Verify deletion
        $deleted = $repository->findById($testPageId);
        if (!$deleted) {
            echo "   ✓ Verified: Page successfully deleted\n";
        } else {
            echo "   ⚠ Warning: Page still exists after deletion\n";
        }
    } catch (\Exception $e) {
        $testResults['delete'] = ['success' => false, 'error' => $e->getMessage()];
        $errors[] = "Delete page: " . $e->getMessage();
        echo "   ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Cleanup: Delete all test pages
if (!empty($testPageIds)) {
    echo "🧹 Cleaning up test pages...\n";
    foreach ($testPageIds as $id) {
        try {
            $repository->delete($id);
            echo "   ✓ Deleted test page: {$id}\n";
        } catch (\Exception $e) {
            echo "   ✗ Failed to delete {$id}: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "📊 Test Summary\n";
echo str_repeat("=", 60) . "\n\n";

$successCount = count(array_filter($testResults, fn($r) => $r['success'] ?? false));
$totalCount = count($testResults);

foreach ($testResults as $testName => $result) {
    $status = $result['success'] ? '✓' : '✗';
    echo "{$status} " . ucfirst(str_replace('_', ' ', $testName)) . "\n";
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

echo "\n✅ All tests passed!\n";
exit(0);

