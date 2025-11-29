<?php
/**
 * Process / How It Works Section Widget
 * Reduces friction by clarifying engagement steps
 */

$steps = [
    [
        'number' => '1',
        'title' => 'Tell us about your project',
        'description' => 'Share your warehouse or factory requirements, and we\'ll understand your specific needs.'
    ],
    [
        'number' => '2',
        'title' => 'Get an expert recommendation',
        'description' => 'Our team analyzes your space and operations to recommend the best equipment solutions.'
    ],
    [
        'number' => '3',
        'title' => 'Delivery & installation',
        'description' => 'We handle everything from procurement to professional installation by certified technicians.'
    ],
    [
        'number' => '4',
        'title' => 'Ongoing support & maintenance',
        'description' => 'Continuous support, maintenance, and training to keep your operations running smoothly.'
    ],
];
?>

<section class="homepage-process-section" aria-labelledby="process-heading">
    <div class="homepage-process-container">
        <div class="homepage-section-header">
            <h2 id="process-heading" class="homepage-section-title">How We Work</h2>
            <p class="homepage-section-subtitle">A simple, streamlined process from consultation to ongoing support</p>
        </div>
        
        <div class="homepage-process-steps">
            <?php foreach ($steps as $index => $step): ?>
                <div class="homepage-process-step modern-animate-on-scroll">
                    <div class="homepage-process-step-number">
                        <span class="homepage-process-step-number-text"><?php echo htmlspecialchars($step['number']); ?></span>
                    </div>
                    <div class="homepage-process-step-content">
                        <h3 class="homepage-process-step-title"><?php echo htmlspecialchars($step['title']); ?></h3>
                        <p class="homepage-process-step-description"><?php echo htmlspecialchars($step['description']); ?></p>
                    </div>
                    <?php if ($index < count($steps) - 1): ?>
                        <div class="homepage-process-step-connector" aria-hidden="true">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="homepage-process-cta">
            <a href="<?php echo base_url('quote.php'); ?>" class="homepage-btn homepage-btn-primary">
                Start Your Project
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>

