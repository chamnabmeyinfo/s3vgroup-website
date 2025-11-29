<?php
/**
 * Product Categories Section Widget
 * Shows main equipment categories
 */

// Get categories from database or use defaults
$categoryData = [];
if (isset($categories) && !empty($categories)) {
    $categoryData = array_slice($categories, 0, 8); // Limit to 8 categories
} else {
    // Default categories if database is empty
    $categoryData = [
        [
            'name' => 'Forklifts',
            'slug' => 'forklifts',
            'description' => 'Electric, diesel, and LPG forklifts for all material handling needs.',
            'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z'
        ],
        [
            'name' => 'Racking & Storage',
            'slug' => 'racking-storage',
            'description' => 'Pallet racking, shelving systems, and warehouse storage solutions.',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
        ],
        [
            'name' => 'Material Handling',
            'slug' => 'material-handling',
            'description' => 'Pallet jacks, hand trucks, and manual handling equipment.',
            'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'
        ],
        [
            'name' => 'Monitoring & Safety',
            'slug' => 'monitoring-safety',
            'description' => 'Industrial monitoring systems and safety equipment for warehouses.',
            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        ],
        [
            'name' => 'Industrial Tools',
            'slug' => 'industrial-tools',
            'description' => 'Professional tools and equipment for factory and warehouse operations.',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
        ],
        [
            'name' => 'Conveyor Systems',
            'slug' => 'conveyor-systems',
            'description' => 'Automated conveyor belts and material transport systems.',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'
        ],
    ];
}
?>

<section class="homepage-categories-section" aria-labelledby="categories-heading">
    <div class="homepage-categories-container">
        <div class="homepage-section-header">
            <h2 id="categories-heading" class="homepage-section-title">Equipment We Supply</h2>
            <p class="homepage-section-subtitle">Everything you need for a safe, efficient warehouse and factory floor</p>
        </div>
        
        <div class="homepage-categories-grid">
            <?php foreach ($categoryData as $index => $category): 
                $categoryName = $category['name'] ?? '';
                $categorySlug = $category['slug'] ?? '';
                $categoryDesc = $category['description'] ?? 'Explore our range of ' . strtolower($categoryName) . ' products.';
                $categoryIcon = $category['icon'] ?? 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
                $categoryLink = $categorySlug ? base_url('products.php?category=' . urlencode($categorySlug)) : base_url('products.php');
            ?>
                <a href="<?php echo $categoryLink; ?>" class="homepage-category-card modern-animate-on-scroll" aria-label="View <?php echo htmlspecialchars($categoryName); ?> products">
                    <div class="homepage-category-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($categoryIcon); ?>"/>
                        </svg>
                    </div>
                    <h3 class="homepage-category-name"><?php echo htmlspecialchars($categoryName); ?></h3>
                    <p class="homepage-category-description"><?php echo htmlspecialchars($categoryDesc); ?></p>
                    <span class="homepage-category-link">
                        View Products
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

