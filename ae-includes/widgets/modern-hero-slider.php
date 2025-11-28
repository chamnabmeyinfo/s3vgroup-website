<?php
/**
 * Modern Hero Slider Widget
 * Styled to match the modern frontend design
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

use App\Database\Connection;
use App\Domain\Content\SliderRepository;

if (!option('enable_hero_slider', '1')) {
    return;
}

$db = getDB();
$repository = new SliderRepository($db);
$slides = $repository->published();

if (empty($slides)) {
    // Use default modern hero section if no slides
    $heroTitle = option('homepage_hero_title', 'Transform Your Warehouse Operations');
    $heroSubtitle = option('homepage_hero_subtitle', 'Discover cutting-edge industrial solutions designed to optimize your workflow, increase productivity, and drive business growth.');
    ?>
    <section class="modern-hero">
        <div class="modern-hero-content">
            <div class="modern-hero-badge">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Industrial Excellence Since 2020</span>
            </div>
            <h1 class="modern-hero-title">
                <?php echo e($heroTitle); ?>
            </h1>
            <p class="modern-hero-description">
                <?php echo e($heroSubtitle); ?>
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
    return;
}
?>

<!-- Modern Hero Slider -->
<section id="modern-hero-slider" class="modern-hero-slider">
    <?php foreach ($slides as $index => $slide): ?>
        <div class="modern-slider-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo $index; ?>">
            <div class="modern-slider-image-wrapper">
                <img 
                    src="<?php echo e(fullImageUrl($slide['image_url'])); ?>" 
                    alt="<?php echo e($slide['title']); ?>"
                    class="modern-slider-image"
                    loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                >
                <div class="modern-slider-overlay"></div>
            </div>
            <div class="modern-slider-content">
                <div class="modern-slider-content-inner">
                    <?php if (!empty($slide['subtitle'])): ?>
                        <div class="modern-slider-badge modern-animate-fade-in-up modern-animate-delay-100">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span><?php echo e($slide['subtitle']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="modern-slider-title modern-animate-fade-in-up modern-animate-delay-200">
                        <?php echo e($slide['title']); ?>
                    </h1>
                    
                    <?php if (!empty($slide['description'])): ?>
                        <p class="modern-slider-description modern-animate-fade-in-up modern-animate-delay-300">
                            <?php echo e($slide['description']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($slide['link_url']) && !empty($slide['link_text'])): ?>
                        <div class="modern-slider-actions modern-animate-fade-in-up modern-animate-delay-400">
                            <a 
                                href="<?php echo e($slide['link_url']); ?>" 
                                class="modern-hero-button-primary"
                                style="<?php echo !empty($slide['button_color']) ? 'background: ' . e($slide['button_color']) . ';' : ''; ?>"
                            >
                                <span><?php echo e($slide['link_text']); ?></span>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Navigation Arrows -->
    <button class="modern-slider-arrow modern-slider-arrow-prev" aria-label="Previous slide">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button class="modern-slider-arrow modern-slider-arrow-next" aria-label="Next slide">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    
    <!-- Dots Navigation -->
    <div class="modern-slider-dots">
        <?php foreach ($slides as $index => $slide): ?>
            <button 
                class="modern-slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                data-slide-index="<?php echo $index; ?>"
                aria-label="Go to slide <?php echo $index + 1; ?>"
            ></button>
        <?php endforeach; ?>
    </div>
</section>

<script>
// Include slider JavaScript
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initModernSlider === 'function') {
        window.initModernSlider();
    }
});
</script>
<script src="<?php echo asset('ae-includes/js/modern-slider.js'); ?>?v=<?php echo time(); ?>"></script>

