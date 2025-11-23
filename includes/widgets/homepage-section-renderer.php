<?php
/**
 * Homepage Section Renderer
 * Renders homepage sections dynamically based on section type
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

use App\Database\Connection;
use App\Domain\Content\HomepageSectionRepository;

if (!isset($db)) {
    $db = Connection::getInstance();
}

// Get page_id if provided (null means homepage)
$pageId = $pageId ?? null;

$repository = new HomepageSectionRepository($db);
$sections = $repository->active($pageId);

$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

if (empty($sections)) {
    // No sections in builder, return false to use default homepage
    return false;
}

foreach ($sections as $section) {
    renderSection($section, $primaryColor, $secondaryColor, $accentColor, $db);
}

return true;

function renderSection($section, $primaryColor, $secondaryColor, $accentColor, $db) {
    $content = $section['content'] ?? [];
    $settings = $section['settings'] ?? [];
    $title = $section['title'] ?? '';
    
    // Get styles from settings (support both old format settings.* and new format settings.styles.*)
    $styles = $settings['styles'] ?? [];
    
    // Merge old format settings directly into styles for backward compatibility
    if (empty($styles)) {
        $styles = array_intersect_key($settings, array_flip([
            'padding', 'margin', 'background_color', 'background_image', 'text_color',
            'font_size', 'font_weight', 'text_align', 'border_style', 'border_radius', 'box_shadow'
        ]));
    }
    
    switch ($section['section_type']) {
        case 'hero':
            renderHeroSection($title, $content, $styles, $primaryColor, $secondaryColor, $accentColor);
            break;
            
        case 'categories':
            renderCategoriesSection($title, $settings, $styles, $primaryColor, $db);
            break;
            
        case 'products':
            renderProductsSection($title, $settings, $styles, $primaryColor, $db);
            break;
            
        case 'features':
            renderFeaturesSection($title, $content, $styles, $primaryColor);
            break;
            
        case 'testimonials':
            renderTestimonialsSection($title, $settings, $styles, $primaryColor, $db);
            break;
            
        case 'newsletter':
            renderNewsletterSection($title, $content, $styles, $primaryColor, $secondaryColor);
            break;
            
        case 'cta':
            renderCTASection($title, $content, $styles, $primaryColor, $secondaryColor, $accentColor);
            break;
            
        case 'custom':
            renderCustomSection($title, $content, $settings);
            break;
            
        case 'heading':
            renderHeadingSection($title, $content, $styles);
            break;
            
        case 'text':
            renderTextSection($title, $content, $styles);
            break;
            
        case 'spacer':
            renderSpacerSection($content);
            break;
            
        case 'divider':
            renderDividerSection($content, $styles);
            break;
    }
}

// Helper function to build inline styles from settings
function buildSectionStyles($styles) {
    if (empty($styles) || !is_array($styles)) {
        return '';
    }
    
    $styleArray = [];
    
    // Layout
    if (!empty($styles['padding'])) {
        $styleArray[] = 'padding: ' . htmlspecialchars($styles['padding'], ENT_QUOTES);
    }
    if (!empty($styles['margin'])) {
        $styleArray[] = 'margin: ' . htmlspecialchars($styles['margin'], ENT_QUOTES);
    }
    
    // Background
    if (!empty($styles['background_color'])) {
        $styleArray[] = 'background-color: ' . htmlspecialchars($styles['background_color'], ENT_QUOTES);
    }
    if (!empty($styles['background_image'])) {
        $styleArray[] = 'background-image: url(' . htmlspecialchars($styles['background_image'], ENT_QUOTES) . ')';
        $styleArray[] = 'background-size: cover';
        $styleArray[] = 'background-position: ' . htmlspecialchars($styles['background_position'] ?? 'center', ENT_QUOTES);
    }
    
    // Typography
    if (!empty($styles['text_color'])) {
        $styleArray[] = 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES);
    }
    if (!empty($styles['font_size'])) {
        $styleArray[] = 'font-size: ' . htmlspecialchars($styles['font_size'], ENT_QUOTES);
    }
    if (!empty($styles['font_weight'])) {
        $styleArray[] = 'font-weight: ' . htmlspecialchars($styles['font_weight'], ENT_QUOTES);
    }
    if (!empty($styles['text_align'])) {
        $styleArray[] = 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES);
    }
    if (!empty($styles['line_height'])) {
        $styleArray[] = 'line-height: ' . htmlspecialchars($styles['line_height'], ENT_QUOTES);
    }
    if (!empty($styles['letter_spacing'])) {
        $styleArray[] = 'letter-spacing: ' . htmlspecialchars($styles['letter_spacing'], ENT_QUOTES);
    }
    if (!empty($styles['font_family'])) {
        $styleArray[] = 'font-family: ' . htmlspecialchars($styles['font_family'], ENT_QUOTES);
    }
    
    // Border & Shadow
    if (!empty($styles['border_style'])) {
        $border = htmlspecialchars($styles['border_width'] ?? '1px', ENT_QUOTES) . ' ';
        $border .= htmlspecialchars($styles['border_style'], ENT_QUOTES) . ' ';
        $border .= htmlspecialchars($styles['border_color'] ?? '#ddd', ENT_QUOTES);
        $styleArray[] = 'border: ' . $border;
    }
    if (!empty($styles['border_radius'])) {
        $styleArray[] = 'border-radius: ' . htmlspecialchars($styles['border_radius'], ENT_QUOTES);
    }
    if (!empty($styles['box_shadow'])) {
        $styleArray[] = 'box-shadow: ' . htmlspecialchars($styles['box_shadow'], ENT_QUOTES);
    }
    
    return !empty($styleArray) ? implode('; ', $styleArray) . ';' : '';
}

function renderHeroSection($title, $content, $styles, $primaryColor, $secondaryColor, $accentColor) {
    $subtitle = $content['subtitle'] ?? '';
    $buttonText = $content['buttonText'] ?? 'Browse Products';
    $buttonLink = $content['buttonLink'] ?? '/products.php';
    $bgImage = $content['backgroundImage'] ?? $styles['background_image'] ?? '';
    $overlayColor = $content['overlayColor'] ?? 'rgba(0,0,0,0.5)';
    
    $sectionStyles = buildSectionStyles($styles);
    
    // Build background style
    $backgroundStyle = '';
    if ($bgImage) {
        $backgroundStyle = "background-image: url('" . htmlspecialchars($bgImage, ENT_QUOTES) . "'); background-size: cover; background-position: center;";
    } elseif (!empty($styles['background_color'])) {
        $backgroundStyle = 'background-color: ' . htmlspecialchars($styles['background_color'], ENT_QUOTES) . ';';
    } else {
        $backgroundStyle = "background: linear-gradient(135deg, {$primaryColor}, {$secondaryColor});";
    }
    
    // Remove background from sectionStyles if we're setting it separately
    $sectionStyles = preg_replace('/background[^;]*;/i', '', $sectionStyles);
    ?>
    <section class="py-20 md:py-32 text-white relative overflow-hidden" 
             style="<?php echo $backgroundStyle . ' ' . $sectionStyles; ?>">
        <?php if ($bgImage): ?>
            <div class="absolute inset-0" style="background-color: <?php echo htmlspecialchars($overlayColor, ENT_QUOTES); ?>"></div>
        <?php endif; ?>
        <div class="container mx-auto px-4 relative z-10" style="<?php echo !empty($styles['text_align']) ? 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES) . ';' : ''; ?>">
            <div class="max-w-4xl mx-auto space-y-6 animate-on-scroll" data-animation="fadeInUp">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight" 
                    style="<?php 
                        if (!empty($styles['font_size'])) echo 'font-size: ' . htmlspecialchars($styles['font_size'], ENT_QUOTES) . '; ';
                        if (!empty($styles['text_color'])) echo 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES) . '; ';
                        if (!empty($styles['font_weight'])) echo 'font-weight: ' . htmlspecialchars($styles['font_weight'], ENT_QUOTES) . '; ';
                    ?>">
                    <?php echo e($content['title'] ?? $title); ?>
                </h1>
                <?php if ($subtitle): ?>
                    <p class="text-lg md:text-xl max-w-3xl mx-auto" 
                       style="<?php 
                           if (!empty($styles['text_color'])) echo 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES) . '; ';
                           if (empty($styles['text_color'])) echo 'color: rgba(255,255,255,0.9); ';
                       ?>">
                        <?php echo e($subtitle); ?>
                    </p>
                <?php endif; ?>
                <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                    <a href="<?php echo e($buttonLink); ?>" 
                       class="px-8 py-3 bg-white text-lg rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg"
                       style="color: <?php echo e($primaryColor); ?>;">
                        <?php echo e($buttonText); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
}

function renderCategoriesSection($title, $settings, $styles, $primaryColor, $db) {
    $limit = (int) ($settings['limit'] ?? 12);
    $columns = min(max((int) ($settings['columns'] ?? 4), 2), 6);
    $gridCols = [
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4',
        5 => 'grid-cols-2 md:grid-cols-5',
        6 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
    ];
    
    $sectionStyles = buildSectionStyles($styles);
    // Set default background if not specified
    if (empty($styles['background_color']) && empty($styles['background_image'])) {
        $sectionStyles = 'background-color: #f9fafb; ' . $sectionStyles;
    }
    
    require_once __DIR__ . '/../../includes/functions.php';
    $categories = getFeaturedCategories($db, $limit);
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <?php if ($title): ?>
                <h2 class="text-3xl md:text-4xl font-bold mb-12" 
                    style="color: <?php echo !empty($styles['text_color']) ? e($styles['text_color']) : e($primaryColor); ?>; 
                           text-align: <?php echo !empty($styles['text_align']) ? e($styles['text_align']) : 'center'; ?>; 
                           <?php if (!empty($styles['font_size'])) echo 'font-size: ' . e($styles['font_size']) . '; '; ?>
                           <?php if (!empty($styles['font_weight'])) echo 'font-weight: ' . e($styles['font_weight']) . '; '; ?>">
                    <?php echo e($title); ?>
                </h2>
            <?php endif; ?>
            <div class="grid <?php echo $gridCols[$columns] ?? $gridCols[4]; ?> gap-4 stagger-children">
                <?php foreach ($categories as $index => $category): ?>
                    <a href="<?php echo base_url('products.php?category=' . urlencode($category['slug'])); ?>" 
                       class="category-item bg-white rounded-lg border border-gray-200 hover-lift p-4 flex items-center gap-3 animate-on-scroll group"
                       data-animation="fadeInUp"
                       data-delay="<?php echo ($index * 0.05); ?>">
                        <div class="category-image-wrapper flex-shrink-0 w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-md overflow-hidden flex items-center justify-center relative">
                            <?php
                            $categoryImage = $category['icon'] ?? null;
                            if (!$categoryImage) {
                                try {
                                    $productStmt = $db->prepare('SELECT heroImage FROM products WHERE categoryId = :categoryId AND heroImage IS NOT NULL AND heroImage != "" AND status = "PUBLISHED" LIMIT 1');
                                    $productStmt->execute([':categoryId' => $category['id']]);
                                    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
                                    $categoryImage = $product['heroImage'] ?? null;
                                } catch (Exception $e) {}
                            }
                            $firstLetter = strtoupper(substr($category['name'], 0, 1));
                            ?>
                            <div class="category-icon-placeholder absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <div class="category-icon-loader">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            <?php if ($categoryImage): ?>
                                <img src="<?php echo e($categoryImage); ?>" 
                                     alt="<?php echo e($category['name']); ?>" 
                                     loading="lazy"
                                     class="category-image w-full h-full object-cover opacity-0 transition-opacity duration-300">
                            <?php else: ?>
                                <div class="category-fallback-icon w-full h-full flex items-center justify-center absolute inset-0 z-10">
                                    <div class="category-initial-badge w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md" 
                                         style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
                                        <?php echo e($firstLetter); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2" style="--hover-color: <?php echo e($primaryColor); ?>;">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderProductsSection($title, $settings, $styles, $primaryColor, $db) {
    $limit = (int) ($settings['limit'] ?? 6);
    $columns = min(max((int) ($settings['columns'] ?? 3), 2), 4);
    
    $sectionStyles = buildSectionStyles($styles);
    // Set default background if not specified
    if (empty($styles['background_color']) && empty($styles['background_image'])) {
        $sectionStyles = 'background-color: #ffffff; ' . $sectionStyles;
    }
    
    require_once __DIR__ . '/../../includes/functions.php';
    $products = getFeaturedProducts($db, $limit);
    
    $gridCols = [
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    ];
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <?php if ($title): ?>
                <h2 class="text-4xl font-bold mb-12" 
                    style="color: <?php echo !empty($styles['text_color']) ? e($styles['text_color']) : e($primaryColor); ?>; 
                           text-align: <?php echo !empty($styles['text_align']) ? e($styles['text_align']) : 'center'; ?>; 
                           <?php if (!empty($styles['font_size'])) echo 'font-size: ' . e($styles['font_size']) . '; '; ?>
                           <?php if (!empty($styles['font_weight'])) echo 'font-weight: ' . e($styles['font_weight']) . '; '; ?>">
                    <?php echo e($title); ?>
                </h2>
            <?php endif; ?>
            <div class="grid <?php echo $gridCols[$columns] ?? $gridCols[3]; ?> gap-6">
                <?php foreach ($products as $product): ?>
                    <a href="<?php echo base_url('product.php?slug=' . urlencode($product['slug'])); ?>" 
                       class="group rounded-xl border border-gray-200 bg-white shadow-sm hover-lift overflow-hidden animate-on-scroll flex flex-col image-hover-scale"
                       data-animation="fadeInUp">
                        <?php if ($product['heroImage']): ?>
                            <div class="product-card-image-wrapper">
                                <img src="<?php echo e($product['heroImage']); ?>" 
                                     alt="<?php echo e($product['name']); ?>" 
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>
                        <div class="p-4 flex-1">
                            <h3 class="font-semibold text-lg mb-2"><?php echo e($product['name']); ?></h3>
                            <?php if ($product['price']): ?>
                                <p class="text-xl font-bold" style="color: <?php echo e($primaryColor); ?>;">
                                    $<?php echo number_format((float)$product['price'], 2); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderFeaturesSection($title, $content, $styles, $primaryColor) {
    $subtitle = $content['subtitle'] ?? '';
    $itemsJson = $content['items'] ?? '[]';
    $items = is_string($itemsJson) ? json_decode($itemsJson, true) : $itemsJson;
    if (!is_array($items)) $items = [];
    
    $sectionStyles = buildSectionStyles($styles);
    if (empty($styles['background_color']) && empty($styles['background_image'])) {
        $sectionStyles = 'background-color: #ffffff; ' . $sectionStyles;
    }
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <?php if ($title): ?>
                <h2 class="text-4xl font-bold mb-4" 
                    style="color: <?php echo !empty($styles['text_color']) ? e($styles['text_color']) : e($primaryColor); ?>; 
                           text-align: <?php echo !empty($styles['text_align']) ? e($styles['text_align']) : 'center'; ?>; 
                           <?php if (!empty($styles['font_size'])) echo 'font-size: ' . e($styles['font_size']) . '; '; ?>
                           <?php if (!empty($styles['font_weight'])) echo 'font-weight: ' . e($styles['font_weight']) . '; '; ?>">
                    <?php echo e($title); ?>
                </h2>
            <?php endif; ?>
            <?php if ($subtitle): ?>
                <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
            <div class="grid md:grid-cols-4 gap-6">
                <?php foreach ($items as $index => $item): ?>
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm p-6 text-center animate-on-scroll hover-lift hover-glow"
                         data-animation="zoomIn"
                         data-delay="<?php echo ($index * 0.1); ?>">
                        <div class="text-4xl mb-4"><?php echo e($item['icon'] ?? '✨'); ?></div>
                        <h3 class="font-semibold text-lg mb-2"><?php echo e($item['title'] ?? 'Feature'); ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo e($item['description'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderTestimonialsSection($title, $settings, $styles, $primaryColor, $db) {
    $limit = (int) ($settings['limit'] ?? 6);
    $featuredOnly = isset($settings['featuredOnly']) ? (bool)$settings['featuredOnly'] : true;
    
    $sectionStyles = buildSectionStyles($styles);
    if (empty($styles['background_color']) && empty($styles['background_image'])) {
        $sectionStyles = 'background-color: #f9fafb; ' . $sectionStyles;
    }
    
    require_once __DIR__ . '/../../includes/functions.php';
    require_once __DIR__ . '/../../app/Domain/Content/TestimonialRepository.php';
    
    $repository = new \App\Domain\Content\TestimonialRepository($db);
    $testimonials = $featuredOnly 
        ? $repository->featured($limit) 
        : array_slice($repository->published(), 0, $limit);
    ?>
    <?php if (!empty($testimonials)): ?>
        <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
            <div class="container mx-auto px-4">
                <?php if ($title): ?>
                    <h2 class="text-4xl font-bold mb-12" 
                        style="color: <?php echo !empty($styles['text_color']) ? e($styles['text_color']) : e($primaryColor); ?>; 
                               text-align: <?php echo !empty($styles['text_align']) ? e($styles['text_align']) : 'center'; ?>; 
                               <?php if (!empty($styles['font_size'])) echo 'font-size: ' . e($styles['font_size']) . '; '; ?>
                               <?php if (!empty($styles['font_weight'])) echo 'font-weight: ' . e($styles['font_weight']) . '; '; ?>">
                        <?php echo e($title); ?>
                    </h2>
                <?php endif; ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 stagger-children">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 hover-lift animate-on-scroll"
                             data-animation="fadeInUp"
                             data-delay="<?php echo ($index * 0.1); ?>">
                            <div class="flex items-start gap-4 mb-4">
                                <?php if ($testimonial['avatar']): ?>
                                    <img src="<?php echo e($testimonial['avatar']); ?>" alt="<?php echo e($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-semibold text-white" style="background-color: <?php echo e($primaryColor); ?>;">
                                        <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900"><?php echo e($testimonial['name']); ?></h3>
                                    <?php if ($testimonial['company']): ?>
                                        <p class="text-sm text-gray-600"><?php echo e($testimonial['company']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 mb-4">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-5 h-5 <?php echo $i <= ($testimonial['rating'] ?? 5) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <p class="text-gray-700 leading-relaxed"><?php echo nl2br(e($testimonial['content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?php
}

function renderNewsletterSection($title, $content, $styles, $primaryColor, $secondaryColor) {
    $subtitle = $content['subtitle'] ?? 'Subscribe to our newsletter for the latest updates and exclusive offers.';
    $buttonText = $content['buttonText'] ?? 'Subscribe';
    
    $sectionStyles = buildSectionStyles($styles);
    
    // Build background style
    $backgroundStyle = '';
    if (!empty($styles['background_image'])) {
        $backgroundStyle = "background-image: url('" . htmlspecialchars($styles['background_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
    } elseif (!empty($styles['background_color'])) {
        $backgroundStyle = 'background-color: ' . htmlspecialchars($styles['background_color'], ENT_QUOTES) . ';';
    } else {
        $backgroundStyle = "background: linear-gradient(to right, {$primaryColor}, {$secondaryColor});";
    }
    
    // Remove background from sectionStyles if we're setting it separately
    $sectionStyles = preg_replace('/background[^;]*;/i', '', $sectionStyles);
    ?>
    <section class="section-wrapper text-white" style="<?php echo $backgroundStyle . ' ' . $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <?php include __DIR__ . '/newsletter-signup.php'; ?>
        </div>
    </section>
    <?php
}

function renderCTASection($title, $content, $styles, $primaryColor, $secondaryColor, $accentColor) {
    $subtitle = $content['subtitle'] ?? '';
    $buttonText = $content['buttonText'] ?? 'Get Started';
    $buttonLink = $content['buttonLink'] ?? '/quote.php';
    $secondaryButtonText = $content['secondaryButtonText'] ?? '';
    $secondaryButtonLink = $content['secondaryButtonLink'] ?? '';
    
    $sectionStyles = buildSectionStyles($styles);
    
    // Build background style
    $backgroundStyle = '';
    if (!empty($styles['background_image'])) {
        $backgroundStyle = "background-image: url('" . htmlspecialchars($styles['background_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
    } elseif (!empty($styles['background_color'])) {
        $backgroundStyle = 'background-color: ' . htmlspecialchars($styles['background_color'], ENT_QUOTES) . ';';
    } else {
        $background = $content['background'] ?? $primaryColor;
        $backgroundStyle = 'background-color: ' . htmlspecialchars($background, ENT_QUOTES) . ';';
    }
    
    // Remove background from sectionStyles if we're setting it separately
    $sectionStyles = preg_replace('/background[^;]*;/i', '', $sectionStyles);
    ?>
    <section class="section-wrapper text-white" style="<?php echo $backgroundStyle . ' ' . $sectionStyles; ?>">
        <div class="container mx-auto px-4" style="<?php echo !empty($styles['text_align']) ? 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES) . ';' : 'text-align: center;'; ?>">
            <h2 class="text-4xl font-bold mb-4 text-reveal"><?php echo e($title); ?></h2>
            <?php if ($subtitle): ?>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto">
                    <?php echo e($subtitle); ?>
                </p>
            <?php endif; ?>
            <div class="flex gap-4 justify-center">
                <a href="<?php echo e($buttonLink); ?>" 
                   class="px-6 py-3 bg-white rounded-md font-semibold hover:bg-gray-100 transition-colors"
                   style="color: <?php echo e($primaryColor); ?>;">
                    <?php echo e($buttonText); ?>
                </a>
                <?php if ($secondaryButtonText && $secondaryButtonLink): ?>
                    <a href="<?php echo e($secondaryButtonLink); ?>" 
                       class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors">
                        <?php echo e($secondaryButtonText); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderCustomSection($title, $content, $settings) {
    $html = $content['html'] ?? '';
    $css = $content['css'] ?? $settings['custom_css'] ?? '';
    $styles = $settings['styles'] ?? [];
    
    $sectionStyles = buildSectionStyles($styles);
    ?>
    <section class="custom-homepage-section section-wrapper" style="<?php echo $sectionStyles; ?>">
        <?php if ($css): ?>
            <style>
                <?php echo $css; ?>
            </style>
        <?php endif; ?>
        <?php if ($html): ?>
            <?php echo $html; ?>
        <?php endif; ?>
    </section>
    <?php
}

function renderHeadingSection($title, $content, $styles) {
    $headingTitle = $content['title'] ?? $title;
    $subtitle = $content['subtitle'] ?? '';
    $tag = $styles['tag'] ?? 'h2';
    
    $sectionStyles = buildSectionStyles($styles);
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <<?php echo $tag; ?> 
                style="<?php 
                    if (!empty($styles['font_size'])) echo 'font-size: ' . htmlspecialchars($styles['font_size'], ENT_QUOTES) . '; ';
                    if (!empty($styles['text_color'])) echo 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES) . '; ';
                    if (!empty($styles['font_weight'])) echo 'font-weight: ' . htmlspecialchars($styles['font_weight'], ENT_QUOTES) . '; ';
                    if (!empty($styles['text_align'])) echo 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES) . '; ';
                ?>">
                <?php echo e($headingTitle); ?>
            </<?php echo $tag; ?>>
            <?php if ($subtitle): ?>
                <p style="<?php 
                    if (!empty($styles['text_color'])) echo 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES) . '; ';
                    if (!empty($styles['text_align'])) echo 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES) . '; ';
                ?>">
                    <?php echo e($subtitle); ?>
                </p>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function renderTextSection($title, $content, $styles) {
    $textContent = $content['content'] ?? '';
    
    $sectionStyles = buildSectionStyles($styles);
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <div style="<?php 
                if (!empty($styles['text_color'])) echo 'color: ' . htmlspecialchars($styles['text_color'], ENT_QUOTES) . '; ';
                if (!empty($styles['text_align'])) echo 'text-align: ' . htmlspecialchars($styles['text_align'], ENT_QUOTES) . '; ';
                if (!empty($styles['font_size'])) echo 'font-size: ' . htmlspecialchars($styles['font_size'], ENT_QUOTES) . '; ';
                if (!empty($styles['line_height'])) echo 'line-height: ' . htmlspecialchars($styles['line_height'], ENT_QUOTES) . '; ';
            ?>">
                <?php echo $textContent; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderSpacerSection($content) {
    $height = (int) ($content['height'] ?? 60);
    ?>
    <section class="section-spacer" style="height: <?php echo $height; ?>px; display: block;"></section>
    <?php
}

function renderDividerSection($content, $styles) {
    $style = $content['style'] ?? 'solid';
    $sectionStyles = buildSectionStyles($styles);
    ?>
    <section class="section-wrapper" style="<?php echo $sectionStyles; ?>">
        <div class="container mx-auto px-4">
            <hr style="border: none; border-top: 1px <?php echo htmlspecialchars($style, ENT_QUOTES); ?> #ccc; width: 80%; margin: 0 auto;">
        </div>
    </section>
    <?php
}

