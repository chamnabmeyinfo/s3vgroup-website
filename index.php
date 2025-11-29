<?php
// Error reporting - production optimized
// Only enable full error reporting in development
if (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0); // Disable in production for better performance
    ini_set('display_errors', 0);
}
ini_set('log_errors', 1); // Always log errors

// Load Ant Elite bootstrap (ae-load.php)
require_once __DIR__ . '/ae-load.php';

require_once __DIR__ . '/config/database.php';

// Load site config with fallback
if (file_exists(__DIR__ . '/config/site.php')) {
    require_once __DIR__ . '/config/site.php';
} else {
    // Fallback if site.php doesn't exist
    $siteConfig = [
        'name' => 'S3V Group',
        'description' => 'Professional warehouse equipment solutions',
        'url' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
        'contact' => [
            'phone' => '',
            'email' => '',
            'address' => '',
            'hours' => '',
        ],
        'social' => [],
    ];
}

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
    // NEW REDESIGNED HOMEPAGE - Conversion-focused layout
    
    // 1. Hero Section
    include __DIR__ . '/ae-includes/widgets/homepage-hero.php';
    
    // 2. Product Categories Section
    include __DIR__ . '/ae-includes/widgets/homepage-categories.php';
    
    // 3. Highlighted Solutions Section
    include __DIR__ . '/ae-includes/widgets/homepage-solutions.php';
    
    // 4. Industries / Use Cases Section
    include __DIR__ . '/ae-includes/widgets/homepage-industries.php';
    
    // 5. Why Choose S3V Section
    include __DIR__ . '/ae-includes/widgets/homepage-why-choose.php';
    
    // 6. Testimonials Section (if enabled)
    if (option('enable_testimonials', '1') === '1') {
        $limit = 3;
        $featuredOnly = true;
        include __DIR__ . '/ae-includes/widgets/testimonials.php';
    }
    
    // 7. Process / How It Works Section
    include __DIR__ . '/ae-includes/widgets/homepage-process.php';
    
    // 8. Strong CTA Strip Section
    include __DIR__ . '/ae-includes/widgets/homepage-cta-strip.php';
    ?>
    
    <script>
    // Enhanced Homepage UX/UI Interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Improved Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    // Optional: Unobserve after animation to improve performance
                    // observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe all animated elements
        document.querySelectorAll('.modern-animate-on-scroll').forEach((el, index) => {
            // Add staggered delay for better visual effect
            el.style.transitionDelay = (index * 0.1) + 's';
            observer.observe(el);
        });
        
        // Enhanced header scroll effect
        const header = document.getElementById('modern-header');
        if (header) {
            let lastScroll = 0;
            window.addEventListener('scroll', function() {
                const currentScroll = window.scrollY;
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                    // Hide header on scroll down, show on scroll up
                    if (currentScroll > lastScroll && currentScroll > 200) {
                        header.style.transform = 'translateY(-100%)';
                    } else {
                        header.style.transform = 'translateY(0)';
                    }
                } else {
                    header.classList.remove('scrolled');
                    header.style.transform = 'translateY(0)';
                }
                lastScroll = currentScroll;
            }, { passive: true });
        }
        
        // Enhanced card interactions
        document.querySelectorAll('.modern-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
        
        // Image loading optimization
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('.modern-card-image[loading="lazy"]');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.animation = 'none';
                });
            });
        }
        
        // Add parallax effect to hero section (subtle)
        const hero = document.querySelector('.modern-hero');
        if (hero && window.innerWidth > 768) {
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * 0.3;
                hero.style.transform = `translateY(${rate}px)`;
            }, { passive: true });
        }
    });
    </script>
    
    <?php
    // Old sections removed - using new design above
}
?>

<?php include __DIR__ . '/ae-includes/footer.php'; ?>
