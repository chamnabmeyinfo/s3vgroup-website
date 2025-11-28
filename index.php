<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users, log them instead
ini_set('log_errors', 1);

// Load Ant Elite bootstrap (ae-load.php)
require_once __DIR__ . '/ae-load.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/ae-includes/functions.php';

$pageTitle = 'Home';
$pageDescription = $siteConfig['description'] ?? 'S3V Group - Warehouse Equipment';

// Fetch featured categories - with error handling
try {
    $db = getDB();
    $allCategories = getFeaturedCategories($db, 12);
    $categories = array_slice($allCategories, 0, 6); // Limit to 6 for 2x3 grid
    $products = getFeaturedProducts($db, 2); // Limit to 2 for featured section
    
    // OPTIMIZATION: Pre-fetch category images in ONE query instead of N queries (N+1 fix)
    // This prevents running a database query for each category (much faster!)
    if (!empty($categories)) {
        $categoryIds = array_column($categories, 'id');
        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            try {
                $imageQuery = $db->prepare("
                    SELECT DISTINCT categoryId, heroImage 
                    FROM products 
                    WHERE categoryId IN ($placeholders) 
                    AND heroImage IS NOT NULL 
                    AND heroImage != '' 
                    AND status = 'PUBLISHED'
                    ORDER BY updatedAt DESC
                ");
                $imageQuery->execute($categoryIds);
                $categoryImages = [];
                while ($row = $imageQuery->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($categoryImages[$row['categoryId']])) {
                        $categoryImages[$row['categoryId']] = $row['heroImage'];
                    }
                }
                
                // Attach images to categories
                foreach ($categories as &$category) {
                    if (empty($category['icon']) && isset($categoryImages[$category['id']])) {
                        $category['icon'] = $categoryImages[$category['id']];
                    }
                }
                unset($category); // Break reference
            } catch (Exception $e) {
                // Ignore image query errors, continue without images
                error_log('Category image query error: ' . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    // Log error but don't break the page
    error_log('Homepage error: ' . $e->getMessage());
    $allCategories = [];
    $categories = [];
    $products = [];
}

// Get site options (bootstrap already loaded above)
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

// Check if homepage builder is enabled and has sections (homepage has page_id = null)
$useHomepageBuilder = option('enable_homepage_builder', '0') === '1';
$homepagePageId = null; // Homepage uses null page_id

include __DIR__ . '/ae-includes/header.php';
?>

<?php
if ($useHomepageBuilder) {
    // Use homepage builder sections (pass null for homepage)
    $pageId = null; // Homepage
    $sections = include __DIR__ . '/ae-includes/widgets/homepage-section-renderer.php';
    // If no sections returned or empty, fall back to default
    if (!$sections) {
        $useHomepageBuilder = false;
    }
}

if (!$useHomepageBuilder) {
    // Check if hero slider is enabled
    $enableHeroSlider = option('enable_hero_slider', '1') === '1';
    
    if ($enableHeroSlider && file_exists(__DIR__ . '/ae-includes/widgets/hero-slider.php')) {
        // Include modern hero slider
        include __DIR__ . '/ae-includes/widgets/modern-hero-slider.php';
    } else {
        // Use static modern hero section
        ?>
        
        <!-- Modern Hero Section -->
        <section class="modern-hero">
            <div class="modern-hero-content">
                <div class="modern-hero-badge">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Industrial Excellence Since 2020</span>
                </div>
                <h1 class="modern-hero-title">
                    <?php echo e(option('hero_title', 'Transform Your Warehouse Operations')); ?>
                </h1>
                <p class="modern-hero-description">
                    <?php echo e(option('hero_subtitle', 'Discover cutting-edge industrial solutions designed to optimize your workflow, increase productivity, and drive business growth.')); ?>
                </p>
                <div class="modern-hero-actions">
                    <a href="<?php echo base_url('products.php'); ?>" class="modern-hero-button-primary">
                        <span>Explore Products</span>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" class="modern-hero-button-secondary">
                        <span>Get Free Quote</span>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
        <?php
    }
    ?>

    <!-- Products Section -->
    <section class="modern-section">
        <div class="modern-container">
            <div class="modern-section-header">
                <span class="modern-section-badge">Our Products</span>
                <h2 class="modern-section-title">Explore Our Product Categories</h2>
                <p class="modern-section-description">
                    Discover a comprehensive range of industrial equipment and solutions designed to meet your business needs.
                </p>
            </div>
            
            <div class="modern-grid modern-grid-3">
                <?php 
                $productImages = [
                    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=400&q=80',
                ];
                
                $productTitles = [
                    'Forklifts & Material Handling',
                    'Storage Racking',
                    'Automation Systems',
                    'Forklifts & Material Handling',
                    'Conveyor Systems',
                    'Safety Equipment'
                ];
                
                for ($i = 0; $i < 6; $i++):
                    $category = $categories[$i] ?? null;
                    $image = $category ? ($category['icon'] ?? $productImages[$i]) : $productImages[$i];
                    $title = $category ? $category['name'] : $productTitles[$i];
                    $description = $category ? ($category['description'] ?? 'Explore our range of ' . strtolower($title) . ' products.') : 'Discover high-quality industrial solutions.';
                    $link = $category ? base_url('products.php?category=' . urlencode($category['slug'])) : base_url('products.php');
                ?>
                    <div class="modern-card modern-animate-on-scroll">
                        <img src="<?php echo e($image); ?>" 
                             alt="<?php echo e($title); ?>" 
                             class="modern-card-image"
                             loading="lazy">
                        <div class="modern-card-content">
                            <h3 class="modern-card-title"><?php echo htmlspecialchars($title); ?></h3>
                            <p class="modern-card-description"><?php echo htmlspecialchars($description); ?></p>
                            <div class="modern-card-footer">
                                <a href="<?php echo $link; ?>" class="modern-card-link">
                                    <span>View Products</span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="modern-section modern-section-alt">
        <div class="modern-container">
            <div class="modern-section-header">
                <span class="modern-section-badge">Featured</span>
                <h2 class="modern-section-title">Featured Products</h2>
                <p class="modern-section-description">
                    Handpicked products that represent the best of our industrial solutions.
                </p>
            </div>
            
            <div class="modern-grid modern-grid-2">
                <?php 
                $featuredImages = [
                    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=600&q=80',
                ];
                
                $featuredTitles = [
                    'Heavy-Duty Pallet Jack',
                    'Warehouse Shelving Unit'
                ];
                
                $featuredDescriptions = [
                    'Robust and reliable material handling solution for heavy-duty operations.',
                    'Maximize your storage capacity with our premium shelving systems.'
                ];
                
                for ($i = 0; $i < 2; $i++):
                    $product = $products[$i] ?? null;
                    $image = $product ? ($product['heroImage'] ?? $featuredImages[$i]) : $featuredImages[$i];
                    $title = $product ? $product['name'] : $featuredTitles[$i];
                    $description = $product ? ($product['description'] ?? $featuredDescriptions[$i]) : $featuredDescriptions[$i];
                    $link = $product ? base_url('product.php?slug=' . urlencode($product['slug'])) : base_url('products.php');
                ?>
                    <div class="modern-card modern-animate-on-scroll">
                        <img src="<?php echo e($image); ?>" 
                             alt="<?php echo e($title); ?>" 
                             class="modern-card-image"
                             loading="lazy">
                        <div class="modern-card-content">
                            <h3 class="modern-card-title"><?php echo htmlspecialchars($title); ?></h3>
                            <p class="modern-card-description"><?php echo htmlspecialchars($description); ?></p>
                            <div class="modern-card-footer">
                                <a href="<?php echo $link; ?>" class="modern-card-link">
                                    <span>View Details</span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="modern-section">
        <div class="modern-container">
            <div class="modern-section-header">
                <span class="modern-section-badge">Our Services</span>
                <h2 class="modern-section-title">Comprehensive Industrial Solutions</h2>
                <p class="modern-section-description">
                    From installation to maintenance, we provide end-to-end support for all your industrial needs.
                </p>
            </div>
            
            <div class="modern-grid modern-grid-3">
                <?php 
                $services = [
                    ['name' => 'Installation', 'icon' => 'M5 13l4 4L19 7', 'description' => 'Professional installation services by certified technicians.'],
                    ['name' => 'Maintenance', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'description' => 'Regular maintenance to keep your equipment running smoothly.'],
                    ['name' => 'Repair', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'description' => 'Fast and reliable repair services for all equipment types.'],
                    ['name' => 'Consulting', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Expert consulting to optimize your operations.'],
                    ['name' => 'Training', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'description' => 'Comprehensive training programs for your team.'],
                    ['name' => 'Support', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'description' => '24/7 support to assist you whenever you need help.']
                ];
                
                foreach ($services as $service):
                ?>
                    <div class="modern-card modern-animate-on-scroll">
                        <div class="modern-card-content">
                            <div style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg);">
                                <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($service['icon']); ?>"/>
                                </svg>
                            </div>
                            <h3 class="modern-card-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p class="modern-card-description"><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
    // Animate on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.modern-animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
        
        // Header scroll effect
        const header = document.getElementById('modern-header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    });
    </script>
    
    <?php
    // Keep old sections commented out for now
    /*

    <!-- Old sections removed - using new design above -->
    /*
<!-- Service Icons Section - Like Screenshot Design -->
<section class="py-12 sm:py-16 bg-gray-800 text-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 sm:gap-6 md:gap-8">
            <div class="text-center animate-on-scroll" data-animation="fadeInUp">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-orange-500 flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base md:text-lg">Expert Support</h3>
            </div>
            <div class="text-center animate-on-scroll" data-animation="fadeInUp" data-delay="0.1">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-orange-500 flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base md:text-lg">Wide Selection</h3>
            </div>
            <div class="text-center animate-on-scroll" data-animation="fadeInUp" data-delay="0.2">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-orange-500 flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base md:text-lg">Quality Assurance</h3>
            </div>
            <div class="text-center animate-on-scroll" data-animation="fadeInUp" data-delay="0.3">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-orange-500 flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base md:text-lg">Warehouse</h3>
            </div>
            <div class="text-center animate-on-scroll" data-animation="fadeInUp" data-delay="0.4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-orange-500 flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base md:text-lg">Fast Delivery</h3>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section (if enabled) -->
<?php if (option('enable_newsletter', '1') === '1'): ?>
    <section class="py-20 bg-gradient-to-r" style="background: linear-gradient(to right, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
        <div class="container mx-auto px-4">
            <?php include __DIR__ . '/ae-includes/widgets/newsletter-signup.php'; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Testimonials Widget (if enabled) -->
<?php if (option('enable_testimonials', '1') === '1'): ?>
    <?php include __DIR__ . '/ae-includes/widgets/testimonials.php'; ?>
<?php endif; ?>

<!-- Why Choose Us Section - Dark Theme Like Screenshot -->
<section class="py-12 sm:py-16 md:py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 sm:mb-12 md:mb-16 animate-on-scroll" data-animation="fadeInDown">
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 sm:mb-4 text-white">
                Why Choose Us?
            </h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
            <!-- Feature Card 1 -->
            <div class="bg-gray-800 rounded-lg p-6 sm:p-7 md:p-8 text-center animate-on-scroll hover:bg-gray-750 transition-all duration-300"
                 data-animation="fadeInUp">
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 sm:mb-5 md:mb-6 rounded-lg bg-orange-500 flex items-center justify-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-white">Expert Support</h3>
                <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                    Professional maintenance, repair, and technical support from certified technicians
                </p>
            </div>
            
            <!-- Feature Card 2 -->
            <div class="bg-gray-800 rounded-lg p-6 sm:p-7 md:p-8 text-center animate-on-scroll hover:bg-gray-750 transition-all duration-300"
                 data-animation="fadeInUp"
                 data-delay="0.1">
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 sm:mb-5 md:mb-6 rounded-lg bg-orange-500 flex items-center justify-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-white">Wide Selection</h3>
                <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                    Comprehensive range of forklifts, racks, and factory equipment from trusted global brands
                </p>
            </div>
            
            <!-- Feature Card 3 -->
            <div class="bg-gray-800 rounded-lg p-6 sm:p-7 md:p-8 text-center animate-on-scroll hover:bg-gray-750 transition-all duration-300"
                 data-animation="fadeInUp"
                 data-delay="0.2">
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 sm:mb-5 md:mb-6 rounded-lg bg-orange-500 flex items-center justify-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-white">Fast Delivery</h3>
                <p class="text-gray-300 text-sm sm:text-base leading-relaxed">
                    Quick delivery and professional installation services across Cambodia
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section - Clean Design -->
<section class="py-12 sm:py-16 md:py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 sm:mb-10 md:mb-12 animate-on-scroll" data-animation="fadeInDown">
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 sm:mb-4 text-gray-900">
                Our Product Categories
            </h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 md:gap-6 stagger-children">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $category): ?>
                    <a href="<?php echo base_url('products.php?category=' . urlencode($category['slug'])); ?>" 
                       class="category-item bg-white rounded-lg border border-gray-200 hover:border-orange-500 shadow-sm hover:shadow-lg p-4 flex flex-col items-center gap-3 animate-on-scroll group transition-all duration-300"
                       data-animation="fadeInUp"
                       data-delay="<?php echo ($index * 0.05); ?>">
                        <!-- Category Image/Icon -->
                        <div class="category-image-wrapper flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center relative group-hover:scale-110 transition-transform duration-300">
                            <?php
                            // Get category image (already pre-fetched in optimization above)
                            $categoryImage = $category['icon'] ?? null;
                            
                            // Get first letter for icon fallback
                            $firstLetter = strtoupper(substr($category['name'], 0, 1));
                            ?>
                            
                            <!-- Loading Placeholder -->
                            <div class="category-icon-placeholder absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <div class="category-icon-loader">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Category Image -->
                            <?php if ($categoryImage): ?>
                                <img src="<?php echo e(fullImageUrl($categoryImage)); ?>" 
                                     alt="<?php echo e($category['name']); ?>" 
                                     loading="lazy"
                                     class="category-image w-full h-full object-cover opacity-0 transition-opacity duration-300">
                            <?php else: ?>
                                <!-- Fallback Icon with Initial -->
                                <div class="category-fallback-icon w-full h-full flex items-center justify-center absolute inset-0 z-10">
                                    <div class="category-initial-badge w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md" 
                                         style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor ?? $primaryColor); ?>);">
                                        <?php echo e($firstLetter); ?>
                                    </div>
                                </div>
                                <style>
                                    .category-initial-badge {
                                        animation: fadeInScale 0.3s ease-out;
                                    }
                                    @keyframes fadeInScale {
                                        from {
                                            opacity: 0;
                                            transform: scale(0.8);
                                        }
                                        to {
                                            opacity: 1;
                                            transform: scale(1);
                                        }
                                    }
                                </style>
                            <?php endif; ?>
                        </div>
                        <!-- Category Label -->
                        <div class="flex-1 min-w-0 text-center">
                            <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-orange-500 transition-colors line-clamp-2">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default categories if database is empty -->
                <?php
                $defaultCategories = [
                    ['name' => 'Forklifts', 'icon' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Racking Systems', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Material Handling', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Lifting Equipment', 'icon' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Weighing Scales', 'icon' => 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Conveyor Systems', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Storage Solutions', 'icon' => 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=200&q=80'],
                    ['name' => 'Safety Equipment', 'icon' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=200&q=80'],
                ];
                foreach ($defaultCategories as $index => $cat):
                ?>
                    <a href="<?php echo base_url('products.php'); ?>" 
                       class="category-item bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all p-4 flex items-center gap-3 animate-on-scroll group"
                       style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                        <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center">
                            <img src="<?php echo e($cat['icon'] ?? ''); ?>" 
                                 alt="<?php echo e($cat['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2" style="--hover-color: <?php echo e($primaryColor); ?>;">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section - Clean White Cards Like Screenshot -->
<?php if (!empty($products)): ?>
<section class="py-12 sm:py-16 md:py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 sm:mb-10 md:mb-12 animate-on-scroll"
             data-animation="fadeInDown">
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 sm:mb-4 text-gray-900">
                Featured Products
            </h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 md:gap-6">
            <?php foreach ($products as $index => $product): ?>
                <div class="group bg-white rounded-lg border border-gray-200 shadow-md hover:shadow-xl overflow-hidden animate-on-scroll flex flex-col transition-all duration-300"
                     data-animation="fadeInUp"
                     data-delay="<?php echo ($index * 0.05); ?>">
                    <!-- Product Image -->
                    <div class="relative w-full bg-gray-100 overflow-hidden" style="aspect-ratio: 4/3;">
                        <?php if ($product['heroImage']): ?>
                            <img 
                                src="<?php echo htmlspecialchars($product['heroImage']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Product Content -->
                    <div class="p-4 sm:p-5 md:p-6 flex flex-col flex-grow">
                        <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3 line-clamp-2 text-gray-900">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <?php if ($product['summary']): ?>
                            <p class="text-gray-600 text-sm mb-3 sm:mb-4 line-clamp-2 flex-grow">
                                <?php echo htmlspecialchars($product['summary']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($product['price']): ?>
                            <div class="mb-3 sm:mb-4">
                                <div class="text-xl sm:text-2xl font-bold text-gray-900">
                                    <?php echo display_price($product['price'], '$'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo base_url('product.php?slug=' . urlencode($product['slug'])); ?>" 
                           class="block w-full px-4 sm:px-6 py-2.5 sm:py-3 text-center text-white rounded-lg font-semibold text-sm sm:text-base transition-all hover:scale-105 transform bg-orange-500 hover:bg-orange-600">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section - Dark Theme -->
<section class="py-12 sm:py-16 md:py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-4 text-center">
        <div class="max-w-3xl mx-auto animate-on-scroll" data-animation="zoomIn">
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4 sm:mb-6 text-white">
                Ready to Equip Your Warehouse or Factory?
            </h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-300 mb-6 sm:mb-8 leading-relaxed">
                Contact our experts today for personalized recommendations and competitive pricing
            </p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center w-full sm:w-auto">
                <a href="<?php echo base_url('quote.php'); ?>" 
                   class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-bold text-base sm:text-lg transition-all duration-300 hover:scale-105 transform inline-flex items-center justify-center gap-2">
                    Get Free Quote
                </a>
                <a href="<?php echo base_url('contact.php'); ?>" 
                   class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 border-2 border-white text-white rounded-lg font-bold text-base sm:text-lg hover:bg-white/10 transition-all duration-300 hover:scale-105 transform inline-flex items-center justify-center gap-2">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
    */
}
?>

<?php include __DIR__ . '/ae-includes/footer.php'; ?>
