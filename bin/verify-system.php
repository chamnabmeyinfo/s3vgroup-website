<?php
/**
 * System Verification Script
 * Tests Database, Backend, and Frontend functionality
 * Run: php bin/verify-system.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\CategoryRepository;
use App\Domain\Content\TeamMemberRepository;
use App\Domain\Content\TestimonialRepository;
use App\Domain\Content\SliderRepository;
use App\Domain\Content\PageRepository;

$db = getDB();
$errors = [];
$warnings = [];
$success = [];

echo "🔍 S3V Group System Verification\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================
// STEP 1: Database Verification
// ============================================
echo "📊 STEP 1: Database Structure Verification\n";
echo str_repeat("-", 60) . "\n";

$requiredTables = [
    'categories',
    'products',
    'team_members',
    'testimonials',
    'sliders',
    'pages',
    'quote_requests',
    'newsletter_subscribers',
    'site_options',
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $success[] = "✓ Table '$table' exists";
            echo "  ✓ Table '$table' exists\n";
        } else {
            $errors[] = "✗ Table '$table' is missing";
            echo "  ✗ Table '$table' is missing\n";
        }
    } catch (Exception $e) {
        $errors[] = "✗ Error checking table '$table': " . $e->getMessage();
        echo "  ✗ Error checking table '$table': " . $e->getMessage() . "\n";
    }
}

// Check for translation tables (should NOT exist)
$translationTables = ['languages', 'translations', 'content_translations'];
foreach ($translationTables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $warnings[] = "⚠ Translation table '$table' still exists (should be removed)";
            echo "  ⚠ Translation table '$table' still exists\n";
        } else {
            $success[] = "✓ Translation table '$table' correctly removed";
            echo "  ✓ Translation table '$table' correctly removed\n";
        }
    } catch (Exception $e) {
        // Ignore errors for non-existent tables
    }
}

echo "\n";

// ============================================
// STEP 2: Backend Repository Verification
// ============================================
echo "🔧 STEP 2: Backend Repository Verification\n";
echo str_repeat("-", 60) . "\n";

try {
    // Test CategoryRepository
    $categoryRepo = new CategoryRepository($db);
    $categories = $categoryRepo->all();
    $success[] = "✓ CategoryRepository::all() works";
    echo "  ✓ CategoryRepository::all() - Found " . count($categories) . " categories\n";
    
    if (count($categories) > 0) {
        $firstCategory = $categories[0];
        $found = $categoryRepo->findById($firstCategory['id']);
        if ($found) {
            $success[] = "✓ CategoryRepository::findById() works";
            echo "  ✓ CategoryRepository::findById() works\n";
        }
    }
} catch (Exception $e) {
    $errors[] = "✗ CategoryRepository error: " . $e->getMessage();
    echo "  ✗ CategoryRepository error: " . $e->getMessage() . "\n";
}

try {
    // Test ProductRepository
    $productRepo = new ProductRepository($db);
    $products = $productRepo->featured(5);
    $success[] = "✓ ProductRepository::featured() works";
    echo "  ✓ ProductRepository::featured() - Found " . count($products) . " products\n";
    
    if (count($products) > 0) {
        $firstProduct = $products[0];
        $found = $productRepo->findBySlug($firstProduct['slug'], false);
        if ($found) {
            $success[] = "✓ ProductRepository::findBySlug() works";
            echo "  ✓ ProductRepository::findBySlug() works\n";
        }
    }
} catch (Exception $e) {
    $errors[] = "✗ ProductRepository error: " . $e->getMessage();
    echo "  ✗ ProductRepository error: " . $e->getMessage() . "\n";
}

try {
    // Test TeamMemberRepository
    $teamRepo = new TeamMemberRepository($db);
    $members = $teamRepo->active();
    $success[] = "✓ TeamMemberRepository::active() works";
    echo "  ✓ TeamMemberRepository::active() - Found " . count($members) . " members\n";
} catch (Exception $e) {
    $errors[] = "✗ TeamMemberRepository error: " . $e->getMessage();
    echo "  ✗ TeamMemberRepository error: " . $e->getMessage() . "\n";
}

try {
    // Test TestimonialRepository
    $testimonialRepo = new TestimonialRepository($db);
    $testimonials = $testimonialRepo->published();
    $success[] = "✓ TestimonialRepository::published() works";
    echo "  ✓ TestimonialRepository::published() - Found " . count($testimonials) . " testimonials\n";
} catch (Exception $e) {
    $errors[] = "✗ TestimonialRepository error: " . $e->getMessage();
    echo "  ✗ TestimonialRepository error: " . $e->getMessage() . "\n";
}

try {
    // Test SliderRepository
    $sliderRepo = new SliderRepository($db);
    $sliders = $sliderRepo->published();
    $success[] = "✓ SliderRepository::published() works";
    echo "  ✓ SliderRepository::published() - Found " . count($sliders) . " sliders\n";
} catch (Exception $e) {
    $errors[] = "✗ SliderRepository error: " . $e->getMessage();
    echo "  ✗ SliderRepository error: " . $e->getMessage() . "\n";
}

try {
    // Test PageRepository
    $pageRepo = new PageRepository($db);
    $pages = $pageRepo->published();
    $success[] = "✓ PageRepository::published() works";
    echo "  ✓ PageRepository::published() - Found " . count($pages) . " pages\n";
} catch (Exception $e) {
    $errors[] = "✗ PageRepository error: " . $e->getMessage();
    echo "  ✗ PageRepository error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// STEP 3: Check for Translation Code Remnants
// ============================================
echo "🧹 STEP 3: Translation Code Cleanup Verification\n";
echo str_repeat("-", 60) . "\n";

$filesToCheck = [
    'app/Domain/Catalog/ProductRepository.php',
    'app/Domain/Catalog/CategoryRepository.php',
    'app/Domain/Content/TeamMemberRepository.php',
    'app/Domain/Content/PageRepository.php',
    'app/Domain/Content/SliderRepository.php',
    'app/Domain/Content/TestimonialRepository.php',
    'includes/functions.php',
    'includes/header.php',
];

$translationKeywords = [
    'ContentTranslationService',
    'TranslationService',
    'TranslationRepository',
    'localizeCollection',
    'localizeRecord',
    'saveDefault',
    'applyTranslations',
    '__(',
];

foreach ($filesToCheck as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (!file_exists($filePath)) {
        $warnings[] = "⚠ File not found: $file";
        echo "  ⚠ File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $found = false;
    foreach ($translationKeywords as $keyword) {
        if (strpos($content, $keyword) !== false) {
            $found = true;
            $errors[] = "✗ Translation code found in $file: '$keyword'";
            echo "  ✗ Translation code found in $file: '$keyword'\n";
            break;
        }
    }
    
    if (!$found) {
        $success[] = "✓ $file is clean";
        echo "  ✓ $file is clean\n";
    }
}

echo "\n";

// ============================================
// STEP 4: Frontend File Verification
// ============================================
echo "🌐 STEP 4: Frontend File Verification\n";
echo str_repeat("-", 60) . "\n";

$frontendFiles = [
    'index.php',
    'products.php',
    'product.php',
    'team.php',
    'testimonials.php',
    'contact.php',
    'about.php',
];

foreach ($frontendFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        // Check for translation function calls
        if (preg_match('/__\(/', $content)) {
            $warnings[] = "⚠ Translation function __() found in $file";
            echo "  ⚠ Translation function __() found in $file\n";
        } else {
            $success[] = "✓ $file exists and is clean";
            echo "  ✓ $file exists and is clean\n";
        }
    } else {
        $warnings[] = "⚠ Frontend file missing: $file";
        echo "  ⚠ Frontend file missing: $file\n";
    }
}

echo "\n";

// ============================================
// STEP 5: Admin File Verification
// ============================================
echo "⚙️  STEP 5: Admin File Verification\n";
echo str_repeat("-", 60) . "\n";

$adminFiles = [
    'admin/index.php',
    'admin/products.php',
    'admin/categories.php',
    'admin/team.php',
    'admin/testimonials.php',
    'admin/quotes.php',
    'admin/options.php',
];

foreach ($adminFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    if (file_exists($filePath)) {
        $success[] = "✓ $file exists";
        echo "  ✓ $file exists\n";
    } else {
        $warnings[] = "⚠ Admin file missing: $file";
        echo "  ⚠ Admin file missing: $file\n";
    }
}

echo "\n";

// ============================================
// SUMMARY
// ============================================
echo str_repeat("=", 60) . "\n";
echo "📋 VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ Success: " . count($success) . " checks passed\n";
echo "⚠️  Warnings: " . count($warnings) . " issues found\n";
echo "❌ Errors: " . count($errors) . " critical issues found\n\n";

if (count($errors) > 0) {
    echo "❌ CRITICAL ERRORS:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  $warning\n";
    }
    echo "\n";
}

if (count($errors) === 0 && count($warnings) === 0) {
    echo "🎉 All checks passed! System is ready.\n";
    exit(0);
} else if (count($errors) === 0) {
    echo "✅ No critical errors. System is functional.\n";
    exit(0);
} else {
    echo "❌ Critical errors found. Please fix before proceeding.\n";
    exit(1);
}

