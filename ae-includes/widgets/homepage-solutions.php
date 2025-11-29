<?php
/**
 * Highlighted Solutions Section Widget
 * Showcases end-to-end solutions and key benefits
 */

$solutionsTitle = option('solutions_title', 'Complete Solutions, From Design to Delivery');
$solutionsDescription = option('solutions_description', 'We provide comprehensive warehouse and factory equipment solutions, from initial consultation to installation and ongoing maintenance.');
?>

<section class="homepage-solutions-section" aria-labelledby="solutions-heading">
    <div class="homepage-solutions-container">
        <div class="homepage-solutions-content">
            <div class="homepage-solutions-text">
                <h2 id="solutions-heading" class="homepage-solutions-title"><?php echo e($solutionsTitle); ?></h2>
                <p class="homepage-solutions-description"><?php echo e($solutionsDescription); ?></p>
                
                <ul class="homepage-solutions-benefits">
                    <li class="homepage-solutions-benefit">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Optimized warehouse layout and design</span>
                    </li>
                    <li class="homepage-solutions-benefit">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Safe and certified equipment from trusted brands</span>
                    </li>
                    <li class="homepage-solutions-benefit">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Professional installation by certified technicians</span>
                    </li>
                    <li class="homepage-solutions-benefit">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Local support and maintenance services</span>
                    </li>
                    <li class="homepage-solutions-benefit">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Competitive pricing and flexible financing options</span>
                    </li>
                </ul>
                
                <div class="homepage-solutions-cta">
                    <a href="<?php echo base_url('quote.php'); ?>" class="homepage-btn homepage-btn-primary">
                        Get Your Free Consultation
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="homepage-solutions-visual">
                <div class="homepage-solutions-image-wrapper">
                    <img 
                        src="https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?auto=format&fit=crop&w=800&q=80" 
                        alt="Professional warehouse installation" 
                        class="homepage-solutions-image"
                        loading="lazy"
                        width="600"
                        height="500"
                    >
                </div>
            </div>
        </div>
    </div>
</section>

