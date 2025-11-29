<?php
/**
 * Homepage Hero Section Widget
 * Two-column layout with text + CTAs on left, visual on right
 */

$heroTitle = option('hero_title', 'Warehouse & Factory Equipment Solutions in Cambodia');
$heroSubtitle = option('hero_subtitle', 'Forklifts, material handling systems, storage racks, and industrial tools from trusted brands—designed to make your warehouse safer, faster, and more efficient.');
$heroImage = option('hero_image', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80');
?>

<section class="homepage-hero-section" aria-label="Hero section">
    <div class="homepage-hero-container">
        <div class="homepage-hero-content">
            <div class="homepage-hero-text">
                <h1 class="homepage-hero-title"><?php echo e($heroTitle); ?></h1>
                <p class="homepage-hero-description"><?php echo e($heroSubtitle); ?></p>
                
                <div class="homepage-hero-ctas">
                    <a href="<?php echo base_url('quote.php'); ?>" class="homepage-btn homepage-btn-primary" aria-label="Request a quote">
                        Request a Quote
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('products.php'); ?>" class="homepage-btn homepage-btn-secondary" aria-label="Browse products">
                        Browse Products
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                
                <div class="homepage-hero-trust">
                    <p class="homepage-hero-trust-text">Trusted by warehouses and factories across Cambodia</p>
                    <div class="homepage-hero-trust-logos">
                        <?php
                        // Placeholder trust logos - can be replaced with actual brand logos
                        for ($i = 0; $i < 5; $i++):
                        ?>
                            <div class="homepage-hero-trust-logo" aria-hidden="true">
                                <div class="homepage-hero-trust-logo-placeholder"></div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="homepage-hero-visual">
                <div class="homepage-hero-image-wrapper">
                    <img 
                        src="<?php echo e($heroImage); ?>" 
                        alt="Warehouse equipment and industrial solutions" 
                        class="homepage-hero-image"
                        loading="eager"
                        width="800"
                        height="600"
                    >
                    <div class="homepage-hero-image-overlay"></div>
                </div>
            </div>
        </div>
    </div>
</section>

