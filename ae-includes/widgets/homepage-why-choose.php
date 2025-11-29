<?php
/**
 * Why Choose S3V Section Widget
 * Value proposition and trust building
 */

$features = [
    [
        'title' => 'Local Expertise',
        'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'description' => 'Deep understanding of Cambodia\'s warehouse and factory needs with local support.'
    ],
    [
        'title' => 'Complete Solutions',
        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'description' => 'End-to-end services from consultation to installation and maintenance.'
    ],
    [
        'title' => 'Trusted Brands',
        'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'description' => 'Equipment from world-renowned manufacturers with full warranty support.'
    ],
    [
        'title' => 'Responsive Support',
        'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
        'description' => '24/7 technical support and rapid response maintenance services.'
    ],
];

$stats = [
    ['value' => '10+', 'label' => 'Years Experience'],
    ['value' => '100+', 'label' => 'Warehouses Served'],
    ['value' => '500+', 'label' => 'Happy Customers'],
];
?>

<section class="homepage-why-choose-section" aria-labelledby="why-choose-heading">
    <div class="homepage-why-choose-container">
        <div class="homepage-section-header">
            <h2 id="why-choose-heading" class="homepage-section-title">Why Partner with S3V Group?</h2>
            <p class="homepage-section-subtitle">Your trusted partner for warehouse and factory equipment in Cambodia</p>
        </div>
        
        <div class="homepage-why-choose-stats">
            <?php foreach ($stats as $stat): ?>
                <div class="homepage-stat-item">
                    <div class="homepage-stat-value"><?php echo htmlspecialchars($stat['value']); ?></div>
                    <div class="homepage-stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="homepage-why-choose-grid">
            <?php foreach ($features as $index => $feature): ?>
                <div class="homepage-feature-card modern-animate-on-scroll">
                    <div class="homepage-feature-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($feature['icon']); ?>"/>
                        </svg>
                    </div>
                    <h3 class="homepage-feature-title"><?php echo htmlspecialchars($feature['title']); ?></h3>
                    <p class="homepage-feature-description"><?php echo htmlspecialchars($feature['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

