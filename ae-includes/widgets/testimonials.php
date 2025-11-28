<?php
/**
 * Testimonials Widget
 * Usage: include __DIR__ . '/widgets/testimonials.php';
 * 
 * @param int $limit Number of testimonials to show (default: 6)
 * @param bool $featuredOnly Show only featured testimonials (default: true)
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

use App\Database\Connection;
use App\Domain\Content\TestimonialRepository;

$limit = $limit ?? 6;
$featuredOnly = $featuredOnly ?? true;

if (!option('enable_testimonials', '1')) {
    return;
}

$db = getDB();
$repository = new TestimonialRepository($db);
$testimonials = $featuredOnly ? $repository->featured($limit) : array_slice($repository->published(), 0, $limit);

if (empty($testimonials)) {
    return;
}
?>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 animate-on-scroll"
             data-animation="fadeInDown">
            <h2 class="text-4xl font-bold mb-4 text-reveal" style="color: var(--primary-color);">What Our Customers Say</h2>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                Don't just take our word for it - see what our customers have to say about us
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 stagger-children">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 hover-lift animate-on-scroll"
                     data-animation="fadeInUp"
                     data-delay="<?php echo ($index * 0.1); ?>">
                    <div class="flex items-start gap-4 mb-4">
                        <?php if ($testimonial['avatar']): ?>
                            <img src="<?php echo e($testimonial['avatar']); ?>" alt="<?php echo e($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-full bg-[#0b3a63] text-white flex items-center justify-center font-semibold" style="background-color: var(--primary-color);">
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
                    
                    <p class="text-gray-700 leading-relaxed line-clamp-4"><?php echo nl2br(e($testimonial['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($testimonials) >= $limit): ?>
            <div class="text-center mt-8">
                <a href="/testimonials.php" class="inline-flex items-center px-6 py-3 bg-[#0b3a63] text-white rounded-md font-semibold hover:bg-[#1a5a8a] transition-colors" style="background-color: var(--primary-color);">
                    View All Testimonials
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

