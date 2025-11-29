<?php
/**
 * Deep Production Diagnosis
 * This will check what code is actually on production vs what should be there
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Production Diagnosis</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .error { color: #d32f2f; background: #ffebee; padding: 10px; border-left: 4px solid #d32f2f; margin: 5px 0; }
    .success { color: #388e3c; background: #e8f5e9; padding: 10px; border-left: 4px solid #388e3c; margin: 5px 0; }
    .warning { color: #f57c00; background: #fff3e0; padding: 10px; border-left: 4px solid #f57c00; margin: 5px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    h1 { color: #1976d2; }
    h2 { color: #424242; border-bottom: 2px solid #e0e0e0; padding-bottom: 5px; }
</style></head><body>";

echo "<h1>🔍 Deep Production Diagnosis</h1>";

// 1. PHP Version
echo "<div class='section'>";
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
echo "<p><strong>Current PHP Version:</strong> $phpVersion</p>";

if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "<div class='success'>✓ PHP 8.0+ detected - supports readonly properties</div>";
} else {
    echo "<div class='warning'>⚠ PHP 7.4 detected - does NOT support readonly properties</div>";
    echo "<p>Code must be PHP 7.4 compatible (no readonly, no match expressions, no str_contains)</p>";
}
echo "</div>";

// 2. Check critical files for PHP 7.4 incompatibilities
echo "<div class='section'>";
echo "<h2>2. PHP 7.4 Compatibility Check</h2>";

$criticalFiles = [
    'app/Domain/Settings/SiteOptionRepository.php',
    'app/Domain/Settings/SiteOptionService.php',
    'app/Domain/Theme/ThemeRepository.php',
    'app/Domain/Catalog/ProductRepository.php',
    'app/Domain/Catalog/CategoryService.php',
    'app/Domain/Catalog/CatalogService.php',
    'app/Domain/Quotes/QuoteRequestRepository.php',
    'app/Domain/Quotes/QuoteService.php',
    'app/Domain/Quotes/QuoteAdminService.php',
];

$issues = [];
$fixed = [];

foreach ($criticalFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        $issues[] = "✗ $file - FILE NOT FOUND";
        continue;
    }
    
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $fileIssues = [];
    
    // Check for readonly
    if (preg_match('/private\s+readonly|public\s+readonly|readonly\s+private|readonly\s+public/', $content)) {
        foreach ($lines as $num => $line) {
            if (preg_match('/readonly/', $line)) {
                $fileIssues[] = [
                    'type' => 'readonly',
                    'line' => $num + 1,
                    'code' => trim($line)
                ];
            }
        }
    }
    
    // Check for match expressions
    if (preg_match('/\bmatch\s*\(/', $content)) {
        foreach ($lines as $num => $line) {
            if (preg_match('/\bmatch\s*\(/', $line)) {
                $fileIssues[] = [
                    'type' => 'match',
                    'line' => $num + 1,
                    'code' => trim($line)
                ];
            }
        }
    }
    
    // Check for str_contains (PHP 8.0+)
    if (preg_match('/\bstr_contains\s*\(/', $content)) {
        foreach ($lines as $num => $line) {
            if (preg_match('/\bstr_contains\s*\(/', $line)) {
                $fileIssues[] = [
                    'type' => 'str_contains',
                    'line' => $num + 1,
                    'code' => trim($line)
                ];
            }
        }
    }
    
    if (empty($fileIssues)) {
        $fixed[] = $file;
    } else {
        $issues[$file] = $fileIssues;
    }
}

if (empty($issues)) {
    echo "<div class='success'>✓ All critical files are PHP 7.4 compatible!</div>";
} else {
    echo "<div class='error'><strong>✗ Found PHP 7.4 incompatibilities:</strong></div>";
    foreach ($issues as $file => $fileIssues) {
        echo "<div class='error'>";
        echo "<strong>$file</strong><br>";
        foreach ($fileIssues as $issue) {
            echo "Line {$issue['line']} ({$issue['type']}): <code>" . htmlspecialchars($issue['code']) . "</code><br>";
        }
        echo "</div>";
    }
}

if (!empty($fixed)) {
    echo "<div class='success'><strong>✓ Fixed files:</strong><br>";
    foreach ($fixed as $file) {
        echo "• $file<br>";
    }
    echo "</div>";
}
echo "</div>";

// 3. Git Status
echo "<div class='section'>";
echo "<h2>3. Git Status</h2>";

if (function_exists('exec')) {
    $gitStatus = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git status --short 2>&1', $gitStatus);
    
    if (empty($gitStatus) || (count($gitStatus) === 1 && strpos($gitStatus[0], 'fatal') !== false)) {
        echo "<div class='error'>✗ Git repository not found or not initialized</div>";
    } else {
        if (empty($gitStatus) || (count($gitStatus) === 1 && trim($gitStatus[0]) === '')) {
            echo "<div class='success'>✓ Working directory clean (no uncommitted changes)</div>";
        } else {
            echo "<div class='warning'>⚠ Uncommitted changes detected:</div>";
            echo "<pre>" . implode("\n", $gitStatus) . "</pre>";
        }
        
        $gitLog = [];
        exec('cd ' . escapeshellarg(__DIR__) . ' && git log --oneline -5 2>&1', $gitLog);
        echo "<h3>Recent Commits:</h3>";
        echo "<pre>" . implode("\n", $gitLog) . "</pre>";
        
        $gitBranch = [];
        exec('cd ' . escapeshellarg(__DIR__) . ' && git branch --show-current 2>&1', $gitBranch);
        $currentBranch = !empty($gitBranch) ? trim($gitBranch[0]) : 'unknown';
        echo "<p><strong>Current Branch:</strong> $currentBranch</p>";
        
        // Check if behind remote
        $gitFetch = [];
        exec('cd ' . escapeshellarg(__DIR__) . ' && git fetch origin 2>&1', $gitFetch);
        $gitBehind = [];
        exec('cd ' . escapeshellarg(__DIR__) . ' && git rev-list HEAD..origin/main --count 2>&1', $gitBehind);
        $behindCount = !empty($gitBehind) ? (int)trim($gitBehind[0]) : 0;
        
        if ($behindCount > 0) {
            echo "<div class='error'>";
            echo "✗ <strong>PRODUCTION IS BEHIND GITHUB!</strong><br>";
            echo "Production is <strong>$behindCount commits behind</strong> origin/main<br>";
            echo "The fixes are in GitHub but not on this server!<br>";
            echo "<strong>ACTION REQUIRED:</strong> Run <code>git pull origin main</code>";
            echo "</div>";
        } else {
            echo "<div class='success'>✓ Production is up to date with GitHub</div>";
        }
    }
} else {
    echo "<div class='warning'>⚠ Cannot check git (exec disabled)</div>";
}
echo "</div>";

// 4. Sample file content
echo "<div class='section'>";
echo "<h2>4. Sample File Content (SiteOptionRepository.php)</h2>";
$sampleFile = __DIR__ . '/app/Domain/Settings/SiteOptionRepository.php';
if (file_exists($sampleFile)) {
    $content = file_get_contents($sampleFile);
    $lines = explode("\n", $content);
    echo "<p>First 25 lines:</p>";
    echo "<pre>";
    foreach (array_slice($lines, 0, 25) as $num => $line) {
        $lineNum = $num + 1;
        $highlight = '';
        if (preg_match('/readonly/', $line)) {
            $highlight = 'background:#ffebee;color:#d32f2f;';
        } elseif (preg_match('/private\s+\$pdo/', $line)) {
            $highlight = 'background:#e8f5e9;color:#388e3c;';
        }
        echo "<span style='$highlight'>" . str_pad($lineNum, 3, ' ', STR_PAD_LEFT) . ": " . htmlspecialchars($line) . "</span>\n";
    }
    echo "</pre>";
    
    // Check constructor
    if (preg_match('/public function __construct\([^)]*readonly[^)]*\)/', $content)) {
        echo "<div class='error'>✗ Constructor still uses readonly (OLD CODE)</div>";
    } elseif (preg_match('/public function __construct\(PDO \$pdo\)/', $content) && preg_match('/\$this->pdo = \$pdo;/', $content)) {
        echo "<div class='success'>✓ Constructor is PHP 7.4 compatible (NEW CODE)</div>";
    }
} else {
    echo "<div class='error'>✗ File not found!</div>";
}
echo "</div>";

// 5. Recommendations
echo "<div class='section'>";
echo "<h2>5. Recommendations</h2>";

if (version_compare($phpVersion, '8.0.0', '<')) {
    if (!empty($issues)) {
        echo "<div class='error'>";
        echo "<strong>IMMEDIATE ACTION REQUIRED:</strong><br><br>";
        echo "1. <strong>Pull latest code from GitHub:</strong><br>";
        echo "   <code>cd ~/public_html && git pull origin main</code><br><br>";
        echo "2. <strong>If git pull doesn't work:</strong><br>";
        echo "   <code>cd ~/public_html && git fetch origin && git reset --hard origin/main</code><br><br>";
        echo "3. <strong>Verify the fix:</strong><br>";
        echo "   Check this page again after pulling<br><br>";
        echo "4. <strong>Test the website:</strong><br>";
        echo "   Visit https://s3vgroup.com/";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "✓ Code is PHP 7.4 compatible!<br>";
        echo "If website still doesn't work, check error logs:<br>";
        echo "<code>tail -50 ~/public_html/error_log</code>";
        echo "</div>";
    }
} else {
    echo "<div class='warning'>";
    echo "⚠ PHP 8.0+ detected. Code should work, but if you see errors, check:<br>";
    echo "1. Database connection<br>";
    echo "2. File permissions<br>";
    echo "3. Error logs";
    echo "</div>";
}
echo "</div>";

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE after diagnosis!</strong></p>";
echo "</body></html>";
?>

