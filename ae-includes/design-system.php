<?php
/**
 * Design System - Dynamic CSS Generator
 * This file generates CSS based on site options
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../bootstrap/app.php';
}

// Get design options
$fontFamily = option('design_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif');
$fontSizeBase = (int) option('design_font_size_base', 16);
$fontWeightNormal = (int) option('design_font_weight_normal', 400);
$fontWeightBold = (int) option('design_font_weight_bold', 700);
$lineHeight = (float) option('design_line_height', 1.6);
$headingFont = option('design_heading_font', '');
$borderRadius = (int) option('design_border_radius', 8);
$buttonStyle = option('design_button_style', 'rounded');
$buttonPaddingX = (int) option('design_button_padding_x', 24);
$buttonPaddingY = (int) option('design_button_padding_y', 12);
$cardShadow = option('design_card_shadow', 'medium');
$spacingUnit = (int) option('design_spacing_unit', 8);
$containerWidth = (int) option('design_container_width', 1280);
$headerHeight = (int) option('design_header_height', 64);
$linkColor = option('design_link_color', '');
$linkHoverColor = option('design_link_hover_color', '');
$backgroundPattern = option('design_background_pattern', 'none');
$backgroundImage = option('design_background_image', '');
$backgroundOverlay = (int) option('design_background_overlay', 0);
$customCSS = option('design_custom_css', '');

// Get color options
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');
$headerBg = option('header_background', '#ffffff');
$footerBg = option('footer_background', $primaryColor);

// Use primary color for links if not specified
if (empty($linkColor)) {
    $linkColor = $primaryColor;
}
if (empty($linkHoverColor)) {
    $linkHoverColor = $accentColor;
}

// Button border radius based on style
$buttonBorderRadius = match($buttonStyle) {
    'square' => '0px',
    'pill' => '9999px',
    default => $borderRadius . 'px',
};

// Card shadow values
$cardShadowValue = match($cardShadow) {
    'small' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
    'medium' => '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
    'large' => '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    default => 'none',
};

// Background pattern CSS
$backgroundPatternCSS = '';
if ($backgroundPattern !== 'none') {
    $patternColor = 'rgba(0, 0, 0, 0.03)';
    switch ($backgroundPattern) {
        case 'dots':
            $backgroundPatternCSS = 'background-image: radial-gradient(circle, ' . $patternColor . ' 1px, transparent 1px); background-size: 20px 20px;';
            break;
        case 'grid':
            $backgroundPatternCSS = 'background-image: linear-gradient(' . $patternColor . ' 1px, transparent 1px), linear-gradient(90deg, ' . $patternColor . ' 1px, transparent 1px); background-size: 20px 20px;';
            break;
        case 'lines':
            $backgroundPatternCSS = 'background-image: repeating-linear-gradient(0deg, transparent, transparent 2px, ' . $patternColor . ' 2px, ' . $patternColor . ' 4px);';
            break;
    }
}

// Generate CSS
ob_start();
?>
<style id="dynamic-design-system">
/* Design System - Generated from Site Options */

:root {
    /* Typography */
    --font-family: <?php echo $fontFamily; ?>;
    --font-size-base: <?php echo $fontSizeBase; ?>px;
    --font-weight-normal: <?php echo $fontWeightNormal; ?>;
    --font-weight-bold: <?php echo $fontWeightBold; ?>;
    --line-height: <?php echo $lineHeight; ?>;
    --heading-font: <?php echo $headingFont ?: $fontFamily; ?>;
    
    /* Colors */
    --primary-color: <?php echo $primaryColor; ?>;
    --secondary-color: <?php echo $secondaryColor; ?>;
    --accent-color: <?php echo $accentColor; ?>;
    --link-color: <?php echo $linkColor; ?>;
    --link-hover-color: <?php echo $linkHoverColor; ?>;
    
    /* Layout */
    --border-radius: <?php echo $borderRadius; ?>px;
    --button-border-radius: <?php echo $buttonBorderRadius; ?>;
    --button-padding-x: <?php echo $buttonPaddingX; ?>px;
    --button-padding-y: <?php echo $buttonPaddingY; ?>px;
    --spacing-unit: <?php echo $spacingUnit; ?>px;
    --container-width: <?php echo $containerWidth; ?>px;
    --header-height: <?php echo $headerHeight; ?>px;
    
    /* Shadows */
    --card-shadow: <?php echo $cardShadowValue; ?>;
}

/* Apply Typography */
body {
    font-family: var(--font-family);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-normal);
    line-height: var(--line-height);
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--heading-font);
    font-weight: var(--font-weight-bold);
}

/* Apply Colors */
a {
    color: var(--link-color);
    transition: color 0.2s;
}

a:hover {
    color: var(--link-hover-color);
}

/* Apply Button Styles */
.btn, button[type="submit"], .button, a.btn {
    border-radius: var(--button-border-radius) !important;
    padding: var(--button-padding-y) var(--button-padding-x) !important;
    transition: all 0.2s;
}

/* Apply Card Styles */
.card, .bg-white.rounded-lg {
    box-shadow: var(--card-shadow);
    border-radius: var(--border-radius);
}

/* Apply Container */
.container {
    max-width: var(--container-width);
}

/* Apply Header Height */
header {
    min-height: var(--header-height);
}

/* Background Pattern */
<?php if ($backgroundPatternCSS): ?>
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: -2;
    <?php echo $backgroundPatternCSS; ?>
    pointer-events: none;
}
<?php endif; ?>

/* Background Image */
<?php if ($backgroundImage): ?>
body::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: -1;
    background-image: url('<?php echo e($backgroundImage); ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: <?php echo $backgroundOverlay / 100; ?>;
    pointer-events: none;
}
<?php endif; ?>

/* Custom CSS from Site Options */
<?php if ($customCSS): ?>
<?php echo $customCSS; ?>
<?php endif; ?>

/* Performance Optimization */
<?php if (option('enable_compression', '1') === '1'): ?>
/* GZIP compression enabled via server configuration */
<?php endif; ?>
</style>
<?php
$cssOutput = ob_get_clean();
echo $cssOutput;
?>

