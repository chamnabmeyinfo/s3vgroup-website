<?php
/**
 * Extract Colors from Logo
 * 
 * Analyzes logo SVG/image files and extracts dominant colors
 * Then updates site color options to match the logo
 * 
 * Usage:
 *   php bin/extract-logo-colors.php                    # Analyze and show colors
 *   php bin/extract-logo-colors.php --apply            # Apply colors to site options
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Settings\SiteOptionRepository;

// Colors for terminal output
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

function printSuccess(string $message): void {
    echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printInfo(string $message): void {
    echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function printWarning(string $message): void {
    echo Colors::YELLOW . "⚠️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

/**
 * Extract colors from SVG file
 */
function extractColorsFromSVG(string $filePath): array {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = file_get_contents($filePath);
    $colors = [];
    
    // Extract hex colors (#RRGGBB or #RGB)
    preg_match_all('/#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b/', $content, $matches);
    if (!empty($matches[0])) {
        $colors = array_unique($matches[0]);
    }
    
    // Extract rgb/rgba colors
    preg_match_all('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $content, $rgbMatches);
    if (!empty($rgbMatches[0])) {
        foreach ($rgbMatches[0] as $index => $rgb) {
            $r = str_pad(dechex((int)$rgbMatches[1][$index]), 2, '0', STR_PAD_LEFT);
            $g = str_pad(dechex((int)$rgbMatches[2][$index]), 2, '0', STR_PAD_LEFT);
            $b = str_pad(dechex((int)$rgbMatches[3][$index]), 2, '0', STR_PAD_LEFT);
            $colors[] = '#' . $r . $g . $b;
        }
    }
    
    // Extract fill and stroke colors
    preg_match_all('/fill=["\']([^"\']+)["\']/', $content, $fillMatches);
    preg_match_all('/stroke=["\']([^"\']+)["\']/', $content, $strokeMatches);
    
    foreach ($fillMatches[1] ?? [] as $color) {
        if (preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $color)) {
            $colors[] = $color;
        }
    }
    
    foreach ($strokeMatches[1] ?? [] as $color) {
        if (preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $color)) {
            $colors[] = $color;
        }
    }
    
    // Normalize 3-digit hex to 6-digit
    $normalized = [];
    foreach ($colors as $color) {
        if (strlen($color) === 4) {
            $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
        }
        $normalized[] = strtoupper($color);
    }
    
    return array_unique($normalized);
}

/**
 * Analyze colors and determine primary, secondary, accent
 */
function analyzeColors(array $colors): array {
    if (empty($colors)) {
        return [
            'primary' => '#0b3a63',
            'secondary' => '#1a5a8a',
            'accent' => '#fa4f26',
        ];
    }
    
    // Remove white, black, and very light/dark colors
    $filtered = array_filter($colors, function($color) {
        $rgb = hex2rgb($color);
        if (!$rgb) return false;
        
        $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
        
        // Exclude very light (white-ish) and very dark (black-ish) colors
        return $brightness > 30 && $brightness < 240;
    });
    
    if (empty($filtered)) {
        $filtered = $colors;
    }
    
    $filtered = array_values($filtered);
    
    // Sort by frequency (if we had frequency data) or use first few
    $primary = $filtered[0] ?? '#0b3a63';
    $secondary = $filtered[1] ?? ($filtered[0] ?? '#1a5a8a');
    $accent = $filtered[2] ?? ($filtered[1] ?? ($filtered[0] ?? '#fa4f26'));
    
    // Analyze colors to assign roles
    // Primary: Usually the darkest/most prominent color (green #086D3B)
    // Secondary: Medium color or complementary (orange #FAA623)
    // Accent: Bright/vibrant color (red #F4162B)
    
    $colorRoles = [];
    foreach ($filtered as $color) {
        $rgb = hex2rgb($color);
        if ($rgb) {
            $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
            $saturation = calculateSaturation($rgb);
            $colorRoles[] = [
                'color' => $color,
                'brightness' => $brightness,
                'saturation' => $saturation,
                'rgb' => $rgb,
            ];
        }
    }
    
    // Sort by brightness (darkest first)
    usort($colorRoles, function($a, $b) {
        return $a['brightness'] <=> $b['brightness'];
    });
    
    // Primary: Darkest color (usually green #086D3B)
    $primary = $colorRoles[0]['color'] ?? $filtered[0] ?? '#0b3a63';
    
    // Find red color for accent (most saturated red)
    $redColor = null;
    foreach ($colorRoles as $role) {
        $rgb = $role['rgb'];
        // Check if it's red-ish (high red, lower green/blue)
        if ($rgb['r'] > 200 && $rgb['g'] < 100 && $rgb['b'] < 100) {
            $redColor = $role['color'];
            break;
        }
    }
    
    // Accent: Red color if found, otherwise most saturated
    if ($redColor) {
        $accent = $redColor;
    } else {
        usort($colorRoles, function($a, $b) {
            return $b['saturation'] <=> $a['saturation'];
        });
        $accent = $colorRoles[0]['color'] ?? $filtered[1] ?? '#fa4f26';
    }
    
    // Secondary: Medium brightness, not primary or accent
    $secondary = null;
    foreach ($colorRoles as $role) {
        if ($role['color'] !== $primary && $role['color'] !== $accent) {
            $secondary = $role['color'];
            break;
        }
    }
    
    // If no secondary found, use a lighter shade of primary or orange
    if (!$secondary) {
        // Look for orange/yellow colors
        foreach ($colorRoles as $role) {
            $rgb = $role['rgb'];
            // Orange/yellow (high red and green, low blue)
            if ($rgb['r'] > 200 && $rgb['g'] > 150 && $rgb['b'] < 100) {
                $secondary = $role['color'];
                break;
            }
        }
    }
    
    // Fallback
    if (!$secondary) {
        $secondary = $colorRoles[1]['color'] ?? $colorRoles[0]['color'] ?? '#1a5a8a';
    }
    
    return [
        'primary' => $primary,
        'secondary' => $secondary,
        'accent' => $accent,
    ];
}

