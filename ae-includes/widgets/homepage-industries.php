<?php
/**
 * Industries / Use Cases Section Widget
 * Shows relevance to different customer types
 */

$industries = [
    [
        'name' => 'Manufacturing',
        'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        'description' => 'Streamline production lines with reliable material handling and storage solutions.'
    ],
    [
        'name' => 'Logistics & Distribution',
        'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        'description' => 'Optimize warehouse operations for faster order fulfillment and distribution.'
    ],
    [
        'name' => 'Retail & E-commerce',
        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'description' => 'Efficient storage and picking systems for growing retail operations.'
    ],
    [
        'name' => 'Cold Storage',
        'icon' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z',
        'description' => 'Specialized equipment for temperature-controlled storage facilities.'
    ],
];
?>

<section class="homepage-industries-section" aria-labelledby="industries-heading">
    <div class="homepage-industries-container">
        <div class="homepage-section-header">
            <h2 id="industries-heading" class="homepage-section-title">Built for Modern Warehouses & Factories</h2>
            <p class="homepage-section-subtitle">Solutions tailored to your industry's unique needs</p>
        </div>
        
        <div class="homepage-industries-grid">
            <?php foreach ($industries as $index => $industry): ?>
                <div class="homepage-industry-card modern-animate-on-scroll">
                    <div class="homepage-industry-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($industry['icon']); ?>"/>
                        </svg>
                    </div>
                    <h3 class="homepage-industry-name"><?php echo htmlspecialchars($industry['name']); ?></h3>
                    <p class="homepage-industry-description"><?php echo htmlspecialchars($industry['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

