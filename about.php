<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/bootstrap/app.php';

use App\Database\Connection;
use App\Domain\Content\CompanyStoryRepository;
use App\Domain\Content\CeoMessageRepository;

$db = getDB();
$storyRepository = new CompanyStoryRepository($db);
$ceoRepository = new CeoMessageRepository($db);

$story = $storyRepository->published();
$ceoMessage = $ceoRepository->published();

$pageTitle = 'About Us';
$pageDescription = $story['subtitle'] ?? 'Learn more about our company, our mission, vision, and values';

$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

include __DIR__ . '/includes/header.php';
?>

<?php if ($story): ?>
    <!-- Hero Section -->
    <?php if ($story['heroImage']): ?>
        <section class="about-hero with-image relative overflow-hidden">
            <img src="<?php echo e($story['heroImage']); ?>" alt="<?php echo e($story['title']); ?>" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60"></div>
            <div class="container mx-auto px-4 relative z-10 py-20">
                <div class="max-w-4xl mx-auto text-center text-white animate-fade-in-up">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight"><?php echo e($story['title']); ?></h1>
                    <?php if ($story['subtitle']): ?>
                        <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-3xl mx-auto"><?php echo e($story['subtitle']); ?></p>
                    <?php endif; ?>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                        <a href="/products.php" class="btn-primary text-white px-8 py-4 text-lg font-semibold rounded-full shadow-lg hover:shadow-xl" style="background-color: <?php echo e($primaryColor); ?>;">
                            Explore Our Products
                        </a>
                        <a href="/contact.php" class="btn-secondary text-white border-2 border-white px-8 py-4 text-lg font-semibold rounded-full hover:bg-white/10">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="about-hero">
            <div class="container mx-auto px-4 py-20">
                <div class="max-w-4xl mx-auto text-center text-white animate-fade-in-up">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight"><?php echo e($story['title']); ?></h1>
                    <?php if ($story['subtitle']): ?>
                        <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-3xl mx-auto"><?php echo e($story['subtitle']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="bg-white">
        <!-- Introduction -->
        <?php if ($story['introduction']): ?>
            <section class="section-padding">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center animate-on-scroll">
                        <p class="text-xl md:text-2xl text-gray-700 leading-relaxed">
                            <?php echo nl2br(e($story['introduction'])); ?>
                        </p>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- CEO Message -->
        <?php if ($ceoMessage): ?>
            <section class="section-padding bg-gray-50">
                <div class="container mx-auto px-4">
                    <div class="max-w-5xl mx-auto">
                        <div class="ceo-message-card animate-on-scroll hover-lift"
                             data-animation="fadeInUp">
                            <div class="flex flex-col md:flex-row gap-8 items-start mb-8">
                                <?php if ($ceoMessage['photo']): ?>
                                    <div class="flex-shrink-0">
                                        <img 
                                            src="<?php echo e($ceoMessage['photo']); ?>" 
                                            alt="<?php echo e($ceoMessage['name']); ?>" 
                                            class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 shadow-xl"
                                            style="border-color: <?php echo e($primaryColor); ?>;"
                                            loading="lazy"
                                        >
                                    </div>
                                <?php else: ?>
                                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full flex items-center justify-center text-4xl font-bold text-white shadow-xl" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
                                        <?php echo strtoupper(substr($ceoMessage['name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gradient" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                        <?php echo e($ceoMessage['title']); ?>
                                    </h2>
                                    <p class="text-2xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>;">
                                        <?php echo e($ceoMessage['name']); ?>
                                    </p>
                                    <?php if ($ceoMessage['position']): ?>
                                        <p class="text-lg text-gray-600 font-medium">
                                            <?php echo e($ceoMessage['position']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                                <p class="text-lg md:text-xl leading-relaxed whitespace-pre-line"><?php echo nl2br(e($ceoMessage['message'])); ?></p>
                            </div>
                            <?php if ($ceoMessage['signature']): ?>
                                <div class="mt-8 pt-8 border-t border-gray-200">
                                    <img src="<?php echo e($ceoMessage['signature']); ?>" alt="Signature" class="h-20 w-auto opacity-80">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- History -->
        <?php if ($story['history']): ?>
            <section class="section-padding">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto animate-on-scroll">
                        <div class="text-center mb-12">
                            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gradient" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                Our History
                            </h2>
                            <div class="w-24 h-1 mx-auto rounded-full" style="background: linear-gradient(90deg, <?php echo e($primaryColor); ?>, <?php echo e($accentColor); ?>);"></div>
                        </div>
                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                            <p class="text-lg leading-relaxed whitespace-pre-line"><?php echo nl2br(e($story['history'])); ?></p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Mission & Vision -->
        <?php if ($story['mission'] || $story['vision']): ?>
            <section class="section-padding bg-gray-50">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 max-w-6xl mx-auto">
                        <?php if ($story['mission']): ?>
                            <div class="product-info-card animate-on-scroll">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
                                        🎯
                                    </div>
                                    <h3 class="text-3xl md:text-4xl font-bold" style="color: <?php echo e($primaryColor); ?>;">
                                        Our Mission
                                    </h3>
                                </div>
                                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                                    <p class="text-lg leading-relaxed whitespace-pre-line"><?php echo nl2br(e($story['mission'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($story['vision']): ?>
                            <div class="product-info-card animate-on-scroll">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background: linear-gradient(135deg, <?php echo e($secondaryColor); ?>, <?php echo e($accentColor); ?>);">
                                        👁️
                                    </div>
                                    <h3 class="text-3xl md:text-4xl font-bold" style="color: <?php echo e($secondaryColor); ?>;">
                                        Our Vision
                                    </h3>
                                </div>
                                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                                    <p class="text-lg leading-relaxed whitespace-pre-line"><?php echo nl2br(e($story['vision'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Core Values -->
        <?php if ($story['values']): ?>
            <?php
            $values = json_decode($story['values'], true);
            if (is_array($values) && !empty($values)):
            ?>
                <section class="section-padding">
                    <div class="container mx-auto px-4">
                        <div class="text-center mb-12 animate-on-scroll">
                            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gradient" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                Our Core Values
                            </h2>
                            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                                The principles that guide everything we do
                            </p>
                            <div class="w-24 h-1 mx-auto mt-4 rounded-full" style="background: linear-gradient(90deg, <?php echo e($primaryColor); ?>, <?php echo e($accentColor); ?>);"></div>
                        </div>
                        <div class="values-grid max-w-6xl mx-auto">
                            <?php foreach ($values as $index => $value): ?>
                                <div class="value-card animate-on-scroll" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                                    <div class="text-5xl mb-4" style="color: <?php echo e($accentColor); ?>;">★</div>
                                    <h3 class="text-2xl font-bold mb-3" style="color: <?php echo e($primaryColor); ?>;"><?php echo e($value); ?></h3>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Milestones -->
        <?php if ($story['milestones']): ?>
            <?php
            $milestones = json_decode($story['milestones'], true);
            if (is_array($milestones) && !empty($milestones)):
            ?>
                <section class="section-padding bg-gray-50">
                    <div class="container mx-auto px-4">
                        <div class="text-center mb-12 animate-on-scroll">
                            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gradient" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                Key Milestones
                            </h2>
                            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                                Our journey of growth and achievement
                            </p>
                            <div class="w-24 h-1 mx-auto mt-4 rounded-full" style="background: linear-gradient(90deg, <?php echo e($primaryColor); ?>, <?php echo e($accentColor); ?>);"></div>
                        </div>
                        <div class="max-w-4xl mx-auto">
                            <div class="milestone-timeline">
                                <?php foreach ($milestones as $index => $milestone): ?>
                                    <div class="milestone-item animate-on-scroll" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                                        <div class="flex gap-6 items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
                                                    <?php echo e($milestone['year'] ?? date('Y')); ?>
                                                </div>
                                            </div>
                                            <div class="flex-1 pt-2">
                                                <h3 class="text-xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>;">
                                                    <?php echo e($milestone['event'] ?? ''); ?>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Achievements -->
        <?php if ($story['achievements']): ?>
            <section class="section-padding">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto animate-on-scroll">
                        <div class="text-center mb-12">
                            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gradient" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                Our Achievements
                            </h2>
                            <div class="w-24 h-1 mx-auto rounded-full" style="background: linear-gradient(90deg, <?php echo e($primaryColor); ?>, <?php echo e($accentColor); ?>);"></div>
                        </div>
                        <div class="product-info-card">
                            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                                <p class="text-lg leading-relaxed whitespace-pre-line"><?php echo nl2br(e($story['achievements'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- CTA Section -->
        <section class="section-padding-lg" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center text-white animate-on-scroll">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6">Ready to Work With Us?</h2>
                    <p class="text-xl md:text-2xl text-gray-200 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Contact us today to discuss how we can help with your warehouse and factory equipment needs.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="/quote.php" class="btn-primary text-white px-8 py-4 text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl bg-white" style="color: <?php echo e($primaryColor); ?>;">
                            Request a Quote
                        </a>
                        <a href="/contact.php" class="btn-secondary text-white border-2 border-white px-8 py-4 text-lg font-semibold rounded-full hover:bg-white/10">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>
<?php else: ?>
    <section class="section-padding">
        <div class="container mx-auto px-4 text-center">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold mb-4" style="color: <?php echo e($primaryColor); ?>;">About Us</h1>
                <p class="text-gray-600 text-lg mb-4">Company story content is coming soon.</p>
                <a href="/" class="inline-block px-6 py-3 text-white rounded-lg font-semibold transition-all hover:scale-105 transform shadow-md hover:shadow-lg" style="background-color: <?php echo e($primaryColor); ?>;">
                    Back to Home
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