function hex2rgb(string $hex): ?array {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return null;
    }
    
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2)),
    ];
}

function calculateSaturation(array $rgb): float {
    $r = $rgb['r'] / 255;
    $g = $rgb['g'] / 255;
    $b = $rgb['b'] / 255;
    
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $delta = $max - $min;
    
    if ($max == 0) return 0;
    
    return $delta / $max;
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$apply = in_array('--apply', $argv);
$dryRun = !$apply;

printHeader("Extract Logo Colors");

try {
    $db = getDB();
    $repository = new SiteOptionRepository($db);
    
    // Get logo URL from site options
    $logoUrl = $repository->get('site_logo');
    
    if (empty($logoUrl)) {
        printError("No logo found in site options!");
        printInfo("Please upload a logo first in Admin → Options → General Settings");
        exit(1);
    }
    
    printInfo("Logo URL: $logoUrl");
    
    // Convert URL to file path
    $logoPath = null;
    if (strpos($logoUrl, '/uploads/') !== false) {
        $relativePath = parse_url($logoUrl, PHP_URL_PATH);
        $logoPath = __DIR__ . '/..' . $relativePath;
    } else {
        // Try to find logo in uploads directory
        $uploadsDir = __DIR__ . '/../uploads/site';
        $files = glob($uploadsDir . '/*.{svg,png,jpg,jpeg}', GLOB_BRACE);
        if (!empty($files)) {
            $logoPath = $files[0]; // Use first logo found
        }
    }
    
    if (!$logoPath || !file_exists($logoPath)) {
        printError("Logo file not found: " . ($logoPath ?? 'N/A'));
        printInfo("Looking for logo files in uploads/site/...");
        
        $uploadsDir = __DIR__ . '/../uploads/site';
        if (is_dir($uploadsDir)) {
            $files = glob($uploadsDir . '/*.{svg,png,jpg,jpeg}', GLOB_BRACE);
            if (!empty($files)) {
                printInfo("Found logo files:");
                foreach ($files as $file) {
                    echo "  - " . basename($file) . "\n";
                }
                $logoPath = $files[0];
                printInfo("Using: " . basename($logoPath));
            }
        }
        
        if (!$logoPath || !file_exists($logoPath)) {
            printError("Could not find logo file. Please check the logo path.");
            exit(1);
        }
    }
    
    printInfo("Analyzing logo: " . basename($logoPath));
    
    // Extract colors
    $extractedColors = [];
    if (strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) === 'svg') {
        $extractedColors = extractColorsFromSVG($logoPath);
    } else {
        printWarning("Image analysis for PNG/JPG not yet implemented. Please use SVG logo.");
        printInfo("You can manually set colors in Admin → Options → Colors & Theme");
        exit(0);
    }
    
    if (empty($extractedColors)) {
        printError("No colors found in logo!");
        printInfo("The logo might be using CSS classes or external styles.");
        printInfo("You can manually set colors in Admin → Options → Colors & Theme");
        exit(1);
    }
    
    printInfo("Found " . count($extractedColors) . " color(s) in logo:");
    foreach ($extractedColors as $color) {
        echo "  - $color\n";
    }
    
    // Analyze and determine primary, secondary, accent
    $colorScheme = analyzeColors($extractedColors);
    
    printHeader("Color Scheme Analysis");
    
    echo "Primary Color:   " . $colorScheme['primary'] . "\n";
    echo "Secondary Color: " . $colorScheme['secondary'] . "\n";
    echo "Accent Color:    " . $colorScheme['accent'] . "\n\n";
    
    // Get current colors
    $currentPrimary = $repository->get('primary_color') ?? '#0b3a63';
    $currentSecondary = $repository->get('secondary_color') ?? '#1a5a8a';
    $currentAccent = $repository->get('accent_color') ?? '#fa4f26';
    
    printInfo("Current colors:");
    echo "  Primary:   $currentPrimary\n";
    echo "  Secondary: $currentSecondary\n";
    echo "  Accent:    $currentAccent\n\n";
    
    if ($dryRun) {
        printWarning("DRY RUN MODE - Colors will not be updated");
        printInfo("Use --apply flag to actually update colors");
    } else {
        printInfo("Updating site colors...");
        
        $repository->set('primary_color', $colorScheme['primary']);
        $repository->set('secondary_color', $colorScheme['secondary']);
        $repository->set('accent_color', $colorScheme['accent']);
        
        printSuccess("Colors updated successfully!");
        printInfo("Refresh your website to see the changes");
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit(0);

