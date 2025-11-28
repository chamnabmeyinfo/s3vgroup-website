<?php
/**
 * Hero Slider Widget
 * Usage: include __DIR__ . '/widgets/hero-slider.php';
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
    // Use default hero section if no slides
    $heroTitle = option('homepage_hero_title', 'Warehouse & Factory Equipment Solutions');
    $heroSubtitle = option('homepage_hero_subtitle', 'Leading supplier of industrial equipment in Cambodia.');
    $primaryColor = option('primary_color', '#0b3a63');
    $secondaryColor = option('secondary_color', '#1a5a8a');
    $accentColor = option('accent_color', '#fa4f26');
    ?>
    <section class="relative text-white overflow-hidden" style="min-height: 400px; background: linear-gradient(135deg, #1a1a2e, #16213e);">
        <!-- Warehouse Background Image with Overlay -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=1920&q=80'); opacity: 0.3;"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-800/90 to-transparent"></div>
        </div>
        
        <div class="container mx-auto px-4 py-16 sm:py-24 md:py-32 relative z-10">
            <div class="max-w-4xl space-y-6 sm:space-y-8">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold fade-in-up leading-tight text-white">
                    <?php echo e($heroTitle); ?>
                </h1>
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-gray-200 max-w-2xl fade-in-up leading-relaxed" style="animation-delay: 0.2s;">
                    <?php echo e($heroSubtitle); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4 sm:pt-6 fade-in-up" style="animation-delay: 0.4s;">
                    <a href="<?php echo base_url('products.php'); ?>" 
                       class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-orange-500 hover:bg-orange-600 rounded-lg font-bold text-base sm:text-lg text-white transition-all duration-300 hover:scale-105 transform shadow-lg inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="whitespace-nowrap">Browse Our Catalog</span>
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" 
                       class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 border-2 border-white text-white rounded-lg font-bold text-base sm:text-lg hover:bg-white/10 transition-all duration-300 hover:scale-105 transform inline-flex items-center justify-center gap-2">
                        Get a Quote
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
    return;
}
?>

<section id="hero-slider" class="relative">
    <?php foreach ($slides as $index => $slide): ?>
        <div class="slider-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="opacity: <?php echo $index === 0 ? '1' : '0'; ?>;">
            <img 
                src="<?php echo e(fullImageUrl($slide['image_url'])); ?>" 
                alt="<?php echo e($slide['title']); ?>"
                loading="eager"
            >
            <div class="slider-content">
                <div class="container mx-auto px-4 text-center text-white">
                    <div class="max-w-4xl mx-auto space-y-6">
                        <?php if ($slide['subtitle']): ?>
                            <p class="text-lg md:text-xl text-gray-200 font-medium fade-in-up"><?php echo e($slide['subtitle']); ?></p>
                        <?php endif; ?>
                        <h1 class="text-4xl md:text-6xl font-bold fade-in-up" style="animation-delay: 0.2s;"><?php echo e($slide['title']); ?></h1>
                        <?php if ($slide['description']): ?>
                            <p class="text-xl text-gray-200 max-w-2xl mx-auto fade-in-up" style="animation-delay: 0.4s;"><?php echo e($slide['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($slide['link_url'] && $slide['link_text']): ?>
                            <div class="pt-4 fade-in-up" style="animation-delay: 0.6s;">
                                <a 
                                    href="<?php echo e($slide['link_url']); ?>" 
                                    class="inline-block px-8 py-4 rounded-md font-semibold text-lg transition-all hover:scale-105 shadow-lg"
                                    style="background-color: <?php echo e($slide['button_color']); ?>; color: white;"
                                >
                                    <?php echo e($slide['link_text']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<script src="<?php echo asset('ae-includes/js/slider.js'); ?>?v=<?php echo time(); ?>"></script>

