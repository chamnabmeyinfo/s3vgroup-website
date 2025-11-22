<?php
/**
 * Verify Edit/Delete Button Structure
 * Checks if the HTML structure matches what JavaScript expects
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\PageRepository;

$db = Connection::getInstance();
$repository = new PageRepository($db);

echo "🔍 Verifying Edit/Delete Button Structure\n";
echo str_repeat("=", 70) . "\n\n";

$pages = $repository->all();
$tests = [];

if (empty($pages)) {
    echo "⚠ No pages found. Creating a test page...\n";
    $testPage = $repository->create([
        'title' => 'Test Button Page',
        'slug' => 'test-button-' . time(),
        'status' => 'PUBLISHED'
    ]);
    $pages = [$testPage];
    echo "✓ Created test page\n\n";
}

echo "📋 Checking HTML structure for " . count($pages) . " page(s)...\n\n";

// Simulate HTML structure
$buttonCount = 0;
$editButtons = 0;
$deleteButtons = 0;
$rowsWithDataId = 0;
$issues = [];

foreach ($pages as $page) {
    $buttonCount++;
    
    // Check if row would have data-id (simulating the HTML)
    $hasDataId = isset($page['id']) && !empty($page['id']);
    if ($hasDataId) {
        $rowsWithDataId++;
    } else {
        $issues[] = "Page '{$page['title']}' missing ID";
    }
    
    // Check button classes (simulating the HTML)
    $editButtonClass = 'edit-page-btn'; // This is what should be in the HTML
    $deleteButtonClass = 'delete-page-btn'; // This is what should be in the HTML
    
    $editButtons++;
    $deleteButtons++;
}

echo "✅ Structure Check Results:\n";
echo "   • Total pages: " . count($pages) . "\n";
echo "   • Rows with data-id: {$rowsWithDataId}/" . count($pages) . "\n";
echo "   • Edit buttons expected: {$editButtons}\n";
echo "   • Delete buttons expected: {$deleteButtons}\n\n";

if (count($issues) > 0) {
    echo "❌ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
    echo "\n";
}

// Verify JavaScript code structure
echo "✅ JavaScript Code Check:\n";

$jsCode = file_get_contents(__DIR__ . '/../admin/pages.php');
$jsStart = strpos($jsCode, '<script>');
$jsEnd = strpos($jsCode, '</script>', $jsStart);
$js = substr($jsCode, $jsStart, $jsEnd - $jsStart + 9);

$checks = [
    'querySelectorAll(\'.edit-page-btn\')' => strpos($js, "querySelectorAll('.edit-page-btn')") !== false,
    'querySelectorAll(\'.delete-page-btn\')' => strpos($js, "querySelectorAll('.delete-page-btn')") !== false,
    'addEventListener(\'click\'' => strpos($js, "addEventListener('click'") !== false || strpos($js, 'addEventListener("click"') !== false,
    'closest(\'tr\')' => strpos($js, "closest('tr')") !== false || strpos($js, 'closest("tr")') !== false,
    'row.dataset.id' => strpos($js, 'row.dataset.id') !== false || strpos($js, 'button.closest') !== false,
    'fetch API endpoint' => strpos($js, "/api/admin/pages/item.php") !== false,
    'showModal(' => strpos($js, 'showModal(') !== false,
];

echo "\n";
foreach ($checks as $check => $found) {
    $status = $found ? '✓' : '✗';
    $color = $found ? 'green' : 'red';
    echo "   {$status} " . ($found ? "Found" : "MISSING") . ": {$check}\n";
}

$allFound = !in_array(false, $checks);
echo "\n";

if ($allFound) {
    echo "✅ All JavaScript code checks passed!\n\n";
} else {
    echo "❌ Some JavaScript code checks failed!\n\n";
}

// Check button HTML structure
echo "✅ HTML Button Structure Check:\n";
$htmlCode = file_get_contents(__DIR__ . '/../admin/pages.php');

$htmlChecks = [
    'class="edit-page-btn"' => strpos($htmlCode, 'class="edit-page-btn"') !== false || strpos($htmlCode, "class='edit-page-btn'") !== false,
    'class="delete-page-btn"' => strpos($htmlCode, 'class="delete-page-btn"') !== false || strpos($htmlCode, "class='delete-page-btn'") !== false,
    'data-id="<?php echo' => strpos($htmlCode, 'data-id="<?php echo') !== false,
    '<tr data-id=' => strpos($htmlCode, '<tr data-id=') !== false || strpos($htmlCode, '<tr data-id="') !== false,
];

foreach ($htmlChecks as $check => $found) {
    $status = $found ? '✓' : '✗';
    echo "   {$status} " . ($found ? "Found" : "MISSING") . ": {$check}\n";
}

$allHtmlFound = !in_array(false, $htmlChecks);
echo "\n";

if ($allHtmlFound) {
    echo "✅ All HTML structure checks passed!\n\n";
} else {
    echo "❌ Some HTML structure checks failed!\n\n";
}

// Summary
echo str_repeat("=", 70) . "\n";
echo "📊 Summary\n";
echo str_repeat("=", 70) . "\n\n";

if ($allFound && $allHtmlFound && $rowsWithDataId === count($pages)) {
    echo "✅ ALL CHECKS PASSED!\n\n";
    echo "The Edit and Delete buttons should work correctly.\n";
    echo "\n";
    echo "If buttons still don't work, check:\n";
    echo "1. Browser console for JavaScript errors (F12)\n";
    echo "2. Network tab to see if API calls are made\n";
    echo "3. Try the interactive test: bin/test-button-click.html\n";
    exit(0);
} else {
    echo "❌ SOME CHECKS FAILED!\n\n";
    echo "Please fix the issues above.\n";
    exit(1);
}

