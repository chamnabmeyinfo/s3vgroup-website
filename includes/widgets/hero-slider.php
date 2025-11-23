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
    <section class="bg-gradient-to-br text-white" style="background: linear-gradient(to bottom right, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
        <div class="container mx-auto px-4 py-24">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-5xl md:text-6xl font-bold fade-in-up"><?php echo e($heroTitle); ?></h1>
                <p class="text-xl text-gray-200 max-w-2xl mx-auto fade-in-up" style="animation-delay: 0.2s;"><?php echo e($heroSubtitle); ?></p>
                <div class="flex gap-4 justify-center pt-4 fade-in-up" style="animation-delay: 0.4s;">
                    <a href="<?php echo base_url('products.php'); ?>" class="px-6 py-3 bg-white rounded-md font-semibold hover:bg-gray-100 transition-colors" style="color: <?php echo e($primaryColor); ?>;">
                        Browse Products
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors" style="background-color: <?php echo e($accentColor); ?>; border-color: <?php echo e($accentColor); ?>;">
                        Request Quote
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
    // Use default hero section if no slides
    $heroTitle = option('homepage_hero_title', 'Warehouse & Factory Equipment Solutions');
    $heroSubtitle = option('homepage_hero_subtitle', 'Leading supplier of industrial equipment in Cambodia.');
    $primaryColor = option('primary_color', '#0b3a63');
    $secondaryColor = option('secondary_color', '#1a5a8a');
    $accentColor = option('accent_color', '#fa4f26');
    ?>
    <section class="bg-gradient-to-br text-white" style="background: linear-gradient(to bottom right, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
        <div class="container mx-auto px-4 py-24">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-5xl md:text-6xl font-bold fade-in-up"><?php echo e($heroTitle); ?></h1>
                <p class="text-xl text-gray-200 max-w-2xl mx-auto fade-in-up" style="animation-delay: 0.2s;"><?php echo e($heroSubtitle); ?></p>
                <div class="flex gap-4 justify-center pt-4 fade-in-up" style="animation-delay: 0.4s;">
                    <a href="/products.php" class="px-6 py-3 bg-white rounded-md font-semibold hover:bg-gray-100 transition-colors hover:scale-105 transform" style="color: <?php echo e($primaryColor); ?>;">
                        Browse Products
                    </a>
                    <a href="/quote.php" class="px-6 py-3 border-2 border-white text-white rounded-md font-semibold hover:bg-white/10 transition-colors hover:scale-105 transform" style="background-color: <?php echo e($accentColor); ?>; border-color: <?php echo e($accentColor); ?>;">
                        Request Quote
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
                src="<?php echo e($slide['image_url']); ?>" 
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

<script src="<?php echo asset('includes/js/slider.js'); ?>?v=<?php echo time(); ?>"></script>

