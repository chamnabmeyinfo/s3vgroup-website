<?php
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

use App\Database\Connection;
use App\Domain\Content\TestimonialRepository;

$db = getDB();
$repository = new TestimonialRepository($db);
$testimonials = $repository->published();

$pageTitle = 'Testimonials';
$pageDescription = 'What our customers say about us';

include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4" style="color: var(--primary-color);">Customer Testimonials</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            See what our customers have to say about our products and services
        </p>
    </div>

    <?php if (empty($testimonials)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500">No testimonials available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4 mb-4">
                        <?php if ($testimonial['avatar']): ?>
                            <img src="<?php echo e($testimonial['avatar']); ?>" alt="<?php echo e($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-full bg-[#0b3a63] text-white flex items-center justify-center font-semibold">
                                <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900"><?php echo e($testimonial['name']); ?></h3>
                            <?php if ($testimonial['company']): ?>
                                <p class="text-sm text-gray-600"><?php echo e($testimonial['company']); ?></p>
                            <?php endif; ?>
                            <?php if ($testimonial['position']): ?>
                                <p class="text-xs text-gray-500"><?php echo e($testimonial['position']); ?></p>
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
                    
                    <?php if ($testimonial['featured']): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">★ Featured</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

